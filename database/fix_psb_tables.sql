-- ============================================================================
-- PSB: SIMPLE FIX SCRIPT
-- ============================================================================
-- Jalankan query ini SATU PER SATU di phpMyAdmin jika ada error
-- ============================================================================

-- 1. Tabel psb_pengaturan
CREATE TABLE IF NOT EXISTS `psb_pengaturan` (
    `id` int NOT NULL AUTO_INCREMENT,
    `judul_halaman` varchar(255) DEFAULT 'Penerimaan Siswa Baru',
    `deskripsi` text,
    `syarat_pendaftaran` text,
    `alur_pendaftaran` text,
    `kontak_info` text,
    `wa_gateway_url` varchar(255) DEFAULT NULL,
    `wa_gateway_token` varchar(255) DEFAULT NULL,
    `brosur_gambar` varchar(255) DEFAULT NULL,
    `tentang_sekolah` text,
    `keunggulan` text,
    `visi_misi` text,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT IGNORE INTO `psb_pengaturan` (`id`, `judul_halaman`, `deskripsi`) VALUES
(1, 'Penerimaan Siswa Baru', 'Selamat datang di portal PSB');

-- 2. Tabel psb_lembaga (buat baru atau recreate)
DROP TABLE IF EXISTS `psb_lembaga`;
CREATE TABLE `psb_lembaga` (
    `id_lembaga` int NOT NULL AUTO_INCREMENT,
    `kode_lembaga` varchar(20) DEFAULT NULL,
    `nama_lembaga` varchar(255) NOT NULL,
    `jenjang` varchar(50) DEFAULT NULL,
    `alamat` text,
    `kuota_default` int DEFAULT 0,
    `urutan` int DEFAULT 0,
    `aktif` tinyint(1) DEFAULT 1,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_lembaga`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 3. Tabel psb_jalur (buat baru atau recreate)
DROP TABLE IF EXISTS `psb_jalur`;
CREATE TABLE `psb_jalur` (
    `id_jalur` int NOT NULL AUTO_INCREMENT,
    `kode_jalur` varchar(20) DEFAULT NULL,
    `nama_jalur` varchar(100) NOT NULL,
    `deskripsi` text,
    `persyaratan` text,
    `urutan` int DEFAULT 0,
    `aktif` tinyint(1) DEFAULT 1,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_jalur`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 4. Tabel psb_periode (buat baru atau recreate)
DROP TABLE IF EXISTS `psb_periode`;
CREATE TABLE `psb_periode` (
    `id_periode` int NOT NULL AUTO_INCREMENT,
    `id_lembaga` int NOT NULL,
    `id_tp` int DEFAULT NULL,
    `nama_periode` varchar(100) NOT NULL,
    `tanggal_buka` date DEFAULT NULL,
    `tanggal_tutup` date DEFAULT NULL,
    `kuota` int DEFAULT 0,
    `biaya_pendaftaran` decimal(12,2) DEFAULT 0.00,
    `status` enum('draft','aktif','selesai') DEFAULT 'draft',
    `keterangan` text,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_periode`),
    KEY `id_lembaga` (`id_lembaga`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 5. Tabel psb_kuota_jalur
DROP TABLE IF EXISTS `psb_kuota_jalur`;
CREATE TABLE `psb_kuota_jalur` (
    `id` int NOT NULL AUTO_INCREMENT,
    `id_periode` int NOT NULL,
    `id_jalur` int NOT NULL,
    `kuota` int DEFAULT 0,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_periode_jalur` (`id_periode`,`id_jalur`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 6. Tabel psb_pendaftar
DROP TABLE IF EXISTS `psb_pendaftar`;
CREATE TABLE `psb_pendaftar` (
    `id_pendaftar` int NOT NULL AUTO_INCREMENT,
    `id_periode` int NOT NULL,
    `id_jalur` int NOT NULL,
    `id_akun` int DEFAULT NULL,
    `no_pendaftaran` varchar(50) NOT NULL,
    `nisn` varchar(20) DEFAULT NULL,
    `nik` varchar(20) DEFAULT NULL,
    `nama_lengkap` varchar(100) NOT NULL,
    `jenis_kelamin` enum('L','P') NOT NULL,
    `tempat_lahir` varchar(100) DEFAULT NULL,
    `tanggal_lahir` date DEFAULT NULL,
    `agama` varchar(20) DEFAULT NULL,
    `alamat` text,
    `rt` varchar(5) DEFAULT NULL,
    `rw` varchar(5) DEFAULT NULL,
    `kelurahan` varchar(100) DEFAULT NULL,
    `kecamatan` varchar(100) DEFAULT NULL,
    `kota` varchar(100) DEFAULT NULL,
    `provinsi` varchar(100) DEFAULT NULL,
    `kode_pos` varchar(10) DEFAULT NULL,
    `no_hp` varchar(20) DEFAULT NULL,
    `email` varchar(100) DEFAULT NULL,
    `nama_ayah` varchar(100) DEFAULT NULL,
    `pekerjaan_ayah` varchar(100) DEFAULT NULL,
    `no_hp_ayah` varchar(20) DEFAULT NULL,
    `nama_ibu` varchar(100) DEFAULT NULL,
    `pekerjaan_ibu` varchar(100) DEFAULT NULL,
    `no_hp_ibu` varchar(20) DEFAULT NULL,
    `nama_wali` varchar(100) DEFAULT NULL,
    `hubungan_wali` varchar(50) DEFAULT NULL,
    `no_hp_wali` varchar(20) DEFAULT NULL,
    `asal_sekolah` varchar(255) DEFAULT NULL,
    `npsn_asal` varchar(20) DEFAULT NULL,
    `alamat_sekolah_asal` text,
    `tahun_lulus` year DEFAULT NULL,
    `foto` varchar(255) DEFAULT NULL,
    `status` enum('draft','pending','verifikasi','revisi','diterima','ditolak','selesai') DEFAULT 'draft',
    `catatan_admin` text,
    `tanggal_daftar` timestamp DEFAULT CURRENT_TIMESTAMP,
    `tanggal_verifikasi` datetime DEFAULT NULL,
    `tanggal_keputusan` datetime DEFAULT NULL,
    `verified_by` int DEFAULT NULL,
    `id_kelas_diterima` int DEFAULT NULL,
    `id_siswa` int DEFAULT NULL,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_pendaftar`),
    UNIQUE KEY `no_pendaftaran` (`no_pendaftaran`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 7. Tabel psb_dokumen
DROP TABLE IF EXISTS `psb_dokumen`;
CREATE TABLE `psb_dokumen` (
    `id_dokumen` int NOT NULL AUTO_INCREMENT,
    `id_pendaftar` int NOT NULL,
    `jenis_dokumen` varchar(50) NOT NULL,
    `nama_file` varchar(255) DEFAULT NULL,
    `path_file` varchar(255) DEFAULT NULL,
    `uploaded_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_dokumen`),
    KEY `id_pendaftar` (`id_pendaftar`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 8. Tabel psb_akun
DROP TABLE IF EXISTS `psb_akun`;
CREATE TABLE `psb_akun` (
    `id_akun` int NOT NULL AUTO_INCREMENT,
    `nisn` varchar(20) NOT NULL,
    `password` varchar(255) NOT NULL,
    `nama_lengkap` varchar(100) NOT NULL,
    `no_wa` varchar(20) DEFAULT NULL,
    `email` varchar(100) DEFAULT NULL,
    `status` enum('aktif','nonaktif') DEFAULT 'aktif',
    `reset_token` varchar(100) DEFAULT NULL,
    `reset_token_expiry` datetime DEFAULT NULL,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_akun`),
    UNIQUE KEY `nisn` (`nisn`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================================
-- SELESAI! Halaman PSB seharusnya sudah bisa diakses
-- ============================================================================
