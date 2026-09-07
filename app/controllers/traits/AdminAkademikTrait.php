<?php
// File: app/controllers/traits/AdminAkademikTrait.php
// v1.25.0 - Hasil pemecahan AdminController.php (domain: Tahun pelajaran, kelas, mapel, penugasan, keanggotaan, naik kelas, kelulusan).
// Perilaku identik dengan method asli; hanya dipindahkan agar mudah dipelihara.

trait AdminAkademikTrait
{
function tahunPelajaran()
    {
        $this->data['judul'] = 'Manajemen Tahun Pelajaran';
        $this->data['tp'] = $this->model('TahunPelajaran_model')->getAllTahunPelajaran();
        $this->data['all_semester'] = $this->model('TahunPelajaran_model')->getAllSemester();
        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/tahun_pelajaran', $this->data);
        $this->view('templates/footer', $this->data);
    }

function tambahTP()
    {
        $this->data['judul'] = 'Tambah Tahun Pelajaran';
        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/tambah_tp', $this->data);
        $this->view('templates/footer', $this->data);
    }

function prosesTambahTP()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if ($this->model('TahunPelajaran_model')->tambahDataTahunPelajaranDanSemester($_POST) > 0) {
                header('Location: ' . BASEURL . '/admin/tahunPelajaran');
                exit;
            }
        }
    }

function editTP($id)
    {
        $this->data['judul'] = 'Edit Tahun Pelajaran';
        $this->data['tp'] = $this->model('TahunPelajaran_model')->getTahunPelajaranById($id);
        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/edit_tp', $this->data);
        $this->view('templates/footer', $this->data);
    }

function prosesUpdateTP()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if ($this->model('TahunPelajaran_model')->updateDataTahunPelajaran($_POST) > 0) {
                header('Location: ' . BASEURL . '/admin/tahunPelajaran');
                exit;
            }
        }
    }

function hapusTP($id)
    {
        if ($this->model('TahunPelajaran_model')->hapusDataTahunPelajaran($id) > 0) {
            header('Location: ' . BASEURL . '/admin/tahunPelajaran');
            exit;
        }
    }

function setDefaultSemester($id_semester)
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

function kelas()
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

function tambahKelas()
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

function prosesTambahKelas()
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

function editKelas($id)
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

function hapusKelas($id)
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

function prosesUpdateKelas()
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

function mapel()
    {
        $this->data['judul'] = 'Manajemen Mata Pelajaran';
        $this->data['mapel'] = $this->model('Mapel_model')->getAllMapel();
        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/mapel', $this->data);
        $this->view('templates/footer', $this->data);
    }

function tambahMapel()
    {
        $this->data['judul'] = 'Tambah Mata Pelajaran';
        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/tambah_mapel', $this->data);
        $this->view('templates/footer', $this->data);
    }

function prosesTambahMapel()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if ($this->model('Mapel_model')->tambahDataMapel($_POST) > 0) {
                header('Location: ' . BASEURL . '/admin/mapel');
                exit;
            }
        }
    }

function editMapel($id)
    {
        $this->data['judul'] = 'Edit Mata Pelajaran';
        $this->data['mapel'] = $this->model('Mapel_model')->getMapelById($id);
        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/edit_mapel', $this->data);
        $this->view('templates/footer', $this->data);
    }

function prosesUpdateMapel()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if ($this->model('Mapel_model')->updateDataMapel($_POST) > 0) {
                header('Location: ' . BASEURL . '/admin/mapel');
                exit;
            }
        }
    }

function hapusMapel($id)
    {
        if ($this->model('Mapel_model')->hapusDataMapel($id) > 0) {
            header('Location: ' . BASEURL . '/admin/mapel');
            exit;
        }
    }

function penugasan()
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

function tambahPenugasan()
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

function prosesTambahPenugasan()
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

            // Cek apakah mapel+kelas sudah ditugaskan ke guru lain
            if (empty($errors)) {
                $existing = $this->model('Penugasan_model')->cekPenugasanMapelKelasExist(
                    $_POST['id_mapel'],
                    $_POST['id_kelas'],
                    $_POST['id_semester']
                );
                if ($existing && $existing['id_guru'] != $_POST['id_guru']) {
                    $pesan = 'Mapel dan kelas ini sudah ditugaskan ke ' . htmlspecialchars($existing['nama_guru']) . '.';
                    if ($existing['jumlah_jurnal'] > 0) {
                        $pesan .= ' Terdapat ' . $existing['jumlah_jurnal'] . ' riwayat jurnal. Gunakan Edit pada penugasan yang sudah ada agar riwayat tidak hilang.';
                    } else {
                        $pesan .= ' Hapus penugasan lama terlebih dahulu, atau gunakan Edit untuk mengganti guru.';
                    }
                    $errors[] = $pesan;
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

function checkPenugasanDuplikat()
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

function checkPenugasanMapelKelas()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['error' => 'Invalid request method']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            echo json_encode(['error' => 'Invalid JSON']);
            exit;
        }

        $id_mapel = $input['id_mapel'] ?? '';
        $id_kelas = $input['id_kelas'] ?? '';
        $id_semester = $_SESSION['id_semester_aktif'] ?? '';

        if (empty($id_mapel) || empty($id_kelas) || empty($id_semester)) {
            echo json_encode(['exists' => false]);
            exit;
        }

        $existing = $this->model('Penugasan_model')->cekPenugasanMapelKelasExist($id_mapel, $id_kelas, $id_semester);

        if ($existing) {
            echo json_encode([
                'exists' => true,
                'id_penugasan' => $existing['id_penugasan'],
                'nama_guru' => $existing['nama_guru'],
                'jumlah_jurnal' => (int)$existing['jumlah_jurnal']
            ]);
        } else {
            echo json_encode(['exists' => false]);
        }
        exit;
    }

function hapusPenugasan($id)
    {
        $jumlahJurnal = $this->model('Penugasan_model')->hitungJurnalByPenugasan($id);
        if ($jumlahJurnal > 0) {
            Flasher::setFlash('Penugasan ini memiliki ' . $jumlahJurnal . ' riwayat jurnal dan tidak bisa dihapus. Gunakan tombol Edit untuk mengganti guru.', 'danger');
            header('Location: ' . BASEURL . '/admin/penugasan');
            exit;
        }

        if ($this->model('Penugasan_model')->hapusDataPenugasan($id) > 0) {
            Flasher::setFlash('Penugasan berhasil dihapus.', 'success');
            header('Location: ' . BASEURL . '/admin/penugasan');
            exit;
        }
    }

function editPenugasan($id)
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

function prosesUpdatePenugasan()
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

function getPenugasanBySemesterApi($id_semester)
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

function prosesCopyPenugasan()
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

        $jumlahError = count($result['errors']);
        $detailError = $jumlahError > 0
            ? ' Gagal dipetakan: ' . implode('; ', array_slice($result['errors'], 0, 3))
              . ($jumlahError > 3 ? '; dan ' . ($jumlahError - 3) . ' lainnya.' : '.')
            : '';

        if ($result['copied'] > 0) {
            $message = "Berhasil copy {$result['copied']} penugasan.";
            if ($result['skipped'] > 0) {
                $message .= " ({$result['skipped']} di-skip karena sudah ada)";
            }
            $message .= $detailError;
            Flasher::setFlash($message, $jumlahError > 0 ? 'warning' : 'success');
        } elseif ($jumlahError > 0) {
            Flasher::setFlash('Tidak ada penugasan yang disalin.' . $detailError, 'warning');
        } elseif ($result['skipped'] > 0) {
            Flasher::setFlash("Semua {$result['skipped']} penugasan sudah ada di semester ini.", 'info');
        } else {
            Flasher::setFlash('Tidak ada penugasan yang bisa di-copy dari semester sumber.', 'warning');
        }

        header('Location: ' . BASEURL . '/admin/penugasan');
        exit;
    }

function keanggotaan()
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

function tambahAnggota($id_kelas)
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

function prosesTambahAnggota()
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

function hapusAnggota($id_keanggotaan)
    {
        if ($this->model('Keanggotaan_model')->hapusAnggotaKelas($id_keanggotaan) > 0) {
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit;
        }
    }

function naikKelas()
    {
        $this->data['judul'] = 'Naik Kelas';
        $this->data['daftar_tp'] = $this->model('TahunPelajaran_model')->getAllTahunPelajaran();
        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/naik_kelas', $this->data);
        $this->view('templates/footer', $this->data);
    }

function getKelasByTP($id_tp)
    {
        $dataKelas = $this->model('Kelas_model')->getKelasByTP($id_tp);
        header('Content-Type: application/json');
        echo json_encode($dataKelas);
    }

function getSiswaByKelas($id_kelas, $id_tp)
    {
        $dataSiswa = $this->model('Keanggotaan_model')->getSiswaByKelas($id_kelas, $id_tp);
        header('Content-Type: application/json');
        echo json_encode($dataSiswa);
    }

function prosesNaikKelas()
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

function kelulusan()
    {
        $this->data['judul'] = 'Kelulusan Siswa';
        $this->data['daftar_tp'] = $this->model('TahunPelajaran_model')->getAllTahunPelajaran();
        
        $id_tp = $_POST['id_tp'] ?? ($_SESSION['kelulusan_id_tp'] ?? null);
        $id_kelas = $_POST['id_kelas'] ?? ($_SESSION['kelulusan_id_kelas'] ?? null);

        if ($id_tp && $id_kelas) {
            unset($_SESSION['kelulusan_id_tp'], $_SESSION['kelulusan_id_kelas']);
            $this->data['id_tp_pilihan'] = $id_tp;
            $this->data['id_kelas_pilihan'] = $id_kelas;
            $this->data['daftar_siswa'] = $this->model('Keanggotaan_model')->getSiswaByKelas($id_kelas, $id_tp);
        }
        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/kelulusan', $this->data);
        $this->view('templates/footer', $this->data);
    }

function prosesKelulusan()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['siswa_terpilih'])) {
            $daftar_siswa = $_POST['siswa_terpilih'];
            $id_tp = $_POST['id_tp'] ?? null;
            $id_kelas = $_POST['id_kelas'] ?? null;
            if ($id_tp && $id_kelas) {
                $_SESSION['kelulusan_id_tp'] = $id_tp;
                $_SESSION['kelulusan_id_kelas'] = $id_kelas;
            }
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

function batalLulus()
    {
        $this->data['judul'] = 'Batal Lulus';
        $this->data['daftar_tp'] = $this->model('TahunPelajaran_model')->getAllTahunPelajaran();
        
        $id_tp = $_POST['id_tp'] ?? ($_SESSION['batal_lulus_id_tp'] ?? null);
        $id_kelas = $_POST['id_kelas'] ?? ($_SESSION['batal_lulus_id_kelas'] ?? null);

        if ($id_tp && $id_kelas) {
            unset($_SESSION['batal_lulus_id_tp'], $_SESSION['batal_lulus_id_kelas']);
            $this->data['id_tp_pilihan'] = $id_tp;
            $this->data['id_kelas_pilihan'] = $id_kelas;
            $this->data['daftar_siswa'] = $this->model('Keanggotaan_model')->getSiswaByKelas($id_kelas, $id_tp);
        }
        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/batal_lulus', $this->data);
        $this->view('templates/footer', $this->data);
    }

function prosesBatalLulus()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['siswa_terpilih'])) {
            $daftar_siswa = $_POST['siswa_terpilih'];
            $id_tp = $_POST['id_tp'] ?? null;
            $id_kelas = $_POST['id_kelas'] ?? null;
            if ($id_tp && $id_kelas) {
                $_SESSION['batal_lulus_id_tp'] = $id_tp;
                $_SESSION['batal_lulus_id_kelas'] = $id_kelas;
            }
            $jumlahSiswa = $this->model('Siswa_model')->batalkanKelulusan($daftar_siswa);
            Flasher::setFlash("Pembatalan kelulusan berhasil. Sebanyak $jumlahSiswa siswa telah dikembalikan statusnya menjadi Aktif.", 'success');
            header('Location: ' . BASEURL . '/admin/batalLulus');
            exit;
        } else {
            Flasher::setFlash('Gagal! Tidak ada siswa yang dipilih.', 'danger');
            header('Location: ' . BASEURL . '/admin/batalLulus');
            exit;
        }
    }
}
