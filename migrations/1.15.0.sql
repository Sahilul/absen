-- Migration: 1.15.0 - WhatsApp Group Absence Notification
-- Date: 2026-01-20
-- Description: Create kelas_grup_wa table for multiple WA groups per class

-- Create kelas_grup_wa table for multiple groups per class
CREATE TABLE IF NOT EXISTS kelas_grup_wa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_kelas INT NOT NULL,
    nama_grup VARCHAR(100) NOT NULL COMMENT 'Label grup (misal: Grup Ortu, Grup Umum)',
    grup_wa_id VARCHAR(50) NOT NULL COMMENT 'Nomor/ID Grup WA',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_kelas) REFERENCES kelas(id_kelas) ON DELETE CASCADE,
    INDEX idx_kelas (id_kelas),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add notification mode settings
INSERT INTO pengaturan_sistem (key_name, value, description) VALUES
('wa_notif_absensi_mode', 'personal', 'Mode notifikasi absensi: personal, grup, both, off')
ON DUPLICATE KEY UPDATE description = VALUES(description);
