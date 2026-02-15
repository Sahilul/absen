<?php
/**
 * File: app/core/Fonnte.php
 * Helper class untuk integrasi dengan berbagai WA Gateway
 * Supports: Fonnte, Go-WhatsApp-Web-Multidevice, Wablas, dll.
 */
class Fonnte
{
    private $apiUrl = 'https://api.fonnte.com/send';
    private $token;
    private $provider = 'fonnte'; // fonnte, gowa, wablas, dripsender
    private $username = '';
    private $password = '';
    private $accountId = null; // ID akun dari wa_accounts
    private $groupAbsensiTemplate = null; // Template pesan grup absensi

    // Daftar provider populer di Indonesia
    public static $providers = [
        'fonnte' => [
            'name' => 'Fonnte',
            'url' => 'https://api.fonnte.com/send',
            'auth_type' => 'token',
            'website' => 'https://fonnte.com'
        ],
        'gowa' => [
            'name' => 'Go-WhatsApp (Self-Hosted)',
            'url' => 'http://localhost:3000/send/message',
            'auth_type' => 'basic',
            'website' => 'https://github.com/aldinokemal/go-whatsapp-web-multidevice'
        ],
        'wablas' => [
            'name' => 'Wablas',
            'url' => 'https://solo.wablas.com/api/send-message',
            'auth_type' => 'token',
            'website' => 'https://wablas.com'
        ],
        'dripsender' => [
            'name' => 'Dripsender',
            'url' => 'https://api.dripsender.id/send',
            'auth_type' => 'token',
            'website' => 'https://dripsender.id'
        ],
        'starsender' => [
            'name' => 'Starsender',
            'url' => 'https://api.starsender.online/api/send',
            'auth_type' => 'token',
            'website' => 'https://starsender.online'
        ],
        'onesender' => [
            'name' => 'OneSender / CloudWA',
            'url' => 'https://wa3407.cloudwa.my.id/api/v1/messages',
            'auth_type' => 'token',
            'website' => 'https://onesender.id'
        ],
    ];

    public function __construct($token = null)
    {
        if ($token) {
            $this->token = $token;
        } else {
            $this->loadSettingsFromDatabase();
        }
    }

    /**
     * Konfigurasi dari data akun wa_accounts
     * @param array $account Data akun dari wa_accounts table
     */
    public function configureFromAccount($account)
    {
        $this->accountId = $account['id'];
        $this->provider = $account['provider'] ?? 'fonnte';
        $this->apiUrl = $account['api_url'] ?? (self::$providers[$this->provider]['url'] ?? 'https://api.fonnte.com/send');
        $this->token = $account['token'] ?? '';
        $this->username = $account['username'] ?? '';
        $this->password = $account['password'] ?? '';
    }

    /**
     * Get current account ID (untuk tracking)
     */
    public function getAccountId()
    {
        return $this->accountId;
    }

    /**
     * Load settings dari database
     */
    private function loadSettingsFromDatabase()
    {
        require_once APPROOT . '/app/core/Database.php';
        $db = new Database();

        // Coba ambil dari psb_pengaturan dulu
        try {
            $db->query('SELECT wa_gateway_provider, wa_gateway_url, wa_gateway_token, wa_gateway_username, wa_gateway_password FROM psb_pengaturan WHERE id = 1');
            $result = $db->single();
            if (!empty($result['wa_gateway_token']) || !empty($result['wa_gateway_username'])) {
                $this->provider = $result['wa_gateway_provider'] ?? 'fonnte';
                $this->apiUrl = $result['wa_gateway_url'] ?? self::$providers[$this->provider]['url'] ?? 'https://api.fonnte.com/send';
                $this->token = $result['wa_gateway_token'] ?? '';
                $this->username = $result['wa_gateway_username'] ?? '';
                $this->password = $result['wa_gateway_password'] ?? '';
                return;
            }
        } catch (Exception $e) {
            // Table mungkin tidak ada, lanjut ke fallback
        }

        // Fallback ke pengaturan_aplikasi
        try {
            $db->query('SELECT wa_gateway_provider, wa_gateway_url, wa_gateway_token, wa_gateway_username, wa_gateway_password, wa_template_group_absensi FROM pengaturan_aplikasi WHERE id = 1');
            $result = $db->single();
            $this->provider = $result['wa_gateway_provider'] ?? 'fonnte';
            $this->apiUrl = $result['wa_gateway_url'] ?? self::$providers[$this->provider]['url'] ?? 'https://api.fonnte.com/send';
            $this->token = $result['wa_gateway_token'] ?? '';
            $this->username = $result['wa_gateway_username'] ?? '';
            $this->password = $result['wa_gateway_password'] ?? '';
            $this->groupAbsensiTemplate = $result['wa_template_group_absensi'] ?? null;
        } catch (Exception $e) {
            $this->token = '';
        }
    }

    /**
     * Kirim pesan WA (auto-detect provider)
     */
    public function send($target, $message)
    {
        $target = $this->formatNumber($target);

        switch ($this->provider) {
            case 'gowa':
                return $this->sendViaGoWa($target, $message);
            case 'wablas':
                return $this->sendViaWablas($target, $message);
            case 'dripsender':
                return $this->sendViaDripsender($target, $message);
            case 'starsender':
                return $this->sendViaStarsender($target, $message);
            case 'onesender':
                return $this->sendViaOneSender($target, $message);
            case 'fonnte':
            default:
                return $this->sendViaFonnte($target, $message);
        }
    }

    /**
     * Kirim via Fonnte
     */
    private function sendViaFonnte($target, $message)
    {
        if (empty($this->token)) {
            return ['status' => false, 'reason' => 'Token WA Gateway tidak dikonfigurasi'];
        }

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $this->apiUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => [
                'target' => $target,
                'message' => $message,
                'countryCode' => '62'
            ],
            CURLOPT_HTTPHEADER => [
                'Authorization: ' . $this->token
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            return ['status' => false, 'reason' => 'cURL Error: ' . $err];
        }

        return json_decode($response, true) ?: ['status' => false, 'reason' => 'Invalid response'];
    }

    /**
     * Kirim via Go-WhatsApp-Web-Multidevice (Basic Auth)
     */
    private function sendViaGoWa($target, $message)
    {
        if (empty($this->username) || empty($this->password)) {
            return ['status' => false, 'reason' => 'Username/Password Go-WA tidak dikonfigurasi'];
        }

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $this->apiUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode([
                'phone' => $target,
                'message' => $message
            ]),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Basic ' . base64_encode($this->username . ':' . $this->password)
            ],
            CURLOPT_SSL_VERIFYPEER => false, // For self-hosted servers
        ]);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            return ['status' => false, 'reason' => 'cURL Error: ' . $err];
        }

        // Debug: Log raw response
        error_log("Go-WA Response (HTTP $httpCode): " . $response);

        $result = json_decode($response, true);

        // Go-WA v8 returns { "code": 200, "message": "...", "results": {...} }
        // Or { "code": "SUCCESS", "message": "...", "results": {...} }
        // Also check for direct success response
        if ($httpCode >= 200 && $httpCode < 300) {
            if (isset($result['code']) && ($result['code'] == 200 || $result['code'] === 'SUCCESS')) {
                return ['status' => true, 'reason' => $result['message'] ?? 'Sent'];
            }
            // Some versions return success directly
            if (isset($result['status']) && $result['status'] === true) {
                return ['status' => true, 'reason' => $result['message'] ?? 'Sent'];
            }
            // If we got 200 but no clear status, assume success
            if ($httpCode == 200 && !empty($response)) {
                return ['status' => true, 'reason' => 'Message sent (HTTP 200)'];
            }
        }

        // Return error with as much detail as possible
        $errorMsg = $result['message'] ?? $result['error'] ?? $result['reason'] ?? "HTTP $httpCode: " . substr($response, 0, 200);
        return ['status' => false, 'reason' => $errorMsg];
    }

    /**
     * Kirim via Wablas
     */
    private function sendViaWablas($target, $message)
    {
        if (empty($this->token)) {
            return ['status' => false, 'reason' => 'Token Wablas tidak dikonfigurasi'];
        }

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $this->apiUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => http_build_query([
                'phone' => $target,
                'message' => $message
            ]),
            CURLOPT_HTTPHEADER => [
                'Authorization: ' . $this->token
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            return ['status' => false, 'reason' => 'cURL Error: ' . $err];
        }

        return json_decode($response, true) ?: ['status' => false, 'reason' => 'Invalid response'];
    }

    /**
     * Kirim via Dripsender
     */
    private function sendViaDripsender($target, $message)
    {
        return $this->sendViaFonnte($target, $message); // Similar API
    }

    /**
     * Kirim via Starsender
     */
    private function sendViaStarsender($target, $message)
    {
        return $this->sendViaFonnte($target, $message); // Similar API
    }

    /**
     * Kirim via OneSender 2.0 / CloudWA
     * Format: https://wa3407.cloudwa.my.id/api/v1/messages
     */
    private function sendViaOneSender($target, $message)
    {
        if (empty($this->token)) {
            return ['status' => false, 'reason' => 'API Key OneSender tidak dikonfigurasi'];
        }

        // Normalize phone number (ensure 62 prefix)
        // Format used in test script that worked: 628...
        $phone = preg_replace('/[^0-9]/', '', $target);
        if (substr($phone, 0, 1) === '0') {
            $phone = '62' . substr($phone, 1);
        }
        if (substr($phone, 0, 2) !== '62') {
            $phone = '62' . $phone;
        }

        // OneSender/CloudWA v1/v2 Payload
        $body = json_encode([
            'recipient_type' => 'individual',
            'to' => $phone,
            'type' => 'text',
            'text' => [
                'body' => $message
            ]
        ]);

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $this->apiUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->token
            ],
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            return ['status' => false, 'reason' => 'cURL Error: ' . $err];
        }

        $result = json_decode($response, true);

        // Debug log
        error_log("[OneSender] HTTP $httpCode Response: " . $response);

        // Success logic matching snippet
        if ($httpCode >= 200 && $httpCode < 300) {
            if (isset($result['success']) && $result['success'] === true) {
                return ['status' => true, 'reason' => 'Sent', 'data' => $result];
            }
            // Some versions return messages array
            if (isset($result['messages']) && !empty($result['messages'])) {
                return ['status' => true, 'reason' => 'Sent', 'data' => $result];
            }
        }

        $errorMsg = $result['message'] ?? $result['error'] ?? "HTTP $httpCode";
        return ['status' => false, 'reason' => $errorMsg];
    }

    /**
     * Format nomor HP ke format internasional
     * Mendukung auto-detect Group ID WhatsApp
     * GOWA: Preserve format asli untuk kompatibilitas
     */
    public function formatNumber($number)
    {
        $number = trim($number);

        // 1. Jika mengandung '@', asumsikan JID valid (Group/Personal), kembalikan as-is
        if (strpos($number, '@') !== false) {
            return $number;
        }

        // 2. Deteksi Legacy Group ID (format: 628xxx-xxxx)
        // GOWA dan provider self-hosted mendukung format ini
        if (preg_match('/^\d+-\d+$/', $number)) {
            // Untuk GOWA, kembalikan dengan @g.us suffix
            return $number . '@g.us';
        }

        // Bersihkan karakter non-numeric untuk nomor HP biasa
        $cleanNumber = preg_replace('/[^0-9]/', '', $number);

        // 3. Deteksi New Group ID (biasanya panjang > 15 digit)
        // Contoh: 120363287671196238 (18 digit)
        if (strlen($cleanNumber) > 15) {
            // Auto-append @g.us jika belum ada
            return $cleanNumber . '@g.us';
        }

        // 4. Format Nomor HP Indonesia (Standard)
        if (substr($cleanNumber, 0, 1) === '0') {
            $cleanNumber = '62' . substr($cleanNumber, 1);
        } elseif (substr($cleanNumber, 0, 2) !== '62' && strlen($cleanNumber) > 0) {
            $cleanNumber = '62' . $cleanNumber;
        }

        return $cleanNumber;
    }

    /**
     * Fetch daftar grup WA dari Fonnte (harus dipanggil sekali setelah join grup baru)
     * Endpoint: POST https://api.fonnte.com/fetch-group
     * @return array Response dari Fonnte
     */
    public function fetchWhatsAppGroups()
    {
        if (empty($this->token)) {
            return ['status' => false, 'reason' => 'Token tidak dikonfigurasi'];
        }

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://api.fonnte.com/fetch-group',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_HTTPHEADER => [
                'Authorization: ' . $this->token
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            return ['status' => false, 'reason' => 'cURL Error: ' . $err];
        }

        $result = json_decode($response, true);
        error_log("[Fonnte fetch-group] Response: " . $response);

        return $result ?: ['status' => false, 'reason' => 'Invalid response'];
    }

    /**
     * Ambil daftar grup WA yang sudah di-fetch dari Fonnte
     * Endpoint: POST https://api.fonnte.com/get-whatsapp-group
     * @return array Response dari Fonnte dengan daftar grup
     */
    public function getWhatsAppGroups()
    {
        if (empty($this->token)) {
            return ['status' => false, 'reason' => 'Token tidak dikonfigurasi'];
        }

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://api.fonnte.com/get-whatsapp-group',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_HTTPHEADER => [
                'Authorization: ' . $this->token
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            return ['status' => false, 'reason' => 'cURL Error: ' . $err];
        }

        $result = json_decode($response, true);
        error_log("[Fonnte get-whatsapp-group] Response: " . $response);

        return $result ?: ['status' => false, 'reason' => 'Invalid response'];
    }

    /**
     * Ambil daftar grup WA dari Go-WhatsApp-Web-Multidevice (GOWA)
     * Endpoint: GET /user/my/groups
     * @return array Response dengan daftar grup
     */
    public function getGowaGroups()
    {
        if (empty($this->username) || empty($this->password)) {
            return ['status' => false, 'reason' => 'Username/Password GOWA tidak dikonfigurasi'];
        }

        // Parse base URL (remove /send/message suffix if present)
        $baseUrl = preg_replace('/\/send\/message$/', '', $this->apiUrl);

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $baseUrl . '/user/my/groups',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Basic ' . base64_encode($this->username . ':' . $this->password)
            ],
            CURLOPT_SSL_VERIFYPEER => false,
        ]);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            return ['status' => false, 'reason' => 'cURL Error: ' . $err];
        }

        error_log("[GOWA get-groups] HTTP $httpCode Response: " . $response);

        $result = json_decode($response, true);

        if ($httpCode >= 200 && $httpCode < 300 && isset($result['results'])) {
            // GOWA returns { "code": 200, "message": "...", "results": { "data": [...] } }
            $groups = $result['results']['data'] ?? $result['results'] ?? [];
            return ['status' => true, 'data' => $groups];
        }

        $errorMsg = $result['message'] ?? $result['error'] ?? "HTTP $httpCode";
        return ['status' => false, 'reason' => $errorMsg];
    }

    /**
     * Kirim pesan WA dengan file attachment (base64)
     * @param string $target Nomor WA (format: 628xxx)
     * @param string $message Pesan yang akan dikirim
     * @param string $fileContent Binary file content (will be base64 encoded)
     * @param string $filename Nama file
     * @return array Response dari Fonnte
     */
    public function sendWithFile($target, $message, $fileContent, $filename = 'document.pdf')
    {
        if (empty($this->token)) {
            return ['status' => false, 'reason' => 'Token WA Gateway tidak dikonfigurasi'];
        }

        $target = $this->formatNumber($target);

        // Encode file to base64
        $fileBase64 = base64_encode($fileContent);

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $this->apiUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 120,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => [
                'target' => $target,
                'message' => $message,
                'file' => $fileBase64,
                'filename' => $filename,
                'countryCode' => '62'
            ],
            CURLOPT_HTTPHEADER => [
                'Authorization: ' . $this->token
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            return ['status' => false, 'reason' => 'cURL Error: ' . $err];
        }

        return json_decode($response, true) ?: ['status' => false, 'reason' => 'Invalid response'];
    }

    /**
     * Kirim notifikasi pendaftaran berhasil
     */
    public function sendPendaftaranBerhasil($noWa, $nama, $noPendaftaran)
    {
        $message = "✅ *Pendaftaran PSB Berhasil*\n\n";
        $message .= "Halo *{$nama}*,\n\n";
        $message .= "Pendaftaran Anda telah berhasil tercatat dengan nomor:\n";
        $message .= "*{$noPendaftaran}*\n\n";
        $message .= "Silakan lengkapi formulir dan upload dokumen di dashboard pendaftaran.\n\n";
        $message .= "Terima kasih.";

        return $this->send($noWa, $message);
    }

    /**
     * Kirim notifikasi revisi dokumen
     */
    public function sendRevisiDokumen($noWa, $nama, $catatan)
    {
        $message = "⚠️ *Revisi Dokumen Diperlukan*\n\n";
        $message .= "Halo *{$nama}*,\n\n";
        $message .= "Dokumen pendaftaran Anda memerlukan revisi:\n";
        $message .= "_{$catatan}_\n\n";
        $message .= "Silakan login dan perbaiki dokumen Anda.\n\n";
        $message .= "Terima kasih.";

        return $this->send($noWa, $message);
    }

    /**
     * Kirim notifikasi diterima
     */
    public function sendDiterima($noWa, $nama, $lembaga)
    {
        $message = "🎉 *SELAMAT! Anda DITERIMA*\n\n";
        $message .= "Halo *{$nama}*,\n\n";
        $message .= "Dengan gembira kami sampaikan bahwa Anda *DITERIMA* di:\n";
        $message .= "*{$lembaga}*\n\n";
        $message .= "Silakan segera melakukan daftar ulang.\n\n";
        $message .= "Selamat bergabung! 🙏";

        return $this->send($noWa, $message);
    }

    /**
     * Kirim notifikasi ditolak
     */
    public function sendDitolak($noWa, $nama, $alasan = '')
    {
        $message = "📋 *Informasi Pendaftaran PSB*\n\n";
        $message .= "Halo *{$nama}*,\n\n";
        $message .= "Mohon maaf, pendaftaran Anda belum dapat kami terima saat ini.";
        if (!empty($alasan)) {
            $message .= "\n\nKeterangan: _{$alasan}_";
        }
        $message .= "\n\nTerima kasih atas partisipasi Anda.";

        return $this->send($noWa, $message);
    }

    /**
     * Kirim token reset password
     */
    public function sendResetToken($noWa, $nama, $token)
    {
        $message = "🔐 *Reset Password PSB*\n\n";
        $message .= "Halo *{$nama}*,\n\n";
        $message .= "Kode reset password Anda:\n";
        $message .= "*{$token}*\n\n";
        $message .= "Kode berlaku 15 menit.\n";
        $message .= "Jika Anda tidak meminta reset password, abaikan pesan ini.";

        return $this->send($noWa, $message);
    }

    /**
     * Kirim notifikasi absensi ke orang tua
     * @param string $noWa Nomor WA orang tua
     * @param string $namaOrtu Nama orang tua (Ayah/Ibu/Wali)
     * @param string $namaSiswa Nama siswa
     * @param string $kelas Nama kelas
     * @param string $status Status absensi (A/I/S/D)
     * @param string $tanggal Tanggal absensi
     * @param string $mataPelajaran Nama mata pelajaran (opsional)
     * @param string $namaSekolah Nama sekolah
     */
    /**
     * Cek apakah notifikasi diaktifkan (via Pengaturan Menu)
     */
    private function isNotificationEnabled($key)
    {
        // Default true jika setting tidak ditemukan
        try {
            // Assuming 'Database' class is available and handles DB connection
            // and query execution. This is a placeholder for actual DB interaction.
            // If this class is part of a framework, you might use its DB facade/helper.
            // For a standalone class, you'd need to define 'Database' or pass a connection.
            // For this example, we'll assume a simple Database class exists.
            // If not, this part would need to be adapted to the actual DB access method.
            if (!class_exists('Database')) {
                // Fallback if Database class is not defined, or handle error
                // For a real application, you'd inject a DB dependency or use a global connection.
                return true;
            }
            $db = new Database();
            $db->query("SELECT value FROM pengaturan_sistem WHERE key_name = :key");
            $db->bind(':key', $key);
            $result = $db->single();

            if ($result) {
                return ($result['value'] == '1');
            }
        } catch (Exception $e) {
            // Ignore error, default to true
            error_log("Error checking notification setting '{$key}': " . $e->getMessage());
        }
        return true;
    }

    public function sendNotifikasiAbsensi($noWa, $namaOrtu, $namaSiswa, $kelas, $status, $tanggal, $mataPelajaran = '', $namaSekolah = '')
    {
        // Cek setting wa_notif_absensi_enabled
        if (!$this->isNotificationEnabled('wa_notif_absensi_enabled')) {
            return ['status' => false, 'reason' => 'Absence notification disabled by setting'];
        }

        $statusText = [
            'A' => 'ALPHA (Tanpa Keterangan)',
            'I' => 'IZIN',
            'S' => 'SAKIT',
            'D' => 'DISPENSASI'
        ];

        $statusLabel = $statusText[$status] ?? $status;

        // Build message using random template
        $message = $this->getRandomAbsensiTemplate($namaOrtu, $namaSiswa, $kelas, $statusLabel, $tanggal, $namaSekolah, $status, $mataPelajaran);

        return $this->send($noWa, $message);
    }

    /**
     * Build pesan absensi dengan random template (untuk queue atau penggunaan eksternal)
     * @param string $namaOrtu Nama orang tua/wali
     * @param string $namaSiswa Nama siswa
     * @param string $kelas Nama kelas
     * @param string $statusLabel Label status (ALPHA, IZIN, SAKIT, DISPENSASI)
     * @param string $tanggal Tanggal format readable
     * @param string $namaSekolah Nama sekolah
     * @param string $statusCode Kode status (A/I/S/D)
     * @param string $mapel Mata pelajaran (opsional)
     * @return string Pesan yang sudah dibuild dengan random template
     */
    public function buildAbsensiMessage($namaOrtu, $namaSiswa, $kelas, $statusLabel, $tanggal, $namaSekolah, $statusCode, $mapel = '')
    {
        return $this->getRandomAbsensiTemplate($namaOrtu, $namaSiswa, $kelas, $statusLabel, $tanggal, $namaSekolah, $statusCode, $mapel);
    }

    /**
     * Get random absensi template (10 variasi)
     */
    private function getRandomAbsensiTemplate($namaOrtu, $namaSiswa, $kelas, $statusLabel, $tanggal, $namaSekolah, $statusCode, $mapel = '')
    {
        $templates = [
            // Template 1 - Formal
            function () use ($namaOrtu, $namaSiswa, $kelas, $statusLabel, $tanggal, $namaSekolah, $statusCode, $mapel) {
                $msg = "🔔 *PEMBERITAHUAN KEHADIRAN*\n\n";
                $msg .= "Yth. Bapak/Ibu *{$namaOrtu}*,\n\n";
                $msg .= "Kami informasikan bahwa putra/putri Anda:\n";
                $msg .= "📌 *{$namaSiswa}* - Kelas *{$kelas}*\n";
                if ($mapel)
                    $msg .= "📚 Mata Pelajaran: *{$mapel}*\n";
                $msg .= "📅 Tanggal: {$tanggal}\n\n";
                $msg .= "Tercatat dengan status: *{$statusLabel}*\n\n";
                if ($statusCode === 'A')
                    $msg .= "⚠️ Mohon konfirmasi kepada pihak sekolah.\n\n";
                $msg .= "Hormat kami,\n*{$namaSekolah}*";
                return $msg;
            },

            // Template 2 - Singkat
            function () use ($namaSiswa, $kelas, $statusLabel, $tanggal, $namaSekolah, $mapel) {
                $msg = "📢 *Info Kehadiran*\n\n";
                $msg .= "{$namaSiswa} ({$kelas})\n";
                if ($mapel)
                    $msg .= "Mapel: {$mapel}\n";
                $msg .= "Status: *{$statusLabel}*\n";
                $msg .= "Tanggal: {$tanggal}\n\n";
                $msg .= "- {$namaSekolah}";
                return $msg;
            },

            // Template 3 - Friendly
            function () use ($namaOrtu, $namaSiswa, $kelas, $statusLabel, $tanggal, $namaSekolah, $statusCode, $mapel) {
                $msg = "Assalamualaikum Bapak/Ibu *{$namaOrtu}* 🙏\n\n";
                $msg .= "Kami sampaikan info kehadiran anak Anda hari ini:\n\n";
                $msg .= "👤 *{$namaSiswa}*\n";
                $msg .= "📚 Kelas: {$kelas}\n";
                if ($mapel)
                    $msg .= "📖 Mapel: {$mapel}\n";
                $msg .= "📆 {$tanggal}\n";
                $msg .= "📋 Status: *{$statusLabel}*\n\n";
                if ($statusCode === 'A')
                    $msg .= "Mohon dapat dikonfirmasi ya Pak/Bu 🙏\n\n";
                $msg .= "Terima kasih atas perhatiannya.\nWassalam, *{$namaSekolah}*";
                return $msg;
            },

            // Template 4 - Emoji Focus
            function () use ($namaSiswa, $kelas, $statusLabel, $tanggal, $namaSekolah, $mapel) {
                $msg = "📱 *UPDATE KEHADIRAN* 📱\n\n";
                $msg .= "🧒 {$namaSiswa}\n";
                $msg .= "🏫 {$kelas}\n";
                if ($mapel)
                    $msg .= "📖 {$mapel}\n";
                $msg .= "📅 {$tanggal}\n";
                $msg .= "✅ Status: *{$statusLabel}*\n\n";
                $msg .= "Salam,\n{$namaSekolah} 🏫";
                return $msg;
            },

            // Template 5 - Minimal
            function () use ($namaSiswa, $kelas, $statusLabel, $tanggal, $namaSekolah, $mapel) {
                $msg = "[INFO ABSENSI]\n\n";
                $msg .= "{$namaSiswa} - {$kelas}\n";
                if ($mapel)
                    $msg .= "Mapel: {$mapel}\n";
                $msg .= "{$tanggal}: *{$statusLabel}*\n\n";
                $msg .= "{$namaSekolah}";
                return $msg;
            },

            // Template 6 - Conversational
            function () use ($namaOrtu, $namaSiswa, $kelas, $statusLabel, $tanggal, $namaSekolah, $statusCode, $mapel) {
                $msg = "Halo Pak/Bu *{$namaOrtu}*! 👋\n\n";
                $msg .= "Sekadar info, anak Bapak/Ibu:\n";
                $msg .= "• Nama: *{$namaSiswa}*\n";
                $msg .= "• Kelas: {$kelas}\n";
                if ($mapel)
                    $msg .= "• Mapel: {$mapel}\n";
                $msg .= "\nHari ini ({$tanggal}) tercatat *{$statusLabel}*.\n\n";
                if ($statusCode === 'A')
                    $msg .= "Boleh dikonfirmasi ya Pak/Bu? 🙏\n\n";
                $msg .= "Salam hangat dari kami!\n*{$namaSekolah}*";
                return $msg;
            },

            // Template 7 - Professional
            function () use ($namaSiswa, $kelas, $statusLabel, $tanggal, $namaSekolah, $mapel) {
                $msg = "━━━━━━━━━━━━━━━━━━━━\n";
                $msg .= "📋 *LAPORAN KEHADIRAN SISWA*\n";
                $msg .= "━━━━━━━━━━━━━━━━━━━━\n\n";
                $msg .= "Nama: {$namaSiswa}\n";
                $msg .= "Kelas: {$kelas}\n";
                if ($mapel)
                    $msg .= "Mapel: {$mapel}\n";
                $msg .= "Tanggal: {$tanggal}\n";
                $msg .= "Status: *{$statusLabel}*\n\n";
                $msg .= "Demikian informasi ini kami sampaikan.\n\n";
                $msg .= "*{$namaSekolah}*";
                return $msg;
            },

            // Template 8 - Parent-Friendly
            function () use ($namaSiswa, $kelas, $statusLabel, $tanggal, $namaSekolah, $statusCode, $mapel) {
                $msg = "Bapak/Ibu Yth,\n\n";
                $mapelText = $mapel ? " pada mata pelajaran *{$mapel}*" : "";
                $msg .= "Putra/putri tersayang *{$namaSiswa}* dari kelas *{$kelas}*{$mapelText} pada tanggal {$tanggal} tercatat dengan status kehadiran:\n\n";
                $msg .= "➡️ *{$statusLabel}*\n\n";
                if ($statusCode === 'A')
                    $msg .= "Mohon dapat berkoordinasi dengan wali kelas.\n\n";
                $msg .= "Salam,\nTim *{$namaSekolah}*";
                return $msg;
            },

            // Template 9 - Quick Alert
            function () use ($namaSiswa, $kelas, $statusLabel, $tanggal, $namaSekolah, $mapel) {
                $msg = "⚡ *NOTIFIKASI CEPAT*\n\n";
                $msg .= "📛 {$namaSiswa}\n";
                $mapelInfo = $mapel ? " | 📖 {$mapel}" : "";
                $msg .= "📖 {$kelas}{$mapelInfo} | 📆 {$tanggal}\n";
                $msg .= "📊 *{$statusLabel}*\n\n";
                $msg .= "Balas OK jika sudah membaca.\n- {$namaSekolah}";
                return $msg;
            },

            // Template 10 - Modern
            function () use ($namaSiswa, $kelas, $statusLabel, $tanggal, $namaSekolah, $mapel) {
                $msg = "Hey Bapak/Ibu! 🌟\n\n";
                $msg .= "Update kehadiran untuk:\n";
                $msg .= "┌─────────────────\n";
                $msg .= "│ 👤 {$namaSiswa}\n";
                $msg .= "│ 🎒 {$kelas}\n";
                if ($mapel)
                    $msg .= "│ 📖 {$mapel}\n";
                $msg .= "│ 📅 {$tanggal}\n";
                $msg .= "│ 📝 *{$statusLabel}*\n";
                $msg .= "└─────────────────\n\n";
                $msg .= "Terima kasih! 💙\n*{$namaSekolah}*";
                return $msg;
            }
        ];

        // Pick random template
        $randomIndex = rand(0, count($templates) - 1);
        return $templates[$randomIndex]();
    }

    /**
     * Kirim notifikasi bulk absensi (untuk multiple siswa)
     * Rate limited: 5-10 detik jeda antar pesan untuk hindari blokir WA
     */
    public function sendNotifikasiBulkAbsensi($dataAbsensi, $namaSekolah = '')
    {
        $results = [];
        $count = 0;
        $total = count($dataAbsensi);

        foreach ($dataAbsensi as $data) {
            if (empty($data['no_wa']))
                continue;

            $result = $this->sendNotifikasiAbsensi(
                $data['no_wa'],
                $data['nama_ortu'] ?? 'Orang Tua/Wali',
                $data['nama_siswa'],
                $data['kelas'],
                $data['status'],
                $data['tanggal'],
                $data['mata_pelajaran'] ?? '',
                $namaSekolah
            );

            $results[] = [
                'siswa' => $data['nama_siswa'],
                'status' => $result['status'] ?? false,
                'message' => $result['reason'] ?? $result['message'] ?? 'Unknown'
            ];

            $count++;

            // Rate limiting: jeda 5-10 detik antar pesan (kecuali pesan terakhir)
            if ($count < $total) {
                $delay = rand(5, 10);
                sleep($delay);
            }
        }
        return $results;
    }

    /**
     * Kirim notifikasi absensi ke grup (rangkuman per mapel)
     * @param string $grupId ID grup WA (format: 628xxx atau xxx@g.us)
     * @param string $namaKelas Nama kelas
     * @param string $mapel Nama mata pelajaran
     * @param string $tanggal Tanggal absensi (format readable)
     * @param string $namaGuru Nama guru pengajar
     * @param array $daftarAbsen Array siswa tidak hadir [{nama, status, keterangan}]
     * @param int $totalSiswa Total siswa di kelas
     * @param string $namaSekolah Nama sekolah
     * @return array Response
     */
    public function sendNotifikasiAbsensiGrup($grupId, $namaKelas, $mapel, $tanggal, $namaGuru, $daftarAbsen, $totalSiswa = 0, $namaSekolah = '')
    {
        // Cek setting wa_notif_absensi_enabled
        if (!$this->isNotificationEnabled('wa_notif_absensi_enabled')) {
            return ['status' => false, 'reason' => 'Absence notification disabled by setting'];
        }

        if (empty($daftarAbsen)) {
            return ['status' => false, 'reason' => 'Tidak ada siswa tidak hadir'];
        }

        // Build message
        $message = $this->buildGrupAbsensiMessage($namaKelas, $mapel, $tanggal, $namaGuru, $daftarAbsen, $totalSiswa, $namaSekolah);

        return $this->send($grupId, $message);
    }

    /**
     * Build pesan rangkuman absensi untuk grup
     * @param string $namaKelas
     * @param string $mapel
     * @param string $tanggal
     * @param string $namaGuru
     * @param array $daftarAbsen [{nama, status, keterangan}]
     * @param int $totalSiswa
     * @param string $namaSekolah
     * @return string
     */
    public function buildGrupAbsensiMessage($namaKelas, $mapel, $tanggal, $namaGuru, $daftarAbsen, $totalSiswa, $namaSekolah, $jam = '', $topik = '')
    {
        // Gunakan template kustom jika ada
        if (!empty($this->groupAbsensiTemplate)) {
            $template = $this->groupAbsensiTemplate;

            // Kelompokkan siswa berdasarkan status
            $grouped = ['S' => [], 'I' => [], 'A' => [], 'D' => [], 'T' => []];
            foreach ($daftarAbsen as $siswa) {
                $status = $siswa['status'] ?? 'A';
                if (!isset($grouped[$status])) {
                    $grouped[$status] = []; // Handle unknown status
                }
                $keterangan = !empty($siswa['keterangan']) ? " ({$siswa['keterangan']})" : "";
                $grouped[$status][] = $siswa['nama'] . $keterangan;
            }

            // Hitung jumlah
            $jumlahSakit = count($grouped['S']);
            $jumlahIzin = count($grouped['I']);
            $jumlahAlpha = count($grouped['A']);
            $jumlahDispen = count($grouped['D']);
            $jumlahTerlambat = count($grouped['T'] ?? []); // Asumsi ada status T, jika tidak array kosong

            // Format list (numbered list)
            $formatList = function ($names) {
                if (empty($names))
                    return "-";
                $list = "";
                foreach ($names as $i => $name) {
                    $list .= ($i + 1) . ". " . $name . "\n";
                }
                return trim($list);
            };

            $listSakit = $formatList($grouped['S']);
            $listIzin = $formatList($grouped['I']);
            $listAlpha = $formatList($grouped['A']);
            $listTerlambat = $formatList($grouped['T'] ?? []);
            $listDispen = $formatList($grouped['D']);

            // Replace variables
            // Format Tanggal Indo
            $bulanIndo = [
                'January' => 'Januari',
                'February' => 'Februari',
                'March' => 'Maret',
                'April' => 'April',
                'May' => 'Mei',
                'June' => 'Juni',
                'July' => 'Juli',
                'August' => 'Agustus',
                'September' => 'September',
                'October' => 'Oktober',
                'November' => 'November',
                'December' => 'Desember'
            ];
            $tglIndo = strtr($tanggal, $bulanIndo);

            $vars = [
                '{{tanggal}}' => $tglIndo,
                '{{waktu_cetak}}' => date('H:i:s'),
                '{{sekolah}}' => $namaSekolah,
                '{{kelas}}' => $namaKelas,
                '{{mapel}}' => $mapel,
                '{{guru}}' => $namaGuru,
                '{{jam}}' => $jam,
                '{{topik}}' => $topik,
                '{{jumlah_siswa}}' => $totalSiswa,
                '{{hadir}}' => $totalSiswa - count($daftarAbsen), // Asumsi sisa hadir (perlu verifikasi logic ini sebenarnya)
                '{{sakit}}' => $jumlahSakit,
                '{{izin}}' => $jumlahIzin,
                '{{alpha}}' => $jumlahAlpha,
                '{{terlambat}}' => $jumlahTerlambat,
                '{{list_sakit}}' => $listSakit,
                '{{list_izin}}' => $listIzin,
                '{{list_alpha}}' => $listAlpha,
                '{{list_terlambat}}' => $listTerlambat
            ];

            return str_replace(array_keys($vars), array_values($vars), $template);
        }

        // Default Template (Hardcoded)
        $statusLabels = [
            'A' => 'ALPHA',
            'I' => 'IZIN',
            'S' => 'SAKIT',
            'D' => 'DISPENSASI'
        ];

        // Format Tanggal Indo
        $bulanIndo = [
            'January' => 'Januari',
            'February' => 'Februari',
            'March' => 'Maret',
            'April' => 'April',
            'May' => 'Mei',
            'June' => 'Juni',
            'July' => 'Juli',
            'August' => 'Agustus',
            'September' => 'September',
            'October' => 'Oktober',
            'November' => 'November',
            'December' => 'Desember'
        ];
        $tglIndo = strtr($tanggal, $bulanIndo);

        $msg = "*LAPORAN ABSENSI KELAS {$namaKelas}*\n";
        $msg .= "-----------------------------------\n";
        $msg .= "Mata Pelajaran: *{$mapel}*\n";
        $msg .= "Tanggal: {$tglIndo}\n";
        if (!empty($jam)) {
            $msg .= "Waktu: {$jam} WIB\n";
        }
        if (!empty($topik)) {
            $msg .= "Materi: {$topik}\n";
        }

        $msg .= "\n*Siswa Tidak Hadir:*\n";
        $msg .= "-----------------------------------\n";

        $no = 1;
        foreach ($daftarAbsen as $siswa) {
            $statusCode = $siswa['status'] ?? 'A';
            $statusLabel = $statusLabels[$statusCode] ?? $statusCode;
            $keterangan = !empty($siswa['keterangan']) ? " ({$siswa['keterangan']})" : "";

            $msg .= "{$no}. {$siswa['nama']} - *{$statusLabel}*{$keterangan}\n";
            $no++;
        }

        $jumlahAbsen = count($daftarAbsen);
        $msg .= "\n";
        if ($totalSiswa > 0) {
            $msg .= "Total: {$jumlahAbsen} siswa tidak hadir dari {$totalSiswa} siswa\n";
        } else {
            $msg .= "Total: {$jumlahAbsen} siswa tidak hadir\n";
        }
        $msg .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $msg .= "Mohon perhatian orang tua/wali.\n";
        if ($namaSekolah) {
            $msg .= "*{$namaSekolah}*";
        }

        return $msg;
    }


    /**
     * Kirim notifikasi pembayaran ke orang tua
     */
    /**
     * Kirim notifikasi pembayaran ke orang tua
     */
    public function sendNotifikasiPembayaran($noWa, $namaOrtu, $namaSiswa, $namaTagihan, $jumlahBayar, $diskon, $tanggal, $penerima, $sisaTagihan = 0, $keterangan = '', $namaSekolah = '')
    {
        // Cek setting wa_notif_pembayaran_enabled
        if (!$this->isNotificationEnabled('wa_notif_pembayaran_enabled')) {
            return ['status' => false, 'reason' => 'Payment notification disabled by setting']; // Silently skip
        }

        // Format Rupiah
        $jumlahFmt = 'Rp ' . number_format((int) $jumlahBayar, 0, ',', '.');
        $diskonFmt = 'Rp ' . number_format((int) $diskon, 0, ',', '.');
        $sisaFmt = 'Rp ' . number_format((int) $sisaTagihan, 0, ',', '.');

        $message = "💰 *BUKTI PEMBAYARAN SISWA*\n\n";
        $message .= "Kepada Yth. Bapak/Ibu *{$namaOrtu}*,\n\n";
        $message .= "Kami informasikan bahwa telah diterima pembayaran:\n\n";
        $message .= "👤 Nama Siswa: *{$namaSiswa}*\n";
        $message .= "🧾 Pembayaran: *{$namaTagihan}*\n";
        $message .= "💵 Nominal Bayar: *{$jumlahFmt}*\n";

        if ($diskon > 0) {
            $message .= "✂️ Diskon: *{$diskonFmt}*\n";
        }

        $message .= "📅 Tanggal: *{$tanggal}*\n";
        $message .= "🧑‍💼 Penerima: *{$penerima}*\n";

        if ($sisaTagihan > 0) {
            $message .= "⚠️ Sisa Tagihan: *{$sisaFmt}*\n";
        } else {
            $message .= "✅ Status: *LUNAS*\n";
        }

        if (!empty($keterangan)) {
            $message .= "📝 Ket: {$keterangan}\n";
        }

        if (!empty($namaSekolah)) {
            $message .= "\nTerima kasih,\n";
            $message .= "*{$namaSekolah}*";
        }

        $message .= "\n\n━━━━━━━━━━━━━━━━━━━━━\n";
        $message .= "✅ *Mohon balas YA jika sudah membaca pesan ini.*\n";
        $message .= "━━━━━━━━━━━━━━━━━━━━━\n\n";

        $message .= "_Pesan ini dikirim otomatis oleh sistem._";

        return $this->send($noWa, $message);
    }

    /**
     * Kirim notifikasi update diskon ke orang tua
     */
    public function sendNotifikasiDiskon($noWa, $namaOrtu, $namaSiswa, $namaTagihan, $diskon, $sisaTagihan, $namaSekolah = '')
    {
        // Cek setting wa_notif_pembayaran_enabled
        if (!$this->isNotificationEnabled('wa_notif_pembayaran_enabled')) {
            return ['status' => false, 'reason' => 'Discount notification disabled by setting']; // Silently skip
        }

        $diskonFmt = 'Rp ' . number_format((int) $diskon, 0, ',', '.');
        $sisaFmt = 'Rp ' . number_format((int) $sisaTagihan, 0, ',', '.');

        $message = "🏷️ *UPDATE DISKON TAGIHAN*\n\n";
        $message .= "Kepada Yth. Bapak/Ibu *{$namaOrtu}*,\n\n";
        $message .= "Kami informasikan bahwa siswa:\n";
        $message .= "👤 Nama: *{$namaSiswa}*\n";
        $message .= "📚 Tagihan: *{$namaTagihan}*\n\n";
        $message .= "Telah mendapatkan potongan/diskon sebesar:\n";
        $message .= "✂️ *{$diskonFmt}*\n\n";

        if ($sisaTagihan <= 0) {
            $message .= "✅ Status Tagihan: *LUNAS*\n";
        } else {
            $message .= "Sehingga sisa tagihan menjadi: *{$sisaFmt}*\n";
        }

        if (!empty($namaSekolah)) {
            $message .= "\nTerima kasih,\n";
            $message .= "*{$namaSekolah}*";
        }

        $message .= "\n\n━━━━━━━━━━━━━━━━━━━━━\n";
        $message .= "✅ *Mohon balas YA jika sudah membaca pesan ini.*\n";
        $message .= "━━━━━━━━━━━━━━━━━━━━━\n\n";
        $message .= "_Pesan ini dikirim otomatis oleh sistem._";

        return $this->send($noWa, $message);
    }

    // =========================================================================
    // QUEUE METHODS - Untuk sistem antrian pesan
    // =========================================================================

    /**
     * Tambah pesan ke antrian (bukan kirim langsung)
     */
    public function queueMessage($noWa, $pesan, $jenis = 'general', $metadata = null)
    {
        require_once APPROOT . '/app/models/WaQueue_model.php';
        $queueModel = new WaQueue_model();
        return $queueModel->addToQueue($this->formatNumber($noWa), $pesan, $jenis, $metadata);
    }

    /**
     * Queue notifikasi absensi (bukan kirim langsung)
     */
    public function queueNotifikasiAbsensi($noWa, $namaOrtu, $namaSiswa, $kelas, $status, $tanggal, $mataPelajaran = '', $namaSekolah = '')
    {
        // Cek setting wa_notif_absensi_enabled
        if (!$this->isNotificationEnabled('wa_notif_absensi_enabled')) {
            return false;
        }

        $statusText = [
            'A' => 'ALPHA (Tanpa Keterangan)',
            'I' => 'IZIN',
            'S' => 'SAKIT',
            'D' => 'DISPENSASI'
        ];

        $statusLabel = $statusText[$status] ?? $status;

        // Build message using random template (same as sendNotifikasiAbsensi)
        $message = $this->getRandomAbsensiTemplate($namaOrtu, $namaSiswa, $kelas, $statusLabel, $tanggal, $namaSekolah, $status);

        // Queue instead of send
        $metadata = [
            'nama_siswa' => $namaSiswa,
            'kelas' => $kelas,
            'status' => $status
        ];

        return $this->queueMessage($noWa, $message, 'absensi', $metadata);
    }

    /**
     * Queue notifikasi bulk absensi
     */
    public function queueBulkAbsensi($dataAbsensi, $namaSekolah = '')
    {
        $queuedIds = [];
        foreach ($dataAbsensi as $data) {
            if (empty($data['no_wa']))
                continue;

            $id = $this->queueNotifikasiAbsensi(
                $data['no_wa'],
                $data['nama_ortu'] ?? 'Orang Tua/Wali',
                $data['nama_siswa'],
                $data['kelas'],
                $data['status'],
                $data['tanggal'],
                $data['mata_pelajaran'] ?? '',
                $namaSekolah
            );

            if ($id) {
                $queuedIds[] = $id;
            }
        }
        return $queuedIds;
    }

    /**
     * Queue notifikasi pembayaran
     */
    public function queueNotifikasiPembayaran($noWa, $namaOrtu, $namaSiswa, $namaTagihan, $jumlahBayar, $diskon, $tanggal, $penerima, $sisaTagihan = 0, $keterangan = '', $namaSekolah = '')
    {
        if (!$this->isNotificationEnabled('wa_notif_pembayaran_enabled')) {
            return false;
        }

        // Format Rupiah
        $jumlahFmt = 'Rp ' . number_format((int) $jumlahBayar, 0, ',', '.');
        $diskonFmt = 'Rp ' . number_format((int) $diskon, 0, ',', '.');
        $sisaFmt = 'Rp ' . number_format((int) $sisaTagihan, 0, ',', '.');

        $message = "💰 *BUKTI PEMBAYARAN SISWA*\n\n";
        $message .= "Kepada Yth. Bapak/Ibu *{$namaOrtu}*,\n\n";
        $message .= "Kami informasikan bahwa telah diterima pembayaran:\n\n";
        $message .= "👤 Nama Siswa: *{$namaSiswa}*\n";
        $message .= "🧾 Pembayaran: *{$namaTagihan}*\n";
        $message .= "💵 Nominal Bayar: *{$jumlahFmt}*\n";

        if ($diskon > 0) {
            $message .= "✂️ Diskon: *{$diskonFmt}*\n";
        }

        $message .= "📅 Tanggal: *{$tanggal}*\n";
        $message .= "🧑‍💼 Penerima: *{$penerima}*\n";

        if ($sisaTagihan > 0) {
            $message .= "⚠️ Sisa Tagihan: *{$sisaFmt}*\n";
        } else {
            $message .= "✅ Status: *LUNAS*\n";
        }

        if (!empty($keterangan)) {
            $message .= "📝 Ket: {$keterangan}\n";
        }

        if (!empty($namaSekolah)) {
            $message .= "\nTerima kasih,\n";
            $message .= "*{$namaSekolah}*";
        }

        $message .= "\n\n━━━━━━━━━━━━━━━━━━━━━\n";
        $message .= "✅ *Mohon balas YA jika sudah membaca pesan ini.*\n";
        $message .= "━━━━━━━━━━━━━━━━━━━━━\n\n";
        $message .= "_Pesan ini dikirim otomatis oleh sistem._";

        $metadata = [
            'nama_siswa' => $namaSiswa,
            'tagihan' => $namaTagihan,
            'jumlah' => $jumlahBayar
        ];

        return $this->queueMessage($noWa, $message, 'pembayaran', $metadata);
    }
}
