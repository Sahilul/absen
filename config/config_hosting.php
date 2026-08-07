<?php

/**
 * Smart Absensi Configuration - HOSTING VERSION
 * 
 * File ini khusus untuk production hosting.
 * Copy file ini ke hosting dan rename menjadi config.php
 */

// Auto-detect BASE URL (untuk development dan production)
function getBaseUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    
    // Untuk shared hosting, auto-detect folder
    $scriptName = dirname($_SERVER['SCRIPT_NAME']);
    $scriptName = str_replace('\\', '/', $scriptName); // Windows compatibility
    $scriptName = rtrim($scriptName, '/');
    
    // Remove /public jika ada
    $scriptName = str_replace('/public', '', $scriptName);
    
    return $protocol . '://' . $host . $scriptName;
}

// Set BASE URL
// HOSTING: Hardcode URL production Anda di sini untuk performa lebih baik
// define('BASEURL', 'https://sekolah-anda.com');
// define('BASEURL', 'https://sekolah-anda.com/absen');

// Atau gunakan auto-detect:
define('BASEURL', getBaseUrl());

// Secret key for QR token generation and security
// WAJIB GANTI untuk production!
define('SECRET_KEY', 'absen_qr_secret_key_2024_change_in_production');

// QR feature flag (set true to enable QR in PDFs)
define('QR_ENABLED', true);

// =============================================================================
// GOOGLE OAUTH 2.0 CONFIGURATION
// =============================================================================
// HOSTING: Update Google Cloud Console dengan redirect URI production Anda
// Contoh: https://sekolah-anda.com/auth/googleCallback
// =============================================================================

define('GOOGLE_OAUTH_ENABLED', true); // Set false untuk disable login Google
define('GOOGLE_CLIENT_ID', '63579907962-r14anpfr1fdi87eqgp2om3e5l9scal07.apps.googleusercontent.com');
define('GOOGLE_CLIENT_SECRET', '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');
define('GOOGLE_REDIRECT_URI', BASEURL . '/auth/googleCallback');

// Domain yang diizinkan untuk Google OAuth (optional, kosongkan untuk allow all)
// Contoh: 'sabilillah.id' akan hanya allow email @sabilillah.id
define('GOOGLE_ALLOWED_DOMAIN', 'sabilillah.id');

// Menu visibility settings (can be changed via Admin > Pengaturan Menu)
define('MENU_INPUT_NILAI_ENABLED', true);
define('MENU_PEMBAYARAN_ENABLED', true);
define('MENU_RAPOR_ENABLED', true);

// =============================================================================
// PENGATURAN APLIKASI HELPER
// =============================================================================
/**
 * Get pengaturan aplikasi dari database
 * Cache di session untuk performa
 */
function getPengaturanAplikasi() {
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
function cekBlokirAksesRPP($fitur = 'all') {
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
            if ($cached === false) return false;
            
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
function cekFiturDiblokir($blokirInfo, $fitur) {
    if ($blokirInfo === false) return false;
    
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
function getStatistikRPPGuru() {
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
function getPengaturanWajibRPP() {
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

require_once 'database.php';
