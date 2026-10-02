-- =============================================
-- Migration v1.35.4 — Penanda "Sudah Dicetak" untuk Soal STS
-- =============================================
-- Menambahkan kolom untuk menandai apakah soal STS suatu mapel/kelas
-- sudah pernah dicetak (preview cetak / PDF / kunci / cetak dengan nama).
-- Berguna bagi admin untuk melihat mana yang sudah/belum dicetak.

ALTER TABLE `soal_sts_pengaturan`
    ADD COLUMN `dicetak_at` DATETIME NULL DEFAULT NULL COMMENT 'Waktu terakhir soal dicetak' AFTER `petunjuk_umum`,
    ADD COLUMN `dicetak_by` INT NULL DEFAULT NULL COMMENT 'User ID yang mencetak terakhir' AFTER `dicetak_at`;
