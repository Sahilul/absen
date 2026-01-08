<?php
// File: app/controllers/SuratTugasController.php

use Dompdf\Dompdf;
use Dompdf\Options;

class SuratTugasController extends Controller
{
    private $data = [];

    public function __construct()
    {
        // Require Login & Role Check
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASEURL . '/auth/login');
            exit;
        }

        // Allow Admin & Kepala Madrasah (extend roles as needed)
        if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'kepala_madrasah') {
            header('Location: ' . BASEURL . '/dashboard');
            exit;
        }

        // Load Models
        $this->data['judul'] = 'Surat Tugas';

        // Flag untuk Sidebar khusus (Separate Panel)
        $this->data['use_surat_sidebar'] = true;

        // Load QR Helper
        require_once APPROOT . '/config/qrcode.php';

        // Load Dompdf Manual
        require_once APPROOT . '/app/core/dompdf/autoload.inc.php';
    }

    public function index()
    {
        $this->data['judul'] = 'Dashboard Surat Tugas';
        $this->data['stats'] = $this->model('SuratTugas_model')->getStats();

        $this->view('templates/header', $this->data);
        $this->view('surat_tugas/dashboard', $this->data);
        $this->view('templates/footer', $this->data);
    }

    // =================================================================
    // LEMBAGA
    // =================================================================

    public function lembaga()
    {
        $this->data['judul'] = 'Kelola Lembaga';
        $this->data['lembaga_list'] = $this->model('SuratTugas_model')->getAllLembaga();

        $this->view('templates/header', $this->data);
        $this->view('surat_tugas/lembaga/index', $this->data);
        $this->view('templates/footer', $this->data);
    }

    public function tambahLembaga()
    {
        $this->data['judul'] = 'Tambah Lembaga';
        $this->view('templates/header', $this->data);
        $this->view('surat_tugas/lembaga/form', $this->data);
        $this->view('templates/footer', $this->data);
    }

    public function editLembaga($id)
    {
        $this->data['judul'] = 'Edit Lembaga';
        $this->data['lembaga'] = $this->model('SuratTugas_model')->getLembagaById($id);

        if (!$this->data['lembaga']) {
            Flasher::setFlash('Lembaga tidak ditemukan', 'danger');
            header('Location: ' . BASEURL . '/suratTugas/lembaga');
            exit;
        }

        $this->view('templates/header', $this->data);
        $this->view('surat_tugas/lembaga/form', $this->data);
        $this->view('templates/footer', $this->data);
    }

    public function simpanLembaga()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/suratTugas/lembaga');
            exit;
        }

        $data = $_POST;

        // Handle Upload Kop
        if (isset($_FILES['kop_surat']) && $_FILES['kop_surat']['error'] === 0) {
            $uploadDir = 'public/uploads/kop_lembaga/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $fileName = 'kop_' . time() . '_' . rand(100, 999) . '.png';
            $ext = pathinfo($_FILES['kop_surat']['name'], PATHINFO_EXTENSION);
            if ($ext)
                $fileName = 'kop_' . time() . '_' . rand(100, 999) . '.' . $ext;

            move_uploaded_file($_FILES['kop_surat']['tmp_name'], $uploadDir . $fileName);
            $data['kop_surat'] = $fileName;
        }

        if (!empty($data['id_lembaga'])) {
            if ($this->model('SuratTugas_model')->updateLembaga($data) > 0) {
                Flasher::setFlash('Lembaga berhasil diperbarui', 'success');
            } else {
                Flasher::setFlash('Data lembaga disimpan', 'success');
            }
        } else {
            if ($this->model('SuratTugas_model')->simpanLembaga($data) > 0) {
                Flasher::setFlash('Lembaga berhasil ditambahkan', 'success');
            } else {
                Flasher::setFlash('Gagal menambah lembaga', 'danger');
            }
        }

        header('Location: ' . BASEURL . '/suratTugas/lembaga');
        exit;
    }

    public function hapusLembaga($id)
    {
        if ($this->model('SuratTugas_model')->hapusLembaga($id) > 0) {
            Flasher::setFlash('Lembaga berhasil dihapus', 'success');
        } else {
            Flasher::setFlash('Gagal menghapus lembaga', 'danger');
        }
        header('Location: ' . BASEURL . '/suratTugas/lembaga');
        exit;
    }

    // =================================================================
    // SURAT TUGAS
    // =================================================================

    public function surat()
    {
        $this->data['judul'] = 'Daftar Surat Tugas';

        $idLembaga = $_GET['lembaga'] ?? null;
        $this->data['filter_lembaga'] = $idLembaga;

        $this->data['lembaga_list'] = $this->model('SuratTugas_model')->getAllLembaga();
        $this->data['surat_list'] = $this->model('SuratTugas_model')->getAllSurat($idLembaga);

        $this->view('templates/header', $this->data);
        $this->view('surat_tugas/surat/index', $this->data);
        $this->view('templates/footer', $this->data);
    }

    public function inputSurat($id = null)
    {
        $this->data['judul'] = $id ? 'Edit Surat Tugas' : 'Buat Surat Tugas';
        $this->data['lembaga_list'] = $this->model('SuratTugas_model')->getAllLembaga();

        if ($id) {
            $this->data['surat'] = $this->model('SuratTugas_model')->getSuratById($id);
            $this->data['petugas_list'] = $this->model('SuratTugas_model')->getPetugasBySurat($id);
            if (!$this->data['surat']) {
                Flasher::setFlash('Surat tidak ditemukan', 'danger');
                header('Location: ' . BASEURL . '/suratTugas/surat');
                exit;
            }
        }

        $this->view('templates/header', $this->data);
        $this->view('surat_tugas/surat/form', $this->data);
        $this->view('templates/footer', $this->data);
    }

    public function simpanSurat()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/suratTugas/surat');
            exit;
        }

        $data = [
            'id_surat' => $_POST['id_surat'] ?? null,
            'id_lembaga' => $_POST['id_lembaga'],
            'nomor_surat' => $_POST['nomor_surat'],
            'tanggal_surat' => $_POST['tanggal_surat'],
            'perihal' => $_POST['perihal'],
            'tempat_tugas' => $_POST['tempat_tugas'],
            'tanggal_mulai' => $_POST['tanggal_mulai'],
            'tanggal_selesai' => $_POST['tanggal_selesai'],
            'status' => 'terbit'
        ];

        // Process Petugas List from Dynamic Rows - Now includes jenis_identitas
        $petugasList = [];
        if (isset($_POST['nama_petugas']) && is_array($_POST['nama_petugas'])) {
            foreach ($_POST['nama_petugas'] as $key => $nama) {
                if (empty(trim($nama)))
                    continue;
                $petugasList[] = [
                    'nama_petugas' => $nama,
                    'jenis_identitas' => $_POST['jenis_identitas'][$key] ?? 'NIK',
                    'identitas_petugas' => $_POST['identitas_petugas'][$key] ?? '',
                    'jabatan_petugas' => $_POST['jabatan_petugas'][$key] ?? ''
                ];
            }
        }

        if ($this->model('SuratTugas_model')->simpanSurat($data, $petugasList)) {
            Flasher::setFlash('Surat tugas berhasil disimpan', 'success');
        } else {
            Flasher::setFlash('Gagal menyimpan surat tugas', 'danger');
        }

        header('Location: ' . BASEURL . '/suratTugas/surat');
        exit;
    }

    public function hapusSurat($id)
    {
        if ($this->model('SuratTugas_model')->hapusSurat($id) > 0) {
            Flasher::setFlash('Surat tugas berhasil dihapus', 'success');
        } else {
            Flasher::setFlash('Gagal menghapus surat', 'danger');
        }
        header('Location: ' . BASEURL . '/suratTugas/surat');
        exit;
    }

    // =================================================================
    // PDF GENERATION
    // =================================================================

    public function cetak($id)
    {
        $surat = $this->model('SuratTugas_model')->getSuratById($id);
        $petugas = $this->model('SuratTugas_model')->getPetugasBySurat($id);

        if (!$surat) {
            echo "Surat tidak ditemukan";
            exit;
        }

        // Generate QR Code
        $qrData = generatePDFQRCode('surat_tugas', $id, [
            'nomor' => $surat['nomor_surat'],
            'tanggal' => $surat['tanggal_surat']
        ]);

        $html = $this->buildSuratPDF($surat, $petugas, $qrData);

        // Render PDF
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Times-Roman');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'Surat_Tugas_' . str_replace(['/', '\\'], '_', $surat['nomor_surat']) . '.pdf';
        $dompdf->stream($filename, ["Attachment" => true]);
    }

    private function buildSuratPDF($surat, $petugas, $qrData)
    {
        // Path handling for image
        $kopPath = '';
        if (!empty($surat['kop_surat'])) {
            $localPath = 'public/uploads/kop_lembaga/' . $surat['kop_surat'];
            if (file_exists($localPath)) {
                $type = pathinfo($localPath, PATHINFO_EXTENSION);
                $data = file_get_contents($localPath);
                $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                $kopPath = $base64;
            }
        }

        // --- Helper Tanggal Indo ---
        $hariIndo = ['Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'];
        $bulanIndo = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

        date_default_timezone_set('Asia/Jakarta');
        $tglSurat = strtotime($surat['tanggal_surat']);
        $tanggalSuratStr = date('j', $tglSurat) . ' ' . $bulanIndo[date('n', $tglSurat)] . ' ' . date('Y', $tglSurat);

        $tglMulai = strtotime($surat['tanggal_mulai']);
        $hariMulaiStr = $hariIndo[date('l', $tglMulai)];
        $tglMulaiStr = date('j', $tglMulai) . ' ' . $bulanIndo[date('n', $tglMulai)] . ' ' . date('Y', $tglMulai);

        $waktuPelaksanaan = $hariMulaiStr . ', ' . $tglMulaiStr;
        if ($surat['tanggal_selesai'] && $surat['tanggal_selesai'] != $surat['tanggal_mulai']) {
            $tglSelesai = strtotime($surat['tanggal_selesai']);
            $waktuPelaksanaan .= ' s.d ' . date('j', $tglSelesai) . ' ' . $bulanIndo[date('n', $tglSelesai)] . ' ' . date('Y', $tglSelesai);
        }

        // QR Code
        $qrImg = '';
        if ($qrData) {
            $qrImg = '<img src="' . $qrData . '" style="width: 80px; height: 80px;">';
        }

        // HTML Template - Updated: No border under kop, footer at absolute bottom
        $html = '<!DOCTYPE html><html><head><style>
            @page { margin: 10mm 15mm; }
            body { 
                font-family: "Times New Roman", Times, serif; 
                font-size: 12pt; 
                line-height: 1.3; 
                margin: 0; 
                padding: 0; 
                position: relative;
            }
            .kop-container { 
                width: 100%; 
                padding-bottom: 10px; 
                margin-bottom: 15px; 
                text-align: center;
                display: block;
            }
            .kop-img { 
                max-width: 100%; 
                height: auto; 
                max-height: 120px;
            }
            .title { text-align: center; font-weight: bold; text-decoration: underline; margin-bottom: 2px; font-size: 14pt; margin-top: 15px; }
            .nomor { text-align: center; margin-bottom: 20px; margin-top: 5px; }
            .content { text-align: justify; margin-bottom: 10px; }
            .table-data { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
            .table-data td { padding: 3px; vertical-align: top; }
            .td-label { width: 120px; } 
            
            .table-petugas { width: 100%; border-collapse: collapse; margin: 10px 0 20px 0; }
            .table-petugas th, .table-petugas td { border: 1px solid black; padding: 6px; text-align: left; vertical-align: middle; }
            .table-petugas th { background-color: #f2f2f2; font-weight: bold; }
            
            .signature-area { margin-top: 30px; page-break-inside: avoid; }
            .ttd-box { float: right; width: 280px; text-align: center; } 
            .qr-box { margin: 10px auto; }
            
            .footer-note { 
                position: fixed;
                bottom: 5mm;
                left: 15mm;
                right: 15mm;
                font-style: italic; 
                font-size: 8pt; 
                color: #666; 
                border-top: 1px solid #ddd; 
                padding-top: 5px; 
                text-align: center; 
            }
        </style></head><body>';

        // KOP - No border
        if ($kopPath) {
            $html .= '<div class="kop-container"><img src="' . $kopPath . '" class="kop-img"></div>';
        } else {
            $html .= '<div class="kop-container">';
            $html .= '<div style="font-size: 14pt; font-weight: bold;">YAYASAN PONDOK PESANTREN SABILILLAH</div>';
            $html .= '<div style="font-size: 16pt; font-weight: bold; color: green;">MADRASAH TSANAWIYAH (MTs) SABILILLAH</div>';
            $html .= '<div style="font-size: 10pt;">' . $surat['alamat'] . ' | ' . $surat['kota'] . '</div>';
            $html .= '</div>';
        }

        $html .= '<div class="title">SURAT TUGAS</div>';
        $html .= '<div class="nomor">Nomor: ' . $surat['nomor_surat'] . '</div>';

        $html .= '<div class="content">';
        $html .= '<p>Yang bertanda tangan di bawah ini:</p>';
        $html .= '<table class="table-data" style="margin-left: 20px;">';
        $html .= '<tr><td class="td-label">Nama</td><td>: ' . $surat['nama_kepala_lembaga'] . '</td></tr>';
        $html .= '<tr><td>Jabatan</td><td>: ' . $surat['jabatan_kepala'] . '</td></tr>';
        $html .= '<tr><td>Alamat</td><td>: ' . $surat['alamat'] . ', ' . $surat['kota'] . '</td></tr>';
        $html .= '</table>';

        $html .= '<p>Memberikan tugas kepada:</p>';

        // Tabel Petugas - Now displays jenis_identitas
        $html .= '<table class="table-petugas">';
        $html .= '<thead><tr>
            <th style="width: 30px; text-align: center;">No</th>
            <th>Nama</th>
            <th>Identitas</th>
            <th>Jabatan</th>
        </tr></thead><tbody>';

        $no = 1;
        foreach ($petugas as $p) {
            $jenisId = $p['jenis_identitas'] ?? 'NIK';
            $nomorId = $p['identitas_petugas'] ?? '-';
            $identitasStr = $nomorId ? $jenisId . ': ' . $nomorId : '-';

            $html .= '<tr>';
            $html .= '<td style="text-align: center;">' . $no++ . '</td>';
            $html .= '<td>' . $p['nama_petugas'] . '</td>';
            $html .= '<td>' . $identitasStr . '</td>';
            $html .= '<td>' . ($p['jabatan_petugas'] ?: '-') . '</td>';
            $html .= '</tr>';
        }
        $html .= '</tbody></table>';

        // Isi Tugas
        $html .= '<p style="text-align: justify; line-height: 1.5;"><strong>Untuk:</strong> ' . $surat['perihal'] . ' di ' . $surat['tempat_tugas'] . ' pada ' . $waktuPelaksanaan . '</p>';

        $html .= '<p style="margin-top: 15px;">Demikian surat tugas ini dibuat agar dapat digunakan sebagaimana mestinya.</p>';
        $html .= '</div>';

        // TTD
        $html .= '<div class="signature-area">';
        $html .= '<div class="ttd-box">';
        $html .= '<div>' . $surat['kota'] . ', ' . $tanggalSuratStr . '</div>';
        $html .= '<div style="font-weight: bold; margin-bottom: 5px;">' . $surat['jabatan_kepala'] . ',</div>';
        $html .= '<div class="qr-box">' . $qrImg . '</div>';

        $html .= '<div style="font-weight: bold; text-decoration: underline;">' . $surat['nama_kepala_lembaga'] . '</div>';
        if ($surat['nip_kepala']) {
            $html .= '<div>NIP. ' . $surat['nip_kepala'] . '</div>';
        }
        $html .= '</div><div style="clear: both;"></div>';
        $html .= '</div>';

        // Footer Note - Fixed at bottom
        $html .= '<div class="footer-note">';
        $html .= 'Dokumen ini telah ditandatangani secara elektronik menggunakan kode QR dan sah tanpa memerlukan tanda tangan basah.<br>Untuk memvalidasi keaslian dokumen ini, silakan pindai (scan) kode QR di atas.';
        $html .= '</div>';

        $html .= '</body></html>';

        return $html;
    }
}
