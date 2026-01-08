-- ============================================================================
-- Google Drive Integration - Database Migration
-- ============================================================================
-- Jalankan SQL ini di phpMyAdmin untuk menambahkan kolom Google Drive
-- ============================================================================

-- 1. Tambah kolom di tabel pengaturan_aplikasi
ALTER TABLE pengaturan_aplikasi 
ADD COLUMN IF NOT EXISTS google_refresh_token TEXT DEFAULT NULL,
ADD COLUMN IF NOT EXISTS google_drive_folder_id VARCHAR(100) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS google_drive_enabled TINYINT(1) DEFAULT 0;

-- 2. Tambah kolom di tabel psb_dokumen
ALTER TABLE psb_dokumen 
ADD COLUMN IF NOT EXISTS drive_file_id VARCHAR(100) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS drive_url VARCHAR(500) DEFAULT NULL;

-- 3. Tambah kolom di tabel siswa_dokumen (jika ada)
-- Note: Cek dulu apakah tabel ini ada di database Anda
-- ALTER TABLE siswa_dokumen 
-- ADD COLUMN IF NOT EXISTS drive_file_id VARCHAR(100) DEFAULT NULL,
-- ADD COLUMN IF NOT EXISTS drive_url VARCHAR(500) DEFAULT NULL;

-- ============================================================================
-- ALTERNATIVE untuk MySQL < 8.0 (tidak support IF NOT EXISTS di ALTER)
-- ============================================================================

-- Untuk pengaturan_aplikasi, gunakan prosedur berikut:
-- (Jalankan satu per satu jika ALTER di atas error)

-- SET @exist := (SELECT COUNT(*) FROM information_schema.COLUMNS 
--     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'pengaturan_aplikasi' AND COLUMN_NAME = 'google_refresh_token');
-- SET @sqlstmt := IF(@exist = 0, 'ALTER TABLE pengaturan_aplikasi ADD COLUMN google_refresh_token TEXT DEFAULT NULL', 'SELECT 1');
-- PREPARE stmt FROM @sqlstmt;
-- EXECUTE stmt;
-- DEALLOCATE PREPARE stmt;

-- SET @exist := (SELECT COUNT(*) FROM information_schema.COLUMNS 
--     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'pengaturan_aplikasi' AND COLUMN_NAME = 'google_drive_folder_id');
-- SET @sqlstmt := IF(@exist = 0, 'ALTER TABLE pengaturan_aplikasi ADD COLUMN google_drive_folder_id VARCHAR(100) DEFAULT NULL', 'SELECT 1');
-- PREPARE stmt FROM @sqlstmt;
-- EXECUTE stmt;
-- DEALLOCATE PREPARE stmt;

-- SET @exist := (SELECT COUNT(*) FROM information_schema.COLUMNS 
--     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'pengaturan_aplikasi' AND COLUMN_NAME = 'google_drive_enabled');
-- SET @sqlstmt := IF(@exist = 0, 'ALTER TABLE pengaturan_aplikasi ADD COLUMN google_drive_enabled TINYINT(1) DEFAULT 0', 'SELECT 1');
-- PREPARE stmt FROM @sqlstmt;
-- EXECUTE stmt;
-- DEALLOCATE PREPARE stmt;

-- ============================================================================
-- SELESAI
-- ============================================================================
