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

    /**
     * Rekap Tagihan untuk kelas tertentu (Bendahara)
     */
    public function rekapTagihan($id_kelas)
    {
        $this->data['judul'] = 'Bendahara - Rekap Tagihan';
        $id_tp_aktif = $_SESSION['id_tp_aktif'] ?? 0;
        $id_semester = $_SESSION['id_semester_aktif'] ?? null;

        $kelas = $this->model('Kelas_model')->getKelasById($id_kelas);
        if (!$kelas) {
            Flasher::setFlash('Kelas tidak ditemukan.', 'danger');
            header('Location: ' . BASEURL . '/bendahara/pembayaran');
            exit;
        }

        $this->data['wali_kelas_info'] = [
            'id_kelas' => $id_kelas,
            'nama_kelas' => $kelas['nama_kelas']
        ];

        // Get all siswa in the class
        $siswaList = $this->model('Siswa_model')->getSiswaByKelas($id_kelas, $id_tp_aktif);

        // Get all tagihan for this class
        $tagihanList = $this->model('Pembayaran_model')->getTagihanKelas($id_kelas, $id_tp_aktif, $id_semester);

        // Build rekap data: siswa -> tagihan -> status
        $rekapData = [];
        foreach ($siswaList as $siswa) {
            $siswaTagihan = [
                'siswa' => $siswa,
                'tagihan' => [],
                'total_tagihan' => 0,
                'total_dibayar' => 0,
                'total_sisa' => 0
            ];

            foreach ($tagihanList as $tagihan) {
                $pembayaranSiswa = $this->model('Pembayaran_model')->getPembayaranSiswa($tagihan['id'], $siswa['id_siswa']);

                $jumlahTagihan = (int) ($tagihan['nominal_default'] ?? 0);
                $sudahBayar = $pembayaranSiswa ? (int) ($pembayaranSiswa['total_terbayar'] ?? 0) : 0;
                $diskon = $pembayaranSiswa ? (int) ($pembayaranSiswa['diskon'] ?? 0) : 0;
                $netTagihan = $jumlahTagihan - $diskon;
                $sisa = $netTagihan - $sudahBayar;
                $status = $sisa <= 0 ? 'Lunas' : ($sudahBayar > 0 ? 'Cicil' : 'Belum');

                $siswaTagihan['tagihan'][] = [
                    'nama_tagihan' => $tagihan['nama'],
                    'nominal' => $netTagihan,
                    'dibayar' => $sudahBayar,
                    'sisa' => max(0, $sisa),
                    'status' => $status,
                    'jatuh_tempo' => $tagihan['jatuh_tempo'] ?? null
                ];

                $siswaTagihan['total_tagihan'] += $netTagihan;
                $siswaTagihan['total_dibayar'] += $sudahBayar;
                $siswaTagihan['total_sisa'] += $sisa;
            }

            $rekapData[] = $siswaTagihan;
        }

        $this->data['tagihan_list'] = $tagihanList;
        $this->data['rekap_data'] = $rekapData;
        $this->data['bendahara_mode'] = true;
        $this->data['back_url'] = BASEURL . '/bendahara/kelolaPembayaran/' . $id_kelas;

        $this->view('templates/header', $this->data);
        $this->view('wali_kelas/rekap_tagihan', $this->data);
        $this->view('templates/footer');
    }

    /**
     * Riwayat Pembayaran untuk kelas tertentu (Bendahara)
     */
    public function pembayaranRiwayat($id_kelas)
    {
        $this->data['judul'] = 'Bendahara - Riwayat Pembayaran';
        $id_tp_aktif = $_SESSION['id_tp_aktif'] ?? 0;
        $id_semester = $_SESSION['id_semester_aktif'] ?? null;

        $kelas = $this->model('Kelas_model')->getKelasById($id_kelas);
        if (!$kelas) {
            Flasher::setFlash('Kelas tidak ditemukan.', 'danger');
            header('Location: ' . BASEURL . '/bendahara/pembayaran');
            exit;
        }

        $this->data['wali_kelas_info'] = [
            'id_kelas' => $id_kelas,
            'nama_kelas' => $kelas['nama_kelas']
        ];
        $this->data['riwayat'] = $this->model('Pembayaran_model')->getRiwayat($id_kelas, $id_tp_aktif, 200);
        $this->data['bendahara_mode'] = true;
        $this->data['back_url'] = BASEURL . '/bendahara/kelolaPembayaran/' . $id_kelas;

        $this->view('templates/header', $this->data);
        $this->view('wali_kelas/pembayaran_riwayat', $this->data);
        $this->view('templates/footer');
    }

    /**
     * Export PDF Pembayaran untuk kelas tertentu (Bendahara)
     */
    public function pembayaranExport($id_kelas)
    {
        $id_tp_aktif = $_SESSION['id_tp_aktif'] ?? 0;
        $id_semester = $_SESSION['id_semester_aktif'] ?? null;

        $kelas = $this->model('Kelas_model')->getKelasById($id_kelas);
        if (!$kelas) {
            Flasher::setFlash('Kelas tidak ditemukan.', 'danger');
            header('Location: ' . BASEURL . '/bendahara/pembayaran');
            exit;
        }

        // Build riwayat PDF
        $rows = $this->model('Pembayaran_model')->getRiwayat($id_kelas, $id_tp_aktif, 10000);

        $namaSemester = $_SESSION['nama_semester_aktif'] ?? '';
        $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>
            body { font-family: Arial, sans-serif; margin: 15px; font-size: 11px; line-height: 1.3; }
            .header { text-align: center; margin-bottom: 15px; border-bottom: 2px solid #333; padding-bottom: 10px; }
            .header h1 { color: #333; font-size: 18px; margin: 0; font-weight: bold; }
            .header h2 { color: #666; font-size: 12px; margin: 5px 0 0 0; font-weight: normal; }
            .info-section { background: #f9f9f9; padding: 10px; margin-bottom: 15px; border: 1px solid #ddd; }
            .info-row { margin-bottom: 3px; }
            .info-label { font-weight: bold; display: inline-block; width: 120px; color: #333; }
            .info-value { color: #666; }
            table { width: 100%; border-collapse: collapse; }
            th, td { border: 1px solid #ddd; padding: 6px; }
            th { background: #f5f5f5; }
            .right { text-align: right; }
        </style></head><body>';
        $html .= '<div class="header">
            <h1>Riwayat Pembayaran (Bendahara)</h1>
            <h2>Kelas ' . htmlspecialchars($kelas['nama_kelas'] ?? '-') . (!empty($namaSemester) ? ' • ' . htmlspecialchars($namaSemester) : '') . '</h2>
        </div>';
        $html .= '<div class="info-section">
            <div class="info-row"><span class="info-label">Tanggal Cetak</span><span class="info-value">' . date('d/m/Y H:i') . ' WIB</span></div>
            <div class="info-row"><span class="info-label">Dicetak Oleh</span><span class="info-value">Bendahara</span></div>
        </div>';
        $html .= '<table><thead><tr>
            <th style="width: 20%">Tanggal</th>
            <th style="width: 22%">Siswa</th>
            <th>Tagihan</th>
            <th style="width: 12%">Jumlah</th>
            <th style="width: 12%">Metode</th>
            <th>Keterangan</th>
        </tr></thead><tbody>';
        foreach ($rows as $r) {
            $html .= '<tr>'
                . '<td>' . htmlspecialchars($r['tanggal']) . '</td>'
                . '<td>' . htmlspecialchars($r['nama_siswa']) . '</td>'
                . '<td>' . htmlspecialchars($r['nama_tagihan']) . '</td>'
                . '<td class="right">' . number_format((int) $r['jumlah'], 0, ',', '.') . '</td>'
                . '<td>' . htmlspecialchars($r['metode'] ?? '-') . '</td>'
                . '<td>' . htmlspecialchars($r['keterangan'] ?? '-') . '</td>'
                . '</tr>';
        }
        if (empty($rows)) {
            $html .= '<tr><td colspan="6" style="text-align:center;color:#666;">Belum ada transaksi</td></tr>';
        }
        $html .= '</tbody></table></body></html>';

        // Dompdf
        require_once APPROOT . '/app/core/dompdf/autoload.inc.php';
        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $dompdf = new \Dompdf\Dompdf($options);

        // Add QR code for document validation
        require_once APPROOT . '/app/core/PDFQRHelper.php';
        $html = PDFQRHelper::addQRToPDF($html, 'pembayaran_riwayat', $id_kelas);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $filename = 'Riwayat_Pembayaran_Bendahara_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $kelas['nama_kelas'] ?? 'Kelas') . '_' . date('Ymd_His') . '.pdf';
        $dompdf->stream($filename, ['Attachment' => true]);
        exit;
    }

    /**
     * Detail Tagihan untuk Bendahara (pembayaran per siswa dalam tagihan tertentu)
     */
    public function pembayaranTagihan($id_tagihan)
    {
        $this->data['judul'] = 'Bendahara - Detail Tagihan';
        $id_tp_aktif = $_SESSION['id_tp_aktif'] ?? 0;

        $tagihan = $this->model('Pembayaran_model')->getTagihanById($id_tagihan);
        if (!$tagihan) {
            Flasher::setFlash('Tagihan tidak ditemukan.', 'danger');
            header('Location: ' . BASEURL . '/bendahara/pembayaran');
            exit;
        }

        $id_kelas = $tagihan['id_kelas'];
        $kelas = $this->model('Kelas_model')->getKelasById($id_kelas);

        $this->data['wali_kelas_info'] = [
            'id_kelas' => $id_kelas,
            'nama_kelas' => $kelas['nama_kelas'] ?? 'Kelas'
        ];
        $this->data['tagihan'] = $tagihan;
        $this->data['siswa_list'] = $this->model('Siswa_model')->getSiswaByKelas($id_kelas, $id_tp_aktif);
        $this->data['tagihan_siswa'] = $this->model('Pembayaran_model')->getTagihanSiswaList($id_tagihan);
        $this->data['bendahara_mode'] = true;

        $this->view('templates/header', $this->data);
        $this->view('wali_kelas/pembayaran_tagihan', $this->data);
        $this->view('templates/footer');
    }

    /**
     * Proses pembayaran untuk Bendahara
     */
    public function prosesPembayaranTagihan()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/bendahara/pembayaran');
            exit;
        }

        $tagihan_id = $_POST['tagihan_id'] ?? 0;
        $id_siswa = $_POST['id_siswa'] ?? 0;
        $jumlah = (int) str_replace(['.', ','], '', $_POST['jumlah'] ?? '0');
        $metode = $_POST['metode'] ?? 'Tunai';
        $keterangan = $_POST['keterangan'] ?? '';

        if (!$tagihan_id || !$id_siswa || $jumlah <= 0) {
            Flasher::setFlash('Data tidak lengkap.', 'danger');
            header('Location: ' . BASEURL . '/bendahara/pembayaran');
            exit;
        }

        $data = [
            'tagihan_id' => $tagihan_id,
            'id_siswa' => $id_siswa,
            'jumlah' => $jumlah,
            'metode' => $metode,
            'keterangan' => $keterangan,
            'user_input_id' => $_SESSION['user_id'] ?? 0
        ];

        // $result sekarang berisi ID transaksi (lastInsertId) atau false
        $lastId = $this->model('Pembayaran_model')->createTransaksi(
            $tagihan_id,
            $id_siswa,
            $jumlah,
            $metode,
            $keterangan,
            null, // bukti_path
            $_SESSION['user_id'] ?? 0
        );

        if ($lastId) {
            // === KIRIM NOTIFIKASI WA ===
            $this->sendPembayaranNotification($lastId);

            Flasher::setFlash('Pembayaran berhasil disimpan dan notifikasi dikirim.', 'success');
        } else {
            Flasher::setFlash('Gagal menyimpan pembayaran.', 'danger');
        }

        header('Location: ' . BASEURL . '/bendahara/pembayaranTagihan/' . $tagihan_id);
        exit;
    }

    /**
     * Helper: Kirim notifikasi pembayaran
     */
    private function sendPembayaranNotification($id_transaksi)
    {
        try {
            $trx = $this->model('Pembayaran_model')->getTransaksiById($id_transaksi);
            if (!$trx)
                return;

            $siswa = $this->model('Siswa_model')->getSiswaById($trx['id_siswa']);
            if (!$siswa)
                return;

            $tagihan = $this->model('Pembayaran_model')->getTagihanById($trx['tagihan_id']);
            $namaTagihan = $tagihan['nama'] ?? 'Pembayaran Sekolah';

            // Ambil info detail tagihan siswa untuk sisa
            $tagihanSiswa = $this->model('Pembayaran_model')->getTagihanSiswa($trx['tagihan_id'], $trx['id_siswa']);
            $nominal = (int) ($tagihan['nominal_default'] ?? 0);
            $diskon = (int) ($tagihanSiswa['diskon'] ?? 0);
            $terbayar = (int) ($tagihanSiswa['total_terbayar'] ?? 0);
            $sisa = max(0, $nominal - $diskon - $terbayar);

            // Penerima (User login)
            $penerima = $_SESSION['user_nama_lengkap'] ?? 'Bendahara';

            // Nama Sekolah
            $pengaturan = $this->model('PengaturanAplikasi_model')->getPengaturan();
            $namaSekolah = $pengaturan['nama_sekolah'] ?? '';

            // Load Fonnte
            require_once APPROOT . '/app/core/Fonnte.php';
            $fonnte = new \Fonnte();

            // Helper cleaning number
            $cleanNumber = function ($num) {
                $num = preg_replace('/[^0-9]/', '', $num);
                return (strlen($num) > 7) ? $num : '';
            };

            $ayahNo = $cleanNumber($siswa['ayah_no_hp'] ?? '');
            $ibuNo = $cleanNumber($siswa['ibu_no_hp'] ?? '');
            $waliNo = $cleanNumber($siswa['wali_no_hp'] ?? '');

            $targets = [];
            if ($ayahNo)
                $targets[] = ['no' => $ayahNo, 'nama' => $siswa['ayah_kandung'] ?? 'Bapak'];
            if ($ibuNo)
                $targets[] = ['no' => $ibuNo, 'nama' => $siswa['ibu_kandung'] ?? 'Ibu'];
            if ($waliNo)
                $targets[] = ['no' => $waliNo, 'nama' => $siswa['wali_nama'] ?? 'Wali'];

            error_log("[DEBUG-BENDAHARA] Targets found: " . count($targets));
            foreach ($targets as $t) {
                error_log("[DEBUG-BENDAHARA] Sending to: " . $t['nama'] . " (" . $t['no'] . ")");
                $result = $fonnte->sendNotifikasiPembayaran(
                    $t['no'],
                    $t['nama'],
                    $siswa['nama_siswa'],
                    $namaTagihan,
                    $trx['jumlah'],
                    $diskon,
                    date('d-m-Y H:i', strtotime($trx['tanggal'])),
                    $penerima,
                    $sisa,
                    $trx['keterangan'],
                    $namaSekolah
                );
                error_log("[DEBUG-BENDAHARA] Result: " . print_r($result, true));
            }
        } catch (Throwable $e) {
            error_log("[DEBUG-BENDAHARA] sendPembayaranNotification FATAL ERROR: " . $e->getMessage());
            error_log("[DEBUG-BENDAHARA] Stack trace: " . $e->getTraceAsString());
        }
    }

    /**
     * Helper: Kirim notifikasi update diskon
     */
    private function sendDiskonNotification($tagihan_id, $id_siswa, $diskon)
    {
        try {
            $siswa = $this->model('Siswa_model')->getSiswaById($id_siswa);
            if (!$siswa)
                return;

            $tagihan = $this->model('Pembayaran_model')->getTagihanById($tagihan_id);
            if (!$tagihan)
                return;

            $tagihanSiswa = $this->model('Pembayaran_model')->getTagihanSiswa($tagihan_id, $id_siswa);
            $nominal = (int) ($tagihan['nominal_default'] ?? 0);
            $terbayar = (int) ($tagihanSiswa['total_terbayar'] ?? 0);
            $sisa = max(0, $nominal - $diskon - $terbayar);

            $pengaturan = $this->model('PengaturanAplikasi_model')->getPengaturan();
            $namaSekolah = $pengaturan['nama_sekolah'] ?? '';

            require_once APPROOT . '/app/core/Fonnte.php';
            $fonnte = new \Fonnte();

            $cleanNumber = function ($num) {
                $n = preg_replace('/[^0-9]/', '', $num);
                return (strlen($n) > 7) ? $n : '';
            };

            $ayahNo = $cleanNumber($siswa['ayah_no_hp'] ?? '');
            $ibuNo = $cleanNumber($siswa['ibu_no_hp'] ?? '');
            $waliNo = $cleanNumber($siswa['wali_no_hp'] ?? '');

            $targets = [];
            if ($ayahNo)
                $targets[] = ['no' => $ayahNo, 'nama' => $siswa['ayah_kandung'] ?? 'Bapak'];
            if ($ibuNo)
                $targets[] = ['no' => $ibuNo, 'nama' => $siswa['ibu_kandung'] ?? 'Ibu'];
            if ($waliNo)
                $targets[] = ['no' => $waliNo, 'nama' => $siswa['wali_nama'] ?? 'Wali'];

            foreach ($targets as $t) {
                $fonnte->sendNotifikasiDiskon(
                    $t['no'],
                    $t['nama'],
                    $siswa['nama_siswa'],
                    $tagihan['nama'],
                    $diskon,
                    $sisa,
                    $namaSekolah
                );
            }
        } catch (Throwable $e) {
            error_log("sendDiskonNotification (Bendahara) error: " . $e->getMessage());
        }
    }

    /**
     * Proxy: Invoice Thermal Data (JSON) untuk Bendahara
     */
    public function invoiceThermalData($tagihan_id, $id_siswa)
    {
        // Cukup panggil method yang sama dari WaliKelasController
        require_once APPROOT . '/app/controllers/WaliKelasController.php';
        $waliKelas = new WaliKelasController();
        // Panggil method langsung
        $waliKelas->invoiceThermalData($tagihan_id, $id_siswa);
    }

    /**
     * Proxy: Invoice Pembayaran PDF untuk Bendahara  
     */
    public function invoicePembayaran($tagihan_id, $id_siswa)
    {
        require_once APPROOT . '/app/controllers/WaliKelasController.php';
        $waliKelas = new WaliKelasController();
        $waliKelas->invoicePembayaran($tagihan_id, $id_siswa);
    }

    /**
     * Proxy: Pembayaran Tagihan PDF untuk Bendahara
     */
    public function pembayaranTagihanPDF($tagihan_id)
    {
        require_once APPROOT . '/app/controllers/WaliKelasController.php';
        $waliKelas = new WaliKelasController();
        $waliKelas->pembayaranTagihanPDF($tagihan_id);
    }

    /**
     * Proxy: Simpan Tagihan Kelas untuk Bendahara
     */
    public function simpanTagihanKelas()
    {
        // Gunakan method simpanTagihan milik Bendahara sendiri agar redirect benar
        $this->simpanTagihan();
    }

    /**
     * Proxy: Hapus Transaksi Pembayaran untuk Bendahara
     */
    public function hapusTransaksi()
    {
        require_once APPROOT . '/app/controllers/WaliKelasController.php';
        $waliKelas = new WaliKelasController();
        $waliKelas->hapusTransaksi();
    }

    /**
     * Proxy: Get Riwayat Transaksi untuk Bendahara
     */
    public function getRiwayatTransaksi($tagihan_id, $id_siswa)
    {
        require_once APPROOT . '/app/controllers/WaliKelasController.php';
        $waliKelas = new WaliKelasController();
        $waliKelas->getRiwayatTransaksi($tagihan_id, $id_siswa);
    }

    /**
     * Proxy: Bayar Lunas untuk Bendahara
     */
    public function bayarLunas()
    {
        require_once APPROOT . '/app/controllers/WaliKelasController.php';
        $waliKelas = new WaliKelasController();
        $waliKelas->bayarLunas();
    }

    /**
     * Rekap Tagihan PDF untuk Bendahara
     */
    public function rekapTagihanPDF($id_kelas)
    {
        $id_tp_aktif = $_SESSION['id_tp_aktif'] ?? 0;
        $id_semester_aktif = $_SESSION['id_semester_aktif'] ?? 0;

        $kelas = $this->model('Kelas_model')->getKelasById($id_kelas);
        if (!$kelas) {
            Flasher::setFlash('Kelas tidak ditemukan.', 'danger');
            header('Location: ' . BASEURL . '/bendahara/pembayaran');
            exit;
        }

        $waliKelasInfo = [
            'id_kelas' => $id_kelas,
            'nama_kelas' => $kelas['nama_kelas']
        ];

        // Get all siswa in the class
        $siswaList = $this->model('Siswa_model')->getSiswaByKelas($id_kelas, $id_tp_aktif);

        // Get all tagihan for this class
        $tagihanList = $this->model('Pembayaran_model')->getTagihanKelas($id_kelas, $id_tp_aktif, $id_semester_aktif);

        // Build rekap data
        $rekapData = [];
        foreach ($siswaList as $siswa) {
            $siswaTagihan = [
                'siswa' => $siswa,
                'tagihan' => [],
                'total_tagihan' => 0,
                'total_dibayar' => 0,
                'total_sisa' => 0
            ];

            foreach ($tagihanList as $tagihan) {
                $pembayaranSiswa = $this->model('Pembayaran_model')->getPembayaranSiswa($tagihan['id'], $siswa['id_siswa']);

                $jumlahTagihan = (int) ($tagihan['nominal_default'] ?? 0);
                $sudahBayar = $pembayaranSiswa ? (int) ($pembayaranSiswa['total_terbayar'] ?? 0) : 0;
                $diskon = $pembayaranSiswa ? (int) ($pembayaranSiswa['diskon'] ?? 0) : 0;
                $netTagihan = $jumlahTagihan - $diskon;
                $sisa = $netTagihan - $sudahBayar;
                $status = $sisa <= 0 ? 'Lunas' : ($sudahBayar > 0 ? 'Cicil' : 'Belum');

                $siswaTagihan['tagihan'][] = [
                    'nama_tagihan' => $tagihan['nama'],
                    'nominal' => $netTagihan,
                    'dibayar' => $sudahBayar,
                    'sisa' => max(0, $sisa),
                    'status' => $status
                ];

                $siswaTagihan['total_tagihan'] += $netTagihan;
                $siswaTagihan['total_dibayar'] += $sudahBayar;
                $siswaTagihan['total_sisa'] += max(0, $sisa);
            }

            $rekapData[] = $siswaTagihan;
        }

        // Calculate totals
        $totalSemuaTagihan = 0;
        $totalSemuaDibayar = 0;
        $totalSemuaSisa = 0;
        foreach ($rekapData as $item) {
            $totalSemuaTagihan += $item['total_tagihan'];
            $totalSemuaDibayar += $item['total_dibayar'];
            $totalSemuaSisa += $item['total_sisa'];
        }

        // Generate PDF using dompdf
        require_once APPROOT . '/app/core/dompdf/autoload.inc.php';

        $options = new \Dompdf\Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);

        $dompdf = new \Dompdf\Dompdf($options);

        // Build clean HTML
        $html = $this->buildRekapTagihanPDFHtml($rekapData, $tagihanList, $waliKelasInfo, $totalSemuaTagihan, $totalSemuaDibayar, $totalSemuaSisa);

        // Add QR code for document validation
        require_once APPROOT . '/app/core/PDFQRHelper.php';
        $html = PDFQRHelper::addQRToPDF($html, 'rekap_tagihan', $id_kelas . '_' . date('Ymd'));

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $filename = 'Rekap_Tagihan_Bendahara_' . preg_replace('/[^A-Za-z0-9]/', '_', $kelas['nama_kelas']) . '_' . date('Ymd') . '.pdf';
        $dompdf->stream($filename, ['Attachment' => true]);
    }

    /**
     * Build HTML untuk PDF Rekap Tagihan (Copied from WaliKelasController)
     */
    private function buildRekapTagihanPDFHtml($rekapData, $tagihanList, $kelasInfo, $totalSemuaTagihan, $totalSemuaDibayar, $totalSemuaSisa)
    {
        $namaSemester = $_SESSION['nama_semester_aktif'] ?? 'Semester Tidak Diketahui';
        $namaKelas = $kelasInfo['nama_kelas'] ?? '';

        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Rekap Tagihan Siswa</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 15px; font-size: 11px; line-height: 1.3; position: relative; }
        .header { text-align: center; margin-bottom: 15px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .header h1 { color: #333; font-size: 18px; margin: 0; font-weight: bold; }
        .header h2 { color: #666; font-size: 12px; margin: 5px 0 0 0; font-weight: normal; }
        .info-section { background: #f9f9f9; padding: 10px; margin-bottom: 15px; border: 1px solid #ddd; }
        .info-row { margin-bottom: 3px; }
        .info-label { font-weight: bold; display: inline-block; width: 100px; color: #333; }
        .info-value { color: #666; }
        .stats { display: table; width: 100%; margin-bottom: 15px; border-collapse: collapse; }
        .stats-row { display: table-row; }
        .stat-box { display: table-cell; width: 33.33%; text-align: center; padding: 8px; background: #f5f5f5; border: 1px solid #ddd; }
        .stat-number { font-size: 16px; font-weight: bold; color: #333; }
        .stat-label { font-size: 10px; color: #666; margin-top: 2px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background: #f0f0f0; color: #333; font-weight: bold; padding: 8px 4px; text-align: center; border: 1px solid #ccc; font-size: 10px; }
        td { padding: 6px 4px; border: 1px solid #ccc; text-align: center; font-size: 10px; }
        tr:nth-child(even) { background-color: #fafafa; }
        .text-left { text-align: left !important; }
        .status-lunas { color: #155724; font-weight: bold; }
        .status-cicil { color: #856404; font-weight: bold; }
        .status-belum { color: #721c24; font-weight: bold; }
        .total-row { background-color: #e8f4f8 !important; font-weight: bold; }
        .footer { margin-top: 15px; text-align: center; color: #666; font-size: 9px; border-top: 1px solid #ddd; padding-top: 8px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN REKAP TAGIHAN SISWA (Bendahara)</h1>
        <h2>Sistem Informasi Manajemen Sekolah</h2>
    </div>
    
    <div class="info-section">
        <div class="info-row"><span class="info-label">Kelas:</span> <span class="info-value">' . htmlspecialchars($namaKelas) . '</span></div>
        <div class="info-row"><span class="info-label">Semester:</span> <span class="info-value">' . htmlspecialchars($namaSemester) . '</span></div>
        <div class="info-row"><span class="info-label">Tanggal Cetak:</span> <span class="info-value">' . date('d/m/Y H:i') . '</span></div>
    </div>
    
    <div class="stats">
        <div class="stats-row">
            <div class="stat-box">
                <div class="stat-number">Rp ' . number_format($totalSemuaTagihan, 0, ',', '.') . '</div>
                <div class="stat-label">Total Tagihan</div>
            </div>
            <div class="stat-box">
                <div class="stat-number">Rp ' . number_format($totalSemuaDibayar, 0, ',', '.') . '</div>
                <div class="stat-label">Total Dibayar</div>
            </div>
            <div class="stat-box">
                <div class="stat-number">Rp ' . number_format($totalSemuaSisa, 0, ',', '.') . '</div>
                <div class="stat-label">Total Sisa</div>
            </div>
        </div>
    </div>
    
    <table>
        <thead>
            <tr>
                <th width="4%">No</th>
                <th width="20%">Siswa</th>';

        foreach ($tagihanList as $tagihan) {
            $html .= '<th>' . htmlspecialchars($tagihan['nama']) . '</th>';
        }

        $html .= '
                <th width="15%">Total</th>
            </tr>
        </thead>
        <tbody>';

        $no = 1;
        foreach ($rekapData as $item) {
            $html .= '
            <tr>
                <td>' . $no++ . '</td>
                <td class="text-left">' . htmlspecialchars($item['siswa']['nama_siswa']) . '<br><small>' . htmlspecialchars($item['siswa']['nisn']) . '</small></td>';

            foreach ($item['tagihan'] as $tag) {
                $statusClass = 'status-belum';
                $statusText = 'Belum';
                if ($tag['status'] === 'Lunas') {
                    $statusClass = 'status-lunas';
                    $statusText = 'Lunas';
                } elseif ($tag['status'] === 'Cicil') {
                    $statusClass = 'status-cicil';
                    $statusText = 'Cicil';
                }

                $html .= '<td><span class="' . $statusClass . '">' . $statusText . '</span>';
                if ($tag['status'] === 'Cicil') {
                    $html .= '<br><small>' . number_format($tag['dibayar'], 0, ',', '.') . '/' . number_format($tag['nominal'], 0, ',', '.') . '</small>';
                } elseif ($tag['status'] === 'Belum') {
                    $html .= '<br><small>Rp ' . number_format($tag['nominal'], 0, ',', '.') . '</small>';
                }
                $html .= '</td>';
            }

            $html .= '
                <td><strong>Rp ' . number_format($item['total_tagihan'], 0, ',', '.') . '</strong><br><small>Bayar: Rp ' . number_format($item['total_dibayar'], 0, ',', '.') . '</small></td>
            </tr>';
        }

        // Total row
        $html .= '
            <tr class="total-row">
                <td colspan="2">TOTAL</td>';

        foreach ($tagihanList as $tagihan) {
            $totalPerTagihan = 0;
            foreach ($rekapData as $item) {
                foreach ($item['tagihan'] as $tag) {
                    if ($tag['nama_tagihan'] === $tagihan['nama']) {
                        $totalPerTagihan += $tag['nominal'];
                    }
                }
            }
            $html .= '<td>Rp ' . number_format($totalPerTagihan, 0, ',', '.') . '</td>';
        }

        $html .= '
                <td>Rp ' . number_format($totalSemuaTagihan, 0, ',', '.') . '<br><small>Bayar: Rp ' . number_format($totalSemuaDibayar, 0, ',', '.') . '</small></td>
            </tr>
        </tbody>
    </table>
    
    <div class="footer">
        <p><strong>Keterangan:</strong> Lunas = Sudah dibayar penuh | Cicil = Dibayar sebagian | Belum = Belum ada pembayaran</p>
        <p>Dicetak pada: ' . date('d/m/Y H:i:s') . '</p>
    </div>
</body>
</html>';

        return $html;
    }

    /**
     * Update Diskon Pembayaran Siswa (Bendahara)
     */
    public function updateDiskon()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/bendahara/pembayaran');
            exit;
        }

        // Validate id_tagihan & id_siswa exist
        $tagihan_id = $_POST['tagihan_id'] ?? 0;
        $id_siswa = $_POST['id_siswa'] ?? 0;
        $diskon = (int) ($_POST['diskon'] ?? 0);

        if (!$tagihan_id || !$id_siswa) {
            Flasher::setFlash('Error', 'Data tidak lengkap.', 'danger');
            header('Location: ' . BASEURL . '/bendahara/pembayaran');
            exit;
        }

        // Verify Tagihan exists
        $tagihan = $this->model('Pembayaran_model')->getTagihanById($tagihan_id);
        if (!$tagihan) {
            Flasher::setFlash('Error', 'Tagihan tidak ditemukan.', 'danger');
            header('Location: ' . BASEURL . '/bendahara/pembayaran');
            exit;
        }

        // Validate nominal vs diskon
        $nominal = (int) $tagihan['nominal_default'];
        if ($diskon > $nominal) {
            Flasher::setFlash('Gagal', 'Nominal diskon tidak boleh melebihi jumlah tagihan.', 'warning');
            header('Location: ' . BASEURL . '/bendahara/pembayaranTagihan/' . $tagihan_id);
            exit;
        }

        // Validate sisa
        $currentPay = $this->model('Pembayaran_model')->getPembayaranSiswa($tagihan_id, $id_siswa);
        $terbayar = $currentPay ? (int) $currentPay['total_terbayar'] : 0;

        if (($nominal - $diskon) < $terbayar) {
            Flasher::setFlash('Gagal', 'Diskon terlalu besar. Siswa sudah membayar Rp ' . number_format($terbayar, 0, ',', '.') . '.', 'warning');
            header('Location: ' . BASEURL . '/bendahara/pembayaranTagihan/' . $tagihan_id);
            exit;
        }

        // Ensure record exists before update (Bendahara can create mapping on the fly)
        $this->model('Pembayaran_model')->ensureTagihanSiswa($tagihan_id, $id_siswa, $nominal, $tagihan['jatuh_tempo']);

        // Update Diskon
        if ($this->model('Pembayaran_model')->updateDiskonSiswa($tagihan_id, $id_siswa, $diskon)) {
            // Kirim Notifikasi Diskon
            $this->sendDiskonNotification($tagihan_id, $id_siswa, $diskon);

            Flasher::setFlash('Berhasil', 'Diskon berhasil diperbarui dan notifikasi dikirim.', 'success');
        } else {
            Flasher::setFlash('Info', 'Tidak ada perubahan data diskon.', 'info');
        }

        header('Location: ' . BASEURL . '/bendahara/pembayaranTagihan/' . $tagihan_id);
        exit;
    }
}

