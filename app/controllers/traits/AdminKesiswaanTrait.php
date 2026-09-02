<?php
// File: app/controllers/traits/AdminKesiswaanTrait.php
// v1.27.0 - Izin Siswa management for admin

trait AdminKesiswaanTrait
{
    function izinSiswa()
    {
        $this->data['judul'] = 'Izin Siswa';
        $id_tp = $_SESSION['id_tp_aktif'] ?? 0;

        $izinModel = $this->model('IzinSiswa_model');
        $izinModel->selesaikanIzinExpired();

        $filters = [];
        if (!empty($_GET['kelas'])) $filters['id_kelas'] = (int) $_GET['kelas'];
        if (!empty($_GET['status'])) $filters['status'] = $_GET['status'];
        if (!empty($_GET['jenis'])) $filters['jenis'] = $_GET['jenis'];
        if (!empty($_GET['bulan'])) $filters['bulan'] = (int) $_GET['bulan'];
        if (!empty($_GET['q'])) $filters['search'] = trim($_GET['q']);

        $this->data['izin_list'] = $izinModel->getAllIzin($id_tp, $filters);
        $this->data['stats'] = $izinModel->countAllIzinByStatus($id_tp);
        $this->data['kelas_list'] = $this->model('Kelas_model')->getAllKelasWithDetails($id_tp);
        $this->data['filters'] = $filters;

        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/izin_siswa', $this->data);
        $this->view('templates/footer', $this->data);
    }

    function adminBatalkanIzin()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/admin/izinSiswa');
            exit;
        }

        $id_izin = (int) ($_POST['id_izin'] ?? 0);
        if (!$id_izin) {
            Flasher::setFlash('Gagal', 'ID izin tidak valid.', 'danger');
            header('Location: ' . BASEURL . '/admin/izinSiswa');
            exit;
        }

        $izinModel = $this->model('IzinSiswa_model');
        $izin = $izinModel->getIzinById($id_izin);
        if (!$izin) {
            Flasher::setFlash('Gagal', 'Data izin tidak ditemukan.', 'danger');
            header('Location: ' . BASEURL . '/admin/izinSiswa');
            exit;
        }

        if ($izin['status'] !== 'aktif') {
            Flasher::setFlash('Info', 'Izin ini sudah tidak aktif.', 'warning');
            header('Location: ' . BASEURL . '/admin/izinSiswa');
            exit;
        }

        $izinModel->batalkanIzin($id_izin);
        Flasher::setFlash('Berhasil', 'Izin siswa ' . htmlspecialchars($izin['nama_siswa']) . ' telah dibatalkan.', 'success');
        header('Location: ' . BASEURL . '/admin/izinSiswa');
        exit;
    }

    function detailIzin($id_izin = 0)
    {
        $this->data['judul'] = 'Detail Izin Siswa';
        $id_izin = (int) $id_izin;

        $izinModel = $this->model('IzinSiswa_model');
        $izin = $izinModel->getIzinById($id_izin);

        if (!$izin) {
            Flasher::setFlash('Gagal', 'Data izin tidak ditemukan.', 'danger');
            header('Location: ' . BASEURL . '/admin/izinSiswa');
            exit;
        }

        $this->data['izin'] = $izin;

        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/detail_izin', $this->data);
        $this->view('templates/footer', $this->data);
    }

    function adminTambahIzin()
    {
        $this->data['judul'] = 'Tambah Izin Siswa';
        $id_tp = $_SESSION['id_tp_aktif'] ?? 0;

        $this->data['kelas_list'] = $this->model('Kelas_model')->getAllKelasWithDetails($id_tp);
        $this->data['siswa_list'] = [];

        $id_kelas = (int) ($_GET['kelas'] ?? 0);
        if ($id_kelas) {
            $this->data['siswa_list'] = $this->model('Kelas_model')->getSiswaByKelas($id_kelas, $id_tp);
            $this->data['selected_kelas'] = $id_kelas;
        }

        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/tambah_izin', $this->data);
        $this->view('templates/footer', $this->data);
    }

    function adminProsesTambahIzin()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/admin/izinSiswa');
            exit;
        }

        $id_tp = $_SESSION['id_tp_aktif'] ?? 0;
        $id_siswa = (int) ($_POST['id_siswa'] ?? 0);
        $id_kelas = (int) ($_POST['id_kelas'] ?? 0);
        $jenis_izin = $_POST['jenis_izin'] ?? '';
        $tanggal_mulai = $_POST['tanggal_mulai'] ?? '';
        $tanggal_selesai = $_POST['tanggal_selesai'] ?? '';
        $keterangan = trim($_POST['keterangan'] ?? '');

        if (!$id_siswa || !$id_kelas || !in_array($jenis_izin, ['I', 'S', 'D']) || !$tanggal_mulai || !$tanggal_selesai) {
            Flasher::setFlash('Data tidak lengkap.', 'danger');
            header('Location: ' . BASEURL . '/admin/adminTambahIzin?kelas=' . $id_kelas);
            exit;
        }

        if ($tanggal_selesai < $tanggal_mulai) {
            Flasher::setFlash('Tanggal selesai tidak boleh sebelum tanggal mulai.', 'danger');
            header('Location: ' . BASEURL . '/admin/adminTambahIzin?kelas=' . $id_kelas);
            exit;
        }

        $izinModel = $this->model('IzinSiswa_model');
        if ($izinModel->cekIzinOverlap($id_siswa, $tanggal_mulai, $tanggal_selesai)) {
            Flasher::setFlash('Siswa sudah memiliki izin aktif pada rentang tanggal tersebut.', 'danger');
            header('Location: ' . BASEURL . '/admin/adminTambahIzin?kelas=' . $id_kelas);
            exit;
        }

        $id_guru_input = ($_SESSION['role'] === 'admin') ? 0 : ($_SESSION['id_ref'] ?? 0);

        $data = [
            'id_siswa' => $id_siswa,
            'id_kelas' => $id_kelas,
            'id_tp' => $id_tp,
            'jenis_izin' => $jenis_izin,
            'tanggal_mulai' => $tanggal_mulai,
            'tanggal_selesai' => $tanggal_selesai,
            'keterangan' => $keterangan,
            'bukti_file' => null,
            'id_guru_input' => $id_guru_input,
        ];

        if ($izinModel->tambahIzin($data)) {
            $jenisLabel = ['I' => 'Izin', 'S' => 'Sakit', 'D' => 'Dispensasi'];
            Flasher::setFlash('Berhasil', 'Izin siswa berhasil ditambahkan (' . ($jenisLabel[$jenis_izin] ?? '') . ').', 'success');
        } else {
            Flasher::setFlash('Gagal', 'Gagal menambahkan izin siswa.', 'danger');
        }

        header('Location: ' . BASEURL . '/admin/izinSiswa');
        exit;
    }
}
