<?php
// File: app/controllers/BendaharaController.php
// Controller untuk fitur Bendahara - Kelola pembayaran SEMUA kelas

class BendaharaController extends Controller
{
    private $data = [];

    public function __construct()
    {
        // Guard: harus login
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASEURL . '/auth/login');
            exit;
        }

        // Set data umum
        $this->data['daftar_semester'] = $this->model('TahunPelajaran_model')->getAllSemester();

        // Set default semester jika belum ada
        if (!isset($_SESSION['id_semester_aktif']) && !empty($this->data['daftar_semester'])) {
            $defaultSemester = $this->data['daftar_semester'][0];
            $_SESSION['id_semester_aktif'] = $defaultSemester['id_semester'];
            $_SESSION['nama_semester_aktif'] = $defaultSemester['nama_tp'] . ' - ' . $defaultSemester['semester'];
            $_SESSION['id_tp_aktif'] = $defaultSemester['id_tp'];
        }

        $role = $_SESSION['role'] ?? '';
        $id_guru = $_SESSION['id_ref'] ?? 0;
        $id_tp_aktif = $_SESSION['id_tp_aktif'] ?? 0;

        // Cek apakah user adalah bendahara
        require_once APPROOT . '/app/models/GuruFungsi_model.php';
        $guruFungsiModel = new GuruFungsi_model();
        $isBendahara = $guruFungsiModel->isBendahara($id_guru, $id_tp_aktif);

        // Hanya bendahara atau admin yang bisa akses
        if (!$isBendahara && $role !== 'admin') {
            Flasher::setFlash('Akses ditolak', 'Anda bukan bendahara untuk Tahun Pelajaran aktif ini.', 'danger');
            // Redirect ke halaman yang aman (Dashboard Guru atau Home) untuk mencegah loop
            header('Location: ' . BASEURL . '/guru/dashboard');
            exit;
        }
    }

    /**
     * Dashboard Pembayaran Bendahara
     */
    public function index()
    {
        $this->pembayaran();
    }

    /**
     * Halaman utama pembayaran - lihat semua kelas
     */
    public function pembayaran()
    {
        $this->data['judul'] = 'Bendahara - Pembayaran Semua Kelas';
        $id_tp_aktif = $_SESSION['id_tp_aktif'] ?? 0;

        // Get all kelas dengan info pembayaran
        $kelasList = $this->model('Kelas_model')->getKelasByTP($id_tp_aktif);

        // Tambahkan info pembayaran per kelas
        foreach ($kelasList as &$kelas) {
            $kelas['jumlah_siswa'] = $this->model('Kelas_model')->getJumlahSiswaKelas($kelas['id_kelas'], $id_tp_aktif);
            $kelas['pembayaran'] = $this->getPembayaranSummary($kelas['id_kelas'], $id_tp_aktif);
        }

        $this->data['kelas_list'] = $kelasList;

        $this->view('templates/header', $this->data);
        // Sidebar handled by header.php
        $this->view('bendahara/pembayaran', $this->data);
        $this->view('templates/footer');
    }

    /**
     * Kelola pembayaran kelas tertentu - load view wali_kelas langsung
     */
    public function kelolaPembayaran($id_kelas)
    {
        $this->data['judul'] = 'Bendahara - Pembayaran Kelas';
        $id_tp_aktif = $_SESSION['id_tp_aktif'] ?? 0;
        $id_semester = $_SESSION['id_semester_aktif'] ?? null;

        $kelas = $this->model('Kelas_model')->getKelasById($id_kelas);
        if (!$kelas) {
            Flasher::setFlash('Kelas tidak ditemukan.', 'danger');
            header('Location: ' . BASEURL . '/bendahara/pembayaran');
            exit;
        }

        // Set wali_kelas_info untuk kompatibilitas dengan view wali_kelas
        $this->data['wali_kelas_info'] = [
            'id_kelas' => $id_kelas,
            'nama_kelas' => $kelas['nama_kelas']
        ];

        // Get tagihan kelas
        $this->data['tagihan_list'] = $this->model('Pembayaran_model')->getTagihanKelas($id_kelas, $id_tp_aktif, $id_semester);

        // Data untuk back button
        $this->data['bendahara_mode'] = true;

        $this->view('templates/header', $this->data);
        // Sidebar handled by header.php
        $this->view('wali_kelas/pembayaran_dashboard', $this->data);
        $this->view('templates/footer');
    }

    /**
     * Detail pembayaran per kelas
     */
    public function kelasDetail($id_kelas)
    {
        $this->data['judul'] = 'Bendahara - Detail Pembayaran Kelas';
        $id_tp_aktif = $_SESSION['id_tp_aktif'] ?? 0;

        $kelas = $this->model('Kelas_model')->getKelasById($id_kelas);
        if (!$kelas) {
            Flasher::setFlash('Error', 'Kelas tidak ditemukan.', 'danger');
            header('Location: ' . BASEURL . '/bendahara/pembayaran');
            exit;
        }

        // Get siswa di kelas ini
        $siswaList = $this->model('Keanggotaan_model')->getSiswaByKelas($id_kelas, $id_tp_aktif);

        // Get tagihan aktif untuk kelas ini
        $tagihanList = $this->model('Pembayaran_model')->getTagihanKelas($id_kelas, $id_tp_aktif);

        $this->data['kelas'] = $kelas;
        $this->data['siswa_list'] = $siswaList;
        $this->data['tagihan_list'] = $tagihanList;

        $this->view('templates/header', $this->data);
        // Sidebar handled by header.php
        $this->view('bendahara/kelas_detail', $this->data);
        $this->view('templates/footer');
    }

    /**
     * Input pembayaran untuk siswa manapun
     */
    public function inputPembayaran($id_siswa)
    {
        $this->data['judul'] = 'Bendahara - Input Pembayaran';
        $id_tp_aktif = $_SESSION['id_tp_aktif'] ?? 0;

        $siswa = $this->model('Siswa_model')->getSiswaById($id_siswa);
        if (!$siswa) {
            Flasher::setFlash('Error', 'Siswa tidak ditemukan.', 'danger');
            header('Location: ' . BASEURL . '/bendahara/pembayaran');
            exit;
        }

        // Get kelas siswa
        $keanggotaan = $this->model('Keanggotaan_model')->getKeanggotaanSiswa($id_siswa, $id_tp_aktif);

        // Get tagihan yang belum lunas
        $tagihanBelumLunas = $this->model('Pembayaran_model')->getTagihanBelumLunasSiswa($id_siswa, $id_tp_aktif);

        $this->data['siswa'] = $siswa;
        $this->data['keanggotaan'] = $keanggotaan;
        $this->data['tagihan_list'] = $tagihanBelumLunas;

        $this->view('templates/header', $this->data);
        // Sidebar handled by header.php
        $this->view('bendahara/input_pembayaran', $this->data);
        $this->view('templates/footer');
    }

    /**
     * Proses simpan pembayaran
     */
    public function prosesPembayaran()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/bendahara/pembayaran');
            exit;
        }

        $id_siswa = $_POST['id_siswa'] ?? 0;
        $id_tagihan = $_POST['id_tagihan'] ?? 0;
        $jumlah = $_POST['jumlah'] ?? 0;
        $metode = $_POST['metode'] ?? 'tunai';
        $keterangan = $_POST['keterangan'] ?? '';
        $id_tp_aktif = $_SESSION['id_tp_aktif'] ?? 0;
        $id_user = $_SESSION['user_id'] ?? 0;

        if (!$id_siswa || !$id_tagihan || !$jumlah) {
            Flasher::setFlash('Error', 'Data pembayaran tidak lengkap.', 'danger');
            header('Location: ' . BASEURL . '/bendahara/inputPembayaran/' . $id_siswa);
            exit;
        }

        // Simpan pembayaran
        $data = [
            'id_siswa' => $id_siswa,
            'id_tagihan' => $id_tagihan,
            'jumlah' => $jumlah,
            'metode' => $metode,
            'keterangan' => $keterangan,
            'id_user' => $id_user,
            'tanggal' => date('Y-m-d H:i:s')
        ];

        if ($this->model('Pembayaran_model')->simpanPembayaran($data)) {
            Flasher::setFlash('Berhasil', 'Pembayaran berhasil disimpan.', 'success');
        } else {
            Flasher::setFlash('Gagal', 'Gagal menyimpan pembayaran.', 'danger');
        }

        header('Location: ' . BASEURL . '/bendahara/inputPembayaran/' . $id_siswa);
        exit;
    }

    /**
     * Riwayat pembayaran
     */
    public function riwayat($id_kelas = null)
    {
        $this->data['judul'] = 'Bendahara - Riwayat Pembayaran';
        $id_tp_aktif = $_SESSION['id_tp_aktif'] ?? 0;

        if ($id_kelas) {
            $kelas = $this->model('Kelas_model')->getKelasById($id_kelas);
            $this->data['kelas'] = $kelas;
            $this->data['riwayat'] = $this->model('Pembayaran_model')->getRiwayatByKelas($id_kelas, $id_tp_aktif);
        } else {
            $this->data['kelas'] = null;
            $this->data['riwayat'] = $this->model('Pembayaran_model')->getAllRiwayat($id_tp_aktif);
        }

        $this->data['kelas_list'] = $this->model('Kelas_model')->getKelasByTP($id_tp_aktif);

        $this->view('templates/header', $this->data);
        // Sidebar handled by header.php
        $this->view('bendahara/riwayat', $this->data);
        $this->view('templates/footer');
    }

    /**
     * Simpan Tagihan (Action for Bendahara)
     */
    public function simpanTagihan()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/bendahara/pembayaran');
            exit;
        }

        $id_kelas = $_POST['id_kelas'] ?? 0;
        if (!$id_kelas) {
            Flasher::setFlash('Error', 'ID Kelas tidak valid', 'danger');
            header('Location: ' . BASEURL . '/bendahara/pembayaran');
            exit;
        }

        $id_tp_aktif = $_SESSION['id_tp_aktif'] ?? 0;
        $id_semester_aktif = $_SESSION['id_semester_aktif'] ?? null;
        $id_user = $_SESSION['user_id'] ?? 0;

        $mode = $_POST['mode'] ?? 'baru';
        $nama = trim($_POST['nama'] ?? '');
        $id_tagihan = $_POST['id_tagihan'] ?? null;
        $nominal_default = (int) ($_POST['nominal_default'] ?? 0);
        $jatuh_tempo = $_POST['jatuh_tempo'] ?? null;

        // Default URL redirect
        $redirectUrl = BASEURL . '/bendahara/kelolaPembayaran/' . $id_kelas;

        try {
            if ($mode === 'edit' && $id_tagihan) {
                // Update
                $updated = $this->model('Pembayaran_model')->updateTagihanKelas($id_tagihan, [
                    'nama' => $nama,
                    'nominal_default' => $nominal_default,
                    'jatuh_tempo' => $jatuh_tempo
                ]);

                if ($updated) {
                    Flasher::setFlash('Berhasil', 'Tagihan berhasil diupdate.', 'success');
                } else {
                    Flasher::setFlash('Gagal', 'Gagal mengupdate tagihan.', 'danger');
                }
            } else {
                // Buat Baru
                $newId = $this->model('Pembayaran_model')->createTagihanKelas([
                    'nama' => $nama,
                    'id_tp' => $id_tp_aktif,
                    'id_semester' => $id_semester_aktif,
                    'id_kelas' => $id_kelas,
                    'tipe' => 'sekali', // Default tipe
                    'nominal_default' => $nominal_default,
                    'jatuh_tempo' => $jatuh_tempo,
                    'created_by_user' => $id_user,
                    'created_by_role' => 'bendahara'
                ]);

                if ($newId) {
                    Flasher::setFlash('Berhasil', 'Tagihan berhasil dibuat.', 'success');
                } else {
                    Flasher::setFlash('Gagal', 'Gagal membuat tagihan.', 'danger');
                }
            }
        } catch (Exception $e) {
            Flasher::setFlash('Error', 'Terjadi kesalahan: ' . $e->getMessage(), 'danger');
        }

        header('Location: ' . $redirectUrl);
        exit;
    }

    /**
     * Hapus Tagihan (Action for Bendahara)
     */
    public function hapusTagihan()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/bendahara/pembayaran');
            exit;
        }

        $id_tagihan = $_POST['id_tagihan'] ?? 0;
        if (!$id_tagihan) {
            header('Location: ' . BASEURL . '/bendahara/pembayaran');
            exit;
        }

        // Get info tagihan untuk redirect balik ke kelas yang benar
        $tagihan = $this->model('Pembayaran_model')->getTagihanById($id_tagihan);
        $id_kelas = $tagihan['id_kelas'] ?? 0;
        $redirectUrl = $id_kelas ? BASEURL . '/bendahara/kelolaPembayaran/' . $id_kelas : BASEURL . '/bendahara/pembayaran';

        if ($this->model('Pembayaran_model')->hapusTagihan($id_tagihan)) {
            Flasher::setFlash('Berhasil', 'Tagihan berhasil dihapus.', 'success');
        } else {
            Flasher::setFlash('Gagal', 'Gagal menghapus tagihan.', 'danger');
        }

        header('Location: ' . $redirectUrl);
        exit;
    }

    /**
     * Helper: Get summary pembayaran per kelas
     * Returns empty summary for now - can be enhanced later when Pembayaran_model has proper methods
     */
    private function getPembayaranSummary($id_kelas, $id_tp)
    {
        // Return empty summary for now
        // TODO: Implement proper pembayaran summary when Pembayaran_model is ready
        return [
            'total_tagihan' => 0,
            'total_terbayar' => 0,
            'total_tunggakan' => 0
        ];
    }
}
