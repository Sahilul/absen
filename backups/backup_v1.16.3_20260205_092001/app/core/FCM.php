<?php
/**
 * Firebase Cloud Messaging (FCM) V1 API Service
 * File: app/core/FCM.php
 * 
 * Menggunakan FCM HTTP v1 API dengan Service Account authentication
 * Credential dibaca dari database (pengaturan_aplikasi)
 */

class FCM
{
    private $projectId;
    private $clientEmail;
    private $privateKey;
    private $accessToken;
    private $tokenExpiry;
    private $fcmUrl;
    private $db;

    public function __construct()
    {
        $this->db = new Database();

        // Load credentials from database (app_settings table)
        $this->loadCredentialsFromDatabase();

        if ($this->projectId) {
            $this->fcmUrl = "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send";
        }
    }

    /**
     * Load Firebase credentials from database
     */
    private function loadCredentialsFromDatabase()
    {
        try {
            // Get project ID
            $this->db->query("SELECT value FROM app_settings WHERE name = 'firebase_project_id' LIMIT 1");
            $result = $this->db->single();
            $this->projectId = $result ? $result['value'] : '';

            // Get client email
            $this->db->query("SELECT value FROM app_settings WHERE name = 'firebase_client_email' LIMIT 1");
            $result = $this->db->single();
            $this->clientEmail = $result ? $result['value'] : '';

            // Get private key
            $this->db->query("SELECT value FROM app_settings WHERE name = 'firebase_private_key' LIMIT 1");
            $result = $this->db->single();
            $this->privateKey = $result ? $result['value'] : '';
        } catch (Exception $e) {
            // Table doesn't exist yet
            $this->projectId = '';
            $this->clientEmail = '';
            $this->privateKey = '';
        }
    }

    /**
     * Check if FCM is configured
     */
    public function isConfigured()
    {
        return !empty($this->projectId) && !empty($this->clientEmail) && !empty($this->privateKey);
    }

    /**
     * Set project ID manually
     */
    public function setProjectId($projectId)
    {
        $this->projectId = $projectId;
        $this->fcmUrl = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";
        return $this;
    }

    /**
     * Set credentials manually
     */
    public function setCredentials($projectId, $clientEmail, $privateKey)
    {
        $this->projectId = $projectId;
        $this->clientEmail = $clientEmail;
        $this->privateKey = $privateKey;
        $this->fcmUrl = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";
        return $this;
    }

    /**
     * Get OAuth2 access token using Service Account
     */
    private function getAccessToken()
    {
        // Check if we have a valid cached token
        if ($this->accessToken && $this->tokenExpiry && time() < $this->tokenExpiry) {
            return $this->accessToken;
        }

        if (!$this->isConfigured()) {
            throw new Exception("Firebase credentials not configured. Please set up in Admin > Pengaturan Mobile App");
        }

        // Create JWT
        $header = [
            'alg' => 'RS256',
            'typ' => 'JWT'
        ];

        $now = time();
        $expiry = $now + 3600; // 1 hour

        $claims = [
            'iss' => $this->clientEmail,
            'sub' => $this->clientEmail,
            'aud' => 'https://oauth2.googleapis.com/token',
            'iat' => $now,
            'exp' => $expiry,
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging'
        ];

        // Encode header and claims
        $headerEncoded = $this->base64UrlEncode(json_encode($header));
        $claimsEncoded = $this->base64UrlEncode(json_encode($claims));

        // Sign with private key
        $signatureInput = $headerEncoded . '.' . $claimsEncoded;
        $privateKey = openssl_pkey_get_private($this->privateKey);

        if (!$privateKey) {
            throw new Exception("Invalid private key. Please check Firebase credentials in Admin settings.");
        }

        openssl_sign($signatureInput, $signature, $privateKey, OPENSSL_ALGO_SHA256);
        $signatureEncoded = $this->base64UrlEncode($signature);

        $jwt = $signatureInput . '.' . $signatureEncoded;

        // Exchange JWT for access token
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://oauth2.googleapis.com/token');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt
        ]));

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new Exception("Failed to get access token: $response");
        }

        $tokenData = json_decode($response, true);
        $this->accessToken = $tokenData['access_token'];
        $this->tokenExpiry = $now + ($tokenData['expires_in'] ?? 3600) - 60; // 1 minute buffer

        return $this->accessToken;
    }

    /**
     * Base64 URL encode
     */
    private function base64UrlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Send notification to a single device
     */
    public function sendToDevice($token, $title, $body, $data = [])
    {
        $message = [
            'message' => [
                'token' => $token,
                'notification' => [
                    'title' => $title,
                    'body' => $body
                ],
                'android' => [
                    'priority' => 'high',
                    'notification' => [
                        'sound' => 'default',
                        'channel_id' => 'high_importance_channel'
                    ]
                ],
                'apns' => [
                    'payload' => [
                        'aps' => [
                            'sound' => 'default',
                            'badge' => 1
                        ]
                    ]
                ]
            ]
        ];

        if (!empty($data)) {
            $message['message']['data'] = array_map('strval', $data);
        }

        return $this->send($message);
    }

    /**
     * Send notification to multiple devices
     */
    public function sendToMultiple($tokens, $title, $body, $data = [])
    {
        $results = [];

        foreach ($tokens as $token) {
            $results[] = $this->sendToDevice($token, $title, $body, $data);
        }

        return $results;
    }

    /**
     * Send notification to a user by user_id
     */
    public function sendToUser($userId, $title, $body, $data = [])
    {
        $this->db->query("SELECT fcm_token FROM user_devices WHERE user_id = :user_id AND is_active = 1");
        $this->db->bind(':user_id', $userId);
        $devices = $this->db->resultSet();

        if (empty($devices)) {
            return ['success' => false, 'error' => 'No devices found for user'];
        }

        $tokens = array_column($devices, 'fcm_token');

        if (count($tokens) === 1) {
            return $this->sendToDevice($tokens[0], $title, $body, $data);
        }

        return $this->sendToMultiple($tokens, $title, $body, $data);
    }

    /**
     * Send notification to users by role
     */
    public function sendToRole($role, $title, $body, $data = [])
    {
        $this->db->query("SELECT ud.fcm_token 
                          FROM user_devices ud 
                          JOIN users u ON ud.user_id = u.id 
                          WHERE u.role = :role AND ud.is_active = 1");
        $this->db->bind(':role', $role);
        $devices = $this->db->resultSet();

        if (empty($devices)) {
            return ['success' => false, 'error' => 'No devices found for role'];
        }

        $tokens = array_column($devices, 'fcm_token');
        return $this->sendToMultiple($tokens, $title, $body, $data);
    }

    /**
     * Send absensi notification to parent
     */
    public function sendAbsensiNotification($idSiswa, $status, $namaSiswa, $tanggal)
    {
        $this->db->query("SELECT u.id as user_id
                          FROM siswa s 
                          LEFT JOIN users u ON u.id_ref = s.id AND u.role = 'ortu'
                          WHERE s.id = :id_siswa");
        $this->db->bind(':id_siswa', $idSiswa);
        $parent = $this->db->single();

        $statusText = [
            'H' => 'Hadir',
            'I' => 'Izin',
            'S' => 'Sakit',
            'A' => 'Tidak Hadir (Alpha)'
        ];

        $title = 'Notifikasi Absensi';
        $body = "$namaSiswa tercatat {$statusText[$status]} pada $tanggal";

        $data = [
            'type' => 'absensi',
            'id_siswa' => (string) $idSiswa,
            'status' => $status,
            'tanggal' => $tanggal
        ];

        if ($parent && $parent['user_id']) {
            return $this->sendToUser($parent['user_id'], $title, $body, $data);
        }

        return ['success' => false, 'error' => 'No parent user found'];
    }

    /**
     * Send pembayaran notification
     */
    public function sendPembayaranNotification($idSiswa, $jenisPembayaran, $nominal, $status)
    {
        $this->db->query("SELECT u.id as user_id, s.nama
                          FROM siswa s 
                          LEFT JOIN users u ON u.id_ref = s.id AND u.role = 'ortu'
                          WHERE s.id = :id_siswa");
        $this->db->bind(':id_siswa', $idSiswa);
        $parent = $this->db->single();

        $title = 'Notifikasi Pembayaran';
        $body = "Pembayaran $jenisPembayaran untuk {$parent['nama']} sebesar Rp " . number_format($nominal, 0, ',', '.') . " berhasil.";

        $data = [
            'type' => 'pembayaran',
            'id_siswa' => (string) $idSiswa,
            'nominal' => (string) $nominal
        ];

        if ($parent && $parent['user_id']) {
            return $this->sendToUser($parent['user_id'], $title, $body, $data);
        }

        return ['success' => false, 'error' => 'No parent user found'];
    }

    /**
     * Send FCM message using V1 API
     */
    private function send($message)
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'error' => 'Firebase not configured. Go to Admin > Pengaturan Mobile App'];
        }

        try {
            $accessToken = $this->getAccessToken();
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }

        $headers = [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->fcmUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($message));
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['success' => false, 'error' => $error];
        }

        $response = json_decode($result, true);

        // Log the notification
        $this->logNotification($message, $response);

        return [
            'success' => $httpCode === 200,
            'http_code' => $httpCode,
            'response' => $response
        ];
    }

    /**
     * Log notification for debugging
     */
    private function logNotification($message, $response)
    {
        $logFile = APPROOT . '/logs/fcm_' . date('Y-m-d') . '.log';
        $logDir = dirname($logFile);

        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $log = [
            'timestamp' => date('Y-m-d H:i:s'),
            'message' => $message,
            'response' => $response
        ];

        file_put_contents($logFile, json_encode($log) . "\n", FILE_APPEND);
    }
}
