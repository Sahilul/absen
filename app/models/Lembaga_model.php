<?php
// File: app/models/Lembaga_model.php
// v1.32.0 - Pengaturan Lembaga (unifikasi kop, nama lembaga, nama kepala)

class Lembaga_model
{
    private $db;

    public function __construct()
    {
        $this->db = new Database;
    }

    /**
     * Ambil semua lembaga (urut kode)
     */
    public function getAll($onlyAktif = false)
    {
        $sql = "SELECT * FROM lembaga";
        if ($onlyAktif) {
            $sql .= " WHERE aktif = 1";
        }
        $sql .= " ORDER BY kode ASC";
        $this->db->query($sql);
        return $this->db->resultSet();
    }

    /**
     * Ambil lembaga by id
     */
    public function getById($id)
    {
        $this->db->query("SELECT * FROM lembaga WHERE id_lembaga = :id");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    /**
     * Ambil lembaga by kode (mis. MTS / MA)
     */
    public function getByKode($kode)
    {
        $this->db->query("SELECT * FROM lembaga WHERE kode = :kode LIMIT 1");
        $this->db->bind(':kode', $kode);
        return $this->db->single();
    }

    /**
     * Cari lembaga yang mencakup jenjang tertentu (mis. 'IX' -> MTs).
     * Kolom `jenjang` berisi daftar dipisah koma, mis. 'VII,VIII,IX'.
     */
    public function getByJenjang($jenjang)
    {
        $jenjang = trim((string) $jenjang);
        if ($jenjang === '') {
            return null;
        }

        // Cari yang cocok persis di antara daftar jenjang
        $this->db->query("SELECT * FROM lembaga 
            WHERE aktif = 1 
              AND FIND_IN_SET(:jenjang, REPLACE(jenjang, ' ', '')) > 0
            ORDER BY id_lembaga ASC
            LIMIT 1");
        $this->db->bind(':jenjang', $jenjang);
        $result = $this->db->single();

        // Fallback: cocokkan case-insensitive / substring
        if (!$result) {
            $this->db->query("SELECT * FROM lembaga 
                WHERE aktif = 1 AND jenjang LIKE :like
                ORDER BY id_lembaga ASC
                LIMIT 1");
            $this->db->bind(':like', '%' . $jenjang . '%');
            $result = $this->db->single();
        }

        return $result;
    }

    /**
     * Simpan (insert/update) lembaga
     */
    public function save($data)
    {
        if (!empty($data['id_lembaga'])) {
            return $this->update($data);
        }
        return $this->insert($data);
    }

    public function insert($data)
    {
        $this->db->query("INSERT INTO lembaga 
            (kode, nama_lembaga, jenjang, nama_kepala, nip_kepala, alamat, telp, email, website, kota, kop_rapor, logo, ttd_kepala, aktif)
            VALUES
            (:kode, :nama_lembaga, :jenjang, :nama_kepala, :nip_kepala, :alamat, :telp, :email, :website, :kota, :kop_rapor, :logo, :ttd_kepala, :aktif)");

        $this->bindData($data);
        return $this->db->execute();
    }

    public function update($data)
    {
        $this->db->query("UPDATE lembaga SET
            kode = :kode,
            nama_lembaga = :nama_lembaga,
            jenjang = :jenjang,
            nama_kepala = :nama_kepala,
            nip_kepala = :nip_kepala,
            alamat = :alamat,
            telp = :telp,
            email = :email,
            website = :website,
            kota = :kota,
            kop_rapor = :kop_rapor,
            logo = :logo,
            ttd_kepala = :ttd_kepala,
            aktif = :aktif
            WHERE id_lembaga = :id_lembaga");

        $this->bindData($data);
        $this->db->bind(':id_lembaga', $data['id_lembaga']);
        return $this->db->execute();
    }

    private function bindData($data)
    {
        $this->db->bind(':kode', $data['kode'] ?? '');
        $this->db->bind(':nama_lembaga', $data['nama_lembaga'] ?? '');
        $this->db->bind(':jenjang', $data['jenjang'] ?? '');
        $this->db->bind(':nama_kepala', $data['nama_kepala'] ?? '');
        $this->db->bind(':nip_kepala', $data['nip_kepala'] ?? '');
        $this->db->bind(':alamat', $data['alamat'] ?? '');
        $this->db->bind(':telp', $data['telp'] ?? '');
        $this->db->bind(':email', $data['email'] ?? '');
        $this->db->bind(':website', $data['website'] ?? '');
        $this->db->bind(':kota', $data['kota'] ?? '');
        $this->db->bind(':kop_rapor', $data['kop_rapor'] ?? '');
        $this->db->bind(':logo', $data['logo'] ?? '');
        $this->db->bind(':ttd_kepala', $data['ttd_kepala'] ?? '');
        $this->db->bind(':aktif', isset($data['aktif']) ? (int) $data['aktif'] : 1);
    }

    /**
     * Hapus lembaga
     */
    public function delete($id)
    {
        $this->db->query("DELETE FROM lembaga WHERE id_lembaga = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }
}
