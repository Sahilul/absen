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

    // ─── v1.35.11 - CETAK SURAT PEMANGGILAN ORANG TUA ──────

    public function cetakPanggilan($id_panggilan = 0)
    {
        $id_panggilan = (int) $id_panggilan;
        if (!$id_panggilan) {
            Flasher::setFlash('Gagal', 'ID panggilan tidak valid.', 'danger');
            header('Location: ' . BASEURL . '/bk/daftarKasus');
            exit;
        }

        $bkModel = $this->model('BK_model');
        $panggilan = $bkModel->getPanggilanForCetak($id_panggilan);

        if (!$panggilan) {
            Flasher::setFlash('Gagal', 'Data panggilan tidak ditemukan.', 'danger');
            header('Location: ' . BASEURL . '/bk/daftarKasus');
            exit;
        }

        // Generate nomor surat jika belum ada
        if (empty($panggilan['nomor_surat'])) {
            $tanggalSurat = $panggilan['tanggal_surat'] ?: date('Y-m-d');
            $nomorSurat = $bkModel->generateNomorSuratPanggilan($tanggalSurat);
            $bkModel->updateNomorSurat($id_panggilan, $nomorSurat, $tanggalSurat);
            $panggilan['nomor_surat'] = $nomorSurat;
            $panggilan['tanggal_surat'] = $tanggalSurat;
        }

        // Resolve lembaga berdasarkan jenjang kelas siswa
        require_once APPROOT . '/app/models/Lembaga_model.php';
        $lembagaModel = new Lembaga_model();
        $lembaga = $lembagaModel->getByJenjang($panggilan['jenjang'] ?? '');

        // Fallback: jika lembaga tidak ditemukan, coba dari pengaturan rapor
        if (!$lembaga) {
            $lembaga = [
                'nama_lembaga' => '',
                'nama_kepala' => '',
                'nip_kepala' => '',
                'kop_rapor' => '',
                'ttd_kepala' => '',
                'kota' => 'Mojokerto',
                'alamat' => '',
            ];
        }

        // Generate QR code
        require_once APPROOT . '/config/qrcode.php';
        $qrData = generatePDFQRCode('bk_panggilan', $id_panggilan, [
            'nomor' => $panggilan['nomor_surat'],
            'siswa' => $panggilan['nama_siswa'],
            'tanggal' => $panggilan['tanggal_surat'],
        ]);

        // Build HTML & render PDF
        require_once APPROOT . '/app/core/dompdf/autoload.inc.php';
        $html = $this->buildSuratPanggilanPDF($panggilan, $lembaga, $qrData);

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Times-Roman');

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'Surat_Panggilan_' . str_replace(' ', '_', $panggilan['nama_siswa']) . '_' . date('Ymd') . '.pdf';
        $dompdf->stream($filename, ['Attachment' => false]);
        exit;
    }

    private function buildSuratPanggilanPDF($panggilan, $lembaga, $qrData = '')
    {
        // Kop surat - base64 encode
        $kopHTML = '';
        if (!empty($lembaga['kop_rapor'])) {
            $kopPath = APPROOT . '/public/img/lembaga/' . $lembaga['kop_rapor'];
            if (file_exists($kopPath)) {
                $imageData = base64_encode(file_get_contents($kopPath));
                $mimeType = 'image/' . pathinfo($kopPath, PATHINFO_EXTENSION);
                $kopHTML = '<div style="text-align:center; margin-bottom:10px;">
                    <img src="data:' . $mimeType . ';base64,' . $imageData . '" style="max-width:100%; height:auto; max-height:110px;">
                </div>';
            }
        }

        // TTD Kepala - base64 encode
        $ttdHTML = '';
        if (!empty($lembaga['ttd_kepala'])) {
            $ttdPath = APPROOT . '/public/img/lembaga/' . $lembaga['ttd_kepala'];
            if (file_exists($ttdPath)) {
                $imageData = base64_encode(file_get_contents($ttdPath));
                $mimeType = 'image/' . pathinfo($ttdPath, PATHINFO_EXTENSION);
                $ttdHTML = '<img src="data:' . $mimeType . ';base64,' . $imageData . '" style="max-width:120px; height:auto; max-height:50px;">';
            }
        }

        // Format tanggal Indonesia
        $bulanIndo = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                      'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $hariIndo = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

        $tglSurat = strtotime($panggilan['tanggal_surat']);
        $tglSuratFormatted = date('d', $tglSurat) . ' ' . $bulanIndo[(int) date('m', $tglSurat)] . ' ' . date('Y', $tglSurat);

        $tglPanggilan = strtotime($panggilan['tanggal_panggilan']);
        $hariPanggilan = $hariIndo[(int) date('w', $tglPanggilan)];
        $tglPanggilanFormatted = date('d', $tglPanggilan) . ' ' . $bulanIndo[(int) date('m', $tglPanggilan)] . ' ' . date('Y', $tglPanggilan);

        // Nama orang tua: prioritas ayah → ibu → wali
        $namaOrtu = $panggilan['ayah_kandung'] ?? '';
        if (empty($namaOrtu)) $namaOrtu = $panggilan['ibu_kandung'] ?? '';
        if (empty($namaOrtu)) $namaOrtu = $panggilan['wali_nama'] ?? '';
        if (empty($namaOrtu)) $namaOrtu = 'Orang Tua/Wali';

        // Alamat siswa
        $alamatParts = array_filter([
            $panggilan['alamat'] ?? '',
            !empty($panggilan['rt']) ? 'RT ' . $panggilan['rt'] : '',
            !empty($panggilan['rw']) ? 'RW ' . $panggilan['rw'] : '',
            $panggilan['desa'] ?? '',
            $panggilan['kecamatan'] ?? '',
            $panggilan['kabupaten'] ?? '',
        ]);
        $alamatSiswa = implode(', ', $alamatParts) ?: '-';

        $kota = $lembaga['kota'] ?? 'Mojokerto';
        $namaKepala = $lembaga['nama_kepala'] ?? '';
        $nipKepala = $lembaga['nip_kepala'] ?? '';
        $namaLembaga = $lembaga['nama_lembaga'] ?? '';

        // Kategori kasus
        $kategoriMap = [
            'pelanggaran' => 'pelanggaran tata tertib',
            'akademik' => 'permasalahan akademik',
            'pribadi' => 'permasalahan pribadi',
            'sosial' => 'permasalahan sosial',
            'karir' => 'bimbingan karir',
        ];
        $kategoriText = $kategoriMap[$panggilan['kategori'] ?? ''] ?? 'permasalahan';

        // QR code HTML
        $qrHTML = '';
        if (!empty($qrData)) {
            $qrHTML = '<img src="' . $qrData . '" style="width:50px; height:50px; margin:3px auto;">';
        }

        $html = '<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Surat Pemanggilan Orang Tua - ' . htmlspecialchars($panggilan['nama_siswa']) . '</title>
<style>
    @page { margin: 12mm 18mm 12mm 18mm; }
    body { font-family: "Times New Roman", Times, serif; font-size: 11pt; line-height: 1.3; color: #000; }
    .header-surat { margin-bottom: 3px; }
    .nomor-table td { padding: 1px 4px; font-size: 11pt; vertical-align: top; }
    .content { text-align: justify; margin-top: 8px; }
    .content p { margin: 0 0 4px 0; text-indent: 35px; }
    .data-table { margin: 2px 0 2px 35px; }
    .data-table td { padding: 1px 6px; font-size: 11pt; vertical-align: top; }
    .ttd-area { float: right; width: 260px; text-align: center; margin-top: 12px; }
    .ttd-area p { margin: 0 0 2px 0; }
    .ttd-area .jabatan { margin-bottom: 3px; }
    .ttd-area .nama { font-weight: bold; text-decoration: underline; }
    .ttd-area .nip { font-size: 9pt; }
    .clear { clear: both; }
    .footer-note { position: fixed; bottom: 0; left: 0; right: 0; text-align: center; font-size: 8pt; color: #666; border-top: 1px solid #ddd; padding-top: 4px; font-style: italic; }
</style>
</head>
<body>

' . $kopHTML . '

<div class="header-surat">
    <table class="nomor-table" style="width:100%;">
        <tr>
            <td style="width:65px;">Nomor</td>
            <td style="width:10px;">:</td>
            <td>' . htmlspecialchars($panggilan['nomor_surat']) . '</td>
        </tr>
        <tr>
            <td>Lampiran</td>
            <td>:</td>
            <td>-</td>
        </tr>
        <tr>
            <td>Hal</td>
            <td>:</td>
            <td><b>Pemanggilan Orang Tua/Wali Murid</b></td>
        </tr>
    </table>
</div>

<div style="margin-top:10px;">
    <p style="margin:0;">Kepada Yth.</p>
    <p style="margin:0;"><b>Bapak/Ibu ' . htmlspecialchars($namaOrtu) . '</b></p>
    <p style="margin:0;">Orang Tua/Wali dari Ananda <b>' . htmlspecialchars($panggilan['nama_siswa']) . '</b></p>
    <p style="margin:0;">di ' . htmlspecialchars($alamatSiswa) . '</p>
</div>

<div class="content">
    <p><i>Assalamu\'alaikum Wr. Wb.</i></p>

    <p>Dengan hormat, melalui surat ini kami sampaikan bahwa putra/putri Bapak/Ibu yang bernama:</p>

    <table class="data-table">
        <tr>
            <td>Nama</td>
            <td>:</td>
            <td><b>' . htmlspecialchars($panggilan['nama_siswa']) . '</b></td>
        </tr>
        <tr>
            <td>NISN</td>
            <td>:</td>
            <td>' . htmlspecialchars($panggilan['nisn'] ?? '-') . '</td>
        </tr>
        <tr>
            <td>Kelas</td>
            <td>:</td>
            <td>' . htmlspecialchars($panggilan['nama_kelas'] ?? '-') . '</td>
        </tr>
    </table>

    <p>Sehubungan dengan adanya ' . htmlspecialchars($kategoriText) . ' yang perlu kami sampaikan dan diskusikan bersama, maka kami mengharapkan kehadiran Bapak/Ibu untuk datang ke madrasah pada:</p>

    <table class="data-table">
        <tr>
            <td>Hari/Tanggal</td>
            <td>:</td>
            <td><b>' . $hariPanggilan . ', ' . $tglPanggilanFormatted . '</b></td>
        </tr>
        <tr>
            <td>Tempat</td>
            <td>:</td>
            <td>Ruang BK ' . htmlspecialchars($namaLembaga) . '</td>
        </tr>
    </table>

    <p>Kehadiran Bapak/Ibu sangat kami harapkan demi kelancaran proses pembinaan dan perkembangan putra/putri Bapak/Ibu. Atas perhatian dan kerjasamanya, kami ucapkan terima kasih.</p>

    <p><i>Wassalamu\'alaikum Wr. Wb.</i></p>
</div>

<div class="ttd-area">
    <p>' . htmlspecialchars($kota) . ', ' . $tglSuratFormatted . '</p>
    <p class="jabatan">Kepala ' . htmlspecialchars($namaLembaga) . '</p>
    ' . $qrHTML . '
    ' . $ttdHTML . '
    <p class="nama">' . htmlspecialchars($namaKepala) . '</p>
    ' . (!empty($nipKepala) ? '<p class="nip">NIP. ' . htmlspecialchars($nipKepala) . '</p>' : '') . '
</div>

<div class="clear"></div>

<div class="footer-note">
    Dokumen ini telah ditandatangani secara elektronik menggunakan kode QR dan sah tanpa memerlukan tanda tangan basah.<br>
    Untuk memvalidasi keaslian dokumen ini, silakan pindai (scan) kode QR di atas.
</div>

</body>
</html>';

        return $html;
    }
}
