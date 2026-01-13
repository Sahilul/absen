-- Migration: Add cron_secret to pengaturan_aplikasi
-- Version: 1.11.4

ALTER TABLE `pengaturan_aplikasi` ADD COLUMN `cron_secret` varchar(100) DEFAULT 'wa_queue_secret_2026' AFTER `wa_gateway_password`;
