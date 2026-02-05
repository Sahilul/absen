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
        $sendSuccess = false;
        $maxRetries = 3; // Maksimal percobaan dengan akun berbeda
        $triedAccounts = []; // Track akun yang sudah dicoba
        $lastError = '';

        // HANYA notifikasi absensi yang pakai multi WA gateway rotasi
        if ($isAbsensi && $rotationEnabled) {

            // Loop untuk mencoba dengan akun berbeda jika gagal
            for ($retry = 0; $retry < $maxRetries && !$sendSuccess; $retry++) {
                $account = $waAccountModel->pickAccount($rotationMode, $lastAccountId);

                // Skip akun yang sudah dicoba
                while ($account && in_array($account['id'], $triedAccounts)) {
                    $lastAccountId = $account['id'];
                    $account = $waAccountModel->pickAccount($rotationMode, $lastAccountId);
                }

                if ($account) {
                    // Konfigurasi Fonnte dengan akun terpilih
                    $fonnte->configureFromAccount($account);
                    $usedAccountId = $account['id'];
                    $usedAccountName = $account['nama'];
                    $triedAccounts[] = $account['id'];

                    echo "[Multi:{$usedAccountName}] ";

                    // Send message
                    $response = $fonnte->send($noWa, $pesan);

                    if (isset($response['status']) && $response['status'] === true) {
                        $sendSuccess = true;
                        $queueModel->markAsSent($id, json_encode($response), $usedAccountId);
                        $waAccountModel->incrementSent($usedAccountId);
                        $lastAccountId = $account['id'];
                        echo "SENT\n";
                        $results[] = ['id' => $id, 'jenis' => $jenis, 'status' => 'sent', 'account' => $usedAccountName];
                    } else {
                        $lastError = $response['reason'] ?? $response['message'] ?? 'Unknown error';
                        echo "FAILED ({$lastError}) ";

                        // Cek apakah error mengindikasikan akun terblokir
                        if (WaAccount_model::checkIfErrorIsBlock($lastError)) {
                            echo ">>> [BLOCKED] Auto-disabling {$usedAccountName}...\n";
                            $waAccountModel->markAsBlocked($usedAccountId, $lastError);

                            // Notifikasi admin
                            $adminNotifNumber = $pengaturan['admin_wa_number'] ?? null;
                            if ($adminNotifNumber) {
                                $alertFonnte = new Fonnte();
                                $alertFonnte->send($adminNotifNumber, "⚠️ *WA Gateway Blocked*\n\nAkun: {$usedAccountName}\nError: {$lastError}\nWaktu: " . date('d/m/Y H:i:s'));
                            }
                        }

                        // Coba akun lain jika masih ada retry
                        if ($retry < $maxRetries - 1) {
                            echo ">>> Trying next account...\n";
                            $lastAccountId = $account['id'];
                        }
                    }
                } else {
                    // Tidak ada akun aktif tersedia
                    echo "[No Active Account] ";
                    break;
                }
            }

            // Tidak ada fallback ke default gateway
            // Hanya gunakan akun dari Multi WA Gateway

        } else {
            // Notifikasi lainnya pakai gateway default dari pengaturan_aplikasi
            echo "[Default] ";

            $response = $fonnte->send($noWa, $pesan);

            if (isset($response['status']) && $response['status'] === true) {
                $sendSuccess = true;
                $queueModel->markAsSent($id, json_encode($response), null);
                echo "SENT\n";
                $results[] = ['id' => $id, 'jenis' => $jenis, 'status' => 'sent', 'account' => 'Default'];
            } else {
                $lastError = $response['reason'] ?? $response['message'] ?? 'Unknown error';
            }
        }

        // Jika masih gagal setelah semua percobaan
        if (!$sendSuccess) {
            $queueModel->markAsFailed($id, $lastError);
            echo "FINAL FAILED: {$lastError}\n";
            $results[] = ['id' => $id, 'jenis' => $jenis, 'status' => 'failed', 'error' => $lastError, 'account' => $usedAccountName];
        }

    } catch (Exception $e) {
        $queueModel->markAsFailed($id, $e->getMessage());
        echo "ERROR: " . $e->getMessage() . "\n";
        $results[] = ['id' => $id, 'jenis' => $jenis, 'status' => 'error', 'error' => $e->getMessage()];
    }

    $processed++;

    // Update last used account ID untuk round robin (hanya jika absensi & berhasil)
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
