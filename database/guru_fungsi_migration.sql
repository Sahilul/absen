-- File: database/guru_fungsi_migration.sql
-- Migration for Guru Additional Functions (Bendahara, Petugas PSB, etc.)

-- Create guru_fungsi table
CREATE TABLE IF NOT EXISTS `guru_fungsi` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `id_guru` INT NOT NULL,
  `fungsi` ENUM('bendahara', 'petugas_psb', 'kurikulum', 'kesiswaan') NOT NULL,
  `id_tp` INT NOT NULL COMMENT 'Tahun Pelajaran aktif untuk fungsi ini',
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `created_by` INT DEFAULT NULL COMMENT 'ID admin yang assign fungsi',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_guru_fungsi_tp` (`id_guru`, `fungsi`, `id_tp`),
  KEY `idx_guru` (`id_guru`),
  KEY `idx_fungsi` (`fungsi`),
  KEY `idx_tp` (`id_tp`),
  CONSTRAINT `fk_guru_fungsi_guru` FOREIGN KEY (`id_guru`) REFERENCES `guru`(`id_guru`) ON DELETE CASCADE,
  CONSTRAINT `fk_guru_fungsi_tp` FOREIGN KEY (`id_tp`) REFERENCES `tp`(`id_tp`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Note: 
-- - Satu guru bisa punya multiple fungsi (bendahara + petugas_psb)
-- - Satu guru yang sudah jadi wali_kelas tetap bisa ditambahkan fungsi bendahara
-- - Fungsi berlaku per tahun pelajaran (id_tp)
