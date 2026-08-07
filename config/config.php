<?php

date_default_timezone_set('Asia/Jakarta');

function getBaseUrl()
{
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scriptName = dirname($_SERVER['SCRIPT_NAME']);
    $scriptName = str_replace('\\', '/', $scriptName);
    $scriptName = rtrim($scriptName, '/');
    $scriptName = str_replace('/public', '', $scriptName);
    return $protocol . '://' . $host . $scriptName;
}

define('BASEURL', getBaseUrl());

function getSystemSetting($key, $default = null)
{
    static $settings = null;

    if ($settings === null) {
        if (!defined('DB_HOST')) {
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

function getPengaturanAplikasi()
{

    $default = [
        'id' => 1,
        'nama_aplikasi' => 'Smart Absensi',
        'logo' => ''
    ];

    if (isset($_SESSION['pengaturan_aplikasi']) && isset($_SESSION['pengaturan_aplikasi_time'])) {
        if (time() - $_SESSION['pengaturan_aplikasi_time'] < 300) {
            return $_SESSION['pengaturan_aplikasi'];
        }
    }


    try {
        $db = new Database();


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

            $db->query("INSERT INTO pengaturan_aplikasi (id, nama_aplikasi, logo) 
                        VALUES (1, 'Smart Absensi', '')");
            $db->execute();
            $result = $default;
        }


        $_SESSION['pengaturan_aplikasi'] = $result;
        $_SESSION['pengaturan_aplikasi_time'] = time();

        return $result;
    } catch (Exception $e) {

        return $default;
    }
}

function cekBlokirAksesRPP($fitur = 'all')
{
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

    $cacheKey = "blokir_akses_rpp_{$id_guru}_{$id_tp}_{$id_semester}";
    if (isset($_SESSION[$cacheKey]) && isset($_SESSION[$cacheKey . '_time'])) {
        if (time() - $_SESSION[$cacheKey . '_time'] < 60) { // Cache 1 menit
            $cached = $_SESSION[$cacheKey];
            if ($cached === false)
                return false;

            return cekFiturDiblokir($cached, $fitur);
        }
    }

    try {
        $db = new Database();

        $db->query("SELECT * FROM pengaturan_rpp WHERE id = 1");
        $pengaturan = $db->single();

        if (!$pengaturan || empty($pengaturan['wajib_rpp_disetujui'])) {
            $_SESSION[$cacheKey] = false;
            $_SESSION[$cacheKey . '_time'] = time();
            return false;
        }

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

function getPengaturanWajibRPP()
{
    $cacheKey = 'pengaturan_wajib_rpp';
    if (isset($_SESSION[$cacheKey]) && isset($_SESSION[$cacheKey . '_time'])) {
        if (time() - $_SESSION[$cacheKey . '_time'] < 30) {
            return $_SESSION[$cacheKey];
        }
    }

    try {
        $db = new Database();
        $db->query("SELECT * FROM pengaturan_rpp WHERE id = 1");
        $result = $db->single();

        if (!$result) {
            $result = [
                'wajib_rpp_disetujui' => 0,
                'blokir_absensi' => 0,
                'blokir_jurnal' => 0,
                'blokir_nilai' => 0,
                'pesan_blokir' => ''
            ];
        }

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

define('LICENSE_ENABLED', true);
define('LICENSE_SERVER_URL', 'https://lisensi.sahil.my.id/api/check');
define('LICENSE_BYPASS_LOCALHOST', false);
define('LICENSE_CACHE_TTL', 86400);

function verifyLicense(): bool
{
    if (!LICENSE_ENABLED)
        return true;

    $domain = getLicenseDomain();

    if (LICENSE_BYPASS_LOCALHOST && isLicenseLocalhost($domain))
        return true;

    $url = LICENSE_SERVER_URL . '?domain=' . urlencode($domain);
    $response = @file_get_contents($url);

    if ($response !== false) {
        $data = @json_decode($response, true);
        if (is_array($data) && array_key_exists('valid', $data)) {
            return $data['valid'] === true;
        }
    }

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $response = curl_exec($ch);
        curl_close($ch);

        if ($response) {
            $data = @json_decode($response, true);
            if (is_array($data) && array_key_exists('valid', $data)) {
                return $data['valid'] === true;
            }
        }
    }

    $cacheFile = APPROOT . '/tmp/license_cache.json';
    if (file_exists($cacheFile)) {
        $cache = @json_decode(file_get_contents($cacheFile), true);
        if (isset($cache['valid']) && isset($cache['time'])) {
            if (time() - $cache['time'] < LICENSE_CACHE_TTL) {
                return (bool) $cache['valid'];
            }
        }
    }

    return false;
}

function getLicenseDomain(): string
{
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $host = strtolower($host);
    $host = preg_replace('#:\d+$#', '', $host);
    return $host;
}

function isLicenseLocalhost(string $domain): bool
{
    $locals = ['localhost', '127.0.0.1', '::1'];
    return in_array($domain, $locals) || strpos($domain, 'localhost') !== false;
}

function showLicenseBlockedPage(): void
{
    $domain = getLicenseDomain();
    http_response_code(403);
    ?>
    <!DOCTYPE html>
    <html lang="id">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Lisensi Tidak Valid</title>
        <script src="https://cdn.tailwindcss.com"></script>
    </head>

    <body class="bg-gradient-to-br from-red-900 to-red-800 min-h-screen flex items-center justify-center p-4">
        <div class="max-w-lg w-full">
            <div class="bg-white rounded-2xl shadow-2xl p-8 text-center">
                <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-10 h-10 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                        </path>
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-gray-800 mb-2">Lisensi Tidak Valid</h1>
                <p class="text-gray-600 mb-6">Domain <strong><?= htmlspecialchars($domain) ?></strong> tidak terdaftar.</p>
                <div class="flex flex-col gap-3">
                    <a href="https://wa.me/6285853704788?text=Halo,%20saya%20ingin%20mendaftarkan%20domain%20<?= urlencode($domain) ?>"
                        target="_blank"
                        class="bg-green-500 hover:bg-green-600 text-white py-3 px-6 rounded-lg font-medium flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />
                        </svg>
                        Hubungi via WhatsApp
                    </a>
                    <button onclick="location.reload()"
                        class="bg-gray-200 hover:bg-gray-300 text-gray-700 py-3 px-6 rounded-lg font-medium">
                        Coba Lagi
                    </button>
                </div>
            </div>
        </div>
    </body>

    </html>
    <?php
    exit;
}

require_once 'database.php';

