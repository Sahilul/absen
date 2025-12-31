<?php

/**
 * UpdateController
 * Handles application update UI and actions
 */
class UpdateController extends Controller
{
    private $updater;

    public function __construct()
    {
        parent::__construct();

        // Only admin can access
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            header('Location: ' . BASEURL);
            exit;
        }

        require_once APPROOT . '/app/core/Updater.php';
        $this->updater = new Updater();

        // Set your GitHub repository here
        // $this->updater->setRepository('your-username', 'your-repo', 'main');
    }

    /**
     * Main update page
     */
    public function index()
    {
        $this->data['judul'] = 'Pembaruan Aplikasi';

        // Get version info
        $this->data['version_info'] = $this->updater->getVersionInfo();

        // Check requirements
        $this->data['requirements'] = $this->updater->checkRequirements();
        $this->data['requirements_met'] = $this->updater->requirementsMet();

        // Get backups
        $this->data['backups'] = $this->updater->getBackups();

        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/update', $this->data);
        $this->view('templates/footer', $this->data);
    }

    /**
     * Check for updates (AJAX)
     */
    public function check()
    {
        header('Content-Type: application/json');

        try {
            $result = $this->updater->checkForUpdates();
            echo json_encode($result);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
        exit;
    }

    /**
     * Install update
     */
    public function install()
    {
        header('Content-Type: application/json');

        try {
            // Check requirements first
            if (!$this->updater->requirementsMet()) {
                throw new Exception('Sistem tidak memenuhi persyaratan untuk update.');
            }

            // Check for updates
            $updateInfo = $this->updater->checkForUpdates();

            if (!$updateInfo['has_update']) {
                throw new Exception('Tidak ada update yang tersedia.');
            }

            if (!$updateInfo['download_url']) {
                throw new Exception('URL download tidak tersedia.');
            }

            // Step 1: Create backup
            $backupPath = $this->updater->createBackup();

            // Step 2: Download update
            $zipPath = $this->updater->downloadUpdate(
                $updateInfo['download_url'],
                $updateInfo['latest']
            );

            // Step 3: Install update
            $this->updater->installUpdate($zipPath, $updateInfo['latest']);

            // Step 4: Clean old backups (keep last 3)
            $this->updater->cleanOldBackups(3);

            echo json_encode([
                'success' => true,
                'message' => 'Update ke versi ' . $updateInfo['latest'] . ' berhasil!',
                'new_version' => $updateInfo['latest'],
                'backup_path' => basename($backupPath)
            ]);

        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
        exit;
    }

    /**
     * Restore from backup
     */
    public function restore()
    {
        header('Content-Type: application/json');

        $backupName = $_POST['backup'] ?? '';

        if (empty($backupName)) {
            echo json_encode([
                'success' => false,
                'error' => 'Nama backup tidak valid.'
            ]);
            exit;
        }

        try {
            $backupPath = APPROOT . '/backups/' . $backupName;
            $this->updater->restoreFromBackup($backupPath);

            echo json_encode([
                'success' => true,
                'message' => 'Restore dari backup berhasil!',
                'restored_from' => $backupName
            ]);

        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
        exit;
    }

    /**
     * Delete a backup
     */
    public function deleteBackup()
    {
        header('Content-Type: application/json');

        $backupName = $_POST['backup'] ?? '';

        if (empty($backupName)) {
            echo json_encode([
                'success' => false,
                'error' => 'Nama backup tidak valid.'
            ]);
            exit;
        }

        try {
            $backupPath = APPROOT . '/backups/' . $backupName;

            if (!is_dir($backupPath)) {
                throw new Exception('Backup tidak ditemukan.');
            }

            // Delete directory recursively
            $this->deleteDir($backupPath);

            echo json_encode([
                'success' => true,
                'message' => 'Backup berhasil dihapus.'
            ]);

        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
        exit;
    }

    /**
     * Helper to delete directory
     */
    private function deleteDir($dir)
    {
        if (!is_dir($dir))
            return;

        $files = array_diff(scandir($dir), ['.', '..']);

        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->deleteDir($path) : unlink($path);
        }

        rmdir($dir);
    }
}
