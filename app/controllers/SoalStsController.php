<?php
// File: app/controllers/SoalStsController.php
// v1.31.0 - Controller untuk fitur Buat Soal STS (Sumatif Tengah Semester)

use Dompdf\Dompdf;
use Dompdf\Options;

class SoalStsController extends Controller
{
    private $data = [];
    private $soalModel;

    public function __construct()
    {
        // Guard: admin, guru, wali_kelas bisa akses
        $allowedRoles = ['admin', 'guru', 'wali_kelas'];
        if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', $allowedRoles)) {
            header('Location: ' . BASEURL . '/auth/login');
            exit;
        }

        $this->data['daftar_semester'] = $this->model('TahunPelajaran_model')->getAllSemester();
        $this->soalModel = $this->model('SoalSts_model');
    }

    /**
     * Helper: Cek apakah user adalah admin
     */
    private function isAdmin()
    {
        return ($_SESSION['role'] ?? '') === 'admin';
    }

    /**
     * Helper: Guard admin-only — redirect guru/wali_kelas ke dashboard
     */
    private function adminOnlyGuard()
    {
        if (!$this->isAdmin()) {
            header('Location: ' . BASEURL . '/guru');
            exit;
        }
    }

    /**
     * Helper: Load sidebar sesuai role
     */
    private function loadSidebar()
    {
        $role = $_SESSION['role'] ?? '';
        if ($role === 'admin') {
            $this->view('templates/sidebar_admin', $this->data);
        } elseif ($role === 'wali_kelas') {
            $this->view('templates/sidebar_walikelas', $this->data);
        } else {
            $this->view('templates/sidebar_guru', $this->data);
        }
    }

    /**
     * Helper: Cek apakah guru punya penugasan untuk mapel+kelas tertentu
     * Return true jika admin (selalu boleh) atau guru punya penugasan
     */
    private function cekAksesMapelKelas($id_mapel, $id_kelas)
    {
        if ($this->isAdmin()) {
            return true;
        }
        $id_guru = $_SESSION['id_ref'] ?? 0;
        $id_semester = $_SESSION['id_semester_aktif'] ?? 0;
        return $this->model('Penugasan_model')->cekDuplikasiPenugasan($id_guru, $id_mapel, $id_kelas, $id_semester);
    }

    /**
     * Helper: Redirect guru ke dashboard jika tidak punya akses
     */
    private function guruAccessDenied()
    {
        Flasher::setFlash('Anda tidak memiliki akses ke soal ini!', 'danger');
        header('Location: ' . BASEURL . '/guru');
        exit;
    }

    /**
     * Dashboard — Grid kelas aktif dengan status soal
     */
    public function index()
    {
        $this->adminOnlyGuard();
        $this->data['judul'] = 'Soal STS';
        $id_tp_aktif = $_SESSION['id_tp_aktif'] ?? 0;
        $id_semester_aktif = $_SESSION['id_semester_aktif'] ?? 0;

        // Ambil semua kelas aktif
        $kelasList = $this->model('Kelas_model')->getKelasByTP($id_tp_aktif);

        // Tambahkan status kelengkapan soal per kelas
        foreach ($kelasList as &$kelas) {
            $status = $this->soalModel->getStatusKelengkapan($kelas['id_kelas'], $id_semester_aktif);
            $kelas['status_soal'] = $status;
            $kelas['total_mapel_soal'] = count($status);

            // Hitung mapel yang sudah lengkap
            $lengkap = 0;
            $sebagian = 0;
            foreach ($status as $s) {
                if ($s['total_target'] > 0 && $s['total_soal'] >= $s['total_target']) {
                    $lengkap++;
                } elseif ($s['total_soal'] > 0) {
                    $sebagian++;
                }
            }
            $kelas['mapel_lengkap'] = $lengkap;
            $kelas['mapel_sebagian'] = $sebagian;

            // Hitung total mapel yang ada penugasan
            $mapelList = $this->model('Penugasan_model')->getMapelByKelas($kelas['id_kelas'], $id_semester_aktif);
            $kelas['total_mapel'] = count($mapelList);
        }
        unset($kelas);

        $this->data['kelas_list'] = $kelasList;

        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/soal_sts/index', $this->data);
        $this->view('templates/footer', $this->data);
    }

    /**
     * Pengaturan model soal per mapel untuk kelas tertentu
     */
    public function pengaturan($id_kelas)
    {
        $this->adminOnlyGuard();
        $this->data['judul'] = 'Soal STS - Pengaturan';
        $id_semester_aktif = $_SESSION['id_semester_aktif'] ?? 0;

        // Info kelas
        $kelas = $this->model('Kelas_model')->getKelasById($id_kelas);
        if (!$kelas) {
            Flasher::setFlash('Kelas tidak ditemukan!', 'danger');
            header('Location: ' . BASEURL . '/soalSts');
            exit;
        }

        // Ambil mapel yang ada penugasan di kelas ini
        $mapelList = $this->model('Penugasan_model')->getMapelByKelas($id_kelas, $id_semester_aktif);

        // Ambil pengaturan yang sudah ada
        $pengaturanList = $this->soalModel->getPengaturanByKelas($id_kelas, $id_semester_aktif);
        $pengaturanMap = [];
        foreach ($pengaturanList as $p) {
            $pengaturanMap[$p['id_mapel']] = $p;
        }

        // Gabungkan mapel dengan pengaturan
        foreach ($mapelList as &$mapel) {
            $mapel['pengaturan'] = $pengaturanMap[$mapel['id_mapel']] ?? null;
        }
        unset($mapel);

        // Status kelengkapan soal per mapel (untuk progress di view)
        $kelas['status_soal'] = $this->soalModel->getStatusKelengkapan($id_kelas, $id_semester_aktif);

        $this->data['kelas'] = $kelas;
        $this->data['mapel_list'] = $mapelList;
        $this->data['id_kelas'] = $id_kelas;

        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/soal_sts/pengaturan', $this->data);
        $this->view('templates/footer', $this->data);
    }

    /**
     * Simpan pengaturan model soal (batch)
     */
    public function simpanPengaturan()
    {
        $this->adminOnlyGuard();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/soalSts');
            exit;
        }

        $id_kelas = $_POST['id_kelas'] ?? 0;
        $id_semester = $_POST['id_semester'] ?? $_SESSION['id_semester_aktif'];
        $mapelIds = $_POST['id_mapel'] ?? [];

        foreach ($mapelIds as $idx => $id_mapel) {
            $data = [
                'id_mapel' => $id_mapel,
                'id_kelas' => $id_kelas,
                'id_semester' => $id_semester,
                'jumlah_pilihan_ganda' => $_POST['jumlah_pg'][$idx] ?? 0,
                'jumlah_essay' => $_POST['jumlah_essay'][$idx] ?? 0,
                'jumlah_isian_singkat' => $_POST['jumlah_isian'][$idx] ?? 0,
                'opsi_pg' => $_POST['opsi_pg'][$idx] ?? 4,
                'waktu_pengerjaan' => $_POST['waktu'][$idx] ?? 60,
                'petunjuk_umum' => $_POST['petunjuk'][$idx] ?? '',
            ];
            $this->soalModel->savePengaturan($data);
        }

        Flasher::setFlash('Pengaturan soal berhasil disimpan!', 'success');
        header('Location: ' . BASEURL . '/soalSts/pengaturan/' . $id_kelas);
        exit;
    }

    /**
     * Halaman Bulk Pengaturan — atur/copy pengaturan ke banyak kelas sekaligus
     */
    public function bulkPengaturan()
    {
        $this->adminOnlyGuard();
        $this->data['judul'] = 'Soal STS - Bulk Pengaturan';
        $id_tp_aktif = $_SESSION['id_tp_aktif'] ?? 0;
        $id_semester_aktif = $_SESSION['id_semester_aktif'] ?? 0;

        // Ambil semua kelas
        $kelasList = $this->model('Kelas_model')->getKelasByTP($id_tp_aktif);

        // Tandai kelas yang sudah punya pengaturan
        foreach ($kelasList as &$kelas) {
            $pengaturan = $this->soalModel->getPengaturanByKelas($kelas['id_kelas'], $id_semester_aktif);
            $kelas['has_pengaturan'] = !empty($pengaturan);
            $kelas['jumlah_pengaturan'] = count($pengaturan);
        }
        unset($kelas);

        $this->data['kelas_list'] = $kelasList;

        // Kelompokkan kelas berdasarkan jenjang
        $kelasGrouped = [];
        foreach ($kelasList as $k) {
            $kelasGrouped[$k['jenjang']][] = $k;
        }
        // Urutkan berdasarkan nilai numerik romawi (VII=7, VIII=8, IX=9, dst)
        $romanValues = ['I'=>1,'II'=>2,'III'=>3,'IV'=>4,'V'=>5,'VI'=>6,'VII'=>7,'VIII'=>8,'IX'=>9,'X'=>10,'XI'=>11,'XII'=>12];
        uksort($kelasGrouped, function($a, $b) use ($romanValues) {
            $va = $romanValues[strtoupper($a)] ?? 99;
            $vb = $romanValues[strtoupper($b)] ?? 99;
            return $va - $vb;
        });
        $this->data['kelas_grouped'] = $kelasGrouped;

        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/soal_sts/bulk_pengaturan', $this->data);
        $this->view('templates/footer', $this->data);
    }

    /**
     * Proses Bulk Set — atur pengaturan baru untuk beberapa kelas sekaligus
     */
    public function prosessBulkSet()
    {
        $this->adminOnlyGuard();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/soalSts/bulkPengaturan');
            exit;
        }

        $kelasIds = $_POST['kelas_ids'] ?? [];
        $id_semester = $_SESSION['id_semester_aktif'] ?? 0;

        if (empty($kelasIds)) {
            Flasher::setFlash('Pilih minimal 1 kelas!', 'danger');
            header('Location: ' . BASEURL . '/soalSts/bulkPengaturan');
            exit;
        }

        $config = [
            'jumlah_pilihan_ganda' => (int)($_POST['jumlah_pg'] ?? 20),
            'jumlah_essay' => (int)($_POST['jumlah_essay'] ?? 5),
            'jumlah_isian_singkat' => (int)($_POST['jumlah_isian'] ?? 0),
            'opsi_pg' => (int)($_POST['opsi_pg'] ?? 4),
            'waktu_pengerjaan' => (int)($_POST['waktu'] ?? 60),
            'petunjuk_umum' => $_POST['petunjuk'] ?? '',
        ];

        $count = $this->soalModel->bulkSetPengaturan($kelasIds, $id_semester, $config);

        Flasher::setFlash("Berhasil mengatur pengaturan soal untuk " . count($kelasIds) . " kelas ({$count} mapel)!", 'success');
        header('Location: ' . BASEURL . '/soalSts');
        exit;
    }

    /**
     * Proses Copy Pengaturan — copy dari 1 kelas ke kelas lain
     */
    public function prosessCopyPengaturan()
    {
        $this->adminOnlyGuard();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/soalSts/bulkPengaturan');
            exit;
        }

        $fromKelas = $_POST['from_kelas'] ?? 0;
        $toKelasIds = $_POST['to_kelas_ids'] ?? [];
        $id_semester = $_SESSION['id_semester_aktif'] ?? 0;

        if (empty($fromKelas) || empty($toKelasIds)) {
            Flasher::setFlash('Pilih kelas sumber dan kelas tujuan!', 'danger');
            header('Location: ' . BASEURL . '/soalSts/bulkPengaturan');
            exit;
        }

        // Hapus kelas sumber dari tujuan jika ada
        $toKelasIds = array_filter($toKelasIds, function ($id) use ($fromKelas) {
            return $id != $fromKelas;
        });

        if (empty($toKelasIds)) {
            Flasher::setFlash('Kelas tujuan tidak boleh sama dengan kelas sumber!', 'danger');
            header('Location: ' . BASEURL . '/soalSts/bulkPengaturan');
            exit;
        }

        $count = $this->soalModel->copyPengaturanToKelas($fromKelas, $toKelasIds, $id_semester);

        Flasher::setFlash("Berhasil menyalin pengaturan ke " . count($toKelasIds) . " kelas ({$count} pengaturan)!", 'success');
        header('Location: ' . BASEURL . '/soalSts');
        exit;
    }

    /**
     * Form input soal berdasarkan pengaturan
     */
    public function inputSoal($id_pengaturan)
    {
        $pengaturan = $this->soalModel->getPengaturanById($id_pengaturan);
        if (!$pengaturan) {
            Flasher::setFlash('Pengaturan tidak ditemukan!', 'danger');
            header('Location: ' . BASEURL . ($this->isAdmin() ? '/soalSts' : '/guru'));
            exit;
        }

        // Cek akses guru: harus punya penugasan untuk mapel+kelas ini
        if (!$this->cekAksesMapelKelas($pengaturan['id_mapel'], $pengaturan['id_kelas'])) {
            $this->guruAccessDenied();
        }

        $this->data['judul'] = 'Soal STS - Input Soal';
        $this->data['pengaturan'] = $pengaturan;

        // Ambil soal yang sudah ada
        $soalList = $this->soalModel->getSoalByPengaturan($id_pengaturan);
        $soalMap = [];
        foreach ($soalList as $s) {
            $soalMap[$s['tipe_soal'] . '_' . $s['nomor_soal']] = $s;
        }
        $this->data['soal_map'] = $soalMap;
        $this->data['total_pg'] = $this->soalModel->hitungSoalByPengaturan($id_pengaturan, 'pg');
        $this->data['total_essay'] = $this->soalModel->hitungSoalByPengaturan($id_pengaturan, 'essay');
        $this->data['total_isian'] = $this->soalModel->hitungSoalByPengaturan($id_pengaturan, 'isian');

        $this->view('templates/header', $this->data);
        $this->loadSidebar();
        $this->view('admin/soal_sts/input_soal', $this->data);
        $this->view('templates/footer', $this->data);
    }

    /**
     * Simpan semua soal sekaligus
     */
    public function simpanSemuaSoal()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . ($this->isAdmin() ? '/soalSts' : '/guru'));
            exit;
        }

        $id_pengaturan = $_POST['id_pengaturan'] ?? 0;
        $pengaturan = $this->soalModel->getPengaturanById($id_pengaturan);
        if (!$pengaturan) {
            Flasher::setFlash('Pengaturan tidak ditemukan!', 'danger');
            header('Location: ' . BASEURL . ($this->isAdmin() ? '/soalSts' : '/guru'));
            exit;
        }

        // Cek akses guru
        if (!$this->cekAksesMapelKelas($pengaturan['id_mapel'], $pengaturan['id_kelas'])) {
            $this->guruAccessDenied();
        }

        $soalData = $_POST['soal'] ?? [];
        $uploadDir = APPROOT . '/public/uploads/soal_sts/';

        // Siapkan R2 storage
        require_once APPROOT . '/app/core/R2Storage.php';
        $r2 = R2Storage::isConfigured() ? new R2Storage() : null;
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0775, true);
        }
        $uploadError = null;

        foreach ($soalData as $key => $soal) {
            // key format: pg_1, essay_1, isian_1
            list($tipe, $nomor) = explode('_', $key, 2);

            // Skip jika pertanyaan kosong
            if (empty(trim($soal['pertanyaan'] ?? ''))) continue;

            $data = [
                'id_pengaturan' => $id_pengaturan,
                'id_mapel' => $pengaturan['id_mapel'],
                'id_kelas' => $pengaturan['id_kelas'],
                'id_semester' => $pengaturan['id_semester'],
                'tipe_soal' => $tipe,
                'nomor_soal' => (int)$nomor,
                'pertanyaan' => $soal['pertanyaan'],
                'opsi_a' => $soal['opsi_a'] ?? null,
                'opsi_b' => $soal['opsi_b'] ?? null,
                'opsi_c' => $soal['opsi_c'] ?? null,
                'opsi_d' => $soal['opsi_d'] ?? null,
                'opsi_e' => $soal['opsi_e'] ?? null,
                'kunci_jawaban' => $soal['kunci_jawaban'] ?? null,
                'skor' => $soal['skor'] ?? 1,
                'created_by' => $_SESSION['user_id'] ?? null,
                'gambar_soal' => null,
            ];

            // Handle file upload
            $fileKey = "gambar_{$key}";
            if (isset($_FILES[$fileKey]) && $_FILES[$fileKey]['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES[$fileKey];
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

                if (in_array($ext, $allowed) && $file['size'] <= 2 * 1024 * 1024) {
                    $filename = 'soal_' . $id_pengaturan . '_' . $key . '_' . time() . '.' . $ext;

                    if ($r2) {
                        // Upload ke Cloudflare R2
                        $objectKey = 'soal-sts/' . $filename;
                        $mime = safeImageMime($file['tmp_name'], $ext);
                        $result = $r2->upload(file_get_contents($file['tmp_name']), $objectKey, $mime);
                        if ($result['success']) {
                            // Hapus gambar R2 lama jika ada
                            $oldKey = soalGambarKey($soal['gambar_existing'] ?? '');
                            if ($oldKey) { $r2->delete($oldKey); }
                            $data['gambar_soal'] = $result['url'];
                        } else {
                            $uploadError = 'Gagal upload gambar soal ' . $key . ' ke R2: ' . ($result['error'] ?? 'Unknown');
                        }
                    } else {
                        $uploadError = 'Penyimpanan R2 belum dikonfigurasi. Hubungi admin.';
                    }
                } elseif (!in_array($ext, $allowed)) {
                    $uploadError = 'Format gambar soal ' . $key . ' tidak didukung (jpg/png/gif/webp).';
                } else {
                    $uploadError = 'Ukuran gambar soal ' . $key . ' melebihi 2MB.';
                }
            }

            // Jika tidak ada upload baru, pertahankan gambar lama
            if (!$data['gambar_soal'] && !empty($soal['gambar_existing'])) {
                $data['gambar_soal'] = $soal['gambar_existing'];
            }

            $this->soalModel->upsertSoal($data);
        }

        if ($uploadError) {
            Flasher::setFlash($uploadError, 'warning');
        } else {
            Flasher::setFlash('Soal berhasil disimpan!', 'success');
        }
        header('Location: ' . BASEURL . '/soalSts/inputSoal/' . $id_pengaturan);
        exit;
    }

    /**
     * Hapus soal
     */
    public function hapusSoal($id_soal)
    {
        $soal = $this->soalModel->getSoalById($id_soal);
        if (!$soal) {
            Flasher::setFlash('Soal tidak ditemukan!', 'danger');
            header('Location: ' . BASEURL . ($this->isAdmin() ? '/soalSts' : '/guru'));
            exit;
        }

        // Cek akses guru
        if (!$this->cekAksesMapelKelas($soal['id_mapel'], $soal['id_kelas'])) {
            $this->guruAccessDenied();
        }

        $this->soalModel->hapusSoal($id_soal);
        Flasher::setFlash('Soal berhasil dihapus!', 'success');

        // Redirect kembali ke daftar soal
        header('Location: ' . BASEURL . '/soalSts/daftarSoal/' . $soal['id_kelas'] . '/' . $soal['id_mapel']);
        exit;
    }

    /**
     * Daftar semua soal per mapel per kelas
     */
    public function daftarSoal($id_kelas, $id_mapel)
    {
        $this->data['judul'] = 'Soal STS - Daftar Soal';
        $id_semester_aktif = $_SESSION['id_semester_aktif'] ?? 0;

        // Cek akses guru
        if (!$this->cekAksesMapelKelas($id_mapel, $id_kelas)) {
            $this->guruAccessDenied();
        }

        $kelas = $this->model('Kelas_model')->getKelasById($id_kelas);
        $pengaturan = $this->soalModel->getPengaturanByMapelKelas($id_mapel, $id_kelas, $id_semester_aktif);

        if (!$kelas || !$pengaturan) {
            Flasher::setFlash('Data tidak ditemukan!', 'danger');
            header('Location: ' . BASEURL . ($this->isAdmin() ? '/soalSts' : '/guru'));
            exit;
        }

        $this->data['kelas'] = $kelas;
        $this->data['pengaturan'] = $pengaturan;
        $this->data['soal_list'] = $this->soalModel->getSoalByPengaturan($pengaturan['id']);
        $this->data['total_pg'] = $this->soalModel->hitungSoalByPengaturan($pengaturan['id'], 'pg');
        $this->data['total_essay'] = $this->soalModel->hitungSoalByPengaturan($pengaturan['id'], 'essay');
        $this->data['total_isian'] = $this->soalModel->hitungSoalByPengaturan($pengaturan['id'], 'isian');

        $this->view('templates/header', $this->data);
        $this->loadSidebar();
        $this->view('admin/soal_sts/daftar_soal', $this->data);
        $this->view('templates/footer', $this->data);
    }

    /**
     * Preview cetak soal (HTML print-friendly)
     */
    public function previewCetak($id_kelas, $id_mapel)
    {
        $this->data['judul'] = 'Soal STS - Preview Cetak';
        $id_semester_aktif = $_SESSION['id_semester_aktif'] ?? 0;
        $id_tp_aktif = $_SESSION['id_tp_aktif'] ?? 0;

        // Cek akses guru
        if (!$this->cekAksesMapelKelas($id_mapel, $id_kelas)) {
            $this->guruAccessDenied();
        }

        $kelas = $this->model('Kelas_model')->getKelasById($id_kelas);
        $pengaturan = $this->soalModel->getPengaturanByMapelKelas($id_mapel, $id_kelas, $id_semester_aktif);

        if (!$kelas || !$pengaturan) {
            Flasher::setFlash('Data tidak ditemukan!', 'danger');
            header('Location: ' . BASEURL . ($this->isAdmin() ? '/soalSts' : '/guru'));
            exit;
        }

        // Ambil kop dari pengaturan rapor
        $kopRapor = $this->model('PengaturanRapor_model')->getPengaturanByKelas($id_kelas, $id_tp_aktif);

        // Ambil semester info
        $semester = $this->model('TahunPelajaran_model')->getSemesterById($id_semester_aktif);

        $this->data['kelas'] = $kelas;
        $this->data['pengaturan'] = $pengaturan;
        $this->data['kop_rapor'] = $kopRapor;
        $this->data['semester'] = $semester;
        $this->data['soal_pg'] = $this->soalModel->getSoalByTipe($pengaturan['id'], 'pg');
        $this->data['soal_essay'] = $this->soalModel->getSoalByTipe($pengaturan['id'], 'essay');
        $this->data['soal_isian'] = $this->soalModel->getSoalByTipe($pengaturan['id'], 'isian');

        $this->view('admin/soal_sts/preview_cetak', $this->data);
    }

    /**
     * Cetak PDF via Dompdf
     */
    public function cetakPDF($id_kelas, $id_mapel)
    {
        $id_semester_aktif = $_SESSION['id_semester_aktif'] ?? 0;
        $id_tp_aktif = $_SESSION['id_tp_aktif'] ?? 0;

        // Cek akses guru
        if (!$this->cekAksesMapelKelas($id_mapel, $id_kelas)) {
            $this->guruAccessDenied();
        }

        $kelas = $this->model('Kelas_model')->getKelasById($id_kelas);
        $pengaturan = $this->soalModel->getPengaturanByMapelKelas($id_mapel, $id_kelas, $id_semester_aktif);

        if (!$kelas || !$pengaturan) {
            Flasher::setFlash('Data tidak ditemukan!', 'danger');
            header('Location: ' . BASEURL . ($this->isAdmin() ? '/soalSts' : '/guru'));
            exit;
        }

        $kopRapor = $this->model('PengaturanRapor_model')->getPengaturanByKelas($id_kelas, $id_tp_aktif);
        $semester = $this->model('TahunPelajaran_model')->getSemesterById($id_semester_aktif);

        $soal_pg = $this->soalModel->getSoalByTipe($pengaturan['id'], 'pg');
        $soal_essay = $this->soalModel->getSoalByTipe($pengaturan['id'], 'essay');
        $soal_isian = $this->soalModel->getSoalByTipe($pengaturan['id'], 'isian');

        // Load Dompdf
        require_once APPROOT . '/app/core/dompdf/autoload.inc.php';

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Arial');

        $dompdf = new Dompdf($options);

        ob_start();
        include APPROOT . '/app/views/admin/soal_sts/cetak_pdf.php';
        $html = ob_get_clean();

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'Soal_STS_' . $pengaturan['nama_mapel'] . '_' . $kelas['nama_kelas'] . '.pdf';
        $dompdf->stream($filename, ['Attachment' => false]);
    }

    /**
     * Cetak kunci jawaban
     */
    public function cetakKunci($id_kelas, $id_mapel)
    {
        $this->data['judul'] = 'Soal STS - Kunci Jawaban';
        $id_semester_aktif = $_SESSION['id_semester_aktif'] ?? 0;
        $id_tp_aktif = $_SESSION['id_tp_aktif'] ?? 0;

        // Cek akses guru
        if (!$this->cekAksesMapelKelas($id_mapel, $id_kelas)) {
            $this->guruAccessDenied();
        }

        $kelas = $this->model('Kelas_model')->getKelasById($id_kelas);
        $pengaturan = $this->soalModel->getPengaturanByMapelKelas($id_mapel, $id_kelas, $id_semester_aktif);

        if (!$kelas || !$pengaturan) {
            Flasher::setFlash('Data tidak ditemukan!', 'danger');
            header('Location: ' . BASEURL . ($this->isAdmin() ? '/soalSts' : '/guru'));
            exit;
        }

        $kopRapor = $this->model('PengaturanRapor_model')->getPengaturanByKelas($id_kelas, $id_tp_aktif);
        $semester = $this->model('TahunPelajaran_model')->getSemesterById($id_semester_aktif);

        $this->data['kelas'] = $kelas;
        $this->data['pengaturan'] = $pengaturan;
        $this->data['kop_rapor'] = $kopRapor;
        $this->data['semester'] = $semester;
        $this->data['soal_pg'] = $this->soalModel->getSoalByTipe($pengaturan['id'], 'pg');

        // Render as print-friendly page
        $this->view('admin/soal_sts/preview_cetak', array_merge($this->data, ['mode' => 'kunci']));
    }

    /**
     * Helper: Upload gambar soal individual (AJAX)
     */
    public function uploadGambar()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit;
        }

        if (!isset($_FILES['gambar']) || $_FILES['gambar']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'message' => 'Tidak ada file yang diupload']);
            exit;
        }

        $file = $_FILES['gambar'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (!in_array($ext, $allowed)) {
            echo json_encode(['success' => false, 'message' => 'Format file tidak didukung']);
            exit;
        }

        if ($file['size'] > 2 * 1024 * 1024) {
            echo json_encode(['success' => false, 'message' => 'Ukuran file melebihi 2MB']);
            exit;
        }

        $uploadDir = APPROOT . '/public/uploads/soal_sts/';
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0775, true);
        }
        $filename = 'soal_' . time() . '_' . mt_rand(1000, 9999) . '.' . $ext;

        require_once APPROOT . '/app/core/R2Storage.php';
        if (!R2Storage::isConfigured()) {
            echo json_encode(['success' => false, 'message' => 'Penyimpanan R2 belum dikonfigurasi']);
            exit;
        }

        $r2 = new R2Storage();
        $objectKey = 'soal-sts/' . $filename;
        $mime = safeImageMime($file['tmp_name'], $ext);
        $result = $r2->upload(file_get_contents($file['tmp_name']), $objectKey, $mime);

        if ($result['success']) {
            echo json_encode([
                'success' => true,
                'filename' => $filename,
                'url' => $result['url']
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal upload ke R2: ' . ($result['error'] ?? 'Unknown')]);
        }
        exit;
    }

    /**
     * Hapus gambar soal (AJAX)
     */
    public function hapusGambar()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit;
        }

        $filename = $_POST['filename'] ?? '';
        if (empty($filename)) {
            echo json_encode(['success' => false, 'message' => 'Filename kosong']);
            exit;
        }

        // Jika URL R2, hapus dari R2
        $r2Key = soalGambarKey($filename);
        if ($r2Key) {
            require_once APPROOT . '/app/core/R2Storage.php';
            if (R2Storage::isConfigured()) {
                $r2 = new R2Storage();
                $r2->delete($r2Key);
            }
            echo json_encode(['success' => true]);
            exit;
        }

        // Gambar lama (lokal)
        $filepath = APPROOT . '/public/uploads/soal_sts/' . basename($filename);
        if (file_exists($filepath)) {
            unlink($filepath);
        }

        echo json_encode(['success' => true]);
        exit;
    }
}
