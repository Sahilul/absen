<?php

// File: app/controllers/AuthController.php - SIMPLE & WORKING VERSION
class AuthController extends Controller
{

    public function index()
    {
        $data['judul'] = 'Login - Aplikasi Absensi';
        $data['daftar_semester'] = $this->model('TahunPelajaran_model')->getAllSemester();

        // Ambil pengaturan aplikasi
        $data['pengaturan_app'] = $this->model('PengaturanAplikasi_model')->getPengaturan();

        $this->view('auth/login', $data);
    }

    public function prosesLogin()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // INPUT VALIDATION & SANITIZATION
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            $id_semester = filter_var($_POST['id_semester'] ?? 0, FILTER_VALIDATE_INT);

            // Validasi input tidak kosong
            if (empty($username) || empty($password) || !$id_semester) {
                Flasher::setFlash('Username, password, dan semester harus diisi.', 'danger');
                header('Location: ' . BASEURL . '/auth/login');
                exit;
            }

            // Validasi panjang untuk mencegah abuse
            if (strlen($username) > 50 || strlen($password) > 100) {
                Flasher::setFlash('Input tidak valid.', 'danger');
                header('Location: ' . BASEURL . '/auth/login');
                exit;
            }

            $userModel = $this->model('User_model');
            $user = $userModel->getUserByUsername($username);

            if ($user && password_verify($password, $user['password'])) {

                // SECURITY: Regenerate session ID untuk mencegah session fixation
                session_regenerate_id(true);

                // === PENTING: Set semester session dulu ===
                $tpModel = $this->model('TahunPelajaran_model');
                $allSemester = $tpModel->getAllSemester();
                foreach ($allSemester as $smt) {
                    if ($smt['id_semester'] == $id_semester) {
                        $_SESSION['id_semester_aktif'] = (int) $smt['id_semester'];
                        $_SESSION['nama_semester_aktif'] = htmlspecialchars($smt['nama_tp'], ENT_QUOTES, 'UTF-8') . ' - ' . htmlspecialchars($smt['semester'], ENT_QUOTES, 'UTF-8');
                        $_SESSION['id_tp_aktif'] = (int) $smt['id_tp'];
                        break;
                    }
                }

                // === PENTING: Set user session data ===
                $_SESSION['user_id'] = (int) $user['id_user'];
                $_SESSION['nama_lengkap'] = htmlspecialchars($user['nama_lengkap'], ENT_QUOTES, 'UTF-8');
                $_SESSION['user_nama_lengkap'] = htmlspecialchars($user['nama_lengkap'], ENT_QUOTES, 'UTF-8'); // Backward compatibility

                // === CRITICAL FIX: Set BOTH role variables untuk kompatibilitas ===
                $_SESSION['role'] = $user['role'];           // Untuk Admin, Guru, Siswa
                $_SESSION['user_role'] = $user['role'];      // Untuk KepalaMadrasah

                $_SESSION['id_ref'] = (int) $user['id_ref'];

                // === Debug log ===
                error_log("Login SUCCESS: " . $user['username'] . " | Role: " . $user['role'] . " | ID: " . $user['id_user']);
                error_log("Session role: " . $_SESSION['role'] . " | Session user_role: " . $_SESSION['user_role']);

                // Redirect berdasarkan role
                switch ($user['role']) {
                    case 'admin':
                        header('Location: ' . BASEURL . '/admin/dashboard');
                        exit;
                    case 'guru':
                        header('Location: ' . BASEURL . '/guru/dashboard');
                        exit;
                    case 'siswa':
                        header('Location: ' . BASEURL . '/siswa/dashboard');
                        exit;
                    case 'kepala_madrasah':
                        header('Location: ' . BASEURL . '/KepalaMadrasah/dashboard');
                        exit;
                    case 'wali_kelas':
                        header('Location: ' . BASEURL . '/waliKelas/dashboard');
                        exit;
                    default:
                        error_log("Unknown role: " . $user['role']);
                        Flasher::setFlash('Role tidak dikenal: ' . $user['role'], 'danger');
                        header('Location: ' . BASEURL . '/auth/login');
                        exit;
                }
            } else {
                error_log("Login FAILED: " . $username . " | Password verification failed");
                Flasher::setFlash('Username atau password salah.', 'danger');
                header('Location: ' . BASEURL . '/auth/login');
                exit;
            }
        }
    }

    public function logout()
    {
        // Session sudah di-start di index.php, tidak perlu start lagi
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_unset();
            session_destroy();

            // Hapus session cookie
            if (isset($_COOKIE['ABSEN_SESSION'])) {
                setcookie('ABSEN_SESSION', '', time() - 3600, '/');
            }
        }
        header('Location: ' . BASEURL . '/');
        exit;
    }

    // =================================================================
    // GOOGLE OAUTH 2.0 LOGIN
    // =================================================================

    /**
     * Redirect ke Google OAuth consent screen
     */
    public function googleLogin()
    {
        if (!defined('GOOGLE_OAUTH_ENABLED') || !GOOGLE_OAUTH_ENABLED) {
            Flasher::setFlash('Login Google tidak diaktifkan.', 'danger');
            header('Location: ' . BASEURL . '/auth/login');
            exit;
        }

        // Simpan semester yang dipilih (jika ada) ke session untuk digunakan setelah callback
        if (isset($_GET['semester'])) {
            $_SESSION['oauth_semester'] = (int) $_GET['semester'];
        }

        // Generate state token untuk CSRF protection
        $state = bin2hex(random_bytes(16));
        $_SESSION['oauth_state'] = $state;

        // Build Google OAuth URL
        $params = [
            'client_id' => GOOGLE_CLIENT_ID,
            'redirect_uri' => GOOGLE_REDIRECT_URI,
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'state' => $state,
            'access_type' => 'online',
            'prompt' => 'select_account' // Selalu tampilkan pilihan akun
        ];

        // Jika ada domain restriction, tambahkan hd parameter
        if (defined('GOOGLE_ALLOWED_DOMAIN') && !empty(GOOGLE_ALLOWED_DOMAIN)) {
            $params['hd'] = GOOGLE_ALLOWED_DOMAIN;
        }

        $authUrl = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
        header('Location: ' . $authUrl);
        exit;
    }

    /**
     * Handle callback dari Google OAuth
     */
    public function googleCallback()
    {
        if (!defined('GOOGLE_OAUTH_ENABLED') || !GOOGLE_OAUTH_ENABLED) {
            Flasher::setFlash('Login Google tidak diaktifkan.', 'danger');
            header('Location: ' . BASEURL . '/auth/login');
            exit;
        }

        // Validasi state token (CSRF protection)
        $state = $_GET['state'] ?? '';
        if (empty($state) || $state !== ($_SESSION['oauth_state'] ?? '')) {
            Flasher::setFlash('Invalid state token. Silakan coba lagi.', 'danger');
            unset($_SESSION['oauth_state']);
            header('Location: ' . BASEURL . '/auth/login');
            exit;
        }
        unset($_SESSION['oauth_state']);

        // Cek error dari Google
        if (isset($_GET['error'])) {
            $error = $_GET['error'];
            Flasher::setFlash('Login Google dibatalkan: ' . htmlspecialchars($error), 'danger');
            header('Location: ' . BASEURL . '/auth/login');
            exit;
        }

        // Ambil authorization code
        $code = $_GET['code'] ?? '';
        if (empty($code)) {
            Flasher::setFlash('Authorization code tidak ditemukan.', 'danger');
            header('Location: ' . BASEURL . '/auth/login');
            exit;
        }

        // Exchange code untuk access token
        $tokenData = $this->getGoogleAccessToken($code);
        if (!$tokenData || !isset($tokenData['access_token'])) {
            Flasher::setFlash('Gagal mendapatkan access token dari Google.', 'danger');
            header('Location: ' . BASEURL . '/auth/login');
            exit;
        }

        // Ambil user info dari Google
        $userInfo = $this->getGoogleUserInfo($tokenData['access_token']);
        if (!$userInfo || empty($userInfo['email'])) {
            Flasher::setFlash('Gagal mendapatkan informasi user dari Google.', 'danger');
            header('Location: ' . BASEURL . '/auth/login');
            exit;
        }

        // Validasi domain (jika diset)
        if (defined('GOOGLE_ALLOWED_DOMAIN') && !empty(GOOGLE_ALLOWED_DOMAIN)) {
            $emailDomain = substr(strrchr($userInfo['email'], "@"), 1);
            if (strcasecmp($emailDomain, GOOGLE_ALLOWED_DOMAIN) !== 0) {
                Flasher::setFlash('Email harus menggunakan domain @' . GOOGLE_ALLOWED_DOMAIN, 'danger');
                header('Location: ' . BASEURL . '/auth/login');
                exit;
            }
        }

        // Cari user berdasarkan email
        $userModel = $this->model('User_model');
        $user = $userModel->getUserByEmail($userInfo['email']);

        if (!$user) {
            Flasher::setFlash('Email ' . htmlspecialchars($userInfo['email']) . ' tidak terdaftar di sistem.', 'danger');
            header('Location: ' . BASEURL . '/auth/login');
            exit;
        }

        // Login berhasil - set session
        session_regenerate_id(true);

        // Set semester - WAJIB gunakan semester yang dipilih user
        $id_semester = $_SESSION['oauth_semester'] ?? null;
        unset($_SESSION['oauth_semester']);

        // Jika tidak ada semester yang dipilih, redirect kembali ke login
        if (!$id_semester) {
            Flasher::setFlash('Silakan pilih semester terlebih dahulu.', 'warning');
            header('Location: ' . BASEURL . '/auth/login');
            exit;
        }

        if ($id_semester) {
            $tpModel = $this->model('TahunPelajaran_model');
            $allSemester = $tpModel->getAllSemester();
            foreach ($allSemester as $smt) {
                if ($smt['id_semester'] == $id_semester) {
                    $_SESSION['id_semester_aktif'] = (int) $smt['id_semester'];
                    $_SESSION['nama_semester_aktif'] = htmlspecialchars($smt['nama_tp'], ENT_QUOTES, 'UTF-8') . ' - ' . htmlspecialchars($smt['semester'], ENT_QUOTES, 'UTF-8');
                    $_SESSION['id_tp_aktif'] = (int) $smt['id_tp'];
                    break;
                }
            }
        }

        // Set user session data
        $_SESSION['user_id'] = (int) $user['id_user'];
        $_SESSION['nama_lengkap'] = htmlspecialchars($user['nama_lengkap'], ENT_QUOTES, 'UTF-8');
        $_SESSION['user_nama_lengkap'] = htmlspecialchars($user['nama_lengkap'], ENT_QUOTES, 'UTF-8');
        $_SESSION['role'] = $user['role'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['id_ref'] = (int) $user['id_ref'];
        $_SESSION['oauth_login'] = true; // Flag untuk tracking login via OAuth

        error_log("OAuth Login SUCCESS: " . $user['username'] . " | Email: " . $userInfo['email'] . " | Role: " . $user['role']);

        // Redirect berdasarkan role
        switch ($user['role']) {
            case 'admin':
                header('Location: ' . BASEURL . '/admin/dashboard');
                exit;
            case 'guru':
                header('Location: ' . BASEURL . '/guru/dashboard');
                exit;
            case 'siswa':
                header('Location: ' . BASEURL . '/siswa/dashboard');
                exit;
            case 'kepala_madrasah':
                header('Location: ' . BASEURL . '/KepalaMadrasah/dashboard');
                exit;
            case 'wali_kelas':
                header('Location: ' . BASEURL . '/waliKelas/dashboard');
                exit;
            default:
                Flasher::setFlash('Role tidak dikenal.', 'danger');
                header('Location: ' . BASEURL . '/auth/login');
                exit;
        }
    }

    /**
     * Exchange authorization code untuk access token
     */
    private function getGoogleAccessToken($code)
    {
        $tokenUrl = 'https://oauth2.googleapis.com/token';

        $postData = [
            'code' => $code,
            'client_id' => GOOGLE_CLIENT_ID,
            'client_secret' => GOOGLE_CLIENT_SECRET,
            'redirect_uri' => GOOGLE_REDIRECT_URI,
            'grant_type' => 'authorization_code'
        ];

        $ch = curl_init($tokenUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            error_log("Google token exchange failed: HTTP $httpCode | Response: $response");
            return null;
        }

        return json_decode($response, true);
    }

    /**
     * Ambil user info dari Google menggunakan access token
     */
    private function getGoogleUserInfo($accessToken)
    {
        $userInfoUrl = 'https://www.googleapis.com/oauth2/v2/userinfo';

        $ch = curl_init($userInfoUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            error_log("Google userinfo failed: HTTP $httpCode | Response: $response");
            return null;
        }

        return json_decode($response, true);
    }

    // =================================================================
    // DEBUG METHOD (hapus di production)
    // =================================================================

    public function debugSession()
    {
        echo "<h2>DEBUG SESSION DATA</h2>";
        echo "<pre>";
        print_r($_SESSION);
        echo "</pre>";

        echo "<h3>Expected by Controllers:</h3>";
        echo "<ul>";
        echo "<li><strong>AdminController:</strong> \$_SESSION['role'] != 'admin'</li>";
        echo "<li><strong>GuruController:</strong> \$_SESSION['role'] !== 'guru'</li>";
        echo "<li><strong>SiswaController:</strong> \$_SESSION['role'] != 'siswa'</li>";
        echo "<li><strong>KepalaMadrasahController:</strong> \$_SESSION['user_role'] !== 'kepala_madrasah'</li>";
        echo "</ul>";

        if (isset($_SESSION['role'])) {
            echo "<p><strong>Current \$_SESSION['role']:</strong> " . $_SESSION['role'] . "</p>";
        } else {
            echo "<p style='color:red;'><strong>\$_SESSION['role'] NOT SET!</strong></p>";
        }

        if (isset($_SESSION['user_role'])) {
            echo "<p><strong>Current \$_SESSION['user_role']:</strong> " . $_SESSION['user_role'] . "</p>";
        } else {
            echo "<p style='color:red;'><strong>\$_SESSION['user_role'] NOT SET!</strong></p>";
        }

        echo "<p><a href='" . BASEURL . "/auth/login'>Back to Login</a></p>";
        echo "<p><a href='" . BASEURL . "/auth/logout'>Logout</a></p>";
    }
}