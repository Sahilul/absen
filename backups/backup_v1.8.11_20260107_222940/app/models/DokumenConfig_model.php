<?php

/**
 * DokumenConfig_model.php
 * Model untuk konfigurasi jenis dokumen yang dapat diupload
 * Digunakan oleh PSB, Siswa, WaliKelas, dan Admin
 */

class DokumenConfig_model
{
    private $db;
    private $table = 'pengaturan_dokumen';

    public function __construct()
    {
        $this->db = new Database();
        $this->ensureTable();
    }

    /**
     * Pastikan tabel dan data default ada
     */
    private function ensureTable()
    {
        try {
            // Cek apakah tabel sudah ada
            $this->db->query("SHOW TABLES LIKE '{$this->table}'");
            $exists = $this->db->single();

            if (!$exists) {
                // Buat tabel
                $this->db->query("
                    CREATE TABLE {$this->table} (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        kode VARCHAR(50) UNIQUE NOT NULL,
                        nama VARCHAR(100) NOT NULL,
                        icon VARCHAR(50) DEFAULT 'file-text',
                        wajib_psb TINYINT(1) DEFAULT 0,
                        wajib_siswa TINYINT(1) DEFAULT 0,
                        aktif TINYINT(1) DEFAULT 1,
                        urutan INT DEFAULT 0,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                ");
                $this->db->execute();

                // Insert data default
                $this->seedDefaults();
            }
        } catch (Exception $e) {
            error_log("DokumenConfig_model ensureTable error: " . $e->getMessage());
        }
    }

    /**
     * Seed data dokumen default
     */
    private function seedDefaults()
    {
        $defaults = [
            ['kode' => 'kartu_keluarga', 'nama' => 'Kartu Keluarga (KK)', 'icon' => 'file-text', 'wajib_psb' => 1, 'wajib_siswa' => 1, 'urutan' => 1],
            ['kode' => 'akta_kelahiran', 'nama' => 'Akta Kelahiran', 'icon' => 'file-text', 'wajib_psb' => 1, 'wajib_siswa' => 1, 'urutan' => 2],
            ['kode' => 'ktp_ayah', 'nama' => 'KTP Ayah', 'icon' => 'id-card', 'wajib_psb' => 1, 'wajib_siswa' => 0, 'urutan' => 3],
            ['kode' => 'ktp_ibu', 'nama' => 'KTP Ibu', 'icon' => 'id-card', 'wajib_psb' => 1, 'wajib_siswa' => 0, 'urutan' => 4],
            ['kode' => 'ijazah', 'nama' => 'Ijazah/SKL', 'icon' => 'award', 'wajib_psb' => 1, 'wajib_siswa' => 1, 'urutan' => 5],
            ['kode' => 'foto', 'nama' => 'Pas Foto', 'icon' => 'image', 'wajib_psb' => 0, 'wajib_siswa' => 1, 'urutan' => 6],
            ['kode' => 'kip', 'nama' => 'Kartu Indonesia Pintar (KIP)', 'icon' => 'credit-card', 'wajib_psb' => 0, 'wajib_siswa' => 0, 'urutan' => 7],
            ['kode' => 'kis_kks', 'nama' => 'KIS/KKS', 'icon' => 'heart', 'wajib_psb' => 0, 'wajib_siswa' => 0, 'urutan' => 8],
            ['kode' => 'pkh', 'nama' => 'PKH', 'icon' => 'wallet', 'wajib_psb' => 0, 'wajib_siswa' => 0, 'urutan' => 9],
            ['kode' => 'skhun', 'nama' => 'SKHUN', 'icon' => 'file-text', 'wajib_psb' => 0, 'wajib_siswa' => 0, 'urutan' => 10],
            ['kode' => 'rapor', 'nama' => 'Rapor', 'icon' => 'book-open', 'wajib_psb' => 0, 'wajib_siswa' => 0, 'urutan' => 11],
            ['kode' => 'surat_pindah', 'nama' => 'Surat Pindah', 'icon' => 'mail', 'wajib_psb' => 0, 'wajib_siswa' => 0, 'urutan' => 12],
            ['kode' => 'sktm', 'nama' => 'SKTM', 'icon' => 'file-check', 'wajib_psb' => 0, 'wajib_siswa' => 0, 'urutan' => 13],
            ['kode' => 'lainnya', 'nama' => 'Dokumen Lainnya', 'icon' => 'paperclip', 'wajib_psb' => 0, 'wajib_siswa' => 0, 'urutan' => 99],
        ];

        foreach ($defaults as $doc) {
            $this->db->query("
                INSERT INTO {$this->table} (kode, nama, icon, wajib_psb, wajib_siswa, urutan, aktif) 
                VALUES (:kode, :nama, :icon, :wajib_psb, :wajib_siswa, :urutan, 1)
            ");
            $this->db->bind(':kode', $doc['kode']);
            $this->db->bind(':nama', $doc['nama']);
            $this->db->bind(':icon', $doc['icon']);
            $this->db->bind(':wajib_psb', $doc['wajib_psb']);
            $this->db->bind(':wajib_siswa', $doc['wajib_siswa']);
            $this->db->bind(':urutan', $doc['urutan']);
            $this->db->execute();
        }
    }

    /**
     * Get semua dokumen aktif
     */
    public function getAllDokumen()
    {
        $this->db->query("SELECT * FROM {$this->table} WHERE aktif = 1 ORDER BY urutan ASC, nama ASC");
        return $this->db->resultSet();
    }

    /**
     * Get semua dokumen (termasuk non-aktif) untuk admin
     */
    public function getAllDokumenAdmin()
    {
        $this->db->query("SELECT * FROM {$this->table} ORDER BY urutan ASC, nama ASC");
        return $this->db->resultSet();
    }

    /**
     * Get dokumen untuk PSB
     */
    public function getDokumenPSB()
    {
        $this->db->query("SELECT * FROM {$this->table} WHERE aktif = 1 ORDER BY urutan ASC");
        return $this->db->resultSet();
    }

    /**
     * Get dokumen wajib untuk PSB
     */
    public function getDokumenWajibPSB()
    {
        $this->db->query("SELECT * FROM {$this->table} WHERE aktif = 1 AND wajib_psb = 1 ORDER BY urutan ASC");
        return $this->db->resultSet();
    }

    /**
     * Get dokumen untuk Siswa
     */
    public function getDokumenSiswa()
    {
        $this->db->query("SELECT * FROM {$this->table} WHERE aktif = 1 ORDER BY urutan ASC");
        return $this->db->resultSet();
    }

    /**
     * Get dokumen wajib untuk Siswa
     */
    public function getDokumenWajibSiswa()
    {
        $this->db->query("SELECT * FROM {$this->table} WHERE aktif = 1 AND wajib_siswa = 1 ORDER BY urutan ASC");
        return $this->db->resultSet();
    }

    /**
     * Get dokumen by kode
     */
    public function getByKode($kode)
    {
        $this->db->query("SELECT * FROM {$this->table} WHERE kode = :kode");
        $this->db->bind(':kode', $kode);
        return $this->db->single();
    }

    /**
     * Get dokumen by ID
     */
    public function getById($id)
    {
        $this->db->query("SELECT * FROM {$this->table} WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    /**
     * Simpan (insert atau update)
     */
    public function simpan($data)
    {
        try {
            if (!empty($data['id'])) {
                // Update
                $this->db->query("
                    UPDATE {$this->table} SET 
                        kode = :kode,
                        nama = :nama,
                        icon = :icon,
                        wajib_psb = :wajib_psb,
                        wajib_siswa = :wajib_siswa,
                        urutan = :urutan
                    WHERE id = :id
                ");
                $this->db->bind(':id', $data['id']);
            } else {
                // Insert
                $this->db->query("
                    INSERT INTO {$this->table} (kode, nama, icon, wajib_psb, wajib_siswa, urutan, aktif) 
                    VALUES (:kode, :nama, :icon, :wajib_psb, :wajib_siswa, :urutan, 1)
                ");
            }

            $this->db->bind(':kode', $data['kode']);
            $this->db->bind(':nama', $data['nama']);
            $this->db->bind(':icon', $data['icon'] ?? 'file-text');
            $this->db->bind(':wajib_psb', $data['wajib_psb'] ?? 0);
            $this->db->bind(':wajib_siswa', $data['wajib_siswa'] ?? 0);
            $this->db->bind(':urutan', $data['urutan'] ?? 0);

            return $this->db->execute();
        } catch (Exception $e) {
            error_log("DokumenConfig simpan error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Toggle status aktif
     */
    public function toggleAktif($id)
    {
        $this->db->query("UPDATE {$this->table} SET aktif = NOT aktif WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    /**
     * Toggle wajib PSB
     */
    public function toggleWajibPSB($id)
    {
        $this->db->query("UPDATE {$this->table} SET wajib_psb = NOT wajib_psb WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    /**
     * Toggle wajib Siswa
     */
    public function toggleWajibSiswa($id)
    {
        $this->db->query("UPDATE {$this->table} SET wajib_siswa = NOT wajib_siswa WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    /**
     * Hapus dokumen by ID
     */
    public function hapus($id)
    {
        $this->db->query("DELETE FROM {$this->table} WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    /**
     * Get dokumen sebagai array key-value untuk dropdown/display
     */
    public function getAsArray()
    {
        $docs = $this->getAllDokumen();
        $result = [];
        foreach ($docs as $doc) {
            $result[$doc['kode']] = $doc['nama'];
        }
        return $result;
    }

    /**
     * Get dokumen lengkap sebagai array dengan kode sebagai key
     */
    public function getAsArrayFull()
    {
        $docs = $this->getAllDokumen();
        $result = [];
        foreach ($docs as $doc) {
            $result[$doc['kode']] = $doc;
        }
        return $result;
    }
}
