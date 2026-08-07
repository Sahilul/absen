<?php
// File: app/controllers/traits/AdminPengaturanTrait.php
// v1.25.0 - Hasil pemecahan AdminController.php (domain: Pengaturan (QR, menu, Drive, role, profil, RPP, rapor, aplikasi, sistem, notifikasi)).
// Perilaku identik dengan method asli; hanya dipindahkan agar mudah dipelihara.

trait AdminPengaturanTrait
{
function configQR()
    {
        $this->data['judul'] = 'Konfigurasi QR Code';

        // Load config file
        $configFile = __DIR__ . '/../../config/qrcode.php';
        $config = [];

        // Load settings from DB for synchronization
        $pengaturanDb = $this->model('PengaturanAplikasi_model')->getPengaturan();
        $dbUrl = $pengaturanDb['url_web'] ?? '';

        if (file_exists($configFile)) {
            include $configFile;
            $config = [
                'QR_API_PROVIDER' => defined('QR_API_PROVIDER') ? QR_API_PROVIDER : 'qrserver',
                'QR_CUSTOM_URL' => defined('QR_CUSTOM_URL') ? QR_CUSTOM_URL : '',
                // Prioritize DB URL if available, otherwise use config file value
                'QR_WEBSITE_URL' => !empty($dbUrl) ? $dbUrl : (defined('QR_WEBSITE_URL') ? QR_WEBSITE_URL : 'http://localhost/absen'),
                'QR_SIZE' => defined('QR_SIZE') ? QR_SIZE : '200x200',
                'QR_DISPLAY_SIZE' => defined('QR_DISPLAY_SIZE') ? str_replace('px', '', QR_DISPLAY_SIZE) : '60',
                'QR_TOKEN_EXPIRY' => defined('QR_TOKEN_EXPIRY') ? QR_TOKEN_EXPIRY : '365',
                'QR_POSITION' => defined('QR_POSITION') ? QR_POSITION : 'bottom-left',
                'QR_DISPLAY_TEXT' => defined('QR_DISPLAY_TEXT') ? QR_DISPLAY_TEXT : 'Scan untuk validasi',
                'QR_TOKEN_SALT' => defined('QR_TOKEN_SALT') ? QR_TOKEN_SALT : 'rapor_2024_secret_key'
            ];
        }

        $this->data['config'] = $config;

        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/config_qr', $this->data);
        $this->view('templates/footer');
    }

function simpanConfigQR()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/admin/configQR');
            exit;
        }

        $provider = $_POST['qr_provider'] ?? 'qrserver';
        $customUrl = $_POST['qr_custom_url'] ?? '';
        $websiteUrl = $_POST['qr_website_url'] ?? 'http://localhost/absen';
        $size = $_POST['qr_size'] ?? '200x200';
        $displaySize = $_POST['qr_display_size'] ?? '60';
        $tokenExpiry = $_POST['qr_token_expiry'] ?? '365';
        $position = $_POST['qr_position'] ?? 'bottom-left';
        $displayText = $_POST['qr_display_text'] ?? 'Scan untuk validasi';
        $tokenSalt = $_POST['qr_token_salt'] ?? 'rapor_2024_secret_key';

        // Sync Website URL to Database (Pengaturan Aplikasi)
        try {
            $pengaturanModel = $this->model('PengaturanAplikasi_model');
            $currentSettings = $pengaturanModel->getPengaturan();

            // Only update if URL changed
            if (($currentSettings['url_web'] ?? '') !== $websiteUrl) {
                $currentSettings['url_web'] = $websiteUrl;
                // Pastikan key yang dibutuhkan model tersedia
                if (!isset($currentSettings['nama_aplikasi']))
                    $currentSettings['nama_aplikasi'] = 'Smart Absensi';
                // Simpan update
                $pengaturanModel->simpan($currentSettings);
            }
        } catch (Exception $e) {
            // Ignore error sync, focus on saving config file
        }

        // Generate config file content
        $configContent = "<?php\n\n";
        $configContent .= "/**\n * QR Code Configuration\n * Auto-generated on " . date('Y-m-d H:i:s') . "\n */\n\n";
        $configContent .= "define('QR_API_PROVIDER', '{$provider}');\n";
        $configContent .= "define('QR_API_QRSERVER', 'https://api.qrserver.com/v1/create-qr-code/');\n";
        $configContent .= "define('QR_CUSTOM_URL', '{$customUrl}');\n";
        // Logic URL Dinamis
        $configContent .= "// Set default URL\n";
        $configContent .= "if (!defined('QR_WEBSITE_URL')) {\n";
        $configContent .= "    \$qrBaseUrl = '';\n";
        $configContent .= "    \n";
        $configContent .= "    // Coba ambil dari database jika helper tersedia\n";
        $configContent .= "    if (function_exists('getPengaturanAplikasi')) {\n";
        $configContent .= "        \$appSettings = getPengaturanAplikasi();\n";
        $configContent .= "        if (!empty(\$appSettings['url_web'])) {\n";
        $configContent .= "            \$qrBaseUrl = \$appSettings['url_web'];\n";
        $configContent .= "        }\n";
        $configContent .= "    }\n";
        $configContent .= "    \n";
        $configContent .= "    // Pastikan ada trailing slash\n";
        $configContent .= "    define('QR_WEBSITE_URL', rtrim(\$qrBaseUrl, '/') . '/');\n";
        $configContent .= "}\n";
        $configContent .= "define('QR_SIZE', '{$size}');\n";
        $configContent .= "define('QR_DISPLAY_SIZE', '{$displaySize}px');\n";
        $configContent .= "define('QR_TOKEN_EXPIRY', {$tokenExpiry});\n";
        $configContent .= "define('QR_POSITION', '{$position}');\n";
        $configContent .= "define('QR_DISPLAY_TEXT', '" . addslashes($displayText) . "');\n";
        $configContent .= "define('QR_TOKEN_SALT', '" . addslashes($tokenSalt) . "');\n\n";

        // Add helper functions
        $configContent .= "function getQRCodeApiUrl(\$data) {\n";
        $configContent .= "    \$encodedData = urlencode(\$data);\n";
        $configContent .= "    // Parse size dari format \"250x250\" ke integer untuk provider yang membutuhkan\n";
        $configContent .= "    \$sizeInt = (int)explode('x', QR_SIZE)[0];\n";
        $configContent .= "    \n";
        $configContent .= "    switch (QR_API_PROVIDER) {\n";
        $configContent .= "        case 'qrserver':\n";
        $configContent .= "            return QR_API_QRSERVER . '?size=' . QR_SIZE . '&data=' . \$encodedData;\n";
        $configContent .= "        case 'quickchart':\n";
        $configContent .= "            return 'https://quickchart.io/qr?text=' . \$encodedData . '&size=' . \$sizeInt;\n";
        $configContent .= "        case 'goqr':\n";
        $configContent .= "            return 'https://api.qrserver.com/v1/create-qr-code/?size=' . QR_SIZE . '&data=' . \$encodedData;\n";
        $configContent .= "        case 'custom':\n";
        $configContent .= "            return str_replace(['{DATA}', '{SIZE}'], [\$encodedData, QR_SIZE], QR_CUSTOM_URL);\n";
        $configContent .= "        default:\n";
        $configContent .= "            return QR_API_QRSERVER . '?size=' . QR_SIZE . '&data=' . \$encodedData;\n";
        $configContent .= "    }\n";
        $configContent .= "}\n\n";
        $configContent .= "function generateQRToken(\$siswaId, \$jenisRapor, \$nisn) {\n";
        $configContent .= "    \$data = \$siswaId . '|' . \$jenisRapor . '|' . \$nisn . '|' . QR_TOKEN_SALT;\n";
        $configContent .= "    return hash('sha256', \$data);\n";
        $configContent .= "}\n\n";

        // Add generatePDFQRCode function
        $configContent .= "/**\n";
        $configContent .= " * Generate PDF QR Code with validation token\n";
        $configContent .= " * @param string \$docType Document type (rapor, pembayaran, absensi, performa_guru, performa_siswa, etc)\n";
        $configContent .= " * @param mixed \$docId Document identifier\n";
        $configContent .= " * @param array \$additionalData Extra metadata for validation\n";
        $configContent .= " * @return string Base64 QR code image data URL\n";
        $configContent .= " */\n";
        $configContent .= "function generatePDFQRCode(\$docType, \$docId, \$additionalData = []) {\n";
        $configContent .= "    try {\n";
        $configContent .= "        // Create validation token\n";
        $configContent .= "        \$tokenData = [\n";
        $configContent .= "            'doc_type' => \$docType,\n";
        $configContent .= "            'doc_id' => \$docId,\n";
        $configContent .= "            'timestamp' => time(),\n";
        $configContent .= "            'expires' => time() + (QR_TOKEN_EXPIRY * 24 * 60 * 60)\n";
        $configContent .= "        ];\n";
        $configContent .= "        \n";
        $configContent .= "        // Merge additional data\n";
        $configContent .= "        if (!empty(\$additionalData)) {\n";
        $configContent .= "            \$tokenData = array_merge(\$tokenData, \$additionalData);\n";
        $configContent .= "        }\n";
        $configContent .= "        \n";
        $configContent .= "        // Create secure token\n";
        $configContent .= "        \$token = hash_hmac('sha256', json_encode(\$tokenData), QR_TOKEN_SALT);\n";
        $configContent .= "        \n";
        $configContent .= "        // Save token to database for validation\n";
        $configContent .= "        try {\n";
        $configContent .= "            \$APPROOT = realpath(__DIR__ . '/..');\n";
        $configContent .= "            require_once \$APPROOT . '/config/database.php';\n";
        $configContent .= "            require_once \$APPROOT . '/app/core/Database.php';\n";
        $configContent .= "            require_once \$APPROOT . '/app/models/QRValidation_model.php';\n";
        $configContent .= "            \$qrModel = new QRValidation_model();\n";
        $configContent .= "            \$qrModel->ensureTables(); // Create table if not exists\n";
        $configContent .= "            \n";
        $configContent .= "            // Store token with correct parameters\n";
        $configContent .= "            \$expiryDays = QR_TOKEN_EXPIRY > 0 ? (int)QR_TOKEN_EXPIRY : 0;\n";
        $configContent .= "            \$identifier = \$docId; // Use doc ID as identifier\n";
        $configContent .= "            \n";
        $configContent .= "            // Save token using storeToken method\n";
        $configContent .= "            \$qrModel->storeToken(\$docType, \$docId, \$identifier, \$token, \$expiryDays, \$additionalData);\n";
        $configContent .= "        } catch (Exception \$e) {\n";
        $configContent .= "            error_log('Failed to save QR token to database: ' . \$e->getMessage());\n";
        $configContent .= "            // Continue anyway - QR will still be generated\n";
        $configContent .= "        }\n";
        $configContent .= "        \n";
        $configContent .= "        // Create validation URL\n";
        $configContent .= "        \$validationUrl = QR_WEBSITE_URL . '/validate?token=' . \$token . '&type=' . urlencode(\$docType);\n";
        $configContent .= "        \n";
        $configContent .= "        // Get QR code image from API\n";
        $configContent .= "        \$qrApiUrl = getQRCodeApiUrl(\$validationUrl);\n";
        $configContent .= "        \n";
        $configContent .= "        // Fetch QR code image\n";
        $configContent .= "        \$qrImageData = @file_get_contents(\$qrApiUrl);\n";
        $configContent .= "        \n";
        $configContent .= "        if (\$qrImageData === false) {\n";
        $configContent .= "            error_log('Failed to generate QR code from API: ' . \$qrApiUrl);\n";
        $configContent .= "            return '';\n";
        $configContent .= "        }\n";
        $configContent .= "        \n";
        $configContent .= "        // Convert to base64 data URL\n";
        $configContent .= "        \$base64 = base64_encode(\$qrImageData);\n";
        $configContent .= "        return 'data:image/png;base64,' . \$base64;\n";
        $configContent .= "        \n";
        $configContent .= "    } catch (Exception \$e) {\n";
        $configContent .= "        error_log('QR code generation error: ' . \$e->getMessage());\n";
        $configContent .= "        return '';\n";
        $configContent .= "    }\n";
        $configContent .= "}\n\n";

        // Add getQRCodeHTML function
        $configContent .= "function getQRCodeHTML(\$qrCodeDataUrl) {\n";
        $configContent .= "    \$position = QR_POSITION;\n";
        $configContent .= "    \$displaySize = QR_DISPLAY_SIZE;\n";
        $configContent .= "    \$displayText = QR_DISPLAY_TEXT;\n";
        $configContent .= "    \n";
        $configContent .= "    // Position styles - menggunakan absolute agar hanya di halaman terakhir\n";
        $configContent .= "    \$positionStyles = [\n";
        $configContent .= "        'bottom-right' => 'bottom: 5mm; right: 5mm;',\n";
        $configContent .= "        'bottom-left' => 'bottom: 5mm; left: 5mm;',\n";
        $configContent .= "        'top-right' => 'top: 5mm; right: 5mm;',\n";
        $configContent .= "        'top-left' => 'top: 5mm; left: 5mm;',\n";
        $configContent .= "    ];\n";
        $configContent .= "    \n";
        $configContent .= "    \$style = \$positionStyles[\$position] ?? \$positionStyles['bottom-right'];\n";
        $configContent .= "    \n";
        $configContent .= "    // Gunakan position absolute dan taruh di akhir document\n";
        $configContent .= "    \$html = '<div style=\"position: absolute; ' . \$style . ' text-align: center; background: white; padding: 5px; border: 1px solid #ddd; border-radius: 3px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);\">';\n";
        $configContent .= "    \$html .= '<img src=\"' . htmlspecialchars(\$qrCodeDataUrl) . '\" style=\"width: ' . \$displaySize . '; height: ' . \$displaySize . '; display: block;\" alt=\"QR Code\">';\n";
        $configContent .= "    if (!empty(\$displayText)) {\n";
        $configContent .= "        \$html .= '<div style=\"font-size: 7px; color: #666; margin-top: 2px;\">' . htmlspecialchars(\$displayText) . '</div>';\n";
        $configContent .= "    }\n";
        $configContent .= "    \$html .= '</div>';\n";
        $configContent .= "    \n";
        $configContent .= "    return \$html;\n";
        $configContent .= "}\n";

        // Save to file
        $configFile = __DIR__ . '/../../config/qrcode.php';
        $result = file_put_contents($configFile, $configContent);

        if ($result !== false) {
            Flasher::setFlash('Konfigurasi QR Code berhasil disimpan!', 'success');
        } else {
            Flasher::setFlash('Gagal menyimpan konfigurasi QR Code!', 'danger');
        }

        header('Location: ' . BASEURL . '/admin/configQR');
        exit;
    }

function clearDashboardCache()
    {
        // Clear semua cache dashboard
        unset($_SESSION['admin_dashboard_stats']);
        unset($_SESSION['admin_dashboard_stats_time']);
        unset($_SESSION['admin_daftar_semester']);
        unset($_SESSION['admin_daftar_semester_time']);
    }

function clearCache()
    {
        $this->clearDashboardCache();
        Flasher::setFlash('Cache berhasil dibersihkan!', 'success');
        header('Location: ' . BASEURL . '/admin/dashboard');
        exit;
    }

function testQRCode()
    {
        header('Content-Type: application/json');

        // Load config
        require_once __DIR__ . '/../../config/qrcode.php';

        try {
            $testUrl = BASEURL . '/test-validation';
            $qrApiUrl = getQRCodeApiUrl($testUrl);

            // Download QR Code
            $imageData = @file_get_contents($qrApiUrl);

            if ($imageData !== false) {
                $base64 = 'data:image/png;base64,' . base64_encode($imageData);
                echo json_encode([
                    'success' => true,
                    'qr_code' => $base64,
                    'api_url' => $qrApiUrl
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Tidak dapat mengakses API QR Code'
                ]);
            }
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

function pengaturanMenu()
    {
        $this->data['judul'] = 'Pengaturan Menu';

        // Baca dari database via PengaturanSistem_model
        $pengaturanModel = $this->model('PengaturanSistem_model');
        $settings = $pengaturanModel->getAll();

        $this->data['menu_input_nilai_enabled'] = ($settings['menu_input_nilai_enabled'] ?? '1') == '1';
        $this->data['menu_pembayaran_enabled'] = ($settings['menu_pembayaran_enabled'] ?? '1') == '1';

        // Notifikasi WA Settings
        $this->data['wa_notif_absensi_enabled'] = ($settings['wa_notif_absensi_enabled'] ?? '1') == '1';
        $this->data['wa_notif_pembayaran_enabled'] = ($settings['wa_notif_pembayaran_enabled'] ?? '1') == '1';

        $this->data['google_oauth_enabled'] = ($settings['google_oauth_enabled'] ?? '0') == '1';
        $this->data['google_client_id'] = $settings['google_client_id'] ?? '';
        $this->data['google_client_secret'] = $settings['google_client_secret'] ?? '';
        $this->data['google_allowed_domain'] = $settings['google_allowed_domain'] ?? '';

        // Google Drive connection status
        $googleDriveConnected = false;
        $googleDriveFolderId = '';
        $googleDriveEmail = '';
        try {
            $db = new Database();
            $db->query("SELECT google_refresh_token, google_drive_folder_id, google_drive_email FROM pengaturan_aplikasi LIMIT 1");
            $driveSettings = $db->single();
            if ($driveSettings) {
                $googleDriveConnected = !empty($driveSettings['google_refresh_token']);
                $googleDriveFolderId = $driveSettings['google_drive_folder_id'] ?? '';
                $googleDriveEmail = $driveSettings['google_drive_email'] ?? '';
            }
        } catch (Exception $e) {
            // Kolom belum ada, abaikan
        }
        $this->data['google_drive_connected'] = $googleDriveConnected;
        $this->data['google_drive_folder_id'] = $googleDriveFolderId;
        $this->data['google_drive_email'] = $googleDriveEmail;

        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/pengaturan_menu', $this->data);
        $this->view('templates/footer', $this->data);
    }

function simpanPengaturanMenu()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/admin/pengaturanMenu');
            exit;
        }

        try {
            $pengaturanModel = $this->model('PengaturanSistem_model');

            $settings = [
                'menu_input_nilai_enabled' => isset($_POST['menu_input_nilai']) ? '1' : '0',
                'menu_pembayaran_enabled' => isset($_POST['menu_pembayaran']) ? '1' : '0',
                'menu_rapor_enabled' => isset($_POST['menu_input_nilai']) ? '1' : '0',

                // WA Notification Settings
                'wa_notif_absensi_enabled' => isset($_POST['wa_notif_absensi_enabled']) ? '1' : '0',
                'wa_notif_pembayaran_enabled' => isset($_POST['wa_notif_pembayaran_enabled']) ? '1' : '0',

                'google_oauth_enabled' => isset($_POST['google_oauth_enabled']) ? '1' : '0',
                'google_client_id' => trim($_POST['google_client_id'] ?? ''),
                'google_client_secret' => trim($_POST['google_client_secret'] ?? ''),
                'google_allowed_domain' => trim($_POST['google_allowed_domain'] ?? ''),
            ];

            $pengaturanModel->updateMultiple($settings);

            // Simpan Google Drive Folder ID ke tabel pengaturan_aplikasi jika ada
            $googleDriveFolderId = trim($_POST['google_drive_folder_id'] ?? '');
            if (!empty($googleDriveFolderId)) {
                try {
                    $db = new Database();
                    $db->query("UPDATE pengaturan_aplikasi SET google_drive_folder_id = :folder_id");
                    $db->bind(':folder_id', $googleDriveFolderId);
                    $db->execute();
                } catch (Exception $dbError) {
                    error_log('GoogleDrive folder save: ' . $dbError->getMessage());
                }
            }

            Flasher::setFlash('Pengaturan berhasil disimpan.', 'success');
        } catch (Exception $e) {
            error_log('Error simpanPengaturanMenu: ' . $e->getMessage());
            Flasher::setFlash('Gagal menyimpan pengaturan: ' . $e->getMessage(), 'danger');
        }

        header('Location: ' . BASEURL . '/admin/pengaturanMenu');
        exit;
    }

function connectGoogleDrive()
    {
        require_once APPROOT . '/app/core/GoogleDrive.php';
        $drive = new GoogleDrive();

        $redirectUri = BASEURL . '/admin/googleDriveCallback';
        $authUrl = $drive->getAuthUrl($redirectUri);

        header('Location: ' . $authUrl);
        exit;
    }

function googleDriveCallback()
    {
        $code = $_GET['code'] ?? null;

        if (!$code) {
            Flasher::setFlash('Gagal menghubungkan Google Drive: kode tidak ditemukan.', 'danger');
            header('Location: ' . BASEURL . '/admin/pengaturanMenu');
            exit;
        }

        try {
            require_once APPROOT . '/app/core/GoogleDrive.php';
            $drive = new GoogleDrive();

            $redirectUri = BASEURL . '/admin/googleDriveCallback';
            $tokens = $drive->exchangeCodeForTokens($code, $redirectUri);

            if (isset($tokens['refresh_token'])) {
                // Get user info (email) dan simpan ke database
                $userInfo = $drive->getUserInfo();
                if ($userInfo && isset($userInfo['email'])) {
                    $drive->saveEmail($userInfo['email']);
                }

                // Otomatis buat folder root untuk aplikasi
                $pengaturanModel = $this->model('PengaturanAplikasi_model');
                $pengaturan = $pengaturanModel->getPengaturan();
                $namaAplikasi = $pengaturan['nama_aplikasi'] ?? 'Absensi Sekolah';
                $folderName = $namaAplikasi . ' - Dokumen Siswa';

                // Buat atau cari folder dengan nama tersebut
                $folder = $drive->findOrCreateFolder($folderName);

                if ($folder && isset($folder['id'])) {
                    $drive->saveFolderId($folder['id']);
                    // Set folder menjadi public agar subfolder juga bisa diakses
                    $drive->setPublic($folder['id']);
                    $emailMsg = $userInfo['email'] ?? '';
                    Flasher::setFlash('Google Drive berhasil terhubung dengan ' . $emailMsg . '! Folder "' . $folderName . '" sudah dibuat.', 'success');
                } else {
                    Flasher::setFlash('Google Drive terhubung, tapi gagal membuat folder otomatis. Silakan buat folder manual.', 'warning');
                }
            } else {
                Flasher::setFlash('Terhubung tapi tidak mendapat refresh token. Coba putuskan dulu lalu hubungkan kembali.', 'warning');
            }

        } catch (Exception $e) {
            error_log('GoogleDrive callback error: ' . $e->getMessage());
            Flasher::setFlash('Gagal menghubungkan Google Drive: ' . $e->getMessage(), 'danger');
        }

        header('Location: ' . BASEURL . '/admin/pengaturanMenu');
        exit;
    }

function disconnectGoogleDrive()
    {
        try {
            require_once APPROOT . '/app/core/GoogleDrive.php';
            $drive = new GoogleDrive();
            $drive->disconnect();
            Flasher::setFlash('Koneksi Google Drive berhasil diputus.', 'success');
        } catch (Exception $e) {
            error_log('GoogleDrive disconnect error: ' . $e->getMessage());
            Flasher::setFlash('Gagal memutus koneksi: ' . $e->getMessage(), 'danger');
        }

        header('Location: ' . BASEURL . '/admin/pengaturanMenu');
        exit;
    }

function pengaturanRole()
    {
        $this->data['judul'] = 'Pengaturan Fungsi Guru';
        $id_tp_aktif = $_SESSION['id_tp_aktif'] ?? 0;

        $guruFungsiModel = $this->model('GuruFungsi_model');

        $this->data['guru_list'] = $guruFungsiModel->getGuruWithFungsi($id_tp_aktif);
        $this->data['fungsi_tersedia'] = GuruFungsi_model::getAvailableFungsi();
        $this->data['id_tp_aktif'] = $id_tp_aktif;
        $this->data['nama_tp'] = $_SESSION['nama_tp_aktif'] ?? '';

        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/pengaturan_role', $this->data);
        $this->view('templates/footer');
    }

function savePengaturanRole()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/admin/pengaturanRole');
            exit;
        }

        $id_tp_aktif = $_SESSION['id_tp_aktif'] ?? 0;
        $guruFungsiModel = $this->model('GuruFungsi_model');
        $fungsiTersedia = array_keys(GuruFungsi_model::getAvailableFungsi());

        // Process each guru
        $guruList = $_POST['guru'] ?? [];

        foreach ($guruList as $id_guru => $fungsiSelected) {
            // Validate fungsi
            $validFungsi = array_intersect($fungsiSelected, $fungsiTersedia);
            $guruFungsiModel->updateFungsiGuru($id_guru, $validFungsi, $id_tp_aktif, $_SESSION['user_id'] ?? null);
        }

        // Also process guru with no functions selected (remove all)
        $allGuruIds = array_column($this->model('GuruFungsi_model')->getAllGuru(), 'id_guru');
        $submittedGuruIds = array_keys($guruList);
        $unselectedGuruIds = array_diff($allGuruIds, $submittedGuruIds);

        foreach ($unselectedGuruIds as $id_guru) {
            $guruFungsiModel->updateFungsiGuru($id_guru, [], $id_tp_aktif, $_SESSION['user_id'] ?? null);
        }

        Flasher::setFlash('Pengaturan fungsi guru berhasil disimpan.', 'success');
        header('Location: ' . BASEURL . '/admin/pengaturanRole');
        exit;
    }

function profil()
    {
        $this->data['judul'] = 'Profil Admin';
        $id_user = $_SESSION['user_id'] ?? 0;

        if (!$id_user) {
            header('Location: ' . BASEURL . '/admin/dashboard');
            exit;
        }

        try {
            $db = new Database();
            $db->query("SELECT * FROM users WHERE id_user = :id_user AND role = 'admin' LIMIT 1");
            $db->bind('id_user', $id_user);
            $admin = $db->single();

            if (!$admin) {
                Flasher::setFlash('Data admin tidak ditemukan.', 'danger');
                header('Location: ' . BASEURL . '/admin/dashboard');
                exit;
            }

            $this->data['admin'] = $admin;
        } catch (Exception $e) {
            error_log('Error profil admin: ' . $e->getMessage());
            Flasher::setFlash('Terjadi kesalahan saat memuat profil.', 'danger');
            header('Location: ' . BASEURL . '/admin/dashboard');
            exit;
        }

        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/profil', $this->data);
        $this->view('templates/footer', $this->data);
    }

function simpanProfil()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/admin/profil');
            exit;
        }

        $id_user = $_SESSION['user_id'] ?? 0;
        if (!$id_user) {
            header('Location: ' . BASEURL . '/admin/profil');
            exit;
        }

        $username = trim($_POST['username'] ?? '');
        $nama_lengkap = trim($_POST['nama_lengkap'] ?? '');
        $email = trim($_POST['email'] ?? '');

        if (empty($username)) {
            Flasher::setFlash('Username tidak boleh kosong.', 'danger');
            header('Location: ' . BASEURL . '/admin/profil');
            exit;
        }

        // v1.23.0 - Validasi format email (boleh kosong, tapi jika diisi harus valid)
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Flasher::setFlash('Format email tidak valid.', 'danger');
            header('Location: ' . BASEURL . '/admin/profil');
            exit;
        }

        try {
            $db = new Database();

            // Cek apakah username sudah digunakan oleh user lain
            $db->query("SELECT id_user FROM users WHERE username = :username AND id_user != :id_user LIMIT 1");
            $db->bind('username', $username);
            $db->bind('id_user', $id_user);
            $exists = $db->single();

            if ($exists) {
                Flasher::setFlash('Username sudah digunakan oleh user lain.', 'danger');
                header('Location: ' . BASEURL . '/admin/profil');
                exit;
            }

            // v1.23.0 - Cek apakah email sudah digunakan oleh user lain
            if ($email !== '') {
                $db->query("SELECT id_user FROM users WHERE email = :email AND id_user != :id_user LIMIT 1");
                $db->bind('email', $email);
                $db->bind('id_user', $id_user);
                $emailExists = $db->single();

                if ($emailExists) {
                    Flasher::setFlash('Email sudah digunakan oleh user lain.', 'danger');
                    header('Location: ' . BASEURL . '/admin/profil');
                    exit;
                }
            }

            // Update profil
            $db->query("UPDATE users SET username = :username, nama_lengkap = :nama_lengkap, email = :email WHERE id_user = :id_user");
            $db->bind('username', $username);
            $db->bind('nama_lengkap', $nama_lengkap);
            $db->bind('email', $email !== '' ? $email : null);
            $db->bind('id_user', $id_user);
            $db->execute();

            // Update session
            $_SESSION['username'] = $username;
            $_SESSION['nama_lengkap'] = $nama_lengkap;

            Flasher::setFlash('Profil berhasil diperbarui.', 'success');
        } catch (Exception $e) {
            error_log('Error simpanProfil admin: ' . $e->getMessage());
            Flasher::setFlash('Terjadi kesalahan saat menyimpan profil.', 'danger');
        }

        header('Location: ' . BASEURL . '/admin/profil');
        exit;
    }

function gantiSandi()
    {
        $this->data['judul'] = 'Ganti Sandi';

        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/ganti_sandi', $this->data);
        $this->view('templates/footer', $this->data);
    }

function simpanSandi()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/admin/gantiSandi');
            exit;
        }

        $id_user = $_SESSION['user_id'] ?? 0;
        if (!$id_user) {
            header('Location: ' . BASEURL . '/admin/gantiSandi');
            exit;
        }

        $password = trim($_POST['password'] ?? '');
        $password2 = trim($_POST['password2'] ?? '');

        if (empty($password) || empty($password2)) {
            Flasher::setFlash('Password dan konfirmasi wajib diisi.', 'danger');
            header('Location: ' . BASEURL . '/admin/gantiSandi');
            exit;
        }

        if ($password !== $password2) {
            Flasher::setFlash('Konfirmasi password tidak cocok.', 'danger');
            header('Location: ' . BASEURL . '/admin/gantiSandi');
            exit;
        }

        if (strlen($password) < 6) {
            Flasher::setFlash('Password minimal 6 karakter.', 'danger');
            header('Location: ' . BASEURL . '/admin/gantiSandi');
            exit;
        }

        try {
            $db = new Database();
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $db->query("UPDATE users SET password = :password WHERE id_user = :id_user");
            $db->bind('password', $hashedPassword);
            $db->bind('id_user', $id_user);
            $db->execute();

            Flasher::setFlash('Password berhasil diperbarui.', 'success');
        } catch (Exception $e) {
            error_log('Error simpanSandi admin: ' . $e->getMessage());
            Flasher::setFlash('Terjadi kesalahan saat menyimpan password.', 'danger');
        }

        header('Location: ' . BASEURL . '/admin/gantiSandi');
        exit;
    }

function monitoringNilai()
    {
        $this->data['judul'] = 'Monitoring Nilai';

        // Ambil semua kelas (atau filter by TP aktif jika diperlukan)
        $kelasModel = $this->model('Kelas_model');
        $waliKelasModel = $this->model('WaliKelas_model');
        $id_tp_aktif = $_SESSION['id_tp_aktif'] ?? 0;

        $kelasList = $kelasModel->getAllKelas();
        foreach ($kelasList as &$k) {
            $k['wali_kelas'] = $waliKelasModel->getWaliKelasByKelas($k['id_kelas'], $id_tp_aktif);
        }
        $this->data['kelas_list'] = $kelasList;

        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/monitoring_nilai', $this->data);
        $this->view('templates/footer');
    }

function monitoringNilaiKelas($id_kelas)
    {
        $this->data['judul'] = 'Monitoring Nilai';

        $kelas = $this->model('Kelas_model')->getKelasById($id_kelas);
        if (!$kelas) {
            header('Location: ' . BASEURL . '/admin/monitoringNilai');
            exit;
        }

        $this->data['id_kelas'] = (int) $id_kelas;
        $this->data['nama_kelas'] = $kelas['nama_kelas'] ?? '';
        $this->data['session_info'] = [
            'nama_semester' => $_SESSION['nama_semester_aktif'] ?? 'Semester Tidak Diketahui'
        ];

        // View khusus admin; menggunakan endpoint admin untuk detail nilai
        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/monitoring_nilai_kelas', $this->data);
        $this->view('templates/footer');
    }

function getNilaiSiswa()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $idSiswa = $input['id_siswa'] ?? 0;
        $jenisNilai = $input['jenis_nilai'] ?? 'harian';
        $id_kelas = $input['id_kelas'] ?? 0;

        if (!$idSiswa || !$id_kelas) {
            echo json_encode(['status' => 'error', 'message' => 'Parameter tidak lengkap']);
            return;
        }

        try {
            $id_semester_aktif = $_SESSION['id_semester_aktif'] ?? 0;

            $nilaiModel = $this->model('Nilai_model');
            $penugasanModel = $this->model('Penugasan_model');

            // Ambil daftar mapel untuk kelas ini
            $mapelList = $penugasanModel->getMapelByKelas($id_kelas, $id_semester_aktif);

            $result = [
                'mapel' => [],
                'rata_rata' => null
            ];
            $totalNilai = 0;
            $jumlahMapel = 0;

            foreach ($mapelList as $mapel) {
                $nilai = null;
                if ($jenisNilai === 'harian') {
                    $nilaiHarian = $nilaiModel->getNilaiHarianByMapelSiswa($mapel['id_penugasan'], $idSiswa);
                    if (!empty($nilaiHarian)) {
                        $nilai = array_sum(array_column($nilaiHarian, 'nilai')) / count($nilaiHarian);
                    }
                } elseif ($jenisNilai === 'sts') {
                    $nilaiSTS = $nilaiModel->getNilaiByJenis($idSiswa, $mapel['id_guru'], $mapel['id_mapel'], $id_semester_aktif, 'sts');
                    $nilai = $nilaiSTS['nilai'] ?? null;
                } elseif ($jenisNilai === 'sas') {
                    $nilaiSAS = $nilaiModel->getNilaiByJenis($idSiswa, $mapel['id_guru'], $mapel['id_mapel'], $id_semester_aktif, 'sas');
                    $nilai = $nilaiSAS['nilai'] ?? null;
                }

                $result['mapel'][] = [
                    'nama_mapel' => $mapel['nama_mapel'],
                    'nilai' => $nilai !== null ? round($nilai, 2) : null
                ];

                if ($nilai !== null) {
                    $totalNilai += $nilai;
                    $jumlahMapel++;
                }
            }

            $result['rata_rata'] = $jumlahMapel > 0 ? round($totalNilai / $jumlahMapel, 2) : null;

            echo json_encode(['status' => 'success', 'data' => $result]);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

function pengaturanRPP()
    {
        $templateModel = $this->model('RPPTemplate_model');
        $sections = $templateModel->getAllSections(false); // include inactive

        // Get fields grouped by section
        $fieldsBySection = [];
        foreach ($sections as $section) {
            $fieldsBySection[$section['id_section']] = $templateModel->getFieldsBySection($section['id_section'], false);
        }

        // Get pengaturan wajib RPP
        $pengaturanWajibRPP = $this->model('PengaturanRPP_model')->getPengaturan();

        $this->data['judul'] = 'Pengaturan Template RPP';
        $this->data['sections'] = $sections;
        $this->data['fields_by_section'] = $fieldsBySection;
        $this->data['pengaturan_wajib_rpp'] = $pengaturanWajibRPP;

        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/pengaturan_rpp', $this->data);
        $this->view('templates/footer', $this->data);
    }

function simpanSection()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/admin/pengaturanRPP');
            exit;
        }

        $templateModel = $this->model('RPPTemplate_model');
        $id_section = $_POST['id_section'] ?? null;

        $data = [
            'kode_section' => $_POST['kode_section'],
            'nama_section' => $_POST['nama_section'],
            'urutan' => $_POST['urutan'] ?? 0,
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];

        if ($id_section) {
            $templateModel->updateSection($id_section, $data);
            Flasher::setFlash('Section berhasil diupdate!', 'success');
        } else {
            $templateModel->tambahSection($data);
            Flasher::setFlash('Section berhasil ditambahkan!', 'success');
        }

        header('Location: ' . BASEURL . '/admin/pengaturanRPP');
        exit;
    }

function hapusSection($id_section = null)
    {
        if (!$id_section) {
            Flasher::setFlash('ID Section tidak valid!', 'danger');
            header('Location: ' . BASEURL . '/admin/pengaturanRPP');
            exit;
        }

        $templateModel = $this->model('RPPTemplate_model');
        $templateModel->hapusSection($id_section);
        Flasher::setFlash('Section berhasil dihapus!', 'success');
        header('Location: ' . BASEURL . '/admin/pengaturanRPP');
        exit;
    }

function toggleSection($id_section = null)
    {
        if (!$id_section) {
            Flasher::setFlash('ID Section tidak valid!', 'danger');
            header('Location: ' . BASEURL . '/admin/pengaturanRPP');
            exit;
        }

        $templateModel = $this->model('RPPTemplate_model');
        $templateModel->toggleSection($id_section);
        Flasher::setFlash('Status section berhasil diubah!', 'success');
        header('Location: ' . BASEURL . '/admin/pengaturanRPP');
        exit;
    }

function simpanField()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/admin/pengaturanRPP');
            exit;
        }

        $templateModel = $this->model('RPPTemplate_model');
        $id_field = $_POST['id_field'] ?? null;

        $data = [
            'id_section' => $_POST['id_section'],
            'nama_field' => $_POST['nama_field'],
            'kode_field' => $_POST['kode_field'],
            'tipe_input' => $_POST['tipe_input'] ?? 'textarea',
            'placeholder' => $_POST['placeholder'] ?? '',
            'urutan' => $_POST['urutan'] ?? 0,
            'is_required' => isset($_POST['is_required']) ? 1 : 0,
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];

        if ($id_field) {
            $templateModel->updateField($id_field, $data);
            Flasher::setFlash('Field berhasil diupdate!', 'success');
        } else {
            $templateModel->tambahField($data);
            Flasher::setFlash('Field berhasil ditambahkan!', 'success');
        }

        header('Location: ' . BASEURL . '/admin/pengaturanRPP');
        exit;
    }

function hapusField($id_field = null)
    {
        if (!$id_field) {
            Flasher::setFlash('ID Field tidak valid!', 'danger');
            header('Location: ' . BASEURL . '/admin/pengaturanRPP');
            exit;
        }

        $templateModel = $this->model('RPPTemplate_model');
        $templateModel->hapusField($id_field);
        Flasher::setFlash('Field berhasil dihapus!', 'success');
        header('Location: ' . BASEURL . '/admin/pengaturanRPP');
        exit;
    }

function toggleField($id_field = null)
    {
        if (!$id_field) {
            Flasher::setFlash('ID Field tidak valid!', 'danger');
            header('Location: ' . BASEURL . '/admin/pengaturanRPP');
            exit;
        }

        $templateModel = $this->model('RPPTemplate_model');
        $templateModel->toggleField($id_field);
        Flasher::setFlash('Status field berhasil diubah!', 'success');
        header('Location: ' . BASEURL . '/admin/pengaturanRPP');
        exit;
    }

function pengaturanRapor($id_guru = null)
    {
        $id_tp_aktif = $_SESSION['id_tp_aktif'] ?? 0;
        $id_semester_aktif = $_SESSION['id_semester_aktif'] ?? 0;

        // Ambil daftar wali kelas
        $waliKelasList = $this->model('WaliKelas_model')->getAllWaliKelas($id_tp_aktif);

        // Jika tidak ada id_guru dipilih, tampilkan list wali kelas
        if (!$id_guru) {
            // Tambahkan status pengaturan untuk setiap wali kelas
            $pengaturanRaporModel = $this->model('PengaturanRapor_model');
            foreach ($waliKelasList as &$wk) {
                $pengaturan = $pengaturanRaporModel->getPengaturanByGuru($wk['id_guru'], $id_tp_aktif);
                $wk['sudah_diatur'] = !empty($pengaturan);
            }
            unset($wk); // Unset reference

            $this->data['judul'] = 'Pengaturan Rapor';
            $this->data['wali_kelas_list'] = $waliKelasList;

            $this->view('templates/header', $this->data);
            $this->view('templates/sidebar_admin', $this->data);
            $this->view('admin/pengaturan_rapor_list', $this->data);
            $this->view('templates/footer');
            return;
        }

        // Get info wali kelas yang dipilih
        $waliKelasInfo = $this->model('WaliKelas_model')->getWaliKelasByGuru($id_guru, $id_tp_aktif);

        if (!$waliKelasInfo) {
            Flasher::setFlash('Data wali kelas tidak ditemukan', 'danger');
            header('Location: ' . BASEURL . '/admin/pengaturanRapor');
            exit;
        }

        $id_kelas = $waliKelasInfo['id_kelas'] ?? 0;

        // Get pengaturan rapor berdasarkan id_guru dan id_tp
        $pengaturanRapor = $this->model('PengaturanRapor_model')->getPengaturanByGuru($id_guru, $id_tp_aktif);

        // Get mapel list untuk kelas ini
        $mapelList = [];
        if ($id_kelas) {
            $mapelList = $this->model('Penugasan_model')->getMapelByKelas($id_kelas, $id_semester_aktif);
        }

        $this->data['judul'] = 'Pengaturan Rapor - ' . ($waliKelasInfo['nama_kelas'] ?? '');
        $this->data['wali_kelas_info'] = $waliKelasInfo;
        $this->data['pengaturan'] = $pengaturanRapor;
        $this->data['mapel_list'] = $mapelList;
        $this->data['id_guru'] = $id_guru;
        $this->data['session_info'] = [
            'nama_semester' => $_SESSION['nama_semester_aktif'] ?? 'Semester Tidak Diketahui'
        ];

        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/pengaturan_rapor', $this->data);
        $this->view('templates/footer');
    }

function simpanPengaturanRapor()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Flasher::setFlash('Method tidak diizinkan', 'danger');
            header('Location: ' . BASEURL . '/admin/pengaturanRapor');
            exit;
        }

        $id_guru = $_POST['id_guru'] ?? 0;
        $id_tp_aktif = $_SESSION['id_tp_aktif'] ?? 0;

        if (!$id_guru || !$id_tp_aktif) {
            Flasher::setFlash('Data guru atau tahun pelajaran tidak ditemukan', 'danger');
            header('Location: ' . BASEURL . '/admin/pengaturanRapor');
            exit;
        }

        // Handle upload gambar kop
        $pengaturanLama = $this->model('PengaturanRapor_model')->getPengaturanByGuru($id_guru, $id_tp_aktif);

        $kopFileName = $pengaturanLama['kop_rapor'] ?? '';
        $ttdKepalaFileName = $pengaturanLama['ttd_kepala_madrasah'] ?? '';
        $ttdWalasFileName = $pengaturanLama['ttd_wali_kelas'] ?? '';

        // Upload Kop Rapor
        if (isset($_FILES['kop_rapor']) && $_FILES['kop_rapor']['error'] === UPLOAD_ERR_OK) {
            $kopFileName = $this->handleRaporImageUpload($_FILES['kop_rapor'], 'kop', 'kop_' . $id_guru . '_' . $id_tp_aktif, 2097152);
            if ($kopFileName === false) {
                Flasher::setFlash('Gagal upload gambar kop', 'danger');
                header('Location: ' . BASEURL . '/admin/pengaturanRapor/' . $id_guru);
                exit;
            }
            // Hapus file lama
            if ($pengaturanLama && !empty($pengaturanLama['kop_rapor'])) {
                $this->deleteRaporImage('kop', $pengaturanLama['kop_rapor']);
            }
        }

        // Upload TTD Kepala Madrasah
        if (isset($_FILES['ttd_kepala_madrasah']) && $_FILES['ttd_kepala_madrasah']['error'] === UPLOAD_ERR_OK) {
            $ttdKepalaFileName = $this->handleRaporImageUpload($_FILES['ttd_kepala_madrasah'], 'ttd', 'ttd_kepala_' . $id_guru . '_' . $id_tp_aktif, 1048576);
            if ($ttdKepalaFileName === false) {
                Flasher::setFlash('Gagal upload tanda tangan kepala madrasah', 'danger');
                header('Location: ' . BASEURL . '/admin/pengaturanRapor/' . $id_guru);
                exit;
            }
            // Hapus file lama
            if ($pengaturanLama && !empty($pengaturanLama['ttd_kepala_madrasah'])) {
                $this->deleteRaporImage('ttd', $pengaturanLama['ttd_kepala_madrasah']);
            }
        }

        // Upload TTD Wali Kelas
        if (isset($_FILES['ttd_wali_kelas']) && $_FILES['ttd_wali_kelas']['error'] === UPLOAD_ERR_OK) {
            $ttdWalasFileName = $this->handleRaporImageUpload($_FILES['ttd_wali_kelas'], 'ttd', 'ttd_walas_' . $id_guru . '_' . $id_tp_aktif, 1048576);
            if ($ttdWalasFileName === false) {
                Flasher::setFlash('Gagal upload tanda tangan wali kelas', 'danger');
                header('Location: ' . BASEURL . '/admin/pengaturanRapor/' . $id_guru);
                exit;
            }
            // Hapus file lama
            if ($pengaturanLama && !empty($pengaturanLama['ttd_wali_kelas'])) {
                $this->deleteRaporImage('ttd', $pengaturanLama['ttd_wali_kelas']);
            }
        }

        $data = [
            'id_guru' => $id_guru,
            'id_tp' => $id_tp_aktif,
            'kop_rapor' => $kopFileName,
            'nama_madrasah' => $_POST['nama_madrasah'] ?? '',
            'tempat_cetak' => $_POST['tempat_cetak'] ?? '',
            'nama_kepala_madrasah' => $_POST['nama_kepala_madrasah'] ?? '',
            'ttd_kepala_madrasah' => $ttdKepalaFileName,
            'ttd_wali_kelas' => $ttdWalasFileName,
            'tanggal_cetak' => $_POST['tanggal_cetak'] ?? date('Y-m-d'),
            'mapel_rapor' => isset($_POST['mapel_rapor']) ? json_encode($_POST['mapel_rapor']) : '[]',
            'persen_harian_sts' => $_POST['persen_harian_sts'] ?? 60,
            'persen_sts' => $_POST['persen_sts'] ?? 40,
            'persen_harian_sas' => $_POST['persen_harian_sas'] ?? 40,
            'persen_sts_sas' => $_POST['persen_sts_sas'] ?? 30,
            'persen_sas' => $_POST['persen_sas'] ?? 30
        ];

        if ($this->model('PengaturanRapor_model')->save($data)) {
            Flasher::setFlash('Pengaturan rapor berhasil disimpan', 'success');
        } else {
            Flasher::setFlash('Gagal menyimpan pengaturan rapor', 'danger');
        }

        header('Location: ' . BASEURL . '/admin/pengaturanRapor/' . $id_guru);
        exit;
    }

function handleRaporImageUpload($file, $folder, $prefix, $maxSize)
    {
        $uploadDir = 'public/img/' . $folder . '/';

        // Buat folder jika belum ada
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];

        if (!in_array($fileExtension, $allowedExtensions)) {
            return false;
        }

        if ($file['size'] > $maxSize) {
            return false;
        }

        $fileName = $prefix . '_' . time() . '.' . $fileExtension;
        $uploadPath = $uploadDir . $fileName;

        if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
            return false;
        }

        return $fileName;
    }

function deleteRaporImage($folder, $fileName)
    {
        $filePath = 'public/img/' . $folder . '/' . $fileName;
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

function pengaturanAplikasi()
    {
        $this->data['judul'] = 'Pengaturan Aplikasi';
        $this->data['pengaturan'] = $this->model('PengaturanAplikasi_model')->getPengaturan();

        // Load document config
        $this->data['dokumen_config'] = $this->model('DokumenConfig_model')->getAllDokumenAdmin();

        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/pengaturan_aplikasi', $this->data);
        $this->view('templates/footer');
    }

function simpanPengaturanAplikasi()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Flasher::setFlash('Method tidak diizinkan', 'danger');
            header('Location: ' . BASEURL . '/admin/pengaturanAplikasi');
            exit;
        }

        $pengaturanLama = $this->model('PengaturanAplikasi_model')->getPengaturan();

        $logoFileName = $pengaturanLama['logo'] ?? '';

        // Buat folder jika belum ada - gunakan absolute path
        $baseDir = dirname(dirname(__DIR__)); // Path ke root folder absen
        $uploadDir = $baseDir . '/public/img/app/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Upload Logo
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $logoFileName = $this->handleAppImageUpload($_FILES['logo'], 'logo', 2097152);
            if ($logoFileName === false) {
                Flasher::setFlash('Gagal upload logo. Pastikan file adalah gambar dan ukuran maksimal 2MB', 'danger');
                header('Location: ' . BASEURL . '/admin/pengaturanAplikasi');
                exit;
            }
            // Hapus file lama
            if (!empty($pengaturanLama['logo'])) {
                $this->deleteAppImage($pengaturanLama['logo']);
            }
        }

        $data = [
            'nama_aplikasi' => $_POST['nama_aplikasi'] ?? 'Smart Absensi',
            'url_web' => trim($_POST['url_web'] ?? 'http://localhost/absen'),
            'logo' => $logoFileName,
            'wa_gateway_provider' => trim($_POST['wa_gateway_provider'] ?? 'fonnte'),
            'wa_gateway_url' => trim($_POST['wa_gateway_url'] ?? 'https://api.fonnte.com/send'),
            'wa_gateway_token' => trim($_POST['wa_gateway_token'] ?? ''),
            'wa_gateway_username' => trim($_POST['wa_gateway_username'] ?? ''),
            'wa_gateway_password' => trim($_POST['wa_gateway_password'] ?? ''),
            'wa_template_group_absensi' => trim($_POST['wa_template_group_absensi'] ?? '')
        ];

        if ($this->model('PengaturanAplikasi_model')->simpan($data)) {
            // Clear session cache agar perubahan langsung terlihat
            unset($_SESSION['pengaturan_aplikasi']);
            Flasher::setFlash('Pengaturan aplikasi berhasil disimpan', 'success');
        } else {
            Flasher::setFlash('Gagal menyimpan pengaturan aplikasi', 'danger');
        }

        header('Location: ' . BASEURL . '/admin/pengaturanAplikasi');
        exit;
    }

function simpanDokumenConfig()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Flasher::setFlash('Method tidak diizinkan', 'danger');
            header('Location: ' . BASEURL . '/admin/pengaturanAplikasi');
            exit;
        }

        $data = [
            'id' => $_POST['id'] ?? null,
            'kode' => strtolower(trim($_POST['kode'] ?? '')),
            'nama' => trim($_POST['nama'] ?? ''),
            'icon' => trim($_POST['icon'] ?? 'file-text'),
            'urutan' => intval($_POST['urutan'] ?? 0),
            'wajib_psb' => isset($_POST['wajib_psb']) ? 1 : 0,
            'wajib_siswa' => isset($_POST['wajib_siswa']) ? 1 : 0,
        ];

        // Validasi
        if (empty($data['kode']) || empty($data['nama'])) {
            Flasher::setFlash('Kode dan nama dokumen wajib diisi', 'danger');
            header('Location: ' . BASEURL . '/admin/pengaturanAplikasi');
            exit;
        }

        $result = $this->model('DokumenConfig_model')->simpan($data);

        if ($result) {
            Flasher::setFlash('Jenis dokumen berhasil disimpan', 'success');
        } else {
            Flasher::setFlash('Gagal menyimpan jenis dokumen', 'danger');
        }

        header('Location: ' . BASEURL . '/admin/pengaturanAplikasi');
        exit;
    }

function toggleDokumenConfig()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method tidak diizinkan']);
            exit;
        }

        $id = intval($_POST['id'] ?? 0);
        $field = $_POST['field'] ?? '';

        if (!$id || !in_array($field, ['aktif', 'wajib_psb', 'wajib_siswa'])) {
            echo json_encode(['success' => false, 'message' => 'Parameter tidak valid']);
            exit;
        }

        $model = $this->model('DokumenConfig_model');
        $result = false;

        switch ($field) {
            case 'aktif':
                $result = $model->toggleAktif($id);
                break;
            case 'wajib_psb':
                $result = $model->toggleWajibPSB($id);
                break;
            case 'wajib_siswa':
                $result = $model->toggleWajibSiswa($id);
                break;
        }

        echo json_encode(['success' => $result]);
        exit;
    }

function hapusDokumenConfig()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method tidak diizinkan']);
            exit;
        }

        $id = intval($_POST['id'] ?? 0);

        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID tidak valid']);
            exit;
        }

        $result = $this->model('DokumenConfig_model')->hapus($id);
        echo json_encode(['success' => $result]);
        exit;
    }

function testWaGateway()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method tidak diizinkan']);
            return;
        }

        $noWa = trim($_POST['no_wa'] ?? '');
        $provider = trim($_POST['provider'] ?? 'fonnte');
        $token = trim($_POST['token'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $url = trim($_POST['url'] ?? '');

        if (empty($noWa)) {
            echo json_encode(['success' => false, 'message' => 'Nomor WA harus diisi']);
            return;
        }

        // Format nomor WA
        $noWa = preg_replace('/[^0-9]/', '', $noWa);
        if (substr($noWa, 0, 1) === '0') {
            $noWa = '62' . substr($noWa, 1);
        }

        $testMessage = '✅ *Test WA Gateway Berhasil!*

Ini adalah pesan test dari sistem ' . (getPengaturanAplikasi()['nama_aplikasi'] ?? 'Smart Absensi') . '.

Waktu: ' . date('d/m/Y H:i:s');

        // Use Fonnte class for sending
        require_once APPROOT . '/app/core/Fonnte.php';

        // Create temporary instance with test credentials
        $fonnte = new Fonnte();

        // Override settings for test
        $reflection = new ReflectionClass($fonnte);
        $apiUrlProp = $reflection->getProperty('apiUrl');
        $apiUrlProp->setAccessible(true);
        $apiUrlProp->setValue($fonnte, $url ?: 'https://api.fonnte.com/send');

        $providerProp = $reflection->getProperty('provider');
        $providerProp->setAccessible(true);
        $providerProp->setValue($fonnte, $provider);

        $tokenProp = $reflection->getProperty('token');
        $tokenProp->setAccessible(true);
        $tokenProp->setValue($fonnte, $token);

        $usernameProp = $reflection->getProperty('username');
        $usernameProp->setAccessible(true);
        $usernameProp->setValue($fonnte, $username);

        $passwordProp = $reflection->getProperty('password');
        $passwordProp->setAccessible(true);
        $passwordProp->setValue($fonnte, $password);

        $result = $fonnte->send($noWa, $testMessage);

        if (isset($result['status']) && $result['status'] === true) {
            echo json_encode(['success' => true, 'message' => 'Pesan berhasil dikirim ke ' . $noWa]);
        } else {
            $errorMsg = $result['reason'] ?? $result['message'] ?? 'Gagal mengirim pesan';
            echo json_encode(['success' => false, 'message' => $errorMsg]);
        }
    }

function handleAppImageUpload($file, $prefix, $maxSize)
    {
        // Gunakan absolute path
        $baseDir = dirname(dirname(__DIR__)); // Path ke root folder absen
        $uploadDir = $baseDir . '/public/img/app/';

        // DEBUG: Log untuk troubleshooting
        error_log("=== UPLOAD DEBUG ===");
        error_log("Base Dir: " . $baseDir);
        error_log("Upload Dir: " . $uploadDir);
        error_log("Upload Dir Exists: " . (is_dir($uploadDir) ? 'YES' : 'NO'));
        error_log("Upload Dir Writable: " . (is_writable($uploadDir) ? 'YES' : 'NO'));
        error_log("File Name: " . ($file['name'] ?? 'N/A'));
        error_log("File Size: " . ($file['size'] ?? 'N/A'));
        error_log("File Error: " . ($file['error'] ?? 'N/A'));
        error_log("File Tmp: " . ($file['tmp_name'] ?? 'N/A'));

        // Pastikan folder ada
        if (!is_dir($uploadDir)) {
            $mkdirResult = @mkdir($uploadDir, 0755, true);
            error_log("Mkdir Result: " . ($mkdirResult ? 'SUCCESS' : 'FAILED'));
            if (!$mkdirResult) {
                error_log("Mkdir Error: " . error_get_last()['message'] ?? 'Unknown');
                return false;
            }
        }

        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'ico'];

        if (!in_array($fileExtension, $allowedExtensions)) {
            error_log("Extension not allowed: " . $fileExtension);
            return false;
        }

        if ($file['size'] > $maxSize) {
            error_log("File too large: " . $file['size'] . " > " . $maxSize);
            return false;
        }

        $fileName = $prefix . '_' . time() . '.' . $fileExtension;
        $uploadPath = $uploadDir . $fileName;

        error_log("Target Path: " . $uploadPath);

        if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
            error_log('Move failed to: ' . $uploadPath);
            error_log('Last Error: ' . json_encode(error_get_last()));
            return false;
        }

        error_log("Upload SUCCESS: " . $fileName);
        return $fileName;
    }

function deleteAppImage($fileName)
    {
        $baseDir = dirname(dirname(__DIR__));
        $filePath = $baseDir . '/public/img/app/' . $fileName;
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

function toggleWajibRPP()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/admin/pengaturanRPP');
            exit;
        }

        $pengaturan = $this->model('PengaturanRPP_model')->getPengaturan();
        $newStatus = empty($pengaturan['wajib_rpp_disetujui']) ? 1 : 0;

        // Jika diaktifkan, otomatis blokir semua fitur
        $data = [
            'wajib_rpp_disetujui' => $newStatus,
            'blokir_absensi' => $newStatus ? 1 : 0,
            'blokir_jurnal' => $newStatus ? 1 : 0,
            'blokir_nilai' => $newStatus ? 1 : 0,
            'pesan_blokir' => $pengaturan['pesan_blokir'] ?? 'Anda belum dapat mengakses fitur ini karena RPP belum dibuat atau belum disetujui oleh Kepala Madrasah.'
        ];

        if ($this->model('PengaturanRPP_model')->simpan($data)) {
            // Hapus semua cache terkait pengaturan RPP
            unset($_SESSION['pengaturan_wajib_rpp']);
            unset($_SESSION['pengaturan_wajib_rpp_time']);

            $status = $newStatus ? 'diaktifkan' : 'dinonaktifkan';
            Flasher::setFlash("Wajib RPP Disetujui berhasil $status", 'success');
        } else {
            Flasher::setFlash('Gagal mengubah pengaturan', 'danger');
        }

        header('Location: ' . BASEURL . '/admin/pengaturanRPP');
        exit;
    }

function pengaturanWajibRPP()
    {
        // Redirect ke halaman pengaturan RPP
        header('Location: ' . BASEURL . '/admin/pengaturanRPP');
        exit;
    }

function simpanPengaturanWajibRPP()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/admin/pengaturanRPP');
            exit;
        }

        $data = [
            'wajib_rpp_disetujui' => isset($_POST['wajib_rpp_disetujui']) ? 1 : 0,
            'blokir_absensi' => isset($_POST['blokir_absensi']) ? 1 : 0,
            'blokir_jurnal' => isset($_POST['blokir_jurnal']) ? 1 : 0,
            'blokir_nilai' => isset($_POST['blokir_nilai']) ? 1 : 0,
            'pesan_blokir' => $_POST['pesan_blokir'] ?? ''
        ];

        if ($this->model('PengaturanRPP_model')->simpan($data)) {
            // Hapus semua cache terkait pengaturan RPP
            unset($_SESSION['pengaturan_wajib_rpp']);
            unset($_SESSION['pengaturan_wajib_rpp_time']);

            Flasher::setFlash('Pengaturan wajib RPP berhasil disimpan', 'success');
        } else {
            Flasher::setFlash('Gagal menyimpan pengaturan', 'danger');
        }

        header('Location: ' . BASEURL . '/admin/pengaturanRPP');
        exit;
    }

function pengaturanSistem()
    {
        $pengaturanModel = $this->model('PengaturanSistem_model');
        $settings = $pengaturanModel->getAll();

        $data = [
            'title' => 'Pengaturan Sistem',
            'sidebar' => 'templates/sidebar_admin',
            'settings' => $settings
        ];

        $this->view('templates/header', $data);
        $this->view('templates/sidebar_admin', $data);
        $this->view('admin/pengaturan_sistem', $data);
        $this->view('templates/footer');
    }

function updatePengaturanSistem()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/admin/pengaturanSistem');
            exit;
        }

        $pengaturanModel = $this->model('PengaturanSistem_model');

        $settings = [
            'secret_key' => $_POST['secret_key'] ?? '',
            'qr_enabled' => isset($_POST['qr_enabled']) ? '1' : '0',
            'google_oauth_enabled' => isset($_POST['google_oauth_enabled']) ? '1' : '0',
            'google_client_id' => $_POST['google_client_id'] ?? '',
            'google_client_secret' => $_POST['google_client_secret'] ?? '',
            'google_allowed_domain' => $_POST['google_allowed_domain'] ?? '',
            'menu_input_nilai_enabled' => isset($_POST['menu_input_nilai_enabled']) ? '1' : '0',
            'menu_pembayaran_enabled' => isset($_POST['menu_pembayaran_enabled']) ? '1' : '0',
            'menu_rapor_enabled' => isset($_POST['menu_rapor_enabled']) ? '1' : '0',
        ];

        $pengaturanModel->updateMultiple($settings);

        Flasher::setFlash('Pengaturan berhasil disimpan!', 'success');
        header('Location: ' . BASEURL . '/admin/pengaturanSistem');
        exit;
    }

function antrianWa()
    {
        $this->data['judul'] = 'Antrian Pesan WhatsApp';

        $queueModel = $this->model('WaQueue_model');
        $pengaturanSistemModel = $this->model('PengaturanSistem_model');

        // Statistik
        $this->data['stats'] = $queueModel->getQueueStats();
        $this->data['stats_by_jenis'] = $queueModel->getStatsByJenis();
        $this->data['today_count'] = $queueModel->getTodayCount();

        // Notifikasi Absensi Status
        $this->data['notif_enabled'] = ($pengaturanSistemModel->get('wa_notif_absensi_enabled') ?? '1') === '1';
        $this->data['notif_mode'] = $pengaturanSistemModel->get('wa_notif_absensi_mode') ?? 'personal';
        $this->data['queue_enabled'] = ($pengaturanSistemModel->get('wa_queue_enabled') ?? '1') === '1';

        // Filter & Pagination
        $status = $_GET['status'] ?? 'all';
        $page = max(1, intval($_GET['page'] ?? 1));
        $perPage = 20;

        $this->data['filter_status'] = $status;

        // Get paginated messages
        $result = $queueModel->getPaginated($status, $page, $perPage);
        $this->data['messages'] = $result['data'];
        $this->data['pagination'] = [
            'current' => $result['page'],
            'total_pages' => $result['total_pages'],
            'total' => $result['total'],
            'per_page' => $result['per_page']
        ];

        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/antrian_wa', $this->data);
        $this->view('templates/footer', $this->data);
    }

function toggleNotifikasiAbsensi()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/admin/antrianWa');
            exit;
        }

        $pengaturanSistemModel = $this->model('PengaturanSistem_model');
        $currentValue = $pengaturanSistemModel->get('wa_notif_absensi_enabled') ?? '1';
        $newValue = $currentValue === '1' ? '0' : '1';

        $pengaturanSistemModel->set('wa_notif_absensi_enabled', $newValue);

        $status = $newValue === '1' ? 'diaktifkan' : 'dinonaktifkan';
        Flasher::setFlash("Notifikasi absensi berhasil {$status}", 'success');

        header('Location: ' . BASEURL . '/admin/antrianWa');
        exit;
    }

function toggleQueueMode()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/admin/antrianWa');
            exit;
        }

        $pengaturanSistemModel = $this->model('PengaturanSistem_model');
        $currentValue = $pengaturanSistemModel->get('wa_queue_enabled') ?? '1';
        $newValue = $currentValue === '1' ? '0' : '1';

        $pengaturanSistemModel->set('wa_queue_enabled', $newValue);

        $mode = $newValue === '1' ? 'ANTRIAN (Queue)' : 'LANGSUNG (Direct)';
        Flasher::setFlash("Mode pengiriman WA diubah ke {$mode}", 'success');

        header('Location: ' . BASEURL . '/admin/antrianWa');
        exit;
    }

function retryWaMessage($id = null)
    {
        if (!$id) {
            Flasher::setFlash('ID tidak valid!', 'danger');
            header('Location: ' . BASEURL . '/admin/antrianWa');
            exit;
        }

        $queueModel = $this->model('WaQueue_model');
        $pengaturanSistemModel = $this->model('PengaturanSistem_model');

        // Cek apakah antrian aktif
        $queueEnabled = ($pengaturanSistemModel->get('wa_queue_enabled') ?? '1') === '1';

        if (!$queueEnabled) {
            // MODE DIRECT: Kirim langsung sekarang juga
            require_once APPROOT . '/app/core/Fonnte.php';
            $fonnte = new Fonnte();

            // Ambil data pesan
            $msg = $queueModel->getById($id);

            if ($msg) {
                // Set processing
                $queueModel->markAsProcessing($id);

                try {
                    // Coba kirim
                    $response = $fonnte->send($msg['no_wa'], $msg['pesan']); // Gunakan send standard, fonnte class akan handle provider

                    if (isset($response['status']) && $response['status'] === true) {
                        $queueModel->markAsSent($id, json_encode($response));
                        Flasher::setFlash('Pesan berhasil dikirim ulang secara langsung.', 'success');
                        header('Location: ' . BASEURL . '/admin/antrianWa?status=sent');
                    } else {
                        $errorMsg = $response['reason'] ?? $response['message'] ?? 'Unknown error';
                        $queueModel->markAsFailedDirect($id, $errorMsg);
                        Flasher::setFlash('Gagal mengirim ulang: ' . $errorMsg, 'danger');
                        header('Location: ' . BASEURL . '/admin/antrianWa?status=failed');
                    }
                } catch (Exception $e) {
                    $queueModel->markAsFailedDirect($id, $e->getMessage());
                    Flasher::setFlash('Error saat retry: ' . $e->getMessage(), 'danger');
                    header('Location: ' . BASEURL . '/admin/antrianWa?status=failed');
                }
            } else {
                Flasher::setFlash('Data pesan tidak ditemukan.', 'danger');
                header('Location: ' . BASEURL . '/admin/antrianWa');
            }
        } else {
            // MODE QUEUE: Reset jadi pending (default behavior)
            $queueModel->retryFailed($id);
            Flasher::setFlash('Pesan dikembalikan ke antrian (Pending).', 'success');
            header('Location: ' . BASEURL . '/admin/antrianWa?status=pending');
        }
        exit;
    }

function retryAllWaMessages()
    {
        $queueModel = $this->model('WaQueue_model');
        $queueModel->retryAllFailed();

        Flasher::setFlash('Semua pesan gagal akan dicoba kirim ulang.', 'success');
        header('Location: ' . BASEURL . '/admin/antrianWa');
        exit;
    }

function hapusWaMessage($id = null)
    {
        if (!$id) {
            Flasher::setFlash('ID tidak valid!', 'danger');
            header('Location: ' . BASEURL . '/admin/antrianWa');
            exit;
        }

        $queueModel = $this->model('WaQueue_model');
        $queueModel->delete($id);

        Flasher::setFlash('Pesan dihapus dari antrian.', 'success');
        header('Location: ' . BASEURL . '/admin/antrianWa');
        exit;
    }

function bulkDeleteWaMessages()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ids = $_POST['ids'] ?? [];

            if (empty($ids)) {
                Flasher::setFlash('Tidak ada pesan yang dipilih!', 'warning');
            } else {
                $queueModel = $this->model('WaQueue_model');
                $queueModel->bulkDelete($ids);
                Flasher::setFlash(count($ids) . ' pesan berhasil dihapus.', 'success');
            }
        }

        header('Location: ' . BASEURL . '/admin/antrianWa');
        exit;
    }

function prosesAntrianWa()
    {
        set_time_limit(120); // 2 minutes max

        require_once APPROOT . '/app/core/Fonnte.php';
        $queueModel = $this->model('WaQueue_model');
        $fonnte = new Fonnte();

        $processed = 0;
        $maxProcess = 5;

        $pendingMessages = $queueModel->getPendingMessages($maxProcess);

        foreach ($pendingMessages as $msg) {
            $queueModel->markAsProcessing($msg['id']);

            try {
                $response = $fonnte->send($msg['no_wa'], $msg['pesan']);

                if (isset($response['status']) && $response['status'] === true) {
                    $queueModel->markAsSent($msg['id'], json_encode($response));
                } else {
                    $errorMsg = $response['reason'] ?? $response['message'] ?? 'Unknown error';
                    $queueModel->markAsFailed($msg['id'], $errorMsg);
                }
            } catch (Exception $e) {
                $queueModel->markAsFailed($msg['id'], $e->getMessage());
            }

            $processed++;

            // Delay between messages
            if ($processed < count($pendingMessages)) {
                sleep(rand(8, 12));
            }
        }

        Flasher::setFlash("Berhasil proses {$processed} pesan.", 'success');
        header('Location: ' . BASEURL . '/admin/antrianWa');
        exit;
    }

function updateCronToken()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit;
        }

        // Check if admin
        if ($_SESSION['role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $token = $input['token'] ?? '';

        if (empty($token)) {
            echo json_encode(['success' => false, 'message' => 'Token cannot be empty']);
            exit;
        }

        // Save to DB
        $pengaturanModel = $this->model('PengaturanAplikasi_model');
        $success = $pengaturanModel->updateCronSecret($token);

        if ($success) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to save token']);
        }
        exit;
    }

function riwayatLogin()
    {
        $this->data['judul'] = 'Riwayat Login';

        // Get query params
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $limit = (int) ($_GET['limit'] ?? 25);
        $search = trim($_GET['search'] ?? '');
        $status = $_GET['status'] ?? 'all';
        $sortBy = $_GET['sort'] ?? 'login_at';
        $sortOrder = $_GET['order'] ?? 'DESC';

        // Validate limit options
        if (!in_array($limit, [10, 25, 50, 100])) {
            $limit = 25;
        }

        $offset = ($page - 1) * $limit;

        $loginHistoryModel = $this->model('LoginHistory_model');

        // Get data
        $this->data['logs'] = $loginHistoryModel->getAll([
            'limit' => $limit,
            'offset' => $offset,
            'search' => $search,
            'status' => $status,
            'sort_by' => $sortBy,
            'sort_order' => $sortOrder
        ]);

        // Get total count for pagination
        $totalRecords = $loginHistoryModel->countAll([
            'search' => $search,
            'status' => $status
        ]);
        $totalPages = max(1, ceil($totalRecords / $limit));

        // Get stats
        $this->data['stats'] = $loginHistoryModel->getStats();

        // Pagination data
        $this->data['pagination'] = [
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_records' => $totalRecords,
            'limit' => $limit
        ];

        // Filter data
        $this->data['filter'] = [
            'search' => $search,
            'status' => $status,
            'sort' => $sortBy,
            'order' => $sortOrder
        ];

        // Cleanup old records (every request, but it's a quick query)
        $loginHistoryModel->deleteOldRecords(90);

        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/riwayat_login', $this->data);
        $this->view('templates/footer', $this->data);
    }

function pengaturanFieldSiswa()
    {
        $this->data['judul'] = 'Pengaturan Field Data Siswa';

        $pengaturanModel = $this->model('PengaturanAplikasi_model');
        $this->data['fieldConfig'] = $pengaturanModel->getFieldSiswaConfig();
        $this->data['mandatoryFields'] = $pengaturanModel->getMandatoryFields();

        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/pengaturan_field_siswa', $this->data);
        $this->view('templates/footer');
    }

function simpanPengaturanFieldSiswa()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/admin/pengaturanFieldSiswa');
            exit;
        }

        $pengaturanModel = $this->model('PengaturanAplikasi_model');
        $mandatoryFields = $pengaturanModel->getMandatoryFields();

        // Get submitted fields
        $submittedFields = $_POST['fields'] ?? [];

        // Build config array (all fields start as false, checked ones become true)
        $defaultConfig = $pengaturanModel->getDefaultFieldConfig();
        $newConfig = [];

        foreach ($defaultConfig as $field => $defaultValue) {
            if (in_array($field, $mandatoryFields)) {
                // Mandatory fields are always true
                $newConfig[$field] = true;
            } else {
                // Optional fields based on checkbox
                $newConfig[$field] = isset($submittedFields[$field]);
            }
        }

        if ($pengaturanModel->saveFieldSiswaConfig($newConfig)) {
            Flasher::setFlash('Pengaturan field data siswa berhasil disimpan', 'success');
        } else {
            Flasher::setFlash('Gagal menyimpan pengaturan', 'danger');
        }

        header('Location: ' . BASEURL . '/admin/pengaturanFieldSiswa');
        exit;
    }

function pengaturanNotifikasiAbsensi()
    {
        $this->data['judul'] = 'Pengaturan Notifikasi Absensi';

        $pengaturanModel = $this->model('PengaturanAplikasi_model');
        $pengaturanSistemModel = $this->model('PengaturanSistem_model');
        $kelasModel = $this->model('Kelas_model');
        $grupModel = $this->model('KelasGrupWa_model');

        $id_tp = $_SESSION['id_tp_aktif'] ?? 0;

        $this->data['pengaturan'] = $pengaturanModel->getPengaturan();
        $this->data['notif_mode'] = $pengaturanSistemModel->get('wa_notif_absensi_mode') ?? 'personal';
        $this->data['notif_enabled'] = ($pengaturanSistemModel->get('wa_notif_absensi_enabled') ?? '1') === '1';
        $this->data['kelas_list'] = $kelasModel->getKelasByTP($id_tp);
        $this->data['grup_data'] = $grupModel->getAllGrupWithKelas();

        // Detect WA Gateway provider for sync button
        $this->data['wa_provider'] = $pengaturanSistemModel->get('wa_gateway_provider')
            ?? $this->data['pengaturan']['wa_gateway_provider']
            ?? 'fonnte';

        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/pengaturan_notifikasi_absensi', $this->data);
        $this->view('templates/footer');
    }

function syncGrupFonnte()
    {
        require_once APPROOT . '/app/core/Fonnte.php';
        $fonnte = new Fonnte();

        // Step 1: Fetch/update grup dari WA ke Fonnte server
        $fetchResult = $fonnte->fetchWhatsAppGroups();
        error_log("[AdminController::syncGrupFonnte] Fetch result: " . json_encode($fetchResult));

        // Step 2: Ambil daftar grup dari Fonnte
        $getResult = $fonnte->getWhatsAppGroups();
        error_log("[AdminController::syncGrupFonnte] Get result: " . json_encode($getResult));

        if (isset($getResult['data']) && is_array($getResult['data'])) {
            $groups = $getResult['data'];
            $grupCount = count($groups);

            // Simpan ke session untuk ditampilkan di UI
            $_SESSION['fonnte_groups'] = $groups;

            Flasher::setFlash("✅ Berhasil sync {$grupCount} grup dari Fonnte! Pilih grup dari daftar untuk menambahkan.", 'success');
        } else {
            $reason = $getResult['reason'] ?? $getResult['detail'] ?? 'Unknown error';
            Flasher::setFlash("❌ Gagal sync grup: {$reason}", 'danger');
        }

        header('Location: ' . BASEURL . '/admin/pengaturanNotifikasiAbsensi');
        exit;
    }

function syncGrupGowa()
    {
        require_once APPROOT . '/app/core/Fonnte.php';
        $gowa = new Fonnte();

        // Ambil daftar grup dari GOWA
        $getResult = $gowa->getGowaGroups();
        error_log("[AdminController::syncGrupGowa] Get result: " . json_encode($getResult));

        if (isset($getResult['status']) && $getResult['status'] === true && isset($getResult['data'])) {
            $groups = $getResult['data'];
            $grupCount = count($groups);

            // Simpan ke session untuk ditampilkan di UI (format sesuai GOWA)
            $_SESSION['gowa_groups'] = $groups;

            Flasher::setFlash("✅ Berhasil sync {$grupCount} grup dari GOWA! Pilih grup dari daftar untuk menambahkan.", 'success');
        } else {
            $reason = $getResult['reason'] ?? 'Unknown error';
            Flasher::setFlash("❌ Gagal sync grup dari GOWA: {$reason}", 'danger');
        }

        header('Location: ' . BASEURL . '/admin/pengaturanNotifikasiAbsensi');
        exit;
    }

function simpanPengaturanNotifikasiAbsensi()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/admin/pengaturanNotifikasiAbsensi');
            exit;
        }

        $pengaturanSistemModel = $this->model('PengaturanSistem_model');

        $mode = $_POST['notif_mode'] ?? 'personal';
        $enabled = isset($_POST['notif_enabled']) ? '1' : '0';

        // Validate mode
        $allowedModes = ['personal', 'grup', 'both', 'off'];
        if (!in_array($mode, $allowedModes)) {
            $mode = 'personal';
        }

        $pengaturanSistemModel->set('wa_notif_absensi_mode', $mode);
        $pengaturanSistemModel->set('wa_notif_absensi_enabled', $enabled);

        Flasher::setFlash('Pengaturan notifikasi absensi berhasil disimpan', 'success');
        header('Location: ' . BASEURL . '/admin/pengaturanNotifikasiAbsensi');
        exit;
    }

function tambahGrupWaKelas()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/admin/pengaturanNotifikasiAbsensi');
            exit;
        }

        $grupModel = $this->model('KelasGrupWa_model');

        $id_kelas = intval($_POST['id_kelas'] ?? 0);
        $nama_grup = trim($_POST['nama_grup'] ?? '');
        $grup_wa_id = trim($_POST['grup_wa_id'] ?? '');

        if ($id_kelas > 0 && !empty($nama_grup) && !empty($grup_wa_id)) {
            if ($grupModel->addGrup($id_kelas, $nama_grup, $grup_wa_id)) {
                Flasher::setFlash('Grup WhatsApp berhasil ditambahkan', 'success');
            } else {
                Flasher::setFlash('Gagal menambahkan grup WhatsApp', 'danger');
            }
        } else {
            Flasher::setFlash('Data tidak lengkap', 'warning');
        }

        header('Location: ' . BASEURL . '/admin/pengaturanNotifikasiAbsensi');
        exit;
    }

function editGrupWaKelas()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/admin/pengaturanNotifikasiAbsensi');
            exit;
        }

        $grupModel = $this->model('KelasGrupWa_model');

        $id = intval($_POST['id'] ?? 0);
        $nama_grup = trim($_POST['nama_grup'] ?? '');
        $grup_wa_id = trim($_POST['grup_wa_id'] ?? '');

        if ($id > 0 && !empty($nama_grup) && !empty($grup_wa_id)) {
            if ($grupModel->updateGrup($id, $nama_grup, $grup_wa_id)) {
                Flasher::setFlash('Grup WhatsApp berhasil diperbarui', 'success');
            } else {
                Flasher::setFlash('Gagal memperbarui grup WhatsApp', 'danger');
            }
        } else {
            Flasher::setFlash('Data tidak lengkap', 'warning');
        }

        header('Location: ' . BASEURL . '/admin/pengaturanNotifikasiAbsensi');
        exit;
    }

function toggleGrupWaKelas($id)
    {
        $grupModel = $this->model('KelasGrupWa_model');

        if ($grupModel->toggleActive(intval($id))) {
            Flasher::setFlash('Status grup WhatsApp berhasil diubah', 'success');
        } else {
            Flasher::setFlash('Gagal mengubah status grup', 'danger');
        }

        header('Location: ' . BASEURL . '/admin/pengaturanNotifikasiAbsensi');
        exit;
    }

function testGrupWa($id)
    {
        $grupModel = $this->model('KelasGrupWa_model');
        $grup = $grupModel->getGrupById($id);

        if (!$grup) {
            Flasher::setFlash('Grup tidak ditemukan', 'danger');
            header('Location: ' . BASEURL . '/admin/pengaturanNotifikasiAbsensi');
            exit;
        }

        $queueModel = $this->model('WaQueue_model');
        $namaGrup = $grup['nama_grup'];
        $grupId = $grup['grup_wa_id'];

        // Pesan Default
        $pesan = "*TEST PESAN WA GRUP*\n\n";
        $pesan .= "Halo grup *{$namaGrup}*,\n";
        $pesan .= "Ini adalah pesan percobaan dari Sistem Notifikasi Absensi.\n";
        $pesan .= "Jika pesan ini diterima, berarti ID Grup *{$grupId}* sudah benar.\n\n";
        $pesan .= "Waktu kirim: " . date('d-m-Y H:i:s');

        // Tambahkan ke antrian (akan otomatis cek mode queue/direct di model)
        // Metadata: { "grup_id": "...", "nama_grup": "..." }
        $queueId = $queueModel->addToQueue(
            $grupId,
            $pesan,
            'test_grup',
            ['grup_id' => $id, 'nama_grup' => $namaGrup]
        );

        if ($queueId) {
            Flasher::setFlash("Pesan test berhasil dibuat/dikirim ke grup {$namaGrup}", 'success');
        } else {
            Flasher::setFlash("Gagal membuat/mengirim pesan test ke grup {$namaGrup}", 'danger');
        }

        header('Location: ' . BASEURL . '/admin/pengaturanNotifikasiAbsensi');
        exit;
    }

function hapusGrupWaKelas($id)
    {
        $grupModel = $this->model('KelasGrupWa_model');

        if ($grupModel->deleteGrup(intval($id))) {
            Flasher::setFlash('Grup WhatsApp berhasil dihapus', 'success');
        } else {
            Flasher::setFlash('Gagal menghapus grup WhatsApp', 'danger');
        }

        header('Location: ' . BASEURL . '/admin/pengaturanNotifikasiAbsensi');
        exit;
    }
}
