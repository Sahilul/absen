-- Migration: v1.16.0 - Dynamic WA Group Template
-- Description: Adding column to store custom WA template for group attendance

ALTER TABLE `pengaturan_aplikasi`
ADD COLUMN IF NOT EXISTS `wa_template_group_absensi` TEXT NULL COMMENT 'Template pesan WA ke grup absensi' AFTER `admin_wa_number`;
