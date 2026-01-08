<?php

/**
 * TamuController - Controller publik untuk tamu (tanpa login)
 */
class TamuController extends Controller
{
    /**
     * Form Buku Tamu (publik)
     */
    public function index($token = null)
    {
        if (!$token) {
            $this->showError('Link tidak valid');
            return;
        }

        $linkModel = $this->model('BukuTamuLink_model');
        $link = $linkModel->getByToken($token);

        if (!$link) {
            $this->showError('Link tidak ditemukan');
            return;
        }

        // Check if expired
        if (strtotime($link['expired_at']) < time()) {
            $this->showExpired();
            return;
        }

        // Check if already used
        if ($link['used']) {
            $this->showUsed();
            return;
        }

        // Show form
        $data = [
            'judul' => 'Buku Tamu - ' . $link['nama_lembaga'],
            'link' => $link,
            'token' => $token
        ];

        $this->view('tamu/form', $data);
    }

    /**
     * Proses submit form tamu
     */
    public function submit()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL);
            exit;
        }

        $token = $_POST['token'] ?? '';
        $linkModel = $this->model('BukuTamuLink_model');
        $link = $linkModel->getByToken($token);

        if (!$link || !$linkModel->isValid($token)) {
            $this->showError('Link tidak valid atau sudah kadaluarsa');
            return;
        }

        // Validasi
        $nama_tamu = trim($_POST['nama_tamu'] ?? '');
        $no_hp = trim($_POST['no_hp'] ?? '');
        $keperluan = trim($_POST['keperluan'] ?? '');
        $foto_base64 = $_POST['foto_base64'] ?? '';

        if (empty($nama_tamu) || empty($no_hp) || empty($keperluan)) {
            $this->showError('Nama, No. HP, dan Keperluan wajib diisi');
            return;
        }

        if (empty($foto_base64)) {
            $this->showError('Foto kehadiran wajib diambil');
            return;
        }

        // Upload foto ke Google Drive
        $foto_drive_id = null;
        $foto_url = null;

        try {
            require_once APPROOT . '/app/core/GoogleDrive.php';
            $drive = new GoogleDrive();

            if ($drive->isConnected()) {
                // Decode base64
                $foto_data = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $foto_base64));

                // Save temp file
                $tempFile = APPROOT . '/tmp/buku_tamu_' . time() . '.jpg';
                if (!is_dir(APPROOT . '/tmp')) {
                    mkdir(APPROOT . '/tmp', 0755, true);
                }
                file_put_contents($tempFile, $foto_data);

                // Find or create buku_tamu folder
                $folder = $drive->findOrCreateFolder('buku_tamu', $drive->getFolderId());

                if ($folder) {
                    $fileName = 'tamu_' . date('Ymd_His') . '_' . substr(md5($nama_tamu), 0, 6) . '.jpg';
                    $result = $drive->uploadFile($tempFile, $fileName, $folder['id']);

                    if ($result) {
                        $drive->setPublic($result['id']);
                        $foto_drive_id = $result['id'];
                        $foto_url = $drive->getPublicUrl($result['id']);
                    }
                }

                // Clean temp file
                if (file_exists($tempFile)) {
                    unlink($tempFile);
                }
            }
        } catch (Exception $e) {
            error_log("BukuTamu foto upload error: " . $e->getMessage());
            // Continue without photo if upload fails
        }

        // Save to database
        $bukuTamuModel = $this->model('BukuTamu_model');

        // Parse waktu datang/pulang
        $waktu_datang = !empty($_POST['waktu_datang']) ? date('Y-m-d H:i:s', strtotime($_POST['waktu_datang'])) : date('Y-m-d H:i:s');
        $waktu_pulang = !empty($_POST['waktu_pulang']) ? date('Y-m-d H:i:s', strtotime($_POST['waktu_pulang'])) : null;

        $bukuTamuModel->create([
            'id_link' => $link['id'],
            'id_lembaga' => $link['id_lembaga'],
            'nama_tamu' => $nama_tamu,
            'instansi' => trim($_POST['instansi'] ?? ''),
            'no_hp' => $no_hp,
            'email' => trim($_POST['email'] ?? ''),
            'keperluan' => $keperluan,
            'bertemu_dengan' => trim($_POST['bertemu_dengan'] ?? ''),
            'catatan' => trim($_POST['catatan'] ?? ''),
            'foto_drive_id' => $foto_drive_id,
            'foto_url' => $foto_url,
            'waktu_datang' => $waktu_datang,
            'waktu_pulang' => $waktu_pulang
        ]);

        // Mark link as used
        $linkModel->markUsed($token);

        // Show success page
        $this->view('tamu/success', [
            'judul' => 'Terima Kasih',
            'nama_tamu' => $nama_tamu,
            'lembaga' => $link['nama_lembaga']
        ]);
    }

    /**
     * Show error page
     */
    private function showError($message)
    {
        $this->view('tamu/error', [
            'judul' => 'Error',
            'message' => $message
        ]);
    }

    /**
     * Show expired page
     */
    private function showExpired()
    {
        $this->view('tamu/expired', [
            'judul' => 'Link Kadaluarsa'
        ]);
    }

    /**
     * Show already used page
     */
    private function showUsed()
    {
        $this->view('tamu/expired', [
            'judul' => 'Link Sudah Digunakan',
            'message' => 'Link ini sudah pernah digunakan untuk mengisi buku tamu.'
        ]);
    }
}
