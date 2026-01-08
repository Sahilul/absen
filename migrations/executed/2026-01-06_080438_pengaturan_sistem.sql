-- Migration: Pengaturan Sistem
-- Memindahkan setting dari config.php ke database agar bisa diedit via web

CREATE TABLE IF NOT EXISTS `pengaturan_sistem` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `key_name` VARCHAR(100) NOT NULL,
    `value` TEXT DEFAULT NULL,
    `description` VARCHAR(255) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_key` (`key_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default values
INSERT INTO `pengaturan_sistem` (`key_name`, `value`, `description`) VALUES
('secret_key', 'absen_qr_secret_key_2024_change_in_production', 'Secret key untuk enkripsi QR token'),
('qr_enabled', '1', 'Enable/disable QR di PDF'),
('google_oauth_enabled', '1', 'Enable/disable Google Login'),
('google_client_id', '', 'Google OAuth Client ID'),
('google_client_secret', '', 'Google OAuth Client Secret'),
('google_allowed_domain', '', 'Domain email yang diizinkan untuk Google OAuth'),
('menu_input_nilai_enabled', '1', 'Show/hide menu Input Nilai'),
('menu_pembayaran_enabled', '1', 'Show/hide menu Pembayaran'),
('menu_rapor_enabled', '1', 'Show/hide menu Rapor')
ON DUPLICATE KEY UPDATE `key_name` = `key_name`;
