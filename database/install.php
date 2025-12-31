<?php
/**
 * SOFT DATABASE INSTALLER
 * Smart Absensi - Database Schema Installation Script
 * 
 * Script ini membuat SEMUA tabel yang diperlukan untuk aplikasi Smart Absensi.
 * Menggunakan pendekatan "soft" - hanya membuat tabel/data jika BELUM ADA.
 * 
 * Cara penggunaan:
 * 1. Akses file ini melalui browser: http://your-domain/database/install.php
 * 2. Atau jalankan via CLI: php install.php
 * 
 * PENTING: Hapus atau pindahkan file ini setelah instalasi selesai!
 */

// Prevent timeout for large operations
set_time_limit(300);

// Load database configuration
require_once __DIR__ . '/../config/database.php';

class SoftInstaller
{
    private $pdo;
    private $log = [];

    public function __construct()
    {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";charset=utf8mb4";
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);

            // Create database if not exists
            $this->pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` 
                             CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
            $this->pdo->exec("USE `" . DB_NAME . "`");

            $this->log("✅ Database '" . DB_NAME . "' siap digunakan.");
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

    private function createTableIfNotExists($table, $sql)
    {
        if ($this->tableExists($table)) {
            $this->log("⏭️  Tabel '$table' sudah ada, dilewati.");
            return false;
        }

        try {
            $this->pdo->exec($sql);
            $this->log("✅ Tabel '$table' berhasil dibuat.");
            return true;
        } catch (PDOException $e) {
            $this->log("❌ Gagal membuat tabel '$table': " . $e->getMessage());
            return false;
        }
    }

    public function run()
    {
        $this->log("\n========================================");
        $this->log("🚀 SMART ABSENSI - SOFT INSTALLER");
        $this->log("========================================\n");

        // Disable foreign key checks during installation
        $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

        // =====================================================================
        // CORE TABLES
        // =====================================================================

        // 1. Tahun Pelajaran (tp)
        $this->createTableIfNotExists('tp', "
            CREATE TABLE `tp` (
                `id_tp` int NOT NULL AUTO_INCREMENT,
                `nama_tp` varchar(20) NOT NULL,
                `tgl_mulai` date NOT NULL,
                `tgl_selesai` date NOT NULL,
                PRIMARY KEY (`id_tp`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");

        // 2. Semester
        $this->createTableIfNotExists('semester', "
            CREATE TABLE `semester` (
                `id_semester` int NOT NULL AUTO_INCREMENT,
                `id_tp` int NOT NULL,
                `semester` enum('Ganjil','Genap') NOT NULL,
                `is_aktif` tinyint(1) DEFAULT '0',
                PRIMARY KEY (`id_semester`),
                KEY `id_tp` (`id_tp`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");

        // 3. Kelas
        $this->createTableIfNotExists('kelas', "
            CREATE TABLE `kelas` (
                `id_kelas` int NOT NULL AUTO_INCREMENT,
                `nama_kelas` varchar(50) NOT NULL,
                `tingkat` enum('1','2','3','4','5','6','7','8','9','10','11','12') DEFAULT NULL,
                `jurusan` varchar(50) DEFAULT NULL,
                `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id_kelas`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");

        // 4. Siswa
        $this->createTableIfNotExists('siswa', "
            CREATE TABLE `siswa` (
                `id_siswa` int NOT NULL AUTO_INCREMENT,
                `nisn` varchar(20) NOT NULL,
                `nama_siswa` varchar(100) NOT NULL,
                `jenis_kelamin` enum('L','P') DEFAULT NULL,
                `tempat_lahir` varchar(50) DEFAULT NULL,
                `tanggal_lahir` date DEFAULT NULL,
                `alamat` text,
                `nama_ortu` varchar(100) DEFAULT NULL,
                `no_hp_ortu` varchar(20) DEFAULT NULL,
                `status_siswa` enum('aktif','nonaktif','lulus','pindah') DEFAULT 'aktif',
                `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id_siswa`),
                UNIQUE KEY `nisn` (`nisn`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");

        // 5. Guru
        $this->createTableIfNotExists('guru', "
            CREATE TABLE `guru` (
                `id_guru` int NOT NULL AUTO_INCREMENT,
                `nik` varchar(30) DEFAULT NULL,
                `nama_guru` varchar(100) NOT NULL,
                `email` varchar(100) DEFAULT NULL,
                PRIMARY KEY (`id_guru`),
                UNIQUE KEY `nip` (`nik`),
                UNIQUE KEY `email` (`email`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");

        // 6. Mata Pelajaran
        $this->createTableIfNotExists('mapel', "
            CREATE TABLE `mapel` (
                `id_mapel` int NOT NULL AUTO_INCREMENT,
                `nama_mapel` varchar(100) NOT NULL,
                `kode_mapel` varchar(20) DEFAULT NULL,
                `kelompok` varchar(50) DEFAULT NULL,
                PRIMARY KEY (`id_mapel`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");

        // 7. Users
        $this->createTableIfNotExists('users', "
            CREATE TABLE `users` (
                `id_user` int NOT NULL AUTO_INCREMENT,
                `username` varchar(50) NOT NULL,
                `password` varchar(255) NOT NULL,
                `password_plain` varchar(255) DEFAULT NULL,
                `nama_lengkap` varchar(100) NOT NULL,
                `role` enum('admin','guru','siswa','kepala_madrasah','wali_kelas') NOT NULL,
                `id_ref` int DEFAULT NULL,
                `status` enum('aktif','nonaktif') DEFAULT 'aktif',
                `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id_user`),
                UNIQUE KEY `username` (`username`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");

        // =====================================================================
        // RELATIONAL TABLES
        // =====================================================================

        // 8. Keanggotaan Kelas
        $this->createTableIfNotExists('keanggotaan_kelas', "
            CREATE TABLE `keanggotaan_kelas` (
                `id_keanggotaan` int NOT NULL AUTO_INCREMENT,
                `id_siswa` int NOT NULL,
                `id_kelas` int NOT NULL,
                `id_tp` int NOT NULL,
                PRIMARY KEY (`id_keanggotaan`),
                KEY `id_siswa` (`id_siswa`),
                KEY `id_kelas` (`id_kelas`),
                KEY `id_tp` (`id_tp`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");

        // 9. Wali Kelas
        $this->createTableIfNotExists('wali_kelas', "
            CREATE TABLE `wali_kelas` (
                `id_walikelas` int NOT NULL AUTO_INCREMENT,
                `id_guru` int NOT NULL,
                `id_kelas` int NOT NULL,
                `id_tp` int NOT NULL,
                `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id_walikelas`),
                UNIQUE KEY `unique_walikelas` (`id_guru`,`id_tp`),
                UNIQUE KEY `unique_kelas_walikelas` (`id_kelas`,`id_tp`),
                KEY `idx_guru` (`id_guru`),
                KEY `idx_kelas` (`id_kelas`),
                KEY `idx_tp` (`id_tp`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");

        // 10. Penugasan (Guru - Mapel - Kelas)
        $this->createTableIfNotExists('penugasan', "
            CREATE TABLE `penugasan` (
                `id_penugasan` int NOT NULL AUTO_INCREMENT,
                `id_guru` int NOT NULL,
                `id_mapel` int NOT NULL,
                `id_kelas` int NOT NULL,
                `id_tp` int NOT NULL,
                `id_semester` int DEFAULT NULL,
                `jam_per_minggu` int DEFAULT '2',
                `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id_penugasan`),
                KEY `id_guru` (`id_guru`),
                KEY `id_mapel` (`id_mapel`),
                KEY `id_kelas` (`id_kelas`),
                KEY `id_tp` (`id_tp`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");

        // 11. Guru Mapel
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

        // =====================================================================
        // JADWAL & JAM PELAJARAN
        // =====================================================================

        // 12. Jam Pelajaran
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

        // 13. Jadwal Pelajaran
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

        // 14. Jadwal Istirahat
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

        // 15. Kebutuhan Jam Mapel
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

        // 16. Ruangan
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

        // =====================================================================
        // ABSENSI & JURNAL
        // =====================================================================

        // 17. Absensi
        $this->createTableIfNotExists('absensi', "
            CREATE TABLE `absensi` (
                `id_absensi` int NOT NULL AUTO_INCREMENT,
                `id_siswa` int NOT NULL,
                `id_penugasan` int NOT NULL,
                `pertemuan_ke` int NOT NULL,
                `tanggal` date NOT NULL,
                `status` enum('hadir','izin','sakit','alpha') DEFAULT 'hadir',
                `timestamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id_absensi`),
                KEY `id_siswa` (`id_siswa`),
                KEY `id_penugasan` (`id_penugasan`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");

        // 18. Jurnal
        $this->createTableIfNotExists('jurnal', "
            CREATE TABLE `jurnal` (
                `id_jurnal` int NOT NULL AUTO_INCREMENT,
                `id_penugasan` int NOT NULL,
                `pertemuan_ke` int NOT NULL,
                `tanggal` date NOT NULL,
                `topik_materi` text,
                `catatan` text,
                `timestamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id_jurnal`),
                KEY `id_penugasan` (`id_penugasan`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");

        // =====================================================================
        // NILAI
        // =====================================================================

        // 19. Nilai
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

        // 20. Nilai Detail
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

        // =====================================================================
        // RPP
        // =====================================================================

        // 21. RPP
        $this->createTableIfNotExists('rpp', "
            CREATE TABLE `rpp` (
                `id_rpp` int NOT NULL AUTO_INCREMENT,
                `id_guru` int NOT NULL,
                `id_mapel` int NOT NULL,
                `id_kelas` int NOT NULL,
                `id_tp` int NOT NULL,
                `id_semester` int NOT NULL,
                `judul` varchar(255) NOT NULL,
                `file_rpp` varchar(255) DEFAULT NULL,
                `status` enum('draft','submitted','approved','revision') DEFAULT 'draft',
                `catatan_reviewer` text,
                `reviewed_by` int DEFAULT NULL,
                `reviewed_at` datetime DEFAULT NULL,
                `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id_rpp`),
                KEY `id_guru` (`id_guru`),
                KEY `id_mapel` (`id_mapel`),
                KEY `id_kelas` (`id_kelas`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");

        // 22. Pengaturan RPP
        $this->createTableIfNotExists('pengaturan_rpp', "
            CREATE TABLE `pengaturan_rpp` (
                `id` int NOT NULL AUTO_INCREMENT,
                `wajib_rpp_disetujui` tinyint(1) DEFAULT '0',
                `blokir_absensi` tinyint(1) DEFAULT '0',
                `blokir_jurnal` tinyint(1) DEFAULT '0',
                `blokir_nilai` tinyint(1) DEFAULT '0',
                `pesan_blokir` text,
                `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");

        // =====================================================================
        // PEMBAYARAN SPP
        // =====================================================================

        // 23. Jenis Pembayaran
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

        // 24. Pembayaran
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
        // PSB (Penerimaan Siswa Baru)
        // =====================================================================

        // 25. PSB Periode
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

        // 26. PSB Pendaftar
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

        // 27. PSB Dokumen
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

        // 28. PSB Pembayaran
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

        // 29. PSB Settings
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

        // =====================================================================
        // SURAT TUGAS
        // =====================================================================

        // 30. Surat Tugas Lembaga
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

        // 31. Surat Tugas
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

        // 32. Surat Tugas Petugas
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

        // =====================================================================
        // CMS (Content Management System)
        // =====================================================================

        // 33. CMS Settings
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

        // 34. CMS Menus
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

        // 35. CMS Posts (Berita/Pengumuman)
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

        // 36. CMS Sliders
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

        // 37. CMS Popups
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

        // 38. CMS Institutions
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

        // =====================================================================
        // PENGATURAN & KONFIGURASI
        // =====================================================================

        // 39. Pengaturan Aplikasi
        $this->createTableIfNotExists('pengaturan_aplikasi', "
            CREATE TABLE `pengaturan_aplikasi` (
                `id` int NOT NULL AUTO_INCREMENT,
                `nama_aplikasi` varchar(255) NOT NULL DEFAULT 'Smart Absensi',
                `logo` varchar(255) DEFAULT NULL,
                `whatsapp_api_url` varchar(255) DEFAULT NULL,
                `whatsapp_api_key` varchar(255) DEFAULT NULL,
                `url_web` varchar(255) DEFAULT NULL,
                `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");

        // 40. Pengaturan Menu
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

        // 41. QR Code Config
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

        // 42. Pengaturan Rapor
        $this->createTableIfNotExists('pengaturan_rapor', "
            CREATE TABLE `pengaturan_rapor` (
                `id` int NOT NULL AUTO_INCREMENT,
                `nama_sekolah` varchar(255) DEFAULT NULL,
                `alamat_sekolah` text,
                `kota` varchar(100) DEFAULT NULL,
                `nama_kepala_sekolah` varchar(150) DEFAULT NULL,
                `nip_kepala_sekolah` varchar(50) DEFAULT NULL,
                `bobot_harian` int DEFAULT '40',
                `bobot_uts` int DEFAULT '30',
                `bobot_uas` int DEFAULT '30',
                `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");

        // Re-enable foreign key checks
        $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

        // =====================================================================
        // INSERT DEFAULT DATA
        // =====================================================================
        $this->log("\n📦 Mengisi data default...\n");

        // Default Admin User (jika belum ada)
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM users WHERE role = 'admin'");
        $stmt->execute();
        if ($stmt->fetchColumn() == 0) {
            $passwordHash = password_hash('admin123', PASSWORD_DEFAULT);
            $stmt = $this->pdo->prepare("INSERT INTO users (username, password, password_plain, nama_lengkap, role, status) 
                                         VALUES ('admin', ?, 'admin123', 'Administrator', 'admin', 'aktif')");
            $stmt->execute([$passwordHash]);
            $this->log("✅ User admin default berhasil dibuat (username: admin, password: admin123)");
        } else {
            $this->log("⏭️  User admin sudah ada, dilewati.");
        }

        // Default Pengaturan Aplikasi
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM pengaturan_aplikasi");
        $stmt->execute();
        if ($stmt->fetchColumn() == 0) {
            $this->pdo->exec("INSERT INTO pengaturan_aplikasi (id, nama_aplikasi) VALUES (1, 'Smart Absensi')");
            $this->log("✅ Pengaturan aplikasi default berhasil dibuat.");
        }

        // Default Pengaturan RPP
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM pengaturan_rpp");
        $stmt->execute();
        if ($stmt->fetchColumn() == 0) {
            $this->pdo->exec("INSERT INTO pengaturan_rpp (id, wajib_rpp_disetujui) VALUES (1, 0)");
            $this->log("✅ Pengaturan RPP default berhasil dibuat.");
        }

        // Default QR Config
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM qr_config");
        $stmt->execute();
        if ($stmt->fetchColumn() == 0) {
            $this->pdo->exec("INSERT INTO qr_config (jenis, field_line1, field_line2, show_foto, show_qr) 
                              VALUES ('guru', 'nama_guru', 'nik', 1, 1), ('siswa', 'nama_siswa', 'nisn', 1, 1)");
            $this->log("✅ Konfigurasi QR default berhasil dibuat.");
        }

        $this->log("\n========================================");
        $this->log("✅ INSTALASI SELESAI!");
        $this->log("========================================\n");
        $this->log("⚠️  PENTING: Hapus file install.php ini setelah selesai!\n");

        return $this->log;
    }

    public function getLog()
    {
        return $this->log;
    }
}

// Run installer
$installer = new SoftInstaller();
$logs = $installer->run();

// Output for browser
if (php_sapi_name() !== 'cli') {
    echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Smart Absensi Installer</title>";
    echo "<style>body{font-family:monospace;background:#1a1a2e;color:#eee;padding:20px;line-height:1.8;}";
    echo ".log{background:#16213e;padding:20px;border-radius:8px;white-space:pre-wrap;}</style></head><body>";
    echo "<h1>🚀 Smart Absensi - Database Installer</h1>";
    echo "<div class='log'>" . implode("\n", $logs) . "</div>";
    echo "</body></html>";
}
