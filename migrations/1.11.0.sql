-- Migration for version 1.11.0
-- Date: 2026-01-13
-- Description: WhatsApp Message Queue System

-- Tabel antrian pesan WhatsApp
CREATE TABLE IF NOT EXISTS wa_message_queue (
    id INT AUTO_INCREMENT PRIMARY KEY,
    no_wa VARCHAR(20) NOT NULL,
    jenis VARCHAR(50) NOT NULL DEFAULT 'general',
    pesan TEXT NOT NULL,
    status ENUM('pending', 'processing', 'sent', 'failed') DEFAULT 'pending',
    attempts INT DEFAULT 0,
    max_attempts INT DEFAULT 3,
    error_message TEXT NULL,
    metadata JSON NULL,
    scheduled_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    sent_at DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_status (status),
    INDEX idx_scheduled (scheduled_at),
    INDEX idx_jenis (jenis)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel log pengiriman (opsional, untuk audit)
CREATE TABLE IF NOT EXISTS wa_message_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    queue_id INT NULL,
    no_wa VARCHAR(20) NOT NULL,
    jenis VARCHAR(50) NOT NULL,
    status ENUM('sent', 'failed') NOT NULL,
    response TEXT NULL,
    sent_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_queue_id (queue_id),
    INDEX idx_sent_at (sent_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
