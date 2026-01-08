-- Migration: 1.2.0.sql
-- Add is_default column to semester table for admin-configurable default semester

ALTER TABLE semester ADD COLUMN IF NOT EXISTS is_default TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Semester default untuk login';

-- Reset all to 0 first, then set the latest one as default (optional)
-- UPDATE semester SET is_default = 0;
