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

    // Cek apakah pesan ini menggunakan rotation (Absensi, Test, Bulk)
    // EXCLUDED: notif_absensi_grup, notif_pembayaran (Gunakan WA Utama)
    $applyRotation = in_array($jenis, [
        'notif_absensi',
        // 'notif_absensi_grup', // Excluded by user request
        'test_grup',
        'pesan_bulk',
        'pesan_admin'
    ]);

    echo "Processing ID {$id} [{$jenis}] -> {$noWa}... ";

    // Mark as processing
    $queueModel->markAsProcessing($id);

    try {
        // Buat instance Fonnte baru untuk setiap pesan
        $sendSuccess = false;
        $maxRetries = 3; // Maksimal percobaan dengan akun berbeda
        $triedAccounts = []; // Track akun yang sudah dicoba
        $lastError = '';

        // Flag untuk fallback ke default
        $useDefaultGateway = true;

        // Jika masuk kategori rotasi dan fitur rotasi aktif
        if ($applyRotation && $rotationEnabled) {
            $accountCheck = $waAccountModel->pickAccount($rotationMode, $lastAccountId);

            // Jika ada akun tersedia, gunakan mode Multi WA
            if ($accountCheck) {
                $useDefaultGateway = false;

                // Loop untuk mencoba dengan akun berbeda jika gagal
                for ($retry = 0; $retry < $maxRetries && !$sendSuccess; $retry++) {
                    $account = ($retry == 0) ? $accountCheck : $waAccountModel->pickAccount($rotationMode, $lastAccountId);

                    // Skip akun yang sudah dicoba
                    while ($account && in_array($account['id'], $triedAccounts)) {
                        $lastAccountId = $account['id'];
                        $account = $waAccountModel->pickAccount($rotationMode, $lastAccountId);
                        // If we've tried all available accounts, break to prevent infinite loop
                        if (count($triedAccounts) >= $waAccountModel->countActiveAccounts()) {
                            $account = null; // Force break from this inner loop and outer for loop
                            break;
                        }
                    }

                    if ($account) {
                        $usedAccountId = $account['id'];
                        $usedAccountName = $account['nama'];
                        $triedAccounts[] = $account['id'];

                        echo "[Multi:{$usedAccountName}] ";

                        // Send message
                        $fonnteMulti = new Fonnte(); // New instance for each account attempt
                        $fonnteMulti->configureFromAccount($account);
                        $response = $fonnteMulti->send($noWa, $pesan);

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
                        // No more active accounts to try in this retry loop
                        echo "[No more active accounts to try] ";
                        break;
                    }
                }
            } else {
                echo "[No Active Account in Rotation] ";
                // $useDefaultGateway remains true -> Fallback to default
            }
        }

        // Jalankan Default Gateway jika tidak pakai rotasi ATAU fallback
        if ($useDefaultGateway && !$sendSuccess) {
            // Notifikasi lainnya pakai gateway default dari pengaturan_aplikasi
            echo "[Default Fallback] ";

            // Buat instance Fonnte baru untuk default gateway
            $fonnteDefault = new Fonnte();
            $response = $fonnteDefault->send($noWa, $pesan);

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

    // Update last used account ID untuk round robin (hanya jika rotation applied & berhasil)
    if ($applyRotation && $rotationEnabled && $usedAccountId) {
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
