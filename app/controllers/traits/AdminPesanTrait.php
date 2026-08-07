<?php
// File: app/controllers/traits/AdminPesanTrait.php
// v1.25.0 - Hasil pemecahan AdminController.php (domain: Pesan & pengiriman pesan).
// Perilaku identik dengan method asli; hanya dipindahkan agar mudah dipelihara.

trait AdminPesanTrait
{
function pesan()
    {
        $this->data['judul'] = 'Kirim Pesan WhatsApp';

        $id_tp = $_SESSION['id_tp_aktif'] ?? 0;

        // Stats
        $db = new Database();

        // Total guru dengan no_wa
        $db->query("SELECT COUNT(*) as total FROM guru WHERE no_wa IS NOT NULL AND no_wa != ''");
        $this->data['total_guru'] = $db->single()['total'] ?? 0;

        // Total siswa dengan no_wa
        $db->query("SELECT COUNT(*) as total FROM siswa WHERE no_wa IS NOT NULL AND no_wa != ''");
        $this->data['total_siswa'] = $db->single()['total'] ?? 0;
        $this->data['total_siswa_wa'] = $this->data['total_siswa'];

        // Total orang tua (ayah + ibu dengan nomor)
        $db->query("SELECT 
            COUNT(DISTINCT CASE WHEN ayah_no_hp IS NOT NULL AND ayah_no_hp != '' THEN id_siswa END) +
            COUNT(DISTINCT CASE WHEN ibu_no_hp IS NOT NULL AND ibu_no_hp != '' THEN id_siswa END) as total 
            FROM siswa");
        $this->data['total_ortu'] = $db->single()['total'] ?? 0;

        // Queue pending
        require_once APPROOT . '/app/models/WaQueue_model.php';
        $queueModel = new WaQueue_model();
        $stats = $queueModel->getQueueStats();
        $this->data['queue_pending'] = $stats['pending'] ?? 0;

        // Recent queue
        $this->data['recent_queue'] = $queueModel->getRecentMessages(10);

        // Kelas list
        $this->data['kelas'] = $this->model('Kelas_model')->getKelasByTP($id_tp);

        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/pesan', $this->data);
        $this->view('templates/footer', $this->data);
    }

function kirimPesan()
    {
        $this->data['judul'] = 'Tulis Pesan Baru';
        $id_tp = $_SESSION['id_tp_aktif'] ?? 0;

        // Data untuk dropdown
        $this->data['kelas'] = $this->model('Kelas_model')->getKelasByTP($id_tp);
        $this->data['guru'] = $this->model('Guru_model')->getAllGuru();
        $this->data['siswa'] = $this->model('Siswa_model')->getAllSiswaWithKelas($id_tp);

        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/kirim_pesan', $this->data);
        $this->view('templates/footer', $this->data);
    }

function prosesKirimPesanWa()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/admin/pesan');
            exit;
        }

        $target_type = $_POST['target_type'] ?? '';
        $id_kelas = $_POST['id_kelas'] ?? null;
        $pesan = trim($_POST['pesan'] ?? '');
        $id_tp = $_SESSION['id_tp_aktif'] ?? 0;

        // Validasi
        if (empty($target_type) || empty($pesan)) {
            Flasher::setFlash('Target dan pesan wajib diisi!', 'error');
            header('Location: ' . BASEURL . '/admin/pesan');
            exit;
        }

        // Get nama sekolah
        $pengaturanModel = $this->model('PengaturanAplikasi_model');
        $pengaturan = $pengaturanModel->getPengaturan();
        $namaSekolah = $pengaturan['nama_aplikasi'] ?? $pengaturan['nama_sekolah'] ?? 'Sekolah';

        // Format pesan dengan header
        $formattedPesan = "📩 *PESAN DARI {$namaSekolah}*\n\n";
        $formattedPesan .= $pesan;
        $formattedPesan .= "\n\n━━━━━━━━━━━━━━━━━━━━━\n";
        $formattedPesan .= "_Pesan ini dikirim otomatis._";

        // Collect phone numbers based on target
        $db = new Database();
        $numbers = [];

        switch ($target_type) {
            case 'semua_guru':
                $db->query("SELECT no_wa, nama_guru AS nama FROM guru WHERE no_wa IS NOT NULL AND no_wa != ''");
                $results = $db->resultSet();
                foreach ($results as $r) {
                    $numbers[] = ['no_wa' => $r['no_wa'], 'nama' => $r['nama']];
                }
                break;

            case 'semua_siswa':
                $db->query("SELECT no_wa, nama_siswa AS nama FROM siswa WHERE no_wa IS NOT NULL AND no_wa != ''");
                $results = $db->resultSet();
                foreach ($results as $r) {
                    $numbers[] = ['no_wa' => $r['no_wa'], 'nama' => $r['nama']];
                }
                break;

            case 'semua_ortu':
                $db->query("SELECT id_siswa, nama_siswa AS nama, ayah_no_hp, ibu_no_hp FROM siswa WHERE (ayah_no_hp IS NOT NULL AND ayah_no_hp != '') OR (ibu_no_hp IS NOT NULL AND ibu_no_hp != '')");
                $results = $db->resultSet();
                foreach ($results as $r) {
                    if (!empty($r['ayah_no_hp'])) {
                        $numbers[] = ['no_wa' => $r['ayah_no_hp'], 'nama' => 'Ortu ' . $r['nama']];
                    }
                    if (!empty($r['ibu_no_hp'])) {
                        $numbers[] = ['no_wa' => $r['ibu_no_hp'], 'nama' => 'Ortu ' . $r['nama']];
                    }
                }
                break;

            case 'kelas_siswa':
                if (!empty($id_kelas)) {
                    $db->query("SELECT s.no_wa, s.nama_siswa AS nama FROM siswa s
                        INNER JOIN anggota_kelas ak ON s.id_siswa = ak.id_siswa
                        WHERE ak.id_kelas = :id_kelas AND ak.id_tp = :id_tp
                        AND s.no_wa IS NOT NULL AND s.no_wa != ''");
                    $db->bind(':id_kelas', $id_kelas);
                    $db->bind(':id_tp', $id_tp);
                    $results = $db->resultSet();
                    foreach ($results as $r) {
                        $numbers[] = ['no_wa' => $r['no_wa'], 'nama' => $r['nama']];
                    }
                }
                break;

            case 'kelas_ortu':
                if (!empty($id_kelas)) {
                    $db->query("SELECT s.id_siswa, s.nama_siswa AS nama, s.ayah_no_hp, s.ibu_no_hp FROM siswa s
                        INNER JOIN anggota_kelas ak ON s.id_siswa = ak.id_siswa
                        WHERE ak.id_kelas = :id_kelas AND ak.id_tp = :id_tp
                        AND ((s.ayah_no_hp IS NOT NULL AND s.ayah_no_hp != '') OR (s.ibu_no_hp IS NOT NULL AND s.ibu_no_hp != ''))");
                    $db->bind(':id_kelas', $id_kelas);
                    $db->bind(':id_tp', $id_tp);
                    $results = $db->resultSet();
                    foreach ($results as $r) {
                        if (!empty($r['ayah_no_hp'])) {
                            $numbers[] = ['no_wa' => $r['ayah_no_hp'], 'nama' => 'Ortu ' . $r['nama']];
                        }
                        if (!empty($r['ibu_no_hp'])) {
                            $numbers[] = ['no_wa' => $r['ibu_no_hp'], 'nama' => 'Ortu ' . $r['nama']];
                        }
                    }
                }
                break;
        }

        // Add to queue
        $queued = 0;
        if (!empty($numbers)) {
            require_once APPROOT . '/app/models/WaQueue_model.php';
            require_once APPROOT . '/app/core/Fonnte.php';
            $queueModel = new WaQueue_model();
            $fonnte = new Fonnte();

            foreach ($numbers as $n) {
                $queueModel->addToQueue(
                    $fonnte->formatNumber($n['no_wa']),
                    $formattedPesan,
                    'pesan_bulk',
                    ['target' => $target_type, 'penerima' => $n['nama']]
                );
                $queued++;
            }
        }

        if ($queued > 0) {
            Flasher::setFlash("📤 {$queued} pesan WhatsApp masuk antrian. Monitor di Antrian WA.", 'success');
        } else {
            Flasher::setFlash('Tidak ada nomor WA yang ditemukan untuk target ini.', 'warning');
        }

        header('Location: ' . BASEURL . '/admin/pesan');
        exit;
    }

function prosesKirimPesan()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/admin/pesan');
            exit;
        }

        $target_type = $_POST['target_type'] ?? '';
        $judul = trim($_POST['judul'] ?? '');
        $isi = trim($_POST['isi'] ?? '');

        // Validasi
        if (empty($target_type) || empty($judul) || empty($isi)) {
            Flasher::setFlash('Semua field wajib diisi!', 'error');
            header('Location: ' . BASEURL . '/admin/kirimPesan');
            exit;
        }

        // Tentukan target_id
        $target_id = null;
        if ($target_type === 'kelas') {
            $target_id = $_POST['target_kelas'] ?? null;
        } elseif ($target_type === 'guru_individual') {
            $target_id = $_POST['target_guru'] ?? null;
        } elseif ($target_type === 'siswa_individual') {
            $target_id = $_POST['target_siswa'] ?? null;
        }

        // Upload lampiran
        $lampiran = null;
        if (!empty($_FILES['lampiran']['name'])) {
            $allowed = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png', 'zip'];
            $ext = strtolower(pathinfo($_FILES['lampiran']['name'], PATHINFO_EXTENSION));

            if (!in_array($ext, $allowed)) {
                Flasher::setFlash('Format file tidak diizinkan!', 'error');
                header('Location: ' . BASEURL . '/admin/kirimPesan');
                exit;
            }

            if ($_FILES['lampiran']['size'] > 10 * 1024 * 1024) {
                Flasher::setFlash('Ukuran file maksimal 10MB!', 'error');
                header('Location: ' . BASEURL . '/admin/kirimPesan');
                exit;
            }

            $lampiran = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $_FILES['lampiran']['name']);
            $uploadPath = APPROOT . '/public/uploads/pesan/' . $lampiran;
            move_uploaded_file($_FILES['lampiran']['tmp_name'], $uploadPath);
        }

        $pesanModel = $this->model('Pesan_model');
        $id_tp = $_SESSION['id_tp_aktif'] ?? 0;

        // Insert pesan
        $id_pesan = $pesanModel->kirimPesan([
            'pengirim_type' => 'admin',
            'pengirim_id' => 0,
            'judul' => $judul,
            'isi' => $isi,
            'target_type' => $target_type,
            'target_id' => $target_id,
            'lampiran' => $lampiran
        ]);

        // Tentukan penerima
        $penerima_list = [];
        switch ($target_type) {
            case 'semua_guru':
                $guru = $pesanModel->getAllGuruIds();
                foreach ($guru as $g) {
                    $penerima_list[] = ['type' => 'guru', 'id' => $g['id']];
                }
                break;
            case 'semua_siswa':
                $siswa = $pesanModel->getAllSiswaIds();
                foreach ($siswa as $s) {
                    $penerima_list[] = ['type' => 'siswa', 'id' => $s['id']];
                }
                break;
            case 'kelas':
                $siswa = $pesanModel->getSiswaIdsByKelas($target_id, $id_tp);
                foreach ($siswa as $s) {
                    $penerima_list[] = ['type' => 'siswa', 'id' => $s['id']];
                }
                break;
            case 'guru_individual':
                $penerima_list[] = ['type' => 'guru', 'id' => $target_id];
                break;
            case 'siswa_individual':
                $penerima_list[] = ['type' => 'siswa', 'id' => $target_id];
                break;
        }

        // Insert penerima
        $pesanModel->kirimKePenerima($id_pesan, $penerima_list);

        // ========================================
        // KIRIM VIA WHATSAPP
        // ========================================
        $wa_sent = 0;
        $wa_failed = 0;

        try {
            require_once APPROOT . '/app/core/Fonnte.php';
            $fonnte = new Fonnte();

            // Format pesan WA - ambil nama aplikasi dari database
            $pengaturanModel = $this->model('PengaturanAplikasi_model');
            $pengaturan = $pengaturanModel->getPengaturan();
            $namaApp = $pengaturan['nama_aplikasi'] ?? $pengaturan['nama_sekolah'] ?? 'Sekolah';

            $waMessage = "📩 *PESAN DARI {$namaApp}*\n\n";
            $waMessage .= "*{$judul}*\n\n";
            $waMessage .= $isi;
            if (!empty($lampiran)) {
                $waMessage .= "\n\n📎 _Lampiran tersedia di aplikasi_";
            }
            $waMessage .= "\n\n━━━━━━━━━━━━━━━━━━━━━\n";
            $waMessage .= "_Pesan ini dikirim otomatis dari {$namaApp}_\n\n";
            $waMessage .= "✅ *Apabila sudah menerima pesan ini, mohon balas dengan mengetik:* YA";

            // Queue messages instead of sending directly
            require_once APPROOT . '/app/models/WaQueue_model.php';
            $queueModel = new WaQueue_model();

            foreach ($penerima_list as $penerima) {
                $no_wa = null;
                $nama_penerima = '';

                if ($penerima['type'] === 'guru') {
                    $guru = $pesanModel->getGuruWithNoWa($penerima['id']);
                    $no_wa = $guru['no_wa'] ?? null;
                    $nama_penerima = $guru['nama'] ?? '';
                } elseif ($penerima['type'] === 'siswa') {
                    $siswa = $pesanModel->getSiswaWithNoWa($penerima['id']);
                    $no_wa = $siswa['no_wa'] ?? null;
                    $nama_penerima = $siswa['nama'] ?? '';
                }

                if (!empty($no_wa)) {
                    // Add to queue instead of send
                    $queueModel->addToQueue(
                        $fonnte->formatNumber($no_wa),
                        $waMessage,
                        'pesan_admin',
                        ['judul' => $judul, 'penerima' => $nama_penerima]
                    );
                    $wa_sent++;
                }
            }
        } catch (Exception $e) {
            error_log("WhatsApp queue error: " . $e->getMessage());
        }

        // Flash message dengan info WA queue
        $flashMsg = "Pesan berhasil disimpan untuk " . count($penerima_list) . " penerima.";
        if ($wa_sent > 0) {
            $flashMsg .= " 📤 {$wa_sent} pesan WA masuk antrian.";
        }

        Flasher::setFlash($flashMsg, 'success');
        header('Location: ' . BASEURL . '/admin/pesan');
        exit;
    }

function detailPesan($id = null)
    {
        if (!$id) {
            header('Location: ' . BASEURL . '/admin/pesan');
            exit;
        }

        $pesanModel = $this->model('Pesan_model');
        $this->data['pesan'] = $pesanModel->getPesanById($id);

        if (!$this->data['pesan']) {
            Flasher::setFlash('Pesan tidak ditemukan!', 'error');
            header('Location: ' . BASEURL . '/admin/pesan');
            exit;
        }

        $this->data['penerima'] = $pesanModel->getPenerimaPesan($id);
        $this->data['judul'] = 'Detail Pesan';

        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/detail_pesan', $this->data);
        $this->view('templates/footer', $this->data);
    }

function hapusPesan($id = null)
    {
        if (!$id || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/admin/pesan');
            exit;
        }

        $pesanModel = $this->model('Pesan_model');
        $pesanModel->hapusPesan($id);

        Flasher::setFlash('Pesan berhasil dihapus!', 'success');
        header('Location: ' . BASEURL . '/admin/pesan');
        exit;
    }
}
