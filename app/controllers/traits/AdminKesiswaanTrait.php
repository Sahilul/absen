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
        $this->data['kelas_list'] = $this->model('Kelas_model')->getAllKelas();
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
}
