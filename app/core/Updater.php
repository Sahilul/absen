<?php

/**
 * Updater Class - Auto-Update System
 * GitHub: Sahilul/absen
 */
class Updater
{
    private $versionFile;
    private $backupDir;
    private $tempDir;
    private $excludeFiles = ['config.php', 'config', '.env', 'uploads', 'backups', 'tmp', 'version.json', '.git', '.gitignore'];

    private $githubUser = 'Sahilul';
    private $githubRepo = 'absen';
    private $githubBranch = 'main';

    public function __construct()
    {
        $this->versionFile = APPROOT . '/version.json';
        $this->backupDir = APPROOT . '/backups/';
        $this->tempDir = APPROOT . '/tmp/updates/';

        if (!is_dir($this->backupDir))
            mkdir($this->backupDir, 0755, true);
        if (!is_dir($this->tempDir))
            mkdir($this->tempDir, 0755, true);
    }

    public function getCurrentVersion()
    {
        if (file_exists($this->versionFile)) {
            $data = json_decode(file_get_contents($this->versionFile), true);
            return $data['version'] ?? '0.0.0';
        }
        return '0.0.0';
    }

    public function getVersionInfo()
    {
        if (file_exists($this->versionFile)) {
            return json_decode(file_get_contents($this->versionFile), true);
        }
        return ['version' => '0.0.0', 'build' => 0, 'release_date' => 'Unknown', 'app_name' => 'School App'];
    }

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
            // Method 1 (Fastest): version.json from branch - most repos have this
            $rawResponse = $this->httpGet("https://raw.githubusercontent.com/{$this->githubUser}/{$this->githubRepo}/{$this->githubBranch}/version.json");
            if ($rawResponse) {
                $remoteVersion = json_decode($rawResponse, true);
                if (isset($remoteVersion['version'])) {
                    $result['latest'] = $remoteVersion['version'];
                    $result['download_url'] = "https://github.com/{$this->githubUser}/{$this->githubRepo}/archive/refs/heads/{$this->githubBranch}.zip";
                    $result['has_update'] = version_compare($result['latest'], $result['current'], '>');
                    $result['success'] = true;

                    // Fetch changelog.json for detailed changelog
                    $changelogResponse = $this->httpGet("https://raw.githubusercontent.com/{$this->githubUser}/{$this->githubRepo}/{$this->githubBranch}/changelog.json");
                    if ($changelogResponse) {
                        $changelogData = json_decode($changelogResponse, true);
                        if (isset($changelogData['versions'])) {
                            foreach ($changelogData['versions'] as $ver) {
                                if ($ver['version'] === $result['latest']) {
                                    $changes = [];
                                    foreach ($ver['changes'] ?? [] as $change) {
                                        $changes[] = $change['description'] ?? '';
                                    }
                                    $result['changelog'] = implode("\n", $changes);
                                    $result['changelog_data'] = $ver;
                                    break;
                                }
                            }
                        }
                    }

                    if (empty($result['changelog'])) {
                        // Fallback: Fetch latest commit message
                        $latestCommitResponse = $this->httpGet("https://api.github.com/repos/{$this->githubUser}/{$this->githubRepo}/commits/{$this->githubBranch}");
                        if ($latestCommitResponse) {
                            $commitData = json_decode($latestCommitResponse, true);
                            $message = $commitData['commit']['message'] ?? '';
                            // Clean up message
                            $message = preg_replace('/^v\d+\.\d+\.\d+:\s*/', '', $message);
                            $result['changelog'] = $message;
                        }
                    }

                    if (empty($result['changelog'])) {
                        $result['changelog'] = 'Update ke versi ' . $result['latest'];
                    }

                    return $result;
                }
            }



            // Method 3: GitHub Tags (fallback)
            $tagsResponse = $this->httpGet("https://api.github.com/repos/{$this->githubUser}/{$this->githubRepo}/tags");
            if ($tagsResponse) {
                $tags = json_decode($tagsResponse, true);
                if (!empty($tags) && is_array($tags)) {
                    $result['latest'] = ltrim($tags[0]['name'], 'v');
                    $result['download_url'] = "https://github.com/{$this->githubUser}/{$this->githubRepo}/archive/refs/tags/{$tags[0]['name']}.zip";
                    $result['has_update'] = version_compare($result['latest'], $result['current'], '>');

                    // Fallback to commit message if no changelog found yet
                    if (empty($result['changelog'])) {
                        // Get commit message for this tag
                        $commitSha = $tags[0]['commit']['sha'] ?? null;
                        if ($commitSha) {
                            $commitResponse = $this->httpGet("https://api.github.com/repos/{$this->githubUser}/{$this->githubRepo}/commits/{$commitSha}");
                            if ($commitResponse) {
                                $commitData = json_decode($commitResponse, true);
                                $message = $commitData['commit']['message'] ?? '';
                                // Clean up message (remove vX.X.X prefix if exists)
                                $message = preg_replace('/^v\d+\.\d+\.\d+:\s*/', '', $message);
                                $result['changelog'] = $message;
                            }
                        }

                        // If still empty, try latest commit on branch
                        if (empty($result['changelog'])) {
                            $latestCommitResponse = $this->httpGet("https://api.github.com/repos/{$this->githubUser}/{$this->githubRepo}/commits/{$this->githubBranch}");
                            if ($latestCommitResponse) {
                                $commitData = json_decode($latestCommitResponse, true);
                                $message = $commitData['commit']['message'] ?? '';
                                $message = preg_replace('/^v\d+\.\d+\.\d+:\s*/', '', $message);
                                $result['changelog'] = $message;
                            }
                        }
                    }

                    $result['success'] = true;
                    return $result;
                }
            }

            $result['error'] = 'Repository tidak memiliki release, tags, atau version.json.';
        } catch (Exception $e) {
            $result['error'] = 'Error: ' . $e->getMessage();
        }
        return $result;
    }

    public function downloadUpdate($url, $version)
    {
        $zipPath = $this->tempDir . "update_v{$version}.zip";
        $this->cleanTempDir();
        $content = $this->httpGet($url, true);
        if ($content === false)
            throw new Exception('Gagal mengunduh file update.');
        if (file_put_contents($zipPath, $content) === false)
            throw new Exception('Gagal menyimpan file update.');
        return $zipPath;
    }

    public function createBackup()
    {
        $backupName = "backup_v{$this->getCurrentVersion()}_" . date('Ymd_His');
        $backupPath = $this->backupDir . $backupName;
        if (!mkdir($backupPath, 0755, true))
            throw new Exception('Gagal membuat folder backup.');

        foreach (['app', 'public'] as $dir) {
            $source = APPROOT . '/' . $dir;
            if (is_dir($source))
                $this->recursiveCopy($source, $backupPath . '/' . $dir);
        }
        if (file_exists($this->versionFile))
            copy($this->versionFile, $backupPath . '/version.json');
        return $backupPath;
    }

    public function installUpdate($zipPath, $newVersion)
    {
        if (!file_exists($zipPath))
            throw new Exception('File update tidak ditemukan.');
        if (!class_exists('ZipArchive'))
            throw new Exception('Ekstensi PHP zip tidak tersedia.');

        $extractPath = $this->tempDir . 'extracted/';
        if (is_dir($extractPath))
            $this->deleteDir($extractPath);
        mkdir($extractPath, 0755, true);

        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== true)
            throw new Exception('Gagal membuka file ZIP.');
        $zip->extractTo($extractPath);
        $zip->close();

        $folders = glob($extractPath . '*', GLOB_ONLYDIR);
        if (empty($folders))
            throw new Exception('Struktur update tidak valid.');
        $sourceDir = $folders[0];

        $this->copyUpdateFiles($sourceDir, APPROOT);
        $this->runMigrations($sourceDir);
        $this->updateVersionFile($newVersion);
        $this->cleanTempDir();
        return true;
    }

    private function runMigrations($sourceDir)
    {
        $migrationsDir = $sourceDir . '/migrations';
        if (!is_dir($migrationsDir))
            return [];

        $sqlFiles = glob($migrationsDir . '/*.sql');
        if (empty($sqlFiles))
            return [];
        sort($sqlFiles);

        require_once APPROOT . '/app/core/Database.php';
        $db = new Database();
        $results = [];

        foreach ($sqlFiles as $sqlFile) {
            $sql = file_get_contents($sqlFile);
            if (empty(trim($sql)))
                continue;

            try {
                $statements = array_filter(array_map('trim', explode(';', $sql)), fn($s) => !empty($s));
                foreach ($statements as $stmt) {
                    $db->query($stmt);
                    $db->execute();
                }
                $results[] = ['file' => basename($sqlFile), 'status' => 'success'];
            } catch (Exception $e) {
                $results[] = ['file' => basename($sqlFile), 'status' => 'error', 'message' => $e->getMessage()];
            }
        }

        // Archive executed migrations
        $archiveDir = APPROOT . '/migrations/executed';
        if (!is_dir($archiveDir))
            mkdir($archiveDir, 0755, true);
        foreach ($sqlFiles as $f)
            copy($f, $archiveDir . '/' . date('Y-m-d_His') . '_' . basename($f));

        return $results;
    }

    public function restoreFromBackup($backupPath)
    {
        if (!is_dir($backupPath))
            throw new Exception('Folder backup tidak ditemukan.');
        foreach (['app', 'public'] as $dir) {
            $source = $backupPath . '/' . $dir;
            $dest = APPROOT . '/' . $dir;
            if (is_dir($source)) {
                if (is_dir($dest))
                    $this->deleteDir($dest);
                $this->recursiveCopy($source, $dest);
            }
        }
        if (file_exists($backupPath . '/version.json'))
            copy($backupPath . '/version.json', $this->versionFile);
        return true;
    }

    public function getBackups()
    {
        $backups = [];
        if (is_dir($this->backupDir)) {
            foreach (glob($this->backupDir . 'backup_*', GLOB_ONLYDIR) as $dir) {
                $backups[] = ['name' => basename($dir), 'path' => $dir, 'date' => filemtime($dir), 'size' => $this->getDirSize($dir)];
            }
            usort($backups, fn($a, $b) => $b['date'] - $a['date']);
        }
        return $backups;
    }

    public function cleanOldBackups($keep = 3)
    {
        $backups = $this->getBackups();
        if (count($backups) > $keep) {
            foreach (array_slice($backups, $keep) as $b)
                $this->deleteDir($b['path']);
        }
    }

    private function httpGet($url, $followRedirect = false)
    {
        if (!function_exists('curl_init'))
            throw new Exception('cURL tidak tersedia.');
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_USERAGENT => 'SchoolApp-Updater/1.0',
            CURLOPT_HTTPHEADER => ['Accept: application/vnd.github.v3+json'],
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_FOLLOWLOCATION => $followRedirect
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        if ($error)
            throw new Exception('cURL Error: ' . $error);
        return $httpCode >= 400 ? false : $response;
    }

    private function recursiveCopy($src, $dst)
    {
        if (!is_dir($dst))
            mkdir($dst, 0755, true);
        $dir = opendir($src);
        while (($file = readdir($dir)) !== false) {
            if ($file == '.' || $file == '..')
                continue;
            is_dir("$src/$file") ? $this->recursiveCopy("$src/$file", "$dst/$file") : copy("$src/$file", "$dst/$file");
        }
        closedir($dir);
    }

    private function copyUpdateFiles($src, $dst)
    {
        if (!is_dir($src))
            return;
        $dir = opendir($src);
        while (($file = readdir($dir)) !== false) {
            if ($file == '.' || $file == '..' || in_array($file, $this->excludeFiles))
                continue;
            $srcPath = "$src/$file";
            $dstPath = "$dst/$file";
            if (is_dir($srcPath)) {
                if (!is_dir($dstPath))
                    mkdir($dstPath, 0755, true);
                $this->copyUpdateFiles($srcPath, $dstPath);
            } else
                copy($srcPath, $dstPath);
        }
        closedir($dir);
    }

    private function deleteDir($dir)
    {
        if (!is_dir($dir))
            return;
        foreach (array_diff(scandir($dir), ['.', '..']) as $file) {
            is_dir("$dir/$file") ? $this->deleteDir("$dir/$file") : unlink("$dir/$file");
        }
        rmdir($dir);
    }

    private function cleanTempDir()
    {
        if (is_dir($this->tempDir)) {
            $this->deleteDir($this->tempDir);
            mkdir($this->tempDir, 0755, true);
        }
    }

    private function updateVersionFile($newVersion)
    {
        $data = $this->getVersionInfo();
        $data['version'] = $newVersion;
        $data['build'] = intval(str_replace('.', '', $newVersion));
        $data['release_date'] = date('Y-m-d');
        file_put_contents($this->versionFile, json_encode($data, JSON_PRETTY_PRINT));
    }

    private function getDirSize($dir)
    {
        $size = 0;
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir)) as $file) {
            if ($file->isFile())
                $size += $file->getSize();
        }
        return $size;
    }

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

    public function checkRequirements()
    {
        return [
            'curl' => ['name' => 'cURL Extension', 'status' => function_exists('curl_init'), 'required' => true],
            'zip' => ['name' => 'ZIP Extension', 'status' => class_exists('ZipArchive'), 'required' => true],
            'writable_app' => ['name' => 'App folder writable', 'status' => is_writable(APPROOT . '/app'), 'required' => true],
            'writable_public' => ['name' => 'Public folder writable', 'status' => is_writable(APPROOT . '/public'), 'required' => true],
            'writable_root' => ['name' => 'Root folder writable', 'status' => is_writable(APPROOT), 'required' => true]
        ];
    }

    public function requirementsMet()
    {
        foreach ($this->checkRequirements() as $r) {
            if ($r['required'] && !$r['status'])
                return false;
        }
        return true;
    }
}
