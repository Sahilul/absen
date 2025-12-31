<?php

/**
 * File: app/core/GoogleDrive.php
 * Helper class untuk integrasi Google Drive API
 * 
 * Fitur:
 * - OAuth flow untuk mendapatkan refresh token
 * - Upload file ke Google Drive
 * - Set permission file menjadi public
 * - Membuat folder di Drive
 */

class GoogleDrive
{
    private $clientId;
    private $clientSecret;
    private $refreshToken;
    private $accessToken;
    private $folderId;

    const AUTH_URL = 'https://accounts.google.com/o/oauth2/v2/auth';
    const TOKEN_URL = 'https://oauth2.googleapis.com/token';
    const DRIVE_UPLOAD_URL = 'https://www.googleapis.com/upload/drive/v3/files';
    const DRIVE_FILES_URL = 'https://www.googleapis.com/drive/v3/files';
    const SCOPES = 'https://www.googleapis.com/auth/drive.file email';

    public function __construct()
    {
        // Load dari config
        $this->clientId = defined('GOOGLE_CLIENT_ID') ? GOOGLE_CLIENT_ID : '';
        $this->clientSecret = defined('GOOGLE_CLIENT_SECRET') ? GOOGLE_CLIENT_SECRET : '';

        // Load refresh token dan folder ID dari database
        $this->loadSettings();
    }

    /**
     * Load settings dari database
     */
    private function loadSettings()
    {
        try {
            require_once APPROOT . '/app/core/Database.php';
            $db = new Database();
            $db->query("SELECT google_refresh_token, google_drive_folder_id FROM pengaturan_aplikasi LIMIT 1");
            $result = $db->single();

            if ($result) {
                $this->refreshToken = $result['google_refresh_token'] ?? null;
                $this->folderId = $result['google_drive_folder_id'] ?? null;
            }
        } catch (Exception $e) {
            error_log("GoogleDrive - loadSettings error: " . $e->getMessage());
        }
    }

    /**
     * Cek apakah Drive sudah terhubung
     */
    public function isConnected()
    {
        return !empty($this->refreshToken);
    }

    /**
     * Get user info (email) dari akun yang terhubung
     */
    public function getUserInfo()
    {
        try {
            $this->ensureAccessToken();

            $url = 'https://www.googleapis.com/oauth2/v2/userinfo';

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $this->accessToken
                ]
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200) {
                return json_decode($response, true);
            }

            return null;
        } catch (Exception $e) {
            error_log("GoogleDrive - getUserInfo error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get URL untuk OAuth consent
     */
    public function getAuthUrl($redirectUri)
    {
        $params = [
            'client_id' => $this->clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => self::SCOPES,
            'access_type' => 'offline',
            'prompt' => 'consent'
        ];

        return self::AUTH_URL . '?' . http_build_query($params);
    }

    /**
     * Exchange auth code untuk tokens
     */
    public function exchangeCodeForTokens($code, $redirectUri)
    {
        $data = [
            'code' => $code,
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri' => $redirectUri,
            'grant_type' => 'authorization_code'
        ];

        $response = $this->httpPost(self::TOKEN_URL, $data);

        if (isset($response['refresh_token'])) {
            $this->saveRefreshToken($response['refresh_token']);
            $this->refreshToken = $response['refresh_token'];
        }

        if (isset($response['access_token'])) {
            $this->accessToken = $response['access_token'];
        }

        return $response;
    }

    /**
     * Simpan refresh token ke database
     */
    private function saveRefreshToken($token)
    {
        try {
            $db = new Database();
            $db->query("UPDATE pengaturan_aplikasi SET google_refresh_token = :token");
            $db->bind(':token', $token);
            $db->execute();
        } catch (Exception $e) {
            error_log("GoogleDrive - saveRefreshToken error: " . $e->getMessage());
        }
    }

    /**
     * Simpan folder ID ke database
     */
    public function saveFolderId($folderId)
    {
        try {
            $db = new Database();
            $db->query("UPDATE pengaturan_aplikasi SET google_drive_folder_id = :folder_id");
            $db->bind(':folder_id', $folderId);
            $db->execute();
            $this->folderId = $folderId;
        } catch (Exception $e) {
            error_log("GoogleDrive - saveFolderId error: " . $e->getMessage());
        }
    }

    /**
     * Simpan email akun yang terhubung ke database
     */
    public function saveEmail($email)
    {
        try {
            $db = new Database();
            $db->query("UPDATE pengaturan_aplikasi SET google_drive_email = :email");
            $db->bind(':email', $email);
            $db->execute();
        } catch (Exception $e) {
            error_log("GoogleDrive - saveEmail error: " . $e->getMessage());
        }
    }

    /**
     * Refresh access token menggunakan refresh token
     */
    public function refreshAccessToken()
    {
        if (empty($this->refreshToken)) {
            throw new Exception('No refresh token available');
        }

        $data = [
            'refresh_token' => $this->refreshToken,
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'grant_type' => 'refresh_token'
        ];

        $response = $this->httpPost(self::TOKEN_URL, $data);

        if (isset($response['access_token'])) {
            $this->accessToken = $response['access_token'];
            return $this->accessToken;
        }

        throw new Exception('Failed to refresh access token: ' . json_encode($response));
    }

    /**
     * Ensure we have a valid access token
     */
    private function ensureAccessToken()
    {
        if (empty($this->accessToken)) {
            $this->refreshAccessToken();
        }
    }

    /**
     * Upload file ke Google Drive
     * 
     * @param string $filePath Path ke file lokal
     * @param string $fileName Nama file di Drive
     * @param string|null $parentFolderId Folder ID parent (opsional)
     * @return array|false File info atau false jika gagal
     */
    public function uploadFile($filePath, $fileName, $parentFolderId = null)
    {
        try {
            $this->ensureAccessToken();

            // Use default folder if not specified
            if (!$parentFolderId) {
                $parentFolderId = $this->folderId;
            }

            // Prepare metadata
            $metadata = [
                'name' => $fileName
            ];

            if ($parentFolderId) {
                $metadata['parents'] = [$parentFolderId];
            }

            // Determine MIME type
            $mimeType = $this->getMimeType($filePath);

            // Create multipart request
            $boundary = '-------' . uniqid();
            $delimiter = "\r\n--" . $boundary . "\r\n";
            $closeDelimiter = "\r\n--" . $boundary . "--";

            $fileContent = file_get_contents($filePath);

            $postBody = $delimiter;
            $postBody .= "Content-Type: application/json; charset=UTF-8\r\n\r\n";
            $postBody .= json_encode($metadata);
            $postBody .= $delimiter;
            $postBody .= "Content-Type: " . $mimeType . "\r\n";
            $postBody .= "Content-Transfer-Encoding: base64\r\n\r\n";
            $postBody .= base64_encode($fileContent);
            $postBody .= $closeDelimiter;

            $url = self::DRIVE_UPLOAD_URL . '?uploadType=multipart';

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $postBody,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $this->accessToken,
                    'Content-Type: multipart/related; boundary=' . $boundary,
                    'Content-Length: ' . strlen($postBody)
                ]
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200) {
                $result = json_decode($response, true);
                error_log("GoogleDrive - File uploaded: " . $result['id']);
                return $result;
            }

            error_log("GoogleDrive - Upload failed: " . $response);
            return false;

        } catch (Exception $e) {
            error_log("GoogleDrive - uploadFile error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Set file permission menjadi public (anyone can view)
     */
    public function setPublic($fileId)
    {
        try {
            $this->ensureAccessToken();

            $url = self::DRIVE_FILES_URL . '/' . $fileId . '/permissions';

            $data = [
                'role' => 'reader',
                'type' => 'anyone'
            ];

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($data),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $this->accessToken,
                    'Content-Type: application/json'
                ]
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            return $httpCode === 200;

        } catch (Exception $e) {
            error_log("GoogleDrive - setPublic error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Hapus file dari Drive
     */
    public function deleteFile($fileId)
    {
        try {
            $this->ensureAccessToken();

            $url = self::DRIVE_FILES_URL . '/' . $fileId;

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_CUSTOMREQUEST => 'DELETE',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $this->accessToken
                ]
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            return $httpCode === 204 || $httpCode === 200;

        } catch (Exception $e) {
            error_log("GoogleDrive - deleteFile error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get public URL untuk file
     */
    public function getPublicUrl($fileId)
    {
        return 'https://drive.google.com/uc?id=' . $fileId;
    }

    /**
     * Get view URL untuk file (embed-able)
     */
    public function getViewUrl($fileId)
    {
        return 'https://drive.google.com/file/d/' . $fileId . '/view';
    }

    /**
     * Create folder di Drive
     */
    public function createFolder($folderName, $parentFolderId = null)
    {
        try {
            $this->ensureAccessToken();

            $metadata = [
                'name' => $folderName,
                'mimeType' => 'application/vnd.google-apps.folder'
            ];

            if ($parentFolderId) {
                $metadata['parents'] = [$parentFolderId];
            }

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => self::DRIVE_FILES_URL,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($metadata),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $this->accessToken,
                    'Content-Type: application/json'
                ]
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200) {
                return json_decode($response, true);
            }

            return false;

        } catch (Exception $e) {
            error_log("GoogleDrive - createFolder error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Find folder by name
     */
    public function findFolder($folderName, $parentFolderId = null)
    {
        try {
            $this->ensureAccessToken();

            $query = "name = '" . addslashes($folderName) . "' and mimeType = 'application/vnd.google-apps.folder' and trashed = false";
            if ($parentFolderId) {
                $query .= " and '" . $parentFolderId . "' in parents";
            }

            $url = self::DRIVE_FILES_URL . '?q=' . urlencode($query);

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $this->accessToken
                ]
            ]);

            $response = curl_exec($ch);
            curl_close($ch);

            $result = json_decode($response, true);

            if (!empty($result['files'])) {
                return $result['files'][0];
            }

            return null;

        } catch (Exception $e) {
            error_log("GoogleDrive - findFolder error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Find or create folder
     */
    public function findOrCreateFolder($folderName, $parentFolderId = null)
    {
        $folder = $this->findFolder($folderName, $parentFolderId);

        if ($folder) {
            return $folder;
        }

        return $this->createFolder($folderName, $parentFolderId);
    }

    /**
     * HTTP POST helper
     */
    private function httpPost($url, $data)
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($data),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }

    /**
     * Get MIME type dari file
     */
    private function getMimeType($filePath)
    {
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        $mimeTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ];

        return $mimeTypes[$ext] ?? 'application/octet-stream';
    }

    /**
     * Disconnect Google Drive (hapus refresh token)
     */
    public function disconnect()
    {
        try {
            $db = new Database();
            $db->query("UPDATE pengaturan_aplikasi SET google_refresh_token = NULL, google_drive_folder_id = NULL, google_drive_email = NULL");
            $db->execute();
            $this->refreshToken = null;
            $this->folderId = null;
            return true;
        } catch (Exception $e) {
            error_log("GoogleDrive - disconnect error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get folder ID saat ini
     */
    public function getFolderId()
    {
        return $this->folderId;
    }

    /**
     * Get or create student folder based on NISN
     * Folder structure: dokumen_siswa/{NISN}_{NAMA_SLUG}/
     * 
     * @param string $nisn Student NISN
     * @param string $namaLengkap Student full name
     * @return array|false Folder info or false on failure
     */
    public function getStudentFolder($nisn, $namaLengkap)
    {
        try {
            $this->ensureAccessToken();

            $folderName = $nisn . '_' . $this->slugify($namaLengkap);

            // Find or create main dokumen_siswa folder
            $parentFolder = $this->findOrCreateFolder('dokumen_siswa', $this->folderId);
            if (!$parentFolder) {
                error_log("GoogleDrive - Failed to create dokumen_siswa folder");
                return false;
            }

            // Find or create student subfolder
            $studentFolder = $this->findOrCreateFolder($folderName, $parentFolder['id']);
            if (!$studentFolder) {
                error_log("GoogleDrive - Failed to create student folder: " . $folderName);
                return false;
            }

            return $studentFolder;
        } catch (Exception $e) {
            error_log("GoogleDrive - getStudentFolder error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Replace file in folder (delete old, upload new)
     * 
     * @param string $folderId Parent folder ID
     * @param string $fileName Target filename
     * @param string $filePath Local file path to upload
     * @return array|false File info or false on failure
     */
    public function replaceFile($folderId, $fileName, $filePath)
    {
        try {
            // Find existing file with same name
            $existing = $this->findFileByName($fileName, $folderId);
            if ($existing) {
                // Delete old file
                $this->deleteFile($existing['id']);
                error_log("GoogleDrive - Deleted existing file: " . $existing['id']);
            }

            // Upload new file
            $result = $this->uploadFile($filePath, $fileName, $folderId);
            if ($result) {
                // Set public permission
                $this->setPublic($result['id']);
            }

            return $result;
        } catch (Exception $e) {
            error_log("GoogleDrive - replaceFile error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Find file by name in folder
     * 
     * @param string $fileName File name to search
     * @param string $folderId Parent folder ID
     * @return array|null File info or null if not found
     */
    public function findFileByName($fileName, $folderId)
    {
        try {
            $this->ensureAccessToken();

            $query = "name = '" . addslashes($fileName) . "' and '" . $folderId . "' in parents and trashed = false";
            $url = self::DRIVE_FILES_URL . '?q=' . urlencode($query);

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $this->accessToken
                ]
            ]);

            $response = curl_exec($ch);
            curl_close($ch);

            $result = json_decode($response, true);

            if (!empty($result['files'])) {
                return $result['files'][0];
            }

            return null;
        } catch (Exception $e) {
            error_log("GoogleDrive - findFileByName error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Convert text to URL-safe slug
     * 
     * @param string $text Text to slugify
     * @return string Slugified text
     */
    public function slugify($text)
    {
        // Convert to lowercase
        $text = strtolower($text);
        // Replace non-alphanumeric with underscore
        $text = preg_replace('/[^a-z0-9]+/', '_', $text);
        // Remove leading/trailing underscores
        $text = trim($text, '_');
        // Limit length
        return substr($text, 0, 50);
    }

    /**
     * Get local student folder path
     * Creates folder if not exists
     * 
     * @param string $nisn Student NISN
     * @param string $namaLengkap Student full name
     * @return string Local folder path
     */
    public function getLocalStudentFolder($nisn, $namaLengkap)
    {
        $folderName = $nisn . '_' . $this->slugify($namaLengkap);
        $basePath = APPROOT . '/../uploads/dokumen_siswa/';
        $fullPath = $basePath . $folderName . '/';

        // Create directories if not exist
        if (!is_dir($basePath)) {
            mkdir($basePath, 0755, true);
        }
        if (!is_dir($fullPath)) {
            mkdir($fullPath, 0755, true);
        }

        return $fullPath;
    }
}
