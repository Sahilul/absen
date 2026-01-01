<?php

class BukuTamuController extends Controller
{
    private $data = [];
    private $isPetugas = false;

    public function __construct()
    {
        // Cek login
        if (!isset($_SESSION['role'])) {
            header('Location: ' . BASEURL . '/auth/login');
            exit;
        }

        $role = $_SESSION['role'];

        // Admin always has access
        if ($role === 'admin') {
            return;
        }

        // Check if guru/wali_kelas has petugas_buku_tamu function
        if (in_array($role, ['guru', 'wali_kelas'])) {
            $id_guru = $_SESSION['id_ref'] ?? 0;
            $id_tp = $_SESSION['id_tp_aktif'] ?? 0;

            if ($id_guru && $id_tp) {
                require_once APPROOT . '/app/models/GuruFungsi_model.php';
                $guruFungsiModel = new GuruFungsi_model();
                if ($guruFungsiModel->isPetugasBukuTamu($id_guru, $id_tp)) {
                    $this->isPetugas = true;
                    return;
                }
            }
        }

        // No access
        header('Location: ' . BASEURL . '/auth/login');
        exit;
    }

    /**
     * Get correct sidebar based on user role
     */
    private function getSidebar()
    {
        $role = $_SESSION['role'] ?? '';
        if ($role === 'admin') {
            return 'templates/sidebar_admin';
        } elseif ($role === 'wali_kelas') {
            return 'templates/sidebar_walikelas';
        } else {
            return 'templates/sidebar_guru';
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
        $this->view($this->getSidebar(), $this->data);
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
        $this->view($this->getSidebar(), $this->data);
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
        $this->view($this->getSidebar(), $this->data);
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
        $this->view($this->getSidebar(), $this->data);
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

    /**
     * Download PDF Buku Tamu
     */
    public function downloadPDF()
    {
        $id_lembaga = $_GET['lembaga'] ?? null;

        $bukuTamuModel = $this->model('BukuTamu_model');
        $lembagaModel = $this->model('BukuTamuLembaga_model');

        // Get data
        if ($id_lembaga) {
            $tamu_list = $bukuTamuModel->getByLembaga($id_lembaga, 1000);
            $lembaga = $lembagaModel->getById($id_lembaga);
            $lembagaName = $lembaga['nama_lembaga'] ?? 'Semua Lembaga';
        } else {
            $tamu_list = $bukuTamuModel->getAll(1000);
            $lembagaName = 'Semua Lembaga';
        }

        // Get pengaturan aplikasi
        $pengaturanModel = $this->model('PengaturanAplikasi_model');
        $pengaturan = $pengaturanModel->getPengaturan();
        $namaApp = $pengaturan['nama_aplikasi'] ?? $pengaturan['nama_sekolah'] ?? 'Sistem Informasi Sekolah';

        // Build HTML
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; font-size: 10px; line-height: 1.3; }
        .header { text-align: center; margin-bottom: 15px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .header h1 { font-size: 16px; margin: 0; font-weight: bold; }
        .header h2 { font-size: 12px; margin: 5px 0 0 0; color: #666; font-weight: normal; }
        .info { background: #f5f5f5; padding: 8px 12px; margin-bottom: 15px; border-radius: 4px; }
        .info span { margin-right: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 6px 8px; text-align: left; vertical-align: top; }
        th { background: #f0f0f0; font-weight: bold; font-size: 9px; }
        td { font-size: 9px; }
        .footer { margin-top: 30px; padding-top: 15px; border-top: 1px solid #ddd; }
        .footer-content { display: table; width: 100%; }
        .footer-left { display: table-cell; width: 70%; vertical-align: bottom; font-size: 8px; color: #666; }
        .footer-right { display: table-cell; width: 30%; text-align: right; }
        .qr-section { text-align: center; }
        .qr-section img { width: 80px; height: 80px; }
        .qr-section p { font-size: 7px; color: #666; margin: 5px 0 0 0; }
        .validity-note { font-size: 8px; color: #666; font-style: italic; margin-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>DAFTAR BUKU TAMU</h1>
        <h2>' . htmlspecialchars($namaApp) . '</h2>
    </div>
    
    <div class="info">
        <span><strong>Lembaga:</strong> ' . htmlspecialchars($lembagaName) . '</span>
        <span><strong>Tanggal Cetak:</strong> ' . date('d/m/Y H:i') . ' WIB</span>
        <span><strong>Total Data:</strong> ' . count($tamu_list) . ' tamu</span>
    </div>
    
    <table>
        <thead>
            <tr>
                <th style="width:25px;">No</th>
                <th style="width:18%">Nama / Instansi</th>
                <th style="width:12%">No. HP</th>
                <th style="width:22%">Keperluan</th>
                <th style="width:12%">Bertemu</th>
                <th style="width:10%">Lembaga</th>
                <th style="width:13%">Waktu Datang</th>
                <th style="width:13%">Waktu Pulang</th>
            </tr>
        </thead>
        <tbody>';

        if (empty($tamu_list)) {
            $html .= '<tr><td colspan="8" style="text-align:center;color:#666;">Belum ada data tamu</td></tr>';
        } else {
            $no = 1;
            foreach ($tamu_list as $t) {
                $waktuDatang = $t['waktu_datang'] ? date('d/m/Y H:i', strtotime($t['waktu_datang'])) : '-';
                $waktuPulang = $t['waktu_pulang'] ? date('d/m/Y H:i', strtotime($t['waktu_pulang'])) : '-';
                $html .= '<tr>
                    <td style="text-align:center;">' . $no++ . '</td>
                    <td>' . htmlspecialchars($t['nama_tamu']) . '<br><small style="color:#666;">' . htmlspecialchars($t['instansi'] ?? '-') . '</small></td>
                    <td>' . htmlspecialchars($t['no_hp']) . '</td>
                    <td>' . htmlspecialchars(mb_substr($t['keperluan'], 0, 50)) . (strlen($t['keperluan']) > 50 ? '...' : '') . '</td>
                    <td>' . htmlspecialchars($t['bertemu_dengan'] ?? '-') . '</td>
                    <td>' . htmlspecialchars($t['nama_lembaga']) . '</td>
                    <td>' . $waktuDatang . '</td>
                    <td>' . $waktuPulang . '</td>
                </tr>';
            }
        }

        $html .= '
        </tbody>
    </table>
    
    <div class="footer">
        <div class="footer-content">
            <div class="footer-left">
                <p>Dokumen ini dicetak secara otomatis oleh sistem dan sah tanpa tanda tangan.</p>
                <p>Dicetak pada: ' . date('d/m/Y H:i:s') . ' WIB</p>
                <p class="validity-note">Scan QR code untuk memverifikasi keaslian dokumen ini.</p>
            </div>
            <div class="footer-right">
                <div class="qr-section" id="qr-placeholder">
                    <!-- QR will be inserted here -->
                </div>
            </div>
        </div>
    </div>
</body>
</html>';

        // Generate PDF using dompdf
        require_once APPROOT . '/app/core/dompdf/autoload.inc.php';

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new \Dompdf\Dompdf($options);

        // Add QR code for validation
        require_once APPROOT . '/app/core/PDFQRHelper.php';
        $docId = 'buku_tamu_' . ($id_lembaga ?: 'all') . '_' . date('Ymd');
        $html = PDFQRHelper::addQRToPDF($html, 'buku_tamu', $docId);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $filename = 'Buku_Tamu_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $lembagaName) . '_' . date('Ymd_His') . '.pdf';
        $dompdf->stream($filename, ['Attachment' => true]);
        exit;
    }
}
