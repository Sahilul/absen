<?php
// File: app/controllers/AdminController.php - ALL SQL QUERIES FIXED
class AdminController extends Controller
{
    private $data = [];
    public function __construct()
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
            header('Location: ' . BASEURL . '/auth/login');
            exit;
        }

        // Cache daftar semester di session (refresh setiap 1 jam)
        $cacheKey = 'admin_daftar_semester';
        $cacheTime = $_SESSION[$cacheKey . '_time'] ?? 0;

        if (!isset($_SESSION[$cacheKey]) || (time() - $cacheTime) > 3600) {
            $this->data['daftar_semester'] = $this->model('TahunPelajaran_model')->getAllSemester();
            $_SESSION[$cacheKey] = $this->data['daftar_semester'];
            $_SESSION[$cacheKey . '_time'] = time();
        } else {
            $this->data['daftar_semester'] = $_SESSION[$cacheKey];
        }

        // Set default semester jika belum ada
        if (!isset($_SESSION['id_semester_aktif']) && !empty($this->data['daftar_semester'])) {
            $defaultSemester = $this->data['daftar_semester'][0];
            $_SESSION['id_semester_aktif'] = $defaultSemester['id_semester'];
            $_SESSION['nama_semester_aktif'] = $defaultSemester['nama_tp'] . ' - ' . $defaultSemester['semester'];
            $_SESSION['id_tp_aktif'] = $defaultSemester['id_tp'];
        }
    }
    // =================================================================
    // TAMBAHAN METHOD INDEX UNTUK ROUTING
    // =================================================================
    public function index()
    {
        error_log("AdminController::index() dipanggil - URL: " . ($_SERVER['REQUEST_URI'] ?? ''));
        header('Location: ' . BASEURL . '/admin/dashboard');
        exit;
    }

    /**
     * Set semester aktif via dropdown header
     */
    public function setSemester()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id_semester = filter_input(INPUT_POST, 'id_semester', FILTER_VALIDATE_INT);

            if ($id_semester) {
                $tpModel = $this->model('TahunPelajaran_model');
                $semester = $tpModel->getSemesterById($id_semester);

                if ($semester) {
                    $_SESSION['id_semester_aktif'] = (int) $semester['id_semester'];
                    // Format: 2023/2024 - Ganjil
                    $_SESSION['nama_semester_aktif'] = htmlspecialchars($semester['nama_tp']) . ' - ' . htmlspecialchars($semester['semester']);
                    $_SESSION['id_tp_aktif'] = (int) $semester['id_tp'];

                    Flasher::setFlash('Semester aktif berhasil diubah ke ' . $_SESSION['nama_semester_aktif'], 'success');
                } else {
                    Flasher::setFlash('Semester tidak ditemukan', 'error');
                }
            }
        }

        // Redirect back to previous page or dashboard
        $referer = $_SERVER['HTTP_REFERER'] ?? BASEURL . '/admin/dashboard';
        header('Location: ' . $referer);
        exit;
    }

    // =================================================================
    // DASHBOARD - ONLY ONE VERSION
    // =================================================================
    public function dashboard()
    {
        $this->data['judul'] = 'Dashboard Admin';
        $this->data['load_chartjs'] = true; // Flag untuk load Chart.js

        // Cache stats dashboard (refresh setiap 5 menit)
        $cacheKey = 'admin_dashboard_stats';
        $cacheTime = $_SESSION[$cacheKey . '_time'] ?? 0;

        if (!isset($_SESSION[$cacheKey]) || (time() - $cacheTime) > 300) {
            // Ambil data real dari database
            $this->data['jumlah_guru'] = $this->model('Guru_model')->getJumlahGuru();
            $this->data['jumlah_siswa'] = $this->model('Siswa_model')->getJumlahSiswa();
            $this->data['jumlah_kelas'] = $this->model('Kelas_model')->getJumlahKelas();
            $this->data['stats'] = $this->getDashboardStats();

            // Simpan ke session cache
            $_SESSION[$cacheKey] = [
                'jumlah_guru' => $this->data['jumlah_guru'],
                'jumlah_siswa' => $this->data['jumlah_siswa'],
                'jumlah_kelas' => $this->data['jumlah_kelas'],
                'stats' => $this->data['stats']
            ];
            $_SESSION[$cacheKey . '_time'] = time();
        } else {
            // Load dari cache
            $cache = $_SESSION[$cacheKey];
            $this->data['jumlah_guru'] = $cache['jumlah_guru'];
            $this->data['jumlah_siswa'] = $cache['jumlah_siswa'];
            $this->data['jumlah_kelas'] = $cache['jumlah_kelas'];
            $this->data['stats'] = $cache['stats'];
        }

        // Data yang harus realtime (tidak di-cache)
        $this->data['recent_journals'] = $this->getRecentJournals();
        $this->data['attendance_today'] = $this->getAttendanceToday();
        $this->data['attendance_trend'] = $this->getAttendanceTrend();
        $this->data['alerts'] = $this->getSystemAlerts();

        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/dashboard', $this->data);
        $this->view('templates/footer', $this->data);
    }
    // =================================================================
    // HELPER METHODS UNTUK DASHBOARD - FIXED SQL
    // =================================================================
    private function getDashboardStats()
    {
        $db = new Database();
        try {
            // Total Guru
            $db->query('SELECT COUNT(*) as total FROM guru');
            $total_guru = $db->single()['total'];
            // Total Siswa Aktif
            $db->query('SELECT COUNT(*) as total FROM siswa WHERE status_siswa = "aktif"');
            $total_siswa_aktif = $db->single()['total'];
            // Total Kelas
            $id_tp_aktif = $_SESSION['id_tp_aktif'] ?? 0;
            $db->query('SELECT COUNT(*) as total FROM kelas WHERE id_tp = :id_tp');
            $db->bind('id_tp', $id_tp_aktif);
            $total_kelas = $db->single()['total'];
            // Jurnal Hari Ini
            $db->query('SELECT COUNT(*) as total FROM jurnal WHERE DATE(tanggal) = CURDATE()');
            $jurnal_hari_ini = $db->single()['total'];
            // Kehadiran Hari Ini
            $attendance_today = $this->calculateAttendanceToday();
            return [
                'total_guru' => $total_guru,
                'total_siswa_aktif' => $total_siswa_aktif,
                'total_kelas' => $total_kelas,
                'jurnal_hari_ini' => $jurnal_hari_ini,
                'kehadiran_hari_ini' => $attendance_today
            ];
        } catch (Exception $e) {
            error_log("Error in getDashboardStats: " . $e->getMessage());
            return [
                'total_guru' => 0,
                'total_siswa_aktif' => 0,
                'total_kelas' => 0,
                'jurnal_hari_ini' => 0,
                'kehadiran_hari_ini' => ['percentage' => 0, 'hadir' => 0, 'total' => 0]
            ];
        }
    }
    private function calculateAttendanceToday()
    {
        $db = new Database();
        try {
            $db->query('SELECT COUNT(*) as total FROM absensi a 
                       JOIN jurnal j ON a.id_jurnal = j.id_jurnal 
                       WHERE DATE(j.tanggal) = CURDATE()');
            $total_absensi = $db->single()['total'];
            if ($total_absensi == 0) {
                return ['percentage' => 0, 'hadir' => 0, 'total' => 0];
            }
            $db->query('SELECT COUNT(*) as hadir FROM absensi a 
                       JOIN jurnal j ON a.id_jurnal = j.id_jurnal 
                       WHERE DATE(j.tanggal) = CURDATE() AND a.status_kehadiran = "H"');
            $total_hadir = $db->single()['hadir'];
            $percentage = round(($total_hadir / $total_absensi) * 100, 1);
            return [
                'percentage' => $percentage,
                'hadir' => $total_hadir,
                'total' => $total_absensi
            ];
        } catch (Exception $e) {
            error_log("Error in calculateAttendanceToday: " . $e->getMessage());
            return ['percentage' => 0, 'hadir' => 0, 'total' => 0];
        }
    }
    private function getRecentJournals()
    {
        $db = new Database();
        try {
            $db->query('SELECT j.id_jurnal, j.tanggal, j.topik_materi, j.timestamp,
                               g.nama_guru, m.nama_mapel, k.nama_kelas
                       FROM jurnal j
                       JOIN penugasan p ON j.id_penugasan = p.id_penugasan
                       JOIN guru g ON p.id_guru = g.id_guru
                       JOIN mapel m ON p.id_mapel = m.id_mapel
                       JOIN kelas k ON p.id_kelas = k.id_kelas
                       ORDER BY j.timestamp DESC
                       LIMIT 10');
            return $db->resultSet();
        } catch (Exception $e) {
            error_log("Error in getRecentJournals: " . $e->getMessage());
            return [];
        }
    }
    private function getAttendanceToday()
    {
        $db = new Database();
        try {
            $db->query('SELECT 
                          SUM(CASE WHEN a.status_kehadiran = "H" THEN 1 ELSE 0 END) as hadir,
                          SUM(CASE WHEN a.status_kehadiran = "I" THEN 1 ELSE 0 END) as izin,
                          SUM(CASE WHEN a.status_kehadiran = "S" THEN 1 ELSE 0 END) as sakit,
                          SUM(CASE WHEN a.status_kehadiran = "A" THEN 1 ELSE 0 END) as alfa,
                          COUNT(*) as total
                        FROM absensi a
                        JOIN jurnal j ON a.id_jurnal = j.id_jurnal
                        WHERE DATE(j.tanggal) = CURDATE()');
            $result = $db->single();
            return $result ?: ['hadir' => 0, 'izin' => 0, 'sakit' => 0, 'alfa' => 0, 'total' => 0];
        } catch (Exception $e) {
            error_log("Error in getAttendanceToday: " . $e->getMessage());
            return ['hadir' => 0, 'izin' => 0, 'sakit' => 0, 'alfa' => 0, 'total' => 0];
        }
    }
    private function getAttendanceTrend()
    {
        $db = new Database();
        try {
            $db->query('SELECT 
                          DATE(j.tanggal) as tanggal,
                          COUNT(a.id_absensi) as total_absensi,
                          SUM(CASE WHEN a.status_kehadiran = "H" THEN 1 ELSE 0 END) as hadir,
                          ROUND(
                            CASE 
                              WHEN COUNT(a.id_absensi) > 0 
                              THEN (SUM(CASE WHEN a.status_kehadiran = "H" THEN 1 ELSE 0 END) / COUNT(a.id_absensi)) * 100
                              ELSE 0 
                            END, 1
                          ) as persentase
                        FROM jurnal j
                        LEFT JOIN absensi a ON j.id_jurnal = a.id_jurnal
                        WHERE j.tanggal >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                        GROUP BY DATE(j.tanggal)
                        ORDER BY DATE(j.tanggal) ASC');
            return $db->resultSet();
        } catch (Exception $e) {
            error_log("Error in getAttendanceTrend: " . $e->getMessage());
            return [];
        }
    }
    private function getSystemAlerts()
    {
        $alerts = [];
        try {
            $db = new Database();
            // Cek guru yang mengajar di semester aktif
            $db->query('SELECT COUNT(DISTINCT id_guru) as total_guru_mengajar
                       FROM penugasan 
                       WHERE id_semester = :id_semester');
            $db->bind('id_semester', $_SESSION['id_semester_aktif'] ?? 0);
            $total_guru_mengajar = $db->single()['total_guru_mengajar'] ?? 0;
            // Guru yang sudah input jurnal hari ini
            $db->query('SELECT COUNT(DISTINCT p.id_guru) as guru_sudah_jurnal
                       FROM penugasan p
                       JOIN jurnal j ON p.id_penugasan = j.id_penugasan
                       WHERE p.id_semester = :id_semester 
                       AND DATE(j.tanggal) = CURDATE()');
            $db->bind('id_semester', $_SESSION['id_semester_aktif'] ?? 0);
            $guru_sudah_jurnal = $db->single()['guru_sudah_jurnal'] ?? 0;
            $guru_belum_jurnal = $total_guru_mengajar - $guru_sudah_jurnal;
            if ($guru_belum_jurnal > 0) {
                $alerts[] = [
                    'type' => 'warning',
                    'title' => 'Jurnal Belum Lengkap',
                    'message' => "$guru_belum_jurnal guru belum input jurnal hari ini",
                    'icon' => 'alert-circle'
                ];
            }
            // Info tahun pelajaran aktif
            $db->query('SELECT nama_tp FROM tp WHERE id_tp = :id_tp');
            $db->bind('id_tp', $_SESSION['id_tp_aktif'] ?? 0);
            $tp_info = $db->single();
            if ($tp_info) {
                $alerts[] = [
                    'type' => 'info',
                    'title' => 'Tahun Pelajaran Aktif',
                    'message' => "Sesi: " . ($tp_info['nama_tp'] ?? ''),
                    'icon' => 'info'
                ];
            }
        } catch (Exception $e) {
            error_log("Error in getSystemAlerts: " . $e->getMessage());
            $alerts[] = [
                'type' => 'error',
                'title' => 'System Error',
                'message' => 'Terjadi kesalahan dalam mengambil data sistem',
                'icon' => 'alert-triangle'
            ];
        }
        return $alerts;
    }
    // =================================================================
    // SIDEBAR DATA - FIXED SQL
    // =================================================================
    public function getSidebarData()
    {
        $db = new Database();
        try {
            $db->query('SELECT COUNT(*) as total FROM guru');
            $total_guru = $db->single()['total'];
            $db->query('SELECT COUNT(*) as total FROM siswa WHERE status_siswa = "aktif"');
            $total_siswa = $db->single()['total'];
            $id_tp_aktif = $_SESSION['id_tp_aktif'] ?? 0;
            $db->query('SELECT COUNT(*) as total FROM kelas WHERE id_tp = :id_tp');
            $db->bind('id_tp', $id_tp_aktif);
            $total_kelas = $db->single()['total'];
            $db->query('SELECT COUNT(*) as total FROM jurnal WHERE DATE(tanggal) = CURDATE()');
            $jurnal_today = $db->single()['total'];
            $attendance = $this->calculateAttendanceToday();
            return [
                'total_guru' => $total_guru,
                'total_siswa' => $total_siswa,
                'total_kelas' => $total_kelas,
                'jurnal_today' => $jurnal_today,
                'guru_belum_jurnal' => 0,
                'attendance_percentage' => $attendance['percentage']
            ];
        } catch (Exception $e) {
            error_log("Error in getSidebarData: " . $e->getMessage());
            return [
                'total_guru' => 0,
                'total_siswa' => 0,
                'total_kelas' => 0,
                'jurnal_today' => 0,
                'guru_belum_jurnal' => 0,
                'attendance_percentage' => 0
            ];
        }
    }
    // =================================================================
    // API ENDPOINTS
    // =================================================================
    public function getStats()
    {
        header('Content-Type: application/json');
        echo json_encode($this->getDashboardStats());
        exit;
    }
    // =================================================================
    // SESI AKTIF
    // =================================================================
    public function setAktifTP()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_semester'])) {
            $allSemester = $this->data['daftar_semester'];
            foreach ($allSemester as $smt) {
                if ($smt['id_semester'] == $_POST['id_semester']) {
                    $_SESSION['id_semester_aktif'] = $smt['id_semester'];
                    $_SESSION['nama_semester_aktif'] = $smt['nama_tp'] . ' - ' . $smt['semester'];
                    $_SESSION['id_tp_aktif'] = $smt['id_tp'];
                    break;
                }
            }
        }
        $previousPage = $_SERVER['HTTP_REFERER'] ?? (BASEURL . '/admin/dashboard');
        header('Location: ' . $previousPage);
        exit;
    }
    // =================================================================
    // CRUD SISWA
    // =================================================================
    public function siswa()
    {
        $this->data['judul'] = 'Manajemen Siswa';
        $this->data['siswa'] = $this->model('Siswa_model')->getAllSiswa();
        $this->data['kelas_list'] = $this->model('Kelas_model')->getAllKelas();

        // Load field configuration
        $pengaturanModel = $this->model('PengaturanAplikasi_model');
        $this->data['fieldConfig'] = $pengaturanModel->getFieldSiswaConfig();

        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/siswa', $this->data);
        $this->view('templates/footer', $this->data);
    }
    public function tambahSiswa()
    {
        $this->data['judul'] = 'Tambah Data Siswa';

        // Load field configuration
        $pengaturanModel = $this->model('PengaturanAplikasi_model');
        $this->data['fieldConfig'] = $pengaturanModel->getFieldSiswaConfig();
        $this->data['mandatoryFields'] = $pengaturanModel->getMandatoryFields();

        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/tambah_siswa', $this->data);
        $this->view('templates/footer', $this->data);
    }
    public function prosesTambahSiswa()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // VALIDASI INPUT
            $nisn = InputValidator::validateNISN($_POST['nisn'] ?? '');
            $nama_siswa = InputValidator::sanitizeNama($_POST['nama_siswa'] ?? '');
            $jenis_kelamin = InputValidator::validateJenisKelamin($_POST['jenis_kelamin'] ?? '');
            $password = trim($_POST['password'] ?? '');

            // Cek input wajib
            if (!$nisn || empty($nama_siswa) || !$jenis_kelamin || empty($password)) {
                Flasher::setFlash('Data tidak lengkap atau tidak valid', 'danger');
                header('Location: ' . BASEURL . '/admin/tambahSiswa');
                exit;
            }

            // Validasi panjang password minimal
            if (strlen($password) < 6) {
                Flasher::setFlash('Password minimal 6 karakter', 'danger');
                header('Location: ' . BASEURL . '/admin/tambahSiswa');
                exit;
            }

            // Sanitize data lainnya
            $dataSiswa = [
                'nisn' => $nisn,
                'nama_siswa' => $nama_siswa,
                'jenis_kelamin' => $jenis_kelamin,
                'tgl_lahir' => InputValidator::validateDate($_POST['tgl_lahir'] ?? '') ? $_POST['tgl_lahir'] : null,
                'tempat_lahir' => trim($_POST['tempat_lahir'] ?? ''),
                'alamat' => trim($_POST['alamat'] ?? ''),
                'no_wa' => trim($_POST['no_wa'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'ayah_kandung' => trim($_POST['ayah_kandung'] ?? ''),
                'ibu_kandung' => trim($_POST['ibu_kandung'] ?? '')
            ];

            $idSiswaBaru = $this->model('Siswa_model')->tambahDataSiswa($dataSiswa);
            if ($idSiswaBaru) {
                $dataAkun = [
                    'username' => $nisn,
                    'password' => $password,
                    'nama_lengkap' => $nama_siswa,
                    'role' => 'siswa',
                    'id_ref' => $idSiswaBaru
                ];
                $this->model('User_model')->buatAkun($dataAkun);
                Flasher::setFlash('Siswa berhasil ditambahkan', 'success');
                header('Location: ' . BASEURL . '/admin/siswa');
                exit;
            } else {
                Flasher::setFlash('Gagal menambahkan siswa', 'danger');
                header('Location: ' . BASEURL . '/admin/tambahSiswa');
                exit;
            }
        }
    }
    public function editSiswa($id)
    {
        $this->data['judul'] = 'Edit Data Siswa';
        $this->data['siswa'] = $this->model('Siswa_model')->getSiswaById($id);

        // Load field configuration
        $pengaturanModel = $this->model('PengaturanAplikasi_model');
        $this->data['fieldConfig'] = $pengaturanModel->getFieldSiswaConfig();

        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/edit_siswa', $this->data);
        $this->view('templates/footer', $this->data);
    }
    public function prosesUpdateSiswa()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Pass all POST data to model (including new fields: tempat_lahir, alamat, no_wa, ayah_kandung, ibu_kandung)
            $this->model('Siswa_model')->updateDataSiswa($_POST);

            // Update password jika diisi (cek field password atau password_baru)
            $password_baru = $_POST['password'] ?? $_POST['password_baru'] ?? '';
            if (!empty($password_baru) && (strlen($password_baru) >= 6)) {
                $this->model('User_model')->updatePassword($_POST['id_siswa'], 'siswa', $password_baru);
            }

            header('Location: ' . BASEURL . '/admin/siswa');
            exit;
        }
    }
    public function hapusSiswa($id)
    {
        // Pastikan session untuk flash tersedia
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        // Penghapusan permanen + relasi (manual cascade tanpa transaksi / FK toggle)
        $db = new Database();
        // Daftar tabel child yang punya kolom id_siswa
        $tablesToCascade = [
            'absensi',
            'nilai_siswa',
            'jurnal',
            'performa_siswa',
            'keanggotaan_kelas',
            'pembayaran_tagihan_siswa' // jika ada pembayaran yang berkait siswa
        ];
        $deletedDetail = [];
        try {
            // Hapus semua relasi terlebih dahulu
            foreach ($tablesToCascade as $t) {
                try {
                    $db->query("DELETE FROM $t WHERE id_siswa = :id");
                    $db->bind('id', $id);
                    $db->execute();
                    $cnt = $db->rowCount();
                    if ($cnt > 0) {
                        $deletedDetail[] = "$t:$cnt";
                    }
                } catch (Exception $childErr) {
                    // Catat error tapi lanjut; bisa ditampilkan nanti jika perlu
                }
            }

            // Verifikasi tidak ada sisa referensi (cek cepat untuk tabel utama yang sering gagal)
            $leftovers = [];
            foreach ($tablesToCascade as $t) {
                try {
                    $db->query("SELECT COUNT(*) AS c FROM $t WHERE id_siswa = :id");
                    $db->bind('id', $id);
                    $cRow = $db->single();
                    $c = isset($cRow['c']) ? (int) $cRow['c'] : 0;
                    if ($c > 0) {
                        $leftovers[] = "$t:$c";
                    }
                } catch (Exception $countErr) {
                }
            }
            if (!empty($leftovers)) {
                Flasher::setFlash('Gagal', 'Tidak dapat hapus siswa. Masih ada data terkait: ' . implode(', ', $leftovers), 'danger');
                header('Location: ' . BASEURL . '/admin/siswa');
                exit;
            }

            // Hapus akun user
            $this->model('User_model')->hapusAkun($id, 'siswa');

            // Hapus siswa
            if ($this->model('Siswa_model')->hapusDataSiswa($id) > 0) {
                $this->clearDashboardCache();
                $extra = empty($deletedDetail) ? '' : ' (menghapus: ' . implode(', ', $deletedDetail) . ')';
                Flasher::setFlash('Berhasil', 'Data siswa berhasil dihapus' . $extra . '.', 'success');
            } else {
                Flasher::setFlash('Gagal', 'Data siswa tidak ditemukan atau gagal dihapus.', 'danger');
            }
        } catch (Exception $e) {
            Flasher::setFlash('Error', 'Terjadi kesalahan: ' . $e->getMessage(), 'danger');
        }
        header('Location: ' . BASEURL . '/admin/siswa');
        exit;
    }

    /**
     * Bulk delete multiple students
     */
    public function bulkHapusSiswa()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Only accept POST requests
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Flasher::setFlash('Error', 'Invalid request method.', 'danger');
            header('Location: ' . BASEURL . '/admin/siswa');
            exit;
        }

        $ids = $_POST['ids'] ?? [];

        if (empty($ids) || !is_array($ids)) {
            Flasher::setFlash('Gagal', 'Tidak ada siswa yang dipilih untuk dihapus.', 'warning');
            header('Location: ' . BASEURL . '/admin/siswa');
            exit;
        }

        // Sanitize IDs
        $ids = array_map('intval', $ids);
        $ids = array_filter($ids, function ($id) {
            return $id > 0;
        });

        if (empty($ids)) {
            Flasher::setFlash('Gagal', 'ID siswa tidak valid.', 'danger');
            header('Location: ' . BASEURL . '/admin/siswa');
            exit;
        }

        $db = new Database();
        $tablesToCascade = [
            'absensi',
            'nilai_siswa',
            'jurnal',
            'performa_siswa',
            'keanggotaan_kelas',
            'pembayaran_tagihan_siswa'
        ];

        $successCount = 0;
        $failedCount = 0;
        $errors = [];

        foreach ($ids as $id) {
            try {
                // Delete related data first
                foreach ($tablesToCascade as $t) {
                    try {
                        $db->query("DELETE FROM $t WHERE id_siswa = :id");
                        $db->bind('id', $id);
                        $db->execute();
                    } catch (Exception $childErr) {
                        // Continue even if child table doesn't exist
                    }
                }

                // Delete user account
                $this->model('User_model')->hapusAkun($id, 'siswa');

                // Delete student
                if ($this->model('Siswa_model')->hapusDataSiswa($id) > 0) {
                    $successCount++;
                } else {
                    $failedCount++;
                    $errors[] = "ID $id tidak ditemukan";
                }
            } catch (Exception $e) {
                $failedCount++;
                $errors[] = "ID $id: " . $e->getMessage();
            }
        }

        // Clear dashboard cache
        if ($successCount > 0) {
            $this->clearDashboardCache();
        }

        // Set flash message
        if ($successCount > 0 && $failedCount === 0) {
            Flasher::setFlash('Berhasil', "$successCount siswa berhasil dihapus.", 'success');
        } elseif ($successCount > 0 && $failedCount > 0) {
            Flasher::setFlash('Sebagian Berhasil', "$successCount siswa dihapus, $failedCount gagal.", 'warning');
        } else {
            $errorDetail = !empty($errors) ? ' (' . implode(', ', array_slice($errors, 0, 3)) . ')' : '';
            Flasher::setFlash('Gagal', "Tidak ada siswa yang berhasil dihapus.$errorDetail", 'danger');
        }

        header('Location: ' . BASEURL . '/admin/siswa');
        exit;
    }

    // =================================================================
    // IMPORT SISWA DARI PSB
    // =================================================================
    public function importPsb()
    {
        $this->data['judul'] = 'Import Siswa dari PSB';
        $this->data['daftar_tp'] = $this->model('TahunPelajaran_model')->getAllTahunPelajaran();

        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/import_psb', $this->data);
        $this->view('templates/footer', $this->data);
    }

    public function getCandidates()
    {
        // Return JSON for AJAX requests
        $id_tp = $_GET['id_tp'] ?? 0;

        if (!$id_tp) {
            header('Content-Type: application/json');
            echo json_encode([]);
            exit;
        }

        $candidates = $this->model('PSB_model')->getCandidatesForImport($id_tp);

        header('Content-Type: application/json');
        echo json_encode($candidates);
        exit;
    }

    public function processImport()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/admin/siswa');
            exit;
        }

        $ids = $_POST['ids'] ?? [];
        if (empty($ids)) {
            Flasher::setFlash('Gagal', 'Tidak ada siswa yang dipilih.', 'warning');
            header('Location: ' . BASEURL . '/admin/importPsb');
            exit;
        }

        $success = 0;
        $failed = 0;
        $errors = [];

        foreach ($ids as $idPendaftar) {
            $result = $this->model('PSB_model')->konversiKeDataSiswa($idPendaftar);
            if ($result['success']) {
                $success++;
            } else {
                $failed++;
                $errors[] = "ID $idPendaftar: " . ($result['error'] ?? 'Unknown error');
            }
        }

        if ($success > 0 && $failed === 0) {
            Flasher::setFlash('Berhasil', "$success siswa berhasil diimport.", 'success');
            header('Location: ' . BASEURL . '/admin/siswa');
            exit;
        } elseif ($success > 0 && $failed > 0) {
            Flasher::setFlash('Sebagian Berhasil', "$success berhasil, $failed gagal.", 'warning');
            header('Location: ' . BASEURL . '/admin/siswa'); // Redirect to list to show results
            exit;
        } else {
            Flasher::setFlash('Gagal', 'Import gagal. ' . implode(', ', array_slice($errors, 0, 3)), 'danger');
            header('Location: ' . BASEURL . '/admin/importPsb'); // Stay on import page if all failed
            exit;
        }
    }
    /**
     * Batalkan import siswa - mengembalikan siswa ke status PSB 'diterima'
     * Menghapus data siswa, user, dokumen, dan keanggotaan kelas
     */
    public function batalkanImport($id_siswa = null)
    {
        if (!$id_siswa) {
            Flasher::setFlash('Gagal', 'ID Siswa tidak valid.', 'danger');
            header('Location: ' . BASEURL . '/admin/siswa');
            exit;
        }

        $db = new Database();

        try {
            // Get siswa data first
            $db->query('SELECT nisn FROM siswa WHERE id_siswa = :id');
            $db->bind(':id', $id_siswa);
            $siswa = $db->single();

            if (!$siswa) {
                Flasher::setFlash('Gagal', 'Siswa tidak ditemukan.', 'danger');
                header('Location: ' . BASEURL . '/admin/siswa');
                exit;
            }

            $nisn = $siswa['nisn'];

            // 1. Hapus dokumen siswa
            $db->query('DELETE FROM siswa_dokumen WHERE id_siswa = :id');
            $db->bind(':id', $id_siswa);
            $db->execute();

            // 2. Hapus keanggotaan kelas
            $db->query('DELETE FROM keanggotaan_kelas WHERE id_siswa = :id');
            $db->bind(':id', $id_siswa);
            $db->execute();

            // 3. Hapus akun user
            $db->query('DELETE FROM users WHERE id_ref = :id AND role = "siswa"');
            $db->bind(':id', $id_siswa);
            $db->execute();

            // 4. Hapus data siswa
            $db->query('DELETE FROM siswa WHERE id_siswa = :id');
            $db->bind(':id', $id_siswa);
            $db->execute();

            // 5. Reset status PSB jika ada
            if ($nisn) {
                $db->query('UPDATE psb_pendaftar SET status = "diterima", id_siswa = NULL WHERE nisn = :nisn');
                $db->bind(':nisn', $nisn);
                $db->execute();
            }

            Flasher::setFlash('Berhasil', 'Import siswa berhasil dibatalkan. Siswa dapat diimport ulang dari PSB.', 'success');

        } catch (Exception $e) {
            Flasher::setFlash('Gagal', 'Error: ' . $e->getMessage(), 'danger');
        }

        header('Location: ' . BASEURL . '/admin/siswa');
        exit;
    }

    // =================================================================
    // DOKUMEN SISWA
    // =================================================================
    public function dokumenSiswa($id_siswa)
    {
        $this->data['judul'] = 'Dokumen Siswa';
        $this->data['siswa'] = $this->model('Siswa_model')->getSiswaById($id_siswa);
        $this->data['dokumen'] = $this->model('Siswa_model')->getDokumenSiswa($id_siswa);
        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/dokumen_siswa', $this->data);
        $this->view('templates/footer', $this->data);
    }

    public function uploadDokumenSiswa($id_siswa_param = null)
    {
        $id_siswa = $id_siswa_param ?? ($_POST['id_siswa'] ?? 0);
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' ||
            strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false;

        $respond = function ($success, $message, $data = []) use ($isAjax, $id_siswa) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(array_merge(['success' => $success, 'message' => $message], $data));
                exit;
            } else {
                Flasher::setFlash($success ? 'Berhasil' : 'Gagal', $message, $success ? 'success' : 'danger');
                header('Location: ' . BASEURL . '/admin/dokumenSiswa/' . $id_siswa);
                exit;
            }
        };

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $respond(false, 'Method tidak diizinkan');
        }

        $jenis = $_POST['jenis_dokumen'] ?? '';
        if (!$id_siswa) {
            $respond(false, 'ID Siswa tidak valid');
        }
        if (empty($_FILES['file_dokumen']['name'])) {
            $respond(false, 'Pilih file untuk diupload');
        }

        $file = $_FILES['file_dokumen'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['pdf', 'jpg', 'jpeg', 'png'];
        if (!in_array($ext, $allowed)) {
            $respond(false, 'Format file tidak diizinkan');
        }

        $newFilename = $id_siswa . '_' . $jenis . '_' . time() . '.' . $ext;

        // STRICT: Wajib Google Drive, tidak ada fallback lokal
        if (!file_exists(APPROOT . '/app/core/GoogleDrive.php')) {
            $respond(false, 'Modul Google Drive tidak tersedia di server');
        }

        try {
            require_once APPROOT . '/app/core/GoogleDrive.php';
            $drive = new GoogleDrive();

            if (!$drive->isConnected()) {
                $respond(false, 'Google Drive belum terhubung. Silakan hubungi Admin untuk menghubungkan akun Google Drive.');
            }

            $siswa = $this->model('Siswa_model')->getSiswaById($id_siswa);
            $namaFolder = ($siswa['nisn'] ?? $id_siswa) . '_' . preg_replace('/\s+/', '_', $siswa['nama_siswa'] ?? 'Siswa');
            $mainFolderId = $drive->getFolderId();
            $siswaFolder = $drive->findOrCreateFolder($namaFolder, $mainFolderId);
            $parentId = $siswaFolder ? $siswaFolder['id'] : $mainFolderId;

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
                $respond(true, 'Dokumen berhasil diupload ke Google Drive', ['path' => $driveUrl]);
            } else {
                $respond(false, 'Google Drive: Gagal mendapatkan ID file setelah upload');
            }
        } catch (Exception $e) {
            error_log("Google Drive upload error: " . $e->getMessage());
            $respond(false, 'Google Drive Error: ' . $e->getMessage());
        }
    }

    public function hapusDokumenSiswa($id_dokumen, $id_siswa)
    {
        $doc = $this->model('Siswa_model')->getDokumenById($id_dokumen);

        if ($doc) {
            // Delete file from disk
            $filePath = APPROOT . '/uploads/siswa_dokumen/' . $doc['path_file'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }

            $this->model('Siswa_model')->deleteDokumenSiswa($id_dokumen);
            Flasher::setFlash('Berhasil', 'Dokumen berhasil dihapus.', 'success');
        } else {
            Flasher::setFlash('Gagal', 'Dokumen tidak ditemukan.', 'danger');
        }

        header('Location: ' . BASEURL . '/admin/dokumenSiswa/' . $id_siswa);
        exit;
    }

    /**
     * Monitoring Dokumen Siswa
     */
    public function monitoringDokumen()
    {
        $id_tp = $_SESSION['id_tp_aktif'] ?? $this->model('TahunPelajaran_model')->getTahunPelajaranAktif()['id_tp'];

        $id_kelas = $_GET['id_kelas'] ?? null;
        $keyword = $_GET['search'] ?? null;

        // Get total required documents for stats calculation
        require_once APPROOT . '/app/models/DokumenConfig_model.php';
        $dokumenConfigModel = new DokumenConfig_model();
        // We use getAllDokumen because we want to see progress against all active document types
        $dokumenList = $dokumenConfigModel->getAllDokumen();
        $totalDokumen = count($dokumenList);

        $this->data['judul'] = 'Monitoring Dokumen Siswa';
        $this->data['siswa'] = $this->model('Siswa_model')->getSiswaWithDocumentStatus($id_tp, $id_kelas, $keyword);
        $this->data['kelas_list'] = $this->model('Kelas_model')->getAllKelas();
        $this->data['filters'] = [
            'id_kelas' => $id_kelas,
            'search' => $keyword
        ];
        $this->data['total_dokumen'] = $totalDokumen;

        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/monitoring_dokumen', $this->data);
        $this->view('templates/footer', $this->data);
    }

    /**
     * Cetak Rekap Dokumen Siswa (PDF)
     */
    public function cetakRekapDokumen()
    {
        $id_tp = $_SESSION['id_tp_aktif'] ?? $this->model('TahunPelajaran_model')->getTahunPelajaranAktif()['id_tp'];

        $id_kelas = $_GET['id_kelas'] ?? null;
        $keyword = $_GET['search'] ?? null;

        // Get total required documents
        require_once APPROOT . '/app/models/DokumenConfig_model.php';
        $dokumenConfigModel = new DokumenConfig_model();
        $dokumenList = $dokumenConfigModel->getAllDokumen();
        $totalDokumen = count($dokumenList);

        // Get pengaturan aplikasi
        $pengaturan = $this->model('PengaturanAplikasi_model')->getPengaturan();

        $siswaList = $this->model('Siswa_model')->getSiswaWithDocumentStatus($id_tp, $id_kelas, $keyword);

        // Get detailed document data for each student
        foreach ($siswaList as &$siswa) {
            $uploadedDocs = $this->model('Siswa_model')->getDokumenSiswa($siswa['id_siswa']);
            $siswa['dokumen_detail'] = [];

            // Create a map of uploaded documents by jenis
            $uploadedMap = [];
            foreach ($uploadedDocs as $doc) {
                $uploadedMap[$doc['jenis_dokumen']] = true;
            }

            // Check each required document
            foreach ($dokumenList as $dokConfig) {
                $siswa['dokumen_detail'][] = [
                    'kode' => $dokConfig['kode'],
                    'nama' => $dokConfig['nama'],
                    'uploaded' => isset($uploadedMap[$dokConfig['kode']])
                ];
            }
        }

        $this->data['siswa'] = $siswaList;
        $this->data['dokumen_list'] = $dokumenList;
        $this->data['total_dokumen'] = $totalDokumen;
        $this->data['pengaturan'] = $pengaturan;
        $this->data['filters'] = [
            'id_kelas' => $id_kelas,
            'search' => $keyword
        ];

        // Get kelas name if filtered
        if ($id_kelas) {
            $kelas = $this->model('Kelas_model')->getKelasById($id_kelas);
            $this->data['nama_kelas'] = $kelas['nama_kelas'] ?? '';
        }

        // Render view to HTML
        $renderView = function ($view, $data) {
            ob_start();
            extract($data);
            require APPROOT . '/app/views/' . $view . '.php';
            return ob_get_clean();
        };

        $html = $renderView('admin/cetak_rekap_dokumen', $this->data);

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
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();

            $filename = 'Rekap_Dokumen_Siswa_' . date('Y-m-d_His') . '.pdf';
            $dompdf->stream($filename, ['Attachment' => true]); // true = auto-download

        } catch (Exception $e) {
            header('Content-Type: text/html; charset=utf-8');
            echo "<div style='padding:20px;color:#ef4444;'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
            echo $html;
        }
    }

    /**
     * Get partial view for document modal (AJAX)
     */
    public function getDokumenSiswaPartial($id_siswa)
    {
        $siswa = $this->model('Siswa_model')->getSiswaById($id_siswa);
        if (!$siswa) {
            echo '<div class="p-6 text-center text-red-500">Siswa tidak ditemukan</div>';
            return;
        }

        require_once APPROOT . '/app/models/DokumenConfig_model.php';
        $dokumenConfigModel = new DokumenConfig_model();

        $data['siswa'] = $siswa;
        $data['dokumenConfig'] = $dokumenConfigModel->getAllDokumen();
        $data['uploadedDocs'] = $this->model('Siswa_model')->getDokumenSiswa($id_siswa);
        $data['nisn'] = $siswa['nisn'] ?? '';
        $data['namaSiswa'] = $siswa['nama_siswa'] ?? '';
        $data['idRef'] = $id_siswa;
        $data['context'] = 'admin';
        $data['readOnly'] = false;

        // Return partial view
        extract($data);
        require APPROOT . '/app/views/admin/dokumen_siswa_partial.php';
    }

    public function lihatDokumenSiswa($id_dokumen)
    {
        $doc = $this->model('Siswa_model')->getDokumenById($id_dokumen);

        if (!$doc) {
            header('HTTP/1.0 404 Not Found');
            echo 'Dokumen tidak ditemukan';
            exit;
        }

        // Cek apakah ini file Google Drive
        if (!empty($doc['drive_url'])) {
            header('Location: ' . $doc['drive_url']);
            exit;
        }

        // Cek path_file jika berisi URL (backward compatibility)
        if (strpos($doc['path_file'], 'http') === 0) {
            header('Location: ' . $doc['path_file']);
            exit;
        }

        $filePath = APPROOT . '/uploads/siswa_dokumen/' . $doc['path_file'];

        if (!file_exists($filePath)) {
            header('HTTP/1.0 404 Not Found');
            echo 'File tidak ditemukan di server';
            exit;
        }

        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $mimeTypes = [
            'pdf' => 'application/pdf',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png'
        ];

        $mime = $mimeTypes[$ext] ?? 'application/octet-stream';

        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    }

    public function downloadDokumenSiswa($id_dokumen)
    {
        $doc = $this->model('Siswa_model')->getDokumenById($id_dokumen);

        if (!$doc) {
            header('HTTP/1.0 404 Not Found');
            echo 'Dokumen tidak ditemukan';
            exit;
        }

        // Cek apakah ini file Google Drive
        if (!empty($doc['drive_url'])) {
            header('Location: ' . $doc['drive_url']);
            exit;
        }

        // Cek path_file jika berisi URL (backward compatibility)
        if (strpos($doc['path_file'], 'http') === 0) {
            header('Location: ' . $doc['path_file']);
            exit;
        }

        $filePath = APPROOT . '/uploads/siswa_dokumen/' . $doc['path_file'];

        if (!file_exists($filePath)) {
            header('HTTP/1.0 404 Not Found');
            echo 'File tidak ditemukan di server';
            exit;
        }

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $doc['nama_file'] . '"');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    }
    // =================================================================
    // CRUD GURU
    // =================================================================
    public function guru()
    {
        $this->data['judul'] = 'Manajemen Guru';
        $this->data['guru'] = $this->model('Guru_model')->getAllGuru();
        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/guru', $this->data);
        $this->view('templates/footer', $this->data);
    }
    public function tambahGuru()
    {
        $this->data['judul'] = 'Tambah Data Guru';
        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/tambah_guru', $this->data);
        $this->view('templates/footer', $this->data);
    }
    public function prosesTambahGuru()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // VALIDASI INPUT
            $nik = InputValidator::validateNIK($_POST['nik'] ?? '');
            $nama_guru = InputValidator::sanitizeNama($_POST['nama_guru'] ?? '');
            $email = InputValidator::validateEmail($_POST['email'] ?? '');
            $password = trim($_POST['password'] ?? '');

            // Cek input wajib
            if (!$nik || empty($nama_guru) || empty($password)) {
                Flasher::setFlash('NIK, nama, dan password wajib diisi', 'danger');
                header('Location: ' . BASEURL . '/admin/tambahGuru');
                exit;
            }

            // Set default password if empty or less than 6 characters
            if (empty($password) || strlen($password) < 6) {
                $password = 'siswa123';
            }

            // Sanitize data
            $dataGuru = [
                'nik' => $nik,
                'nama_guru' => $nama_guru,
                'email' => $email ?: null
            ];

            $idGuruBaru = $this->model('Guru_model')->tambahDataGuru($dataGuru);
            if ($idGuruBaru) {
                $dataAkun = [
                    'username' => $nik,
                    'password' => $password,
                    'nama_lengkap' => $nama_guru,
                    'role' => 'guru',
                    'id_ref' => $idGuruBaru
                ];
                $this->model('User_model')->buatAkun($dataAkun);
                Flasher::setFlash('Guru berhasil ditambahkan', 'success');
                header('Location: ' . BASEURL . '/admin/guru');
                exit;
            } else {
                Flasher::setFlash('Gagal menambahkan guru', 'danger');
                header('Location: ' . BASEURL . '/admin/tambahGuru');
                exit;
            }
        } else {
            header('Location: ' . BASEURL . '/admin/guru');
            exit;
        }
    }

    public function editGuru($id)
    {
        $this->data['judul'] = 'Edit Data Guru';
        $this->data['guru'] = $this->model('Guru_model')->getGuruById($id);
        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/edit_guru', $this->data);
        $this->view('templates/footer', $this->data);
    }
    public function prosesUpdateGuru()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $updated = $this->model('Guru_model')->updateDataGuru($_POST);
            $passUpdated = false;

            // Cek field 'password' atau 'password_baru' (untuk kompatibilitas)
            $newPass = !empty($_POST['password']) ? $_POST['password'] : ($_POST['password_baru'] ?? '');

            if (!empty($newPass)) {
                $userModel = $this->model('User_model');
                $idGuru = (int) $_POST['id_guru'];

                // Cek akun role guru terlebih dahulu
                $existingGuru = $userModel->getByRef($idGuru, 'guru');
                if ($existingGuru) {
                    $userModel->updatePassword($idGuru, 'guru', $newPass);
                    $passUpdated = true;
                } else {
                    // Jika tidak ada, cek jika akun wali_kelas
                    $existingWali = $userModel->getByRef($idGuru, 'wali_kelas');
                    if ($existingWali) {
                        // Update password pada akun wali_kelas agar konsisten dengan tampilan
                        $userModel->updatePassword($idGuru, 'wali_kelas', $newPass);
                        $passUpdated = true;
                    } else {
                        // Tidak ada akun sama sekali -> buat akun role guru
                        $guru = $this->model('Guru_model')->getGuruById($idGuru);
                        $username = $guru['nik'] ?? ('GURU' . $idGuru);
                        $userModel->buatAkun([
                            'username' => $username,
                            'password' => $newPass,
                            'nama_lengkap' => $guru['nama_guru'] ?? 'Guru',
                            'role' => 'guru',
                            'id_ref' => $idGuru
                        ]);
                        $passUpdated = true;
                    }
                }
            }

            if ($updated > 0 || $passUpdated) {
                Flasher::setFlash('Data guru berhasil diperbarui.', 'success');
            } else {
                Flasher::setFlash('Tidak ada perubahan data.', 'info');
            }

            header('Location: ' . BASEURL . '/admin/guru');
            exit;
        } else {
            // Jika diakses via GET, kembalikan ke halaman guru
            header('Location: ' . BASEURL . '/admin/guru');
            exit;
        }
    }
    public function hapusGuru($id)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $db = new Database();

        // Tables that reference id_guru
        $tablesToCascade = [
            'penugasan',
            'jurnal',
            'absensi',
        ];

        $deletedDetail = [];

        try {
            // Disable foreign key checks
            $db->query("SET FOREIGN_KEY_CHECKS = 0");
            $db->execute();

            // Delete related data first
            foreach ($tablesToCascade as $t) {
                try {
                    $db->query("DELETE FROM $t WHERE id_guru = :id");
                    $db->bind('id', $id);
                    $db->execute();
                    $cnt = $db->rowCount();
                    if ($cnt > 0) {
                        $deletedDetail[] = "$t:$cnt";
                    }
                } catch (Exception $childErr) {
                    // Table might not exist or different column name
                }
            }

            // Delete user account
            $this->model('User_model')->hapusAkun($id, 'guru');

            // Delete guru
            if ($this->model('Guru_model')->hapusDataGuru($id) > 0) {
                $this->clearDashboardCache();
                $extra = empty($deletedDetail) ? '' : ' (menghapus: ' . implode(', ', $deletedDetail) . ')';
                Flasher::setFlash('Berhasil', 'Data guru berhasil dihapus' . $extra . '.', 'success');
            } else {
                Flasher::setFlash('Gagal', 'Data guru tidak ditemukan atau gagal dihapus.', 'danger');
            }

            // Re-enable foreign key checks
            $db->query("SET FOREIGN_KEY_CHECKS = 1");
            $db->execute();
        } catch (Exception $e) {
            // Re-enable foreign key checks even on error
            try {
                $db->query("SET FOREIGN_KEY_CHECKS = 1");
                $db->execute();
            } catch (Exception $fkErr) {
            }

            Flasher::setFlash('Error', 'Terjadi kesalahan: ' . $e->getMessage(), 'danger');
        }

        header('Location: ' . BASEURL . '/admin/guru');
        exit;
    }

    /**
     * Bulk delete multiple teachers
     */
    public function bulkHapusGuru()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Flasher::setFlash('Error', 'Invalid request method.', 'danger');
            header('Location: ' . BASEURL . '/admin/guru');
            exit;
        }

        $ids = $_POST['ids'] ?? [];

        if (empty($ids) || !is_array($ids)) {
            Flasher::setFlash('Gagal', 'Tidak ada guru yang dipilih untuk dihapus.', 'warning');
            header('Location: ' . BASEURL . '/admin/guru');
            exit;
        }

        $ids = array_map('intval', $ids);
        $ids = array_filter($ids, function ($id) {
            return $id > 0;
        });

        if (empty($ids)) {
            Flasher::setFlash('Gagal', 'ID guru tidak valid.', 'danger');
            header('Location: ' . BASEURL . '/admin/guru');
            exit;
        }

        $db = new Database();
        $tablesToCascade = ['penugasan', 'jurnal', 'absensi'];

        $successCount = 0;
        $failedCount = 0;

        try {
            // Disable foreign key checks
            $db->query("SET FOREIGN_KEY_CHECKS = 0");
            $db->execute();

            foreach ($ids as $id) {
                try {
                    // Delete related data first
                    foreach ($tablesToCascade as $t) {
                        try {
                            $db->query("DELETE FROM $t WHERE id_guru = :id");
                            $db->bind('id', $id);
                            $db->execute();
                        } catch (Exception $childErr) {
                            // Continue
                        }
                    }

                    // Delete user account
                    $this->model('User_model')->hapusAkun($id, 'guru');

                    // Delete guru
                    if ($this->model('Guru_model')->hapusDataGuru($id) > 0) {
                        $successCount++;
                    } else {
                        $failedCount++;
                    }
                } catch (Exception $e) {
                    $failedCount++;
                }
            }

            // Re-enable foreign key checks
            $db->query("SET FOREIGN_KEY_CHECKS = 1");
            $db->execute();
        } catch (Exception $e) {
            // Re-enable foreign key checks even on error
            try {
                $db->query("SET FOREIGN_KEY_CHECKS = 1");
                $db->execute();
            } catch (Exception $fkErr) {
            }
        }

        if ($successCount > 0) {
            $this->clearDashboardCache();
        }

        if ($successCount > 0 && $failedCount === 0) {
            Flasher::setFlash('Berhasil', "$successCount guru berhasil dihapus.", 'success');
        } elseif ($successCount > 0 && $failedCount > 0) {
            Flasher::setFlash('Sebagian Berhasil', "$successCount guru dihapus, $failedCount gagal.", 'warning');
        } else {
            Flasher::setFlash('Gagal', 'Tidak ada guru yang berhasil dihapus.', 'danger');
        }

        header('Location: ' . BASEURL . '/admin/guru');
        exit;
    }

    /**
     * Generate password Gmail untuk guru
     * Admin akan mendapat password yang bisa dicopy untuk reset manual di Google Workspace
     */
    public function generatePasswordGmail($id_guru)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/admin/guru');
            exit;
        }

        $guru = $this->model('Guru_model')->getGuruById($id_guru);

        if (!$guru) {
            echo json_encode([
                'success' => false,
                'message' => 'Guru tidak ditemukan'
            ]);
            exit;
        }

        // Cek apakah guru punya email
        if (empty($guru['email'])) {
            echo json_encode([
                'success' => false,
                'message' => 'Guru belum memiliki email. Silakan isi email terlebih dahulu di form edit guru.'
            ]);
            exit;
        }

        // Load password generator
        require_once APPROOT . '/app/core/PasswordGenerator.php';

        // Generate password kuat
        $newPassword = PasswordGenerator::generate(12, true);

        // Update password di database lokal (untuk backup dan login aplikasi)
        $userModel = $this->model('User_model');
        $existingUser = $userModel->getByRef($id_guru, 'guru');

        if (!$existingUser) {
            $existingUser = $userModel->getByRef($id_guru, 'wali_kelas');
        }

        if ($existingUser) {
            // Update existing account
            $role = $existingUser['role'];
            $userModel->updatePassword($id_guru, $role, $newPassword);
        } else {
            // Create new account
            $username = $guru['nik'] ?? ('GURU' . $id_guru);
            $userModel->buatAkun([
                'username' => $username,
                'password' => $newPassword,
                'nama_lengkap' => $guru['nama_guru'],
                'role' => 'guru',
                'id_ref' => $id_guru
            ]);
        }

        // Return response dengan password
        echo json_encode([
            'success' => true,
            'password' => $newPassword,
            'email' => $guru['email'],
            'nama' => $guru['nama_guru'],
            'message' => 'Password berhasil di-generate. Silakan copy dan reset manual di Google Workspace Admin Console.'
        ]);
        exit;
    }

    /**
     * Rekonsiliasi akun guru: buat akun untuk guru yang belum punya akun
     */
    public function rekonsiliasiAkunGuru()
    {
        try {
            $guruModel = $this->model('Guru_model');
            $userModel = $this->model('User_model');

            // Ambil semua guru
            $allGuru = $guruModel->getAllGuru();
            $created = 0;
            foreach ($allGuru as $g) {
                // Cek apakah sudah punya akun (password_plain dari LEFT JOIN)
                if (empty($g['password_plain'])) {
                    // Cek lagi via users table untuk keakuratan
                    $existing = $userModel->getByRef($g['id_guru'], 'guru');
                    if (!$existing) {
                        $username = $g['nik'] ?? ('GURU' . $g['id_guru']);
                        $defaultPassword = '12345';
                        $userModel->buatAkun([
                            'username' => $username,
                            'password' => $defaultPassword,
                            'nama_lengkap' => $g['nama_guru'] ?? 'Guru',
                            'role' => 'guru',
                            'id_ref' => (int) $g['id_guru']
                        ]);
                        $created++;
                    }
                }
            }
            Flasher::setFlash("Rekonsiliasi selesai. {$created} akun baru dibuat.", 'success');
        } catch (Exception $e) {
            error_log('rekonsiliasiAkunGuru error: ' . $e->getMessage());
            Flasher::setFlash('Terjadi kesalahan saat rekonsiliasi akun.', 'danger');
        }
        header('Location: ' . BASEURL . '/admin/guru');
        exit;
    }
    // =================================================================
    // CRUD TAHUN PELAJARAN
    // =================================================================
    public function tahunPelajaran()
    {
        $this->data['judul'] = 'Manajemen Tahun Pelajaran';
        $this->data['tp'] = $this->model('TahunPelajaran_model')->getAllTahunPelajaran();
        $this->data['all_semester'] = $this->model('TahunPelajaran_model')->getAllSemester();
        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/tahun_pelajaran', $this->data);
        $this->view('templates/footer', $this->data);
    }
    public function tambahTP()
    {
        $this->data['judul'] = 'Tambah Tahun Pelajaran';
        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/tambah_tp', $this->data);
        $this->view('templates/footer', $this->data);
    }
    public function prosesTambahTP()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if ($this->model('TahunPelajaran_model')->tambahDataTahunPelajaranDanSemester($_POST) > 0) {
                header('Location: ' . BASEURL . '/admin/tahunPelajaran');
                exit;
            }
        }
    }
    public function editTP($id)
    {
        $this->data['judul'] = 'Edit Tahun Pelajaran';
        $this->data['tp'] = $this->model('TahunPelajaran_model')->getTahunPelajaranById($id);
        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/edit_tp', $this->data);
        $this->view('templates/footer', $this->data);
    }
    public function prosesUpdateTP()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if ($this->model('TahunPelajaran_model')->updateDataTahunPelajaran($_POST) > 0) {
                header('Location: ' . BASEURL . '/admin/tahunPelajaran');
                exit;
            }
        }
    }
    public function hapusTP($id)
    {
        if ($this->model('TahunPelajaran_model')->hapusDataTahunPelajaran($id) > 0) {
            header('Location: ' . BASEURL . '/admin/tahunPelajaran');
            exit;
        }
    }

    /**
     * Set semester sebagai default untuk dropdown login
     */
    public function setDefaultSemester($id_semester)
    {
        $result = $this->model('TahunPelajaran_model')->setDefaultSemester($id_semester);

        if ($result !== false) {
            // Get semester info untuk flash message
            $smt = $this->model('TahunPelajaran_model')->getSemesterById($id_semester);
            $namaSmtFull = ($smt['nama_tp'] ?? 'TP') . ' - ' . ($smt['semester'] ?? 'Semester');
            Flasher::setFlash('Semester default login berhasil di-set ke: ' . $namaSmtFull, 'success');
        } else {
            Flasher::setFlash('Gagal mengatur semester default.', 'danger');
        }

        header('Location: ' . BASEURL . '/admin/tahunPelajaran');
        exit;
    }

    // =================================================================
    // CRUD KELAS - METHOD TAMBAH KELAS DIPERBAIKI
    // =================================================================
    public function kelas()
    {
        error_log("AdminController::kelas() method dipanggil");
        $this->data['judul'] = 'Manajemen Kelas';
        // Ambil TP aktif dari session
        $id_tp_aktif = $_SESSION['id_tp_aktif'] ?? 0;
        error_log("ID TP Aktif: " . $id_tp_aktif);
        // Ambil semua kelas dengan data tambahan (jumlah siswa & guru)
        $this->data['kelas'] = $this->model('Kelas_model')->getAllKelasWithDetails($id_tp_aktif);
        error_log("Jumlah kelas ditemukan: " . count($this->data['kelas']));
        // Data untuk statistics
        $this->data['total_kelas_aktif'] = count($this->data['kelas']);
        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/kelas', $this->data);
        $this->view('templates/footer', $this->data);
    }
    public function tambahKelas()
    {
        $this->data['judul'] = 'Tambah Kelas';
        // Ambil semua tahun pelajaran untuk dropdown
        $this->data['daftar_tp'] = $this->model('TahunPelajaran_model')->getAllTahunPelajaran();
        // Ambil semua guru untuk dropdown wali kelas
        $this->data['daftar_guru'] = $this->model('Guru_model')->getAllGuru();
        // Set default TP aktif jika ada
        $this->data['id_tp_default'] = $_SESSION['id_tp_aktif'] ?? 0;
        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/tambah_kelas', $this->data);
        $this->view('templates/footer', $this->data);
    }
    public function prosesTambahKelas()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Validasi input
            $errors = [];
            if (empty($_POST['nama_kelas'])) {
                $errors[] = 'Nama kelas harus diisi';
            }
            if (empty($_POST['jenjang'])) {
                $errors[] = 'Jenjang harus diisi';
            }
            if (empty($_POST['id_tp'])) {
                $errors[] = 'Tahun pelajaran harus dipilih';
            }

            // Cek duplikasi nama kelas dalam TP yang sama
            if (!empty($_POST['nama_kelas']) && !empty($_POST['id_tp'])) {
                if ($this->model('Kelas_model')->cekDuplikasiKelas($_POST['nama_kelas'], $_POST['id_tp'])) {
                    $errors[] = 'Nama kelas sudah ada untuk tahun pelajaran ini';
                }
            }

            if (!empty($errors)) {
                // Set flash message dengan error
                Flasher::setFlash('Gagal: ' . implode(', ', $errors), 'danger');
                header('Location: ' . BASEURL . '/admin/tambahKelas');
                exit;
            }

            // Proses insert data kelas
            $id_kelas_baru = $this->model('Kelas_model')->tambahDataKelas($_POST);

            if ($id_kelas_baru > 0) {
                // Jika wali kelas dipilih, assign wali kelas
                if (!empty($_POST['id_guru_walikelas'])) {
                    $this->model('Kelas_model')->assignWaliKelas($id_kelas_baru, $_POST['id_guru_walikelas']);

                    // Update Role User -> wali_kelas
                    $this->model('User_model')->updateRoleToWaliKelas($_POST['id_guru_walikelas']);
                }

                Flasher::setFlash('Berhasil', 'Data kelas berhasil ditambahkan.', 'success');
                header('Location: ' . BASEURL . '/admin/kelas');
                exit;
            } else {
                Flasher::setFlash('Gagal', 'Gagal menambahkan data kelas.', 'danger');
                header('Location: ' . BASEURL . '/admin/tambahKelas');
                exit;
            }
        }
        header('Location: ' . BASEURL . '/admin/tambahKelas');
        exit;
    }
    public function editKelas($id)
    {
        $this->data['judul'] = 'Edit Data Kelas';
        // Ambil data kelas berdasarkan ID
        $this->data['kelas'] = $this->model('Kelas_model')->getKelasById($id);
        // Jika kelas tidak ditemukan
        if (empty($this->data['kelas'])) {
            Flasher::setFlash('Data kelas tidak ditemukan.', 'danger');
            header('Location: ' . BASEURL . '/admin/kelas');
            exit;
        }
        // Ambil info tambahan jika ada
        $kelasDetail = $this->model('Kelas_model')->getAllKelasWithDetails(0);
        foreach ($kelasDetail as $detail) {
            if ($detail['id_kelas'] == $id) {
                $this->data['kelas']['nama_tp'] = $detail['nama_tp'];
                $this->data['kelas']['jumlah_siswa'] = $detail['jumlah_siswa'];
                $this->data['kelas']['jumlah_guru'] = $detail['jumlah_guru'];
                $this->data['kelas']['nama_guru_walikelas'] = $detail['nama_guru_walikelas'] ?? null;
                break;
            }
        }

        // Ambil daftar guru untuk dropdown wali kelas
        $this->data['daftar_guru'] = $this->model('Guru_model')->getAllGuru();

        // Ambil wali kelas saat ini jika ada
        $this->data['wali_kelas_current'] = $this->model('Kelas_model')->getWaliKelasByKelasId($id);

        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/edit_kelas', $this->data);
        $this->view('templates/footer', $this->data);
    }

    public function hapusKelas($id)
    {
        // Panggil method di model untuk cek keterkaitan data
        if ($this->model('Kelas_model')->cekKeterkaitanData($id) > 0) {
            // Jika ada, beri pesan error dan jangan hapus
            Flasher::setFlash('Gagal menghapus! Kelas ini masih memiliki data siswa atau penugasan mengajar.', 'danger');
            header('Location: ' . BASEURL . '/admin/kelas');
            exit;
        }

        // Jika tidak ada keterkaitan, lanjutkan proses hapus
        if ($this->model('Kelas_model')->hapusDataKelas($id) > 0) {
            Flasher::setFlash('Data kelas berhasil dihapus.', 'success');
        } else {
            Flasher::setFlash('Gagal menghapus data kelas.', 'danger');
        }

        // Redirect kembali ke halaman manajemen kelas
        header('Location: ' . BASEURL . '/admin/kelas');
        exit;
    }

    public function prosesUpdateKelas()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/admin/kelas');
            exit;
        }

        $id_kelas = $_POST['id_kelas'];
        $nama_kelas = trim($_POST['nama_kelas']);
        $jenjang = $_POST['jenjang'];
        $id_guru_walikelas = $_POST['id_guru_walikelas'] ?? '';

        // Validasi input
        if (empty($nama_kelas) || empty($jenjang)) {
            Flasher::setFlash('Gagal', 'Nama Kelas dan Jenjang harus diisi.', 'danger');
            header('Location: ' . BASEURL . '/admin/editKelas/' . $id_kelas);
            exit;
        }

        // Ambil data kelas saat ini
        $kelas = $this->model('Kelas_model')->getKelasById($id_kelas);
        if (!$kelas) {
            Flasher::setFlash('Gagal', 'Kelas tidak ditemukan.', 'danger');
            header('Location: ' . BASEURL . '/admin/kelas');
            exit;
        }

        // Cek duplikasi nama kelas (jika berubah)
        if ($nama_kelas !== $kelas['nama_kelas']) {
            $isDuplicate = $this->model('Kelas_model')->cekDuplikasiKelasEdit($nama_kelas, $kelas['id_tp'], $id_kelas);
            if ($isDuplicate) {
                Flasher::setFlash('Gagal', "Nama kelas '$nama_kelas' sudah ada di tahun pelajaran ini.", 'danger');
                header('Location: ' . BASEURL . '/admin/editKelas/' . $id_kelas);
                exit;
            }
        }

        // 1. Update Data Kelas Dasar
        $dataUpdate = [
            'id_kelas' => $id_kelas,
            'nama_kelas' => $nama_kelas,
            'jenjang' => $jenjang
        ];
        $updateResult = $this->model('Kelas_model')->updateDataKelas($dataUpdate);

        // 2. Handle Wali Kelas
        $waliKelasResult = 0;
        $id_tp_kelas = $kelas['id_tp'];

        // Ambil wali kelas saat ini
        $currentWali = $this->model('Kelas_model')->getWaliKelasByKelasId($id_kelas);
        $id_guru_lama = $currentWali ? $currentWali['id_guru'] : null;

        // Jika user memilih wali kelas baru
        if (!empty($id_guru_walikelas)) {
            // Cek apakah berbeda dengan yang lama
            if ($id_guru_walikelas != $id_guru_lama) {
                // Assign (akan overwrite jika ada, atau insert jika belum)
                $waliKelasResult = $this->model('Kelas_model')->assignWaliKelas($id_kelas, $id_guru_walikelas);

                // Update Role User Baru -> wali_kelas
                $this->model('User_model')->updateRoleToWaliKelas($id_guru_walikelas);

                // Cek User Lama (jika ada) -> apakah perlu revert role ke guru?
                if ($id_guru_lama) {
                    // Cek apakah guru lama masih jadi walas di kelas lain di TP yang sama?
                    // Note: Kita cek SETELAH assign di atas, jadi walas lama sudah terlepas dari kelas ini.
                    $masihWali = $this->model('WaliKelas_model')->cekWaliKelasExists($id_guru_lama, $id_tp_kelas);

                    if (!$masihWali) {
                        $this->model('User_model')->updateRoleToGuru($id_guru_lama);
                    }
                }
            }
        } else {
            // User memilih "Tidak Ada Wali Kelas" (kosong)
            if ($id_guru_lama) {
                $waliKelasResult = $this->model('Kelas_model')->removeWaliKelas($id_kelas);

                // Cek apakah guru lama masih walas di tempat lain
                $masihWali = $this->model('WaliKelas_model')->cekWaliKelasExists($id_guru_lama, $id_tp_kelas);
                if (!$masihWali) {
                    $this->model('User_model')->updateRoleToGuru($id_guru_lama);
                }
            }
        }

        if ($updateResult > 0 || $waliKelasResult > 0) {
            Flasher::setFlash('Berhasil', 'Data kelas berhasil diperbarui.', 'success');
            header('Location: ' . BASEURL . '/admin/kelas');
        } else {
            Flasher::setFlash('Info', 'Tidak ada perubahan data.', 'info');
            header('Location: ' . BASEURL . '/admin/editKelas/' . $id_kelas);
        }
        exit;
    }
    // =================================================================
    // CRUD MATA PELAJARAN
    // =================================================================
    public function mapel()
    {
        $this->data['judul'] = 'Manajemen Mata Pelajaran';
        $this->data['mapel'] = $this->model('Mapel_model')->getAllMapel();
        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/mapel', $this->data);
        $this->view('templates/footer', $this->data);
    }
    public function tambahMapel()
    {
        $this->data['judul'] = 'Tambah Mata Pelajaran';
        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/tambah_mapel', $this->data);
        $this->view('templates/footer', $this->data);
    }
    public function prosesTambahMapel()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if ($this->model('Mapel_model')->tambahDataMapel($_POST) > 0) {
                header('Location: ' . BASEURL . '/admin/mapel');
                exit;
            }
        }
    }
    public function editMapel($id)
    {
        $this->data['judul'] = 'Edit Mata Pelajaran';
        $this->data['mapel'] = $this->model('Mapel_model')->getMapelById($id);
        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/edit_mapel', $this->data);
        $this->view('templates/footer', $this->data);
    }
    public function prosesUpdateMapel()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if ($this->model('Mapel_model')->updateDataMapel($_POST) > 0) {
                header('Location: ' . BASEURL . '/admin/mapel');
                exit;
            }
        }
    }
    public function hapusMapel($id)
    {
        if ($this->model('Mapel_model')->hapusDataMapel($id) > 0) {
            header('Location: ' . BASEURL . '/admin/mapel');
            exit;
        }
    }
    // =================================================================
    // PENUGASAN - DIPERBAIKI DENGAN VALIDASI DUPLIKASI
    // =================================================================
    public function penugasan()
    {
        $this->data['judul'] = 'Penugasan Guru';
        $id_semester_aktif = $_SESSION['id_semester_aktif'] ?? 0;
        $this->data['penugasan'] = $this->model('Penugasan_model')->getAllPenugasanBySemester($id_semester_aktif);
        // Daftar semester untuk fitur copy penugasan
        $this->data['daftar_semester'] = $this->model('TahunPelajaran_model')->getAllSemester();
        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/penugasan', $this->data);
        $this->view('templates/footer', $this->data);
    }
    public function tambahPenugasan()
    {
        $this->data['judul'] = 'Tambah Penugasan';
        $this->data['guru'] = $this->model('Guru_model')->getAllGuru();
        $this->data['mapel'] = $this->model('Mapel_model')->getAllMapel();
        // PERBAIKAN: Filter kelas berdasarkan TP aktif
        $id_tp_aktif = $_SESSION['id_tp_aktif'] ?? 0;
        $this->data['kelas'] = $this->model('Kelas_model')->getKelasByTP($id_tp_aktif);
        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/tambah_penugasan', $this->data);
        $this->view('templates/footer', $this->data);
    }
    public function prosesTambahPenugasan()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Validasi input
            $errors = [];
            if (empty($_POST['id_guru'])) {
                $errors[] = 'Guru harus dipilih';
            }
            if (empty($_POST['id_mapel'])) {
                $errors[] = 'Mata pelajaran harus dipilih';
            }
            if (empty($_POST['id_kelas'])) {
                $errors[] = 'Kelas harus dipilih';
            }
            if (empty($_POST['id_semester'])) {
                $errors[] = 'Semester harus dipilih';
            }

            // Cek duplikasi penugasan
            if (empty($errors)) {
                $isDuplicate = $this->model('Penugasan_model')->cekDuplikasiPenugasan(
                    $_POST['id_guru'],
                    $_POST['id_mapel'],
                    $_POST['id_kelas'],
                    $_POST['id_semester']
                );
                if ($isDuplicate) {
                    $errors[] = 'Penugasan dengan kombinasi guru, mata pelajaran, kelas, dan semester ini sudah ada.';
                }
            }

            if (!empty($errors)) {
                Flasher::setFlash(implode(', ', $errors), 'danger');
                header('Location: ' . BASEURL . '/admin/tambahPenugasan');
                exit;
            }

            // Jika lolos validasi, simpan data
            if ($this->model('Penugasan_model')->tambahDataPenugasan($_POST) > 0) {
                Flasher::setFlash('Penugasan berhasil ditambahkan.', 'success');
                header('Location: ' . BASEURL . '/admin/penugasan');
                exit;
            } else {
                Flasher::setFlash('Gagal menambahkan penugasan.', 'danger');
                header('Location: ' . BASEURL . '/admin/tambahPenugasan');
                exit;
            }
        }
        header('Location: ' . BASEURL . '/admin/tambahPenugasan');
        exit;
    }

    // API endpoint untuk cek duplikasi penugasan via AJAX
    public function checkPenugasanDuplikat()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['error' => 'Invalid request method']);
            exit;
        }

        // Ambil data JSON dari request body
        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input) {
            echo json_encode(['error' => 'Invalid JSON']);
            exit;
        }

        // Validasi parameter yang diperlukan
        $id_guru = $input['id_guru'] ?? '';
        $id_mapel = $input['id_mapel'] ?? '';
        $id_kelas = $input['id_kelas'] ?? '';
        $id_semester = $_SESSION['id_semester_aktif'] ?? '';

        // Jika salah satu field kosong, tidak perlu cek duplikasi
        if (empty($id_guru) || empty($id_mapel) || empty($id_kelas) || empty($id_semester)) {
            echo json_encode(['isDuplicate' => false]);
            exit;
        }

        // Cek duplikasi menggunakan model
        $isDuplicate = $this->model('Penugasan_model')->cekDuplikasiPenugasan(
            $id_guru,
            $id_mapel,
            $id_kelas,
            $id_semester
        );

        echo json_encode(['isDuplicate' => $isDuplicate]);
        exit;
    }

    public function hapusPenugasan($id)
    {
        if ($this->model('Penugasan_model')->hapusDataPenugasan($id) > 0) {
            header('Location: ' . BASEURL . '/admin/penugasan');
            exit;
        }
    }
    public function editPenugasan($id)
    {
        $this->data['judul'] = 'Edit Penugasan';
        $this->data['penugasan'] = $this->model('Penugasan_model')->getPenugasanById($id);
        // Jika data tidak ditemukan
        if (empty($this->data['penugasan'])) {
            Flasher::setFlash('Data penugasan tidak ditemukan.', 'danger');
            header('Location: ' . BASEURL . '/admin/penugasan');
            exit;
        }
        $this->data['guru'] = $this->model('Guru_model')->getAllGuru();
        $this->data['mapel'] = $this->model('Mapel_model')->getAllMapel();
        // Filter kelas berdasarkan TP aktif
        $id_tp_aktif = $_SESSION['id_tp_aktif'] ?? 0;
        $this->data['kelas'] = $this->model('Kelas_model')->getKelasByTP($id_tp_aktif);
        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/edit_penugasan', $this->data);
        $this->view('templates/footer', $this->data);
    }
    public function prosesUpdatePenugasan()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Validasi input
            $errors = [];
            if (empty($_POST['id_penugasan'])) {
                $errors[] = 'ID penugasan tidak valid';
            }
            if (empty($_POST['id_guru'])) {
                $errors[] = 'Guru harus dipilih';
            }
            if (empty($_POST['id_mapel'])) {
                $errors[] = 'Mata pelajaran harus dipilih';
            }
            if (empty($_POST['id_kelas'])) {
                $errors[] = 'Kelas harus dipilih';
            }
            // Cek duplikasi penugasan (kecuali penugasan yang sedang diedit)
            if (!empty($_POST['id_guru']) && !empty($_POST['id_mapel']) && !empty($_POST['id_kelas'])) {
                $isDuplicate = $this->model('Penugasan_model')->cekDuplikasiPenugasanEdit(
                    $_POST['id_guru'],
                    $_POST['id_mapel'],
                    $_POST['id_kelas'],
                    $_POST['id_semester'],
                    $_POST['id_penugasan']
                );
                if ($isDuplicate) {
                    $errors[] = 'Penugasan dengan kombinasi guru, mata pelajaran, dan kelas ini sudah ada';
                }
            }
            if (!empty($errors)) {
                Flasher::setFlash(implode(', ', $errors), 'danger');
                header('Location: ' . BASEURL . '/admin/editPenugasan/' . $_POST['id_penugasan']);
                exit;
            }
            // Proses update
            if ($this->model('Penugasan_model')->updateDataPenugasan($_POST) > 0) {
                Flasher::setFlash('Data penugasan berhasil diperbarui.', 'success');
                header('Location: ' . BASEURL . '/admin/penugasan');
                exit;
            } else {
                Flasher::setFlash('Gagal memperbarui data penugasan.', 'danger');
                header('Location: ' . BASEURL . '/admin/editPenugasan/' . $_POST['id_penugasan']);
                exit;
            }
        }
        header('Location: ' . BASEURL . '/admin/penugasan');
        exit;
    }

    /**
     * API endpoint untuk preview penugasan dari semester tertentu (AJAX)
     */
    public function getPenugasanBySemesterApi($id_semester)
    {
        header('Content-Type: application/json');

        if (empty($id_semester)) {
            echo json_encode(['success' => false, 'message' => 'ID Semester tidak valid']);
            exit;
        }

        $penugasan = $this->model('Penugasan_model')->getAllPenugasanBySemester($id_semester);
        $count = count($penugasan);

        echo json_encode([
            'success' => true,
            'count' => $count,
            'data' => $penugasan
        ]);
        exit;
    }

    /**
     * Proses copy penugasan dari semester sumber ke semester aktif
     */
    public function prosesCopyPenugasan()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/admin/penugasan');
            exit;
        }

        $id_semester_sumber = $_POST['id_semester_sumber'] ?? null;
        $id_semester_tujuan = $_SESSION['id_semester_aktif'] ?? null;

        if (empty($id_semester_sumber) || empty($id_semester_tujuan)) {
            Flasher::setFlash('Semester sumber atau tujuan tidak valid.', 'danger');
            header('Location: ' . BASEURL . '/admin/penugasan');
            exit;
        }

        if ($id_semester_sumber == $id_semester_tujuan) {
            Flasher::setFlash('Tidak bisa copy ke semester yang sama.', 'danger');
            header('Location: ' . BASEURL . '/admin/penugasan');
            exit;
        }

        $result = $this->model('Penugasan_model')->copyPenugasanFromSemester(
            $id_semester_sumber,
            $id_semester_tujuan
        );

        if ($result['copied'] > 0) {
            $message = "Berhasil copy {$result['copied']} penugasan.";
            if ($result['skipped'] > 0) {
                $message .= " ({$result['skipped']} di-skip karena sudah ada)";
            }
            Flasher::setFlash($message, 'success');
        } else {
            if ($result['skipped'] > 0) {
                Flasher::setFlash("Semua {$result['skipped']} penugasan sudah ada di semester ini.", 'info');
            } else {
                Flasher::setFlash('Tidak ada penugasan yang bisa di-copy dari semester sumber.', 'warning');
            }
        }

        header('Location: ' . BASEURL . '/admin/penugasan');
        exit;
    }

    // =================================================================
    // KEANGGOTAAN KELAS
    // =================================================================
    public function keanggotaan()
    {
        $this->data['judul'] = 'Anggota Kelas';
        $id_tp_aktif = $_SESSION['id_tp_aktif'] ?? 0;
        $this->data['daftar_kelas'] = $this->model('Kelas_model')->getKelasByTP($id_tp_aktif);
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_kelas'])) {
            $id_kelas = $_POST['id_kelas'];
            $this->data['kelas_terpilih'] = $this->model('Kelas_model')->getKelasById($id_kelas);
            $this->data['anggota_kelas'] = $this->model('Keanggotaan_model')->getSiswaByKelas($id_kelas, $id_tp_aktif);
        }
        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/keanggotaan', $this->data);
        $this->view('templates/footer', $this->data);
    }
    public function tambahAnggota($id_kelas)
    {
        $this->data['judul'] = 'Tambah Anggota Kelas';
        $id_tp_aktif = $_SESSION['id_tp_aktif'] ?? 0;
        $this->data['kelas_terpilih'] = $this->model('Kelas_model')->getKelasById($id_kelas);
        $this->data['siswa_tersedia'] = $this->model('Keanggotaan_model')->getSiswaNotInAnyClass($id_tp_aktif);
        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/tambah_anggota', $this->data);
        $this->view('templates/footer', $this->data);
    }
    public function prosesTambahAnggota()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_siswa'])) {
            if ($this->model('Keanggotaan_model')->tambahAnggotaKelas($_POST) > 0) {
                header('Location: ' . BASEURL . '/admin/keanggotaan');
                exit;
            }
        } else {
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit;
        }
    }
    public function hapusAnggota($id_keanggotaan)
    {
        if ($this->model('Keanggotaan_model')->hapusAnggotaKelas($id_keanggotaan) > 0) {
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit;
        }
    }
    // =================================================================
    // NAIK KELAS
    // =================================================================
    public function naikKelas()
    {
        $this->data['judul'] = 'Naik Kelas';
        $this->data['daftar_tp'] = $this->model('TahunPelajaran_model')->getAllTahunPelajaran();
        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/naik_kelas', $this->data);
        $this->view('templates/footer', $this->data);
    }
    public function getKelasByTP($id_tp)
    {
        $dataKelas = $this->model('Kelas_model')->getKelasByTP($id_tp);
        header('Content-Type: application/json');
        echo json_encode($dataKelas);
    }
    public function getSiswaByKelas($id_kelas, $id_tp)
    {
        $dataSiswa = $this->model('Keanggotaan_model')->getSiswaByKelas($id_kelas, $id_tp);
        header('Content-Type: application/json');
        echo json_encode($dataSiswa);
    }
    public function prosesNaikKelas()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['siswa_terpilih'])) {
            $id_tp_tujuan = $_POST['id_tp_tujuan'];
            $id_kelas_tujuan = $_POST['id_kelas_tujuan'];
            $daftar_siswa = $_POST['siswa_terpilih'];
            $jumlahSiswa = $this->model('Keanggotaan_model')->prosesPromosiSiswaTerpilih($id_tp_tujuan, $id_kelas_tujuan, $daftar_siswa);
            Flasher::setFlash("Proses kenaikan kelas berhasil. Sebanyak $jumlahSiswa siswa telah dipindahkan.", 'success');
            header('Location: ' . BASEURL . '/admin/naikKelas');
            exit;
        } else {
            Flasher::setFlash('Gagal! Tidak ada siswa yang dipilih atau kelas tujuan belum ditentukan.', 'danger');
            header('Location: ' . BASEURL . '/admin/naikKelas');
            exit;
        }
    }
    // =================================================================
    // KELULUSAN
    // =================================================================
    public function kelulusan()
    {
        $this->data['judul'] = 'Kelulusan Siswa';
        $this->data['daftar_tp'] = $this->model('TahunPelajaran_model')->getAllTahunPelajaran();
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tampilkan_siswa'])) {
            $id_tp = $_POST['id_tp'];
            $id_kelas = $_POST['id_kelas'];
            $this->data['id_tp_pilihan'] = $id_tp;
            $this->data['id_kelas_pilihan'] = $id_kelas;
            $this->data['daftar_siswa'] = $this->model('Keanggotaan_model')->getSiswaByKelas($id_kelas, $id_tp);
        }
        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/kelulusan', $this->data);
        $this->view('templates/footer', $this->data);
    }
    public function prosesKelulusan()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['siswa_terpilih'])) {
            $daftar_siswa = $_POST['siswa_terpilih'];
            $jumlahSiswa = $this->model('Siswa_model')->luluskanSiswaByIds($daftar_siswa);
            Flasher::setFlash("Proses kelulusan berhasil. Sebanyak $jumlahSiswa siswa telah diubah statusnya menjadi Lulus.", 'success');
            header('Location: ' . BASEURL . '/admin/kelulusan');
            exit;
        } else {
            Flasher::setFlash('Gagal! Tidak ada siswa yang dipilih.', 'danger');
            header('Location: ' . BASEURL . '/admin/kelulusan');
            exit;
        }
    }
    // =================================================================
    // RIWAYAT JURNAL & STATISTIK ADMIN - 4 METHOD BARU - FIXED SQL
    // =================================================================
    /**
     * Riwayat Per Mapel dengan Statistik - Admin melihat semua mapel
     */
    public function riwayatPerMapel()
    {
        $this->data['judul'] = 'Riwayat Jurnal & Statistik';
        $id_semester_aktif = $_SESSION['id_semester_aktif'] ?? 0;
        // Filter dari GET parameters
        $filter_guru = $_GET['guru'] ?? null;
        $filter_mapel = $_GET['mapel'] ?? null;
        $filter_kelas = $_GET['kelas'] ?? null;
        // Data untuk dropdown filter
        $this->data['daftar_guru'] = $this->model('Guru_model')->getAllGuru();
        $this->data['daftar_mapel'] = $this->model('Mapel_model')->getAllMapel();
        $this->data['daftar_kelas'] = $this->model('Kelas_model')->getKelasByTP($_SESSION['id_tp_aktif'] ?? 0);
        // Data riwayat jurnal dengan statistik untuk admin
        $this->data['jurnal_per_mapel'] = $this->getAllJurnalPerMapelAdmin($id_semester_aktif, $filter_guru, $filter_mapel, $filter_kelas);
        // Data filter yang dipilih
        $this->data['filter'] = [
            'guru' => $filter_guru,
            'mapel' => $filter_mapel,
            'kelas' => $filter_kelas
        ];
        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/riwayat_per_mapel_with_stats', $this->data);
        $this->view('templates/footer', $this->data);
    }
    /**
     * Rincian Absen per Pertemuan - Admin dengan filter guru/kelas
     */
    /**
     * Cetak Mapel Admin - Cetak laporan mapel dari guru tertentu
     */
    public function cetakMapelAdmin($combo_id)
    {
        // Format combo_id: "guru_id-mapel_id" 
        $parts = explode('-', $combo_id);
        if (count($parts) < 2) {
            echo "<div style='padding:20px;color:#ef4444;'>Error: Format combo_id tidak valid. Harus berupa 'guru_id-mapel_id'</div>";
            return;
        }
        $id_guru = $parts[0];
        $id_mapel = $parts[1];
        $id_semester = $_SESSION['id_semester_aktif'] ?? null;
        // Ambil data untuk laporan
        $meta = $this->getMetaLaporanAdmin($id_guru, $id_mapel, $id_semester);
        $rekap_siswa = $this->getRekapSiswaAdmin($id_guru, $id_mapel, $id_semester);
        $rekap_pertemuan = $this->getRekapPertemuanAdmin($id_guru, $id_mapel, $id_semester);
        $this->data = [
            'meta' => $meta,
            'rekap_siswa' => $rekap_siswa,
            'rekap_pertemuan' => $rekap_pertemuan,
            'total_siswa' => count($rekap_siswa),
            'id_mapel' => $combo_id
        ];
        // Render view
        $wantPdf = isset($_GET['pdf']) && $_GET['pdf'] == 1;
        $renderView = function ($view, $data) {
            extract($data);
            ob_start();
            require __DIR__ . "/../views/$view.php";
            return ob_get_clean();
        };
        $html = $renderView('admin/cetak_mapel', $this->data);
        if ($wantPdf) {
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
                $mapel_name = $meta['nama_mapel'] ?? 'Mapel';
                $guru_name = $meta['nama_guru'] ?? 'Guru';
                $filename = 'Laporan_' . preg_replace('/\s+/', '_', $mapel_name) . '_' . preg_replace('/\s+/', '_', $guru_name) . '_' . date('Y-m-d') . '.pdf';
                $dompdf->stream($filename, ['Attachment' => true]);
                return;
            } catch (Exception $e) {
                header('Content-Type: text/html; charset=utf-8');
                echo "<div style='padding:20px;color:#ef4444;'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
                echo $html;
                return;
            }
        }
        // Tampilkan halaman cetak HTML
        header('Content-Type: text/html; charset=utf-8');
        echo $html;
    }
    /**
     * Cetak Rincian Absen Admin - dengan filter guru
     */
    public function cetakRincianAbsenAdmin($combo_id)
    {
        // Format combo_id: "guru_id-mapel_id"
        $parts = explode('-', $combo_id);
        if (count($parts) < 2) {
            echo "<div style='padding:20px;color:#ef4444;'>Error: Format combo_id tidak valid. Harus berupa 'guru_id-mapel_id'</div>";
            return;
        }
        $id_guru = $parts[0];
        $id_mapel = $parts[1];
        $id_semester = $_SESSION['id_semester_aktif'] ?? null;
        // Parameter filter
        $periode = $_GET['periode'] ?? 'semester';
        $tanggal_mulai = $_GET['tanggal_mulai'] ?? '';
        $tanggal_akhir = $_GET['tanggal_akhir'] ?? '';
        // Ambil data
        $this->data['mapel_info'] = $this->getMapelInfoAdmin($id_semester, $id_mapel, $id_guru);
        $this->data['rincian_data'] = $this->getRincianAbsenAdmin($id_semester, $id_mapel, $id_guru, $periode, $tanggal_mulai, $tanggal_akhir);
        $this->data['filter_info'] = [
            'periode' => $periode,
            'tanggal_mulai' => $tanggal_mulai,
            'tanggal_akhir' => $tanggal_akhir,
            'tanggal_cetak' => date('d F Y')
        ];
        // Render view
        $wantPdf = isset($_GET['pdf']) && $_GET['pdf'] == 1;
        $renderView = function ($view, $data) {
            extract($data);
            ob_start();
            require __DIR__ . "/../views/$view.php";
            return ob_get_clean();
        };
        $html = $renderView('admin/cetak_rincian_absen', $this->data);
        if ($wantPdf) {
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
                $dompdf->setPaper('A4', 'landscape');
                $dompdf->render();
                $mapel_name = $this->data['mapel_info']['nama_mapel'] ?? 'Mapel';
                $guru_name = $this->data['mapel_info']['nama_guru'] ?? 'Guru';
                $filename = 'Rincian_Absen_' . preg_replace('/\s+/', '_', $mapel_name) . '_' . preg_replace('/\s+/', '_', $guru_name) . '_' . date('Y-m-d') . '.pdf';
                $dompdf->stream($filename, ['Attachment' => true]);
                return;
            } catch (Exception $e) {
                header('Content-Type: text/html; charset=utf-8');
                echo "<div style='padding:20px;color:#ef4444;'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
                echo $html;
                return;
            }
        }
        // Tampilkan halaman cetak HTML
        header('Content-Type: text/html; charset=utf-8');
        echo $html;
    }
    // =================================================================
    // HELPER METHODS UNTUK ADMIN RIWAYAT & CETAK - FIXED SQL
    // =================================================================
    /**
     * Ambil semua jurnal per mapel untuk admin dengan filter - FIXED SQL
     */
    private function getAllJurnalPerMapelAdmin($id_semester, $filter_guru = null, $filter_mapel = null, $filter_kelas = null)
    {
        $db = new Database();
        try {
            // Build WHERE clause
            $whereClause = "p.id_semester = :id_semester";
            $params = ['id_semester' => $id_semester];
            if ($filter_guru) {
                $whereClause .= " AND p.id_guru = :id_guru";
                $params['id_guru'] = $filter_guru;
            }
            if ($filter_mapel) {
                $whereClause .= " AND p.id_mapel = :id_mapel";
                $params['id_mapel'] = $filter_mapel;
            }
            if ($filter_kelas) {
                $whereClause .= " AND p.id_kelas = :id_kelas";
                $params['id_kelas'] = $filter_kelas;
            }
            // Query statistik absensi per mapel-guru kombinasi
            $sql = "SELECT 
                        p.id_penugasan,
                        g.id_guru,
                        g.nama_guru,
                        m.id_mapel,
                        m.nama_mapel,
                        k.nama_kelas,
                        COUNT(DISTINCT j.id_jurnal) as total_pertemuan,
                        COUNT(DISTINCT siswa.id_siswa) as total_siswa,
                        SUM(CASE WHEN a.status_kehadiran = 'H' THEN 1 ELSE 0 END) as total_hadir,
                        SUM(CASE WHEN a.status_kehadiran = 'I' THEN 1 ELSE 0 END) as total_izin,
                        SUM(CASE WHEN a.status_kehadiran = 'S' THEN 1 ELSE 0 END) as total_sakit,
                        SUM(CASE WHEN a.status_kehadiran = 'A' OR a.status_kehadiran IS NULL THEN 1 ELSE 0 END) as total_alpha,
                        COUNT(a.id_absensi) as total_absensi_records
                    FROM penugasan p
                    JOIN mapel m ON p.id_mapel = m.id_mapel
                    JOIN kelas k ON p.id_kelas = k.id_kelas
                    JOIN guru g ON p.id_guru = g.id_guru
                    LEFT JOIN jurnal j ON p.id_penugasan = j.id_penugasan
                    LEFT JOIN absensi a ON j.id_jurnal = a.id_jurnal
                    LEFT JOIN siswa ON k.id_kelas = siswa.id_kelas
                    WHERE {$whereClause}
                    GROUP BY p.id_penugasan, g.id_guru, m.id_mapel, k.id_kelas
                    HAVING total_pertemuan > 0
                    ORDER BY g.nama_guru, m.nama_mapel, k.nama_kelas";
            $db->query($sql);
            foreach ($params as $key => $value) {
                $db->bind($key, $value);
            }
            $statistik_results = $db->resultSet();
            // Transform data untuk view (mirip struktur guru)
            $jurnal_per_mapel = [];
            foreach ($statistik_results as $stat) {
                $persentase_kehadiran = $stat['total_absensi_records'] > 0 ?
                    round(($stat['total_hadir'] / $stat['total_absensi_records']) * 100, 1) : 0;
                $chart_data = [
                    'hadir' => (int) $stat['total_hadir'],
                    'izin' => (int) $stat['total_izin'],
                    'sakit' => (int) $stat['total_sakit'],
                    'alpha' => (int) $stat['total_alpha']
                ];
                $jurnal_per_mapel[] = [
                    'id_mapel_untuk_link' => $stat['id_guru'] . '-' . $stat['id_mapel'], // Format combo untuk link
                    'id_guru' => $stat['id_guru'],
                    'nama_guru' => $stat['nama_guru'],
                    'id_mapel' => $stat['id_mapel'],
                    'nama_mapel' => $stat['nama_mapel'],
                    'nama_kelas' => $stat['nama_kelas'],
                    'statistik' => [
                        'total_pertemuan' => $stat['total_pertemuan'],
                        'total_siswa' => $stat['total_siswa'],
                        'total_hadir' => $stat['total_hadir'],
                        'total_izin' => $stat['total_izin'],
                        'total_sakit' => $stat['total_sakit'],
                        'total_alpha' => $stat['total_alpha'],
                        'total_absensi_records' => $stat['total_absensi_records'],
                        'persentase_kehadiran' => $persentase_kehadiran
                    ],
                    'chart_data' => $chart_data,
                    'pertemuan' => [] // Bisa diisi jika diperlukan detail pertemuan
                ];
            }
            return $jurnal_per_mapel;
        } catch (Exception $e) {
            error_log("Error in getAllJurnalPerMapelAdmin: " . $e->getMessage());
            return [];
        }
    }
    /**
     * Ambil daftar mapel untuk admin dengan info guru - FIXED SQL
     */
    private function getDaftarMapelAdmin($id_semester)
    {
        $db = new Database();
        try {
            $sql = "SELECT DISTINCT 
                        CONCAT(p.id_guru, '-', m.id_mapel) as combo_id,
                        m.id_mapel, 
                        m.nama_mapel, 
                        k.nama_kelas,
                        g.nama_guru,
                        g.id_guru
                    FROM penugasan p
                    JOIN mapel m ON p.id_mapel = m.id_mapel
                    JOIN kelas k ON p.id_kelas = k.id_kelas
                    JOIN guru g ON p.id_guru = g.id_guru
                    WHERE p.id_semester = :id_semester
                    ORDER BY g.nama_guru, m.nama_mapel, k.nama_kelas";
            $db->query($sql);
            $db->bind('id_semester', $id_semester);
            return $db->resultSet();
        } catch (Exception $e) {
            error_log("Error in getDaftarMapelAdmin: " . $e->getMessage());
            return [];
        }
    }
    /**
     * Ambil info mapel admin dengan guru - FIXED SQL
     */
    private function getMapelInfoAdmin($id_semester, $id_mapel, $id_guru)
    {
        $db = new Database();
        try {
            // FIXED: JOIN semester dengan tp untuk mendapatkan nama_tp
            $sql = "SELECT m.nama_mapel, k.nama_kelas, g.nama_guru, tp.nama_tp, smt.semester
                    FROM penugasan p
                    JOIN mapel m ON p.id_mapel = m.id_mapel
                    JOIN kelas k ON p.id_kelas = k.id_kelas
                    JOIN guru g ON p.id_guru = g.id_guru
                    JOIN semester smt ON p.id_semester = smt.id_semester
                    JOIN tp ON smt.id_tp = tp.id_tp
                    WHERE p.id_semester = :id_semester AND m.id_mapel = :id_mapel";
            // Jika id_guru tidak kosong, tambahkan filter guru
            if (!empty($id_guru)) {
                $sql .= " AND p.id_guru = :id_guru";
            }
            $sql .= " LIMIT 1";
            $db->query($sql);
            $db->bind('id_semester', $id_semester);
            $db->bind('id_mapel', $id_mapel);
            if (!empty($id_guru)) {
                $db->bind('id_guru', $id_guru);
            }
            return $db->single() ?: [];
        } catch (Exception $e) {
            error_log("Error in getMapelInfoAdmin: " . $e->getMessage());
            return [];
        }
    }
    /**
     * Ambil rincian absen admin dengan filter guru - FIXED SQL
     */
    private function getRincianAbsenAdmin($id_semester, $id_mapel, $id_guru, $periode, $tanggal_mulai, $tanggal_akhir)
    {
        $db = new Database();
        try {
            // Build WHERE clause berdasarkan periode
            $whereClause = "p.id_semester = :id_semester AND m.id_mapel = :id_mapel AND p.id_guru = :id_guru";
            $params = [
                'id_semester' => $id_semester,
                'id_mapel' => $id_mapel,
                'id_guru' => $id_guru
            ];
            // Tambahkan filter periode
            switch ($periode) {
                case 'hari_ini':
                    $whereClause .= " AND DATE(j.tanggal) = CURDATE()";
                    break;
                case 'minggu_ini':
                    $whereClause .= " AND YEARWEEK(j.tanggal, 1) = YEARWEEK(CURDATE(), 1)";
                    break;
                case 'bulan_ini':
                    $whereClause .= " AND YEAR(j.tanggal) = YEAR(CURDATE()) AND MONTH(j.tanggal) = MONTH(CURDATE())";
                    break;
                case 'custom':
                    if ($tanggal_mulai && $tanggal_akhir) {
                        $whereClause .= " AND j.tanggal BETWEEN :tanggal_mulai AND :tanggal_akhir";
                        $params['tanggal_mulai'] = $tanggal_mulai;
                        $params['tanggal_akhir'] = $tanggal_akhir;
                    }
                    break;
            }
            // Query sama seperti method guru
            $sql = "SELECT 
                        s.id_siswa,
                        s.nama_siswa,
                        s.nisn,
                        j.id_jurnal,
                        j.tanggal,
                        j.pertemuan_ke,
                        j.topik_materi,
                        COALESCE(a.status_kehadiran, 'A') as status_kehadiran,
                        a.waktu_absen,
                        a.keterangan
                    FROM penugasan p
                    JOIN mapel m ON p.id_mapel = m.id_mapel
                    JOIN kelas k ON p.id_kelas = k.id_kelas
                    JOIN jurnal j ON p.id_penugasan = j.id_penugasan
                    JOIN siswa s ON s.id_kelas = k.id_kelas
                    LEFT JOIN absensi a ON j.id_jurnal = a.id_jurnal AND s.id_siswa = a.id_siswa
                    WHERE $whereClause
                    ORDER BY s.nama_siswa ASC, j.tanggal ASC, j.pertemuan_ke ASC";
            $db->query($sql);
            foreach ($params as $key => $value) {
                $db->bind($key, $value);
            }
            $result = $db->resultSet();
            // Structure data sama seperti method guru
            $structured_data = [];
            $pertemuan_list = [];
            foreach ($result as $row) {
                $id_siswa = $row['id_siswa'];
                $id_jurnal = $row['id_jurnal'];
                if (!isset($structured_data[$id_siswa])) {
                    $structured_data[$id_siswa] = [
                        'id_siswa' => $id_siswa,
                        'nama_siswa' => $row['nama_siswa'],
                        'nisn' => $row['nisn'],
                        'pertemuan' => [],
                        'total_hadir' => 0,
                        'total_izin' => 0,
                        'total_sakit' => 0,
                        'total_alpha' => 0
                    ];
                }
                $structured_data[$id_siswa]['pertemuan'][$id_jurnal] = [
                    'tanggal' => $row['tanggal'],
                    'pertemuan_ke' => $row['pertemuan_ke'],
                    'topik_materi' => $row['topik_materi'],
                    'status' => $row['status_kehadiran'],
                    'waktu_absen' => $row['waktu_absen'],
                    'keterangan' => $row['keterangan']
                ];
                switch ($row['status_kehadiran']) {
                    case 'H':
                        $structured_data[$id_siswa]['total_hadir']++;
                        break;
                    case 'I':
                        $structured_data[$id_siswa]['total_izin']++;
                        break;
                    case 'S':
                        $structured_data[$id_siswa]['total_sakit']++;
                        break;
                    default:
                        $structured_data[$id_siswa]['total_alpha']++;
                        break;
                }
                if (!isset($pertemuan_list[$id_jurnal])) {
                    $pertemuan_list[$id_jurnal] = [
                        'tanggal' => $row['tanggal'],
                        'pertemuan_ke' => $row['pertemuan_ke'],
                        'topik_materi' => $row['topik_materi']
                    ];
                }
            }
            uasort($pertemuan_list, function ($a, $b) {
                return strtotime($a['tanggal']) - strtotime($b['tanggal']);
            });
            return [
                'siswa_data' => array_values($structured_data),
                'pertemuan_headers' => array_values($pertemuan_list)
            ];
        } catch (Exception $e) {
            error_log("Error in getRincianAbsenAdmin: " . $e->getMessage());
            return [
                'siswa_data' => [],
                'pertemuan_headers' => []
            ];
        }
    }
    /**
     * Helper untuk meta laporan admin - FIXED SQL
     */
    private function getMetaLaporanAdmin($id_guru, $id_mapel, $id_semester)
    {
        $db = new Database();
        try {
            // FIXED: JOIN semester dengan tp
            $sql = "SELECT m.nama_mapel, k.nama_kelas, g.nama_guru, tp.nama_tp, smt.semester
                    FROM penugasan p
                    JOIN mapel m ON p.id_mapel = m.id_mapel
                    JOIN kelas k ON p.id_kelas = k.id_kelas
                    JOIN guru g ON p.id_guru = g.id_guru
                    JOIN semester smt ON p.id_semester = smt.id_semester
                    JOIN tp ON smt.id_tp = tp.id_tp
                    WHERE p.id_guru = :id_guru AND p.id_semester = :id_semester AND m.id_mapel = :id_mapel
                    LIMIT 1";
            $db->query($sql);
            $db->bind('id_guru', $id_guru);
            $db->bind('id_semester', $id_semester);
            $db->bind('id_mapel', $id_mapel);
            $result = $db->single();
            if ($result) {
                $result['tanggal'] = date('d F Y');
                $result['tp'] = $result['nama_tp'] ?? '';
            }
            return $result ?: [];
        } catch (Exception $e) {
            error_log("Error in getMetaLaporanAdmin: " . $e->getMessage());
            return [];
        }
    }
    /**
     * Helper untuk rekap siswa admin - FIXED SQL
     */
    private function getRekapSiswaAdmin($id_guru, $id_mapel, $id_semester)
    {
        $db = new Database();
        try {
            $sql = "SELECT 
                        s.nama_siswa,
                        SUM(CASE WHEN a.status_kehadiran = 'H' THEN 1 ELSE 0 END) as hadir,
                        SUM(CASE WHEN a.status_kehadiran = 'I' THEN 1 ELSE 0 END) as izin,
                        SUM(CASE WHEN a.status_kehadiran = 'S' THEN 1 ELSE 0 END) as sakit,
                        SUM(CASE WHEN a.status_kehadiran = 'A' OR a.status_kehadiran IS NULL THEN 1 ELSE 0 END) as alpha,
                        COUNT(j.id_jurnal) as total
                    FROM penugasan p
                    JOIN kelas k ON p.id_kelas = k.id_kelas
                    JOIN siswa s ON k.id_kelas = s.id_kelas
                    LEFT JOIN jurnal j ON p.id_penugasan = j.id_penugasan
                    LEFT JOIN absensi a ON j.id_jurnal = a.id_jurnal AND s.id_siswa = a.id_siswa
                    WHERE p.id_guru = :id_guru AND p.id_semester = :id_semester AND p.id_mapel = :id_mapel
                    GROUP BY s.id_siswa, s.nama_siswa
                    ORDER BY s.nama_siswa";
            $db->query($sql);
            $db->bind('id_guru', $id_guru);
            $db->bind('id_semester', $id_semester);
            $db->bind('id_mapel', $id_mapel);
            return $db->resultSet();
        } catch (Exception $e) {
            error_log("Error in getRekapSiswaAdmin: " . $e->getMessage());
            return [];
        }
    }
    /**
     * Helper untuk rekap pertemuan admin - FIXED SQL
     */
    private function getRekapPertemuanAdmin($id_guru, $id_mapel, $id_semester)
    {
        $db = new Database();
        try {
            $sql = "SELECT 
                        j.pertemuan_ke,
                        j.tanggal,
                        j.topik_materi,
                        SUM(CASE WHEN a.status_kehadiran = 'H' THEN 1 ELSE 0 END) as hadir,
                        SUM(CASE WHEN a.status_kehadiran = 'I' THEN 1 ELSE 0 END) as izin,
                        SUM(CASE WHEN a.status_kehadiran = 'S' THEN 1 ELSE 0 END) as sakit,
                        SUM(CASE WHEN a.status_kehadiran = 'A' OR a.status_kehadiran IS NULL THEN 1 ELSE 0 END) as alpha
                    FROM penugasan p
                    JOIN jurnal j ON p.id_penugasan = j.id_penugasan
                    LEFT JOIN absensi a ON j.id_jurnal = a.id_jurnal
                    WHERE p.id_guru = :id_guru AND p.id_semester = :id_semester AND p.id_mapel = :id_mapel
                    GROUP BY j.id_jurnal, j.pertemuan_ke, j.tanggal
                    ORDER BY j.tanggal, j.pertemuan_ke";
            $db->query($sql);
            $db->bind('id_guru', $id_guru);
            $db->bind('id_semester', $id_semester);
            $db->bind('id_mapel', $id_mapel);
            return $db->resultSet();
        } catch (Exception $e) {
            error_log("Error in getRekapPertemuanAdmin: " . $e->getMessage());
            return [];
        }
    }
    // =================================================================
    // LEGACY METHODS - DIPERBAIKI SQL
    // =================================================================
    /**
     * Helper: Ambil daftar mapel yang diajar guru - FIXED SQL
     */
    private function getDaftarMapelGuru($id_guru, $id_semester)
    {
        $db = new Database();
        try {
            $sql = "SELECT DISTINCT m.id_mapel, m.nama_mapel, k.nama_kelas
                    FROM penugasan p
                    JOIN mapel m ON p.id_mapel = m.id_mapel  
                    JOIN kelas k ON p.id_kelas = k.id_kelas
                    WHERE p.id_guru = :id_guru AND p.id_semester = :id_semester
                    ORDER BY m.nama_mapel, k.nama_kelas";
            $db->query($sql);
            $db->bind('id_guru', $id_guru);
            $db->bind('id_semester', $id_semester);
            return $db->resultSet();
        } catch (Exception $e) {
            error_log("Error in getDaftarMapelGuru: " . $e->getMessage());
            return [];
        }
    }
    /**
     * Helper: Ambil info mapel (nama, kelas, dll) - FIXED SQL
     */
    private function getMapelInfo($id_guru, $id_semester, $id_mapel)
    {
        $db = new Database();
        try {
            // FIXED: Gunakan nama tabel yang benar
            $sql = "SELECT m.nama_mapel, k.nama_kelas, g.nama_guru, tp.nama_tp, smt.semester
                    FROM penugasan p
                    JOIN mapel m ON p.id_mapel = m.id_mapel
                    JOIN kelas k ON p.id_kelas = k.id_kelas  
                    JOIN guru g ON p.id_guru = g.id_guru
                    JOIN semester smt ON p.id_semester = smt.id_semester
                    JOIN tp ON smt.id_tp = tp.id_tp
                    WHERE p.id_guru = :id_guru AND p.id_semester = :id_semester AND m.id_mapel = :id_mapel
                    LIMIT 1";
            $db->query($sql);
            $db->bind('id_guru', $id_guru);
            $db->bind('id_semester', $id_semester);
            $db->bind('id_mapel', $id_mapel);
            return $db->single() ?: [];
        } catch (Exception $e) {
            error_log("Error in getMapelInfo: " . $e->getMessage());
            return [];
        }
    }
    /**
     * Helper: Ambil rincian absen per pertemuan dengan filter - FIXED SQL
     */
    private function getRincianAbsenPerPertemuan($id_guru, $id_semester, $id_mapel, $periode, $tanggal_mulai, $tanggal_akhir)
    {
        $db = new Database();
        try {
            // Build WHERE clause berdasarkan periode
            $whereClause = "p.id_guru = :id_guru AND p.id_semester = :id_semester AND m.id_mapel = :id_mapel";
            $params = [
                'id_guru' => $id_guru,
                'id_semester' => $id_semester,
                'id_mapel' => $id_mapel
            ];
            switch ($periode) {
                case 'hari_ini':
                    $whereClause .= " AND DATE(j.tanggal) = CURDATE()";
                    break;
                case 'minggu_ini':
                    $whereClause .= " AND YEARWEEK(j.tanggal, 1) = YEARWEEK(CURDATE(), 1)";
                    break;
                case 'bulan_ini':
                    $whereClause .= " AND YEAR(j.tanggal) = YEAR(CURDATE()) AND MONTH(j.tanggal) = MONTH(CURDATE())";
                    break;
                case 'custom':
                    if ($tanggal_mulai && $tanggal_akhir) {
                        $whereClause .= " AND j.tanggal BETWEEN :tanggal_mulai AND :tanggal_akhir";
                        $params['tanggal_mulai'] = $tanggal_mulai;
                        $params['tanggal_akhir'] = $tanggal_akhir;
                    }
                    break;
                default: // semester - tidak ada filter tambahan
                    break;
            }
            // Query utama - ambil data absen per siswa per pertemuan
            $sql = "SELECT 
                        s.id_siswa,
                        s.nama_siswa,
                        s.nisn,
                        j.id_jurnal,
                        j.tanggal,
                        j.pertemuan_ke,
                        j.topik_materi,
                        COALESCE(a.status_kehadiran, 'A') as status_kehadiran,
                        a.waktu_absen,
                        a.keterangan
                    FROM penugasan p
                    JOIN mapel m ON p.id_mapel = m.id_mapel
                    JOIN kelas k ON p.id_kelas = k.id_kelas
                    JOIN jurnal j ON p.id_penugasan = j.id_penugasan
                    JOIN siswa s ON s.id_kelas = k.id_kelas
                    LEFT JOIN absensi a ON j.id_jurnal = a.id_jurnal AND s.id_siswa = a.id_siswa
                    WHERE $whereClause
                    ORDER BY s.nama_siswa ASC, j.tanggal ASC, j.pertemuan_ke ASC";
            $db->query($sql);
            foreach ($params as $key => $value) {
                $db->bind($key, $value);
            }
            $result = $db->resultSet();
            // Restructure data: group by siswa, dengan detail per pertemuan
            $structured_data = [];
            $pertemuan_list = [];
            foreach ($result as $row) {
                $id_siswa = $row['id_siswa'];
                $id_jurnal = $row['id_jurnal'];
                // Simpan info siswa
                if (!isset($structured_data[$id_siswa])) {
                    $structured_data[$id_siswa] = [
                        'id_siswa' => $id_siswa,
                        'nama_siswa' => $row['nama_siswa'],
                        'nisn' => $row['nisn'],
                        'pertemuan' => [],
                        'total_hadir' => 0,
                        'total_izin' => 0,
                        'total_sakit' => 0,
                        'total_alpha' => 0
                    ];
                }
                // Simpan detail pertemuan
                $structured_data[$id_siswa]['pertemuan'][$id_jurnal] = [
                    'tanggal' => $row['tanggal'],
                    'pertemuan_ke' => $row['pertemuan_ke'],
                    'topik_materi' => $row['topik_materi'],
                    'status' => $row['status_kehadiran'],
                    'waktu_absen' => $row['waktu_absen'],
                    'keterangan' => $row['keterangan']
                ];
                // Hitung total per status
                switch ($row['status_kehadiran']) {
                    case 'H':
                        $structured_data[$id_siswa]['total_hadir']++;
                        break;
                    case 'I':
                        $structured_data[$id_siswa]['total_izin']++;
                        break;
                    case 'S':
                        $structured_data[$id_siswa]['total_sakit']++;
                        break;
                    default:
                        $structured_data[$id_siswa]['total_alpha']++;
                        break;
                }
                // Simpan daftar pertemuan untuk header tabel
                if (!isset($pertemuan_list[$id_jurnal])) {
                    $pertemuan_list[$id_jurnal] = [
                        'tanggal' => $row['tanggal'],
                        'pertemuan_ke' => $row['pertemuan_ke'],
                        'topik_materi' => $row['topik_materi']
                    ];
                }
            }
            // Sort pertemuan by tanggal
            uasort($pertemuan_list, function ($a, $b) {
                return strtotime($a['tanggal']) - strtotime($b['tanggal']);
            });
            return [
                'siswa_data' => array_values($structured_data),
                'pertemuan_headers' => array_values($pertemuan_list)
            ];
        } catch (Exception $e) {
            error_log("Error in getRincianAbsenPerPertemuan: " . $e->getMessage());
            return [
                'siswa_data' => [],
                'pertemuan_headers' => []
            ];
        }
    }
    // =================================================================
    // LEGACY METHODS - MUNGKIN MASIH DIGUNAKAN
    // =================================================================
    public function rincianAbsen($id_mapel = null)
    {
        $this->data['judul'] = 'Rincian Absen per Pertemuan';
        $id_guru = $_SESSION['id_ref'] ?? null;
        $id_semester = $_SESSION['id_semester_aktif'] ?? null;
        // Parameter filter dari GET
        $periode = $_GET['periode'] ?? 'semester';
        $tanggal_mulai = $_GET['tanggal_mulai'] ?? '';
        $tanggal_akhir = $_GET['tanggal_akhir'] ?? '';
        $id_mapel_filter = $_GET['id_mapel'] ?? $id_mapel;
        $this->data['filter'] = [
            'periode' => $periode,
            'tanggal_mulai' => $tanggal_mulai,
            'tanggal_akhir' => $tanggal_akhir,
            'id_mapel' => $id_mapel_filter
        ];
        // Ambil daftar mapel yang diajar guru
        $this->data['daftar_mapel'] = $this->getDaftarMapelGuru($id_guru, $id_semester);
        // Jika ada mapel yang dipilih, ambil rincian absen
        $this->data['rincian_data'] = [];
        $this->data['mapel_info'] = null;
        if ($id_mapel_filter) {
            $this->data['mapel_info'] = $this->getMapelInfo($id_guru, $id_semester, $id_mapel_filter);
            $this->data['rincian_data'] = $this->getRincianAbsenPerPertemuan($id_guru, $id_semester, $id_mapel_filter, $periode, $tanggal_mulai, $tanggal_akhir);
        }
        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_guru', $this->data);
        $this->view('guru/rincian_absen_filter', $this->data);
        $this->view('templates/footer', $this->data);
    }
    public function cetakRincianAbsen($id_mapel)
    {
        $id_guru = $_SESSION['id_ref'] ?? null;
        $id_semester = $_SESSION['id_semester_aktif'] ?? null;
        // Parameter filter
        $periode = $_GET['periode'] ?? 'semester';
        $tanggal_mulai = $_GET['tanggal_mulai'] ?? '';
        $tanggal_akhir = $_GET['tanggal_akhir'] ?? '';
        // Ambil data
        $this->data['mapel_info'] = $this->getMapelInfo($id_guru, $id_semester, $id_mapel);
        $this->data['rincian_data'] = $this->getRincianAbsenPerPertemuan($id_guru, $id_semester, $id_mapel, $periode, $tanggal_mulai, $tanggal_akhir);
        $this->data['filter_info'] = [
            'periode' => $periode,
            'tanggal_mulai' => $tanggal_mulai,
            'tanggal_akhir' => $tanggal_akhir,
            'tanggal_cetak' => date('d F Y')
        ];
        // Render view
        $wantPdf = isset($_GET['pdf']) && $_GET['pdf'] == 1;
        $renderView = function ($view, $data) {
            extract($data);
            ob_start();
            require __DIR__ . "/../views/$view.php";
            return ob_get_clean();
        };
        $html = $renderView('guru/cetak_rincian_absen', $this->data);
        if ($wantPdf) {
            // Setup Dompdf (sama seperti method cetakMapel)
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
                $dompdf->setPaper('A4', 'landscape');
                $dompdf->render();
                $mapel_name = $this->data['mapel_info']['nama_mapel'] ?? 'Mapel';
                $filename = 'Rincian_Absen_' . preg_replace('/\s+/', '_', $mapel_name) . '_' . date('Y-m-d') . '.pdf';
                $dompdf->stream($filename, ['Attachment' => true]);
                return;
            } catch (Exception $e) {
                header('Content-Type: text/html; charset=utf-8');
                echo "<div style='padding:20px;color:#ef4444;'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
                echo $html;
                return;
            }
        }
        // Tampilkan halaman cetak HTML
        header('Content-Type: text/html; charset=utf-8');
        echo $html;
    }
    /**
     * Detail Riwayat Jurnal per Mapel untuk Admin - FIXED SQL
     */
    public function detailRiwayatAdmin($id_guru, $id_mapel)
    {
        $this->data['judul'] = 'Detail Riwayat Jurnal Admin';
        $id_semester_aktif = $_SESSION['id_semester_aktif'] ?? null;
        $this->data['detail_jurnal'] = [];
        $this->data['detail_absensi_siswa'] = [];
        $this->data['nama_mapel'] = 'Mapel Tidak Ditemukan';
        $this->data['nama_guru'] = 'Guru Tidak Ditemukan';
        if ($id_guru && $id_semester_aktif && $id_mapel) {
            // Ambil detail jurnal menggunakan method yang sudah ada di model
            $this->data['detail_jurnal'] =
                $this->model('Jurnal_model')->getDetailRiwayatByMapel($id_guru, $id_semester_aktif, $id_mapel);
            // Ambil detail absensi per siswa
            $this->data['detail_absensi_siswa'] =
                $this->model('Jurnal_model')->getDetailAbsensiPerMapel($id_guru, $id_semester_aktif, $id_mapel);
            // Set nama guru dan mapel dari data yang diambil
            if (!empty($this->data['detail_jurnal'])) {
                $this->data['nama_mapel'] = $this->data['detail_jurnal'][0]['nama_mapel'] ?? 'Mapel';
                $this->data['nama_guru'] = $this->data['detail_jurnal'][0]['nama_guru'] ?? 'Guru';
            } else {
                // Fallback: ambil nama dari master data
                $guruInfo = $this->model('Guru_model')->getGuruById($id_guru);
                $mapelInfo = $this->model('Mapel_model')->getMapelById($id_mapel);
                if (!empty($guruInfo['nama_guru'])) {
                    $this->data['nama_guru'] = $guruInfo['nama_guru'];
                }
                if (!empty($mapelInfo['nama_mapel'])) {
                    $this->data['nama_mapel'] = $mapelInfo['nama_mapel'];
                }
            }
        }
        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/detail_riwayat_admin', $this->data);
        $this->view('templates/footer', $this->data);
    }
    // --- Helper aman untuk ambil daftar guru dari berbagai versi model
    private function _getGuruListSafe()
    {
        $m = $this->model('Guru_model');
        if (method_exists($m, 'getAll'))
            return $m->getAll();
        if (method_exists($m, 'getAllGuru'))
            return $m->getAllGuru();
        if (method_exists($m, 'getGuru'))
            return $m->getGuru();
        if (method_exists($m, 'getAllData'))
            return $m->getAllData();
        if (method_exists($m, 'all'))
            return $m->all();
        // fallback super aman (silakan sesuaikan nama tabel kolom)
        if (property_exists($m, 'db')) {
            $m->db->query("SELECT id_guru, nama_guru FROM guru ORDER BY nama_guru ASC");
            return $m->db->resultSet();
        }
        return [];
    }

    // ================= RPP REVIEW =====================
    public function listRPPReview()
    {
        $rppModel = $this->model('RPP_model');

        // Get filter from query string
        $status = $_GET['status'] ?? null;
        $id_semester = $_SESSION['id_semester_aktif'] ?? null;
        $id_tp = $_SESSION['id_tp_aktif'] ?? null;

        // Get all RPP with filter
        $list_rpp = $rppModel->getAllRPP($id_tp, $id_semester, $status);

        // Count by status for tabs
        $count_submitted = count(array_filter($list_rpp, fn($r) => $r['status'] === 'submitted'));
        $count_approved = count(array_filter($list_rpp, fn($r) => $r['status'] === 'approved'));
        $count_revision = count(array_filter($list_rpp, fn($r) => $r['status'] === 'revision'));
        $count_draft = count(array_filter($list_rpp, fn($r) => $r['status'] === 'draft'));

        $this->data['judul'] = 'Review RPP';
        $this->data['list_rpp'] = $list_rpp;
        $this->data['current_status'] = $status;
        $this->data['count_submitted'] = $count_submitted;
        $this->data['count_approved'] = $count_approved;
        $this->data['count_revision'] = $count_revision;
        $this->data['count_draft'] = $count_draft;

        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/list_rpp_review', $this->data);
        $this->view('templates/footer', $this->data);
    }

    public function detailRPPReview($id_rpp = null)
    {
        if (!$id_rpp) {
            Flasher::setFlash('ID RPP tidak valid!', 'danger');
            header('Location: ' . BASEURL . '/admin/listRPPReview');
            exit;
        }
        $rppModel = $this->model('RPP_model');
        $rpp = $rppModel->getRPPById($id_rpp);
        if (!$rpp) {
            Flasher::setFlash('RPP tidak ditemukan!', 'danger');
            header('Location: ' . BASEURL . '/admin/listRPPReview');
            exit;
        }

        // Load template sections and fields for dynamic display
        $templateModel = $this->model('RPPTemplate_model');
        $sectionsList = $templateModel->getAllSections(true);
        $sections = [];

        foreach ($sectionsList as $section) {
            $section['fields'] = $templateModel->getFieldsBySection($section['id_section'], true);
            $sections[] = $section;
        }

        $this->data['judul'] = 'Detail RPP';
        $this->data['rpp'] = $rpp;
        $this->data['sections'] = $sections;
        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/detail_rpp_review', $this->data);
        $this->view('templates/footer', $this->data);
    }

    public function approveRPP($id_rpp = null)
    {
        if (!$id_rpp) {
            Flasher::setFlash('ID RPP tidak valid!', 'danger');
            header('Location: ' . BASEURL . '/admin/listRPPReview');
            exit;
        }
        $rppModel = $this->model('RPP_model');
        $rppModel->approveRPP($id_rpp, $_SESSION['user_id']);
        Flasher::setFlash('RPP berhasil diapprove!', 'success');
        header('Location: ' . BASEURL . '/admin/listRPPReview');
        exit;
    }

    public function revisionRPP($id_rpp = null)
    {
        if (!$id_rpp) {
            Flasher::setFlash('ID RPP tidak valid!', 'danger');
            header('Location: ' . BASEURL . '/admin/listRPPReview');
            exit;
        }
        $catatan = $_POST['catatan'] ?? '';
        $rppModel = $this->model('RPP_model');
        $rppModel->revisionRPP($id_rpp, $catatan, $_SESSION['user_id']);
        Flasher::setFlash('RPP dikembalikan untuk revisi.', 'warning');
        header('Location: ' . BASEURL . '/admin/listRPPReview');
        exit;
    }

    // --- Helper aman untuk ambil daftar mapel
    private function _getMapelListSafe()
    {
        $m = $this->model('Mapel_model');
        if (method_exists($m, 'getAll'))
            return $m->getAll();
        if (method_exists($m, 'getAllMapel'))
            return $m->getAllMapel();
        if (method_exists($m, 'getMapel'))
            return $m->getMapel();
        if (method_exists($m, 'getAllData'))
            return $m->getAllData();
        if (method_exists($m, 'all'))
            return $m->all();
        // fallback super aman (silakan sesuaikan)
        if (property_exists($m, 'db')) {
            $m->db->query("SELECT id_mapel, nama_mapel FROM mapel ORDER BY nama_mapel ASC");
            return $m->db->resultSet();
        }
        return [];
    }
    // =================================================================
    // IMPORT SISWA EXCEL - SESUAI DATABASE SCHEMA YANG ADA
    // =================================================================
    /**
     * Halaman import siswa dari Excel
     */
    public function importSiswa()
    {
        $this->data['judul'] = 'Import Data Siswa Excel';
        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/import_siswa', $this->data);
        $this->view('templates/footer', $this->data);
    }
    /**
     * Proses import siswa dari Excel via AJAX
     */
    public function prosesImportSiswa()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method tidak diizinkan']);
            exit;
        }
        // Baca input JSON
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || !isset($input['data'])) {
            echo json_encode(['success' => false, 'message' => 'Data tidak valid']);
            exit;
        }
        $excelData = $input['data'];
        if (empty($excelData)) {
            echo json_encode(['success' => false, 'message' => 'Tidak ada data untuk diimport']);
            exit;
        }
        // Validasi dan proses import
        $result = $this->processImportData($excelData);
        echo json_encode($result);
        exit;
    }
    /**
     * Proses validasi dan import data siswa
     */
    private function processImportData($excelData)
    {
        $inserted = 0;
        $updated = 0;
        $skipped = 0;
        $errorCount = 0;
        $errors = [];
        $currentBatchNisn = [];
        $siswaModel = $this->model('Siswa_model');
        try {
            foreach ($excelData as $index => $row) {
                $rowNum = $index + 1;
                $rowErrors = [];
                // Sanitize data
                // Normalisasi kolom agar kompatibel dengan variasi header
                // Terima baik 'nisn' atau 'NISN' dsb.
                $rowNorm = [
                    'nisn' => $row['nisn'] ?? ($row['NISN'] ?? ''),
                    'nama_siswa' => $row['nama_siswa'] ?? ($row['Nama Siswa'] ?? ''),
                    'jenis_kelamin' => $row['jenis_kelamin'] ?? ($row['Jenis Kelamin'] ?? ''),
                    'password' => $row['password'] ?? ($row['Password'] ?? ''),
                    'tgl_lahir' => $row['tgl_lahir'] ?? ($row['Tanggal Lahir'] ?? null),
                    'tempat_lahir' => $row['tempat_lahir'] ?? ($row['Tempat Lahir'] ?? ''),
                    'alamat' => $row['alamat'] ?? ($row['Alamat'] ?? ''),
                    'no_wa' => $row['no_wa'] ?? ($row['No WA'] ?? ($row['No WhatsApp'] ?? '')),
                    'email' => $row['email'] ?? ($row['Email'] ?? ''),
                    'ayah_kandung' => $row['ayah_kandung'] ?? ($row['Ayah Kandung'] ?? ''),
                    'ibu_kandung' => $row['ibu_kandung'] ?? ($row['Ibu Kandung'] ?? '')
                ];

                $rawNisn = trim((string) $rowNorm['nisn']);
                // Hilangkan semua karakter non-digit, tetapi simpan leading zeros dengan hanya ambil digit
                $normalizedNisn = preg_replace('/[^0-9]/', '', $rawNisn);
                // Jika hasil kosong tapi raw berisi karakter aneh (seperti "+ " atau simbol), anggap error
                $cleanData = [
                    'nisn' => $normalizedNisn,
                    'nama_siswa' => trim((string) $rowNorm['nama_siswa']),
                    'jenis_kelamin' => strtoupper(trim((string) $rowNorm['jenis_kelamin'])),
                    'password' => trim((string) $rowNorm['password']),
                    'tgl_lahir' => !empty($rowNorm['tgl_lahir']) ? $rowNorm['tgl_lahir'] : null,
                    'tempat_lahir' => trim((string) $rowNorm['tempat_lahir']),
                    'alamat' => trim((string) $rowNorm['alamat']),
                    'no_wa' => trim((string) $rowNorm['no_wa']),
                    'email' => trim((string) $rowNorm['email']),
                    'ayah_kandung' => trim((string) $rowNorm['ayah_kandung']),
                    'ibu_kandung' => trim((string) $rowNorm['ibu_kandung'])
                ];
                // Validasi NISN
                if (empty($rawNisn)) {
                    $rowErrors[] = "Baris {$rowNum}: NISN tidak boleh kosong";
                } elseif (empty($cleanData['nisn'])) {
                    $rowErrors[] = "Baris {$rowNum}: NISN berisi karakter tidak valid";
                } elseif (strlen($cleanData['nisn']) < 6) {
                    // Jadikan rekomendasi saja, tetap diproses
                    // (Jika ingin strict, kembalikan menjadi error)
                } elseif (in_array($cleanData['nisn'], $currentBatchNisn)) {
                    $rowErrors[] = "Baris {$rowNum}: NISN {$cleanData['nisn']} duplikat dalam file";
                } else {
                    $currentBatchNisn[] = $cleanData['nisn'];
                }
                // Validasi Nama
                if (empty($cleanData['nama_siswa'])) {
                    $rowErrors[] = "Baris {$rowNum}: Nama siswa tidak boleh kosong";
                } elseif (strlen($cleanData['nama_siswa']) < 2) {
                    $rowErrors[] = "Baris {$rowNum}: Nama siswa minimal 2 karakter";
                }
                // Validasi Jenis Kelamin
                if (empty($cleanData['jenis_kelamin'])) {
                    $rowErrors[] = "Baris {$rowNum}: Jenis kelamin tidak boleh kosong";
                } else {
                    $jk = strtoupper($cleanData['jenis_kelamin']);
                    // Hilangkan tanda petik/simbol HTML dan ambil huruf pertama saja
                    $jk = preg_replace('/[^A-Z]/', '', $jk);
                    $jkFirst = substr($jk, 0, 1);
                    if (in_array($jk, ['LAKILAKI', 'LAKI', 'MALE']) || $jkFirst === 'L' || $jkFirst === 'M') {
                        $cleanData['jenis_kelamin'] = 'L';
                    } elseif (in_array($jk, ['PEREMPUAN', 'WANITA', 'FEMALE']) || $jkFirst === 'P' || $jkFirst === 'F') {
                        $cleanData['jenis_kelamin'] = 'P';
                    } else {
                        // Fallback: set default 'L' jika tidak dapat dikenali
                        $cleanData['jenis_kelamin'] = 'L';
                    }
                }
                // Validasi Password: kosong/pendek -> default 'siswa123'
                if (empty($cleanData['password']) || strlen($cleanData['password']) < 6) {
                    $cleanData['password'] = 'siswa123';
                }
                // Jika valid, proses insert/update/abaikan
                if (empty($rowErrors)) {
                    $existing = $siswaModel->getSiswaByNisn($cleanData['nisn']);
                    if ($existing) {
                        // Bandingkan data: skip HANYA jika SEMUA field sama persis
                        $namaSame = trim($existing['nama_siswa']) === trim($cleanData['nama_siswa']);
                        $jkSame = strtoupper(trim($existing['jenis_kelamin'])) === strtoupper(trim($cleanData['jenis_kelamin']));
                        $tglSame = trim($existing['tgl_lahir'] ?? '') === trim($cleanData['tgl_lahir'] ?? '');
                        $tempatSame = trim($existing['tempat_lahir'] ?? '') === trim($cleanData['tempat_lahir'] ?? '');
                        $alamatSame = trim($existing['alamat'] ?? '') === trim($cleanData['alamat'] ?? '');
                        $noWaSame = trim($existing['no_wa'] ?? '') === trim($cleanData['no_wa'] ?? '');
                        $emailSame = trim($existing['email'] ?? '') === trim($cleanData['email'] ?? '');
                        $ayahSame = trim($existing['ayah_kandung'] ?? '') === trim($cleanData['ayah_kandung'] ?? '');
                        $ibuSame = trim($existing['ibu_kandung'] ?? '') === trim($cleanData['ibu_kandung'] ?? '');
                        $existingPwd = trim($existing['password_plain'] ?? '');
                        $uploadedPwd = trim($cleanData['password'] ?? '');
                        $passSame = ($existingPwd === $uploadedPwd);
                        // Jika existing kosong dan upload kosong/default, kita ingin set default -> jangan dianggap sama
                        $needDefaultPwdForExisting = ($existingPwd === '' && ($uploadedPwd === '' || $uploadedPwd === 'siswa123'));
                        $isSame = $namaSame && $jkSame && $tglSame && $tempatSame && $alamatSame && $noWaSame && $emailSame && $ayahSame && $ibuSame && $passSame && !$needDefaultPwdForExisting;

                        // Log untuk debug
                        error_log("NISN {$cleanData['nisn']}: namaSame=$namaSame, passSame=$passSame, isSame=$isSame");

                        if ($isSame) {
                            $skipped++;
                        } else {
                            // Update data siswa
                            $updateData = [
                                'id_siswa' => $existing['id_siswa'],
                                'nisn' => $cleanData['nisn'],
                                'nama_siswa' => $cleanData['nama_siswa'],
                                'jenis_kelamin' => $cleanData['jenis_kelamin'],
                                'tgl_lahir' => $cleanData['tgl_lahir'],
                                'tempat_lahir' => $cleanData['tempat_lahir'],
                                'alamat' => $cleanData['alamat'],
                                'no_wa' => $cleanData['no_wa'],
                                'email' => $cleanData['email'],
                                'ayah_kandung' => $cleanData['ayah_kandung'],
                                'ibu_kandung' => $cleanData['ibu_kandung']
                            ];
                            $rowsAffected = $siswaModel->updateDataSiswa($updateData);
                            error_log("Update siswa NISN {$cleanData['nisn']}: {$rowsAffected} rows affected");

                            // Sinkronkan akun user siswa: update nama + password bila berbeda / perlu default
                            $userModel = $this->model('User_model');
                            if (!$passSame || $needDefaultPwdForExisting) {
                                $hashedPassword = password_hash($cleanData['password'], PASSWORD_DEFAULT);
                                $userModel->updateUserBySiswaId($existing['id_siswa'], [
                                    'password' => $hashedPassword,
                                    'password_plain' => $cleanData['password'],
                                    'nama_lengkap' => $cleanData['nama_siswa']
                                ]);
                            } else {
                                $userModel->updateUserBySiswaId($existing['id_siswa'], [
                                    'nama_lengkap' => $cleanData['nama_siswa']
                                ]);
                            }

                            $updated++;
                        }
                    } else {
                        // Insert baru
                        // Pastikan password default bila kosong/pendek
                        if (empty($cleanData['password']) || strlen($cleanData['password']) < 6) {
                            $cleanData['password'] = 'siswa123';
                        }
                        $idBaru = $siswaModel->tambahDataSiswa($cleanData);
                        // Buat akun user siswa via model users
                        $userModel = $this->model('User_model');
                        $userModel->buatAkun([
                            'username' => $cleanData['nisn'],
                            'password' => $cleanData['password'],
                            'nama_lengkap' => $cleanData['nama_siswa'],
                            'role' => 'siswa',
                            'id_ref' => $idBaru
                        ]);
                        $inserted++;
                    }
                } else {
                    $errorCount++;
                    $errors = array_merge($errors, $rowErrors);
                }
            }
            return [
                'success' => true,
                'inserted' => $inserted,
                'updated' => $updated,
                'skipped' => $skipped,
                'error_count' => $errorCount,
                'total_processed' => count($excelData),
                'errors' => $errors,
                'message' => "Import selesai. Ditambah: {$inserted}, Diupdate: {$updated}, Diabaikan: {$skipped}, Error: {$errorCount}."
            ];
        } catch (Exception $e) {
            error_log("processImportData error: " . $e->getMessage());
            return [
                'success' => false,
                'inserted' => $inserted,
                'updated' => $updated,
                'skipped' => $skipped,
                'error_count' => $errorCount,
                'total_processed' => count($excelData),
                'errors' => ["Error sistem: " . $e->getMessage()],
                'message' => 'Import gagal karena error sistem'
            ];
        }
    }
    /**
     * Insert siswa beserta akun dalam satu transaksi
     */
    private function insertSiswaWithAccount($data)
    {
        try {
            // Insert siswa terlebih dahulu
            $idSiswaBaru = $this->model('Siswa_model')->tambahDataSiswa($data);
            if ($idSiswaBaru) {
                // Buat akun user
                $dataAkun = [
                    'username' => $data['nisn'],
                    'password' => $data['password'],
                    'nama_lengkap' => $data['nama_siswa'],
                    'role' => 'siswa',
                    'id_ref' => $idSiswaBaru
                ];
                $userModel = $this->model('User_model');
                $akunId = $userModel->buatAkun($dataAkun);
                if ($akunId) {
                    return ['success' => true, 'siswa_id' => $idSiswaBaru, 'user_id' => $akunId];
                } else {
                    // Rollback siswa jika gagal buat akun
                    $this->model('Siswa_model')->hapusDataSiswa($idSiswaBaru);
                    return ['success' => false, 'error' => 'Gagal membuat akun untuk ' . $data['nama_siswa']];
                }
            } else {
                return ['success' => false, 'error' => 'Gagal menyimpan data siswa ' . $data['nama_siswa']];
            }
        } catch (Exception $e) {
            error_log("insertSiswaWithAccount error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Database error: ' . $e->getMessage()];
        }
    }
    /**
     * Ambil semua NISN yang sudah ada di database
     */
    private function getExistingNisn()
    {
        try {
            $siswaModel = $this->model('Siswa_model');
            $allSiswa = $siswaModel->getAllSiswa();
            return array_column($allSiswa, 'nisn');
        } catch (Exception $e) {
            error_log("getExistingNisn error: " . $e->getMessage());
            return [];
        }
    }
    /**
     * Download template Excel untuk import siswa
     */
    public function downloadTemplateSiswa()
    {
        // Legacy HTML XLS (ke belakang kompatibel). Tetap dipertahankan sebagai opsi,
        // namun disarankan gunakan tombol "Download XLSX (Data Siswa)" di halaman import.
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename="Template_Import_Siswa.xls"');
        header('Cache-Control: max-age=0');
        echo "<html><head><meta charset='UTF-8'></head><body>";
        echo "<table border='1' cellspacing='0' cellpadding='4'>";
        echo "<tr style='background:#e2e8f0;font-weight:bold'><td>NISN</td><td>Nama Siswa</td><td>Jenis Kelamin</td><td>Password</td></tr>";
        $siswaList = $this->model('Siswa_model')->getAllSiswa();
        if (!empty($siswaList)) {
            foreach ($siswaList as $siswa) {
                $nisn = htmlspecialchars($siswa['nisn']);
                $nama = htmlspecialchars($siswa['nama_siswa']);
                $jk = htmlspecialchars($siswa['jenis_kelamin']);
                $pwd = htmlspecialchars($siswa['password_plain'] ?? '');
                // Preserve leading zeros in Excel by forcing text format
                echo "<tr><td style='mso-number-format:\"@\"'>{$nisn}</td><td>{$nama}</td><td>{$jk}</td><td>{$pwd}</td></tr>";
            }
        } else {
            // Tambahkan contoh baris jika belum ada data
            echo "<tr><td style='mso-number-format:\"@\"'>0123456789</td><td>Contoh Satu</td><td>L</td><td>password123</td></tr>";
            echo "<tr><td style='mso-number-format:\"@\"'>0123456790</td><td>Contoh Dua</td><td>P</td><td>password456</td></tr>";
        }
        echo "</table>";
        // Keterangan
        echo "<br><table border='0' cellpadding='3'>";
        echo "<tr><td style='font-weight:bold'>KETERANGAN:</td></tr>";
        echo "<tr><td>NISN: Nomor Induk Siswa Nasional (angka). Gunakan yang sudah terdaftar bila ingin update.</td></tr>";
        echo "<tr><td>Jenis Kelamin: L (Laki-laki) atau P (Perempuan).</td></tr>";
        echo "<tr><td>Password: Minimal 6 karakter. Kosongkan jika tidak ingin mengubah password saat update.</td></tr>";
        echo "<tr><td>Baris dengan NISN yang sama dan data sama akan diabaikan ketika import.</td></tr>";
        echo "<tr><td><strong>PENTING:</strong> Jangan ubah format kolom NISN; leading zero dipertahankan dengan format teks.</td></tr>";
        echo "<tr><td>Jika data berbeda (nama/jk/tgl_lahir), sistem akan melakukan update.</td></tr>";
        echo "<tr><td>Tambahkan kolom Tanggal Lahir (tgl_lahir) manual jika diperlukan (format YYYY-MM-DD) sebelum import.</td></tr>";
        echo "</table>";
        echo "</body></html>";
        exit;
    }

    /**
     * Endpoint: data siswa dalam JSON untuk pembuatan XLSX di sisi klien
     */
    public function downloadDataSiswaJson()
    {
        try {
            $siswaList = $this->model('Siswa_model')->getAllSiswa();
            // Normalisasi kolom yang diperlukan untuk template
            $rows = [];
            foreach ($siswaList as $s) {
                $rows[] = [
                    'nisn' => (string) ($s['nisn'] ?? ''),
                    'nama_siswa' => (string) ($s['nama_siswa'] ?? ''),
                    'jenis_kelamin' => strtoupper((string) ($s['jenis_kelamin'] ?? '')),
                    'password' => (string) ($s['password_plain'] ?? '')
                ];
            }
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'count' => count($rows),
                'data' => $rows
            ]);
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Gagal mengambil data siswa',
                'error' => $e->getMessage()
            ]);
        }
        exit;
    }
    /**
     * Cek ketersediaan NISN via AJAX
     */
    public function cekNisnTersedia()
    {
        header('Content-Type: application/json');
        if (!isset($_GET['nisn'])) {
            echo json_encode(['available' => false, 'message' => 'NISN tidak diberikan']);
            exit;
        }
        $nisn = trim($_GET['nisn']);
        if (empty($nisn)) {
            echo json_encode(['available' => false, 'message' => 'NISN kosong']);
            exit;
        }
        // Cek di database menggunakan model
        $siswaModel = $this->model('Siswa_model');
        $exists = $siswaModel->cekNisnExists($nisn);
        if ($exists) {
            echo json_encode(['available' => false, 'message' => 'NISN sudah terdaftar']);
        } else {
            echo json_encode(['available' => true, 'message' => 'NISN tersedia']);
        }
        exit;
    }
    /**
     * Export data siswa ke Excel/CSV
     */
    public function exportSiswaExcel()
    {
        try {
            $kelasFilter = isset($_GET['kelas']) ? trim($_GET['kelas']) : '';
            $id_tp = $_SESSION['id_tp_aktif'] ?? 0;

            if (!empty($kelasFilter)) {
                // Get class ID by name
                $kelasData = $this->model('Kelas_model')->getKelasByName($kelasFilter, $id_tp);
                if ($kelasData) {
                    $dataSiswa = $this->model('Siswa_model')->getSiswaByKelas($kelasData['id_kelas'], $id_tp);
                    $classNameSlug = preg_replace('/[^A-Za-z0-9]+/', '_', $kelasFilter);
                    $filename = "Export_Data_Siswa_Kelas_" . $classNameSlug . "_" . date('Y-m-d') . ".csv";
                } else {
                    $dataSiswa = [];
                    $filename = "Export_Data_Siswa_" . date('Y-m-d') . ".csv";
                }
            } else {
                $dataSiswa = $this->model('Siswa_model')->getAllSiswa();
                $filename = "Export_Data_Siswa_Semua_" . date('Y-m-d') . ".csv";
            }

            // Set headers untuk download CSV
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: max-age=0');

            $output = fopen('php://output', 'w');

            // Add BOM untuk Excel UTF-8 support
            fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Header
            fputcsv($output, ['NISN', 'Nama Siswa', 'Kelas', 'Jenis Kelamin', 'Tanggal Lahir', 'Status', 'Password', 'No HP Ayah', 'No HP Ibu'], ';');

            // Data
            foreach ($dataSiswa as $siswa) {
                $row = [
                    $siswa['nisn'],
                    $siswa['nama_siswa'],
                    $siswa['nama_kelas'] ?? '-',
                    $siswa['jenis_kelamin'] === 'L' ? 'Laki-laki' : 'Perempuan',
                    $siswa['tgl_lahir'] ?? '',
                    $siswa['status_siswa'],
                    $siswa['password_plain'] ?? '',
                    $siswa['ayah_no_hp'] ?? '-',
                    $siswa['ibu_no_hp'] ?? '-'
                ];
                fputcsv($output, $row, ';');
            }

            fclose($output);
            exit;
        } catch (Exception $e) {
            error_log("Export error: " . $e->getMessage());
            header('Location: ' . BASEURL . '/admin/siswa?error=export_failed');
            exit;
        }
    }
    /**
     * Preview import data untuk validasi
     */
    public function previewImportSiswa()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method tidak diizinkan']);
            exit;
        }
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || !isset($input['data'])) {
            echo json_encode(['success' => false, 'message' => 'Data tidak valid']);
            exit;
        }
        $excelData = $input['data'];
        $validatedData = $this->validateExcelData($excelData);
        echo json_encode([
            'success' => true,
            'preview' => $validatedData,
            'summary' => [
                'total' => count($excelData),
                'valid' => $validatedData['valid_count'],
                'error' => $validatedData['error_count']
            ]
        ]);
        exit;
    }
    /**
     * Validasi data Excel sebelum import
     */
    private function validateExcelData($excelData)
    {
        $validData = [];
        $errors = [];
        $existingNisn = $this->getExistingNisn();
        $currentBatchNisn = [];
        foreach ($excelData as $index => $row) {
            $rowErrors = [];
            $rowNum = $index + 1;
            // Sanitize data
            $cleanData = [
                'nisn' => trim($row['nisn'] ?? ''),
                'nama_siswa' => trim($row['nama_siswa'] ?? ''),
                'jenis_kelamin' => strtoupper(trim($row['jenis_kelamin'] ?? '')),
                'password' => trim($row['password'] ?? ''),
                'tgl_lahir' => !empty($row['tgl_lahir']) ? $row['tgl_lahir'] : null
            ];
            // Validasi NISN
            if (empty($cleanData['nisn'])) {
                $rowErrors[] = "NISN tidak boleh kosong";
            } elseif (!preg_match('/^\d+$/', $cleanData['nisn'])) {
                $rowErrors[] = "NISN harus berisi angka";
            } elseif (in_array($cleanData['nisn'], $existingNisn)) {
                $rowErrors[] = "NISN sudah terdaftar";
            } elseif (in_array($cleanData['nisn'], $currentBatchNisn)) {
                $rowErrors[] = "NISN duplikat dalam file";
            } else {
                $currentBatchNisn[] = $cleanData['nisn'];
            }
            // Validasi Nama
            if (empty($cleanData['nama_siswa'])) {
                $rowErrors[] = "Nama siswa tidak boleh kosong";
            } elseif (strlen($cleanData['nama_siswa']) < 2) {
                $rowErrors[] = "Nama siswa minimal 2 karakter";
            }
            // Validasi Jenis Kelamin
            if (empty($cleanData['jenis_kelamin'])) {
                $rowErrors[] = "Jenis kelamin tidak boleh kosong";
            } else {
                $jk = strtoupper($cleanData['jenis_kelamin']);
                if (in_array($jk, ['L', 'LAKI-LAKI', 'LAKI', 'M', 'MALE'])) {
                    $cleanData['jenis_kelamin'] = 'L';
                } elseif (in_array($jk, ['P', 'PEREMPUAN', 'WANITA', 'F', 'FEMALE'])) {
                    $cleanData['jenis_kelamin'] = 'P';
                } else {
                    $rowErrors[] = "Jenis kelamin harus L atau P";
                }
            }
            // Validasi Password
            if (empty($cleanData['password'])) {
                $rowErrors[] = "Password tidak boleh kosong";
            } elseif (strlen($cleanData['password']) < 6) {
                $rowErrors[] = "Password minimal 6 karakter";
            }
            if (empty($rowErrors)) {
                $validData[] = $cleanData;
            } else {
                $errors[] = "Baris {$rowNum}: " . implode(', ', $rowErrors);
            }
        }
        return [
            'valid_data' => $validData,
            'valid_count' => count($validData),
            'error_count' => count($errors),
            'errors' => $errors
        ];
    }
    // =================================================================
    // UPDATE METHOD SISWA YANG SUDAH ADA - TAMBAH FLASH MESSAGE
    // =================================================================
    // =================================================================
    // UTILITY METHODS UNTUK IMPORT
    // =================================================================
    /**
     * Generate batch ID untuk tracking import
     */
    private function generateBatchId()
    {
        return 'IMP_' . date('YmdHis') . '_' . uniqid();
    }
    /**
     * Log import activity (simple version tanpa tabel khusus)
     */
    private function logImportActivity($action, $details)
    {
        try {
            // Log ke file jika tidak ada tabel activity_log
            $logMessage = date('Y-m-d H:i:s') . " - User: " . ($_SESSION['username'] ?? 'unknown') .
                " - Action: {$action} - Details: " . json_encode($details) . "
";
            $logFile = APPROOT . '/logs/import_activity.log';
            $logDir = dirname($logFile);
            if (!is_dir($logDir)) {
                mkdir($logDir, 0755, true);
            }
            file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
            return true;
        } catch (Exception $e) {
            error_log("logImportActivity error: " . $e->getMessage());
            return false;
        }
    }
    /**
     * Cleanup import logs lama
     */
    public function cleanupImportLogs()
    {
        try {
            $logFile = APPROOT . '/logs/import_activity.log';
            if (file_exists($logFile) && filesize($logFile) > 10 * 1024 * 1024) { // 10MB
                // Backup dan truncate log file
                $backupFile = $logFile . '.' . date('Y-m-d-H-i-s') . '.backup';
                copy($logFile, $backupFile);
                file_put_contents($logFile, '');
            }
            return true;
        } catch (Exception $e) {
            error_log("cleanupImportLogs error: " . $e->getMessage());
            return false;
        }
    }
    /**
     * Validasi format file upload
     */
    private function validateUploadedFile($file)
    {
        $allowedTypes = [
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'text/csv'
        ];
        $allowedExtensions = ['xls', 'xlsx', 'csv'];
        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($fileExtension, $allowedExtensions)) {
            return ['valid' => false, 'error' => 'Format file tidak didukung. Gunakan .xlsx, .xls, atau .csv'];
        }
        if ($file['size'] > 5 * 1024 * 1024) { // 5MB max
            return ['valid' => false, 'error' => 'Ukuran file terlalu besar. Maksimal 5MB'];
        }
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['valid' => false, 'error' => 'Error upload file: ' . $file['error']];
        }
        return ['valid' => true, 'error' => null];
    }
    /**
     * Process upload file dan return data
     */
    public function processUploadedExcel()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method tidak diizinkan']);
            exit;
        }
        if (!isset($_FILES['excel_file'])) {
            echo json_encode(['success' => false, 'message' => 'File tidak ditemukan']);
            exit;
        }
        $file = $_FILES['excel_file'];
        // Validasi file
        $validation = $this->validateUploadedFile($file);
        if (!$validation['valid']) {
            echo json_encode(['success' => false, 'message' => $validation['error']]);
            exit;
        }
        try {
            // Process file berdasarkan extension
            $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if ($fileExtension === 'csv') {
                $data = $this->processCSVFile($file['tmp_name']);
            } else {
                // Untuk .xls/.xlsx, perlu library tambahan atau convert ke CSV dulu
                $data = $this->processExcelFile($file['tmp_name']);
            }
            echo json_encode([
                'success' => true,
                'data' => $data,
                'filename' => $file['name'],
                'message' => count($data) . ' baris data berhasil dibaca'
            ]);
        } catch (Exception $e) {
            error_log("processUploadedExcel error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error memproses file: ' . $e->getMessage()]);
        }
        exit;
    }
    /**
     * Process CSV file
     */
    private function processCSVFile($filePath)
    {
        $data = [];
        if (($handle = fopen($filePath, "r")) !== FALSE) {
            $isFirstRow = true;
            while (($row = fgetcsv($handle, 1000, ";")) !== FALSE) {
                // Skip header row
                if ($isFirstRow) {
                    $isFirstRow = false;
                    continue;
                }
                // Skip empty rows
                if (empty(array_filter($row))) {
                    continue;
                }
                // Map ke struktur yang diharapkan
                $data[] = [
                    'nisn' => $row[0] ?? '',
                    'nama_siswa' => $row[1] ?? '',
                    'jenis_kelamin' => $row[2] ?? '',
                    'password' => $row[3] ?? '',
                    'tgl_lahir' => $row[4] ?? null
                ];
            }
            fclose($handle);
        }
        return $data;
    }
    /**
     * Process Excel file (basic implementation)
     */
    private function processExcelFile($filePath)
    {
        // Untuk implementasi sederhana, convert Excel ke CSV dulu
        // Atau gunakan library PHP Excel seperti PhpSpreadsheet
        // Implementasi fallback: return empty atau error
        throw new Exception("Excel file processing memerlukan library tambahan. Gunakan format CSV untuk sementara.");
    }
    // =================================================================
    // BATCH OPERATIONS
    // =================================================================
    /**
     * Batch delete siswa (untuk cleanup import yang error)
     */
    public function batchDeleteSiswa()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method tidak diizinkan']);
            exit;
        }
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || !isset($input['ids']) || !is_array($input['ids'])) {
            echo json_encode(['success' => false, 'message' => 'Data ID tidak valid']);
            exit;
        }
        $ids = array_filter($input['ids'], 'is_numeric');
        if (empty($ids)) {
            echo json_encode(['success' => false, 'message' => 'Tidak ada ID yang valid']);
            exit;
        }
        try {
            $deletedCount = 0;
            $userModel = $this->model('User_model');
            $siswaModel = $this->model('Siswa_model');
            foreach ($ids as $id) {
                // Hapus akun user terlebih dahulu
                $userModel->hapusAkun($id, 'siswa');
                // Hapus data siswa
                if ($siswaModel->hapusDataSiswa($id) > 0) {
                    $deletedCount++;
                }
            }
            echo json_encode([
                'success' => true,
                'message' => "{$deletedCount} siswa berhasil dihapus",
                'deleted_count' => $deletedCount
            ]);
        } catch (Exception $e) {
            error_log("Batch delete error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
        exit;
    }
    /**
     * Generate password otomatis untuk siswa yang belum punya
     */
    public function generatePasswordSiswa()
    {
        header('Content-Type: application/json');
        try {
            $siswaModel = $this->model('Siswa_model');
            $userModel = $this->model('User_model');
            // Ambil siswa yang belum punya password
            $siswaList = $siswaModel->getAllSiswa();
            $siswaWithoutPassword = array_filter($siswaList, function ($siswa) {
                return empty($siswa['password_plain']);
            });
            $updatedCount = 0;
            foreach ($siswaWithoutPassword as $siswa) {
                // Generate password: 3 digit terakhir NISN + nama depan
                $password = $this->generateSimplePassword($siswa['nisn'], $siswa['nama_siswa']);
                // Update atau buat akun
                $existingUser = $userModel->getUserByIdRef($siswa['id_siswa'], 'siswa');
                if ($existingUser) {
                    // Update password existing user
                    if ($userModel->updatePassword($siswa['id_siswa'], 'siswa', $password)) {
                        $updatedCount++;
                    }
                } else {
                    // Buat akun baru
                    $dataAkun = [
                        'username' => $siswa['nisn'],
                        'password' => $password,
                        'nama_lengkap' => $siswa['nama_siswa'],
                        'role' => 'siswa',
                        'id_ref' => $siswa['id_siswa']
                    ];
                    if ($userModel->buatAkun($dataAkun)) {
                        $updatedCount++;
                    }
                }
            }
            echo json_encode([
                'success' => true,
                'message' => "Password berhasil digenerate untuk {$updatedCount} siswa",
                'updated_count' => $updatedCount
            ]);
        } catch (Exception $e) {
            error_log("generatePasswordSiswa error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
        exit;
    }
    /**
     * Generate password sederhana
     */
    private function generateSimplePassword($nisn, $nama)
    {
        $lastDigits = substr($nisn, -3);
        $namePrefix = strtolower(substr(preg_replace('/[^a-zA-Z]/', '', $nama), 0, 3));
        return $lastDigits . $namePrefix;
    }
    // =================================================================
    // CETAK LAPORAN REKAP ADMIN - METHOD BARU
    // =================================================================
    /**
     * Cetak Laporan Rekap Absensi untuk Admin
     * Format sederhana sesuai template PDF
     */
    public function cetakLaporanRekap()
    {
        // Ambil parameter dari GET
        $id_kelas = $_GET['id_kelas'] ?? null;
        $id_mapel = $_GET['id_mapel'] ?? null;
        $periode = $_GET['periode'] ?? 'semester';
        $tanggal_mulai = $_GET['tanggal_mulai'] ?? null;
        $tanggal_akhir = $_GET['tanggal_akhir'] ?? null;
        $mode = $_GET['mode'] ?? 'rekap';
        $isPdfMode = isset($_GET['pdf']) && $_GET['pdf'] == '1';
        $id_semester_aktif = $_SESSION['id_semester_aktif'] ?? 0;
        // Validasi input minimal
        if (empty($id_kelas)) {
            echo "<div style='padding:20px;color:#ef4444;'>Error: Kelas harus dipilih</div>";
            return;
        }
        // Ambil info kelas
        $kelasModel = $this->model('Kelas_model');
        $this->data['kelas_info'] = $kelasModel->getKelasById($id_kelas);
        // Ambil info mapel jika dipilih
        $this->data['mapel_info'] = null;
        $this->data['guru_info'] = null;
        if (!empty($id_mapel)) {
            $mapelModel = $this->model('Mapel_model');
            $this->data['mapel_info'] = $mapelModel->getMapelById($id_mapel);
            // Ambil guru yang mengajar mapel di kelas ini
            $penugasanModel = $this->model('Penugasan_model');
            $guru_pengampu = $penugasanModel->getGuruByMapelKelas($id_mapel, $id_kelas, $id_semester_aktif);
            if (!empty($guru_pengampu)) {
                $this->data['guru_info'] = $guru_pengampu;
            }
        }
        // Ambil info semester dan TP
        $semesterModel = $this->model('TahunPelajaran_model');
        $this->data['semester_info'] = $semesterModel->getSemesterById($id_semester_aktif);
        $this->data['tp_info'] = null;
        if (isset($this->data['semester_info']['id_tp'])) {
            $this->data['tp_info'] = $semesterModel->getTahunPelajaranById($this->data['semester_info']['id_tp']);
        }
        // Setup filter info untuk template
        $this->data['filter_info'] = [
            'periode' => $periode,
            'tanggal_mulai' => $tanggal_mulai,
            'tanggal_akhir' => $tanggal_akhir,
            'tanggal_cetak' => date('d F Y')
        ];
        // Ambil data rekap absensi
        $filter = [
            'id_kelas' => $id_kelas,
            'id_mapel' => $id_mapel,
            'periode' => $periode,
            'tgl_mulai' => $tanggal_mulai,
            'tgl_selesai' => $tanggal_akhir,
            'id_semester' => $id_semester_aktif
        ];
        $laporanModel = $this->model('Laporan_model');
        $this->data['rekap_absensi'] = $laporanModel->getRekapAbsensiPerKelas($filter);
        // Jika mode rincian dan ada mapel spesifik, ambil data rincian
        if ($mode === 'rincian' && !empty($id_mapel) && !empty($this->data['guru_info']['id_guru'])) {
            $this->data['rincian_data'] = $this->getRincianAbsenAdmin(
                $id_semester_aktif,
                $id_mapel,
                $this->data['guru_info']['id_guru'],
                $periode,
                $tanggal_mulai,
                $tanggal_akhir
            );
        }
        // Render view dengan template cetak
        $renderView = function ($view, $data) {
            extract($data);
            ob_start();
            require __DIR__ . "/../views/$view.php";
            return ob_get_clean();
        };
        // Gunakan template cetak admin
        $html = $renderView('admin/cetak_laporan_rekap', $this->data);
        if ($isPdfMode) {
            // Setup Dompdf untuk PDF
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
                $kelas_name = $this->data['kelas_info']['nama_kelas'] ?? 'Kelas';
                $mapel_name = $this->data['mapel_info']['nama_mapel'] ?? 'Semua_Mapel';
                $filename = 'Laporan_Kehadiran_' . preg_replace('/\s+/', '_', $kelas_name) . '_' . preg_replace('/\s+/', '_', $mapel_name) . '_' . date('Y-m-d') . '.pdf';
                $dompdf->stream($filename, ['Attachment' => true]);
                return;
            } catch (Exception $e) {
                header('Content-Type: text/html; charset=utf-8');
                echo "<div style='padding:20px;color:#ef4444;'>Error PDF: " . htmlspecialchars($e->getMessage()) . "</div>";
                echo $html;
                return;
            }
        }
        // Tampilkan halaman cetak HTML
        header('Content-Type: text/html; charset=utf-8');
        echo $html;
    }

    /**
     * Konfigurasi QR Code
     */
    public function configQR()
    {
        $this->data['judul'] = 'Konfigurasi QR Code';

        // Load config file
        $configFile = __DIR__ . '/../../config/qrcode.php';
        $config = [];

        // Load settings from DB for synchronization
        $pengaturanDb = $this->model('PengaturanAplikasi_model')->getPengaturan();
        $dbUrl = $pengaturanDb['url_web'] ?? '';

        if (file_exists($configFile)) {
            include $configFile;
            $config = [
                'QR_API_PROVIDER' => defined('QR_API_PROVIDER') ? QR_API_PROVIDER : 'qrserver',
                'QR_CUSTOM_URL' => defined('QR_CUSTOM_URL') ? QR_CUSTOM_URL : '',
                // Prioritize DB URL if available, otherwise use config file value
                'QR_WEBSITE_URL' => !empty($dbUrl) ? $dbUrl : (defined('QR_WEBSITE_URL') ? QR_WEBSITE_URL : 'http://localhost/absen'),
                'QR_SIZE' => defined('QR_SIZE') ? QR_SIZE : '200x200',
                'QR_DISPLAY_SIZE' => defined('QR_DISPLAY_SIZE') ? str_replace('px', '', QR_DISPLAY_SIZE) : '60',
                'QR_TOKEN_EXPIRY' => defined('QR_TOKEN_EXPIRY') ? QR_TOKEN_EXPIRY : '365',
                'QR_POSITION' => defined('QR_POSITION') ? QR_POSITION : 'bottom-left',
                'QR_DISPLAY_TEXT' => defined('QR_DISPLAY_TEXT') ? QR_DISPLAY_TEXT : 'Scan untuk validasi',
                'QR_TOKEN_SALT' => defined('QR_TOKEN_SALT') ? QR_TOKEN_SALT : 'rapor_2024_secret_key'
            ];
        }

        $this->data['config'] = $config;

        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/config_qr', $this->data);
        $this->view('templates/footer');
    }

    /**
     * Simpan Konfigurasi QR Code
     */
    public function simpanConfigQR()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/admin/configQR');
            exit;
        }

        $provider = $_POST['qr_provider'] ?? 'qrserver';
        $customUrl = $_POST['qr_custom_url'] ?? '';
        $websiteUrl = $_POST['qr_website_url'] ?? 'http://localhost/absen';
        $size = $_POST['qr_size'] ?? '200x200';
        $displaySize = $_POST['qr_display_size'] ?? '60';
        $tokenExpiry = $_POST['qr_token_expiry'] ?? '365';
        $position = $_POST['qr_position'] ?? 'bottom-left';
        $displayText = $_POST['qr_display_text'] ?? 'Scan untuk validasi';
        $tokenSalt = $_POST['qr_token_salt'] ?? 'rapor_2024_secret_key';

        // Sync Website URL to Database (Pengaturan Aplikasi)
        try {
            $pengaturanModel = $this->model('PengaturanAplikasi_model');
            $currentSettings = $pengaturanModel->getPengaturan();

            // Only update if URL changed
            if (($currentSettings['url_web'] ?? '') !== $websiteUrl) {
                $currentSettings['url_web'] = $websiteUrl;
                // Pastikan key yang dibutuhkan model tersedia
                if (!isset($currentSettings['nama_aplikasi']))
                    $currentSettings['nama_aplikasi'] = 'Smart Absensi';
                // Simpan update
                $pengaturanModel->simpan($currentSettings);
            }
        } catch (Exception $e) {
            // Ignore error sync, focus on saving config file
        }

        // Generate config file content
        $configContent = "<?php\n\n";
        $configContent .= "/**\n * QR Code Configuration\n * Auto-generated on " . date('Y-m-d H:i:s') . "\n */\n\n";
        $configContent .= "define('QR_API_PROVIDER', '{$provider}');\n";
        $configContent .= "define('QR_API_QRSERVER', 'https://api.qrserver.com/v1/create-qr-code/');\n";
        $configContent .= "define('QR_CUSTOM_URL', '{$customUrl}');\n";
        // Logic URL Dinamis
        $configContent .= "// Set default URL\n";
        $configContent .= "if (!defined('QR_WEBSITE_URL')) {\n";
        $configContent .= "    \$qrBaseUrl = '';\n";
        $configContent .= "    \n";
        $configContent .= "    // Coba ambil dari database jika helper tersedia\n";
        $configContent .= "    if (function_exists('getPengaturanAplikasi')) {\n";
        $configContent .= "        \$appSettings = getPengaturanAplikasi();\n";
        $configContent .= "        if (!empty(\$appSettings['url_web'])) {\n";
        $configContent .= "            \$qrBaseUrl = \$appSettings['url_web'];\n";
        $configContent .= "        }\n";
        $configContent .= "    }\n";
        $configContent .= "    \n";
        $configContent .= "    // Pastikan ada trailing slash\n";
        $configContent .= "    define('QR_WEBSITE_URL', rtrim(\$qrBaseUrl, '/') . '/');\n";
        $configContent .= "}\n";
        $configContent .= "define('QR_SIZE', '{$size}');\n";
        $configContent .= "define('QR_DISPLAY_SIZE', '{$displaySize}px');\n";
        $configContent .= "define('QR_TOKEN_EXPIRY', {$tokenExpiry});\n";
        $configContent .= "define('QR_POSITION', '{$position}');\n";
        $configContent .= "define('QR_DISPLAY_TEXT', '" . addslashes($displayText) . "');\n";
        $configContent .= "define('QR_TOKEN_SALT', '" . addslashes($tokenSalt) . "');\n\n";

        // Add helper functions
        $configContent .= "function getQRCodeApiUrl(\$data) {\n";
        $configContent .= "    \$encodedData = urlencode(\$data);\n";
        $configContent .= "    // Parse size dari format \"250x250\" ke integer untuk provider yang membutuhkan\n";
        $configContent .= "    \$sizeInt = (int)explode('x', QR_SIZE)[0];\n";
        $configContent .= "    \n";
        $configContent .= "    switch (QR_API_PROVIDER) {\n";
        $configContent .= "        case 'qrserver':\n";
        $configContent .= "            return QR_API_QRSERVER . '?size=' . QR_SIZE . '&data=' . \$encodedData;\n";
        $configContent .= "        case 'quickchart':\n";
        $configContent .= "            return 'https://quickchart.io/qr?text=' . \$encodedData . '&size=' . \$sizeInt;\n";
        $configContent .= "        case 'goqr':\n";
        $configContent .= "            return 'https://api.qrserver.com/v1/create-qr-code/?size=' . QR_SIZE . '&data=' . \$encodedData;\n";
        $configContent .= "        case 'custom':\n";
        $configContent .= "            return str_replace(['{DATA}', '{SIZE}'], [\$encodedData, QR_SIZE], QR_CUSTOM_URL);\n";
        $configContent .= "        default:\n";
        $configContent .= "            return QR_API_QRSERVER . '?size=' . QR_SIZE . '&data=' . \$encodedData;\n";
        $configContent .= "    }\n";
        $configContent .= "}\n\n";
        $configContent .= "function generateQRToken(\$siswaId, \$jenisRapor, \$nisn) {\n";
        $configContent .= "    \$data = \$siswaId . '|' . \$jenisRapor . '|' . \$nisn . '|' . QR_TOKEN_SALT;\n";
        $configContent .= "    return hash('sha256', \$data);\n";
        $configContent .= "}\n\n";

        // Add generatePDFQRCode function
        $configContent .= "/**\n";
        $configContent .= " * Generate PDF QR Code with validation token\n";
        $configContent .= " * @param string \$docType Document type (rapor, pembayaran, absensi, performa_guru, performa_siswa, etc)\n";
        $configContent .= " * @param mixed \$docId Document identifier\n";
        $configContent .= " * @param array \$additionalData Extra metadata for validation\n";
        $configContent .= " * @return string Base64 QR code image data URL\n";
        $configContent .= " */\n";
        $configContent .= "function generatePDFQRCode(\$docType, \$docId, \$additionalData = []) {\n";
        $configContent .= "    try {\n";
        $configContent .= "        // Create validation token\n";
        $configContent .= "        \$tokenData = [\n";
        $configContent .= "            'doc_type' => \$docType,\n";
        $configContent .= "            'doc_id' => \$docId,\n";
        $configContent .= "            'timestamp' => time(),\n";
        $configContent .= "            'expires' => time() + (QR_TOKEN_EXPIRY * 24 * 60 * 60)\n";
        $configContent .= "        ];\n";
        $configContent .= "        \n";
        $configContent .= "        // Merge additional data\n";
        $configContent .= "        if (!empty(\$additionalData)) {\n";
        $configContent .= "            \$tokenData = array_merge(\$tokenData, \$additionalData);\n";
        $configContent .= "        }\n";
        $configContent .= "        \n";
        $configContent .= "        // Create secure token\n";
        $configContent .= "        \$token = hash_hmac('sha256', json_encode(\$tokenData), QR_TOKEN_SALT);\n";
        $configContent .= "        \n";
        $configContent .= "        // Save token to database for validation\n";
        $configContent .= "        try {\n";
        $configContent .= "            \$APPROOT = realpath(__DIR__ . '/..');\n";
        $configContent .= "            require_once \$APPROOT . '/config/database.php';\n";
        $configContent .= "            require_once \$APPROOT . '/app/core/Database.php';\n";
        $configContent .= "            require_once \$APPROOT . '/app/models/QRValidation_model.php';\n";
        $configContent .= "            \$qrModel = new QRValidation_model();\n";
        $configContent .= "            \$qrModel->ensureTables(); // Create table if not exists\n";
        $configContent .= "            \n";
        $configContent .= "            // Store token with correct parameters\n";
        $configContent .= "            \$expiryDays = QR_TOKEN_EXPIRY > 0 ? (int)QR_TOKEN_EXPIRY : 0;\n";
        $configContent .= "            \$identifier = \$docId; // Use doc ID as identifier\n";
        $configContent .= "            \n";
        $configContent .= "            // Save token using storeToken method\n";
        $configContent .= "            \$qrModel->storeToken(\$docType, \$docId, \$identifier, \$token, \$expiryDays, \$additionalData);\n";
        $configContent .= "        } catch (Exception \$e) {\n";
        $configContent .= "            error_log('Failed to save QR token to database: ' . \$e->getMessage());\n";
        $configContent .= "            // Continue anyway - QR will still be generated\n";
        $configContent .= "        }\n";
        $configContent .= "        \n";
        $configContent .= "        // Create validation URL\n";
        $configContent .= "        \$validationUrl = QR_WEBSITE_URL . '/validate?token=' . \$token . '&type=' . urlencode(\$docType);\n";
        $configContent .= "        \n";
        $configContent .= "        // Get QR code image from API\n";
        $configContent .= "        \$qrApiUrl = getQRCodeApiUrl(\$validationUrl);\n";
        $configContent .= "        \n";
        $configContent .= "        // Fetch QR code image\n";
        $configContent .= "        \$qrImageData = @file_get_contents(\$qrApiUrl);\n";
        $configContent .= "        \n";
        $configContent .= "        if (\$qrImageData === false) {\n";
        $configContent .= "            error_log('Failed to generate QR code from API: ' . \$qrApiUrl);\n";
        $configContent .= "            return '';\n";
        $configContent .= "        }\n";
        $configContent .= "        \n";
        $configContent .= "        // Convert to base64 data URL\n";
        $configContent .= "        \$base64 = base64_encode(\$qrImageData);\n";
        $configContent .= "        return 'data:image/png;base64,' . \$base64;\n";
        $configContent .= "        \n";
        $configContent .= "    } catch (Exception \$e) {\n";
        $configContent .= "        error_log('QR code generation error: ' . \$e->getMessage());\n";
        $configContent .= "        return '';\n";
        $configContent .= "    }\n";
        $configContent .= "}\n\n";

        // Add getQRCodeHTML function
        $configContent .= "function getQRCodeHTML(\$qrCodeDataUrl) {\n";
        $configContent .= "    \$position = QR_POSITION;\n";
        $configContent .= "    \$displaySize = QR_DISPLAY_SIZE;\n";
        $configContent .= "    \$displayText = QR_DISPLAY_TEXT;\n";
        $configContent .= "    \n";
        $configContent .= "    // Position styles - menggunakan absolute agar hanya di halaman terakhir\n";
        $configContent .= "    \$positionStyles = [\n";
        $configContent .= "        'bottom-right' => 'bottom: 5mm; right: 5mm;',\n";
        $configContent .= "        'bottom-left' => 'bottom: 5mm; left: 5mm;',\n";
        $configContent .= "        'top-right' => 'top: 5mm; right: 5mm;',\n";
        $configContent .= "        'top-left' => 'top: 5mm; left: 5mm;',\n";
        $configContent .= "    ];\n";
        $configContent .= "    \n";
        $configContent .= "    \$style = \$positionStyles[\$position] ?? \$positionStyles['bottom-right'];\n";
        $configContent .= "    \n";
        $configContent .= "    // Gunakan position absolute dan taruh di akhir document\n";
        $configContent .= "    \$html = '<div style=\"position: absolute; ' . \$style . ' text-align: center; background: white; padding: 5px; border: 1px solid #ddd; border-radius: 3px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);\">';\n";
        $configContent .= "    \$html .= '<img src=\"' . htmlspecialchars(\$qrCodeDataUrl) . '\" style=\"width: ' . \$displaySize . '; height: ' . \$displaySize . '; display: block;\" alt=\"QR Code\">';\n";
        $configContent .= "    if (!empty(\$displayText)) {\n";
        $configContent .= "        \$html .= '<div style=\"font-size: 7px; color: #666; margin-top: 2px;\">' . htmlspecialchars(\$displayText) . '</div>';\n";
        $configContent .= "    }\n";
        $configContent .= "    \$html .= '</div>';\n";
        $configContent .= "    \n";
        $configContent .= "    return \$html;\n";
        $configContent .= "}\n";

        // Save to file
        $configFile = __DIR__ . '/../../config/qrcode.php';
        $result = file_put_contents($configFile, $configContent);

        if ($result !== false) {
            Flasher::setFlash('Konfigurasi QR Code berhasil disimpan!', 'success');
        } else {
            Flasher::setFlash('Gagal menyimpan konfigurasi QR Code!', 'danger');
        }

        header('Location: ' . BASEURL . '/admin/configQR');
        exit;
    }

    // =================================================================
    // HELPER: CLEAR CACHE
    // =================================================================
    private function clearDashboardCache()
    {
        // Clear semua cache dashboard
        unset($_SESSION['admin_dashboard_stats']);
        unset($_SESSION['admin_dashboard_stats_time']);
        unset($_SESSION['admin_daftar_semester']);
        unset($_SESSION['admin_daftar_semester_time']);
    }

    // Method untuk manual clear cache (bisa dipanggil dari menu)
    public function clearCache()
    {
        $this->clearDashboardCache();
        Flasher::setFlash('Cache berhasil dibersihkan!', 'success');
        header('Location: ' . BASEURL . '/admin/dashboard');
        exit;
    }

    /**
     * Test QR Code Generation (AJAX)
     */
    public function testQRCode()
    {
        header('Content-Type: application/json');

        // Load config
        require_once __DIR__ . '/../../config/qrcode.php';

        try {
            $testUrl = BASEURL . '/test-validation';
            $qrApiUrl = getQRCodeApiUrl($testUrl);

            // Download QR Code
            $imageData = @file_get_contents($qrApiUrl);

            if ($imageData !== false) {
                $base64 = 'data:image/png;base64,' . base64_encode($imageData);
                echo json_encode([
                    'success' => true,
                    'qr_code' => $base64,
                    'api_url' => $qrApiUrl
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Tidak dapat mengakses API QR Code'
                ]);
            }
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    // =================================================================
    // PENGATURAN MENU - ENABLE/DISABLE MENU
    // =================================================================
    public function pengaturanMenu()
    {
        $this->data['judul'] = 'Pengaturan Menu';

        // Baca dari database via PengaturanSistem_model
        $pengaturanModel = $this->model('PengaturanSistem_model');
        $settings = $pengaturanModel->getAll();

        $this->data['menu_input_nilai_enabled'] = ($settings['menu_input_nilai_enabled'] ?? '1') == '1';
        $this->data['menu_pembayaran_enabled'] = ($settings['menu_pembayaran_enabled'] ?? '1') == '1';

        // Notifikasi WA Settings
        $this->data['wa_notif_absensi_enabled'] = ($settings['wa_notif_absensi_enabled'] ?? '1') == '1';
        $this->data['wa_notif_pembayaran_enabled'] = ($settings['wa_notif_pembayaran_enabled'] ?? '1') == '1';

        $this->data['google_oauth_enabled'] = ($settings['google_oauth_enabled'] ?? '0') == '1';
        $this->data['google_client_id'] = $settings['google_client_id'] ?? '';
        $this->data['google_client_secret'] = $settings['google_client_secret'] ?? '';
        $this->data['google_allowed_domain'] = $settings['google_allowed_domain'] ?? '';

        // Google Drive connection status
        $googleDriveConnected = false;
        $googleDriveFolderId = '';
        $googleDriveEmail = '';
        try {
            $db = new Database();
            $db->query("SELECT google_refresh_token, google_drive_folder_id, google_drive_email FROM pengaturan_aplikasi LIMIT 1");
            $driveSettings = $db->single();
            if ($driveSettings) {
                $googleDriveConnected = !empty($driveSettings['google_refresh_token']);
                $googleDriveFolderId = $driveSettings['google_drive_folder_id'] ?? '';
                $googleDriveEmail = $driveSettings['google_drive_email'] ?? '';
            }
        } catch (Exception $e) {
            // Kolom belum ada, abaikan
        }
        $this->data['google_drive_connected'] = $googleDriveConnected;
        $this->data['google_drive_folder_id'] = $googleDriveFolderId;
        $this->data['google_drive_email'] = $googleDriveEmail;

        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/pengaturan_menu', $this->data);
        $this->view('templates/footer', $this->data);
    }

    public function simpanPengaturanMenu()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/admin/pengaturanMenu');
            exit;
        }

        try {
            $pengaturanModel = $this->model('PengaturanSistem_model');

            $settings = [
                'menu_input_nilai_enabled' => isset($_POST['menu_input_nilai']) ? '1' : '0',
                'menu_pembayaran_enabled' => isset($_POST['menu_pembayaran']) ? '1' : '0',
                'menu_rapor_enabled' => isset($_POST['menu_input_nilai']) ? '1' : '0',

                // WA Notification Settings
                'wa_notif_absensi_enabled' => isset($_POST['wa_notif_absensi_enabled']) ? '1' : '0',
                'wa_notif_pembayaran_enabled' => isset($_POST['wa_notif_pembayaran_enabled']) ? '1' : '0',

                'google_oauth_enabled' => isset($_POST['google_oauth_enabled']) ? '1' : '0',
                'google_client_id' => trim($_POST['google_client_id'] ?? ''),
                'google_client_secret' => trim($_POST['google_client_secret'] ?? ''),
                'google_allowed_domain' => trim($_POST['google_allowed_domain'] ?? ''),
            ];

            $pengaturanModel->updateMultiple($settings);

            // Simpan Google Drive Folder ID ke tabel pengaturan_aplikasi jika ada
            $googleDriveFolderId = trim($_POST['google_drive_folder_id'] ?? '');
            if (!empty($googleDriveFolderId)) {
                try {
                    $db = new Database();
                    $db->query("UPDATE pengaturan_aplikasi SET google_drive_folder_id = :folder_id");
                    $db->bind(':folder_id', $googleDriveFolderId);
                    $db->execute();
                } catch (Exception $dbError) {
                    error_log('GoogleDrive folder save: ' . $dbError->getMessage());
                }
            }

            Flasher::setFlash('Pengaturan berhasil disimpan.', 'success');
        } catch (Exception $e) {
            error_log('Error simpanPengaturanMenu: ' . $e->getMessage());
            Flasher::setFlash('Gagal menyimpan pengaturan: ' . $e->getMessage(), 'danger');
        }

        header('Location: ' . BASEURL . '/admin/pengaturanMenu');
        exit;
    }

    // =================================================================
    // GOOGLE DRIVE INTEGRATION
    // =================================================================

    /**
     * Redirect ke Google OAuth untuk menghubungkan Drive
     */
    public function connectGoogleDrive()
    {
        require_once APPROOT . '/app/core/GoogleDrive.php';
        $drive = new GoogleDrive();

        $redirectUri = BASEURL . '/admin/googleDriveCallback';
        $authUrl = $drive->getAuthUrl($redirectUri);

        header('Location: ' . $authUrl);
        exit;
    }

    /**
     * Handle OAuth callback dari Google Drive
     */
    public function googleDriveCallback()
    {
        $code = $_GET['code'] ?? null;

        if (!$code) {
            Flasher::setFlash('Gagal menghubungkan Google Drive: kode tidak ditemukan.', 'danger');
            header('Location: ' . BASEURL . '/admin/pengaturanMenu');
            exit;
        }

        try {
            require_once APPROOT . '/app/core/GoogleDrive.php';
            $drive = new GoogleDrive();

            $redirectUri = BASEURL . '/admin/googleDriveCallback';
            $tokens = $drive->exchangeCodeForTokens($code, $redirectUri);

            if (isset($tokens['refresh_token'])) {
                // Get user info (email) dan simpan ke database
                $userInfo = $drive->getUserInfo();
                if ($userInfo && isset($userInfo['email'])) {
                    $drive->saveEmail($userInfo['email']);
                }

                // Otomatis buat folder root untuk aplikasi
                $pengaturanModel = $this->model('PengaturanAplikasi_model');
                $pengaturan = $pengaturanModel->getPengaturan();
                $namaAplikasi = $pengaturan['nama_aplikasi'] ?? 'Absensi Sekolah';
                $folderName = $namaAplikasi . ' - Dokumen Siswa';

                // Buat atau cari folder dengan nama tersebut
                $folder = $drive->findOrCreateFolder($folderName);

                if ($folder && isset($folder['id'])) {
                    $drive->saveFolderId($folder['id']);
                    // Set folder menjadi public agar subfolder juga bisa diakses
                    $drive->setPublic($folder['id']);
                    $emailMsg = $userInfo['email'] ?? '';
                    Flasher::setFlash('Google Drive berhasil terhubung dengan ' . $emailMsg . '! Folder "' . $folderName . '" sudah dibuat.', 'success');
                } else {
                    Flasher::setFlash('Google Drive terhubung, tapi gagal membuat folder otomatis. Silakan buat folder manual.', 'warning');
                }
            } else {
                Flasher::setFlash('Terhubung tapi tidak mendapat refresh token. Coba putuskan dulu lalu hubungkan kembali.', 'warning');
            }

        } catch (Exception $e) {
            error_log('GoogleDrive callback error: ' . $e->getMessage());
            Flasher::setFlash('Gagal menghubungkan Google Drive: ' . $e->getMessage(), 'danger');
        }

        header('Location: ' . BASEURL . '/admin/pengaturanMenu');
        exit;
    }

    /**
     * Putuskan koneksi Google Drive
     */
    public function disconnectGoogleDrive()
    {
        try {
            require_once APPROOT . '/app/core/GoogleDrive.php';
            $drive = new GoogleDrive();
            $drive->disconnect();
            Flasher::setFlash('Koneksi Google Drive berhasil diputus.', 'success');
        } catch (Exception $e) {
            error_log('GoogleDrive disconnect error: ' . $e->getMessage());
            Flasher::setFlash('Gagal memutus koneksi: ' . $e->getMessage(), 'danger');
        }

        header('Location: ' . BASEURL . '/admin/pengaturanMenu');
        exit;
    }

    // =================================================================
    // PENGATURAN ROLE / FUNGSI GURU
    // =================================================================

    /**
     * Halaman pengaturan role/fungsi tambahan guru (bendahara, petugas_psb, dll)
     */
    public function pengaturanRole()
    {
        $this->data['judul'] = 'Pengaturan Fungsi Guru';
        $id_tp_aktif = $_SESSION['id_tp_aktif'] ?? 0;

        $guruFungsiModel = $this->model('GuruFungsi_model');

        $this->data['guru_list'] = $guruFungsiModel->getGuruWithFungsi($id_tp_aktif);
        $this->data['fungsi_tersedia'] = GuruFungsi_model::getAvailableFungsi();
        $this->data['id_tp_aktif'] = $id_tp_aktif;
        $this->data['nama_tp'] = $_SESSION['nama_tp_aktif'] ?? '';

        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/pengaturan_role', $this->data);
        $this->view('templates/footer');
    }

    /**
     * Simpan pengaturan fungsi guru
     */
    public function savePengaturanRole()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/admin/pengaturanRole');
            exit;
        }

        $id_tp_aktif = $_SESSION['id_tp_aktif'] ?? 0;
        $guruFungsiModel = $this->model('GuruFungsi_model');
        $fungsiTersedia = array_keys(GuruFungsi_model::getAvailableFungsi());

        // Process each guru
        $guruList = $_POST['guru'] ?? [];

        foreach ($guruList as $id_guru => $fungsiSelected) {
            // Validate fungsi
            $validFungsi = array_intersect($fungsiSelected, $fungsiTersedia);
            $guruFungsiModel->updateFungsiGuru($id_guru, $validFungsi, $id_tp_aktif, $_SESSION['user_id'] ?? null);
        }

        // Also process guru with no functions selected (remove all)
        $allGuruIds = array_column($this->model('GuruFungsi_model')->getAllGuru(), 'id_guru');
        $submittedGuruIds = array_keys($guruList);
        $unselectedGuruIds = array_diff($allGuruIds, $submittedGuruIds);

        foreach ($unselectedGuruIds as $id_guru) {
            $guruFungsiModel->updateFungsiGuru($id_guru, [], $id_tp_aktif, $_SESSION['user_id'] ?? null);
        }

        Flasher::setFlash('Pengaturan fungsi guru berhasil disimpan.', 'success');
        header('Location: ' . BASEURL . '/admin/pengaturanRole');
        exit;
    }

    // =================================================================
    // PROFIL & GANTI SANDI ADMIN
    // =================================================================

    public function profil()
    {
        $this->data['judul'] = 'Profil Admin';
        $id_user = $_SESSION['user_id'] ?? 0;

        if (!$id_user) {
            header('Location: ' . BASEURL . '/admin/dashboard');
            exit;
        }

        try {
            $db = new Database();
            $db->query("SELECT * FROM users WHERE id_user = :id_user AND role = 'admin' LIMIT 1");
            $db->bind('id_user', $id_user);
            $admin = $db->single();

            if (!$admin) {
                Flasher::setFlash('Data admin tidak ditemukan.', 'danger');
                header('Location: ' . BASEURL . '/admin/dashboard');
                exit;
            }

            $this->data['admin'] = $admin;
        } catch (Exception $e) {
            error_log('Error profil admin: ' . $e->getMessage());
            Flasher::setFlash('Terjadi kesalahan saat memuat profil.', 'danger');
            header('Location: ' . BASEURL . '/admin/dashboard');
            exit;
        }

        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/profil', $this->data);
        $this->view('templates/footer', $this->data);
    }

    public function simpanProfil()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/admin/profil');
            exit;
        }

        $id_user = $_SESSION['user_id'] ?? 0;
        if (!$id_user) {
            header('Location: ' . BASEURL . '/admin/profil');
            exit;
        }

        $username = trim($_POST['username'] ?? '');
        $nama_lengkap = trim($_POST['nama_lengkap'] ?? '');

        if (empty($username)) {
            Flasher::setFlash('Username tidak boleh kosong.', 'danger');
            header('Location: ' . BASEURL . '/admin/profil');
            exit;
        }

        try {
            $db = new Database();

            // Cek apakah username sudah digunakan oleh user lain
            $db->query("SELECT id_user FROM users WHERE username = :username AND id_user != :id_user LIMIT 1");
            $db->bind('username', $username);
            $db->bind('id_user', $id_user);
            $exists = $db->single();

            if ($exists) {
                Flasher::setFlash('Username sudah digunakan oleh user lain.', 'danger');
                header('Location: ' . BASEURL . '/admin/profil');
                exit;
            }

            // Update profil
            $db->query("UPDATE users SET username = :username, nama_lengkap = :nama_lengkap WHERE id_user = :id_user");
            $db->bind('username', $username);
            $db->bind('nama_lengkap', $nama_lengkap);
            $db->bind('id_user', $id_user);
            $db->execute();

            // Update session
            $_SESSION['username'] = $username;
            $_SESSION['nama_lengkap'] = $nama_lengkap;

            Flasher::setFlash('Profil berhasil diperbarui.', 'success');
        } catch (Exception $e) {
            error_log('Error simpanProfil admin: ' . $e->getMessage());
            Flasher::setFlash('Terjadi kesalahan saat menyimpan profil.', 'danger');
        }

        header('Location: ' . BASEURL . '/admin/profil');
        exit;
    }

    public function gantiSandi()
    {
        $this->data['judul'] = 'Ganti Sandi';

        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/ganti_sandi', $this->data);
        $this->view('templates/footer', $this->data);
    }

    public function simpanSandi()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/admin/gantiSandi');
            exit;
        }

        $id_user = $_SESSION['user_id'] ?? 0;
        if (!$id_user) {
            header('Location: ' . BASEURL . '/admin/gantiSandi');
            exit;
        }

        $password = trim($_POST['password'] ?? '');
        $password2 = trim($_POST['password2'] ?? '');

        if (empty($password) || empty($password2)) {
            Flasher::setFlash('Password dan konfirmasi wajib diisi.', 'danger');
            header('Location: ' . BASEURL . '/admin/gantiSandi');
            exit;
        }

        if ($password !== $password2) {
            Flasher::setFlash('Konfirmasi password tidak cocok.', 'danger');
            header('Location: ' . BASEURL . '/admin/gantiSandi');
            exit;
        }

        if (strlen($password) < 6) {
            Flasher::setFlash('Password minimal 6 karakter.', 'danger');
            header('Location: ' . BASEURL . '/admin/gantiSandi');
            exit;
        }

        try {
            $db = new Database();
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $db->query("UPDATE users SET password = :password WHERE id_user = :id_user");
            $db->bind('password', $hashedPassword);
            $db->bind('id_user', $id_user);
            $db->execute();

            Flasher::setFlash('Password berhasil diperbarui.', 'success');
        } catch (Exception $e) {
            error_log('Error simpanSandi admin: ' . $e->getMessage());
            Flasher::setFlash('Terjadi kesalahan saat menyimpan password.', 'danger');
        }

        header('Location: ' . BASEURL . '/admin/gantiSandi');
        exit;
    }

    // =================================================================
    // PEMBAYARAN - Admin dapat melihat semua pembayaran per kelas
    // =================================================================

    public function pembayaran()
    {
        $this->data['judul'] = 'Pembayaran Sekolah';

        $id_tp_aktif = $_SESSION['id_tp_aktif'] ?? 0;
        $id_semester_aktif = $_SESSION['id_semester_aktif'] ?? null;

        // Get all kelas dengan wali kelas
        $kelasModel = $this->model('Kelas_model');
        $waliKelasModel = $this->model('WaliKelas_model');
        $pembayaranModel = $this->model('Pembayaran_model');

        $kelasList = $kelasModel->getAllKelasWithDetails($id_tp_aktif);

        // Enrich dengan info wali kelas dan total tagihan
        foreach ($kelasList as &$kelas) {
            $waliKelas = $waliKelasModel->getWaliKelasByKelas($kelas['id_kelas'], $id_tp_aktif);
            $kelas['wali_kelas'] = $waliKelas;

            // Get total tagihan untuk kelas ini
            $tagihanList = $pembayaranModel->getTagihanKelas($kelas['id_kelas'], $id_tp_aktif, $id_semester_aktif);
            $kelas['total_tagihan'] = count($tagihanList);
        }

        $this->data['kelas_list'] = $kelasList;

        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/pembayaran', $this->data);
        $this->view('templates/footer');
    }

    public function pembayaranKelas($id_kelas)
    {
        $this->data['judul'] = 'Pembayaran Kelas';

        $id_tp_aktif = $_SESSION['id_tp_aktif'] ?? 0;
        $id_semester_aktif = $_SESSION['id_semester_aktif'] ?? null;

        // Get kelas info
        $kelas = $this->model('Kelas_model')->getKelasById($id_kelas);
        if (!$kelas) {
            header('Location: ' . BASEURL . '/admin/pembayaran');
            exit;
        }

        $this->data['kelas'] = $kelas;
        $this->data['tagihan_list'] = $this->model('Pembayaran_model')->getTagihanKelas($id_kelas, $id_tp_aktif, $id_semester_aktif);

        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/pembayaran_kelas', $this->data);
        $this->view('templates/footer');
    }

    public function pembayaranTagihan($id_tagihan)
    {
        $this->data['judul'] = 'Pembayaran Detail Tagihan';

        $id_tp_aktif = $_SESSION['id_tp_aktif'] ?? 0;

        // Get tagihan
        $tagihan = $this->model('Pembayaran_model')->getTagihanById($id_tagihan);
        if (!$tagihan) {
            header('Location: ' . BASEURL . '/admin/pembayaran');
            exit;
        }

        $this->data['tagihan'] = $tagihan;

        // Get kelas info
        $kelas = $this->model('Kelas_model')->getKelasById($tagihan['id_kelas']);
        $this->data['kelas'] = $kelas;

        // Get siswa list
        $this->data['siswa_list'] = $this->model('Siswa_model')->getSiswaByKelas($tagihan['id_kelas'], $id_tp_aktif);

        // Get tagihan siswa mapping
        $this->data['tagihan_siswa'] = $this->model('Pembayaran_model')->getTagihanSiswaList($id_tagihan);

        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/pembayaran_tagihan', $this->data);
        $this->view('templates/footer');
    }

    public function pembayaranRiwayat()
    {
        $this->data['judul'] = 'Pembayaran Riwayat Transaksi';

        $id_tp_aktif = $_SESSION['id_tp_aktif'] ?? 0;

        // Get all transactions across all classes
        $this->data['riwayat'] = $this->model('Pembayaran_model')->getRiwayatAll($id_tp_aktif, 500);

        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/pembayaran_riwayat', $this->data);
        $this->view('templates/footer');
    }

    // =================================================================
    // MONITORING NILAI (ADMIN) - Read-only overview like wali kelas
    // =================================================================
    public function monitoringNilai()
    {
        $this->data['judul'] = 'Monitoring Nilai';

        // Ambil semua kelas (atau filter by TP aktif jika diperlukan)
        $kelasModel = $this->model('Kelas_model');
        $waliKelasModel = $this->model('WaliKelas_model');
        $id_tp_aktif = $_SESSION['id_tp_aktif'] ?? 0;

        $kelasList = $kelasModel->getAllKelas();
        foreach ($kelasList as &$k) {
            $k['wali_kelas'] = $waliKelasModel->getWaliKelasByKelas($k['id_kelas'], $id_tp_aktif);
        }
        $this->data['kelas_list'] = $kelasList;

        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/monitoring_nilai', $this->data);
        $this->view('templates/footer');
    }

    public function monitoringNilaiKelas($id_kelas)
    {
        $this->data['judul'] = 'Monitoring Nilai';

        $kelas = $this->model('Kelas_model')->getKelasById($id_kelas);
        if (!$kelas) {
            header('Location: ' . BASEURL . '/admin/monitoringNilai');
            exit;
        }

        $this->data['id_kelas'] = (int) $id_kelas;
        $this->data['nama_kelas'] = $kelas['nama_kelas'] ?? '';
        $this->data['session_info'] = [
            'nama_semester' => $_SESSION['nama_semester_aktif'] ?? 'Semester Tidak Diketahui'
        ];

        // View khusus admin; menggunakan endpoint admin untuk detail nilai
        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/monitoring_nilai_kelas', $this->data);
        $this->view('templates/footer');
    }

    // AJAX: Get Nilai Siswa untuk Admin (butuh id_kelas eksplisit)
    public function getNilaiSiswa()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $idSiswa = $input['id_siswa'] ?? 0;
        $jenisNilai = $input['jenis_nilai'] ?? 'harian';
        $id_kelas = $input['id_kelas'] ?? 0;

        if (!$idSiswa || !$id_kelas) {
            echo json_encode(['status' => 'error', 'message' => 'Parameter tidak lengkap']);
            return;
        }

        try {
            $id_semester_aktif = $_SESSION['id_semester_aktif'] ?? 0;

            $nilaiModel = $this->model('Nilai_model');
            $penugasanModel = $this->model('Penugasan_model');

            // Ambil daftar mapel untuk kelas ini
            $mapelList = $penugasanModel->getMapelByKelas($id_kelas, $id_semester_aktif);

            $result = [
                'mapel' => [],
                'rata_rata' => null
            ];
            $totalNilai = 0;
            $jumlahMapel = 0;

            foreach ($mapelList as $mapel) {
                $nilai = null;
                if ($jenisNilai === 'harian') {
                    $nilaiHarian = $nilaiModel->getNilaiHarianByMapelSiswa($mapel['id_penugasan'], $idSiswa);
                    if (!empty($nilaiHarian)) {
                        $nilai = array_sum(array_column($nilaiHarian, 'nilai')) / count($nilaiHarian);
                    }
                } elseif ($jenisNilai === 'sts') {
                    $nilaiSTS = $nilaiModel->getNilaiByJenis($idSiswa, $mapel['id_guru'], $mapel['id_mapel'], $id_semester_aktif, 'sts');
                    $nilai = $nilaiSTS['nilai'] ?? null;
                } elseif ($jenisNilai === 'sas') {
                    $nilaiSAS = $nilaiModel->getNilaiByJenis($idSiswa, $mapel['id_guru'], $mapel['id_mapel'], $id_semester_aktif, 'sas');
                    $nilai = $nilaiSAS['nilai'] ?? null;
                }

                $result['mapel'][] = [
                    'nama_mapel' => $mapel['nama_mapel'],
                    'nilai' => $nilai !== null ? round($nilai, 2) : null
                ];

                if ($nilai !== null) {
                    $totalNilai += $nilai;
                    $jumlahMapel++;
                }
            }

            $result['rata_rata'] = $jumlahMapel > 0 ? round($totalNilai / $jumlahMapel, 2) : null;

            echo json_encode(['status' => 'success', 'data' => $result]);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    // ================= PENGATURAN TEMPLATE RPP =====================

    public function pengaturanRPP()
    {
        $templateModel = $this->model('RPPTemplate_model');
        $sections = $templateModel->getAllSections(false); // include inactive

        // Get fields grouped by section
        $fieldsBySection = [];
        foreach ($sections as $section) {
            $fieldsBySection[$section['id_section']] = $templateModel->getFieldsBySection($section['id_section'], false);
        }

        // Get pengaturan wajib RPP
        $pengaturanWajibRPP = $this->model('PengaturanRPP_model')->getPengaturan();

        $this->data['judul'] = 'Pengaturan Template RPP';
        $this->data['sections'] = $sections;
        $this->data['fields_by_section'] = $fieldsBySection;
        $this->data['pengaturan_wajib_rpp'] = $pengaturanWajibRPP;

        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/pengaturan_rpp', $this->data);
        $this->view('templates/footer', $this->data);
    }

    public function simpanSection()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/admin/pengaturanRPP');
            exit;
        }

        $templateModel = $this->model('RPPTemplate_model');
        $id_section = $_POST['id_section'] ?? null;

        $data = [
            'kode_section' => $_POST['kode_section'],
            'nama_section' => $_POST['nama_section'],
            'urutan' => $_POST['urutan'] ?? 0,
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];

        if ($id_section) {
            $templateModel->updateSection($id_section, $data);
            Flasher::setFlash('Section berhasil diupdate!', 'success');
        } else {
            $templateModel->tambahSection($data);
            Flasher::setFlash('Section berhasil ditambahkan!', 'success');
        }

        header('Location: ' . BASEURL . '/admin/pengaturanRPP');
        exit;
    }

    public function hapusSection($id_section = null)
    {
        if (!$id_section) {
            Flasher::setFlash('ID Section tidak valid!', 'danger');
            header('Location: ' . BASEURL . '/admin/pengaturanRPP');
            exit;
        }

        $templateModel = $this->model('RPPTemplate_model');
        $templateModel->hapusSection($id_section);
        Flasher::setFlash('Section berhasil dihapus!', 'success');
        header('Location: ' . BASEURL . '/admin/pengaturanRPP');
        exit;
    }

    public function toggleSection($id_section = null)
    {
        if (!$id_section) {
            Flasher::setFlash('ID Section tidak valid!', 'danger');
            header('Location: ' . BASEURL . '/admin/pengaturanRPP');
            exit;
        }

        $templateModel = $this->model('RPPTemplate_model');
        $templateModel->toggleSection($id_section);
        Flasher::setFlash('Status section berhasil diubah!', 'success');
        header('Location: ' . BASEURL . '/admin/pengaturanRPP');
        exit;
    }

    public function simpanField()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/admin/pengaturanRPP');
            exit;
        }

        $templateModel = $this->model('RPPTemplate_model');
        $id_field = $_POST['id_field'] ?? null;

        $data = [
            'id_section' => $_POST['id_section'],
            'nama_field' => $_POST['nama_field'],
            'kode_field' => $_POST['kode_field'],
            'tipe_input' => $_POST['tipe_input'] ?? 'textarea',
            'placeholder' => $_POST['placeholder'] ?? '',
            'urutan' => $_POST['urutan'] ?? 0,
            'is_required' => isset($_POST['is_required']) ? 1 : 0,
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];

        if ($id_field) {
            $templateModel->updateField($id_field, $data);
            Flasher::setFlash('Field berhasil diupdate!', 'success');
        } else {
            $templateModel->tambahField($data);
            Flasher::setFlash('Field berhasil ditambahkan!', 'success');
        }

        header('Location: ' . BASEURL . '/admin/pengaturanRPP');
        exit;
    }

    public function hapusField($id_field = null)
    {
        if (!$id_field) {
            Flasher::setFlash('ID Field tidak valid!', 'danger');
            header('Location: ' . BASEURL . '/admin/pengaturanRPP');
            exit;
        }

        $templateModel = $this->model('RPPTemplate_model');
        $templateModel->hapusField($id_field);
        Flasher::setFlash('Field berhasil dihapus!', 'success');
        header('Location: ' . BASEURL . '/admin/pengaturanRPP');
        exit;
    }

    public function toggleField($id_field = null)
    {
        if (!$id_field) {
            Flasher::setFlash('ID Field tidak valid!', 'danger');
            header('Location: ' . BASEURL . '/admin/pengaturanRPP');
            exit;
        }

        $templateModel = $this->model('RPPTemplate_model');
        $templateModel->toggleField($id_field);
        Flasher::setFlash('Status field berhasil diubah!', 'success');
        header('Location: ' . BASEURL . '/admin/pengaturanRPP');
        exit;
    }

    // =================================================================
    // PENGATURAN RAPOR (ADMIN)
    // =================================================================

    /**
     * Halaman Pengaturan Rapor untuk Admin
     * Admin bisa mengatur pengaturan rapor untuk semua wali kelas
     */
    public function pengaturanRapor($id_guru = null)
    {
        $id_tp_aktif = $_SESSION['id_tp_aktif'] ?? 0;
        $id_semester_aktif = $_SESSION['id_semester_aktif'] ?? 0;

        // Ambil daftar wali kelas
        $waliKelasList = $this->model('WaliKelas_model')->getAllWaliKelas($id_tp_aktif);

        // Jika tidak ada id_guru dipilih, tampilkan list wali kelas
        if (!$id_guru) {
            // Tambahkan status pengaturan untuk setiap wali kelas
            $pengaturanRaporModel = $this->model('PengaturanRapor_model');
            foreach ($waliKelasList as &$wk) {
                $pengaturan = $pengaturanRaporModel->getPengaturanByGuru($wk['id_guru'], $id_tp_aktif);
                $wk['sudah_diatur'] = !empty($pengaturan);
            }
            unset($wk); // Unset reference

            $this->data['judul'] = 'Pengaturan Rapor';
            $this->data['wali_kelas_list'] = $waliKelasList;

            $this->view('templates/header', $this->data);
            $this->view('templates/sidebar_admin', $this->data);
            $this->view('admin/pengaturan_rapor_list', $this->data);
            $this->view('templates/footer');
            return;
        }

        // Get info wali kelas yang dipilih
        $waliKelasInfo = $this->model('WaliKelas_model')->getWaliKelasByGuru($id_guru, $id_tp_aktif);

        if (!$waliKelasInfo) {
            Flasher::setFlash('Data wali kelas tidak ditemukan', 'danger');
            header('Location: ' . BASEURL . '/admin/pengaturanRapor');
            exit;
        }

        $id_kelas = $waliKelasInfo['id_kelas'] ?? 0;

        // Get pengaturan rapor berdasarkan id_guru dan id_tp
        $pengaturanRapor = $this->model('PengaturanRapor_model')->getPengaturanByGuru($id_guru, $id_tp_aktif);

        // Get mapel list untuk kelas ini
        $mapelList = [];
        if ($id_kelas) {
            $mapelList = $this->model('Penugasan_model')->getMapelByKelas($id_kelas, $id_semester_aktif);
        }

        $this->data['judul'] = 'Pengaturan Rapor - ' . ($waliKelasInfo['nama_kelas'] ?? '');
        $this->data['wali_kelas_info'] = $waliKelasInfo;
        $this->data['pengaturan'] = $pengaturanRapor;
        $this->data['mapel_list'] = $mapelList;
        $this->data['id_guru'] = $id_guru;
        $this->data['session_info'] = [
            'nama_semester' => $_SESSION['nama_semester_aktif'] ?? 'Semester Tidak Diketahui'
        ];

        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/pengaturan_rapor', $this->data);
        $this->view('templates/footer');
    }

    /**
     * Simpan Pengaturan Rapor (Admin)
     */
    public function simpanPengaturanRapor()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Flasher::setFlash('Method tidak diizinkan', 'danger');
            header('Location: ' . BASEURL . '/admin/pengaturanRapor');
            exit;
        }

        $id_guru = $_POST['id_guru'] ?? 0;
        $id_tp_aktif = $_SESSION['id_tp_aktif'] ?? 0;

        if (!$id_guru || !$id_tp_aktif) {
            Flasher::setFlash('Data guru atau tahun pelajaran tidak ditemukan', 'danger');
            header('Location: ' . BASEURL . '/admin/pengaturanRapor');
            exit;
        }

        // Handle upload gambar kop
        $pengaturanLama = $this->model('PengaturanRapor_model')->getPengaturanByGuru($id_guru, $id_tp_aktif);

        $kopFileName = $pengaturanLama['kop_rapor'] ?? '';
        $ttdKepalaFileName = $pengaturanLama['ttd_kepala_madrasah'] ?? '';
        $ttdWalasFileName = $pengaturanLama['ttd_wali_kelas'] ?? '';

        // Upload Kop Rapor
        if (isset($_FILES['kop_rapor']) && $_FILES['kop_rapor']['error'] === UPLOAD_ERR_OK) {
            $kopFileName = $this->handleRaporImageUpload($_FILES['kop_rapor'], 'kop', 'kop_' . $id_guru . '_' . $id_tp_aktif, 2097152);
            if ($kopFileName === false) {
                Flasher::setFlash('Gagal upload gambar kop', 'danger');
                header('Location: ' . BASEURL . '/admin/pengaturanRapor/' . $id_guru);
                exit;
            }
            // Hapus file lama
            if ($pengaturanLama && !empty($pengaturanLama['kop_rapor'])) {
                $this->deleteRaporImage('kop', $pengaturanLama['kop_rapor']);
            }
        }

        // Upload TTD Kepala Madrasah
        if (isset($_FILES['ttd_kepala_madrasah']) && $_FILES['ttd_kepala_madrasah']['error'] === UPLOAD_ERR_OK) {
            $ttdKepalaFileName = $this->handleRaporImageUpload($_FILES['ttd_kepala_madrasah'], 'ttd', 'ttd_kepala_' . $id_guru . '_' . $id_tp_aktif, 1048576);
            if ($ttdKepalaFileName === false) {
                Flasher::setFlash('Gagal upload tanda tangan kepala madrasah', 'danger');
                header('Location: ' . BASEURL . '/admin/pengaturanRapor/' . $id_guru);
                exit;
            }
            // Hapus file lama
            if ($pengaturanLama && !empty($pengaturanLama['ttd_kepala_madrasah'])) {
                $this->deleteRaporImage('ttd', $pengaturanLama['ttd_kepala_madrasah']);
            }
        }

        // Upload TTD Wali Kelas
        if (isset($_FILES['ttd_wali_kelas']) && $_FILES['ttd_wali_kelas']['error'] === UPLOAD_ERR_OK) {
            $ttdWalasFileName = $this->handleRaporImageUpload($_FILES['ttd_wali_kelas'], 'ttd', 'ttd_walas_' . $id_guru . '_' . $id_tp_aktif, 1048576);
            if ($ttdWalasFileName === false) {
                Flasher::setFlash('Gagal upload tanda tangan wali kelas', 'danger');
                header('Location: ' . BASEURL . '/admin/pengaturanRapor/' . $id_guru);
                exit;
            }
            // Hapus file lama
            if ($pengaturanLama && !empty($pengaturanLama['ttd_wali_kelas'])) {
                $this->deleteRaporImage('ttd', $pengaturanLama['ttd_wali_kelas']);
            }
        }

        $data = [
            'id_guru' => $id_guru,
            'id_tp' => $id_tp_aktif,
            'kop_rapor' => $kopFileName,
            'nama_madrasah' => $_POST['nama_madrasah'] ?? '',
            'tempat_cetak' => $_POST['tempat_cetak'] ?? '',
            'nama_kepala_madrasah' => $_POST['nama_kepala_madrasah'] ?? '',
            'ttd_kepala_madrasah' => $ttdKepalaFileName,
            'ttd_wali_kelas' => $ttdWalasFileName,
            'tanggal_cetak' => $_POST['tanggal_cetak'] ?? date('Y-m-d'),
            'mapel_rapor' => isset($_POST['mapel_rapor']) ? json_encode($_POST['mapel_rapor']) : '[]',
            'persen_harian_sts' => $_POST['persen_harian_sts'] ?? 60,
            'persen_sts' => $_POST['persen_sts'] ?? 40,
            'persen_harian_sas' => $_POST['persen_harian_sas'] ?? 40,
            'persen_sts_sas' => $_POST['persen_sts_sas'] ?? 30,
            'persen_sas' => $_POST['persen_sas'] ?? 30
        ];

        if ($this->model('PengaturanRapor_model')->save($data)) {
            Flasher::setFlash('Pengaturan rapor berhasil disimpan', 'success');
        } else {
            Flasher::setFlash('Gagal menyimpan pengaturan rapor', 'danger');
        }

        header('Location: ' . BASEURL . '/admin/pengaturanRapor/' . $id_guru);
        exit;
    }

    /**
     * Helper function untuk upload gambar rapor
     */
    private function handleRaporImageUpload($file, $folder, $prefix, $maxSize)
    {
        $uploadDir = 'public/img/' . $folder . '/';

        // Buat folder jika belum ada
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];

        if (!in_array($fileExtension, $allowedExtensions)) {
            return false;
        }

        if ($file['size'] > $maxSize) {
            return false;
        }

        $fileName = $prefix . '_' . time() . '.' . $fileExtension;
        $uploadPath = $uploadDir . $fileName;

        if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
            return false;
        }

        return $fileName;
    }

    /**
     * Helper function untuk hapus gambar rapor lama
     */
    private function deleteRaporImage($folder, $fileName)
    {
        $filePath = 'public/img/' . $folder . '/' . $fileName;
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    // =================================================================
    // PENGATURAN APLIKASI
    // =================================================================

    /**
     * Halaman Pengaturan Aplikasi
     */
    public function pengaturanAplikasi()
    {
        $this->data['judul'] = 'Pengaturan Aplikasi';
        $this->data['pengaturan'] = $this->model('PengaturanAplikasi_model')->getPengaturan();

        // Load document config
        $this->data['dokumen_config'] = $this->model('DokumenConfig_model')->getAllDokumenAdmin();

        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/pengaturan_aplikasi', $this->data);
        $this->view('templates/footer');
    }

    /**
     * Simpan Pengaturan Aplikasi
     */
    public function simpanPengaturanAplikasi()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Flasher::setFlash('Method tidak diizinkan', 'danger');
            header('Location: ' . BASEURL . '/admin/pengaturanAplikasi');
            exit;
        }

        $pengaturanLama = $this->model('PengaturanAplikasi_model')->getPengaturan();

        $logoFileName = $pengaturanLama['logo'] ?? '';

        // Buat folder jika belum ada - gunakan absolute path
        $baseDir = dirname(dirname(__DIR__)); // Path ke root folder absen
        $uploadDir = $baseDir . '/public/img/app/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Upload Logo
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $logoFileName = $this->handleAppImageUpload($_FILES['logo'], 'logo', 2097152);
            if ($logoFileName === false) {
                Flasher::setFlash('Gagal upload logo. Pastikan file adalah gambar dan ukuran maksimal 2MB', 'danger');
                header('Location: ' . BASEURL . '/admin/pengaturanAplikasi');
                exit;
            }
            // Hapus file lama
            if (!empty($pengaturanLama['logo'])) {
                $this->deleteAppImage($pengaturanLama['logo']);
            }
        }

        $data = [
            'nama_aplikasi' => $_POST['nama_aplikasi'] ?? 'Smart Absensi',
            'url_web' => trim($_POST['url_web'] ?? 'http://localhost/absen'),
            'logo' => $logoFileName,
            'wa_gateway_provider' => trim($_POST['wa_gateway_provider'] ?? 'fonnte'),
            'wa_gateway_url' => trim($_POST['wa_gateway_url'] ?? 'https://api.fonnte.com/send'),
            'wa_gateway_token' => trim($_POST['wa_gateway_token'] ?? ''),
            'wa_gateway_username' => trim($_POST['wa_gateway_username'] ?? ''),
            'wa_gateway_password' => trim($_POST['wa_gateway_password'] ?? ''),
            'wa_template_group_absensi' => trim($_POST['wa_template_group_absensi'] ?? '')
        ];

        if ($this->model('PengaturanAplikasi_model')->simpan($data)) {
            // Clear session cache agar perubahan langsung terlihat
            unset($_SESSION['pengaturan_aplikasi']);
            Flasher::setFlash('Pengaturan aplikasi berhasil disimpan', 'success');
        } else {
            Flasher::setFlash('Gagal menyimpan pengaturan aplikasi', 'danger');
        }

        header('Location: ' . BASEURL . '/admin/pengaturanAplikasi');
        exit;
    }

    /**
     * Simpan Dokumen Config (Insert/Update)
     */
    public function simpanDokumenConfig()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Flasher::setFlash('Method tidak diizinkan', 'danger');
            header('Location: ' . BASEURL . '/admin/pengaturanAplikasi');
            exit;
        }

        $data = [
            'id' => $_POST['id'] ?? null,
            'kode' => strtolower(trim($_POST['kode'] ?? '')),
            'nama' => trim($_POST['nama'] ?? ''),
            'icon' => trim($_POST['icon'] ?? 'file-text'),
            'urutan' => intval($_POST['urutan'] ?? 0),
            'wajib_psb' => isset($_POST['wajib_psb']) ? 1 : 0,
            'wajib_siswa' => isset($_POST['wajib_siswa']) ? 1 : 0,
        ];

        // Validasi
        if (empty($data['kode']) || empty($data['nama'])) {
            Flasher::setFlash('Kode dan nama dokumen wajib diisi', 'danger');
            header('Location: ' . BASEURL . '/admin/pengaturanAplikasi');
            exit;
        }

        $result = $this->model('DokumenConfig_model')->simpan($data);

        if ($result) {
            Flasher::setFlash('Jenis dokumen berhasil disimpan', 'success');
        } else {
            Flasher::setFlash('Gagal menyimpan jenis dokumen', 'danger');
        }

        header('Location: ' . BASEURL . '/admin/pengaturanAplikasi');
        exit;
    }

    /**
     * Toggle Dokumen Config (AJAX)
     */
    public function toggleDokumenConfig()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method tidak diizinkan']);
            exit;
        }

        $id = intval($_POST['id'] ?? 0);
        $field = $_POST['field'] ?? '';

        if (!$id || !in_array($field, ['aktif', 'wajib_psb', 'wajib_siswa'])) {
            echo json_encode(['success' => false, 'message' => 'Parameter tidak valid']);
            exit;
        }

        $model = $this->model('DokumenConfig_model');
        $result = false;

        switch ($field) {
            case 'aktif':
                $result = $model->toggleAktif($id);
                break;
            case 'wajib_psb':
                $result = $model->toggleWajibPSB($id);
                break;
            case 'wajib_siswa':
                $result = $model->toggleWajibSiswa($id);
                break;
        }

        echo json_encode(['success' => $result]);
        exit;
    }

    /**
     * Hapus Dokumen Config (AJAX)
     */
    public function hapusDokumenConfig()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method tidak diizinkan']);
            exit;
        }

        $id = intval($_POST['id'] ?? 0);

        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID tidak valid']);
            exit;
        }

        $result = $this->model('DokumenConfig_model')->hapus($id);
        echo json_encode(['success' => $result]);
        exit;
    }

    /**
     * Test WA Gateway (AJAX)
     */
    public function testWaGateway()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method tidak diizinkan']);
            return;
        }

        $noWa = trim($_POST['no_wa'] ?? '');
        $provider = trim($_POST['provider'] ?? 'fonnte');
        $token = trim($_POST['token'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $url = trim($_POST['url'] ?? '');

        if (empty($noWa)) {
            echo json_encode(['success' => false, 'message' => 'Nomor WA harus diisi']);
            return;
        }

        // Format nomor WA
        $noWa = preg_replace('/[^0-9]/', '', $noWa);
        if (substr($noWa, 0, 1) === '0') {
            $noWa = '62' . substr($noWa, 1);
        }

        $testMessage = '✅ *Test WA Gateway Berhasil!*

Ini adalah pesan test dari sistem ' . (getPengaturanAplikasi()['nama_aplikasi'] ?? 'Smart Absensi') . '.

Waktu: ' . date('d/m/Y H:i:s');

        // Use Fonnte class for sending
        require_once APPROOT . '/app/core/Fonnte.php';

        // Create temporary instance with test credentials
        $fonnte = new Fonnte();

        // Override settings for test
        $reflection = new ReflectionClass($fonnte);
        $apiUrlProp = $reflection->getProperty('apiUrl');
        $apiUrlProp->setAccessible(true);
        $apiUrlProp->setValue($fonnte, $url ?: 'https://api.fonnte.com/send');

        $providerProp = $reflection->getProperty('provider');
        $providerProp->setAccessible(true);
        $providerProp->setValue($fonnte, $provider);

        $tokenProp = $reflection->getProperty('token');
        $tokenProp->setAccessible(true);
        $tokenProp->setValue($fonnte, $token);

        $usernameProp = $reflection->getProperty('username');
        $usernameProp->setAccessible(true);
        $usernameProp->setValue($fonnte, $username);

        $passwordProp = $reflection->getProperty('password');
        $passwordProp->setAccessible(true);
        $passwordProp->setValue($fonnte, $password);

        $result = $fonnte->send($noWa, $testMessage);

        if (isset($result['status']) && $result['status'] === true) {
            echo json_encode(['success' => true, 'message' => 'Pesan berhasil dikirim ke ' . $noWa]);
        } else {
            $errorMsg = $result['reason'] ?? $result['message'] ?? 'Gagal mengirim pesan';
            echo json_encode(['success' => false, 'message' => $errorMsg]);
        }
    }

    /**
     * Helper function untuk upload gambar aplikasi
     */
    private function handleAppImageUpload($file, $prefix, $maxSize)
    {
        // Gunakan absolute path
        $baseDir = dirname(dirname(__DIR__)); // Path ke root folder absen
        $uploadDir = $baseDir . '/public/img/app/';

        // DEBUG: Log untuk troubleshooting
        error_log("=== UPLOAD DEBUG ===");
        error_log("Base Dir: " . $baseDir);
        error_log("Upload Dir: " . $uploadDir);
        error_log("Upload Dir Exists: " . (is_dir($uploadDir) ? 'YES' : 'NO'));
        error_log("Upload Dir Writable: " . (is_writable($uploadDir) ? 'YES' : 'NO'));
        error_log("File Name: " . ($file['name'] ?? 'N/A'));
        error_log("File Size: " . ($file['size'] ?? 'N/A'));
        error_log("File Error: " . ($file['error'] ?? 'N/A'));
        error_log("File Tmp: " . ($file['tmp_name'] ?? 'N/A'));

        // Pastikan folder ada
        if (!is_dir($uploadDir)) {
            $mkdirResult = @mkdir($uploadDir, 0755, true);
            error_log("Mkdir Result: " . ($mkdirResult ? 'SUCCESS' : 'FAILED'));
            if (!$mkdirResult) {
                error_log("Mkdir Error: " . error_get_last()['message'] ?? 'Unknown');
                return false;
            }
        }

        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'ico'];

        if (!in_array($fileExtension, $allowedExtensions)) {
            error_log("Extension not allowed: " . $fileExtension);
            return false;
        }

        if ($file['size'] > $maxSize) {
            error_log("File too large: " . $file['size'] . " > " . $maxSize);
            return false;
        }

        $fileName = $prefix . '_' . time() . '.' . $fileExtension;
        $uploadPath = $uploadDir . $fileName;

        error_log("Target Path: " . $uploadPath);

        if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
            error_log('Move failed to: ' . $uploadPath);
            error_log('Last Error: ' . json_encode(error_get_last()));
            return false;
        }

        error_log("Upload SUCCESS: " . $fileName);
        return $fileName;
    }

    /**
     * Helper function untuk hapus gambar aplikasi lama
     */
    private function deleteAppImage($fileName)
    {
        $baseDir = dirname(dirname(__DIR__));
        $filePath = $baseDir . '/public/img/app/' . $fileName;
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    // =================================================================
    // PENGATURAN WAJIB RPP
    // =================================================================

    /**
     * Toggle Wajib RPP Disetujui (quick toggle dari halaman pengaturan RPP)
     */
    public function toggleWajibRPP()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/admin/pengaturanRPP');
            exit;
        }

        $pengaturan = $this->model('PengaturanRPP_model')->getPengaturan();
        $newStatus = empty($pengaturan['wajib_rpp_disetujui']) ? 1 : 0;

        // Jika diaktifkan, otomatis blokir semua fitur
        $data = [
            'wajib_rpp_disetujui' => $newStatus,
            'blokir_absensi' => $newStatus ? 1 : 0,
            'blokir_jurnal' => $newStatus ? 1 : 0,
            'blokir_nilai' => $newStatus ? 1 : 0,
            'pesan_blokir' => $pengaturan['pesan_blokir'] ?? 'Anda belum dapat mengakses fitur ini karena RPP belum dibuat atau belum disetujui oleh Kepala Madrasah.'
        ];

        if ($this->model('PengaturanRPP_model')->simpan($data)) {
            // Hapus semua cache terkait pengaturan RPP
            unset($_SESSION['pengaturan_wajib_rpp']);
            unset($_SESSION['pengaturan_wajib_rpp_time']);

            $status = $newStatus ? 'diaktifkan' : 'dinonaktifkan';
            Flasher::setFlash("Wajib RPP Disetujui berhasil $status", 'success');
        } else {
            Flasher::setFlash('Gagal mengubah pengaturan', 'danger');
        }

        header('Location: ' . BASEURL . '/admin/pengaturanRPP');
        exit;
    }

    /**
     * Halaman pengaturan wajib RPP (redirect ke pengaturanRPP)
     */
    public function pengaturanWajibRPP()
    {
        // Redirect ke halaman pengaturan RPP
        header('Location: ' . BASEURL . '/admin/pengaturanRPP');
        exit;
    }

    /**
     * Simpan Pengaturan Wajib RPP (keep for backward compatibility)
     */
    public function simpanPengaturanWajibRPP()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/admin/pengaturanRPP');
            exit;
        }

        $data = [
            'wajib_rpp_disetujui' => isset($_POST['wajib_rpp_disetujui']) ? 1 : 0,
            'blokir_absensi' => isset($_POST['blokir_absensi']) ? 1 : 0,
            'blokir_jurnal' => isset($_POST['blokir_jurnal']) ? 1 : 0,
            'blokir_nilai' => isset($_POST['blokir_nilai']) ? 1 : 0,
            'pesan_blokir' => $_POST['pesan_blokir'] ?? ''
        ];

        if ($this->model('PengaturanRPP_model')->simpan($data)) {
            // Hapus semua cache terkait pengaturan RPP
            unset($_SESSION['pengaturan_wajib_rpp']);
            unset($_SESSION['pengaturan_wajib_rpp_time']);

            Flasher::setFlash('Pengaturan wajib RPP berhasil disimpan', 'success');
        } else {
            Flasher::setFlash('Gagal menyimpan pengaturan', 'danger');
        }

        header('Location: ' . BASEURL . '/admin/pengaturanRPP');
        exit;
    }

    // =================================================================
    // SURAT TUGAS CRUD
    // =================================================================

    public function suratTugas()
    {
        $this->data['judul'] = 'Surat Tugas';
        $idTp = $_SESSION['id_tp_aktif'] ?? null;
        $this->data['tp_aktif'] = $this->model('TahunPelajaran_model')->getTPById($idTp);
        $this->data['surat_list'] = $this->model('SuratTugas_model')->getAllSuratTugas($idTp);

        $this->view('templates/header', $this->data);
        $this->view('surat_tugas/index', $this->data);
        $this->view('templates/footer', $this->data);
    }

    public function tambahSuratTugas()
    {
        $this->data['judul'] = 'Buat Surat Tugas';
        $idTp = $_SESSION['id_tp_aktif'] ?? null;
        $this->data['guru_list'] = $this->model('Guru_model')->getAllGuru();
        $this->data['pengaturan'] = $this->model('PengaturanAplikasi_model')->getAllSettings();
        $this->data['nomor_surat_auto'] = $this->model('SuratTugas_model')->generateNomorSurat($idTp);

        $this->view('templates/header', $this->data);
        $this->view('surat_tugas/form', $this->data);
        $this->view('templates/footer', $this->data);
    }

    public function editSuratTugas($id)
    {
        $this->data['judul'] = 'Edit Surat Tugas';
        $this->data['surat'] = $this->model('SuratTugas_model')->getSuratById($id);
        $this->data['guru_list'] = $this->model('Guru_model')->getAllGuru();
        $this->data['pengaturan'] = $this->model('PengaturanAplikasi_model')->getAllSettings();

        if (!$this->data['surat']) {
            Flasher::setFlash('Surat tidak ditemukan', 'danger');
            header('Location: ' . BASEURL . '/admin/suratTugas');
            exit;
        }

        $this->view('templates/header', $this->data);
        $this->view('surat_tugas/form', $this->data);
        $this->view('templates/footer', $this->data);
    }

    public function simpanSuratTugas()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/admin/suratTugas');
            exit;
        }

        $idTp = $_SESSION['id_tp_aktif'] ?? null;
        $data = [
            'nomor_surat' => $_POST['nomor_surat'],
            'id_guru' => $_POST['id_guru'],
            'id_tp' => $idTp,
            'tanggal_surat' => $_POST['tanggal_surat'],
            'perihal' => $_POST['perihal'],
            'isi_tugas' => $_POST['isi_tugas'] ?? '',
            'tempat_tugas' => $_POST['tempat_tugas'] ?? '',
            'tanggal_mulai' => $_POST['tanggal_mulai'] ?: null,
            'tanggal_selesai' => $_POST['tanggal_selesai'] ?: null,
            'keterangan' => $_POST['keterangan'] ?? '',
            'status' => $_POST['status'] ?? 'draft'
        ];

        $suratModel = $this->model('SuratTugas_model');

        if (!empty($_POST['id_surat'])) {
            $suratModel->updateSurat($_POST['id_surat'], $data);
            Flasher::setFlash('Surat tugas berhasil diperbarui', 'success');
        } else {
            $suratModel->tambahSurat($data);
            Flasher::setFlash('Surat tugas berhasil dibuat', 'success');
        }

        header('Location: ' . BASEURL . '/admin/suratTugas');
        exit;
    }

    public function hapusSuratTugas($id)
    {
        $this->model('SuratTugas_model')->hapusSurat($id);
        Flasher::setFlash('Surat tugas berhasil dihapus', 'success');
        header('Location: ' . BASEURL . '/admin/suratTugas');
        exit;
    }

    public function cetakSuratTugas($id)
    {
        $this->data['surat'] = $this->model('SuratTugas_model')->getSuratById($id);
        $this->data['pengaturan'] = $this->model('PengaturanAplikasi_model')->getAllSettings();

        if (!$this->data['surat']) {
            echo 'Surat tidak ditemukan';
            exit;
        }

        $this->view('surat_tugas/cetak', $this->data);
    }

    // =================================================================
    // CETAK KARTU LOGIN
    // =================================================================

    public function cetakKartuLogin($id_kelas = null)
    {
        $this->data['judul'] = 'Cetak Kartu Login';
        $this->data['kelas_list'] = $this->model('Kelas_model')->getAllKelas();
        $this->data['pengaturan'] = $this->model('PengaturanAplikasi_model')->getAllSettings();

        // If no kelas selected, show selection page
        if (!$id_kelas) {
            $this->view('templates/header', $this->data);
            $this->view('admin/pilih_kelas_kartu', $this->data);
            $this->view('templates/footer', $this->data);
            return;
        }

        // Get kelas info
        $kelas = $this->model('Kelas_model')->getKelasById($id_kelas);
        if (!$kelas) {
            Flasher::setFlash('Kelas tidak ditemukan', 'danger');
            header('Location: ' . BASEURL . '/admin/cetakKartuLogin');
            exit;
        }

        $this->data['nama_kelas'] = $kelas['nama_kelas'];
        $this->data['id_kelas'] = $id_kelas;

        // Get siswa in this kelas
        $id_tp = $_SESSION['id_tp_aktif'] ?? null;
        $this->data['siswa_list'] = $this->model('Kelas_model')->getSiswaByKelas($id_kelas, $id_tp);

        // Get user credentials for each siswa
        $userModel = $this->model('User_model');
        foreach ($this->data['siswa_list'] as &$siswa) {
            $user = $userModel->getUserByIdRef($siswa['id_siswa'], 'siswa');
            $siswa['username'] = $user['username'] ?? '-';
            $siswa['password_plain'] = $user['password_plain'] ?? '-';
        }

        // Direct to print view without header/footer
        $this->view('admin/cetak_kartu_login', $this->data);
    }

    /**
     * Cetak Kartu Login dari halaman siswa (dengan filter kelas via GET)
     */
    public function cetakKartuLoginSiswa()
    {
        $this->data['judul'] = 'Cetak Kartu Login Siswa';
        $this->data['pengaturan'] = $this->model('PengaturanAplikasi_model')->getPengaturan();

        $kelasFilter = $_GET['kelas'] ?? null;
        $id_tp = $_SESSION['id_tp_aktif'] ?? null;

        // Get all siswa or filter by kelas name
        if ($kelasFilter) {
            // Get kelas id from name
            $kelas = $this->model('Kelas_model')->getKelasByName($kelasFilter, $id_tp);
            if ($kelas) {
                $this->data['siswa_list'] = $this->model('Kelas_model')->getSiswaByKelas($kelas['id_kelas'], $id_tp);
                $this->data['nama_kelas'] = $kelasFilter;
            } else {
                $this->data['siswa_list'] = [];
                $this->data['nama_kelas'] = $kelasFilter . ' (Tidak ditemukan)';
            }
        } else {
            // Get all siswa
            $this->data['siswa_list'] = $this->model('Siswa_model')->getAllSiswaWithKelas($id_tp);
            $this->data['nama_kelas'] = 'Semua Kelas';
        }

        // Get user credentials for each siswa
        $userModel = $this->model('User_model');
        foreach ($this->data['siswa_list'] as &$siswa) {
            $user = $userModel->getUserByIdRef($siswa['id_siswa'], 'siswa');
            $siswa['username'] = $user['username'] ?? $siswa['nisn'];
            $siswa['password_plain'] = $user['password_plain'] ?? '-';
        }

        // Direct to new print view
        $this->view('admin/cetak_kartu_login_siswa', $this->data);
    }

    // =================================================================
    // PESAN (MESSAGING SYSTEM)
    // =================================================================

    /**
     * Halaman utama pesan - daftar terkirim
     */
    public function pesan()
    {
        $this->data['judul'] = 'Kirim Pesan WhatsApp';

        $id_tp = $_SESSION['id_tp_aktif'] ?? 0;

        // Stats
        $db = new Database();

        // Total guru dengan no_wa
        $db->query("SELECT COUNT(*) as total FROM guru WHERE no_wa IS NOT NULL AND no_wa != ''");
        $this->data['total_guru'] = $db->single()['total'] ?? 0;

        // Total siswa dengan no_wa
        $db->query("SELECT COUNT(*) as total FROM siswa WHERE no_wa IS NOT NULL AND no_wa != ''");
        $this->data['total_siswa'] = $db->single()['total'] ?? 0;
        $this->data['total_siswa_wa'] = $this->data['total_siswa'];

        // Total orang tua (ayah + ibu dengan nomor)
        $db->query("SELECT 
            COUNT(DISTINCT CASE WHEN ayah_no_hp IS NOT NULL AND ayah_no_hp != '' THEN id_siswa END) +
            COUNT(DISTINCT CASE WHEN ibu_no_hp IS NOT NULL AND ibu_no_hp != '' THEN id_siswa END) as total 
            FROM siswa");
        $this->data['total_ortu'] = $db->single()['total'] ?? 0;

        // Queue pending
        require_once APPROOT . '/app/models/WaQueue_model.php';
        $queueModel = new WaQueue_model();
        $stats = $queueModel->getQueueStats();
        $this->data['queue_pending'] = $stats['pending'] ?? 0;

        // Recent queue
        $this->data['recent_queue'] = $queueModel->getRecentMessages(10);

        // Kelas list
        $this->data['kelas'] = $this->model('Kelas_model')->getKelasByTP($id_tp);

        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/pesan', $this->data);
        $this->view('templates/footer', $this->data);
    }

    /**
     * Form kirim pesan baru
     */
    public function kirimPesan()
    {
        $this->data['judul'] = 'Tulis Pesan Baru';
        $id_tp = $_SESSION['id_tp_aktif'] ?? 0;

        // Data untuk dropdown
        $this->data['kelas'] = $this->model('Kelas_model')->getKelasByTP($id_tp);
        $this->data['guru'] = $this->model('Guru_model')->getAllGuru();
        $this->data['siswa'] = $this->model('Siswa_model')->getAllSiswaWithKelas($id_tp);

        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/kirim_pesan', $this->data);
        $this->view('templates/footer', $this->data);
    }

    /**
     * Proses kirim pesan WA massal (Bulk WA Sender)
     */
    public function prosesKirimPesanWa()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/admin/pesan');
            exit;
        }

        $target_type = $_POST['target_type'] ?? '';
        $id_kelas = $_POST['id_kelas'] ?? null;
        $pesan = trim($_POST['pesan'] ?? '');
        $id_tp = $_SESSION['id_tp_aktif'] ?? 0;

        // Validasi
        if (empty($target_type) || empty($pesan)) {
            Flasher::setFlash('Target dan pesan wajib diisi!', 'error');
            header('Location: ' . BASEURL . '/admin/pesan');
            exit;
        }

        // Get nama sekolah
        $pengaturanModel = $this->model('PengaturanAplikasi_model');
        $pengaturan = $pengaturanModel->getPengaturan();
        $namaSekolah = $pengaturan['nama_aplikasi'] ?? $pengaturan['nama_sekolah'] ?? 'Sekolah';

        // Format pesan dengan header
        $formattedPesan = "📩 *PESAN DARI {$namaSekolah}*\n\n";
        $formattedPesan .= $pesan;
        $formattedPesan .= "\n\n━━━━━━━━━━━━━━━━━━━━━\n";
        $formattedPesan .= "_Pesan ini dikirim otomatis._";

        // Collect phone numbers based on target
        $db = new Database();
        $numbers = [];

        switch ($target_type) {
            case 'semua_guru':
                $db->query("SELECT no_wa, nama_guru AS nama FROM guru WHERE no_wa IS NOT NULL AND no_wa != ''");
                $results = $db->resultSet();
                foreach ($results as $r) {
                    $numbers[] = ['no_wa' => $r['no_wa'], 'nama' => $r['nama']];
                }
                break;

            case 'semua_siswa':
                $db->query("SELECT no_wa, nama_siswa AS nama FROM siswa WHERE no_wa IS NOT NULL AND no_wa != ''");
                $results = $db->resultSet();
                foreach ($results as $r) {
                    $numbers[] = ['no_wa' => $r['no_wa'], 'nama' => $r['nama']];
                }
                break;

            case 'semua_ortu':
                $db->query("SELECT id_siswa, nama_siswa AS nama, ayah_no_hp, ibu_no_hp FROM siswa WHERE (ayah_no_hp IS NOT NULL AND ayah_no_hp != '') OR (ibu_no_hp IS NOT NULL AND ibu_no_hp != '')");
                $results = $db->resultSet();
                foreach ($results as $r) {
                    if (!empty($r['ayah_no_hp'])) {
                        $numbers[] = ['no_wa' => $r['ayah_no_hp'], 'nama' => 'Ortu ' . $r['nama']];
                    }
                    if (!empty($r['ibu_no_hp'])) {
                        $numbers[] = ['no_wa' => $r['ibu_no_hp'], 'nama' => 'Ortu ' . $r['nama']];
                    }
                }
                break;

            case 'kelas_siswa':
                if (!empty($id_kelas)) {
                    $db->query("SELECT s.no_wa, s.nama_siswa AS nama FROM siswa s
                        INNER JOIN anggota_kelas ak ON s.id_siswa = ak.id_siswa
                        WHERE ak.id_kelas = :id_kelas AND ak.id_tp = :id_tp
                        AND s.no_wa IS NOT NULL AND s.no_wa != ''");
                    $db->bind(':id_kelas', $id_kelas);
                    $db->bind(':id_tp', $id_tp);
                    $results = $db->resultSet();
                    foreach ($results as $r) {
                        $numbers[] = ['no_wa' => $r['no_wa'], 'nama' => $r['nama']];
                    }
                }
                break;

            case 'kelas_ortu':
                if (!empty($id_kelas)) {
                    $db->query("SELECT s.id_siswa, s.nama_siswa AS nama, s.ayah_no_hp, s.ibu_no_hp FROM siswa s
                        INNER JOIN anggota_kelas ak ON s.id_siswa = ak.id_siswa
                        WHERE ak.id_kelas = :id_kelas AND ak.id_tp = :id_tp
                        AND ((s.ayah_no_hp IS NOT NULL AND s.ayah_no_hp != '') OR (s.ibu_no_hp IS NOT NULL AND s.ibu_no_hp != ''))");
                    $db->bind(':id_kelas', $id_kelas);
                    $db->bind(':id_tp', $id_tp);
                    $results = $db->resultSet();
                    foreach ($results as $r) {
                        if (!empty($r['ayah_no_hp'])) {
                            $numbers[] = ['no_wa' => $r['ayah_no_hp'], 'nama' => 'Ortu ' . $r['nama']];
                        }
                        if (!empty($r['ibu_no_hp'])) {
                            $numbers[] = ['no_wa' => $r['ibu_no_hp'], 'nama' => 'Ortu ' . $r['nama']];
                        }
                    }
                }
                break;
        }

        // Add to queue
        $queued = 0;
        if (!empty($numbers)) {
            require_once APPROOT . '/app/models/WaQueue_model.php';
            require_once APPROOT . '/app/core/Fonnte.php';
            $queueModel = new WaQueue_model();
            $fonnte = new Fonnte();

            foreach ($numbers as $n) {
                $queueModel->addToQueue(
                    $fonnte->formatNumber($n['no_wa']),
                    $formattedPesan,
                    'pesan_bulk',
                    ['target' => $target_type, 'penerima' => $n['nama']]
                );
                $queued++;
            }
        }

        if ($queued > 0) {
            Flasher::setFlash("📤 {$queued} pesan WhatsApp masuk antrian. Monitor di Antrian WA.", 'success');
        } else {
            Flasher::setFlash('Tidak ada nomor WA yang ditemukan untuk target ini.', 'warning');
        }

        header('Location: ' . BASEURL . '/admin/pesan');
        exit;
    }

    /**
     * Proses kirim pesan
     */
    public function prosesKirimPesan()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/admin/pesan');
            exit;
        }

        $target_type = $_POST['target_type'] ?? '';
        $judul = trim($_POST['judul'] ?? '');
        $isi = trim($_POST['isi'] ?? '');

        // Validasi
        if (empty($target_type) || empty($judul) || empty($isi)) {
            Flasher::setFlash('Semua field wajib diisi!', 'error');
            header('Location: ' . BASEURL . '/admin/kirimPesan');
            exit;
        }

        // Tentukan target_id
        $target_id = null;
        if ($target_type === 'kelas') {
            $target_id = $_POST['target_kelas'] ?? null;
        } elseif ($target_type === 'guru_individual') {
            $target_id = $_POST['target_guru'] ?? null;
        } elseif ($target_type === 'siswa_individual') {
            $target_id = $_POST['target_siswa'] ?? null;
        }

        // Upload lampiran
        $lampiran = null;
        if (!empty($_FILES['lampiran']['name'])) {
            $allowed = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png', 'zip'];
            $ext = strtolower(pathinfo($_FILES['lampiran']['name'], PATHINFO_EXTENSION));

            if (!in_array($ext, $allowed)) {
                Flasher::setFlash('Format file tidak diizinkan!', 'error');
                header('Location: ' . BASEURL . '/admin/kirimPesan');
                exit;
            }

            if ($_FILES['lampiran']['size'] > 10 * 1024 * 1024) {
                Flasher::setFlash('Ukuran file maksimal 10MB!', 'error');
                header('Location: ' . BASEURL . '/admin/kirimPesan');
                exit;
            }

            $lampiran = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $_FILES['lampiran']['name']);
            $uploadPath = APPROOT . '/public/uploads/pesan/' . $lampiran;
            move_uploaded_file($_FILES['lampiran']['tmp_name'], $uploadPath);
        }

        $pesanModel = $this->model('Pesan_model');
        $id_tp = $_SESSION['id_tp_aktif'] ?? 0;

        // Insert pesan
        $id_pesan = $pesanModel->kirimPesan([
            'pengirim_type' => 'admin',
            'pengirim_id' => 0,
            'judul' => $judul,
            'isi' => $isi,
            'target_type' => $target_type,
            'target_id' => $target_id,
            'lampiran' => $lampiran
        ]);

        // Tentukan penerima
        $penerima_list = [];
        switch ($target_type) {
            case 'semua_guru':
                $guru = $pesanModel->getAllGuruIds();
                foreach ($guru as $g) {
                    $penerima_list[] = ['type' => 'guru', 'id' => $g['id']];
                }
                break;
            case 'semua_siswa':
                $siswa = $pesanModel->getAllSiswaIds();
                foreach ($siswa as $s) {
                    $penerima_list[] = ['type' => 'siswa', 'id' => $s['id']];
                }
                break;
            case 'kelas':
                $siswa = $pesanModel->getSiswaIdsByKelas($target_id, $id_tp);
                foreach ($siswa as $s) {
                    $penerima_list[] = ['type' => 'siswa', 'id' => $s['id']];
                }
                break;
            case 'guru_individual':
                $penerima_list[] = ['type' => 'guru', 'id' => $target_id];
                break;
            case 'siswa_individual':
                $penerima_list[] = ['type' => 'siswa', 'id' => $target_id];
                break;
        }

        // Insert penerima
        $pesanModel->kirimKePenerima($id_pesan, $penerima_list);

        // ========================================
        // KIRIM VIA WHATSAPP
        // ========================================
        $wa_sent = 0;
        $wa_failed = 0;

        try {
            require_once APPROOT . '/app/core/Fonnte.php';
            $fonnte = new Fonnte();

            // Format pesan WA - ambil nama aplikasi dari database
            $pengaturanModel = $this->model('PengaturanAplikasi_model');
            $pengaturan = $pengaturanModel->getPengaturan();
            $namaApp = $pengaturan['nama_aplikasi'] ?? $pengaturan['nama_sekolah'] ?? 'Sekolah';

            $waMessage = "📩 *PESAN DARI {$namaApp}*\n\n";
            $waMessage .= "*{$judul}*\n\n";
            $waMessage .= $isi;
            if (!empty($lampiran)) {
                $waMessage .= "\n\n📎 _Lampiran tersedia di aplikasi_";
            }
            $waMessage .= "\n\n━━━━━━━━━━━━━━━━━━━━━\n";
            $waMessage .= "_Pesan ini dikirim otomatis dari {$namaApp}_\n\n";
            $waMessage .= "✅ *Apabila sudah menerima pesan ini, mohon balas dengan mengetik:* YA";

            // Queue messages instead of sending directly
            require_once APPROOT . '/app/models/WaQueue_model.php';
            $queueModel = new WaQueue_model();

            foreach ($penerima_list as $penerima) {
                $no_wa = null;
                $nama_penerima = '';

                if ($penerima['type'] === 'guru') {
                    $guru = $pesanModel->getGuruWithNoWa($penerima['id']);
                    $no_wa = $guru['no_wa'] ?? null;
                    $nama_penerima = $guru['nama'] ?? '';
                } elseif ($penerima['type'] === 'siswa') {
                    $siswa = $pesanModel->getSiswaWithNoWa($penerima['id']);
                    $no_wa = $siswa['no_wa'] ?? null;
                    $nama_penerima = $siswa['nama'] ?? '';
                }

                if (!empty($no_wa)) {
                    // Add to queue instead of send
                    $queueModel->addToQueue(
                        $fonnte->formatNumber($no_wa),
                        $waMessage,
                        'pesan_admin',
                        ['judul' => $judul, 'penerima' => $nama_penerima]
                    );
                    $wa_sent++;
                }
            }
        } catch (Exception $e) {
            error_log("WhatsApp queue error: " . $e->getMessage());
        }

        // Flash message dengan info WA queue
        $flashMsg = "Pesan berhasil disimpan untuk " . count($penerima_list) . " penerima.";
        if ($wa_sent > 0) {
            $flashMsg .= " 📤 {$wa_sent} pesan WA masuk antrian.";
        }

        Flasher::setFlash($flashMsg, 'success');
        header('Location: ' . BASEURL . '/admin/pesan');
        exit;
    }

    /**
     * Detail pesan
     */
    public function detailPesan($id = null)
    {
        if (!$id) {
            header('Location: ' . BASEURL . '/admin/pesan');
            exit;
        }

        $pesanModel = $this->model('Pesan_model');
        $this->data['pesan'] = $pesanModel->getPesanById($id);

        if (!$this->data['pesan']) {
            Flasher::setFlash('Pesan tidak ditemukan!', 'error');
            header('Location: ' . BASEURL . '/admin/pesan');
            exit;
        }

        $this->data['penerima'] = $pesanModel->getPenerimaPesan($id);
        $this->data['judul'] = 'Detail Pesan';

        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/detail_pesan', $this->data);
        $this->view('templates/footer', $this->data);
    }

    /**
     * Hapus pesan
     */
    public function hapusPesan($id = null)
    {
        if (!$id || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/admin/pesan');
            exit;
        }

        $pesanModel = $this->model('Pesan_model');
        $pesanModel->hapusPesan($id);

        Flasher::setFlash('Pesan berhasil dihapus!', 'success');
        header('Location: ' . BASEURL . '/admin/pesan');
        exit;
    }

    public function pengaturanSistem()
    {
        $pengaturanModel = $this->model('PengaturanSistem_model');
        $settings = $pengaturanModel->getAll();

        $data = [
            'title' => 'Pengaturan Sistem',
            'sidebar' => 'templates/sidebar_admin',
            'settings' => $settings
        ];

        $this->view('templates/header', $data);
        $this->view('templates/sidebar_admin', $data);
        $this->view('admin/pengaturan_sistem', $data);
        $this->view('templates/footer');
    }

    public function updatePengaturanSistem()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/admin/pengaturanSistem');
            exit;
        }

        $pengaturanModel = $this->model('PengaturanSistem_model');

        $settings = [
            'secret_key' => $_POST['secret_key'] ?? '',
            'qr_enabled' => isset($_POST['qr_enabled']) ? '1' : '0',
            'google_oauth_enabled' => isset($_POST['google_oauth_enabled']) ? '1' : '0',
            'google_client_id' => $_POST['google_client_id'] ?? '',
            'google_client_secret' => $_POST['google_client_secret'] ?? '',
            'google_allowed_domain' => $_POST['google_allowed_domain'] ?? '',
            'menu_input_nilai_enabled' => isset($_POST['menu_input_nilai_enabled']) ? '1' : '0',
            'menu_pembayaran_enabled' => isset($_POST['menu_pembayaran_enabled']) ? '1' : '0',
            'menu_rapor_enabled' => isset($_POST['menu_rapor_enabled']) ? '1' : '0',
        ];

        $pengaturanModel->updateMultiple($settings);

        Flasher::setFlash('Pengaturan berhasil disimpan!', 'success');
        header('Location: ' . BASEURL . '/admin/pengaturanSistem');
        exit;
    }

    // =================================================================
    // ANTRIAN PESAN WHATSAPP
    // =================================================================

    /**
     * Halaman dashboard antrian WA
     */
    public function antrianWa()
    {
        $this->data['judul'] = 'Antrian Pesan WhatsApp';

        $queueModel = $this->model('WaQueue_model');
        $pengaturanSistemModel = $this->model('PengaturanSistem_model');

        // Statistik
        $this->data['stats'] = $queueModel->getQueueStats();
        $this->data['stats_by_jenis'] = $queueModel->getStatsByJenis();
        $this->data['today_count'] = $queueModel->getTodayCount();

        // Notifikasi Absensi Status
        $this->data['notif_enabled'] = ($pengaturanSistemModel->get('wa_notif_absensi_enabled') ?? '1') === '1';
        $this->data['notif_mode'] = $pengaturanSistemModel->get('wa_notif_absensi_mode') ?? 'personal';
        $this->data['queue_enabled'] = ($pengaturanSistemModel->get('wa_queue_enabled') ?? '1') === '1';

        // Filter & Pagination
        $status = $_GET['status'] ?? 'all';
        $page = max(1, intval($_GET['page'] ?? 1));
        $perPage = 20;

        $this->data['filter_status'] = $status;

        // Get paginated messages
        $result = $queueModel->getPaginated($status, $page, $perPage);
        $this->data['messages'] = $result['data'];
        $this->data['pagination'] = [
            'current' => $result['page'],
            'total_pages' => $result['total_pages'],
            'total' => $result['total'],
            'per_page' => $result['per_page']
        ];

        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/antrian_wa', $this->data);
        $this->view('templates/footer', $this->data);
    }

    /**
     * Toggle aktif/nonaktif notifikasi absensi (quick toggle)
     */
    public function toggleNotifikasiAbsensi()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/admin/antrianWa');
            exit;
        }

        $pengaturanSistemModel = $this->model('PengaturanSistem_model');
        $currentValue = $pengaturanSistemModel->get('wa_notif_absensi_enabled') ?? '1';
        $newValue = $currentValue === '1' ? '0' : '1';

        $pengaturanSistemModel->set('wa_notif_absensi_enabled', $newValue);

        $status = $newValue === '1' ? 'diaktifkan' : 'dinonaktifkan';
        Flasher::setFlash("Notifikasi absensi berhasil {$status}", 'success');

        header('Location: ' . BASEURL . '/admin/antrianWa');
        exit;
    }

    /**
     * Toggle mode antrian WA: queue vs direct
     * Queue = kirim via cron job (async)
     * Direct = kirim langsung tanpa antrian (sync)
     */
    public function toggleQueueMode()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/admin/antrianWa');
            exit;
        }

        $pengaturanSistemModel = $this->model('PengaturanSistem_model');
        $currentValue = $pengaturanSistemModel->get('wa_queue_enabled') ?? '1';
        $newValue = $currentValue === '1' ? '0' : '1';

        $pengaturanSistemModel->set('wa_queue_enabled', $newValue);

        $mode = $newValue === '1' ? 'ANTRIAN (Queue)' : 'LANGSUNG (Direct)';
        Flasher::setFlash("Mode pengiriman WA diubah ke {$mode}", 'success');

        header('Location: ' . BASEURL . '/admin/antrianWa');
        exit;
    }

    /**
     * Retry pesan yang gagal
     */
    public function retryWaMessage($id = null)
    {
        if (!$id) {
            Flasher::setFlash('ID tidak valid!', 'danger');
            header('Location: ' . BASEURL . '/admin/antrianWa');
            exit;
        }

        $queueModel = $this->model('WaQueue_model');
        $pengaturanSistemModel = $this->model('PengaturanSistem_model');

        // Cek apakah antrian aktif
        $queueEnabled = ($pengaturanSistemModel->get('wa_queue_enabled') ?? '1') === '1';

        if (!$queueEnabled) {
            // MODE DIRECT: Kirim langsung sekarang juga
            require_once APPROOT . '/app/core/Fonnte.php';
            $fonnte = new Fonnte();

            // Ambil data pesan
            $msg = $queueModel->getById($id);

            if ($msg) {
                // Set processing
                $queueModel->markAsProcessing($id);

                try {
                    // Coba kirim
                    $response = $fonnte->send($msg['no_wa'], $msg['pesan']); // Gunakan send standard, fonnte class akan handle provider

                    if (isset($response['status']) && $response['status'] === true) {
                        $queueModel->markAsSent($id, json_encode($response));
                        Flasher::setFlash('Pesan berhasil dikirim ulang secara langsung.', 'success');
                        header('Location: ' . BASEURL . '/admin/antrianWa?status=sent');
                    } else {
                        $errorMsg = $response['reason'] ?? $response['message'] ?? 'Unknown error';
                        $queueModel->markAsFailedDirect($id, $errorMsg);
                        Flasher::setFlash('Gagal mengirim ulang: ' . $errorMsg, 'danger');
                        header('Location: ' . BASEURL . '/admin/antrianWa?status=failed');
                    }
                } catch (Exception $e) {
                    $queueModel->markAsFailedDirect($id, $e->getMessage());
                    Flasher::setFlash('Error saat retry: ' . $e->getMessage(), 'danger');
                    header('Location: ' . BASEURL . '/admin/antrianWa?status=failed');
                }
            } else {
                Flasher::setFlash('Data pesan tidak ditemukan.', 'danger');
                header('Location: ' . BASEURL . '/admin/antrianWa');
            }
        } else {
            // MODE QUEUE: Reset jadi pending (default behavior)
            $queueModel->retryFailed($id);
            Flasher::setFlash('Pesan dikembalikan ke antrian (Pending).', 'success');
            header('Location: ' . BASEURL . '/admin/antrianWa?status=pending');
        }
        exit;
    }

    /**
     * Retry semua pesan gagal
     */
    public function retryAllWaMessages()
    {
        $queueModel = $this->model('WaQueue_model');
        $queueModel->retryAllFailed();

        Flasher::setFlash('Semua pesan gagal akan dicoba kirim ulang.', 'success');
        header('Location: ' . BASEURL . '/admin/antrianWa');
        exit;
    }

    /**
     * Hapus pesan dari antrian
     */
    public function hapusWaMessage($id = null)
    {
        if (!$id) {
            Flasher::setFlash('ID tidak valid!', 'danger');
            header('Location: ' . BASEURL . '/admin/antrianWa');
            exit;
        }

        $queueModel = $this->model('WaQueue_model');
        $queueModel->delete($id);

        Flasher::setFlash('Pesan dihapus dari antrian.', 'success');
        header('Location: ' . BASEURL . '/admin/antrianWa');
        exit;
    }

    /**
     * Bulk delete pesan dari antrian
     */
    public function bulkDeleteWaMessages()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ids = $_POST['ids'] ?? [];

            if (empty($ids)) {
                Flasher::setFlash('Tidak ada pesan yang dipilih!', 'warning');
            } else {
                $queueModel = $this->model('WaQueue_model');
                $queueModel->bulkDelete($ids);
                Flasher::setFlash(count($ids) . ' pesan berhasil dihapus.', 'success');
            }
        }

        header('Location: ' . BASEURL . '/admin/antrianWa');
        exit;
    }

    /**
     * Proses antrian manual (tanpa cron)
     */
    public function prosesAntrianWa()
    {
        set_time_limit(120); // 2 minutes max

        require_once APPROOT . '/app/core/Fonnte.php';
        $queueModel = $this->model('WaQueue_model');
        $fonnte = new Fonnte();

        $processed = 0;
        $maxProcess = 5;

        $pendingMessages = $queueModel->getPendingMessages($maxProcess);

        foreach ($pendingMessages as $msg) {
            $queueModel->markAsProcessing($msg['id']);

            try {
                $response = $fonnte->send($msg['no_wa'], $msg['pesan']);

                if (isset($response['status']) && $response['status'] === true) {
                    $queueModel->markAsSent($msg['id'], json_encode($response));
                } else {
                    $errorMsg = $response['reason'] ?? $response['message'] ?? 'Unknown error';
                    $queueModel->markAsFailed($msg['id'], $errorMsg);
                }
            } catch (Exception $e) {
                $queueModel->markAsFailed($msg['id'], $e->getMessage());
            }

            $processed++;

            // Delay between messages
            if ($processed < count($pendingMessages)) {
                sleep(rand(8, 12));
            }
        }

        Flasher::setFlash("Berhasil proses {$processed} pesan.", 'success');
        header('Location: ' . BASEURL . '/admin/antrianWa');
        exit;
    }
    /**
     * AJAX: Update Cron Token
     */
    public function updateCronToken()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit;
        }

        // Check if admin
        if ($_SESSION['role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $token = $input['token'] ?? '';

        if (empty($token)) {
            echo json_encode(['success' => false, 'message' => 'Token cannot be empty']);
            exit;
        }

        // Save to DB
        $pengaturanModel = $this->model('PengaturanAplikasi_model');
        $success = $pengaturanModel->updateCronSecret($token);

        if ($success) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to save token']);
        }
        exit;
    }

    /**
     * Halaman Riwayat Login
     */
    public function riwayatLogin()
    {
        $this->data['judul'] = 'Riwayat Login';

        // Get query params
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $limit = (int) ($_GET['limit'] ?? 25);
        $search = trim($_GET['search'] ?? '');
        $status = $_GET['status'] ?? 'all';
        $sortBy = $_GET['sort'] ?? 'login_at';
        $sortOrder = $_GET['order'] ?? 'DESC';

        // Validate limit options
        if (!in_array($limit, [10, 25, 50, 100])) {
            $limit = 25;
        }

        $offset = ($page - 1) * $limit;

        $loginHistoryModel = $this->model('LoginHistory_model');

        // Get data
        $this->data['logs'] = $loginHistoryModel->getAll([
            'limit' => $limit,
            'offset' => $offset,
            'search' => $search,
            'status' => $status,
            'sort_by' => $sortBy,
            'sort_order' => $sortOrder
        ]);

        // Get total count for pagination
        $totalRecords = $loginHistoryModel->countAll([
            'search' => $search,
            'status' => $status
        ]);
        $totalPages = max(1, ceil($totalRecords / $limit));

        // Get stats
        $this->data['stats'] = $loginHistoryModel->getStats();

        // Pagination data
        $this->data['pagination'] = [
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_records' => $totalRecords,
            'limit' => $limit
        ];

        // Filter data
        $this->data['filter'] = [
            'search' => $search,
            'status' => $status,
            'sort' => $sortBy,
            'order' => $sortOrder
        ];

        // Cleanup old records (every request, but it's a quick query)
        $loginHistoryModel->deleteOldRecords(90);

        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/riwayat_login', $this->data);
        $this->view('templates/footer', $this->data);
    }

    // ===============================================
    // WA GATEWAY MANAGEMENT - Multi Account Rotation
    // ===============================================

    /**
     * Halaman Pengaturan WA Gateway
     */
    public function waGateway()
    {
        $this->data['judul'] = 'Pengaturan WA Gateway';

        // Load model
        $waAccountModel = $this->model('WaAccount_model');
        $pengaturanModel = $this->model('PengaturanAplikasi_model');

        // Get data
        $this->data['accounts'] = $waAccountModel->getAll();
        $this->data['stats'] = $waAccountModel->getStats();
        $pengaturan = $pengaturanModel->getPengaturan();
        $this->data['rotation_enabled'] = ($pengaturan['wa_rotation_enabled'] ?? 0) == 1;
        $this->data['rotation_mode'] = $pengaturan['wa_rotation_mode'] ?? 'round_robin';
        $this->data['admin_wa_number'] = $pengaturan['admin_wa_number'] ?? '';

        // Load Fonnte class untuk akses providers list
        require_once APPROOT . '/app/core/Fonnte.php';
        $this->data['providers'] = Fonnte::$providers;

        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/wa_gateway', $this->data);
        $this->view('templates/footer', $this->data);
    }

    /**
     * Test Koneksi WA Gateway (Form Submit)
     */
    public function processTestWaGateway()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/admin/waGateway');
            exit;
        }

        $target = trim($_POST['target'] ?? '');
        $message = trim($_POST['message'] ?? '');
        $accountId = $_POST['account_id'] ?? 'auto';

        if (empty($target) || empty($message)) {
            Flasher::setFlash('Nomor tujuan dan pesan tidak boleh kosong', 'danger');
            header('Location: ' . BASEURL . '/admin/waGateway');
            exit;
        }

        require_once APPROOT . '/app/core/Fonnte.php';
        $fonnte = new Fonnte();

        // Jika pilih akun spesifik
        if ($accountId !== 'auto') {
            $accountModel = $this->model('WaAccount_model');
            $account = $accountModel->getById($accountId);

            if ($account) {
                // Configure Fonnte to use this specific account
                $fonnte->configureFromAccount($account);
            } else {
                Flasher::setFlash('Akun yang dipilih tidak ditemukan', 'danger');
                header('Location: ' . BASEURL . '/admin/waGateway');
                exit;
            }
        }

        // Kirim pesan
        $result = $fonnte->send($target, $message);

        // Parse result
        // Fonnte helper returns ['status' => bool, 'reason' => string] or raw AP response
        $isSuccess = isset($result['status']) && ($result['status'] === true || $result['status'] === 'true');

        // Determine reason/message
        $reason = 'Unknown response from server';
        if (isset($result['reason'])) {
            $reason = $result['reason'];
        } elseif (isset($result['message'])) {
            $reason = $result['message'];
        } elseif (isset($result['detail'])) {
            $reason = $result['detail'];
        } elseif ($isSuccess) {
            $reason = 'Pesan berhasil dikirim (No detail)';
        }

        // Detailed feedback
        if ($isSuccess) {
            $msg = "<strong>Sukses!</strong> Pesan berhasil dikirim ke {$target}.<br><small>Response: {$reason}</small>";
            Flasher::setFlash($msg, 'success');
        } else {
            $msg = "<strong>Gagal!</strong> Tidak dapat mengirim pesan.<br><small><strong>Reason:</strong> {$reason}</small>";
            // Jika ada debug info tambahan dari result
            if (isset($result['debug'])) {
                $msg .= "<br><small>Debug: " . htmlspecialchars($result['debug']) . "</small>";
            }
            Flasher::setFlash($msg, 'danger');
        }

        header('Location: ' . BASEURL . '/admin/waGateway');
        exit;
    }

    /**
     * Simpan pengaturan rotasi
     */
    public function prosesWaGateway()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $pengaturanModel = $this->model('PengaturanAplikasi_model');

            $rotationEnabled = isset($_POST['rotation_enabled']) ? 1 : 0;
            $rotationMode = $_POST['rotation_mode'] ?? 'round_robin';
            $adminWaNumber = $_POST['admin_wa_number'] ?? '';

            $pengaturanModel->updateSetting('wa_rotation_enabled', $rotationEnabled);
            $pengaturanModel->updateSetting('wa_rotation_mode', $rotationMode);
            $pengaturanModel->updateSetting('admin_wa_number', $adminWaNumber);

            Flasher::setFlash('Pengaturan rotasi WA berhasil disimpan.', 'success');
        }

        header('Location: ' . BASEURL . '/admin/waGateway');
        exit;
    }

    /**
     * Tambah akun WA baru
     */
    public function tambahWaAkun()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $waAccountModel = $this->model('WaAccount_model');

            $data = [
                'nama' => $_POST['nama'] ?? '',
                'provider' => $_POST['provider'] ?? 'fonnte',
                'api_url' => $_POST['api_url'] ?? '',
                'token' => $_POST['token'] ?? '',
                'username' => $_POST['username'] ?? '',
                'password' => $_POST['password'] ?? '',
                'is_active' => isset($_POST['is_active']) ? 1 : 0,
                'daily_limit' => (int) ($_POST['daily_limit'] ?? 100)
            ];

            if (empty($data['nama'])) {
                Flasher::setFlash('Nama akun wajib diisi.', 'danger');
            } else {
                $waAccountModel->create($data);
                Flasher::setFlash('Akun WA berhasil ditambahkan.', 'success');
            }
        }

        header('Location: ' . BASEURL . '/admin/waGateway');
        exit;
    }

    /**
     * Edit akun WA
     */
    public function editWaAkun($id = null)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $id) {
            $waAccountModel = $this->model('WaAccount_model');

            $data = [
                'nama' => $_POST['nama'] ?? '',
                'provider' => $_POST['provider'] ?? 'fonnte',
                'api_url' => $_POST['api_url'] ?? '',
                'token' => $_POST['token'] ?? '',
                'username' => $_POST['username'] ?? '',
                'password' => $_POST['password'] ?? '',
                'is_active' => isset($_POST['is_active']) ? 1 : 0,
                'daily_limit' => (int) ($_POST['daily_limit'] ?? 100)
            ];

            $waAccountModel->update($id, $data);
            Flasher::setFlash('Akun WA berhasil diperbarui.', 'success');
        }

        header('Location: ' . BASEURL . '/admin/waGateway');
        exit;
    }

    /**
     * Hapus akun WA
     */
    public function hapusWaAkun($id = null)
    {
        if ($id) {
            $waAccountModel = $this->model('WaAccount_model');
            $waAccountModel->delete($id);
            Flasher::setFlash('Akun WA berhasil dihapus.', 'success');
        }

        header('Location: ' . BASEURL . '/admin/waGateway');
        exit;
    }

    /**
     * Toggle aktif/nonaktif akun WA
     */
    public function toggleWaAkun($id = null)
    {
        if ($id) {
            $waAccountModel = $this->model('WaAccount_model');
            $waAccountModel->toggleActive($id);
            Flasher::setFlash('Status akun WA berhasil diubah.', 'success');
        }

        header('Location: ' . BASEURL . '/admin/waGateway');
        exit;
    }

    /**
     * Reset counter harian semua akun
     */
    public function resetWaCounter()
    {
        $waAccountModel = $this->model('WaAccount_model');
        $waAccountModel->resetAllCounters();
        Flasher::setFlash('Counter harian semua akun berhasil direset.', 'success');

        header('Location: ' . BASEURL . '/admin/waGateway');
        exit;
    }

    // =================================================================
    // PENGATURAN FIELD DATA SISWA
    // =================================================================

    /**
     * Halaman Pengaturan Field Data Siswa
     */
    public function pengaturanFieldSiswa()
    {
        $this->data['judul'] = 'Pengaturan Field Data Siswa';

        $pengaturanModel = $this->model('PengaturanAplikasi_model');
        $this->data['fieldConfig'] = $pengaturanModel->getFieldSiswaConfig();
        $this->data['mandatoryFields'] = $pengaturanModel->getMandatoryFields();

        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/pengaturan_field_siswa', $this->data);
        $this->view('templates/footer');
    }

    /**
     * Simpan Pengaturan Field Data Siswa
     */
    public function simpanPengaturanFieldSiswa()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/admin/pengaturanFieldSiswa');
            exit;
        }

        $pengaturanModel = $this->model('PengaturanAplikasi_model');
        $mandatoryFields = $pengaturanModel->getMandatoryFields();

        // Get submitted fields
        $submittedFields = $_POST['fields'] ?? [];

        // Build config array (all fields start as false, checked ones become true)
        $defaultConfig = $pengaturanModel->getDefaultFieldConfig();
        $newConfig = [];

        foreach ($defaultConfig as $field => $defaultValue) {
            if (in_array($field, $mandatoryFields)) {
                // Mandatory fields are always true
                $newConfig[$field] = true;
            } else {
                // Optional fields based on checkbox
                $newConfig[$field] = isset($submittedFields[$field]);
            }
        }

        if ($pengaturanModel->saveFieldSiswaConfig($newConfig)) {
            Flasher::setFlash('Pengaturan field data siswa berhasil disimpan', 'success');
        } else {
            Flasher::setFlash('Gagal menyimpan pengaturan', 'danger');
        }

        header('Location: ' . BASEURL . '/admin/pengaturanFieldSiswa');
        exit;
    }

    // =================================================================
    // PENGATURAN NOTIFIKASI ABSENSI
    // =================================================================

    /**
     * Halaman pengaturan notifikasi absensi
     */
    public function pengaturanNotifikasiAbsensi()
    {
        $this->data['judul'] = 'Pengaturan Notifikasi Absensi';

        $pengaturanModel = $this->model('PengaturanAplikasi_model');
        $pengaturanSistemModel = $this->model('PengaturanSistem_model');
        $kelasModel = $this->model('Kelas_model');
        $grupModel = $this->model('KelasGrupWa_model');

        $id_tp = $_SESSION['id_tp_aktif'] ?? 0;

        $this->data['pengaturan'] = $pengaturanModel->getPengaturan();
        $this->data['notif_mode'] = $pengaturanSistemModel->get('wa_notif_absensi_mode') ?? 'personal';
        $this->data['notif_enabled'] = ($pengaturanSistemModel->get('wa_notif_absensi_enabled') ?? '1') === '1';
        $this->data['kelas_list'] = $kelasModel->getKelasByTP($id_tp);
        $this->data['grup_data'] = $grupModel->getAllGrupWithKelas();

        // Detect WA Gateway provider for sync button
        $this->data['wa_provider'] = $pengaturanSistemModel->get('wa_gateway_provider')
            ?? $this->data['pengaturan']['wa_gateway_provider']
            ?? 'fonnte';

        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/pengaturan_notifikasi_absensi', $this->data);
        $this->view('templates/footer');
    }

    /**
     * Sync daftar grup WhatsApp dari Fonnte API
     * Menampilkan daftar grup yang tersedia untuk dipilih
     */
    public function syncGrupFonnte()
    {
        require_once APPROOT . '/app/core/Fonnte.php';
        $fonnte = new Fonnte();

        // Step 1: Fetch/update grup dari WA ke Fonnte server
        $fetchResult = $fonnte->fetchWhatsAppGroups();
        error_log("[AdminController::syncGrupFonnte] Fetch result: " . json_encode($fetchResult));

        // Step 2: Ambil daftar grup dari Fonnte
        $getResult = $fonnte->getWhatsAppGroups();
        error_log("[AdminController::syncGrupFonnte] Get result: " . json_encode($getResult));

        if (isset($getResult['data']) && is_array($getResult['data'])) {
            $groups = $getResult['data'];
            $grupCount = count($groups);

            // Simpan ke session untuk ditampilkan di UI
            $_SESSION['fonnte_groups'] = $groups;

            Flasher::setFlash("✅ Berhasil sync {$grupCount} grup dari Fonnte! Pilih grup dari daftar untuk menambahkan.", 'success');
        } else {
            $reason = $getResult['reason'] ?? $getResult['detail'] ?? 'Unknown error';
            Flasher::setFlash("❌ Gagal sync grup: {$reason}", 'danger');
        }

        header('Location: ' . BASEURL . '/admin/pengaturanNotifikasiAbsensi');
        exit;
    }

    /**
     * Sync daftar grup WhatsApp dari GOWA (Go-WhatsApp-Web-Multidevice) API
     */
    public function syncGrupGowa()
    {
        require_once APPROOT . '/app/core/Fonnte.php';
        $gowa = new Fonnte();

        // Ambil daftar grup dari GOWA
        $getResult = $gowa->getGowaGroups();
        error_log("[AdminController::syncGrupGowa] Get result: " . json_encode($getResult));

        if (isset($getResult['status']) && $getResult['status'] === true && isset($getResult['data'])) {
            $groups = $getResult['data'];
            $grupCount = count($groups);

            // Simpan ke session untuk ditampilkan di UI (format sesuai GOWA)
            $_SESSION['gowa_groups'] = $groups;

            Flasher::setFlash("✅ Berhasil sync {$grupCount} grup dari GOWA! Pilih grup dari daftar untuk menambahkan.", 'success');
        } else {
            $reason = $getResult['reason'] ?? 'Unknown error';
            Flasher::setFlash("❌ Gagal sync grup dari GOWA: {$reason}", 'danger');
        }

        header('Location: ' . BASEURL . '/admin/pengaturanNotifikasiAbsensi');
        exit;
    }

    /**
     * Simpan pengaturan mode notifikasi
     */
    public function simpanPengaturanNotifikasiAbsensi()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/admin/pengaturanNotifikasiAbsensi');
            exit;
        }

        $pengaturanSistemModel = $this->model('PengaturanSistem_model');

        $mode = $_POST['notif_mode'] ?? 'personal';
        $enabled = isset($_POST['notif_enabled']) ? '1' : '0';

        // Validate mode
        $allowedModes = ['personal', 'grup', 'both', 'off'];
        if (!in_array($mode, $allowedModes)) {
            $mode = 'personal';
        }

        $pengaturanSistemModel->set('wa_notif_absensi_mode', $mode);
        $pengaturanSistemModel->set('wa_notif_absensi_enabled', $enabled);

        Flasher::setFlash('Pengaturan notifikasi absensi berhasil disimpan', 'success');
        header('Location: ' . BASEURL . '/admin/pengaturanNotifikasiAbsensi');
        exit;
    }

    /**
     * Tambah grup WA untuk kelas
     */
    public function tambahGrupWaKelas()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/admin/pengaturanNotifikasiAbsensi');
            exit;
        }

        $grupModel = $this->model('KelasGrupWa_model');

        $id_kelas = intval($_POST['id_kelas'] ?? 0);
        $nama_grup = trim($_POST['nama_grup'] ?? '');
        $grup_wa_id = trim($_POST['grup_wa_id'] ?? '');

        if ($id_kelas > 0 && !empty($nama_grup) && !empty($grup_wa_id)) {
            if ($grupModel->addGrup($id_kelas, $nama_grup, $grup_wa_id)) {
                Flasher::setFlash('Grup WhatsApp berhasil ditambahkan', 'success');
            } else {
                Flasher::setFlash('Gagal menambahkan grup WhatsApp', 'danger');
            }
        } else {
            Flasher::setFlash('Data tidak lengkap', 'warning');
        }

        header('Location: ' . BASEURL . '/admin/pengaturanNotifikasiAbsensi');
        exit;
    }

    /**
     * Edit grup WA
     */
    public function editGrupWaKelas()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/admin/pengaturanNotifikasiAbsensi');
            exit;
        }

        $grupModel = $this->model('KelasGrupWa_model');

        $id = intval($_POST['id'] ?? 0);
        $nama_grup = trim($_POST['nama_grup'] ?? '');
        $grup_wa_id = trim($_POST['grup_wa_id'] ?? '');

        if ($id > 0 && !empty($nama_grup) && !empty($grup_wa_id)) {
            if ($grupModel->updateGrup($id, $nama_grup, $grup_wa_id)) {
                Flasher::setFlash('Grup WhatsApp berhasil diperbarui', 'success');
            } else {
                Flasher::setFlash('Gagal memperbarui grup WhatsApp', 'danger');
            }
        } else {
            Flasher::setFlash('Data tidak lengkap', 'warning');
        }

        header('Location: ' . BASEURL . '/admin/pengaturanNotifikasiAbsensi');
        exit;
    }

    /**
     * Toggle aktif/nonaktif grup WA
     */
    public function toggleGrupWaKelas($id)
    {
        $grupModel = $this->model('KelasGrupWa_model');

        if ($grupModel->toggleActive(intval($id))) {
            Flasher::setFlash('Status grup WhatsApp berhasil diubah', 'success');
        } else {
            Flasher::setFlash('Gagal mengubah status grup', 'danger');
        }

        header('Location: ' . BASEURL . '/admin/pengaturanNotifikasiAbsensi');
        exit;
    }

    /**
     * Test kirim pesan ke grup WA
     */
    public function testGrupWa($id)
    {
        $grupModel = $this->model('KelasGrupWa_model');
        $grup = $grupModel->getGrupById($id);

        if (!$grup) {
            Flasher::setFlash('Grup tidak ditemukan', 'danger');
            header('Location: ' . BASEURL . '/admin/pengaturanNotifikasiAbsensi');
            exit;
        }

        $queueModel = $this->model('WaQueue_model');
        $namaGrup = $grup['nama_grup'];
        $grupId = $grup['grup_wa_id'];

        // Pesan Default
        $pesan = "*TEST PESAN WA GRUP*\n\n";
        $pesan .= "Halo grup *{$namaGrup}*,\n";
        $pesan .= "Ini adalah pesan percobaan dari Sistem Notifikasi Absensi.\n";
        $pesan .= "Jika pesan ini diterima, berarti ID Grup *{$grupId}* sudah benar.\n\n";
        $pesan .= "Waktu kirim: " . date('d-m-Y H:i:s');

        // Tambahkan ke antrian (akan otomatis cek mode queue/direct di model)
        // Metadata: { "grup_id": "...", "nama_grup": "..." }
        $queueId = $queueModel->addToQueue(
            $grupId,
            $pesan,
            'test_grup',
            ['grup_id' => $id, 'nama_grup' => $namaGrup]
        );

        if ($queueId) {
            Flasher::setFlash("Pesan test berhasil dibuat/dikirim ke grup {$namaGrup}", 'success');
        } else {
            Flasher::setFlash("Gagal membuat/mengirim pesan test ke grup {$namaGrup}", 'danger');
        }

        header('Location: ' . BASEURL . '/admin/pengaturanNotifikasiAbsensi');
        exit;
    }

    /**
     * Hapus grup WA
     */
    public function hapusGrupWaKelas($id)
    {
        $grupModel = $this->model('KelasGrupWa_model');

        if ($grupModel->deleteGrup(intval($id))) {
            Flasher::setFlash('Grup WhatsApp berhasil dihapus', 'success');
        } else {
            Flasher::setFlash('Gagal menghapus grup WhatsApp', 'danger');
        }

        header('Location: ' . BASEURL . '/admin/pengaturanNotifikasiAbsensi');
        exit;
    }

}


