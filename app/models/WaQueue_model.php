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
     * Tambah pesan ke antrian
     */
    public function addToQueue($noWa, $pesan, $jenis = 'general', $metadata = null, $scheduledAt = null)
    {
        $this->db->query('INSERT INTO wa_message_queue (no_wa, pesan, jenis, metadata, scheduled_at, status) 
                          VALUES (:no_wa, :pesan, :jenis, :metadata, :scheduled_at, "pending")');
        $this->db->bind(':no_wa', $noWa);
        $this->db->bind(':pesan', $pesan);
        $this->db->bind(':jenis', $jenis);
        $this->db->bind(':metadata', $metadata ? json_encode($metadata) : null);
        $this->db->bind(':scheduled_at', $scheduledAt ?? date('Y-m-d H:i:s'));
        $this->db->execute();
        return $this->db->lastInsertId();
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
     * Ambil beberapa pesan pending
     */
    public function getPendingMessages($limit = 5)
    {
        $this->db->query('SELECT * FROM wa_message_queue 
                          WHERE status = "pending" 
                          AND scheduled_at <= NOW()
                          AND attempts < max_attempts
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
    public function markAsSent($id, $response = null)
    {
        $this->db->query('UPDATE wa_message_queue 
                          SET status = "sent", sent_at = NOW(), error_message = NULL 
                          WHERE id = :id');
        $this->db->bind(':id', $id);
        $this->db->execute();

        // Log ke tabel log
        $this->logMessage($id, 'sent', $response);

        return true;
    }

    /**
     * Tandai pesan gagal
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
     * Hitung pesan hari ini
     */
    public function getTodayCount()
    {
        $this->db->query('SELECT COUNT(*) as total FROM wa_message_queue 
                          WHERE DATE(created_at) = CURDATE()');
        $result = $this->db->single();
        return $result['total'] ?? 0;
    }
}
