<?php
// File: app/controllers/RiwayatJurnalController.php

class RiwayatJurnalController extends Controller
{
    private $data = [];

    public function __construct()
    {
        // Guard akses - Allow both 'guru' and 'wali_kelas' role
        if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? null, ['guru', 'wali_kelas'])) {
            header('Location: ' . BASEURL . '/auth/login');
            exit;
        }

        // Data umum
        $this->data['daftar_semester'] = $this->model('TahunPelajaran_model')->getAllSemester();
        $this->data['judul'] = 'Riwayat Jurnal';
    }

    /**
     * Helper method untuk menampilkan sidebar yang sesuai dengan role
     */
    private function loadSidebar()
    {
        $role = $_SESSION['role'] ?? 'guru';
        if ($role === 'wali_kelas') {
            $this->view('templates/sidebar_walikelas', $this->data);
        } else {
            $this->view('templates/sidebar_guru', $this->data);
        }
    }

    /**
     * Halaman utama riwayat jurnal per mapel-kelas
     */
    public function index()
    {
        $this->data['judul'] = 'Riwayat Jurnal Mengajar';

        $id_guru = $_SESSION['id_ref'] ?? null;
        $id_semester_aktif = $_SESSION['id_semester_aktif'] ?? null;

        if (!$id_guru || !$id_semester_aktif) {
            error_log("RiwayatJurnalController::index() missing session keys");
            $this->data['jurnal_per_mapel'] = [];
            $this->renderView('riwayat_per_mapel_with_stats');
            return;
        }

        try {
            // Ambil data penugasan dengan statistik
            $this->data['jurnal_per_mapel'] = $this->getRiwayatPerMapelKelas($id_guru, $id_semester_aktif);

            error_log("DEBUG: Total mapel-kelas = " . count($this->data['jurnal_per_mapel']));
        } catch (Exception $e) {
            error_log("Error di riwayat(): " . $e->getMessage());
            $this->data['jurnal_per_mapel'] = [];
        }

        $this->renderView('riwayat_per_mapel_with_stats');
    }

    /**
     * Detail riwayat per penugasan spesifik
     */
    public function detail($id_penugasan)
    {
        $this->data['judul'] = 'Detail Riwayat Jurnal';

        $id_guru = $_SESSION['id_ref'] ?? null;
        $id_semester_aktif = $_SESSION['id_semester_aktif'] ?? null;

        if (!$id_guru || !$id_semester_aktif || !$id_penugasan) {
            $this->data['detail_jurnal'] = [];
            $this->data['detail_absensi_siswa'] = [];
            $this->data['nama_mapel'] = 'Data Tidak Ditemukan';
            $this->renderView('detail_riwayat_with_stats');
            return;
        }

        try {
            // Ambil info penugasan
            $penugasanInfo = $this->getPenugasanInfo($id_penugasan);
            $this->data['nama_mapel'] = $penugasanInfo['nama_mapel'] ?? 'Mapel Tidak Ditemukan';
            $this->data['nama_kelas'] = $penugasanInfo['nama_kelas'] ?? 'Kelas Tidak Ditemukan';
            $this->data['info_penugasan'] = $penugasanInfo;

            // Ambil detail jurnal per penugasan
            $this->data['detail_jurnal'] = $this->getDetailJurnalByPenugasan($id_penugasan);
            
            // Ambil detail absensi siswa per penugasan
            $this->data['detail_absensi_siswa'] = $this->getDetailAbsensiByPenugasan($id_penugasan);

        } catch (Exception $e) {
            error_log("Error di detail(): " . $e->getMessage());
            $this->data['detail_jurnal'] = [];
            $this->data['detail_absensi_siswa'] = [];
            $this->data['nama_mapel'] = 'Error Loading Data';
        }

        $this->renderView('detail_riwayat_with_stats');
    }

    /**
     * Cetak laporan per penugasan (mapel-kelas spesifik)
     */
    public function cetak($id_penugasan)
    {
        $id_guru = $_SESSION['id_ref'] ?? null;
        $id_semester = $_SESSION['id_semester_aktif'] ?? null;

        if (!$id_guru || !$id_semester || !$id_penugasan) {
            echo "Data tidak lengkap untuk mencetak laporan.";
            return;
        }

        try {
            // Ambil info penugasan
            $penugasanInfo = $this->getPenugasanInfo($id_penugasan);
            if (empty($penugasanInfo)) {
                echo "Penugasan tidak ditemukan.";
                return;
            }

            // Ambil data untuk cetak
            $this->data['meta'] = [
                'nama_mapel' => $penugasanInfo['nama_mapel'],
                'nama_kelas' => $penugasanInfo['nama_kelas'],
                'nama_guru' => $penugasanInfo['nama_guru'],
                'semester' => $_SESSION['nama_semester_aktif'] ?? 'Semester',
                'tp' => $_SESSION['nama_tp_aktif'] ?? 'TP',
                'tanggal' => date('d F Y')
            ];

            $this->data['id_penugasan'] = $id_penugasan;
            $this->data['rekap_siswa'] = $this->getRekapSiswaByPenugasan($id_penugasan);
            $this->data['rekap_pertemuan'] = $this->getRekapPertemuanByPenugasan($id_penugasan);
            $this->data['total_siswa'] = count($this->data['rekap_siswa']);

            // Render PDF atau HTML
            $wantPdf = isset($_GET['pdf']) && $_GET['pdf'] == 1;
            $html = $this->renderViewToString('cetak_mapel_kelas', $this->data);

            if ($wantPdf) {
                $this->generatePDF($html, $penugasanInfo['nama_mapel'] . '_' . $penugasanInfo['nama_kelas'], $id_penugasan);
                return;
            }

            // Tampilkan halaman cetak HTML
            header('Content-Type: text/html; charset=utf-8');
            echo $html;

        } catch (Exception $e) {
            error_log("Error di cetak(): " . $e->getMessage());
            echo "Terjadi kesalahan saat mencetak laporan: " . $e->getMessage();
        }
    }

    /**
     * Helper: Ambil riwayat per mapel-kelas dengan statistik
     */
    private function getRiwayatPerMapelKelas($id_guru, $id_semester)
    {
        $db = new Database();
        
        // Query untuk mendapatkan penugasan dengan statistik
        $sql = "
            SELECT 
                p.id_penugasan,
                m.id_mapel,
                m.nama_mapel,
                k.nama_kelas,
                COUNT(DISTINCT j.id_jurnal) as total_pertemuan,
                COUNT(DISTINCT siswa.id_siswa) as total_siswa,
                SUM(CASE WHEN a.status_kehadiran = 'H' THEN 1 ELSE 0 END) as total_hadir,
                SUM(CASE WHEN a.status_kehadiran = 'I' THEN 1 ELSE 0 END) as total_izin,
                SUM(CASE WHEN a.status_kehadiran = 'S' THEN 1 ELSE 0 END) as total_sakit,
                SUM(CASE WHEN a.status_kehadiran = 'A' THEN 1 ELSE 0 END) as total_alpha,
                COUNT(a.id_absensi) as total_absensi_records
            FROM penugasan p
            JOIN mapel m ON p.id_mapel = m.id_mapel
            JOIN kelas k ON p.id_kelas = k.id_kelas
            LEFT JOIN jurnal j ON p.id_penugasan = j.id_penugasan
            LEFT JOIN absensi a ON j.id_jurnal = a.id_jurnal
            LEFT JOIN keanggotaan_kelas kk ON k.id_kelas = kk.id_kelas
            LEFT JOIN siswa ON kk.id_siswa = siswa.id_siswa
            WHERE p.id_guru = :id_guru 
              AND p.id_semester = :id_semester
            GROUP BY p.id_penugasan, m.id_mapel, m.nama_mapel, k.nama_kelas
            ORDER BY m.nama_mapel
        ";

        $db->query($sql);
        $db->bind('id_guru', $id_guru);
        $db->bind('id_semester', $id_semester);
        $penugasan_list = $db->resultSet();

        $result = [];
        foreach ($penugasan_list as $penugasan) {
            $total_absensi = (int)($penugasan['total_absensi_records'] ?? 0);
            $total_hadir = (int)($penugasan['total_hadir'] ?? 0);
            $persentase = $total_absensi > 0 ? round(($total_hadir / $total_absensi) * 100, 1) : 0;

            // Ambil pertemuan untuk penugasan ini
            $pertemuan = $this->getPertemuanByPenugasan($penugasan['id_penugasan']);

            $result[] = [
                'id_penugasan' => $penugasan['id_penugasan'],
                'id_mapel' => $penugasan['id_mapel'],
                'nama_mapel' => $penugasan['nama_mapel'],
                'nama_kelas' => $penugasan['nama_kelas'],
                'pertemuan' => $pertemuan,
                'statistik' => [
                    'total_pertemuan' => (int)($penugasan['total_pertemuan'] ?? 0),
                    'total_siswa' => (int)($penugasan['total_siswa'] ?? 0),
                    'total_hadir' => $total_hadir,
                    'total_izin' => (int)($penugasan['total_izin'] ?? 0),
                    'total_sakit' => (int)($penugasan['total_sakit'] ?? 0),
                    'total_alpha' => (int)($penugasan['total_alpha'] ?? 0),
                    'total_absensi_records' => $total_absensi,
                    'persentase_kehadiran' => $persentase,
                ],
                'chart_data' => [
                    'hadir' => $total_hadir,
                    'izin' => (int)($penugasan['total_izin'] ?? 0),
                    'sakit' => (int)($penugasan['total_sakit'] ?? 0),
                    'alpha' => (int)($penugasan['total_alpha'] ?? 0),
                ]
            ];
        }

        return $result;
    }

    /**
     * Helper: Ambil pertemuan by penugasan
     */
    private function getPertemuanByPenugasan($id_penugasan)
    {
        $db = new Database();
        $sql = "
            SELECT pertemuan_ke, tanggal, topik_materi 
            FROM jurnal 
            WHERE id_penugasan = :id_penugasan 
            ORDER BY tanggal DESC, pertemuan_ke DESC
        ";
        
        $db->query($sql);
        $db->bind('id_penugasan', $id_penugasan);
        return $db->resultSet();
    }

    /**
     * Helper: Ambil info penugasan
     */
    private function getPenugasanInfo($id_penugasan)
    {
        $db = new Database();
        $sql = "
            SELECT 
                p.id_penugasan,
                p.id_kelas,
                m.nama_mapel, 
                k.nama_kelas, 
                g.nama_guru
            FROM penugasan p
            JOIN mapel m ON p.id_mapel = m.id_mapel
            JOIN kelas k ON p.id_kelas = k.id_kelas  
            JOIN guru g ON p.id_guru = g.id_guru
            WHERE p.id_penugasan = :id_penugasan
            LIMIT 1
        ";
        
        $db->query($sql);
        $db->bind('id_penugasan', $id_penugasan);
        return $db->single() ?: [];
    }

    /**
     * Helper: Ambil detail jurnal by penugasan
     */
    private function getDetailJurnalByPenugasan($id_penugasan)
    {
        $db = new Database();
        $sql = "
            SELECT 
                j.*,
                m.nama_mapel,
                k.nama_kelas
            FROM jurnal j
            JOIN penugasan p ON j.id_penugasan = p.id_penugasan
            JOIN mapel m ON p.id_mapel = m.id_mapel
            JOIN kelas k ON p.id_kelas = k.id_kelas
            WHERE j.id_penugasan = :id_penugasan
            ORDER BY j.tanggal DESC, j.pertemuan_ke DESC
        ";

        $db->query($sql);
        $db->bind('id_penugasan', $id_penugasan);
        return $db->resultSet();
    }

    /**
     * Helper: Ambil detail absensi siswa by penugasan
     */
    private function getDetailAbsensiByPenugasan($id_penugasan)
    {
        $db = new Database();
        $sql = "
            SELECT 
                s.id_siswa,
                s.nama_siswa,
                s.nisn,
                COUNT(DISTINCT j.id_jurnal) as total_pertemuan,
                SUM(CASE WHEN a.status_kehadiran = 'H' THEN 1 ELSE 0 END) as hadir,
                SUM(CASE WHEN a.status_kehadiran = 'I' THEN 1 ELSE 0 END) as izin,
                SUM(CASE WHEN a.status_kehadiran = 'S' THEN 1 ELSE 0 END) as sakit,
                SUM(CASE WHEN a.status_kehadiran = 'A' THEN 1 ELSE 0 END) as alpha,
                COUNT(a.id_absensi) as total_absensi
            FROM penugasan p
            JOIN kelas k ON p.id_kelas = k.id_kelas
            JOIN keanggotaan_kelas kk ON k.id_kelas = kk.id_kelas
            JOIN siswa s ON kk.id_siswa = s.id_siswa
            LEFT JOIN jurnal j ON p.id_penugasan = j.id_penugasan
            LEFT JOIN absensi a ON j.id_jurnal = a.id_jurnal AND s.id_siswa = a.id_siswa
            WHERE p.id_penugasan = :id_penugasan
            GROUP BY s.id_siswa, s.nama_siswa, s.nisn
            ORDER BY s.nama_siswa ASC
        ";

        $db->query($sql);
        $db->bind('id_penugasan', $id_penugasan);
        return $db->resultSet();
    }

    /**
     * Helper: Ambil rekap siswa untuk cetak
     */
    private function getRekapSiswaByPenugasan($id_penugasan)
    {
        return $this->getDetailAbsensiByPenugasan($id_penugasan);
    }

    /**
     * Helper: Ambil rekap pertemuan untuk cetak
     */
    private function getRekapPertemuanByPenugasan($id_penugasan)
    {
        $db = new Database();
        $sql = "
            SELECT 
                j.id_jurnal, 
                j.tanggal, 
                j.pertemuan_ke, 
                j.topik_materi,
                SUM(CASE WHEN a.status_kehadiran='H' THEN 1 ELSE 0 END) AS hadir,
                SUM(CASE WHEN a.status_kehadiran='I' THEN 1 ELSE 0 END) AS izin,
                SUM(CASE WHEN a.status_kehadiran='S' THEN 1 ELSE 0 END) AS sakit,
                SUM(CASE WHEN a.status_kehadiran='A' THEN 1 ELSE 0 END) AS alpha,
                COUNT(a.id_absensi) AS total
            FROM jurnal j
            LEFT JOIN absensi a ON j.id_jurnal = a.id_jurnal
            WHERE j.id_penugasan = :id_penugasan
            GROUP BY j.id_jurnal, j.tanggal, j.pertemuan_ke, j.topik_materi
            ORDER BY j.tanggal ASC, j.pertemuan_ke ASC
        ";

        $db->query($sql);
        $db->bind('id_penugasan', $id_penugasan);
        return $db->resultSet();
    }

    /**
     * Helper: Ambil absensi detail per siswa per pertemuan
     * Returns array indexed by id_siswa => [pertemuan_ke => status]
     */
    private function getAbsensiDetailPerSiswaPertemuan($id_penugasan)
    {
        $db = new Database();
        $sql = "
            SELECT 
                s.id_siswa,
                s.nama_siswa,
                s.nisn,
                j.pertemuan_ke,
                a.status_kehadiran
            FROM penugasan p
            JOIN kelas k ON p.id_kelas = k.id_kelas
            JOIN keanggotaan_kelas kk ON k.id_kelas = kk.id_kelas
            JOIN siswa s ON kk.id_siswa = s.id_siswa
            LEFT JOIN jurnal j ON p.id_penugasan = j.id_penugasan
            LEFT JOIN absensi a ON j.id_jurnal = a.id_jurnal AND s.id_siswa = a.id_siswa
            WHERE p.id_penugasan = :id_penugasan
            ORDER BY s.nama_siswa ASC, j.pertemuan_ke ASC
        ";

        $db->query($sql);
        $db->bind('id_penugasan', $id_penugasan);
        $rows = $db->resultSet();
        
        // Transform to indexed array: id_siswa => [pertemuan_ke => status, ...]
        $result = [];
        foreach ($rows as $row) {
            $idSiswa = $row['id_siswa'];
            if (!isset($result[$idSiswa])) {
                $result[$idSiswa] = [
                    'nama_siswa' => $row['nama_siswa'],
                    'nisn' => $row['nisn'],
                    'absensi' => []
                ];
            }
            if (!empty($row['pertemuan_ke'])) {
                $result[$idSiswa]['absensi'][$row['pertemuan_ke']] = $row['status_kehadiran'] ?? '-';
            }
        }
        
        return $result;
    }

    /**
     * Helper: render view dengan layout
     */
    private function renderView($viewName)
    {
        $this->view('templates/header', $this->data);
        $this->loadSidebar();
        $this->view('guru/' . $viewName, $this->data);
        $this->view('templates/footer', $this->data);
    }

    /**
     * Helper: render view ke string (untuk PDF)
     */
    private function renderViewToString($viewName, $data)
    {
        extract($data);
        ob_start();
        $viewPath = __DIR__ . "/../views/guru/$viewName.php";
        if (file_exists($viewPath)) {
            require $viewPath;
        } else {
            throw new Exception("View file tidak ditemukan: $viewPath");
        }
        return ob_get_clean();
    }

    /**
     * Helper: generate PDF
     */
    private function generatePDF($html, $filename, $id_penugasan = null)
    {
        $dompdfPath = __DIR__ . '/../core/dompdf/autoload.inc.php';
        
        if (!file_exists($dompdfPath)) {
            header('Content-Type: text/html; charset=utf-8');
            echo "<p>Library Dompdf tidak tersedia. Menampilkan preview HTML:</p>";
            echo $html;
            return;
        }

        require_once $dompdfPath;
        
        if (!class_exists('\\Dompdf\\Dompdf')) {
            header('Content-Type: text/html; charset=utf-8');
            echo "<p>Error: Class Dompdf tidak ditemukan.</p>";
            echo $html;
            return;
        }

        try {
            $dompdf = new \Dompdf\Dompdf([
                'isRemoteEnabled' => true, 
                'isHtml5ParserEnabled' => true,
                'defaultFont' => 'Arial'
            ]);
            
            // Add QR code for document validation
            if ($id_penugasan) {
                require_once APPROOT . '/app/core/PDFQRHelper.php';
                $html = PDFQRHelper::addQRToPDF($html, 'jurnal_mapel', $id_penugasan);
            }
            
            $dompdf->loadHtml($html, 'UTF-8');
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            
            $clean_filename = preg_replace('/[^A-Za-z0-9_-]/', '_', $filename) . '_' . date('Y-m-d') . '.pdf';
            $dompdf->stream($clean_filename, ['Attachment' => true]);
            
        } catch (Exception $e) {
            error_log("PDF Generation Error: " . $e->getMessage());
            header('Content-Type: text/html; charset=utf-8');
            echo "<p>Error generating PDF: " . htmlspecialchars($e->getMessage()) . "</p>";
            echo $html;
        }
    }

    /**
     * Download PDF Absensi per penugasan (terpisah dari jurnal)
     */
    public function downloadAbsensi($id_penugasan)
    {
        $id_guru = $_SESSION['id_ref'] ?? null;
        $id_semester = $_SESSION['id_semester_aktif'] ?? null;
        $id_tp = $_SESSION['id_tp_aktif'] ?? null;

        if (!$id_guru || !$id_semester || !$id_penugasan) {
            echo "Data tidak lengkap untuk download.";
            return;
        }

        try {
            // Ambil info penugasan
            $penugasanInfo = $this->getPenugasanInfo($id_penugasan);
            if (empty($penugasanInfo)) {
                echo "Penugasan tidak ditemukan.";
                return;
            }

            // Ambil pengaturan rapor untuk kop - coba dari kelas dulu, lalu guru
            $pengaturanRapor = $this->getKopFromKelas($penugasanInfo['id_kelas'] ?? null, $id_tp);
            if (empty($pengaturanRapor)) {
                $pengaturanRapor = $this->model('PengaturanRapor_model')->getPengaturanByGuru($id_guru, $id_tp);
            }
            
            // Data untuk PDF
            $meta = [
                'nama_mapel' => $penugasanInfo['nama_mapel'],
                'nama_kelas' => $penugasanInfo['nama_kelas'],
                'nama_guru' => $penugasanInfo['nama_guru'],
                'semester' => $_SESSION['nama_semester_aktif'] ?? 'Semester',
                'tanggal' => date('d F Y')
            ];

            $rekap_siswa = $this->getRekapSiswaByPenugasan($id_penugasan);
            $rekap_pertemuan = $this->getRekapPertemuanByPenugasan($id_penugasan);
            $detail_absensi = $this->getAbsensiDetailPerSiswaPertemuan($id_penugasan);

            // Generate HTML
            $html = $this->buildAbsensiPDFHtml($meta, $rekap_siswa, $rekap_pertemuan, $pengaturanRapor, $id_penugasan, $detail_absensi);

            // Generate PDF - Landscape
            $this->generatePDFWithQRLandscape($html, 'Absensi_' . $meta['nama_mapel'] . '_' . $meta['nama_kelas'], 'absensi', $id_penugasan);

        } catch (Exception $e) {
            error_log("Error di downloadAbsensi(): " . $e->getMessage());
            echo "Terjadi kesalahan: " . $e->getMessage();
        }
    }

    /**
     * Download PDF Jurnal per penugasan (terpisah dari absensi)
     */
    public function downloadJurnal($id_penugasan)
    {
        $id_guru = $_SESSION['id_ref'] ?? null;
        $id_semester = $_SESSION['id_semester_aktif'] ?? null;
        $id_tp = $_SESSION['id_tp_aktif'] ?? null;

        if (!$id_guru || !$id_semester || !$id_penugasan) {
            echo "Data tidak lengkap untuk download.";
            return;
        }

        try {
            // Ambil info penugasan
            $penugasanInfo = $this->getPenugasanInfo($id_penugasan);
            if (empty($penugasanInfo)) {
                echo "Penugasan tidak ditemukan.";
                return;
            }

            // Ambil pengaturan rapor untuk kop - coba dari kelas dulu, lalu guru
            $pengaturanRapor = $this->getKopFromKelas($penugasanInfo['id_kelas'] ?? null, $id_tp);
            if (empty($pengaturanRapor)) {
                $pengaturanRapor = $this->model('PengaturanRapor_model')->getPengaturanByGuru($id_guru, $id_tp);
            }
            
            // Data untuk PDF
            $meta = [
                'nama_mapel' => $penugasanInfo['nama_mapel'],
                'nama_kelas' => $penugasanInfo['nama_kelas'],
                'nama_guru' => $penugasanInfo['nama_guru'],
                'semester' => $_SESSION['nama_semester_aktif'] ?? 'Semester',
                'tanggal' => date('d F Y')
            ];

            $rekap_pertemuan = $this->getRekapPertemuanByPenugasan($id_penugasan);
            $detail_jurnal = $this->getDetailJurnalByPenugasan($id_penugasan);

            // Generate HTML
            $html = $this->buildJurnalPDFHtml($meta, $rekap_pertemuan, $detail_jurnal, $pengaturanRapor, $id_penugasan);

            // Generate PDF
            $this->generatePDFWithQR($html, 'Jurnal_' . $meta['nama_mapel'] . '_' . $meta['nama_kelas'], 'jurnal', $id_penugasan);

        } catch (Exception $e) {
            error_log("Error di downloadJurnal(): " . $e->getMessage());
            echo "Terjadi kesalahan: " . $e->getMessage();
        }
    }

    /**
     * Build HTML untuk PDF Absensi dengan Kop - Landscape dengan kolom per pertemuan
     */
    private function buildAbsensiPDFHtml($meta, $rekap_siswa, $rekap_pertemuan, $pengaturanRapor, $id_penugasan, $detail_absensi = [])
    {
        // Kop header
        $kopHTML = '';
        if (!empty($pengaturanRapor['kop_rapor'])) {
            $kopPath = 'public/img/kop/' . $pengaturanRapor['kop_rapor'];
            if (file_exists($kopPath)) {
                $imageData = base64_encode(file_get_contents($kopPath));
                $imageType = pathinfo($kopPath, PATHINFO_EXTENSION);
                $imageSrc = 'data:image/' . $imageType . ';base64,' . $imageData;
                $kopHTML = '<div style="text-align:center; margin-bottom:10px;"><img src="' . $imageSrc . '" style="max-width:100%; height:auto; max-height:80px;"></div>';
            }
        }

        // Ambil daftar pertemuan
        $pertemuanList = [];
        foreach ($rekap_pertemuan as $p) {
            $pertemuanList[] = (int)$p['pertemuan_ke'];
        }
        sort($pertemuanList);
        $totalPertemuan = count($pertemuanList);

        // Waktu cetak dalam format Indonesia
        $waktuCetak = $this->formatWaktuIndonesia();
        $namaPencetak = $_SESSION['nama_lengkap'] ?? 'Pengguna';

        $html = '<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Laporan Kehadiran Siswa - ' . htmlspecialchars($meta['nama_mapel']) . '</title>
<style>
    @page { size: A4 landscape; margin: 10mm; }
    body { font-family: Arial, sans-serif; font-size: 10px; line-height: 1.3; color: #333; margin: 0; padding: 0; }
    .kop { text-align: center; margin-bottom: 10px; }
    .title { text-align: center; font-size: 14px; font-weight: bold; margin: 10px 0 12px; text-transform: uppercase; }
    
    /* Meta info dengan tabel untuk alignment rapi */
    .meta-table { margin-bottom: 12px; border: none; width: auto; }
    .meta-table td { border: none; padding: 2px 5px 2px 0; font-size: 10px; vertical-align: top; }
    .meta-table .label { font-weight: bold; white-space: nowrap; width: 120px; }
    .meta-table .colon { width: 10px; text-align: center; }
    .meta-table .value { }
    
    /* Tabel utama - auto width */
    table.data-table { width: 100%; border-collapse: collapse; margin: 8px 0; table-layout: auto; }
    table.data-table th, table.data-table td { border: 1px solid #333; padding: 4px 6px; }
    table.data-table th { background: #e0e0e0; font-weight: bold; text-align: center; font-size: 9px; white-space: nowrap; }
    table.data-table td { font-size: 9px; }
    table.data-table .center { text-align: center; }
    table.data-table .right { text-align: right; }
    table.data-table .left { text-align: left; }
    table.data-table .nama-col { white-space: nowrap; }
    table.data-table tfoot td { font-weight: bold; background: #f0f0f0; }
    
    /* Kolom pertemuan - auto fit */
    table.data-table .pert-col { width: auto; min-width: 22px; text-align: center; }
    table.data-table .rekap-col { width: auto; min-width: 25px; text-align: center; }
    
    /* Warna untuk status kehadiran */
    .status-h { color: #16a34a; font-weight: bold; } /* Hijau - Hadir */
    .status-i { color: #2563eb; font-weight: bold; } /* Biru - Izin */
    .status-s { color: #ea580c; font-weight: bold; } /* Orange - Sakit */
    .status-a { color: #dc2626; font-weight: bold; } /* Merah - Alpha */
    .status-empty { color: #9ca3af; } /* Abu-abu - Kosong */
    
    .doc-info-box {
        margin-top: 15px;
        border: 1px solid #333;
        padding: 10px 12px;
        background: #f9f9f9;
        font-size: 9px;
    }
    .doc-info-title {
        font-weight: bold;
        font-size: 10px;
        margin-bottom: 6px;
        border-bottom: 1px solid #ccc;
        padding-bottom: 4px;
    }
    .doc-info-row {
        margin-bottom: 4px;
        line-height: 1.5;
    }
    .legend {
        margin-top: 10px;
        font-size: 9px;
    }
    .legend-item {
        display: inline-block;
        margin-right: 20px;
    }
</style>
</head>
<body>
' . $kopHTML . '
<div class="title">LAPORAN KEHADIRAN SISWA</div>

<table class="meta-table"><tr><td class="label">Mata Pelajaran</td><td class="colon">:</td><td class="value">' . htmlspecialchars($meta['nama_mapel']) . '</td></tr><tr><td class="label">Kelas</td><td class="colon">:</td><td class="value">' . htmlspecialchars($meta['nama_kelas']) . '</td></tr><tr><td class="label">Nama Guru</td><td class="colon">:</td><td class="value">' . htmlspecialchars($meta['nama_guru']) . '</td></tr><tr><td class="label">Semester</td><td class="colon">:</td><td class="value">' . htmlspecialchars($meta['semester']) . '</td></tr><tr><td class="label">Total Pertemuan</td><td class="colon">:</td><td class="value">' . $totalPertemuan . ' kali</td></tr>
</table>

<table class="data-table">
    <thead>
        <tr>
            <th rowspan="2">No</th>
            <th rowspan="2" class="nama-col">Nama Siswa</th>
            <th rowspan="2">NISN</th>
            <th colspan="' . $totalPertemuan . '">Pertemuan Ke-</th>
            <th colspan="5">Rekap</th>
        </tr>
        <tr>';
        
        // Header pertemuan
        foreach ($pertemuanList as $pert) {
            $html .= '<th class="pert-col">' . $pert . '</th>';
        }
        
        $html .= '
            <th class="rekap-col status-h">H</th>
            <th class="rekap-col status-i">I</th>
            <th class="rekap-col status-s">S</th>
            <th class="rekap-col status-a">A</th>
            <th class="rekap-col">%</th>
        </tr>
    </thead>
    <tbody>';

        $no = 1;
        $totalH = $totalI = $totalS = $totalA = 0;
        
        foreach ($detail_absensi as $idSiswa => $siswaData) {
            $html .= '<tr>
                <td class="center">' . $no++ . '</td>
                <td class="left nama-col">' . htmlspecialchars($siswaData['nama_siswa'] ?? '-') . '</td>
                <td class="center">' . htmlspecialchars($siswaData['nisn'] ?? '-') . '</td>';
            
            // Kolom per pertemuan
            $h = $i = $s = $a = 0;
            foreach ($pertemuanList as $pert) {
                $status = $siswaData['absensi'][$pert] ?? '-';
                $statusClass = 'status-empty';
                
                switch (strtoupper($status)) {
                    case 'H': $statusClass = 'status-h'; $h++; break;
                    case 'I': $statusClass = 'status-i'; $i++; break;
                    case 'S': $statusClass = 'status-s'; $s++; break;
                    case 'A': $statusClass = 'status-a'; $a++; break;
                }
                
                $html .= '<td class="center pert-col ' . $statusClass . '">' . strtoupper($status) . '</td>';
            }
            
            $totalH += $h;
            $totalI += $i;
            $totalS += $s;
            $totalA += $a;
            
            $totalAbsen = $h + $i + $s + $a;
            $pct = $totalAbsen > 0 ? number_format(($h / $totalAbsen) * 100, 0) : '0';
            
            $html .= '
                <td class="center rekap-col status-h">' . $h . '</td>
                <td class="center rekap-col status-i">' . $i . '</td>
                <td class="center rekap-col status-s">' . $s . '</td>
                <td class="center rekap-col status-a">' . $a . '</td>
                <td class="center rekap-col">' . $pct . '%</td>
            </tr>';
        }

        $grandTotal = $totalH + $totalI + $totalS + $totalA;
        $grandPct = $grandTotal > 0 ? number_format(($totalH / $grandTotal) * 100, 0) : '0';

        $html .= '</tbody>
    <tfoot>
        <tr>
            <td colspan="' . (3 + $totalPertemuan) . '" class="right"><strong>TOTAL</strong></td>
            <td class="center rekap-col status-h">' . $totalH . '</td>
            <td class="center rekap-col status-i">' . $totalI . '</td>
            <td class="center rekap-col status-s">' . $totalS . '</td>
            <td class="center rekap-col status-a">' . $totalA . '</td>
            <td class="center rekap-col">' . $grandPct . '%</td>
        </tr>
    </tfoot>
</table>

<div class="legend">
    <span class="legend-item"><span class="status-h">H</span> = Hadir</span>
    <span class="legend-item"><span class="status-i">I</span> = Izin</span>
    <span class="legend-item"><span class="status-s">S</span> = Sakit</span>
    <span class="legend-item"><span class="status-a">A</span> = Alpha/Tanpa Keterangan</span>
</div>

<div class="doc-info-box">
    <div class="doc-info-title">Informasi Dokumen:</div>
    <div class="doc-info-row">Dokumen ini dicetak pada ' . $waktuCetak . ' oleh ' . htmlspecialchars($namaPencetak) . '</div>
    <div class="doc-info-row">Dokumen ini sah dan terverifikasi dengan QR Code di pojok kanan bawah halaman.</div>
</div>
</body>
</html>';

        return $html;
    }

    /**
     * Build HTML untuk PDF Jurnal dengan Kop
     */
    private function buildJurnalPDFHtml($meta, $rekap_pertemuan, $detail_jurnal, $pengaturanRapor, $id_penugasan)
    {
        // Kop header
        $kopHTML = '';
        if (!empty($pengaturanRapor['kop_rapor'])) {
            $kopPath = 'public/img/kop/' . $pengaturanRapor['kop_rapor'];
            if (file_exists($kopPath)) {
                $imageData = base64_encode(file_get_contents($kopPath));
                $imageType = pathinfo($kopPath, PATHINFO_EXTENSION);
                $imageSrc = 'data:image/' . $imageType . ';base64,' . $imageData;
                $kopHTML = '<div style="text-align:center; margin-bottom:15px;"><img src="' . $imageSrc . '" style="max-width:100%; height:auto; max-height:100px;"></div>';
            }
        }

        // Waktu cetak dalam format Indonesia
        $waktuCetak = $this->formatWaktuIndonesia();
        $namaPencetak = $_SESSION['nama_lengkap'] ?? 'Pengguna';

        $html = '<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Laporan Jurnal Mengajar - ' . htmlspecialchars($meta['nama_mapel']) . '</title>
<style>
    @page { size: A4; margin: 15mm; }
    body { font-family: Arial, sans-serif; font-size: 11px; line-height: 1.4; color: #333; }
    .kop { text-align: center; margin-bottom: 15px; }
    .title { text-align: center; font-size: 14px; font-weight: bold; margin: 15px 0 10px; text-transform: uppercase; }
    .meta { margin-bottom: 15px; }
    .meta-row { margin-bottom: 3px; }
    .meta-label { display: inline-block; width: 130px; font-weight: bold; }
    table { width: 100%; border-collapse: collapse; margin: 10px 0; }
    th, td { border: 1px solid #333; padding: 6px 8px; }
    th { background: #f0f0f0; font-weight: bold; text-align: center; font-size: 10px; }
    td { font-size: 10px; }
    .center { text-align: center; }
    .right { text-align: right; }
    .doc-info-box {
        margin-top: 25px;
        border: 1px solid #333;
        padding: 12px 15px;
        background: #f9f9f9;
        font-size: 10px;
    }
    .doc-info-title {
        font-weight: bold;
        font-size: 11px;
        margin-bottom: 8px;
        border-bottom: 1px solid #ccc;
        padding-bottom: 5px;
    }
    .doc-info-row {
        margin-bottom: 4px;
        line-height: 1.5;
    }
</style>
</head>
<body>
' . $kopHTML . '
<div class="title">LAPORAN JURNAL MENGAJAR</div>

<div class="meta">
    <div class="meta-row"><span class="meta-label">Mata Pelajaran</span>: ' . htmlspecialchars($meta['nama_mapel']) . '</div>
    <div class="meta-row"><span class="meta-label">Kelas</span>: ' . htmlspecialchars($meta['nama_kelas']) . '</div>
    <div class="meta-row"><span class="meta-label">Nama Guru</span>: ' . htmlspecialchars($meta['nama_guru']) . '</div>
    <div class="meta-row"><span class="meta-label">Semester</span>: ' . htmlspecialchars($meta['semester']) . '</div>
    <div class="meta-row"><span class="meta-label">Total Pertemuan</span>: ' . count($rekap_pertemuan) . ' kali</div>
</div>

<table>
    <thead>
        <tr>
            <th style="width:40px;">Pert.</th>
            <th style="width:80px;">Tanggal</th>
            <th>Topik/Materi Pembelajaran</th>
            <th style="width:30px;">H</th>
            <th style="width:30px;">I</th>
            <th style="width:30px;">S</th>
            <th style="width:30px;">A</th>
        </tr>
    </thead>
    <tbody>';

        $totalH = $totalI = $totalS = $totalA = 0;
        foreach ($rekap_pertemuan as $row) {
            $hadir = (int)($row['hadir'] ?? 0);
            $izin = (int)($row['izin'] ?? 0);
            $sakit = (int)($row['sakit'] ?? 0);
            $alpha = (int)($row['alpha'] ?? 0);
            
            $totalH += $hadir;
            $totalI += $izin;
            $totalS += $sakit;
            $totalA += $alpha;

            $html .= '<tr>
                <td class="center">' . (int)($row['pertemuan_ke'] ?? 0) . '</td>
                <td class="center">' . (!empty($row['tanggal']) ? date('d-m-Y', strtotime($row['tanggal'])) : '-') . '</td>
                <td>' . htmlspecialchars($row['topik_materi'] ?? 'Tidak ada keterangan') . '</td>
                <td class="center">' . $hadir . '</td>
                <td class="center">' . $izin . '</td>
                <td class="center">' . $sakit . '</td>
                <td class="center">' . $alpha . '</td>
            </tr>';
        }

        $html .= '</tbody>
    <tfoot>
        <tr>
            <td colspan="3" class="right"><strong>TOTAL</strong></td>
            <td class="center"><strong>' . $totalH . '</strong></td>
            <td class="center"><strong>' . $totalI . '</strong></td>
            <td class="center"><strong>' . $totalS . '</strong></td>
            <td class="center"><strong>' . $totalA . '</strong></td>
        </tr>
    </tfoot>
</table>

<div class="doc-info-box">
    <div class="doc-info-title">Informasi Dokumen:</div>
    <div class="doc-info-row">Dokumen ini dicetak pada ' . $waktuCetak . ' oleh ' . htmlspecialchars($namaPencetak) . '</div>
    <div class="doc-info-row">Dokumen ini sah dan terverifikasi dengan QR Code di pojok kanan bawah halaman.</div>
</div>
</body>
</html>';

        return $html;
    }

    /**
     * Helper: Ambil kop rapor berdasarkan kelas (via wali_kelas)
     */
    private function getKopFromKelas($id_kelas, $id_tp)
    {
        if (empty($id_kelas) || empty($id_tp)) {
            return null;
        }
        
        try {
            $db = new Database;
            $db->query("
                SELECT pr.kop_rapor 
                FROM pengaturan_rapor pr
                INNER JOIN wali_kelas wk ON pr.id_guru = wk.id_guru AND pr.id_tp = wk.id_tp
                WHERE wk.id_kelas = :id_kelas AND wk.id_tp = :id_tp
                LIMIT 1
            ");
            $db->bind(':id_kelas', $id_kelas);
            $db->bind(':id_tp', $id_tp);
            return $db->single();
        } catch (Exception $e) {
            error_log("Error getKopFromKelas: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Helper: Generate PDF dengan QR Code
     */
    private function generatePDFWithQR($html, $filename, $docType, $id_penugasan)
    {
        $dompdfPath = __DIR__ . '/../core/dompdf/autoload.inc.php';
        
        if (!file_exists($dompdfPath)) {
            header('Content-Type: text/html; charset=utf-8');
            echo $html;
            return;
        }

        require_once $dompdfPath;

        try {
            $dompdf = new \Dompdf\Dompdf([
                'isRemoteEnabled' => true, 
                'isHtml5ParserEnabled' => true,
                'defaultFont' => 'Arial'
            ]);
            
            // Add QR code for document validation
            require_once APPROOT . '/app/core/PDFQRHelper.php';
            $html = PDFQRHelper::addQRToPDF($html, $docType . '_penugasan', $id_penugasan);
            
            $dompdf->loadHtml($html, 'UTF-8');
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            
            $clean_filename = preg_replace('/[^A-Za-z0-9_-]/', '_', $filename) . '_' . date('Y-m-d') . '.pdf';
            $dompdf->stream($clean_filename, ['Attachment' => true]);
            
        } catch (Exception $e) {
            error_log("PDF Generation Error: " . $e->getMessage());
            header('Content-Type: text/html; charset=utf-8');
            echo $html;
        }
    }

    /**
     * Helper: Generate PDF dengan QR Code - Landscape Mode
     */
    private function generatePDFWithQRLandscape($html, $filename, $docType, $id_penugasan)
    {
        $dompdfPath = __DIR__ . '/../core/dompdf/autoload.inc.php';
        
        if (!file_exists($dompdfPath)) {
            header('Content-Type: text/html; charset=utf-8');
            echo $html;
            return;
        }

        require_once $dompdfPath;

        try {
            $dompdf = new \Dompdf\Dompdf([
                'isRemoteEnabled' => true, 
                'isHtml5ParserEnabled' => true,
                'defaultFont' => 'Arial'
            ]);
            
            // Add QR code for document validation
            require_once APPROOT . '/app/core/PDFQRHelper.php';
            $html = PDFQRHelper::addQRToPDF($html, $docType . '_penugasan', $id_penugasan);
            
            $dompdf->loadHtml($html, 'UTF-8');
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();
            
            $clean_filename = preg_replace('/[^A-Za-z0-9_-]/', '_', $filename) . '_' . date('Y-m-d') . '.pdf';
            $dompdf->stream($clean_filename, ['Attachment' => true]);
            
        } catch (Exception $e) {
            error_log("PDF Generation Error: " . $e->getMessage());
            header('Content-Type: text/html; charset=utf-8');
            echo $html;
        }
    }

    /**
     * Helper: Format waktu dalam format Indonesia
     * Output: "05 Desember 2025 pukul 17:36 WIB"
     */
    private function formatWaktuIndonesia()
    {
        $bulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        
        $tanggal = date('d');
        $bulanIdx = (int)date('m');
        $tahun = date('Y');
        $waktu = date('H:i');
        
        return $tanggal . ' ' . $bulan[$bulanIdx] . ' ' . $tahun . ' pukul ' . $waktu . ' WIB';
    }
}
?>
