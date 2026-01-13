-- Migration for version 1.10.0
-- Date: 2026-01-13
-- Description: PSB Letterhead (Kop Surat) feature

-- Add kop surat columns to psb_lembaga table
ALTER TABLE psb_lembaga 
ADD COLUMN IF NOT EXISTS kop_logo VARCHAR(255) NULL,
ADD COLUMN IF NOT EXISTS kop_nama VARCHAR(255) NULL,
ADD COLUMN IF NOT EXISTS kop_alamat TEXT NULL,
ADD COLUMN IF NOT EXISTS kop_telp VARCHAR(50) NULL,
ADD COLUMN IF NOT EXISTS kop_email VARCHAR(100) NULL,
ADD COLUMN IF NOT EXISTS kop_website VARCHAR(100) NULL,
ADD COLUMN IF NOT EXISTS kop_gambar VARCHAR(255) NULL;

-- Add drive columns to siswa_dokumen if not exists
ALTER TABLE siswa_dokumen 
ADD COLUMN IF NOT EXISTS drive_file_id VARCHAR(255) NULL,
ADD COLUMN IF NOT EXISTS drive_url TEXT NULL;
