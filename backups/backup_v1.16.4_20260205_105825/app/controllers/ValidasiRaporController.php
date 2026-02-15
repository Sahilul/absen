<?php

class ValidasiRaporController extends Controller
{
    private $data = [];
    
    // Flag untuk bypass login check - halaman ini PUBLIC
    protected $requiresAuth = false;

    public function __construct()
    {
        // Session sudah di-start di index.php, tidak perlu start lagi
        // Tidak perlu cek login untuk validasi rapor (halaman publik)
    }

    /**
     * Halaman validasi rapor dengan QR Code
     * PUBLIC ACCESS - Tidak perlu login
     */
    public function index($token = '')
    {
        $this->data['judul'] = 'Validasi Rapor';
        $this->data['token'] = $token;
        $this->data['valid'] = false;
        $this->data['message'] = '';
        $this->data['siswa_data'] = null;
        $this->data['doc_name'] = '';
        $this->data['doc_meta'] = [];
        $this->data['guru_nama'] = '';
        $this->data['kepala_nama'] = '';
        $this->data['tanggal_dibuat'] = '';

        if (!empty($token)) {
            // Load validation info from database
            try {
                require_once APPROOT . '/app/models/QRValidation_model.php';
                $qrModel = new QRValidation_model();
                $record = $qrModel->findByToken($token);
                if ($record && empty($record['revoked'])) {
                    // Valid token
                    $this->data['valid'] = true;
                    $this->data['message'] = 'Dokumen ini adalah dokumen resmi yang diterbitkan oleh sistem.';
                    // Parse meta
                    $meta = [];
                    if (!empty($record['meta_json'])) {
                        $meta = json_decode($record['meta_json'], true) ?: [];
                    }
                    // Compose Indonesian date
                    $bulan = [1=>'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
                    $verified = new DateTime();
                    $this->data['verified_at'] = $verified->format('d') . ' ' . $bulan[(int)$verified->format('n')] . ' ' . $verified->format('Y') . ' ' . $verified->format('H:i') . ' WIB';

                    // Derive document name and fields
                    $docType = $record['doc_type'] ?? '';
                    $docId = $record['doc_id'] ?? '';
                    $mapel = $meta['mapel'] ?? '';
                    // Long document name: e.g., RPP Pendidikan Pancasila
                    $this->data['doc_name'] = strtoupper($docType) . (!empty($mapel) ? ' ' . $mapel : '');
                    $this->data['guru_nama'] = $meta['printed_by'] ?? ($meta['guru'] ?? '');
                    $this->data['kepala_nama'] = $meta['kepala'] ?? ($_SESSION['nama_kepala_madrasah'] ?? '');

                    // Tanggal pembuatan (prefer tanggal_rpp in meta, else issued_at)
                    $tanggalSrc = $meta['tanggal_rpp'] ?? ($record['issued_at'] ?? null);
                    if ($tanggalSrc) {
                        $dt = new DateTime($tanggalSrc);
                        $this->data['tanggal_dibuat'] = $dt->format('d') . ' ' . $bulan[(int)$dt->format('n')] . ' ' . $dt->format('Y');
                    }

                    // Log scan
                    $qrModel->logScan($token, true, 'Valid scan');
                } else {
                    $this->data['message'] = 'Token validasi tidak valid atau sudah kedaluwarsa.';
                    $this->data['verified_at'] = '';
                    if ($record) { $qrModel->logScan($token, false, 'Revoked/expired'); }
                }
            } catch (Exception $e) {
                $this->data['message'] = 'Terjadi kesalahan saat memverifikasi token.';
                $this->data['verified_at'] = '';
            }
        } else {
            $this->data['message'] = 'Token validasi tidak valid atau sudah kedaluwarsa.';
        }

        // Gunakan view standalone tanpa template header/footer
        $this->viewStandalone('validasi_rapor/index', $this->data);
    }
    
    /**
     * View standalone untuk halaman publik (tanpa header/footer yang butuh login)
     */
    private function viewStandalone($view, $data = [])
    {
        ?>
        <!DOCTYPE html>
        <html lang="id">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?= $data['judul'] ?? 'Validasi Rapor' ?> - Madrasah Sabilillah</title>
            <script src="https://cdn.tailwindcss.com"></script>
            <script src="https://unpkg.com/lucide@latest"></script>
        </head>
        <body class="antialiased">
            <?php require_once APPROOT . '/app/views/' . $view . '.php'; ?>
            <script>
                // Initialize Lucide icons
                lucide.createIcons();
            </script>
        </body>
        </html>
        <?php
    }
}
