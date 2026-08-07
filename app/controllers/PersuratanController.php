<?php
// File: app/controllers/PersuratanController.php
// v1.22.0 - Panel Persuratan: gabungan Panel Surat Tugas & Panel Surat Penerimaan.
// Dashboard terpadu ada di sini; CRUD masing-masing jenis surat tetap ditangani
// SuratTugasController dan SuratPenerimaanController (URL lama tetap valid).

class PersuratanController extends Controller
{
    private $data = [];

    public function __construct()
    {
        // Require Login
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASEURL . '/auth/login');
            exit;
        }

        // Allow Admin & Kepala Madrasah
        if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'kepala_madrasah') {
            header('Location: ' . BASEURL . '/dashboard');
            exit;
        }

        $this->data['judul'] = 'Persuratan';

        // Flag untuk Sidebar Panel Persuratan (gabungan)
        $this->data['use_persuratan_sidebar'] = true;
    }

    public function index()
    {
        $this->data['judul'] = 'Dashboard Persuratan';

        $statsTugas = $this->model('SuratTugas_model')->getStats();
        $statsTerima = $this->model('SuratPenerimaan_model')->getStats();

        $this->data['stats'] = [
            'total_lembaga' => $statsTugas['total_lembaga'] ?? 0,
            'total_surat_tugas' => $statsTugas['total_surat'] ?? 0,
            'total_surat_penerimaan' => $statsTerima['total_surat'] ?? 0,
        ];

        $this->view('templates/header', $this->data);
        $this->view('persuratan/dashboard', $this->data);
        $this->view('templates/footer', $this->data);
    }
}
