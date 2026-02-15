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
        $this->db->query('INSERT INTO wa_message_queue (no_wa, pesan, jenis, metadata, scheduled_at, status, created_at) 
                          VALUES (:no_wa, :pesan, :jenis, :metadata, :scheduled_at, "pending", :created_at)');
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
        // Gunakan ORDER BY id DESC LIMIT 1 untuk mengambil settingan TERBARU
        // Ini mengatasi masalah jika ada duplikasi row di database
        $this->db->query("SELECT value FROM pengaturan_sistem WHERE key_name = :key ORDER BY id DESC LIMIT 1");
        $this->db->bind(':key', 'wa_queue_enabled');
        $result = $this->db->single();

        // Default ke enabled (1) jika tidak ditemukan setting
        return ($result['value'] ?? '1') == '1';
    }

    /**
     * Kirim pesan langsung tanpa antrian
     */
    /**
     * Kirim pesan langsung tanpa antrian (Supports Rotation)
     */
    private function sendDirect($noWa, $pesan, $jenis, $metadata)
    {
        try {
            require_once APPROOT . '/app/core/Fonnte.php';
            require_once APPROOT . '/app/models/WaAccount_model.php';

            $fonnte = new Fonnte();
            $waAccountModel = new WaAccount_model();

            // Get Rotation Settings manually
            $this->db->query("SELECT setting_key, setting_value FROM cms_settings WHERE setting_key IN ('wa_rotation_enabled', 'wa_rotation_mode', 'wa_last_account_id')");
            // Fallback for settings if cms_settings not used or using different table
            // Wait, cron uses PengaturanAplikasi_model which maps to 'pengaturan_aplikasi' table.
            // Let's use direct query to pengaturan_aplikasi as seen in cron logic references
            $this->db->query("SELECT * FROM pengaturan_aplikasi LIMIT 1");
            $settings = $this->db->single();

            $rotationEnabled = ($settings['wa_rotation_enabled'] ?? 0) == 1;
            $rotationMode = $settings['wa_rotation_mode'] ?? 'round_robin';
            $lastAccountId = $settings['wa_last_account_id'] ?? null;

            // Whitelist jenis pesan yang rotasi
            // EXCLUDED: notif_absensi_grup, notif_pembayaran (Gunakan WA Utama)
            $applyRotation = in_array($jenis, [
                'notif_absensi',
                // 'notif_absensi_grup', // Excluded by user request
                'test_grup',
                'pesan_bulk',
                'pesan_admin'
            ]);

            $isSuccess = false;
            $errorMessage = 'Unknown error';
            $usedAccountId = null;
            $response = [];

            $useDefaultGateway = true;

            if ($applyRotation && $rotationEnabled) {
                // ROTATION LOGIC
                $maxRetries = 3;
                $triedAccounts = [];
                $accountCheck = $waAccountModel->pickAccount($rotationMode, $lastAccountId);

                if ($accountCheck) {
                    $useDefaultGateway = false;

                    for ($retry = 0; $retry < $maxRetries && !$isSuccess; $retry++) {
                        $account = ($retry == 0) ? $accountCheck : $waAccountModel->pickAccount($rotationMode, $lastAccountId);

                        if ($account) {
                            if (in_array($account['id'], $triedAccounts)) {
                                if (count($triedAccounts) >= $waAccountModel->countActiveAccounts()) {
                                    break;
                                }
                            }
                            $triedAccounts[] = $account['id'];

                            // Configure Fonnte
                            $fonnteMulti = new Fonnte();
                            $fonnteMulti->configureFromAccount($account);

                            // Send
                            $result = $fonnteMulti->send($noWa, $pesan);

                            if (isset($result['status']) && $result['status'] === true) {
                                $isSuccess = true;
                                $response = $result;
                                $usedAccountId = $account['id'];

                                // Update last account ID
                                if ($usedAccountId) {
                                    $this->db->query("UPDATE pengaturan_aplikasi SET wa_last_account_id = :id");
                                    $this->db->bind(':id', $usedAccountId);
                                    $this->db->execute();
                                }
                            } else {
                                $errorMessage = $result['reason'] ?? $result['message'] ?? 'Unknown error';
                            }
                        } else {
                            break;
                        }
                    }
                }
            }

            // DEFAULT LOGIC (No Rotation or Fallback)
            if ($useDefaultGateway && !$isSuccess) {
                $fonnteDefault = new Fonnte();
                $result = $fonnteDefault->send($noWa, $pesan);
                $isSuccess = isset($result['status']) && $result['status'] === true;
                if ($isSuccess) {
                    $response = $result;
                } else {
                    $errorMessage = $result['reason'] ?? 'Unknown error';
                }
            }

            // Log ke database
            $status = $isSuccess ? 'sent' : 'failed';
            $now = date('Y-m-d H:i:s');

            $sql = 'INSERT INTO wa_message_queue (no_wa, pesan, jenis, metadata, status, sent_at, created_at, attempts, error_message, wa_account_id) 
                    VALUES (:no_wa, :pesan, :jenis, :metadata, :status, :sent_at, :created_at, 1, :error_message, :wa_account_id)';

            $this->db->query($sql);
            $this->db->bind(':no_wa', $noWa);
            $this->db->bind(':pesan', $pesan);
            $this->db->bind(':jenis', $jenis);
            $this->db->bind(':metadata', $metadata ? json_encode($metadata) : null);
            $this->db->bind(':status', $status);
            $this->db->bind(':sent_at', $now);
            $this->db->bind(':created_at', $now);
            $this->db->bind(':error_message', $isSuccess ? null : $errorMessage);
            $this->db->bind(':wa_account_id', $usedAccountId);

            $this->db->execute();
            $insertedId = $this->db->lastInsertId();

            if ($isSuccess) {
                // Log ke wa_message_log
                $this->logMessage($insertedId, 'sent', json_encode($response));
                error_log("WA Direct Send SUCCESS to {$noWa} (Acc: {$usedAccountId})");
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
