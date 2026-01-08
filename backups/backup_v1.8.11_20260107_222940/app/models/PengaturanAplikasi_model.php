<?php
// File: app/models/PengaturanAplikasi_model.php

class PengaturanAplikasi_model
{
    private $db;
    private $table = 'pengaturan_aplikasi';

    public function __construct()
    {
        $this->db = new Database;
        $this->ensureTableExists();
        $this->ensureColumnExists();
    }

    /**
     * Pastikan tabel ada, jika tidak buat tabel
     */
    private function ensureTableExists()
    {
        try {
            $this->db->query("CREATE TABLE IF NOT EXISTS `{$this->table}` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `nama_aplikasi` varchar(255) NOT NULL DEFAULT 'Smart Absensi',
                `url_web` varchar(255) DEFAULT 'http://localhost/absen',
                `logo` varchar(255) DEFAULT NULL,
                `wa_gateway_provider` varchar(50) DEFAULT 'fonnte',
                `wa_gateway_url` varchar(255) DEFAULT 'https://api.fonnte.com/send',
                `wa_gateway_token` varchar(255) DEFAULT '',
                `wa_gateway_username` varchar(100) DEFAULT '',
                `wa_gateway_password` varchar(100) DEFAULT '',
                `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            $this->db->execute();
        } catch (Exception $e) {
            // Ignore jika sudah ada
        }
    }

    /**
     * Pastikan kolom url_web ada (Migrasi otomatis)
     */
    private function ensureColumnExists()
    {
        try {
            // Add url_web column if missing
            $this->db->query("SHOW COLUMNS FROM `{$this->table}` LIKE 'url_web'");
            $result = $this->db->single();
            if (!$result) {
                $this->db->query("ALTER TABLE `{$this->table}` ADD COLUMN `url_web` varchar(255) DEFAULT 'http://localhost/absen' AFTER `nama_aplikasi`");
                $this->db->execute();
            }

            // Add wa_gateway_provider column if missing
            $this->db->query("SHOW COLUMNS FROM `{$this->table}` LIKE 'wa_gateway_provider'");
            if (!$this->db->single()) {
                $this->db->query("ALTER TABLE `{$this->table}` ADD COLUMN `wa_gateway_provider` varchar(50) DEFAULT 'fonnte' AFTER `logo`");
                $this->db->execute();
            }

            // Add wa_gateway_username column if missing
            $this->db->query("SHOW COLUMNS FROM `{$this->table}` LIKE 'wa_gateway_username'");
            if (!$this->db->single()) {
                $this->db->query("ALTER TABLE `{$this->table}` ADD COLUMN `wa_gateway_username` varchar(100) DEFAULT '' AFTER `wa_gateway_token`");
                $this->db->execute();
            }

            // Add wa_gateway_password column if missing
            $this->db->query("SHOW COLUMNS FROM `{$this->table}` LIKE 'wa_gateway_password'");
            if (!$this->db->single()) {
                $this->db->query("ALTER TABLE `{$this->table}` ADD COLUMN `wa_gateway_password` varchar(100) DEFAULT '' AFTER `wa_gateway_username`");
                $this->db->execute();
            }
        } catch (Exception $e) {
            // Ignore error
        }
    }

    /**
     * Get semua pengaturan aplikasi
     */
    public function getPengaturan()
    {
        try {
            $this->db->query("SELECT * FROM {$this->table} WHERE id = 1");
            $result = $this->db->single();

            // Jika tidak ada data, insert default dan kembalikan
            if (!$result) {
                $this->insertDefault();
                return [
                    'id' => 1,
                    'nama_aplikasi' => 'Smart Absensi',
                    'url_web' => 'http://localhost/absen',
                    'logo' => '',
                    'wa_gateway_provider' => 'fonnte',
                    'wa_gateway_url' => 'https://api.fonnte.com/send',
                    'wa_gateway_token' => '',
                    'wa_gateway_username' => '',
                    'wa_gateway_password' => '',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
            }

            return $result;
        } catch (Exception $e) {
            // Return default jika error
            return [
                'id' => 1,
                'nama_aplikasi' => 'Smart Absensi',
                'url_web' => 'http://localhost/absen',
                'logo' => '',
                'wa_gateway_provider' => 'fonnte',
                'wa_gateway_url' => 'https://api.fonnte.com/send',
                'wa_gateway_token' => '',
                'wa_gateway_username' => '',
                'wa_gateway_password' => '',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
        }
    }

    /**
     * Insert data default
     */
    private function insertDefault()
    {
        try {
            $this->db->query("INSERT INTO {$this->table} (id, nama_aplikasi, url_web, logo) 
                              VALUES (1, 'Smart Absensi', 'http://localhost/absen', '')
                              ON DUPLICATE KEY UPDATE id = id");
            $this->db->execute();
        } catch (Exception $e) {
            // Ignore
        }
    }

    /**
     * Simpan pengaturan (insert atau update)
     */
    public function simpan($data)
    {
        // Cek apakah sudah ada data
        $existing = $this->getPengaturan();

        if ($existing && isset($existing['id']) && $this->cekDataExists()) {
            return $this->update($data);
        } else {
            return $this->insert($data);
        }
    }

    /**
     * Cek apakah data sudah ada di database
     */
    private function cekDataExists()
    {
        try {
            $this->db->query("SELECT COUNT(*) as total FROM {$this->table}");
            $result = $this->db->single();
            return $result['total'] > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    private function insert($data)
    {
        $this->db->query("INSERT INTO {$this->table} 
            (id, nama_aplikasi, url_web, logo, wa_gateway_provider, wa_gateway_url, wa_gateway_token, wa_gateway_username, wa_gateway_password, created_at, updated_at) 
            VALUES (1, :nama_aplikasi, :url_web, :logo, :wa_provider, :wa_url, :wa_token, :wa_username, :wa_password, NOW(), NOW())");

        $this->db->bind(':nama_aplikasi', $data['nama_aplikasi']);
        $this->db->bind(':url_web', $data['url_web'] ?? 'http://localhost/absen');
        $this->db->bind(':logo', $data['logo'] ?? '');
        $this->db->bind(':wa_provider', $data['wa_gateway_provider'] ?? 'fonnte');
        $this->db->bind(':wa_url', $data['wa_gateway_url'] ?? 'https://api.fonnte.com/send');
        $this->db->bind(':wa_token', $data['wa_gateway_token'] ?? '');
        $this->db->bind(':wa_username', $data['wa_gateway_username'] ?? '');
        $this->db->bind(':wa_password', $data['wa_gateway_password'] ?? '');

        return $this->db->execute();
    }

    private function update($data)
    {
        $this->db->query("UPDATE {$this->table} SET 
            nama_aplikasi = :nama_aplikasi,
            url_web = :url_web,
            logo = :logo,
            wa_gateway_provider = :wa_provider,
            wa_gateway_url = :wa_url,
            wa_gateway_token = :wa_token,
            wa_gateway_username = :wa_username,
            wa_gateway_password = :wa_password,
            updated_at = NOW()
            WHERE id = 1");

        $this->db->bind(':nama_aplikasi', $data['nama_aplikasi']);
        $this->db->bind(':url_web', $data['url_web'] ?? 'http://localhost/absen');
        $this->db->bind(':logo', $data['logo'] ?? '');
        $this->db->bind(':wa_provider', $data['wa_gateway_provider'] ?? 'fonnte');
        $this->db->bind(':wa_url', $data['wa_gateway_url'] ?? 'https://api.fonnte.com/send');
        $this->db->bind(':wa_token', $data['wa_gateway_token'] ?? '');
        $this->db->bind(':wa_username', $data['wa_gateway_username'] ?? '');
        $this->db->bind(':wa_password', $data['wa_gateway_password'] ?? '');

        return $this->db->execute();
    }
}