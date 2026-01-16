-- Migration: 1.13.8.sql
-- Mobile App Settings table

-- Create app_settings table for key-value settings
CREATE TABLE IF NOT EXISTS `app_settings` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(100) NOT NULL,
    `value` text,
    `created_at` datetime DEFAULT NULL,
    `updated_at` datetime DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default Firebase settings
INSERT INTO app_settings (name, value, created_at) VALUES 
('firebase_project_id', '', NOW()),
('firebase_client_email', '', NOW()),
('firebase_private_key', '', NOW()),
('google_client_id', '', NOW()),
('mobile_app_enabled', '1', NOW())
ON DUPLICATE KEY UPDATE updated_at = NOW();
