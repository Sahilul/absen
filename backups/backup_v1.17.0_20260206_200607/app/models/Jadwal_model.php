<?php
// File: app/models/Jadwal_model.php

class Jadwal_model
{
    private $db;

    public function __construct()
    {
        require_once APPROOT . '/app/core/Database.php';
        $this->db = new Database;
    }

    // =================================================================
    // PENGATURAN JADWAL
    // =================================================================

    public function getPengaturanArray()
    {
        $this->db->query('SELECT * FROM pengaturan_jadwal');
        $results = $this->db->resultSet();
        $arr = [];
        foreach ($results as $r) {
            $arr[$r['nama_pengaturan']] = $r['nilai'];
        }
        return $arr;
    }

    public function updatePengaturan($nama, $nilai)
    {
        $this->db->query('INSERT INTO pengaturan_jadwal (nama_pengaturan, nilai) VALUES (:nama, :nilai)
                          ON DUPLICATE KEY UPDATE nilai = :nilai2');
        $this->db->bind('nama', $nama);
        $this->db->bind('nilai', $nilai);
        $this->db->bind('nilai2', $nilai);
        $this->db->execute();
    }

    // =================================================================
    // JAM PELAJARAN CRUD
    // =================================================================

    public function getAllJam()
    {
        $this->db->query('SELECT * FROM jam_pelajaran ORDER BY urutan, jam_ke');
        return $this->db->resultSet();
    }

    public function getJamById($id)
    {
        $this->db->query('SELECT * FROM jam_pelajaran WHERE id_jam = :id');
        $this->db->bind('id', $id);
        return $this->db->single();
    }

    public function tambahJam($data)
    {
        $this->db->query('INSERT INTO jam_pelajaran (jam_ke, waktu_mulai, waktu_selesai, is_istirahat, keterangan, urutan) 
                          VALUES (:jam_ke, :waktu_mulai, :waktu_selesai, :is_istirahat, :keterangan, :urutan)');
        $this->db->bind('jam_ke', $data['jam_ke']);
        $this->db->bind('waktu_mulai', $data['waktu_mulai']);
        $this->db->bind('waktu_selesai', $data['waktu_selesai']);
        $this->db->bind('is_istirahat', $data['is_istirahat'] ?? 0);
        $this->db->bind('keterangan', $data['keterangan'] ?? '');
        $this->db->bind('urutan', $data['urutan'] ?? 0);
        $this->db->execute();
        return $this->db->rowCount();
    }

    public function updateJam($data)
    {
        $this->db->query('UPDATE jam_pelajaran SET jam_ke = :jam_ke, waktu_mulai = :waktu_mulai, waktu_selesai = :waktu_selesai, 
                          is_istirahat = :is_istirahat, keterangan = :keterangan, urutan = :urutan WHERE id_jam = :id_jam');
        $this->db->bind('jam_ke', $data['jam_ke']);
        $this->db->bind('waktu_mulai', $data['waktu_mulai']);
        $this->db->bind('waktu_selesai', $data['waktu_selesai']);
        $this->db->bind('is_istirahat', $data['is_istirahat'] ?? 0);
        $this->db->bind('keterangan', $data['keterangan'] ?? '');
        $this->db->bind('urutan', $data['urutan'] ?? 0);
        $this->db->bind('id_jam', $data['id_jam']);
        $this->db->execute();
        return $this->db->rowCount();
    }

    public function hapusJam($id)
    {
        $this->db->query('DELETE FROM jam_pelajaran WHERE id_jam = :id');
        $this->db->bind('id', $id);
        $this->db->execute();
        return $this->db->rowCount();
    }

    public function countJam()
    {
        $this->db->query('SELECT COUNT(*) as total FROM jam_pelajaran WHERE is_istirahat = 0');
        return $this->db->single()['total'];
    }

    // =================================================================
    // GURU MAPEL
    // =================================================================

    public function getAllGuruMapel()
    {
        $this->db->query('SELECT gm.*, g.nama_guru, m.nama_mapel 
                          FROM guru_mapel gm 
                          JOIN guru g ON gm.id_guru = g.id_guru 
                          JOIN mapel m ON gm.id_mapel = m.id_mapel 
                          ORDER BY g.nama_guru, m.nama_mapel');
        return $this->db->resultSet();
    }

    public function getGuruByMapel($idMapel)
    {
        $this->db->query('SELECT g.* FROM guru g 
                          JOIN guru_mapel gm ON g.id_guru = gm.id_guru 
                          WHERE gm.id_mapel = :id_mapel 
                          ORDER BY g.nama_guru');
        $this->db->bind('id_mapel', $idMapel);
        return $this->db->resultSet();
    }

    public function tambahGuruMapel($idGuru, $idMapel)
    {
        $this->db->query('INSERT IGNORE INTO guru_mapel (id_guru, id_mapel) VALUES (:id_guru, :id_mapel)');
        $this->db->bind('id_guru', $idGuru);
        $this->db->bind('id_mapel', $idMapel);
        $this->db->execute();
        return $this->db->rowCount();
    }

    public function hapusGuruMapel($id)
    {
        $this->db->query('DELETE FROM guru_mapel WHERE id_guru_mapel = :id');
        $this->db->bind('id', $id);
        $this->db->execute();
        return $this->db->rowCount();
    }

    public function hapusGuruMapelByGuru($idGuru)
    {
        $this->db->query('DELETE FROM guru_mapel WHERE id_guru = :id_guru');
        $this->db->bind('id_guru', $idGuru);
        $this->db->execute();
        return $this->db->rowCount();
    }

    public function countGuruMapel()
    {
        $this->db->query('SELECT COUNT(DISTINCT id_guru) as total FROM guru_mapel');
        return $this->db->single()['total'];
    }

    public function getJadwalDetailPerGuruMapelKelas($idTp)
    {
        $this->db->query('SELECT jp.id_guru, jp.id_mapel, jp.id_kelas, COUNT(*) as jumlah_jam, 
                                 g.nama_guru, m.nama_mapel, k.nama_kelas
                          FROM jadwal_pelajaran jp
                          JOIN guru g ON jp.id_guru = g.id_guru
                          JOIN mapel m ON jp.id_mapel = m.id_mapel
                          JOIN kelas k ON jp.id_kelas = k.id_kelas
                          WHERE jp.id_tp = :id_tp
                          GROUP BY jp.id_guru, jp.id_mapel, jp.id_kelas
                          ORDER BY g.nama_guru, m.nama_mapel, k.nama_kelas');
        $this->db->bind('id_tp', $idTp);
        return $this->db->resultSet();
    }

    // =================================================================
    // JADWAL PELAJARAN CRUD
    // =================================================================

    public function getAllJadwal($idTp = null)
    {
        $sql = 'SELECT j.*, k.nama_kelas, g.nama_guru, m.nama_mapel, jp.jam_ke, jp.waktu_mulai, jp.waktu_selesai
                FROM jadwal_pelajaran j
                JOIN kelas k ON j.id_kelas = k.id_kelas
                JOIN guru g ON j.id_guru = g.id_guru
                JOIN mapel m ON j.id_mapel = m.id_mapel
                JOIN jam_pelajaran jp ON j.id_jam = jp.id_jam';
        if ($idTp) {
            $sql .= ' WHERE j.id_tp = :id_tp';
        }
        $sql .= ' ORDER BY k.nama_kelas, j.hari, jp.urutan';

        $this->db->query($sql);
        if ($idTp) {
            $this->db->bind('id_tp', $idTp);
        }
        return $this->db->resultSet();
    }

    public function getJadwalByKelas($idKelas, $idTp)
    {
        $this->db->query('SELECT j.*, g.nama_guru, m.nama_mapel, jp.jam_ke, jp.waktu_mulai, jp.waktu_selesai, jp.is_istirahat
                          FROM jadwal_pelajaran j
                          JOIN guru g ON j.id_guru = g.id_guru
                          JOIN mapel m ON j.id_mapel = m.id_mapel
                          JOIN jam_pelajaran jp ON j.id_jam = jp.id_jam
                          WHERE j.id_kelas = :id_kelas AND j.id_tp = :id_tp
                          ORDER BY FIELD(j.hari, "Senin","Selasa","Rabu","Kamis","Jumat","Sabtu"), jp.urutan');
        $this->db->bind('id_kelas', $idKelas);
        $this->db->bind('id_tp', $idTp);
        return $this->db->resultSet();
    }

    public function getJadwalByGuru($idGuru, $idTp)
    {
        $this->db->query('SELECT j.*, k.nama_kelas, m.nama_mapel, jp.jam_ke, jp.waktu_mulai, jp.waktu_selesai
                          FROM jadwal_pelajaran j
                          JOIN kelas k ON j.id_kelas = k.id_kelas
                          JOIN mapel m ON j.id_mapel = m.id_mapel
                          JOIN jam_pelajaran jp ON j.id_jam = jp.id_jam
                          WHERE j.id_guru = :id_guru AND j.id_tp = :id_tp
                          ORDER BY FIELD(j.hari, "Senin","Selasa","Rabu","Kamis","Jumat","Sabtu"), jp.urutan');
        $this->db->bind('id_guru', $idGuru);
        $this->db->bind('id_tp', $idTp);
        return $this->db->resultSet();
    }

    public function getJadwalByJam($idJam, $idTp)
    {
        $this->db->query('SELECT j.*, k.nama_kelas, g.nama_guru, m.nama_mapel
                          FROM jadwal_pelajaran j
                          JOIN kelas k ON j.id_kelas = k.id_kelas
                          JOIN guru g ON j.id_guru = g.id_guru
                          JOIN mapel m ON j.id_mapel = m.id_mapel
                          WHERE j.id_jam = :id_jam AND j.id_tp = :id_tp
                          ORDER BY k.nama_kelas, FIELD(j.hari, "Senin","Selasa","Rabu","Kamis","Jumat","Sabtu")');
        $this->db->bind('id_jam', $idJam);
        $this->db->bind('id_tp', $idTp);
        return $this->db->resultSet();
    }

    public function getJadwalBySlot($idKelas, $idJam, $hari, $idTp)
    {
        $this->db->query('SELECT * FROM jadwal_pelajaran 
                          WHERE id_kelas = :id_kelas AND id_jam = :id_jam AND hari = :hari AND id_tp = :id_tp');
        $this->db->bind('id_kelas', $idKelas);
        $this->db->bind('id_jam', $idJam);
        $this->db->bind('hari', $hari);
        $this->db->bind('id_tp', $idTp);
        return $this->db->single();
    }

    public function cekBentrokGuru($idGuru, $idJam, $hari, $idTp, $excludeId = null)
    {
        $sql = 'SELECT j.*, k.nama_kelas FROM jadwal_pelajaran j 
                JOIN kelas k ON j.id_kelas = k.id_kelas
                WHERE j.id_guru = :id_guru AND j.id_jam = :id_jam AND j.hari = :hari AND j.id_tp = :id_tp';
        if ($excludeId) {
            $sql .= ' AND j.id_jadwal != :exclude_id';
        }
        $this->db->query($sql);
        $this->db->bind('id_guru', $idGuru);
        $this->db->bind('id_jam', $idJam);
        $this->db->bind('hari', $hari);
        $this->db->bind('id_tp', $idTp);
        if ($excludeId) {
            $this->db->bind('exclude_id', $excludeId);
        }
        return $this->db->single();
    }

    public function tambahJadwal($data)
    {
        $this->db->query('INSERT INTO jadwal_pelajaran (id_kelas, id_guru, id_mapel, id_jam, hari, id_ruangan, id_tp) 
                          VALUES (:id_kelas, :id_guru, :id_mapel, :id_jam, :hari, :id_ruangan, :id_tp)');
        $this->db->bind('id_kelas', $data['id_kelas']);
        $this->db->bind('id_guru', $data['id_guru']);
        $this->db->bind('id_mapel', $data['id_mapel']);
        $this->db->bind('id_jam', $data['id_jam']);
        $this->db->bind('hari', $data['hari']);
        $this->db->bind('id_ruangan', $data['id_ruangan'] ?? null);
        $this->db->bind('id_tp', $data['id_tp']);
        $this->db->execute();
        return $this->db->rowCount();
    }

    public function updateJadwal($id, $data)
    {
        $this->db->query('UPDATE jadwal_pelajaran SET id_guru = :id_guru, id_mapel = :id_mapel, id_ruangan = :id_ruangan 
                          WHERE id_jadwal = :id');
        $this->db->bind('id_guru', $data['id_guru']);
        $this->db->bind('id_mapel', $data['id_mapel']);
        $this->db->bind('id_ruangan', $data['id_ruangan'] ?? null);
        $this->db->bind('id', $id);
        $this->db->execute();
        return $this->db->rowCount();
    }

    public function hapusJadwal($id)
    {
        $this->db->query('DELETE FROM jadwal_pelajaran WHERE id_jadwal = :id');
        $this->db->bind('id', $id);
        $this->db->execute();
        return $this->db->rowCount();
    }

    public function countJadwal()
    {
        $this->db->query('SELECT COUNT(*) as total FROM jadwal_pelajaran');
        return $this->db->single()['total'];
    }

    // =================================================================
    // ISTIRAHAT METHODS
    // =================================================================

    /**
     * Ensure jadwal_istirahat table exists
     */
    private function ensureIstirahatTableExists()
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS jadwal_istirahat (
            id INT AUTO_INCREMENT PRIMARY KEY,
            id_kelas INT NOT NULL,
            hari VARCHAR(20) NOT NULL,
            setelah_jam INT NOT NULL,
            id_tp INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_istirahat (id_kelas, hari, setelah_jam, id_tp)
        )");
        $this->db->execute();
    }

    /**
     * Get all istirahat for a TP
     */
    public function getAllIstirahat($idTp)
    {
        $this->ensureIstirahatTableExists();
        $this->db->query('SELECT * FROM jadwal_istirahat WHERE id_tp = :id_tp');
        $this->db->bind('id_tp', $idTp);
        return $this->db->resultSet();
    }

    /**
     * Add istirahat
     */
    public function addIstirahat($idKelas, $hari, $setelahJam, $idTp)
    {
        $this->ensureIstirahatTableExists();
        $this->db->query('INSERT IGNORE INTO jadwal_istirahat (id_kelas, hari, setelah_jam, id_tp) 
                          VALUES (:id_kelas, :hari, :setelah_jam, :id_tp)');
        $this->db->bind('id_kelas', $idKelas);
        $this->db->bind('hari', $hari);
        $this->db->bind('setelah_jam', $setelahJam);
        $this->db->bind('id_tp', $idTp);
        $this->db->execute();
        return $this->db->rowCount() > 0;
    }

    /**
     * Remove istirahat
     */
    public function removeIstirahat($idKelas, $hari, $setelahJam, $idTp)
    {
        $this->ensureIstirahatTableExists();
        $this->db->query('DELETE FROM jadwal_istirahat WHERE id_kelas = :id_kelas AND hari = :hari AND setelah_jam = :setelah_jam AND id_tp = :id_tp');
        $this->db->bind('id_kelas', $idKelas);
        $this->db->bind('hari', $hari);
        $this->db->bind('setelah_jam', $setelahJam);
        $this->db->bind('id_tp', $idTp);
        $this->db->execute();
        return $this->db->rowCount() > 0;
    }

    /**
     * Get istirahat by kelas
     */
    public function getIstirahatByKelas($idKelas, $idTp)
    {
        $this->ensureIstirahatTableExists();
        $this->db->query('SELECT * FROM jadwal_istirahat WHERE id_kelas = :id_kelas AND id_tp = :id_tp');
        $this->db->bind('id_kelas', $idKelas);
        $this->db->bind('id_tp', $idTp);
        return $this->db->resultSet();
    }

    /**
     * Reset Jadwal (Delete all schedule and breaks for specific TP)
     */
    public function resetJadwal($idTp)
    {
        try {
            $this->db->query("DELETE FROM jadwal_pelajaran WHERE id_tp = :id_tp");
            $this->db->bind('id_tp', $idTp);
            $this->db->execute();

            $this->ensureIstirahatTableExists();
            $this->db->query("DELETE FROM jadwal_istirahat WHERE id_tp = :id_tp");
            $this->db->bind('id_tp', $idTp);
            $this->db->execute();

            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
