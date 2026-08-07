<?php
// File: app/controllers/SuratPenerimaanController.php
// Panel Surat Penerimaan Siswa (siswa pindahan) - multi lembaga, reuse tabel lembaga surat tugas.

use Dompdf\Dompdf;
use Dompdf\Options;

class SuratPenerimaanController extends Controller
{
    private $data = [];

    public function __construct()
    {
        // Require Login & Role Check
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASEURL . '/auth/login');
            exit;
        }

        // Allow Admin & Kepala Madrasah
        if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'kepala_madrasah') {
            header('Location: ' . BASEURL . '/dashboard');
            exit;
        }

        $this->data['judul'] = 'Surat Penerimaan';

        // Flag untuk Sidebar khusus (Separate Panel)
        $this->data['use_penerimaan_sidebar'] = true;

        // Load QR Helper
        require_once APPROOT . '/config/qrcode.php';

        // Load Dompdf Manual
        require_once APPROOT . '/app/core/dompdf/autoload.inc.php';
    }

    public function index()
    {
        $this->data['judul'] = 'Dashboard Surat Penerimaan';
        $this->data['stats'] = $this->model('SuratPenerimaan_model')->getStats();

        $this->view('templates/header', $this->data);
        $this->view('surat_penerimaan/dashboard', $this->data);
        $this->view('templates/footer', $this->data);
    }

    // =================================================================
    // SURAT PENERIMAAN
    // =================================================================

    public function surat()
    {
        $this->data['judul'] = 'Daftar Surat Penerimaan';

        $idLembaga = $_GET['lembaga'] ?? null;
        $this->data['filter_lembaga'] = $idLembaga;

        $this->data['lembaga_list'] = $this->model('SuratPenerimaan_model')->getAllLembaga();
        $this->data['surat_list'] = $this->model('SuratPenerimaan_model')->getAllSurat($idLembaga);

        $this->view('templates/header', $this->data);
        $this->view('surat_penerimaan/surat/index', $this->data);
        $this->view('templates/footer', $this->data);
    }

    public function inputSurat($id = null)
    {
        $this->data['judul'] = $id ? 'Edit Surat Penerimaan' : 'Buat Surat Penerimaan';
        $this->data['lembaga_list'] = $this->model('SuratPenerimaan_model')->getAllLembaga();

        if ($id) {
            $this->data['surat'] = $this->model('SuratPenerimaan_model')->getSuratById($id);
            if (!$this->data['surat']) {
                Flasher::setFlash('Surat tidak ditemukan', 'danger');
                header('Location: ' . BASEURL . '/suratPenerimaan/surat');
                exit;
            }
        }

        $this->view('templates/header', $this->data);
        $this->view('surat_penerimaan/surat/form', $this->data);
        $this->view('templates/footer', $this->data);
    }

    public function simpanSurat()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/suratPenerimaan/surat');
            exit;
        }

        // Validasi minimal
        if (empty($_POST['id_lembaga']) || empty($_POST['nomor_surat']) || empty($_POST['nama_siswa'])) {
            Flasher::setFlash('Lembaga, nomor surat, dan nama siswa wajib diisi', 'danger');
            header('Location: ' . BASEURL . '/suratPenerimaan/inputSurat' . (!empty($_POST['id_surat']) ? '/' . $_POST['id_surat'] : ''));
            exit;
        }

        $data = [
            'id_surat' => $_POST['id_surat'] ?? null,
            'id_lembaga' => $_POST['id_lembaga'],
            'nomor_surat' => trim($_POST['nomor_surat']),
            'tanggal_surat' => $_POST['tanggal_surat'] ?: date('Y-m-d'),
            'kota_surat' => trim($_POST['kota_surat'] ?? ''),
            'nama_siswa' => trim($_POST['nama_siswa']),
            'tempat_lahir' => trim($_POST['tempat_lahir'] ?? ''),
            'tanggal_lahir' => $_POST['tanggal_lahir'] ?? '',
            'jenis_kelamin' => $_POST['jenis_kelamin'] ?? '',
            'nisn' => trim($_POST['nisn'] ?? ''),
            'asal_sekolah' => trim($_POST['asal_sekolah'] ?? ''),
            'nama_orang_tua' => trim($_POST['nama_orang_tua'] ?? ''),
            'alamat_siswa' => trim($_POST['alamat_siswa'] ?? ''),
            'diterima_di_kelas' => trim($_POST['diterima_di_kelas'] ?? ''),
            'tahun_pelajaran' => trim($_POST['tahun_pelajaran'] ?? ''),
            'keterangan' => trim($_POST['keterangan'] ?? ''),
            'status' => 'terbit',
            'created_by' => $_SESSION['user_id'] ?? null
        ];

        if ($this->model('SuratPenerimaan_model')->simpanSurat($data)) {
            Flasher::setFlash('Surat penerimaan siswa berhasil disimpan', 'success');
        } else {
            Flasher::setFlash('Gagal menyimpan surat penerimaan siswa', 'danger');
        }

        header('Location: ' . BASEURL . '/suratPenerimaan/surat');
        exit;
    }

    public function hapusSurat($id)
    {
        if ($this->model('SuratPenerimaan_model')->hapusSurat($id) > 0) {
            Flasher::setFlash('Surat penerimaan siswa berhasil dihapus', 'success');
        } else {
            Flasher::setFlash('Gagal menghapus surat', 'danger');
        }
        header('Location: ' . BASEURL . '/suratPenerimaan/surat');
        exit;
    }

    // =================================================================
    // PDF GENERATION
    // =================================================================

    public function cetak($id)
    {
        $surat = $this->model('SuratPenerimaan_model')->getSuratById($id);

        if (!$surat) {
            echo "Surat tidak ditemukan";
            exit;
        }

        // Generate QR Code
        $qrData = generatePDFQRCode('surat_penerimaan', $id, [
            'nomor' => $surat['nomor_surat'],
            'tanggal' => $surat['tanggal_surat'],
            'nama_siswa' => $surat['nama_siswa']
        ]);

        $html = $this->buildSuratPDF($surat, $qrData);

        // Render PDF
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Times-Roman');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'Surat_Penerimaan_Siswa_' . str_replace(['/', '\\'], '_', $surat['nomor_surat']) . '.pdf';
        $dompdf->stream($filename, ["Attachment" => true]);
    }

    private function buildSuratPDF($surat, $qrData)
    {
        $e = function ($v) {
            return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');
        };

        // Path handling for kop image
        $kopPath = '';
        if (!empty($surat['kop_surat'])) {
            $localPath = 'public/uploads/kop_lembaga/' . $surat['kop_surat'];
            if (file_exists($localPath)) {
                $type = strtolower(pathinfo($localPath, PATHINFO_EXTENSION));
                if ($type === 'jpg') $type = 'jpeg';
                $base64 = 'data:image/' . $type . ';base64,' . base64_encode(file_get_contents($localPath));
                $kopPath = $base64;
            }
        }

        // --- Helper Tanggal Indo ---
        $bulanIndo = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

        date_default_timezone_set('Asia/Jakarta');
        $fmtTanggal = function ($dateStr) use ($bulanIndo) {
            $ts = strtotime($dateStr);
            if (!$ts) return '';
            return date('j', $ts) . ' ' . $bulanIndo[(int)date('n', $ts)] . ' ' . date('Y', $ts);
        };

        $tanggalSuratStr = $fmtTanggal($surat['tanggal_surat']);
        $ttlStr = trim(($surat['tempat_lahir'] ?? '') . ', ' . $fmtTanggal($surat['tanggal_lahir'] ?? ''), ', ');
        $jenisKelaminStr = ($surat['jenis_kelamin'] === 'L') ? 'Laki-laki' : (($surat['jenis_kelamin'] === 'P') ? 'Perempuan' : '-');

        // Kota surat: pakai isian manual, fallback ke kota lembaga
        $kotaSurat = !empty($surat['kota_surat']) ? $surat['kota_surat'] : ($surat['kota'] ?? '');

        // QR Code
        $qrImg = '';
        if ($qrData) {
            $qrImg = '<img src="' . $qrData . '" style="width: 80px; height: 80px;">';
        }

        $html = '<!DOCTYPE html><html><head><style>
            @page { margin: 15mm 20mm; }
            body {
                font-family: "Times New Roman", Times, serif;
                font-size: 12pt;
                line-height: 1.4;
                margin: 0;
                padding: 0;
                position: relative;
            }
            .kop-container { width: 100%; padding-bottom: 8px; margin-bottom: 12px; text-align: center; border-bottom: 3px double #000; }
            .kop-img { max-width: 100%; height: auto; max-height: 120px; }
            .title { text-align: center; font-weight: bold; text-decoration: underline; margin-top: 18px; margin-bottom: 2px; font-size: 14pt; letter-spacing: 0.5px; }
            .nomor { text-align: center; margin-bottom: 22px; margin-top: 2px; }
            .content p { margin: 0 0 8px 0; text-align: justify; }
            .table-data { width: 92%; border-collapse: collapse; margin: 0 0 12px 30px; }
            .table-data td { padding: 2px 4px; vertical-align: top; }
            .td-label { width: 170px; }
            .td-sep { width: 12px; }
            .signature-area { margin-top: 28px; page-break-inside: avoid; }
            .ttd-box { float: right; width: 300px; text-align: center; }
            .qr-box { margin: 8px auto; }
            .footer-note {
                position: fixed;
                bottom: 5mm;
                left: 20mm;
                right: 20mm;
                font-style: italic;
                font-size: 8pt;
                color: #666;
                border-top: 1px solid #ddd;
                padding-top: 5px;
                text-align: center;
            }
        </style></head><body>';

        // KOP
        if ($kopPath) {
            $html .= '<div class="kop-container" style="border-bottom: none;"><img src="' . $kopPath . '" class="kop-img"></div>';
        } else {
            $html .= '<div class="kop-container">';
            $html .= '<div style="font-size: 14pt; font-weight: bold;">YAYASAN PONDOK PESANTREN SABILILLAH</div>';
            $html .= '<div style="font-size: 16pt; font-weight: bold; color: green;">' . $e($surat['nama_lembaga']) . '</div>';
            $html .= '<div style="font-size: 10pt;">' . $e($surat['alamat']) . ' ' . $e($surat['kota']) . '</div>';
            if (!empty($surat['telepon']) || !empty($surat['email'])) {
                $html .= '<div style="font-size: 10pt;">' . $e($surat['telepon']) . ' | ' . $e($surat['email']) . '</div>';
            }
            $html .= '</div>';
        }

        // Judul Surat (sesuai contoh: SURAT PENERIMAAN SISWA)
        $html .= '<div class="title">SURAT PENERIMAAN SISWA</div>';
        $html .= '<div class="nomor">No : ' . $e($surat['nomor_surat']) . '</div>';

        $html .= '<div class="content">';
        $html .= '<p>Yang bertanda tangan dibawah ini :</p>';
        $html .= '<table class="table-data">';
        $html .= '<tr><td class="td-label">Nama</td><td class="td-sep">:</td><td>' . $e($surat['nama_kepala_lembaga']) . '</td></tr>';
        $html .= '<tr><td class="td-label">Jabatan</td><td class="td-sep">:</td><td>' . $e($surat['jabatan_kepala']) . '</td></tr>';
        $html .= '<tr><td class="td-label">Unit Kerja</td><td class="td-sep">:</td><td>' . $e($surat['nama_lembaga']) . '</td></tr>';
        $html .= '<tr><td class="td-label">Alamat</td><td class="td-sep">:</td><td>' . $e($surat['alamat']) . ' ' . $e($surat['kota']) . '</td></tr>';
        $html .= '</table>';

        $html .= '<p>Menerangkan dengan sebenarnya bahwa :</p>';
        $html .= '<table class="table-data">';
        $html .= '<tr><td class="td-label">Nama</td><td class="td-sep">:</td><td>' . $e($surat['nama_siswa']) . '</td></tr>';
        $html .= '<tr><td class="td-label">Tempat / Tanggal Lahir</td><td class="td-sep">:</td><td>' . $e($ttlStr) . '</td></tr>';
        $html .= '<tr><td class="td-label">Jenis Kelamin</td><td class="td-sep">:</td><td>' . $e($jenisKelaminStr) . '</td></tr>';
        $html .= '<tr><td class="td-label">NISN</td><td class="td-sep">:</td><td>' . $e($surat['nisn']) . '</td></tr>';
        $html .= '<tr><td class="td-label">Asal Sekolah</td><td class="td-sep">:</td><td>' . $e($surat['asal_sekolah']) . '</td></tr>';
        $html .= '<tr><td class="td-label">Nama Orang Tua</td><td class="td-sep">:</td><td>' . $e($surat['nama_orang_tua']) . '</td></tr>';
        $html .= '</table>';

        // Paragraf penerimaan
        $kalimatTerima = 'Sesuai dengan permohonan orang tua/ wali murid dari siswa a.n. ' . $e($surat['nama_siswa'])
            . ' telah diterima menjadi siswa ' . $e($surat['nama_lembaga']) . ' ' . $e($surat['alamat']) . ' ' . $e($surat['kota']) . '.';
        if (!empty($surat['diterima_di_kelas']) || !empty($surat['tahun_pelajaran'])) {
            $detail = [];
            if (!empty($surat['diterima_di_kelas'])) $detail[] = 'kelas ' . $e($surat['diterima_di_kelas']);
            if (!empty($surat['tahun_pelajaran'])) $detail[] = 'tahun pelajaran ' . $e($surat['tahun_pelajaran']);
            $kalimatTerima .= ' Siswa yang bersangkutan ditempatkan di ' . implode(' ', $detail) . '.';
        }
        if (!empty($surat['keterangan'])) {
            $kalimatTerima .= ' ' . $e($surat['keterangan']);
        }
        $html .= '<p>' . $kalimatTerima . '</p>';

        $html .= '<p>Demikian surat keterangan ini dibuat agar dipergunakan sebagaimana mestinya.</p>';
        $html .= '</div>';

        // TTD (kanan)
        $html .= '<div class="signature-area">';
        $html .= '<div class="ttd-box">';
        $html .= '<div>' . $e($kotaSurat) . ', ' . $e($tanggalSuratStr) . '</div>';
        $html .= '<div style="margin-bottom: 2px;">' . $e($surat['jabatan_kepala']) . '</div>';
        $html .= '<div class="qr-box">' . $qrImg . '</div>';
        $html .= '<div style="font-weight: bold; text-decoration: underline;">' . $e($surat['nama_kepala_lembaga']) . '</div>';
        if (!empty($surat['nip_kepala'])) {
            $html .= '<div>NIP. ' . $e($surat['nip_kepala']) . '</div>';
        }
        $html .= '</div><div style="clear: both;"></div>';
        $html .= '</div>';

        // Footer Note
        $html .= '<div class="footer-note">';
        $html .= 'Dokumen ini telah ditandatangani secara elektronik menggunakan kode QR dan sah tanpa memerlukan tanda tangan basah.<br>Untuk memvalidasi keaslian dokumen ini, silakan pindai (scan) kode QR di atas.';
        $html .= '</div>';

        $html .= '</body></html>';

        return $html;
    }
}
