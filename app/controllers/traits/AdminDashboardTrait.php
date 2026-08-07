<?php
// File: app/controllers/traits/AdminDashboardTrait.php
// v1.25.0 - Hasil pemecahan AdminController.php (domain: Dashboard, routing dasar, statistik & helper dashboard).
// Perilaku identik dengan method asli; hanya dipindahkan agar mudah dipelihara.

trait AdminDashboardTrait
{
function __construct()
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

function index()
    {
        error_log("AdminController::index() dipanggil - URL: " . ($_SERVER['REQUEST_URI'] ?? ''));
        header('Location: ' . BASEURL . '/admin/dashboard');
        exit;
    }

function setSemester()
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

function dashboard()
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

function getDashboardStats()
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

function calculateAttendanceToday()
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

function getRecentJournals()
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

function getAttendanceToday()
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

function getAttendanceTrend()
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

function getSystemAlerts()
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

function getSidebarData()
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

function getStats()
    {
        header('Content-Type: application/json');
        echo json_encode($this->getDashboardStats());
        exit;
    }

function setAktifTP()
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

function validateExcelData($excelData)
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

function generateBatchId()
    {
        return 'IMP_' . date('YmdHis') . '_' . uniqid();
    }

function logImportActivity($action, $details)
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

function cleanupImportLogs()
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

function validateUploadedFile($file)
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

function processUploadedExcel()
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

function processCSVFile($filePath)
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

function processExcelFile($filePath)
    {
        // Untuk implementasi sederhana, convert Excel ke CSV dulu
        // Atau gunakan library PHP Excel seperti PhpSpreadsheet
        // Implementasi fallback: return empty atau error
        throw new Exception("Excel file processing memerlukan library tambahan. Gunakan format CSV untuk sementara.");
    }

function batchDeleteSiswa()
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

function generatePasswordSiswa()
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
}
