<?php

class BukuTamuLink_model
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    public function generateToken()
    {
        return bin2hex(random_bytes(16));
    }

    public function create($data)
    {
        $token = $this->generateToken();

        $this->db->query("INSERT INTO buku_tamu_link 
            (id_lembaga, token, nama_tamu, no_wa_tamu, keperluan_prefill, expired_at, created_by) 
            VALUES (:id_lembaga, :token, :nama_tamu, :no_wa_tamu, :keperluan, :expired_at, :created_by)");

        $this->db->bind('id_lembaga', $data['id_lembaga']);
        $this->db->bind('token', $token);
        $this->db->bind('nama_tamu', $data['nama_tamu'] ?? null);
        $this->db->bind('no_wa_tamu', $data['no_wa_tamu'] ?? null);
        $this->db->bind('keperluan', $data['keperluan_prefill'] ?? null);
        $this->db->bind('expired_at', $data['expired_at']);
        $this->db->bind('created_by', $data['created_by'] ?? null);

        $this->db->execute();

        return [
            'id' => $this->db->lastInsertId(),
            'token' => $token
        ];
    }

    public function getByToken($token)
    {
        $this->db->query("SELECT l.*, lb.nama_lembaga, lb.kode_lembaga 
                          FROM buku_tamu_link l 
                          JOIN buku_tamu_lembaga lb ON l.id_lembaga = lb.id 
                          WHERE l.token = :token");
        $this->db->bind('token', $token);
        return $this->db->single();
    }

    public function isValid($token)
    {
        $link = $this->getByToken($token);
        if (!$link)
            return false;
        if ($link['used'])
            return false;
        if (strtotime($link['expired_at']) < time())
            return false;
        return true;
    }

    public function markUsed($token)
    {
        $this->db->query("UPDATE buku_tamu_link SET used = 1 WHERE token = :token");
        $this->db->bind('token', $token);
        return $this->db->execute();
    }

    public function getAll($limit = 50)
    {
        $this->db->query("SELECT l.*, lb.nama_lembaga 
                          FROM buku_tamu_link l 
                          JOIN buku_tamu_lembaga lb ON l.id_lembaga = lb.id 
                          ORDER BY l.created_at DESC LIMIT :limit");
        $this->db->bind('limit', $limit);
        return $this->db->resultSet();
    }

    public function delete($id)
    {
        $this->db->query("DELETE FROM buku_tamu_link WHERE id = :id");
        $this->db->bind('id', $id);
        return $this->db->execute();
    }
}
