<?php
/**
 * Model untuk mengelola antrian pesan WhatsApp
 * File: app/models/WaQueue_model.php
 */
class WaQueue_model
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    /**
     * Tambah pesan ke antrian atau kirim langsung tergantung setting
     * Jika wa_queue_enabled = 1: masuk antrian (default)
     * Jika wa_queue_enabled = 0: kirim langsung via Fonnte
     */
    public function addToQueue($noWa, $pesan, $jenis = 'general', $metadata = null, $scheduledAt = null)
    {
        // Cek apakah mode antrian aktif
        $queueEnabled = $this->isQueueEnabled();

        if (!$queueEnabled) {
            // Mode LANGSUNG: kirim langsung via Fonnte tanpa antrian
            return $this->sendDirect($noWa, $pesan, $jenis, $metadata);
        }

        // Mode ANTRIAN: masukkan ke database queue
        // UPDATE: Gunakan PHP date() untuk created_at agar timezone konsisten (WIB)
        // Set max_attempts = 3 agar bisa retry otomatis
        $this->db->query('INSERT INTO wa_message_queue (no_wa, pesan, jenis, metadata, scheduled_at, status, created_at, max_attempts) 
                          VALUES (:no_wa, :pesan, :jenis, :metadata, :scheduled_at, "pending", :created_at, 3)');
        $this->db->bind(':no_wa', $noWa);
        $this->db->bind(':pesan', $pesan);
        $this->db->bind(':jenis', $jenis);
        $this->db->bind(':metadata', $metadata ? json_encode($metadata) : null);
        $this->db->bind(':scheduled_at', $scheduledAt ?? date('Y-m-d H:i:s'));
        $this->db->bind(':created_at', date('Y-m-d H:i:s'));
        $this->db->execute();
        return $this->db->lastInsertId();
    }

    /**
     * Cek apakah mode antrian aktif
     */
    private function isQueueEnabled()
    {
        $this->db->query("SELECT value FROM pengaturan_sistem WHERE key_name = :key");
        $this->db->bind(':key', 'wa_queue_enabled');
        $result = $this->db->single();

        // Default ke enabled (1) jika tidak ditemukan setting
        // Gunakan false comparison agar lebih aman ( '1' == 1 )
        return ($result['value'] ?? '1') == '1';
    }

    /**
     * Kirim pesan langsung tanpa antrian
     */
    private function sendDirect($noWa, $pesan, $jenis, $metadata)
    {
        try {
            require_once APPROOT . '/app/core/Fonnte.php';
            $fonnte = new Fonnte();
            $result = $fonnte->send($noWa, $pesan);

            // Periksa status pengiriman
            // Fonnte::send mengembalikan array ['status' => bool, 'reason' => string]
            $isSuccess = isset($result['status']) && $result['status'] == true;
            $status = $isSuccess ? 'sent' : 'failed';
            $errorMessage = $isSuccess ? null : ($result['reason'] ?? 'Unknown error');

            // Log ke database
            // UPDATE: Gunakan PHP date() untuk created_at dan sent_at agar timezone konsisten
            // Set max_attempts = 3 agar jika gagal bisa di-retry oleh cron (jika nanti dinyalakan)
            $now = date('Y-m-d H:i:s');
            $this->db->query('INSERT INTO wa_message_queue (no_wa, pesan, jenis, metadata, status, sent_at, created_at, attempts, max_attempts, error_message) 
                              VALUES (:no_wa, :pesan, :jenis, :metadata, :status, :sent_at, :created_at, 1, 3, :error_message)');
            $this->db->bind(':no_wa', $noWa);
            $this->db->bind(':pesan', $pesan);
            $this->db->bind(':jenis', $jenis);
            $this->db->bind(':metadata', $metadata ? json_encode($metadata) : null);
            $this->db->bind(':status', $status);
            $this->db->bind(':sent_at', $now);
            $this->db->bind(':created_at', $now);
            $this->db->bind(':error_message', $errorMessage);
            $this->db->execute();
            $insertedId = $this->db->lastInsertId();

            if ($isSuccess) {
                // Log ke wa_message_log jika sukses (opsional, untuk konsistensi)
                // $this->logMessage($insertedId, 'sent', json_encode($result));
                error_log("WA Direct Send SUCCESS to {$noWa}");
            } else {
                error_log("WA Direct Send FAILED to {$noWa}: {$errorMessage}");
            }

            return $insertedId;
        } catch (Exception $e) {
            error_log("WA Direct Send Exception: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Tambah batch pesan ke antrian dengan delay bertahap
     */
    public function addBatchToQueue($messages, $delayBetweenSeconds = 10)
    {
        $scheduledTime = time();
        $ids = [];

        foreach ($messages as $msg) {
            $scheduledAt = date('Y-m-d H:i:s', $scheduledTime);
            $id = $this->addToQueue(
                $msg['no_wa'],
                $msg['pesan'],
                $msg['jenis'] ?? 'general',
                $msg['metadata'] ?? null,
                $scheduledAt
            );
            $ids[] = $id;
            $scheduledTime += $delayBetweenSeconds;
        }

        return $ids;
    }

    /**
     * Ambil pesan pending berikutnya untuk diproses
     */
    public function getNextPending()
    {
        $this->db->query('SELECT * FROM wa_message_queue 
                          WHERE status = "pending" 
                          AND scheduled_at <= NOW()
                          AND attempts < max_attempts
                          ORDER BY scheduled_at ASC 
                          LIMIT 1');
        return $this->db->single();
    }

    /**
     * Ambil pesan yang perlu diproses (pending atau failed yang masih punya sisa attempts)
     */
    public function getPendingMessages($limit = 5)
    {
        $this->db->query('SELECT * FROM wa_message_queue 
                          WHERE (status = "pending" OR (status = "failed" AND attempts < max_attempts))
                          AND scheduled_at <= NOW()
                          ORDER BY scheduled_at ASC 
                          LIMIT :limit');
        $this->db->bind(':limit', $limit);
        return $this->db->resultSet();
    }

    /**
     * Tandai pesan sedang diproses
     */
    public function markAsProcessing($id)
    {
        $this->db->query('UPDATE wa_message_queue 
                          SET status = "processing", attempts = attempts + 1 
                          WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    /**
     * Tandai pesan berhasil terkirim
     */
    public function markAsSent($id, $response = null, $waAccountId = null)
    {
        // UPDATE: Gunakan PHP date() alih-alih NOW()
        $sql = 'UPDATE wa_message_queue 
                SET status = "sent", sent_at = :sent_at, error_message = NULL';
        if ($waAccountId) {
            $sql .= ', wa_account_id = :wa_account_id';
        }
        $sql .= ' WHERE id = :id';

        $this->db->query($sql);
        $this->db->bind(':id', $id);
        $this->db->bind(':sent_at', date('Y-m-d H:i:s'));
        if ($waAccountId) {
            $this->db->bind(':wa_account_id', $waAccountId);
        }
        $this->db->execute();

        // Log ke tabel log
        $this->logMessage($id, 'sent', $response);

        return true;
    }

    /**
     * Tandai pesan gagal
     */
    /**
     * Tandai pesan gagal (dengan logic retry otomatis)
     */
    public function markAsFailed($id, $errorMessage = null)
    {
        $this->db->query('UPDATE wa_message_queue 
                          SET status = CASE WHEN attempts >= max_attempts THEN "failed" ELSE "pending" END,
                              error_message = :error
                          WHERE id = :id');
        $this->db->bind(':id', $id);
        $this->db->bind(':error', $errorMessage);
        $this->db->execute();

        // Log ke tabel log
        $this->logMessage($id, 'failed', $errorMessage);

        return true;
    }

    /**
     * Tandai pesan gagal secara langsung (TANPA retry logic)
     * Digunakan untuk Mode Direct agar status langsung Failed
     */
    public function markAsFailedDirect($id, $errorMessage = null)
    {
        $this->db->query('UPDATE wa_message_queue 
                          SET status = "failed",
                              error_message = :error
                          WHERE id = :id');
        $this->db->bind(':id', $id);
        $this->db->bind(':error', $errorMessage);
        $this->db->execute();

        // Log ke tabel log
        $this->logMessage($id, 'failed', $errorMessage);

        return true;
    }

    /**
     * Log pesan ke tabel log
     */
    private function logMessage($queueId, $status, $response = null)
    {
        try {
            $queue = $this->getById($queueId);
            if ($queue) {
                $this->db->query('INSERT INTO wa_message_log (queue_id, no_wa, jenis, status, response) 
                                  VALUES (:queue_id, :no_wa, :jenis, :status, :response)');
                $this->db->bind(':queue_id', $queueId);
                $this->db->bind(':no_wa', $queue['no_wa']);
                $this->db->bind(':jenis', $queue['jenis']);
                $this->db->bind(':status', $status);
                $this->db->bind(':response', $response);
                $this->db->execute();
            }
        } catch (Exception $e) {
            error_log("Error logging WA message: " . $e->getMessage());
        }
    }

    /**
     * Ambil pesan berdasarkan ID
     */
    public function getById($id)
    {
        $this->db->query('SELECT * FROM wa_message_queue WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    /**
     * Statistik antrian
     */
    public function getQueueStats()
    {
        $this->db->query('SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status = "processing" THEN 1 ELSE 0 END) as processing,
            SUM(CASE WHEN status = "sent" THEN 1 ELSE 0 END) as sent,
            SUM(CASE WHEN status = "failed" THEN 1 ELSE 0 END) as failed
        FROM wa_message_queue
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)');
        return $this->db->single();
    }

    /**
     * Statistik per jenis
     */
    public function getStatsByJenis()
    {
        $this->db->query('SELECT 
            jenis,
            COUNT(*) as total,
            SUM(CASE WHEN status = "sent" THEN 1 ELSE 0 END) as sent,
            SUM(CASE WHEN status = "failed" THEN 1 ELSE 0 END) as failed
        FROM wa_message_queue
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        GROUP BY jenis');
        return $this->db->resultSet();
    }

    /**
     * Ambil pesan terbaru untuk dashboard
     */
    public function getRecentMessages($limit = 50)
    {
        $this->db->query('SELECT * FROM wa_message_queue 
                          ORDER BY created_at DESC 
                          LIMIT :limit');
        $this->db->bind(':limit', $limit);
        return $this->db->resultSet();
    }

    /**
     * Ambil pesan by status
     */
    public function getByStatus($status, $limit = 50)
    {
        $this->db->query('SELECT * FROM wa_message_queue 
                          WHERE status = :status
                          ORDER BY created_at DESC 
                          LIMIT :limit');
        $this->db->bind(':status', $status);
        $this->db->bind(':limit', $limit);
        return $this->db->resultSet();
    }

    /**
     * Retry pesan yang gagal
     */
    public function retryFailed($id)
    {
        $this->db->query('UPDATE wa_message_queue 
                          SET status = "pending", attempts = 0, error_message = NULL 
                          WHERE id = :id AND status = "failed"');
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    /**
     * Retry semua pesan yang gagal
     */
    public function retryAllFailed()
    {
        $this->db->query('UPDATE wa_message_queue 
                          SET status = "pending", attempts = 0, error_message = NULL 
                          WHERE status = "failed"');
        return $this->db->execute();
    }

    /**
     * Hapus pesan lama (cleanup)
     */
    public function cleanup($daysOld = 30)
    {
        $this->db->query('DELETE FROM wa_message_queue 
                          WHERE created_at < DATE_SUB(NOW(), INTERVAL :days DAY)
                          AND status IN ("sent", "failed")');
        $this->db->bind(':days', $daysOld);
        return $this->db->execute();
    }

    /**
     * Hapus pesan berdasarkan ID
     */
    public function delete($id)
    {
        $this->db->query('DELETE FROM wa_message_queue WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    /**
     * Hapus banyak pesan sekaligus (bulk delete)
     * @param array $ids Array of message IDs
     */
    public function bulkDelete($ids)
    {
        if (empty($ids))
            return 0;

        // Sanitize IDs
        $ids = array_map('intval', $ids);
        $placeholders = implode(',', $ids);

        $this->db->query("DELETE FROM wa_message_queue WHERE id IN ({$placeholders})");
        return $this->db->execute();
    }

    /**
     * Hitung pesan hari ini
     */
    public function getTodayCount()
    {
        $this->db->query('SELECT COUNT(*) as total FROM wa_message_queue 
                          WHERE DATE(created_at) = CURDATE()');
        return $this->db->single()['total'] ?? 0;
    }

    /**
     * Ambil pesan dengan pagination
     * @param string $status Filter status (all, pending, sent, failed)
     * @param int $page Halaman saat ini
     * @param int $perPage Jumlah per halaman
     */
    public function getPaginated($status = 'all', $page = 1, $perPage = 20)
    {
        $offset = ($page - 1) * $perPage;

        // Count total
        if ($status === 'all') {
            $this->db->query('SELECT COUNT(*) as total FROM wa_message_queue WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)');
        } else {
            $this->db->query('SELECT COUNT(*) as total FROM wa_message_queue WHERE status = :status AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)');
            $this->db->bind(':status', $status);
        }
        $total = $this->db->single()['total'] ?? 0;

        // Get messages
        if ($status === 'all') {
            $this->db->query("SELECT * FROM wa_message_queue 
                              WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                              ORDER BY created_at DESC 
                              LIMIT {$perPage} OFFSET {$offset}");
        } else {
            $this->db->query("SELECT * FROM wa_message_queue 
                              WHERE status = :status AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                              ORDER BY created_at DESC 
                              LIMIT {$perPage} OFFSET {$offset}");
            $this->db->bind(':status', $status);
        }

        return [
            'data' => $this->db->resultSet(),
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($total / $perPage)
        ];
    }
}
