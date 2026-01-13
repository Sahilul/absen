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
 */

// Security: Cek token atau CLI mode
// Load config
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/core/Fonnte.php';
require_once __DIR__ . '/../app/models/WaQueue_model.php';
require_once __DIR__ . '/../app/models/PengaturanAplikasi_model.php';

// Initialize
$queueModel = new WaQueue_model();
$pengaturanModel = new PengaturanAplikasi_model();
$fonnte = new Fonnte();

// Get settings from DB
$pengaturan = $pengaturanModel->getPengaturan();
$secretToken = $pengaturan['cron_secret'] ?? 'wa_queue_secret_2026';

// Security: Cek token atau CLI mode
$isCliMode = (php_sapi_name() === 'cli');

if (!$isCliMode) {
    // Web mode - cek token
    $providedToken = $_GET['token'] ?? '';
    // Allow both the new DB token AND the old hardcoded fallback for transition
    if ($providedToken !== $secretToken && $providedToken !== 'wa_queue_secret_2026') {
        http_response_code(403);
        die('Forbidden');
    }
}

// Settings
$maxMessagesPerRun = 5;      // Maksimal pesan per eksekusi
$delayBetweenMessages = 10;  // Detik jeda antar pesan

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

foreach ($pendingMessages as $msg) {
    $id = $msg['id'];
    $noWa = $msg['no_wa'];
    $pesan = $msg['pesan'];

    echo "Processing ID {$id} -> {$noWa}... ";

    // Mark as processing
    $queueModel->markAsProcessing($id);

    try {
        // Send message
        $response = $fonnte->send($noWa, $pesan);

        if (isset($response['status']) && $response['status'] === true) {
            $queueModel->markAsSent($id, json_encode($response));
            echo "SENT\n";
            $results[] = ['id' => $id, 'status' => 'sent'];
        } else {
            $errorMsg = $response['reason'] ?? $response['message'] ?? 'Unknown error';
            $queueModel->markAsFailed($id, $errorMsg);
            echo "FAILED: {$errorMsg}\n";
            $results[] = ['id' => $id, 'status' => 'failed', 'error' => $errorMsg];
        }
    } catch (Exception $e) {
        $queueModel->markAsFailed($id, $e->getMessage());
        echo "ERROR: " . $e->getMessage() . "\n";
        $results[] = ['id' => $id, 'status' => 'error', 'error' => $e->getMessage()];
    }

    $processed++;

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
        'results' => $results
    ]);
}
