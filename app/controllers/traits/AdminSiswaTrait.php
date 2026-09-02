<?php
// File: app/controllers/traits/AdminSiswaTrait.php
// v1.25.0 - Hasil pemecahan AdminController.php (domain: CRUD siswa, dokumen siswa, import PSB/Excel, kartu login).
// Perilaku identik dengan method asli; hanya dipindahkan agar mudah dipelihara.

trait AdminSiswaTrait
{
function siswa()
    {
        $this->data['judul'] = 'Manajemen Siswa';
        $this->data['siswa'] = $this->model('Siswa_model')->getAllSiswa();
        // v1.26.0 - Masker password_plain agar tidak bocor ke HTML (kecuali kartu login)
        $this->data['siswa'] = PasswordMask::maskRows($this->data['siswa']);
        $id_tp = $_SESSION['id_tp_aktif'] ?? $this->model('TahunPelajaran_model')->getTahunPelajaranAktif()['id_tp'];
        $this->data['kelas_list'] = $this->model('Kelas_model')->getAllKelasWithDetails($id_tp);

        // Load field configuration
        $pengaturanModel = $this->model('PengaturanAplikasi_model');
        $this->data['fieldConfig'] = $pengaturanModel->getFieldSiswaConfig();

        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/siswa', $this->data);
        $this->view('templates/footer', $this->data);
    }

    /**
     * v1.26.0 - Lihat password_plain on-demand (AJAX, khusus admin).
     * Password tidak lagi dirender di HTML daftar; diambil hanya saat
     * tombol "Lihat" ditekan dari halaman admin/siswa atau admin/guru.
     */
    function lihatPassword()
    {
        header('Content-Type: application/json');

        if (($_SESSION['role'] ?? '') !== 'admin') {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Akses ditolak.']);
            exit;
        }

        // Hanya terima permintaan AJAX dari halaman admin
        if (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') !== 'XMLHttpRequest') {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Permintaan tidak valid.']);
            exit;
        }

        $id_ref = filter_var($_GET['id_ref'] ?? 0, FILTER_VALIDATE_INT);
        $role = $_GET['role'] ?? '';
        if (!$id_ref || !in_array($role, ['siswa', 'guru'], true)) {
            echo json_encode(['success' => false, 'message' => 'Parameter tidak valid.']);
            exit;
        }

        $user = $this->model('User_model')->getByRef($id_ref, $role);
        if (!$user || empty($user['password_plain'])) {
            echo json_encode(['success' => false, 'message' => 'Password belum diset.']);
            exit;
        }

        echo json_encode(['success' => true, 'password' => $user['password_plain']]);
        exit;
    }

function tambahSiswa()
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

function prosesTambahSiswa()
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

function editSiswa($id)
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

function prosesUpdateSiswa()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $idSiswa = $_POST['id_siswa'] ?? 0;

            // Preserve existing foto (foto dikelola via modal AJAX terpisah)
            $siswa = $this->model('Siswa_model')->getSiswaById($idSiswa);
            $_POST['foto'] = $siswa['foto'] ?? null;

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

    /**
     * Handle foto upload from webcam (base64) or file input.
     * Returns R2 public URL on success, null if no upload attempted.
     */
    private function handleFotoUpload($idSiswa)
    {
        $base64 = $_POST['foto_base64'] ?? '';
        $hasFile = !empty($_FILES['foto_file']['tmp_name']) && $_FILES['foto_file']['error'] === UPLOAD_ERR_OK;

        if (empty($base64) && !$hasFile) {
            return null;
        }

        require_once APPROOT . '/app/core/R2Storage.php';

        if (!R2Storage::isConfigured()) {
            Flasher::setFlash('Penyimpanan R2 belum dikonfigurasi. Foto tidak diupload.', 'warning');
            return null;
        }

        $r2 = new R2Storage();

        // Delete old foto if exists
        $siswa = $this->model('Siswa_model')->getSiswaById($idSiswa);
        if (!empty($siswa['foto'])) {
            $oldKey = $this->extractR2Key($siswa['foto']);
            if ($oldKey) {
                $r2->delete($oldKey);
            }
        }

        $nisn = $siswa['nisn'] ?? $idSiswa;
        $key = 'foto-siswa/' . $nisn . '_' . time() . '.jpg';

        if (!empty($base64)) {
            $processed = R2Storage::processImage($base64, 400, 400, 85);
            if (!$processed) {
                Flasher::setFlash('Gagal memproses foto dari kamera', 'danger');
                return null;
            }
            $result = $r2->upload($processed, $key, 'image/jpeg');
        } else {
            $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
            if (!in_array($_FILES['foto_file']['type'], $allowed)) {
                Flasher::setFlash('Format foto tidak didukung. Gunakan JPG, PNG, atau WebP.', 'danger');
                return null;
            }
            if ($_FILES['foto_file']['size'] > 5 * 1024 * 1024) {
                Flasher::setFlash('Ukuran foto maksimal 5MB', 'danger');
                return null;
            }
            $processed = R2Storage::processImage($_FILES['foto_file']['tmp_name'], 400, 400, 85);
            if (!$processed) {
                Flasher::setFlash('Gagal memproses foto', 'danger');
                return null;
            }
            $result = $r2->upload($processed, $key, 'image/jpeg');
        }

        if ($result['success']) {
            return $result['url'];
        }

        Flasher::setFlash('Gagal mengupload foto: ' . ($result['error'] ?? 'Unknown error'), 'danger');
        return null;
    }

    private function extractR2Key($url)
    {
        if (empty($url) || !defined('R2_PUBLIC_URL') || empty(R2_PUBLIC_URL)) {
            return null;
        }
        $prefix = rtrim(R2_PUBLIC_URL, '/') . '/';
        if (strpos($url, $prefix) === 0) {
            return substr($url, strlen($prefix));
        }
        return null;
    }

    private function deleteFotoFromR2($idSiswa)
    {
        $siswa = $this->model('Siswa_model')->getSiswaById($idSiswa);
        if (empty($siswa['foto'])) return;

        require_once APPROOT . '/app/core/R2Storage.php';
        if (!R2Storage::isConfigured()) return;

        $key = $this->extractR2Key($siswa['foto']);
        if ($key) {
            $r2 = new R2Storage();
            $r2->delete($key);
        }
    }

    private function invalidateFotoCache($idSiswa)
    {
        $siswa = $this->model('Siswa_model')->getSiswaById($idSiswa);
        if (empty($siswa['foto'])) return;
        $cacheDir = APPROOT . '/tmp/foto_cache';
        $cacheFile = $cacheDir . '/' . md5($siswa['foto']) . '.jpg';
        if (file_exists($cacheFile)) {
            @unlink($cacheFile);
        }
    }

    function simpanFotoSiswaAjax()
    {
        header('Content-Type: application/json');

        if (($_SESSION['role'] ?? '') !== 'admin') {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Akses ditolak.']);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit;
        }

        $idSiswa = filter_var($_POST['id_siswa'] ?? 0, FILTER_VALIDATE_INT);
        if (!$idSiswa) {
            echo json_encode(['success' => false, 'message' => 'ID siswa tidak valid']);
            exit;
        }

        $siswa = $this->model('Siswa_model')->getSiswaById($idSiswa);
        if (!$siswa) {
            echo json_encode(['success' => false, 'message' => 'Siswa tidak ditemukan']);
            exit;
        }

        $base64 = $_POST['foto_base64'] ?? '';
        $hasFile = !empty($_FILES['foto_file']['tmp_name']) && $_FILES['foto_file']['error'] === UPLOAD_ERR_OK;

        if (empty($base64) && !$hasFile) {
            echo json_encode(['success' => false, 'message' => 'Tidak ada foto yang dikirim']);
            exit;
        }

        require_once APPROOT . '/app/core/R2Storage.php';

        if (!R2Storage::isConfigured()) {
            echo json_encode(['success' => false, 'message' => 'Penyimpanan R2 belum dikonfigurasi']);
            exit;
        }

        $r2 = new R2Storage();

        // Invalidate cache & delete old foto
        $this->invalidateFotoCache($idSiswa);
        if (!empty($siswa['foto'])) {
            $oldKey = $this->extractR2Key($siswa['foto']);
            if ($oldKey) {
                $r2->delete($oldKey);
            }
        }

        $nisn = $siswa['nisn'] ?? $idSiswa;
        $key = 'foto-siswa/' . $nisn . '_' . time() . '.jpg';

        // Crop coordinates from frontend cropper (optional)
        $cropX = isset($_POST['crop_x']) ? (int)$_POST['crop_x'] : null;
        $cropY = isset($_POST['crop_y']) ? (int)$_POST['crop_y'] : null;
        $cropW = isset($_POST['crop_w']) ? (int)$_POST['crop_w'] : null;
        $cropH = isset($_POST['crop_h']) ? (int)$_POST['crop_h'] : null;
        $hasCrop = ($cropX !== null && $cropY !== null && $cropW !== null && $cropH !== null);

        if (!empty($base64)) {
            $processed = R2Storage::processImage(
                $base64, 300, 400, 85,
                $hasCrop ? $cropX : null, $hasCrop ? $cropY : null,
                $hasCrop ? $cropW : null, $hasCrop ? $cropH : null
            );
            if (!$processed) {
                echo json_encode(['success' => false, 'message' => 'Gagal memproses foto dari kamera']);
                exit;
            }
            $result = $r2->upload($processed, $key, 'image/jpeg');
        } else {
            $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
            if (!in_array($_FILES['foto_file']['type'], $allowed)) {
                echo json_encode(['success' => false, 'message' => 'Format foto tidak didukung. Gunakan JPG, PNG, atau WebP.']);
                exit;
            }
            if ($_FILES['foto_file']['size'] > 5 * 1024 * 1024) {
                echo json_encode(['success' => false, 'message' => 'Ukuran foto maksimal 5MB']);
                exit;
            }
            $processed = R2Storage::processImage(
                $_FILES['foto_file']['tmp_name'], 300, 400, 85,
                $hasCrop ? $cropX : null, $hasCrop ? $cropY : null,
                $hasCrop ? $cropW : null, $hasCrop ? $cropH : null
            );
            if (!$processed) {
                echo json_encode(['success' => false, 'message' => 'Gagal memproses foto']);
                exit;
            }
            $result = $r2->upload($processed, $key, 'image/jpeg');
        }

        if ($result['success']) {
            $this->model('Siswa_model')->updateFotoSiswa($idSiswa, $result['url']);
            echo json_encode([
                'success' => true,
                'message' => 'Foto siswa berhasil diperbarui',
                'foto_url' => BASEURL . '/foto/siswa/' . $idSiswa . '?v=' . time()
            ]);
            exit;
        }

        echo json_encode(['success' => false, 'message' => 'Gagal mengupload foto: ' . ($result['error'] ?? 'Unknown error')]);
        exit;
    }

    function hapusFotoSiswaAjax()
    {
        header('Content-Type: application/json');

        if (($_SESSION['role'] ?? '') !== 'admin') {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Akses ditolak.']);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit;
        }

        $idSiswa = filter_var($_POST['id_siswa'] ?? 0, FILTER_VALIDATE_INT);
        if (!$idSiswa) {
            echo json_encode(['success' => false, 'message' => 'ID siswa tidak valid']);
            exit;
        }

        $this->invalidateFotoCache($idSiswa);
        $this->deleteFotoFromR2($idSiswa);
        $this->model('Siswa_model')->updateFotoSiswa($idSiswa, null);

        echo json_encode(['success' => true, 'message' => 'Foto siswa berhasil dihapus']);
        exit;
    }

function hapusSiswa($id)
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

function bulkHapusSiswa()
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

function importPsb()
    {
        $this->data['judul'] = 'Import Siswa dari PSB';
        $this->data['daftar_tp'] = $this->model('TahunPelajaran_model')->getAllTahunPelajaran();

        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/import_psb', $this->data);
        $this->view('templates/footer', $this->data);
    }

function getCandidates()
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

function processImport()
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

function batalkanImport($id_siswa = null)
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

function dokumenSiswa($id_siswa)
    {
        $this->data['judul'] = 'Dokumen Siswa';
        $this->data['siswa'] = $this->model('Siswa_model')->getSiswaById($id_siswa);
        $this->data['dokumen'] = $this->model('Siswa_model')->getDokumenSiswa($id_siswa);
        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/dokumen_siswa', $this->data);
        $this->view('templates/footer', $this->data);
    }

function uploadDokumenSiswa($id_siswa_param = null)
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

function hapusDokumenSiswa($id_dokumen, $id_siswa)
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

function monitoringDokumen()
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

function cetakRekapDokumen()
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

function getDokumenSiswaPartial($id_siswa)
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

function lihatDokumenSiswa($id_dokumen)
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

function downloadDokumenSiswa($id_dokumen)
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

function importSiswa()
    {
        $this->data['judul'] = 'Import Data Siswa Excel';
        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/import_siswa', $this->data);
        $this->view('templates/footer', $this->data);
    }

function prosesImportSiswa()
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

function processImportData($excelData)
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

function insertSiswaWithAccount($data)
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

function getExistingNisn()
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

function downloadTemplateSiswa()
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

function downloadDataSiswaJson()
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

function cekNisnTersedia()
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

function exportSiswaExcel()
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

function previewImportSiswa()
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

function generateSimplePassword($nisn, $nama)
    {
        $lastDigits = substr($nisn, -3);
        $namePrefix = strtolower(substr(preg_replace('/[^a-zA-Z]/', '', $nama), 0, 3));
        return $lastDigits . $namePrefix;
    }

function cetakKartuLogin($id_kelas = null)
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

function cetakKartuLoginSiswa()
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
}
