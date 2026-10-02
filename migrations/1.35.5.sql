-- =============================================
-- Migration v1.35.5 — Fitur "Siap Cetak" Soal STS
-- =============================================
-- Guru menandai soal siap cetak setelah lengkap.
-- Setelah ditandai, soal terkunci dari edit/hapus.
-- Hanya admin yang bisa membatalkan.

ALTER TABLE `soal_sts_pengaturan`
    ADD COLUMN `siap_cetak_at` DATETIME NULL DEFAULT NULL COMMENT 'Waktu guru menandai siap cetak' AFTER `dicetak_by`,
    ADD COLUMN `siap_cetak_by` INT NULL DEFAULT NULL COMMENT 'User ID yang menandai siap cetak' AFTER `siap_cetak_at`;
