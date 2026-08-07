<?php
// File: app/controllers/traits/AdminGuruTrait.php
// v1.25.0 - Hasil pemecahan AdminController.php (domain: CRUD guru & rekonsiliasi akun).
// Perilaku identik dengan method asli; hanya dipindahkan agar mudah dipelihara.

trait AdminGuruTrait
{
function guru()
    {
        $this->data['judul'] = 'Manajemen Guru';
        $this->data['guru'] = $this->model('Guru_model')->getAllGuru();
        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/guru', $this->data);
        $this->view('templates/footer', $this->data);
    }

function tambahGuru()
    {
        $this->data['judul'] = 'Tambah Data Guru';
        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/tambah_guru', $this->data);
        $this->view('templates/footer', $this->data);
    }

function prosesTambahGuru()
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

function editGuru($id)
    {
        $this->data['judul'] = 'Edit Data Guru';
        $this->data['guru'] = $this->model('Guru_model')->getGuruById($id);
        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/edit_guru', $this->data);
        $this->view('templates/footer', $this->data);
    }

function prosesUpdateGuru()
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

function hapusGuru($id)
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

function bulkHapusGuru()
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

function generatePasswordGmail($id_guru)
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

function rekonsiliasiAkunGuru()
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
}
