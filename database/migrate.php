<?php
/**
 * DATABASE MIGRATION SCRIPT
 * Smart Absensi - Update Hosting Database Schema
 * 
 * Script ini menambahkan tabel dan kolom yang BELUM ADA ke database hosting.
 * TIDAK AKAN menghapus data apapun!
 * 
 * Cara penggunaan:
 * 1. Upload file ini ke hosting
 * 2. Akses via browser: http://your-domain/database/migrate.php
 * 3. HAPUS file ini setelah selesai!
 */

set_time_limit(300);
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load database configuration
require_once __DIR__ . '/../config/database.php';

class DatabaseMigrator
{
    private $pdo;
    private $log = [];
    private $errors = [];

    public function __construct()
    {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
            $this->log("✅ Koneksi database berhasil.");
        } catch (PDOException $e) {
            die("❌ Koneksi database gagal: " . $e->getMessage());
        }
    }

    private function log($message)
    {
        $this->log[] = $message;
        if (php_sapi_name() === 'cli') {
            echo $message . "\n";
        }
    }

    private function tableExists($table)
    {
        $stmt = $this->pdo->prepare("SHOW TABLES LIKE ?");
        $stmt->execute([$table]);
        return $stmt->rowCount() > 0;
    }

    private function columnExists($table, $column)
    {
        try {
            $stmt = $this->pdo->prepare("SHOW COLUMNS FROM `$table` LIKE ?");
            $stmt->execute([$column]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            return false;
        }
    }

    private function addColumnIfNotExists($table, $column, $definition, $after = null)
    {
        if (!$this->tableExists($table)) {
            $this->log("⚠️  Tabel '$table' tidak ada, skip kolom '$column'.");
            return false;
        }

        if ($this->columnExists($table, $column)) {
            $this->log("⏭️  Kolom '$table.$column' sudah ada.");
            return false;
        }

        try {
            $sql = "ALTER TABLE `$table` ADD COLUMN `$column` $definition";
            if ($after) {
                $sql .= " AFTER `$after`";
            }
            $this->pdo->exec($sql);
            $this->log("✅ Kolom '$table.$column' berhasil ditambahkan.");
            return true;
        } catch (PDOException $e) {
            $this->errors[] = "❌ Gagal menambahkan kolom '$table.$column': " . $e->getMessage();
            $this->log("❌ Gagal menambahkan kolom '$table.$column': " . $e->getMessage());
            return false;
        }
    }

    private function createTableIfNotExists($table, $sql)
    {
        if ($this->tableExists($table)) {
            $this->log("⏭️  Tabel '$table' sudah ada.");
            return false;
        }

        try {
            $this->pdo->exec($sql);
            $this->log("✅ Tabel '$table' berhasil dibuat.");
            return true;
        } catch (PDOException $e) {
            $this->errors[] = "❌ Gagal membuat tabel '$table': " . $e->getMessage();
            $this->log("❌ Gagal membuat tabel '$table': " . $e->getMessage());
            return false;
        }
    }

    private function addIndexIfNotExists($table, $indexName, $columns, $unique = false)
    {
        try {
            $stmt = $this->pdo->query("SHOW INDEX FROM `$table` WHERE Key_name = '$indexName'");
            if ($stmt->rowCount() > 0) {
                return false;
            }

            $type = $unique ? 'UNIQUE INDEX' : 'INDEX';
            $sql = "ALTER TABLE `$table` ADD $type `$indexName` ($columns)";
            $this->pdo->exec($sql);
            $this->log("✅ Index '$indexName' pada '$table' berhasil ditambahkan.");
            return true;
        } catch (PDOException $e) {
            // Ignore if already exists
            return false;
        }
    }

    public function run()
    {
        $this->log("\n========================================");
        $this->log("🔄 SMART ABSENSI - DATABASE MIGRATION");
        $this->log("========================================\n");

        // Disable foreign key checks
        $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

        // =====================================================================
        // SECTION 1: ADD MISSING TABLES
        // =====================================================================
        $this->log("\n📦 PHASE 1: Membuat tabel yang belum ada...\n");

        // CMS Tables
        $this->createTableIfNotExists('cms_settings', "
            CREATE TABLE `cms_settings` (
                `id` int NOT NULL AUTO_INCREMENT,
                `setting_key` varchar(50) NOT NULL,
                `setting_value` text,
                `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `setting_key` (`setting_key`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");

        $this->createTableIfNotExists('cms_menus', "
            CREATE TABLE `cms_menus` (
                `id` int NOT NULL AUTO_INCREMENT,
                `parent_id` int DEFAULT NULL,
                `label` varchar(100) NOT NULL,
                `url` varchar(255) DEFAULT NULL,
                `icon` varchar(50) DEFAULT NULL,
                `order_index` int DEFAULT '0',
                `is_active` tinyint(1) DEFAULT '1',
                `target` enum('_self','_blank') DEFAULT '_self',
                `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `parent_id` (`parent_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");

        $this->createTableIfNotExists('cms_posts', "
            CREATE TABLE `cms_posts` (
                `id` int NOT NULL AUTO_INCREMENT,
                `title` varchar(255) NOT NULL,
                `slug` varchar(255) NOT NULL,
                `content` longtext,
                `type` enum('page','news','announcement') DEFAULT 'news',
                `image` varchar(255) DEFAULT NULL,
                `author_id` int DEFAULT NULL,
                `view_count` int DEFAULT '0',
                `is_published` tinyint(1) DEFAULT '1',
                `published_at` datetime DEFAULT CURRENT_TIMESTAMP,
                `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `slug` (`slug`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");

        $this->createTableIfNotExists('cms_sliders', "
            CREATE TABLE `cms_sliders` (
                `id` int NOT NULL AUTO_INCREMENT,
                `title` varchar(100) DEFAULT NULL,
                `description` text,
                `image` varchar(255) NOT NULL,
                `link_url` varchar(255) DEFAULT NULL,
                `button_text` varchar(50) DEFAULT 'Selengkapnya',
                `order_index` int DEFAULT '0',
                `is_active` tinyint(1) DEFAULT '1',
                `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");

        $this->createTableIfNotExists('cms_popups', "
            CREATE TABLE `cms_popups` (
                `id` int NOT NULL AUTO_INCREMENT,
                `title` varchar(100) DEFAULT NULL,
                `image` varchar(255) DEFAULT NULL,
                `content` text,
                `link_url` varchar(255) DEFAULT NULL,
                `is_active` tinyint(1) DEFAULT '1',
                `start_date` datetime DEFAULT NULL,
                `end_date` datetime DEFAULT NULL,
                `frequency` enum('once','always','daily') DEFAULT 'once',
                `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");

        $this->createTableIfNotExists('cms_institutions', "
            CREATE TABLE `cms_institutions` (
                `id` int NOT NULL AUTO_INCREMENT,
                `name` varchar(255) NOT NULL,
                `logo` varchar(255) DEFAULT NULL,
                `description` text,
                `order_index` int DEFAULT '0',
                `is_active` tinyint(1) DEFAULT '1',
                `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");

        // PSB Tables
        $this->createTableIfNotExists('psb_periode', "
            CREATE TABLE `psb_periode` (
                `id_periode` int NOT NULL AUTO_INCREMENT,
                `nama_periode` varchar(100) NOT NULL,
                `tahun_ajaran` varchar(20) NOT NULL,
                `tgl_mulai` date NOT NULL,
                `tgl_selesai` date NOT NULL,
                `kuota` int DEFAULT '100',
                `biaya_pendaftaran` decimal(12,2) DEFAULT '0.00',
                `status` enum('aktif','nonaktif','selesai') DEFAULT 'aktif',
                `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id_periode`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");

        $this->createTableIfNotExists('psb_pendaftar', "
            CREATE TABLE `psb_pendaftar` (
                `id_pendaftar` int NOT NULL AUTO_INCREMENT,
                `id_periode` int NOT NULL,
                `no_pendaftaran` varchar(50) NOT NULL,
                `nisn` varchar(20) DEFAULT NULL,
                `nik` varchar(20) DEFAULT NULL,
                `nama_lengkap` varchar(100) NOT NULL,
                `jenis_kelamin` enum('L','P') NOT NULL,
                `tempat_lahir` varchar(50) DEFAULT NULL,
                `tanggal_lahir` date DEFAULT NULL,
                `alamat` text,
                `no_hp` varchar(20) DEFAULT NULL,
                `email` varchar(100) DEFAULT NULL,
                `asal_sekolah` varchar(100) DEFAULT NULL,
                `nama_ayah` varchar(100) DEFAULT NULL,
                `pekerjaan_ayah` varchar(50) DEFAULT NULL,
                `nama_ibu` varchar(100) DEFAULT NULL,
                `pekerjaan_ibu` varchar(50) DEFAULT NULL,
                `no_hp_ortu` varchar(20) DEFAULT NULL,
                `foto` varchar(255) DEFAULT NULL,
                `status_pendaftaran` enum('pending','diterima','ditolak','mengundurkan_diri') DEFAULT 'pending',
                `keterangan` text,
                `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id_pendaftar`),
                UNIQUE KEY `no_pendaftaran` (`no_pendaftaran`),
                KEY `id_periode` (`id_periode`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");

        $this->createTableIfNotExists('psb_dokumen', "
            CREATE TABLE `psb_dokumen` (
                `id_dokumen` int NOT NULL AUTO_INCREMENT,
                `id_pendaftar` int NOT NULL,
                `jenis_dokumen` varchar(50) NOT NULL,
                `nama_file` varchar(255) NOT NULL,
                `uploaded_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id_dokumen`),
                KEY `id_pendaftar` (`id_pendaftar`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");

        $this->createTableIfNotExists('psb_pembayaran', "
            CREATE TABLE `psb_pembayaran` (
                `id_pembayaran` int NOT NULL AUTO_INCREMENT,
                `id_pendaftar` int NOT NULL,
                `jenis_pembayaran` varchar(50) NOT NULL,
                `nominal` decimal(12,2) NOT NULL,
                `status` enum('pending','verified','rejected') DEFAULT 'pending',
                `bukti_transfer` varchar(255) DEFAULT NULL,
                `verified_by` int DEFAULT NULL,
                `verified_at` datetime DEFAULT NULL,
                `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id_pembayaran`),
                KEY `id_pendaftar` (`id_pendaftar`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");

        $this->createTableIfNotExists('psb_settings', "
            CREATE TABLE `psb_settings` (
                `id` int NOT NULL AUTO_INCREMENT,
                `setting_key` varchar(50) NOT NULL,
                `setting_value` text,
                `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `setting_key` (`setting_key`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");

        // Surat Tugas Tables
        $this->createTableIfNotExists('surat_tugas_lembaga', "
            CREATE TABLE `surat_tugas_lembaga` (
                `id_lembaga` int NOT NULL AUTO_INCREMENT,
                `nama_lembaga` varchar(255) NOT NULL,
                `kop_surat` varchar(255) DEFAULT NULL,
                `alamat` text,
                `kota` varchar(100) DEFAULT NULL,
                `nama_kepala_lembaga` varchar(255) DEFAULT NULL,
                `nip_kepala` varchar(100) DEFAULT NULL,
                `jabatan_kepala` varchar(100) DEFAULT NULL,
                `email` varchar(100) DEFAULT NULL,
                `telepon` varchar(50) DEFAULT NULL,
                `website` varchar(255) DEFAULT NULL,
                `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id_lembaga`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");

        $this->createTableIfNotExists('surat_tugas', "
            CREATE TABLE `surat_tugas` (
                `id_surat` int NOT NULL AUTO_INCREMENT,
                `id_lembaga` int NOT NULL,
                `nomor_surat` varchar(100) NOT NULL,
                `tanggal_surat` date NOT NULL,
                `kota_surat` varchar(100) NOT NULL,
                `menimbang` text,
                `dasar` text,
                `untuk` text NOT NULL,
                `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `created_by` int DEFAULT NULL,
                PRIMARY KEY (`id_surat`),
                KEY `id_lembaga` (`id_lembaga`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");

        $this->createTableIfNotExists('surat_tugas_petugas', "
            CREATE TABLE `surat_tugas_petugas` (
                `id_petugas` int NOT NULL AUTO_INCREMENT,
                `id_surat` int NOT NULL,
                `nama_petugas` varchar(255) NOT NULL,
                `jenis_identitas` enum('NIP','NIK','NISN','Lainnya') DEFAULT 'NIK',
                `identitas_petugas` varchar(100) DEFAULT NULL,
                `jabatan_petugas` varchar(100) DEFAULT NULL,
                `pangkat_golongan` varchar(100) DEFAULT NULL,
                `keterangan` text,
                `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id_petugas`),
                KEY `id_surat` (`id_surat`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");

        // Jadwal Tables
        $this->createTableIfNotExists('jam_pelajaran', "
            CREATE TABLE `jam_pelajaran` (
                `id_jam` int NOT NULL AUTO_INCREMENT,
                `jam_ke` tinyint NOT NULL,
                `waktu_mulai` time NOT NULL,
                `waktu_selesai` time NOT NULL,
                `is_istirahat` tinyint DEFAULT '0',
                `keterangan` varchar(50) DEFAULT NULL,
                `urutan` int DEFAULT '0',
                PRIMARY KEY (`id_jam`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");

        $this->createTableIfNotExists('jadwal_pelajaran', "
            CREATE TABLE `jadwal_pelajaran` (
                `id_jadwal` int NOT NULL AUTO_INCREMENT,
                `id_kelas` int NOT NULL,
                `id_guru` int NOT NULL,
                `id_mapel` int NOT NULL,
                `id_jam` int NOT NULL,
                `hari` enum('Senin','Selasa','Rabu','Kamis','Jumat','Sabtu') NOT NULL,
                `id_ruangan` int DEFAULT NULL,
                `id_tp` int NOT NULL,
                `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id_jadwal`),
                UNIQUE KEY `no_guru_conflict` (`id_guru`,`id_jam`,`hari`,`id_tp`),
                UNIQUE KEY `no_kelas_conflict` (`id_kelas`,`id_jam`,`hari`,`id_tp`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");

        $this->createTableIfNotExists('jadwal_istirahat', "
            CREATE TABLE `jadwal_istirahat` (
                `id` int NOT NULL AUTO_INCREMENT,
                `id_kelas` int NOT NULL,
                `hari` varchar(20) NOT NULL,
                `setelah_jam` int NOT NULL,
                `id_tp` int NOT NULL,
                `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `unique_istirahat` (`id_kelas`,`hari`,`setelah_jam`,`id_tp`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");

        $this->createTableIfNotExists('kebutuhan_jam_mapel', "
            CREATE TABLE `kebutuhan_jam_mapel` (
                `id` int NOT NULL AUTO_INCREMENT,
                `id_kelas` int NOT NULL,
                `id_mapel` int NOT NULL,
                `jumlah_jam` int NOT NULL DEFAULT '2',
                `id_tp` int NOT NULL,
                `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `unique_kebutuhan` (`id_kelas`,`id_mapel`,`id_tp`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");

        $this->createTableIfNotExists('ruangan', "
            CREATE TABLE `ruangan` (
                `id_ruangan` int NOT NULL AUTO_INCREMENT,
                `nama_ruangan` varchar(100) NOT NULL,
                `kapasitas` int DEFAULT NULL,
                `keterangan` text,
                `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id_ruangan`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");

        // Nilai Tables (different from nilai_siswa)
        $this->createTableIfNotExists('nilai', "
            CREATE TABLE `nilai` (
                `id_nilai` int NOT NULL AUTO_INCREMENT,
                `id_penugasan` int NOT NULL,
                `id_siswa` int NOT NULL,
                `nilai_harian` decimal(5,2) DEFAULT NULL,
                `nilai_uts` decimal(5,2) DEFAULT NULL,
                `nilai_uas` decimal(5,2) DEFAULT NULL,
                `nilai_akhir` decimal(5,2) DEFAULT NULL,
                `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id_nilai`),
                KEY `id_penugasan` (`id_penugasan`),
                KEY `id_siswa` (`id_siswa`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");

        $this->createTableIfNotExists('nilai_detail', "
            CREATE TABLE `nilai_detail` (
                `id` int NOT NULL AUTO_INCREMENT,
                `id_penugasan` int NOT NULL,
                `id_siswa` int NOT NULL,
                `jenis_nilai` enum('tugas','ulangan','praktik','uts','uas') NOT NULL,
                `nama_nilai` varchar(100) DEFAULT NULL,
                `nilai` decimal(5,2) DEFAULT NULL,
                `tanggal` date DEFAULT NULL,
                `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `id_penugasan` (`id_penugasan`),
                KEY `id_siswa` (`id_siswa`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");

        // Other missing tables
        $this->createTableIfNotExists('guru_mapel', "
            CREATE TABLE `guru_mapel` (
                `id` int NOT NULL AUTO_INCREMENT,
                `id_guru` int NOT NULL,
                `id_mapel` int NOT NULL,
                `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `unique_guru_mapel` (`id_guru`,`id_mapel`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");

        $this->createTableIfNotExists('pengaturan_menu', "
            CREATE TABLE `pengaturan_menu` (
                `id` int NOT NULL AUTO_INCREMENT,
                `menu_key` varchar(50) NOT NULL,
                `is_visible` tinyint(1) DEFAULT '1',
                `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `menu_key` (`menu_key`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");

        $this->createTableIfNotExists('qr_config', "
            CREATE TABLE `qr_config` (
                `id` int NOT NULL AUTO_INCREMENT,
                `jenis` enum('guru','siswa') NOT NULL,
                `field_line1` varchar(50) DEFAULT NULL,
                `field_line2` varchar(50) DEFAULT NULL,
                `field_line3` varchar(50) DEFAULT NULL,
                `show_foto` tinyint(1) DEFAULT '1',
                `show_qr` tinyint(1) DEFAULT '1',
                `qr_size` int DEFAULT '100',
                `template` varchar(50) DEFAULT 'default',
                `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `jenis` (`jenis`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");

        $this->createTableIfNotExists('jenis_pembayaran', "
            CREATE TABLE `jenis_pembayaran` (
                `id_jenis` int NOT NULL AUTO_INCREMENT,
                `nama_jenis` varchar(100) NOT NULL,
                `nominal` decimal(12,2) NOT NULL DEFAULT '0.00',
                `keterangan` text,
                `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id_jenis`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");

        $this->createTableIfNotExists('pembayaran', "
            CREATE TABLE `pembayaran` (
                `id_pembayaran` int NOT NULL AUTO_INCREMENT,
                `id_siswa` int NOT NULL,
                `id_jenis` int NOT NULL,
                `bulan` tinyint NOT NULL,
                `tahun` year NOT NULL,
                `nominal` decimal(12,2) NOT NULL,
                `tanggal_bayar` date DEFAULT NULL,
                `status` enum('belum_bayar','lunas') DEFAULT 'belum_bayar',
                `keterangan` text,
                `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id_pembayaran`),
                KEY `id_siswa` (`id_siswa`),
                KEY `id_jenis` (`id_jenis`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");

        // =====================================================================
        // SECTION 2: ADD MISSING COLUMNS TO EXISTING TABLES
        // =====================================================================
        $this->log("\n📦 PHASE 2: Menambahkan kolom yang belum ada...\n");

        // USERS table - ensure all columns exist
        $this->addColumnIfNotExists('users', 'password_plain', "varchar(255) DEFAULT NULL", 'password');
        $this->addColumnIfNotExists('users', 'id_ref', "int DEFAULT NULL", 'role');
        $this->addColumnIfNotExists('users', 'status', "enum('aktif','nonaktif') DEFAULT 'aktif'", 'id_ref');
        $this->addColumnIfNotExists('users', 'created_at', "timestamp NULL DEFAULT CURRENT_TIMESTAMP");

        // GURU table
        $this->addColumnIfNotExists('guru', 'created_at', "timestamp NULL DEFAULT CURRENT_TIMESTAMP");

        // SISWA table
        $this->addColumnIfNotExists('siswa', 'created_at', "timestamp NULL DEFAULT CURRENT_TIMESTAMP");
        $this->addColumnIfNotExists('siswa', 'foto', "varchar(255) DEFAULT NULL");

        // KELAS table
        $this->addColumnIfNotExists('kelas', 'tingkat', "enum('1','2','3','4','5','6','7','8','9','10','11','12') DEFAULT NULL", 'nama_kelas');
        $this->addColumnIfNotExists('kelas', 'jurusan', "varchar(50) DEFAULT NULL", 'tingkat');
        $this->addColumnIfNotExists('kelas', 'created_at', "timestamp NULL DEFAULT CURRENT_TIMESTAMP");

        // MAPEL table
        $this->addColumnIfNotExists('mapel', 'kode_mapel', "varchar(20) DEFAULT NULL", 'nama_mapel');
        $this->addColumnIfNotExists('mapel', 'kelompok', "varchar(50) DEFAULT NULL");

        // PENUGASAN table
        $this->addColumnIfNotExists('penugasan', 'id_semester', "int DEFAULT NULL", 'id_tp');
        $this->addColumnIfNotExists('penugasan', 'jam_per_minggu', "int DEFAULT '2'");
        $this->addColumnIfNotExists('penugasan', 'created_at', "timestamp NULL DEFAULT CURRENT_TIMESTAMP");

        // ABSENSI table
        $this->addColumnIfNotExists('absensi', 'pertemuan_ke', "int NOT NULL DEFAULT '1'", 'id_penugasan');
        $this->addColumnIfNotExists('absensi', 'tanggal', "date DEFAULT NULL", 'pertemuan_ke');

        // JURNAL table
        $this->addColumnIfNotExists('jurnal', 'topik_materi', "text", 'tanggal');
        $this->addColumnIfNotExists('jurnal', 'catatan', "text", 'topik_materi');

        // SEMESTER table
        $this->addColumnIfNotExists('semester', 'is_aktif', "tinyint(1) DEFAULT '0'");

        // TP table
        $this->addColumnIfNotExists('tp', 'created_at', "timestamp NULL DEFAULT CURRENT_TIMESTAMP");

        // PENGATURAN_APLIKASI table - add missing columns
        $this->addColumnIfNotExists('pengaturan_aplikasi', 'whatsapp_api_url', "varchar(255) DEFAULT NULL");
        $this->addColumnIfNotExists('pengaturan_aplikasi', 'whatsapp_api_key', "varchar(255) DEFAULT NULL");
        $this->addColumnIfNotExists('pengaturan_aplikasi', 'url_web', "varchar(255) DEFAULT NULL");

        // PENGATURAN_RAPOR table
        $this->addColumnIfNotExists('pengaturan_rapor', 'kota', "varchar(100) DEFAULT NULL");
        $this->addColumnIfNotExists('pengaturan_rapor', 'bobot_harian', "int DEFAULT '40'");
        $this->addColumnIfNotExists('pengaturan_rapor', 'bobot_uts', "int DEFAULT '30'");
        $this->addColumnIfNotExists('pengaturan_rapor', 'bobot_uas', "int DEFAULT '30'");

        // RPP table
        $this->addColumnIfNotExists('rpp', 'catatan_reviewer', "text");
        $this->addColumnIfNotExists('rpp', 'reviewed_by', "int DEFAULT NULL");
        $this->addColumnIfNotExists('rpp', 'reviewed_at', "datetime DEFAULT NULL");

        // Re-enable foreign key checks
        $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

        // =====================================================================
        // SECTION 3: INSERT DEFAULT DATA IF MISSING
        // =====================================================================
        $this->log("\n📦 PHASE 3: Menambahkan data default...\n");

        // Default QR Config
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM qr_config");
        $stmt->execute();
        if ($stmt->fetchColumn() == 0) {
            $this->pdo->exec("INSERT INTO qr_config (jenis, field_line1, field_line2, show_foto, show_qr) 
                              VALUES ('guru', 'nama_guru', 'nik', 1, 1), ('siswa', 'nama_siswa', 'nisn', 1, 1)");
            $this->log("✅ Konfigurasi QR default berhasil dibuat.");
        }

        // Default Pengaturan RPP
        if ($this->tableExists('pengaturan_rpp')) {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM pengaturan_rpp");
            $stmt->execute();
            if ($stmt->fetchColumn() == 0) {
                $this->pdo->exec("INSERT INTO pengaturan_rpp (id, wajib_rpp_disetujui) VALUES (1, 0)");
                $this->log("✅ Pengaturan RPP default berhasil dibuat.");
            }
        }

        $this->log("\n========================================");
        $this->log("✅ MIGRASI SELESAI!");
        $this->log("========================================\n");

        if (count($this->errors) > 0) {
            $this->log("\n⚠️ Ada " . count($this->errors) . " error yang perlu diperhatikan:");
            foreach ($this->errors as $error) {
                $this->log("   " . $error);
            }
        }

        $this->log("\n⚠️  PENTING: Hapus file migrate.php ini setelah selesai!\n");

        return $this->log;
    }

    public function getLog()
    {
        return $this->log;
    }
}

// Run migrator
$migrator = new DatabaseMigrator();
$logs = $migrator->run();

// Output for browser
if (php_sapi_name() !== 'cli') {
    echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Smart Absensi Migration</title>";
    echo "<style>body{font-family:monospace;background:#1a1a2e;color:#eee;padding:20px;line-height:1.8;}";
    echo ".log{background:#16213e;padding:20px;border-radius:8px;white-space:pre-wrap;max-height:80vh;overflow-y:auto;}</style></head><body>";
    echo "<h1>🔄 Smart Absensi - Database Migration</h1>";
    echo "<div class='log'>" . implode("\n", $logs) . "</div>";
    echo "</body></html>";
}
