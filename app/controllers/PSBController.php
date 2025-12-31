<?php

/**
 * File: app/controllers/PSBController.php
 * Controller untuk PSB (Penerimaan Siswa Baru)
 * Menangani halaman publik dan admin dashboard PSB
 */
class PSBController extends Controller
{

    private $psbModel;
    private $isAdmin = false;
    private $isPetugasPSB = false;

    public function __construct()
    {
        $this->psbModel = $this->model('PSB_model');

        // Cek apakah user adalah admin
        if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin') {
            $this->isAdmin = true;
        }

        // Cek apakah user adalah petugas PSB (guru/wali_kelas dengan fungsi petugas_psb)
        $role = $_SESSION['role'] ?? '';
        if (in_array($role, ['guru', 'wali_kelas'])) {
            $id_guru = $_SESSION['id_ref'] ?? 0;
            $id_tp = $_SESSION['id_tp_aktif'] ?? 0;
            if ($id_guru && $id_tp) {
                require_once APPROOT . '/app/models/GuruFungsi_model.php';
                $guruFungsiModel = new GuruFungsi_model();
                $this->isPetugasPSB = $guruFungsiModel->isPetugasPSB($id_guru, $id_tp);
            }
        }
    }

    /**
     * Middleware untuk memastikan hanya admin atau petugas PSB yang bisa akses
     */
    private function requireAdmin()
    {
        if (!$this->isAdmin && !$this->isPetugasPSB) {
            header('Location: ' . BASEURL . '/auth/login');
            exit;
        }
    }

    /**
     * Helper untuk mendapatkan data sidebar admin PSB
     * Menggunakan sidebar PSB khusus
     */
    private function getSidebarData()
    {
        $tpModel = $this->model('TahunPelajaran_model');
        return [
            'daftar_semester' => $tpModel->getAllSemester(),
            'use_psb_sidebar' => true  // Flag untuk menggunakan sidebar PSB khusus
        ];
    }

    // =========================================================================
    // PUBLIC ROUTES (Tanpa Login)
    // =========================================================================

    /**
     * Serve file dokumen PSB (public access untuk preview)
     * URL: /psb/serveFile/{id_pendaftar}/{jenis}
     */
    public function serveFile($idPendaftar = null, $jenis = null)
    {
        if (!$idPendaftar || !$jenis) {
            http_response_code(404);
            echo 'File not found';
            return;
        }

        // Ambil dokumen dari database
        $dokumen = $this->psbModel->getDokumenByPendaftar($idPendaftar);
        $doc = null;
        foreach ($dokumen as $d) {
            if ($d['jenis_dokumen'] === $jenis) {
                $doc = $d;
                break;
            }
        }

        if (!$doc || empty($doc['path_file'])) {
            http_response_code(404);
            echo 'File not found';
            return;
        }

        // Cek apakah file ada di Google Drive
        if (!empty($doc['drive_url'])) {
            // Redirect ke Google Drive URL
            header('Location: ' . $doc['drive_url']);
            exit;
        }

        $filePath = $doc['path_file'];
        $baseDir = dirname(dirname(__DIR__));

        // Try multiple possible locations
        $possiblePaths = [
            $baseDir . '/' . $filePath,                    // path as stored
            $baseDir . '/public/' . $filePath,             // with public prefix
            $baseDir . '/public/public/' . str_replace('public/', '', $filePath) // fix double public
        ];

        $fullPath = null;
        foreach ($possiblePaths as $path) {
            $normalizedPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
            if (file_exists($normalizedPath)) {
                $fullPath = $normalizedPath;
                break;
            }
        }

        if (!$fullPath) {
            http_response_code(404);
            echo 'File not found';
            return;
        }

        // Determine mime type
        $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
        $mimeTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'pdf' => 'application/pdf'
        ];
        $contentType = $mimeTypes[$ext] ?? 'application/octet-stream';

        // Output file
        header('Content-Type: ' . $contentType);
        header('Content-Length: ' . filesize($fullPath));
        header('Cache-Control: public, max-age=86400');
        readfile($fullPath);
        exit;
    }

    /**
     * Cari sekolah berdasarkan NPSN (public access)
     * Menggunakan API dari fazriansyah.eu.org
     */
    public function cariNPSN($npsn = null)
    {
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');

        if (!$npsn || strlen($npsn) !== 8 || !ctype_digit($npsn)) {
            echo json_encode(['success' => false, 'message' => 'NPSN harus 8 digit angka']);
            return;
        }

        // Gunakan API fazriansyah
        $url = "https://api.fazriansyah.eu.org/v1/sekolah?npsn=" . urlencode($npsn);

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json'
            ]
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        $curlErrno = curl_errno($ch);
        curl_close($ch);

        if ($curlErrno) {
            echo json_encode(['success' => false, 'message' => 'Koneksi gagal: ' . $curlError]);
            return;
        }

        if ($httpCode === 200 && $response) {
            $data = json_decode($response, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                echo json_encode(['success' => false, 'message' => 'Gagal parse response']);
                return;
            }

            // API fazriansyah struktur: data.satuanPendidikan
            if (isset($data['data']['satuanPendidikan'])) {
                $sekolah = $data['data']['satuanPendidikan'];

                // Gabungkan alamat lengkap
                $alamatParts = [];
                if (!empty($sekolah['alamatJalan']))
                    $alamatParts[] = $sekolah['alamatJalan'];
                if (!empty($sekolah['namaDesa']))
                    $alamatParts[] = $sekolah['namaDesa'];
                if (!empty($sekolah['namaKecamatan']))
                    $alamatParts[] = $sekolah['namaKecamatan'];
                if (!empty($sekolah['namaKabupaten']))
                    $alamatParts[] = $sekolah['namaKabupaten'];
                if (!empty($sekolah['namaProvinsi']))
                    $alamatParts[] = $sekolah['namaProvinsi'];
                $alamatLengkap = implode(', ', $alamatParts);

                echo json_encode([
                    'success' => true,
                    'sekolah' => [
                        'npsn' => $sekolah['npsn'] ?? $npsn,
                        'nama' => $sekolah['nama'] ?? '',
                        'alamat' => $alamatLengkap,
                        'bentuk' => $sekolah['bentukPendidikan'] ?? '',
                        'status' => $sekolah['statusSatuanPendidikan'] ?? '',
                        'akreditasi' => $sekolah['akreditasi'] ?? '',
                        'kecamatan' => $sekolah['namaKecamatan'] ?? '',
                        'kabupaten' => $sekolah['namaKabupaten'] ?? '',
                        'provinsi' => $sekolah['namaProvinsi'] ?? ''
                    ]
                ]);
                return;
            }
        }

        echo json_encode(['success' => false, 'message' => 'Sekolah dengan NPSN ' . $npsn . ' tidak ditemukan.']);
    }

    /**
     * Landing page PSB
     */
    public function index()
    {
        // Get periode aktif
        $periodeAktif = $this->psbModel->getPeriodeAktif();

        // Group periode by lembaga for view display
        $periodeByLembaga = [];
        foreach ($periodeAktif as $p) {
            $idLembaga = $p['id_lembaga'];
            if (!isset($periodeByLembaga[$idLembaga])) {
                $periodeByLembaga[$idLembaga] = [
                    'id_lembaga' => $idLembaga,
                    'nama_lembaga' => $p['nama_lembaga'],
                    'jenjang' => $p['jenjang'],
                    'periode' => []
                ];
            }
            // Get jalur for this periode
            $p['jalur'] = $this->psbModel->getKuotaJalur($p['id_periode']);
            $periodeByLembaga[$idLembaga]['periode'][] = $p;
        }

        $data = [
            'judul' => 'Penerimaan Siswa Baru',
            'pengaturan' => $this->psbModel->getPengaturan(),
            'lembaga' => $this->psbModel->getAllLembaga(true),
            'periode_aktif' => $periodeAktif,
            'periode_by_lembaga' => array_values($periodeByLembaga),
            'jalur' => $this->psbModel->getAllJalur(true)
        ];

        $this->view('psb/index', $data);
    }

    /**
     * Form pendaftaran online
     */
    public function daftar($id_periode = null)
    {
        $this->requirePsbLogin();

        if (!$id_periode) {
            header('Location: ' . BASEURL . '/psb');
            exit;
        }

        $periode = $this->psbModel->getPeriodeById($id_periode);
        if (!$periode || $periode['status'] != 'aktif') {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Periode pendaftaran tidak tersedia'];
            header('Location: ' . BASEURL . '/psb');
            exit;
        }

        // Cek apakah masih dalam periode
        $today = date('Y-m-d');
        if ($today < $periode['tanggal_buka'] || $today > $periode['tanggal_tutup']) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Periode pendaftaran sudah ditutup'];
            header('Location: ' . BASEURL . '/psb');
            exit;
        }

        $data = [
            'judul' => 'Formulir Pendaftaran',
            'periode' => $periode,
            'jalur' => $this->psbModel->getAllJalur(true),
            'pengaturan' => $this->psbModel->getPengaturan()
        ];

        $this->view('psb/daftar', $data);
    }

    /**
     * Proses pendaftaran
     */
    public function prosesDaftar()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            header('Location: ' . BASEURL . '/psb');
            exit;
        }

        // Validasi dan sanitasi input
        $data = [
            'id_periode' => filter_input(INPUT_POST, 'id_periode', FILTER_VALIDATE_INT),
            'id_jalur' => filter_input(INPUT_POST, 'id_jalur', FILTER_VALIDATE_INT),
            'nisn' => trim($_POST['nisn'] ?? ''),
            'nik' => trim($_POST['nik'] ?? ''),
            'nama_lengkap' => trim($_POST['nama_lengkap'] ?? ''),
            'jenis_kelamin' => $_POST['jenis_kelamin'] ?? '',
            'tempat_lahir' => trim($_POST['tempat_lahir'] ?? ''),
            'tanggal_lahir' => $_POST['tanggal_lahir'] ?? null,
            'agama' => $_POST['agama'] ?? '',
            'alamat' => trim($_POST['alamat'] ?? ''),
            'rt' => trim($_POST['rt'] ?? ''),
            'rw' => trim($_POST['rw'] ?? ''),
            'kelurahan' => trim($_POST['kelurahan'] ?? ''),
            'kecamatan' => trim($_POST['kecamatan'] ?? ''),
            'kota' => trim($_POST['kota'] ?? ''),
            'provinsi' => trim($_POST['provinsi'] ?? ''),
            'kode_pos' => trim($_POST['kode_pos'] ?? ''),
            'no_hp' => trim($_POST['no_hp'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'nama_ayah' => trim($_POST['nama_ayah'] ?? ''),
            'pekerjaan_ayah' => trim($_POST['pekerjaan_ayah'] ?? ''),
            'no_hp_ayah' => trim($_POST['no_hp_ayah'] ?? ''),
            'nama_ibu' => trim($_POST['nama_ibu'] ?? ''),
            'pekerjaan_ibu' => trim($_POST['pekerjaan_ibu'] ?? ''),
            'no_hp_ibu' => trim($_POST['no_hp_ibu'] ?? ''),
            'nama_wali' => trim($_POST['nama_wali'] ?? ''),
            'hubungan_wali' => trim($_POST['hubungan_wali'] ?? ''),
            'no_hp_wali' => trim($_POST['no_hp_wali'] ?? ''),
            'asal_sekolah' => trim($_POST['asal_sekolah'] ?? ''),
            'npsn_asal' => trim($_POST['npsn_asal'] ?? ''),
            'alamat_sekolah_asal' => trim($_POST['alamat_sekolah_asal'] ?? ''),
            'tahun_lulus' => filter_input(INPUT_POST, 'tahun_lulus', FILTER_VALIDATE_INT)
        ];

        // Validasi required fields
        $errors = [];
        if (empty($data['nama_lengkap']))
            $errors[] = 'Nama lengkap wajib diisi';
        if (empty($data['jenis_kelamin']))
            $errors[] = 'Jenis kelamin wajib dipilih';
        if (empty($data['id_periode']))
            $errors[] = 'Periode tidak valid';
        if (empty($data['id_jalur']))
            $errors[] = 'Jalur pendaftaran wajib dipilih';

        if (!empty($errors)) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => implode('<br>', $errors)];
            $_SESSION['form_data'] = $data;
            header('Location: ' . BASEURL . '/psb/daftar/' . $data['id_periode']);
            exit;
        }

        // Handle upload foto jika ada
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
            $uploadDir = APPROOT . '/../public/uploads/psb/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9.]/', '', $_FILES['foto']['name']);
            $filePath = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['foto']['tmp_name'], $filePath)) {
                $data['foto'] = $fileName;
            }
        }

        // Simpan data
        $result = $this->psbModel->tambahPendaftar($data);

        if ($result) {
            $_SESSION['psb_success'] = [
                'no_pendaftaran' => $result['no_pendaftaran'],
                'nama' => $data['nama_lengkap']
            ];
            header('Location: ' . BASEURL . '/psb/sukses');
        } else {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Gagal menyimpan data pendaftaran'];
            $_SESSION['form_data'] = $data;
            header('Location: ' . BASEURL . '/psb/daftar/' . $data['id_periode']);
        }
        exit;
    }

    /**
     * Halaman sukses pendaftaran
     */
    public function sukses()
    {
        if (!isset($_SESSION['psb_success'])) {
            header('Location: ' . BASEURL . '/psb');
            exit;
        }

        $data = [
            'judul' => 'Pendaftaran Berhasil',
            'result' => $_SESSION['psb_success'],
            'pengaturan' => $this->psbModel->getPengaturan()
        ];

        unset($_SESSION['psb_success']);
        $this->view('psb/sukses', $data);
    }

    /**
     * Cek status pendaftaran
     */
    public function cekStatus()
    {
        $data = [
            'judul' => 'Cek Status Pendaftaran',
            'pengaturan' => $this->psbModel->getPengaturan(),
            'pendaftar' => null
        ];

        if (isset($_GET['no']) && !empty($_GET['no'])) {
            $data['pendaftar'] = $this->psbModel->getPendaftarByNo($_GET['no']);
        }

        $this->view('psb/cek_status', $data);
    }

    /**
     * Cetak bukti pendaftaran
     */
    public function cetakBukti($no_pendaftaran)
    {
        $pendaftar = $this->psbModel->getPendaftarByNo($no_pendaftaran);

        if (!$pendaftar) {
            echo 'Data tidak ditemukan';
            exit;
        }

        $data = [
            'judul' => 'Bukti Pendaftaran',
            'pendaftar' => $pendaftar,
            'pengaturan' => $this->psbModel->getPengaturan()
        ];

        $this->view('psb/bukti_pendaftaran', $data);
    }

    // =========================================================================
    // AUTH ROUTES (Register, Login, Logout untuk Calon Siswa)
    // =========================================================================

    /**
     * Halaman register akun
     */
    public function register()
    {
        // Jika sudah login, redirect ke dashboard
        if (isset($_SESSION['psb_akun'])) {
            header('Location: ' . BASEURL . '/psb/dashboardPendaftar');
            exit;
        }

        $data = [
            'judul' => 'Daftar Akun PSB',
            'pengaturan' => $this->psbModel->getPengaturan()
        ];

        $this->view('psb/register', $data);
    }

    /**
     * Proses registrasi akun
     */
    public function prosesRegister()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            header('Location: ' . BASEURL . '/psb/register');
            exit;
        }

        $nama = trim($_POST['nama_lengkap'] ?? '');
        $nisn = trim($_POST['nisn'] ?? '');
        $noWa = trim($_POST['no_wa'] ?? '');
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';

        // Validasi
        if (empty($nama) || empty($nisn) || empty($noWa) || empty($password)) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Semua field harus diisi'];
            $_SESSION['form_data'] = $_POST;
            header('Location: ' . BASEURL . '/psb/register');
            exit;
        }

        if (strlen($nisn) != 10 || !ctype_digit($nisn)) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'NISN harus 10 digit angka'];
            $_SESSION['form_data'] = $_POST;
            header('Location: ' . BASEURL . '/psb/register');
            exit;
        }

        if ($password !== $passwordConfirm) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Konfirmasi password tidak cocok'];
            $_SESSION['form_data'] = $_POST;
            header('Location: ' . BASEURL . '/psb/register');
            exit;
        }

        if (strlen($password) < 6) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Password minimal 6 karakter'];
            $_SESSION['form_data'] = $_POST;
            header('Location: ' . BASEURL . '/psb/register');
            exit;
        }

        // Cek NISN sudah terdaftar
        if ($this->psbModel->isNisnRegistered($nisn)) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'NISN sudah terdaftar. Silakan login.'];
            $_SESSION['form_data'] = $_POST;
            header('Location: ' . BASEURL . '/psb/register');
            exit;
        }

        // Buat akun
        $result = $this->psbModel->createAkun([
            'nama_lengkap' => $nama,
            'nisn' => $nisn,
            'no_wa' => $noWa,
            'password' => $password
        ]);

        if ($result) {
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Akun berhasil dibuat! Silakan login.'];
            header('Location: ' . BASEURL . '/psb/login');
        } else {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Gagal membuat akun. Silakan coba lagi.'];
            header('Location: ' . BASEURL . '/psb/register');
        }
        exit;
    }

    /**
     * Halaman login
     */
    public function login()
    {
        // Jika sudah login, redirect ke dashboard
        if (isset($_SESSION['psb_akun'])) {
            header('Location: ' . BASEURL . '/psb/dashboardPendaftar');
            exit;
        }

        $data = [
            'judul' => 'Login PSB',
            'pengaturan' => $this->psbModel->getPengaturan()
        ];

        $this->view('psb/login', $data);
    }

    /**
     * Proses login
     */
    public function prosesLogin()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            header('Location: ' . BASEURL . '/psb/login');
            exit;
        }

        $nisn = trim($_POST['nisn'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($nisn) || empty($password)) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'NISN dan password harus diisi'];
            header('Location: ' . BASEURL . '/psb/login');
            exit;
        }

        $akun = $this->psbModel->loginAkun($nisn, $password);

        if ($akun) {
            $_SESSION['psb_akun'] = [
                'id_akun' => $akun['id_akun'],
                'nisn' => $akun['nisn'],
                'nama_lengkap' => $akun['nama_lengkap'],
                'no_wa' => $akun['no_wa']
            ];
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Login berhasil!'];
            header('Location: ' . BASEURL . '/psb/dashboardPendaftar');
        } else {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'NISN atau password salah'];
            header('Location: ' . BASEURL . '/psb/login');
        }
        exit;
    }

    /**
     * Logout
     */
    public function logout()
    {
        unset($_SESSION['psb_akun']);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Anda telah logout'];
        header('Location: ' . BASEURL . '/psb/login');
        exit;
    }

    /**
     * Lupa password
     */
    public function lupaPassword()
    {
        $data = [
            'judul' => 'Lupa Password',
            'step' => 1
        ];
        $this->view('psb/lupa_password', $data);
    }

    /**
     * Proses lupa password
     */
    public function prosesLupaPassword()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            header('Location: ' . BASEURL . '/psb/lupaPassword');
            exit;
        }

        $step = $_POST['step'] ?? 1;

        if ($step == 1) {
            // Step 1: Cari akun by No WA
            $noWa = trim($_POST['no_wa'] ?? '');
            $akun = $this->psbModel->getAkunByNoWa($noWa);

            if (!$akun) {
                $_SESSION['flash'] = ['type' => 'error', 'message' => 'Nomor WhatsApp tidak ditemukan'];
                header('Location: ' . BASEURL . '/psb/lupaPassword');
                exit;
            }

            // Generate token
            $token = $this->psbModel->generateResetToken($akun['id_akun']);

            // Kirim via WA
            require_once APPROOT . '/app/core/Fonnte.php';
            $fonnte = new Fonnte();
            $fonnte->sendResetToken($akun['no_wa'], $akun['nama_lengkap'], $token);

            $data = [
                'judul' => 'Verifikasi Token',
                'step' => 2,
                'nisn' => $akun['nisn']
            ];
            $this->view('psb/lupa_password', $data);

        } elseif ($step == 2) {
            // Step 2: Verifikasi token
            $nisn = $_POST['nisn'] ?? '';
            $token = trim($_POST['token'] ?? '');

            $akun = $this->psbModel->verifyResetToken($nisn, $token);

            if (!$akun) {
                $_SESSION['flash'] = ['type' => 'error', 'message' => 'Kode tidak valid atau sudah kadaluarsa'];
                $data = ['judul' => 'Verifikasi Token', 'step' => 2, 'nisn' => $nisn];
                $this->view('psb/lupa_password', $data);
                return;
            }

            $data = [
                'judul' => 'Password Baru',
                'step' => 3,
                'nisn' => $nisn,
                'token' => $token
            ];
            $this->view('psb/lupa_password', $data);

        } else {
            // Step 3: Set password baru
            $nisn = $_POST['nisn'] ?? '';
            $token = $_POST['token'] ?? '';
            $password = $_POST['password'] ?? '';
            $passwordConfirm = $_POST['password_confirm'] ?? '';

            if ($password !== $passwordConfirm) {
                $_SESSION['flash'] = ['type' => 'error', 'message' => 'Konfirmasi password tidak cocok'];
                $data = ['judul' => 'Password Baru', 'step' => 3, 'nisn' => $nisn, 'token' => $token];
                $this->view('psb/lupa_password', $data);
                return;
            }

            $akun = $this->psbModel->verifyResetToken($nisn, $token);
            if (!$akun) {
                $_SESSION['flash'] = ['type' => 'error', 'message' => 'Sesi tidak valid. Silakan ulangi.'];
                header('Location: ' . BASEURL . '/psb/lupaPassword');
                exit;
            }

            $this->psbModel->resetPassword($akun['id_akun'], $password);
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Password berhasil diubah! Silakan login.'];
            header('Location: ' . BASEURL . '/psb/login');
            exit;
        }
    }

    /**
     * Dashboard pendaftar (perlu login PSB)
     */
    public function dashboardPendaftar()
    {
        $this->requirePsbLogin();

        $idAkun = $_SESSION['psb_akun']['id_akun'];

        // Get periode aktif with jalur kuota
        $periodeAktif = $this->psbModel->getPeriodeAktif();
        foreach ($periodeAktif as &$periode) {
            $periode['jalur'] = $this->psbModel->getKuotaJalur($periode['id_periode']);
        }

        $data = [
            'judul' => 'Dashboard Pendaftar',
            'akun' => $this->psbModel->getAkunById($idAkun),
            'pendaftaran' => $this->psbModel->getPendaftaranByAkun($idAkun),
            'periode_aktif' => $periodeAktif
        ];

        $this->view('psb/dashboard_pendaftar', $data);
    }

    /**
     * Helper: Require PSB Login
     */
    private function requirePsbLogin()
    {
        if (!isset($_SESSION['psb_akun'])) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Silakan login terlebih dahulu'];
            header('Location: ' . BASEURL . '/psb/login');
            exit;
        }
    }

    /**
     * Pilih jalur pendaftaran
     */
    public function pilihJalur($idPeriode = null)
    {
        $this->requirePsbLogin();

        if (!$idPeriode) {
            header('Location: ' . BASEURL . '/psb/dashboardPendaftar');
            exit;
        }

        $periode = $this->psbModel->getPeriodeById($idPeriode);
        if (!$periode || $periode['status'] != 'aktif') {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Periode tidak tersedia'];
            header('Location: ' . BASEURL . '/psb/dashboardPendaftar');
            exit;
        }

        // Get jalur with kuota for this specific periode (filter kuota > 0)
        $allJalur = $this->psbModel->getKuotaJalur($idPeriode);
        $jalur = array_filter($allJalur, function ($j) {
            return ($j['kuota'] ?? 0) > 0;
        });

        $data = [
            'judul' => 'Pilih Jalur Pendaftaran',
            'periode' => $periode,
            'jalur' => array_values($jalur) // Re-index array
        ];

        $this->view('psb/pilih_jalur', $data);
    }

    /**
     * Mulai pendaftaran baru
     */
    public function mulaiPendaftaran()
    {
        $this->requirePsbLogin();

        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            header('Location: ' . BASEURL . '/psb/dashboardPendaftar');
            exit;
        }

        $idPeriode = $_POST['id_periode'] ?? null;
        $idJalur = $_POST['id_jalur'] ?? null;
        $idAkun = $_SESSION['psb_akun']['id_akun'];

        if (!$idPeriode || !$idJalur) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Data tidak lengkap'];
            header('Location: ' . BASEURL . '/psb/dashboardPendaftar');
            exit;
        }

        // Cek apakah sudah ada pendaftaran aktif di periode ini
        $existing = $this->psbModel->getPendaftaranByAkunPeriode($idAkun, $idPeriode);
        if ($existing) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Anda sudah terdaftar di periode ini'];
            header('Location: ' . BASEURL . '/psb/dashboardPendaftar');
            exit;
        }

        $result = $this->psbModel->createPendaftaran($idAkun, $idPeriode, $idJalur);

        if ($result) {
            // Kirim notifikasi WA bahwa pendaftaran dimulai
            try {
                require_once APPROOT . '/app/core/Fonnte.php';
                $fonnte = new Fonnte();
                $akun = $_SESSION['psb_akun'];
                $periode = $this->psbModel->getPeriodeById($idPeriode);
                $pendaftar = $this->psbModel->getPendaftarById($result);

                $message = "📝 *Pendaftaran Dimulai*\n\n";
                $message .= "Halo *" . ($akun['nama'] ?? 'Calon Siswa') . "*,\n\n";
                $message .= "Pendaftaran Anda telah dibuat dengan:\n";
                $message .= "No. Pendaftaran: *" . ($pendaftar['no_pendaftaran'] ?? '-') . "*\n";
                $message .= "Lembaga: " . ($periode['nama_lembaga'] ?? '-') . "\n\n";
                $message .= "Silakan lengkapi formulir dan upload dokumen.\n\n";
                $message .= "Terima kasih.";

                $fonnte->send($akun['no_wa'], $message);
            } catch (Exception $e) {
                // Silent fail - notifikasi tidak wajib
            }

            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Pendaftaran berhasil dibuat! Silakan lengkapi formulir.'];
            header('Location: ' . BASEURL . '/psb/dashboardPendaftar');
        } else {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Gagal membuat pendaftaran'];
            header('Location: ' . BASEURL . '/psb/dashboardPendaftar');
        }
        exit;
    }

    /**
     * Hapus pendaftaran (hanya draft/revisi)
     */
    public function hapusPendaftaran($idPendaftar = null)
    {
        $this->requirePsbLogin();

        if (!$idPendaftar) {
            header('Location: ' . BASEURL . '/psb/dashboardPendaftar');
            exit;
        }

        $pendaftar = $this->psbModel->getPendaftarById($idPendaftar);

        // Verifikasi kepemilikan dan status
        if (!$pendaftar || $pendaftar['id_akun'] != $_SESSION['psb_akun']['id_akun']) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Akses ditolak'];
            header('Location: ' . BASEURL . '/psb/dashboardPendaftar');
            exit;
        }

        // Hanya bisa hapus jika draft atau revisi
        if (!in_array($pendaftar['status'], ['draft', 'revisi'])) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Pendaftaran tidak dapat dihapus'];
            header('Location: ' . BASEURL . '/psb/dashboardPendaftar');
            exit;
        }

        if ($this->psbModel->hapusPendaftaran($idPendaftar)) {
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Pendaftaran berhasil dihapus'];
        } else {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Gagal menghapus pendaftaran'];
        }

        header('Location: ' . BASEURL . '/psb/dashboardPendaftar');
        exit;
    }

    /**
     * Isi formulir per bagian (menu-based)
     */
    public function isiFormulir($idPendaftar = null, $section = 'data_diri')
    {
        $this->requirePsbLogin();

        if (!$idPendaftar) {
            header('Location: ' . BASEURL . '/psb/dashboardPendaftar');
            exit;
        }

        $pendaftar = $this->psbModel->getPendaftarById($idPendaftar);

        if (!$pendaftar || $pendaftar['id_akun'] != $_SESSION['psb_akun']['id_akun']) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Akses ditolak'];
            header('Location: ' . BASEURL . '/psb/dashboardPendaftar');
            exit;
        }

        $validSections = ['data_diri', 'alamat', 'sekolah_asal', 'data_ayah', 'data_ibu', 'data_wali', 'dokumen'];
        if (!in_array($section, $validSections)) {
            $section = 'data_diri';
        }

        $data = [
            'judul' => 'Isi Formulir',
            'pendaftar' => $pendaftar,
            'section' => $section,
            'dokumen' => $this->psbModel->getDokumenPendaftar($idPendaftar)
        ];

        $this->view('psb/isi_formulir', $data);
    }

    /**
     * Simpan isian formulir per bagian
     */
    public function simpanIsiFormulir($idPendaftar = null)
    {
        $this->requirePsbLogin();

        if ($_SERVER['REQUEST_METHOD'] != 'POST' || !$idPendaftar) {
            header('Location: ' . BASEURL . '/psb/dashboardPendaftar');
            exit;
        }

        $pendaftar = $this->psbModel->getPendaftarById($idPendaftar);

        if (!$pendaftar || $pendaftar['id_akun'] != $_SESSION['psb_akun']['id_akun']) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Akses ditolak'];
            header('Location: ' . BASEURL . '/psb/dashboardPendaftar');
            exit;
        }

        $section = $_POST['section'] ?? 'data_diri';
        $formData = $this->getFormDataBySection($section);

        if (!empty($formData)) {
            $this->psbModel->updatePendaftarData($idPendaftar, $formData);
        }

        if ($section == 'dokumen' && !empty($_FILES['dokumen'])) {
            $this->handleDokumenUpload($idPendaftar, $_FILES['dokumen']);
        }

        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Data berhasil disimpan'];
        header('Location: ' . BASEURL . '/psb/dashboardPendaftar');
        exit;
    }

    /**
     * Get form data by section
     */
    private function getFormDataBySection($section)
    {
        $data = [];
        $fields = [];

        switch ($section) {
            case 'data_diri':
                $fields = [
                    'nik',
                    'nisn',
                    'nama_lengkap',
                    'kip',
                    'tempat_lahir',
                    'tanggal_lahir',
                    'jenis_kelamin',
                    'agama',
                    'jumlah_saudara',
                    'anak_ke',
                    'hobi',
                    'cita_cita',
                    'no_hp',
                    'email',
                    'yang_membiayai',
                    'kebutuhan_disabilitas',
                    'kebutuhan_khusus'
                ];
                break;
            case 'alamat':
                $fields = [
                    'alamat',
                    'rt',
                    'rw',
                    'dusun',
                    'desa',
                    'kecamatan',
                    'kabupaten',
                    'provinsi',
                    'kode_pos',
                    'jarak_ke_sekolah',
                    'waktu_tempuh',
                    'transportasi'
                ];
                break;
            case 'sekolah_asal':
                $fields = ['nama_sekolah_asal', 'npsn_sekolah_asal', 'alamat_sekolah_asal', 'tahun_lulus'];
                break;
            case 'data_ayah':
                $fields = [
                    'ayah_nama',
                    'ayah_nik',
                    'ayah_tempat_lahir',
                    'ayah_tanggal_lahir',
                    'ayah_pendidikan',
                    'ayah_pekerjaan',
                    'ayah_penghasilan',
                    'ayah_no_hp'
                ];
                break;
            case 'data_ibu':
                $fields = [
                    'ibu_nama',
                    'ibu_nik',
                    'ibu_tempat_lahir',
                    'ibu_tanggal_lahir',
                    'ibu_pendidikan',
                    'ibu_pekerjaan',
                    'ibu_penghasilan',
                    'ibu_no_hp'
                ];
                break;
            case 'data_wali':
                $fields = [
                    'wali_nama',
                    'wali_nik',
                    'wali_tempat_lahir',
                    'wali_tanggal_lahir',
                    'wali_pendidikan',
                    'wali_pekerjaan',
                    'wali_penghasilan',
                    'wali_no_hp',
                    'wali_hubungan',
                    'wali_alamat'
                ];
                break;
        }

        foreach ($fields as $field) {
            if (isset($_POST[$field])) {
                $data[$field] = trim($_POST[$field]);
            }
        }

        return $data;
    }

    /**
     * Handle dokumen upload
     */
    private function handleDokumenUpload($idPendaftar, $files)
    {
        $uploadDir = 'public/uploads/psb/' . $idPendaftar . '/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        foreach ($files['name'] as $jenis => $name) {
            if (empty($name) || $files['error'][$jenis] != UPLOAD_ERR_OK)
                continue;

            $tmpName = $files['tmp_name'][$jenis];
            $size = $files['size'][$jenis];
            if ($size > 2 * 1024 * 1024)
                continue;

            $ext = pathinfo($name, PATHINFO_EXTENSION);
            $newName = $jenis . '_' . time() . '.' . $ext;
            $targetPath = $uploadDir . $newName;

            if (move_uploaded_file($tmpName, $targetPath)) {
                $this->psbModel->uploadDokumen($idPendaftar, $jenis, $name, $targetPath, $size);
            }
        }
    }

    /**
     * Upload Dokumen AJAX (auto-save)
     */
    public function uploadDokumen($idPendaftar = null)
    {
        // Suppress PHP errors to prevent HTML output before JSON
        error_reporting(E_ALL);
        ini_set('display_errors', 0);

        // Capture any fatal errors
        ob_start();

        // Register shutdown to catch fatal errors
        register_shutdown_function(function () {
            $error = error_get_last();
            if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
                ob_end_clean();
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => 'Fatal Error: ' . $error['message'],
                    'file' => $error['file'],
                    'line' => $error['line']
                ]);
            }
        });

        header('Content-Type: application/json');

        try {
            // DBG: Start
            error_log("PSB Upload: Start Request for ID " . $idPendaftar);

            // Check PSB login for AJAX (return JSON instead of redirect)
            if (!isset($_SESSION['psb_akun'])) {
                error_log("PSB Upload: No Session");
                echo json_encode(['success' => false, 'message' => 'Silakan login terlebih dahulu']);
                return;
            }

            if (!$idPendaftar || $_SERVER['REQUEST_METHOD'] != 'POST') {
                error_log("PSB Upload: Invalid Request");
                echo json_encode(['success' => false, 'message' => 'Invalid request']);
                return;
            }

            // DBG: Get Pendaftar
            error_log("PSB Upload: Getting Pendaftar Data");
            $pendaftar = $this->psbModel->getPendaftarById($idPendaftar);

            if (!$pendaftar || $pendaftar['id_akun'] != $_SESSION['psb_akun']['id_akun']) {
                echo json_encode(['success' => false, 'message' => 'Akses ditolak']);
                return;
            }

            if (empty($_FILES['dokumen'])) {
                echo json_encode(['success' => false, 'message' => 'Tidak ada file']);
                return;
            }

            $uploadDir = 'public/uploads/psb/' . $idPendaftar . '/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $jenis = $_POST['jenis'] ?? '';
            $files = $_FILES['dokumen'];

            // Handle both array format dokumen[key] and single file
            if (!isset($files['name'][$jenis])) {
                echo json_encode(['success' => false, 'message' => 'File tidak ditemukan untuk jenis: ' . $jenis, 'debug' => array_keys($files['name'] ?? [])]);
                return;
            }

            $name = $files['name'][$jenis];
            $tmpName = $files['tmp_name'][$jenis];
            $size = $files['size'][$jenis];
            $error = $files['error'][$jenis];

            if (empty($name) || $error != UPLOAD_ERR_OK) {
                echo json_encode(['success' => false, 'message' => 'Upload error code: ' . $error]);
                return;
            }

            if ($size > 2 * 1024 * 1024) {
                echo json_encode(['success' => false, 'message' => 'File terlalu besar (max 2MB)']);
                return;
            }

            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            $allowedExt = ['jpg', 'jpeg', 'png', 'pdf'];
            if (!in_array($ext, $allowedExt)) {
                echo json_encode(['success' => false, 'message' => 'Format tidak didukung']);
                return;
            }

            // DBG: Processing file
            error_log("PSB Upload: Processing file " . $name);

            $newName = $jenis . '_' . time() . '.' . $ext;

            // Cek apakah Google Drive sudah terhubung
            $driveFileId = null;
            $driveUrl = null;
            $useGoogleDrive = false;
            $drive = null;

            try {
                error_log("PSB Upload: Checking Google Drive");
                if (file_exists(APPROOT . '/app/core/GoogleDrive.php')) {
                    require_once APPROOT . '/app/core/GoogleDrive.php';
                    $drive = new GoogleDrive();
                    $useGoogleDrive = $drive->isConnected();
                    error_log("PSB Upload: Drive Connected? " . ($useGoogleDrive ? 'Yes' : 'No'));
                } else {
                    error_log("PSB Upload: GoogleDrive.php not found");
                }
            } catch (Exception $e) {
                // GoogleDrive class tidak tersedia, gunakan lokal
                error_log("PSB Upload: Drive check error: " . $e->getMessage());
            }

            if ($useGoogleDrive) {
                // Upload ke Google Drive
                try {
                    error_log("PSB Upload: Attempting Drive Upload");
                    $pendaftar = $this->psbModel->getPendaftarById($idPendaftar);
                    $namaFolder = $pendaftar['nisn'] ?? $pendaftar['no_pendaftaran'];
                    $namaFolder = $namaFolder . '_' . preg_replace('/\s+/', '_', $pendaftar['nama_lengkap']);

                    // Ambil parent folder ID (folder utama yang dibuat saat OAuth)
                    $mainFolderId = $drive->getFolderId();

                    // Cari atau buat folder untuk pendaftar ini DI DALAM folder utama
                    $siswaFolder = $drive->findOrCreateFolder($namaFolder, $mainFolderId);
                    $parentId = $siswaFolder ? $siswaFolder['id'] : $mainFolderId;

                    // Upload file
                    $uploadResult = $drive->uploadFile($tmpName, $newName, $parentId);
                    error_log("PSB Upload: Drive Result: " . print_r($uploadResult, true));

                    if ($uploadResult && isset($uploadResult['id'])) {
                        $driveFileId = $uploadResult['id'];
                        // Set file menjadi public
                        $drive->setPublic($driveFileId);
                        $driveUrl = $drive->getPublicUrl($driveFileId);

                        // Simpan ke database dengan info Drive
                        $res = $this->psbModel->uploadDokumen($idPendaftar, $jenis, $name, $driveUrl, $size, $driveFileId, $driveUrl);
                        error_log("PSB Upload: DB Save Result: " . $res);

                        echo json_encode([
                            'success' => true,
                            'message' => 'Berhasil disimpan ke Google Drive',
                            'path' => $driveUrl,
                            'drive_id' => $driveFileId
                        ]);
                        return;
                    } else {
                        // Fallback ke lokal jika gagal upload ke Drive
                        error_log("GoogleDrive upload failed, falling back to local storage");
                    }
                } catch (Exception $e) {
                    error_log("GoogleDrive upload error: " . $e->getMessage());
                    // Fallback ke lokal
                }
            }

            error_log("PSB Upload: Falling back to local");

            // Upload ke lokal (fallback atau jika Drive tidak aktif)
            $targetPath = $uploadDir . $newName;
            if (move_uploaded_file($tmpName, $targetPath)) {
                $this->psbModel->uploadDokumen($idPendaftar, $jenis, $name, $targetPath, $size);
                echo json_encode(['success' => true, 'message' => 'Berhasil disimpan', 'path' => $targetPath]);
                return;
            } else {
                echo json_encode(['success' => false, 'message' => 'Gagal menyimpan file']);
                return;
            }

        } catch (Exception $e) {
            error_log("PSB uploadDokumen error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    /**
     * Kirim pendaftaran (final submit)
     */
    public function kirimPendaftaran($idPendaftar = null)
    {
        $this->requirePsbLogin();

        if (!$idPendaftar) {
            header('Location: ' . BASEURL . '/psb/dashboardPendaftar');
            exit;
        }

        $pendaftar = $this->psbModel->getPendaftarById($idPendaftar);

        if (!$pendaftar || $pendaftar['id_akun'] != $_SESSION['psb_akun']['id_akun']) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Akses ditolak'];
            header('Location: ' . BASEURL . '/psb/dashboardPendaftar');
            exit;
        }

        $this->psbModel->submitPendaftaran($idPendaftar);

        // Kirim notifikasi WA bahwa formulir sudah dikirim
        try {
            require_once APPROOT . '/app/core/Fonnte.php';
            $fonnte = new Fonnte();
            $akun = $_SESSION['psb_akun'];

            $message = "✅ *Formulir Terkirim*\n\n";
            $message .= "Halo *" . ($pendaftar['nama_lengkap'] ?? $akun['nama'] ?? 'Calon Siswa') . "*,\n\n";
            $message .= "Formulir pendaftaran Anda dengan nomor:\n";
            $message .= "*" . ($pendaftar['no_pendaftaran'] ?? '-') . "*\n\n";
            $message .= "telah berhasil dikirim untuk diverifikasi.\n\n";
            $message .= "Kami akan menginformasikan hasilnya melalui WhatsApp ini.\n\n";
            $message .= "Terima kasih.";

            $fonnte->send($akun['no_wa'], $message);
        } catch (Exception $e) {
            // Silent fail
        }

        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Pendaftaran berhasil dikirim! Silakan tunggu verifikasi.'];
        header('Location: ' . BASEURL . '/psb/dashboardPendaftar');
        exit;
    }

    /**
     * Detail pendaftaran untuk pendaftar (view only)
     */
    public function detailPendaftaran($idPendaftar = null)
    {
        $this->requirePsbLogin();

        if (!$idPendaftar) {
            header('Location: ' . BASEURL . '/psb/dashboardPendaftar');
            exit;
        }

        $pendaftar = $this->psbModel->getPendaftarById($idPendaftar);

        if (!$pendaftar || $pendaftar['id_akun'] != $_SESSION['psb_akun']['id_akun']) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Akses ditolak'];
            header('Location: ' . BASEURL . '/psb/dashboardPendaftar');
            exit;
        }

        // Get dokumen
        $dokumen = $this->psbModel->getDokumenPendaftar($idPendaftar);

        $data = [
            'judul' => 'Detail Pendaftaran',
            'pendaftar' => $pendaftar,
            'dokumen' => $dokumen,
            'akun' => $_SESSION['psb_akun']
        ];

        $this->view('psb/detail_pendaftaran', $data);
    }

    /**
     * Cetak formulir pendaftaran PDF
     */
    public function cetakFormulir($idPendaftar = null)
    {
        $this->requirePsbLogin();

        if (!$idPendaftar) {
            header('Location: ' . BASEURL . '/psb/dashboardPendaftar');
            exit;
        }

        $pendaftar = $this->psbModel->getPendaftarById($idPendaftar);

        if (!$pendaftar || $pendaftar['id_akun'] != $_SESSION['psb_akun']['id_akun']) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Akses ditolak'];
            header('Location: ' . BASEURL . '/psb/dashboardPendaftar');
            exit;
        }

        $dokumen = $this->psbModel->getDokumenPendaftar($idPendaftar);
        $namaSekolah = getPengaturanAplikasi()['nama_aplikasi'] ?? 'Smart Absensi';

        // Generate HTML for PDF
        $html = $this->generateFormulirHTML($pendaftar, $dokumen, $namaSekolah);

        // Setup Dompdf
        $dompdfPath = __DIR__ . '/../core/dompdf/autoload.inc.php';
        if (!file_exists($dompdfPath)) {
            header('Content-Type: text/html; charset=utf-8');
            echo "<div style='padding:20px;font-family:Arial,sans-serif;'>Library Dompdf tidak ditemukan di core/dompdf/</div>";
            echo $html;
            return;
        }

        require_once $dompdfPath;
        try {
            $dompdf = new \Dompdf\Dompdf([
                'isRemoteEnabled' => true,
                'isHtml5ParserEnabled' => true,
                'defaultFont' => 'Arial'
            ]);
            $dompdf->loadHtml($html, 'UTF-8');
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            $filename = 'Formulir_PSB_' . ($pendaftar['no_pendaftaran'] ?? 'Unknown') . '.pdf';
            $dompdf->stream($filename, ['Attachment' => true]);
            return;
        } catch (Exception $e) {
            header('Content-Type: text/html; charset=utf-8');
            echo "<div style='padding:20px;color:#ef4444;'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
            echo $html;
            return;
        }
    }

    /**
     * Kirim formulir PDF via WhatsApp
     */
    public function kirimFormulirWA($idPendaftar = null)
    {
        $this->requirePsbLogin();

        if (!$idPendaftar) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'ID pendaftar tidak valid'];
            header('Location: ' . BASEURL . '/psb/dashboardPendaftar');
            exit;
        }

        $pendaftar = $this->psbModel->getPendaftarById($idPendaftar);

        if (!$pendaftar || $pendaftar['id_akun'] != $_SESSION['psb_akun']['id_akun']) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Akses ditolak'];
            header('Location: ' . BASEURL . '/psb/dashboardPendaftar');
            exit;
        }

        $dokumen = $this->psbModel->getDokumenPendaftar($idPendaftar);
        $namaSekolah = getPengaturanAplikasi()['nama_aplikasi'] ?? 'Smart Absensi';

        // Generate PDF
        $html = $this->generateFormulirHTML($pendaftar, $dokumen, $namaSekolah);

        $dompdfPath = __DIR__ . '/../core/dompdf/autoload.inc.php';
        if (!file_exists($dompdfPath)) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Library Dompdf tidak ditemukan'];
            header('Location: ' . BASEURL . '/psb/detailPendaftaran/' . $idPendaftar);
            exit;
        }

        require_once $dompdfPath;
        try {
            $dompdf = new \Dompdf\Dompdf([
                'isRemoteEnabled' => true,
                'isHtml5ParserEnabled' => true,
                'defaultFont' => 'Arial'
            ]);
            $dompdf->loadHtml($html, 'UTF-8');
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            // Save PDF to temp file
            $pdfOutput = $dompdf->output();
            $filename = 'Formulir_PSB_' . ($pendaftar['no_pendaftaran'] ?? 'Unknown') . '.pdf';

            // Send via Fonnte
            require_once APPROOT . '/app/core/Fonnte.php';
            $fonnte = new Fonnte();

            $noWa = $pendaftar['akun_no_wa'] ?? $_SESSION['psb_akun']['no_wa'] ?? '';
            if (empty($noWa)) {
                $_SESSION['flash'] = ['type' => 'error', 'message' => 'Nomor WA tidak ditemukan'];
                header('Location: ' . BASEURL . '/psb/detailPendaftaran/' . $idPendaftar);
                exit;
            }

            $message = "📋 *Formulir Pendaftaran PSB*\n\n";
            $message .= "Berikut adalah formulir pendaftaran Anda:\n";
            $message .= "No. Pendaftaran: *" . ($pendaftar['no_pendaftaran'] ?? '-') . "*\n";
            $message .= "Nama: *" . ($pendaftar['nama_lengkap'] ?? '-') . "*\n\n";
            $message .= "Terima kasih.";

            // Send with file content (base64 encoded internally)
            $result = $fonnte->sendWithFile($noWa, $message, $pdfOutput, $filename);

            if ($result && isset($result['status']) && $result['status'] === true) {
                $_SESSION['flash'] = ['type' => 'success', 'message' => 'Formulir berhasil dikirim ke WhatsApp!'];
            } else {
                $errorMsg = $result['reason'] ?? 'Gagal mengirim ke WA';
                $_SESSION['flash'] = ['type' => 'error', 'message' => 'Gagal: ' . $errorMsg];
            }

        } catch (Exception $e) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Error: ' . $e->getMessage()];
        }

        header('Location: ' . BASEURL . '/psb/detailPendaftaran/' . $idPendaftar);
        exit;
    }

    /**
     * Generate HTML for formulir PDF
     */
    private function generateFormulirHTML($p, $dokumen, $namaSekolah)
    {
        $statusLabels = [
            'draft' => 'Draft',
            'pending' => 'Menunggu Verifikasi',
            'verifikasi' => 'Terverifikasi',
            'revisi' => 'Perlu Revisi',
            'diterima' => 'Diterima',
            'ditolak' => 'Ditolak'
        ];
        $statusLabel = $statusLabels[$p['status'] ?? 'draft'] ?? 'Draft';

        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Formulir Pendaftaran - ' . htmlspecialchars($p['no_pendaftaran'] ?? '') . '</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; line-height: 1.4; margin: 20px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #0ea5e9; padding-bottom: 15px; }
        .header h1 { margin: 0; font-size: 18px; color: #0ea5e9; }
        .header p { margin: 5px 0 0; color: #666; }
        .section { margin-bottom: 15px; }
        .section-title { background: #f0f9ff; padding: 8px 12px; font-weight: bold; border-left: 4px solid #0ea5e9; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 5px 8px; vertical-align: top; }
        .label { color: #666; width: 30%; }
        .value { font-weight: 500; }
        .status-box { display: inline-block; padding: 5px 15px; border-radius: 20px; font-weight: bold; }
        .status-diterima { background: #dcfce7; color: #16a34a; }
        .status-pending { background: #fef3c7; color: #d97706; }
        .status-draft { background: #f3f4f6; color: #6b7280; }
        .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #999; }
        .two-col { display: table; width: 100%; }
        .two-col .col { display: table-cell; width: 50%; padding-right: 10px; vertical-align: top; }
    </style>
</head>
<body>
    <div class="header">
        <h1>' . htmlspecialchars($namaSekolah) . '</h1>
        <p>FORMULIR PENDAFTARAN SISWA BARU</p>
        <p style="margin-top:10px">No. Pendaftaran: <strong>' . htmlspecialchars($p['no_pendaftaran'] ?? '-') . '</strong></p>
        <p>Status: <span class="status-box status-' . ($p['status'] ?? 'draft') . '">' . $statusLabel . '</span></p>
    </div>

    <div class="section">
        <div class="section-title">INFORMASI PENDAFTARAN</div>
        <table>
            <tr><td class="label">Lembaga</td><td class="value">' . htmlspecialchars($p['nama_lembaga'] ?? '-') . '</td></tr>
            <tr><td class="label">Periode</td><td class="value">' . htmlspecialchars($p['nama_periode'] ?? '-') . '</td></tr>
            <tr><td class="label">Jalur</td><td class="value">' . htmlspecialchars($p['nama_jalur'] ?? '-') . '</td></tr>
            <tr><td class="label">Tanggal Daftar</td><td class="value">' . ($p['tanggal_daftar'] ? date('d/m/Y H:i', strtotime($p['tanggal_daftar'])) : '-') . '</td></tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">DATA PRIBADI</div>
        <table>
            <tr><td class="label">Nama Lengkap</td><td class="value">' . htmlspecialchars($p['nama_lengkap'] ?? '-') . '</td></tr>
            <tr><td class="label">NIK</td><td class="value">' . htmlspecialchars($p['nik'] ?? '-') . '</td></tr>
            <tr><td class="label">NISN</td><td class="value">' . htmlspecialchars($p['nisn'] ?? '-') . '</td></tr>
            <tr><td class="label">No. KIP</td><td class="value">' . htmlspecialchars($p['no_kip'] ?? '-') . '</td></tr>
            <tr><td class="label">Tempat, Tgl Lahir</td><td class="value">' . htmlspecialchars($p['tempat_lahir'] ?? '-') . ', ' . ($p['tanggal_lahir'] ? date('d/m/Y', strtotime($p['tanggal_lahir'])) : '-') . '</td></tr>
            <tr><td class="label">Jenis Kelamin</td><td class="value">' . ($p['jenis_kelamin'] == 'L' ? 'Laki-laki' : ($p['jenis_kelamin'] == 'P' ? 'Perempuan' : '-')) . '</td></tr>
            <tr><td class="label">Agama</td><td class="value">' . htmlspecialchars($p['agama'] ?? '-') . '</td></tr>
            <tr><td class="label">Anak Ke / Jumlah Saudara</td><td class="value">' . ($p['anak_ke'] ?? '-') . ' / ' . ($p['jumlah_saudara'] ?? '-') . '</td></tr>
            <tr><td class="label">No. HP/WA</td><td class="value">' . htmlspecialchars($p['no_hp'] ?? '-') . '</td></tr>
            <tr><td class="label">Email</td><td class="value">' . htmlspecialchars($p['email'] ?? '-') . '</td></tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">ALAMAT</div>
        <table>
            <tr><td class="label">Alamat</td><td class="value">' . htmlspecialchars($p['alamat'] ?? '-') . '</td></tr>
            <tr><td class="label">RT/RW</td><td class="value">' . htmlspecialchars($p['rt'] ?? '-') . '/' . htmlspecialchars($p['rw'] ?? '-') . '</td></tr>
            <tr><td class="label">Dusun</td><td class="value">' . htmlspecialchars($p['dusun'] ?? '-') . '</td></tr>
            <tr><td class="label">Desa/Kelurahan</td><td class="value">' . htmlspecialchars($p['desa'] ?? '-') . '</td></tr>
            <tr><td class="label">Kecamatan</td><td class="value">' . htmlspecialchars($p['kecamatan'] ?? '-') . '</td></tr>
            <tr><td class="label">Kabupaten/Kota</td><td class="value">' . htmlspecialchars($p['kabupaten'] ?? '-') . '</td></tr>
            <tr><td class="label">Provinsi</td><td class="value">' . htmlspecialchars($p['provinsi'] ?? '-') . '</td></tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">ASAL SEKOLAH</div>
        <table>
            <tr><td class="label">Nama Sekolah</td><td class="value">' . htmlspecialchars($p['nama_sekolah_asal'] ?? ($p['asal_sekolah'] ?? '-')) . '</td></tr>
            <tr><td class="label">NPSN</td><td class="value">' . htmlspecialchars($p['npsn_sekolah_asal'] ?? '-') . '</td></tr>
            <tr><td class="label">Tahun Lulus</td><td class="value">' . htmlspecialchars($p['tahun_lulus'] ?? '-') . '</td></tr>
        </table>
    </div>

    <div class="two-col">
        <div class="col">
            <div class="section">
                <div class="section-title">DATA AYAH</div>
                <table>
                    <tr><td class="label">Nama</td><td class="value">' . htmlspecialchars($p['ayah_nama'] ?? '-') . '</td></tr>
                    <tr><td class="label">NIK</td><td class="value">' . htmlspecialchars($p['ayah_nik'] ?? '-') . '</td></tr>
                    <tr><td class="label">Pekerjaan</td><td class="value">' . htmlspecialchars($p['ayah_pekerjaan'] ?? '-') . '</td></tr>
                    <tr><td class="label">No. HP</td><td class="value">' . htmlspecialchars($p['ayah_no_hp'] ?? '-') . '</td></tr>
                </table>
            </div>
        </div>
        <div class="col">
            <div class="section">
                <div class="section-title">DATA IBU</div>
                <table>
                    <tr><td class="label">Nama</td><td class="value">' . htmlspecialchars($p['ibu_nama'] ?? '-') . '</td></tr>
                    <tr><td class="label">NIK</td><td class="value">' . htmlspecialchars($p['ibu_nik'] ?? '-') . '</td></tr>
                    <tr><td class="label">Pekerjaan</td><td class="value">' . htmlspecialchars($p['ibu_pekerjaan'] ?? '-') . '</td></tr>
                    <tr><td class="label">No. HP</td><td class="value">' . htmlspecialchars($p['ibu_no_hp'] ?? '-') . '</td></tr>
                </table>
            </div>
        </div>
    </div>';

        if (!empty($p['wali_nama'])) {
            $html .= '
    <div class="section">
        <div class="section-title">DATA WALI</div>
        <table>
            <tr><td class="label">Nama</td><td class="value">' . htmlspecialchars($p['wali_nama']) . '</td></tr>
            <tr><td class="label">Hubungan</td><td class="value">' . htmlspecialchars($p['wali_hubungan'] ?? '-') . '</td></tr>
            <tr><td class="label">Pekerjaan</td><td class="value">' . htmlspecialchars($p['wali_pekerjaan'] ?? '-') . '</td></tr>
            <tr><td class="label">No. HP</td><td class="value">' . htmlspecialchars($p['wali_no_hp'] ?? '-') . '</td></tr>
        </table>
    </div>';
        }

        // Generate QR Code
        $qrHtml = '';
        try {
            require_once APPROOT . '/config/qrcode.php';
            $qrCodeDataUrl = generatePDFQRCode('psb_formulir', $p['id_pendaftar'], [
                'no_pendaftaran' => $p['no_pendaftaran'] ?? '',
                'nama' => $p['nama_lengkap'] ?? '',
                'nisn' => $p['nisn'] ?? ''
            ]);
            if (!empty($qrCodeDataUrl)) {
                $qrHtml = '
    <div style="position: absolute; bottom: 10mm; right: 10mm; text-align: center; background: white; padding: 5px; border: 1px solid #ddd; border-radius: 3px;">
        <img src="' . htmlspecialchars($qrCodeDataUrl) . '" style="width: 70px; height: 70px; display: block;" alt="QR Code">
        <div style="font-size: 7px; color: #666; margin-top: 2px;">Scan untuk validasi</div>
    </div>';
            }
        } catch (Exception $e) {
            error_log('QR code generation error: ' . $e->getMessage());
        }

        $html .= '
    <div class="footer">
        <p>Dicetak pada: ' . date('d/m/Y H:i') . ' | ' . $namaSekolah . '</p>
    </div>
    ' . $qrHtml . '
</body>
</html>';

        return $html;
    }

    // =========================================================================
    // AKUN PENDAFTAR MANAGEMENT
    // =========================================================================

    /**
     * Daftar akun calon siswa
     */
    public function akunPendaftar()
    {
        $this->requireAdmin();

        $data = [
            'judul' => 'Akun Calon Siswa',
            'sidebar_data' => $this->getSidebarData(),
            'akun_list' => $this->psbModel->getAllAkun()
        ];

        $this->view('admin/psb/akun_pendaftar', $data);
    }

    /**
     * Update akun pendaftar
     */
    public function updateAkun()
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            header('Location: ' . BASEURL . '/psb/akunPendaftar');
            exit;
        }

        $id = filter_input(INPUT_POST, 'id_akun', FILTER_VALIDATE_INT);
        $data = [
            'nama_lengkap' => trim($_POST['nama_lengkap'] ?? ''),
            'no_wa' => trim($_POST['no_wa'] ?? ''),
            'password' => $_POST['password'] ?? ''
        ];

        if ($this->psbModel->updateAkun($id, $data)) {
            Flasher::setFlash('Akun berhasil diupdate', 'success');
        } else {
            Flasher::setFlash('Gagal mengupdate akun', 'error');
        }

        header('Location: ' . BASEURL . '/psb/akunPendaftar');
        exit;
    }

    /**
     * Hapus akun pendaftar
     */
    public function hapusAkun($id)
    {
        $this->requireAdmin();

        if ($this->psbModel->hapusAkun($id)) {
            Flasher::setFlash('Akun berhasil dihapus', 'success');
        } else {
            Flasher::setFlash('Gagal menghapus akun. Mungkin sudah ada pendaftaran terkait.', 'error');
        }

        header('Location: ' . BASEURL . '/psb/akunPendaftar');
        exit;
    }

    // =========================================================================
    // ADMIN ROUTES (Memerlukan Login Admin)
    // =========================================================================

    /**
     * Dashboard PSB Admin
     */
    public function dashboard()
    {
        $this->requireAdmin();

        $data = array_merge($this->getSidebarData(), [
            'judul' => 'Dashboard PSB',
            'statistik' => $this->psbModel->getStatistikDashboard(),
            'periode_aktif' => $this->psbModel->getPeriodeAktif()
        ]);

        $this->view('admin/psb/dashboard', $data);
    }

    // =========================================================================
    // LEMBAGA MANAGEMENT
    // =========================================================================

    /**
     * Daftar lembaga
     */
    public function lembaga()
    {
        $this->requireAdmin();

        $data = [
            'judul' => 'Kelola Lembaga PSB',
            'sidebar_data' => $this->getSidebarData(),
            'lembaga' => $this->psbModel->getAllLembaga(false)
        ];

        $this->view('admin/psb/lembaga', $data);
    }

    /**
     * Tambah lembaga
     */
    public function tambahLembaga()
    {
        $this->requireAdmin();

        $data = [
            'judul' => 'Tambah Lembaga',
            'sidebar_data' => $this->getSidebarData()
        ];

        $this->view('admin/psb/tambah_lembaga', $data);
    }

    /**
     * Proses tambah lembaga
     */
    public function prosesTambahLembaga()
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            header('Location: ' . BASEURL . '/psb/lembaga');
            exit;
        }

        $data = [
            'kode_lembaga' => strtoupper(trim($_POST['kode_lembaga'] ?? '')),
            'nama_lembaga' => trim($_POST['nama_lembaga'] ?? ''),
            'jenjang' => $_POST['jenjang'] ?? '',
            'alamat' => trim($_POST['alamat'] ?? ''),
            'kuota_default' => filter_input(INPUT_POST, 'kuota_default', FILTER_VALIDATE_INT) ?: 0,
            'urutan' => filter_input(INPUT_POST, 'urutan', FILTER_VALIDATE_INT) ?: 0,
            'aktif' => isset($_POST['aktif']) ? 1 : 0
        ];

        if ($this->psbModel->tambahLembaga($data)) {
            Flasher::setFlash('Lembaga berhasil ditambahkan', 'success');
        } else {
            Flasher::setFlash('Gagal menambahkan lembaga', 'error');
        }

        header('Location: ' . BASEURL . '/psb/lembaga');
        exit;
    }

    /**
     * Edit lembaga
     */
    public function editLembaga($id)
    {
        $this->requireAdmin();

        $data = [
            'judul' => 'Edit Lembaga',
            'sidebar_data' => $this->getSidebarData(),
            'lembaga' => $this->psbModel->getLembagaById($id)
        ];

        if (!$data['lembaga']) {
            header('Location: ' . BASEURL . '/psb/lembaga');
            exit;
        }

        $this->view('admin/psb/edit_lembaga', $data);
    }

    /**
     * Proses update lembaga
     */
    public function prosesUpdateLembaga()
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            header('Location: ' . BASEURL . '/psb/lembaga');
            exit;
        }

        $id = filter_input(INPUT_POST, 'id_lembaga', FILTER_VALIDATE_INT);
        $data = [
            'kode_lembaga' => strtoupper(trim($_POST['kode_lembaga'] ?? '')),
            'nama_lembaga' => trim($_POST['nama_lembaga'] ?? ''),
            'jenjang' => $_POST['jenjang'] ?? '',
            'alamat' => trim($_POST['alamat'] ?? ''),
            'kuota_default' => filter_input(INPUT_POST, 'kuota_default', FILTER_VALIDATE_INT) ?: 0,
            'urutan' => filter_input(INPUT_POST, 'urutan', FILTER_VALIDATE_INT) ?: 0,
            'aktif' => isset($_POST['aktif']) ? 1 : 0
        ];

        if ($this->psbModel->updateLembaga($id, $data)) {
            Flasher::setFlash('Lembaga berhasil diupdate', 'success');
        } else {
            Flasher::setFlash('Gagal mengupdate lembaga', 'error');
        }

        header('Location: ' . BASEURL . '/psb/lembaga');
        exit;
    }

    /**
     * Hapus lembaga
     */
    public function hapusLembaga($id)
    {
        $this->requireAdmin();

        if ($this->psbModel->hapusLembaga($id)) {
            Flasher::setFlash('Lembaga berhasil dihapus', 'success');
        } else {
            Flasher::setFlash('Gagal menghapus lembaga. Mungkin masih digunakan.', 'error');
        }

        header('Location: ' . BASEURL . '/psb/lembaga');
        exit;
    }

    // =========================================================================
    // JALUR MANAGEMENT
    // =========================================================================

    /**
     * Daftar jalur
     */
    public function jalur()
    {
        $this->requireAdmin();

        $data = [
            'judul' => 'Kelola Jalur Pendaftaran',
            'sidebar_data' => $this->getSidebarData(),
            'jalur' => $this->psbModel->getAllJalur(false)
        ];

        $this->view('admin/psb/jalur', $data);
    }

    /**
     * Tambah jalur
     */
    public function tambahJalur()
    {
        $this->requireAdmin();

        $data = [
            'judul' => 'Tambah Jalur Pendaftaran',
            'sidebar_data' => $this->getSidebarData()
        ];

        $this->view('admin/psb/tambah_jalur', $data);
    }

    /**
     * Proses tambah jalur
     */
    public function prosesTambahJalur()
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            header('Location: ' . BASEURL . '/psb/jalur');
            exit;
        }

        $data = [
            'kode_jalur' => strtoupper(trim($_POST['kode_jalur'] ?? '')),
            'nama_jalur' => trim($_POST['nama_jalur'] ?? ''),
            'deskripsi' => trim($_POST['deskripsi'] ?? ''),
            'persyaratan' => trim($_POST['persyaratan'] ?? ''),
            'urutan' => filter_input(INPUT_POST, 'urutan', FILTER_VALIDATE_INT) ?: 0,
            'aktif' => isset($_POST['aktif']) ? 1 : 0
        ];

        if ($this->psbModel->tambahJalur($data)) {
            Flasher::setFlash('Jalur pendaftaran berhasil ditambahkan', 'success');
        } else {
            Flasher::setFlash('Gagal menambahkan jalur', 'error');
        }

        header('Location: ' . BASEURL . '/psb/jalur');
        exit;
    }

    /**
     * Edit jalur
     */
    public function editJalur($id)
    {
        $this->requireAdmin();

        $data = [
            'judul' => 'Edit Jalur Pendaftaran',
            'sidebar_data' => $this->getSidebarData(),
            'jalur' => $this->psbModel->getJalurById($id)
        ];

        if (!$data['jalur']) {
            header('Location: ' . BASEURL . '/psb/jalur');
            exit;
        }

        $this->view('admin/psb/edit_jalur', $data);
    }

    /**
     * Proses update jalur
     */
    public function prosesUpdateJalur()
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            header('Location: ' . BASEURL . '/psb/jalur');
            exit;
        }

        $id = filter_input(INPUT_POST, 'id_jalur', FILTER_VALIDATE_INT);
        $data = [
            'kode_jalur' => strtoupper(trim($_POST['kode_jalur'] ?? '')),
            'nama_jalur' => trim($_POST['nama_jalur'] ?? ''),
            'deskripsi' => trim($_POST['deskripsi'] ?? ''),
            'persyaratan' => trim($_POST['persyaratan'] ?? ''),
            'urutan' => filter_input(INPUT_POST, 'urutan', FILTER_VALIDATE_INT) ?: 0,
            'aktif' => isset($_POST['aktif']) ? 1 : 0
        ];

        if ($this->psbModel->updateJalur($id, $data)) {
            Flasher::setFlash('Jalur berhasil diupdate', 'success');
        } else {
            Flasher::setFlash('Gagal mengupdate jalur', 'error');
        }

        header('Location: ' . BASEURL . '/psb/jalur');
        exit;
    }

    /**
     * Hapus jalur
     */
    public function hapusJalur($id)
    {
        $this->requireAdmin();

        if ($this->psbModel->hapusJalur($id)) {
            Flasher::setFlash('Jalur berhasil dihapus', 'success');
        } else {
            Flasher::setFlash('Gagal menghapus jalur. Mungkin masih digunakan.', 'error');
        }

        header('Location: ' . BASEURL . '/psb/jalur');
        exit;
    }

    // =========================================================================
    // PERIODE MANAGEMENT
    // =========================================================================

    /**
     * Daftar periode
     */
    public function periode()
    {
        $this->requireAdmin();

        $data = [
            'judul' => 'Kelola Periode PSB',
            'sidebar_data' => $this->getSidebarData(),
            'periode' => $this->psbModel->getAllPeriode()
        ];

        $this->view('admin/psb/periode', $data);
    }

    /**
     * Tambah periode
     */
    public function tambahPeriode()
    {
        $this->requireAdmin();

        $tpModel = $this->model('TahunPelajaran_model');

        $data = [
            'judul' => 'Tambah Periode PSB',
            'sidebar_data' => $this->getSidebarData(),
            'lembaga' => $this->psbModel->getAllLembaga(true),
            'jalur' => $this->psbModel->getAllJalur(true),
            'tahun_pelajaran' => $tpModel->getAllTahunPelajaran()
        ];

        $this->view('admin/psb/tambah_periode', $data);
    }

    /**
     * Proses tambah periode
     */
    public function prosesTambahPeriode()
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            header('Location: ' . BASEURL . '/psb/periode');
            exit;
        }

        $data = [
            'id_lembaga' => filter_input(INPUT_POST, 'id_lembaga', FILTER_VALIDATE_INT),
            'nama_periode' => trim($_POST['nama_periode'] ?? ''),
            'id_tp' => filter_input(INPUT_POST, 'id_tp', FILTER_VALIDATE_INT),
            'tanggal_buka' => $_POST['tanggal_buka'] ?? '',
            'tanggal_tutup' => $_POST['tanggal_tutup'] ?? '',
            'kuota' => filter_input(INPUT_POST, 'kuota', FILTER_VALIDATE_INT) ?: 0,
            'biaya_pendaftaran' => filter_input(INPUT_POST, 'biaya_pendaftaran', FILTER_VALIDATE_FLOAT) ?: 0,
            'status' => $_POST['status'] ?? 'draft',
            'keterangan' => trim($_POST['keterangan'] ?? '')
        ];

        $id_periode = $this->psbModel->tambahPeriode($data);

        if ($id_periode) {
            // Simpan kuota per jalur
            if (isset($_POST['kuota_jalur']) && is_array($_POST['kuota_jalur'])) {
                foreach ($_POST['kuota_jalur'] as $id_jalur => $kuota) {
                    $this->psbModel->updateKuotaJalur($id_periode, $id_jalur, intval($kuota));
                }
            }
            Flasher::setFlash('Periode PSB berhasil ditambahkan', 'success');
        } else {
            Flasher::setFlash('Gagal menambahkan periode', 'error');
        }

        header('Location: ' . BASEURL . '/psb/periode');
        exit;
    }

    /**
     * Edit periode
     */
    public function editPeriode($id)
    {
        $this->requireAdmin();

        $tpModel = $this->model('TahunPelajaran_model');

        $data = [
            'judul' => 'Edit Periode PSB',
            'sidebar_data' => $this->getSidebarData(),
            'periode' => $this->psbModel->getPeriodeById($id),
            'lembaga' => $this->psbModel->getAllLembaga(true),
            'jalur' => $this->psbModel->getKuotaJalur($id),
            'tahun_pelajaran' => $tpModel->getAllTahunPelajaran()
        ];

        if (!$data['periode']) {
            header('Location: ' . BASEURL . '/psb/periode');
            exit;
        }

        $this->view('admin/psb/edit_periode', $data);
    }

    /**
     * Proses update periode
     */
    public function prosesUpdatePeriode()
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            header('Location: ' . BASEURL . '/psb/periode');
            exit;
        }

        $id = filter_input(INPUT_POST, 'id_periode', FILTER_VALIDATE_INT);
        $data = [
            'id_lembaga' => filter_input(INPUT_POST, 'id_lembaga', FILTER_VALIDATE_INT),
            'nama_periode' => trim($_POST['nama_periode'] ?? ''),
            'id_tp' => filter_input(INPUT_POST, 'id_tp', FILTER_VALIDATE_INT),
            'tanggal_buka' => $_POST['tanggal_buka'] ?? '',
            'tanggal_tutup' => $_POST['tanggal_tutup'] ?? '',
            'kuota' => filter_input(INPUT_POST, 'kuota', FILTER_VALIDATE_INT) ?: 0,
            'biaya_pendaftaran' => filter_input(INPUT_POST, 'biaya_pendaftaran', FILTER_VALIDATE_FLOAT) ?: 0,
            'status' => $_POST['status'] ?? 'draft',
            'keterangan' => trim($_POST['keterangan'] ?? '')
        ];

        if ($this->psbModel->updatePeriode($id, $data)) {
            // Update kuota per jalur
            if (isset($_POST['kuota_jalur']) && is_array($_POST['kuota_jalur'])) {
                foreach ($_POST['kuota_jalur'] as $id_jalur => $kuota) {
                    $this->psbModel->updateKuotaJalur($id, $id_jalur, intval($kuota));
                }
            }
            Flasher::setFlash('Periode berhasil diupdate', 'success');
        } else {
            Flasher::setFlash('Gagal mengupdate periode', 'error');
        }

        header('Location: ' . BASEURL . '/psb/periode');
        exit;
    }

    /**
     * Hapus periode
     */
    public function hapusPeriode($id)
    {
        $this->requireAdmin();

        if ($this->psbModel->hapusPeriode($id)) {
            Flasher::setFlash('Periode berhasil dihapus', 'success');
        } else {
            Flasher::setFlash('Gagal menghapus periode. Mungkin sudah ada pendaftar.', 'error');
        }

        header('Location: ' . BASEURL . '/psb/periode');
        exit;
    }

    // =========================================================================
    // PENDAFTAR MANAGEMENT
    // =========================================================================

    /**
     * Daftar periode PSB dengan statistik (Card View)
     */
    public function pendaftar()
    {
        $this->requireAdmin();

        $periodeList = $this->psbModel->getAllPeriode();

        // Get statistik per periode
        $statistikPerPeriode = [];
        foreach ($periodeList as $p) {
            $stat = $this->psbModel->getStatistikPeriode($p['id_periode']);
            $statistikPerPeriode[$p['id_periode']] = $stat ?: ['total' => 0, 'pending' => 0, 'diterima' => 0];
        }

        $data = [
            'judul' => 'Daftar Pendaftar PSB',
            'sidebar_data' => $this->getSidebarData(),
            'periode_list' => $periodeList,
            'statistik_per_periode' => $statistikPerPeriode
        ];

        $this->view('admin/psb/pendaftar', $data);
    }

    /**
     * List pendaftar per periode
     */
    public function listPendaftar($id_periode)
    {
        $this->requireAdmin();

        $periode = $this->psbModel->getPeriodeById($id_periode);
        if (!$periode) {
            header('Location: ' . BASEURL . '/psb/pendaftar');
            exit;
        }

        $data = [
            'judul' => 'Calon Siswa - ' . $periode['nama_periode'],
            'sidebar_data' => $this->getSidebarData(),
            'periode' => $periode,
            'pendaftar' => $this->psbModel->getPendaftarByPeriode($id_periode),
            'statistik' => $this->psbModel->getStatistikPeriode($id_periode)
        ];

        $this->view('admin/psb/list_pendaftar', $data);
    }

    /**
     * Detail pendaftar
     */
    public function detailPendaftar($id)
    {
        $this->requireAdmin();

        $pendaftar = $this->psbModel->getPendaftarById($id);
        if (!$pendaftar) {
            header('Location: ' . BASEURL . '/psb/pendaftar');
            exit;
        }

        $kelasModel = $this->model('Kelas_model');

        // Get dokumen pendaftar
        $dokumen = $this->psbModel->getDokumenByPendaftar($id);

        $data = [
            'judul' => 'Detail Pendaftar',
            'sidebar_data' => $this->getSidebarData(),
            'pendaftar' => $pendaftar,
            'kelas' => $kelasModel->getAllKelas(),
            'dokumen' => $dokumen
        ];

        $this->view('admin/psb/detail_pendaftar', $data);
    }

    /**
     * Update status pendaftar
     */
    public function updateStatus()
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            header('Location: ' . BASEURL . '/psb/pendaftar');
            exit;
        }

        $id = filter_input(INPUT_POST, 'id_pendaftar', FILTER_VALIDATE_INT);
        $status = $_POST['status'] ?? '';
        $catatan = trim($_POST['catatan_admin'] ?? '');
        $verified_by = $_SESSION['id_user'] ?? null;

        // Get pendaftar data before update for WA notification
        $pendaftar = $this->psbModel->getPendaftarById($id);

        if ($this->psbModel->updateStatusPendaftar($id, $status, $catatan, $verified_by)) {
            // Kirim notifikasi WA berdasarkan status
            // Gunakan no_wa dari akun pendaftaran (bukan dari formulir siswa)
            $noWa = $pendaftar['akun_no_wa'] ?? null;
            $waResult = null;
            $waError = null;

            // Debug log
            error_log("PSB WA Notification - Pendaftar ID: $id, Status: $status, No WA: " . ($noWa ?: 'KOSONG'));

            if ($pendaftar && !empty($noWa)) {
                try {
                    require_once APPROOT . '/app/core/Fonnte.php';
                    $fonnte = new Fonnte();
                    $nama = $pendaftar['nama_lengkap'] ?? ($pendaftar['akun_nama'] ?? 'Calon Siswa');
                    $lembaga = $pendaftar['nama_lembaga'] ?? '';

                    switch ($status) {
                        case 'verifikasi':
                            $message = "✅ *Dokumen Terverifikasi*\n\n";
                            $message .= "Halo *{$nama}*,\n\n";
                            $message .= "Dokumen pendaftaran Anda telah berhasil diverifikasi.\n\n";
                            $message .= "Silakan tunggu pengumuman selanjutnya.\n\n";
                            $message .= "Terima kasih.";
                            $waResult = $fonnte->send($noWa, $message);
                            break;

                        case 'revisi':
                            $waResult = $fonnte->sendRevisiDokumen($noWa, $nama, $catatan ?: 'Mohon periksa kembali dokumen Anda');
                            break;

                        case 'diterima':
                            $waResult = $fonnte->sendDiterima($noWa, $nama, $lembaga);
                            break;

                        case 'ditolak':
                            $waResult = $fonnte->sendDitolak($noWa, $nama, $catatan);
                            break;
                    }

                    // Log hasil kirim WA
                    error_log("PSB WA Result: " . json_encode($waResult));

                } catch (Exception $e) {
                    $waError = $e->getMessage();
                    error_log("PSB WA Exception: " . $waError);
                }
            } else {
                error_log("PSB WA Skip - No WA number for pendaftar ID: $id");
            }

            // Flash message dengan info WA
            if ($waResult && ($waResult['status'] ?? false)) {
                Flasher::setFlash('Status berhasil diupdate & notifikasi WA terkirim', 'success');
            } elseif ($waError) {
                Flasher::setFlash('Status diupdate, tapi WA error: ' . $waError, 'warning');
            } elseif (empty($noWa)) {
                Flasher::setFlash('Status diupdate, tapi nomor WA akun tidak ditemukan', 'warning');
            } else {
                Flasher::setFlash('Status diupdate. WA: ' . ($waResult['reason'] ?? 'tidak diketahui'), 'warning');
            }
        } else {
            Flasher::setFlash('Gagal mengupdate status', 'error');
        }

        header('Location: ' . BASEURL . '/psb/detailPendaftar/' . $id);
        exit;
    }

    /**
     * Konversi pendaftar ke siswa
     */
    public function konversiSiswa($id)
    {
        $this->requireAdmin();

        $result = $this->psbModel->konversiKeDataSiswa($id);

        if ($result['success']) {
            Flasher::setFlash('Pendaftar berhasil dikonversi menjadi siswa. Username: ' . $result['username'] . ', Password: ' . $result['password'], 'success');
        } else {
            Flasher::setFlash('Gagal konversi: ' . $result['error'], 'error');
        }

        header('Location: ' . BASEURL . '/psb/detailPendaftar/' . $id);
        exit;
    }

    // =========================================================================
    // PENGATURAN
    // =========================================================================

    /**
     * Halaman pengaturan PSB
     */
    public function pengaturan()
    {
        $this->requireAdmin();

        $data = [
            'judul' => 'Pengaturan PSB',
            'sidebar_data' => $this->getSidebarData(),
            'pengaturan' => $this->psbModel->getPengaturan()
        ];

        $this->view('admin/psb/pengaturan', $data);
    }

    /**
     * Simpan pengaturan PSB
     */
    public function simpanPengaturan()
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            header('Location: ' . BASEURL . '/psb/pengaturan');
            exit;
        }

        // Get current settings for brosur_gambar
        $currentSettings = $this->psbModel->getPengaturan();
        $brosurGambar = $currentSettings['brosur_gambar'] ?? null;

        // Handle brochure upload
        if (isset($_FILES['brosur_gambar']) && $_FILES['brosur_gambar']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = APPROOT . '/../public/uploads/psb/brosur/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
            $fileType = $_FILES['brosur_gambar']['type'];

            if (in_array($fileType, $allowedTypes)) {
                $ext = pathinfo($_FILES['brosur_gambar']['name'], PATHINFO_EXTENSION);
                $newName = 'brosur_' . time() . '.' . $ext;
                $targetPath = $uploadDir . $newName;

                if (move_uploaded_file($_FILES['brosur_gambar']['tmp_name'], $targetPath)) {
                    // Delete old brochure if exists
                    if ($brosurGambar && file_exists($uploadDir . $brosurGambar)) {
                        unlink($uploadDir . $brosurGambar);
                    }
                    $brosurGambar = $newName;
                }
            }
        }

        // Handle brochure deletion
        if (isset($_POST['hapus_brosur']) && $_POST['hapus_brosur'] == '1') {
            $uploadDir = APPROOT . '/../public/uploads/psb/brosur/';
            if ($brosurGambar && file_exists($uploadDir . $brosurGambar)) {
                unlink($uploadDir . $brosurGambar);
            }
            $brosurGambar = null;
        }

        $data = [
            'judul_halaman' => trim($_POST['judul_halaman'] ?? ''),
            'deskripsi' => trim($_POST['deskripsi'] ?? ''),
            'syarat_pendaftaran' => trim($_POST['syarat_pendaftaran'] ?? ''),
            'alur_pendaftaran' => trim($_POST['alur_pendaftaran'] ?? ''),
            'kontak_info' => trim($_POST['kontak_info'] ?? ''),
            'wa_gateway_url' => trim($_POST['wa_gateway_url'] ?? 'https://api.fonnte.com/send'),
            'wa_gateway_token' => trim($_POST['wa_gateway_token'] ?? ''),
            'brosur_gambar' => $brosurGambar,
            'tentang_sekolah' => trim($_POST['tentang_sekolah'] ?? ''),
            'keunggulan' => trim($_POST['keunggulan'] ?? ''),
            'visi_misi' => trim($_POST['visi_misi'] ?? '')
        ];

        if ($this->psbModel->updatePengaturan($data)) {
            Flasher::setFlash('Pengaturan berhasil disimpan', 'success');
        } else {
            Flasher::setFlash('Gagal menyimpan pengaturan', 'error');
        }

        header('Location: ' . BASEURL . '/psb/pengaturan');
        exit;
    }
}
