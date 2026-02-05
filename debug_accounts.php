<?php
// debug_accounts.php
require_once 'app/config/config.php';
require_once 'app/core/Database.php';

$db = new Database();

echo "=== WA ACCOUNTS DEBUG ===\n";
$db->query("SELECT * FROM wa_accounts");
$accounts = $db->resultSet();

if (empty($accounts)) {
    echo "❌ No accounts found in 'wa_accounts' table!\n";
} else {
    foreach ($accounts as $acc) {
        echo "ID: " . $acc['id'] . "\n";
        echo "Name: " . $acc['nama'] . "\n";
        echo "Active: " . ($acc['is_active'] ? '✅ YES' : '❌ NO') . "\n";
        echo "Today Sent: " . $acc['today_sent'] . "\n";
        echo "Daily Limit: " . ($acc['daily_limit'] == 0 ? 'Unlimited' : $acc['daily_limit']) . "\n";

        $isUsable = $acc['is_active'] == 1 && ($acc['daily_limit'] == 0 || $acc['today_sent'] < $acc['daily_limit']);
        echo "Status: " . ($isUsable ? '🟢 READY TO USE' : '🔴 UNAVAILABLE') . "\n";
        echo "------------------------\n";
    }
}

echo "\n=== ROTATION SETTINGS ===\n";
$db->query("SELECT * FROM pengaturan_aplikasi LIMIT 1");
$settings = $db->single();
echo "Rotation Enabled: " . ($settings['wa_rotation_enabled'] ? 'YES' : 'NO') . "\n";
echo "Rotation Mode: " . $settings['wa_rotation_mode'] . "\n";
