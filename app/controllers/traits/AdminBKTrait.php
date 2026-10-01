<?php

trait AdminBKTrait
{
    function bimbinganKonseling()
    {
        $this->data['judul'] = 'Bimbingan Konseling';
        $id_tp = $_SESSION['id_tp_aktif'] ?? 0;

        $bkModel = $this->model('BK_model');

        $filters = [];
        if (!empty($_GET['kelas'])) $filters['id_kelas'] = (int) $_GET['kelas'];
        if (!empty($_GET['status'])) $filters['status'] = $_GET['status'];
        if (!empty($_GET['kategori'])) $filters['kategori'] = $_GET['kategori'];
        if (!empty($_GET['tingkat'])) $filters['tingkat'] = $_GET['tingkat'];
        if (!empty($_GET['q'])) $filters['search'] = trim($_GET['q']);

        $this->data['kasus_list'] = $bkModel->getAllKasus($id_tp, $filters);
        $this->data['stats'] = $bkModel->countKasusByStatus($id_tp);
        $this->data['kelas_list'] = $this->model('Kelas_model')->getAllKelasWithDetails($id_tp);
        $this->data['guru_bk_list'] = $bkModel->getGuruBKList($id_tp);
        $this->data['filters'] = $filters;

        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/bk_kasus', $this->data);
        $this->view('templates/footer', $this->data);
    }

    function detailKasusBK($id_kasus = 0)
    {
        $this->data['judul'] = 'Detail Kasus BK';
        $id_kasus = (int) $id_kasus;

        $bkModel = $this->model('BK_model');
        $kasus = $bkModel->getKasusById($id_kasus);

        if (!$kasus) {
            Flasher::setFlash('Gagal', 'Data kasus tidak ditemukan.', 'danger');
            header('Location: ' . BASEURL . '/admin/bimbinganKonseling');
            exit;
        }

        $this->data['kasus'] = $kasus;
        $this->data['konseling_list'] = $bkModel->getKonselingByKasus($id_kasus);
        $this->data['panggilan_list'] = $bkModel->getPanggilanByKasus($id_kasus);

        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/bk_detail', $this->data);
        $this->view('templates/footer', $this->data);
    }

    function hapusKasusBK()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/admin/bimbinganKonseling');
            exit;
        }

        $id_kasus = (int) ($_POST['id_kasus'] ?? 0);
        $bkModel = $this->model('BK_model');
        $bkModel->deleteKasus($id_kasus);
        Flasher::setFlash('Berhasil', 'Kasus BK berhasil dihapus.', 'success');
        header('Location: ' . BASEURL . '/admin/bimbinganKonseling');
        exit;
    }
}
