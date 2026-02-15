<?php

class BukuTamu_model
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    public function create($data)
    {
        $this->db->query("INSERT INTO buku_tamu 
            (id_link, id_lembaga, nama_tamu, instansi, no_hp, email, keperluan, bertemu_dengan, catatan, foto_drive_id, foto_url, waktu_datang, waktu_pulang) 
            VALUES (:id_link, :id_lembaga, :nama_tamu, :instansi, :no_hp, :email, :keperluan, :bertemu_dengan, :catatan, :foto_drive_id, :foto_url, :waktu_datang, :waktu_pulang)");

        $this->db->bind('id_link', $data['id_link'] ?? null);
        $this->db->bind('id_lembaga', $data['id_lembaga']);
        $this->db->bind('nama_tamu', $data['nama_tamu']);
        $this->db->bind('instansi', $data['instansi'] ?? null);
        $this->db->bind('no_hp', $data['no_hp']);
        $this->db->bind('email', $data['email'] ?? null);
        $this->db->bind('keperluan', $data['keperluan']);
        $this->db->bind('bertemu_dengan', $data['bertemu_dengan'] ?? null);
        $this->db->bind('catatan', $data['catatan'] ?? null);
        $this->db->bind('foto_drive_id', $data['foto_drive_id'] ?? null);
        $this->db->bind('foto_url', $data['foto_url'] ?? null);
        $this->db->bind('waktu_datang', $data['waktu_datang'] ?? date('Y-m-d H:i:s'));
        $this->db->bind('waktu_pulang', $data['waktu_pulang'] ?? null);

        $this->db->execute();
        return $this->db->lastInsertId();
    }

    public function getAll($limit = 100)
    {
        $this->db->query("SELECT t.*, lb.nama_lembaga 
                          FROM buku_tamu t 
                          JOIN buku_tamu_lembaga lb ON t.id_lembaga = lb.id 
                          ORDER BY t.waktu_datang DESC LIMIT :limit");
        $this->db->bind('limit', $limit);
        return $this->db->resultSet();
    }

    public function getByLembaga($id_lembaga, $limit = 100)
    {
        $this->db->query("SELECT t.*, lb.nama_lembaga 
                          FROM buku_tamu t 
                          JOIN buku_tamu_lembaga lb ON t.id_lembaga = lb.id 
                          WHERE t.id_lembaga = :id_lembaga
                          ORDER BY t.waktu_datang DESC LIMIT :limit");
        $this->db->bind('id_lembaga', $id_lembaga);
        $this->db->bind('limit', $limit);
        return $this->db->resultSet();
    }

    public function getById($id)
    {
        $this->db->query("SELECT t.*, lb.nama_lembaga 
                          FROM buku_tamu t 
                          JOIN buku_tamu_lembaga lb ON t.id_lembaga = lb.id 
                          WHERE t.id = :id");
        $this->db->bind('id', $id);
        return $this->db->single();
    }

    public function getByDateRange($start, $end, $id_lembaga = null)
    {
        $sql = "SELECT t.*, lb.nama_lembaga 
                FROM buku_tamu t 
                JOIN buku_tamu_lembaga lb ON t.id_lembaga = lb.id 
                WHERE DATE(t.waktu_datang) BETWEEN :start AND :end";

        if ($id_lembaga) {
            $sql .= " AND t.id_lembaga = :id_lembaga";
        }

        $sql .= " ORDER BY t.waktu_datang DESC";

        $this->db->query($sql);
        $this->db->bind('start', $start);
        $this->db->bind('end', $end);

        if ($id_lembaga) {
            $this->db->bind('id_lembaga', $id_lembaga);
        }

        return $this->db->resultSet();
    }

    public function getTodayCount($id_lembaga = null)
    {
        $sql = "SELECT COUNT(*) as total FROM buku_tamu WHERE DATE(waktu_datang) = CURDATE()";
        if ($id_lembaga) {
            $sql .= " AND id_lembaga = :id_lembaga";
        }

        $this->db->query($sql);
        if ($id_lembaga) {
            $this->db->bind('id_lembaga', $id_lembaga);
        }

        $result = $this->db->single();
        return $result['total'] ?? 0;
    }

    public function getStats($id_lembaga = null)
    {
        $andWhere = $id_lembaga ? " AND id_lembaga = :id_lembaga" : "";
        $where = $id_lembaga ? " WHERE id_lembaga = :id_lembaga" : "";

        $stats = [];

        // Today
        $this->db->query("SELECT COUNT(*) as total FROM buku_tamu WHERE DATE(waktu_datang) = CURDATE()" . $andWhere);
        if ($id_lembaga)
            $this->db->bind('id_lembaga', $id_lembaga);
        $stats['today'] = $this->db->single()['total'] ?? 0;

        // This week
        $this->db->query("SELECT COUNT(*) as total FROM buku_tamu WHERE YEARWEEK(waktu_datang) = YEARWEEK(NOW())" . $andWhere);
        if ($id_lembaga)
            $this->db->bind('id_lembaga', $id_lembaga);
        $stats['week'] = $this->db->single()['total'] ?? 0;

        // This month
        $this->db->query("SELECT COUNT(*) as total FROM buku_tamu WHERE MONTH(waktu_datang) = MONTH(NOW()) AND YEAR(waktu_datang) = YEAR(NOW())" . $andWhere);
        if ($id_lembaga)
            $this->db->bind('id_lembaga', $id_lembaga);
        $stats['month'] = $this->db->single()['total'] ?? 0;

        // Total
        $this->db->query("SELECT COUNT(*) as total FROM buku_tamu" . $where);
        if ($id_lembaga)
            $this->db->bind('id_lembaga', $id_lembaga);
        $stats['total'] = $this->db->single()['total'] ?? 0;

        return $stats;
    }

    public function delete($id)
    {
        $this->db->query("DELETE FROM buku_tamu WHERE id = :id");
        $this->db->bind('id', $id);
        return $this->db->execute();
    }

    public function setPulang($id)
    {
        $this->db->query("UPDATE buku_tamu SET waktu_pulang = NOW() WHERE id = :id");
        $this->db->bind('id', $id);
        return $this->db->execute();
    }
}
