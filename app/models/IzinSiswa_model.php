<?php
// File: app/models/IzinSiswa_model.php

class IzinSiswa_model
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    public function getIzinByKelas($id_kelas, $id_tp, $filters = [])
    {
        $sql = "SELECT iz.*, s.nama_siswa, s.nisn, k.nama_kelas, g.nama_guru
                FROM izin_siswa iz
                JOIN siswa s ON s.id_siswa = iz.id_siswa
                JOIN kelas k ON k.id_kelas = iz.id_kelas
                LEFT JOIN guru g ON g.id_guru = iz.id_guru_input
                WHERE iz.id_kelas = :id_kelas AND iz.id_tp = :id_tp";

        if (!empty($filters['status'])) {
            $sql .= " AND iz.status = :status";
        }
        if (!empty($filters['bulan'])) {
            $sql .= " AND (MONTH(iz.tanggal_mulai) = :bulan OR MONTH(iz.tanggal_selesai) = :bulan2)";
        }

        $sql .= " ORDER BY iz.created_at DESC";

        $this->db->query($sql);
        $this->db->bind('id_kelas', $id_kelas);
        $this->db->bind('id_tp', $id_tp);

        if (!empty($filters['status'])) {
            $this->db->bind('status', $filters['status']);
        }
        if (!empty($filters['bulan'])) {
            $this->db->bind('bulan', $filters['bulan']);
            $this->db->bind('bulan2', $filters['bulan']);
        }

        return $this->db->resultSet();
    }

    public function getIzinById($id_izin)
    {
        $this->db->query("SELECT iz.*, s.nama_siswa, s.nisn, k.nama_kelas
                          FROM izin_siswa iz
                          JOIN siswa s ON s.id_siswa = iz.id_siswa
                          JOIN kelas k ON k.id_kelas = iz.id_kelas
                          WHERE iz.id_izin = :id_izin");
        $this->db->bind('id_izin', $id_izin);
        return $this->db->single();
    }

    public function tambahIzin($data)
    {
        $this->db->query("INSERT INTO izin_siswa (id_siswa, id_kelas, id_tp, jenis_izin, tanggal_mulai, tanggal_selesai, keterangan, bukti_file, id_guru_input)
                          VALUES (:id_siswa, :id_kelas, :id_tp, :jenis_izin, :tanggal_mulai, :tanggal_selesai, :keterangan, :bukti_file, :id_guru_input)");
        $this->db->bind('id_siswa', $data['id_siswa']);
        $this->db->bind('id_kelas', $data['id_kelas']);
        $this->db->bind('id_tp', $data['id_tp']);
        $this->db->bind('jenis_izin', $data['jenis_izin']);
        $this->db->bind('tanggal_mulai', $data['tanggal_mulai']);
        $this->db->bind('tanggal_selesai', $data['tanggal_selesai']);
        $this->db->bind('keterangan', $data['keterangan'] ?? '');
        $this->db->bind('bukti_file', $data['bukti_file'] ?? null);
        $this->db->bind('id_guru_input', $data['id_guru_input']);
        $this->db->execute();
        return $this->db->rowCount();
    }

    public function updateIzin($id_izin, $data)
    {
        $this->db->query("UPDATE izin_siswa
                          SET jenis_izin = :jenis_izin,
                              tanggal_mulai = :tanggal_mulai,
                              tanggal_selesai = :tanggal_selesai,
                              keterangan = :keterangan
                          WHERE id_izin = :id_izin");
        $this->db->bind('jenis_izin', $data['jenis_izin']);
        $this->db->bind('tanggal_mulai', $data['tanggal_mulai']);
        $this->db->bind('tanggal_selesai', $data['tanggal_selesai']);
        $this->db->bind('keterangan', $data['keterangan'] ?? '');
        $this->db->bind('id_izin', $id_izin);
        $this->db->execute();
        return $this->db->rowCount();
    }

    public function batalkanIzin($id_izin)
    {
        $this->db->query("UPDATE izin_siswa SET status = 'dibatalkan' WHERE id_izin = :id_izin");
        $this->db->bind('id_izin', $id_izin);
        $this->db->execute();
        return $this->db->rowCount();
    }

    public function selesaikanIzinExpired()
    {
        $this->db->query("UPDATE izin_siswa SET status = 'selesai'
                          WHERE status = 'aktif' AND tanggal_selesai < CURDATE()");
        $this->db->execute();
        return $this->db->rowCount();
    }

    /**
     * Cek izin aktif untuk siswa pada tanggal tertentu.
     * Dipakai oleh Absensi_model untuk pre-fill status.
     */
    public function getIzinAktifByTanggal($id_siswa, $tanggal)
    {
        $this->db->query("SELECT iz.*, g.nama_guru AS nama_wali_kelas
                          FROM izin_siswa iz
                          LEFT JOIN guru g ON g.id_guru = iz.id_guru_input
                          WHERE iz.id_siswa = :id_siswa
                            AND iz.status = 'aktif'
                            AND :tanggal BETWEEN iz.tanggal_mulai AND iz.tanggal_selesai
                          LIMIT 1");
        $this->db->bind('id_siswa', $id_siswa);
        $this->db->bind('tanggal', $tanggal);
        return $this->db->single();
    }

    /**
     * Batch: ambil semua izin aktif untuk sekumpulan siswa pada tanggal tertentu.
     * Return: [id_siswa => ['jenis_izin' => 'I/S/D', 'keterangan' => '...', 'nama_wali_kelas' => '...']]
     */
    public function getIzinAktifBatchByTanggal($siswaIds, $tanggal)
    {
        if (empty($siswaIds)) return [];

        $placeholders = [];
        foreach ($siswaIds as $i => $id) {
            $placeholders[] = ":sid{$i}";
        }
        $inClause = implode(',', $placeholders);

        $this->db->query("SELECT iz.id_siswa, iz.jenis_izin, iz.keterangan, iz.tanggal_mulai, iz.tanggal_selesai, g.nama_guru AS nama_wali_kelas
                          FROM izin_siswa iz
                          LEFT JOIN guru g ON g.id_guru = iz.id_guru_input
                          WHERE iz.id_siswa IN ({$inClause})
                            AND iz.status = 'aktif'
                            AND :tanggal BETWEEN iz.tanggal_mulai AND iz.tanggal_selesai");

        foreach ($siswaIds as $i => $id) {
            $this->db->bind("sid{$i}", $id);
        }
        $this->db->bind('tanggal', $tanggal);

        $results = $this->db->resultSet();
        $map = [];
        foreach ($results as $row) {
            $map[$row['id_siswa']] = $row;
        }
        return $map;
    }

    public function cekIzinOverlap($id_siswa, $tanggal_mulai, $tanggal_selesai, $exclude_id = null)
    {
        $sql = "SELECT COUNT(*) as total FROM izin_siswa
                WHERE id_siswa = :id_siswa
                  AND status != 'dibatalkan'
                  AND tanggal_mulai <= :tanggal_selesai
                  AND tanggal_selesai >= :tanggal_mulai";
        if ($exclude_id) {
            $sql .= " AND id_izin != :exclude_id";
        }

        $this->db->query($sql);
        $this->db->bind('id_siswa', $id_siswa);
        $this->db->bind('tanggal_mulai', $tanggal_mulai);
        $this->db->bind('tanggal_selesai', $tanggal_selesai);
        if ($exclude_id) {
            $this->db->bind('exclude_id', $exclude_id);
        }

        $result = $this->db->single();
        return $result['total'] > 0;
    }

    public function countIzinAktif($id_kelas, $id_tp)
    {
        $this->db->query("SELECT COUNT(*) as total FROM izin_siswa
                          WHERE id_kelas = :id_kelas AND id_tp = :id_tp AND status = 'aktif'
                            AND tanggal_selesai >= CURDATE()");
        $this->db->bind('id_kelas', $id_kelas);
        $this->db->bind('id_tp', $id_tp);
        $result = $this->db->single();
        return $result['total'];
    }
}
