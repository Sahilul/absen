-- Migration 1.5.0: Buku Tamu Enhancements
-- Date: 2026-01-01

-- Add catatan column to buku_tamu table
ALTER TABLE buku_tamu ADD COLUMN IF NOT EXISTS catatan TEXT NULL AFTER bertemu_dengan;

-- Update foto_url to use new Google Drive format (lh3.googleusercontent.com)
UPDATE buku_tamu 
SET foto_url = REPLACE(foto_url, 'https://drive.google.com/uc?id=', 'https://lh3.googleusercontent.com/d/') 
WHERE foto_url LIKE '%drive.google.com/uc%';

-- Add petugas_buku_tamu to guru_fungsi enum
ALTER TABLE guru_fungsi MODIFY COLUMN fungsi ENUM('bendahara','petugas_psb','admin_cms','kurikulum','kesiswaan','petugas_buku_tamu') NOT NULL;
