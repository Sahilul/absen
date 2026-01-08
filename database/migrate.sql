-- ============================================================================
-- SMART ABSENSI - DATABASE MIGRATION SQL
-- ============================================================================
-- Script ini menambahkan tabel dan kolom yang BELUM ADA ke database hosting.
-- TIDAK AKAN menghapus data apapun!
-- 
-- Cara penggunaan:
-- 1. Login ke phpMyAdmin di hosting
-- 2. Pilih database: u696487583_absen
-- 3. Klik tab "SQL" atau "Import"
-- 4. Paste/upload file ini dan jalankan
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";

-- ============================================================================
-- PHASE 1: MEMBUAT TABEL BARU (JIKA BELUM ADA)
-- ============================================================================

-- -----------------------------------------------------------------------------
-- CMS TABLES
-- -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `cms_settings` (
    `id` int NOT NULL AUTO_INCREMENT,
    `setting_key` varchar(50) NOT NULL,
    `setting_value` text,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `cms_menus` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `cms_posts` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `cms_sliders` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `cms_popups` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `cms_institutions` (
    `id` int NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `logo` varchar(255) DEFAULT NULL,
    `description` text,
    `order_index` int DEFAULT '0',
    `is_active` tinyint(1) DEFAULT '1',
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------------------
-- PSB TABLES (Penerimaan Siswa Baru)
-- -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `psb_periode` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `psb_pendaftar` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `psb_dokumen` (
    `id_dokumen` int NOT NULL AUTO_INCREMENT,
    `id_pendaftar` int NOT NULL,
    `jenis_dokumen` varchar(50) NOT NULL,
    `nama_file` varchar(255) NOT NULL,
    `uploaded_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_dokumen`),
    KEY `id_pendaftar` (`id_pendaftar`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `psb_pembayaran` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `psb_settings` (
    `id` int NOT NULL AUTO_INCREMENT,
    `setting_key` varchar(50) NOT NULL,
    `setting_value` text,
    `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------------------
-- SURAT TUGAS TABLES
-- -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `surat_tugas_lembaga` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `surat_tugas` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `surat_tugas_petugas` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------------------
-- JADWAL TABLES
-- -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `jam_pelajaran` (
    `id_jam` int NOT NULL AUTO_INCREMENT,
    `jam_ke` tinyint NOT NULL,
    `waktu_mulai` time NOT NULL,
    `waktu_selesai` time NOT NULL,
    `is_istirahat` tinyint DEFAULT '0',
    `keterangan` varchar(50) DEFAULT NULL,
    `urutan` int DEFAULT '0',
    PRIMARY KEY (`id_jam`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `jadwal_pelajaran` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `jadwal_istirahat` (
    `id` int NOT NULL AUTO_INCREMENT,
    `id_kelas` int NOT NULL,
    `hari` varchar(20) NOT NULL,
    `setelah_jam` int NOT NULL,
    `id_tp` int NOT NULL,
    `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_istirahat` (`id_kelas`,`hari`,`setelah_jam`,`id_tp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `kebutuhan_jam_mapel` (
    `id` int NOT NULL AUTO_INCREMENT,
    `id_kelas` int NOT NULL,
    `id_mapel` int NOT NULL,
    `jumlah_jam` int NOT NULL DEFAULT '2',
    `id_tp` int NOT NULL,
    `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_kebutuhan` (`id_kelas`,`id_mapel`,`id_tp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `ruangan` (
    `id_ruangan` int NOT NULL AUTO_INCREMENT,
    `nama_ruangan` varchar(100) NOT NULL,
    `kapasitas` int DEFAULT NULL,
    `keterangan` text,
    `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_ruangan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------------------
-- NILAI TABLES
-- -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `nilai` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `nilai_detail` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------------------
-- OTHER TABLES
-- -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `guru_mapel` (
    `id` int NOT NULL AUTO_INCREMENT,
    `id_guru` int NOT NULL,
    `id_mapel` int NOT NULL,
    `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_guru_mapel` (`id_guru`,`id_mapel`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `pengaturan_menu` (
    `id` int NOT NULL AUTO_INCREMENT,
    `menu_key` varchar(50) NOT NULL,
    `is_visible` tinyint(1) DEFAULT '1',
    `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `menu_key` (`menu_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `qr_config` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `jenis_pembayaran` (
    `id_jenis` int NOT NULL AUTO_INCREMENT,
    `nama_jenis` varchar(100) NOT NULL,
    `nominal` decimal(12,2) NOT NULL DEFAULT '0.00',
    `keterangan` text,
    `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_jenis`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================================
-- PHASE 2: MENAMBAHKAN KOLOM YANG BELUM ADA (DENGAN PENGECEKAN)
-- ============================================================================
-- Note: MySQL tidak support IF NOT EXISTS untuk ADD COLUMN, 
-- jadi kita gunakan stored procedure untuk cek

DELIMITER //

DROP PROCEDURE IF EXISTS AddColumnIfNotExists//
CREATE PROCEDURE AddColumnIfNotExists(
    IN tableName VARCHAR(64),
    IN columnName VARCHAR(64),
    IN columnDef VARCHAR(255)
)
BEGIN
    DECLARE columnCount INT;
    
    SELECT COUNT(*) INTO columnCount
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = tableName
      AND COLUMN_NAME = columnName;
    
    IF columnCount = 0 THEN
        SET @sql = CONCAT('ALTER TABLE `', tableName, '` ADD COLUMN `', columnName, '` ', columnDef);
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END//

DELIMITER ;

-- USERS table
CALL AddColumnIfNotExists('users', 'password_plain', 'varchar(255) DEFAULT NULL');
CALL AddColumnIfNotExists('users', 'id_ref', 'int DEFAULT NULL');
CALL AddColumnIfNotExists('users', 'status', "enum('aktif','nonaktif') DEFAULT 'aktif'");
CALL AddColumnIfNotExists('users', 'created_at', 'timestamp NULL DEFAULT CURRENT_TIMESTAMP');

-- GURU table
CALL AddColumnIfNotExists('guru', 'created_at', 'timestamp NULL DEFAULT CURRENT_TIMESTAMP');

-- SISWA table
CALL AddColumnIfNotExists('siswa', 'created_at', 'timestamp NULL DEFAULT CURRENT_TIMESTAMP');
CALL AddColumnIfNotExists('siswa', 'foto', 'varchar(255) DEFAULT NULL');

-- KELAS table
CALL AddColumnIfNotExists('kelas', 'tingkat', "enum('1','2','3','4','5','6','7','8','9','10','11','12') DEFAULT NULL");
CALL AddColumnIfNotExists('kelas', 'jurusan', 'varchar(50) DEFAULT NULL');
CALL AddColumnIfNotExists('kelas', 'created_at', 'timestamp NULL DEFAULT CURRENT_TIMESTAMP');

-- MAPEL table
CALL AddColumnIfNotExists('mapel', 'kode_mapel', 'varchar(20) DEFAULT NULL');
CALL AddColumnIfNotExists('mapel', 'kelompok', 'varchar(50) DEFAULT NULL');

-- PENUGASAN table
CALL AddColumnIfNotExists('penugasan', 'id_semester', 'int DEFAULT NULL');
CALL AddColumnIfNotExists('penugasan', 'jam_per_minggu', "int DEFAULT '2'");
CALL AddColumnIfNotExists('penugasan', 'created_at', 'timestamp NULL DEFAULT CURRENT_TIMESTAMP');

-- ABSENSI table  
CALL AddColumnIfNotExists('absensi', 'pertemuan_ke', "int NOT NULL DEFAULT '1'");
CALL AddColumnIfNotExists('absensi', 'tanggal', 'date DEFAULT NULL');

-- JURNAL table
CALL AddColumnIfNotExists('jurnal', 'topik_materi', 'text');
CALL AddColumnIfNotExists('jurnal', 'catatan', 'text');

-- SEMESTER table
CALL AddColumnIfNotExists('semester', 'is_aktif', "tinyint(1) DEFAULT '0'");

-- PENGATURAN_APLIKASI table
CALL AddColumnIfNotExists('pengaturan_aplikasi', 'whatsapp_api_url', 'varchar(255) DEFAULT NULL');
CALL AddColumnIfNotExists('pengaturan_aplikasi', 'whatsapp_api_key', 'varchar(255) DEFAULT NULL');
CALL AddColumnIfNotExists('pengaturan_aplikasi', 'url_web', 'varchar(255) DEFAULT NULL');

-- PENGATURAN_RAPOR table
CALL AddColumnIfNotExists('pengaturan_rapor', 'kota', 'varchar(100) DEFAULT NULL');
CALL AddColumnIfNotExists('pengaturan_rapor', 'bobot_harian', "int DEFAULT '40'");
CALL AddColumnIfNotExists('pengaturan_rapor', 'bobot_uts', "int DEFAULT '30'");
CALL AddColumnIfNotExists('pengaturan_rapor', 'bobot_uas', "int DEFAULT '30'");

-- RPP table
CALL AddColumnIfNotExists('rpp', 'catatan_reviewer', 'text');
CALL AddColumnIfNotExists('rpp', 'reviewed_by', 'int DEFAULT NULL');
CALL AddColumnIfNotExists('rpp', 'reviewed_at', 'datetime DEFAULT NULL');

-- Clean up procedure
DROP PROCEDURE IF EXISTS AddColumnIfNotExists;

-- ============================================================================
-- PHASE 3: INSERT DATA DEFAULT (JIKA BELUM ADA)
-- ============================================================================

-- QR Config default
INSERT IGNORE INTO `qr_config` (`jenis`, `field_line1`, `field_line2`, `show_foto`, `show_qr`) VALUES
('guru', 'nama_guru', 'nik', 1, 1),
('siswa', 'nama_siswa', 'nisn', 1, 1);

-- Pengaturan RPP default (jika tabel kosong)
INSERT INTO `pengaturan_rpp` (`id`, `wajib_rpp_disetujui`, `blokir_absensi`, `blokir_jurnal`, `blokir_nilai`)
SELECT 1, 0, 0, 0, 0
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `pengaturan_rpp` WHERE id = 1);

-- ============================================================================
-- SELESAI - Re-enable foreign key checks
-- ============================================================================
SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================================
-- MIGRATION COMPLETE!
-- ============================================================================
