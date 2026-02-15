<?php

class RPP_model {
    private $db;

    public function __construct() {
        require_once APPROOT . '/app/core/Database.php';
        $this->db = new Database;
    }

    // Get all RPP by guru
    public function getAllRPPByGuru($id_guru, $id_tp = null, $id_semester = null) {
        $sql = "SELECT r.*, m.nama_mapel, k.nama_kelas, g.nama_guru,
                       tp.nama_tp, s.semester
                FROM rpp r
                JOIN mapel m ON r.id_mapel = m.id_mapel
                JOIN kelas k ON r.id_kelas = k.id_kelas
                JOIN guru g ON r.id_guru = g.id_guru
                JOIN tp ON r.id_tp = tp.id_tp
                JOIN semester s ON r.id_semester = s.id_semester
                WHERE r.id_guru = :id_guru";
        
        if ($id_tp) $sql .= " AND r.id_tp = :id_tp";
        if ($id_semester) $sql .= " AND r.id_semester = :id_semester";
        $sql .= " ORDER BY r.updated_at DESC";
        
        $this->db->query($sql);
        $this->db->bind('id_guru', $id_guru);
        if ($id_tp) $this->db->bind('id_tp', $id_tp);
        if ($id_semester) $this->db->bind('id_semester', $id_semester);
        
        return $this->db->resultSet();
    }

    // Get RPP by ID
    public function getRPPById($id_rpp) {
        $this->db->query("SELECT r.*, m.nama_mapel, m.kode_mapel, k.nama_kelas, 
                                 g.nama_guru, g.nik,
                                 tp.nama_tp, s.semester
                          FROM rpp r
                          JOIN mapel m ON r.id_mapel = m.id_mapel
                          JOIN kelas k ON r.id_kelas = k.id_kelas
                          JOIN guru g ON r.id_guru = g.id_guru
                          JOIN tp ON r.id_tp = tp.id_tp
                          JOIN semester s ON r.id_semester = s.id_semester
                          WHERE r.id_rpp = :id_rpp");
        $this->db->bind('id_rpp', $id_rpp);
        return $this->db->single();
    }

    // Check if RPP exists
    public function checkExisting($id_guru, $id_mapel, $id_kelas, $id_tp, $id_semester) {
        $this->db->query("SELECT id_rpp FROM rpp 
                          WHERE id_guru = :id_guru 
                          AND id_mapel = :id_mapel 
                          AND id_kelas = :id_kelas 
                          AND id_tp = :id_tp 
                          AND id_semester = :id_semester");
        $this->db->bind('id_guru', $id_guru);
        $this->db->bind('id_mapel', $id_mapel);
        $this->db->bind('id_kelas', $id_kelas);
        $this->db->bind('id_tp', $id_tp);
        $this->db->bind('id_semester', $id_semester);
        return $this->db->single();
    }

    // Create RPP (Legacy)
    public function tambahRPP($data) {
        $this->db->query("INSERT INTO rpp (
            id_guru, id_mapel, id_kelas, id_tp, id_semester,
            nama_madrasah, alokasi_waktu,
            peserta_didik, materi_pelajaran, dimensi_profil_lulusan, materi_integrasi_kbc,
            capaian_pembelajaran, tujuan_pembelajaran,
            praktik_pedagogis, kemitraan_pembelajaran, lingkungan_pembelajaran, pemanfaatan_digital,
            kegiatan_awal, kegiatan_inti_memahami, kegiatan_inti_mengaplikasi, kegiatan_inti_merefleksi, kegiatan_penutup,
            asesmen_awal, asesmen_proses, asesmen_akhir,
            file_rpp, status
        ) VALUES (
            :id_guru, :id_mapel, :id_kelas, :id_tp, :id_semester,
            :nama_madrasah, :alokasi_waktu,
            :peserta_didik, :materi_pelajaran, :dimensi_profil_lulusan, :materi_integrasi_kbc,
            :capaian_pembelajaran, :tujuan_pembelajaran,
            :praktik_pedagogis, :kemitraan_pembelajaran, :lingkungan_pembelajaran, :pemanfaatan_digital,
            :kegiatan_awal, :kegiatan_inti_memahami, :kegiatan_inti_mengaplikasi, :kegiatan_inti_merefleksi, :kegiatan_penutup,
            :asesmen_awal, :asesmen_proses, :asesmen_akhir,
            :file_rpp, :status
        )");
        
        foreach ($data as $key => $value) {
            $this->db->bind($key, $value);
        }
        
        $this->db->execute();
        return $this->db->lastInsertId();
    }

    // Create RPP dengan field dinamis (template)
    public function tambahRPPDinamis($data) {
        $this->db->query("INSERT INTO rpp (
            id_guru, id_mapel, id_kelas, id_tp, id_semester, id_penugasan,
            alokasi_waktu, tanggal_rpp,
            rpp_field_values, status
        ) VALUES (
            :id_guru, :id_mapel, :id_kelas, :id_tp, :id_semester, :id_penugasan,
            :alokasi_waktu, :tanggal_rpp,
            :rpp_field_values, :status
        )");
        
        foreach ($data as $key => $value) {
            $this->db->bind($key, $value);
        }
        
        $this->db->execute();
        return $this->db->lastInsertId();
    }

    // Update RPP (Legacy)
    public function updateRPP($id_rpp, $data) {
        $this->db->query("UPDATE rpp SET
            nama_madrasah = :nama_madrasah,
            alokasi_waktu = :alokasi_waktu,
            peserta_didik = :peserta_didik,
            materi_pelajaran = :materi_pelajaran,
            dimensi_profil_lulusan = :dimensi_profil_lulusan,
            materi_integrasi_kbc = :materi_integrasi_kbc,
            capaian_pembelajaran = :capaian_pembelajaran,
            tujuan_pembelajaran = :tujuan_pembelajaran,
            praktik_pedagogis = :praktik_pedagogis,
            kemitraan_pembelajaran = :kemitraan_pembelajaran,
            lingkungan_pembelajaran = :lingkungan_pembelajaran,
            pemanfaatan_digital = :pemanfaatan_digital,
            kegiatan_awal = :kegiatan_awal,
            kegiatan_inti_memahami = :kegiatan_inti_memahami,
            kegiatan_inti_mengaplikasi = :kegiatan_inti_mengaplikasi,
            kegiatan_inti_merefleksi = :kegiatan_inti_merefleksi,
            kegiatan_penutup = :kegiatan_penutup,
            asesmen_awal = :asesmen_awal,
            asesmen_proses = :asesmen_proses,
            asesmen_akhir = :asesmen_akhir,
            file_rpp = :file_rpp,
            status = :status
            WHERE id_rpp = :id_rpp");
        
        $this->db->bind('id_rpp', $id_rpp);
        foreach ($data as $key => $value) {
            $this->db->bind($key, $value);
        }
        
        $this->db->execute();
        return $this->db->rowCount();
    }

    // Update RPP dengan field dinamis (template)
    public function updateRPPDinamis($id_rpp, $data) {
        $this->db->query("UPDATE rpp SET
            alokasi_waktu = :alokasi_waktu,
            tanggal_rpp = :tanggal_rpp,
            rpp_field_values = :rpp_field_values,
            status = :status,
            updated_at = NOW()
            WHERE id_rpp = :id_rpp");
        
        $this->db->bind('id_rpp', $id_rpp);
        foreach ($data as $key => $value) {
            $this->db->bind($key, $value);
        }
        
        $this->db->execute();
        return $this->db->rowCount();
    }

    // Delete RPP
    public function hapusRPP($id_rpp) {
        $this->db->query("DELETE FROM rpp WHERE id_rpp = :id_rpp");
        $this->db->bind('id_rpp', $id_rpp);
        $this->db->execute();
        return $this->db->rowCount();
    }

    // Submit RPP for review
    public function submitRPP($id_rpp) {
        $this->db->query("UPDATE rpp SET status = 'submitted' WHERE id_rpp = :id_rpp");
        $this->db->bind('id_rpp', $id_rpp);
        $this->db->execute();
        return $this->db->rowCount();
    }

    // Approve RPP
    public function approveRPP($id_rpp, $reviewed_by) {
        $this->db->query("UPDATE rpp SET 
                          status = 'approved',
                          reviewed_by = :reviewed_by,
                          reviewed_at = NOW()
                          WHERE id_rpp = :id_rpp");
        $this->db->bind('id_rpp', $id_rpp);
        $this->db->bind('reviewed_by', $reviewed_by);
        $this->db->execute();
        return $this->db->rowCount();
    }

    // Request revision
    public function revisionRPP($id_rpp, $catatan, $reviewed_by) {
        $this->db->query("UPDATE rpp SET 
                          status = 'revision',
                          catatan_review = :catatan,
                          reviewed_by = :reviewed_by,
                          reviewed_at = NOW()
                          WHERE id_rpp = :id_rpp");
        $this->db->bind('id_rpp', $id_rpp);
        $this->db->bind('catatan', $catatan);
        $this->db->bind('reviewed_by', $reviewed_by);
        $this->db->execute();
        return $this->db->rowCount();
    }

    // Get RPP for review (Kepala Madrasah)
    public function getRPPForReview($status = 'submitted') {
        $this->db->query("SELECT r.*, m.nama_mapel, k.nama_kelas, g.nama_guru,
                                 tp.nama_tp, s.semester
                          FROM rpp r
                          JOIN mapel m ON r.id_mapel = m.id_mapel
                          JOIN kelas k ON r.id_kelas = k.id_kelas
                          JOIN guru g ON r.id_guru = g.id_guru
                          JOIN tp ON r.id_tp = tp.id_tp
                          JOIN semester s ON r.id_semester = s.id_semester
                          WHERE r.status = :status
                          ORDER BY r.updated_at DESC");
        $this->db->bind('status', $status);
        return $this->db->resultSet();
    }

    // Get all RPP for admin review (multiple statuses)
    public function getAllRPP($id_tp = null, $id_semester = null, $status = null) {
        $sql = "SELECT r.*, m.nama_mapel, k.nama_kelas, g.nama_guru,
                       tp.nama_tp, s.semester
                FROM rpp r
                JOIN mapel m ON r.id_mapel = m.id_mapel
                JOIN kelas k ON r.id_kelas = k.id_kelas
                JOIN guru g ON r.id_guru = g.id_guru
                JOIN tp ON r.id_tp = tp.id_tp
                JOIN semester s ON r.id_semester = s.id_semester
                WHERE 1=1";
        
        if ($id_tp) $sql .= " AND r.id_tp = :id_tp";
        if ($id_semester) $sql .= " AND r.id_semester = :id_semester";
        if ($status) $sql .= " AND r.status = :status";
        $sql .= " ORDER BY r.updated_at DESC";
        
        $this->db->query($sql);
        if ($id_tp) $this->db->bind('id_tp', $id_tp);
        if ($id_semester) $this->db->bind('id_semester', $id_semester);
        if ($status) $this->db->bind('status', $status);
        
        return $this->db->resultSet();
    }

    // Count pending RPP for admin dashboard
    public function countPendingReview() {
        $this->db->query("SELECT COUNT(*) as total FROM rpp WHERE status = 'submitted'");
        $result = $this->db->single();
        return $result['total'] ?? 0;
    }

    // Count RPP by status
    public function countByStatus($id_guru, $status) {
        $this->db->query("SELECT COUNT(*) as total FROM rpp 
                          WHERE id_guru = :id_guru AND status = :status");
        $this->db->bind('id_guru', $id_guru);
        $this->db->bind('status', $status);
        $result = $this->db->single();
        return $result['total'] ?? 0;
    }

    // Get pengaturan rapor for kelas (untuk kop, ttd, dll)
    public function getPengaturanRapor($id_kelas, $id_tp) {
        $this->db->query("
            SELECT pr.*, g.nama_guru as nama_wali_kelas, g.nik as nik_wali_kelas
            FROM pengaturan_rapor pr
            INNER JOIN wali_kelas wk ON pr.id_guru = wk.id_guru AND pr.id_tp = wk.id_tp
            LEFT JOIN guru g ON pr.id_guru = g.id_guru
            WHERE wk.id_kelas = :id_kelas AND wk.id_tp = :id_tp
            LIMIT 1
        ");
        $this->db->bind('id_kelas', $id_kelas);
        $this->db->bind('id_tp', $id_tp);
        return $this->db->single();
    }
}
