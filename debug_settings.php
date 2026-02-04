<?php
// debug_settings.php
require_once 'app/config/config.php';
require_once 'app/core/Database.php';

$db = new Database();

echo "Checking 'wa_queue_enabled' in pengaturan_sistem...\n";
$db->query("SELECT * FROM pengaturan_sistem WHERE key_name = 'wa_queue_enabled'");
$results = $db->resultSet();

if (count($results) > 1) {
    echo "⚠️ DUPLICATE ROWS FOUND! (" . count($results) . " rows)\n";
    foreach ($results as $row) {
        echo "ID: " . ($row['id'] ?? 'unknown') . " | Key: " . $row['key_name'] . " | Value: " . $row['value'] . "\n";
    }
} else if (count($results) == 1) {
    echo "✅ Single row found.\n";
    print_r($results[0]);
} else {
    echo "❌ No rows found.\n";
}
