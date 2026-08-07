<?php

class BukuTamuLembaga_model
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    public function getAll()
    {
        $this->db->query("SELECT * FROM buku_tamu_lembaga ORDER BY nama_lembaga");
        return $this->db->resultSet();
    }

    public function getActive()
    {
        $this->db->query("SELECT * FROM buku_tamu_lembaga WHERE is_active = 1 ORDER BY nama_lembaga");
        return $this->db->resultSet();
    }

    public function getById($id)
    {
        $this->db->query("SELECT * FROM buku_tamu_lembaga WHERE id = :id");
        $this->db->bind('id', $id);
        return $this->db->single();
    }

    public function create($data)
    {
        $this->db->query("INSERT INTO buku_tamu_lembaga (nama_lembaga, kode_lembaga, is_active) VALUES (:nama, :kode, :active)");
        $this->db->bind('nama', $data['nama_lembaga']);
        $this->db->bind('kode', $data['kode_lembaga'] ?? null);
        $this->db->bind('active', $data['is_active'] ?? 1);
        $this->db->execute();
        return $this->db->lastInsertId();
    }

    public function update($id, $data)
    {
        $this->db->query("UPDATE buku_tamu_lembaga SET nama_lembaga = :nama, kode_lembaga = :kode, is_active = :active WHERE id = :id");
        $this->db->bind('nama', $data['nama_lembaga']);
        $this->db->bind('kode', $data['kode_lembaga'] ?? null);
        $this->db->bind('active', $data['is_active'] ?? 1);
        $this->db->bind('id', $id);
        return $this->db->execute();
    }

    public function delete($id)
    {
        $this->db->query("DELETE FROM buku_tamu_lembaga WHERE id = :id");
        $this->db->bind('id', $id);
        return $this->db->execute();
    }

    public function toggleActive($id)
    {
        $this->db->query("UPDATE buku_tamu_lembaga SET is_active = NOT is_active WHERE id = :id");
        $this->db->bind('id', $id);
        return $this->db->execute();
    }
}
