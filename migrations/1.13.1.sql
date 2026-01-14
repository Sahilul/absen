-- ==============================================
-- Migration: v1.13.0 - Multi WA Account Rotation
-- ==============================================

-- Tabel untuk menyimpan multiple akun WA Gateway
CREATE TABLE IF NOT EXISTS `wa_accounts` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `nama` VARCHAR(100) NOT NULL COMMENT 'Nama akun (misal: WA Utama)',
    `provider` ENUM('fonnte', 'gowa', 'wablas', 'dripsender', 'starsender', 'onesender') NOT NULL DEFAULT 'fonnte',
    `api_url` VARCHAR(255) DEFAULT NULL,
    `token` TEXT DEFAULT NULL COMMENT 'API Token',
    `username` VARCHAR(100) DEFAULT NULL COMMENT 'Username (jika ada)',
    `password` VARCHAR(100) DEFAULT NULL COMMENT 'Password (jika ada)',
    `is_active` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=Aktif, 0=Nonaktif',
    `daily_limit` INT(11) NOT NULL DEFAULT 100 COMMENT 'Limit pesan per hari (0 = unlimited)',
    `today_sent` INT(11) NOT NULL DEFAULT 0 COMMENT 'Counter pesan hari ini',
    `last_used_at` DATETIME DEFAULT NULL COMMENT 'Terakhir digunakan',
    `last_reset_date` DATE DEFAULT NULL COMMENT 'Tanggal terakhir reset counter',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_is_active` (`is_active`),
    KEY `idx_provider` (`provider`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tambah kolom rotasi di pengaturan_aplikasi
ALTER TABLE `pengaturan_aplikasi` 
ADD COLUMN IF NOT EXISTS `wa_rotation_enabled` TINYINT(1) NOT NULL DEFAULT 0 
COMMENT '1=Gunakan rotasi multi akun, 0=Gunakan akun default' 
AFTER `wa_gateway_password`;

ALTER TABLE `pengaturan_aplikasi` 
ADD COLUMN IF NOT EXISTS `wa_rotation_mode` ENUM('round_robin', 'random', 'load_balance') NOT NULL DEFAULT 'round_robin' 
COMMENT 'Mode rotasi: round_robin, random, load_balance' 
AFTER `wa_rotation_enabled`;

ALTER TABLE `pengaturan_aplikasi` 
ADD COLUMN IF NOT EXISTS `wa_last_account_id` INT(11) UNSIGNED DEFAULT NULL 
COMMENT 'ID akun terakhir yang digunakan (untuk round robin)' 
AFTER `wa_rotation_mode`;

-- Index untuk wa_message_queue (tambah kolom untuk tracking akun)
ALTER TABLE `wa_message_queue` 
ADD COLUMN IF NOT EXISTS `wa_account_id` INT(11) UNSIGNED DEFAULT NULL 
COMMENT 'ID akun WA yang mengirim' 
AFTER `status`;

-- Event untuk reset counter harian (opsional - bisa juga via cron)
-- Note: Uncomment jika MySQL Event Scheduler aktif
/*
DELIMITER $$
CREATE EVENT IF NOT EXISTS `reset_wa_account_daily_counter`
ON SCHEDULE EVERY 1 DAY
STARTS CONCAT(CURDATE() + INTERVAL 1 DAY, ' 00:00:00')
DO
BEGIN
    UPDATE `wa_accounts` 
    SET `today_sent` = 0, `last_reset_date` = CURDATE()
    WHERE `last_reset_date` IS NULL OR `last_reset_date` < CURDATE();
END$$
DELIMITER ;
*/

-- Kolom untuk nomor WA admin (menerima notifikasi blokir)
ALTER TABLE `pengaturan_aplikasi` 
ADD COLUMN IF NOT EXISTS `admin_wa_number` VARCHAR(20) DEFAULT NULL 
COMMENT 'Nomor WA admin untuk notifikasi blokir' 
AFTER `wa_last_account_id`;
