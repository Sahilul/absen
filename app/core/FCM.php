<?php
/**
 * Firebase Cloud Messaging (FCM) Service
 * File: app/core/FCM.php
 * 
 * Digunakan untuk mengirim push notification ke device mobile
 */

class FCM
{
    private $serverKey;
    private $fcmUrl = 'https://fcm.googleapis.com/fcm/send';
    private $db;

    public function __construct()
    {
        $this->db = new Database();

        // Get FCM Server Key from pengaturan_aplikasi
        $this->db->query("SELECT value FROM pengaturan_aplikasi WHERE name = 'fcm_server_key' LIMIT 1");
        $result = $this->db->single();
        $this->serverKey = $result['value'] ?? '';
    }

    /**
     * Set server key manually
     */
    public function setServerKey($key)
    {
        $this->serverKey = $key;
        return $this;
    }

    /**
     * Send notification to a single device
     * 
     * @param string $token FCM device token
     * @param string $title Notification title
     * @param string $body Notification body
     * @param array $data Additional data payload
     * @return array Response from FCM
     */
    public function sendToDevice($token, $title, $body, $data = [])
    {
        $message = [
            'to' => $token,
            'notification' => [
                'title' => $title,
                'body' => $body,
                'sound' => 'default',
                'badge' => 1
            ],
            'data' => $data,
            'priority' => 'high'
        ];

        return $this->send($message);
    }

    /**
     * Send notification to multiple devices
     * 
     * @param array $tokens Array of FCM device tokens
     * @param string $title Notification title
     * @param string $body Notification body
     * @param array $data Additional data payload
     * @return array Response from FCM
     */
    public function sendToMultiple($tokens, $title, $body, $data = [])
    {
        // FCM supports max 1000 tokens per request
        $chunks = array_chunk($tokens, 1000);
        $results = [];

        foreach ($chunks as $chunk) {
            $message = [
                'registration_ids' => $chunk,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                    'sound' => 'default',
                    'badge' => 1
                ],
                'data' => $data,
                'priority' => 'high'
            ];

            $results[] = $this->send($message);
        }

        return $results;
    }

    /**
     * Send notification to a user by user_id
     * 
     * @param int $userId User ID
     * @param string $title Notification title
     * @param string $body Notification body
     * @param array $data Additional data payload
     * @return array Response
     */
    public function sendToUser($userId, $title, $body, $data = [])
    {
        // Get all tokens for user
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
     * 
     * @param string $role Role name (guru, siswa, admin)
     * @param string $title Notification title
     * @param string $body Notification body
     * @param array $data Additional data payload
     * @return array Response
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
     * 
     * @param int $idSiswa Siswa ID
     * @param string $status Absensi status (H/I/S/A)
     * @param string $namaSiswa Nama siswa
     * @param string $tanggal Tanggal absensi
     * @return array Response
     */
    public function sendAbsensiNotification($idSiswa, $status, $namaSiswa, $tanggal)
    {
        // Get parent user_id from siswa
        $this->db->query("SELECT u.id as user_id, s.ayah_no_hp, s.ibu_no_hp
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
            'id_siswa' => $idSiswa,
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
     * 
     * @param int $idSiswa Siswa ID
     * @param string $jenisPembayaran Jenis pembayaran
     * @param int $nominal Nominal
     * @param string $status Status pembayaran
     * @return array Response
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
            'id_siswa' => $idSiswa,
            'nominal' => $nominal
        ];

        if ($parent && $parent['user_id']) {
            return $this->sendToUser($parent['user_id'], $title, $body, $data);
        }

        return ['success' => false, 'error' => 'No parent user found'];
    }

    /**
     * Send raw FCM message
     */
    private function send($message)
    {
        if (empty($this->serverKey)) {
            return ['success' => false, 'error' => 'FCM Server Key not configured'];
        }

        $headers = [
            'Authorization: key=' . $this->serverKey,
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
        // Optional: Log to database or file
        $logFile = dirname(__DIR__, 2) . '/logs/fcm_' . date('Y-m-d') . '.log';
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
