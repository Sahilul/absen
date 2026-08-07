<?php
// File: app/models/SuratPenerimaan_model.php
// Model untuk fitur Surat Penerimaan Siswa (siswa pindahan).
// Lembaga (kop surat, kepala madrasah, dll) memakai ulang tabel surat_tugas_lembaga.

class SuratPenerimaan_model
{
    private $db;

    public function __construct()
    {
        $this->db = new Database;
    }

    // =================================================================
    // LEMBAGA (reuse tabel surat_tugas_lembaga)
    // =================================================================

    public function getAllLembaga()
    {
        $this->db->query('SELECT * FROM surat_tugas_lembaga ORDER BY nama_lembaga ASC');
        return $this->db->resultSet();
    }

    // =================================================================
    // SURAT PENERIMAAN CRUD
    // =================================================================

    public function getAllSurat($idLembaga = null)
    {
        $sql = "SELECT sp.*, l.nama_lembaga
                FROM surat_penerimaan sp
                JOIN surat_tugas_lembaga l ON sp.id_lembaga = l.id_lembaga";

        if ($idLembaga) {
            $sql .= " WHERE sp.id_lembaga = :id_lembaga";
        }

        $sql .= " ORDER BY sp.tanggal_surat DESC, sp.created_at DESC";

        $this->db->query($sql);
        if ($idLembaga) {
            $this->db->bind('id_lembaga', $idLembaga);
        }
        return $this->db->resultSet();
    }

    public function getSuratById($id)
    {
        $this->db->query("SELECT sp.*, l.*
                          FROM surat_penerimaan sp
                          JOIN surat_tugas_lembaga l ON sp.id_lembaga = l.id_lembaga
                          WHERE sp.id_surat = :id");
        $this->db->bind('id', $id);
        return $this->db->single();
    }

    public function simpanSurat($data)
    {
        try {
            if (empty($data['id_surat'])) {
                $query = "INSERT INTO surat_penerimaan
                            (id_lembaga, nomor_surat, tanggal_surat, kota_surat,
                             nama_siswa, tempat_lahir, tanggal_lahir, jenis_kelamin, nisn,
                             asal_sekolah, nama_orang_tua, alamat_siswa,
                             diterima_di_kelas, tahun_pelajaran, keterangan, status, created_by)
                          VALUES
                            (:id_lembaga, :nomor_surat, :tanggal_surat, :kota_surat,
                             :nama_siswa, :tempat_lahir, :tanggal_lahir, :jenis_kelamin, :nisn,
                             :asal_sekolah, :nama_orang_tua, :alamat_siswa,
                             :diterima_di_kelas, :tahun_pelajaran, :keterangan, :status, :created_by)";
            } else {
                $query = "UPDATE surat_penerimaan SET
                            id_lembaga = :id_lembaga,
                            nomor_surat = :nomor_surat,
                            tanggal_surat = :tanggal_surat,
                            kota_surat = :kota_surat,
                            nama_siswa = :nama_siswa,
                            tempat_lahir = :tempat_lahir,
                            tanggal_lahir = :tanggal_lahir,
                            jenis_kelamin = :jenis_kelamin,
                            nisn = :nisn,
                            asal_sekolah = :asal_sekolah,
                            nama_orang_tua = :nama_orang_tua,
                            alamat_siswa = :alamat_siswa,
                            diterima_di_kelas = :diterima_di_kelas,
                            tahun_pelajaran = :tahun_pelajaran,
                            keterangan = :keterangan,
                            status = :status
                          WHERE id_surat = :id_surat";
            }

            $this->db->query($query);
            $this->bindSuratData($data);
            if (empty($data['id_surat'])) {
                $this->db->bind('created_by', $data['created_by'] ?? null);
            } else {
                $this->db->bind('id_surat', $data['id_surat']);
            }

            $this->db->execute();

            return empty($data['id_surat']) ? $this->db->lastInsertId() : $data['id_surat'];
        } catch (Exception $e) {
            return false;
        }
    }

    private function bindSuratData($data)
    {
        $this->db->bind('id_lembaga', $data['id_lembaga']);
        $this->db->bind('nomor_surat', $data['nomor_surat']);
        $this->db->bind('tanggal_surat', $data['tanggal_surat']);
        $this->db->bind('kota_surat', $data['kota_surat'] ?? null);
        $this->db->bind('nama_siswa', $data['nama_siswa']);
        $this->db->bind('tempat_lahir', $data['tempat_lahir'] ?? null);
        $this->db->bind('tanggal_lahir', $data['tanggal_lahir'] ?: null);
        $this->db->bind('jenis_kelamin', $data['jenis_kelamin'] ?? null);
        $this->db->bind('nisn', $data['nisn'] ?? null);
        $this->db->bind('asal_sekolah', $data['asal_sekolah'] ?? null);
        $this->db->bind('nama_orang_tua', $data['nama_orang_tua'] ?? null);
        $this->db->bind('alamat_siswa', $data['alamat_siswa'] ?? null);
        $this->db->bind('diterima_di_kelas', $data['diterima_di_kelas'] ?? null);
        $this->db->bind('tahun_pelajaran', $data['tahun_pelajaran'] ?? null);
        $this->db->bind('keterangan', $data['keterangan'] ?? null);
        $this->db->bind('status', $data['status'] ?? 'terbit');
    }

    public function hapusSurat($id)
    {
        $this->db->query("DELETE FROM surat_penerimaan WHERE id_surat = :id");
        $this->db->bind('id', $id);
        $this->db->execute();
        return $this->db->rowCount();
    }

    // Stats untuk Dashboard
    public function getStats()
    {
        $stats = [];
        $this->db->query("SELECT COUNT(*) as total FROM surat_tugas_lembaga");
        $stats['total_lembaga'] = $this->db->single()['total'];

        $this->db->query("SELECT COUNT(*) as total FROM surat_penerimaan");
        $stats['total_surat'] = $this->db->single()['total'];

        return $stats;
    }
}
