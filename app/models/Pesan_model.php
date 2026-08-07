<?php

class Pesan_model
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    /**
     * Kirim pesan baru
     */
    public function kirimPesan($data)
    {
        $this->db->query("INSERT INTO pesan (pengirim_type, pengirim_id, judul, isi, target_type, target_id, lampiran) 
                          VALUES (:pengirim_type, :pengirim_id, :judul, :isi, :target_type, :target_id, :lampiran)");
        $this->db->bind('pengirim_type', $data['pengirim_type']);
        $this->db->bind('pengirim_id', $data['pengirim_id']);
        $this->db->bind('judul', $data['judul']);
        $this->db->bind('isi', $data['isi']);
        $this->db->bind('target_type', $data['target_type']);
        $this->db->bind('target_id', $data['target_id'] ?? null);
        $this->db->bind('lampiran', $data['lampiran'] ?? null);
        $this->db->execute();
        return $this->db->lastInsertId();
    }

    /**
     * Kirim ke multiple penerima
     */
    public function kirimKePenerima($id_pesan, $penerima_list)
    {
        foreach ($penerima_list as $penerima) {
            $this->db->query("INSERT INTO pesan_penerima (id_pesan, penerima_type, penerima_id) 
                              VALUES (:id_pesan, :penerima_type, :penerima_id)");
            $this->db->bind('id_pesan', $id_pesan);
            $this->db->bind('penerima_type', $penerima['type']);
            $this->db->bind('penerima_id', $penerima['id']);
            $this->db->execute();
        }
    }

    /**
     * Get all guru IDs
     */
    public function getAllGuruIds()
    {
        $this->db->query("SELECT id_guru as id FROM guru");
        return $this->db->resultSet();
    }

    /**
     * Get guru with no_wa by ID
     */
    public function getGuruWithNoWa($id_guru)
    {
        $this->db->query("SELECT id_guru, nama_guru, no_wa FROM guru WHERE id_guru = :id_guru");
        $this->db->bind('id_guru', $id_guru);
        return $this->db->single();
    }

    /**
     * Get all guru with no_wa
     */
    public function getAllGuruWithNoWa()
    {
        $this->db->query("SELECT id_guru, nama_guru, no_wa FROM guru WHERE no_wa IS NOT NULL AND no_wa != ''");
        return $this->db->resultSet();
    }

    /**
     * Get siswa with no_wa by ID
     */
    public function getSiswaWithNoWa($id_siswa)
    {
        $this->db->query("SELECT id_siswa, nama_siswa, no_wa FROM siswa WHERE id_siswa = :id_siswa");
        $this->db->bind('id_siswa', $id_siswa);
        return $this->db->single();
    }

    /**
     * Get all siswa with no_wa
     */
    public function getAllSiswaWithNoWa()
    {
        $this->db->query("SELECT id_siswa, nama_siswa, no_wa FROM siswa WHERE no_wa IS NOT NULL AND no_wa != '' AND status_siswa = 'aktif'");
        return $this->db->resultSet();
    }

    /**
     * Get siswa with no_wa by kelas
     */
    public function getSiswaWithNoWaByKelas($id_kelas, $id_tp)
    {
        $this->db->query("SELECT s.id_siswa, s.nama_siswa, s.no_wa FROM siswa s
                          JOIN keanggotaan_kelas kk ON s.id_siswa = kk.id_siswa
                          WHERE kk.id_kelas = :id_kelas AND kk.id_tp = :id_tp AND s.status_siswa = 'aktif' AND s.no_wa IS NOT NULL AND s.no_wa != ''");
        $this->db->bind('id_kelas', $id_kelas);
        $this->db->bind('id_tp', $id_tp);
        return $this->db->resultSet();
    }

    /**
     * Get all siswa IDs
     */
    public function getAllSiswaIds()
    {
        $this->db->query("SELECT id_siswa as id FROM siswa WHERE status_siswa = 'aktif'");
        return $this->db->resultSet();
    }

    /**
     * Get siswa IDs by kelas
     */
    public function getSiswaIdsByKelas($id_kelas, $id_tp)
    {
        $this->db->query("SELECT s.id_siswa as id FROM siswa s
                          JOIN keanggotaan_kelas kk ON s.id_siswa = kk.id_siswa
                          WHERE kk.id_kelas = :id_kelas AND kk.id_tp = :id_tp AND s.status_siswa = 'aktif'");
        $this->db->bind('id_kelas', $id_kelas);
        $this->db->bind('id_tp', $id_tp);
        return $this->db->resultSet();
    }

    /**
     * Get pesan terkirim by pengirim
     */
    public function getPesanTerkirim($pengirim_type, $pengirim_id, $limit = 50)
    {
        $this->db->query("SELECT p.*, 
                          (SELECT COUNT(*) FROM pesan_penerima WHERE id_pesan = p.id_pesan) as total_penerima,
                          (SELECT COUNT(*) FROM pesan_penerima WHERE id_pesan = p.id_pesan AND dibaca = 1) as sudah_dibaca
                          FROM pesan p 
                          WHERE p.pengirim_type = :pengirim_type AND p.pengirim_id = :pengirim_id
                          ORDER BY p.created_at DESC LIMIT :limit");
        $this->db->bind('pengirim_type', $pengirim_type);
        $this->db->bind('pengirim_id', $pengirim_id);
        $this->db->bind('limit', $limit);
        return $this->db->resultSet();
    }

    /**
     * Get inbox penerima (guru/siswa)
     */
    public function getInbox($penerima_type, $penerima_id, $limit = 50)
    {
        $this->db->query("SELECT p.*, pp.dibaca, pp.dibaca_at
                          FROM pesan p
                          JOIN pesan_penerima pp ON p.id_pesan = pp.id_pesan
                          WHERE pp.penerima_type = :penerima_type AND pp.penerima_id = :penerima_id
                          ORDER BY p.created_at DESC LIMIT :limit");
        $this->db->bind('penerima_type', $penerima_type);
        $this->db->bind('penerima_id', $penerima_id);
        $this->db->bind('limit', $limit);
        return $this->db->resultSet();
    }

    /**
     * Get detail pesan
     */
    public function getPesanById($id_pesan)
    {
        $this->db->query("SELECT p.* FROM pesan p WHERE p.id_pesan = :id_pesan");
        $this->db->bind('id_pesan', $id_pesan);
        return $this->db->single();
    }

    /**
     * Get penerima pesan
     */
    public function getPenerimaPesan($id_pesan)
    {
        $this->db->query("SELECT pp.*, 
                          CASE 
                            WHEN pp.penerima_type = 'guru' THEN (SELECT nama_guru FROM guru WHERE id_guru = pp.penerima_id)
                            WHEN pp.penerima_type = 'siswa' THEN (SELECT nama_siswa FROM siswa WHERE id_siswa = pp.penerima_id)
                          END as nama_penerima
                          FROM pesan_penerima pp 
                          WHERE pp.id_pesan = :id_pesan
                          ORDER BY pp.dibaca ASC, nama_penerima ASC");
        $this->db->bind('id_pesan', $id_pesan);
        return $this->db->resultSet();
    }

    /**
     * Tandai pesan sudah dibaca
     */
    public function tandaiDibaca($id_pesan, $penerima_type, $penerima_id)
    {
        $this->db->query("UPDATE pesan_penerima SET dibaca = 1, dibaca_at = NOW() 
                          WHERE id_pesan = :id_pesan AND penerima_type = :penerima_type AND penerima_id = :penerima_id AND dibaca = 0");
        $this->db->bind('id_pesan', $id_pesan);
        $this->db->bind('penerima_type', $penerima_type);
        $this->db->bind('penerima_id', $penerima_id);
        return $this->db->execute();
    }

    /**
     * Get jumlah pesan belum dibaca
     */
    public function getUnreadCount($penerima_type, $penerima_id)
    {
        $this->db->query("SELECT COUNT(*) as total FROM pesan_penerima 
                          WHERE penerima_type = :penerima_type AND penerima_id = :penerima_id AND dibaca = 0");
        $this->db->bind('penerima_type', $penerima_type);
        $this->db->bind('penerima_id', $penerima_id);
        $result = $this->db->single();
        return $result['total'] ?? 0;
    }

    /**
     * Hapus pesan
     */
    public function hapusPesan($id_pesan)
    {
        // Get lampiran untuk dihapus
        $pesan = $this->getPesanById($id_pesan);
        if ($pesan && !empty($pesan['lampiran'])) {
            $filePath = APPROOT . '/public/uploads/pesan/' . $pesan['lampiran'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        // Delete pesan (cascade akan hapus penerima)
        $this->db->query("DELETE FROM pesan WHERE id_pesan = :id_pesan");
        $this->db->bind('id_pesan', $id_pesan);
        return $this->db->execute();
    }

    /**
     * Cek apakah user adalah penerima pesan
     */
    public function isPenerima($id_pesan, $penerima_type, $penerima_id)
    {
        $this->db->query("SELECT id FROM pesan_penerima 
                          WHERE id_pesan = :id_pesan AND penerima_type = :penerima_type AND penerima_id = :penerima_id");
        $this->db->bind('id_pesan', $id_pesan);
        $this->db->bind('penerima_type', $penerima_type);
        $this->db->bind('penerima_id', $penerima_id);
        return $this->db->single() ? true : false;
    }

    /**
     * Get nama pengirim
     */
    public function getNamaPengirim($pesan)
    {
        if ($pesan['pengirim_type'] === 'admin') {
            return 'Administrator';
        }
        $this->db->query("SELECT nama_guru FROM guru WHERE id_guru = :id_guru");
        $this->db->bind('id_guru', $pesan['pengirim_id']);
        $result = $this->db->single();
        return $result['nama_guru'] ?? 'Unknown';
    }
}
