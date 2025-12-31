<?php
/**
 * Debug Script - Test Kirim Notifikasi WA Absensi
 * Akses via: http://localhost/absen/debug_wa_test.php
 */

// Load konfigurasi
require_once __DIR__ . '/config/database.php';
define('APPROOT', __DIR__);

// Load Fonnte
require_once __DIR__ . '/app/core/Fonnte.php';

echo "<h1>Debug WhatsApp Notification Test</h1>";
echo "<pre>";

// Test data
$noWa = '085853704788';
$namaOrtu = 'Orang Tua Test';
$namaSiswa = 'Albab';
$kelas = 'Kelas Test';
$status = 'A'; // Alpha
$tanggal = date('d F Y');
$mataPelajaran = 'Matematika';
$namaSekolah = 'MI Test';

echo "=== KONFIGURASI ===\n";
echo "Target: $noWa\n";
echo "Status: $status\n\n";

// Load Fonnte dan cek konfigurasi
try {
    $fonnte = new Fonnte();

    echo "=== MENGIRIM NOTIFIKASI ===\n";
    $result = $fonnte->sendNotifikasiAbsensi(
        $noWa,
        $namaOrtu,
        $namaSiswa,
        $kelas,
        $status,
        $tanggal,
        $mataPelajaran,
        $namaSekolah
    );

    echo "=== HASIL ===\n";
    print_r($result);

    if (isset($result['status']) && $result['status'] === true) {
        echo "\n✅ SUKSES! Pesan terkirim.\n";
    } else {
        echo "\n❌ GAGAL! " . ($result['reason'] ?? $result['message'] ?? 'Unknown error') . "\n";
    }

} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}

echo "</pre>";
echo "<p><a href='/absen/'>Kembali ke Aplikasi</a></p>";
