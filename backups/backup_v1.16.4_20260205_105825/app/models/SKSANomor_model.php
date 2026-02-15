<?php

class SKSANomor_model {
    private $db;
    
    public function __construct() {
        $this->db = new Database;
    }
    
    /**
     * Generate nomor SKSA baru setiap kali download
     * Nomor urut akan increment, id_siswa tetap unik
     */
    public function getOrCreateNomor($id_siswa, $id_tp, $id_user, $nama_walas) {
        // Selalu generate nomor baru setiap download
        return $this->createNomor($id_siswa, $id_tp, $id_user, $nama_walas);
    }
    
    /**
     * Create nomor baru dengan urutan otomatis
     */
    private function createNomor($id_siswa, $id_tp, $id_user, $nama_walas) {
        // Get nomor urut terakhir untuk tahun pelajaran ini (global counter untuk semua siswa)
        $this->db->query("SELECT COALESCE(MAX(nomor_urut), 0) as last_nomor FROM sksa_nomor WHERE id_tp = :id_tp");
        $this->db->bind(':id_tp', $id_tp);
        $result = $this->db->single();
        $nextNomor = $result['last_nomor'] + 1;
        
        // Format nomor surat: [nomor_urut]/[id_siswa]/SKSA/[bulan]/[tahun]
        // Contoh: 001/123/SKSA/11/2025
        $bulan = date('m'); // bulan cetak (01-12)
        $tahun = date('Y');
        $nomorSurat = sprintf("%03d/%d/SKSA/%s/%s", $nextNomor, $id_siswa, $bulan, $tahun);
        
        // Insert ke database
        $this->db->query("INSERT INTO sksa_nomor (id_siswa, id_tp, nomor_urut, nomor_surat, created_by, nama_walas, tanggal_cetak) 
                          VALUES (:id_siswa, :id_tp, :nomor_urut, :nomor_surat, :created_by, :nama_walas, NOW())");
        $this->db->bind(':id_siswa', $id_siswa);
        $this->db->bind(':id_tp', $id_tp);
        $this->db->bind(':nomor_urut', $nextNomor);
        $this->db->bind(':nomor_surat', $nomorSurat);
        $this->db->bind(':created_by', $id_user);
        $this->db->bind(':nama_walas', $nama_walas);
        $this->db->execute();
        
        // Return data yang baru dibuat
        return [
            'id' => $this->db->lastInsertId(),
            'id_siswa' => $id_siswa,
            'id_tp' => $id_tp,
            'nomor_urut' => $nextNomor,
            'nomor_surat' => $nomorSurat,
            'tanggal_cetak' => date('Y-m-d H:i:s'),
            'created_by' => $id_user,
            'nama_walas' => $nama_walas
        ];
    }
    
    /**
     * Get nomor SKSA by ID
     */
    public function getNomorById($id) {
        $this->db->query("SELECT * FROM sksa_nomor WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }
    
    /**
     * Get all nomor SKSA untuk tahun pelajaran tertentu
     */
    public function getNomorByTahunPelajaran($id_tp) {
        $this->db->query("SELECT sn.*, s.nama_siswa, s.nisn 
                          FROM sksa_nomor sn 
                          LEFT JOIN siswa s ON sn.id_siswa = s.id_siswa 
                          WHERE sn.id_tp = :id_tp 
                          ORDER BY sn.nomor_urut ASC");
        $this->db->bind(':id_tp', $id_tp);
        return $this->db->resultSet();
    }
}
