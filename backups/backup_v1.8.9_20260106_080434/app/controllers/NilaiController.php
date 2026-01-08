<?php
// File: app/controllers/NilaiController.php

class NilaiController extends Controller {
    private $nilaiModel;
    private $data = [];

    public function __construct() {
        // Guard akses: Guru dan Wali Kelas bisa mengakses
        if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? null, ['guru', 'wali_kelas'])) {
            header('Location: ' . BASEURL . '/auth/login');
            exit;
        }

        // Set data umum
        $this->data['daftar_semester'] = $this->model('TahunPelajaran_model')->getAllSemester();
        $this->data['judul'] = 'Nilai';

        // Load model
        $this->nilaiModel = $this->model('Nilai_model');
    }

    /**
     * Helper method untuk menampilkan sidebar yang sesuai dengan role
     */
    private function loadSidebar()
    {
        $role = $_SESSION['role'] ?? 'guru';
        if ($role === 'wali_kelas') {
            $this->view('templates/sidebar_walikelas', $this->data);
        } else {
            $this->view('templates/sidebar_guru', $this->data);
        }
    }

    /**
     * Halaman utama menu Nilai - Redirect ke dashboard
     */
    public function index() {
        // Input nilai sudah dipindahkan ke dashboard guru
        // Redirect ke dashboard
        header('Location: ' . BASEURL . '/guru');
        exit;
    }

    /**
     * Halaman pilih kelas untuk input nilai
     */
    public function pilihKelas() {
        // Cek blokir akses RPP
        $blokir = cekBlokirAksesRPP('nilai');
        if ($blokir) {
            $this->data['judul'] = 'Akses Dibatasi';
            $this->data['blokir_akses'] = $blokir;
            $this->view('templates/header', $this->data);
            $this->loadSidebar();
            $this->view('guru/akses_diblokir', $this->data);
            $this->view('templates/footer');
            return;
        }

        $id_guru = $_SESSION['id_ref'] ?? null;
        $id_semester_aktif = $_SESSION['id_semester_aktif'] ?? null;

        if (!$id_guru || !$id_semester_aktif) {
            Flasher::setFlash('Gagal mengakses data. Pastikan Anda login dan semester aktif.', 'danger');
            header('Location: ' . BASEURL . '/nilai/index');
            exit;
        }

        // Ambil parameter jenis nilai dari URL (opsional)
        $jenis = $_GET['jenis'] ?? null; // harian, sts, sas
        $this->data['jenis_nilai'] = $jenis;

        $this->data['judul'] = 'Pilih Kelas - Input Nilai';
        $this->data['jadwal_mengajar'] = $this->model('Penugasan_model')->getPenugasanByGuru($id_guru, $id_semester_aktif);

        $this->view('templates/header', $this->data);
        $this->loadSidebar();
        $this->view('guru/nilai/pilih_kelas', $this->data);
        $this->view('templates/footer');
    }

    /**
     * Tampilkan daftar jurnal untuk dipilih sebelum input nilai harian
     */
    public function tugasHarian() {
        $id_guru = $_SESSION['id_ref'] ?? null;
        $id_semester_aktif = $_SESSION['id_semester_aktif'] ?? null;

        if (!$id_guru || !$id_semester_aktif) {
            Flasher::setFlash('Gagal mengakses data. Pastikan Anda login dan semester aktif.', 'danger');
            header('Location: ' . BASEURL . '/guru/dashboard');
            exit;
        }

        // Ambil parameter dari URL
        $id_penugasan = $_GET['id_penugasan'] ?? null;

        if (!$id_penugasan) {
            Flasher::setFlash('ID Penugasan tidak valid.', 'danger');
            header('Location: ' . BASEURL . '/nilai');
            exit;
        }

        // Ambil data penugasan
        $penugasan = $this->model('Penugasan_model')->getPenugasanById($id_penugasan);
        $this->data['penugasan'] = $penugasan;
        
        // Ambil daftar jurnal untuk penugasan ini
        $jurnal_list = $this->model('Jurnal_model')->getJurnalByPenugasan($id_penugasan);
        
        // Cek setiap jurnal apakah sudah ada nilainya
        foreach ($jurnal_list as &$jurnal) {
            $nilai_count = $this->nilaiModel->countNilaiByJurnal($jurnal['id_jurnal']);
            $jurnal['has_nilai'] = $nilai_count > 0;
            $jurnal['jumlah_nilai'] = $nilai_count;
        }
        
        $this->data['jurnal_list'] = $jurnal_list;

        // Tampilkan view pilih jurnal
        $this->view('templates/header', $this->data);
        $this->loadSidebar();
        $this->view('guru/nilai/pilih_jurnal', $this->data);
        $this->view('templates/footer');
    }

    /**
     * Form input nilai harian berdasarkan jurnal yang dipilih
     */
    public function inputNilaiHarian() {
        // Cek blokir akses RPP
        $blokir = cekBlokirAksesRPP('nilai');
        if ($blokir) {
            Flasher::setFlash($blokir['pesan'], 'danger');
            header('Location: ' . BASEURL . '/guru/dashboard');
            exit;
        }

        $id_guru = $_SESSION['id_ref'] ?? null;
        $id_semester_aktif = $_SESSION['id_semester_aktif'] ?? null;

        if (!$id_guru || !$id_semester_aktif) {
            Flasher::setFlash('Gagal mengakses data. Pastikan Anda login dan semester aktif.', 'danger');
            header('Location: ' . BASEURL . '/guru/dashboard');
            exit;
        }

        // Ambil parameter dari URL
        $id_jurnal = $_GET['id_jurnal'] ?? null;

        if (!$id_jurnal) {
            Flasher::setFlash('ID Jurnal tidak valid.', 'danger');
            header('Location: ' . BASEURL . '/nilai');
            exit;
        }

        // Ambil data jurnal dengan detail
        $jurnal = $this->model('Jurnal_model')->getJurnalDetailById($id_jurnal);
        if (!$jurnal) {
            Flasher::setFlash('Jurnal tidak ditemukan.', 'danger');
            header('Location: ' . BASEURL . '/nilai');
            exit;
        }
        $this->data['jurnal'] = $jurnal;
        
        // Ambil data penugasan
        $penugasan = $this->model('Penugasan_model')->getPenugasanById($jurnal['id_penugasan']);
        $this->data['penugasan'] = $penugasan;
        
        // Ambil siswa dan absensi berdasarkan jurnal
        $this->data['siswa_list'] = $this->model('Absensi_model')->getSiswaDanAbsensiByJurnal($id_jurnal);
        
        // Ambil nilai yang sudah ada
        $this->data['nilai_tugas_harian'] = $this->nilaiModel->getNilaiTugasHarianByJurnal($id_jurnal);

        // Siapkan data siswa
        $this->data['filtered_siswa'] = $this->data['siswa_list'];

        // Tampilkan view input nilai
        $this->view('templates/header', $this->data);
        $this->loadSidebar();
        $this->view('guru/nilai/nilai_tugas_harian', $this->data);
        $this->view('templates/footer');
    }

    /**
     * Proses penyimpanan nilai tugas harian
     */
    public function prosesSimpanTugasHarian() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // VALIDASI INPUT
            $id_jurnal = InputValidator::sanitizeInt($_POST['id_jurnal'] ?? 0);
            $id_penugasan = InputValidator::sanitizeInt($_POST['id_penugasan'] ?? 0);
            $nilai_array = $_POST['nilai'] ?? [];

            if (!$id_jurnal || !$id_penugasan) {
                Flasher::setFlash('Data tidak lengkap.', 'danger');
                header('Location: ' . BASEURL . '/nilai');
                exit;
            }

            // Validasi array nilai
            if (!is_array($nilai_array)) {
                Flasher::setFlash('Format data nilai tidak valid.', 'danger');
                header('Location: ' . BASEURL . '/nilai');
                exit;
            }

            // Get jurnal data (with detail including id_semester)
            $jurnal = $this->model('Jurnal_model')->getJurnalDetailById($id_jurnal);
            $penugasan = $this->model('Penugasan_model')->getPenugasanById($id_penugasan);
            
            // Ambil id_semester dari penugasan (lebih reliable)
            $id_semester = $penugasan['id_semester'] ?? null;
            
            if (!$id_semester) {
                Flasher::setFlash('Data semester tidak ditemukan.', 'danger');
                header('Location: ' . BASEURL . '/guru');
                exit;
            }
            
            $sukses = 0;
            $gagal = 0;

            foreach ($nilai_array as $id_siswa => $nilai) {
                // Sanitize ID siswa
                $id_siswa = InputValidator::sanitizeInt($id_siswa);
                if (!$id_siswa) {
                    $gagal++;
                    continue;
                }

                // Skip jika nilai kosong
                if (empty($nilai)) continue;
                
                // Validasi dan sanitasi nilai
                $nilai = InputValidator::sanitizeNilai($nilai);
                if ($nilai === false) {
                    $gagal++;
                    continue;
                }

                $data = [
                    'id_siswa' => $id_siswa,
                    'id_guru' => (int)$penugasan['id_guru'],
                    'id_mapel' => (int)$penugasan['id_mapel'],
                    'id_semester' => (int)$id_semester,
                    'jenis_nilai' => 'harian',
                    'keterangan' => $id_jurnal, // Simpan id_jurnal di keterangan
                    'nilai' => $nilai,
                    'tanggal_input' => date('Y-m-d')
                ];

                if ($this->nilaiModel->simpanNilaiHarian($data)) {
                    $sukses++;
                } else {
                    $gagal++;
                }
            }

            if ($sukses > 0) {
                Flasher::setFlash("Berhasil menyimpan $sukses nilai.", 'success');
            }
            if ($gagal > 0) {
                Flasher::setFlash("Gagal menyimpan $gagal nilai.", 'danger');
            }

            header('Location: ' . BASEURL . '/nilai/inputNilaiHarian?id_jurnal=' . $id_jurnal);
            exit;
        }

        header('Location: ' . BASEURL . '/nilai');
        exit;
    }

    /**
     * Proses edit nilai tugas harian
     */
    public function prosesEditTugasHarian() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // VALIDASI INPUT
            $id_nilai = InputValidator::sanitizeInt($_POST['id_nilai'] ?? 0);
            $nilai_baru = InputValidator::sanitizeNilai($_POST['nilai'] ?? 0);

            if (!$id_nilai || $nilai_baru === false) {
                Flasher::setFlash('Data tidak lengkap.', 'danger');
                header('Location: ' . BASEURL . '/nilai/tugasHarian?id_jurnal=' . $_POST['id_jurnal'] . '&id_penugasan=' . $_POST['id_penugasan']);
                exit;
            }

            if (!is_numeric($nilai_baru) || $nilai_baru < 0 || $nilai_baru > 100) {
                Flasher::setFlash('Nilai harus antara 0-100.', 'danger');
                header('Location: ' . BASEURL . '/nilai/tugasHarian?id_jurnal=' . $_POST['id_jurnal'] . '&id_penugasan=' . $_POST['id_penugasan']);
                exit;
            }

            // Edit nilai
            if ($this->nilaiModel->editNilai($id_nilai, $nilai_baru)) {
                Flasher::setFlash('Nilai berhasil diupdate.', 'success');
            } else {
                Flasher::setFlash('Gagal mengupdate nilai.', 'danger');
            }

            header('Location: ' . BASEURL . '/nilai/tugasHarian?id_jurnal=' . $_POST['id_jurnal'] . '&id_penugasan=' . $_POST['id_penugasan']);
            exit;
        }

        header('Location: ' . BASEURL . '/nilai');
        exit;
    }

    /**
     * Proses hapus nilai tugas harian
     */
    public function prosesHapusTugasHarian() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_nilai = $_POST['id_nilai'] ?? null;

            if (!$id_nilai) {
                Flasher::setFlash('Data tidak lengkap.', 'danger');
                header('Location: ' . BASEURL . '/nilai/tugasHarian?id_jurnal=' . $_POST['id_jurnal'] . '&id_penugasan=' . $_POST['id_penugasan']);
                exit;
            }

            // Hapus nilai
            if ($this->nilaiModel->hapusNilai($id_nilai)) {
                Flasher::setFlash('Nilai berhasil dihapus.', 'success');
            } else {
                Flasher::setFlash('Gagal menghapus nilai.', 'danger');
            }

            header('Location: ' . BASEURL . '/nilai/tugasHarian?id_jurnal=' . $_POST['id_jurnal'] . '&id_penugasan=' . $_POST['id_penugasan']);
            exit;
        }

        header('Location: ' . BASEURL . '/nilai');
        exit;
    }

    /**
     * Halaman detail nilai tengah semester
     */
    public function tengahSemester() {
        $id_guru = $_SESSION['id_ref'] ?? null;
        $id_semester_aktif = $_SESSION['id_semester_aktif'] ?? null;

        if (!$id_guru || !$id_semester_aktif) {
            Flasher::setFlash('Gagal mengakses data. Pastikan Anda login dan semester aktif.', 'danger');
            header('Location: ' . BASEURL . '/guru/dashboard');
            exit;
        }

        // Ambil parameter dari URL
        $id_penugasan = $_GET['id_penugasan'] ?? null;

        if (!$id_penugasan) {
            Flasher::setFlash('ID Penugasan tidak valid.', 'danger');
            header('Location: ' . BASEURL . '/nilai');
            exit;
        }

        // Ambil data penugasan dan siswa
        $penugasan = $this->model('Penugasan_model')->getPenugasanById($id_penugasan);
        $this->data['penugasan'] = $penugasan;
        $this->data['siswa_list'] = $this->model('Siswa_model')->getSiswaByKelas($penugasan['id_kelas'], $id_semester_aktif);
        $this->data['nilai_tengah_semester'] = $this->nilaiModel->getNilaiTengahSemesterByPenugasan($id_penugasan);

        // Siapkan data siswa
        $this->data['filtered_siswa'] = $this->data['siswa_list'];

        // Tampilkan view
        $this->view('templates/header', $this->data);
        $this->loadSidebar();
        $this->view('guru/nilai/nilai_tengah_semester', $this->data);
        $this->view('templates/footer');
    }

    /**
     * Proses penyimpanan nilai tengah semester
     */
    public function prosesSimpanTengahSemester() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_penugasan = $_POST['id_penugasan'] ?? null;
            $nilai_array = $_POST['nilai'] ?? [];

            if (!$id_penugasan) {
                Flasher::setFlash('Data penugasan tidak valid.', 'danger');
                header('Location: ' . BASEURL . '/guru');
                exit;
            }

            if (empty($nilai_array)) {
                Flasher::setFlash('Tidak ada nilai yang diisi.', 'warning');
                header('Location: ' . BASEURL . '/nilai/tengahSemester?id_penugasan=' . $id_penugasan);
                exit;
            }

            // Get penugasan data
            $penugasan = $this->model('Penugasan_model')->getPenugasanById($id_penugasan);
            
            // Ambil id_semester dari penugasan
            $id_semester = $penugasan['id_semester'] ?? null;
            
            if (!$id_semester) {
                Flasher::setFlash('Data semester tidak ditemukan.', 'danger');
                header('Location: ' . BASEURL . '/guru');
                exit;
            }
            
            $sukses = 0;
            $gagal = 0;

            foreach ($nilai_array as $id_siswa => $nilai) {
                // Skip jika nilai kosong
                if (empty($nilai)) continue;
                
                // Validasi nilai
                if (!is_numeric($nilai) || $nilai < 0 || $nilai > 100) {
                    $gagal++;
                    continue;
                }

                $data = [
                    'id_siswa' => $id_siswa,
                    'id_guru' => $penugasan['id_guru'],
                    'id_mapel' => $penugasan['id_mapel'],
                    'id_semester' => $id_semester,
                    'jenis_nilai' => 'sts',
                    'keterangan' => null,
                    'nilai' => $nilai,
                    'tanggal_input' => date('Y-m-d')
                ];

                if ($this->nilaiModel->simpanNilaiTengahSemester($data)) {
                    $sukses++;
                } else {
                    $gagal++;
                }
            }

            if ($sukses > 0) {
                Flasher::setFlash("Berhasil menyimpan $sukses nilai tengah semester.", 'success');
            }
            if ($gagal > 0) {
                Flasher::setFlash("Gagal menyimpan $gagal nilai.", 'warning');
            }

            header('Location: ' . BASEURL . '/nilai/tengahSemester?id_penugasan=' . $id_penugasan);
            exit;
        }

        header('Location: ' . BASEURL . '/guru');
        exit;
    }

    /**
     * Proses edit nilai tengah semester
     */
    public function prosesEditTengahSemester() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_nilai = $_POST['id_nilai'] ?? null;
            $nilai_baru = $_POST['nilai'] ?? null;

            if (!$id_nilai || !$nilai_baru) {
                Flasher::setFlash('Data tidak lengkap.', 'danger');
                header('Location: ' . BASEURL . '/nilai/tengahSemester?id_jurnal=' . $_POST['id_jurnal'] . '&id_penugasan=' . $_POST['id_penugasan']);
                exit;
            }

            if (!is_numeric($nilai_baru) || $nilai_baru < 0 || $nilai_baru > 100) {
                Flasher::setFlash('Nilai harus antara 0-100.', 'danger');
                header('Location: ' . BASEURL . '/nilai/tengahSemester?id_jurnal=' . $_POST['id_jurnal'] . '&id_penugasan=' . $_POST['id_penugasan']);
                exit;
            }

            // Edit nilai
            if ($this->nilaiModel->editNilai($id_nilai, $nilai_baru)) {
                Flasher::setFlash('Nilai berhasil diupdate.', 'success');
            } else {
                Flasher::setFlash('Gagal mengupdate nilai.', 'danger');
            }

            header('Location: ' . BASEURL . '/nilai/tengahSemester?id_jurnal=' . $_POST['id_jurnal'] . '&id_penugasan=' . $_POST['id_penugasan']);
            exit;
        }

        header('Location: ' . BASEURL . '/nilai');
        exit;
    }

    /**
     * Proses hapus nilai tengah semester
     */
    public function prosesHapusTengahSemester() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_nilai = $_POST['id_nilai'] ?? null;

            if (!$id_nilai) {
                Flasher::setFlash('Data tidak lengkap.', 'danger');
                header('Location: ' . BASEURL . '/nilai/tengahSemester?id_jurnal=' . $_POST['id_jurnal'] . '&id_penugasan=' . $_POST['id_penugasan']);
                exit;
            }

            // Hapus nilai
            if ($this->nilaiModel->hapusNilai($id_nilai)) {
                Flasher::setFlash('Nilai berhasil dihapus.', 'success');
            } else {
                Flasher::setFlash('Gagal menghapus nilai.', 'danger');
            }

            header('Location: ' . BASEURL . '/nilai/tengahSemester?id_jurnal=' . $_POST['id_jurnal'] . '&id_penugasan=' . $_POST['id_penugasan']);
            exit;
        }

        header('Location: ' . BASEURL . '/nilai');
        exit;
    }

    /**
     * Halaman detail nilai akhir semester
     */
    public function akhirSemester() {
        $id_guru = $_SESSION['id_ref'] ?? null;
        $id_semester_aktif = $_SESSION['id_semester_aktif'] ?? null;

        if (!$id_guru || !$id_semester_aktif) {
            Flasher::setFlash('Gagal mengakses data. Pastikan Anda login dan semester aktif.', 'danger');
            header('Location: ' . BASEURL . '/guru/dashboard');
            exit;
        }

        // Ambil parameter dari URL
        $id_penugasan = $_GET['id_penugasan'] ?? null;

        if (!$id_penugasan) {
            Flasher::setFlash('ID Penugasan tidak valid.', 'danger');
            header('Location: ' . BASEURL . '/nilai');
            exit;
        }

        // Ambil data penugasan dan siswa
        $penugasan = $this->model('Penugasan_model')->getPenugasanById($id_penugasan);
        $this->data['penugasan'] = $penugasan;
        $this->data['siswa_list'] = $this->model('Siswa_model')->getSiswaByKelas($penugasan['id_kelas'], $id_semester_aktif);
        $this->data['nilai_akhir_semester'] = $this->nilaiModel->getNilaiAkhirSemesterByPenugasan($id_penugasan);

        // Siapkan data siswa
        $this->data['filtered_siswa'] = $this->data['siswa_list'];

        // Tampilkan view
        $this->view('templates/header', $this->data);
        $this->loadSidebar();
        $this->view('guru/nilai/nilai_akhir_semester', $this->data);
        $this->view('templates/footer');
    }

    /**
     * Proses penyimpanan nilai akhir semester
     */
    public function prosesSimpanAkhirSemester() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_penugasan = $_POST['id_penugasan'] ?? null;
            $nilai_array = $_POST['nilai'] ?? [];

            if (!$id_penugasan) {
                Flasher::setFlash('Data penugasan tidak valid.', 'danger');
                header('Location: ' . BASEURL . '/guru');
                exit;
            }

            if (empty($nilai_array)) {
                Flasher::setFlash('Tidak ada nilai yang diisi.', 'warning');
                header('Location: ' . BASEURL . '/nilai/akhirSemester?id_penugasan=' . $id_penugasan);
                exit;
            }

            // Get penugasan data
            $penugasan = $this->model('Penugasan_model')->getPenugasanById($id_penugasan);
            
            // Ambil id_semester dari penugasan
            $id_semester = $penugasan['id_semester'] ?? null;
            
            if (!$id_semester) {
                Flasher::setFlash('Data semester tidak ditemukan.', 'danger');
                header('Location: ' . BASEURL . '/guru');
                exit;
            }
            
            $sukses = 0;
            $gagal = 0;

            foreach ($nilai_array as $id_siswa => $nilai) {
                // Skip jika nilai kosong
                if (empty($nilai)) continue;
                
                // Validasi nilai
                if (!is_numeric($nilai) || $nilai < 0 || $nilai > 100) {
                    $gagal++;
                    continue;
                }

                $data = [
                    'id_siswa' => $id_siswa,
                    'id_guru' => $penugasan['id_guru'],
                    'id_mapel' => $penugasan['id_mapel'],
                    'id_semester' => $id_semester,
                    'jenis_nilai' => 'sas',
                    'keterangan' => null,
                    'nilai' => $nilai,
                    'tanggal_input' => date('Y-m-d')
                ];

                if ($this->nilaiModel->simpanNilaiAkhirSemester($data)) {
                    $sukses++;
                } else {
                    $gagal++;
                }
            }

            if ($sukses > 0) {
                Flasher::setFlash("Berhasil menyimpan $sukses nilai akhir semester.", 'success');
            }
            if ($gagal > 0) {
                Flasher::setFlash("Gagal menyimpan $gagal nilai.", 'warning');
            }

            header('Location: ' . BASEURL . '/nilai/akhirSemester?id_penugasan=' . $id_penugasan);
            exit;
        }

        header('Location: ' . BASEURL . '/guru');
        exit;
    }

    /**
     * Proses edit nilai akhir semester
     */
    public function prosesEditAkhirSemester() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_nilai = $_POST['id_nilai'] ?? null;
            $nilai_baru = $_POST['nilai'] ?? null;

            if (!$id_nilai || !$nilai_baru) {
                Flasher::setFlash('Data tidak lengkap.', 'danger');
                header('Location: ' . BASEURL . '/nilai/akhirSemester?id_jurnal=' . $_POST['id_jurnal'] . '&id_penugasan=' . $_POST['id_penugasan']);
                exit;
            }

            if (!is_numeric($nilai_baru) || $nilai_baru < 0 || $nilai_baru > 100) {
                Flasher::setFlash('Nilai harus antara 0-100.', 'danger');
                header('Location: ' . BASEURL . '/nilai/akhirSemester?id_jurnal=' . $_POST['id_jurnal'] . '&id_penugasan=' . $_POST['id_penugasan']);
                exit;
            }

            // Edit nilai
            if ($this->nilaiModel->editNilai($id_nilai, $nilai_baru)) {
                Flasher::setFlash('Nilai berhasil diupdate.', 'success');
            } else {
                Flasher::setFlash('Gagal mengupdate nilai.', 'danger');
            }

            header('Location: ' . BASEURL . '/nilai/akhirSemester?id_jurnal=' . $_POST['id_jurnal'] . '&id_penugasan=' . $_POST['id_penugasan']);
            exit;
        }

        header('Location: ' . BASEURL . '/nilai');
        exit;
    }

    /**
     * Proses hapus nilai akhir semester
     */
    public function prosesHapusAkhirSemester() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_nilai = $_POST['id_nilai'] ?? null;

            if (!$id_nilai) {
                Flasher::setFlash('Data tidak lengkap.', 'danger');
                header('Location: ' . BASEURL . '/nilai/akhirSemester?id_jurnal=' . $_POST['id_jurnal'] . '&id_penugasan=' . $_POST['id_penugasan']);
                exit;
            }

            // Hapus nilai
            if ($this->nilaiModel->hapusNilai($id_nilai)) {
                Flasher::setFlash('Nilai berhasil dihapus.', 'success');
            } else {
                Flasher::setFlash('Gagal menghapus nilai.', 'danger');
            }

            header('Location: ' . BASEURL . '/nilai/akhirSemester?id_jurnal=' . $_POST['id_jurnal'] . '&id_penugasan=' . $_POST['id_penugasan']);
            exit;
        }

        header('Location: ' . BASEURL . '/nilai');
        exit;
    }

    /**
     * Download template Excel untuk import hasil CBT
     */
    public function downloadTemplateCBT() {
        // Validasi parameter
        $id_penugasan = $_GET['id_penugasan'] ?? null;
        
        if (!$id_penugasan) {
            Flasher::setFlash('Parameter tidak lengkap.', 'danger');
            header('Location: ' . BASEURL . '/guru');
            exit;
        }

        // Ambil data penugasan dan siswa
        $penugasan = $this->model('Penugasan_model')->getPenugasanById($id_penugasan);
        if (!$penugasan) {
            Flasher::setFlash('Data penugasan tidak ditemukan.', 'danger');
            header('Location: ' . BASEURL . '/guru');
            exit;
        }

        $siswa_list = $this->model('Kelas_model')->getSiswaByKelas($penugasan['id_kelas']);

        // Filter siswa yang masih aktif dan urutkan berdasarkan nomor absen
        $filtered_siswa = array_filter($siswa_list, function($siswa) {
            return $siswa['status_keanggotaan'] === 'aktif';
        });
        usort($filtered_siswa, function($a, $b) {
            return $a['nomor_absen'] - $b['nomor_absen'];
        });

        // Set headers untuk download Excel
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="Template_CBT_' . $penugasan['nama_mapel'] . '_' . $penugasan['nama_kelas'] . '.xls"');
        header('Cache-Control: max-age=0');

        // Generate Excel HTML dengan format CBT
        echo '
        <html xmlns:o="urn:schemas-microsoft-com:office:office"
              xmlns:x="urn:schemas-microsoft-com:office:excel"
              xmlns="http://www.w3.org/TR/REC-html40">
        <head>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
            <style>
                table { border-collapse: collapse; width: 100%; }
                th, td { border: 1px solid black; padding: 8px; text-align: center; }
                th { background-color: #4CAF50; color: white; font-weight: bold; }
                .header { background-color: #2196F3; color: white; font-weight: bold; font-size: 14pt; }
                .subheader { background-color: #FFC107; font-weight: bold; }
            </style>
        </head>
        <body>
            <table>
                <!-- Header Template CBT (5 baris) -->
                <tr>
                    <td colspan="8" class="header">HASIL UJIAN CBT</td>
                </tr>
                <tr>
                    <td colspan="3" class="subheader">Mata Pelajaran:</td>
                    <td colspan="5">' . htmlspecialchars($penugasan['nama_mapel']) . '</td>
                </tr>
                <tr>
                    <td colspan="3" class="subheader">Kelas:</td>
                    <td colspan="5">' . htmlspecialchars($penugasan['nama_kelas']) . '</td>
                </tr>
                <tr>
                    <td colspan="3" class="subheader">Tahun Pelajaran:</td>
                    <td colspan="5">' . htmlspecialchars($penugasan['tahun_pelajaran'] . ' - ' . $penugasan['nama_semester']) . '</td>
                </tr>
                <tr>
                    <td colspan="8" style="height: 10px;"></td>
                </tr>
                
                <!-- Header Kolom -->
                <tr style="background-color: #4CAF50; color: white; font-weight: bold;">
                    <th>NO</th>
                    <th>NOMOR UJIAN</th>
                    <th>NAMA LENGKAP</th>
                    <th>KELAS</th>
                    <th>BENAR</th>
                    <th>SALAH</th>
                    <th>KOSONG</th>
                    <th>NILAI AKHIR</th>
                </tr>';

        // Data siswa
        $no = 1;
        foreach ($filtered_siswa as $siswa) {
            echo '
                <tr>
                    <td>' . $no . '</td>
                    <td>' . htmlspecialchars($siswa['nisn']) . '</td>
                    <td style="text-align: left;">' . htmlspecialchars($siswa['nama_siswa']) . '</td>
                    <td>' . htmlspecialchars($penugasan['nama_kelas']) . '</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>';
            $no++;
        }

        echo '
            </table>
        </body>
        </html>';
        exit;
    }

    /**
     * Download PDF Rekap Nilai Harian
     */
    public function downloadNilaiHarianPDF() {
        $id_penugasan = $_GET['id_penugasan'] ?? null;
        
        if (!$id_penugasan) {
            Flasher::setFlash('Parameter tidak lengkap.', 'danger');
            header('Location: ' . BASEURL . '/guru');
            exit;
        }

        // Load DOMPDF
        require_once APPROOT . '/app/core/dompdf/autoload.inc.php';
        require_once APPROOT . '/config/qrcode.php';

        // Ambil data penugasan dan nilai
        $penugasan = $this->model('Penugasan_model')->getPenugasanById($id_penugasan);
        if (!$penugasan) {
            Flasher::setFlash('Data penugasan tidak ditemukan.', 'danger');
            header('Location: ' . BASEURL . '/guru');
            exit;
        }

        // Get id_tp from semester
        require_once APPROOT . '/app/core/Database.php';
        $db = new Database;
        $db->query('SELECT id_tp FROM semester WHERE id_semester = :id_semester');
        $db->bind('id_semester', $penugasan['id_semester']);
        $semester = $db->single();
        $id_tp = $semester['id_tp'] ?? null;

        $siswa_list = $this->model('Siswa_model')->getSiswaByKelas($penugasan['id_kelas'], $id_tp);
        
        // Sort by nomor_absen if available, otherwise by nama_siswa
        usort($siswa_list, function($a, $b) {
            if (isset($a['nomor_absen']) && isset($b['nomor_absen'])) {
                return $a['nomor_absen'] - $b['nomor_absen'];
            }
            return strcmp($a['nama_siswa'], $b['nama_siswa']);
        });

        // Ambil nilai harian per siswa
        $nilai_harian = [];
        foreach ($siswa_list as $siswa) {
            $nilai_list = $this->nilaiModel->getNilaiHarianByMapelSiswa($id_penugasan, $siswa['id_siswa']);
            $rata_nilai = 0;
            if (!empty($nilai_list)) {
                $rata_nilai = array_sum(array_column($nilai_list, 'nilai')) / count($nilai_list);
            }
            $nilai_harian[$siswa['id_siswa']] = round($rata_nilai, 2);
        }

        // Load kop and settings
        $pengaturanRapor = $this->model('PengaturanRapor_model')->getPengaturanByGuru($_SESSION['user_id'], $id_tp);
        $kopRapor = $pengaturanRapor['kop_rapor'] ?? '';

        // Kop header
        $kopHTML = '';
        if (!empty($kopRapor)) {
            $kopPath = 'public/img/kop/' . $kopRapor;
            if (file_exists($kopPath)) {
                $imageData = base64_encode(file_get_contents($kopPath));
                $imageType = pathinfo($kopPath, PATHINFO_EXTENSION);
                $imageSrc = 'data:image/' . $imageType . ';base64,' . $imageData;
                $kopHTML = '<div style="text-align:center; margin-bottom:15px;"><img src="' . $imageSrc . '" style="max-width:100%; height:auto; max-height:100px;"></div>';
            }
        }

        // Format waktu Indonesia
        $bulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $waktuCetak = date('d') . ' ' . $bulan[date('n') - 1] . ' ' . date('Y H:i');
        $namaPencetak = $_SESSION['nama_lengkap'] ?? ($_SESSION['nama_guru'] ?? 'Pengguna');

        $html = '<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Laporan Nilai Harian - ' . htmlspecialchars($penugasan['nama_mapel']) . '</title>
<style>
    @page { size: A4; margin: 15mm; }
    body { font-family: Arial, sans-serif; font-size: 11px; line-height: 1.4; color: #333; }
    .kop { text-align: center; margin-bottom: 15px; }
    .title { text-align: center; font-size: 14px; font-weight: bold; margin: 15px 0 10px; text-transform: uppercase; }
    .meta-table { margin-bottom: 15px; border: none; width: auto; }
    .meta-table td { border: none; padding: 2px 5px 2px 0; font-size: 11px; vertical-align: top; }
    .meta-table .label { font-weight: bold; white-space: nowrap; width: 130px; }
    .meta-table .colon { width: 10px; text-align: center; }
    .meta-table .value { }
    table { width: 100%; border-collapse: collapse; margin: 10px 0; }
    th, td { border: 1px solid #333; padding: 6px 8px; }
    th { background: #f0f0f0; font-weight: bold; text-align: center; font-size: 10px; }
    td { font-size: 10px; }
    .center { text-align: center; }
    .right { text-align: right; }
    .doc-info-box {
        margin-top: 25px;
        border: 1px solid #333;
        padding: 12px 15px;
        background: #f9f9f9;
        font-size: 10px;
    }
    .doc-info-title {
        font-weight: bold;
        font-size: 11px;
        margin-bottom: 8px;
        border-bottom: 1px solid #ccc;
        padding-bottom: 5px;
    }
    .doc-info-row {
        margin-bottom: 4px;
        line-height: 1.5;
    }
</style>
</head>
<body>
' . $kopHTML . '
<div class="title">LAPORAN NILAI HARIAN</div>

<table class="meta-table">
    <tr><td class="label">Mata Pelajaran</td><td class="colon">:</td><td class="value">' . htmlspecialchars($penugasan['nama_mapel']) . '</td></tr>
    <tr><td class="label">Kelas</td><td class="colon">:</td><td class="value">' . htmlspecialchars($penugasan['nama_kelas']) . '</td></tr>
    <tr><td class="label">Nama Guru</td><td class="colon">:</td><td class="value">' . htmlspecialchars($_SESSION['nama_guru'] ?? '-') . '</td></tr>
    <tr><td class="label">Semester</td><td class="colon">:</td><td class="value">' . htmlspecialchars($penugasan['nama_semester'] ?? '-') . '</td></tr>
</table>

<table>
    <thead>
        <tr>
            <th style="width:40px;">No</th>
            <th style="width:80px;">NISN</th>
            <th>Nama Siswa</th>
            <th style="width:120px;">Nilai Rata-rata Harian</th>
        </tr>
    </thead>
    <tbody>';

        $no = 1;
        foreach ($siswa_list as $siswa) {
            $nilai = $nilai_harian[$siswa['id_siswa']] ?? 0;
            $html .= '<tr>
                <td class="center">' . $no . '</td>
                <td class="center">' . htmlspecialchars($siswa['nisn']) . '</td>
                <td>' . htmlspecialchars($siswa['nama_siswa']) . '</td>
                <td class="center">' . ($nilai > 0 ? number_format($nilai, 2) : '-') . '</td>
            </tr>';
            $no++;
        }

        $html .= '</tbody>
</table>

<div class="doc-info-box">
    <div class="doc-info-title">Informasi Dokumen:</div>
    <div class="doc-info-row">Dokumen ini dicetak pada ' . $waktuCetak . ' oleh ' . htmlspecialchars($namaPencetak) . '</div>
    <div class="doc-info-row">Dokumen ini sah dan terverifikasi dengan QR Code di pojok kanan bawah halaman.</div>
</div>
</body>
</html>';

        // Add QR code using PDFQRHelper
        require_once APPROOT . '/app/core/PDFQRHelper.php';
        $html = PDFQRHelper::addQRToPDF($html, 'nilai_harian_penugasan', $id_penugasan, [
            'mapel' => $penugasan['nama_mapel'],
            'kelas' => $penugasan['nama_kelas'],
            'jenis' => 'harian'
        ]);

        // Generate PDF
        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        if (ob_get_length()) {
            ob_end_clean();
        }

        $filename = 'Nilai_Harian_' . str_replace(' ', '_', $penugasan['nama_mapel']) . '_' . date('Y-m-d') . '.pdf';
        $dompdf->stream($filename);
        exit;
    }

    /**
     * Download PDF Rekap Nilai STS
     */
    public function downloadNilaiSTSPDF() {
        $id_penugasan = $_GET['id_penugasan'] ?? null;
        
        if (!$id_penugasan) {
            Flasher::setFlash('Parameter tidak lengkap.', 'danger');
            header('Location: ' . BASEURL . '/guru');
            exit;
        }

        require_once APPROOT . '/app/core/dompdf/autoload.inc.php';
        require_once APPROOT . '/config/qrcode.php';

        $penugasan = $this->model('Penugasan_model')->getPenugasanById($id_penugasan);
        if (!$penugasan) {
            Flasher::setFlash('Data penugasan tidak ditemukan.', 'danger');
            header('Location: ' . BASEURL . '/guru');
            exit;
        }

        // Get id_tp from semester
        require_once APPROOT . '/app/core/Database.php';
        $db = new Database;
        $db->query('SELECT id_tp FROM semester WHERE id_semester = :id_semester');
        $db->bind('id_semester', $penugasan['id_semester']);
        $semester = $db->single();
        $id_tp = $semester['id_tp'] ?? null;

        $siswa_list = $this->model('Siswa_model')->getSiswaByKelas($penugasan['id_kelas'], $id_tp);
        
        // Sort by nomor_absen if available, otherwise by nama_siswa
        usort($siswa_list, function($a, $b) {
            if (isset($a['nomor_absen']) && isset($b['nomor_absen'])) {
                return $a['nomor_absen'] - $b['nomor_absen'];
            }
            return strcmp($a['nama_siswa'], $b['nama_siswa']);
        });

        // Ambil nilai STS per siswa
        $nilai_sts = [];
        foreach ($siswa_list as $siswa) {
            $nilai = $this->nilaiModel->getNilaiByJenis($siswa['id_siswa'], $penugasan['id_guru'], $penugasan['id_mapel'], $penugasan['id_semester'], 'sts');
            $nilai_sts[$siswa['id_siswa']] = $nilai['nilai'] ?? 0;
        }

        $pengaturanRapor = $this->model('PengaturanRapor_model')->getPengaturanByGuru($_SESSION['user_id'], $id_tp);
        $kopRapor = $pengaturanRapor['kop_rapor'] ?? '';

        // Kop header
        $kopHTML = '';
        if (!empty($kopRapor)) {
            $kopPath = 'public/img/kop/' . $kopRapor;
            if (file_exists($kopPath)) {
                $imageData = base64_encode(file_get_contents($kopPath));
                $imageType = pathinfo($kopPath, PATHINFO_EXTENSION);
                $imageSrc = 'data:image/' . $imageType . ';base64,' . $imageData;
                $kopHTML = '<div style="text-align:center; margin-bottom:15px;"><img src="' . $imageSrc . '" style="max-width:100%; height:auto; max-height:100px;"></div>';
            }
        }

        // Format waktu Indonesia
        $bulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $waktuCetak = date('d') . ' ' . $bulan[date('n') - 1] . ' ' . date('Y H:i');
        $namaPencetak = $_SESSION['nama_lengkap'] ?? ($_SESSION['nama_guru'] ?? 'Pengguna');

        $html = '<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Laporan Nilai Tengah Semester - ' . htmlspecialchars($penugasan['nama_mapel']) . '</title>
<style>
    @page { size: A4; margin: 15mm; }
    body { font-family: Arial, sans-serif; font-size: 11px; line-height: 1.4; color: #333; }
    .kop { text-align: center; margin-bottom: 15px; }
    .title { text-align: center; font-size: 14px; font-weight: bold; margin: 15px 0 10px; text-transform: uppercase; }
    .meta-table { margin-bottom: 15px; border: none; width: auto; }
    .meta-table td { border: none; padding: 2px 5px 2px 0; font-size: 11px; vertical-align: top; }
    .meta-table .label { font-weight: bold; white-space: nowrap; width: 130px; }
    .meta-table .colon { width: 10px; text-align: center; }
    .meta-table .value { }
    table { width: 100%; border-collapse: collapse; margin: 10px 0; }
    th, td { border: 1px solid #333; padding: 6px 8px; }
    th { background: #f0f0f0; font-weight: bold; text-align: center; font-size: 10px; }
    td { font-size: 10px; }
    .center { text-align: center; }
    .right { text-align: right; }
    .doc-info-box {
        margin-top: 25px;
        border: 1px solid #333;
        padding: 12px 15px;
        background: #f9f9f9;
        font-size: 10px;
    }
    .doc-info-title {
        font-weight: bold;
        font-size: 11px;
        margin-bottom: 8px;
        border-bottom: 1px solid #ccc;
        padding-bottom: 5px;
    }
    .doc-info-row {
        margin-bottom: 4px;
        line-height: 1.5;
    }
</style>
</head>
<body>
' . $kopHTML . '
<div class="title">LAPORAN NILAI TENGAH SEMESTER (STS)</div>

<table class="meta-table">
    <tr><td class="label">Mata Pelajaran</td><td class="colon">:</td><td class="value">' . htmlspecialchars($penugasan['nama_mapel']) . '</td></tr>
    <tr><td class="label">Kelas</td><td class="colon">:</td><td class="value">' . htmlspecialchars($penugasan['nama_kelas']) . '</td></tr>
    <tr><td class="label">Nama Guru</td><td class="colon">:</td><td class="value">' . htmlspecialchars($_SESSION['nama_guru'] ?? '-') . '</td></tr>
    <tr><td class="label">Semester</td><td class="colon">:</td><td class="value">' . htmlspecialchars($penugasan['nama_semester'] ?? '-') . '</td></tr>
</table>

<table>
    <thead>
        <tr>
            <th style="width:40px;">No</th>
            <th style="width:80px;">NISN</th>
            <th>Nama Siswa</th>
            <th style="width:120px;">Nilai STS</th>
        </tr>
    </thead>
    <tbody>';

        $no = 1;
        foreach ($siswa_list as $siswa) {
            $nilai = $nilai_sts[$siswa['id_siswa']] ?? 0;
            $html .= '<tr>
                <td class="center">' . $no . '</td>
                <td class="center">' . htmlspecialchars($siswa['nisn']) . '</td>
                <td>' . htmlspecialchars($siswa['nama_siswa']) . '</td>
                <td class="center">' . ($nilai > 0 ? number_format($nilai, 2) : '-') . '</td>
            </tr>';
            $no++;
        }

        $html .= '</tbody>
</table>

<div class="doc-info-box">
    <div class="doc-info-title">Informasi Dokumen:</div>
    <div class="doc-info-row">Dokumen ini dicetak pada ' . $waktuCetak . ' oleh ' . htmlspecialchars($namaPencetak) . '</div>
    <div class="doc-info-row">Dokumen ini sah dan terverifikasi dengan QR Code di pojok kanan bawah halaman.</div>
</div>
</body>
</html>';

        // Add QR code using PDFQRHelper
        require_once APPROOT . '/app/core/PDFQRHelper.php';
        $html = PDFQRHelper::addQRToPDF($html, 'nilai_sts_penugasan', $id_penugasan, [
            'mapel' => $penugasan['nama_mapel'],
            'kelas' => $penugasan['nama_kelas'],
            'jenis' => 'sts'
        ]);

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        if (ob_get_length()) {
            ob_end_clean();
        }

        $filename = 'Nilai_STS_' . str_replace(' ', '_', $penugasan['nama_mapel']) . '_' . date('Y-m-d') . '.pdf';
        $dompdf->stream($filename);
        exit;
    }

    /**
     * Download PDF Rekap Nilai SAS
     */
    public function downloadNilaiSASPDF() {
        $id_penugasan = $_GET['id_penugasan'] ?? null;
        
        if (!$id_penugasan) {
            Flasher::setFlash('Parameter tidak lengkap.', 'danger');
            header('Location: ' . BASEURL . '/guru');
            exit;
        }

        require_once APPROOT . '/app/core/dompdf/autoload.inc.php';
        require_once APPROOT . '/config/qrcode.php';

        $penugasan = $this->model('Penugasan_model')->getPenugasanById($id_penugasan);
        if (!$penugasan) {
            Flasher::setFlash('Data penugasan tidak ditemukan.', 'danger');
            header('Location: ' . BASEURL . '/guru');
            exit;
        }

        // Get id_tp from semester
        require_once APPROOT . '/app/core/Database.php';
        $db = new Database;
        $db->query('SELECT id_tp FROM semester WHERE id_semester = :id_semester');
        $db->bind('id_semester', $penugasan['id_semester']);
        $semester = $db->single();
        $id_tp = $semester['id_tp'] ?? null;

        $siswa_list = $this->model('Siswa_model')->getSiswaByKelas($penugasan['id_kelas'], $id_tp);
        
        // Sort by nomor_absen if available, otherwise by nama_siswa
        usort($siswa_list, function($a, $b) {
            if (isset($a['nomor_absen']) && isset($b['nomor_absen'])) {
                return $a['nomor_absen'] - $b['nomor_absen'];
            }
            return strcmp($a['nama_siswa'], $b['nama_siswa']);
        });

        // Ambil nilai SAS per siswa
        $nilai_sas = [];
        foreach ($siswa_list as $siswa) {
            $nilai = $this->nilaiModel->getNilaiByJenis($siswa['id_siswa'], $penugasan['id_guru'], $penugasan['id_mapel'], $penugasan['id_semester'], 'sas');
            $nilai_sas[$siswa['id_siswa']] = $nilai['nilai'] ?? 0;
        }

        $pengaturanRapor = $this->model('PengaturanRapor_model')->getPengaturanByGuru($_SESSION['user_id'], $id_tp);
        $kopRapor = $pengaturanRapor['kop_rapor'] ?? '';

        // Kop header
        $kopHTML = '';
        if (!empty($kopRapor)) {
            $kopPath = 'public/img/kop/' . $kopRapor;
            if (file_exists($kopPath)) {
                $imageData = base64_encode(file_get_contents($kopPath));
                $imageType = pathinfo($kopPath, PATHINFO_EXTENSION);
                $imageSrc = 'data:image/' . $imageType . ';base64,' . $imageData;
                $kopHTML = '<div style="text-align:center; margin-bottom:15px;"><img src="' . $imageSrc . '" style="max-width:100%; height:auto; max-height:100px;"></div>';
            }
        }

        // Format waktu Indonesia
        $bulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $waktuCetak = date('d') . ' ' . $bulan[date('n') - 1] . ' ' . date('Y H:i');
        $namaPencetak = $_SESSION['nama_lengkap'] ?? ($_SESSION['nama_guru'] ?? 'Pengguna');

        $html = '<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Laporan Nilai Akhir Semester - ' . htmlspecialchars($penugasan['nama_mapel']) . '</title>
<style>
    @page { size: A4; margin: 15mm; }
    body { font-family: Arial, sans-serif; font-size: 11px; line-height: 1.4; color: #333; }
    .kop { text-align: center; margin-bottom: 15px; }
    .title { text-align: center; font-size: 14px; font-weight: bold; margin: 15px 0 10px; text-transform: uppercase; }
    .meta-table { margin-bottom: 15px; border: none; width: auto; }
    .meta-table td { border: none; padding: 2px 5px 2px 0; font-size: 11px; vertical-align: top; }
    .meta-table .label { font-weight: bold; white-space: nowrap; width: 130px; }
    .meta-table .colon { width: 10px; text-align: center; }
    .meta-table .value { }
    table { width: 100%; border-collapse: collapse; margin: 10px 0; }
    th, td { border: 1px solid #333; padding: 6px 8px; }
    th { background: #f0f0f0; font-weight: bold; text-align: center; font-size: 10px; }
    td { font-size: 10px; }
    .center { text-align: center; }
    .right { text-align: right; }
    .doc-info-box {
        margin-top: 25px;
        border: 1px solid #333;
        padding: 12px 15px;
        background: #f9f9f9;
        font-size: 10px;
    }
    .doc-info-title {
        font-weight: bold;
        font-size: 11px;
        margin-bottom: 8px;
        border-bottom: 1px solid #ccc;
        padding-bottom: 5px;
    }
    .doc-info-row {
        margin-bottom: 4px;
        line-height: 1.5;
    }
</style>
</head>
<body>
' . $kopHTML . '
<div class="title">LAPORAN NILAI AKHIR SEMESTER (SAS)</div>

<table class="meta-table">
    <tr><td class="label">Mata Pelajaran</td><td class="colon">:</td><td class="value">' . htmlspecialchars($penugasan['nama_mapel']) . '</td></tr>
    <tr><td class="label">Kelas</td><td class="colon">:</td><td class="value">' . htmlspecialchars($penugasan['nama_kelas']) . '</td></tr>
    <tr><td class="label">Nama Guru</td><td class="colon">:</td><td class="value">' . htmlspecialchars($_SESSION['nama_guru'] ?? '-') . '</td></tr>
    <tr><td class="label">Semester</td><td class="colon">:</td><td class="value">' . htmlspecialchars($penugasan['nama_semester'] ?? '-') . '</td></tr>
</table>

<table>
    <thead>
        <tr>
            <th style="width:40px;">No</th>
            <th style="width:80px;">NISN</th>
            <th>Nama Siswa</th>
            <th style="width:120px;">Nilai SAS</th>
        </tr>
    </thead>
    <tbody>';

        $no = 1;
        foreach ($siswa_list as $siswa) {
            $nilai = $nilai_sas[$siswa['id_siswa']] ?? 0;
            $html .= '<tr>
                <td class="center">' . $no . '</td>
                <td class="center">' . htmlspecialchars($siswa['nisn']) . '</td>
                <td>' . htmlspecialchars($siswa['nama_siswa']) . '</td>
                <td class="center">' . ($nilai > 0 ? number_format($nilai, 2) : '-') . '</td>
            </tr>';
            $no++;
        }

        $html .= '</tbody>
</table>

<div class="doc-info-box">
    <div class="doc-info-title">Informasi Dokumen:</div>
    <div class="doc-info-row">Dokumen ini dicetak pada ' . $waktuCetak . ' oleh ' . htmlspecialchars($namaPencetak) . '</div>
    <div class="doc-info-row">Dokumen ini sah dan terverifikasi dengan QR Code di pojok kanan bawah halaman.</div>
</div>
</body>
</html>';

        // Add QR code using PDFQRHelper
        require_once APPROOT . '/app/core/PDFQRHelper.php';
        $html = PDFQRHelper::addQRToPDF($html, 'nilai_sas_penugasan', $id_penugasan, [
            'mapel' => $penugasan['nama_mapel'],
            'kelas' => $penugasan['nama_kelas'],
            'jenis' => 'sas'
        ]);

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        if (ob_get_length()) {
            ob_end_clean();
        }

        $filename = 'Nilai_SAS_' . str_replace(' ', '_', $penugasan['nama_mapel']) . '_' . date('Y-m-d') . '.pdf';
        $dompdf->stream($filename);
        exit;
    }
}
