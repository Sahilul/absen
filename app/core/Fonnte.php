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
            $db->query('SELECT wa_gateway_provider, wa_gateway_url, wa_gateway_token, wa_gateway_username, wa_gateway_password FROM pengaturan_aplikasi WHERE id = 1');
            $result = $db->single();
            $this->provider = $result['wa_gateway_provider'] ?? 'fonnte';
            $this->apiUrl = $result['wa_gateway_url'] ?? self::$providers[$this->provider]['url'] ?? 'https://api.fonnte.com/send';
            $this->token = $result['wa_gateway_token'] ?? '';
            $this->username = $result['wa_gateway_username'] ?? '';
            $this->password = $result['wa_gateway_password'] ?? '';
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
        // Also check for direct success response
        if ($httpCode >= 200 && $httpCode < 300) {
            if (isset($result['code']) && $result['code'] == 200) {
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
     * Format nomor HP ke format internasional
     */
    private function formatNumber($number)
    {
        $number = preg_replace('/[^0-9]/', '', $number);
        if (substr($number, 0, 1) === '0') {
            $number = '62' . substr($number, 1);
        }
        if (substr($number, 0, 2) !== '62') {
            $number = '62' . $number;
        }
        return $number;
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
    public function sendNotifikasiAbsensi($noWa, $namaOrtu, $namaSiswa, $kelas, $status, $tanggal, $mataPelajaran = '', $namaSekolah = '')
    {
        $statusText = [
            'A' => 'ALPHA (Tanpa Keterangan)',
            'I' => 'IZIN',
            'S' => 'SAKIT',
            'D' => 'DISPENSASI'
        ];

        $statusEmoji = [
            'A' => '🚨',
            'I' => '📝',
            'S' => '🏥',
            'D' => '📋'
        ];

        $statusLabel = $statusText[$status] ?? $status;
        $emoji = $statusEmoji[$status] ?? '📢';

        $message = "{$emoji} *NOTIFIKASI KEHADIRAN SISWA*\n\n";
        $message .= "Kepada Yth. Bapak/Ibu *{$namaOrtu}*,\n\n";
        $message .= "Dengan hormat,\n";
        $message .= "Kami informasikan bahwa putra/putri Anda:\n\n";
        $message .= "👤 Nama: *{$namaSiswa}*\n";
        $message .= "📚 Kelas: *{$kelas}*\n";
        $message .= "📅 Tanggal: *{$tanggal}*\n";
        if (!empty($mataPelajaran)) {
            $message .= "📖 Mapel: *{$mataPelajaran}*\n";
        }
        $message .= "\nTercatat dengan status: *{$statusLabel}*\n\n";

        if ($status === 'A') {
            $message .= "⚠️ Mohon konfirmasi kehadiran anak Anda kepada pihak sekolah.\n\n";
        }

        if (!empty($namaSekolah)) {
            $message .= "Hormat kami,\n";
            $message .= "*{$namaSekolah}*\n\n";
        }

        $message .= "━━━━━━━━━━━━━━━━━━━━━\n";
        $message .= "✅ *Mohon balas YA jika sudah membaca pesan ini.*\n";
        $message .= "━━━━━━━━━━━━━━━━━━━━━\n\n";

        $message .= "_Pesan ini dikirim otomatis oleh sistem._";

        return $this->send($noWa, $message);
    }

    /**
     * Kirim notifikasi bulk absensi (untuk multiple siswa)
     */
    public function sendNotifikasiBulkAbsensi($dataAbsensi, $namaSekolah = '')
    {
        $results = [];
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
        }
        return $results;
    }
}
