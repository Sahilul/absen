-- Migration 1.37.0
-- Fitur Kartu Ujian (admin): kartu peserta ujian + kartu meja
-- Kop otomatis per lembaga (MTs/MA) berdasarkan jenjang kelas siswa.

CREATE TABLE IF NOT EXISTS `kartu_ujian` (
  `id_kartu_ujian` INT(11) NOT NULL AUTO_INCREMENT,
  `nama_ujian` VARCHAR(200) NOT NULL,
  `id_tp` INT(11) NOT NULL,
  `id_semester` INT(11) NULL DEFAULT NULL,
  `created_by` INT(11) NULL DEFAULT NULL,
  `created_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_kartu_ujian`),
  KEY `idx_kartu_ujian_tp` (`id_tp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
