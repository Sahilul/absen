-- Migration: 1.8.4.sql
-- Fix Missing Column last_login on psb_akun

ALTER TABLE psb_akun
ADD COLUMN IF NOT EXISTS last_login DATETIME DEFAULT NULL;
