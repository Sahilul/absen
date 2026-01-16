-- Migration: 1.13.5.sql
-- Mobile App API - User Devices Table for FCM Push Notifications

-- Table untuk menyimpan FCM token devices
CREATE TABLE IF NOT EXISTS user_devices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    fcm_token VARCHAR(500) NOT NULL,
    device_type ENUM('android', 'ios', 'web') DEFAULT 'android',
    device_name VARCHAR(255) DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_fcm_token (fcm_token(100)),
    UNIQUE KEY unique_fcm_token (fcm_token(255))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add id_ref to users table if not exists (for linking to guru/siswa)
-- This column may already exist
-- ALTER TABLE users ADD COLUMN IF NOT EXISTS id_ref INT DEFAULT NULL AFTER role;
