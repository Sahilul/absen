<?php
// File: app/models/KartuUjian_model.php
// v1.37.0 - Kartu Ujian (kartu peserta + kartu meja), kop per lembaga

class KartuUjian_model
{
    private $db;

    public function __construct()
    {
        $this->db = new Database;
    }

    public function getAll($id_tp = null)
    {
        $sql = "SELECT ku.*, tp.nama_tp, s.semester
                FROM kartu_ujian ku
                LEFT JOIN tp ON tp.id_tp = ku.id_tp
                LEFT JOIN semester s ON s.id_semester = ku.id_semester";
        if ($id_tp) {
            $sql .= " WHERE ku.id_tp = :id_tp";
        }
        $sql .= " ORDER BY ku.id_kartu_ujian DESC";
        $this->db->query($sql);
        if ($id_tp) {
            $this->db->bind(':id_tp', $id_tp);
        }
        return $this->db->resultSet();
    }

    public function getById($id)
    {
        $this->db->query("SELECT ku.*, tp.nama_tp, s.semester
                FROM kartu_ujian ku
                LEFT JOIN tp ON tp.id_tp = ku.id_tp
                LEFT JOIN semester s ON s.id_semester = ku.id_semester
                WHERE ku.id_kartu_ujian = :id");
        $this->db->bind(':id', (int) $id);
        return $this->db->single();
    }

    public function insert($data)
    {
        $this->db->query("INSERT INTO kartu_ujian (nama_ujian, id_tp, id_semester, created_by)
                          VALUES (:nama, :id_tp, :id_semester, :by)");
        $this->db->bind(':nama', $data['nama_ujian']);
        $this->db->bind(':id_tp', (int) $data['id_tp']);
        $this->db->bind(':id_semester', $data['id_semester'] ?: null);
        $this->db->bind(':by', $data['created_by'] ?: null);
        return $this->db->execute();
    }

    public function update($id, $nama_ujian)
    {
        $this->db->query("UPDATE kartu_ujian SET nama_ujian = :nama WHERE id_kartu_ujian = :id");
        $this->db->bind(':nama', $nama_ujian);
        $this->db->bind(':id', (int) $id);
        return $this->db->execute();
    }

    public function delete($id)
    {
        $this->db->query("DELETE FROM kartu_ujian WHERE id_kartu_ujian = :id");
        $this->db->bind(':id', (int) $id);
        return $this->db->execute();
    }

    /**
     * Daftar siswa aktif peserta kartu.
     * $filter: 'all' | 'lembaga:{id_lembaga}' | 'kelas:{id_kelas}'
     * Kelas diurutkan VII..XII lalu nama kelas, siswa urut nama.
     */
    public function getPeserta($id_tp, $filter = 'all', $lembagaList = [])
    {
        $sql = "SELECT s.id_siswa, s.nama_siswa, s.nisn, k.id_kelas, k.nama_kelas, k.jenjang
                FROM keanggotaan_kelas kk
                JOIN siswa s ON s.id_siswa = kk.id_siswa
                JOIN kelas k ON k.id_kelas = kk.id_kelas
                WHERE kk.id_tp = :id_tp AND s.status_siswa = 'aktif'";
        $params = [':id_tp' => (int) $id_tp];

        if (strpos($filter, 'kelas:') === 0) {
            $sql .= " AND k.id_kelas = :id_kelas";
            $params[':id_kelas'] = (int) substr($filter, 6);
        } elseif (strpos($filter, 'lembaga:') === 0) {
            $idLembaga = (int) substr($filter, 8);
            $jenjangs = [];
            foreach ($lembagaList as $l) {
                if ((int) $l['id_lembaga'] === $idLembaga) {
                    $jenjangs = array_filter(array_map('trim', explode(',', $l['jenjang'])));
                }
            }
            if (empty($jenjangs)) {
                return [];
            }
            $ph = [];
            foreach (array_values($jenjangs) as $i => $j) {
                $ph[] = ':j' . $i;
                $params[':j' . $i] = $j;
            }
            $sql .= " AND k.jenjang IN (" . implode(',', $ph) . ")";
        }

        $sql .= " ORDER BY CASE k.jenjang
                    WHEN 'VII' THEN 7 WHEN 'VIII' THEN 8 WHEN 'IX' THEN 9
                    WHEN 'X' THEN 10 WHEN 'XI' THEN 11 WHEN 'XII' THEN 12 ELSE 99 END,
                  k.nama_kelas, s.nama_siswa";

        $this->db->query($sql);
        foreach ($params as $k => $v) {
            $this->db->bind($k, $v);
        }
        return $this->db->resultSet();
    }

    /**
     * Data satu peserta untuk verifikasi QR (kelas di TP kartu).
     */
    public function getPesertaSingle($id_tp, $id_siswa)
    {
        $this->db->query("SELECT s.id_siswa, s.nama_siswa, s.nisn, k.nama_kelas, k.jenjang
                FROM keanggotaan_kelas kk
                JOIN siswa s ON s.id_siswa = kk.id_siswa
                JOIN kelas k ON k.id_kelas = kk.id_kelas
                WHERE kk.id_tp = :id_tp AND kk.id_siswa = :id_siswa
                LIMIT 1");
        $this->db->bind(':id_tp', (int) $id_tp);
        $this->db->bind(':id_siswa', (int) $id_siswa);
        return $this->db->single();
    }
}
