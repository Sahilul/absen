<?php
// File: app/models/SoalSts_model.php
// v1.31.0 - Model untuk fitur Buat Soal STS

class SoalSts_model
{
    private $db;

    public function __construct()
    {
        $this->db = new Database;
    }

    // ========================================
    // PENGATURAN
    // ========================================

    /**
     * Ambil semua pengaturan soal untuk kelas tertentu di semester aktif
     */
    public function getPengaturanByKelas($id_kelas, $id_semester)
    {
        $this->db->query("SELECT sp.*, m.nama_mapel, m.kode_mapel
            FROM soal_sts_pengaturan sp
            JOIN mapel m ON sp.id_mapel = m.id_mapel
            WHERE sp.id_kelas = :id_kelas AND sp.id_semester = :id_semester
            ORDER BY m.nama_mapel");
        $this->db->bind('id_kelas', $id_kelas);
        $this->db->bind('id_semester', $id_semester);
        return $this->db->resultSet();
    }

    /**
     * Ambil pengaturan soal untuk mapel + kelas + semester tertentu
     */
    public function getPengaturanByMapelKelas($id_mapel, $id_kelas, $id_semester)
    {
        $this->db->query("SELECT sp.*, m.nama_mapel, m.kode_mapel
            FROM soal_sts_pengaturan sp
            JOIN mapel m ON sp.id_mapel = m.id_mapel
            WHERE sp.id_mapel = :id_mapel AND sp.id_kelas = :id_kelas AND sp.id_semester = :id_semester
            LIMIT 1");
        $this->db->bind('id_mapel', $id_mapel);
        $this->db->bind('id_kelas', $id_kelas);
        $this->db->bind('id_semester', $id_semester);
        return $this->db->single();
    }

    /**
     * Ambil pengaturan berdasarkan ID
     */
    public function getPengaturanById($id)
    {
        $this->db->query("SELECT sp.*, m.nama_mapel, m.kode_mapel, k.nama_kelas, k.jenjang
            FROM soal_sts_pengaturan sp
            JOIN mapel m ON sp.id_mapel = m.id_mapel
            JOIN kelas k ON sp.id_kelas = k.id_kelas
            WHERE sp.id = :id
            LIMIT 1");
        $this->db->bind('id', $id);
        return $this->db->single();
    }

    /**
     * Simpan pengaturan baru (INSERT)
     */
    public function simpanPengaturan($data)
    {
        $this->db->query("INSERT INTO soal_sts_pengaturan 
            (id_mapel, id_kelas, id_semester, jumlah_pilihan_ganda, jumlah_essay, jumlah_isian_singkat, opsi_pg, waktu_pengerjaan, petunjuk_umum)
            VALUES 
            (:id_mapel, :id_kelas, :id_semester, :jumlah_pg, :jumlah_essay, :jumlah_isian, :opsi_pg, :waktu, :petunjuk)");
        $this->db->bind('id_mapel', $data['id_mapel']);
        $this->db->bind('id_kelas', $data['id_kelas']);
        $this->db->bind('id_semester', $data['id_semester']);
        $this->db->bind('jumlah_pg', $data['jumlah_pilihan_ganda'] ?? 0);
        $this->db->bind('jumlah_essay', $data['jumlah_essay'] ?? 0);
        $this->db->bind('jumlah_isian', $data['jumlah_isian_singkat'] ?? 0);
        $this->db->bind('opsi_pg', $data['opsi_pg'] ?? 4);
        $this->db->bind('waktu', $data['waktu_pengerjaan'] ?? 60);
        $this->db->bind('petunjuk', $data['petunjuk_umum'] ?? '');
        $this->db->execute();
        return $this->db->lastInsertId();
    }

    /**
     * Update pengaturan
     */
    public function updatePengaturan($data)
    {
        $this->db->query("UPDATE soal_sts_pengaturan SET
            jumlah_pilihan_ganda = :jumlah_pg,
            jumlah_essay = :jumlah_essay,
            jumlah_isian_singkat = :jumlah_isian,
            opsi_pg = :opsi_pg,
            waktu_pengerjaan = :waktu,
            petunjuk_umum = :petunjuk
            WHERE id = :id");
        $this->db->bind('jumlah_pg', $data['jumlah_pilihan_ganda'] ?? 0);
        $this->db->bind('jumlah_essay', $data['jumlah_essay'] ?? 0);
        $this->db->bind('jumlah_isian', $data['jumlah_isian_singkat'] ?? 0);
        $this->db->bind('opsi_pg', $data['opsi_pg'] ?? 4);
        $this->db->bind('waktu', $data['waktu_pengerjaan'] ?? 60);
        $this->db->bind('petunjuk', $data['petunjuk_umum'] ?? '');
        $this->db->bind('id', $data['id']);
        $this->db->execute();
        return $this->db->rowCount();
    }

    /**
     * Simpan atau update pengaturan (upsert) — untuk batch save
     */
    public function savePengaturan($data)
    {
        $existing = $this->getPengaturanByMapelKelas($data['id_mapel'], $data['id_kelas'], $data['id_semester']);
        if ($existing) {
            $data['id'] = $existing['id'];
            return $this->updatePengaturan($data);
        } else {
            return $this->simpanPengaturan($data);
        }
    }

    /**
     * Copy pengaturan dari satu kelas ke kelas lain
     * @param int $fromKelas ID kelas sumber
     * @param array $toKelasIds Array ID kelas tujuan
     * @param int $id_semester
     * @return int jumlah pengaturan yang berhasil dicopy
     */
    public function copyPengaturanToKelas($fromKelas, $toKelasIds, $id_semester)
    {
        $source = $this->getPengaturanByKelas($fromKelas, $id_semester);
        if (empty($source)) return 0;

        $count = 0;
        foreach ($toKelasIds as $id_kelas) {
            foreach ($source as $s) {
                $data = [
                    'id_mapel' => $s['id_mapel'],
                    'id_kelas' => $id_kelas,
                    'id_semester' => $id_semester,
                    'jumlah_pilihan_ganda' => $s['jumlah_pilihan_ganda'],
                    'jumlah_essay' => $s['jumlah_essay'],
                    'jumlah_isian_singkat' => $s['jumlah_isian_singkat'],
                    'opsi_pg' => $s['opsi_pg'],
                    'waktu_pengerjaan' => $s['waktu_pengerjaan'],
                    'petunjuk_umum' => $s['petunjuk_umum'],
                ];
                $this->savePengaturan($data);
                $count++;
            }
        }
        return $count;
    }

    /**
     * Bulk set pengaturan untuk beberapa kelas sekaligus
     * Hanya set mapel yang ada penugasan di masing-masing kelas
     */
    public function bulkSetPengaturan($kelasIds, $id_semester, $config)
    {
        $count = 0;
        require_once APPROOT . '/app/models/Penugasan_model.php';
        $penugasanModel = new Penugasan_model();

        foreach ($kelasIds as $id_kelas) {
            $mapelList = $penugasanModel->getMapelByKelas($id_kelas, $id_semester);
            foreach ($mapelList as $mapel) {
                $data = [
                    'id_mapel' => $mapel['id_mapel'],
                    'id_kelas' => $id_kelas,
                    'id_semester' => $id_semester,
                    'jumlah_pilihan_ganda' => $config['jumlah_pilihan_ganda'] ?? 20,
                    'jumlah_essay' => $config['jumlah_essay'] ?? 5,
                    'jumlah_isian_singkat' => $config['jumlah_isian_singkat'] ?? 0,
                    'opsi_pg' => $config['opsi_pg'] ?? 4,
                    'waktu_pengerjaan' => $config['waktu_pengerjaan'] ?? 60,
                    'petunjuk_umum' => $config['petunjuk_umum'] ?? '',
                ];
                $this->savePengaturan($data);
                $count++;
            }
        }
        return $count;
    }

    /**
     * Hapus pengaturan (dan soal terkait via CASCADE)
     */
    public function hapusPengaturan($id)
    {
        $this->db->query("DELETE FROM soal_sts_pengaturan WHERE id = :id");
        $this->db->bind('id', $id);
        $this->db->execute();
        return $this->db->rowCount();
    }

    // ========================================
    // SOAL
    // ========================================

    /**
     * Ambil semua soal berdasarkan id_pengaturan
     */
    public function getSoalByPengaturan($id_pengaturan)
    {
        $this->db->query("SELECT * FROM soal_sts 
            WHERE id_pengaturan = :id_pengaturan 
            ORDER BY tipe_soal, nomor_soal");
        $this->db->bind('id_pengaturan', $id_pengaturan);
        return $this->db->resultSet();
    }

    /**
     * Ambil soal berdasarkan mapel + kelas + semester
     */
    public function getSoalByMapelKelas($id_mapel, $id_kelas, $id_semester)
    {
        $this->db->query("SELECT s.*, m.nama_mapel 
            FROM soal_sts s
            JOIN mapel m ON s.id_mapel = m.id_mapel
            WHERE s.id_mapel = :id_mapel AND s.id_kelas = :id_kelas AND s.id_semester = :id_semester
            ORDER BY s.tipe_soal, s.nomor_soal");
        $this->db->bind('id_mapel', $id_mapel);
        $this->db->bind('id_kelas', $id_kelas);
        $this->db->bind('id_semester', $id_semester);
        return $this->db->resultSet();
    }

    /**
     * Ambil soal berdasarkan tipe
     */
    public function getSoalByTipe($id_pengaturan, $tipe_soal)
    {
        $this->db->query("SELECT * FROM soal_sts 
            WHERE id_pengaturan = :id_pengaturan AND tipe_soal = :tipe_soal
            ORDER BY nomor_soal");
        $this->db->bind('id_pengaturan', $id_pengaturan);
        $this->db->bind('tipe_soal', $tipe_soal);
        return $this->db->resultSet();
    }

    /**
     * Ambil soal berdasarkan ID
     */
    public function getSoalById($id_soal)
    {
        $this->db->query("SELECT s.*, m.nama_mapel, k.nama_kelas
            FROM soal_sts s
            JOIN mapel m ON s.id_mapel = m.id_mapel
            JOIN kelas k ON s.id_kelas = k.id_kelas
            WHERE s.id_soal = :id_soal LIMIT 1");
        $this->db->bind('id_soal', $id_soal);
        return $this->db->single();
    }

    /**
     * Simpan soal baru
     */
    public function simpanSoal($data)
    {
        $this->db->query("INSERT INTO soal_sts 
            (id_pengaturan, id_mapel, id_kelas, id_semester, tipe_soal, nomor_soal, pertanyaan, gambar_soal, opsi_a, opsi_b, opsi_c, opsi_d, opsi_e, kunci_jawaban, skor, created_by)
            VALUES 
            (:id_pengaturan, :id_mapel, :id_kelas, :id_semester, :tipe_soal, :nomor_soal, :pertanyaan, :gambar_soal, :opsi_a, :opsi_b, :opsi_c, :opsi_d, :opsi_e, :kunci_jawaban, :skor, :created_by)");
        $this->db->bind('id_pengaturan', $data['id_pengaturan']);
        $this->db->bind('id_mapel', $data['id_mapel']);
        $this->db->bind('id_kelas', $data['id_kelas']);
        $this->db->bind('id_semester', $data['id_semester']);
        $this->db->bind('tipe_soal', $data['tipe_soal']);
        $this->db->bind('nomor_soal', $data['nomor_soal']);
        $this->db->bind('pertanyaan', $data['pertanyaan']);
        $this->db->bind('gambar_soal', $data['gambar_soal'] ?? null);
        $this->db->bind('opsi_a', $data['opsi_a'] ?? null);
        $this->db->bind('opsi_b', $data['opsi_b'] ?? null);
        $this->db->bind('opsi_c', $data['opsi_c'] ?? null);
        $this->db->bind('opsi_d', $data['opsi_d'] ?? null);
        $this->db->bind('opsi_e', $data['opsi_e'] ?? null);
        $this->db->bind('kunci_jawaban', $data['kunci_jawaban'] ?? null);
        $this->db->bind('skor', $data['skor'] ?? 1);
        $this->db->bind('created_by', $data['created_by'] ?? null);
        $this->db->execute();
        return $this->db->lastInsertId();
    }

    /**
     * Update soal
     */
    public function updateSoal($data)
    {
        $this->db->query("UPDATE soal_sts SET
            pertanyaan = :pertanyaan,
            gambar_soal = :gambar_soal,
            opsi_a = :opsi_a,
            opsi_b = :opsi_b,
            opsi_c = :opsi_c,
            opsi_d = :opsi_d,
            opsi_e = :opsi_e,
            kunci_jawaban = :kunci_jawaban,
            skor = :skor
            WHERE id_soal = :id_soal");
        $this->db->bind('pertanyaan', $data['pertanyaan']);
        $this->db->bind('gambar_soal', $data['gambar_soal'] ?? null);
        $this->db->bind('opsi_a', $data['opsi_a'] ?? null);
        $this->db->bind('opsi_b', $data['opsi_b'] ?? null);
        $this->db->bind('opsi_c', $data['opsi_c'] ?? null);
        $this->db->bind('opsi_d', $data['opsi_d'] ?? null);
        $this->db->bind('opsi_e', $data['opsi_e'] ?? null);
        $this->db->bind('kunci_jawaban', $data['kunci_jawaban'] ?? null);
        $this->db->bind('skor', $data['skor'] ?? 1);
        $this->db->bind('id_soal', $data['id_soal']);
        $this->db->execute();
        return $this->db->rowCount();
    }

    /**
     * Hapus soal
     */
    public function hapusSoal($id_soal)
    {
        // Ambil info gambar dulu untuk cleanup
        $soal = $this->getSoalById($id_soal);
        if ($soal && !empty($soal['gambar_soal'])) {
            $imgPath = APPROOT . '/public/uploads/soal_sts/' . $soal['gambar_soal'];
            if (file_exists($imgPath)) {
                unlink($imgPath);
            }
        }

        $this->db->query("DELETE FROM soal_sts WHERE id_soal = :id_soal");
        $this->db->bind('id_soal', $id_soal);
        $this->db->execute();
        return $this->db->rowCount();
    }

    /**
     * Hapus semua soal berdasarkan pengaturan
     */
    public function hapusSoalByPengaturan($id_pengaturan)
    {
        // Cleanup gambar
        $soalList = $this->getSoalByPengaturan($id_pengaturan);
        foreach ($soalList as $soal) {
            if (!empty($soal['gambar_soal'])) {
                $imgPath = APPROOT . '/public/uploads/soal_sts/' . $soal['gambar_soal'];
                if (file_exists($imgPath)) {
                    unlink($imgPath);
                }
            }
        }

        $this->db->query("DELETE FROM soal_sts WHERE id_pengaturan = :id_pengaturan");
        $this->db->bind('id_pengaturan', $id_pengaturan);
        $this->db->execute();
        return $this->db->rowCount();
    }

    /**
     * Hitung jumlah soal berdasarkan pengaturan dan tipe
     */
    public function hitungSoalByPengaturan($id_pengaturan, $tipe_soal = null)
    {
        if ($tipe_soal) {
            $this->db->query("SELECT COUNT(*) as total FROM soal_sts 
                WHERE id_pengaturan = :id_pengaturan AND tipe_soal = :tipe_soal");
            $this->db->bind('id_pengaturan', $id_pengaturan);
            $this->db->bind('tipe_soal', $tipe_soal);
        } else {
            $this->db->query("SELECT COUNT(*) as total FROM soal_sts 
                WHERE id_pengaturan = :id_pengaturan");
            $this->db->bind('id_pengaturan', $id_pengaturan);
        }
        $result = $this->db->single();
        return (int)($result['total'] ?? 0);
    }

    /**
     * Ambil status kelengkapan soal per kelas
     * Return: array mapel dengan jumlah soal vs target
     */
    public function getStatusKelengkapan($id_kelas, $id_semester)
    {
        $this->db->query("SELECT 
                sp.id as id_pengaturan,
                sp.id_mapel,
                m.nama_mapel,
                sp.jumlah_pilihan_ganda,
                sp.jumlah_essay,
                sp.jumlah_isian_singkat,
                (sp.jumlah_pilihan_ganda + sp.jumlah_essay + sp.jumlah_isian_singkat) as total_target,
                COALESCE(sc.total_soal, 0) as total_soal,
                COALESCE(sc.total_pg, 0) as total_pg,
                COALESCE(sc.total_essay, 0) as total_essay,
                COALESCE(sc.total_isian, 0) as total_isian
            FROM soal_sts_pengaturan sp
            JOIN mapel m ON sp.id_mapel = m.id_mapel
            LEFT JOIN (
                SELECT id_pengaturan,
                    COUNT(*) as total_soal,
                    SUM(CASE WHEN tipe_soal = 'pg' THEN 1 ELSE 0 END) as total_pg,
                    SUM(CASE WHEN tipe_soal = 'essay' THEN 1 ELSE 0 END) as total_essay,
                    SUM(CASE WHEN tipe_soal = 'isian' THEN 1 ELSE 0 END) as total_isian
                FROM soal_sts
                GROUP BY id_pengaturan
            ) sc ON sp.id = sc.id_pengaturan
            WHERE sp.id_kelas = :id_kelas AND sp.id_semester = :id_semester
            ORDER BY m.nama_mapel");
        $this->db->bind('id_kelas', $id_kelas);
        $this->db->bind('id_semester', $id_semester);
        return $this->db->resultSet();
    }

    /**
     * Cek apakah soal dengan nomor tertentu sudah ada
     */
    public function cekSoalExist($id_pengaturan, $tipe_soal, $nomor_soal)
    {
        $this->db->query("SELECT id_soal FROM soal_sts 
            WHERE id_pengaturan = :id_pengaturan AND tipe_soal = :tipe_soal AND nomor_soal = :nomor_soal
            LIMIT 1");
        $this->db->bind('id_pengaturan', $id_pengaturan);
        $this->db->bind('tipe_soal', $tipe_soal);
        $this->db->bind('nomor_soal', $nomor_soal);
        $result = $this->db->single();
        return $result ? $result['id_soal'] : false;
    }

    /**
     * Upsert soal — simpan baru atau update jika sudah ada
     */
    public function upsertSoal($data)
    {
        $existingId = $this->cekSoalExist($data['id_pengaturan'], $data['tipe_soal'], $data['nomor_soal']);
        if ($existingId) {
            $data['id_soal'] = $existingId;
            return $this->updateSoal($data);
        } else {
            return $this->simpanSoal($data);
        }
    }
}
