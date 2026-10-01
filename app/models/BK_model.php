<?php

class BK_model
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    // ─── KASUS ───────────────────────────────────────────────

    public function getAllKasus($id_tp, $filters = [])
    {
        $sql = "SELECT k.*, s.nama_siswa, s.nisn, kl.nama_kelas,
                       gp.nama_guru AS nama_pelapor, gb.nama_guru AS nama_guru_bk
                FROM bk_kasus k
                JOIN siswa s ON s.id_siswa = k.id_siswa
                JOIN kelas kl ON kl.id_kelas = k.id_kelas
                LEFT JOIN guru gp ON gp.id_guru = k.id_guru_pelapor
                LEFT JOIN guru gb ON gb.id_guru = k.id_guru_bk
                WHERE k.id_tp = :id_tp";

        if (!empty($filters['id_kelas'])) {
            $sql .= " AND k.id_kelas = :id_kelas";
        }
        if (!empty($filters['status'])) {
            $sql .= " AND k.status = :status";
        }
        if (!empty($filters['kategori'])) {
            $sql .= " AND k.kategori = :kategori";
        }
        if (!empty($filters['tingkat'])) {
            $sql .= " AND k.tingkat = :tingkat";
        }
        if (!empty($filters['id_guru_bk'])) {
            $sql .= " AND k.id_guru_bk = :id_guru_bk";
        }
        if (!empty($filters['search'])) {
            $sql .= " AND (s.nama_siswa LIKE :search OR k.judul LIKE :search2)";
        }

        $sql .= " ORDER BY k.created_at DESC";

        $this->db->query($sql);
        $this->db->bind('id_tp', $id_tp);

        if (!empty($filters['id_kelas'])) $this->db->bind('id_kelas', $filters['id_kelas']);
        if (!empty($filters['status'])) $this->db->bind('status', $filters['status']);
        if (!empty($filters['kategori'])) $this->db->bind('kategori', $filters['kategori']);
        if (!empty($filters['tingkat'])) $this->db->bind('tingkat', $filters['tingkat']);
        if (!empty($filters['id_guru_bk'])) $this->db->bind('id_guru_bk', $filters['id_guru_bk']);
        if (!empty($filters['search'])) {
            $term = '%' . $filters['search'] . '%';
            $this->db->bind('search', $term);
            $this->db->bind('search2', $term);
        }

        return $this->db->resultSet();
    }

    public function getKasusById($id_kasus)
    {
        $this->db->query("SELECT k.*, s.nama_siswa, s.nisn,
                                 kl.nama_kelas,
                                 gp.nama_guru AS nama_pelapor,
                                 gb.nama_guru AS nama_guru_bk
                          FROM bk_kasus k
                          JOIN siswa s ON s.id_siswa = k.id_siswa
                          JOIN kelas kl ON kl.id_kelas = k.id_kelas
                          LEFT JOIN guru gp ON gp.id_guru = k.id_guru_pelapor
                          LEFT JOIN guru gb ON gb.id_guru = k.id_guru_bk
                          WHERE k.id_kasus = :id_kasus");
        $this->db->bind('id_kasus', $id_kasus);
        return $this->db->single();
    }

    public function getKasusBySiswa($id_siswa, $id_tp = null)
    {
        $sql = "SELECT k.*, kl.nama_kelas, gb.nama_guru AS nama_guru_bk
                FROM bk_kasus k
                JOIN kelas kl ON kl.id_kelas = k.id_kelas
                LEFT JOIN guru gb ON gb.id_guru = k.id_guru_bk
                WHERE k.id_siswa = :id_siswa";
        if ($id_tp) $sql .= " AND k.id_tp = :id_tp";
        $sql .= " ORDER BY k.created_at DESC";

        $this->db->query($sql);
        $this->db->bind('id_siswa', $id_siswa);
        if ($id_tp) $this->db->bind('id_tp', $id_tp);
        return $this->db->resultSet();
    }

    public function getKasusByGuruBK($id_guru_bk, $id_tp, $filters = [])
    {
        $sql = "SELECT k.*, s.nama_siswa, s.nisn, kl.nama_kelas,
                       gp.nama_guru AS nama_pelapor
                FROM bk_kasus k
                JOIN siswa s ON s.id_siswa = k.id_siswa
                JOIN kelas kl ON kl.id_kelas = k.id_kelas
                LEFT JOIN guru gp ON gp.id_guru = k.id_guru_pelapor
                WHERE k.id_guru_bk = :id_guru_bk AND k.id_tp = :id_tp";

        if (!empty($filters['status'])) $sql .= " AND k.status = :status";
        if (!empty($filters['kategori'])) $sql .= " AND k.kategori = :kategori";

        $sql .= " ORDER BY k.created_at DESC";

        $this->db->query($sql);
        $this->db->bind('id_guru_bk', $id_guru_bk);
        $this->db->bind('id_tp', $id_tp);
        if (!empty($filters['status'])) $this->db->bind('status', $filters['status']);
        if (!empty($filters['kategori'])) $this->db->bind('kategori', $filters['kategori']);

        return $this->db->resultSet();
    }

    public function tambahKasus($data)
    {
        $this->db->query("INSERT INTO bk_kasus (id_siswa, id_kelas, id_tp, id_semester, kategori, judul, deskripsi, tingkat, poin, status, id_guru_pelapor, id_guru_bk)
                          VALUES (:id_siswa, :id_kelas, :id_tp, :id_semester, :kategori, :judul, :deskripsi, :tingkat, :poin, :status, :id_guru_pelapor, :id_guru_bk)");
        $this->db->bind('id_siswa', $data['id_siswa']);
        $this->db->bind('id_kelas', $data['id_kelas']);
        $this->db->bind('id_tp', $data['id_tp']);
        $this->db->bind('id_semester', $data['id_semester']);
        $this->db->bind('kategori', $data['kategori']);
        $this->db->bind('judul', $data['judul']);
        $this->db->bind('deskripsi', $data['deskripsi'] ?? '');
        $this->db->bind('tingkat', $data['tingkat'] ?? 'ringan');
        $this->db->bind('poin', $data['poin'] ?? 0);
        $this->db->bind('status', $data['status'] ?? 'baru');
        $this->db->bind('id_guru_pelapor', $data['id_guru_pelapor'] ?? null);
        $this->db->bind('id_guru_bk', $data['id_guru_bk'] ?? null);
        $this->db->execute();
        return $this->db->rowCount();
    }

    public function updateKasus($id_kasus, $data)
    {
        $this->db->query("UPDATE bk_kasus SET
                            kategori = :kategori, judul = :judul, deskripsi = :deskripsi,
                            tingkat = :tingkat, poin = :poin, status = :status,
                            id_guru_bk = :id_guru_bk, tindak_lanjut = :tindak_lanjut
                          WHERE id_kasus = :id_kasus");
        $this->db->bind('kategori', $data['kategori']);
        $this->db->bind('judul', $data['judul']);
        $this->db->bind('deskripsi', $data['deskripsi'] ?? '');
        $this->db->bind('tingkat', $data['tingkat']);
        $this->db->bind('poin', $data['poin'] ?? 0);
        $this->db->bind('status', $data['status']);
        $this->db->bind('id_guru_bk', $data['id_guru_bk'] ?? null);
        $this->db->bind('tindak_lanjut', $data['tindak_lanjut'] ?? '');
        $this->db->bind('id_kasus', $id_kasus);
        $this->db->execute();
        return $this->db->rowCount();
    }

    public function updateStatusKasus($id_kasus, $status, $tindak_lanjut = null)
    {
        $sql = "UPDATE bk_kasus SET status = :status";
        if ($tindak_lanjut !== null) $sql .= ", tindak_lanjut = :tindak_lanjut";
        $sql .= " WHERE id_kasus = :id_kasus";

        $this->db->query($sql);
        $this->db->bind('status', $status);
        if ($tindak_lanjut !== null) $this->db->bind('tindak_lanjut', $tindak_lanjut);
        $this->db->bind('id_kasus', $id_kasus);
        $this->db->execute();
        return $this->db->rowCount();
    }

    public function ambilKasus($id_kasus, $id_guru_bk)
    {
        $this->db->query("UPDATE bk_kasus SET id_guru_bk = :id_guru_bk, status = 'proses' WHERE id_kasus = :id_kasus AND status = 'baru'");
        $this->db->bind('id_guru_bk', $id_guru_bk);
        $this->db->bind('id_kasus', $id_kasus);
        $this->db->execute();
        return $this->db->rowCount();
    }

    public function countKasusByStatus($id_tp, $id_guru_bk = null)
    {
        $sql = "SELECT
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'baru' THEN 1 ELSE 0 END) as baru,
                    SUM(CASE WHEN status = 'proses' THEN 1 ELSE 0 END) as proses,
                    SUM(CASE WHEN status = 'selesai' THEN 1 ELSE 0 END) as selesai,
                    SUM(CASE WHEN status = 'dirujuk' THEN 1 ELSE 0 END) as dirujuk,
                    SUM(CASE WHEN tingkat = 'ringan' THEN 1 ELSE 0 END) as ringan,
                    SUM(CASE WHEN tingkat = 'sedang' THEN 1 ELSE 0 END) as sedang,
                    SUM(CASE WHEN tingkat = 'berat' THEN 1 ELSE 0 END) as berat
                FROM bk_kasus WHERE id_tp = :id_tp";
        if ($id_guru_bk) $sql .= " AND id_guru_bk = :id_guru_bk";

        $this->db->query($sql);
        $this->db->bind('id_tp', $id_tp);
        if ($id_guru_bk) $this->db->bind('id_guru_bk', $id_guru_bk);
        return $this->db->single();
    }

    public function deleteKasus($id_kasus)
    {
        $this->db->query("DELETE FROM bk_konseling WHERE id_kasus = :id_kasus");
        $this->db->bind('id_kasus', $id_kasus);
        $this->db->execute();

        $this->db->query("DELETE FROM bk_panggilan_ortu WHERE id_kasus = :id_kasus");
        $this->db->bind('id_kasus', $id_kasus);
        $this->db->execute();

        $this->db->query("DELETE FROM bk_kasus WHERE id_kasus = :id_kasus");
        $this->db->bind('id_kasus', $id_kasus);
        $this->db->execute();
        return $this->db->rowCount();
    }

    // ─── KONSELING ───────────────────────────────────────────

    public function getKonselingByKasus($id_kasus)
    {
        $this->db->query("SELECT kn.*, gb.nama_guru AS nama_guru_bk
                          FROM bk_konseling kn
                          LEFT JOIN guru gb ON gb.id_guru = kn.id_guru_bk
                          WHERE kn.id_kasus = :id_kasus
                          ORDER BY kn.konseling_ke ASC");
        $this->db->bind('id_kasus', $id_kasus);
        return $this->db->resultSet();
    }

    public function getKonselingById($id_konseling)
    {
        $this->db->query("SELECT kn.*, s.nama_siswa, s.nisn, gb.nama_guru AS nama_guru_bk
                          FROM bk_konseling kn
                          JOIN siswa s ON s.id_siswa = kn.id_siswa
                          LEFT JOIN guru gb ON gb.id_guru = kn.id_guru_bk
                          WHERE kn.id_konseling = :id_konseling");
        $this->db->bind('id_konseling', $id_konseling);
        return $this->db->single();
    }

    public function getAllKonseling($id_guru_bk, $id_tp, $filters = [])
    {
        $sql = "SELECT kn.*, s.nama_siswa, s.nisn, kl.nama_kelas,
                       bk.judul AS judul_kasus, bk.kategori
                FROM bk_konseling kn
                JOIN siswa s ON s.id_siswa = kn.id_siswa
                LEFT JOIN bk_kasus bk ON bk.id_kasus = kn.id_kasus
                LEFT JOIN kelas kl ON kl.id_kelas = bk.id_kelas
                WHERE kn.id_guru_bk = :id_guru_bk";

        if ($id_tp) {
            $sql .= " AND (bk.id_tp = :id_tp OR bk.id_tp IS NULL)";
        }
        if (!empty($filters['tanggal_dari'])) $sql .= " AND kn.tanggal >= :tanggal_dari";
        if (!empty($filters['tanggal_sampai'])) $sql .= " AND kn.tanggal <= :tanggal_sampai";

        $sql .= " ORDER BY kn.tanggal DESC, kn.waktu_mulai DESC";

        $this->db->query($sql);
        $this->db->bind('id_guru_bk', $id_guru_bk);
        if ($id_tp) $this->db->bind('id_tp', $id_tp);
        if (!empty($filters['tanggal_dari'])) $this->db->bind('tanggal_dari', $filters['tanggal_dari']);
        if (!empty($filters['tanggal_sampai'])) $this->db->bind('tanggal_sampai', $filters['tanggal_sampai']);

        return $this->db->resultSet();
    }

    public function getNextKonselingKe($id_kasus)
    {
        $this->db->query("SELECT COALESCE(MAX(konseling_ke), 0) + 1 AS next_ke FROM bk_konseling WHERE id_kasus = :id_kasus");
        $this->db->bind('id_kasus', $id_kasus);
        $result = $this->db->single();
        return $result['next_ke'];
    }

    public function tambahKonseling($data)
    {
        $this->db->query("INSERT INTO bk_konseling (id_kasus, id_siswa, id_guru_bk, jenis, tanggal, waktu_mulai, waktu_selesai, tempat, catatan, hasil, rencana_tindak_lanjut, konseling_ke)
                          VALUES (:id_kasus, :id_siswa, :id_guru_bk, :jenis, :tanggal, :waktu_mulai, :waktu_selesai, :tempat, :catatan, :hasil, :rencana_tindak_lanjut, :konseling_ke)");
        $this->db->bind('id_kasus', $data['id_kasus'] ?? null);
        $this->db->bind('id_siswa', $data['id_siswa']);
        $this->db->bind('id_guru_bk', $data['id_guru_bk']);
        $this->db->bind('jenis', $data['jenis'] ?? 'individual');
        $this->db->bind('tanggal', $data['tanggal']);
        $this->db->bind('waktu_mulai', $data['waktu_mulai'] ?? null);
        $this->db->bind('waktu_selesai', $data['waktu_selesai'] ?? null);
        $this->db->bind('tempat', $data['tempat'] ?? null);
        $this->db->bind('catatan', $data['catatan'] ?? '');
        $this->db->bind('hasil', $data['hasil'] ?? '');
        $this->db->bind('rencana_tindak_lanjut', $data['rencana_tindak_lanjut'] ?? '');
        $this->db->bind('konseling_ke', $data['konseling_ke'] ?? 1);
        $this->db->execute();
        return $this->db->rowCount();
    }

    public function updateKonseling($id_konseling, $data)
    {
        $this->db->query("UPDATE bk_konseling SET
                            jenis = :jenis, tanggal = :tanggal, waktu_mulai = :waktu_mulai,
                            waktu_selesai = :waktu_selesai, tempat = :tempat, catatan = :catatan,
                            hasil = :hasil, rencana_tindak_lanjut = :rencana_tindak_lanjut
                          WHERE id_konseling = :id_konseling");
        $this->db->bind('jenis', $data['jenis']);
        $this->db->bind('tanggal', $data['tanggal']);
        $this->db->bind('waktu_mulai', $data['waktu_mulai'] ?? null);
        $this->db->bind('waktu_selesai', $data['waktu_selesai'] ?? null);
        $this->db->bind('tempat', $data['tempat'] ?? null);
        $this->db->bind('catatan', $data['catatan'] ?? '');
        $this->db->bind('hasil', $data['hasil'] ?? '');
        $this->db->bind('rencana_tindak_lanjut', $data['rencana_tindak_lanjut'] ?? '');
        $this->db->bind('id_konseling', $id_konseling);
        $this->db->execute();
        return $this->db->rowCount();
    }

    public function deleteKonseling($id_konseling)
    {
        $this->db->query("DELETE FROM bk_konseling WHERE id_konseling = :id_konseling");
        $this->db->bind('id_konseling', $id_konseling);
        $this->db->execute();
        return $this->db->rowCount();
    }

    // ─── PANGGILAN ORTU ──────────────────────────────────────

    public function getPanggilanByKasus($id_kasus)
    {
        $this->db->query("SELECT p.*, s.nama_siswa
                          FROM bk_panggilan_ortu p
                          JOIN siswa s ON s.id_siswa = p.id_siswa
                          WHERE p.id_kasus = :id_kasus
                          ORDER BY p.tanggal_panggilan DESC");
        $this->db->bind('id_kasus', $id_kasus);
        return $this->db->resultSet();
    }

    public function getPanggilanById($id_panggilan)
    {
        $this->db->query("SELECT p.*, s.nama_siswa, s.nisn, bk.judul AS judul_kasus
                          FROM bk_panggilan_ortu p
                          JOIN siswa s ON s.id_siswa = p.id_siswa
                          JOIN bk_kasus bk ON bk.id_kasus = p.id_kasus
                          WHERE p.id_panggilan = :id_panggilan");
        $this->db->bind('id_panggilan', $id_panggilan);
        return $this->db->single();
    }

    public function tambahPanggilan($data)
    {
        $this->db->query("INSERT INTO bk_panggilan_ortu (id_kasus, id_siswa, nomor_surat, tanggal_surat, tanggal_panggilan, status, catatan_hasil)
                          VALUES (:id_kasus, :id_siswa, :nomor_surat, :tanggal_surat, :tanggal_panggilan, :status, :catatan_hasil)");
        $this->db->bind('id_kasus', $data['id_kasus']);
        $this->db->bind('id_siswa', $data['id_siswa']);
        $this->db->bind('nomor_surat', $data['nomor_surat'] ?? null);
        $this->db->bind('tanggal_surat', $data['tanggal_surat'] ?? null);
        $this->db->bind('tanggal_panggilan', $data['tanggal_panggilan']);
        $this->db->bind('status', $data['status'] ?? 'dijadwalkan');
        $this->db->bind('catatan_hasil', $data['catatan_hasil'] ?? '');
        $this->db->execute();
        return $this->db->rowCount();
    }

    public function updatePanggilan($id_panggilan, $data)
    {
        $this->db->query("UPDATE bk_panggilan_ortu SET
                            status = :status, catatan_hasil = :catatan_hasil,
                            tanggal_panggilan = :tanggal_panggilan
                          WHERE id_panggilan = :id_panggilan");
        $this->db->bind('status', $data['status']);
        $this->db->bind('catatan_hasil', $data['catatan_hasil'] ?? '');
        $this->db->bind('tanggal_panggilan', $data['tanggal_panggilan']);
        $this->db->bind('id_panggilan', $id_panggilan);
        $this->db->execute();
        return $this->db->rowCount();
    }

    // ─── HELPERS ─────────────────────────────────────────────

    public function getGuruBKList($id_tp)
    {
        $this->db->query("SELECT g.id_guru, g.nama_guru
                          FROM guru_fungsi gf
                          JOIN guru g ON g.id_guru = gf.id_guru
                          WHERE gf.fungsi = 'guru_bk' AND gf.id_tp = :id_tp AND gf.is_active = 1
                          ORDER BY g.nama_guru");
        $this->db->bind('id_tp', $id_tp);
        return $this->db->resultSet();
    }

    public function getSiswaByKelas($id_kelas, $id_tp)
    {
        $this->db->query("SELECT s.id_siswa, s.nama_siswa, s.nisn
                          FROM siswa s
                          JOIN keanggotaan_kelas kk ON kk.id_siswa = s.id_siswa
                          WHERE kk.id_kelas = :id_kelas AND kk.id_tp = :id_tp
                          ORDER BY s.nama_siswa");
        $this->db->bind('id_kelas', $id_kelas);
        $this->db->bind('id_tp', $id_tp);
        return $this->db->resultSet();
    }

    public function getKasusByPelapor($id_guru_pelapor, $id_tp, $filters = [])
    {
        $sql = "SELECT bk.*, s.nama_siswa, s.nisn, k.nama_kelas,
                       gp.nama_guru AS nama_pelapor, gbk.nama_guru AS nama_guru_bk
                FROM bk_kasus bk
                JOIN siswa s ON s.id_siswa = bk.id_siswa
                JOIN kelas k ON k.id_kelas = bk.id_kelas
                LEFT JOIN guru gp ON gp.id_guru = bk.id_guru_pelapor
                LEFT JOIN guru gbk ON gbk.id_guru = bk.id_guru_bk
                WHERE bk.id_guru_pelapor = :id_guru AND bk.id_tp = :id_tp";

        if (!empty($filters['status'])) {
            $sql .= " AND bk.status = :status";
        }
        $sql .= " ORDER BY bk.created_at DESC";

        $this->db->query($sql);
        $this->db->bind('id_guru', $id_guru_pelapor);
        $this->db->bind('id_tp', $id_tp);
        if (!empty($filters['status'])) {
            $this->db->bind('status', $filters['status']);
        }
        return $this->db->resultSet();
    }

    public function getKelasByGuru($id_guru, $id_semester)
    {
        $this->db->query("SELECT DISTINCT k.id_kelas, k.nama_kelas
                          FROM penugasan p
                          JOIN kelas k ON k.id_kelas = p.id_kelas
                          WHERE p.id_guru = :id_guru AND p.id_semester = :id_semester
                          ORDER BY k.nama_kelas");
        $this->db->bind('id_guru', $id_guru);
        $this->db->bind('id_semester', $id_semester);
        return $this->db->resultSet();
    }

    public function countKasusByPelapor($id_guru_pelapor, $id_tp)
    {
        $this->db->query("SELECT
                            COUNT(*) AS total,
                            SUM(CASE WHEN status = 'baru' THEN 1 ELSE 0 END) AS baru,
                            SUM(CASE WHEN status = 'proses' THEN 1 ELSE 0 END) AS proses,
                            SUM(CASE WHEN status = 'selesai' THEN 1 ELSE 0 END) AS selesai,
                            SUM(CASE WHEN status = 'dirujuk' THEN 1 ELSE 0 END) AS dirujuk
                          FROM bk_kasus
                          WHERE id_guru_pelapor = :id_guru AND id_tp = :id_tp");
        $this->db->bind('id_guru', $id_guru_pelapor);
        $this->db->bind('id_tp', $id_tp);
        return $this->db->single();
    }
}
