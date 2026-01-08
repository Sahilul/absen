-- Migration: 1.3.0.sql
-- Fitur Pesan (Messaging System)

-- Tabel utama pesan
CREATE TABLE IF NOT EXISTS pesan (
    id_pesan INT AUTO_INCREMENT PRIMARY KEY,
    pengirim_type ENUM('admin', 'guru', 'wali_kelas') NOT NULL,
    pengirim_id INT NOT NULL,
    judul VARCHAR(255) NOT NULL,
    isi TEXT NOT NULL,
    target_type ENUM('semua_guru', 'semua_siswa', 'kelas', 'guru_individual', 'siswa_individual') NOT NULL,
    target_id INT NULL COMMENT 'id_kelas atau id_guru/id_siswa jika individual',
    lampiran VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_pengirim (pengirim_type, pengirim_id),
    INDEX idx_target (target_type, target_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabel penerima pesan (untuk tracking status baca)
CREATE TABLE IF NOT EXISTS pesan_penerima (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_pesan INT NOT NULL,
    penerima_type ENUM('guru', 'siswa') NOT NULL,
    penerima_id INT NOT NULL,
    dibaca TINYINT(1) DEFAULT 0,
    dibaca_at TIMESTAMP NULL,
    FOREIGN KEY (id_pesan) REFERENCES pesan(id_pesan) ON DELETE CASCADE,
    INDEX idx_penerima (penerima_type, penerima_id),
    INDEX idx_dibaca (dibaca),
    INDEX idx_pesan_penerima (id_pesan, penerima_type, penerima_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tambah kolom no_wa ke tabel guru
ALTER TABLE guru ADD COLUMN IF NOT EXISTS no_wa VARCHAR(20) NULL AFTER email;
