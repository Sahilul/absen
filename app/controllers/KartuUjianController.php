<?php
// File: app/controllers/KartuUjianController.php
// v1.37.0 - Kartu Ujian (admin): kartu peserta ujian + kartu meja.
// Kop otomatis per lembaga (berdasarkan jenjang kelas siswa).
// Method `verifikasi` bersifat PUBLIK (target QR di kartu).

class KartuUjianController extends Controller
{
    private $data = [];
    private $kartuModel;

    public function __construct()
    {
        $this->kartuModel = $this->model('KartuUjian_model');

        // Halaman verifikasi QR boleh diakses tanpa login
        $url = explode('/', trim($_GET['url'] ?? '', '/'));
        if (($url[1] ?? '') === 'verifikasi') {
            return;
        }

        if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
            header('Location: ' . BASEURL . '/auth/login');
            exit;
        }
        $this->data['daftar_semester'] = $this->model('TahunPelajaran_model')->getAllSemester();
    }

    // ------------------------------------------------------------------
    // Halaman admin
    // ------------------------------------------------------------------
    public function index()
    {
        $id_tp = $_SESSION['id_tp_aktif'] ?? 0;
        $this->data['judul'] = 'Kartu Ujian';
        $this->data['kartu_list'] = $this->kartuModel->getAll($id_tp);
        $this->data['lembaga_list'] = $this->model('Lembaga_model')->getAll(true);
        $this->data['kelas_list'] = $this->model('Kelas_model')->getKelasByTP($id_tp);

        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/kartu_ujian/index', $this->data);
        $this->view('templates/footer', $this->data);
    }

    public function simpan()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/kartuUjian');
            exit;
        }
        $nama = trim($_POST['nama_ujian'] ?? '');
        $id = (int) ($_POST['id_kartu_ujian'] ?? 0);

        if ($nama === '' || mb_strlen($nama) > 200) {
            Flasher::setFlash('Nama ujian wajib diisi (maks. 200 karakter).', 'danger');
        } elseif ($id > 0) {
            $this->kartuModel->update($id, $nama);
            Flasher::setFlash('Nama ujian diperbarui.', 'success');
        } else {
            $this->kartuModel->insert([
                'nama_ujian' => $nama,
                'id_tp' => $_SESSION['id_tp_aktif'] ?? 0,
                'id_semester' => $_SESSION['id_semester_aktif'] ?? null,
                'created_by' => $_SESSION['user_id'] ?? null,
            ]);
            Flasher::setFlash('Ujian ditambahkan.', 'success');
        }
        header('Location: ' . BASEURL . '/kartuUjian');
        exit;
    }

    public function hapus($id = 0)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && (int) $id > 0) {
            $this->kartuModel->delete($id);
            Flasher::setFlash('Ujian dihapus.', 'success');
        }
        header('Location: ' . BASEURL . '/kartuUjian');
        exit;
    }

    /**
     * Cetak kartu.
     * GET ?jenis=kartu|meja&filter=all|lembaga:{id}|kelas:{id}
     */
    public function cetak($id = 0)
    {
        $ujian = $this->kartuModel->getById($id);
        if (!$ujian) {
            Flasher::setFlash('Data ujian tidak ditemukan.', 'danger');
            header('Location: ' . BASEURL . '/kartuUjian');
            exit;
        }

        $jenis = ($_GET['jenis'] ?? 'kartu') === 'meja' ? 'meja' : 'kartu';
        $filter = $_GET['filter'] ?? 'all';
        if (!preg_match('/^(all|lembaga:\d+|kelas:\d+)$/', $filter)) {
            $filter = 'all';
        }

        $lembagaList = $this->model('Lembaga_model')->getAll(true);
        $peserta = $this->kartuModel->getPeserta($ujian['id_tp'], $filter, $lembagaList);

        // Map jenjang -> lembaga (kop, kepala)
        $lembagaByJenjang = [];
        foreach ($lembagaList as $l) {
            $l['kop_url'] = kopRaporUrl(['kop_rapor' => $l['kop_rapor'], 'lembaga_id' => $l['id_lembaga']]);
            foreach (array_filter(array_map('trim', explode(',', $l['jenjang']))) as $j) {
                $lembagaByJenjang[$j] = $l;
            }
        }

        foreach ($peserta as &$p) {
            $p['lembaga'] = $lembagaByJenjang[trim($p['jenjang'])] ?? null;
            if ($jenis === 'kartu') {
                $p['verify_url'] = BASEURL . '/kartuUjian/verifikasi/' . $this->buildToken($ujian['id_kartu_ujian'], $p['id_siswa']);
            }
        }
        unset($p);

        $this->data['judul'] = ($jenis === 'meja' ? 'Kartu Meja - ' : 'Kartu Ujian - ') . $ujian['nama_ujian'];
        $this->data['ujian'] = $ujian;
        $this->data['peserta'] = $peserta;
        $this->view($jenis === 'meja' ? 'admin/kartu_ujian/cetak_meja' : 'admin/kartu_ujian/cetak_kartu', $this->data);
    }

    // ------------------------------------------------------------------
    // Verifikasi QR (PUBLIK)
    // ------------------------------------------------------------------
    public function verifikasi($token = '')
    {
        $this->data['valid'] = false;
        $this->data['ujian'] = null;
        $this->data['siswa'] = null;
        $this->data['lembaga'] = null;

        if (preg_match('/^(\d+)-(\d+)-([a-f0-9]{16})$/', (string) $token, $m)) {
            $expected = $this->buildToken($m[1], $m[2]);
            if (hash_equals($expected, $token)) {
                $ujian = $this->kartuModel->getById($m[1]);
                $siswa = $ujian ? $this->kartuModel->getPesertaSingle($ujian['id_tp'], $m[2]) : null;
                if ($ujian && $siswa) {
                    $this->data['valid'] = true;
                    $this->data['ujian'] = $ujian;
                    $this->data['siswa'] = $siswa;
                    $this->data['lembaga'] = $this->model('Lembaga_model')->getByJenjang($siswa['jenjang']);
                }
            }
        }
        $this->view('admin/kartu_ujian/verifikasi', $this->data);
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------
    private function buildToken($id_kartu, $id_siswa)
    {
        require_once APPROOT . '/config/qrcode.php';
        $sig = substr(hash_hmac('sha256', 'kartu_ujian|' . (int) $id_kartu . '|' . (int) $id_siswa, QR_TOKEN_SALT), 0, 16);
        return (int) $id_kartu . '-' . (int) $id_siswa . '-' . $sig;
    }
}
