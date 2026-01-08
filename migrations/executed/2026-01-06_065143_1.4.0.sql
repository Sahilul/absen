-- ============================================
-- Migration 1.4.0 - Buku Tamu Digital
-- ============================================

-- Tabel Lembaga
CREATE TABLE IF NOT EXISTS buku_tamu_lembaga (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_lembaga VARCHAR(255) NOT NULL,
    kode_lembaga VARCHAR(50) UNIQUE,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel Link Undangan
CREATE TABLE IF NOT EXISTS buku_tamu_link (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_lembaga INT NOT NULL,
    token VARCHAR(100) UNIQUE NOT NULL,
    nama_tamu VARCHAR(255),
    no_wa_tamu VARCHAR(20),
    keperluan_prefill TEXT,
    expired_at DATETIME NOT NULL,
    used TINYINT(1) DEFAULT 0,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_token (token),
    INDEX idx_lembaga (id_lembaga),
    FOREIGN KEY (id_lembaga) REFERENCES buku_tamu_lembaga(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel Data Tamu
CREATE TABLE IF NOT EXISTS buku_tamu (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_link INT,
    id_lembaga INT NOT NULL,
    nama_tamu VARCHAR(255) NOT NULL,
    instansi VARCHAR(255),
    no_hp VARCHAR(20) NOT NULL,
    email VARCHAR(255),
    keperluan TEXT NOT NULL,
    bertemu_dengan VARCHAR(255),
    foto_drive_id VARCHAR(255),
    foto_url VARCHAR(500),
    waktu_datang DATETIME DEFAULT CURRENT_TIMESTAMP,
    waktu_pulang DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_lembaga (id_lembaga),
    INDEX idx_waktu (waktu_datang),
    FOREIGN KEY (id_link) REFERENCES buku_tamu_link(id) ON DELETE SET NULL,
    FOREIGN KEY (id_lembaga) REFERENCES buku_tamu_lembaga(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default lembaga
INSERT IGNORE INTO buku_tamu_lembaga (nama_lembaga, kode_lembaga) VALUES
('TK Islam Sabilillah', 'TK'),
('SD Islam Sabilillah', 'SD'),
('SMP Islam Sabilillah', 'SMP'),
('Yayasan Sabilillah', 'YYS');
