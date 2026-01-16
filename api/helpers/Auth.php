<?php
/**
 * API Authentication Helper
 * File: api/helpers/Auth.php
 */

class Auth
{
    private static $secretKey = 'sabilillah_mobile_app_secret_key_2026';

    /**
     * Generate JWT token
     */
    public static function generateToken($userId, $role, $idRef, $expiresIn = 86400)
    {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payload = json_encode([
            'user_id' => $userId,
            'role' => $role,
            'id_ref' => $idRef,
            'iat' => time(),
            'exp' => time() + $expiresIn
        ]);

        $base64Header = self::base64UrlEncode($header);
        $base64Payload = self::base64UrlEncode($payload);

        $signature = hash_hmac('sha256', $base64Header . '.' . $base64Payload, self::$secretKey, true);
        $base64Signature = self::base64UrlEncode($signature);

        return $base64Header . '.' . $base64Payload . '.' . $base64Signature;
    }

    /**
     * Verify JWT token
     */
    public static function verifyToken($token)
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            return false;
        }

        list($base64Header, $base64Payload, $base64Signature) = $parts;

        // Verify signature
        $signature = self::base64UrlDecode($base64Signature);
        $expectedSignature = hash_hmac('sha256', $base64Header . '.' . $base64Payload, self::$secretKey, true);

        if (!hash_equals($signature, $expectedSignature)) {
            return false;
        }

        // Decode payload
        $payload = json_decode(self::base64UrlDecode($base64Payload), true);

        // Check expiration
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return false;
        }

        return $payload;
    }

    /**
     * Get Bearer token from header
     */
    public static function getBearerToken()
    {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';

        if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Require authentication
     */
    public static function requireAuth()
    {
        $token = self::getBearerToken();

        if (!$token) {
            Response::unauthorized('Token not provided');
        }

        $payload = self::verifyToken($token);

        if (!$payload) {
            Response::unauthorized('Invalid or expired token');
        }

        return $payload;
    }

    /**
     * Require specific role
     */
    public static function requireRole($roles)
    {
        $payload = self::requireAuth();

        if (!is_array($roles)) {
            $roles = [$roles];
        }

        if (!in_array($payload['role'], $roles)) {
            Response::error('Access denied. Required role: ' . implode(' or ', $roles), 403);
        }

        return $payload;
    }

    /**
     * Base64 URL encode
     */
    private static function base64UrlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Base64 URL decode
     */
    private static function base64UrlDecode($data)
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}
