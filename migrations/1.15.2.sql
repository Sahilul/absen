-- Migration: Visitor Counter
-- Date: 2026-01-27
-- Description: Menambahkan tabel untuk tracking pengunjung website

-- Tabel untuk menyimpan data pengunjung
CREATE TABLE IF NOT EXISTS `website_visitors` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `ip_address` VARCHAR(45) NOT NULL,
    `user_agent` TEXT,
    `page_url` VARCHAR(500) DEFAULT '/',
    `visit_date` DATE NOT NULL,
    `visit_time` TIME NOT NULL,
    `country` VARCHAR(100) DEFAULT NULL,
    `city` VARCHAR(100) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_visit_date` (`visit_date`),
    INDEX `idx_ip_date` (`ip_address`, `visit_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel untuk ringkasan statistik harian (untuk performa query)
CREATE TABLE IF NOT EXISTS `visitor_stats` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `stat_date` DATE NOT NULL UNIQUE,
    `total_hits` INT DEFAULT 0,
    `unique_visitors` INT DEFAULT 0,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
