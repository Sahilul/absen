<?php
// File: app/controllers/GuruController.php

class GuruController extends Controller
{
    private $data = [];

    public function __construct()
    {
        // Guard akses - Allow both 'guru' and 'wali_kelas' role
        if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? null, ['guru', 'wali_kelas'])) {
            header('Location: ' . BASEURL . '/auth/login');
            exit;
        }

        // Clear old flash messages yang mungkin tertinggal dari login
        if (isset($_SESSION['flash']) && strpos($_SESSION['flash']['pesan'] ?? '', 'Role tidak dikenal') !== false) {
            unset($_SESSION['flash']);
        }

        // Data umum
        $this->data['daftar_semester'] = $this->model('TahunPelajaran_model')->getAllSemester();
        $this->data['judul'] = 'Guru';
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
     * Default -> lempar ke dashboard
     */
    public function index()
    {
        error_log("GuruController::index() dipanggil");
        $this->dashboard();
    }

    /**
     * Dashboard - Dengan statistik dan daftar kelas mengajar
     */
    public function dashboard()
    {
        error_log("GuruController::dashboard() dipanggil");
        $this->data['judul'] = 'Dashboard Guru';

        $id_guru = $_SESSION['id_ref'] ?? null;
        $id_semester_aktif = $_SESSION['id_semester_aktif'] ?? null;

        // Ambil jadwal mengajar
        $this->data['jadwal_mengajar'] = [];
        if ($id_guru && $id_semester_aktif) {
            $jadwal = $this->model('Penugasan_model')->getPenugasanByGuru($id_guru, $id_semester_aktif);

            // Tambahkan info jumlah nilai STS dan SAS per penugasan
            $nilaiModel = $this->model('Nilai_model');
            foreach ($jadwal as &$item) {
                $id_penugasan = $item['id_penugasan'];
                $item['jumlah_nilai_sts'] = $nilaiModel->countNilaiByPenugasanAndJenis($id_penugasan, 'sts');
                $item['jumlah_nilai_sas'] = $nilaiModel->countNilaiByPenugasanAndJenis($id_penugasan, 'sas');
            }

            $this->data['jadwal_mengajar'] = $jadwal;
        }

        // Hitung statistik
        $this->data['total_penugasan'] = $this->getTotalPenugasan($id_guru, $id_semester_aktif);
        $this->data['total_hari_mengajar'] = $this->getTotalHariMengajar($id_guru, $id_semester_aktif);
        $this->data['kelas_mapel_info'] = $this->getKelasMapelInfo($id_guru, $id_semester_aktif);

        $this->view('templates/header', $this->data);
        $this->loadSidebar();
        $this->view('guru/dashboard', $this->data);
        $this->view('templates/footer', $this->data);
    }

    /**
     * Rekap Absen: Ringkasan kehadiran per mapel-kelas untuk guru pada semester aktif.
     * Menampilkan total pertemuan (jurnal), total H/I/S/A dan persentase hadir.
     */
    public function rekapAbsen()
    {
        $this->data['judul'] = 'Rekap Absensi';
        $id_guru = $_SESSION['id_ref'] ?? null;
        $id_semester = $_SESSION['id_semester_aktif'] ?? null;
        if (!$id_guru || !$id_semester) {
            $this->view('templates/header', $this->data);
            $this->loadSidebar();
            echo '<div class="p-6">Sesi tidak valid.</div>';
            $this->view('templates/footer', $this->data);
            return;
        }

        $rekap = $this->getRekapAbsenMapelKelas($id_guru, $id_semester);
        $this->data['rekap'] = $rekap;

        $this->view('templates/header', $this->data);
        $this->loadSidebar();
        $this->view('guru/rekap_absen', $this->data);
        $this->view('templates/footer', $this->data);
    }

    /**
     * Download PDF rekap absensi per mapel-kelas.
     */
    public function downloadRekapAbsenPDF($id_penugasan = null)
    {
        $id_guru = $_SESSION['id_ref'] ?? null;
        $id_semester = $_SESSION['id_semester_aktif'] ?? null;
        if (!$id_guru || !$id_semester) {
            echo 'Sesi tidak valid.';
            return;
        }
        // Allow filter by penugasan (single mapel-kelas)
        if (!$id_penugasan) {
            $id_penugasan = $_GET['id_penugasan'] ?? null;
        }
        if ($id_penugasan) {
            $single = $this->getRekapAbsenForPenugasan($id_guru, $id_semester, (int) $id_penugasan);
            $rekap = $single ? [$single] : [];
        } else {
            $rekap = $this->getRekapAbsenMapelKelas($id_guru, $id_semester);
        }
        if (empty($rekap)) {
            echo 'Tidak ada data untuk dicetak.';
            return;
        }

        // Build minimal HTML
        ob_start();
        ?>
        <html>

        <head>
            <meta charset="utf-8">
            <style>
                body {
                    font-family: Arial, Helvetica, sans-serif;
                    font-size: 12px;
                    color: #222;
                }

                h2 {
                    margin: 0 0 12px;
                    font-size: 16px;
                    text-align: center;
                }

                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-top: 10px;
                }

                th,
                td {
                    border: 1px solid #999;
                    padding: 6px;
                    font-size: 11px;
                }

                th {
                    background: #eee;
                }

                .num {
                    text-align: right;
                }
            </style>
        </head>

        <body>
            <h2>Rekap Absensi Semester <?= htmlspecialchars($_SESSION['nama_semester_aktif'] ?? '') ?></h2>
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Mapel</th>
                        <th>Kelas</th>
                        <th>Pertemuan</th>
                        <th>H</th>
                        <th>I</th>
                        <th>S</th>
                        <th>A</th>
                        <th>% Hadir</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1;
                    foreach ($rekap as $r):
                        $totalPert = (int) $r['total_pertemuan'];
                        $pct = $totalPert > 0 ? round(($r['hadir'] / $totalPert) * 100, 1) : 0; ?>
                        <tr>
                            <td class="num"><?= $no++ ?></td>
                            <td><?= htmlspecialchars($r['nama_mapel']) ?></td>
                            <td><?= htmlspecialchars($r['nama_kelas']) ?></td>
                            <td class="num"><?= $totalPert ?></td>
                            <td class="num"><?= $r['hadir'] ?></td>
                            <td class="num"><?= $r['izin'] ?></td>
                            <td class="num"><?= $r['sakit'] ?></td>
                            <td class="num"><?= $r['alpha'] ?></td>
                            <td class="num"><?= $pct ?>%</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div style="margin-top:15px;font-size:10px;">Dicetak: <?= date('d/m/Y H:i') ?></div>
        </body>

        </html>
        <?php
        $html = ob_get_clean();

        // Dompdf
        require_once APPROOT . '/app/core/dompdf/autoload.inc.php';
        try {
            $dompdf = new Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            $dompdf->stream('rekap_absensi.pdf', ['Attachment' => false]);
        } catch (Exception $e) {
            echo 'Gagal generate PDF: ' . htmlspecialchars($e->getMessage());
        }
    }

    /**
     * Query rekap absensi per mapel-kelas.
     */
    private function getRekapAbsenMapelKelas($id_guru, $id_semester)
    {
        try {
            $db = new Database();
            $sql = "SELECT 
                        m.id_mapel,
                        m.nama_mapel,
                        k.id_kelas,
                        k.nama_kelas,
                        COUNT(DISTINCT j.id_jurnal) AS total_pertemuan,
                        SUM(CASE WHEN a.status_kehadiran='H' THEN 1 ELSE 0 END) AS hadir,
                        SUM(CASE WHEN a.status_kehadiran='I' THEN 1 ELSE 0 END) AS izin,
                        SUM(CASE WHEN a.status_kehadiran='S' THEN 1 ELSE 0 END) AS sakit,
                        SUM(CASE WHEN a.status_kehadiran='A' OR a.status_kehadiran IS NULL THEN 1 ELSE 0 END) AS alpha
                    FROM penugasan p
                    JOIN mapel m ON p.id_mapel = m.id_mapel
                    JOIN kelas k ON p.id_kelas = k.id_kelas
                    LEFT JOIN jurnal j ON p.id_penugasan = j.id_penugasan
                    LEFT JOIN absensi a ON j.id_jurnal = a.id_jurnal
                    WHERE p.id_guru = :id_guru AND p.id_semester = :id_semester
                    GROUP BY m.id_mapel, k.id_kelas
                    ORDER BY m.nama_mapel, k.nama_kelas";
            $db->query($sql);
            $db->bind('id_guru', $id_guru);
            $db->bind('id_semester', $id_semester);
            return $db->resultSet();
        } catch (Exception $e) {
            error_log('Error getRekapAbsenMapelKelas: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Rekap untuk satu penugasan (mapel-kelas tertentu)
     */
    private function getRekapAbsenForPenugasan($id_guru, $id_semester, $id_penugasan)
    {
        try {
            $db = new Database();
            $sql = "SELECT 
                        m.id_mapel,
                        m.nama_mapel,
                        k.id_kelas,
                        k.nama_kelas,
                        COUNT(DISTINCT j.id_jurnal) AS total_pertemuan,
                        SUM(CASE WHEN a.status_kehadiran='H' THEN 1 ELSE 0 END) AS hadir,
                        SUM(CASE WHEN a.status_kehadiran='I' THEN 1 ELSE 0 END) AS izin,
                        SUM(CASE WHEN a.status_kehadiran='S' THEN 1 ELSE 0 END) AS sakit,
                        SUM(CASE WHEN a.status_kehadiran='A' THEN 1 ELSE 0 END) AS alpha,
                        COUNT(a.id_absensi) AS total_row
                    FROM penugasan p
                    JOIN mapel m ON p.id_mapel = m.id_mapel
                    JOIN kelas k ON p.id_kelas = k.id_kelas
                    LEFT JOIN jurnal j ON p.id_penugasan = j.id_penugasan
                    LEFT JOIN absensi a ON j.id_jurnal = a.id_jurnal
                    WHERE p.id_penugasan = :id_penugasan AND p.id_guru = :id_guru AND p.id_semester = :id_semester
                    GROUP BY m.id_mapel, k.id_kelas";
            $db->query($sql);
            $db->bind('id_penugasan', $id_penugasan);
            $db->bind('id_guru', $id_guru);
            $db->bind('id_semester', $id_semester);
            return $db->single();
        } catch (Exception $e) {
            error_log('Error getRekapAbsenForPenugasan: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Halaman ringkas rekap untuk satu penugasan (langsung dari Dashboard).
     */
    public function rekapAbsenDetail($id_penugasan = null)
    {
        $this->data['judul'] = 'Rekap Absensi';
        $id_guru = $_SESSION["id_ref"] ?? null;
        $id_semester = $_SESSION['id_semester_aktif'] ?? null;
        if (!$id_guru || !$id_semester) {
            header('Location: ' . BASEURL . '/guru');
            return;
        }
        if (!$id_penugasan) {
            echo 'Parameter penugasan tidak ditemukan.';
            return;
        }

        $rekap = $this->getRekapAbsenForPenugasan($id_guru, $id_semester, (int) $id_penugasan);
        $this->data['rekap_detail'] = $rekap;
        $this->data['id_penugasan'] = (int) $id_penugasan;

        $this->view('templates/header', $this->data);
        $this->loadSidebar();
        $this->view('guru/rekap_absen_detail', $this->data);
        $this->view('templates/footer', $this->data);
    }

    /**
     * Edit Profil (Guru & Wali Kelas) - username/password
     */
    public function profil()
    {
        $this->data['judul'] = 'Profil';
        $id_guru = $_SESSION['id_ref'] ?? 0;
        if (!$id_guru) {
            header('Location: ' . BASEURL . '/guru');
            exit;
        }

        $guru = $this->model('Guru_model')->getGuruById($id_guru);
        $this->data['guru'] = $guru;

        $this->view('templates/header', $this->data);
        $this->loadSidebar();
        $this->view('guru/profil', $this->data);
        $this->view('templates/footer', $this->data);
    }

    /**
     * Simpan perubahan profil (POST)
     */
    public function simpanProfil()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/guru/profil');
            exit;
        }
        $id_guru = $_SESSION['id_ref'] ?? 0;
        if (!$id_guru) {
            header('Location: ' . BASEURL . '/guru/profil');
            exit;
        }

        $nama_guru = trim($_POST['nama_guru'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $no_wa = trim($_POST['no_wa'] ?? '');

        if (empty($nama_guru)) {
            Flasher::setFlash('Gagal', 'Nama tidak boleh kosong', 'danger');
            header('Location: ' . BASEURL . '/guru/profil');
            exit;
        }

        $updated = $this->model('Guru_model')->updateProfilGuru($id_guru, [
            'nama_guru' => $nama_guru,
            'email' => $email,
            'no_wa' => $no_wa,
        ]);

        if ($updated !== false) {
            // Refresh data guru dari database untuk memastikan session terupdate
            $guruUpdated = $this->model('Guru_model')->getGuruById($id_guru);
            if ($guruUpdated) {
                $_SESSION['user_nama_lengkap'] = $guruUpdated['nama_guru'];
            }
            Flasher::setFlash('Berhasil', 'Profil berhasil disimpan', 'success');
        } else {
            Flasher::setFlash('Gagal', 'Terjadi kesalahan saat menyimpan', 'danger');
        }

        header('Location: ' . BASEURL . '/guru/profil');
        exit;
    }

    /**
     * Ganti sandi (password only)
     */
    public function gantiSandi()
    {
        $this->data['judul'] = 'Ganti Sandi';
        $this->view('templates/header', $this->data);
        $this->loadSidebar();
        $this->view('guru/ganti_sandi', $this->data);
        $this->view('templates/footer', $this->data);
    }

    /**
     * Simpan sandi baru (POST) - username tidak boleh diganti di sini
     */
    public function simpanSandi()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/guru/gantiSandi');
            exit;
        }

        $password = trim($_POST['password'] ?? '');
        $password2 = trim($_POST['password2'] ?? '');
        if (empty($password) || empty($password2)) {
            Flasher::setFlash('Gagal', 'Password dan konfirmasi wajib diisi', 'danger');
            header('Location: ' . BASEURL . '/guru/gantiSandi');
            exit;
        }
        if ($password !== $password2) {
            Flasher::setFlash('Gagal', 'Konfirmasi password tidak cocok', 'danger');
            header('Location: ' . BASEURL . '/guru/gantiSandi');
            exit;
        }

        $id_ref = $_SESSION['id_ref'] ?? 0;
        $role = $_SESSION['role'] ?? '';
        $ok = $this->model('User_model')->updatePassword($id_ref, $role, $password);
        if ($ok) {
            Flasher::setFlash('Berhasil', 'Password berhasil diperbarui', 'success');
        } else {
            Flasher::setFlash('Gagal', 'Tidak dapat memperbarui password', 'danger');
        }
        header('Location: ' . BASEURL . '/guru/gantiSandi');
        exit;
    }

    /**
     * Riwayat Jurnal Mengajar - REDIRECT ke RiwayatJurnalController
     */
    public function riwayat()
    {
        // Redirect ke controller yang benar
        header('Location: ' . BASEURL . '/riwayatJurnal');
        exit;
    }

    /**
     * Alias untuk test routing - REDIRECT
     */
    public function history()
    {
        error_log("GuruController::history() dipanggil - redirecting");
        header('Location: ' . BASEURL . '/riwayatJurnal');
        exit;
    }

    /**
     * Alias sederhana untuk test routing - REDIRECT
     */
    public function list()
    {
        error_log("GuruController::list() dipanggil - redirecting");
        header('Location: ' . BASEURL . '/riwayatJurnal');
        exit;
    }

    /**
     * Detail Riwayat per Mapel - REDIRECT ke RiwayatJurnalController dengan id_penugasan
     */
    public function detailRiwayat($id_mapel)
    {
        $id_guru = $_SESSION['id_ref'] ?? null;
        $id_semester = $_SESSION['id_semester_aktif'] ?? null;

        if (!$id_guru || !$id_semester || !$id_mapel) {
            header('Location: ' . BASEURL . '/riwayatJurnal');
            exit;
        }

        try {
            // Cari id_penugasan berdasarkan guru, semester, dan mapel
            $db = new Database();
            $db->query("
                SELECT p.id_penugasan 
                FROM penugasan p
                WHERE p.id_guru = :g AND p.id_semester = :s AND p.id_mapel = :m
                ORDER BY p.id_penugasan DESC LIMIT 1
            ");
            $db->bind('g', $id_guru);
            $db->bind('s', $id_semester);
            $db->bind('m', (int) $id_mapel);
            $row = $db->single();

            if ($row && !empty($row['id_penugasan'])) {
                // Redirect ke RiwayatJurnalController::detail dengan id_penugasan
                header('Location: ' . BASEURL . '/riwayatJurnal/detail/' . $row['id_penugasan']);
                exit;
            }
        } catch (Exception $e) {
            error_log("Error finding penugasan: " . $e->getMessage());
        }

        // Fallback: kembali ke daftar riwayat
        header('Location: ' . BASEURL . '/riwayatJurnal');
        exit;
    }

    /**
     * Cetak per Mapel - REDIRECT ke RiwayatJurnalController::cetak dengan id_penugasan
     */
    public function cetakMapel($id_mapel)
    {
        $id_guru = $_SESSION['id_ref'] ?? null;
        $id_semester = $_SESSION['id_semester_aktif'] ?? null;

        if (!$id_guru || !$id_semester || !$id_mapel) {
            header('Location: ' . BASEURL . '/riwayatJurnal');
            exit;
        }

        try {
            // Cari id_penugasan berdasarkan guru, semester, dan mapel
            $db = new Database();
            $db->query("
                SELECT p.id_penugasan 
                FROM penugasan p
                WHERE p.id_guru = :g AND p.id_semester = :s AND p.id_mapel = :m
                ORDER BY p.id_penugasan DESC LIMIT 1
            ");
            $db->bind('g', $id_guru);
            $db->bind('s', $id_semester);
            $db->bind('m', (int) $id_mapel);
            $row = $db->single();

            if ($row && !empty($row['id_penugasan'])) {
                // Preserve PDF parameter jika ada
                $pdfParam = isset($_GET['pdf']) && $_GET['pdf'] == '1' ? '?pdf=1' : '';

                // Redirect ke RiwayatJurnalController::cetak dengan id_penugasan
                header('Location: ' . BASEURL . '/riwayatJurnal/cetak/' . $row['id_penugasan'] . $pdfParam);
                exit;
            }
        } catch (Exception $e) {
            error_log("Error finding penugasan for cetak: " . $e->getMessage());
        }

        // Fallback: kembali ke daftar riwayat
        header('Location: ' . BASEURL . '/riwayatJurnal');
        exit;
    }

    /**
     * Halaman test routing
     */
    public function test()
    {
        error_log("GuruController::test() dipanggil");
        echo "<h1>Method test() berhasil dipanggil!</h1>";
        echo "<p>Routing berfungsi dengan baik.</p>";
        echo "<a href='" . BASEURL . "/guru/dashboard'>Kembali ke Dashboard</a>";
    }

    /**
     * Input Jurnal
     */
    public function jurnal()
    {
        $this->data['judul'] = 'Input Jurnal';

        // Cek blokir akses RPP
        $blokir = cekBlokirAksesRPP('jurnal');
        if ($blokir) {
            $this->data['blokir_akses'] = $blokir;
            $this->view('templates/header', $this->data);
            $this->loadSidebar();
            $this->view('guru/akses_diblokir', $this->data);
            $this->view('templates/footer', $this->data);
            return;
        }

        $id_guru = $_SESSION['id_ref'] ?? null;
        $id_semester_aktif = $_SESSION['id_semester_aktif'] ?? null;

        $this->data['jadwal_mengajar'] = [];
        if ($id_guru && $id_semester_aktif) {
            $this->data['jadwal_mengajar'] = $this->model('Penugasan_model')->getPenugasanByGuru($id_guru, $id_semester_aktif);
        }

        $this->view('templates/header', $this->data);
        $this->loadSidebar();
        $this->view('guru/jurnal', $this->data);
        $this->view('templates/footer', $this->data);
    }

    /**
     * Tambah Jurnal
     */
    public function tambahJurnal($id_penugasan)
    {
        // Cek blokir akses RPP
        $blokir = cekBlokirAksesRPP('jurnal');
        if ($blokir) {
            Flasher::setFlash($blokir['pesan'], 'danger');
            header('Location: ' . BASEURL . '/guru/dashboard');
            exit;
        }

        $this->data['judul'] = 'Tambah Jurnal Mengajar';
        $this->data['id_penugasan'] = $id_penugasan;
        $this->data['pertemuan_selanjutnya'] =
            (int) $this->model('Jurnal_model')->getPertemuanTerakhir($id_penugasan) + 1;

        $this->view('templates/header', $this->data);
        $this->loadSidebar();
        $this->view('guru/tambah_jurnal', $this->data);
        $this->view('templates/footer', $this->data);
    }

    /**
     * Proses Tambah Jurnal
     */
    public function prosesTambahJurnal()
    {
        // Cek blokir akses RPP
        $blokir = cekBlokirAksesRPP('jurnal');
        if ($blokir) {
            Flasher::setFlash($blokir['pesan'], 'danger');
            header('Location: ' . BASEURL . '/guru/dashboard');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $idBaru = $this->model('Jurnal_model')->tambahDataJurnal($_POST);
            if ($idBaru) {
                header('Location: ' . BASEURL . '/guru/absensi/' . $idBaru);
                exit;
            }
        }
        // fallback kembali ke jurnal
        header('Location: ' . BASEURL . '/guru/jurnal');
        exit;
    }

    /**
     * Input Absensi untuk 1 jurnal
     * Jika dipanggil tanpa parameter, redirect ke halaman jurnal
     */
    public function absensi($id_jurnal = null)
    {
        // Jika tidak ada id_jurnal, redirect ke halaman jurnal untuk memilih kelas
        if ($id_jurnal === null) {
            header('Location: ' . BASEURL . '/guru/jurnal');
            exit;
        }

        // Cek blokir akses RPP
        $blokir = cekBlokirAksesRPP('absensi');
        if ($blokir) {
            $this->data['judul'] = 'Akses Dibatasi';
            $this->data['blokir_akses'] = $blokir;
            $this->view('templates/header', $this->data);
            $this->loadSidebar();
            $this->view('guru/akses_diblokir', $this->data);
            $this->view('templates/footer', $this->data);
            return;
        }

        $this->data['judul'] = 'Input Absensi';
        $this->data['jurnal'] = $this->model('Jurnal_model')->getJurnalDetailById($id_jurnal);
        $this->data['daftar_siswa'] = $this->model('Absensi_model')->getSiswaDanAbsensiByJurnal($id_jurnal);

        $this->view('templates/header', $this->data);
        $this->loadSidebar();
        $this->view('guru/absensi', $this->data);
        $this->view('templates/footer', $this->data);
    }

    /**
     * Simpan Absensi
     */
    public function prosesSimpanAbsensi()
    {
        // Cek blokir akses RPP
        $blokir = cekBlokirAksesRPP('absensi');
        if ($blokir) {
            Flasher::setFlash($blokir['pesan'], 'danger');
            header('Location: ' . BASEURL . '/guru/dashboard');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($this->model('Absensi_model')->simpanAbsensi($_POST) > 0) {

                // === KIRIM NOTIFIKASI WA UNTUK KETIDAKHADIRAN ===
                $this->sendAbsensiNotifications($_POST);

                // Tambahkan notifikasi sukses
                if (class_exists('Flasher')) {
                    Flasher::setFlash('Data absensi berhasil disimpan.', 'success');
                }

                // Arahkan ke dashboard guru
                header('Location: ' . BASEURL . '/guru/dashboard');
                exit;
            }
        }

        // Fallback jika gagal atau metode request salah
        if (class_exists('Flasher')) {
            Flasher::setFlash('Gagal menyimpan data absensi.', 'danger');
        }
        header('Location: ' . BASEURL . '/guru/dashboard');
        exit;
    }

    /**
     * Kirim notifikasi WA untuk absensi yang tidak hadir (A/I/S/D)
     * Mendukung mode: personal, grup, both, off
     */
    private function sendAbsensiNotifications($postData)
    {
        try {
            error_log("=== sendAbsensiNotifications START ===");

            // Ambil data jurnal untuk info kelas dan mapel
            $id_jurnal = $postData['id_jurnal'] ?? null;
            error_log("id_jurnal: " . ($id_jurnal ?? 'NULL'));

            if (!$id_jurnal) {
                error_log("EXIT: id_jurnal kosong");
                return;
            }

            $jurnal = $this->model('Jurnal_model')->getJurnalDetailById($id_jurnal);
            if (!$jurnal) {
                error_log("EXIT: jurnal tidak ditemukan");
                return;
            }
            error_log("jurnal found: " . json_encode($jurnal));

            // Ambil pengaturan sekolah
            $pengaturan = $this->model('PengaturanAplikasi_model')->getPengaturan();
            $namaSekolah = $pengaturan['nama_aplikasi'] ?? '';
            error_log("namaSekolah: " . $namaSekolah);

            // Cek setting notifikasi absensi (master toggle)
            $pengaturanSistemModel = $this->model('PengaturanSistem_model');
            $notifEnabled = $pengaturanSistemModel->get('wa_notif_absensi_enabled') ?? '1';
            error_log("notifEnabled: " . $notifEnabled);
            if ($notifEnabled == '0') {
                error_log("EXIT: Notifikasi absensi dinonaktifkan di pengaturan.");
                return;
            }

            // Cek mode notifikasi: personal, grup, both, off
            $notifMode = $pengaturanSistemModel->get('wa_notif_absensi_mode') ?? 'personal';
            error_log("notifMode: " . $notifMode);
            if ($notifMode === 'off') {
                error_log("EXIT: Mode notifikasi = off");
                return;
            }

            // Status yang perlu notifikasi
            $statusNotif = ['A', 'I', 'S', 'D'];

            // Kumpulkan siswa yang tidak hadir
            $siswaNotHadir = [];
            $statusSiswa = $postData['absensi'] ?? [];
            $keteranganSiswa = $postData['keterangan'] ?? [];
            error_log("statusSiswa count: " . count($statusSiswa));

            foreach ($statusSiswa as $id_siswa => $status) {
                if (in_array($status, $statusNotif)) {
                    $siswaNotHadir[$id_siswa] = [
                        'status' => $status,
                        'keterangan' => $keteranganSiswa[$id_siswa] ?? ''
                    ];
                }
            }

            error_log("siswaNotHadir count: " . count($siswaNotHadir));
            if (empty($siswaNotHadir)) {
                error_log("EXIT: Tidak ada siswa yang tidak hadir (semua H)");
                return;
            }

            // Ambil data siswa dan orang tua
            $siswaModel = $this->model('Siswa_model');

            // Load Queue Model dan Fonnte
            require_once APPROOT . '/app/models/WaQueue_model.php';
            require_once APPROOT . '/app/core/Fonnte.php';
            $queueModel = new WaQueue_model();
            $fonnte = new Fonnte();
            error_log("Models loaded successfully");

            // Format tanggal
            $tanggal = date('d F Y', strtotime($jurnal['tanggal']));
            $namaKelas = $jurnal['nama_kelas'] ?? '-';
            $namaMapel = $jurnal['nama_mapel'] ?? '-';
            $namaGuru = $jurnal['nama_guru'] ?? '';
            $id_kelas = $jurnal['id_kelas'] ?? null;

            // Status text mapping
            $statusText = [
                'A' => 'ALPHA (Tanpa Keterangan)',
                'I' => 'IZIN',
                'S' => 'SAKIT',
                'D' => 'DISPENSASI'
            ];

            $queued = 0;

            // === MODE GRUP atau BOTH: Kirim ke grup kelas ===
            if (in_array($notifMode, ['grup', 'both']) && $id_kelas) {
                error_log("Processing GROUP notification for kelas: " . $id_kelas);

                // Load grup WA model
                require_once APPROOT . '/app/models/KelasGrupWa_model.php';
                $grupModel = new KelasGrupWa_model();
                $grupList = $grupModel->getActiveGrupByKelas($id_kelas);

                error_log("Active groups found: " . count($grupList));

                if (!empty($grupList)) {
                    // Build daftar absen untuk grup
                    $daftarAbsen = [];
                    foreach ($siswaNotHadir as $id_siswa => $data) {
                        $siswa = $siswaModel->getSiswaById($id_siswa);
                        if ($siswa) {
                            $daftarAbsen[] = [
                                'nama' => $siswa['nama_siswa'] ?? '-',
                                'status' => $data['status'],
                                'keterangan' => $data['keterangan']
                            ];
                        }
                    }

                    // Kirim ke setiap grup aktif
                    foreach ($grupList as $grup) {
                        $grupId = $grup['grup_wa_id'];
                        $namaGrup = $grup['nama_grup'];

                        // Build message menggunakan Fonnte helper
                        $pesan = $fonnte->buildGrupAbsensiMessage($namaKelas, $namaMapel, $tanggal, $namaGuru, $daftarAbsen, 0, $namaSekolah);

                        // Masukkan ke antrian
                        $queueModel->addToQueue(
                            $grupId,
                            $pesan,
                            'notif_absensi_grup',
                            ['kelas' => $namaKelas, 'mapel' => $namaMapel, 'grup' => $namaGrup, 'jumlah_absen' => count($daftarAbsen)]
                        );
                        $queued++;
                        error_log("Queued group message to: {$namaGrup} ({$grupId})");
                    }
                } else {
                    error_log("No active groups for kelas: " . $id_kelas);
                }
            }

            // === MODE PERSONAL atau BOTH: Kirim ke orang tua per siswa ===
            if (in_array($notifMode, ['personal', 'both'])) {
                error_log("Processing PERSONAL notification");

                foreach ($siswaNotHadir as $id_siswa => $data) {
                    $status = $data['status'];
                    $siswa = $siswaModel->getSiswaById($id_siswa);
                    error_log("Processing siswa id: {$id_siswa}, status: {$status}");

                    if (!$siswa) {
                        error_log("SKIP: siswa not found for id: {$id_siswa}");
                        continue;
                    }

                    $namaSiswa = $siswa['nama_siswa'] ?? '-';
                    $statusLabel = $statusText[$status] ?? $status;

                    // Helper cleaning number
                    $cleanNumber = function ($num) use ($fonnte) {
                        $num = preg_replace('/[^0-9]/', '', $num);
                        return (strlen($num) > 7) ? $fonnte->formatNumber($num) : '';
                    };

                    $ayahNo = $cleanNumber($siswa['ayah_no_hp'] ?? '');
                    $ibuNo = $cleanNumber($siswa['ibu_no_hp'] ?? '');

                    // Prioritas: Kirim ke Ibu dulu, jika tidak ada baru ke Ayah
                    if ($ibuNo) {
                        $namaIbu = $siswa['ibu_kandung'] ?? 'Ibu';
                        $pesan = $fonnte->buildAbsensiMessage($namaIbu, $namaSiswa, $namaKelas, $statusLabel, $tanggal, $namaSekolah, $status, $namaMapel);
                        $queueModel->addToQueue(
                            $ibuNo,
                            $pesan,
                            'notif_absensi',
                            ['siswa' => $namaSiswa, 'target' => 'Ibu', 'status' => $status]
                        );
                        $queued++;
                    } elseif ($ayahNo) {
                        $namaAyah = $siswa['ayah_kandung'] ?? 'Bapak';
                        $pesan = $fonnte->buildAbsensiMessage($namaAyah, $namaSiswa, $namaKelas, $statusLabel, $tanggal, $namaSekolah, $status, $namaMapel);
                        $queueModel->addToQueue(
                            $ayahNo,
                            $pesan,
                            'notif_absensi',
                            ['siswa' => $namaSiswa, 'target' => 'Ayah', 'status' => $status]
                        );
                        $queued++;
                    }
                }
            }

            // Log hasil
            error_log("Notifikasi absensi masuk antrian: {$queued} pesan (mode: {$notifMode})");

        } catch (Exception $e) {
            error_log("Error queuing absensi notifications: " . $e->getMessage());
        }
    }

    /**
     * Edit Jurnal
     */
    public function editJurnal($id_jurnal)
    {
        $this->data['judul'] = 'Edit Jurnal';
        $this->data['jurnal'] = $this->model('Jurnal_model')->getJurnalById($id_jurnal);

        $this->view('templates/header', $this->data);
        $this->loadSidebar();
        $this->view('guru/edit_jurnal', $this->data);
        $this->view('templates/footer', $this->data);
    }

    /**
     * Update Jurnal - dengan redirect ke detail yang benar
     */
    public function prosesUpdateJurnal()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_jurnal = $_POST['id_jurnal'] ?? null;

            if ($this->model('Jurnal_model')->updateDataJurnal($_POST) > 0) {
                // Ambil id_penugasan dari id_jurnal untuk redirect yang tepat
                if ($id_jurnal) {
                    try {
                        $db = new Database();
                        $db->query("
                            SELECT j.id_penugasan 
                            FROM jurnal j
                            WHERE j.id_jurnal = :id_jurnal
                            LIMIT 1
                        ");
                        $db->bind('id_jurnal', $id_jurnal);
                        $row = $db->single();

                        if ($row && !empty($row['id_penugasan'])) {
                            // Redirect ke halaman detail dengan id_penugasan
                            if (class_exists('Flasher')) {
                                Flasher::setFlash('Jurnal berhasil diperbarui.', 'success');
                            }
                            header('Location: ' . BASEURL . '/riwayatJurnal/detail/' . $row['id_penugasan']);
                            exit;
                        }
                    } catch (Exception $e) {
                        error_log("Error getting penugasan for updated jurnal: " . $e->getMessage());
                    }
                }

                // Fallback ke riwayat jika query gagal
                if (class_exists('Flasher')) {
                    Flasher::setFlash('Jurnal berhasil diperbarui.', 'success');
                }
                header('Location: ' . BASEURL . '/riwayatJurnal');
                exit;
            } else {
                // Gagal update
                if (class_exists('Flasher')) {
                    Flasher::setFlash('Gagal memperbarui jurnal.', 'danger');
                }

                // Redirect kembali ke form edit
                if ($id_jurnal) {
                    header('Location: ' . BASEURL . '/guru/editJurnal/' . $id_jurnal);
                    exit;
                }
            }
        }

        // fallback
        header('Location: ' . BASEURL . '/guru/dashboard');
        exit;
    }

    /**
     * Cetak Absensi (tanpa layout)
     */
    public function cetakAbsensi($id_jurnal)
    {
        $this->data['judul'] = 'Cetak Laporan Absensi';
        $this->data['jurnal'] = $this->model('Jurnal_model')->getJurnalDetailById($id_jurnal);
        $this->data['daftar_absensi'] = $this->model('Absensi_model')->getAbsensiByJurnalId($id_jurnal);

        $this->view('guru/cetak_absensi', $this->data);
    }

    public function editAbsensi($id_jurnal)
    {
        $this->data['judul'] = 'Edit Absensi';
        $this->data['jurnal'] = $this->model('Jurnal_model')->getJurnalDetailById($id_jurnal);
        $this->data['daftar_siswa'] = $this->model('Absensi_model')->getSiswaDanAbsensiByJurnal($id_jurnal);

        $this->view('templates/header', $this->data);
        $this->loadSidebar();
        $this->view('guru/edit_absensi', $this->data);
        $this->view('templates/footer', $this->data);
    }

    /**
     * Proses Edit Absensi - Update data absensi yang sudah ada
     */
    public function prosesEditAbsensi()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_jurnal = $_POST['id_jurnal'] ?? null;

            if ($this->model('Absensi_model')->simpanAbsensi($_POST) > 0) {

                // === KIRIM NOTIFIKASI WA UNTUK KETIDAKHADIRAN ===
                $this->sendAbsensiNotifications($_POST);

                // SUKSES: Set notifikasi dan redirect ke dashboard
                if (class_exists('Flasher')) {
                    Flasher::setFlash('Data absensi berhasil diperbarui.', 'success');
                }
                header('Location: ' . BASEURL . '/guru/dashboard');
                exit;

            } else {
                // GAGAL: Set notifikasi dan kembali ke halaman edit
                if (class_exists('Flasher')) {
                    Flasher::setFlash('Gagal memperbarui data absensi. Tidak ada perubahan yang disimpan.', 'danger');
                }

                if ($id_jurnal) {
                    header('Location: ' . BASEURL . '/guru/editAbsensi/' . $id_jurnal);
                    exit;
                }
            }
        }

        // Fallback jika metode request bukan POST, arahkan ke dashboard
        header('Location: ' . BASEURL . '/guru/dashboard');
        exit;
    }
    /**
     * Rincian Absen - Menampilkan halaman filter rincian absen
     */
    public function rincianAbsen($id_mapel = null)
    {
        $this->data['judul'] = 'Rincian Absen per Pertemuan';

        $id_guru = $_SESSION['id_ref'] ?? null;
        $id_semester = $_SESSION['id_semester_aktif'] ?? null;

        // Parameter filter dari GET
        $periode = $_GET['periode'] ?? 'semester';
        $tanggal_mulai = $_GET['tanggal_mulai'] ?? '';
        $tanggal_akhir = $_GET['tanggal_akhir'] ?? '';
        $id_mapel_filter = $_GET['id_mapel'] ?? $id_mapel;
        $id_kelas_filter = $_GET['id_kelas'] ?? null;

        $this->data['filter'] = [
            'periode' => $periode,
            'tanggal_mulai' => $tanggal_mulai,
            'tanggal_akhir' => $tanggal_akhir,
            'id_mapel' => $id_mapel_filter,
            'id_kelas' => $id_kelas_filter
        ];

        // Ambil daftar mapel yang diajar guru
        $this->data['daftar_mapel'] = $this->getDaftarMapelGuru($id_guru, $id_semester);

        // Jika ada mapel yang dipilih, ambil rincian absen
        $this->data['rincian_data'] = [];
        $this->data['mapel_info'] = null;

        if ($id_mapel_filter) {
            $this->data['mapel_info'] = $this->getMapelInfo($id_guru, $id_semester, $id_mapel_filter, $id_kelas_filter);
            $id_kelas_mapel = $id_kelas_filter ?: ($this->data['mapel_info']['id_kelas'] ?? null);
            if ($id_kelas_mapel) {
                $this->data['rincian_data'] = $this->getRincianAbsenPerPertemuan($id_guru, $id_semester, $id_mapel_filter, $id_kelas_mapel, $periode, $tanggal_mulai, $tanggal_akhir);
            }
        }

        $this->view('templates/header', $this->data);
        $this->loadSidebar();
        $this->view('guru/rincian_absen_filter', $this->data);
        $this->view('templates/footer', $this->data);
    }

    /**
     * Download Rincian Absen per Pertemuan dalam format PDF dengan QR Code
     */
    public function downloadRincianAbsenPDF($id_mapel = null)
    {
        $id_guru = $_SESSION['id_ref'] ?? null;
        $id_semester = $_SESSION['id_semester_aktif'] ?? null;

        if (!$id_guru || !$id_semester) {
            echo "Sesi tidak valid. Silakan login ulang.";
            return;
        }

        // Ambil id_mapel dari parameter GET jika tidak ada di URL
        if (!$id_mapel) {
            $id_mapel = $_GET['id_mapel'] ?? null;
        }

        if (!$id_mapel) {
            echo "Parameter id_mapel tidak ditemukan.";
            return;
        }

        try {
            // Ambil info mapel
            $id_kelas_req = $_GET['id_kelas'] ?? null;
            $mapel_info = $this->getMapelInfo($id_guru, $id_semester, $id_mapel, $id_kelas_req);
            if (empty($mapel_info)) {
                echo "Data mapel tidak ditemukan atau Anda tidak memiliki akses.";
                return;
            }

            // Parameter filter
            $periode = $_GET['periode'] ?? 'semester';
            $tanggal_mulai = $_GET['tanggal_mulai'] ?? '';
            $tanggal_akhir = $_GET['tanggal_akhir'] ?? '';

            // Ambil data rincian
            $id_kelas_mapel = $id_kelas_req ?: ($mapel_info['id_kelas'] ?? null);
            $rincian_data = $id_kelas_mapel ? $this->getRincianAbsenPerPertemuan($id_guru, $id_semester, $id_mapel, $id_kelas_mapel, $periode, $tanggal_mulai, $tanggal_akhir) : ['siswa_data' => [], 'pertemuan_headers' => []];

            if (empty($rincian_data['siswa_data'])) {
                echo "Tidak ada data absensi untuk dicetak.";
                return;
            }

            // Render HTML untuk PDF
            $filter_info = [
                'periode' => $periode,
                'tanggal_mulai' => $tanggal_mulai,
                'tanggal_akhir' => $tanggal_akhir,
                'tanggal_cetak' => date('d F Y'),
                'id_mapel' => $id_mapel,
                'id_kelas' => $id_kelas_mapel,
                'is_pdf' => true
            ];

            $renderView = function ($viewPath, $data) {
                extract($data);
                ob_start();
                $fullPath = __DIR__ . "/../views/$viewPath.php";
                if (!file_exists($fullPath)) {
                    throw new Exception("View file tidak ditemukan: $fullPath");
                }
                require $fullPath;
                return ob_get_clean();
            };

            $html = $renderView('guru/cetak_rincian_absen', [
                'mapel_info' => $mapel_info,
                'rincian_data' => $rincian_data,
                'filter_info' => $filter_info
            ]);

            // Generate PDF dengan QR Code
            $dompdfPath = __DIR__ . '/../core/dompdf/autoload.inc.php';

            if (!file_exists($dompdfPath)) {
                echo "Library Dompdf tidak tersedia.";
                return;
            }

            require_once $dompdfPath;

            if (!class_exists('\\Dompdf\\Dompdf')) {
                echo "Class Dompdf tidak ditemukan.";
                return;
            }

            // Load QR Helper
            require_once APPROOT . '/app/core/PDFQRHelper.php';

            // Generate metadata for QR with fingerprint (consistent with rapor format)
            $semesterName = $_SESSION['nama_semester_aktif'] ?? '';
            $tpName = $mapel_info['nama_tp'] ?? '';
            $namaMapel = $mapel_info['nama_mapel'] ?? '';
            $namaKelas = $mapel_info['nama_kelas'] ?? '';
            $printedBy = $_SESSION['nama_lengkap'] ?? $_SESSION['username'] ?? 'Unknown';

            $metadata = [
                'doc' => 'rincian_absen',
                'id_mapel' => $id_mapel,
                'id_guru' => $id_guru,
                'nama_mapel' => $namaMapel,
                'kelas' => $namaKelas,
                'semester' => $semesterName,
                'tahun_pelajaran' => $tpName,
                'periode' => $periode,
                'printed_by' => $printedBy,
                'printed_at' => date('Y-m-d H:i:s')
            ];

            // Generate fingerprint for document verification
            $fingerprintBase = implode('|', [
                $id_mapel,
                $namaMapel,
                $namaKelas,
                $semesterName,
                $periode,
                date('Y-m-d')
            ]);
            $metadata['fingerprint'] = hash('sha256', $fingerprintBase);

            // Add QR to PDF
            $html = PDFQRHelper::addQRToPDF($html, 'rincian_absen', $id_mapel, $metadata);

            // Generate PDF
            $dompdf = new \Dompdf\Dompdf([
                'isRemoteEnabled' => true,
                'isHtml5ParserEnabled' => true,
                'defaultFont' => 'Arial'
            ]);

            $dompdf->loadHtml($html, 'UTF-8');
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();

            // Download filename
            $nama_file = 'Rincian_Absen_' .
                preg_replace('/[^A-Za-z0-9_-]/', '_', $namaMapel) . '_' .
                preg_replace('/[^A-Za-z0-9_-]/', '_', $namaKelas) . '_' .
                date('Y-m-d') . '.pdf';

            $dompdf->stream($nama_file, ['Attachment' => true]);

        } catch (Exception $e) {
            error_log("Error in downloadRincianAbsenPDF(): " . $e->getMessage());
            echo "Terjadi kesalahan saat membuat PDF: " . htmlspecialchars($e->getMessage());
        }
    }

    /**
     * Helper: Hitung total penugasan guru pada semester aktif
     */
    private function getTotalPenugasan($id_guru, $id_semester)
    {
        if (!$id_guru || !$id_semester)
            return 0;

        try {
            $db = new Database();
            $db->query("
                SELECT COUNT(DISTINCT p.id_penugasan) as total
                FROM penugasan p
                WHERE p.id_guru = :id_guru 
                AND p.id_semester = :id_semester
            ");
            $db->bind('id_guru', $id_guru);
            $db->bind('id_semester', $id_semester);

            $result = $db->single();
            return (int) ($result['total'] ?? 0);
        } catch (Exception $e) {
            error_log("Error in getTotalPenugasan(): " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Helper: Hitung total hari mengajar (distinct tanggal) sejak awal penugasan
     */
    private function getTotalHariMengajar($id_guru, $id_semester)
    {
        if (!$id_guru || !$id_semester)
            return 0;

        try {
            $db = new Database();
            $db->query("
                SELECT COUNT(DISTINCT DATE(j.tanggal)) as total_hari
                FROM jurnal j
                JOIN penugasan p ON j.id_penugasan = p.id_penugasan
                WHERE p.id_guru = :id_guru 
                AND p.id_semester = :id_semester
            ");
            $db->bind('id_guru', $id_guru);
            $db->bind('id_semester', $id_semester);

            $result = $db->single();
            return (int) ($result['total_hari'] ?? 0);
        } catch (Exception $e) {
            error_log("Error in getTotalHariMengajar(): " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Helper: Ambil info kelas dan mapel yang diajar guru
     */
    private function getKelasMapelInfo($id_guru, $id_semester)
    {
        if (!$id_guru || !$id_semester)
            return [];

        try {
            $db = new Database();
            $db->query("
                SELECT DISTINCT 
                    k.nama_kelas,
                    m.nama_mapel
                FROM penugasan p
                JOIN kelas k ON p.id_kelas = k.id_kelas
                JOIN mapel m ON p.id_mapel = m.id_mapel
                WHERE p.id_guru = :id_guru 
                AND p.id_semester = :id_semester
                ORDER BY k.nama_kelas ASC, m.nama_mapel ASC
            ");
            $db->bind('id_guru', $id_guru);
            $db->bind('id_semester', $id_semester);

            return $db->resultSet();
        } catch (Exception $e) {
            error_log("Error in getKelasMapelInfo(): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Helper: Ambil daftar mapel yang diajar guru
     */
    private function getDaftarMapelGuru($id_guru, $id_semester)
    {
        try {
            $db = new Database();
            $sql = "SELECT DISTINCT p.id_penugasan, m.id_mapel, m.nama_mapel, k.id_kelas, k.nama_kelas
                    FROM penugasan p
                    JOIN mapel m ON p.id_mapel = m.id_mapel  
                    JOIN kelas k ON p.id_kelas = k.id_kelas
                    WHERE p.id_guru = :id_guru AND p.id_semester = :id_semester
                    ORDER BY m.nama_mapel, k.nama_kelas";

            $db->query($sql);
            $db->bind('id_guru', $id_guru);
            $db->bind('id_semester', $id_semester);

            return $db->resultSet();
        } catch (Exception $e) {
            error_log("Error in getDaftarMapelGuru(): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Helper: Ambil info mapel (nama, kelas, dll)
     */
    private function getMapelInfo($id_guru, $id_semester, $id_mapel, $id_kelas = null)
    {
        try {
            $db = new Database();
            $sql = "SELECT m.id_mapel, k.id_kelas, m.nama_mapel, k.nama_kelas, g.nama_guru
                    FROM penugasan p
                    JOIN mapel m ON p.id_mapel = m.id_mapel
                    JOIN kelas k ON p.id_kelas = k.id_kelas  
                    JOIN guru g ON p.id_guru = g.id_guru
                    WHERE p.id_guru = :id_guru AND p.id_semester = :id_semester AND m.id_mapel = :id_mapel" . ($id_kelas ? " AND k.id_kelas = :id_kelas" : "") . "
                    LIMIT 1";

            $db->query($sql);
            $db->bind('id_guru', $id_guru);
            $db->bind('id_semester', $id_semester);
            $db->bind('id_mapel', $id_mapel);
            if ($id_kelas) {
                $db->bind('id_kelas', $id_kelas);
            }

            $result = $db->single() ?: [];

            // Tambahkan info semester dari session jika tersedia
            if (!empty($result)) {
                $result['nama_tp'] = $_SESSION['nama_tp_aktif'] ?? 'Tahun Pelajaran';
                $result['semester'] = $_SESSION['nama_semester_aktif'] ?? 'Semester';
            }

            return $result;
        } catch (Exception $e) {
            error_log("Error in getMapelInfo(): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Helper: Ambil rincian absen per pertemuan dengan filter
     */
    private function getRincianAbsenPerPertemuan($id_guru, $id_semester, $id_mapel, $id_kelas, $periode, $tanggal_mulai, $tanggal_akhir)
    {
        try {
            $db = new Database();

            // Build WHERE clause berdasarkan periode
            $whereClause = "p.id_guru = :id_guru AND p.id_semester = :id_semester AND m.id_mapel = :id_mapel AND k.id_kelas = :id_kelas";
            $params = [
                'id_guru' => $id_guru,
                'id_semester' => $id_semester,
                'id_mapel' => $id_mapel,
                'id_kelas' => $id_kelas
            ];

            switch ($periode) {
                case 'hari_ini':
                    $whereClause .= " AND DATE(j.tanggal) = CURDATE()";
                    break;
                case 'minggu_ini':
                    $whereClause .= " AND YEARWEEK(j.tanggal, 1) = YEARWEEK(CURDATE(), 1)";
                    break;
                case 'bulan_ini':
                    $whereClause .= " AND YEAR(j.tanggal) = YEAR(CURDATE()) AND MONTH(j.tanggal) = MONTH(CURDATE())";
                    break;
                case 'custom':
                    if ($tanggal_mulai && $tanggal_akhir) {
                        $whereClause .= " AND j.tanggal BETWEEN :tanggal_mulai AND :tanggal_akhir";
                        $params['tanggal_mulai'] = $tanggal_mulai;
                        $params['tanggal_akhir'] = $tanggal_akhir;
                    }
                    break;
                default: // semester - tidak ada filter tambahan
                    break;
            }

            // Query utama - ambil seluruh siswa dalam kelas pada TP semester ini, dengan kehadiran per jurnal (LEFT JOIN)
            $sql = "SELECT 
                        s.id_siswa,
                        s.nama_siswa,
                        s.nisn,
                        j.id_jurnal,
                        j.tanggal,
                        j.pertemuan_ke,
                        j.topik_materi,
                        COALESCE(a.status_kehadiran, 'A') as status_kehadiran,
                        a.waktu_input as waktu_absen,
                        a.keterangan,
                        k.id_kelas,
                        p.id_penugasan
                    FROM penugasan p
                    JOIN mapel m ON p.id_mapel = m.id_mapel
                    JOIN kelas k ON p.id_kelas = k.id_kelas
            JOIN semester sem ON sem.id_semester = p.id_semester
            JOIN jurnal j ON p.id_penugasan = j.id_penugasan
            JOIN keanggotaan_kelas kk ON k.id_kelas = kk.id_kelas AND kk.id_tp = sem.id_tp
            JOIN siswa s ON kk.id_siswa = s.id_siswa
            LEFT JOIN absensi a ON j.id_jurnal = a.id_jurnal AND s.id_siswa = a.id_siswa
                    WHERE $whereClause
                    ORDER BY s.nama_siswa ASC, j.tanggal ASC, j.pertemuan_ke ASC";

            $db->query($sql);
            foreach ($params as $key => $value) {
                $db->bind($key, $value);
            }

            $result = $db->resultSet();

            // Restructure data: group by siswa, dengan detail per pertemuan
            $structured_data = [];
            $pertemuan_list = [];

            foreach ($result as $row) {
                $id_siswa = $row['id_siswa'];
                $id_jurnal = $row['id_jurnal'];

                // Simpan info siswa
                if (!isset($structured_data[$id_siswa])) {
                    $structured_data[$id_siswa] = [
                        'id_siswa' => $id_siswa,
                        'nama_siswa' => $row['nama_siswa'],
                        'nisn' => $row['nisn'],
                        'pertemuan' => [],
                        'total_hadir' => 0,
                        'total_izin' => 0,
                        'total_sakit' => 0,
                        'total_alpha' => 0
                    ];
                }

                // Simpan detail pertemuan
                $structured_data[$id_siswa]['pertemuan'][$id_jurnal] = [
                    'tanggal' => $row['tanggal'],
                    'pertemuan_ke' => $row['pertemuan_ke'],
                    'topik_materi' => $row['topik_materi'],
                    'status' => $row['status_kehadiran'],
                    'waktu_absen' => $row['waktu_absen'],
                    'keterangan' => $row['keterangan']
                ];

                // Hitung total per status
                switch ($row['status_kehadiran']) {
                    case 'H':
                        $structured_data[$id_siswa]['total_hadir']++;
                        break;
                    case 'I':
                        $structured_data[$id_siswa]['total_izin']++;
                        break;
                    case 'S':
                        $structured_data[$id_siswa]['total_sakit']++;
                        break;
                    default:
                        $structured_data[$id_siswa]['total_alpha']++;
                        break;
                }

                // Simpan daftar pertemuan untuk header tabel
                if (!isset($pertemuan_list[$id_jurnal])) {
                    $pertemuan_list[$id_jurnal] = [
                        'tanggal' => $row['tanggal'],
                        'pertemuan_ke' => $row['pertemuan_ke'],
                        'topik_materi' => $row['topik_materi']
                    ];
                }
            }

            // Sort pertemuan by tanggal
            uasort($pertemuan_list, function ($a, $b) {
                return strtotime($a['tanggal']) - strtotime($b['tanggal']);
            });

            return [
                'siswa_data' => array_values($structured_data),
                'pertemuan_headers' => array_values($pertemuan_list)
            ];

        } catch (Exception $e) {
            error_log("Error in getRincianAbsenPerPertemuan(): " . $e->getMessage());
            return [
                'siswa_data' => [],
                'pertemuan_headers' => []
            ];
        }
    }

    // ============================================================================
    // RPP METHODS
    // ============================================================================

    /**
     * Redirect dari dashboard - Buat/Edit RPP dari id_penugasan
     */
    public function buatRPP($id_penugasan = null)
    {
        if (!$id_penugasan) {
            Flasher::setFlash('ID Penugasan tidak valid!', 'danger');
            header('Location: ' . BASEURL . '/guru/dashboard');
            exit;
        }

        $rppModel = $this->model('RPP_model');
        $penugasanModel = $this->model('Penugasan_model');

        // Get penugasan data
        $penugasan = $penugasanModel->getPenugasanById($id_penugasan);
        if (!$penugasan) {
            Flasher::setFlash('Penugasan tidak ditemukan!', 'danger');
            header('Location: ' . BASEURL . '/guru/dashboard');
            exit;
        }

        // Check if RPP already exists
        $id_guru = $_SESSION['id_ref'] ?? null;
        $id_tp = $_SESSION['id_tp_aktif'] ?? null;
        $id_semester = $_SESSION['id_semester_aktif'] ?? null;

        $existing = $rppModel->checkExisting(
            $id_guru,
            $penugasan['id_mapel'],
            $penugasan['id_kelas'],
            $id_tp,
            $id_semester
        );

        if ($existing) {
            // Edit existing
            header('Location: ' . BASEURL . '/guru/editRPP/' . $existing['id_rpp']);
        } else {
            // Create new
            header('Location: ' . BASEURL . '/guru/tambahRPP?id_penugasan=' . $id_penugasan);
        }
        exit;
    }

    /**
     * Form tambah RPP baru
     */
    public function tambahRPP()
    {
        $id_penugasan = $_GET['id_penugasan'] ?? null;
        if (!$id_penugasan) {
            Flasher::setFlash('ID Penugasan tidak valid!', 'danger');
            header('Location: ' . BASEURL . '/guru/dashboard');
            exit;
        }

        $penugasanModel = $this->model('Penugasan_model');
        $penugasan = $penugasanModel->getPenugasanById($id_penugasan);

        if (!$penugasan) {
            Flasher::setFlash('Penugasan tidak ditemukan!', 'danger');
            header('Location: ' . BASEURL . '/guru/dashboard');
            exit;
        }

        // Get pengaturan rapor for kop, kepala sekolah, etc
        $rppModel = $this->model('RPP_model');
        $pengaturan = $rppModel->getPengaturanRapor(
            $penugasan['id_kelas'],
            $_SESSION['id_tp_aktif'] ?? null
        );

        // Get dynamic template
        $templateModel = $this->model('RPPTemplate_model');
        $template = $templateModel->getFullTemplate();

        $this->data['judul'] = 'Tambah RPP';
        $this->data['penugasan'] = $penugasan;
        $this->data['pengaturan'] = $pengaturan;
        $this->data['id_penugasan'] = $id_penugasan;
        $this->data['template'] = $template;

        $this->view('templates/header', $this->data);
        $this->loadSidebar();
        $this->view('guru/tambah_rpp', $this->data);
        $this->view('templates/footer', $this->data);
    }

    /**
     * Form edit RPP
     */
    public function editRPP($id_rpp = null)
    {
        if (!$id_rpp) {
            Flasher::setFlash('ID RPP tidak valid!', 'danger');
            header('Location: ' . BASEURL . '/guru/dashboard');
            exit;
        }

        $rppModel = $this->model('RPP_model');
        $rpp = $rppModel->getRPPById($id_rpp);

        if (!$rpp) {
            Flasher::setFlash('RPP tidak ditemukan!', 'danger');
            header('Location: ' . BASEURL . '/guru/dashboard');
            exit;
        }

        // Security: ensure guru owns this RPP
        if ($rpp['id_guru'] != ($_SESSION['id_ref'] ?? null)) {
            Flasher::setFlash('Akses ditolak!', 'danger');
            header('Location: ' . BASEURL . '/guru/dashboard');
            exit;
        }

        // Get pengaturan rapor
        $pengaturan = $rppModel->getPengaturanRapor($rpp['id_kelas'], $rpp['id_tp']);

        // Get dynamic template (without pre-filled data, will use JSON from rpp_field_values)
        $templateModel = $this->model('RPPTemplate_model');
        $template = $templateModel->getFullTemplate();

        $this->data['judul'] = 'Edit RPP';
        $this->data['rpp'] = $rpp;
        $this->data['pengaturan'] = $pengaturan;
        $this->data['template'] = $template;

        $this->view('templates/header', $this->data);
        $this->loadSidebar();
        $this->view('guru/edit_rpp', $this->data);
        $this->view('templates/footer', $this->data);
    }

    /**
     * Lihat daftar RPP dari id_penugasan
     */
    public function lihatRPP($id_penugasan = null)
    {
        $rppModel = $this->model('RPP_model');
        $id_guru = $_SESSION['id_ref'] ?? null;
        $id_tp = $_SESSION['id_tp_aktif'] ?? null;
        $id_semester = $_SESSION['id_semester_aktif'] ?? null;

        // If id_penugasan provided, show specific RPP
        if ($id_penugasan) {
            $penugasanModel = $this->model('Penugasan_model');
            $penugasan = $penugasanModel->getPenugasanById($id_penugasan);

            if (!$penugasan) {
                Flasher::setFlash('Penugasan tidak ditemukan!', 'danger');
                header('Location: ' . BASEURL . '/guru/dashboard');
                exit;
            }

            $rpp = $rppModel->checkExisting(
                $id_guru,
                $penugasan['id_mapel'],
                $penugasan['id_kelas'],
                $id_tp,
                $id_semester
            );

            if (!$rpp) {
                Flasher::setFlash('RPP belum dibuat untuk mapel/kelas ini.', 'warning');
                header('Location: ' . BASEURL . '/guru/buatRPP/' . $id_penugasan);
                exit;
            }

            header('Location: ' . BASEURL . '/guru/detailRPP/' . $rpp['id_rpp']);
            exit;
        }

        // Show all RPP for this guru
        $list_rpp = $rppModel->getAllRPPByGuru($id_guru, $id_tp, $id_semester);

        $this->data['judul'] = 'Daftar RPP';
        $this->data['list_rpp'] = $list_rpp;

        $this->view('templates/header', $this->data);
        $this->loadSidebar();
        $this->view('guru/list_rpp', $this->data);
        $this->view('templates/footer', $this->data);
    }

    /**
     * Detail RPP
     */
    public function detailRPP($id_rpp = null)
    {
        if (!$id_rpp) {
            Flasher::setFlash('ID RPP tidak valid!', 'danger');
            header('Location: ' . BASEURL . '/guru/dashboard');
            exit;
        }

        $rppModel = $this->model('RPP_model');
        $rpp = $rppModel->getRPPById($id_rpp);

        if (!$rpp) {
            Flasher::setFlash('RPP tidak ditemukan!', 'danger');
            header('Location: ' . BASEURL . '/guru/dashboard');
            exit;
        }

        // Get pengaturan rapor
        $pengaturan = $rppModel->getPengaturanRapor($rpp['id_kelas'], $rpp['id_tp']);

        // Load template sections and fields for dynamic display
        $templateModel = $this->model('RPPTemplate_model');
        $sectionsList = $templateModel->getAllSections(true); // only active sections
        $sections = [];

        foreach ($sectionsList as $section) {
            $section['fields'] = $templateModel->getFieldsBySection($section['id_section'], true);
            $sections[] = $section;
        }

        $this->data['judul'] = 'Detail RPP';
        $this->data['rpp'] = $rpp;
        $this->data['pengaturan'] = $pengaturan;
        $this->data['sections'] = $sections;

        $this->view('templates/header', $this->data);
        $this->loadSidebar();
        $this->view('guru/detail_rpp', $this->data);
        $this->view('templates/footer', $this->data);
    }

    /**
     * Simpan RPP Dinamis (menggunakan template)
     */
    public function simpanRPPDinamis()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/guru/dashboard');
            exit;
        }

        $rppModel = $this->model('RPP_model');
        $id_rpp = $_POST['id_rpp'] ?? null;

        // Collect dynamic field values as JSON
        $fieldValues = [];
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'field_') === 0 && strpos($key, '_existing') === false) {
                $fieldValues[$key] = $value;
            }
        }

        // Handle file uploads for dynamic fields
        // Gunakan absolute path berdasarkan APPROOT (c:\laragon\www\absen)
        $upload_dir = APPROOT . '/public/uploads/rpp/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        foreach ($_FILES as $fileKey => $fileData) {
            if (strpos($fileKey, 'field_file_') === 0 && $fileData['error'] === UPLOAD_ERR_OK) {
                // Extract field ID from key (field_file_123 -> field_123)
                $fieldId = str_replace('field_file_', '', $fileKey);
                $fieldName = 'field_' . $fieldId;

                // Validate file size (max 5MB)
                if ($fileData['size'] > 5 * 1024 * 1024) {
                    continue; // Skip file yang terlalu besar
                }

                // Validate extension
                $allowedExt = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'jpg', 'jpeg', 'png'];
                $ext = strtolower(pathinfo($fileData['name'], PATHINFO_EXTENSION));
                if (!in_array($ext, $allowedExt)) {
                    continue; // Skip file dengan extension tidak valid
                }

                // Generate unique filename
                $fileName = 'RPP_' . time() . '_' . uniqid() . '.' . $ext;
                $filePath = $upload_dir . $fileName;

                if (move_uploaded_file($fileData['tmp_name'], $filePath)) {
                    $fieldValues[$fieldName] = $fileName;
                }
            }
        }

        // Keep existing files if no new upload
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'field_') === 0 && strpos($key, '_existing') !== false) {
                $originalKey = str_replace('_existing', '', $key);
                if (!isset($fieldValues[$originalKey]) || empty($fieldValues[$originalKey])) {
                    $fieldValues[$originalKey] = $value;
                }
            }
        }

        try {
            if ($id_rpp) {
                // Update existing RPP
                $data = [
                    'alokasi_waktu' => $_POST['alokasi_waktu'] ?? '',
                    'tanggal_rpp' => $_POST['tanggal_rpp'] ?? null,
                    'rpp_field_values' => json_encode($fieldValues),
                    'status' => $_POST['status'] ?? 'draft'
                ];
                $rppModel->updateRPPDinamis($id_rpp, $data);
            } else {
                // Create new RPP
                $data = [
                    'id_guru' => $_SESSION['id_ref'] ?? null,
                    'id_mapel' => $_POST['id_mapel'],
                    'id_kelas' => $_POST['id_kelas'],
                    'id_tp' => $_SESSION['id_tp_aktif'] ?? null,
                    'id_semester' => $_SESSION['id_semester_aktif'] ?? null,
                    'id_penugasan' => $_POST['id_penugasan'] ?? null,
                    'alokasi_waktu' => $_POST['alokasi_waktu'] ?? '',
                    'tanggal_rpp' => $_POST['tanggal_rpp'] ?? null,
                    'rpp_field_values' => json_encode($fieldValues),
                    'status' => $_POST['status'] ?? 'draft'
                ];

                $id_rpp = $rppModel->tambahRPPDinamis($data);
            }

            Flasher::setFlash('RPP berhasil disimpan!', 'success');
            header('Location: ' . BASEURL . '/guru/detailRPP/' . $id_rpp);
        } catch (Exception $e) {
            Flasher::setFlash('Gagal menyimpan RPP: ' . $e->getMessage(), 'danger');
            header('Location: ' . BASEURL . '/guru/dashboard');
        }
        exit;
    }

    /**
     * Simpan RPP (create/update) - Legacy method
     */
    public function simpanRPP()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/guru/dashboard');
            exit;
        }

        $rppModel = $this->model('RPP_model');
        $id_rpp = $_POST['id_rpp'] ?? null;

        // File upload dihapus sesuai requirement terbaru
        $file_rpp = $_POST['existing_file'] ?? null;

        $data = [
            'alokasi_waktu' => $_POST['alokasi_waktu'] ?? '',
            'tanggal_rpp' => $_POST['tanggal_rpp'] ?? null,
            'materi_pelajaran' => $_POST['materi_pelajaran'] ?? '',
            'dimensi_profil_lulusan' => $_POST['dimensi_profil_lulusan'] ?? '',
            'materi_integrasi_kbc' => $_POST['materi_integrasi_kbc'] ?? '',
            'capaian_pembelajaran' => $_POST['capaian_pembelajaran'] ?? '',
            'tujuan_pembelajaran' => $_POST['tujuan_pembelajaran'] ?? '',
            'praktik_pedagogis' => $_POST['praktik_pedagogis'] ?? '',
            'kemitraan_pembelajaran' => $_POST['kemitraan_pembelajaran'] ?? '',
            'lingkungan_pembelajaran' => $_POST['lingkungan_pembelajaran'] ?? '',
            'pemanfaatan_digital' => $_POST['pemanfaatan_digital'] ?? '',
            'kegiatan_awal' => $_POST['kegiatan_awal'] ?? '',
            'kegiatan_inti_memahami' => $_POST['kegiatan_inti_memahami'] ?? '',
            'kegiatan_inti_mengaplikasi' => $_POST['kegiatan_inti_mengaplikasi'] ?? '',
            'kegiatan_inti_merefleksi' => $_POST['kegiatan_inti_merefleksi'] ?? '',
            'kegiatan_penutup' => $_POST['kegiatan_penutup'] ?? '',
            'asesmen_awal' => $_POST['asesmen_awal'] ?? '',
            'asesmen_proses' => $_POST['asesmen_proses'] ?? '',
            'asesmen_akhir' => $_POST['asesmen_akhir'] ?? '',
            'file_rpp' => $file_rpp ?? ($_POST['existing_file'] ?? null),
            'status' => $_POST['status'] ?? 'draft'
        ];

        try {
            if ($id_rpp) {
                // Update
                $result = $rppModel->updateRPP($id_rpp, $data);
                Flasher::setFlash('RPP berhasil diupdate!', 'success');
                header('Location: ' . BASEURL . '/guru/detailRPP/' . $id_rpp);
            } else {
                // Create new
                $data['id_guru'] = $_SESSION['id_ref'] ?? null;
                $data['id_mapel'] = $_POST['id_mapel'];
                $data['id_kelas'] = $_POST['id_kelas'];
                $data['id_tp'] = $_SESSION['id_tp_aktif'] ?? null;
                $data['id_semester'] = $_SESSION['id_semester_aktif'] ?? null;

                $new_id = $rppModel->tambahRPP($data);
                Flasher::setFlash('RPP berhasil dibuat!', 'success');
                header('Location: ' . BASEURL . '/guru/detailRPP/' . $new_id);
            }
        } catch (Exception $e) {
            Flasher::setFlash('Gagal menyimpan RPP: ' . $e->getMessage(), 'danger');
            header('Location: ' . BASEURL . '/guru/dashboard');
        }
        exit;
    }

    /**
     * Submit RPP for review
     */
    public function submitRPP($id_rpp = null)
    {
        if (!$id_rpp) {
            Flasher::setFlash('ID RPP tidak valid!', 'danger');
            header('Location: ' . BASEURL . '/guru/dashboard');
            exit;
        }

        $rppModel = $this->model('RPP_model');
        $rpp = $rppModel->getRPPById($id_rpp);

        if (!$rpp || $rpp['id_guru'] != ($_SESSION['id_ref'] ?? null)) {
            Flasher::setFlash('Akses ditolak!', 'danger');
            header('Location: ' . BASEURL . '/guru/dashboard');
            exit;
        }

        $rppModel->submitRPP($id_rpp);
        Flasher::setFlash('RPP berhasil diajukan untuk review!', 'success');
        header('Location: ' . BASEURL . '/guru/detailRPP/' . $id_rpp);
        exit;
    }

    /**
     * Delete RPP
     */
    public function hapusRPP($id_rpp = null)
    {
        if (!$id_rpp) {
            Flasher::setFlash('ID RPP tidak valid!', 'danger');
            header('Location: ' . BASEURL . '/guru/dashboard');
            exit;
        }

        $rppModel = $this->model('RPP_model');
        $rpp = $rppModel->getRPPById($id_rpp);

        if (!$rpp || $rpp['id_guru'] != ($_SESSION['id_ref'] ?? null)) {
            Flasher::setFlash('Akses ditolak!', 'danger');
            header('Location: ' . BASEURL . '/guru/dashboard');
            exit;
        }

        // Delete file if exists
        if ($rpp['file_rpp']) {
            $file_path = 'public/uploads/rpp/' . $rpp['file_rpp'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }

        $rppModel->hapusRPP($id_rpp);
        Flasher::setFlash('RPP berhasil dihapus!', 'success');
        header('Location: ' . BASEURL . '/guru/dashboard');
        exit;
    }

    /**
     * Download RPP as PDF
     */
    public function downloadRPPPDF($id_rpp = null)
    {
        if (!$id_rpp) {
            echo "ID RPP tidak valid!";
            exit;
        }

        $rppModel = $this->model('RPP_model');
        $rpp = $rppModel->getRPPById($id_rpp);

        if (!$rpp) {
            echo "RPP tidak ditemukan!";
            exit;
        }

        // Get pengaturan rapor
        $pengaturan = $rppModel->getPengaturanRapor($rpp['id_kelas'], $rpp['id_tp']);

        // Load template sections and fields for dynamic display
        $templateModel = $this->model('RPPTemplate_model');
        $sectionsList = $templateModel->getAllSections(true); // only active sections
        $sections = [];

        foreach ($sectionsList as $section) {
            $section['fields'] = $templateModel->getFieldsBySection($section['id_section'], true);
            $sections[] = $section;
        }

        ob_start();
        include __DIR__ . '/../views/guru/cetak_rpp.php';
        $html = ob_get_clean();

        // Inject QR validation with metadata
        require_once __DIR__ . '/../core/PDFQRHelper.php';
        $meta = [
            'jenis' => 'rpp',
            'mapel' => $rpp['nama_mapel'] ?? '',
            'kelas' => $rpp['nama_kelas'] ?? '',
            'semester' => $_SESSION['nama_semester_aktif'] ?? '',
            'printed_at' => gmdate('Y-m-d H:i:s'),
            'printed_by' => ($_SESSION['user_nama_lengkap'] ?? ($_SESSION['nama_lengkap'] ?? 'Pengguna')),
        ];
        $html = PDFQRHelper::addQRToPDF($html, 'rpp', $rpp['id_rpp'] ?? 0, $meta);

        require_once __DIR__ . '/../core/dompdf/autoload.inc.php';
        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'RPP_' . $rpp['nama_mapel'] . '_' . $rpp['nama_kelas'] . '_' . date('Y-m-d') . '.pdf';
        $dompdf->stream($filename, ['Attachment' => 1]);
        exit;
    }

    // =================================================================
    // PESAN (MESSAGING INBOX)
    // =================================================================

    /**
     * Inbox pesan guru
     */
    public function pesan()
    {
        $this->data['judul'] = 'Kotak Masuk Pesan';
        $id_guru = $_SESSION['id_ref'] ?? 0;

        $pesanModel = $this->model('Pesan_model');
        $this->data['pesan'] = $pesanModel->getInbox('guru', $id_guru);
        $this->data['unread_count'] = $pesanModel->getUnreadCount('guru', $id_guru);

        $this->view('templates/header', $this->data);
        $this->loadSidebar();
        $this->view('guru/pesan', $this->data);
        $this->view('templates/footer', $this->data);
    }

    /**
     * Detail pesan guru
     */
    public function detailPesan($id = null)
    {
        if (!$id) {
            header('Location: ' . BASEURL . '/guru/pesan');
            exit;
        }

        $id_guru = $_SESSION['id_ref'] ?? 0;
        $pesanModel = $this->model('Pesan_model');

        // Cek apakah guru ini penerima
        if (!$pesanModel->isPenerima($id, 'guru', $id_guru)) {
            Flasher::setFlash('Anda tidak memiliki akses ke pesan ini!', 'error');
            header('Location: ' . BASEURL . '/guru/pesan');
            exit;
        }

        // Tandai sudah dibaca
        $pesanModel->tandaiDibaca($id, 'guru', $id_guru);

        $this->data['pesan'] = $pesanModel->getPesanById($id);
        $this->data['judul'] = 'Detail Pesan';

        $this->view('templates/header', $this->data);
        $this->loadSidebar();
        $this->view('guru/detail_pesan', $this->data);
        $this->view('templates/footer', $this->data);
    }
}
?>