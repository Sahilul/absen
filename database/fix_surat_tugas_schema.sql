-- ============================================================================
-- FIX SURAT TUGAS TABLE SCHEMA
-- ============================================================================
-- Tabel surat_tugas di database tidak match dengan yang dibutuhkan controller
-- Jalankan SQL ini di phpMyAdmin untuk update schema
-- ============================================================================

-- Drop tabel lama jika ada (HATI-HATI: ini akan menghapus data!)
-- Jika tidak mau kehilangan data, gunakan ALTER TABLE di bawah

-- Option 1: Drop dan buat ulang (HAPUS DATA!)
DROP TABLE IF EXISTS surat_tugas_petugas;
DROP TABLE IF EXISTS surat_tugas;

-- Buat ulang dengan schema yang benar
CREATE TABLE `surat_tugas` (
    `id_surat` int NOT NULL AUTO_INCREMENT,
    `id_lembaga` int NOT NULL,
    `nomor_surat` varchar(100) NOT NULL,
    `tanggal_surat` date NOT NULL,
    `kota_surat` varchar(100) DEFAULT NULL,
    `perihal` text NOT NULL,
    `tempat_tugas` varchar(255) DEFAULT NULL,
    `tanggal_mulai` date NOT NULL,
    `tanggal_selesai` date DEFAULT NULL,
    `status` enum('draft','terbit','dibatalkan') DEFAULT 'terbit',
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `created_by` int DEFAULT NULL,
    PRIMARY KEY (`id_surat`),
    KEY `id_lembaga` (`id_lembaga`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `surat_tugas_petugas` (
    `id_petugas` int NOT NULL AUTO_INCREMENT,
    `id_surat` int NOT NULL,
    `nama_petugas` varchar(255) NOT NULL,
    `jenis_identitas` enum('NIP','NIK','NISN','Lainnya') DEFAULT 'NIK',
    `identitas_petugas` varchar(100) DEFAULT NULL,
    `jabatan_petugas` varchar(100) DEFAULT NULL,
    `pangkat_golongan` varchar(100) DEFAULT NULL,
    `keterangan` text,
    `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_petugas`),
    KEY `id_surat` (`id_surat`),
    CONSTRAINT `fk_petugas_surat` FOREIGN KEY (`id_surat`) REFERENCES `surat_tugas` (`id_surat`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================================
-- Option 2: ALTER TABLE (Jika mau keep data yang sudah ada)
-- Uncomment jika ingin pakai opsi ini:
-- ============================================================================

/*
-- Tambah kolom yang kurang
ALTER TABLE `surat_tugas` 
    ADD COLUMN `perihal` text AFTER `kota_surat`,
    ADD COLUMN `tempat_tugas` varchar(255) DEFAULT NULL AFTER `perihal`,
    ADD COLUMN `tanggal_mulai` date AFTER `tempat_tugas`,
    ADD COLUMN `tanggal_selesai` date DEFAULT NULL AFTER `tanggal_mulai`,
    ADD COLUMN `status` enum('draft','terbit','dibatalkan') DEFAULT 'terbit'  AFTER `tanggal_selesai`;

-- Hapus kolom lama yang tidak terpakai
ALTER TABLE `surat_tugas`
    DROP COLUMN `menimbang`,
    DROP COLUMN `dasar`,
    DROP COLUMN `untuk`;
    
-- Update kota_surat jadi nullable
ALTER TABLE `surat_tugas` 
    MODIFY COLUMN `kota_surat` varchar(100) DEFAULT NULL;
*/

-- ============================================================================
-- SELESAI
-- ============================================================================
