<?php

/**
 * File: app/models/PSB_model.php
 * Model untuk operasi database PSB (Penerimaan Siswa Baru)
 */
class PSB_model
{
    private $db;

    public function __construct()
    {
        require_once APPROOT . '/app/core/Database.php';
        $this->db = new Database();
    }

    // =========================================================================
    // LEMBAGA METHODS
    // =========================================================================

    /**
     * Get semua lembaga aktif
     */
    public function getAllLembaga($aktifOnly = true)
    {
        $sql = 'SELECT * FROM psb_lembaga';
        if ($aktifOnly) {
            $sql .= ' WHERE aktif = 1';
        }
        $sql .= ' ORDER BY urutan ASC, nama_lembaga ASC';

        $this->db->query($sql);
        return $this->db->resultSet();
    }

    /**
     * Get lembaga by ID
     */
    public function getLembagaById($id)
    {
        $this->db->query('SELECT * FROM psb_lembaga WHERE id_lembaga = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    /**
     * Tambah lembaga baru
     */
    public function tambahLembaga($data)
    {
        $this->db->query('INSERT INTO psb_lembaga (kode_lembaga, nama_lembaga, jenjang, alamat, kuota_default, urutan, aktif) 
                          VALUES (:kode, :nama, :jenjang, :alamat, :kuota, :urutan, :aktif)');
        $this->db->bind(':kode', $data['kode_lembaga']);
        $this->db->bind(':nama', $data['nama_lembaga']);
        $this->db->bind(':jenjang', $data['jenjang']);
        $this->db->bind(':alamat', $data['alamat'] ?? null);
        $this->db->bind(':kuota', $data['kuota_default'] ?? 0);
        $this->db->bind(':urutan', $data['urutan'] ?? 0);
        $this->db->bind(':aktif', $data['aktif'] ?? 1);
        $this->db->execute();
        return $this->db->lastInsertId();
    }

    /**
     * Update lembaga
     */
    public function updateLembaga($id, $data)
    {
        $this->db->query('UPDATE psb_lembaga SET 
                          kode_lembaga = :kode, nama_lembaga = :nama, jenjang = :jenjang, 
                          alamat = :alamat, kuota_default = :kuota, urutan = :urutan, aktif = :aktif 
                          WHERE id_lembaga = :id');
        $this->db->bind(':kode', $data['kode_lembaga']);
        $this->db->bind(':nama', $data['nama_lembaga']);
        $this->db->bind(':jenjang', $data['jenjang']);
        $this->db->bind(':alamat', $data['alamat'] ?? null);
        $this->db->bind(':kuota', $data['kuota_default'] ?? 0);
        $this->db->bind(':urutan', $data['urutan'] ?? 0);
        $this->db->bind(':aktif', $data['aktif'] ?? 1);
        $this->db->bind(':id', $id);
        $this->db->execute();
        return $this->db->rowCount();
    }

    /**
     * Hapus lembaga
     */
    public function hapusLembaga($id)
    {
        $this->db->query('DELETE FROM psb_lembaga WHERE id_lembaga = :id');
        $this->db->bind(':id', $id);
        $this->db->execute();
        return $this->db->rowCount();
    }

    // =========================================================================
    // JALUR METHODS
    // =========================================================================

    /**
     * Get semua jalur aktif
     */
    public function getAllJalur($aktifOnly = true)
    {
        $sql = 'SELECT * FROM psb_jalur';
        if ($aktifOnly) {
            $sql .= ' WHERE aktif = 1';
        }
        $sql .= ' ORDER BY urutan ASC, nama_jalur ASC';

        $this->db->query($sql);
        return $this->db->resultSet();
    }

    /**
     * Get jalur yang aktif di periode-periode aktif (memiliki kuota > 0)
     */
    public function getJalurAktifDiPeriode()
    {
        $this->db->query('SELECT j.*, 
                            SUM(COALESCE(kj.kuota, 0)) as total_kuota,
                            SUM(COALESCE((SELECT COUNT(*) FROM psb_pendaftar pd 
                                WHERE pd.id_jalur = j.id_jalur 
                                AND pd.id_periode = p.id_periode), 0)) as total_terisi
                          FROM psb_jalur j
                          INNER JOIN psb_kuota_jalur kj ON j.id_jalur = kj.id_jalur
                          INNER JOIN psb_periode p ON kj.id_periode = p.id_periode
                          WHERE j.aktif = 1 
                          AND p.status = "aktif"
                          AND kj.kuota > 0
                          AND CURDATE() BETWEEN p.tanggal_buka AND p.tanggal_tutup
                          GROUP BY j.id_jalur
                          ORDER BY j.urutan ASC, j.nama_jalur ASC');
        return $this->db->resultSet();
    }

    /**
     * Get jalur by ID
     */
    public function getJalurById($id)
    {
        $this->db->query('SELECT * FROM psb_jalur WHERE id_jalur = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    /**
     * Tambah jalur baru
     */
    public function tambahJalur($data)
    {
        $this->db->query('INSERT INTO psb_jalur (kode_jalur, nama_jalur, deskripsi, persyaratan, urutan, aktif) 
                          VALUES (:kode, :nama, :deskripsi, :persyaratan, :urutan, :aktif)');
        $this->db->bind(':kode', $data['kode_jalur']);
        $this->db->bind(':nama', $data['nama_jalur']);
        $this->db->bind(':deskripsi', $data['deskripsi'] ?? null);
        $this->db->bind(':persyaratan', $data['persyaratan'] ?? null);
        $this->db->bind(':urutan', $data['urutan'] ?? 0);
        $this->db->bind(':aktif', $data['aktif'] ?? 1);
        $this->db->execute();
        return $this->db->lastInsertId();
    }

    /**
     * Update jalur
     */
    public function updateJalur($id, $data)
    {
        $this->db->query('UPDATE psb_jalur SET 
                          kode_jalur = :kode, nama_jalur = :nama, deskripsi = :deskripsi, 
                          persyaratan = :persyaratan, urutan = :urutan, aktif = :aktif 
                          WHERE id_jalur = :id');
        $this->db->bind(':kode', $data['kode_jalur']);
        $this->db->bind(':nama', $data['nama_jalur']);
        $this->db->bind(':deskripsi', $data['deskripsi'] ?? null);
        $this->db->bind(':persyaratan', $data['persyaratan'] ?? null);
        $this->db->bind(':urutan', $data['urutan'] ?? 0);
        $this->db->bind(':aktif', $data['aktif'] ?? 1);
        $this->db->bind(':id', $id);
        $this->db->execute();
        return $this->db->rowCount();
    }

    /**
     * Hapus jalur
     */
    public function hapusJalur($id)
    {
        $this->db->query('DELETE FROM psb_jalur WHERE id_jalur = :id');
        $this->db->bind(':id', $id);
        $this->db->execute();
        return $this->db->rowCount();
    }

    // =========================================================================
    // PERIODE METHODS
    // =========================================================================

    /**
     * Get semua periode
     */
    public function getAllPeriode()
    {
        $this->db->query('SELECT p.*, l.nama_lembaga, l.jenjang, tp.nama_tp,
                          (SELECT COUNT(*) FROM psb_pendaftar WHERE id_periode = p.id_periode) as total_pendaftar
                          FROM psb_periode p
                          JOIN psb_lembaga l ON p.id_lembaga = l.id_lembaga
                          JOIN tp ON p.id_tp = tp.id_tp
                          ORDER BY p.created_at DESC');
        return $this->db->resultSet();
    }

    /**
     * Get periode aktif (optional: per lembaga)
     */
    public function getPeriodeAktif($id_lembaga = null)
    {
        $sql = "SELECT p.*, l.nama_lembaga, l.jenjang, tp.nama_tp
                FROM psb_periode p
                JOIN psb_lembaga l ON p.id_lembaga = l.id_lembaga
                JOIN tp ON p.id_tp = tp.id_tp
                WHERE p.status = 'aktif' 
                AND CURDATE() BETWEEN p.tanggal_buka AND p.tanggal_tutup";

        if ($id_lembaga) {
            $sql .= ' AND p.id_lembaga = :id_lembaga';
        }
        $sql .= ' ORDER BY l.urutan ASC';

        $this->db->query($sql);
        if ($id_lembaga) {
            $this->db->bind(':id_lembaga', $id_lembaga);
        }
        return $this->db->resultSet();
    }

    /**
     * Get periode by ID
     */
    public function getPeriodeById($id)
    {
        $this->db->query('SELECT p.*, l.nama_lembaga, l.jenjang, tp.nama_tp
                          FROM psb_periode p
                          JOIN psb_lembaga l ON p.id_lembaga = l.id_lembaga
                          JOIN tp ON p.id_tp = tp.id_tp
                          WHERE p.id_periode = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    /**
     * Tambah periode baru
     */
    public function tambahPeriode($data)
    {
        $this->db->query('INSERT INTO psb_periode (id_lembaga, nama_periode, id_tp, tanggal_buka, tanggal_tutup, kuota, biaya_pendaftaran, status, keterangan) 
                          VALUES (:id_lembaga, :nama, :id_tp, :tgl_buka, :tgl_tutup, :kuota, :biaya, :status, :keterangan)');
        $this->db->bind(':id_lembaga', $data['id_lembaga']);
        $this->db->bind(':nama', $data['nama_periode']);
        $this->db->bind(':id_tp', $data['id_tp']);
        $this->db->bind(':tgl_buka', $data['tanggal_buka']);
        $this->db->bind(':tgl_tutup', $data['tanggal_tutup']);
        $this->db->bind(':kuota', $data['kuota'] ?? 0);
        $this->db->bind(':biaya', $data['biaya_pendaftaran'] ?? 0);
        $this->db->bind(':status', $data['status'] ?? 'draft');
        $this->db->bind(':keterangan', $data['keterangan'] ?? null);
        $this->db->execute();
        return $this->db->lastInsertId();
    }

    /**
     * Update periode
     */
    public function updatePeriode($id, $data)
    {
        $this->db->query('UPDATE psb_periode SET 
                          id_lembaga = :id_lembaga, nama_periode = :nama, id_tp = :id_tp, 
                          tanggal_buka = :tgl_buka, tanggal_tutup = :tgl_tutup, 
                          kuota = :kuota, biaya_pendaftaran = :biaya, status = :status, keterangan = :keterangan 
                          WHERE id_periode = :id');
        $this->db->bind(':id_lembaga', $data['id_lembaga']);
        $this->db->bind(':nama', $data['nama_periode']);
        $this->db->bind(':id_tp', $data['id_tp']);
        $this->db->bind(':tgl_buka', $data['tanggal_buka']);
        $this->db->bind(':tgl_tutup', $data['tanggal_tutup']);
        $this->db->bind(':kuota', $data['kuota'] ?? 0);
        $this->db->bind(':biaya', $data['biaya_pendaftaran'] ?? 0);
        $this->db->bind(':status', $data['status'] ?? 'draft');
        $this->db->bind(':keterangan', $data['keterangan'] ?? null);
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    /**
     * Hapus periode
     */
    public function hapusPeriode($id)
    {
        $this->db->query('DELETE FROM psb_periode WHERE id_periode = :id');
        $this->db->bind(':id', $id);
        $this->db->execute();
        return $this->db->rowCount();
    }

    /**
     * Update kuota per jalur
     */
    public function updateKuotaJalur($id_periode, $id_jalur, $kuota)
    {
        $this->db->query('INSERT INTO psb_kuota_jalur (id_periode, id_jalur, kuota) 
                          VALUES (:id_periode, :id_jalur, :kuota)
                          ON DUPLICATE KEY UPDATE kuota = :kuota2');
        $this->db->bind(':id_periode', $id_periode);
        $this->db->bind(':id_jalur', $id_jalur);
        $this->db->bind(':kuota', $kuota);
        $this->db->bind(':kuota2', $kuota);
        return $this->db->execute();
    }

    /**
     * Delete kuota jalur (when jalur is disabled for a periode)
     */
    public function deleteKuotaJalur($id_periode, $id_jalur)
    {
        $this->db->query('DELETE FROM psb_kuota_jalur WHERE id_periode = :id_periode AND id_jalur = :id_jalur');
        $this->db->bind(':id_periode', $id_periode);
        $this->db->bind(':id_jalur', $id_jalur);
        return $this->db->execute();
    }

    /**
     * Get kuota per jalur untuk periode
     */
    public function getKuotaJalur($id_periode)
    {
        $this->db->query('SELECT kj.*, j.nama_jalur, j.kode_jalur,
                          (SELECT COUNT(*) FROM psb_pendaftar WHERE id_periode = :id_periode2 AND id_jalur = j.id_jalur) as terisi
                          FROM psb_jalur j
                          LEFT JOIN psb_kuota_jalur kj ON j.id_jalur = kj.id_jalur AND kj.id_periode = :id_periode
                          WHERE j.aktif = 1
                          ORDER BY j.urutan ASC');
        $this->db->bind(':id_periode', $id_periode);
        $this->db->bind(':id_periode2', $id_periode);
        return $this->db->resultSet();
    }

    // =========================================================================
    // PENDAFTAR METHODS
    // =========================================================================

    /**
     * Generate nomor pendaftaran unik
     */
    public function generateNoPendaftaran($id_periode)
    {
        $periode = $this->getPeriodeById($id_periode);
        if (!$periode)
            return null;

        // Format: [KODE_LEMBAGA][TAHUN][URUTAN 4 DIGIT]
        // Contoh: SMP2024-0001
        $lembaga = $this->getLembagaById($periode['id_lembaga']);
        $tahun = date('Y');

        // Cari urutan terakhir
        $this->db->query("SELECT no_pendaftaran FROM psb_pendaftar 
                          WHERE id_periode = :id_periode 
                          ORDER BY id_pendaftar DESC LIMIT 1");
        $this->db->bind(':id_periode', $id_periode);
        $last = $this->db->single();

        if ($last) {
            // Extract nomor urut dari format XXX2024-0001
            preg_match('/(\d{4})$/', $last['no_pendaftaran'], $matches);
            $urutan = isset($matches[1]) ? intval($matches[1]) + 1 : 1;
        } else {
            $urutan = 1;
        }

        return $lembaga['kode_lembaga'] . $tahun . '-' . str_pad($urutan, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Tambah pendaftar baru
     */
    public function tambahPendaftar($data)
    {
        // Generate nomor pendaftaran
        $no_pendaftaran = $this->generateNoPendaftaran($data['id_periode']);
        if (!$no_pendaftaran)
            return false;

        $this->db->query('INSERT INTO psb_pendaftar (
            id_periode, id_jalur, no_pendaftaran, nisn, nik, nama_lengkap, jenis_kelamin,
            tempat_lahir, tanggal_lahir, agama, alamat, rt, rw, kelurahan, kecamatan, 
            kota, provinsi, kode_pos, no_hp, email, nama_ayah, pekerjaan_ayah, no_hp_ayah,
            nama_ibu, pekerjaan_ibu, no_hp_ibu, nama_wali, hubungan_wali, no_hp_wali,
            asal_sekolah, npsn_asal, alamat_sekolah_asal, tahun_lulus, foto, status
        ) VALUES (
            :id_periode, :id_jalur, :no_pendaftaran, :nisn, :nik, :nama_lengkap, :jenis_kelamin,
            :tempat_lahir, :tanggal_lahir, :agama, :alamat, :rt, :rw, :kelurahan, :kecamatan,
            :kota, :provinsi, :kode_pos, :no_hp, :email, :nama_ayah, :pekerjaan_ayah, :no_hp_ayah,
            :nama_ibu, :pekerjaan_ibu, :no_hp_ibu, :nama_wali, :hubungan_wali, :no_hp_wali,
            :asal_sekolah, :npsn_asal, :alamat_sekolah_asal, :tahun_lulus, :foto, "pending"
        )');

        $this->db->bind(':id_periode', $data['id_periode']);
        $this->db->bind(':id_jalur', $data['id_jalur']);
        $this->db->bind(':no_pendaftaran', $no_pendaftaran);
        $this->db->bind(':nisn', $data['nisn'] ?? null);
        $this->db->bind(':nik', $data['nik'] ?? null);
        $this->db->bind(':nama_lengkap', $data['nama_lengkap']);
        $this->db->bind(':jenis_kelamin', $data['jenis_kelamin']);
        $this->db->bind(':tempat_lahir', $data['tempat_lahir'] ?? null);
        $this->db->bind(':tanggal_lahir', $data['tanggal_lahir'] ?? null);
        $this->db->bind(':agama', $data['agama'] ?? null);
        $this->db->bind(':alamat', $data['alamat'] ?? null);
        $this->db->bind(':rt', $data['rt'] ?? null);
        $this->db->bind(':rw', $data['rw'] ?? null);
        $this->db->bind(':kelurahan', $data['kelurahan'] ?? null);
        $this->db->bind(':kecamatan', $data['kecamatan'] ?? null);
        $this->db->bind(':kota', $data['kota'] ?? null);
        $this->db->bind(':provinsi', $data['provinsi'] ?? null);
        $this->db->bind(':kode_pos', $data['kode_pos'] ?? null);
        $this->db->bind(':no_hp', $data['no_hp'] ?? null);
        $this->db->bind(':email', $data['email'] ?? null);
        $this->db->bind(':nama_ayah', $data['nama_ayah'] ?? null);
        $this->db->bind(':pekerjaan_ayah', $data['pekerjaan_ayah'] ?? null);
        $this->db->bind(':no_hp_ayah', $data['no_hp_ayah'] ?? null);
        $this->db->bind(':nama_ibu', $data['nama_ibu'] ?? null);
        $this->db->bind(':pekerjaan_ibu', $data['pekerjaan_ibu'] ?? null);
        $this->db->bind(':no_hp_ibu', $data['no_hp_ibu'] ?? null);
        $this->db->bind(':nama_wali', $data['nama_wali'] ?? null);
        $this->db->bind(':hubungan_wali', $data['hubungan_wali'] ?? null);
        $this->db->bind(':no_hp_wali', $data['no_hp_wali'] ?? null);
        $this->db->bind(':asal_sekolah', $data['asal_sekolah'] ?? null);
        $this->db->bind(':npsn_asal', $data['npsn_asal'] ?? null);
        $this->db->bind(':alamat_sekolah_asal', $data['alamat_sekolah_asal'] ?? null);
        $this->db->bind(':tahun_lulus', $data['tahun_lulus'] ?? null);
        $this->db->bind(':foto', $data['foto'] ?? null);

        if ($this->db->execute()) {
            return [
                'id' => $this->db->lastInsertId(),
                'no_pendaftaran' => $no_pendaftaran
            ];
        }
        return false;
    }

    /**
     * Get pendaftar by periode
     */
    public function getPendaftarByPeriode($id_periode, $status = null)
    {
        $sql = 'SELECT p.*, j.nama_jalur, j.kode_jalur
                FROM psb_pendaftar p
                JOIN psb_jalur j ON p.id_jalur = j.id_jalur
                WHERE p.id_periode = :id_periode';

        if ($status) {
            $sql .= ' AND p.status = :status';
        }
        $sql .= ' ORDER BY p.tanggal_daftar DESC';

        $this->db->query($sql);
        $this->db->bind(':id_periode', $id_periode);
        if ($status) {
            $this->db->bind(':status', $status);
        }
        return $this->db->resultSet();
    }

    /**
     * Get pendaftar by ID
     */
    public function getPendaftarById($id)
    {
        $this->db->query('SELECT p.*, j.nama_jalur, j.kode_jalur, 
                          per.nama_periode, l.nama_lembaga, l.jenjang,
                          a.no_wa as akun_no_wa, a.nama_lengkap as akun_nama
                          FROM psb_pendaftar p
                          JOIN psb_jalur j ON p.id_jalur = j.id_jalur
                          JOIN psb_periode per ON p.id_periode = per.id_periode
                          JOIN psb_lembaga l ON per.id_lembaga = l.id_lembaga
                          LEFT JOIN psb_akun a ON p.id_akun = a.id_akun
                          WHERE p.id_pendaftar = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    /**
     * Get pendaftar by nomor pendaftaran
     */
    public function getPendaftarByNo($no)
    {
        $this->db->query('SELECT p.*, j.nama_jalur, j.kode_jalur, 
                          per.nama_periode, l.nama_lembaga, l.jenjang
                          FROM psb_pendaftar p
                          JOIN psb_jalur j ON p.id_jalur = j.id_jalur
                          JOIN psb_periode per ON p.id_periode = per.id_periode
                          JOIN psb_lembaga l ON per.id_lembaga = l.id_lembaga
                          WHERE p.no_pendaftaran = :no');
        $this->db->bind(':no', $no);
        return $this->db->single();
    }

    /**
     * Update status pendaftar
     * Note: Status 'ditolak' will trigger auto-delete of the registration
     */
    public function updateStatusPendaftar($id, $status, $catatan = null, $verified_by = null)
    {
        // If status is 'ditolak', auto-delete the registration
        if ($status == 'ditolak') {
            // First, optionally log the rejection before deleting
            error_log("PSB: Pendaftaran ID $id ditolak dan dihapus otomatis. Catatan: $catatan");

            // Delete the registration
            return $this->hapusPendaftaran($id);
        }

        $sql = 'UPDATE psb_pendaftar SET status = :status, catatan_admin = :catatan';

        if ($status == 'verifikasi' || $status == 'revisi') {
            $sql .= ', tanggal_verifikasi = NOW(), verified_by = :verified_by';
        }
        if ($status == 'diterima') {
            $sql .= ', tanggal_keputusan = NOW()';
        }
        $sql .= ' WHERE id_pendaftar = :id';

        $this->db->query($sql);
        $this->db->bind(':status', $status);
        $this->db->bind(':catatan', $catatan);
        $this->db->bind(':id', $id);
        if ($status == 'verifikasi' || $status == 'revisi') {
            $this->db->bind(':verified_by', $verified_by);
        }
        $this->db->execute();
        return $this->db->rowCount();
    }

    /**
     * Update kelas diterima
     */
    public function updateKelasDiterima($id, $id_kelas)
    {
        $this->db->query('UPDATE psb_pendaftar SET id_kelas_diterima = :id_kelas WHERE id_pendaftar = :id');
        $this->db->bind(':id_kelas', $id_kelas);
        $this->db->bind(':id', $id);
        $this->db->execute();
        return $this->db->rowCount();
    }

    /**
     * Konversi pendaftar ke data siswa
     */
    public function konversiKeDataSiswa($id_pendaftar)
    {
        $pendaftar = $this->getPendaftarById($id_pendaftar);
        if (!$pendaftar || $pendaftar['status'] != 'diterima') {
            return ['success' => false, 'error' => 'Pendaftar tidak valid atau belum diterima'];
        }

        // Cek apakah NISN sudah ada
        if (!empty($pendaftar['nisn'])) {
            $this->db->query('SELECT id_siswa FROM siswa WHERE nisn = :nisn');
            $this->db->bind(':nisn', $pendaftar['nisn']);
            $existing = $this->db->single();
            if ($existing) {
                return ['success' => false, 'error' => 'NISN sudah terdaftar sebagai siswa'];
            }
        }

        try {
            $this->db->beginTransaction();

            // Insert ke tabel siswa
            $this->db->query('INSERT INTO siswa (nisn, nama_siswa, jenis_kelamin, tgl_lahir, tempat_lahir, 
                              alamat, no_wa, email, ayah_kandung, ibu_kandung, status_siswa) 
                              VALUES (:nisn, :nama, :jk, :tgl_lahir, :tempat_lahir, :alamat, :no_wa, :email, :ayah, :ibu, "aktif")');
            $this->db->bind(':nisn', $pendaftar['nisn'] ?? '');
            $this->db->bind(':nama', $pendaftar['nama_lengkap']);
            $this->db->bind(':jk', $pendaftar['jenis_kelamin']);
            $this->db->bind(':tgl_lahir', $pendaftar['tanggal_lahir']);
            $this->db->bind(':tempat_lahir', $pendaftar['tempat_lahir']);
            $this->db->bind(':alamat', $pendaftar['alamat']);
            $this->db->bind(':no_wa', $pendaftar['no_hp']);
            $this->db->bind(':email', $pendaftar['email']);
            $this->db->bind(':ayah', $pendaftar['nama_ayah']);
            $this->db->bind(':ibu', $pendaftar['nama_ibu']);
            $this->db->execute();

            $id_siswa = $this->db->lastInsertId();

            // Update pendaftar dengan id_siswa
            $this->db->query('UPDATE psb_pendaftar SET id_siswa = :id_siswa, status = "selesai" WHERE id_pendaftar = :id');
            $this->db->bind(':id_siswa', $id_siswa);
            $this->db->bind(':id', $id_pendaftar);
            $this->db->execute();

            // Buat akun user untuk siswa
            $username = $pendaftar['nisn'] ?? $pendaftar['no_pendaftaran'];
            $password = substr($pendaftar['nisn'] ?? $pendaftar['no_pendaftaran'], -6);
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $this->db->query('INSERT INTO users (username, password, password_plain, nama_lengkap, role, id_ref, status) 
                              VALUES (:username, :password, :password_plain, :nama, "siswa", :id_ref, "aktif")');
            $this->db->bind(':username', $username);
            $this->db->bind(':password', $hashedPassword);
            $this->db->bind(':password_plain', $password);
            $this->db->bind(':nama', $pendaftar['nama_lengkap']);
            $this->db->bind(':id_ref', $id_siswa);
            $this->db->execute();

            // Assign ke kelas jika sudah ditentukan
            if (!empty($pendaftar['id_kelas_diterima'])) {
                // Get id_tp dari periode
                $periode = $this->getPeriodeById($pendaftar['id_periode']);

                $this->db->query('INSERT INTO keanggotaan_kelas (id_siswa, id_kelas, id_tp) VALUES (:id_siswa, :id_kelas, :id_tp)');
                $this->db->bind(':id_siswa', $id_siswa);
                $this->db->bind(':id_kelas', $pendaftar['id_kelas_diterima']);
                $this->db->bind(':id_tp', $periode['id_tp']);
                $this->db->execute();
            }

            $this->db->commit();

            return [
                'success' => true,
                'id_siswa' => $id_siswa,
                'username' => $username,
                'password' => $password
            ];

        } catch (Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // =========================================================================
    // STATISTIK METHODS
    // =========================================================================

    /**
     * Get statistik dashboard PSB
     */
    public function getStatistikDashboard()
    {
        $stats = [];

        // Total pendaftar semua periode aktif
        $this->db->query("SELECT COUNT(*) as total FROM psb_pendaftar p
                          JOIN psb_periode per ON p.id_periode = per.id_periode
                          WHERE per.status = 'aktif'");
        $stats['total_pendaftar'] = $this->db->single()['total'];

        // Per status
        $this->db->query("SELECT p.status, COUNT(*) as total FROM psb_pendaftar p
                          JOIN psb_periode per ON p.id_periode = per.id_periode
                          WHERE per.status = 'aktif'
                          GROUP BY p.status");
        $statusData = $this->db->resultSet();
        $stats['pending'] = 0;
        $stats['verifikasi'] = 0;
        $stats['diterima'] = 0;
        $stats['ditolak'] = 0;
        foreach ($statusData as $row) {
            $stats[$row['status']] = $row['total'];
        }

        // Periode aktif
        $this->db->query("SELECT COUNT(*) as total FROM psb_periode WHERE status = 'aktif'");
        $stats['periode_aktif'] = $this->db->single()['total'];

        // Pendaftar hari ini
        $this->db->query("SELECT COUNT(*) as total FROM psb_pendaftar WHERE DATE(tanggal_daftar) = CURDATE()");
        $stats['pendaftar_hari_ini'] = $this->db->single()['total'];

        return $stats;
    }

    /**
     * Get statistik per periode
     */
    public function getStatistikPeriode($id_periode)
    {
        $stats = [];

        // Total pendaftar
        $this->db->query("SELECT COUNT(*) as total FROM psb_pendaftar WHERE id_periode = :id");
        $this->db->bind(':id', $id_periode);
        $stats['total'] = $this->db->single()['total'];

        // Per status
        $this->db->query("SELECT status, COUNT(*) as total FROM psb_pendaftar WHERE id_periode = :id GROUP BY status");
        $this->db->bind(':id', $id_periode);
        $statusData = $this->db->resultSet();
        foreach ($statusData as $row) {
            $stats[$row['status']] = $row['total'];
        }

        // Per jalur
        $this->db->query("SELECT j.nama_jalur, COUNT(p.id_pendaftar) as total 
                          FROM psb_jalur j 
                          LEFT JOIN psb_pendaftar p ON j.id_jalur = p.id_jalur AND p.id_periode = :id
                          WHERE j.aktif = 1
                          GROUP BY j.id_jalur
                          ORDER BY j.urutan");
        $this->db->bind(':id', $id_periode);
        $stats['per_jalur'] = $this->db->resultSet();

        return $stats;
    }

    // =========================================================================
    // PENGATURAN METHODS
    // =========================================================================

    /**
     * Get pengaturan PSB
     */
    public function getPengaturan()
    {
        try {
            $this->db->query('SELECT * FROM psb_pengaturan WHERE id = 1');
            $result = $this->db->single();

            // Jika tidak ada data, insert default
            if (!$result) {
                $this->db->query("INSERT INTO psb_pengaturan (id, judul_halaman, deskripsi) VALUES (1, 'Penerimaan Siswa Baru', 'Selamat datang di portal PSB') ON DUPLICATE KEY UPDATE id = id");
                $this->db->execute();

                // Query ulang
                $this->db->query('SELECT * FROM psb_pengaturan WHERE id = 1');
                $result = $this->db->single();
            }

            return $result;

        } catch (PDOException $e) {
            // Table tidak ada, coba buat table  
            error_log("PSB_model - Table psb_pengaturan not found, creating...");

            try {
                $this->db->query("CREATE TABLE IF NOT EXISTS `psb_pengaturan` (
                    `id` int NOT NULL AUTO_INCREMENT,
                    `judul_halaman` varchar(255) DEFAULT 'Penerimaan Siswa Baru',
                    `deskripsi` text,
                    `syarat_pendaftaran` text,
                    `alur_pendaftaran` text,
                    `kontak_info` text,
                    `wa_gateway_url` varchar(255) DEFAULT 'https://api.fonnte.com/send',
                    `wa_gateway_token` varchar(255) DEFAULT NULL,
                    `brosur_gambar` varchar(255) DEFAULT NULL,
                    `tentang_sekolah` text,
                    `keunggulan` text,
                   `visi_misi` text,
                    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
                $this->db->execute();

                // Insert default data
                $this->db->query("INSERT INTO psb_pengaturan (id, judul_halaman, deskripsi) VALUES (1, 'Penerimaan Siswa Baru', 'Selamat datang di portal Penerimaan Siswa Baru')");
                $this->db->execute();

                // Return default data
                return [
                    'id' => 1,
                    'judul_halaman' => 'Penerimaan Siswa Baru',
                    'deskripsi' => 'Selamat datang di portal Penerimaan Siswa Baru'
                ];

            } catch (Exception $createError) {
                error_log("PSB_model - Failed to create table: " . $createError->getMessage());
                // Return default fallback
                return [
                    'id' => 1,
                    'judul_halaman' => 'Penerimaan Siswa Baru',
                    'deskripsi' => 'Portal PSB'
                ];
            }
        }
    }

    /**
     * Update pengaturan PSB
     */
    public function updatePengaturan($data)
    {
        $this->db->query('UPDATE psb_pengaturan SET 
                          judul_halaman = :judul, deskripsi = :deskripsi, 
                          syarat_pendaftaran = :syarat, alur_pendaftaran = :alur, kontak_info = :kontak,
                          wa_gateway_url = :wa_url, wa_gateway_token = :wa_token,
                          brosur_gambar = :brosur, tentang_sekolah = :tentang,
                          keunggulan = :keunggulan, visi_misi = :visi
                          WHERE id = 1');
        $this->db->bind(':judul', $data['judul_halaman']);
        $this->db->bind(':deskripsi', $data['deskripsi'] ?? null);
        $this->db->bind(':syarat', $data['syarat_pendaftaran'] ?? null);
        $this->db->bind(':alur', $data['alur_pendaftaran'] ?? null);
        $this->db->bind(':kontak', $data['kontak_info'] ?? null);
        $this->db->bind(':wa_url', $data['wa_gateway_url'] ?? null);
        $this->db->bind(':wa_token', $data['wa_gateway_token'] ?? null);
        $this->db->bind(':brosur', $data['brosur_gambar'] ?? null);
        $this->db->bind(':tentang', $data['tentang_sekolah'] ?? null);
        $this->db->bind(':keunggulan', $data['keunggulan'] ?? null);
        $this->db->bind(':visi', $data['visi_misi'] ?? null);
        $this->db->execute();
        return $this->db->rowCount();
    }

    // =========================================================================
    // AKUN CALON SISWA METHODS
    // =========================================================================

    /**
     * Cek apakah NISN sudah terdaftar
     */
    public function isNisnRegistered($nisn)
    {
        $this->db->query('SELECT id_akun FROM psb_akun WHERE nisn = :nisn');
        $this->db->bind(':nisn', $nisn);
        return $this->db->single() ? true : false;
    }

    /**
     * Buat akun baru
     */
    public function createAkun($data)
    {
        $this->db->query('INSERT INTO psb_akun (nisn, nama_lengkap, no_wa, password) 
                          VALUES (:nisn, :nama, :no_wa, :password)');
        $this->db->bind(':nisn', $data['nisn']);
        $this->db->bind(':nama', $data['nama_lengkap']);
        $this->db->bind(':no_wa', $data['no_wa']);
        $this->db->bind(':password', password_hash($data['password'], PASSWORD_DEFAULT));
        $this->db->execute();
        return $this->db->lastInsertId();
    }

    /**
     * Login akun dengan NISN & password
     */
    public function loginAkun($nisn, $password)
    {
        $this->db->query('SELECT * FROM psb_akun WHERE nisn = :nisn AND status = "aktif"');
        $this->db->bind(':nisn', $nisn);
        $akun = $this->db->single();

        if ($akun && password_verify($password, $akun['password'])) {
            // Update last login
            $this->db->query('UPDATE psb_akun SET last_login = NOW() WHERE id_akun = :id');
            $this->db->bind(':id', $akun['id_akun']);
            $this->db->execute();
            return $akun;
        }
        return false;
    }

    /**
     * Get akun by ID
     */
    public function getAkunById($id)
    {
        $this->db->query('SELECT * FROM psb_akun WHERE id_akun = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    /**
     * Get akun by NISN
     */
    public function getAkunByNisn($nisn)
    {
        $this->db->query('SELECT * FROM psb_akun WHERE nisn = :nisn');
        $this->db->bind(':nisn', $nisn);
        return $this->db->single();
    }

    /**
     * Get akun by No WA
     */
    public function getAkunByNoWa($noWa)
    {
        // Normalisasi nomor
        $noWa = preg_replace('/[^0-9]/', '', $noWa);
        if (substr($noWa, 0, 1) === '0') {
            $noWa = '62' . substr($noWa, 1);
        }

        $this->db->query('SELECT * FROM psb_akun WHERE REPLACE(REPLACE(no_wa, " ", ""), "-", "") LIKE :no_wa');
        $this->db->bind(':no_wa', '%' . substr($noWa, -10) . '%');
        return $this->db->single();
    }

    /**
     * Generate dan simpan reset token
     */
    public function generateResetToken($idAkun)
    {
        $token = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiry = date('Y-m-d H:i:s', strtotime('+15 minutes'));

        $this->db->query('UPDATE psb_akun SET reset_token = :token, reset_token_expiry = :expiry WHERE id_akun = :id');
        $this->db->bind(':token', $token);
        $this->db->bind(':expiry', $expiry);
        $this->db->bind(':id', $idAkun);
        $this->db->execute();

        return $token;
    }

    /**
     * Verifikasi reset token
     */
    public function verifyResetToken($nisn, $token)
    {
        // Normalize token - remove whitespace and ensure it's a string
        $token = trim((string) $token);

        // Debug log
        error_log("PSB Reset Token Verify - NISN: $nisn, Token Input: '$token'");

        // First check what token is stored
        $this->db->query('SELECT reset_token, reset_token_expiry, NOW() as server_now FROM psb_akun WHERE nisn = :nisn');
        $this->db->bind(':nisn', $nisn);
        $check = $this->db->single();

        if ($check) {
            error_log("PSB Reset Token DB - Stored: '{$check['reset_token']}', Expiry: {$check['reset_token_expiry']}, Now: {$check['server_now']}");
        } else {
            error_log("PSB Reset Token - No account found for NISN: $nisn");
        }

        $this->db->query('SELECT * FROM psb_akun 
                          WHERE nisn = :nisn AND reset_token = :token AND reset_token_expiry > NOW()');
        $this->db->bind(':nisn', $nisn);
        $this->db->bind(':token', $token);
        return $this->db->single();
    }

    /**
     * Reset password
     */
    public function resetPassword($idAkun, $newPassword)
    {
        $this->db->query('UPDATE psb_akun SET password = :password, reset_token = NULL, reset_token_expiry = NULL WHERE id_akun = :id');
        $this->db->bind(':password', password_hash($newPassword, PASSWORD_DEFAULT));
        $this->db->bind(':id', $idAkun);
        $this->db->execute();
        return $this->db->rowCount();
    }

    // =========================================================================
    // PENDAFTARAN BY AKUN METHODS
    // =========================================================================

    /**
     * Get semua pendaftaran milik akun
     */
    public function getPendaftaranByAkun($idAkun)
    {
        $this->db->query('SELECT p.*, per.nama_periode, l.nama_lembaga, j.nama_jalur
                          FROM psb_pendaftar p
                          JOIN psb_periode per ON p.id_periode = per.id_periode
                          JOIN psb_lembaga l ON per.id_lembaga = l.id_lembaga
                          JOIN psb_jalur j ON p.id_jalur = j.id_jalur
                          WHERE p.id_akun = :id_akun
                          ORDER BY p.created_at DESC');
        $this->db->bind(':id_akun', $idAkun);
        return $this->db->resultSet();
    }

    /**
     * Buat pendaftaran baru (draft)
     */
    public function createPendaftaran($idAkun, $idPeriode, $idJalur)
    {
        $noPendaftaran = $this->generateNoPendaftaran($idPeriode);

        $this->db->query('INSERT INTO psb_pendaftar (id_akun, id_periode, id_jalur, no_pendaftaran, nama_lengkap, status) 
                          SELECT :id_akun, :id_periode, :id_jalur, :no_pendaftaran, nama_lengkap, "draft"
                          FROM psb_akun WHERE id_akun = :id_akun2');
        $this->db->bind(':id_akun', $idAkun);
        $this->db->bind(':id_akun2', $idAkun);
        $this->db->bind(':id_periode', $idPeriode);
        $this->db->bind(':id_jalur', $idJalur);
        $this->db->bind(':no_pendaftaran', $noPendaftaran);
        $this->db->execute();

        return [
            'id' => $this->db->lastInsertId(),
            'no_pendaftaran' => $noPendaftaran
        ];
    }

    /**
     * Update step formulir
     */
    public function updateFormulirStep($idPendaftar, $step, $data)
    {
        // Build dynamic query based on step
        $fields = array_keys($data);
        $setClause = implode(', ', array_map(fn($f) => "$f = :$f", $fields));
        $setClause .= ', step_terakhir = :step';

        $this->db->query("UPDATE psb_pendaftar SET $setClause WHERE id_pendaftar = :id");
        foreach ($data as $key => $value) {
            $this->db->bind(":$key", $value);
        }
        $this->db->bind(':step', $step);
        $this->db->bind(':id', $idPendaftar);
        $this->db->execute();
        return $this->db->rowCount();
    }

    /**
     * Submit pendaftaran (ubah status ke pending)
     */
    public function submitPendaftaran($idPendaftar)
    {
        $this->db->query('UPDATE psb_pendaftar SET status = "pending", tanggal_submit = NOW() WHERE id_pendaftar = :id');
        $this->db->bind(':id', $idPendaftar);
        $this->db->execute();
        return $this->db->rowCount();
    }

    // =========================================================================
    // DOKUMEN METHODS
    // =========================================================================

    /**
     * Get dokumen pendaftar
     */
    public function getDokumenPendaftar($idPendaftar)
    {
        $this->db->query('SELECT * FROM psb_dokumen WHERE id_pendaftar = :id ORDER BY jenis_dokumen');
        $this->db->bind(':id', $idPendaftar);
        return $this->db->resultSet();
    }

    /**
     * Upload dokumen
     */
    public function uploadDokumen($idPendaftar, $jenisDokumen, $namaFile, $pathFile, $ukuran = 0, $driveFileId = null, $driveUrl = null)
    {
        // Cek apakah sudah ada dokumen jenis ini
        $this->db->query('SELECT id_dokumen FROM psb_dokumen WHERE id_pendaftar = :id AND jenis_dokumen = :jenis');
        $this->db->bind(':id', $idPendaftar);
        $this->db->bind(':jenis', $jenisDokumen);
        $existing = $this->db->single();

        if ($existing) {
            // Update existing
            $sql = 'UPDATE psb_dokumen SET nama_file = :nama, path_file = :path, ukuran = :ukuran, 
                    status = "pending", uploaded_at = NOW()';
            if ($driveFileId !== null) {
                $sql .= ', drive_file_id = :drive_id, drive_url = :drive_url';
            }
            $sql .= ' WHERE id_dokumen = :id_dok';

            $this->db->query($sql);
            $this->db->bind(':nama', $namaFile);
            $this->db->bind(':path', $pathFile);
            $this->db->bind(':ukuran', $ukuran);
            if ($driveFileId !== null) {
                $this->db->bind(':drive_id', $driveFileId);
                $this->db->bind(':drive_url', $driveUrl);
            }
            $this->db->bind(':id_dok', $existing['id_dokumen']);
        } else {
            // Insert new
            if ($driveFileId !== null) {
                $this->db->query('INSERT INTO psb_dokumen (id_pendaftar, jenis_dokumen, nama_file, path_file, ukuran, drive_file_id, drive_url) 
                                  VALUES (:id, :jenis, :nama, :path, :ukuran, :drive_id, :drive_url)');
                $this->db->bind(':drive_id', $driveFileId);
                $this->db->bind(':drive_url', $driveUrl);
            } else {
                $this->db->query('INSERT INTO psb_dokumen (id_pendaftar, jenis_dokumen, nama_file, path_file, ukuran) 
                                  VALUES (:id, :jenis, :nama, :path, :ukuran)');
            }
            $this->db->bind(':id', $idPendaftar);
            $this->db->bind(':jenis', $jenisDokumen);
            $this->db->bind(':nama', $namaFile);
            $this->db->bind(':path', $pathFile);
            $this->db->bind(':ukuran', $ukuran);
        }
        $this->db->execute();
        return $this->db->rowCount();
    }

    /**
     * Hapus dokumen
     */
    public function hapusDokumen($idDokumen)
    {
        $this->db->query('DELETE FROM psb_dokumen WHERE id_dokumen = :id');
        $this->db->bind(':id', $idDokumen);
        $this->db->execute();
        return $this->db->rowCount();
    }

    /**
     * Update status dokumen (admin)
     */
    public function updateStatusDokumen($idDokumen, $status, $catatan = null)
    {
        $this->db->query('UPDATE psb_dokumen SET status = :status, catatan = :catatan WHERE id_dokumen = :id');
        $this->db->bind(':status', $status);
        $this->db->bind(':catatan', $catatan);
        $this->db->bind(':id', $idDokumen);
        $this->db->execute();
        return $this->db->rowCount();
    }

    // =========================================================================
    // AKUN MANAGEMENT
    // =========================================================================

    /**
     * Get semua akun pendaftar
     */
    public function getAllAkun()
    {
        $this->db->query('
            SELECT a.*, 
                   (SELECT COUNT(*) FROM psb_pendaftar p WHERE p.id_akun = a.id_akun) as jumlah_pendaftaran
            FROM psb_akun a 
            ORDER BY a.created_at DESC
        ');
        return $this->db->resultSet();
    }

    /**
     * Update akun
     */
    public function updateAkun($id, $data)
    {
        $sql = 'UPDATE psb_akun SET nama_lengkap = :nama, no_wa = :no_wa';

        if (!empty($data['password'])) {
            $sql .= ', password = :password';
        }

        $sql .= ' WHERE id_akun = :id';

        $this->db->query($sql);
        $this->db->bind(':nama', $data['nama_lengkap']);
        $this->db->bind(':no_wa', $data['no_wa']);
        $this->db->bind(':id', $id);

        if (!empty($data['password'])) {
            $this->db->bind(':password', password_hash($data['password'], PASSWORD_DEFAULT));
        }

        $this->db->execute();
        return $this->db->rowCount();
    }

    /**
     * Hapus akun
     */
    public function hapusAkun($id)
    {
        $this->db->query('SELECT COUNT(*) as total FROM psb_pendaftar WHERE id_akun = :id');
        $this->db->bind(':id', $id);
        $result = $this->db->single();

        if ($result['total'] > 0) {
            return false;
        }

        $this->db->query('DELETE FROM psb_akun WHERE id_akun = :id');
        $this->db->bind(':id', $id);
        $this->db->execute();
        return $this->db->rowCount() > 0;
    }

    /**
     * Update data pendaftar (untuk menu-based form)
     */
    public function updatePendaftarData($idPendaftar, $data)
    {
        if (empty($data))
            return 0;

        $fields = array_keys($data);
        $setClause = implode(', ', array_map(fn($f) => "$f = :$f", $fields));

        $this->db->query("UPDATE psb_pendaftar SET $setClause WHERE id_pendaftar = :id");
        foreach ($data as $key => $value) {
            $this->db->bind(":$key", $value);
        }
        $this->db->bind(':id', $idPendaftar);
        $this->db->execute();
        return $this->db->rowCount();
    }

    /**
     * Cek pendaftaran by akun dan periode
     */
    public function getPendaftaranByAkunPeriode($idAkun, $idPeriode)
    {
        $this->db->query('SELECT * FROM psb_pendaftar WHERE id_akun = :id_akun AND id_periode = :id_periode LIMIT 1');
        $this->db->bind(':id_akun', $idAkun);
        $this->db->bind(':id_periode', $idPeriode);
        return $this->db->single();
    }

    /**
     * Hapus pendaftaran
     */
    public function hapusPendaftaran($idPendaftar)
    {
        // Hapus dokumen terkait dulu
        $this->db->query('DELETE FROM psb_dokumen WHERE id_pendaftar = :id');
        $this->db->bind(':id', $idPendaftar);
        $this->db->execute();

        // Hapus pendaftaran
        $this->db->query('DELETE FROM psb_pendaftar WHERE id_pendaftar = :id');
        $this->db->bind(':id', $idPendaftar);
        $this->db->execute();
        return $this->db->rowCount() > 0;
    }

    /**
     * Get dokumen by pendaftar
     */
    public function getDokumenByPendaftar($idPendaftar)
    {
        $this->db->query('SELECT * FROM psb_dokumen WHERE id_pendaftar = :id');
        $this->db->bind(':id', $idPendaftar);
        return $this->db->resultSet();
    }
}

