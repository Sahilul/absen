<?php
// File: app/controllers/traits/AdminPembayaranTrait.php
// v1.25.0 - Hasil pemecahan AdminController.php (domain: Pembayaran & tagihan).
// Perilaku identik dengan method asli; hanya dipindahkan agar mudah dipelihara.

trait AdminPembayaranTrait
{
function pembayaran()
    {
        $this->data['judul'] = 'Pembayaran Sekolah';

        $id_tp_aktif = $_SESSION['id_tp_aktif'] ?? 0;
        $id_semester_aktif = $_SESSION['id_semester_aktif'] ?? null;

        // Get all kelas dengan wali kelas
        $kelasModel = $this->model('Kelas_model');
        $waliKelasModel = $this->model('WaliKelas_model');
        $pembayaranModel = $this->model('Pembayaran_model');

        $kelasList = $kelasModel->getAllKelasWithDetails($id_tp_aktif);

        // Enrich dengan info wali kelas dan total tagihan
        foreach ($kelasList as &$kelas) {
            $waliKelas = $waliKelasModel->getWaliKelasByKelas($kelas['id_kelas'], $id_tp_aktif);
            $kelas['wali_kelas'] = $waliKelas;

            // Get total tagihan untuk kelas ini
            $tagihanList = $pembayaranModel->getTagihanKelas($kelas['id_kelas'], $id_tp_aktif, $id_semester_aktif);
            $kelas['total_tagihan'] = count($tagihanList);
        }

        $this->data['kelas_list'] = $kelasList;

        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/pembayaran', $this->data);
        $this->view('templates/footer');
    }

function pembayaranKelas($id_kelas)
    {
        $this->data['judul'] = 'Pembayaran Kelas';

        $id_tp_aktif = $_SESSION['id_tp_aktif'] ?? 0;
        $id_semester_aktif = $_SESSION['id_semester_aktif'] ?? null;

        // Get kelas info
        $kelas = $this->model('Kelas_model')->getKelasById($id_kelas);
        if (!$kelas) {
            header('Location: ' . BASEURL . '/admin/pembayaran');
            exit;
        }

        $this->data['kelas'] = $kelas;
        $this->data['tagihan_list'] = $this->model('Pembayaran_model')->getTagihanKelas($id_kelas, $id_tp_aktif, $id_semester_aktif);

        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/pembayaran_kelas', $this->data);
        $this->view('templates/footer');
    }

function pembayaranTagihan($id_tagihan)
    {
        $this->data['judul'] = 'Pembayaran Detail Tagihan';

        $id_tp_aktif = $_SESSION['id_tp_aktif'] ?? 0;

        // Get tagihan
        $tagihan = $this->model('Pembayaran_model')->getTagihanById($id_tagihan);
        if (!$tagihan) {
            header('Location: ' . BASEURL . '/admin/pembayaran');
            exit;
        }

        $this->data['tagihan'] = $tagihan;

        // Get kelas info
        $kelas = $this->model('Kelas_model')->getKelasById($tagihan['id_kelas']);
        $this->data['kelas'] = $kelas;

        // Get siswa list
        $this->data['siswa_list'] = $this->model('Siswa_model')->getSiswaByKelas($tagihan['id_kelas'], $id_tp_aktif);

        // Get tagihan siswa mapping
        $this->data['tagihan_siswa'] = $this->model('Pembayaran_model')->getTagihanSiswaList($id_tagihan);

        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/pembayaran_tagihan', $this->data);
        $this->view('templates/footer');
    }

function pembayaranRiwayat()
    {
        $this->data['judul'] = 'Pembayaran Riwayat Transaksi';

        $id_tp_aktif = $_SESSION['id_tp_aktif'] ?? 0;

        // Get all transactions across all classes
        $this->data['riwayat'] = $this->model('Pembayaran_model')->getRiwayatAll($id_tp_aktif, 500);

        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/pembayaran_riwayat', $this->data);
        $this->view('templates/footer');
    }

function bayar()
    {
        $this->data['judul'] = 'Pembayaran Siswa';

        $id_tp_aktif = $_SESSION['id_tp_aktif'] ?? 0;

        // Get all kelas for filter dropdown
        $this->data['kelas_list'] = $this->model('Kelas_model')->getKelasByTP($id_tp_aktif);

        // Get filter from GET
        $id_kelas = $_GET['id_kelas'] ?? null;
        $search = $_GET['search'] ?? null;

        $this->data['filter_kelas'] = $id_kelas;
        $this->data['filter_search'] = $search;

        // Get siswa list based on filter
        if ($id_kelas || $search) {
            $this->data['siswa_list'] = $this->model('Siswa_model')->searchSiswaBayar($id_tp_aktif, $id_kelas, $search);
        } else {
            $this->data['siswa_list'] = [];
        }

        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/bayar', $this->data);
        $this->view('templates/footer');
    }

function bayarSiswa($id_siswa)
    {
        $this->data['judul'] = 'Tagihan Siswa';

        $id_tp_aktif = $_SESSION['id_tp_aktif'] ?? 0;

        // Get siswa info
        $siswa = $this->model('Siswa_model')->getSiswaById($id_siswa);
        if (!$siswa) {
            Flasher::setFlash('Siswa tidak ditemukan.', 'danger');
            header('Location: ' . BASEURL . '/admin/bayar');
            exit;
        }

        $this->data['siswa'] = $siswa;

        // Get siswa's kelas
        $keanggotaan = $this->model('Keanggotaan_model')->getKeanggotaanSiswa($id_siswa, $id_tp_aktif);
        $this->data['keanggotaan'] = $keanggotaan;

        // Get all tagihan for this siswa
        $this->data['tagihan_list'] = $this->model('Pembayaran_model')->getAllTagihanSiswaWithStatus($id_siswa, $id_tp_aktif);

        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/bayar_siswa', $this->data);
        $this->view('templates/footer');
    }

function bayarCheckout($id_siswa)
    {
        $this->data['judul'] = 'Checkout Pembayaran';

        $id_tp_aktif = $_SESSION['id_tp_aktif'] ?? 0;

        // Get siswa info
        $siswa = $this->model('Siswa_model')->getSiswaById($id_siswa);
        if (!$siswa) {
            Flasher::setFlash('Siswa tidak ditemukan.', 'danger');
            header('Location: ' . BASEURL . '/admin/bayar');
            exit;
        }

        $this->data['siswa'] = $siswa;
        $keanggotaan = $this->model('Keanggotaan_model')->getKeanggotaanSiswa($id_siswa, $id_tp_aktif);
        $this->data['keanggotaan'] = $keanggotaan;

        // Get selected tagihan IDs from GET
        $rawIds = $_GET['ids'] ?? [];
        $selectedIds = is_array($rawIds) ? $rawIds : explode(',', $rawIds);
        $selectedIds = array_filter(array_map('intval', $selectedIds));

        if (empty($selectedIds)) {
            Flasher::setFlash('Pilih minimal satu tagihan untuk dibayar.', 'warning');
            header('Location: ' . BASEURL . '/admin/bayarSiswa/' . $id_siswa);
            exit;
        }

        // Get all tagihan and filter selected ones
        $allTagihan = $this->model('Pembayaran_model')->getAllTagihanSiswaWithStatus($id_siswa, $id_tp_aktif);

        $selectedTagihan = [];
        $totalTagihan = 0;
        foreach ($allTagihan as $t) {
            if (in_array($t['id'], $selectedIds) && $t['status'] !== 'lunas') {
                $selectedTagihan[] = $t;
                $totalTagihan += $t['sisa'];
            }
        }

        $this->data['selected_tagihan'] = $selectedTagihan;
        $this->data['total_tagihan'] = $totalTagihan;
        $this->data['selected_ids'] = implode(',', array_column($selectedTagihan, 'id'));

        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/bayar_checkout', $this->data);
        $this->view('templates/footer');
    }

function bayarProses()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/admin/bayar');
            exit;
        }

        $id_tp_aktif = $_SESSION['id_tp_aktif'] ?? 0;
        $id_siswa = (int)($_POST['id_siswa'] ?? 0);
        $selectedIds = isset($_POST['selected_ids']) ? explode(',', $_POST['selected_ids']) : [];
        $selectedIds = array_filter(array_map('intval', $selectedIds));
        $mode = $_POST['mode'] ?? 'full'; // full or cicil
        $metode = $_POST['metode'] ?? 'Tunai';
        $keterangan = trim($_POST['keterangan'] ?? '');
        $userId = $_SESSION['user_id'] ?? null;

        if (!$id_siswa || empty($selectedIds)) {
            Flasher::setFlash('Data tidak lengkap.', 'danger');
            header('Location: ' . BASEURL . '/admin/bayar');
            exit;
        }

        // Validate siswa exists
        $siswa = $this->model('Siswa_model')->getSiswaById($id_siswa);
        if (!$siswa) {
            Flasher::setFlash('Siswa tidak ditemukan.', 'danger');
            header('Location: ' . BASEURL . '/admin/bayar');
            exit;
        }

        // Calculate total sisa for selected tagihan
        $allTagihan = $this->model('Pembayaran_model')->getAllTagihanSiswaWithStatus($id_siswa, $id_tp_aktif);
        $totalSisa = 0;
        $validSelectedIds = [];
        foreach ($allTagihan as $t) {
            if (in_array($t['id'], $selectedIds) && $t['status'] !== 'lunas') {
                $totalSisa += $t['sisa'];
                $validSelectedIds[] = $t['id'];
            }
        }

        if (empty($validSelectedIds)) {
            Flasher::setFlash('Semua tagihan yang dipilih sudah lunas.', 'warning');
            header('Location: ' . BASEURL . '/admin/bayarSiswa/' . $id_siswa);
            exit;
        }

        if ($mode === 'full') {
            $jumlahBayar = $totalSisa;
        } else {
            $jumlahBayar = (int)str_replace(['.', ','], '', $_POST['jumlah_bayar'] ?? '0');
            if ($jumlahBayar <= 0) {
                Flasher::setFlash('Jumlah pembayaran tidak valid.', 'danger');
                header('Location: ' . BASEURL . '/admin/bayarCheckout/' . $id_siswa . '?ids=' . implode(',', $validSelectedIds));
                exit;
            }
            // Cap at total sisa
            if ($jumlahBayar > $totalSisa) {
                $jumlahBayar = $totalSisa;
            }
        }

        // Process bulk payment
        $result = $this->model('Pembayaran_model')->prosesBulkPayment(
            $id_siswa,
            $validSelectedIds,
            $jumlahBayar,
            $metode,
            $keterangan,
            $userId
        );

        if ($result['success']) {
            $totalDibayar = $result['total_dibayar'];
            Flasher::setFlash('Pembayaran berhasil. Total dibayar: Rp ' . number_format($totalDibayar, 0, ',', '.'), 'success');

            // Redirect to bayarSiswa to show updated status
            header('Location: ' . BASEURL . '/admin/bayarSiswa/' . $id_siswa);
        } else {
            Flasher::setFlash('Gagal memproses pembayaran.', 'danger');
            header('Location: ' . BASEURL . '/admin/bayarCheckout/' . $id_siswa . '?ids=' . implode(',', $validSelectedIds));
        }
        exit;
    }

function bayarSiswaThermalData($id_siswa)
    {
        $role = $_SESSION['role'] ?? '';
        if ($role !== 'admin') {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }

        $id_tp_aktif = $_SESSION['id_tp_aktif'] ?? 0;

        $siswa = $this->model('Siswa_model')->getSiswaById($id_siswa);
        if (!$siswa) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Siswa tidak ditemukan']);
            exit;
        }

        $keanggotaan = $this->model('Keanggotaan_model')->getKeanggotaanSiswa($id_siswa, $id_tp_aktif);
        $namaKelas = $keanggotaan['nama_kelas'] ?? '-';

        $tagihanList = $this->model('Pembayaran_model')->getAllTagihanSiswaWithStatus($id_siswa, $id_tp_aktif);

        // Get nama madrasah from pengaturan
        $pengaturan = $this->model('PengaturanRapor_model')->getPengaturanByKelas(
            $keanggotaan['id_kelas'] ?? 0,
            $id_tp_aktif
        );
        $namaMadrasah = $pengaturan['nama_madrasah'] ?? 'MI SABILILLAH';

        // Build tagihan data
        $tagihanData = [];
        $totalNominal = 0;
        $totalDiskon = 0;
        $totalTerbayar = 0;
        $totalSisa = 0;

        foreach ($tagihanList as $t) {
            $nominal = (int)($t['nominal'] ?? 0);
            $diskon = (int)($t['diskon'] ?? 0);
            $terbayar = (int)($t['total_terbayar'] ?? 0);
            $sisa = (int)($t['sisa'] ?? 0);

            $tagihanData[] = [
                'nama' => $t['nama'],
                'nominal' => $nominal,
                'diskon' => $diskon,
                'terbayar' => $terbayar,
                'sisa' => $sisa,
                'status' => $t['status']
            ];

            $totalNominal += $nominal;
            $totalDiskon += $diskon;
            $totalTerbayar += $terbayar;
            $totalSisa += $sisa;
        }

        // Generate QR Code validasi
        require_once APPROOT . '/config/qrcode.php';

        $docId = 'rekap_' . $id_siswa . '_' . $id_tp_aktif . '_' . date('Ymd');
        $tokenData = [
            'doc_type' => 'rekap_tagihan',
            'doc_id' => $docId,
            'timestamp' => time(),
            'expires' => time() + (QR_TOKEN_EXPIRY * 24 * 60 * 60)
        ];

        $token = hash_hmac('sha256', json_encode($tokenData), QR_TOKEN_SALT);

        // Save token to database
        try {
            require_once APPROOT . '/app/models/QRValidation_model.php';
            $qrModel = new QRValidation_model();
            $qrModel->ensureTables();

            $expiryDays = QR_TOKEN_EXPIRY > 0 ? QR_TOKEN_EXPIRY : 0;
            $additionalData = [
                'id_siswa' => $id_siswa,
                'nama_siswa' => $siswa['nama_siswa'],
                'nisn' => $siswa['nisn'],
                'kelas' => $namaKelas,
                'total_tagihan' => $totalNominal,
                'total_terbayar' => $totalTerbayar
            ];

            $qrModel->storeToken('rekap_tagihan', $docId, $docId, $token, $expiryDays, $additionalData);
        } catch (Exception $e) {
            error_log('Failed to save QR token: ' . $e->getMessage());
        }

        $validationUrl = QR_WEBSITE_URL . '/validate?token=' . $token . '&type=rekap_tagihan';
        $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' . urlencode($validationUrl);

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'data' => [
                'sekolah' => $namaMadrasah,
                'judul' => 'REKAP TAGIHAN SISWA',
                'tanggal' => date('d/m/Y H:i'),
                'semester' => $_SESSION['nama_semester_aktif'] ?? '-',
                'siswa' => [
                    'nama' => $siswa['nama_siswa'],
                    'nisn' => $siswa['nisn'],
                    'kelas' => $namaKelas
                ],
                'tagihan_list' => $tagihanData,
                'total' => [
                    'nominal' => $totalNominal,
                    'diskon' => $totalDiskon,
                    'terbayar' => $totalTerbayar,
                    'sisa' => $totalSisa
                ],
                'qr' => [
                    'data' => $validationUrl,
                    'url' => $qrUrl
                ]
            ]
        ]);
        exit;
    }
}
