<?php
// File: app/models/PengaturanRapor_model.php

class PengaturanRapor_model {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    /**
     * Get pengaturan rapor by guru dan tahun pelajaran
     */
    public function getPengaturanByGuru($id_guru, $id_tp) {
        $this->db->query("SELECT * FROM pengaturan_rapor 
            WHERE id_guru = :id_guru AND id_tp = :id_tp");
        $this->db->bind(':id_guru', $id_guru);
        $this->db->bind(':id_tp', $id_tp);
        return $this->db->single();
    }

    /**
     * Get pengaturan rapor by kelas dan tahun pelajaran
     * v1.32.0 - Data lembaga (kop, nama, kepala, ttd) di-merge dari tabel `lembaga`
     *           berdasarkan jenjang kelas, sehingga tidak lagi terduplikasi per wali kelas.
     */
    public function getPengaturanByKelas($id_kelas, $id_tp) {
        $this->db->query("SELECT pr.*, 
            pr.nama_madrasah as nama_sekolah,
            pr.nama_kepala_madrasah as nama_kepsek,
            pr.ttd_kepala_madrasah as ttd_kepsek,
            '' as nip_kepsek,
            '' as logo_sekolah,
            '' as alamat_sekolah,
            '' as telepon_sekolah,
            '' as email_sekolah,
            k.jenjang as jenjang_kelas
            FROM pengaturan_rapor pr
            INNER JOIN wali_kelas wk ON pr.id_guru = wk.id_guru AND pr.id_tp = wk.id_tp
            LEFT JOIN kelas k ON wk.id_kelas = k.id_kelas
            WHERE wk.id_kelas = :id_kelas AND wk.id_tp = :id_tp
            LIMIT 1");
        $this->db->bind(':id_kelas', $id_kelas);
        $this->db->bind(':id_tp', $id_tp);
        $result = $this->db->single();

        if ($result) {
            $result = $this->mergeLembaga($result, $result['jenjang_kelas'] ?? null);
        }

        return $result;
    }

    /**
     * v1.32.0 - Override data lembaga (kop, nama, kepala, ttd, kota) dari tabel `lembaga`
     * berdasarkan jenjang kelas. Jika lembaga belum diatur, data lama tetap dipakai.
     */
    private function mergeLembaga($pengaturan, $jenjang)
    {
        if (empty($jenjang)) {
            return $pengaturan;
        }

        try {
            if (!class_exists('Lembaga_model')) {
                require_once APPROOT . '/app/models/Lembaga_model.php';
            }
            $lembagaModel = new Lembaga_model();
            $lembaga = $lembagaModel->getByJenjang($jenjang);
        } catch (Exception $e) {
            return $pengaturan;
        }

        if (!$lembaga) {
            return $pengaturan;
        }

        // Override hanya jika lembaga punya nilai (agar tidak menimpa dengan kosong)
        if (!empty($lembaga['nama_lembaga'])) {
            $pengaturan['nama_madrasah'] = $lembaga['nama_lembaga'];
            $pengaturan['nama_sekolah'] = $lembaga['nama_lembaga'];
        }
        if (!empty($lembaga['nama_kepala'])) {
            $pengaturan['nama_kepala_madrasah'] = $lembaga['nama_kepala'];
            $pengaturan['nama_kepsek'] = $lembaga['nama_kepala'];
        }
        if (!empty($lembaga['nip_kepala'])) {
            $pengaturan['nip_kepala_madrasah'] = $lembaga['nip_kepala'];
            $pengaturan['nip_kepsek'] = $lembaga['nip_kepala'];
        }
        if (!empty($lembaga['kop_rapor'])) {
            $pengaturan['kop_rapor'] = $lembaga['kop_rapor'];
        }
        if (!empty($lembaga['ttd_kepala'])) {
            $pengaturan['ttd_kepala_madrasah'] = $lembaga['ttd_kepala'];
            $pengaturan['ttd_kepsek'] = $lembaga['ttd_kepala'];
        }
        if (!empty($lembaga['logo'])) {
            $pengaturan['logo_madrasah'] = $lembaga['logo'];
            $pengaturan['logo_sekolah'] = $lembaga['logo'];
        }
        if (!empty($lembaga['kota'])) {
            $pengaturan['tempat_cetak'] = $lembaga['kota'];
        }
        if (!empty($lembaga['alamat'])) {
            $pengaturan['alamat_madrasah'] = $lembaga['alamat'];
            $pengaturan['alamat_sekolah'] = $lembaga['alamat'];
        }
        if (!empty($lembaga['telp'])) {
            $pengaturan['telp_madrasah'] = $lembaga['telp'];
            $pengaturan['telepon_sekolah'] = $lembaga['telp'];
        }
        if (!empty($lembaga['email'])) {
            $pengaturan['email_madrasah'] = $lembaga['email'];
            $pengaturan['email_sekolah'] = $lembaga['email'];
        }

        // Simpan info lembaga untuk referensi view
        $pengaturan['lembaga_id'] = $lembaga['id_lembaga'];
        $pengaturan['lembaga_kode'] = $lembaga['kode'];

        return $pengaturan;
    }

    /**
     * Insert pengaturan rapor baru
     */
    public function insert($data) {
        $this->db->query("INSERT INTO pengaturan_rapor 
            (id_guru, id_tp, kop_rapor, nama_madrasah, tempat_cetak, nama_kepala_madrasah, ttd_kepala_madrasah, ttd_wali_kelas, tanggal_cetak, mapel_rapor, persen_harian_sts, persen_sts, persen_harian_sas, persen_sts_sas, persen_sas) 
            VALUES 
            (:id_guru, :id_tp, :kop_rapor, :nama_madrasah, :tempat_cetak, :nama_kepala_madrasah, :ttd_kepala_madrasah, :ttd_wali_kelas, :tanggal_cetak, :mapel_rapor, :persen_harian_sts, :persen_sts, :persen_harian_sas, :persen_sts_sas, :persen_sas)");
        
        $this->db->bind(':id_guru', $data['id_guru']);
        $this->db->bind(':id_tp', $data['id_tp']);
        $this->db->bind(':kop_rapor', $data['kop_rapor'] ?? '');
        $this->db->bind(':nama_madrasah', $data['nama_madrasah'] ?? '');
        $this->db->bind(':tempat_cetak', $data['tempat_cetak'] ?? '');
        $this->db->bind(':nama_kepala_madrasah', $data['nama_kepala_madrasah'] ?? '');
        $this->db->bind(':ttd_kepala_madrasah', $data['ttd_kepala_madrasah'] ?? '');
        $this->db->bind(':ttd_wali_kelas', $data['ttd_wali_kelas'] ?? '');
        $this->db->bind(':tanggal_cetak', $data['tanggal_cetak'] ?? date('Y-m-d'));
        $this->db->bind(':mapel_rapor', $data['mapel_rapor'] ?? '[]');
        $this->db->bind(':persen_harian_sts', $data['persen_harian_sts'] ?? 60);
        $this->db->bind(':persen_sts', $data['persen_sts'] ?? 40);
        $this->db->bind(':persen_harian_sas', $data['persen_harian_sas'] ?? 40);
        $this->db->bind(':persen_sts_sas', $data['persen_sts_sas'] ?? 30);
        $this->db->bind(':persen_sas', $data['persen_sas'] ?? 30);
        
        return $this->db->execute();
    }

    /**
     * Update pengaturan rapor
     */
    public function update($data) {
        $this->db->query("UPDATE pengaturan_rapor SET 
            kop_rapor = :kop_rapor,
            nama_madrasah = :nama_madrasah,
            tempat_cetak = :tempat_cetak,
            nama_kepala_madrasah = :nama_kepala_madrasah,
            ttd_kepala_madrasah = :ttd_kepala_madrasah,
            ttd_wali_kelas = :ttd_wali_kelas,
            tanggal_cetak = :tanggal_cetak,
            mapel_rapor = :mapel_rapor,
            persen_harian_sts = :persen_harian_sts,
            persen_sts = :persen_sts,
            persen_harian_sas = :persen_harian_sas,
            persen_sts_sas = :persen_sts_sas,
            persen_sas = :persen_sas
            WHERE id_guru = :id_guru AND id_tp = :id_tp");
        
        $this->db->bind(':kop_rapor', $data['kop_rapor'] ?? '');
        $this->db->bind(':nama_madrasah', $data['nama_madrasah'] ?? '');
        $this->db->bind(':tempat_cetak', $data['tempat_cetak'] ?? '');
        $this->db->bind(':nama_kepala_madrasah', $data['nama_kepala_madrasah'] ?? '');
        $this->db->bind(':ttd_kepala_madrasah', $data['ttd_kepala_madrasah'] ?? '');
        $this->db->bind(':ttd_wali_kelas', $data['ttd_wali_kelas'] ?? '');
        $this->db->bind(':tanggal_cetak', $data['tanggal_cetak'] ?? date('Y-m-d'));
        $this->db->bind(':mapel_rapor', $data['mapel_rapor'] ?? '[]');
        $this->db->bind(':persen_harian_sts', $data['persen_harian_sts'] ?? 60);
        $this->db->bind(':persen_sts', $data['persen_sts'] ?? 40);
        $this->db->bind(':persen_harian_sas', $data['persen_harian_sas'] ?? 40);
        $this->db->bind(':persen_sts_sas', $data['persen_sts_sas'] ?? 30);
        $this->db->bind(':persen_sas', $data['persen_sas'] ?? 30);
        $this->db->bind(':id_guru', $data['id_guru']);
        $this->db->bind(':id_tp', $data['id_tp']);
        
        return $this->db->execute();
    }

    /**
     * Simpan atau update pengaturan (upsert)
     */
    public function save($data) {
        $existing = $this->getPengaturanByGuru($data['id_guru'], $data['id_tp']);
        
        if ($existing) {
            return $this->update($data);
        } else {
            return $this->insert($data);
        }
    }

    /**
     * Delete pengaturan rapor
     */
    public function delete($id_guru, $id_tp) {
        $this->db->query("DELETE FROM pengaturan_rapor 
            WHERE id_guru = :id_guru AND id_tp = :id_tp");
        $this->db->bind(':id_guru', $id_guru);
        $this->db->bind(':id_tp', $id_tp);
        return $this->db->execute();
    }
}