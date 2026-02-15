<?php
// File: app/controllers/JadwalController.php

use Dompdf\Dompdf;
use Dompdf\Options;

class JadwalController extends Controller
{
    private $data = [];

    public function __construct()
    {
        // Guard: Admin only
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASEURL . '/auth/login');
            exit;
        }

        if ($_SESSION['role'] !== 'admin') {
            header('Location: ' . BASEURL . '/auth/login');
            exit;
        }

        // Set flag for independent sidebar
        $this->data['use_jadwal_sidebar'] = true;
    }

    public function index()
    {
        $this->dashboard();
    }

    // =================================================================
    // DASHBOARD
    // =================================================================

    public function dashboard()
    {
        $this->data['judul'] = 'Dashboard Jadwal Pelajaran';

        $jadwalModel = $this->model('Jadwal_model');
        $this->data['total_jam'] = $jadwalModel->countJam();
        $this->data['total_guru_mapel'] = $jadwalModel->countGuruMapel();
        $this->data['total_jadwal'] = $jadwalModel->countJadwal();
        $this->data['pengaturan'] = $jadwalModel->getPengaturanArray();

        $this->view('templates/header', $this->data);
        $this->view('jadwal/dashboard', $this->data);
        $this->view('templates/footer', $this->data);
    }

    // =================================================================
    // PENGATURAN
    // =================================================================

    public function pengaturan()
    {
        $this->data['judul'] = 'Pengaturan Jadwal';
        $jadwalModel = $this->model('Jadwal_model');
        $this->data['pengaturan'] = $jadwalModel->getPengaturanArray();

        $this->view('templates/header', $this->data);
        $this->view('jadwal/pengaturan', $this->data);
        $this->view('templates/footer', $this->data);
    }

    public function simpanPengaturan()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/jadwal/pengaturan');
            exit;
        }

        $jadwalModel = $this->model('Jadwal_model');

        // Update each setting
        $settings = ['durasi_jam', 'jam_mulai', 'jam_selesai', 'lama_istirahat', 'tracking_ruangan', 'mode_generate'];
        foreach ($settings as $key) {
            if (isset($_POST[$key])) {
                $jadwalModel->updatePengaturan($key, $_POST[$key]);
            }
        }

        // Handle hari_aktif as array
        if (isset($_POST['hari_aktif']) && is_array($_POST['hari_aktif'])) {
            $jadwalModel->updatePengaturan('hari_aktif', implode(',', $_POST['hari_aktif']));
        }

        Flasher::setFlash('Pengaturan berhasil disimpan', 'success');
        header('Location: ' . BASEURL . '/jadwal/pengaturan');
        exit;
    }

    // =================================================================
    // JAM PELAJARAN CRUD
    // =================================================================

    public function jamPelajaran()
    {
        $this->data['judul'] = 'Kelola Jam Pelajaran';
        $jadwalModel = $this->model('Jadwal_model');
        $this->data['jam_list'] = $jadwalModel->getAllJam();
        $this->data['pengaturan'] = $jadwalModel->getPengaturanArray();

        $this->view('templates/header', $this->data);
        $this->view('jadwal/jam_index', $this->data);
        $this->view('templates/footer', $this->data);
    }

    public function tambahJam()
    {
        $this->data['judul'] = 'Tambah Jam Pelajaran';
        $jadwalModel = $this->model('Jadwal_model');
        $this->data['pengaturan'] = $jadwalModel->getPengaturanArray();

        $this->view('templates/header', $this->data);
        $this->view('jadwal/jam_form', $this->data);
        $this->view('templates/footer', $this->data);
    }

    public function editJam($id)
    {
        $this->data['judul'] = 'Edit Jam Pelajaran';
        $jadwalModel = $this->model('Jadwal_model');
        $this->data['jam'] = $jadwalModel->getJamById($id);
        $this->data['pengaturan'] = $jadwalModel->getPengaturanArray();

        if (!$this->data['jam']) {
            Flasher::setFlash('Jam pelajaran tidak ditemukan', 'error');
            header('Location: ' . BASEURL . '/jadwal/jamPelajaran');
            exit;
        }

        $this->view('templates/header', $this->data);
        $this->view('jadwal/jam_form', $this->data);
        $this->view('templates/footer', $this->data);
    }

    public function simpanJam()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/jadwal/jamPelajaran');
            exit;
        }

        $jadwalModel = $this->model('Jadwal_model');
        $data = [
            'jam_ke' => $_POST['jam_ke'],
            'waktu_mulai' => $_POST['waktu_mulai'],
            'waktu_selesai' => $_POST['waktu_selesai'],
            'is_istirahat' => isset($_POST['is_istirahat']) ? 1 : 0,
            'keterangan' => $_POST['keterangan'] ?? '',
            'urutan' => $_POST['urutan'] ?? 0
        ];

        if (!empty($_POST['id_jam'])) {
            $data['id_jam'] = $_POST['id_jam'];
            $jadwalModel->updateJam($data);
            Flasher::setFlash('Jam pelajaran berhasil diperbarui', 'success');
        } else {
            $jadwalModel->tambahJam($data);
            Flasher::setFlash('Jam pelajaran berhasil ditambahkan', 'success');
        }

        header('Location: ' . BASEURL . '/jadwal/jamPelajaran');
        exit;
    }

    public function hapusJam($id)
    {
        $jadwalModel = $this->model('Jadwal_model');
        $jadwalModel->hapusJam($id);
        Flasher::setFlash('Jam pelajaran berhasil dihapus', 'success');
        header('Location: ' . BASEURL . '/jadwal/jamPelajaran');
        exit;
    }

    public function generateJamOtomatis()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/jadwal/jamPelajaran');
            exit;
        }

        $jadwalModel = $this->model('Jadwal_model');
        $pengaturan = $jadwalModel->getPengaturanArray();

        $durasi = (int) ($pengaturan['durasi_jam'] ?? 45);
        $mulai = $pengaturan['jam_mulai'] ?? '07:00';
        $selesai = $pengaturan['jam_selesai'] ?? '14:00';
        $jumlahIstirahat = (int) ($_POST['jumlah_istirahat'] ?? 1);
        $durasiIstirahat = (int) ($_POST['durasi_istirahat'] ?? 15);
        $istirahatSetelahJam = (int) ($_POST['istirahat_setelah_jam'] ?? 3);

        // Parse times
        $currentTime = strtotime($mulai);
        $endTime = strtotime($selesai);
        $jamKe = 1;
        $urutan = 1;

        // Clear existing
        $this->model('Jadwal_model');

        while ($currentTime < $endTime) {
            $waktuMulai = date('H:i', $currentTime);

            // Check if this is a break time
            if ($jumlahIstirahat > 0 && $jamKe > $istirahatSetelahJam && ($jamKe - $istirahatSetelahJam) % ($istirahatSetelahJam) == 1) {
                // Insert break
                $jadwalModel->tambahJam([
                    'jam_ke' => 0,
                    'waktu_mulai' => $waktuMulai,
                    'waktu_selesai' => date('H:i', $currentTime + ($durasiIstirahat * 60)),
                    'is_istirahat' => 1,
                    'keterangan' => 'Istirahat',
                    'urutan' => $urutan++
                ]);
                $currentTime += ($durasiIstirahat * 60);
                $jumlahIstirahat--;
                continue;
            }

            // Insert regular period
            $jadwalModel->tambahJam([
                'jam_ke' => $jamKe,
                'waktu_mulai' => $waktuMulai,
                'waktu_selesai' => date('H:i', $currentTime + ($durasi * 60)),
                'is_istirahat' => 0,
                'keterangan' => 'Jam ' . $jamKe,
                'urutan' => $urutan++
            ]);

            $currentTime += ($durasi * 60);
            $jamKe++;
        }

        Flasher::setFlash('Jam pelajaran berhasil di-generate', 'success');
        header('Location: ' . BASEURL . '/jadwal/jamPelajaran');
        exit;
    }

    // =================================================================
    // GURU MAPEL
    // =================================================================

    public function guruMapel()
    {
        $this->data['judul'] = 'Kelola Guru-Mapel';
        $jadwalModel = $this->model('Jadwal_model');

        $this->data['guru_mapel_list'] = $jadwalModel->getAllGuruMapel();
        $this->data['guru_list'] = $this->model('Guru_model')->getAllGuru();
        $this->data['mapel_list'] = $this->model('Mapel_model')->getAllMapel();

        // Get detailed jadwal count per guru per mapel per kelas
        $idTp = $_SESSION['id_tp_aktif'] ?? null;
        $this->data['jadwal_detail'] = $idTp ? $jadwalModel->getJadwalDetailPerGuruMapelKelas($idTp) : [];

        $this->view('templates/header', $this->data);
        $this->view('jadwal/guru_mapel', $this->data);
        $this->view('templates/footer', $this->data);
    }

    public function simpanGuruMapel()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/jadwal/guruMapel');
            exit;
        }

        $jadwalModel = $this->model('Jadwal_model');
        $idGuru = $_POST['id_guru'];
        $mapelIds = $_POST['id_mapel'] ?? [];

        // Clear existing and re-add
        $jadwalModel->hapusGuruMapelByGuru($idGuru);
        foreach ($mapelIds as $idMapel) {
            $jadwalModel->tambahGuruMapel($idGuru, $idMapel);
        }

        Flasher::setFlash('Mapel guru berhasil disimpan', 'success');
        header('Location: ' . BASEURL . '/jadwal/guruMapel');
        exit;
    }

    public function hapusGuruMapel($id)
    {
        $jadwalModel = $this->model('Jadwal_model');
        $jadwalModel->hapusGuruMapel($id);
        Flasher::setFlash('Data berhasil dihapus', 'success');
        header('Location: ' . BASEURL . '/jadwal/guruMapel');
        exit;
    }

    // =================================================================
    // JADWAL PELAJARAN
    // =================================================================

    public function kelola($idKelas = null)
    {
        $this->data['judul'] = 'Kelola Jadwal Pelajaran';
        $jadwalModel = $this->model('Jadwal_model');

        $this->data['kelas_list'] = $this->model('Kelas_model')->getAllKelas();
        $this->data['mapel_list'] = $this->model('Mapel_model')->getAllMapel();
        $this->data['pengaturan'] = $jadwalModel->getPengaturanArray();
        $this->data['jam_list'] = $jadwalModel->getAllJam();
        $this->data['id_kelas'] = $idKelas;

        // Get current tahun pelajaran from session
        $idTp = $_SESSION['id_tp_aktif'] ?? null;
        $namaTp = $_SESSION['nama_semester_aktif'] ?? 'Tidak ada';
        $this->data['tp_aktif'] = $idTp ? ['id_tp' => $idTp, 'tahun_pelajaran' => $namaTp] : null;

        if ($idKelas && $this->data['tp_aktif']) {
            $this->data['jadwal_list'] = $jadwalModel->getJadwalByKelas($idKelas, $this->data['tp_aktif']['id_tp']);
            $this->data['kelas'] = $this->model('Kelas_model')->getKelasById($idKelas);
        }

        $this->view('templates/header', $this->data);
        $this->view('jadwal/kelola', $this->data);
        $this->view('templates/footer', $this->data);
    }

    public function tambahJadwal()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            exit;
        }

        header('Content-Type: application/json');

        $jadwalModel = $this->model('Jadwal_model');
        $idTp = $_SESSION['id_tp_aktif'] ?? null;

        if (!$idTp) {
            echo json_encode(['success' => false, 'message' => 'Tahun pelajaran aktif tidak ditemukan']);
            exit;
        }

        $data = [
            'id_kelas' => $_POST['id_kelas'],
            'id_guru' => $_POST['id_guru'],
            'id_mapel' => $_POST['id_mapel'],
            'id_jam' => $_POST['id_jam'],
            'hari' => $_POST['hari'],
            'id_ruangan' => $_POST['id_ruangan'] ?? null,
            'id_tp' => $idTp
        ];

        // Check if slot already exists (update mode)
        $existingSlot = $jadwalModel->getJadwalBySlot($data['id_kelas'], $data['id_jam'], $data['hari'], $data['id_tp']);

        if ($existingSlot) {
            // Update existing slot - no need to check kelas conflict
            // But still check guru conflict (excluding current slot)
            $bentrokGuru = $jadwalModel->cekBentrokGuru($data['id_guru'], $data['id_jam'], $data['hari'], $data['id_tp'], $existingSlot['id_jadwal']);
            if ($bentrokGuru) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Guru sudah mengajar di kelas ' . $bentrokGuru['nama_kelas'] . ' pada jam ini'
                ]);
                exit;
            }

            $jadwalModel->updateJadwal($existingSlot['id_jadwal'], $data);
            echo json_encode(['success' => true, 'message' => 'Jadwal berhasil diupdate']);
            exit;
        }

        // Insert new - check all conflicts
        $bentrokGuru = $jadwalModel->cekBentrokGuru($data['id_guru'], $data['id_jam'], $data['hari'], $data['id_tp']);
        if ($bentrokGuru) {
            echo json_encode([
                'success' => false,
                'message' => 'Guru sudah mengajar di kelas ' . $bentrokGuru['nama_kelas'] . ' pada jam ini'
            ]);
            exit;
        }

        try {
            $jadwalModel->tambahJadwal($data);
            echo json_encode(['success' => true, 'message' => 'Jadwal berhasil ditambahkan']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Gagal menambahkan jadwal: ' . $e->getMessage()]);
        }
        exit;
    }

    public function hapusJadwalItem($id)
    {
        header('Content-Type: application/json');

        error_log("Request hapusJadwalItem ID: " . $id);

        $jadwalModel = $this->model('Jadwal_model');
        $result = $jadwalModel->hapusJadwal($id);

        error_log("Result hapus: " . $result);

        echo json_encode(['success' => true, 'message' => 'Jadwal berhasil dihapus', 'rows_affected' => $result]);
        exit;
    }

    public function getGuruByMapel($idMapel)
    {
        header('Content-Type: application/json');

        $jadwalModel = $this->model('Jadwal_model');
        $guru = $jadwalModel->getGuruByMapel($idMapel);

        echo json_encode($guru);
        exit;
    }

    public function getPenugasanByKelas($idKelas)
    {
        header('Content-Type: application/json');

        $idSemester = $_SESSION['id_semester_aktif'] ?? null;
        if (!$idSemester) {
            echo json_encode([]);
            exit;
        }

        $penugasanModel = $this->model('Penugasan_model');
        $data = $penugasanModel->getMapelByKelas($idKelas, $idSemester);
        echo json_encode($data);
        exit;
    }

    public function getScheduleByJam($idJam)
    {
        header('Content-Type: application/json');

        $jadwalModel = $this->model('Jadwal_model');
        $idTp = $_SESSION['id_tp_aktif'] ?? null;

        if (!$idTp) {
            echo json_encode([]);
            exit;
        }

        $jadwal = $jadwalModel->getJadwalByJam($idJam, $idTp);
        echo json_encode($jadwal);
        exit;
    }

    public function getAllSchedules()
    {
        header('Content-Type: application/json');

        $jadwalModel = $this->model('Jadwal_model');
        $idTp = $_SESSION['id_tp_aktif'] ?? null;

        if (!$idTp) {
            echo json_encode([]);
            exit;
        }

        $jadwal = $jadwalModel->getAllJadwal($idTp);
        echo json_encode($jadwal);
        exit;
    }

    // =================================================================
    // VIEW JADWAL
    // =================================================================

    public function lihatKelas($idKelas = null)
    {
        $this->data['judul'] = 'Jadwal Per Kelas';
        $jadwalModel = $this->model('Jadwal_model');

        $this->data['kelas_list'] = $this->model('Kelas_model')->getAllKelas();
        $this->data['pengaturan'] = $jadwalModel->getPengaturanArray();
        $this->data['jam_list'] = $jadwalModel->getAllJam();
        $this->data['id_kelas'] = $idKelas;

        // Get current tahun pelajaran from session
        $idTp = $_SESSION['id_tp_aktif'] ?? null;
        $namaTp = $_SESSION['nama_semester_aktif'] ?? 'Tidak ada';
        $this->data['tp_aktif'] = $idTp ? ['id_tp' => $idTp, 'tahun_pelajaran' => $namaTp] : null;

        if ($idKelas && $this->data['tp_aktif']) {
            $this->data['jadwal_list'] = $jadwalModel->getJadwalByKelas($idKelas, $this->data['tp_aktif']['id_tp']);
            $this->data['kelas'] = $this->model('Kelas_model')->getKelasById($idKelas);
        }

        $this->view('templates/header', $this->data);
        $this->view('jadwal/lihat_kelas', $this->data);
        $this->view('templates/footer', $this->data);
    }

    public function lihatGuru($idGuru = null)
    {
        $this->data['judul'] = 'Jadwal Per Guru';
        $jadwalModel = $this->model('Jadwal_model');

        $this->data['guru_list'] = $this->model('Guru_model')->getAllGuru();
        $this->data['pengaturan'] = $jadwalModel->getPengaturanArray();
        $this->data['jam_list'] = $jadwalModel->getAllJam();
        $this->data['id_guru'] = $idGuru;

        // Get current tahun pelajaran from session
        $idTp = $_SESSION['id_tp_aktif'] ?? null;
        $namaTp = $_SESSION['nama_semester_aktif'] ?? 'Tidak ada';
        $this->data['tp_aktif'] = $idTp ? ['id_tp' => $idTp, 'tahun_pelajaran' => $namaTp] : null;

        if ($idGuru && $this->data['tp_aktif']) {
            $this->data['jadwal_list'] = $jadwalModel->getJadwalByGuru($idGuru, $this->data['tp_aktif']['id_tp']);
            $this->data['guru'] = $this->model('Guru_model')->getGuruById($idGuru);
        }

        $this->view('templates/header', $this->data);
        $this->view('jadwal/lihat_guru', $this->data);
        $this->view('templates/footer', $this->data);
    }

    // =================================================================
    // ISTIRAHAT API METHODS
    // =================================================================

    /**
     * Get all istirahat (AJAX)
     */
    public function getAllIstirahat()
    {
        header('Content-Type: application/json');
        $idTp = $_SESSION['id_tp_aktif'] ?? null;

        if (!$idTp) {
            echo json_encode([]);
            exit;
        }

        $jadwalModel = $this->model('Jadwal_model');
        $istirahat = $jadwalModel->getAllIstirahat($idTp);

        echo json_encode($istirahat);
        exit;
    }

    /**
     * Add istirahat (AJAX)
     */
    public function addIstirahat()
    {
        header('Content-Type: application/json');

        $idKelas = $_POST['id_kelas'] ?? $_GET['id_kelas'] ?? null;
        $hari = $_POST['hari'] ?? $_GET['hari'] ?? null;
        $setelahJam = $_POST['setelah_jam'] ?? $_GET['setelah_jam'] ?? null;
        $idTp = $_SESSION['id_tp_aktif'] ?? null;

        if (!$idKelas || !$hari || $setelahJam === null || !$idTp) {
            echo json_encode(['success' => false, 'message' => 'Data tidak lengkap']);
            exit;
        }

        $jadwalModel = $this->model('Jadwal_model');
        $result = $jadwalModel->addIstirahat($idKelas, $hari, $setelahJam, $idTp);

        echo json_encode(['success' => $result, 'message' => $result ? 'Istirahat ditambahkan' : 'Istirahat sudah ada']);
        exit;
    }

    /**
     * Remove istirahat (AJAX)
     */
    public function removeIstirahat()
    {
        header('Content-Type: application/json');

        $idKelas = $_POST['id_kelas'] ?? $_GET['id_kelas'] ?? null;
        $hari = $_POST['hari'] ?? $_GET['hari'] ?? null;
        $setelahJam = $_POST['setelah_jam'] ?? $_GET['setelah_jam'] ?? null;
        $idTp = $_SESSION['id_tp_aktif'] ?? null;

        if (!$idKelas || !$hari || $setelahJam === null || !$idTp) {
            echo json_encode(['success' => false, 'message' => 'Data tidak lengkap']);
            exit;
        }

        $jadwalModel = $this->model('Jadwal_model');
        $result = $jadwalModel->removeIstirahat($idKelas, $hari, $setelahJam, $idTp);

        echo json_encode(['success' => $result, 'message' => $result ? 'Istirahat dihapus' : 'Istirahat tidak ditemukan']);
        exit;
    }

    /**
     * Reset Jadwal (AJAX)
     */
    public function resetJadwal()
    {
        header('Content-Type: application/json');

        $idTp = $_SESSION['id_tp_aktif'] ?? null;
        if (!$idTp) {
            echo json_encode(['success' => false, 'message' => 'Tahun pelajaran tidak aktif']);
            exit;
        }

        $jadwalModel = $this->model('Jadwal_model');

        // Reset jadwal pelajaran and jadwal istirahat for this TP
        $result = $jadwalModel->resetJadwal($idTp);

        echo json_encode(['success' => $result, 'message' => $result ? 'Jadwal berhasil direset' : 'Gagal mereset jadwal']);
        exit;
    }

    /**
     * Cetak Jadwal (PDF/View)
     */
    public function cetakJadwal()
    {
        $idTp = $_SESSION['id_tp_aktif'] ?? null;
        if (!$idTp) {
            Flasher::setFlash('Gagal', 'Tahun pelajaran tidak aktif', 'danger');
            header('Location: ' . BASEURL . '/jadwal/kelola');
            exit;
        }

        $jadwalModel = $this->model('Jadwal_model');

        // Get data needed for printing
        $this->data['judul'] = 'Cetak Jadwal Pelajaran';
        $this->data['tp_aktif'] = ['id_tp' => $idTp, 'tahun_pelajaran' => $this->model('TahunPelajaran_model')->getTPById($idTp)['nama_tp']];
        $this->data['pengaturan'] = $jadwalModel->getPengaturanArray();

        // Get all jadwal organized by Kelas
        $this->data['kelas_list'] = $this->model('Kelas_model')->getAllKelas();
        $this->data['jam_list'] = $jadwalModel->getAllJam();
        $this->data['jadwal_semua'] = $jadwalModel->getAllJadwal($idTp);
        $this->data['istirahat_semua'] = $jadwalModel->getAllIstirahat($idTp);

        // Use a clean print view
        $this->view('jadwal/cetak_jadwal', $this->data);
    }
}
