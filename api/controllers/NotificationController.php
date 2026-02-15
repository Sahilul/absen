<?php
/**
 * API Notification Controller
 * File: api/controllers/NotificationController.php
 */

namespace Api;

class NotificationController
{
    private $db;

    public function __construct()
    {
        $this->db = new \Database();
    }

    public function handleRequest($method, $action, $param = null)
    {
        $payload = \Auth::requireAuth();

        switch ($action) {
            case 'register':
                if ($method === 'POST') {
                    $this->registerDevice($payload);
                }
                break;

            case 'unregister':
                if ($method === 'POST') {
                    $this->unregisterDevice($payload);
                }
                break;

            default:
                \Response::notFound('Action not found');
        }
    }

    /**
     * POST /api/notifications/register
     * Register FCM token for push notifications
     */
    private function registerDevice($payload)
    {
        $input = json_decode(file_get_contents('php://input'), true);

        $fcmToken = $input['fcm_token'] ?? '';
        $deviceType = $input['device_type'] ?? 'android';
        $deviceName = $input['device_name'] ?? '';

        if (empty($fcmToken)) {
            \Response::validationError(['fcm_token' => 'FCM token is required']);
        }

        $userId = $payload['user_id'];

        // Check if token exists
        $this->db->query("SELECT id FROM user_devices WHERE fcm_token = :token");
        $this->db->bind(':token', $fcmToken);
        $existing = $this->db->single();

        if ($existing) {
            // Update existing
            $this->db->query("UPDATE user_devices SET 
                              user_id = :user_id,
                              device_type = :device_type,
                              device_name = :device_name,
                              updated_at = NOW()
                              WHERE fcm_token = :token");
        } else {
            // Insert new
            $this->db->query("INSERT INTO user_devices (user_id, fcm_token, device_type, device_name, created_at)
                              VALUES (:user_id, :token, :device_type, :device_name, NOW())");
        }

        $this->db->bind(':user_id', $userId);
        $this->db->bind(':token', $fcmToken);
        $this->db->bind(':device_type', $deviceType);
        $this->db->bind(':device_name', $deviceName);

        if ($this->db->execute()) {
            \Response::success(null, 'Device registered successfully');
        } else {
            \Response::error('Failed to register device');
        }
    }

    /**
     * POST /api/notifications/unregister
     * Unregister FCM token
     */
    private function unregisterDevice($payload)
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $fcmToken = $input['fcm_token'] ?? '';

        if (empty($fcmToken)) {
            \Response::validationError(['fcm_token' => 'FCM token is required']);
        }

        $this->db->query("DELETE FROM user_devices WHERE fcm_token = :token AND user_id = :user_id");
        $this->db->bind(':token', $fcmToken);
        $this->db->bind(':user_id', $payload['user_id']);

        $this->db->execute();

        \Response::success(null, 'Device unregistered successfully');
    }
}
