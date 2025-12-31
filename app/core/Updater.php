<?php

/**
 * Updater Class
 * Handles application auto-update functionality
 * 
 * @author System
 * @version 1.0.0
 */
class Updater
{
    private $versionFile;
    private $backupDir;
    private $tempDir;
    private $excludeFiles = [
        'config.php',
        '.env',
        'uploads',
        'backups',
        'tmp',
        'version.json' // Will be updated separately
    ];

    // GitHub repository info (Update with your repo)
    private $githubUser = 'USERNAME';
    private $githubRepo = 'REPOSITORY';
    private $githubBranch = 'main';

    public function __construct()
    {
        $this->versionFile = APPROOT . '/version.json';
        $this->backupDir = APPROOT . '/backups/';
        $this->tempDir = APPROOT . '/tmp/updates/';

        // Create directories if not exist
        if (!is_dir($this->backupDir)) {
            mkdir($this->backupDir, 0755, true);
        }
        if (!is_dir($this->tempDir)) {
            mkdir($this->tempDir, 0755, true);
        }
    }

    /**
     * Get current application version
     */
    public function getCurrentVersion()
    {
        if (file_exists($this->versionFile)) {
            $data = json_decode(file_get_contents($this->versionFile), true);
            return $data['version'] ?? '0.0.0';
        }
        return '0.0.0';
    }

    /**
     * Get full version info
     */
    public function getVersionInfo()
    {
        if (file_exists($this->versionFile)) {
            return json_decode(file_get_contents($this->versionFile), true);
        }
        return [
            'version' => '0.0.0',
            'build' => 0,
            'release_date' => 'Unknown',
            'app_name' => 'School App'
        ];
    }

    /**
     * Check for available updates from GitHub
     */
    public function checkForUpdates()
    {
        $result = [
            'success' => false,
            'current' => $this->getCurrentVersion(),
            'latest' => null,
            'has_update' => false,
            'download_url' => null,
            'changelog' => '',
            'error' => null
        ];

        try {
            // GitHub API URL for latest release
            $apiUrl = "https://api.github.com/repos/{$this->githubUser}/{$this->githubRepo}/releases/latest";

            $response = $this->httpGet($apiUrl);

            if (!$response) {
                // Fallback: Try to get from tags
                $tagsUrl = "https://api.github.com/repos/{$this->githubUser}/{$this->githubRepo}/tags";
                $tagsResponse = $this->httpGet($tagsUrl);

                if ($tagsResponse) {
                    $tags = json_decode($tagsResponse, true);
                    if (!empty($tags)) {
                        $latestTag = $tags[0];
                        $result['latest'] = ltrim($latestTag['name'], 'v');
                        $result['download_url'] = "https://github.com/{$this->githubUser}/{$this->githubRepo}/archive/refs/tags/{$latestTag['name']}.zip";
                        $result['has_update'] = version_compare($result['latest'], $result['current'], '>');
                        $result['success'] = true;
                    }
                }
            } else {
                $release = json_decode($response, true);

                if (isset($release['tag_name'])) {
                    $result['latest'] = ltrim($release['tag_name'], 'v');
                    $result['download_url'] = $release['zipball_url'] ?? null;
                    $result['changelog'] = $release['body'] ?? '';
                    $result['has_update'] = version_compare($result['latest'], $result['current'], '>');
                    $result['success'] = true;
                }
            }

            if (!$result['success']) {
                $result['error'] = 'Tidak dapat mengambil informasi update dari server.';
            }

        } catch (Exception $e) {
            $result['error'] = 'Error: ' . $e->getMessage();
        }

        return $result;
    }

    /**
     * Download update package
     */
    public function downloadUpdate($url, $version)
    {
        $zipPath = $this->tempDir . "update_v{$version}.zip";

        // Clean temp directory first
        $this->cleanTempDir();

        $content = $this->httpGet($url, true);

        if ($content === false) {
            throw new Exception('Gagal mengunduh file update.');
        }

        if (file_put_contents($zipPath, $content) === false) {
            throw new Exception('Gagal menyimpan file update.');
        }

        return $zipPath;
    }

    /**
     * Create backup of current application
     */
    public function createBackup()
    {
        $currentVersion = $this->getCurrentVersion();
        $timestamp = date('Ymd_His');
        $backupName = "backup_v{$currentVersion}_{$timestamp}";
        $backupPath = $this->backupDir . $backupName;

        if (!mkdir($backupPath, 0755, true)) {
            throw new Exception('Gagal membuat folder backup.');
        }

        // Copy important directories
        $dirsToBackup = ['app', 'public'];

        foreach ($dirsToBackup as $dir) {
            $source = APPROOT . '/' . $dir;
            $dest = $backupPath . '/' . $dir;

            if (is_dir($source)) {
                $this->recursiveCopy($source, $dest);
            }
        }

        // Copy version.json
        if (file_exists($this->versionFile)) {
            copy($this->versionFile, $backupPath . '/version.json');
        }

        return $backupPath;
    }

    /**
     * Install update from downloaded ZIP
     */
    public function installUpdate($zipPath, $newVersion)
    {
        if (!file_exists($zipPath)) {
            throw new Exception('File update tidak ditemukan.');
        }

        if (!class_exists('ZipArchive')) {
            throw new Exception('Ekstensi PHP zip tidak tersedia. Aktifkan di aaPanel.');
        }

        $extractPath = $this->tempDir . 'extracted/';

        // Clean extract directory
        if (is_dir($extractPath)) {
            $this->deleteDir($extractPath);
        }
        mkdir($extractPath, 0755, true);

        // Extract ZIP
        $zip = new ZipArchive();
        $result = $zip->open($zipPath);

        if ($result !== true) {
            throw new Exception('Gagal membuka file ZIP. Error code: ' . $result);
        }

        $zip->extractTo($extractPath);
        $zip->close();

        // Find extracted folder (GitHub adds folder with repo name)
        $folders = glob($extractPath . '*', GLOB_ONLYDIR);
        if (empty($folders)) {
            throw new Exception('Struktur update tidak valid.');
        }

        $sourceDir = $folders[0];

        // Copy files to application root (excluding protected files)
        $this->copyUpdateFiles($sourceDir, APPROOT);

        // Update version.json
        $this->updateVersionFile($newVersion);

        // Cleanup
        $this->cleanTempDir();

        return true;
    }

    /**
     * Restore from backup
     */
    public function restoreFromBackup($backupPath)
    {
        if (!is_dir($backupPath)) {
            throw new Exception('Folder backup tidak ditemukan.');
        }

        // Restore directories
        $dirsToRestore = ['app', 'public'];

        foreach ($dirsToRestore as $dir) {
            $source = $backupPath . '/' . $dir;
            $dest = APPROOT . '/' . $dir;

            if (is_dir($source)) {
                // Remove current
                if (is_dir($dest)) {
                    $this->deleteDir($dest);
                }
                // Restore from backup
                $this->recursiveCopy($source, $dest);
            }
        }

        // Restore version.json
        $backupVersion = $backupPath . '/version.json';
        if (file_exists($backupVersion)) {
            copy($backupVersion, $this->versionFile);
        }

        return true;
    }

    /**
     * Get list of available backups
     */
    public function getBackups()
    {
        $backups = [];

        if (is_dir($this->backupDir)) {
            $dirs = glob($this->backupDir . 'backup_*', GLOB_ONLYDIR);

            foreach ($dirs as $dir) {
                $name = basename($dir);
                $backups[] = [
                    'name' => $name,
                    'path' => $dir,
                    'date' => filemtime($dir),
                    'size' => $this->getDirSize($dir)
                ];
            }

            // Sort by date descending
            usort($backups, function ($a, $b) {
                return $b['date'] - $a['date'];
            });
        }

        return $backups;
    }

    /**
     * Delete old backups (keep last N)
     */
    public function cleanOldBackups($keep = 3)
    {
        $backups = $this->getBackups();

        if (count($backups) > $keep) {
            $toDelete = array_slice($backups, $keep);

            foreach ($toDelete as $backup) {
                $this->deleteDir($backup['path']);
            }
        }
    }

    // ==================== HELPER METHODS ====================

    /**
     * HTTP GET request using cURL
     */
    private function httpGet($url, $followRedirect = false)
    {
        if (!function_exists('curl_init')) {
            throw new Exception('Ekstensi PHP cURL tidak tersedia.');
        }

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 120,
            CURLOPT_USERAGENT => 'SchoolApp-Updater/1.0',
            CURLOPT_HTTPHEADER => [
                'Accept: application/vnd.github.v3+json'
            ],
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_FOLLOWLOCATION => $followRedirect
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new Exception('cURL Error: ' . $error);
        }

        if ($httpCode >= 400) {
            return false;
        }

        return $response;
    }

    /**
     * Recursively copy directory
     */
    private function recursiveCopy($source, $dest)
    {
        if (!is_dir($dest)) {
            mkdir($dest, 0755, true);
        }

        $dir = opendir($source);

        while (($file = readdir($dir)) !== false) {
            if ($file == '.' || $file == '..') {
                continue;
            }

            $sourcePath = $source . '/' . $file;
            $destPath = $dest . '/' . $file;

            if (is_dir($sourcePath)) {
                $this->recursiveCopy($sourcePath, $destPath);
            } else {
                copy($sourcePath, $destPath);
            }
        }

        closedir($dir);
    }

    /**
     * Copy update files while respecting excludes
     */
    private function copyUpdateFiles($source, $dest)
    {
        if (!is_dir($source)) {
            return;
        }

        $dir = opendir($source);

        while (($file = readdir($dir)) !== false) {
            if ($file == '.' || $file == '..') {
                continue;
            }

            // Check if file/folder should be excluded
            if (in_array($file, $this->excludeFiles)) {
                continue;
            }

            $sourcePath = $source . '/' . $file;
            $destPath = $dest . '/' . $file;

            if (is_dir($sourcePath)) {
                if (!is_dir($destPath)) {
                    mkdir($destPath, 0755, true);
                }
                $this->copyUpdateFiles($sourcePath, $destPath);
            } else {
                copy($sourcePath, $destPath);
            }
        }

        closedir($dir);
    }

    /**
     * Recursively delete directory
     */
    private function deleteDir($dir)
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);

        foreach ($files as $file) {
            $path = $dir . '/' . $file;

            if (is_dir($path)) {
                $this->deleteDir($path);
            } else {
                unlink($path);
            }
        }

        rmdir($dir);
    }

    /**
     * Clean temporary directory
     */
    private function cleanTempDir()
    {
        if (is_dir($this->tempDir)) {
            $this->deleteDir($this->tempDir);
            mkdir($this->tempDir, 0755, true);
        }
    }

    /**
     * Update version.json file
     */
    private function updateVersionFile($newVersion)
    {
        $data = $this->getVersionInfo();
        $data['version'] = $newVersion;
        $data['build'] = intval(str_replace('.', '', $newVersion));
        $data['release_date'] = date('Y-m-d');

        file_put_contents($this->versionFile, json_encode($data, JSON_PRETTY_PRINT));
    }

    /**
     * Get directory size in bytes
     */
    private function getDirSize($dir)
    {
        $size = 0;

        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir)) as $file) {
            if ($file->isFile()) {
                $size += $file->getSize();
            }
        }

        return $size;
    }

    /**
     * Format bytes to human readable
     */
    public function formatBytes($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Check system requirements
     */
    public function checkRequirements()
    {
        return [
            'curl' => [
                'name' => 'cURL Extension',
                'status' => function_exists('curl_init'),
                'required' => true
            ],
            'zip' => [
                'name' => 'ZIP Extension',
                'status' => class_exists('ZipArchive'),
                'required' => true
            ],
            'writable_app' => [
                'name' => 'App folder writable',
                'status' => is_writable(APPROOT . '/app'),
                'required' => true
            ],
            'writable_public' => [
                'name' => 'Public folder writable',
                'status' => is_writable(APPROOT . '/public'),
                'required' => true
            ],
            'writable_root' => [
                'name' => 'Root folder writable',
                'status' => is_writable(APPROOT),
                'required' => true
            ]
        ];
    }

    /**
     * Check if all requirements are met
     */
    public function requirementsMet()
    {
        $reqs = $this->checkRequirements();

        foreach ($reqs as $req) {
            if ($req['required'] && !$req['status']) {
                return false;
            }
        }

        return true;
    }

    /**
     * Set GitHub repository info
     */
    public function setRepository($user, $repo, $branch = 'main')
    {
        $this->githubUser = $user;
        $this->githubRepo = $repo;
        $this->githubBranch = $branch;
    }
}
