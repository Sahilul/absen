-- =====================================================
-- SCRIPT SINKRONISASI LOKAL dari HOSTING
-- Jalankan di MySQL lokal (Laragon)
-- Kolom-kolom yang ada di hosting tapi tidak di lokal
-- Dibuat: 2025-12-31
-- =====================================================

-- Tambah kolom di pengaturan_rapor yang ada di hosting tapi tidak di lokal
ALTER TABLE `pengaturan_rapor` 
ADD COLUMN `kota` varchar(100) DEFAULT NULL AFTER `logo_madrasah`,
ADD COLUMN `bobot_harian` int(11) DEFAULT 40 AFTER `kota`,
ADD COLUMN `bobot_uts` int(11) DEFAULT 30 AFTER `bobot_harian`,
ADD COLUMN `bobot_uas` int(11) DEFAULT 30 AFTER `bobot_uts`;

-- Tabel qr_config (jika ingin menambahkan)
CREATE TABLE IF NOT EXISTS `qr_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `jenis` enum('guru','siswa') NOT NULL,
  `field_line1` varchar(50) DEFAULT NULL,
  `field_line2` varchar(50) DEFAULT NULL,
  `field_line3` varchar(50) DEFAULT NULL,
  `show_foto` tinyint(1) DEFAULT 1,
  `show_qr` tinyint(1) DEFAULT 1,
  `qr_size` int(11) DEFAULT 100,
  `template` varchar(50) DEFAULT 'default',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert default qr_config
INSERT INTO `qr_config` (`jenis`, `field_line1`, `field_line2`, `field_line3`, `show_foto`, `show_qr`, `qr_size`, `template`) VALUES
('guru', 'nama_guru', 'nik', NULL, 1, 1, 100, 'default'),
('siswa', 'nama_siswa', 'nisn', NULL, 1, 1, 100, 'default')
ON DUPLICATE KEY UPDATE jenis=VALUES(jenis);

-- Tabel pengaturan_menu (jika ingin menambahkan)
CREATE TABLE IF NOT EXISTS `pengaturan_menu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `menu_key` varchar(50) NOT NULL,
  `is_visible` tinyint(1) DEFAULT 1,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =====================================================
-- SELESAI
-- =====================================================
