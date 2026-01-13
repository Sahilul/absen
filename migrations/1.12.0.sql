-- Migration: Fitur Riwayat Login
-- Version: 1.12.0

-- Tabel untuk menyimpan riwayat login
CREATE TABLE IF NOT EXISTS `login_history` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) DEFAULT NULL,
  `username` VARCHAR(100) NOT NULL,
  `nama_lengkap` VARCHAR(255) DEFAULT NULL,
  `role` VARCHAR(50) DEFAULT NULL,
  `ip_address` VARCHAR(45) NOT NULL,
  `user_agent` TEXT,
  `status` ENUM('success', 'failed') NOT NULL DEFAULT 'success',
  `failure_reason` VARCHAR(255) DEFAULT NULL,
  `login_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_login_at` (`login_at`),
  KEY `idx_status` (`status`),
  KEY `idx_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Event untuk auto-delete data lebih dari 90 hari (opsional, jika MySQL Event Scheduler aktif)
-- Jika tidak aktif, bisa diabaikan dan cleanup dilakukan via cron PHP
-- DELIMITER //
-- CREATE EVENT IF NOT EXISTS cleanup_login_history
-- ON SCHEDULE EVERY 1 DAY
-- DO
-- BEGIN
--   DELETE FROM login_history WHERE login_at < DATE_SUB(NOW(), INTERVAL 90 DAY);
-- END //
-- DELIMITER ;
