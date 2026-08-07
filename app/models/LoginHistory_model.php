<?php
// File: app/models/LoginHistory_model.php

class LoginHistory_model
{
    private $db;
    private $table = 'login_history';

    public function __construct()
    {
        $this->db = new Database;
    }

    /**
     * Catat login (sukses atau gagal)
     */
    public function log($userId, $username, $namaLengkap, $role, $status = 'success', $failureReason = null)
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN';

        $this->db->query("INSERT INTO {$this->table} 
            (user_id, username, nama_lengkap, role, ip_address, user_agent, status, failure_reason) 
            VALUES (:user_id, :username, :nama_lengkap, :role, :ip, :ua, :status, :reason)");

        $this->db->bind(':user_id', $userId);
        $this->db->bind(':username', $username);
        $this->db->bind(':nama_lengkap', $namaLengkap);
        $this->db->bind(':role', $role);
        $this->db->bind(':ip', $ip);
        $this->db->bind(':ua', $ua);
        $this->db->bind(':status', $status);
        $this->db->bind(':reason', $failureReason);

        return $this->db->execute();
    }

    /**
     * Ambil semua riwayat login dengan pagination, search, dan sorting
     */
    public function getAll($params = [])
    {
        $limit = (int) ($params['limit'] ?? 25);
        $offset = (int) ($params['offset'] ?? 0);
        $search = trim($params['search'] ?? '');
        $status = $params['status'] ?? 'all';
        $sortBy = $params['sort_by'] ?? 'login_at';
        $sortOrder = strtoupper($params['sort_order'] ?? 'DESC');

        // Whitelist kolom yang bisa disort
        $allowedSort = ['id', 'username', 'nama_lengkap', 'role', 'ip_address', 'status', 'login_at'];
        if (!in_array($sortBy, $allowedSort)) {
            $sortBy = 'login_at';
        }
        if (!in_array($sortOrder, ['ASC', 'DESC'])) {
            $sortOrder = 'DESC';
        }

        $where = [];
        $bindings = [];

        // Filter status
        if ($status === 'success' || $status === 'failed') {
            $where[] = "status = :status";
            $bindings[':status'] = $status;
        }

        // Search
        if (!empty($search)) {
            $where[] = "(username LIKE :search OR nama_lengkap LIKE :search2 OR ip_address LIKE :search3)";
            $bindings[':search'] = "%{$search}%";
            $bindings[':search2'] = "%{$search}%";
            $bindings[':search3'] = "%{$search}%";
        }

        $whereClause = '';
        if (!empty($where)) {
            $whereClause = 'WHERE ' . implode(' AND ', $where);
        }

        // Query
        $sql = "SELECT * FROM {$this->table} {$whereClause} ORDER BY {$sortBy} {$sortOrder} LIMIT :limit OFFSET :offset";
        $this->db->query($sql);

        foreach ($bindings as $key => $val) {
            $this->db->bind($key, $val);
        }
        $this->db->bind(':limit', $limit);
        $this->db->bind(':offset', $offset);

        return $this->db->resultSet();
    }

    /**
     * Hitung total record (untuk pagination)
     */
    public function countAll($params = [])
    {
        $search = trim($params['search'] ?? '');
        $status = $params['status'] ?? 'all';

        $where = [];
        $bindings = [];

        if ($status === 'success' || $status === 'failed') {
            $where[] = "status = :status";
            $bindings[':status'] = $status;
        }

        if (!empty($search)) {
            $where[] = "(username LIKE :search OR nama_lengkap LIKE :search2 OR ip_address LIKE :search3)";
            $bindings[':search'] = "%{$search}%";
            $bindings[':search2'] = "%{$search}%";
            $bindings[':search3'] = "%{$search}%";
        }

        $whereClause = '';
        if (!empty($where)) {
            $whereClause = 'WHERE ' . implode(' AND ', $where);
        }

        $this->db->query("SELECT COUNT(*) as total FROM {$this->table} {$whereClause}");

        foreach ($bindings as $key => $val) {
            $this->db->bind($key, $val);
        }

        return $this->db->single()['total'] ?? 0;
    }

    /**
     * Hapus data lebih dari 90 hari
     */
    public function deleteOldRecords($days = 90)
    {
        $this->db->query("DELETE FROM {$this->table} WHERE login_at < DATE_SUB(NOW(), INTERVAL :days DAY)");
        $this->db->bind(':days', $days);
        return $this->db->execute();
    }

    /**
     * Statistik ringkas
     */
    public function getStats()
    {
        $this->db->query("SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) as success,
            SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed,
            SUM(CASE WHEN DATE(login_at) = CURDATE() THEN 1 ELSE 0 END) as today
            FROM {$this->table}");
        return $this->db->single();
    }

    // =================================================================
    // v1.24.0 - RATE LIMITING LOGIN (anti brute-force)
    // =================================================================

    /**
     * Cek apakah kombinasi username/IP sedang terkunci akibat terlalu banyak
     * percobaan gagal.
     *
     * @param string $username username yang dicoba
     * @param string $ip       alamat IP peminta
     * @param int    $maxAttempts jumlah kegagalan maksimum sebelum dikunci
     * @param int    $lockMinutes durasi penguncian (menit)
     * @return array ['locked' => bool, 'remaining' => int detik tersisa, 'attempts' => int]
     */
    public function checkLockout($username, $ip, $maxAttempts = 5, $lockMinutes = 15)
    {
        try {
            $this->db->query("SELECT COUNT(*) AS cnt, MAX(login_at) AS last_at
                FROM {$this->table}
                WHERE (username = :username OR ip_address = :ip)
                  AND status = 'failed'
                  AND login_at > DATE_SUB(NOW(), INTERVAL :minutes MINUTE)");
            $this->db->bind(':username', (string) $username);
            $this->db->bind(':ip', (string) $ip);
            $this->db->bind(':minutes', (int) $lockMinutes);
            $row = $this->db->single();

            $count = (int) ($row['cnt'] ?? 0);
            if ($count < $maxAttempts || empty($row['last_at'])) {
                return ['locked' => false, 'remaining' => 0, 'attempts' => $count];
            }

            $lastTs = strtotime($row['last_at']);
            $unlockAt = $lastTs + ($lockMinutes * 60);
            $remaining = $unlockAt - time();

            if ($remaining > 0) {
                return ['locked' => true, 'remaining' => $remaining, 'attempts' => $count];
            }
            return ['locked' => false, 'remaining' => 0, 'attempts' => $count];
        } catch (Exception $e) {
            error_log('checkLockout error: ' . $e->getMessage());
            // Fail-open: jangan sampai error DB menghalangi login
            return ['locked' => false, 'remaining' => 0, 'attempts' => 0];
        }
    }
}
