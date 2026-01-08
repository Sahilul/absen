-- Versi 1.8.10
-- Memastikan kolom drive_file_id dan drive_url ada di tabel siswa_dokumen

-- Note: Syntax ALERT TABLE IF NOT EXISTS kolom kadang beda antar versi MySQL.
-- Kita gunakan pendekatan aman: Add column dan ignore error jika sudah ada.

-- Mariadb/MySQL modern support IF NOT EXISTS
ALTER TABLE siswa_dokumen ADD COLUMN IF NOT EXISTS drive_file_id VARCHAR(255) NULL AFTER ukuran;
ALTER TABLE siswa_dokumen ADD COLUMN IF NOT EXISTS drive_url TEXT NULL AFTER drive_file_id;
