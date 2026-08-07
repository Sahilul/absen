<?php

// File: app/models/Siswa_model.php - Sesuai Database Schema yang Ada
class Siswa_model
{
    private $db;

    public function __construct()
    {
        require_once APPROOT . '/app/core/Database.php';
        $this->db = new Database;
    }

    /**
     * Check if a table exists in the database
     */
    private function checkTableExists($tableName)
    {
        try {
            $this->db->query("SELECT 1 FROM `$tableName` LIMIT 1");
            $this->db->execute();
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    // =================================================================
    // EXISTING METHODS - TETAP SAMA
    // =================================================================

    public function getJumlahSiswa()
    {
        $this->db->query("SELECT COUNT(*) as total FROM siswa WHERE status_siswa = 'aktif'");
        return $this->db->single()['total'];
    }

    public function getAllSiswa()
    {
        // Ambil kelas terakhir (berdasarkan id_keanggotaan terbesar) jika ada
        $this->db->query('
            SELECT s.*, u.password_plain, k.nama_kelas
            FROM siswa s
            LEFT JOIN users u ON s.id_siswa = u.id_ref AND u.role = "siswa"
            LEFT JOIN (
                SELECT kk1.id_siswa, kk1.id_kelas
                FROM keanggotaan_kelas kk1
                INNER JOIN (
                    SELECT id_siswa, MAX(id_keanggotaan) AS max_kk
                    FROM keanggotaan_kelas
                    GROUP BY id_siswa
                ) latest ON kk1.id_siswa = latest.id_siswa AND kk1.id_keanggotaan = latest.max_kk
            ) last_kk ON s.id_siswa = last_kk.id_siswa
            LEFT JOIN kelas k ON last_kk.id_kelas = k.id_kelas
            ORDER BY s.nama_siswa ASC
        ');
        return $this->db->resultSet();
    }

    /**
     * Get all siswa with kelas info, optionally filtered by TP
     * Uses the same subquery approach as getAllSiswa() for reliability
     */
    public function getAllSiswaWithKelas($id_tp = null)
    {
        // Use the same subquery approach as getAllSiswa() to get latest class membership
        $sql = '
            SELECT s.*, u.password_plain, k.nama_kelas
            FROM siswa s
            LEFT JOIN users u ON s.id_siswa = u.id_ref AND u.role = "siswa"
            LEFT JOIN (
                SELECT kk1.id_siswa, kk1.id_kelas
                FROM keanggotaan_kelas kk1
                INNER JOIN (
                    SELECT id_siswa, MAX(id_keanggotaan) AS max_kk
                    FROM keanggotaan_kelas
                    GROUP BY id_siswa
                ) latest ON kk1.id_siswa = latest.id_siswa AND kk1.id_keanggotaan = latest.max_kk
            ) last_kk ON s.id_siswa = last_kk.id_siswa
            LEFT JOIN kelas k ON last_kk.id_kelas = k.id_kelas
            WHERE s.status_siswa = "aktif"
            ORDER BY k.nama_kelas ASC, s.nama_siswa ASC
        ';
        $this->db->query($sql);
        return $this->db->resultSet();
    }

    public function getSiswaById($id)
    {
        $this->db->query('SELECT * FROM siswa WHERE id_siswa = :id');
        $this->db->bind('id', $id);
        return $this->db->single();
    }

    public function getSiswaKelasAkhir($id_tp)
    {
        // Ambil siswa dari kelas terakhir (misal jenjang XII atau 9)
        $this->db->query('SELECT s.*, k.nama_kelas 
                         FROM siswa s 
                         JOIN keanggotaan_kelas kk ON s.id_siswa = kk.id_siswa 
                         JOIN kelas k ON kk.id_kelas = k.id_kelas 
                         WHERE kk.id_tp = :id_tp 
                         AND s.status_siswa = "aktif" 
                         AND (k.jenjang = "XII" OR k.jenjang = "9") 
                         ORDER BY s.nama_siswa ASC');
        $this->db->bind('id_tp', $id_tp);
        return $this->db->resultSet();
    }

    public function tambahDataSiswa($data)
    {
        $this->db->query('INSERT INTO siswa (
            nisn, nik, nama_siswa, jenis_kelamin, tgl_lahir, tempat_lahir, agama,
            foto, hobi, cita_cita, jumlah_saudara, anak_ke, kip, yang_membiayai, kebutuhan_khusus, kebutuhan_disabilitas,
            alamat, rt, rw, dusun, desa, kelurahan, kecamatan, kabupaten, kota, provinsi, kode_pos,
            status_tempat_tinggal, jarak_ke_sekolah, waktu_tempuh, transportasi,
            no_wa, email,
            ayah_kandung, ayah_nik, ayah_tempat_lahir, ayah_tanggal_lahir, ayah_status, ayah_pendidikan, ayah_pekerjaan, ayah_penghasilan, ayah_no_hp,
            ibu_kandung, ibu_nik, ibu_tempat_lahir, ibu_tanggal_lahir, ibu_status, ibu_pendidikan, ibu_pekerjaan, ibu_penghasilan, ibu_no_hp,
            wali_nama, wali_nik, wali_hubungan, wali_pendidikan, wali_pekerjaan, wali_penghasilan, wali_no_hp,
            status_siswa
        ) VALUES (
            :nisn, :nik, :nama_siswa, :jenis_kelamin, :tgl_lahir, :tempat_lahir, :agama,
            :foto, :hobi, :cita_cita, :jumlah_saudara, :anak_ke, :kip, :yang_membiayai, :kebutuhan_khusus, :kebutuhan_disabilitas,
            :alamat, :rt, :rw, :dusun, :desa, :kelurahan, :kecamatan, :kabupaten, :kota, :provinsi, :kode_pos,
            :status_tempat_tinggal, :jarak_ke_sekolah, :waktu_tempuh, :transportasi,
            :no_wa, :email,
            :ayah_kandung, :ayah_nik, :ayah_tempat_lahir, :ayah_tanggal_lahir, :ayah_status, :ayah_pendidikan, :ayah_pekerjaan, :ayah_penghasilan, :ayah_no_hp,
            :ibu_kandung, :ibu_nik, :ibu_tempat_lahir, :ibu_tanggal_lahir, :ibu_status, :ibu_pendidikan, :ibu_pekerjaan, :ibu_penghasilan, :ibu_no_hp,
            :wali_nama, :wali_nik, :wali_hubungan, :wali_pendidikan, :wali_pekerjaan, :wali_penghasilan, :wali_no_hp,
            "aktif"
        )');

        // Bind all parameters
        $this->db->bind('nisn', $data['nisn'] ?? null);
        $this->db->bind('nik', $data['nik'] ?? null);
        $this->db->bind('nama_siswa', $data['nama_siswa']);
        $this->db->bind('jenis_kelamin', $data['jenis_kelamin']);

        // Normalize date
        $tgl = isset($data['tgl_lahir']) ? trim($data['tgl_lahir']) : '';
        $this->db->bind('tgl_lahir', ($tgl && $tgl !== '0000-00-00') ? $tgl : null);

        $this->db->bind('tempat_lahir', $data['tempat_lahir'] ?? null);
        $this->db->bind('agama', $data['agama'] ?? null);
        $this->db->bind('foto', $data['foto'] ?? null);
        $this->db->bind('hobi', $data['hobi'] ?? null);
        $this->db->bind('cita_cita', $data['cita_cita'] ?? null);
        $this->db->bind('jumlah_saudara', $data['jumlah_saudara'] ?? 0);
        $this->db->bind('anak_ke', $data['anak_ke'] ?? 1);
        $this->db->bind('kip', $data['kip'] ?? null);
        $this->db->bind('yang_membiayai', $data['yang_membiayai'] ?? 'Orang Tua');
        $this->db->bind('kebutuhan_khusus', $data['kebutuhan_khusus'] ?? 'Tidak Ada');
        $this->db->bind('kebutuhan_disabilitas', $data['kebutuhan_disabilitas'] ?? 'Tidak Ada');

        // Address
        $this->db->bind('alamat', $data['alamat'] ?? null);
        $this->db->bind('rt', $data['rt'] ?? null);
        $this->db->bind('rw', $data['rw'] ?? null);
        $this->db->bind('dusun', $data['dusun'] ?? null);
        $this->db->bind('desa', $data['desa'] ?? null);
        $this->db->bind('kelurahan', $data['kelurahan'] ?? null);
        $this->db->bind('kecamatan', $data['kecamatan'] ?? null);
        $this->db->bind('kabupaten', $data['kabupaten'] ?? null);
        $this->db->bind('kota', $data['kota'] ?? null);
        $this->db->bind('provinsi', $data['provinsi'] ?? null);
        $this->db->bind('kode_pos', $data['kode_pos'] ?? null);
        $this->db->bind('status_tempat_tinggal', $data['status_tempat_tinggal'] ?? null);
        $this->db->bind('jarak_ke_sekolah', $data['jarak_ke_sekolah'] ?? null);
        $this->db->bind('waktu_tempuh', $data['waktu_tempuh'] ?? null);
        $this->db->bind('transportasi', $data['transportasi'] ?? null);

        // Contact
        $this->db->bind('no_wa', $data['no_wa'] ?? null);
        $this->db->bind('email', $data['email'] ?? null);

        // Father
        $this->db->bind('ayah_kandung', $data['ayah_kandung'] ?? null);
        $this->db->bind('ayah_nik', $data['ayah_nik'] ?? null);
        $this->db->bind('ayah_tempat_lahir', $data['ayah_tempat_lahir'] ?? null);
        $ayahTgl = isset($data['ayah_tanggal_lahir']) ? trim($data['ayah_tanggal_lahir']) : '';
        $this->db->bind('ayah_tanggal_lahir', ($ayahTgl && $ayahTgl !== '0000-00-00') ? $ayahTgl : null);
        $this->db->bind('ayah_status', $data['ayah_status'] ?? 'Masih Hidup');
        $this->db->bind('ayah_pendidikan', $data['ayah_pendidikan'] ?? null);
        $this->db->bind('ayah_pekerjaan', $data['ayah_pekerjaan'] ?? null);
        $this->db->bind('ayah_penghasilan', $data['ayah_penghasilan'] ?? null);
        $this->db->bind('ayah_no_hp', $data['ayah_no_hp'] ?? null);

        // Mother
        $this->db->bind('ibu_kandung', $data['ibu_kandung'] ?? null);
        $this->db->bind('ibu_nik', $data['ibu_nik'] ?? null);
        $this->db->bind('ibu_tempat_lahir', $data['ibu_tempat_lahir'] ?? null);
        $ibuTgl = isset($data['ibu_tanggal_lahir']) ? trim($data['ibu_tanggal_lahir']) : '';
        $this->db->bind('ibu_tanggal_lahir', ($ibuTgl && $ibuTgl !== '0000-00-00') ? $ibuTgl : null);
        $this->db->bind('ibu_status', $data['ibu_status'] ?? 'Masih Hidup');
        $this->db->bind('ibu_pendidikan', $data['ibu_pendidikan'] ?? null);
        $this->db->bind('ibu_pekerjaan', $data['ibu_pekerjaan'] ?? null);
        $this->db->bind('ibu_penghasilan', $data['ibu_penghasilan'] ?? null);
        $this->db->bind('ibu_no_hp', $data['ibu_no_hp'] ?? null);

        // Guardian
        $this->db->bind('wali_nama', $data['wali_nama'] ?? null);
        $this->db->bind('wali_nik', $data['wali_nik'] ?? null);
        $this->db->bind('wali_hubungan', $data['wali_hubungan'] ?? null);
        $this->db->bind('wali_pendidikan', $data['wali_pendidikan'] ?? null);
        $this->db->bind('wali_pekerjaan', $data['wali_pekerjaan'] ?? null);
        $this->db->bind('wali_penghasilan', $data['wali_penghasilan'] ?? null);
        $this->db->bind('wali_no_hp', $data['wali_no_hp'] ?? null);

        $this->db->execute();
        return $this->db->lastInsertId();
    }

    public function updateDataSiswa($data)
    {
        $this->db->query('UPDATE siswa SET 
            nisn = :nisn, nik = :nik, nama_siswa = :nama_siswa, jenis_kelamin = :jenis_kelamin, 
            tgl_lahir = :tgl_lahir, tempat_lahir = :tempat_lahir, agama = :agama,
            foto = :foto, hobi = :hobi, cita_cita = :cita_cita, 
            jumlah_saudara = :jumlah_saudara, anak_ke = :anak_ke, kip = :kip,
            yang_membiayai = :yang_membiayai, kebutuhan_khusus = :kebutuhan_khusus, kebutuhan_disabilitas = :kebutuhan_disabilitas,
            alamat = :alamat, rt = :rt, rw = :rw, dusun = :dusun, desa = :desa,
            kelurahan = :kelurahan, kecamatan = :kecamatan, kabupaten = :kabupaten, kota = :kota, provinsi = :provinsi, kode_pos = :kode_pos,
            status_tempat_tinggal = :status_tempat_tinggal, jarak_ke_sekolah = :jarak_ke_sekolah, waktu_tempuh = :waktu_tempuh, transportasi = :transportasi,
            no_wa = :no_wa, email = :email,
            ayah_kandung = :ayah_kandung, ayah_nik = :ayah_nik, ayah_tempat_lahir = :ayah_tempat_lahir, ayah_tanggal_lahir = :ayah_tanggal_lahir,
            ayah_status = :ayah_status, ayah_pendidikan = :ayah_pendidikan, ayah_pekerjaan = :ayah_pekerjaan, ayah_penghasilan = :ayah_penghasilan, ayah_no_hp = :ayah_no_hp,
            ibu_kandung = :ibu_kandung, ibu_nik = :ibu_nik, ibu_tempat_lahir = :ibu_tempat_lahir, ibu_tanggal_lahir = :ibu_tanggal_lahir,
            ibu_status = :ibu_status, ibu_pendidikan = :ibu_pendidikan, ibu_pekerjaan = :ibu_pekerjaan, ibu_penghasilan = :ibu_penghasilan, ibu_no_hp = :ibu_no_hp,
            wali_nama = :wali_nama, wali_nik = :wali_nik, wali_hubungan = :wali_hubungan,
            wali_pendidikan = :wali_pendidikan, wali_pekerjaan = :wali_pekerjaan, wali_penghasilan = :wali_penghasilan, wali_no_hp = :wali_no_hp
            WHERE id_siswa = :id_siswa
        ');

        // Bind all parameters
        $this->db->bind('nisn', $data['nisn'] ?? null);
        $this->db->bind('nik', $data['nik'] ?? null);
        $this->db->bind('nama_siswa', $data['nama_siswa']);
        $this->db->bind('jenis_kelamin', $data['jenis_kelamin']);

        $tgl = isset($data['tgl_lahir']) ? trim($data['tgl_lahir']) : '';
        $this->db->bind('tgl_lahir', ($tgl && $tgl !== '0000-00-00') ? $tgl : null);

        $this->db->bind('tempat_lahir', $data['tempat_lahir'] ?? null);
        $this->db->bind('agama', $data['agama'] ?? null);
        $this->db->bind('foto', $data['foto'] ?? null);
        $this->db->bind('hobi', $data['hobi'] ?? null);
        $this->db->bind('cita_cita', $data['cita_cita'] ?? null);
        $this->db->bind('jumlah_saudara', $data['jumlah_saudara'] ?? 0);
        $this->db->bind('anak_ke', $data['anak_ke'] ?? 1);
        $this->db->bind('kip', $data['kip'] ?? null);
        $this->db->bind('yang_membiayai', $data['yang_membiayai'] ?? 'Orang Tua');
        $this->db->bind('kebutuhan_khusus', $data['kebutuhan_khusus'] ?? 'Tidak Ada');
        $this->db->bind('kebutuhan_disabilitas', $data['kebutuhan_disabilitas'] ?? 'Tidak Ada');

        // Address
        $this->db->bind('alamat', $data['alamat'] ?? null);
        $this->db->bind('rt', $data['rt'] ?? null);
        $this->db->bind('rw', $data['rw'] ?? null);
        $this->db->bind('dusun', $data['dusun'] ?? null);
        $this->db->bind('desa', $data['desa'] ?? null);
        $this->db->bind('kelurahan', $data['kelurahan'] ?? null);
        $this->db->bind('kecamatan', $data['kecamatan'] ?? null);
        $this->db->bind('kabupaten', $data['kabupaten'] ?? null);
        $this->db->bind('kota', $data['kota'] ?? null);
        $this->db->bind('provinsi', $data['provinsi'] ?? null);
        $this->db->bind('kode_pos', $data['kode_pos'] ?? null);
        $this->db->bind('status_tempat_tinggal', $data['status_tempat_tinggal'] ?? null);
        $this->db->bind('jarak_ke_sekolah', $data['jarak_ke_sekolah'] ?? null);
        $this->db->bind('waktu_tempuh', $data['waktu_tempuh'] ?? null);
        $this->db->bind('transportasi', $data['transportasi'] ?? null);

        $this->db->bind('no_wa', $data['no_wa'] ?? null);
        $this->db->bind('email', $data['email'] ?? null);

        // Father
        $this->db->bind('ayah_kandung', $data['ayah_kandung'] ?? null);
        $this->db->bind('ayah_nik', $data['ayah_nik'] ?? null);
        $this->db->bind('ayah_tempat_lahir', $data['ayah_tempat_lahir'] ?? null);
        $ayahTgl = isset($data['ayah_tanggal_lahir']) ? trim($data['ayah_tanggal_lahir']) : '';
        $this->db->bind('ayah_tanggal_lahir', ($ayahTgl && $ayahTgl !== '0000-00-00') ? $ayahTgl : null);
        $this->db->bind('ayah_status', $data['ayah_status'] ?? 'Masih Hidup');
        $this->db->bind('ayah_pendidikan', $data['ayah_pendidikan'] ?? null);
        $this->db->bind('ayah_pekerjaan', $data['ayah_pekerjaan'] ?? null);
        $this->db->bind('ayah_penghasilan', $data['ayah_penghasilan'] ?? null);
        $this->db->bind('ayah_no_hp', $data['ayah_no_hp'] ?? null);

        // Mother
        $this->db->bind('ibu_kandung', $data['ibu_kandung'] ?? null);
        $this->db->bind('ibu_nik', $data['ibu_nik'] ?? null);
        $this->db->bind('ibu_tempat_lahir', $data['ibu_tempat_lahir'] ?? null);
        $ibuTgl = isset($data['ibu_tanggal_lahir']) ? trim($data['ibu_tanggal_lahir']) : '';
        $this->db->bind('ibu_tanggal_lahir', ($ibuTgl && $ibuTgl !== '0000-00-00') ? $ibuTgl : null);
        $this->db->bind('ibu_status', $data['ibu_status'] ?? 'Masih Hidup');
        $this->db->bind('ibu_pendidikan', $data['ibu_pendidikan'] ?? null);
        $this->db->bind('ibu_pekerjaan', $data['ibu_pekerjaan'] ?? null);
        $this->db->bind('ibu_penghasilan', $data['ibu_penghasilan'] ?? null);
        $this->db->bind('ibu_no_hp', $data['ibu_no_hp'] ?? null);

        // Guardian
        $this->db->bind('wali_nama', $data['wali_nama'] ?? null);
        $this->db->bind('wali_nik', $data['wali_nik'] ?? null);
        $this->db->bind('wali_hubungan', $data['wali_hubungan'] ?? null);
        $this->db->bind('wali_pendidikan', $data['wali_pendidikan'] ?? null);
        $this->db->bind('wali_pekerjaan', $data['wali_pekerjaan'] ?? null);
        $this->db->bind('wali_penghasilan', $data['wali_penghasilan'] ?? null);
        $this->db->bind('wali_no_hp', $data['wali_no_hp'] ?? null);

        $this->db->bind('id_siswa', $data['id_siswa']);
        $this->db->execute();
        return $this->db->rowCount() >= 0; // Return true even if no changes
    }

    /**
     * Update data siswa oleh siswa sendiri
     * TIDAK termasuk ayah_no_hp dan ibu_no_hp (field terkunci)
     */
    public function updateDataSiswaByStudent($data)
    {
        $this->db->query('UPDATE siswa SET 
            nik = :nik, nama_siswa = :nama_siswa, jenis_kelamin = :jenis_kelamin, 
            tgl_lahir = :tgl_lahir, tempat_lahir = :tempat_lahir, agama = :agama,
            hobi = :hobi, cita_cita = :cita_cita,
            alamat = :alamat, rt = :rt, rw = :rw, dusun = :dusun,
            kelurahan = :kelurahan, kecamatan = :kecamatan, kabupaten = :kabupaten, provinsi = :provinsi, kode_pos = :kode_pos,
            no_wa = :no_wa, email = :email,
            ayah_kandung = :ayah_kandung, ayah_nik = :ayah_nik, ayah_tempat_lahir = :ayah_tempat_lahir, ayah_tanggal_lahir = :ayah_tanggal_lahir,
            ayah_status = :ayah_status, ayah_pendidikan = :ayah_pendidikan, ayah_pekerjaan = :ayah_pekerjaan, ayah_penghasilan = :ayah_penghasilan,
            ibu_kandung = :ibu_kandung, ibu_nik = :ibu_nik, ibu_tempat_lahir = :ibu_tempat_lahir, ibu_tanggal_lahir = :ibu_tanggal_lahir,
            ibu_status = :ibu_status, ibu_pendidikan = :ibu_pendidikan, ibu_pekerjaan = :ibu_pekerjaan, ibu_penghasilan = :ibu_penghasilan,
            wali_nama = :wali_nama, wali_nik = :wali_nik, wali_hubungan = :wali_hubungan,
            wali_pendidikan = :wali_pendidikan, wali_pekerjaan = :wali_pekerjaan, wali_penghasilan = :wali_penghasilan, wali_no_hp = :wali_no_hp
            WHERE id_siswa = :id_siswa
        ');

        // Bind parameters
        $this->db->bind('nik', $data['nik'] ?? null);
        $this->db->bind('nama_siswa', $data['nama_siswa']);
        $this->db->bind('jenis_kelamin', $data['jenis_kelamin'] ?? null);

        $tgl = isset($data['tgl_lahir']) ? trim($data['tgl_lahir']) : '';
        $this->db->bind('tgl_lahir', ($tgl && $tgl !== '0000-00-00') ? $tgl : null);

        $this->db->bind('tempat_lahir', $data['tempat_lahir'] ?? null);
        $this->db->bind('agama', $data['agama'] ?? null);
        $this->db->bind('hobi', $data['hobi'] ?? null);
        $this->db->bind('cita_cita', $data['cita_cita'] ?? null);

        // Address
        $this->db->bind('alamat', $data['alamat'] ?? null);
        $this->db->bind('rt', $data['rt'] ?? null);
        $this->db->bind('rw', $data['rw'] ?? null);
        $this->db->bind('dusun', $data['dusun'] ?? null);
        $this->db->bind('kelurahan', $data['kelurahan'] ?? null);
        $this->db->bind('kecamatan', $data['kecamatan'] ?? null);
        $this->db->bind('kabupaten', $data['kabupaten'] ?? null);
        $this->db->bind('provinsi', $data['provinsi'] ?? null);
        $this->db->bind('kode_pos', $data['kode_pos'] ?? null);

        $this->db->bind('no_wa', $data['no_wa'] ?? null);
        $this->db->bind('email', $data['email'] ?? null);

        // Father (TANPA ayah_no_hp - field terkunci)
        $this->db->bind('ayah_kandung', $data['ayah_kandung'] ?? null);
        $this->db->bind('ayah_nik', $data['ayah_nik'] ?? null);
        $this->db->bind('ayah_tempat_lahir', $data['ayah_tempat_lahir'] ?? null);
        $ayahTgl = isset($data['ayah_tanggal_lahir']) ? trim($data['ayah_tanggal_lahir']) : '';
        $this->db->bind('ayah_tanggal_lahir', ($ayahTgl && $ayahTgl !== '0000-00-00') ? $ayahTgl : null);
        $this->db->bind('ayah_status', $data['ayah_status'] ?? 'Masih Hidup');
        $this->db->bind('ayah_pendidikan', $data['ayah_pendidikan'] ?? null);
        $this->db->bind('ayah_pekerjaan', $data['ayah_pekerjaan'] ?? null);
        $this->db->bind('ayah_penghasilan', $data['ayah_penghasilan'] ?? null);

        // Mother (TANPA ibu_no_hp - field terkunci)
        $this->db->bind('ibu_kandung', $data['ibu_kandung'] ?? null);
        $this->db->bind('ibu_nik', $data['ibu_nik'] ?? null);
        $this->db->bind('ibu_tempat_lahir', $data['ibu_tempat_lahir'] ?? null);
        $ibuTgl = isset($data['ibu_tanggal_lahir']) ? trim($data['ibu_tanggal_lahir']) : '';
        $this->db->bind('ibu_tanggal_lahir', ($ibuTgl && $ibuTgl !== '0000-00-00') ? $ibuTgl : null);
        $this->db->bind('ibu_status', $data['ibu_status'] ?? 'Masih Hidup');
        $this->db->bind('ibu_pendidikan', $data['ibu_pendidikan'] ?? null);
        $this->db->bind('ibu_pekerjaan', $data['ibu_pekerjaan'] ?? null);
        $this->db->bind('ibu_penghasilan', $data['ibu_penghasilan'] ?? null);

        // Guardian
        $this->db->bind('wali_nama', $data['wali_nama'] ?? null);
        $this->db->bind('wali_nik', $data['wali_nik'] ?? null);
        $this->db->bind('wali_hubungan', $data['wali_hubungan'] ?? null);
        $this->db->bind('wali_pendidikan', $data['wali_pendidikan'] ?? null);
        $this->db->bind('wali_pekerjaan', $data['wali_pekerjaan'] ?? null);
        $this->db->bind('wali_penghasilan', $data['wali_penghasilan'] ?? null);
        $this->db->bind('wali_no_hp', $data['wali_no_hp'] ?? null);

        $this->db->bind('id_siswa', $data['id_siswa']);
        $this->db->execute();
        return $this->db->rowCount() >= 0;
    }

    public function hapusDataSiswa($id)
    {
        $this->db->query('DELETE FROM siswa WHERE id_siswa = :id');
        $this->db->bind('id', $id);
        $this->db->execute();
        return $this->db->rowCount();
    }

    // Soft delete sebelumnya dihapus (tidak digunakan lagi)

    public function prosesKelulusan($daftar_id_siswa)
    {
        if (empty($daftar_id_siswa))
            return 0;

        $placeholders = implode(',', array_fill(0, count($daftar_id_siswa), '?'));
        $this->db->query("UPDATE siswa SET status_siswa = 'lulus' WHERE id_siswa IN ($placeholders)");

        foreach ($daftar_id_siswa as $k => $id) {
            $this->db->bind($k + 1, $id);
        }

        $this->db->execute();
        return $this->db->rowCount();
    }

    // ALIAS untuk compatibility dengan kode lama
    public function luluskanSiswaByIds($daftar_id_siswa)
    {
        return $this->prosesKelulusan($daftar_id_siswa);
    }

    // =================================================================
    // NEW METHODS UNTUK IMPORT EXCEL - SESUAI SCHEMA DATABASE
    // =================================================================

    /**
     * Cek apakah NISN sudah ada di database
     */
    public function cekNisnExists($nisn)
    {
        try {
            $this->db->query('SELECT COUNT(*) as total FROM siswa WHERE nisn = :nisn');
            $this->db->bind('nisn', $nisn);
            $result = $this->db->single();
            return $result['total'] > 0;
        } catch (Exception $e) {
            error_log("cekNisnExists error: " . $e->getMessage());
            return true; // Return true untuk safety
        }
    }

    /**
     * Get siswa by NISN
     */
    public function getSiswaByNisn($nisn)
    {
        try {
            $this->db->query('SELECT s.*, u.password_plain 
                              FROM siswa s 
                              LEFT JOIN users u ON u.role = "siswa" AND u.id_ref = s.id_siswa 
                              WHERE s.nisn = :nisn LIMIT 1');
            $this->db->bind('nisn', $nisn);
            return $this->db->single();
        } catch (Exception $e) {
            error_log("getSiswaByNisn error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Mendapatkan daftar siswa berdasarkan kelas dan tahun pelajaran
     * @param int $id_kelas ID Kelas
     * @param int $id_tp ID Tahun Pelajaran
     * @return array Daftar siswa aktif di kelas tersebut
     */
    public function getSiswaByKelas($id_kelas, $id_tp)
    {
        try {
            $this->db->query('SELECT s.*, kk.id_keanggotaan, u.password_plain 
                             FROM siswa s
                             JOIN keanggotaan_kelas kk ON s.id_siswa = kk.id_siswa
                             LEFT JOIN users u ON u.id_ref = s.id_siswa AND u.role = "siswa"
                             WHERE kk.id_kelas = :id_kelas 
                             AND kk.id_tp = :id_tp 
                             AND s.status_siswa = "aktif"
                             ORDER BY s.nama_siswa ASC');
            $this->db->bind('id_kelas', $id_kelas);
            $this->db->bind('id_tp', $id_tp);
            return $this->db->resultSet();
        } catch (Exception $e) {
            error_log("getSiswaByKelas error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Batch insert siswa untuk import Excel
     */
    public function batchInsertSiswa($dataSiswa)
    {
        if (empty($dataSiswa)) {
            return ['success' => 0, 'errors' => [], 'ids' => []];
        }

        $successCount = 0;
        $errors = [];
        $createdIds = [];

        foreach ($dataSiswa as $index => $data) {
            try {
                // Validasi data sebelum insert
                if (empty($data['nisn']) || empty($data['nama_siswa'])) {
                    $errors[] = "Baris " . ($index + 1) . ": Data tidak lengkap";
                    continue;
                }

                // Cek duplikasi NISN
                if ($this->cekNisnExists($data['nisn'])) {
                    $errors[] = "Baris " . ($index + 1) . ": NISN {$data['nisn']} sudah terdaftar";
                    continue;
                }

                // Insert siswa
                $this->db->query('INSERT INTO siswa (nisn, nama_siswa, jenis_kelamin, tgl_lahir, status_siswa) 
                                 VALUES (:nisn, :nama, :jk, :tgl, "aktif")');
                $this->db->bind('nisn', $data['nisn']);
                $this->db->bind('nama', trim($data['nama_siswa']));
                $this->db->bind('jk', strtoupper($data['jenis_kelamin']));
                $this->db->bind('tgl', $data['tgl_lahir'] ?? null);

                if ($this->db->execute()) {
                    $newId = $this->db->lastInsertId();
                    $successCount++;
                    $createdIds[] = $newId;
                } else {
                    $errors[] = "Baris " . ($index + 1) . ": Gagal menyimpan data {$data['nama_siswa']}";
                }

            } catch (Exception $e) {
                $errors[] = "Baris " . ($index + 1) . ": " . $e->getMessage();
                error_log("Batch insert error for row " . ($index + 1) . ": " . $e->getMessage());
            }
        }

        return [
            'success' => $successCount,
            'errors' => $errors,
            'ids' => $createdIds,
            'total_processed' => count($dataSiswa)
        ];
    }

    /**
     * Cek multiple NISN sekaligus untuk validasi batch
     */
    public function cekMultipleNisnExists($arrayNisn)
    {
        if (empty($arrayNisn))
            return [];

        try {
            $placeholders = implode(',', array_fill(0, count($arrayNisn), '?'));
            $this->db->query("SELECT nisn FROM siswa WHERE nisn IN ($placeholders)");

            foreach ($arrayNisn as $k => $nisn) {
                $this->db->bind($k + 1, $nisn);
            }

            $result = $this->db->resultSet();
            return array_column($result, 'nisn');
        } catch (Exception $e) {
            error_log("cekMultipleNisnExists error: " . $e->getMessage());
            return $arrayNisn; // Return semua sebagai existing untuk safety
        }
    }

    /**
     * Get semua NISN yang ada untuk validasi import
     */
    public function getAllNisn()
    {
        try {
            $this->db->query('SELECT nisn FROM siswa WHERE nisn IS NOT NULL AND nisn != ""');
            $result = $this->db->resultSet();
            return array_column($result, 'nisn');
        } catch (Exception $e) {
            error_log("getAllNisn error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Update status siswa (aktif/lulus/pindah)
     */
    public function updateStatusSiswa($id_siswa, $status)
    {
        $allowedStatus = ['aktif', 'lulus', 'pindah'];

        if (!in_array($status, $allowedStatus)) {
            return false;
        }

        try {
            $this->db->query('UPDATE siswa SET status_siswa = :status WHERE id_siswa = :id');
            $this->db->bind('status', $status);
            $this->db->bind('id', $id_siswa);
            $this->db->execute();
            return $this->db->rowCount();
        } catch (Exception $e) {
            error_log("updateStatusSiswa error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get siswa by status
     */
    public function getSiswaByStatus($status = 'aktif')
    {
        try {
            $this->db->query('SELECT s.*, u.password_plain 
                             FROM siswa s 
                             LEFT JOIN users u ON s.id_siswa = u.id_ref AND u.role = "siswa" 
                             WHERE s.status_siswa = :status
                             ORDER BY s.nama_siswa ASC');
            $this->db->bind('status', $status);
            return $this->db->resultSet();
        } catch (Exception $e) {
            error_log("getSiswaByStatus error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Search siswa by nama or NISN
     */
    public function searchSiswa($keyword)
    {
        try {
            $this->db->query('SELECT s.*, u.password_plain 
                             FROM siswa s 
                             LEFT JOIN users u ON s.id_siswa = u.id_ref AND u.role = "siswa" 
                             WHERE (s.nama_siswa LIKE :keyword OR s.nisn LIKE :keyword)
                             AND s.status_siswa = "aktif"
                             ORDER BY s.nama_siswa ASC');
            $this->db->bind('keyword', '%' . $keyword . '%');
            return $this->db->resultSet();
        } catch (Exception $e) {
            error_log("searchSiswa error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get statistik siswa untuk dashboard
     */
    public function getStatistikSiswa()
    {
        try {
            $stats = [];

            // Total siswa aktif
            $this->db->query('SELECT COUNT(*) as total FROM siswa WHERE status_siswa = "aktif"');
            $stats['total_aktif'] = $this->db->single()['total'];

            // Total siswa lulus
            $this->db->query('SELECT COUNT(*) as total FROM siswa WHERE status_siswa = "lulus"');
            $stats['total_lulus'] = $this->db->single()['total'];

            // Total siswa pindah
            $this->db->query('SELECT COUNT(*) as total FROM siswa WHERE status_siswa = "pindah"');
            $stats['total_pindah'] = $this->db->single()['total'];

            // Total siswa dengan akun
            $this->db->query('SELECT COUNT(*) as total FROM siswa s 
                             JOIN users u ON s.id_siswa = u.id_ref 
                             WHERE u.role = "siswa" AND s.status_siswa = "aktif"');
            $stats['total_dengan_akun'] = $this->db->single()['total'];

            // Total siswa tanpa akun
            $stats['total_tanpa_akun'] = $stats['total_aktif'] - $stats['total_dengan_akun'];

            // Per jenis kelamin
            $this->db->query('SELECT jenis_kelamin, COUNT(*) as total 
                             FROM siswa 
                             WHERE status_siswa = "aktif" 
                             GROUP BY jenis_kelamin');
            $jkData = $this->db->resultSet();

            $stats['laki_laki'] = 0;
            $stats['perempuan'] = 0;
            foreach ($jkData as $jk) {
                if ($jk['jenis_kelamin'] === 'L') {
                    $stats['laki_laki'] = $jk['total'];
                } elseif ($jk['jenis_kelamin'] === 'P') {
                    $stats['perempuan'] = $jk['total'];
                }
            }

            return $stats;
        } catch (Exception $e) {
            error_log("getStatistikSiswa error: " . $e->getMessage());
            return [
                'total_aktif' => 0,
                'total_lulus' => 0,
                'total_pindah' => 0,
                'total_dengan_akun' => 0,
                'total_tanpa_akun' => 0,
                'laki_laki' => 0,
                'perempuan' => 0
            ];
        }
    }

    /**
     * Batch delete siswa berdasarkan array ID
     */
    public function batchDeleteSiswa($arrayIds)
    {
        if (empty($arrayIds))
            return 0;

        try {
            $placeholders = implode(',', array_fill(0, count($arrayIds), '?'));
            $this->db->query("DELETE FROM siswa WHERE id_siswa IN ($placeholders)");

            foreach ($arrayIds as $k => $id) {
                $this->db->bind($k + 1, $id);
            }

            $this->db->execute();
            return $this->db->rowCount();
        } catch (Exception $e) {
            error_log("batchDeleteSiswa error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get siswa yang belum memiliki akun user
     */
    public function getSiswaWithoutAccount()
    {
        try {
            $this->db->query('SELECT s.* 
                             FROM siswa s 
                             LEFT JOIN users u ON s.id_siswa = u.id_ref AND u.role = "siswa"
                             WHERE u.id_user IS NULL AND s.status_siswa = "aktif"
                             ORDER BY s.nama_siswa ASC');
            return $this->db->resultSet();
        } catch (Exception $e) {
            error_log("getSiswaWithoutAccount error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Validasi data import Excel
     */
    public function validateImportData($excelData)
    {
        $validData = [];
        $errors = [];
        $warnings = [];

        // Ambil NISN yang sudah ada
        $existingNisn = $this->getAllNisn();
        $currentBatchNisn = [];

        foreach ($excelData as $index => $row) {
            $rowErrors = [];
            $rowWarnings = [];
            $rowNum = $index + 1;

            // Sanitize data
            $cleanData = [
                'nisn' => trim($row['nisn'] ?? ''),
                'nama_siswa' => trim($row['nama_siswa'] ?? ''),
                'jenis_kelamin' => strtoupper(trim($row['jenis_kelamin'] ?? '')),
                'password' => trim($row['password'] ?? ''),
                'tgl_lahir' => $row['tgl_lahir'] ?? null
            ];

            // Validasi NISN
            if (empty($cleanData['nisn'])) {
                $rowErrors[] = "NISN tidak boleh kosong";
            } elseif (!preg_match('/^\d+$/', $cleanData['nisn'])) {
                $rowErrors[] = "NISN harus berisi angka";
            } elseif (in_array($cleanData['nisn'], $existingNisn)) {
                $rowErrors[] = "NISN sudah terdaftar di database";
            } elseif (in_array($cleanData['nisn'], $currentBatchNisn)) {
                $rowErrors[] = "NISN duplikat dalam file Excel";
            } else {
                $currentBatchNisn[] = $cleanData['nisn'];

                // Warning jika NISN bukan 10 digit
                if (strlen($cleanData['nisn']) != 10) {
                    $rowWarnings[] = "NISN sebaiknya 10 digit";
                }
            }

            // Validasi Nama
            if (empty($cleanData['nama_siswa'])) {
                $rowErrors[] = "Nama siswa tidak boleh kosong";
            } elseif (strlen($cleanData['nama_siswa']) < 2) {
                $rowErrors[] = "Nama siswa minimal 2 karakter";
            } elseif (strlen($cleanData['nama_siswa']) > 100) {
                $rowErrors[] = "Nama siswa maksimal 100 karakter";
            }

            // Validasi Jenis Kelamin
            if (empty($cleanData['jenis_kelamin'])) {
                $rowErrors[] = "Jenis kelamin tidak boleh kosong";
            } else {
                $jk = strtoupper($cleanData['jenis_kelamin']);
                if (in_array($jk, ['L', 'LAKI-LAKI', 'LAKI', 'M', 'MALE'])) {
                    $cleanData['jenis_kelamin'] = 'L';
                    if ($jk !== 'L') {
                        $rowWarnings[] = "Jenis kelamin dinormalisasi menjadi 'L'";
                    }
                } elseif (in_array($jk, ['P', 'PEREMPUAN', 'WANITA', 'F', 'FEMALE'])) {
                    $cleanData['jenis_kelamin'] = 'P';
                    if ($jk !== 'P') {
                        $rowWarnings[] = "Jenis kelamin dinormalisasi menjadi 'P'";
                    }
                } else {
                    $rowErrors[] = "Jenis kelamin harus L atau P";
                }
            }

            // Validasi Password
            if (empty($cleanData['password'])) {
                $rowErrors[] = "Password tidak boleh kosong";
            } elseif (strlen($cleanData['password']) < 6) {
                $rowErrors[] = "Password minimal 6 karakter";
            } elseif (strlen($cleanData['password']) > 255) {
                $rowErrors[] = "Password maksimal 255 karakter";
            }

            // Validasi Tanggal Lahir (opsional)
            if (!empty($cleanData['tgl_lahir'])) {
                if (!$this->validateDate($cleanData['tgl_lahir'])) {
                    $rowWarnings[] = "Format tanggal lahir tidak valid, akan diabaikan";
                    $cleanData['tgl_lahir'] = null;
                }
            }

            if (empty($rowErrors)) {
                $validData[] = $cleanData;
            } else {
                $errors[] = "Baris {$rowNum}: " . implode(', ', $rowErrors);
            }

            if (!empty($rowWarnings)) {
                $warnings[] = "Baris {$rowNum}: " . implode(', ', $rowWarnings);
            }
        }

        return [
            'valid_data' => $validData,
            'valid_count' => count($validData),
            'error_count' => count($errors),
            'warning_count' => count($warnings),
            'errors' => $errors,
            'warnings' => $warnings
        ];
    }

    /**
     * Validasi format tanggal
     */
    private function validateDate($date)
    {
        if (empty($date))
            return true;

        $formats = ['Y-m-d', 'd/m/Y', 'd-m-Y', 'Y/m/d', 'm/d/Y'];

        foreach ($formats as $format) {
            $dateObj = DateTime::createFromFormat($format, $date);
            if ($dateObj && $dateObj->format($format) === $date) {
                return true;
            }
        }
        return false;
    }

    /**
     * Import siswa dengan auto-create user accounts (sesuai schema database)
     */
    public function importSiswaWithAccounts($validData)
    {
        $successCount = 0;
        $errorCount = 0;
        $errors = [];
        $createdIds = [];

        foreach ($validData as $index => $data) {
            try {
                // 1. Insert siswa
                $idSiswaBaru = $this->tambahDataSiswa($data);

                if ($idSiswaBaru) {
                    // 2. Buat akun user - gunakan Database class langsung untuk konsistensi
                    $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

                    $this->db->query('INSERT INTO users (username, password, password_plain, nama_lengkap, role, id_ref, status) 
                                     VALUES (:username, :password, :password_plain, :nama_lengkap, :role, :id_ref, "aktif")');
                    $this->db->bind('username', $data['nisn']);
                    $this->db->bind('password', $hashedPassword);
                    $this->db->bind('password_plain', $data['password']);
                    $this->db->bind('nama_lengkap', $data['nama_siswa']);
                    $this->db->bind('role', 'siswa');
                    $this->db->bind('id_ref', $idSiswaBaru);

                    if ($this->db->execute()) {
                        $successCount++;
                        $createdIds[] = $idSiswaBaru;
                    } else {
                        // Rollback siswa jika gagal buat akun
                        $this->hapusDataSiswa($idSiswaBaru);
                        $errorCount++;
                        $errors[] = "Baris " . ($index + 1) . ": Gagal membuat akun untuk {$data['nama_siswa']}";
                    }
                } else {
                    $errorCount++;
                    $errors[] = "Baris " . ($index + 1) . ": Gagal menyimpan data {$data['nama_siswa']}";
                }

            } catch (Exception $e) {
                $errorCount++;
                $errors[] = "Baris " . ($index + 1) . ": " . $e->getMessage();
                error_log("Import with accounts error: " . $e->getMessage());
            }
        }

        return [
            'success' => $successCount > 0,
            'success_count' => $successCount,
            'error_count' => $errorCount,
            'total_processed' => count($validData),
            'errors' => $errors,
            'created_ids' => $createdIds,
            'message' => "Import selesai. {$successCount} siswa berhasil ditambahkan dengan akun."
        ];
    }

    /**
     * Generate password default untuk siswa
     */
    public function generateDefaultPassword($nisn, $nama)
    {
        // Format: 3 digit terakhir NISN + 3 huruf pertama nama (lowercase)
        $lastDigits = substr($nisn, -3);
        $namePrefix = strtolower(substr(preg_replace('/[^a-zA-Z]/', '', $nama), 0, 3));
        return $lastDigits . $namePrefix . '123'; // Tambah 123 untuk keamanan
    }

    /**
     * Cleanup data siswa yang tidak valid
     */
    public function cleanupInvalidData()
    {
        try {
            $cleanupCount = 0;

            // Hapus siswa dengan NISN kosong atau null
            $this->db->query('DELETE FROM siswa WHERE nisn IS NULL OR nisn = ""');
            $this->db->execute();
            $cleanupCount += $this->db->rowCount();

            // Hapus siswa dengan nama kosong
            $this->db->query('DELETE FROM siswa WHERE nama_siswa IS NULL OR nama_siswa = ""');
            $this->db->execute();
            $cleanupCount += $this->db->rowCount();

            return $cleanupCount;
        } catch (Exception $e) {
            error_log("cleanupInvalidData error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get siswa untuk export dengan format yang bersih
     */
    public function getSiswaForExport()
    {
        try {
            $this->db->query('SELECT 
                                s.nisn,
                                s.nama_siswa,
                                CASE 
                                    WHEN s.jenis_kelamin = "L" THEN "Laki-laki"
                                    WHEN s.jenis_kelamin = "P" THEN "Perempuan"
                                    ELSE s.jenis_kelamin
                                END as jenis_kelamin_display,
                                s.jenis_kelamin,
                                s.tgl_lahir,
                                s.status_siswa,
                                u.password_plain,
                                CASE 
                                    WHEN u.password_plain IS NOT NULL THEN "Ada"
                                    ELSE "Belum Ada"
                                END as status_akun
                             FROM siswa s 
                             LEFT JOIN users u ON s.id_siswa = u.id_ref AND u.role = "siswa" 
                             ORDER BY s.status_siswa, s.nama_siswa ASC');
            return $this->db->resultSet();
        } catch (Exception $e) {
            error_log("getSiswaForExport error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Update NISN untuk siswa (dengan validasi unik)
     */
    public function updateNisn($id_siswa, $nisn_baru)
    {
        try {
            // Cek apakah NISN baru sudah ada (kecuali untuk siswa yang sama)
            $this->db->query('SELECT COUNT(*) as total FROM siswa WHERE nisn = :nisn AND id_siswa != :id');
            $this->db->bind('nisn', $nisn_baru);
            $this->db->bind('id', $id_siswa);
            $result = $this->db->single();

            if ($result['total'] > 0) {
                return ['success' => false, 'error' => 'NISN sudah digunakan siswa lain'];
            }

            // Update NISN siswa
            $this->db->query('UPDATE siswa SET nisn = :nisn WHERE id_siswa = :id');
            $this->db->bind('nisn', $nisn_baru);
            $this->db->bind('id', $id_siswa);
            $this->db->execute();

            if ($this->db->rowCount() > 0) {
                // Update username di tabel users juga
                $this->db->query('UPDATE users SET username = :username WHERE id_ref = :id_ref AND role = "siswa"');
                $this->db->bind('username', $nisn_baru);
                $this->db->bind('id_ref', $id_siswa);
                $this->db->execute();

                return ['success' => true, 'message' => 'NISN berhasil diupdate'];
            } else {
                return ['success' => false, 'error' => 'Gagal update NISN'];
            }
        } catch (Exception $e) {
            error_log("updateNisn error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Database error: ' . $e->getMessage()];
        }
    }

    /**
     * Get duplicate NISN dalam database
     */
    public function getDuplicateNisn()
    {
        try {
            $this->db->query('SELECT nisn, COUNT(*) as jumlah 
                             FROM siswa 
                             WHERE nisn IS NOT NULL AND nisn != ""
                             GROUP BY nisn 
                             HAVING COUNT(*) > 1
                             ORDER BY jumlah DESC, nisn');
            return $this->db->resultSet();
        } catch (Exception $e) {
            error_log("getDuplicateNisn error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Fix duplicate NISN dengan auto-generate NISN baru
     */
    public function fixDuplicateNisn()
    {
        try {
            $duplicates = $this->getDuplicateNisn();
            $fixedCount = 0;

            foreach ($duplicates as $duplicate) {
                $nisn = $duplicate['nisn'];

                // Ambil semua siswa dengan NISN duplikat
                $this->db->query('SELECT * FROM siswa WHERE nisn = :nisn ORDER BY id_siswa');
                $this->db->bind('nisn', $nisn);
                $siswaList = $this->db->resultSet();

                // Skip siswa pertama (biarkan tetap), fix yang lainnya
                for ($i = 1; $i < count($siswaList); $i++) {
                    $siswa = $siswaList[$i];
                    $newNisn = $this->generateUniqueNisn($nisn, $i);

                    // Update NISN
                    $this->db->query('UPDATE siswa SET nisn = :new_nisn WHERE id_siswa = :id');
                    $this->db->bind('new_nisn', $newNisn);
                    $this->db->bind('id', $siswa['id_siswa']);

                    if ($this->db->execute()) {
                        // Update username di users juga
                        $this->db->query('UPDATE users SET username = :username WHERE id_ref = :id_ref AND role = "siswa"');
                        $this->db->bind('username', $newNisn);
                        $this->db->bind('id_ref', $siswa['id_siswa']);
                        $this->db->execute();

                        $fixedCount++;
                    }
                }
            }

            return $fixedCount;
        } catch (Exception $e) {
            error_log("fixDuplicateNisn error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Generate NISN unik untuk fix duplikasi
     */
    private function generateUniqueNisn($originalNisn, $suffix)
    {
        $newNisn = $originalNisn . sprintf('%02d', $suffix);

        // Pastikan tidak duplikat lagi
        if ($this->cekNisnExists($newNisn)) {
            // Jika masih duplikat, tambah timestamp
            $newNisn = substr($originalNisn, 0, 7) . date('His');
        }

        return $newNisn;
    }

    /**
     * Update data siswa lengkap (untuk wali kelas)
     */
    public function updateSiswa($id_siswa, $data)
    {
        $this->db->query('UPDATE siswa SET 
            nisn = :nisn,
            nik = :nik,
            nama_siswa = :nama_siswa,
            jenis_kelamin = :jenis_kelamin,
            agama = :agama,
            tgl_lahir = :tgl_lahir,
            tempat_lahir = :tempat_lahir,
            anak_ke = :anak_ke,
            jumlah_saudara = :jumlah_saudara,
            hobi = :hobi,
            cita_cita = :cita_cita,
            alamat = :alamat,
            rt = :rt,
            rw = :rw,
            dusun = :dusun,
            kelurahan = :kelurahan,
            kecamatan = :kecamatan,
            kabupaten = :kabupaten,
            provinsi = :provinsi,
            kode_pos = :kode_pos,
            id_provinsi = :id_provinsi,
            id_kabupaten = :id_kabupaten,
            id_kecamatan = :id_kecamatan,
            id_kelurahan = :id_kelurahan,
            status_tempat_tinggal = :status_tempat_tinggal,
            jarak_ke_sekolah = :jarak_ke_sekolah,
            transportasi = :transportasi,
            no_wa = :no_wa,
            email = :email,
            ayah_kandung = :ayah_kandung,
            ayah_nik = :ayah_nik,
            ayah_tempat_lahir = :ayah_tempat_lahir,
            ayah_tanggal_lahir = :ayah_tanggal_lahir,
            ayah_status = :ayah_status,
            ayah_pendidikan = :ayah_pendidikan,
            ayah_pekerjaan = :ayah_pekerjaan,
            ayah_penghasilan = :ayah_penghasilan,
            ayah_no_hp = :ayah_no_hp,
            ibu_kandung = :ibu_kandung,
            ibu_nik = :ibu_nik,
            ibu_tempat_lahir = :ibu_tempat_lahir,
            ibu_tanggal_lahir = :ibu_tanggal_lahir,
            ibu_status = :ibu_status,
            ibu_pendidikan = :ibu_pendidikan,
            ibu_pekerjaan = :ibu_pekerjaan,
            ibu_penghasilan = :ibu_penghasilan,
            ibu_no_hp = :ibu_no_hp,
            wali_nama = :wali_nama,
            wali_hubungan = :wali_hubungan,
            wali_nik = :wali_nik,
            wali_no_hp = :wali_no_hp,
            wali_pendidikan = :wali_pendidikan,
            wali_pekerjaan = :wali_pekerjaan,
            wali_penghasilan = :wali_penghasilan
            WHERE id_siswa = :id_siswa');

        $this->db->bind('nisn', $data['nisn']);
        $this->db->bind('nik', $data['nik'] ?? null);
        $this->db->bind('nama_siswa', $data['nama_siswa']);
        $this->db->bind('jenis_kelamin', $data['jenis_kelamin']);
        $this->db->bind('agama', $data['agama'] ?? null);
        $this->db->bind('tgl_lahir', $data['tanggal_lahir'] ?? $data['tgl_lahir'] ?? null);
        $this->db->bind('tempat_lahir', $data['tempat_lahir'] ?? null);
        $this->db->bind('anak_ke', $data['anak_ke'] ?? null);
        $this->db->bind('jumlah_saudara', $data['jumlah_saudara'] ?? null);
        $this->db->bind('hobi', $data['hobi'] ?? null);
        $this->db->bind('cita_cita', $data['cita_cita'] ?? null);
        $this->db->bind('alamat', $data['alamat'] ?? null);
        $this->db->bind('rt', $data['rt'] ?? null);
        $this->db->bind('rw', $data['rw'] ?? null);
        $this->db->bind('dusun', $data['dusun'] ?? null);
        $this->db->bind('kelurahan', $data['kelurahan'] ?? null);
        $this->db->bind('kecamatan', $data['kecamatan'] ?? null);
        $this->db->bind('kabupaten', $data['kabupaten'] ?? null);
        $this->db->bind('provinsi', $data['provinsi'] ?? null);
        $this->db->bind('kode_pos', $data['kode_pos'] ?? null);
        $this->db->bind('id_provinsi', $data['id_provinsi'] ?? null);
        $this->db->bind('id_kabupaten', $data['id_kabupaten'] ?? null);
        $this->db->bind('id_kecamatan', $data['id_kecamatan'] ?? null);
        $this->db->bind('id_kelurahan', $data['id_kelurahan'] ?? null);
        $this->db->bind('status_tempat_tinggal', $data['status_tempat_tinggal'] ?? null);
        $this->db->bind('jarak_ke_sekolah', $data['jarak_ke_sekolah'] ?? null);
        $this->db->bind('transportasi', $data['transportasi'] ?? null);
        $this->db->bind('no_wa', $data['no_wa'] ?? null);
        $this->db->bind('email', $data['email'] ?? null);
        $this->db->bind('ayah_kandung', $data['ayah_kandung'] ?? null);
        $this->db->bind('ayah_nik', $data['ayah_nik'] ?? null);
        $this->db->bind('ayah_tempat_lahir', $data['ayah_tempat_lahir'] ?? null);
        $this->db->bind('ayah_tanggal_lahir', $data['ayah_tanggal_lahir'] ?? null);
        $this->db->bind('ayah_status', $data['ayah_status'] ?? null);
        $this->db->bind('ayah_pendidikan', $data['ayah_pendidikan'] ?? null);
        $this->db->bind('ayah_pekerjaan', $data['ayah_pekerjaan'] ?? null);
        $this->db->bind('ayah_penghasilan', $data['ayah_penghasilan'] ?? null);
        $this->db->bind('ayah_no_hp', $data['ayah_no_hp'] ?? null);
        $this->db->bind('ibu_kandung', $data['ibu_kandung'] ?? null);
        $this->db->bind('ibu_nik', $data['ibu_nik'] ?? null);
        $this->db->bind('ibu_tempat_lahir', $data['ibu_tempat_lahir'] ?? null);
        $this->db->bind('ibu_tanggal_lahir', $data['ibu_tanggal_lahir'] ?? null);
        $this->db->bind('ibu_status', $data['ibu_status'] ?? null);
        $this->db->bind('ibu_pendidikan', $data['ibu_pendidikan'] ?? null);
        $this->db->bind('ibu_pekerjaan', $data['ibu_pekerjaan'] ?? null);
        $this->db->bind('ibu_penghasilan', $data['ibu_penghasilan'] ?? null);
        $this->db->bind('ibu_no_hp', $data['ibu_no_hp'] ?? null);
        $this->db->bind('wali_nama', $data['wali_nama'] ?? null);
        $this->db->bind('wali_hubungan', $data['wali_hubungan'] ?? null);
        $this->db->bind('wali_nik', $data['wali_nik'] ?? null);
        $this->db->bind('wali_no_hp', $data['wali_no_hp'] ?? null);
        $this->db->bind('wali_pendidikan', $data['wali_pendidikan'] ?? null);
        $this->db->bind('wali_pekerjaan', $data['wali_pekerjaan'] ?? null);
        $this->db->bind('wali_penghasilan', $data['wali_penghasilan'] ?? null);
        $this->db->bind('id_siswa', $id_siswa);

        $this->db->execute();
        return $this->db->rowCount() > 0;
    }

    // =================================================================
    // DOCUMENT MANAGEMENT METHODS
    // =================================================================

    /**
     * Get all documents for a student
     */
    public function getDokumenSiswa($id_siswa)
    {
        $this->db->query('SELECT * FROM siswa_dokumen WHERE id_siswa = :id ORDER BY jenis_dokumen');
        $this->db->bind('id', $id_siswa);
        return $this->db->resultSet();
    }

    /**
     * Get document by ID
     */
    public function getDokumenById($id_dokumen)
    {
        $this->db->query('SELECT * FROM siswa_dokumen WHERE id_dokumen = :id');
        $this->db->bind('id', $id_dokumen);
        return $this->db->single();
    }

    /**
     * Save or update document (upsert by jenis_dokumen)
     */
    public function saveDokumenSiswa($id_siswa, $data)
    {
        // Check if document already exists for this type
        $this->db->query('SELECT id_dokumen FROM siswa_dokumen WHERE id_siswa = :id AND jenis_dokumen = :jenis');
        $this->db->bind('id', $id_siswa);
        $this->db->bind('jenis', $data['jenis_dokumen']);
        $existing = $this->db->single();

        $hasDriveFields = isset($data['drive_file_id']) && !empty($data['drive_file_id']);

        if ($existing) {
            // Update existing
            if ($hasDriveFields) {
                $this->db->query('UPDATE siswa_dokumen SET nama_file = :nama, path_file = :path, ukuran = :ukuran, 
                                 drive_file_id = :drive_id, drive_url = :drive_url,
                                 status = "pending", uploaded_at = NOW() WHERE id_dokumen = :id');
                $this->db->bind('drive_id', $data['drive_file_id']);
                $this->db->bind('drive_url', $data['drive_url']);
            } else {
                $this->db->query('UPDATE siswa_dokumen SET nama_file = :nama, path_file = :path, ukuran = :ukuran, 
                                 status = "pending", uploaded_at = NOW() WHERE id_dokumen = :id');
            }
            $this->db->bind('nama', $data['nama_file']);
            $this->db->bind('path', $data['path_file']);
            $this->db->bind('ukuran', $data['ukuran'] ?? 0);
            $this->db->bind('id', $existing['id_dokumen']);
        } else {
            // Insert new
            if ($hasDriveFields) {
                $this->db->query('INSERT INTO siswa_dokumen (id_siswa, jenis_dokumen, nama_file, path_file, ukuran, drive_file_id, drive_url) 
                                 VALUES (:id_siswa, :jenis, :nama, :path, :ukuran, :drive_id, :drive_url)');
                $this->db->bind('drive_id', $data['drive_file_id']);
                $this->db->bind('drive_url', $data['drive_url']);
            } else {
                $this->db->query('INSERT INTO siswa_dokumen (id_siswa, jenis_dokumen, nama_file, path_file, ukuran) 
                                 VALUES (:id_siswa, :jenis, :nama, :path, :ukuran)');
            }
            $this->db->bind('id_siswa', $id_siswa);
            $this->db->bind('jenis', $data['jenis_dokumen']);
            $this->db->bind('nama', $data['nama_file']);
            $this->db->bind('path', $data['path_file']);
            $this->db->bind('ukuran', $data['ukuran'] ?? 0);
        }
        return $this->db->execute();
    }

    /**
     * Delete dokumen siswa
     */
    public function deleteDokumenSiswa($id_dokumen)
    {
        $this->db->query('DELETE FROM siswa_dokumen WHERE id_dokumen = :id');
        $this->db->bind('id', $id_dokumen);
        return $this->db->execute();
    }

    /**
     * Count documents for a student
     */
    public function countDokumenSiswa($id_siswa)
    {
        $this->db->query('SELECT COUNT(*) as total FROM siswa_dokumen WHERE id_siswa = :id');
        $this->db->bind('id', $id_siswa);
        $result = $this->db->single();
        return $result['total'] ?? 0;
    }

    // =================================================================
    // PSB TO SISWA MIGRATION
    // =================================================================

    /**
     * Get list of PSB applicants with status 'diterima' that haven't been migrated yet
     */
    public function getPendaftarDiterima()
    {
        $this->db->query('
            SELECT p.*, l.nama_lembaga 
            FROM psb_pendaftar p
            LEFT JOIN psb_lembaga l ON p.id_lembaga = l.id_lembaga
            WHERE p.status = "diterima"
            AND p.nisn NOT IN (SELECT nisn FROM siswa WHERE nisn IS NOT NULL)
            ORDER BY p.nama_lengkap
        ');
        return $this->db->resultSet();
    }

    /**
     * Transfer PSB pendaftar data to siswa (including documents)
     * @param int $id_pendaftar
     * @return array ['success' => bool, 'message' => string, 'id_siswa' => int|null]
     */
    public function tarikDariPSB($id_pendaftar)
    {
        try {
            // Get PSB data
            $this->db->query('SELECT * FROM psb_pendaftar WHERE id_pendaftar = :id');
            $this->db->bind('id', $id_pendaftar);
            $psb = $this->db->single();

            if (!$psb) {
                return ['success' => false, 'message' => 'Data pendaftar tidak ditemukan', 'id_siswa' => null];
            }

            // Check if NISN already exists in siswa
            if ($this->cekNisnExists($psb['nisn'])) {
                return ['success' => false, 'message' => 'NISN sudah terdaftar sebagai siswa', 'id_siswa' => null];
            }

            // Map PSB fields to Siswa fields
            $dataSiswa = [
                'nisn' => $psb['nisn'],
                'nik' => $psb['nik'] ?? null,
                'nama_siswa' => $psb['nama_lengkap'],
                'jenis_kelamin' => $psb['jenis_kelamin'],
                'tempat_lahir' => $psb['tempat_lahir'] ?? null,
                'tgl_lahir' => $psb['tanggal_lahir'] ?? null,
                'agama' => $psb['agama'] ?? null,
                'anak_ke' => $psb['anak_ke'] ?? 1,
                'jumlah_saudara' => $psb['jumlah_saudara'] ?? 0,
                'hobi' => $psb['hobi'] ?? null,
                'cita_cita' => $psb['cita_cita'] ?? null,
                'kip' => $psb['kip'] ?? null,
                'yang_membiayai' => $psb['yang_membiayai'] ?? 'Orang Tua',
                'kebutuhan_khusus' => $psb['kebutuhan_khusus'] ?? 'Tidak Ada',
                'kebutuhan_disabilitas' => $psb['kebutuhan_disabilitas'] ?? 'Tidak Ada',
                // Address
                'alamat' => $psb['alamat'] ?? null,
                'rt' => $psb['rt'] ?? null,
                'rw' => $psb['rw'] ?? null,
                'dusun' => $psb['dusun'] ?? null,
                'kelurahan' => $psb['kelurahan'] ?? null,
                'kecamatan' => $psb['kecamatan'] ?? null,
                'kabupaten' => $psb['kabupaten'] ?? null,
                'provinsi' => $psb['provinsi'] ?? null,
                'kode_pos' => $psb['kode_pos'] ?? null,
                'status_tempat_tinggal' => $psb['status_tempat_tinggal'] ?? null,
                'jarak_ke_sekolah' => $psb['jarak_ke_sekolah'] ?? null,
                'transportasi' => $psb['transportasi'] ?? null,
                // Contact
                'no_wa' => $psb['no_hp'] ?? null,
                'email' => $psb['email'] ?? null,
                // Father
                'ayah_kandung' => $psb['ayah_nama'] ?? null,
                'ayah_nik' => $psb['ayah_nik'] ?? null,
                'ayah_tempat_lahir' => $psb['ayah_tempat_lahir'] ?? null,
                'ayah_tanggal_lahir' => $psb['ayah_tanggal_lahir'] ?? null,
                'ayah_status' => $psb['ayah_status'] ?? 'Masih Hidup',
                'ayah_pendidikan' => $psb['ayah_pendidikan'] ?? null,
                'ayah_pekerjaan' => $psb['ayah_pekerjaan'] ?? null,
                'ayah_penghasilan' => $psb['ayah_penghasilan'] ?? null,
                'ayah_no_hp' => $psb['ayah_no_hp'] ?? null,
                // Mother
                'ibu_kandung' => $psb['ibu_nama'] ?? null,
                'ibu_nik' => $psb['ibu_nik'] ?? null,
                'ibu_tempat_lahir' => $psb['ibu_tempat_lahir'] ?? null,
                'ibu_tanggal_lahir' => $psb['ibu_tanggal_lahir'] ?? null,
                'ibu_status' => $psb['ibu_status'] ?? 'Masih Hidup',
                'ibu_pendidikan' => $psb['ibu_pendidikan'] ?? null,
                'ibu_pekerjaan' => $psb['ibu_pekerjaan'] ?? null,
                'ibu_penghasilan' => $psb['ibu_penghasilan'] ?? null,
                'ibu_no_hp' => $psb['ibu_no_hp'] ?? null,
                // Guardian
                'wali_nama' => $psb['wali_nama'] ?? null,
                'wali_nik' => $psb['wali_nik'] ?? null,
                'wali_hubungan' => $psb['wali_hubungan'] ?? null,
                'wali_pendidikan' => $psb['wali_pendidikan'] ?? null,
                'wali_pekerjaan' => $psb['wali_pekerjaan'] ?? null,
                'wali_penghasilan' => $psb['wali_penghasilan'] ?? null,
                'wali_no_hp' => $psb['wali_no_hp'] ?? null,
            ];

            // Insert into siswa
            $id_siswa = $this->tambahDataSiswa($dataSiswa);

            if (!$id_siswa) {
                return ['success' => false, 'message' => 'Gagal menyimpan data siswa', 'id_siswa' => null];
            }

            // Copy documents from psb_dokumen to siswa_dokumen
            $this->db->query('SELECT * FROM psb_dokumen WHERE id_pendaftar = :id');
            $this->db->bind('id', $id_pendaftar);
            $dokumenPSB = $this->db->resultSet();

            $docsCopied = 0;
            foreach ($dokumenPSB as $doc) {
                // Copy file
                $srcPath = APPROOT . '/uploads/psb/' . $doc['path_file'];
                $destFilename = $id_siswa . '_' . $doc['jenis_dokumen'] . '_' . time() . '.' . pathinfo($doc['path_file'], PATHINFO_EXTENSION);
                $destPath = APPROOT . '/uploads/siswa_dokumen/' . $destFilename;

                if (file_exists($srcPath)) {
                    if (copy($srcPath, $destPath)) {
                        // Insert document record
                        $this->db->query('INSERT INTO siswa_dokumen (id_siswa, jenis_dokumen, nama_file, path_file, ukuran) 
                                         VALUES (:id_siswa, :jenis, :nama, :path, :ukuran)');
                        $this->db->bind('id_siswa', $id_siswa);
                        $this->db->bind('jenis', $doc['jenis_dokumen']);
                        $this->db->bind('nama', $doc['nama_file']);
                        $this->db->bind('path', $destFilename);
                        $this->db->bind('ukuran', $doc['ukuran']);
                        $this->db->execute();
                        $docsCopied++;
                    }
                }
            }

            // Update PSB status to 'selesai'
            $this->db->query('UPDATE psb_pendaftar SET status = "selesai" WHERE id_pendaftar = :id');
            $this->db->bind('id', $id_pendaftar);
            $this->db->execute();

            return [
                'success' => true,
                'message' => 'Data berhasil ditarik. ' . $docsCopied . ' dokumen disalin.',
                'id_siswa' => $id_siswa
            ];

        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage(), 'id_siswa' => null];
        }
    }

    /**
     * Batch transfer multiple PSB pendaftar to siswa
     */
    public function tarikSemuaDariPSB()
    {
        $pendaftar = $this->getPendaftarDiterima();
        $results = ['total' => count($pendaftar), 'success' => 0, 'failed' => 0, 'errors' => []];

        foreach ($pendaftar as $p) {
            $result = $this->tarikDariPSB($p['id_pendaftar']);
            if ($result['success']) {
                $results['success']++;
            } else {
                $results['failed']++;
                $results['errors'][] = $p['nama_lengkap'] . ': ' . $result['message'];
            }
        }

        return $results;
    }
    /**
     * Get siswa with document upload status
     */
    /**
     * Get siswa with document upload status
     */
    public function getSiswaWithDocumentStatus($id_tp, $id_kelas = null, $keyword = null)
    {
        // 1. Prepare base query parts
        $whereConditions = "WHERE s.status_siswa = 'aktif'";
        $params = [':id_tp' => $id_tp];

        if ($id_kelas) {
            $whereConditions .= " AND k.id_kelas = :id_kelas";
            $params[':id_kelas'] = $id_kelas;
        }

        if ($keyword) {
            // Fix: Use unique parameter names for each placeholder because ATTR_EMULATE_PREPARES is false
            $whereConditions .= " AND (s.nama_siswa LIKE :keyword1 OR s.nisn LIKE :keyword2)";
            $params[':keyword1'] = "%$keyword%";
            $params[':keyword2'] = "%$keyword%";
        }

        $orderBy = "ORDER BY k.nama_kelas ASC, s.nama_siswa ASC";
        $joinLatest = "INNER JOIN (
                SELECT id_siswa, MAX(id_keanggotaan) AS max_kk
                FROM keanggotaan_kelas
                WHERE id_tp = :id_tp
                GROUP BY id_siswa
            ) latest ON kk.id_siswa = latest.id_siswa AND kk.id_keanggotaan = latest.max_kk";

        // 2. Try PRIMARY Query (with siswa_dokumen)
        $sqlPrimary = "
            SELECT s.id_siswa, s.nisn, s.nama_siswa, s.foto, k.nama_kelas,
                (SELECT COUNT(*) FROM siswa_dokumen sd WHERE sd.id_siswa = s.id_siswa) as jumlah_upload
            FROM siswa s
            JOIN keanggotaan_kelas kk ON s.id_siswa = kk.id_siswa
            JOIN kelas k ON kk.id_kelas = k.id_kelas
            $joinLatest
            $whereConditions
            $orderBy
        ";

        try {
            $this->db->query($sqlPrimary);
            foreach ($params as $key => $val) {
                $this->db->bind($key, $val);
            }
            return $this->db->resultSet();
        } catch (PDOException $e) {
            // 3. Fallback Query (WITHOUT siswa_dokumen) if table missing
            $sqlFallback = "
                SELECT s.id_siswa, s.nisn, s.nama_siswa, s.foto, k.nama_kelas,
                    0 as jumlah_upload
                FROM siswa s
                JOIN keanggotaan_kelas kk ON s.id_siswa = kk.id_siswa
                JOIN kelas k ON kk.id_kelas = k.id_kelas
                $joinLatest
                $whereConditions
                $orderBy
            ";

            $this->db->query($sqlFallback);
            foreach ($params as $key => $val) {
                $this->db->bind($key, $val);
            }
            return $this->db->resultSet();
        }
    }
}
