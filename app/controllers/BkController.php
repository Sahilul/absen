<?php

class BkController extends Controller
{
    private $data = [];

    public function __construct()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASEURL . '/auth/login');
            exit;
        }

        $role = $_SESSION['role'] ?? '';
        $id_guru = $_SESSION['id_ref'] ?? 0;
        $id_tp = $_SESSION['id_tp_aktif'] ?? 0;

        $isGuruBK = false;
        if (in_array($role, ['guru', 'wali_kelas']) && $id_guru && $id_tp) {
            require_once APPROOT . '/app/models/GuruFungsi_model.php';
            $gf = new GuruFungsi_model();
            $isGuruBK = $gf->hasFungsi($id_guru, 'guru_bk', $id_tp);
        }

        if ($role !== 'admin' && !$isGuruBK) {
            header('Location: ' . BASEURL . '/auth/login');
            exit;
        }

        $this->data['daftar_semester'] = $this->model('TahunPelajaran_model')->getAllSemester();
        $this->data['judul'] = 'Bimbingan Konseling';
    }

    private function loadSidebar()
    {
        $role = $_SESSION['role'] ?? 'guru';
        if ($role === 'admin') {
            $this->view('templates/sidebar_admin', $this->data);
        } elseif ($role === 'wali_kelas') {
            $this->view('templates/sidebar_walikelas', $this->data);
        } else {
            $this->view('templates/sidebar_guru', $this->data);
        }
    }

    public function index()
    {
        $this->dashboard();
    }

    public function dashboard()
    {
        $this->data['judul'] = 'Dashboard BK';
        $id_guru = $_SESSION['id_ref'] ?? 0;
        $id_tp = $_SESSION['id_tp_aktif'] ?? 0;

        $bkModel = $this->model('BK_model');
        $this->data['stats'] = $bkModel->countKasusByStatus($id_tp, $id_guru);
        $this->data['kasus_list'] = $bkModel->getKasusByGuruBK($id_guru, $id_tp);
        $this->data['kasus_baru'] = $bkModel->getAllKasus($id_tp, ['status' => 'baru']);

        $this->view('templates/header', $this->data);
        $this->loadSidebar();
        $this->view('bk/dashboard', $this->data);
        $this->view('templates/footer', $this->data);
    }

    public function daftarKasus()
    {
        $this->data['judul'] = 'Daftar Kasus BK';
        $id_guru = $_SESSION['id_ref'] ?? 0;
        $id_tp = $_SESSION['id_tp_aktif'] ?? 0;

        $bkModel = $this->model('BK_model');

        $filters = [];
        if (!empty($_GET['status'])) $filters['status'] = $_GET['status'];
        if (!empty($_GET['kategori'])) $filters['kategori'] = $_GET['kategori'];
        if (!empty($_GET['q'])) $filters['search'] = trim($_GET['q']);

        $this->data['kasus_list'] = $bkModel->getKasusByGuruBK($id_guru, $id_tp, $filters);
        $this->data['stats'] = $bkModel->countKasusByStatus($id_tp, $id_guru);
        $this->data['filters'] = $filters;

        $this->view('templates/header', $this->data);
        $this->loadSidebar();
        $this->view('bk/daftar_kasus', $this->data);
        $this->view('templates/footer', $this->data);
    }

    public function semuaKasus()
    {
        $this->data['judul'] = 'Semua Kasus BK';
        $id_tp = $_SESSION['id_tp_aktif'] ?? 0;

        $bkModel = $this->model('BK_model');

        $filters = [];
        if (!empty($_GET['status'])) $filters['status'] = $_GET['status'];
        if (!empty($_GET['kategori'])) $filters['kategori'] = $_GET['kategori'];
        if (!empty($_GET['kelas'])) $filters['id_kelas'] = (int) $_GET['kelas'];
        if (!empty($_GET['q'])) $filters['search'] = trim($_GET['q']);

        $this->data['kasus_list'] = $bkModel->getAllKasus($id_tp, $filters);
        $this->data['stats'] = $bkModel->countKasusByStatus($id_tp);
        $this->data['kelas_list'] = $this->model('Kelas_model')->getAllKelasWithDetails($id_tp);
        $this->data['filters'] = $filters;

        $this->view('templates/header', $this->data);
        $this->loadSidebar();
        $this->view('bk/semua_kasus', $this->data);
        $this->view('templates/footer', $this->data);
    }

    public function tambahKasus()
    {
        $this->data['judul'] = 'Tambah Kasus BK';
        $id_tp = $_SESSION['id_tp_aktif'] ?? 0;

        $this->data['kelas_list'] = $this->model('Kelas_model')->getAllKelasWithDetails($id_tp);
        $this->data['siswa_list'] = [];
        $this->data['guru_bk_list'] = $this->model('BK_model')->getGuruBKList($id_tp);

        $id_kelas = (int) ($_GET['kelas'] ?? 0);
        if ($id_kelas) {
            $this->data['siswa_list'] = $this->model('BK_model')->getSiswaByKelas($id_kelas, $id_tp);
            $this->data['selected_kelas'] = $id_kelas;
        }

        $this->view('templates/header', $this->data);
        $this->loadSidebar();
        $this->view('bk/tambah_kasus', $this->data);
        $this->view('templates/footer', $this->data);
    }

    public function prosesTambahKasus()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/bk/daftarKasus');
            exit;
        }

        $id_tp = $_SESSION['id_tp_aktif'] ?? 0;
        $id_semester = $_SESSION['id_semester_aktif'] ?? 0;
        $id_guru = $_SESSION['id_ref'] ?? 0;

        $id_siswa = (int) ($_POST['id_siswa'] ?? 0);
        $id_kelas = (int) ($_POST['id_kelas'] ?? 0);
        $kategori = $_POST['kategori'] ?? 'pelanggaran';
        $judul = trim($_POST['judul'] ?? '');
        $deskripsi = trim($_POST['deskripsi'] ?? '');
        $tingkat = $_POST['tingkat'] ?? 'ringan';
        $poin = (int) ($_POST['poin'] ?? 0);

        if (!$id_siswa || !$id_kelas || !$judul) {
            Flasher::setFlash('Data tidak lengkap.', 'danger');
            header('Location: ' . BASEURL . '/bk/tambahKasus?kelas=' . $id_kelas);
            exit;
        }

        $data = [
            'id_siswa' => $id_siswa,
            'id_kelas' => $id_kelas,
            'id_tp' => $id_tp,
            'id_semester' => $id_semester,
            'kategori' => $kategori,
            'judul' => $judul,
            'deskripsi' => $deskripsi,
            'tingkat' => $tingkat,
            'poin' => $poin,
            'status' => 'baru',
            'id_guru_pelapor' => $id_guru,
            'id_guru_bk' => $id_guru,
        ];

        $bkModel = $this->model('BK_model');
        if ($bkModel->tambahKasus($data)) {
            Flasher::setFlash('Berhasil', 'Kasus BK berhasil ditambahkan.', 'success');
        } else {
            Flasher::setFlash('Gagal', 'Gagal menambahkan kasus BK.', 'danger');
        }

        header('Location: ' . BASEURL . '/bk/daftarKasus');
        exit;
    }

    public function detailKasus($id_kasus = 0)
    {
        $this->data['judul'] = 'Detail Kasus BK';
        $id_kasus = (int) $id_kasus;

        $bkModel = $this->model('BK_model');
        $kasus = $bkModel->getKasusById($id_kasus);

        if (!$kasus) {
            Flasher::setFlash('Gagal', 'Data kasus tidak ditemukan.', 'danger');
            header('Location: ' . BASEURL . '/bk/daftarKasus');
            exit;
        }

        $this->data['kasus'] = $kasus;
        $this->data['konseling_list'] = $bkModel->getKonselingByKasus($id_kasus);
        $this->data['panggilan_list'] = $bkModel->getPanggilanByKasus($id_kasus);
        $this->data['next_konseling_ke'] = $bkModel->getNextKonselingKe($id_kasus);

        $this->view('templates/header', $this->data);
        $this->loadSidebar();
        $this->view('bk/detail_kasus', $this->data);
        $this->view('templates/footer', $this->data);
    }

    public function editKasus($id_kasus = 0)
    {
        $this->data['judul'] = 'Edit Kasus BK';
        $id_kasus = (int) $id_kasus;
        $id_tp = $_SESSION['id_tp_aktif'] ?? 0;

        $bkModel = $this->model('BK_model');
        $kasus = $bkModel->getKasusById($id_kasus);

        if (!$kasus) {
            Flasher::setFlash('Gagal', 'Data kasus tidak ditemukan.', 'danger');
            header('Location: ' . BASEURL . '/bk/daftarKasus');
            exit;
        }

        $this->data['kasus'] = $kasus;
        $this->data['guru_bk_list'] = $bkModel->getGuruBKList($id_tp);

        $this->view('templates/header', $this->data);
        $this->loadSidebar();
        $this->view('bk/edit_kasus', $this->data);
        $this->view('templates/footer', $this->data);
    }

    public function prosesEditKasus()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/bk/daftarKasus');
            exit;
        }

        $id_kasus = (int) ($_POST['id_kasus'] ?? 0);
        if (!$id_kasus) {
            Flasher::setFlash('Gagal', 'ID kasus tidak valid.', 'danger');
            header('Location: ' . BASEURL . '/bk/daftarKasus');
            exit;
        }

        $data = [
            'kategori' => $_POST['kategori'] ?? 'pelanggaran',
            'judul' => trim($_POST['judul'] ?? ''),
            'deskripsi' => trim($_POST['deskripsi'] ?? ''),
            'tingkat' => $_POST['tingkat'] ?? 'ringan',
            'poin' => (int) ($_POST['poin'] ?? 0),
            'status' => $_POST['status'] ?? 'baru',
            'id_guru_bk' => (int) ($_POST['id_guru_bk'] ?? 0) ?: null,
            'tindak_lanjut' => trim($_POST['tindak_lanjut'] ?? ''),
        ];

        $bkModel = $this->model('BK_model');
        $bkModel->updateKasus($id_kasus, $data);
        Flasher::setFlash('Berhasil', 'Kasus BK berhasil diperbarui.', 'success');
        header('Location: ' . BASEURL . '/bk/detailKasus/' . $id_kasus);
        exit;
    }

    public function ambilKasus()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/bk/daftarKasus');
            exit;
        }

        $id_kasus = (int) ($_POST['id_kasus'] ?? 0);
        $id_guru = $_SESSION['id_ref'] ?? 0;

        $bkModel = $this->model('BK_model');
        if ($bkModel->ambilKasus($id_kasus, $id_guru)) {
            Flasher::setFlash('Berhasil', 'Kasus berhasil diambil.', 'success');
        } else {
            Flasher::setFlash('Gagal', 'Kasus tidak bisa diambil (mungkin sudah ditangani).', 'danger');
        }

        header('Location: ' . BASEURL . '/bk/detailKasus/' . $id_kasus);
        exit;
    }

    public function tambahKonseling($id_kasus = 0)
    {
        $this->data['judul'] = 'Tambah Konseling';
        $id_kasus = (int) $id_kasus;

        $bkModel = $this->model('BK_model');
        $kasus = $bkModel->getKasusById($id_kasus);

        if (!$kasus) {
            Flasher::setFlash('Gagal', 'Data kasus tidak ditemukan.', 'danger');
            header('Location: ' . BASEURL . '/bk/daftarKasus');
            exit;
        }

        $this->data['kasus'] = $kasus;
        $this->data['next_ke'] = $bkModel->getNextKonselingKe($id_kasus);

        $this->view('templates/header', $this->data);
        $this->loadSidebar();
        $this->view('bk/tambah_konseling', $this->data);
        $this->view('templates/footer', $this->data);
    }

    public function prosesTambahKonseling()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/bk/daftarKasus');
            exit;
        }

        $id_kasus = (int) ($_POST['id_kasus'] ?? 0);
        $id_guru = $_SESSION['id_ref'] ?? 0;

        $bkModel = $this->model('BK_model');
        $kasus = $bkModel->getKasusById($id_kasus);

        if (!$kasus) {
            Flasher::setFlash('Gagal', 'Data kasus tidak ditemukan.', 'danger');
            header('Location: ' . BASEURL . '/bk/daftarKasus');
            exit;
        }

        $data = [
            'id_kasus' => $id_kasus,
            'id_siswa' => $kasus['id_siswa'],
            'id_guru_bk' => $id_guru,
            'jenis' => $_POST['jenis'] ?? 'individual',
            'tanggal' => $_POST['tanggal'] ?? date('Y-m-d'),
            'waktu_mulai' => $_POST['waktu_mulai'] ?: null,
            'waktu_selesai' => $_POST['waktu_selesai'] ?: null,
            'tempat' => trim($_POST['tempat'] ?? ''),
            'catatan' => trim($_POST['catatan'] ?? ''),
            'hasil' => trim($_POST['hasil'] ?? ''),
            'rencana_tindak_lanjut' => trim($_POST['rencana_tindak_lanjut'] ?? ''),
            'konseling_ke' => $bkModel->getNextKonselingKe($id_kasus),
        ];

        if ($bkModel->tambahKonseling($data)) {
            Flasher::setFlash('Berhasil', 'Sesi konseling berhasil dicatat.', 'success');
        } else {
            Flasher::setFlash('Gagal', 'Gagal mencatat sesi konseling.', 'danger');
        }

        header('Location: ' . BASEURL . '/bk/detailKasus/' . $id_kasus);
        exit;
    }

    public function tambahPanggilan($id_kasus = 0)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/bk/detailKasus/' . $id_kasus);
            exit;
        }

        $id_kasus = (int) ($_POST['id_kasus'] ?? $id_kasus);
        $bkModel = $this->model('BK_model');
        $kasus = $bkModel->getKasusById($id_kasus);

        if (!$kasus) {
            Flasher::setFlash('Gagal', 'Data kasus tidak ditemukan.', 'danger');
            header('Location: ' . BASEURL . '/bk/daftarKasus');
            exit;
        }

        $data = [
            'id_kasus' => $id_kasus,
            'id_siswa' => $kasus['id_siswa'],
            'nomor_surat' => trim($_POST['nomor_surat'] ?? ''),
            'tanggal_surat' => $_POST['tanggal_surat'] ?: null,
            'tanggal_panggilan' => $_POST['tanggal_panggilan'] ?? date('Y-m-d'),
            'status' => 'dijadwalkan',
        ];

        if ($bkModel->tambahPanggilan($data)) {
            Flasher::setFlash('Berhasil', 'Panggilan orang tua berhasil dijadwalkan.', 'success');
        } else {
            Flasher::setFlash('Gagal', 'Gagal menjadwalkan panggilan.', 'danger');
        }

        header('Location: ' . BASEURL . '/bk/detailKasus/' . $id_kasus);
        exit;
    }

    public function updatePanggilan()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/bk/daftarKasus');
            exit;
        }

        $id_panggilan = (int) ($_POST['id_panggilan'] ?? 0);
        $id_kasus = (int) ($_POST['id_kasus'] ?? 0);

        $data = [
            'status' => $_POST['status'] ?? 'dijadwalkan',
            'catatan_hasil' => trim($_POST['catatan_hasil'] ?? ''),
            'tanggal_panggilan' => $_POST['tanggal_panggilan'] ?? date('Y-m-d'),
        ];

        $bkModel = $this->model('BK_model');
        $bkModel->updatePanggilan($id_panggilan, $data);
        Flasher::setFlash('Berhasil', 'Status panggilan berhasil diperbarui.', 'success');
        header('Location: ' . BASEURL . '/bk/detailKasus/' . $id_kasus);
        exit;
    }

    public function hapusKasus()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/bk/daftarKasus');
            exit;
        }

        $id_kasus = (int) ($_POST['id_kasus'] ?? 0);
        $bkModel = $this->model('BK_model');
        $bkModel->deleteKasus($id_kasus);
        Flasher::setFlash('Berhasil', 'Kasus BK berhasil dihapus.', 'success');
        header('Location: ' . BASEURL . '/bk/daftarKasus');
        exit;
    }
}
