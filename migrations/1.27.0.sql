-- Migration: 1.27.0
-- Fitur: Izin Siswa oleh Wali Kelas
-- Tanggal: 2025-02-XX

-- Tabel izin siswa
CREATE TABLE IF NOT EXISTS `izin_siswa` (
  `id_izin` INT AUTO_INCREMENT PRIMARY KEY,
  `id_siswa` INT NOT NULL,
  `id_kelas` INT NOT NULL,
  `id_tp` INT NOT NULL,
  `jenis_izin` ENUM('I','S','D') NOT NULL COMMENT 'I=Izin, S=Sakit, D=Dispensasi',
  `tanggal_mulai` DATE NOT NULL,
  `tanggal_selesai` DATE NOT NULL,
  `keterangan` TEXT,
  `bukti_file` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('aktif','selesai','dibatalkan') DEFAULT 'aktif',
  `id_guru_input` INT DEFAULT NULL COMMENT 'ID guru wali kelas yang menginput',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_siswa_tanggal` (`id_siswa`, `tanggal_mulai`, `tanggal_selesai`),
  INDEX `idx_kelas_tp` (`id_kelas`, `id_tp`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tambah status 'D' (Dispensasi) ke enum absensi
ALTER TABLE `absensi`
  MODIFY COLUMN `status_kehadiran` ENUM('H','I','S','A','T','D') DEFAULT 'H';
