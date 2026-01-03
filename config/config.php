<?php

/**
 * Smart Absensi Configuration
 * 
 * IMPORTANT: Sesuaikan BASE URL dengan environment Anda
 */

// Set timezone to Asia/Jakarta (WIB)
date_default_timezone_set('Asia/Jakarta');

// ============================================================================
// KONFIGURASI BASE URL - OTOMATIS DETECT LOCALHOST VS PRODUCTION
// ============================================================================
// 
// CARA KERJA:
// - Localhost/127.0.0.1  --> http://localhost/absen
// - Production (hosting) --> https://domain-anda.com
// 
// UNTUK HOSTING: Tidak perlu ubah apapun! Auto-detect akan pakai HTTPS.
// ============================================================================

function getBaseUrl()
{
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

    // ===== AUTO-DETECT PROTOCOL =====
    // Localhost = HTTP, Production = HTTPS (PAKSA!)
    if ($host === 'localhost' || $host === '127.0.0.1' || strpos($host, 'localhost:') === 0) {
        $protocol = 'http';
    } else {
        // Production selalu HTTPS (ignore $_SERVER['HTTPS'] karena kadang salah di proxy/cloudflare)
        $protocol = 'https';
    }

    // Path dari request saat ini
    $requestPath = $_SERVER['SCRIPT_NAME'] ?? '/';
    $requestPath = str_replace('\\', '/', $requestPath); // Windows compatibility

    // Ambil folder base aplikasi
    $baseFolder = basename(str_replace('\\', '/', realpath(__DIR__ . '/..')));

    // Normalisasi path: buang /public
    $dirPath = dirname($requestPath);
    $dirPath = rtrim(str_replace('/public', '', $dirPath), '/');

    // Jika localhost dan ada baseFolder (misal: /absen), tambahkan
    if ($baseFolder && stripos($dirPath, '/' . $baseFolder) === false) {
        $dirPath = '/' . trim($baseFolder, '/');
    }

    // Localhost minimal harus ada baseFolder
    if ($host === 'localhost' && empty($dirPath)) {
        $dirPath = '/' . trim($baseFolder ?: '', '/');
    }

    return $protocol . '://' . $host . $dirPath;
}

// ===== SET BASE URL =====
define('BASEURL', getBaseUrl());

function getSystemSetting($key, $default = null)
{
    static $settings = null;

    if ($settings === null) {
        // Check if database config is loaded
        if (!defined('DB_HOST')) {
            // Load database.php first
            require_once __DIR__ . '/database.php';
        }

        try {
            if (!class_exists('Database')) {
                require_once APPROOT . '/app/core/Database.php';
            }
            $db = new Database();
            $db->query("SELECT key_name, value FROM pengaturan_sistem");
            $results = $db->resultSet();
            $settings = [];
            foreach ($results as $row) {
                $settings[$row['key_name']] = $row['value'];
            }
        } catch (Exception $e) {
            $settings = [];
        }
    }

    return $settings[$key] ?? $default;
}

define('SECRET_KEY', getSystemSetting('secret_key', 'absen_qr_secret_key_2024'));
define('QR_ENABLED', getSystemSetting('qr_enabled', '1') == '1');

define('GOOGLE_OAUTH_ENABLED', getSystemSetting('google_oauth_enabled', '0') == '1');
define('GOOGLE_CLIENT_ID', getSystemSetting('google_client_id', ''));
define('GOOGLE_CLIENT_SECRET', getSystemSetting('google_client_secret', ''));
define('GOOGLE_REDIRECT_URI', BASEURL . '/auth/googleCallback');
define('GOOGLE_ALLOWED_DOMAIN', getSystemSetting('google_allowed_domain', ''));

define('MENU_INPUT_NILAI_ENABLED', getSystemSetting('menu_input_nilai_enabled', '1') == '1');
define('MENU_PEMBAYARAN_ENABLED', getSystemSetting('menu_pembayaran_enabled', '1') == '1');
define('MENU_RAPOR_ENABLED', getSystemSetting('menu_rapor_enabled', '1') == '1');

// =============================================================================
// PENGATURAN APLIKASI HELPER
// =============================================================================
/**
 * Get pengaturan aplikasi dari database
 * Cache di session untuk performa
 */
function getPengaturanAplikasi()
{
    // Default values
    $default = [
        'id' => 1,
        'nama_aplikasi' => 'Smart Absensi',
        'logo' => ''
    ];

    // Cache di session agar tidak query terus menerus
    if (isset($_SESSION['pengaturan_aplikasi']) && isset($_SESSION['pengaturan_aplikasi_time'])) {
        // Refresh cache setiap 5 menit
        if (time() - $_SESSION['pengaturan_aplikasi_time'] < 300) {
            return $_SESSION['pengaturan_aplikasi'];
        }
    }

    // Query database
    try {
        $db = new Database();

        // Cek apakah tabel ada, jika tidak buat
        $db->query("CREATE TABLE IF NOT EXISTS `pengaturan_aplikasi` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `nama_aplikasi` varchar(255) NOT NULL DEFAULT 'Smart Absensi',
            `logo` varchar(255) DEFAULT NULL,
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $db->execute();

        $db->query("SELECT * FROM pengaturan_aplikasi WHERE id = 1");
        $result = $db->single();

        if (!$result) {
            // Insert default
            $db->query("INSERT INTO pengaturan_aplikasi (id, nama_aplikasi, logo) 
                        VALUES (1, 'Smart Absensi', '')");
            $db->execute();
            $result = $default;
        }

        // Cache di session
        $_SESSION['pengaturan_aplikasi'] = $result;
        $_SESSION['pengaturan_aplikasi_time'] = time();

        return $result;
    } catch (Exception $e) {
        // Fallback jika error
        return $default;
    }
}

// =============================================================================
// CEK BLOKIR AKSES RPP HELPER
// =============================================================================
/**
 * Cek apakah guru diblokir aksesnya karena belum punya RPP disetujui
 * @param string $fitur - 'absensi', 'jurnal', 'nilai', atau 'all'
 * @return array|false - false jika tidak diblokir, array jika diblokir
 */
function cekBlokirAksesRPP($fitur = 'all')
{
    // Hanya berlaku untuk role guru atau wali_kelas
    $role = $_SESSION['role'] ?? '';
    if (!in_array($role, ['guru', 'wali_kelas'])) {
        return false;
    }

    $id_guru = $_SESSION['id_ref'] ?? null;
    $id_tp = $_SESSION['id_tp_aktif'] ?? null;
    $id_semester = $_SESSION['id_semester_aktif'] ?? null;

    if (!$id_guru || !$id_tp || !$id_semester) {
        return false;
    }

    // Cache di session
    $cacheKey = "blokir_akses_rpp_{$id_guru}_{$id_tp}_{$id_semester}";
    if (isset($_SESSION[$cacheKey]) && isset($_SESSION[$cacheKey . '_time'])) {
        if (time() - $_SESSION[$cacheKey . '_time'] < 60) { // Cache 1 menit
            $cached = $_SESSION[$cacheKey];
            if ($cached === false)
                return false;

            // Cek berdasarkan fitur
            return cekFiturDiblokir($cached, $fitur);
        }
    }

    try {
        $db = new Database();

        // Get pengaturan RPP
        $db->query("SELECT * FROM pengaturan_rpp WHERE id = 1");
        $pengaturan = $db->single();

        if (!$pengaturan || empty($pengaturan['wajib_rpp_disetujui'])) {
            $_SESSION[$cacheKey] = false;
            $_SESSION[$cacheKey . '_time'] = time();
            return false;
        }

        // Cek apakah guru punya RPP disetujui
        $db->query("SELECT COUNT(*) as total FROM rpp 
            WHERE id_guru = :id_guru 
            AND id_tp = :id_tp 
            AND id_semester = :id_semester 
            AND status = 'approved'");
        $db->bind(':id_guru', $id_guru);
        $db->bind(':id_tp', $id_tp);
        $db->bind(':id_semester', $id_semester);
        $result = $db->single();

        if (($result['total'] ?? 0) > 0) {
            $_SESSION[$cacheKey] = false;
            $_SESSION[$cacheKey . '_time'] = time();
            return false;
        }

        // Guru belum punya RPP disetujui - cache info blokir
        $blokirInfo = [
            'diblokir' => true,
            'pesan' => $pengaturan['pesan_blokir'] ?? 'RPP belum disetujui',
            'blokir_absensi' => !empty($pengaturan['blokir_absensi']),
            'blokir_jurnal' => !empty($pengaturan['blokir_jurnal']),
            'blokir_nilai' => !empty($pengaturan['blokir_nilai'])
        ];

        $_SESSION[$cacheKey] = $blokirInfo;
        $_SESSION[$cacheKey . '_time'] = time();

        return cekFiturDiblokir($blokirInfo, $fitur);

    } catch (Exception $e) {
        return false;
    }
}

/**
 * Helper untuk cek fitur spesifik diblokir
 */
function cekFiturDiblokir($blokirInfo, $fitur)
{
    if ($blokirInfo === false)
        return false;

    switch ($fitur) {
        case 'absensi':
            return $blokirInfo['blokir_absensi'] ? $blokirInfo : false;
        case 'jurnal':
            return $blokirInfo['blokir_jurnal'] ? $blokirInfo : false;
        case 'nilai':
            return $blokirInfo['blokir_nilai'] ? $blokirInfo : false;
        case 'all':
        default:
            return $blokirInfo;
    }
}

/**
 * Get statistik RPP untuk guru
 */
function getStatistikRPPGuru()
{
    $id_guru = $_SESSION['id_ref'] ?? null;
    $id_tp = $_SESSION['id_tp_aktif'] ?? null;
    $id_semester = $_SESSION['id_semester_aktif'] ?? null;

    if (!$id_guru || !$id_tp || !$id_semester) {
        return ['total_rpp' => 0, 'draft' => 0, 'submitted' => 0, 'approved' => 0, 'revision' => 0];
    }

    try {
        $db = new Database();
        $db->query("SELECT 
            COUNT(*) as total_rpp,
            SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as draft,
            SUM(CASE WHEN status = 'submitted' THEN 1 ELSE 0 END) as submitted,
            SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
            SUM(CASE WHEN status = 'revision' THEN 1 ELSE 0 END) as revision
            FROM rpp 
            WHERE id_guru = :id_guru 
            AND id_tp = :id_tp 
            AND id_semester = :id_semester");
        $db->bind(':id_guru', $id_guru);
        $db->bind(':id_tp', $id_tp);
        $db->bind(':id_semester', $id_semester);
        return $db->single() ?: ['total_rpp' => 0, 'draft' => 0, 'submitted' => 0, 'approved' => 0, 'revision' => 0];
    } catch (Exception $e) {
        return ['total_rpp' => 0, 'draft' => 0, 'submitted' => 0, 'approved' => 0, 'revision' => 0];
    }
}

/**
 * Get pengaturan wajib RPP dari admin
 * Digunakan untuk menyembunyikan menu di dashboard jika RPP belum approved
 * @return array - pengaturan wajib RPP
 */
function getPengaturanWajibRPP()
{
    // Cache di session untuk efisiensi (30 detik saja agar perubahan admin cepat terlihat)
    $cacheKey = 'pengaturan_wajib_rpp';
    if (isset($_SESSION[$cacheKey]) && isset($_SESSION[$cacheKey . '_time'])) {
        if (time() - $_SESSION[$cacheKey . '_time'] < 30) { // Cache 30 detik
            return $_SESSION[$cacheKey];
        }
    }

    try {
        $db = new Database();
        $db->query("SELECT * FROM pengaturan_rpp WHERE id = 1");
        $result = $db->single();

        if (!$result) {
            // Default values
            $result = [
                'wajib_rpp_disetujui' => 0,
                'blokir_absensi' => 0,
                'blokir_jurnal' => 0,
                'blokir_nilai' => 0,
                'pesan_blokir' => ''
            ];
        }

        // Mapping ke nama yang lebih readable
        $pengaturan = [
            'aktif' => !empty($result['wajib_rpp_disetujui']),
            'wajib_rpp_untuk_absen' => !empty($result['wajib_rpp_disetujui']) && !empty($result['blokir_absensi']),
            'wajib_rpp_untuk_jurnal' => !empty($result['wajib_rpp_disetujui']) && !empty($result['blokir_jurnal']),
            'wajib_rpp_untuk_nilai' => !empty($result['wajib_rpp_disetujui']) && !empty($result['blokir_nilai']),
            'pesan_blokir' => $result['pesan_blokir'] ?? ''
        ];

        $_SESSION[$cacheKey] = $pengaturan;
        $_SESSION[$cacheKey . '_time'] = time();

        return $pengaturan;
    } catch (Exception $e) {
        return [
            'aktif' => false,
            'wajib_rpp_untuk_absen' => false,
            'wajib_rpp_untuk_jurnal' => false,
            'wajib_rpp_untuk_nilai' => false,
            'pesan_blokir' => ''
        ];
    }
}

// =============================================================================
// PENGATURAN LISENSI (DILINDUNGI IONCUBE)
// =============================================================================
// PENTING: File config.php HARUS dienkripsi dengan IonCube!
// Jika tidak, client bisa menonaktifkan pengecekan lisensi.
// =============================================================================

// Aktifkan pengecekan lisensi (WAJIB true untuk production)
define('LICENSE_ENABLED', true);

// URL License Server API
define('LICENSE_SERVER_URL', 'https://license.sabilillah.id/api/check');

// Bypass localhost untuk development (set false untuk production ketat)
define('LICENSE_BYPASS_LOCALHOST', true);

// Cache duration dalam detik (24 jam = 86400)
define('LICENSE_CACHE_TTL', 86400);

/**
 * Verifikasi Lisensi
 * @return bool
 */
function verifyLicense(): bool
{
    if (!LICENSE_ENABLED) {
        return true;
    }

    $domain = getLicenseDomain();

    if (LICENSE_BYPASS_LOCALHOST && isLicenseLocalhost($domain)) {
        return true;
    }

    $cached = getLicenseCache();
    if ($cached !== null) {
        return $cached;
    }

    $isValid = callLicenseApi($domain);
    saveLicenseCache($isValid, $domain);

    return $isValid;
}

function getLicenseDomain(): string
{
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return strtolower(preg_replace('#:\d+$#', '', $host));
}

function isLicenseLocalhost(string $domain): bool
{
    return in_array($domain, ['localhost', '127.0.0.1', '::1']) || strpos($domain, 'localhost') !== false;
}

function getLicenseCache(): ?bool
{
    $cacheFile = APPROOT . '/tmp/license_cache.json';
    if (!file_exists($cacheFile))
        return null;

    $data = json_decode(file_get_contents($cacheFile), true);
    if (!$data || !isset($data['valid'], $data['time']))
        return null;
    if (time() - $data['time'] > LICENSE_CACHE_TTL)
        return null;

    return (bool) $data['valid'];
}

function saveLicenseCache(bool $isValid, string $domain): void
{
    $dir = APPROOT . '/tmp';
    if (!is_dir($dir))
        mkdir($dir, 0755, true);

    file_put_contents($dir . '/license_cache.json', json_encode([
        'valid' => $isValid,
        'time' => time(),
        'domain' => $domain
    ]));
}

function callLicenseApi(string $domain): bool
{
    $url = LICENSE_SERVER_URL . '?domain=' . urlencode($domain);

    if (function_exists('curl_init')) {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => false
        ]);
        $response = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code === 200 && $response) {
            $data = json_decode($response, true);
            return isset($data['valid']) && $data['valid'] === true;
        }
    }

    $ctx = stream_context_create(['http' => ['timeout' => 10], 'ssl' => ['verify_peer' => false]]);
    $response = @file_get_contents($url, false, $ctx);
    if ($response) {
        $data = json_decode($response, true);
        return isset($data['valid']) && $data['valid'] === true;
    }

    return getLicenseCache() ?? false;
}

function showLicenseBlockedPage(): void
{
    $domain = getLicenseDomain();
    echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Lisensi Tidak Valid</title><script src="https://cdn.tailwindcss.com"></script></head>';
    echo '<body class="bg-gradient-to-br from-red-900 to-red-800 min-h-screen flex items-center justify-center p-4"><div class="max-w-lg w-full"><div class="bg-white rounded-2xl shadow-2xl p-8 text-center">';
    echo '<div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6"><svg class="w-10 h-10 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg></div>';
    echo '<h1 class="text-2xl font-bold text-gray-800 mb-2">Lisensi Tidak Valid</h1>';
    echo '<p class="text-gray-600 mb-6">Domain <strong>' . htmlspecialchars($domain) . '</strong> tidak terdaftar.</p>';
    echo '<div class="bg-gray-50 rounded-lg p-4 mb-6"><p class="text-sm text-gray-500">Hubungi administrator untuk mendaftarkan domain Anda.</p></div>';
    echo '<button onclick="location.reload()" class="bg-gray-200 hover:bg-gray-300 text-gray-700 py-3 px-6 rounded-lg font-medium">Coba Lagi</button>';
    echo '</div></div></body></html>';
    exit;
}

// =============================================================================
// END LICENSE
// =============================================================================

require_once 'database.php';

