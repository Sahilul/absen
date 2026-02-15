<?php
// File: app/models/SuratTugas_model.php

class SuratTugas_model
{
    private $db;

    public function __construct()
    {
        $this->db = new Database;
        // ensureTableExists removed, handled by setup script
    }

    // =================================================================
    // LEMBAGA CRUD
    // =================================================================

    public function getAllLembaga()
    {
        $this->db->query('SELECT * FROM surat_tugas_lembaga ORDER BY nama_lembaga ASC');
        return $this->db->resultSet();
    }

    public function getLembagaById($id)
    {
        $this->db->query('SELECT * FROM surat_tugas_lembaga WHERE id_lembaga = :id');
        $this->db->bind('id', $id);
        return $this->db->single();
    }

    public function simpanLembaga($data)
    {
        $query = "INSERT INTO surat_tugas_lembaga 
                    (nama_lembaga, kop_surat, alamat, kota, nama_kepala_lembaga, nip_kepala, jabatan_kepala, email, telepon, website)
                  VALUES 
                    (:nama_lembaga, :kop_surat, :alamat, :kota, :nama_kepala, :nip_kepala, :jabatan_kepala, :email, :telepon, :website)";

        $this->db->query($query);
        $this->bindLembagaData($data);
        $this->db->execute();
        return $this->db->rowCount();
    }

    public function updateLembaga($data)
    {
        $query = "UPDATE surat_tugas_lembaga SET 
                    nama_lembaga = :nama_lembaga,
                    alamat = :alamat,
                    kota = :kota,
                    nama_kepala_lembaga = :nama_kepala,
                    nip_kepala = :nip_kepala,
                    jabatan_kepala = :jabatan_kepala,
                    email = :email,
                    telepon = :telepon,
                    website = :website";

        if (!empty($data['kop_surat'])) {
            $query .= ", kop_surat = :kop_surat";
        }

        $query .= " WHERE id_lembaga = :id_lembaga";

        $this->db->query($query);
        $this->bindLembagaData($data);
        if (!empty($data['kop_surat'])) {
            $this->db->bind('kop_surat', $data['kop_surat']);
        }
        $this->db->bind('id_lembaga', $data['id_lembaga']);

        $this->db->execute();
        return $this->db->rowCount();
    }

    private function bindLembagaData($data)
    {
        $this->db->bind('nama_lembaga', $data['nama_lembaga']);
        if (isset($data['kop_surat']) && !empty($data['kop_surat'])) { // Only bind if part of query
            $this->db->bind('kop_surat', $data['kop_surat']);
        }
        $this->db->bind('alamat', $data['alamat']);
        $this->db->bind('kota', $data['kota']);
        $this->db->bind('nama_kepala', $data['nama_kepala_lembaga']);
        $this->db->bind('nip_kepala', $data['nip_kepala']);
        $this->db->bind('jabatan_kepala', $data['jabatan_kepala']);
        $this->db->bind('email', $data['email'] ?? '');
        $this->db->bind('telepon', $data['telepon'] ?? '');
        $this->db->bind('website', $data['website'] ?? '');
    }

    public function hapusLembaga($id)
    {
        $this->db->query('DELETE FROM surat_tugas_lembaga WHERE id_lembaga = :id');
        $this->db->bind('id', $id);
        $this->db->execute();
        return $this->db->rowCount();
    }

    // =================================================================
    // SURAT TUGAS CRUD
    // =================================================================

    public function getAllSurat($idLembaga = null)
    {
        $sql = "SELECT st.*, l.nama_lembaga 
                FROM surat_tugas st
                JOIN surat_tugas_lembaga l ON st.id_lembaga = l.id_lembaga";

        if ($idLembaga) {
            $sql .= " WHERE st.id_lembaga = :id_lembaga";
        }

        $sql .= " ORDER BY st.tanggal_surat DESC, st.created_at DESC";

        $this->db->query($sql);
        if ($idLembaga) {
            $this->db->bind('id_lembaga', $idLembaga);
        }
        return $this->db->resultSet();
    }

    public function getSuratById($id)
    {
        // Get surat info + lembaga info
        $this->db->query("SELECT st.*, l.* 
                          FROM surat_tugas st
                          JOIN surat_tugas_lembaga l ON st.id_lembaga = l.id_lembaga
                          WHERE st.id_surat = :id");
        $this->db->bind('id', $id);
        return $this->db->single();
    }

    public function getPetugasBySurat($idSurat)
    {
        $this->db->query("SELECT * FROM surat_tugas_petugas WHERE id_surat = :id ORDER BY id_petugas ASC");
        $this->db->bind('id', $idSurat);
        return $this->db->resultSet();
    }

    public function simpanSurat($data, $petugasList)
    {
        try {
            $this->db->beginTransaction();

            // 1. Insert/Update Surat
            if (empty($data['id_surat'])) {
                $query = "INSERT INTO surat_tugas 
                            (id_lembaga, nomor_surat, tanggal_surat, perihal, tempat_tugas, tanggal_mulai, tanggal_selesai, status)
                          VALUES 
                            (:id_lembaga, :nomor_surat, :tanggal_surat, :perihal, :tempat_tugas, :tanggal_mulai, :tanggal_selesai, :status)";
                $this->db->query($query);
            } else {
                $query = "UPDATE surat_tugas SET 
                            id_lembaga = :id_lembaga,
                            nomor_surat = :nomor_surat,
                            tanggal_surat = :tanggal_surat,
                            perihal = :perihal,
                            tempat_tugas = :tempat_tugas,
                            tanggal_mulai = :tanggal_mulai,
                            tanggal_selesai = :tanggal_selesai,
                            status = :status
                          WHERE id_surat = :id_surat";
                $this->db->query($query);
                $this->db->bind('id_surat', $data['id_surat']);
            }

            $this->db->bind('id_lembaga', $data['id_lembaga']);
            $this->db->bind('nomor_surat', $data['nomor_surat']);
            $this->db->bind('tanggal_surat', $data['tanggal_surat']);
            $this->db->bind('perihal', $data['perihal']);
            $this->db->bind('tempat_tugas', $data['tempat_tugas']);
            $this->db->bind('tanggal_mulai', $data['tanggal_mulai'] ?: null);
            $this->db->bind('tanggal_selesai', $data['tanggal_selesai'] ?: null);
            $this->db->bind('status', $data['status'] ?? 'draft');

            $this->db->execute();

            $idSurat = empty($data['id_surat']) ? $this->db->lastInsertId() : $data['id_surat'];

            // 2. Handle Petugas
            // Delete old valid petugas if update (simple overwrite strategy)
            $this->db->query("DELETE FROM surat_tugas_petugas WHERE id_surat = :id_surat");
            $this->db->bind('id_surat', $idSurat);
            $this->db->execute();

            // Insert new petugas
            if (!empty($petugasList)) {
                $queryPetugas = "INSERT INTO surat_tugas_petugas (id_surat, nama_petugas, jenis_identitas, identitas_petugas, jabatan_petugas) VALUES (:id_surat, :nama, :jenis, :identitas, :jabatan)";
                $this->db->query($queryPetugas);

                foreach ($petugasList as $p) {
                    if (empty($p['nama_petugas']))
                        continue; // Skip empty rows

                    $this->db->bind('id_surat', $idSurat);
                    $this->db->bind('nama', $p['nama_petugas']);
                    $this->db->bind('jenis', $p['jenis_identitas'] ?? 'NIK');
                    $this->db->bind('identitas', $p['identitas_petugas'] ?? '');
                    $this->db->bind('jabatan', $p['jabatan_petugas'] ?? '');
                    $this->db->execute();
                }
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function hapusSurat($id)
    {
        $this->db->query("DELETE FROM surat_tugas WHERE id_surat = :id");
        $this->db->bind('id', $id);
        $this->db->execute();
        return $this->db->rowCount();
    }

    // Stats for Dashboard
    public function getStats()
    {
        $stats = [];
        $this->db->query("SELECT COUNT(*) as total FROM surat_tugas_lembaga");
        $stats['total_lembaga'] = $this->db->single()['total'];

        $this->db->query("SELECT COUNT(*) as total FROM surat_tugas");
        $stats['total_surat'] = $this->db->single()['total'];

        return $stats;
    }
}
