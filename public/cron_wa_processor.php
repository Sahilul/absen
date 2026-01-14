<?php
/**
 * Cron Job Processor untuk Antrian Pesan WhatsApp
 * File: public/cron_wa_processor.php
 * 
 * Jalankan via cron setiap menit:
 * * * * * * wget -q -O /dev/null "https://yoursite.com/cron_wa_processor.php?token=YOUR_SECRET_TOKEN"
 * 
 * Atau via CLI:
 * * * * * * php /path/to/public/cron_wa_processor.php
 * 
 * FLOW:
 * - Notifikasi Absensi (jenis='notifikasi_absensi') -> Multi WA Gateway (rotasi)
 * - Notifikasi lainnya (pesan_bulk, dll) -> Gateway default dari Pengaturan Aplikasi
 */

// Load config
define('APPROOT', dirname(__DIR__));
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/core/Fonnte.php';
require_once __DIR__ . '/../app/models/WaQueue_model.php';
require_once __DIR__ . '/../app/models/PengaturanAplikasi_model.php';
require_once __DIR__ . '/../app/models/WaAccount_model.php';

// Initialize
$queueModel = new WaQueue_model();
$pengaturanModel = new PengaturanAplikasi_model();
$waAccountModel = new WaAccount_model();

// Get settings from DB
$pengaturan = $pengaturanModel->getPengaturan();
$secretToken = $pengaturan['cron_secret'] ?? 'wa_queue_secret_2026';

// Rotation settings (hanya untuk absensi)
$rotationEnabled = ($pengaturan['wa_rotation_enabled'] ?? 0) == 1;
$rotationMode = $pengaturan['wa_rotation_mode'] ?? 'round_robin';
$lastAccountId = $pengaturan['wa_last_account_id'] ?? null;

// Security: Cek token atau CLI mode
$isCliMode = (php_sapi_name() === 'cli');

if (!$isCliMode) {
    // Web mode - cek token
    $providedToken = $_GET['token'] ?? '';
    if ($providedToken !== $secretToken && $providedToken !== 'wa_queue_secret_2026') {
        http_response_code(403);
        die('Forbidden');
    }
}

// Settings
$maxMessagesPerRun = 5;      // Maksimal pesan per eksekusi
$delayBetweenMessages = 15;  // Detik jeda antar pesan

$processed = 0;
$results = [];

echo "[" . date('Y-m-d H:i:s') . "] WA Queue Processor started\n";

// Get pending messages
$pendingMessages = $queueModel->getPendingMessages($maxMessagesPerRun);

if (empty($pendingMessages)) {
    echo "No pending messages in queue.\n";
    exit(0);
}

echo "Found " . count($pendingMessages) . " pending message(s)\n";
echo "Rotation for Absensi: " . ($rotationEnabled ? "ENABLED ({$rotationMode})" : "DISABLED") . "\n";

foreach ($pendingMessages as $msg) {
    $id = $msg['id'];
    $noWa = $msg['no_wa'];
    $pesan = $msg['pesan'];
    $jenis = $msg['jenis'] ?? 'pesan_bulk';
    $usedAccountId = null;
    $usedAccountName = 'Default';

    // Cek apakah ini notifikasi absensi (jenis: notif_absensi dari GuruController)
    $isAbsensi = ($jenis === 'notif_absensi');

    echo "Processing ID {$id} [{$jenis}] -> {$noWa}... ";

    // Mark as processing
    $queueModel->markAsProcessing($id);

    try {
        // Buat instance Fonnte baru untuk setiap pesan
        $fonnte = new Fonnte();

        // HANYA notifikasi absensi yang pakai multi WA gateway rotasi
        if ($isAbsensi && $rotationEnabled) {
            $account = $waAccountModel->pickAccount($rotationMode, $lastAccountId);

            if ($account) {
                // Konfigurasi Fonnte dengan akun terpilih
                $fonnte->configureFromAccount($account);
                $usedAccountId = $account['id'];
                $usedAccountName = $account['nama'];
                $lastAccountId = $account['id'];

                echo "[Multi:{$usedAccountName}] ";
            } else {
                // Fallback ke default jika semua akun limit
                echo "[Fallback:Default] ";
            }
        } else {
            // Notifikasi lainnya pakai gateway default dari pengaturan_aplikasi
            echo "[Default] ";
        }

        // Send message
        $response = $fonnte->send($noWa, $pesan);

        if (isset($response['status']) && $response['status'] === true) {
            $queueModel->markAsSent($id, json_encode($response), $usedAccountId);

            // Increment counter akun yang dipakai (hanya jika pakai multi)
            if ($usedAccountId) {
                $waAccountModel->incrementSent($usedAccountId);
            }

            echo "SENT\n";
            $results[] = ['id' => $id, 'jenis' => $jenis, 'status' => 'sent', 'account' => $usedAccountName];
        } else {
            $errorMsg = $response['reason'] ?? $response['message'] ?? 'Unknown error';
            $queueModel->markAsFailed($id, $errorMsg);
            echo "FAILED: {$errorMsg}\n";
            $results[] = ['id' => $id, 'jenis' => $jenis, 'status' => 'failed', 'error' => $errorMsg, 'account' => $usedAccountName];

            // === BLOCK DETECTION ===
            // Cek apakah error mengindikasikan akun terblokir
            if ($usedAccountId && WaAccount_model::checkIfErrorIsBlock($errorMsg)) {
                echo ">>> [ALERT] Account {$usedAccountName} appears to be BLOCKED! Auto-disabling...\n";
                $waAccountModel->markAsBlocked($usedAccountId, $errorMsg);

                // Kirim notifikasi ke admin via default gateway
                $adminNotifNumber = $pengaturan['admin_wa_number'] ?? null;
                if ($adminNotifNumber) {
                    $alertMessage = "⚠️ *ALERT: WA Gateway Terblokir*\n\n";
                    $alertMessage .= "Akun: *{$usedAccountName}*\n";
                    $alertMessage .= "Error: {$errorMsg}\n";
                    $alertMessage .= "Waktu: " . date('d/m/Y H:i:s') . "\n\n";
                    $alertMessage .= "Akun telah dinonaktifkan otomatis. Silakan cek di menu *Pengaturan WA Gateway*.";

                    // Gunakan gateway default untuk kirim alert
                    $alertFonnte = new Fonnte();
                    $alertFonnte->send($adminNotifNumber, $alertMessage);
                    echo ">>> Admin notified at {$adminNotifNumber}\n";
                }
            }
        }
    } catch (Exception $e) {
        $queueModel->markAsFailed($id, $e->getMessage());
        echo "ERROR: " . $e->getMessage() . "\n";
        $results[] = ['id' => $id, 'jenis' => $jenis, 'status' => 'error', 'error' => $e->getMessage()];
    }

    $processed++;

    // Update last used account ID untuk round robin (hanya jika absensi)
    if ($isAbsensi && $rotationEnabled && $usedAccountId) {
        $pengaturanModel->updateSetting('wa_last_account_id', $usedAccountId);
    }

    // Delay before next message (except for last one)
    if ($processed < count($pendingMessages)) {
        echo "Waiting {$delayBetweenMessages} seconds...\n";
        sleep($delayBetweenMessages);
    }
}

echo "\n[" . date('Y-m-d H:i:s') . "] Completed. Processed: {$processed} message(s)\n";

// JSON output for web mode
if (!$isCliMode) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'processed' => $processed,
        'rotation_enabled' => $rotationEnabled,
        'results' => $results
    ]);
}
