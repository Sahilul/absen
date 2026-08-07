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

            // Add wa_rotation_enabled column if missing
            $this->db->query("SHOW COLUMNS FROM `{$this->table}` LIKE 'wa_rotation_enabled'");
            if (!$this->db->single()) {
                $this->db->query("ALTER TABLE `{$this->table}` ADD COLUMN `wa_rotation_enabled` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1=Gunakan rotasi multi akun, 0=Gunakan akun default' AFTER `wa_gateway_password`");
                $this->db->execute();
            }

            // Add wa_rotation_mode column if missing
            $this->db->query("SHOW COLUMNS FROM `{$this->table}` LIKE 'wa_rotation_mode'");
            if (!$this->db->single()) {
                $this->db->query("ALTER TABLE `{$this->table}` ADD COLUMN `wa_rotation_mode` ENUM('round_robin', 'random', 'load_balance') NOT NULL DEFAULT 'round_robin' AFTER `wa_rotation_enabled`");
                $this->db->execute();
            }

            // Add wa_last_account_id column if missing
            $this->db->query("SHOW COLUMNS FROM `{$this->table}` LIKE 'wa_last_account_id'");
            if (!$this->db->single()) {
                $this->db->query("ALTER TABLE `{$this->table}` ADD COLUMN `wa_last_account_id` INT(11) UNSIGNED DEFAULT NULL AFTER `wa_rotation_mode`");
                $this->db->execute();
            }

            // Add admin_wa_number column if missing
            $this->db->query("SHOW COLUMNS FROM `{$this->table}` LIKE 'admin_wa_number'");
            if (!$this->db->single()) {
                $this->db->query("ALTER TABLE `{$this->table}` ADD COLUMN `admin_wa_number` VARCHAR(20) DEFAULT NULL AFTER `wa_last_account_id`");
                $this->db->execute();
            }

            // Add wa_template_group_absensi column if missing
            $this->db->query("SHOW COLUMNS FROM `{$this->table}` LIKE 'wa_template_group_absensi'");
            if (!$this->db->single()) {
                $this->db->query("ALTER TABLE `{$this->table}` ADD COLUMN `wa_template_group_absensi` TEXT NULL COMMENT 'Template pesan WA ke grup absensi' AFTER `admin_wa_number`");
                $this->db->execute();
            }
        } catch (Exception $e) {
            // Ignore error
        }
    }

    /**
     * Update satu setting spesifik
     * @param string $key Nama kolom
     * @param mixed $value Nilai baru
     * @return bool
     */
    public function updateSetting($key, $value)
    {
        // Whitelist kolom yang boleh diupdate via method ini
        $allowed_columns = [
            'nama_aplikasi',
            'url_web',
            'logo',
            'wa_gateway_provider',
            'wa_gateway_url',
            'wa_gateway_token',
            'wa_gateway_username',
            'wa_gateway_password',
            'wa_rotation_enabled',
            'wa_rotation_mode',
            'wa_last_account_id',
            'admin_wa_number',
            'wa_template_group_absensi'
        ];

        if (!in_array($key, $allowed_columns)) {
            return false;
        }

        try {
            // Pastikan kolom ada sebelum update (lazy migration handling)
            $this->ensureColumnExists();

            $this->db->query("UPDATE {$this->table} SET {$key} = :value, updated_at = NOW() WHERE id = 1");
            $this->db->bind(':value', $value);
            return $this->db->execute();
        } catch (Exception $e) {
            return false;
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
                    'wa_template_group_absensi' => null,
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
                'wa_template_group_absensi' => null,
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
            (id, nama_aplikasi, url_web, logo, wa_gateway_provider, wa_gateway_url, wa_gateway_token, wa_gateway_username, wa_gateway_password, wa_template_group_absensi, created_at, updated_at) 
            VALUES (1, :nama_aplikasi, :url_web, :logo, :wa_provider, :wa_url, :wa_token, :wa_username, :wa_password, :wa_template, NOW(), NOW())");

        $this->db->bind(':nama_aplikasi', $data['nama_aplikasi']);
        $this->db->bind(':url_web', $data['url_web'] ?? 'http://localhost/absen');
        $this->db->bind(':logo', $data['logo'] ?? '');
        $this->db->bind(':wa_provider', $data['wa_gateway_provider'] ?? 'fonnte');
        $this->db->bind(':wa_url', $data['wa_gateway_url'] ?? 'https://api.fonnte.com/send');
        $this->db->bind(':wa_token', $data['wa_gateway_token'] ?? '');
        $this->db->bind(':wa_username', $data['wa_gateway_username'] ?? '');
        $this->db->bind(':wa_password', $data['wa_gateway_password'] ?? '');
        $this->db->bind(':wa_template', $data['wa_template_group_absensi'] ?? null);

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
            wa_template_group_absensi = :wa_template,
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
        $this->db->bind(':wa_template', $data['wa_template_group_absensi'] ?? null);

        return $this->db->execute();
    }

    /**
     * Get konfigurasi field siswa
     * @return array
     */
    public function getFieldSiswaConfig()
    {
        // Ensure column exists
        $this->ensureFieldSiswaConfigColumn();

        try {
            $this->db->query("SELECT field_siswa_config FROM {$this->table} WHERE id = 1");
            $result = $this->db->single();

            if ($result && !empty($result['field_siswa_config'])) {
                $config = json_decode($result['field_siswa_config'], true);
                if (is_array($config)) {
                    return array_merge($this->getDefaultFieldConfig(), $config);
                }
            }
        } catch (Exception $e) {
            // Ignore
        }

        return $this->getDefaultFieldConfig();
    }

    /**
     * Save konfigurasi field siswa
     * @param array $config
     * @return bool
     */
    public function saveFieldSiswaConfig($config)
    {
        $this->ensureFieldSiswaConfigColumn();

        try {
            $jsonConfig = json_encode($config);
            $this->db->query("UPDATE {$this->table} SET field_siswa_config = :config WHERE id = 1");
            $this->db->bind(':config', $jsonConfig);
            return $this->db->execute();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Ensure field_siswa_config column exists
     */
    private function ensureFieldSiswaConfigColumn()
    {
        try {
            $this->db->query("SHOW COLUMNS FROM `{$this->table}` LIKE 'field_siswa_config'");
            if (!$this->db->single()) {
                $this->db->query("ALTER TABLE `{$this->table}` ADD COLUMN `field_siswa_config` TEXT DEFAULT NULL");
                $this->db->execute();
            }
        } catch (Exception $e) {
            // Ignore
        }
    }

    /**
     * Get default field configuration (all enabled)
     * @return array
     */
    public function getDefaultFieldConfig()
    {
        return [
            // Data Identitas - WAJIB (always true)
            'nisn' => true,
            'nama' => true,
            'jenis_kelamin' => true,
            'tempat_lahir' => true,
            'tanggal_lahir' => true,
            // Data Identitas - OPSIONAL
            'nik' => true,
            'password' => true,
            'agama' => true,
            'anak_ke' => true,
            'jumlah_saudara' => true,
            'hobi' => true,
            'cita_cita' => true,
            'no_wa' => true,
            'email' => true,
            'no_kip' => false,
            'yang_membiayai' => false,
            'kebutuhan_khusus' => false,
            // Alamat
            'alamat' => true,
            'rt' => false,
            'rw' => false,
            'dusun' => false,
            'kode_pos' => false,
            'provinsi' => false,
            'kabupaten' => false,
            'kecamatan' => false,
            'kelurahan' => false,
            'status_tempat_tinggal' => false,
            'jarak_sekolah' => false,
            'transportasi' => false,
            // Data Ayah - WAJIB
            'ayah_no_hp' => true,
            // Data Ayah - OPSIONAL
            'ayah_nama' => true,
            'ayah_nik' => false,
            'ayah_tempat_lahir' => false,
            'ayah_tanggal_lahir' => false,
            'ayah_status' => false,
            'ayah_pendidikan' => false,
            'ayah_pekerjaan' => false,
            'ayah_penghasilan' => false,
            // Data Ibu - WAJIB
            'ibu_no_hp' => true,
            // Data Ibu - OPSIONAL
            'ibu_nama' => true,
            'ibu_nik' => false,
            'ibu_tempat_lahir' => false,
            'ibu_tanggal_lahir' => false,
            'ibu_status' => false,
            'ibu_pendidikan' => false,
            'ibu_pekerjaan' => false,
            'ibu_penghasilan' => false,
            // Data Wali
            'wali_nama' => false,
            'wali_hubungan' => false,
            'wali_nik' => false,
            'wali_no_hp' => false,
            'wali_pendidikan' => false,
            'wali_pekerjaan' => false,
            'wali_penghasilan' => false,
        ];
    }

    /**
     * Get list of mandatory fields (cannot be disabled)
     * @return array
     */
    public function getMandatoryFields()
    {
        return [
            'nisn',
            'nama',
            'jenis_kelamin',
            'tempat_lahir',
            'tanggal_lahir',
            'ayah_no_hp',
            'ibu_no_hp'
        ];
    }
}