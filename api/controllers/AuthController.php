<?php
/**
 * API Auth Controller
 * File: api/controllers/AuthController.php
 */

namespace Api;

require_once APPROOT . '/app/models/User_model.php';

class AuthController
{
    private $db;

    public function __construct()
    {
        $this->db = new \Database();
    }

    public function handleRequest($method, $action, $param = null)
    {
        switch ($action) {
            case 'login':
                if ($method === 'POST') {
                    $this->login();
                }
                break;

            case 'google':
                if ($method === 'POST') {
                    $this->googleAuth();
                }
                break;

            case 'logout':
                if ($method === 'POST') {
                    $this->logout();
                }
                break;

            case 'me':
                if ($method === 'GET') {
                    $this->me();
                }
                break;

            case 'refresh':
                if ($method === 'POST') {
                    $this->refresh();
                }
                break;

            default:
                \Response::notFound('Action not found');
        }
    }

    /**
     * POST /api/auth/login
     */
    private function login()
    {
        // Get JSON input
        $input = json_decode(file_get_contents('php://input'), true);

        $username = $input['username'] ?? '';
        $password = $input['password'] ?? '';

        if (empty($username) || empty($password)) {
            \Response::validationError([
                'username' => empty($username) ? 'Username is required' : null,
                'password' => empty($password) ? 'Password is required' : null
            ]);
        }

        // Find user
        $this->db->query("SELECT * FROM users WHERE username = :username LIMIT 1");
        $this->db->bind(':username', $username);
        $user = $this->db->single();

        if (!$user) {
            \Response::error('Username atau password salah', 401);
        }

        // Verify password
        if (!password_verify($password, $user['password'])) {
            \Response::error('Username atau password salah', 401);
        }

        // Check if active
        if (isset($user['is_active']) && $user['is_active'] == 0) {
            \Response::error('Akun tidak aktif', 401);
        }

        // Get user details based on role
        $userData = $this->getUserDetails($user);

        // Generate token
        $token = \Auth::generateToken($user['id'], $user['role'], $user['id_ref'] ?? null);

        \Response::success([
            'token' => $token,
            'user' => $userData
        ], 'Login berhasil');
    }

    /**
     * Get user details based on role
     */
    private function getUserDetails($user)
    {
        $role = $user['role'];
        $idRef = $user['id_ref'] ?? null;

        $userData = [
            'id' => $user['id'],
            'username' => $user['username'],
            'role' => $role,
            'nama' => $user['nama_lengkap'] ?? $user['username']
        ];

        if ($role === 'guru' || $role === 'walikelas') {
            $this->db->query("SELECT * FROM guru WHERE id = :id LIMIT 1");
            $this->db->bind(':id', $idRef);
            $guru = $this->db->single();

            if ($guru) {
                $userData['nama'] = $guru['nama'];
                $userData['nip'] = $guru['nip'] ?? '';
                $userData['foto'] = $guru['foto'] ?? null;
            }

            // Check if wali kelas
            if ($role === 'walikelas' || $role === 'guru') {
                $this->db->query("SELECT wk.*, k.nama_kelas 
                                  FROM wali_kelas wk 
                                  JOIN kelas k ON wk.id_kelas = k.id 
                                  WHERE wk.id_guru = :id_guru 
                                  AND wk.id_tp = (SELECT id FROM tahun_pelajaran WHERE is_active = 1 LIMIT 1)
                                  LIMIT 1");
                $this->db->bind(':id_guru', $idRef);
                $wali = $this->db->single();

                if ($wali) {
                    $userData['is_wali_kelas'] = true;
                    $userData['kelas_wali'] = $wali['nama_kelas'];
                    $userData['id_kelas_wali'] = $wali['id_kelas'];
                }
            }
        } elseif ($role === 'siswa') {
            $this->db->query("SELECT s.*, k.nama_kelas 
                              FROM siswa s 
                              LEFT JOIN kelas k ON s.id_kelas = k.id 
                              WHERE s.id = :id LIMIT 1");
            $this->db->bind(':id', $idRef);
            $siswa = $this->db->single();

            if ($siswa) {
                $userData['nama'] = $siswa['nama'];
                $userData['nisn'] = $siswa['nisn'] ?? '';
                $userData['kelas'] = $siswa['nama_kelas'] ?? '';
                $userData['foto'] = $siswa['foto'] ?? null;
            }
        }

        return $userData;
    }

    /**
     * GET /api/auth/me
     */
    private function me()
    {
        $payload = \Auth::requireAuth();

        $this->db->query("SELECT * FROM users WHERE id = :id LIMIT 1");
        $this->db->bind(':id', $payload['user_id']);
        $user = $this->db->single();

        if (!$user) {
            \Response::notFound('User not found');
        }

        $userData = $this->getUserDetails($user);

        \Response::success($userData);
    }

    /**
     * POST /api/auth/logout
     */
    private function logout()
    {
        // Just verify token exists (for logging purposes)
        $payload = \Auth::requireAuth();

        // In a real app, you might want to blacklist the token
        // For now, client should just delete the token

        \Response::success(null, 'Logout berhasil');
    }

    /**
     * POST /api/auth/refresh
     */
    private function refresh()
    {
        $payload = \Auth::requireAuth();

        // Generate new token
        $token = \Auth::generateToken(
            $payload['user_id'],
            $payload['role'],
            $payload['id_ref']
        );

        \Response::success(['token' => $token], 'Token refreshed');
    }

    /**
     * POST /api/auth/google
     * Handle Google Sign-In authentication
     */
    private function googleAuth()
    {
        $input = json_decode(file_get_contents('php://input'), true);

        $googleId = $input['google_id'] ?? '';
        $email = $input['email'] ?? '';
        $name = $input['name'] ?? '';
        $photoUrl = $input['photo_url'] ?? '';

        if (empty($googleId) || empty($email)) {
            \Response::validationError([
                'google_id' => empty($googleId) ? 'Google ID is required' : null,
                'email' => empty($email) ? 'Email is required' : null
            ]);
        }

        // Check if user exists with this google_id
        $this->db->query("SELECT * FROM users WHERE google_id = :google_id LIMIT 1");
        $this->db->bind(':google_id', $googleId);
        $user = $this->db->single();

        if (!$user) {
            // Check if user exists with this email (link existing account)
            $this->db->query("SELECT * FROM users WHERE email = :email LIMIT 1");
            $this->db->bind(':email', $email);
            $user = $this->db->single();

            if ($user) {
                // Link Google account to existing user
                $this->db->query("UPDATE users SET google_id = :google_id, avatar = :avatar WHERE id = :id");
                $this->db->bind(':google_id', $googleId);
                $this->db->bind(':avatar', $photoUrl);
                $this->db->bind(':id', $user['id']);
                $this->db->execute();
            } else {
                // User not found - they need to have an account first
                // Mobile app users must be pre-registered (guru/siswa/ortu)
                \Response::error('Email tidak terdaftar. Silakan hubungi administrator untuk mendaftarkan akun Anda.', 404);
            }
        }

        // Reload user data
        $this->db->query("SELECT * FROM users WHERE id = :id LIMIT 1");
        $this->db->bind(':id', $user['id']);
        $user = $this->db->single();

        // Check if active
        if (isset($user['is_active']) && $user['is_active'] == 0) {
            \Response::error('Akun tidak aktif', 401);
        }

        // Get user details based on role
        $userData = $this->getUserDetails($user);
        $userData['avatar'] = $photoUrl;

        // Generate token
        $token = \Auth::generateToken($user['id'], $user['role'], $user['id_ref'] ?? null);

        \Response::success([
            'token' => $token,
            'user' => $userData
        ], 'Login dengan Google berhasil');
    }
}
