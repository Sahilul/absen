<?php

class UpdateController extends Controller
{
    private $updater;
    private $data = [];

    public function __construct()
    {
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            header('Location: ' . BASEURL);
            exit;
        }
        require_once APPROOT . '/app/core/Updater.php';
        $this->updater = new Updater();
    }

    public function index()
    {
        $this->data['judul'] = 'Pembaruan Aplikasi';
        $this->data['version_info'] = $this->updater->getVersionInfo();
        $this->data['requirements'] = $this->updater->checkRequirements();
        $this->data['requirements_met'] = $this->updater->requirementsMet();
        $this->data['backups'] = $this->updater->getBackups();

        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/update', $this->data);
        $this->view('templates/footer', $this->data);
    }

    public function check()
    {
        // Clear any previous output
        if (ob_get_level())
            ob_end_clean();

        // Suppress errors from being output
        error_reporting(0);
        ini_set('display_errors', 0);

        header('Content-Type: application/json');

        try {
            $result = $this->updater->checkForUpdates();
            echo json_encode($result);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage(),
                'current' => $this->updater->getCurrentVersion(),
                'has_update' => false
            ]);
        }
        exit;
    }

    public function install()
    {
        // Extend execution time and memory for large updates
        @set_time_limit(300); // 5 minutes
        @ini_set('memory_limit', '256M');

        // Clear any previous output
        if (ob_get_level())
            ob_end_clean();

        // Suppress errors from being output
        error_reporting(0);
        ini_set('display_errors', 0);

        header('Content-Type: application/json');

        // Flush headers immediately
        if (function_exists('fastcgi_finish_request')) {
            // For FastCGI - don't use this as we need to return response
        }

        try {
            if (!$this->updater->requirementsMet())
                throw new Exception('Sistem tidak memenuhi persyaratan.');
            $info = $this->updater->checkForUpdates();
            if (!$info['has_update'])
                throw new Exception('Tidak ada update tersedia.');

            $backupPath = $this->updater->createBackup();
            $zipPath = $this->updater->downloadUpdate($info['download_url'], $info['latest']);
            $this->updater->installUpdate($zipPath, $info['latest']);
            $this->updater->cleanOldBackups(3);

            echo json_encode(['success' => true, 'message' => 'Update ke v' . $info['latest'] . ' berhasil!', 'backup_path' => basename($backupPath)]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        } catch (Error $e) {
            echo json_encode(['success' => false, 'error' => 'Fatal error: ' . $e->getMessage()]);
        }
        exit;
    }

    public function restore()
    {
        header('Content-Type: application/json');
        $backupName = $_POST['backup'] ?? '';
        if (empty($backupName)) {
            echo json_encode(['success' => false, 'error' => 'Nama backup tidak valid.']);
            exit;
        }

        try {
            $this->updater->restoreFromBackup(APPROOT . '/backups/' . $backupName);
            echo json_encode(['success' => true, 'message' => 'Restore berhasil!']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    public function deleteBackup()
    {
        header('Content-Type: application/json');
        $backupName = $_POST['backup'] ?? '';
        $backupPath = APPROOT . '/backups/' . $backupName;

        if (is_dir($backupPath)) {
            $this->deleteDir($backupPath);
            echo json_encode(['success' => true, 'message' => 'Backup dihapus.']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Backup tidak ditemukan.']);
        }
        exit;
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

    public function dismissNotification()
    {
        $version = $_GET['version'] ?? '';
        if (!empty($version)) {
            $_SESSION['update_dismissed_version'] = $version;
        }

        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    }
}
