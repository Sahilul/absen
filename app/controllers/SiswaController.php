<?php

// File: app/controllers/SiswaController.php
class SiswaController extends Controller
{

    private $data = [];

    public function __construct()
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'siswa') {
            header('Location: ' . BASEURL . '/auth/login');
            exit;
        }
    }

    public function dashboard()
    {
        $this->data['judul'] = 'Dashboard Siswa';
        $id_siswa = $_SESSION['id_ref'];
        $id_semester_aktif = $_SESSION['id_semester_aktif'];

        // Data Absensi
        $this->data['rekap_absensi'] = $this->model('Absensi_model')->getRekapAbsensiSiswa($id_siswa, $id_semester_aktif);

        // Data Nilai - langsung query dengan struktur yang benar
        $db = new Database();

        // Get nilai siswa dengan rata-rata per mapel
        $db->query("
            SELECT 
                m.nama_mapel,
                AVG(n.nilai) as rata_nilai,
                COUNT(n.id_nilai) as jumlah_nilai
            FROM nilai_siswa n
            JOIN mapel m ON n.id_mapel = m.id_mapel
            WHERE n.id_siswa = :id_siswa 
            AND n.id_semester = :id_semester
            AND n.nilai > 0
            GROUP BY m.id_mapel, m.nama_mapel
            ORDER BY m.nama_mapel
        ");
        $db->bind('id_siswa', $id_siswa);
        $db->bind('id_semester', $id_semester_aktif);
        $this->data['nilai_per_mapel'] = $db->resultSet();

        $total_nilai = 0;
        $jumlah_mapel = 0;
        if (!empty($this->data['nilai_per_mapel'])) {
            foreach ($this->data['nilai_per_mapel'] as $nilai) {
                if (isset($nilai['rata_nilai']) && $nilai['rata_nilai'] > 0) {
                    $total_nilai += $nilai['rata_nilai'];
                    $jumlah_mapel++;
                }
            }
        }
        $this->data['rata_rata_nilai'] = $jumlah_mapel > 0 ? round($total_nilai / $jumlah_mapel, 2) : 0;
        $this->data['jumlah_mapel_dinilai'] = $jumlah_mapel;

        // Cek tagihan belum lunas
        $db->query("
            SELECT k.id_kelas, k.nama_kelas
            FROM keanggotaan_kelas kk
            JOIN kelas k ON kk.id_kelas = k.id_kelas
            WHERE kk.id_siswa = :id_siswa
            LIMIT 1
        ");
        $db->bind('id_siswa', $id_siswa);
        $kelasInfo = $db->single();
        $id_kelas = $kelasInfo['id_kelas'] ?? 0;

        $this->data['tagihan_belum_lunas'] = [];
        if ($id_kelas > 0) {
            $id_tp_aktif = $_SESSION['id_tp_aktif'] ?? 0;
            $db->query("
                SELECT 
                    pt.id,
                    pt.nama,
                    pt.nominal_default as nominal,
                    COALESCE(pts.diskon, 0) as diskon,
                    COALESCE(
                        (SELECT SUM(jumlah) 
                         FROM pembayaran_transaksi 
                         WHERE tagihan_id = pt.id 
                         AND id_siswa = :id_siswa), 
                        0
                    ) as total_terbayar
                FROM pembayaran_tagihan pt
                LEFT JOIN pembayaran_tagihan_siswa pts ON pt.id = pts.tagihan_id AND pts.id_siswa = :id_siswa2
                WHERE pt.id_kelas = :id_kelas 
                AND pt.id_tp = :id_tp
                AND (pt.id_semester = :id_semester OR pt.id_semester IS NULL)
                HAVING (nominal - diskon) > total_terbayar
                ORDER BY pt.created_at DESC
            ");
            $db->bind('id_siswa', $id_siswa);
            $db->bind('id_siswa2', $id_siswa);
            $db->bind('id_kelas', $id_kelas);
            $db->bind('id_tp', $id_tp_aktif);
            $db->bind('id_semester', $id_semester_aktif);
            $this->data['tagihan_belum_lunas'] = $db->resultSet();
        }

        // Render views
        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_siswa', $this->data);
        $this->view('siswa/dashboard', $this->data);
        $this->view('templates/footer', $this->data);
    }

    // Default method for SiswaController
    public function index()
    {
        // Redirect to dashboard or show a default page
        header('Location: ' . BASEURL . '/siswa/dashboard');
        exit;
    }

    public function absensiHarian()
    {
        $this->data['judul'] = 'Absensi Harian';
        $id_siswa = $_SESSION['id_ref'];
        $id_semester_aktif = $_SESSION['id_semester_aktif'];

        $this->data['absensi_harian'] = $this->model('Absensi_model')->getAbsensiHarianSiswa($id_siswa, $id_semester_aktif);

        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_siswa', $this->data);
        $this->view('siswa/absensi_harian', $this->data);
        $this->view('templates/footer', $this->data);
    }

    // FUNGSI BARU: Untuk menampilkan halaman rekap absensi per mapel
    public function rekapAbsensi()
    {
        $this->data['judul'] = 'Rekap Absensi';
        $id_siswa = $_SESSION['id_ref'];
        $id_semester_aktif = $_SESSION['id_semester_aktif'];

        $this->data['rekap_per_mapel'] = $this->model('Absensi_model')->getRekapAbsensiSiswaPerMapel($id_siswa, $id_semester_aktif);

        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_siswa', $this->data);
        $this->view('siswa/rekap_absensi', $this->data);
        $this->view('templates/footer', $this->data);
    }

    public function pembayaran()
    {
        $this->data['judul'] = 'Pembayaran';
        $id_siswa = $_SESSION['id_ref'];
        $id_semester_aktif = $_SESSION['id_semester_aktif'];
        $id_tp_aktif = $_SESSION['id_tp_aktif'] ?? 0;

        $db = new Database();

        // Get id_kelas siswa
        $db->query("
            SELECT k.id_kelas, k.nama_kelas
            FROM keanggotaan_kelas kk
            JOIN kelas k ON kk.id_kelas = k.id_kelas
            WHERE kk.id_siswa = :id_siswa
            LIMIT 1
        ");
        $db->bind('id_siswa', $id_siswa);
        $kelasInfo = $db->single();
        $id_kelas = $kelasInfo['id_kelas'] ?? 0;
        $this->data['nama_kelas'] = $kelasInfo['nama_kelas'] ?? '-';

        // Get tagihan kelas dengan detail transaksi
        $this->data['tagihan_siswa'] = [];
        if ($id_kelas > 0) {
            $db->query("
                SELECT 
                    pt.id,
                    pt.nama,
                    pt.nominal_default as nominal,
                    COALESCE(pts.diskon, 0) as diskon,
                    COALESCE(
                        (SELECT SUM(jumlah) 
                         FROM pembayaran_transaksi 
                         WHERE tagihan_id = pt.id 
                         AND id_siswa = :id_siswa), 
                        0
                    ) as total_terbayar,
                    pts.status,
                    pt.created_at as tanggal_tagihan
                FROM pembayaran_tagihan pt
                LEFT JOIN pembayaran_tagihan_siswa pts ON pt.id = pts.tagihan_id AND pts.id_siswa = :id_siswa2
                WHERE pt.id_kelas = :id_kelas 
                AND pt.id_tp = :id_tp
                AND (pt.id_semester = :id_semester OR pt.id_semester IS NULL)
                ORDER BY pt.created_at DESC
            ");
            $db->bind('id_siswa', $id_siswa);
            $db->bind('id_siswa2', $id_siswa);
            $db->bind('id_kelas', $id_kelas);
            $db->bind('id_tp', $id_tp_aktif);
            $db->bind('id_semester', $id_semester_aktif);
            $this->data['tagihan_siswa'] = $db->resultSet();

            // Get riwayat transaksi pembayaran
            $db->query("
                SELECT 
                    pt.id,
                    pt.id_siswa,
                    pt.tagihan_id,
                    pth.nama as nama_tagihan,
                    pt.jumlah,
                    pt.created_at
                FROM pembayaran_transaksi pt
                LEFT JOIN pembayaran_tagihan pth ON pt.tagihan_id = pth.id
                WHERE pt.id_siswa = :id_siswa
                ORDER BY pt.created_at DESC
            ");
            $db->bind('id_siswa', $id_siswa);
            $this->data['riwayat_transaksi'] = $db->resultSet();
        } else {
            $this->data['riwayat_transaksi'] = [];
        }

        // Hitung total tagihan dan terbayar
        $total_tagihan = 0;
        $total_terbayar = 0;
        $total_belum_bayar = 0;
        $jumlah_lunas = 0;
        $jumlah_belum_lunas = 0;

        if (!empty($this->data['tagihan_siswa'])) {
            foreach ($this->data['tagihan_siswa'] as $tagihan) {
                $nominal = isset($tagihan['nominal']) ? (int) $tagihan['nominal'] : 0;
                $diskon = isset($tagihan['diskon']) ? (int) $tagihan['diskon'] : 0;
                $terbayar = isset($tagihan['total_terbayar']) ? (int) $tagihan['total_terbayar'] : 0;

                $total_harus_bayar = $nominal - $diskon;
                $total_tagihan += $total_harus_bayar;
                $total_terbayar += $terbayar;

                if ($terbayar >= $total_harus_bayar) {
                    $jumlah_lunas++;
                } else {
                    $jumlah_belum_lunas++;
                    $total_belum_bayar += ($total_harus_bayar - $terbayar);
                }
            }
        }

        $this->data['total_tagihan'] = $total_tagihan;
        $this->data['total_terbayar'] = $total_terbayar;
        $this->data['total_belum_bayar'] = $total_belum_bayar;
        $this->data['jumlah_tagihan'] = count($this->data['tagihan_siswa']);
        $this->data['jumlah_lunas'] = $jumlah_lunas;
        $this->data['jumlah_belum_lunas'] = $jumlah_belum_lunas;

        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_siswa', $this->data);
        $this->view('siswa/pembayaran', $this->data);
        $this->view('templates/footer', $this->data);
    }

    // Menampilkan halaman riwayat pembayaran siswa
    public function riwayatPembayaran()
    {
        $this->data['judul'] = 'Riwayat Pembayaran';
        $id_siswa = $_SESSION['id_ref'];
        // Ambil data riwayat pembayaran dari model
        $db = new Database();
        $db->query("
            SELECT pt.created_at as tanggal, pth.nama as nama_tagihan, pt.jumlah, pt.metode, pt.keterangan,
                   COALESCE(g.nama_guru, u.nama_lengkap, 'Sistem') AS petugas_input
            FROM pembayaran_transaksi pt
            LEFT JOIN pembayaran_tagihan pth ON pt.tagihan_id = pth.id
            LEFT JOIN users u ON u.id_user = pt.user_input_id
            LEFT JOIN guru g ON g.id_guru = u.id_ref AND u.role IN ('guru', 'wali_kelas')
            WHERE pt.id_siswa = :id_siswa
            ORDER BY pt.created_at DESC
        ");
        $db->bind('id_siswa', $id_siswa);
        $this->data['riwayat_pembayaran'] = $db->resultSet();

        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_siswa', $this->data);
        $this->view('siswa/riwayatPembayaran', $this->data);
        $this->view('templates/footer', $this->data);
    }

    // =========== DOKUMEN SISWA ===========

    /**
     * Halaman dokumen siswa - tampilkan dan upload dokumen
     */
    public function dokumen()
    {
        $this->data['judul'] = 'Dokumen Saya';
        $id_siswa = $_SESSION['id_ref'];

        // Ambil data siswa
        $this->data['siswa'] = $this->model('Siswa_model')->getSiswaById($id_siswa);

        // Ambil dokumen siswa
        $this->data['dokumen'] = $this->model('Siswa_model')->getDokumenSiswa($id_siswa);

        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_siswa', $this->data);
        $this->view('siswa/dokumen', $this->data);
        $this->view('templates/footer', $this->data);
    }

    // =========== EDIT IDENTITAS SISWA ===========

    /**
     * Halaman Edit Identitas - Siswa dapat mengedit data pribadi
     */
    public function editIdentitas()
    {
        $this->data['judul'] = 'Edit Identitas';
        $id_siswa = $_SESSION['id_ref'];

        // Ambil data siswa lengkap
        $this->data['siswa'] = $this->model('Siswa_model')->getSiswaById($id_siswa);

        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_siswa', $this->data);
        $this->view('siswa/edit_identitas', $this->data);
        $this->view('templates/footer', $this->data);
    }

    /**
     * Proses Update Identitas Siswa
     */
    public function prosesEditIdentitas()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/siswa/editIdentitas');
            exit;
        }

        $id_siswa = $_SESSION['id_ref'];

        // Data yang boleh diedit oleh siswa
        $data = [
            'id_siswa' => $id_siswa,
            'nama_siswa' => $_POST['nama_siswa'] ?? '',
            'jenis_kelamin' => $_POST['jenis_kelamin'] ?? '',
            'tempat_lahir' => $_POST['tempat_lahir'] ?? '',
            'tgl_lahir' => $_POST['tgl_lahir'] ?? '',
            'agama' => $_POST['agama'] ?? '',
            'hobi' => $_POST['hobi'] ?? '',
            'cita_cita' => $_POST['cita_cita'] ?? '',
            'no_wa' => $_POST['no_wa'] ?? '',
            'email' => $_POST['email'] ?? '',
            // Alamat
            'alamat' => $_POST['alamat'] ?? '',
            'rt' => $_POST['rt'] ?? '',
            'rw' => $_POST['rw'] ?? '',
            'dusun' => $_POST['dusun'] ?? '',
            'kelurahan' => $_POST['kelurahan'] ?? '',
            'kecamatan' => $_POST['kecamatan'] ?? '',
            'kabupaten' => $_POST['kabupaten'] ?? '',
            'provinsi' => $_POST['provinsi'] ?? '',
            'kode_pos' => $_POST['kode_pos'] ?? '',
            // Data Ayah
            'ayah_kandung' => $_POST['ayah_kandung'] ?? '',
            'ayah_nik' => $_POST['ayah_nik'] ?? '',
            'ayah_tempat_lahir' => $_POST['ayah_tempat_lahir'] ?? '',
            'ayah_tanggal_lahir' => $_POST['ayah_tanggal_lahir'] ?? '',
            'ayah_status' => $_POST['ayah_status'] ?? '',
            'ayah_pendidikan' => $_POST['ayah_pendidikan'] ?? '',
            'ayah_pekerjaan' => $_POST['ayah_pekerjaan'] ?? '',
            'ayah_penghasilan' => $_POST['ayah_penghasilan'] ?? '',
            // ayah_no_hp - DIKUNCI: hanya bisa diedit oleh Admin/Wali Kelas
            // Data Ibu
            'ibu_kandung' => $_POST['ibu_kandung'] ?? '',
            'ibu_nik' => $_POST['ibu_nik'] ?? '',
            'ibu_tempat_lahir' => $_POST['ibu_tempat_lahir'] ?? '',
            'ibu_tanggal_lahir' => $_POST['ibu_tanggal_lahir'] ?? '',
            'ibu_status' => $_POST['ibu_status'] ?? '',
            'ibu_pendidikan' => $_POST['ibu_pendidikan'] ?? '',
            'ibu_pekerjaan' => $_POST['ibu_pekerjaan'] ?? '',
            'ibu_penghasilan' => $_POST['ibu_penghasilan'] ?? '',
            // ibu_no_hp - DIKUNCI: hanya bisa diedit oleh Admin/Wali Kelas
            // Data Wali
            'wali_nama' => $_POST['wali_nama'] ?? '',
            'wali_hubungan' => $_POST['wali_hubungan'] ?? '',
            'wali_nik' => $_POST['wali_nik'] ?? '',
            'wali_no_hp' => $_POST['wali_no_hp'] ?? '',
            'wali_pendidikan' => $_POST['wali_pendidikan'] ?? '',
            'wali_pekerjaan' => $_POST['wali_pekerjaan'] ?? '',
            'wali_penghasilan' => $_POST['wali_penghasilan'] ?? '',
        ];

        try {
            $result = $this->model('Siswa_model')->updateDataSiswaByStudent($data);
            if ($result) {
                Flasher::setFlash('Data identitas berhasil diperbarui.', 'success');
            } else {
                Flasher::setFlash('Tidak ada perubahan data.', 'info');
            }
        } catch (Exception $e) {
            Flasher::setFlash('Gagal memperbarui data: ' . $e->getMessage(), 'danger');
        }

        header('Location: ' . BASEURL . '/siswa/editIdentitas');
        exit;
    }

    // =========== UPLOAD DOKUMEN ===========

    /**
     * Upload dokumen dari panel siswa
     */
    public function uploadDokumen()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/siswa/dokumen');
            exit;
        }

        $id_siswa = $_SESSION['id_ref'];
        $jenis = $_POST['jenis_dokumen'] ?? '';

        if (empty($_FILES['file_dokumen']['name'])) {
            Flasher::setFlash('Gagal', 'Pilih file untuk diupload.', 'danger');
            header('Location: ' . BASEURL . '/siswa/dokumen');
            exit;
        }

        $file = $_FILES['file_dokumen'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['pdf', 'jpg', 'jpeg', 'png'];

        if (!in_array($ext, $allowed)) {
            Flasher::setFlash('Gagal', 'Format file tidak diizinkan. Gunakan PDF, JPG, atau PNG.', 'danger');
            header('Location: ' . BASEURL . '/siswa/dokumen');
            exit;
        }

        /* REMOVED LIMIT AS REQUESTED
        if ($file['size'] > 2 * 1024 * 1024) {
            Flasher::setFlash('Gagal', 'Ukuran file maksimal 2MB.', 'danger');
            header('Location: ' . BASEURL . '/siswa/dokumen');
            exit;
        }
        */

        // Generate unique filename
        $newFilename = $id_siswa . '_' . $jenis . '_' . time() . '.' . $ext;

        // Cek apakah Google Drive terhubung
        $useGoogleDrive = false;
        $driveFileId = null;
        $driveUrl = null;

        try {
            require_once APPROOT . '/app/core/GoogleDrive.php';
            $drive = new GoogleDrive();
            $useGoogleDrive = $drive->isConnected();
        } catch (Exception $e) {
            // GoogleDrive tidak tersedia
        }

        if ($useGoogleDrive) {
            // Upload ke Google Drive
            try {
                $siswa = $this->model('Siswa_model')->getSiswaById($id_siswa);
                $namaFolder = ($siswa['nisn'] ?? $id_siswa) . '_' . preg_replace('/\s+/', '_', $siswa['nama_siswa'] ?? 'Siswa');

                // Ambil parent folder ID
                $mainFolderId = $drive->getFolderId();

                // Cari atau buat folder untuk siswa ini
                $siswaFolder = $drive->findOrCreateFolder($namaFolder, $mainFolderId);
                $parentId = $siswaFolder ? $siswaFolder['id'] : $mainFolderId;

                // Upload file
                $uploadResult = $drive->uploadFile($file['tmp_name'], $newFilename, $parentId);

                if ($uploadResult && isset($uploadResult['id'])) {
                    $driveFileId = $uploadResult['id'];
                    $drive->setPublic($driveFileId);
                    $driveUrl = $drive->getPublicUrl($driveFileId);

                    $data = [
                        'jenis_dokumen' => $jenis,
                        'nama_file' => $file['name'],
                        'path_file' => $driveUrl,
                        'ukuran' => $file['size'],
                        'drive_file_id' => $driveFileId,
                        'drive_url' => $driveUrl
                    ];
                    $this->model('Siswa_model')->saveDokumenSiswa($id_siswa, $data);
                    Flasher::setFlash('Berhasil', 'Dokumen berhasil diupload ke Google Drive.', 'success');
                    header('Location: ' . BASEURL . '/siswa/dokumen');
                    exit;
                }
            } catch (Exception $e) {
                error_log("Google Drive upload error: " . $e->getMessage());
                // Fallback ke lokal
            }
        }

        // Fallback: Upload ke lokal
        $uploadDir = APPROOT . '/uploads/siswa_dokumen/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $uploadPath = $uploadDir . $newFilename;

        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            $data = [
                'jenis_dokumen' => $jenis,
                'nama_file' => $file['name'],
                'path_file' => $newFilename,
                'ukuran' => $file['size']
            ];
            $this->model('Siswa_model')->saveDokumenSiswa($id_siswa, $data);
            Flasher::setFlash('Berhasil', 'Dokumen berhasil diupload.', 'success');
        } else {
            Flasher::setFlash('Gagal', 'Gagal mengupload file.', 'danger');
        }

        header('Location: ' . BASEURL . '/siswa/dokumen');
        exit;
    }

    /**
     * Download Absensi Harian dalam format PDF dengan QR Code validasi
     */
    public function downloadAbsensiHarianPDF()
    {
        $id_siswa = $_SESSION['id_ref'] ?? null;
        $id_semester = $_SESSION['id_semester_aktif'] ?? null;

        if (!$id_siswa || !$id_semester) {
            echo "Sesi tidak valid. Silakan login ulang.";
            return;
        }

        try {
            // Ambil data absensi harian
            $absensi_model = $this->model('Absensi_model');
            $absensi_harian = $absensi_model->getAbsensiHarianSiswa($id_siswa, $id_semester);

            // Hitung statistik
            $stats = ['H' => 0, 'I' => 0, 'S' => 0, 'A' => 0];
            foreach ($absensi_harian as $absen) {
                $status = $absen['status_kehadiran'];
                if (isset($stats[$status])) {
                    $stats[$status]++;
                }
            }

            // Ambil info siswa
            $db = new Database();
            $db->query("
                SELECT 
                    s.id_siswa,
                    s.nisn,
                    s.nama_siswa,
                    k.id_kelas,
                    k.nama_kelas,
                    s2.id_semester,
                    s2.semester AS semester,
                    tp.id_tp,
                    tp.nama_tp
                FROM siswa s
                LEFT JOIN keanggotaan_kelas kk ON s.id_siswa = kk.id_siswa
                LEFT JOIN kelas k ON kk.id_kelas = k.id_kelas
                LEFT JOIN semester s2 ON s2.id_semester = :id_semester
                LEFT JOIN tp ON tp.id_tp = :id_tp
                WHERE s.id_siswa = :id_siswa
                LIMIT 1
            ");
            $db->bind('id_siswa', $id_siswa);
            $db->bind('id_semester', $id_semester);
            $db->bind('id_tp', $_SESSION['id_tp_aktif'] ?? 0);
            $siswa_info = $db->single();

            if (empty($siswa_info)) {
                echo "Data siswa tidak ditemukan.";
                return;
            }
            // Susun nama_semester dari field yang ada
            $nama_semester_display = '-';
            if (!empty($siswa_info['nama_tp'])) {
                $nama_semester_display = $siswa_info['nama_tp'];
                if (!empty($siswa_info['semester'])) {
                    $nama_semester_display .= ' - Semester ' . $siswa_info['semester'];
                }
            } elseif (!empty($siswa_info['semester'])) {
                $nama_semester_display = 'Semester ' . $siswa_info['semester'];
            }

            // Ambil pengaturan rapor untuk kop berdasarkan id_kelas melalui wali_kelas
            $pengaturan = [];
            if (!empty($siswa_info['id_kelas'])) {
                $db->query("
                    SELECT pr.kop_rapor 
                    FROM pengaturan_rapor pr
                    INNER JOIN wali_kelas wk ON pr.id_guru = wk.id_guru AND pr.id_tp = wk.id_tp
                    WHERE wk.id_kelas = :id_kelas AND wk.id_tp = :id_tp
                    LIMIT 1
                ");
                $db->bind('id_kelas', $siswa_info['id_kelas']);
                $db->bind('id_tp', $_SESSION['id_tp_aktif'] ?? 0);
                $pengaturan = $db->single();
            }

            // Render HTML untuk PDF
            $renderView = function ($viewPath, $data) {
                extract($data);
                ob_start();
                $fullPath = __DIR__ . "/../views/$viewPath.php";
                if (!file_exists($fullPath)) {
                    throw new Exception("View file tidak ditemukan: $fullPath");
                }
                require $fullPath;
                return ob_get_clean();
            };

            $pdfData = [
                'id_siswa' => $siswa_info['id_siswa'] ?? '',
                'nisn' => $siswa_info['nisn'] ?? '',
                'nama_siswa' => $siswa_info['nama_siswa'] ?? 'Unknown',
                'id_kelas' => $siswa_info['id_kelas'] ?? '',
                'nama_kelas' => $siswa_info['nama_kelas'] ?? '-',
                'id_semester' => $siswa_info['id_semester'] ?? '',
                'nama_semester' => $nama_semester_display,
                'id_tp' => $siswa_info['id_tp'] ?? '',
                'nama_tp' => $siswa_info['nama_tp'] ?? '-',
                'nama_sekolah' => 'MTs Negeri 1 Kota Tangerang',
                'pengaturan' => $pengaturan,
                'absensi_harian' => $absensi_harian,
                'total_hadir' => $stats['H'],
                'total_izin' => $stats['I'],
                'total_sakit' => $stats['S'],
                'total_alpha' => $stats['A']
            ];

            $html = $renderView('siswa/cetak_absensi_harian', $pdfData);

            // Generate PDF dengan QR Code
            $dompdfPath = __DIR__ . '/../core/dompdf/autoload.inc.php';

            if (!file_exists($dompdfPath)) {
                echo "Library Dompdf tidak tersedia.";
                return;
            }

            require_once $dompdfPath;

            if (!class_exists('\\Dompdf\\Dompdf')) {
                echo "Class Dompdf tidak ditemukan.";
                return;
            }

            // Load QR Helper
            require_once APPROOT . '/app/core/PDFQRHelper.php';

            // Generate metadata for QR with fingerprint
            $semesterName = $nama_semester_display;
            $tpName = $siswa_info['nama_tp'] ?? '';
            $namaSiswa = $siswa_info['nama_siswa'] ?? '';
            $namaKelas = $siswa_info['nama_kelas'] ?? '';

            $metadata = [
                'doc' => 'absensi_harian_siswa',
                'id_siswa' => $siswa_info['id_siswa'] ?? '',
                'nisn' => $siswa_info['nisn'] ?? '',
                'nama_siswa' => $siswa_info['nama_siswa'] ?? '',
                'id_kelas' => $siswa_info['id_kelas'] ?? '',
                'nama_kelas' => $siswa_info['nama_kelas'] ?? '',
                'id_semester' => $siswa_info['id_semester'] ?? '',
                'nama_semester' => $nama_semester_display,
                'id_tp' => $siswa_info['id_tp'] ?? '',
                'nama_tp' => $siswa_info['nama_tp'] ?? '',
                'total_hadir' => $stats['H'],
                'total_izin' => $stats['I'],
                'total_sakit' => $stats['S'],
                'total_alpha' => $stats['A'],
                'printed_by' => $siswa_info['nama_siswa'] ?? '',
                'printed_at' => date('Y-m-d H:i:s')
            ];

            $html = PDFQRHelper::addQRToPDF($html, 'absensi_harian_siswa', $id_siswa, $metadata);

            // Generate PDF
            $dompdf = new \Dompdf\Dompdf([
                'isRemoteEnabled' => true,
                'isHtml5ParserEnabled' => true,
                'defaultFont' => 'Arial'
            ]);

            $dompdf->loadHtml($html, 'UTF-8');
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            // Download filename
            $nama_file = 'Absensi_Harian_' .
                preg_replace('/[^A-Za-z0-9_-]/', '_', $namaSiswa) . '_' .
                date('Y-m-d') . '.pdf';

            $dompdf->stream($nama_file, ['Attachment' => true]);

        } catch (Exception $e) {
            error_log("Error in downloadAbsensiHarianPDF(): " . $e->getMessage());
            echo "Terjadi kesalahan saat membuat PDF: " . htmlspecialchars($e->getMessage());
        }
    }

    /**
     * Download Rekap Absensi per Mata Pelajaran dalam format PDF dengan QR Code validasi
     */
    public function downloadRekapAbsensiPDF()
    {
        $id_siswa = $_SESSION['id_ref'] ?? null;
        $id_semester = $_SESSION['id_semester_aktif'] ?? null;

        if (!$id_siswa || !$id_semester) {
            echo "Sesi tidak valid. Silakan login ulang.";
            return;
        }

        try {
            // Ambil rekap per mapel
            $absensi_model = $this->model('Absensi_model');
            $rekap_per_mapel = $absensi_model->getRekapAbsensiSiswaPerMapel($id_siswa, $id_semester);

            // Hitung statistik keseluruhan
            $total_subjects = count($rekap_per_mapel);
            $total_meetings = 0;
            $total_present = 0;
            $total_all = 0;

            foreach ($rekap_per_mapel as $rekap) {
                $subject_total = $rekap['hadir'] + $rekap['izin'] + $rekap['sakit'] + $rekap['alfa'];
                $total_meetings += $subject_total;
                $total_present += $rekap['hadir'];
                $total_all += $subject_total;
            }

            $overall_percentage = ($total_all > 0) ? round(($total_present / $total_all) * 100) : 0;

            // Ambil info siswa
            $db = new Database();
            $db->query("
                SELECT 
                    s.id_siswa,
                    s.nisn,
                    s.nama_siswa,
                    k.id_kelas,
                    k.nama_kelas,
                    s2.id_semester,
                    s2.semester AS semester,
                    tp.id_tp,
                    tp.nama_tp
                FROM siswa s
                LEFT JOIN keanggotaan_kelas kk ON s.id_siswa = kk.id_siswa
                LEFT JOIN kelas k ON kk.id_kelas = k.id_kelas
                LEFT JOIN semester s2 ON s2.id_semester = :id_semester
                LEFT JOIN tp ON tp.id_tp = :id_tp
                WHERE s.id_siswa = :id_siswa
                LIMIT 1
            ");
            $db->bind('id_siswa', $id_siswa);
            $db->bind('id_semester', $id_semester);
            $db->bind('id_tp', $_SESSION['id_tp_aktif'] ?? 0);
            $siswa_info = $db->single();

            if (empty($siswa_info)) {
                echo "Data siswa tidak ditemukan.";
                return;
            }

            // Susun nama_semester
            $nama_semester_display = '-';
            if (!empty($siswa_info['nama_tp'])) {
                $nama_semester_display = $siswa_info['nama_tp'];
                if (!empty($siswa_info['semester'])) {
                    $nama_semester_display .= ' - Semester ' . $siswa_info['semester'];
                }
            } elseif (!empty($siswa_info['semester'])) {
                $nama_semester_display = 'Semester ' . $siswa_info['semester'];
            }

            // Ambil pengaturan rapor untuk kop berdasarkan id_kelas melalui wali_kelas
            $pengaturan = [];
            if (!empty($siswa_info['id_kelas'])) {
                $db->query("
                    SELECT pr.kop_rapor 
                    FROM pengaturan_rapor pr
                    INNER JOIN wali_kelas wk ON pr.id_guru = wk.id_guru AND pr.id_tp = wk.id_tp
                    WHERE wk.id_kelas = :id_kelas AND wk.id_tp = :id_tp
                    LIMIT 1
                ");
                $db->bind('id_kelas', $siswa_info['id_kelas']);
                $db->bind('id_tp', $_SESSION['id_tp_aktif'] ?? 0);
                $pengaturan = $db->single();
            }

            // Render HTML untuk PDF
            $renderView = function ($viewPath, $data) {
                extract($data);
                ob_start();
                $fullPath = __DIR__ . "/../views/$viewPath.php";
                if (!file_exists($fullPath)) {
                    throw new Exception("View file tidak ditemukan: $fullPath");
                }
                require $fullPath;
                return ob_get_clean();
            };

            $pdfData = [
                'id_siswa' => $siswa_info['id_siswa'] ?? '',
                'nisn' => $siswa_info['nisn'] ?? '',
                'nama_siswa' => $siswa_info['nama_siswa'] ?? 'Unknown',
                'id_kelas' => $siswa_info['id_kelas'] ?? '',
                'nama_kelas' => $siswa_info['nama_kelas'] ?? '-',
                'id_semester' => $siswa_info['id_semester'] ?? '',
                'nama_semester' => $nama_semester_display,
                'id_tp' => $siswa_info['id_tp'] ?? '',
                'nama_tp' => $siswa_info['nama_tp'] ?? '-',
                'pengaturan' => $pengaturan,
                'rekap_per_mapel' => $rekap_per_mapel,
                'total_subjects' => $total_subjects,
                'total_meetings' => $total_meetings,
                'total_present' => $total_present,
                'overall_percentage' => $overall_percentage
            ];

            $html = $renderView('siswa/cetak_rekap_absensi', $pdfData);

            // Generate PDF dengan QR Code
            $dompdfPath = __DIR__ . '/../core/dompdf/autoload.inc.php';

            if (!file_exists($dompdfPath)) {
                echo "Library Dompdf tidak tersedia.";
                return;
            }

            require_once $dompdfPath;

            if (!class_exists('\\Dompdf\\Dompdf')) {
                echo "Class Dompdf tidak ditemukan.";
                return;
            }

            // Load QR Helper
            require_once APPROOT . '/app/core/PDFQRHelper.php';

            // Generate metadata for QR
            $metadata = [
                'doc' => 'rekap_absensi_siswa',
                'id_siswa' => $siswa_info['id_siswa'] ?? '',
                'nisn' => $siswa_info['nisn'] ?? '',
                'nama_siswa' => $siswa_info['nama_siswa'] ?? '',
                'id_kelas' => $siswa_info['id_kelas'] ?? '',
                'nama_kelas' => $siswa_info['nama_kelas'] ?? '',
                'id_semester' => $siswa_info['id_semester'] ?? '',
                'nama_semester' => $nama_semester_display,
                'id_tp' => $siswa_info['id_tp'] ?? '',
                'nama_tp' => $siswa_info['nama_tp'] ?? '',
                'total_subjects' => $total_subjects,
                'total_meetings' => $total_meetings,
                'total_present' => $total_present,
                'overall_percentage' => $overall_percentage,
                'printed_by' => $siswa_info['nama_siswa'] ?? '',
                'printed_at' => date('Y-m-d H:i:s')
            ];

            // Generate fingerprint
            $fingerprintBase = implode('|', [
                $id_siswa,
                $siswa_info['nama_siswa'],
                $siswa_info['nama_kelas'],
                $nama_semester_display,
                $overall_percentage,
                date('Y-m-d')
            ]);
            $metadata['fingerprint'] = hash('sha256', $fingerprintBase);

            // Add QR to PDF
            $html = PDFQRHelper::addQRToPDF($html, 'rekap_absensi_siswa', $id_siswa, $metadata);

            // Generate PDF
            $dompdf = new \Dompdf\Dompdf([
                'isRemoteEnabled' => true,
                'isHtml5ParserEnabled' => true,
                'defaultFont' => 'Arial'
            ]);

            $dompdf->loadHtml($html, 'UTF-8');
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            // Download filename
            $nama_file = 'Rekap_Absensi_' .
                preg_replace('/[^A-Za-z0-9_-]/', '_', $siswa_info['nama_siswa']) . '_' .
                date('Y-m-d') . '.pdf';

            $dompdf->stream($nama_file, ['Attachment' => true]);

        } catch (Exception $e) {
            error_log("Error in downloadRekapAbsensiPDF(): " . $e->getMessage());
            echo "Terjadi kesalahan saat membuat PDF: " . htmlspecialchars($e->getMessage());
        }
    }

    // Export PDF Riwayat Pembayaran Siswa
    public function downloadRiwayatPembayaranPDF()
    {
        $id_siswa = $_SESSION['id_ref'];
        $id_semester = $_SESSION['id_semester_aktif'];
        $db = new Database();

        // Ambil data siswa lengkap
        $db->query("SELECT s.nama_siswa, s.nisn, k.nama_kelas, k.id_kelas FROM siswa s LEFT JOIN keanggotaan_kelas kk ON s.id_siswa = kk.id_siswa LEFT JOIN kelas k ON kk.id_kelas = k.id_kelas WHERE s.id_siswa = :id_siswa LIMIT 1");
        $db->bind('id_siswa', $id_siswa);
        $siswa = $db->single();

        // Ambil semester aktif
        $db->query("SELECT semester.semester, semester.id_semester, tp.nama_tp, tp.id_tp FROM semester JOIN tp ON semester.id_tp = tp.id_tp WHERE semester.id_semester = :id_semester LIMIT 1");
        $db->bind('id_semester', $id_semester);
        $semester = $db->single();

        // Ambil riwayat pembayaran
        $db->query("
            SELECT pt.created_at as tanggal, pth.nama as nama_tagihan, pt.jumlah, pt.metode, pt.keterangan,
                   COALESCE(g.nama_guru, u.nama_lengkap, 'Sistem') AS petugas_input
            FROM pembayaran_transaksi pt 
            LEFT JOIN pembayaran_tagihan pth ON pt.tagihan_id = pth.id 
            LEFT JOIN users u ON u.id_user = pt.user_input_id
            LEFT JOIN guru g ON g.id_guru = u.id_ref AND u.role IN ('guru', 'wali_kelas')
            WHERE pt.id_siswa = :id_siswa 
            ORDER BY pt.created_at DESC
        ");
        $db->bind('id_siswa', $id_siswa);
        $riwayat = $db->resultSet();

        // Hitung total pembayaran
        $total_pembayaran = array_sum(array_column($riwayat, 'jumlah'));

        // Ambil pengaturan kop
        $pengaturan = [];
        if (!empty($siswa['id_kelas'])) {
            $db->query("
                SELECT pr.kop_rapor 
                FROM pengaturan_rapor pr
                INNER JOIN wali_kelas wk ON pr.id_guru = wk.id_guru AND pr.id_tp = wk.id_tp
                WHERE wk.id_kelas = :id_kelas AND wk.id_tp = :id_tp
                LIMIT 1
            ");
            $db->bind('id_kelas', $siswa['id_kelas']);
            $db->bind('id_tp', $_SESSION['id_tp_aktif'] ?? 0);
            $pengaturan = $db->single();
        }

        // Data untuk PDF
        $data = [
            'nama_siswa' => $siswa['nama_siswa'] ?? '-',
            'nisn' => $siswa['nisn'] ?? '-',
            'nama_kelas' => $siswa['nama_kelas'] ?? '-',
            'nama_semester' => ($semester['semester'] ?? '-') . ' / ' . ($semester['nama_tp'] ?? '-'),
            'riwayat_pembayaran' => $riwayat,
            'tanggal_cetak' => date('Y-m-d'),
            'waktu_cetak' => date('H:i') . ' WIB',
            'pengaturan' => $pengaturan,
        ];

        // Render HTML
        ob_start();
        include APPROOT . '/app/views/siswa/cetak_riwayat_pembayaran.php';
        $html = ob_get_clean();

        // Load QR Helper
        require_once APPROOT . '/app/core/PDFQRHelper.php';

        // Generate metadata for QR with fingerprint
        $metadata = [
            'doc' => 'riwayat_pembayaran_siswa',
            'id_siswa' => $id_siswa,
            'nisn' => $siswa['nisn'] ?? '',
            'nama_siswa' => $siswa['nama_siswa'] ?? '',
            'id_kelas' => $siswa['id_kelas'] ?? '',
            'nama_kelas' => $siswa['nama_kelas'] ?? '',
            'id_semester' => $semester['id_semester'] ?? '',
            'nama_semester' => ($semester['semester'] ?? '-') . ' / ' . ($semester['nama_tp'] ?? '-'),
            'id_tp' => $semester['id_tp'] ?? '',
            'nama_tp' => $semester['nama_tp'] ?? '',
            'total_transaksi' => count($riwayat),
            'total_pembayaran' => $total_pembayaran,
            'printed_by' => $siswa['nama_siswa'] ?? '',
            'printed_at' => date('Y-m-d H:i:s')
        ];

        $html = PDFQRHelper::addQRToPDF($html, 'riwayat_pembayaran_siswa', $id_siswa, $metadata);

        // PDF generation
        require_once APPROOT . '/app/core/dompdf/autoload.inc.php';
        $dompdf = new \Dompdf\Dompdf([
            'isRemoteEnabled' => true,
            'isHtml5ParserEnabled' => true,
            'defaultFont' => 'Arial'
        ]);

        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $nama_file = 'Riwayat_Pembayaran_' . preg_replace('/[^A-Za-z0-9_-]/', '_', $data['nama_siswa']) . '_' . date('Y-m-d') . '.pdf';
        $dompdf->stream($nama_file, ['Attachment' => true]);
        exit;
    }
}