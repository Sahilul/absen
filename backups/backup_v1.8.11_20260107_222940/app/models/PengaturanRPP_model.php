<?php
// File: app/models/PengaturanRPP_model.php

class PengaturanRPP_model {
    private $db;
    private $table = 'pengaturan_rpp';

    public function __construct() {
        $this->db = new Database;
        $this->ensureTableExists();
    }

    /**
     * Pastikan tabel ada, jika tidak buat tabel
     */
    private function ensureTableExists() {
        try {
            $this->db->query("CREATE TABLE IF NOT EXISTS `{$this->table}` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `wajib_rpp_disetujui` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1=Wajib RPP disetujui untuk akses fitur',
                `blokir_absensi` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1=Blokir akses absensi',
                `blokir_jurnal` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1=Blokir akses jurnal',
                `blokir_nilai` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1=Blokir akses input nilai',
                `pesan_blokir` text DEFAULT NULL COMMENT 'Pesan yang ditampilkan saat diblokir',
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
     * Get pengaturan RPP
     */
    public function getPengaturan() {
        try {
            $this->db->query("SELECT * FROM {$this->table} WHERE id = 1");
            $result = $this->db->single();
            
            if (!$result) {
                $this->insertDefault();
                return $this->getDefaultValues();
            }
            
            return $result;
        } catch (Exception $e) {
            return $this->getDefaultValues();
        }
    }

    /**
     * Get default values
     */
    private function getDefaultValues() {
        return [
            'id' => 1,
            'wajib_rpp_disetujui' => 0,
            'blokir_absensi' => 1,
            'blokir_jurnal' => 1,
            'blokir_nilai' => 1,
            'pesan_blokir' => 'Anda belum dapat mengakses fitur ini karena RPP belum dibuat atau belum disetujui oleh Kepala Madrasah. Silakan buat dan ajukan RPP terlebih dahulu.',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Insert data default
     */
    private function insertDefault() {
        try {
            $default = $this->getDefaultValues();
            $this->db->query("INSERT INTO {$this->table} 
                (id, wajib_rpp_disetujui, blokir_absensi, blokir_jurnal, blokir_nilai, pesan_blokir) 
                VALUES (1, :wajib, :absensi, :jurnal, :nilai, :pesan)
                ON DUPLICATE KEY UPDATE id = id");
            $this->db->bind(':wajib', $default['wajib_rpp_disetujui']);
            $this->db->bind(':absensi', $default['blokir_absensi']);
            $this->db->bind(':jurnal', $default['blokir_jurnal']);
            $this->db->bind(':nilai', $default['blokir_nilai']);
            $this->db->bind(':pesan', $default['pesan_blokir']);
            $this->db->execute();
        } catch (Exception $e) {
            // Ignore
        }
    }

    /**
     * Simpan pengaturan
     */
    public function simpan($data) {
        $existing = $this->getPengaturan();
        
        if ($existing && isset($existing['id'])) {
            return $this->update($data);
        } else {
            return $this->insert($data);
        }
    }

    /**
     * Insert pengaturan baru
     */
    private function insert($data) {
        $this->db->query("INSERT INTO {$this->table} 
            (id, wajib_rpp_disetujui, blokir_absensi, blokir_jurnal, blokir_nilai, pesan_blokir) 
            VALUES (1, :wajib, :absensi, :jurnal, :nilai, :pesan)");
        
        $this->db->bind(':wajib', $data['wajib_rpp_disetujui'] ?? 0);
        $this->db->bind(':absensi', $data['blokir_absensi'] ?? 1);
        $this->db->bind(':jurnal', $data['blokir_jurnal'] ?? 1);
        $this->db->bind(':nilai', $data['blokir_nilai'] ?? 1);
        $this->db->bind(':pesan', $data['pesan_blokir'] ?? '');
        
        return $this->db->execute();
    }

    /**
     * Update pengaturan
     */
    private function update($data) {
        $this->db->query("UPDATE {$this->table} SET 
            wajib_rpp_disetujui = :wajib,
            blokir_absensi = :absensi,
            blokir_jurnal = :jurnal,
            blokir_nilai = :nilai,
            pesan_blokir = :pesan,
            updated_at = NOW()
            WHERE id = 1");
        
        $this->db->bind(':wajib', $data['wajib_rpp_disetujui'] ?? 0);
        $this->db->bind(':absensi', $data['blokir_absensi'] ?? 1);
        $this->db->bind(':jurnal', $data['blokir_jurnal'] ?? 1);
        $this->db->bind(':nilai', $data['blokir_nilai'] ?? 1);
        $this->db->bind(':pesan', $data['pesan_blokir'] ?? '');
        
        return $this->db->execute();
    }

    /**
     * Cek apakah guru sudah punya RPP yang disetujui untuk semester aktif
     */
    public function cekGuruPunyaRPPDisetujui($id_guru, $id_tp, $id_semester) {
        try {
            $this->db->query("SELECT COUNT(*) as total FROM rpp 
                WHERE id_guru = :id_guru 
                AND id_tp = :id_tp 
                AND id_semester = :id_semester 
                AND status = 'approved'");
            $this->db->bind(':id_guru', $id_guru);
            $this->db->bind(':id_tp', $id_tp);
            $this->db->bind(':id_semester', $id_semester);
            $result = $this->db->single();
            return ($result['total'] ?? 0) > 0;
        } catch (Exception $e) {
            return true; // Jika error, anggap punya akses
        }
    }

    /**
     * Cek apakah guru perlu diblokir aksesnya
     * Return: false = tidak diblokir, array = diblokir dengan info
     */
    public function cekBlokirAkses($id_guru, $id_tp, $id_semester, $fitur = 'all') {
        $pengaturan = $this->getPengaturan();
        
        // Jika fitur wajib RPP tidak diaktifkan, tidak perlu blokir
        if (empty($pengaturan['wajib_rpp_disetujui'])) {
            return false;
        }
        
        // Cek apakah guru sudah punya RPP yang disetujui
        $punyaRPP = $this->cekGuruPunyaRPPDisetujui($id_guru, $id_tp, $id_semester);
        
        if ($punyaRPP) {
            return false; // Tidak diblokir
        }
        
        // Cek berdasarkan fitur yang diminta
        $diblokir = false;
        switch ($fitur) {
            case 'absensi':
                $diblokir = !empty($pengaturan['blokir_absensi']);
                break;
            case 'jurnal':
                $diblokir = !empty($pengaturan['blokir_jurnal']);
                break;
            case 'nilai':
                $diblokir = !empty($pengaturan['blokir_nilai']);
                break;
            case 'all':
            default:
                $diblokir = !empty($pengaturan['blokir_absensi']) || 
                            !empty($pengaturan['blokir_jurnal']) || 
                            !empty($pengaturan['blokir_nilai']);
                break;
        }
        
        if ($diblokir) {
            return [
                'diblokir' => true,
                'pesan' => $pengaturan['pesan_blokir'] ?? 'RPP belum disetujui',
                'blokir_absensi' => !empty($pengaturan['blokir_absensi']),
                'blokir_jurnal' => !empty($pengaturan['blokir_jurnal']),
                'blokir_nilai' => !empty($pengaturan['blokir_nilai'])
            ];
        }
        
        return false;
    }

    /**
     * Get statistik RPP guru untuk dashboard
     */
    public function getStatistikRPPGuru($id_guru, $id_tp, $id_semester) {
        try {
            $this->db->query("SELECT 
                COUNT(*) as total_rpp,
                SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as draft,
                SUM(CASE WHEN status = 'submitted' THEN 1 ELSE 0 END) as submitted,
                SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN status = 'revision' THEN 1 ELSE 0 END) as revision
                FROM rpp 
                WHERE id_guru = :id_guru 
                AND id_tp = :id_tp 
                AND id_semester = :id_semester");
            $this->db->bind(':id_guru', $id_guru);
            $this->db->bind(':id_tp', $id_tp);
            $this->db->bind(':id_semester', $id_semester);
            return $this->db->single();
        } catch (Exception $e) {
            return [
                'total_rpp' => 0,
                'draft' => 0,
                'submitted' => 0,
                'approved' => 0,
                'revision' => 0
            ];
        }
    }
}
