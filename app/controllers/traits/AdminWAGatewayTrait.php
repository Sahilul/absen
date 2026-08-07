<?php
// File: app/controllers/traits/AdminWAGatewayTrait.php
// v1.25.0 - Hasil pemecahan AdminController.php (domain: WA Gateway & akun WA).
// Perilaku identik dengan method asli; hanya dipindahkan agar mudah dipelihara.

trait AdminWAGatewayTrait
{
function waGateway()
    {
        $this->data['judul'] = 'Pengaturan WA Gateway';

        // Load model
        $waAccountModel = $this->model('WaAccount_model');
        $pengaturanModel = $this->model('PengaturanAplikasi_model');

        // Get data
        $this->data['accounts'] = $waAccountModel->getAll();
        $this->data['stats'] = $waAccountModel->getStats();
        $pengaturan = $pengaturanModel->getPengaturan();
        $this->data['rotation_enabled'] = ($pengaturan['wa_rotation_enabled'] ?? 0) == 1;
        $this->data['rotation_mode'] = $pengaturan['wa_rotation_mode'] ?? 'round_robin';
        $this->data['admin_wa_number'] = $pengaturan['admin_wa_number'] ?? '';

        // Load Fonnte class untuk akses providers list
        require_once APPROOT . '/app/core/Fonnte.php';
        $this->data['providers'] = Fonnte::$providers;

        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/wa_gateway', $this->data);
        $this->view('templates/footer', $this->data);
    }

function processTestWaGateway()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/admin/waGateway');
            exit;
        }

        $target = trim($_POST['target'] ?? '');
        $message = trim($_POST['message'] ?? '');
        $accountId = $_POST['account_id'] ?? 'auto';

        if (empty($target) || empty($message)) {
            Flasher::setFlash('Nomor tujuan dan pesan tidak boleh kosong', 'danger');
            header('Location: ' . BASEURL . '/admin/waGateway');
            exit;
        }

        require_once APPROOT . '/app/core/Fonnte.php';
        $fonnte = new Fonnte();

        // Jika pilih akun spesifik
        if ($accountId !== 'auto') {
            $accountModel = $this->model('WaAccount_model');
            $account = $accountModel->getById($accountId);

            if ($account) {
                // Configure Fonnte to use this specific account
                $fonnte->configureFromAccount($account);
            } else {
                Flasher::setFlash('Akun yang dipilih tidak ditemukan', 'danger');
                header('Location: ' . BASEURL . '/admin/waGateway');
                exit;
            }
        }

        // Kirim pesan
        $result = $fonnte->send($target, $message);

        // Parse result
        // Fonnte helper returns ['status' => bool, 'reason' => string] or raw AP response
        $isSuccess = isset($result['status']) && ($result['status'] === true || $result['status'] === 'true');

        // Determine reason/message
        $reason = 'Unknown response from server';
        if (isset($result['reason'])) {
            $reason = $result['reason'];
        } elseif (isset($result['message'])) {
            $reason = $result['message'];
        } elseif (isset($result['detail'])) {
            $reason = $result['detail'];
        } elseif ($isSuccess) {
            $reason = 'Pesan berhasil dikirim (No detail)';
        }

        // Detailed feedback
        if ($isSuccess) {
            $msg = "<strong>Sukses!</strong> Pesan berhasil dikirim ke {$target}.<br><small>Response: {$reason}</small>";
            Flasher::setFlash($msg, 'success');
        } else {
            $msg = "<strong>Gagal!</strong> Tidak dapat mengirim pesan.<br><small><strong>Reason:</strong> {$reason}</small>";
            // Jika ada debug info tambahan dari result
            if (isset($result['debug'])) {
                $msg .= "<br><small>Debug: " . htmlspecialchars($result['debug']) . "</small>";
            }
            Flasher::setFlash($msg, 'danger');
        }

        header('Location: ' . BASEURL . '/admin/waGateway');
        exit;
    }

function prosesWaGateway()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $pengaturanModel = $this->model('PengaturanAplikasi_model');

            $rotationEnabled = isset($_POST['rotation_enabled']) ? 1 : 0;
            $rotationMode = $_POST['rotation_mode'] ?? 'round_robin';
            $adminWaNumber = $_POST['admin_wa_number'] ?? '';

            $pengaturanModel->updateSetting('wa_rotation_enabled', $rotationEnabled);
            $pengaturanModel->updateSetting('wa_rotation_mode', $rotationMode);
            $pengaturanModel->updateSetting('admin_wa_number', $adminWaNumber);

            Flasher::setFlash('Pengaturan rotasi WA berhasil disimpan.', 'success');
        }

        header('Location: ' . BASEURL . '/admin/waGateway');
        exit;
    }

function tambahWaAkun()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $waAccountModel = $this->model('WaAccount_model');

            $data = [
                'nama' => $_POST['nama'] ?? '',
                'provider' => $_POST['provider'] ?? 'fonnte',
                'api_url' => $_POST['api_url'] ?? '',
                'token' => $_POST['token'] ?? '',
                'username' => $_POST['username'] ?? '',
                'password' => $_POST['password'] ?? '',
                'is_active' => isset($_POST['is_active']) ? 1 : 0,
                'daily_limit' => (int) ($_POST['daily_limit'] ?? 100)
            ];

            if (empty($data['nama'])) {
                Flasher::setFlash('Nama akun wajib diisi.', 'danger');
            } else {
                $waAccountModel->create($data);
                Flasher::setFlash('Akun WA berhasil ditambahkan.', 'success');
            }
        }

        header('Location: ' . BASEURL . '/admin/waGateway');
        exit;
    }

function editWaAkun($id = null)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $id) {
            $waAccountModel = $this->model('WaAccount_model');

            $data = [
                'nama' => $_POST['nama'] ?? '',
                'provider' => $_POST['provider'] ?? 'fonnte',
                'api_url' => $_POST['api_url'] ?? '',
                'token' => $_POST['token'] ?? '',
                'username' => $_POST['username'] ?? '',
                'password' => $_POST['password'] ?? '',
                'is_active' => isset($_POST['is_active']) ? 1 : 0,
                'daily_limit' => (int) ($_POST['daily_limit'] ?? 100)
            ];

            $waAccountModel->update($id, $data);
            Flasher::setFlash('Akun WA berhasil diperbarui.', 'success');
        }

        header('Location: ' . BASEURL . '/admin/waGateway');
        exit;
    }

function hapusWaAkun($id = null)
    {
        if ($id) {
            $waAccountModel = $this->model('WaAccount_model');
            $waAccountModel->delete($id);
            Flasher::setFlash('Akun WA berhasil dihapus.', 'success');
        }

        header('Location: ' . BASEURL . '/admin/waGateway');
        exit;
    }

function toggleWaAkun($id = null)
    {
        if ($id) {
            $waAccountModel = $this->model('WaAccount_model');
            $waAccountModel->toggleActive($id);
            Flasher::setFlash('Status akun WA berhasil diubah.', 'success');
        }

        header('Location: ' . BASEURL . '/admin/waGateway');
        exit;
    }

function resetWaCounter()
    {
        $waAccountModel = $this->model('WaAccount_model');
        $waAccountModel->resetAllCounters();
        Flasher::setFlash('Counter harian semua akun berhasil direset.', 'success');

        header('Location: ' . BASEURL . '/admin/waGateway');
        exit;
    }
}
