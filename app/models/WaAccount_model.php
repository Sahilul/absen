<?php

/**
 * Model untuk mengelola akun WA Gateway
 * File: app/models/WaAccount_model.php
 */
class WaAccount_model
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    /**
     * Ambil semua akun
     */
    public function getAll()
    {
        $this->db->query('SELECT * FROM wa_accounts ORDER BY nama ASC');
        return $this->db->resultSet();
    }

    /**
     * Ambil semua akun aktif
     */
    public function getActive()
    {
        $this->db->query('SELECT * FROM wa_accounts WHERE is_active = 1 ORDER BY id ASC');
        return $this->db->resultSet();
    }

    /**
     * Ambil akun by ID
     */
    public function getById($id)
    {
        $this->db->query('SELECT * FROM wa_accounts WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    /**
     * Tambah akun baru
     */
    public function create($data)
    {
        $this->db->query('INSERT INTO wa_accounts (nama, provider, api_url, token, username, password, is_active, daily_limit) 
                          VALUES (:nama, :provider, :api_url, :token, :username, :password, :is_active, :daily_limit)');
        $this->db->bind(':nama', $data['nama']);
        $this->db->bind(':provider', $data['provider']);
        $this->db->bind(':api_url', $data['api_url'] ?? null);
        $this->db->bind(':token', $data['token'] ?? null);
        $this->db->bind(':username', $data['username'] ?? null);
        $this->db->bind(':password', $data['password'] ?? null);
        $this->db->bind(':is_active', $data['is_active'] ?? 1);
        $this->db->bind(':daily_limit', $data['daily_limit'] ?? 100);
        $this->db->execute();
        return $this->db->rowCount();
    }

    /**
     * Update akun
     */
    public function update($id, $data)
    {
        $this->db->query('UPDATE wa_accounts SET 
                          nama = :nama, 
                          provider = :provider, 
                          api_url = :api_url, 
                          token = :token, 
                          username = :username, 
                          password = :password, 
                          is_active = :is_active, 
                          daily_limit = :daily_limit,
                          updated_at = NOW()
                          WHERE id = :id');
        $this->db->bind(':id', $id);
        $this->db->bind(':nama', $data['nama']);
        $this->db->bind(':provider', $data['provider']);
        $this->db->bind(':api_url', $data['api_url'] ?? null);
        $this->db->bind(':token', $data['token'] ?? null);
        $this->db->bind(':username', $data['username'] ?? null);
        $this->db->bind(':password', $data['password'] ?? null);
        $this->db->bind(':is_active', $data['is_active'] ?? 1);
        $this->db->bind(':daily_limit', $data['daily_limit'] ?? 100);
        $this->db->execute();
        return $this->db->rowCount();
    }

    /**
     * Hapus akun
     */
    public function delete($id)
    {
        $this->db->query('DELETE FROM wa_accounts WHERE id = :id');
        $this->db->bind(':id', $id);
        $this->db->execute();
        return $this->db->rowCount();
    }

    /**
     * Toggle status aktif
     */
    public function toggleActive($id)
    {
        $this->db->query('UPDATE wa_accounts SET is_active = NOT is_active WHERE id = :id');
        $this->db->bind(':id', $id);
        $this->db->execute();
        return $this->db->rowCount();
    }

    /**
     * Pilih akun untuk pengiriman berdasarkan mode rotasi
     * @param string $mode round_robin|random|load_balance
     * @param int|null $lastAccountId ID akun terakhir (untuk round_robin)
     * @return array|false Akun terpilih atau false jika tidak ada
     */
    public function pickAccount($mode = 'round_robin', $lastAccountId = null)
    {
        // Reset counter harian jika perlu
        $this->resetDailyCounterIfNeeded();

        // Ambil akun aktif yang belum mencapai limit
        $this->db->query('SELECT * FROM wa_accounts 
                          WHERE is_active = 1 
                          AND (daily_limit = 0 OR today_sent < daily_limit)
                          ORDER BY id ASC');
        $accounts = $this->db->resultSet();

        if (empty($accounts)) {
            return false;
        }

        switch ($mode) {
            case 'random':
                return $accounts[array_rand($accounts)];

            case 'load_balance':
                // Pilih akun dengan today_sent paling sedikit
                usort($accounts, fn($a, $b) => $a['today_sent'] <=> $b['today_sent']);
                return $accounts[0];

            case 'round_robin':
            default:
                // Cari akun setelah lastAccountId
                if ($lastAccountId) {
                    $found = false;
                    foreach ($accounts as $account) {
                        if ($found) {
                            return $account;
                        }
                        if ($account['id'] == $lastAccountId) {
                            $found = true;
                        }
                    }
                }
                // Jika tidak ditemukan atau pertama kali, gunakan akun pertama
                return $accounts[0];
        }
    }

    /**
     * Increment counter pengiriman
     */
    public function incrementSent($id)
    {
        $this->db->query('UPDATE wa_accounts SET 
                          today_sent = today_sent + 1, 
                          last_used_at = NOW() 
                          WHERE id = :id');
        $this->db->bind(':id', $id);
        $this->db->execute();
        return $this->db->rowCount();
    }

    /**
     * Reset counter harian jika tanggal berbeda
     */
    public function resetDailyCounterIfNeeded()
    {
        $this->db->query('UPDATE wa_accounts SET 
                          today_sent = 0, 
                          last_reset_date = CURDATE() 
                          WHERE last_reset_date IS NULL OR last_reset_date < CURDATE()');
        $this->db->execute();
    }

    /**
     * Reset semua counter (manual)
     */
    public function resetAllCounters()
    {
        $this->db->query('UPDATE wa_accounts SET today_sent = 0, last_reset_date = CURDATE()');
        $this->db->execute();
        return $this->db->rowCount();
    }

    /**
     * Get statistik penggunaan
     */
    public function getStats()
    {
        $this->db->query('SELECT 
                          COUNT(*) as total,
                          SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active,
                          SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) as inactive,
                          SUM(today_sent) as total_sent_today,
                          SUM(daily_limit) as total_limit
                          FROM wa_accounts');
        return $this->db->single();
    }

    /**
     * Tandai akun sebagai terblokir/bermasalah
     * @param int $id ID akun
     * @param string $reason Alasan blokir
     */
    public function markAsBlocked($id, $reason = 'Unknown error')
    {
        // Nonaktifkan akun dan log error
        $this->db->query('UPDATE wa_accounts SET 
                          is_active = 0,
                          updated_at = NOW()
                          WHERE id = :id');
        $this->db->bind(':id', $id);
        $this->db->execute();

        // Log ke error_log
        error_log("[WA BLOCKED] Account ID {$id} marked as blocked. Reason: {$reason}");

        return $this->db->rowCount();
    }

    /**
     * Cek apakah error message menandakan akun terblokir
     * @param string $errorMessage Pesan error dari API
     * @return bool True jika terindikasi blokir
     */
    public static function checkIfErrorIsBlock($errorMessage)
    {
        if (empty($errorMessage)) {
            return false;
        }

        $errorMessage = strtolower($errorMessage);

        // Daftar kata kunci yang mengindikasikan akun bermasalah
        $blockIndicators = [
            'banned',
            'blocked',
            'disconnect',
            'not connected',
            'device not found',
            'device offline',
            'expired',
            'invalid token',
            'unauthorized',
            'forbidden',
            'account suspended',
            'account disabled',
            'number not registered',
            'not logged in',
            'session expired',
            'qr code',
            'tidak terhubung',
            'terblokir',
            'diblokir',
            'tidak dikonfigurasi',
            'token kosong',
            'empty token'
        ];

        foreach ($blockIndicators as $indicator) {
            if (strpos($errorMessage, $indicator) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Ambil daftar akun yang tidak aktif (possibly blocked)
     */
    public function getInactiveAccounts()
    {
        $this->db->query('SELECT * FROM wa_accounts WHERE is_active = 0 ORDER BY updated_at DESC');
        return $this->db->resultSet();
    }

    /**
     * Reaktivasi akun (setelah diperbaiki)
     */
    public function reactivate($id)
    {
        $this->db->query('UPDATE wa_accounts SET is_active = 1, updated_at = NOW() WHERE id = :id');
        $this->db->bind(':id', $id);
        $this->db->execute();
        return $this->db->rowCount();
    }
}
