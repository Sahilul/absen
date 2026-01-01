<?php

class BukuTamuController extends Controller
{
    private $data = [];

    public function __construct()
    {
        // Cek login admin
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            header('Location: ' . BASEURL . '/auth/login');
            exit;
        }
    }

    /**
     * Dashboard Buku Tamu
     */
    public function index()
    {
        $this->data['judul'] = 'Buku Tamu Digital';

        $bukuTamuModel = $this->model('BukuTamu_model');
        $lembagaModel = $this->model('BukuTamuLembaga_model');

        // Filter by lembaga
        $id_lembaga = $_GET['lembaga'] ?? null;
        $this->data['selected_lembaga'] = $id_lembaga;

        if ($id_lembaga) {
            $this->data['stats'] = $bukuTamuModel->getStats($id_lembaga);
            $this->data['tamu_list'] = $bukuTamuModel->getByLembaga($id_lembaga, 50);
        } else {
            $this->data['stats'] = $bukuTamuModel->getStats();
            $this->data['tamu_list'] = $bukuTamuModel->getAll(50);
        }

        $this->data['lembaga_list'] = $lembagaModel->getActive();

        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/buku_tamu/index', $this->data);
        $this->view('templates/footer', $this->data);
    }

    /**
     * Kelola Lembaga
     */
    public function lembaga()
    {
        $this->data['judul'] = 'Kelola Lembaga';

        $lembagaModel = $this->model('BukuTamuLembaga_model');
        $this->data['lembaga_list'] = $lembagaModel->getAll();

        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/buku_tamu/lembaga', $this->data);
        $this->view('templates/footer', $this->data);
    }

    /**
     * Proses tambah/edit lembaga
     */
    public function prosesLembaga()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/bukuTamu/lembaga');
            exit;
        }

        $lembagaModel = $this->model('BukuTamuLembaga_model');
        $id = $_POST['id'] ?? null;

        $data = [
            'nama_lembaga' => trim($_POST['nama_lembaga'] ?? ''),
            'kode_lembaga' => trim($_POST['kode_lembaga'] ?? ''),
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];

        if (empty($data['nama_lembaga'])) {
            Flasher::setFlash('Nama lembaga wajib diisi!', 'danger');
            header('Location: ' . BASEURL . '/bukuTamu/lembaga');
            exit;
        }

        if ($id) {
            $lembagaModel->update($id, $data);
            Flasher::setFlash('Lembaga berhasil diupdate!', 'success');
        } else {
            $lembagaModel->create($data);
            Flasher::setFlash('Lembaga berhasil ditambah!', 'success');
        }

        header('Location: ' . BASEURL . '/bukuTamu/lembaga');
        exit;
    }

    /**
     * Toggle aktif lembaga
     */
    public function toggleLembaga($id)
    {
        $lembagaModel = $this->model('BukuTamuLembaga_model');
        $lembagaModel->toggleActive($id);

        header('Location: ' . BASEURL . '/bukuTamu/lembaga');
        exit;
    }

    /**
     * Hapus lembaga
     */
    public function hapusLembaga($id)
    {
        $lembagaModel = $this->model('BukuTamuLembaga_model');
        $lembagaModel->delete($id);

        Flasher::setFlash('Lembaga berhasil dihapus!', 'success');
        header('Location: ' . BASEURL . '/bukuTamu/lembaga');
        exit;
    }

    /**
     * Form Generate Link
     */
    public function generateLink()
    {
        $this->data['judul'] = 'Generate Link Tamu';

        $lembagaModel = $this->model('BukuTamuLembaga_model');
        $this->data['lembaga_list'] = $lembagaModel->getActive();

        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/buku_tamu/generate_link', $this->data);
        $this->view('templates/footer', $this->data);
    }

    /**
     * Proses Generate Link + Kirim WA
     */
    public function prosesGenerateLink()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/bukuTamu/generateLink');
            exit;
        }

        $linkModel = $this->model('BukuTamuLink_model');
        $lembagaModel = $this->model('BukuTamuLembaga_model');

        $id_lembaga = $_POST['id_lembaga'] ?? null;
        $nama_tamu = trim($_POST['nama_tamu'] ?? '');
        $no_wa = trim($_POST['no_wa_tamu'] ?? '');
        $keperluan = trim($_POST['keperluan_prefill'] ?? '');
        $expiry_hours = intval($_POST['expiry_hours'] ?? 24);

        if (!$id_lembaga || !$no_wa) {
            Flasher::setFlash('Lembaga dan No. WA wajib diisi!', 'danger');
            header('Location: ' . BASEURL . '/bukuTamu/generateLink');
            exit;
        }

        // Calculate expired_at
        $expired_at = date('Y-m-d H:i:s', strtotime("+{$expiry_hours} hours"));

        // Create link
        $result = $linkModel->create([
            'id_lembaga' => $id_lembaga,
            'nama_tamu' => $nama_tamu,
            'no_wa_tamu' => $no_wa,
            'keperluan_prefill' => $keperluan,
            'expired_at' => $expired_at,
            'created_by' => $_SESSION['id_ref'] ?? 0
        ]);

        $token = $result['token'];
        $link = BASEURL . '/tamu/' . $token;

        // Get lembaga name and app name
        $lembaga = $lembagaModel->getById($id_lembaga);
        $pengaturanModel = $this->model('PengaturanAplikasi_model');
        $pengaturan = $pengaturanModel->getPengaturan();
        $namaApp = $pengaturan['nama_aplikasi'] ?? $pengaturan['nama_sekolah'] ?? 'Sekolah';

        // Format WA message
        $greeting = $nama_tamu ? "Yth. {$nama_tamu}," : "Assalamu'alaikum,";
        $waMessage = "{$greeting}\n\n";
        $waMessage .= "Anda diundang untuk mengisi Buku Tamu Digital *{$lembaga['nama_lembaga']}*.\n\n";
        $waMessage .= "📝 Silakan klik link berikut:\n{$link}\n\n";
        $waMessage .= "⏰ Link berlaku sampai: " . date('d M Y H:i', strtotime($expired_at)) . "\n\n";
        $waMessage .= "Terima kasih.\n_{$namaApp}_";

        // Send via WA
        $waSent = false;
        try {
            require_once APPROOT . '/app/core/Fonnte.php';
            $fonnte = new Fonnte();
            $waResult = $fonnte->send($no_wa, $waMessage);
            $waSent = !empty($waResult['status']) && $waResult['status'] === true;
        } catch (Exception $e) {
            error_log("BukuTamu WA Error: " . $e->getMessage());
        }

        $flashMsg = "Link berhasil dibuat!";
        if ($waSent) {
            $flashMsg .= " ✅ Pesan WA terkirim.";
        } else {
            $flashMsg .= " ⚠️ Gagal kirim WA. Link: " . $link;
        }

        Flasher::setFlash($flashMsg, 'success');
        header('Location: ' . BASEURL . '/bukuTamu');
        exit;
    }

    /**
     * Detail Tamu
     */
    public function detail($id)
    {
        $this->data['judul'] = 'Detail Tamu';

        $bukuTamuModel = $this->model('BukuTamu_model');
        $this->data['tamu'] = $bukuTamuModel->getById($id);

        if (!$this->data['tamu']) {
            header('Location: ' . BASEURL . '/bukuTamu');
            exit;
        }

        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/buku_tamu/detail', $this->data);
        $this->view('templates/footer', $this->data);
    }

    /**
     * Set waktu pulang
     */
    public function setPulang($id)
    {
        $bukuTamuModel = $this->model('BukuTamu_model');
        $bukuTamuModel->setPulang($id);

        Flasher::setFlash('Waktu pulang tercatat!', 'success');
        header('Location: ' . BASEURL . '/bukuTamu');
        exit;
    }

    /**
     * Hapus data tamu
     */
    public function hapus($id)
    {
        $bukuTamuModel = $this->model('BukuTamu_model');
        $bukuTamuModel->delete($id);

        Flasher::setFlash('Data tamu dihapus!', 'success');
        header('Location: ' . BASEURL . '/bukuTamu');
        exit;
    }

    /**
     * Filter by date (AJAX)
     */
    public function filter()
    {
        header('Content-Type: application/json');

        $start = $_GET['start'] ?? date('Y-m-d');
        $end = $_GET['end'] ?? date('Y-m-d');
        $id_lembaga = $_GET['id_lembaga'] ?? null;

        $bukuTamuModel = $this->model('BukuTamu_model');
        $data = $bukuTamuModel->getByDateRange($start, $end, $id_lembaga ?: null);

        echo json_encode(['success' => true, 'data' => $data]);
        exit;
    }
}
