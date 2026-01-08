-- Migration: 1.8.6.sql
-- Fix Missing Column ukuran on psb_dokumen

ALTER TABLE psb_dokumen
ADD COLUMN IF NOT EXISTS ukuran INT DEFAULT 0 AFTER path_file;
