-- =====================================================
-- SCRIPT MIGRASI DATABASE HOSTING SABILILLAH
-- Jalankan di phpMyAdmin pada database hosting
-- Dibuat: 2025-12-31
-- 
-- PANDUAN:
-- 1. Backup database hosting SEBELUM menjalankan script ini
-- 2. Jalankan script ini di phpMyAdmin tab SQL
-- 3. Jika ada error, cek pesan error dan sesuaikan
-- =====================================================

-- =====================================================
-- BAGIAN 1: TABEL YANG HARUS DIBUAT (TIDAK ADA DI HOSTING)
-- =====================================================

-- Tabel guru_fungsi (untuk fitur bendahara, petugas_psb, admin_cms)
CREATE TABLE IF NOT EXISTS `guru_fungsi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_guru` int(11) NOT NULL,
  `fungsi` enum('bendahara','petugas_psb','admin_cms','kurikulum','kesiswaan') NOT NULL,
  `id_tp` int(11) NOT NULL COMMENT 'Tahun Pelajaran aktif untuk fungsi ini',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` int(11) DEFAULT NULL COMMENT 'ID admin yang assign fungsi',
  PRIMARY KEY (`id`),
  KEY `id_guru` (`id_guru`),
  KEY `fungsi` (`fungsi`),
  KEY `id_tp` (`id_tp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabel siswa_dokumen (untuk fitur upload dokumen siswa)
CREATE TABLE IF NOT EXISTS `siswa_dokumen` (
  `id_dokumen` int(11) NOT NULL AUTO_INCREMENT,
  `id_siswa` int(11) NOT NULL,
  `jenis_dokumen` varchar(30) NOT NULL,
  `nama_file` varchar(255) NOT NULL,
  `path_file` varchar(255) NOT NULL,
  `ukuran` int(11) DEFAULT 0,
  `status` enum('pending','diterima','ditolak') DEFAULT 'pending',
  `catatan` varchar(255) DEFAULT NULL,
  `uploaded_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `drive_file_id` varchar(100) DEFAULT NULL,
  `drive_url` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`id_dokumen`),
  KEY `id_siswa` (`id_siswa`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabel pengaturan_dokumen (untuk konfigurasi jenis dokumen)
CREATE TABLE IF NOT EXISTS `pengaturan_dokumen` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kode` varchar(50) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `icon` varchar(50) DEFAULT 'file-text',
  `wajib_psb` tinyint(1) DEFAULT 0,
  `wajib_siswa` tinyint(1) DEFAULT 0,
  `aktif` tinyint(1) DEFAULT 1,
  `urutan` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode` (`kode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default pengaturan_dokumen
INSERT INTO `pengaturan_dokumen` (`kode`, `nama`, `icon`, `wajib_psb`, `wajib_siswa`, `aktif`, `urutan`) VALUES
('kartu_keluarga', 'Kartu Keluarga (KK)', 'file-text', 1, 1, 1, 1),
('akta_kelahiran', 'Akta Kelahiran', 'file-text', 1, 1, 1, 2),
('ktp_ayah', 'KTP Ayah', 'id-card', 1, 0, 1, 3),
('ktp_ibu', 'KTP Ibu', 'id-card', 1, 0, 1, 4),
('ijazah', 'Ijazah/SKL', 'award', 1, 1, 1, 5),
('foto', 'Pas Foto', 'image', 0, 1, 1, 6),
('kip', 'Kartu Indonesia Pintar (KIP)', 'credit-card', 0, 0, 1, 7),
('kis_kks', 'KIS/KKS', 'heart', 0, 0, 1, 8),
('pkh', 'PKH', 'wallet', 0, 0, 1, 9),
('skhun', 'SKHUN', 'file-text', 0, 0, 1, 10),
('rapor', 'Rapor', 'book-open', 0, 0, 1, 11),
('surat_pindah', 'Surat Pindah', 'mail', 0, 0, 1, 12),
('sktm', 'SKTM', 'file-check', 0, 0, 1, 13)
ON DUPLICATE KEY UPDATE nama=VALUES(nama);

-- Tabel pengaturan_jadwal (untuk fitur jadwal pelajaran)
CREATE TABLE IF NOT EXISTS `pengaturan_jadwal` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_pengaturan` varchar(50) NOT NULL,
  `nilai` text DEFAULT NULL,
  `keterangan` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nama_pengaturan` (`nama_pengaturan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert default pengaturan_jadwal
INSERT INTO `pengaturan_jadwal` (`nama_pengaturan`, `nilai`, `keterangan`) VALUES
('durasi_jam', '45', 'Durasi 1 jam pelajaran dalam menit'),
('jam_mulai', '07:00', 'Jam mulai pelajaran'),
('jam_selesai', '14:00', 'Jam selesai pelajaran'),
('hari_aktif', 'Senin,Selasa,Rabu,Kamis,Jumat,Sabtu', 'Hari aktif sekolah'),
('tracking_ruangan', '0', 'Aktifkan tracking ruangan (0/1)')
ON DUPLICATE KEY UPDATE keterangan=VALUES(keterangan);

-- =====================================================
-- BAGIAN 2: ALTER TABLE pengaturan_aplikasi
-- Tambah kolom yang kurang di hosting
-- =====================================================

-- Kolom wa_gateway_provider
ALTER TABLE `pengaturan_aplikasi` ADD COLUMN `wa_gateway_provider` varchar(50) DEFAULT 'fonnte' AFTER `favicon`;

-- Kolom url_web (mungkin sudah ada dengan nama berbeda)
-- ALTER TABLE `pengaturan_aplikasi` ADD COLUMN `url_web` varchar(255) DEFAULT 'http://localhost/absen' AFTER `nama_aplikasi`;

-- Kolom wa_gateway_username dan wa_gateway_password
ALTER TABLE `pengaturan_aplikasi` ADD COLUMN `wa_gateway_username` varchar(100) DEFAULT '' AFTER `wa_gateway_token`;
ALTER TABLE `pengaturan_aplikasi` ADD COLUMN `wa_gateway_password` varchar(100) DEFAULT '' AFTER `wa_gateway_username`;

-- Kolom Google Drive
ALTER TABLE `pengaturan_aplikasi` ADD COLUMN `google_refresh_token` text DEFAULT NULL AFTER `wa_gateway_password`;
ALTER TABLE `pengaturan_aplikasi` ADD COLUMN `google_drive_folder_id` varchar(100) DEFAULT NULL AFTER `google_refresh_token`;
ALTER TABLE `pengaturan_aplikasi` ADD COLUMN `google_drive_enabled` tinyint(1) DEFAULT 0 AFTER `google_drive_folder_id`;
ALTER TABLE `pengaturan_aplikasi` ADD COLUMN `google_drive_email` varchar(255) DEFAULT NULL AFTER `google_drive_enabled`;

-- =====================================================
-- BAGIAN 3: ALTER TABLE siswa
-- Tambah 50+ kolom yang kurang di hosting
-- =====================================================

-- Kolom NIK dan data pribadi
ALTER TABLE `siswa` ADD COLUMN `nik` varchar(20) DEFAULT NULL AFTER `nisn`;
ALTER TABLE `siswa` ADD COLUMN `agama` varchar(20) DEFAULT NULL AFTER `jenis_kelamin`;

-- Kolom Alamat Detail (setelah alamat yang sudah ada)
ALTER TABLE `siswa` ADD COLUMN `rt` varchar(5) DEFAULT NULL AFTER `alamat`;
ALTER TABLE `siswa` ADD COLUMN `rw` varchar(5) DEFAULT NULL AFTER `rt`;
ALTER TABLE `siswa` ADD COLUMN `dusun` varchar(100) DEFAULT NULL AFTER `rw`;
ALTER TABLE `siswa` ADD COLUMN `desa` varchar(100) DEFAULT NULL AFTER `dusun`;
ALTER TABLE `siswa` ADD COLUMN `kelurahan` varchar(100) DEFAULT NULL AFTER `desa`;
ALTER TABLE `siswa` ADD COLUMN `kecamatan` varchar(100) DEFAULT NULL AFTER `kelurahan`;
ALTER TABLE `siswa` ADD COLUMN `kabupaten` varchar(100) DEFAULT NULL AFTER `kecamatan`;
ALTER TABLE `siswa` ADD COLUMN `kota` varchar(100) DEFAULT NULL AFTER `kabupaten`;
ALTER TABLE `siswa` ADD COLUMN `provinsi` varchar(100) DEFAULT NULL AFTER `kota`;
ALTER TABLE `siswa` ADD COLUMN `kode_pos` varchar(10) DEFAULT NULL AFTER `provinsi`;
ALTER TABLE `siswa` ADD COLUMN `status_tempat_tinggal` varchar(50) DEFAULT NULL AFTER `kode_pos`;
ALTER TABLE `siswa` ADD COLUMN `jarak_ke_sekolah` varchar(50) DEFAULT NULL AFTER `status_tempat_tinggal`;
ALTER TABLE `siswa` ADD COLUMN `waktu_tempuh` varchar(50) DEFAULT NULL AFTER `jarak_ke_sekolah`;
ALTER TABLE `siswa` ADD COLUMN `transportasi` varchar(50) DEFAULT NULL AFTER `waktu_tempuh`;

-- Kolom data pribadi tambahan
ALTER TABLE `siswa` ADD COLUMN `hobi` varchar(100) DEFAULT NULL AFTER `foto`;
ALTER TABLE `siswa` ADD COLUMN `cita_cita` varchar(100) DEFAULT NULL AFTER `hobi`;
ALTER TABLE `siswa` ADD COLUMN `jumlah_saudara` int(11) DEFAULT 0 AFTER `cita_cita`;
ALTER TABLE `siswa` ADD COLUMN `anak_ke` int(11) DEFAULT 1 AFTER `jumlah_saudara`;
ALTER TABLE `siswa` ADD COLUMN `kip` varchar(50) DEFAULT NULL AFTER `anak_ke`;
ALTER TABLE `siswa` ADD COLUMN `yang_membiayai` varchar(50) DEFAULT 'Orang Tua' AFTER `kip`;
ALTER TABLE `siswa` ADD COLUMN `kebutuhan_khusus` varchar(100) DEFAULT 'Tidak Ada' AFTER `yang_membiayai`;
ALTER TABLE `siswa` ADD COLUMN `kebutuhan_disabilitas` varchar(100) DEFAULT 'Tidak Ada' AFTER `kebutuhan_khusus`;

-- Kolom Data Ayah Lengkap
ALTER TABLE `siswa` ADD COLUMN `ayah_nik` varchar(20) DEFAULT NULL AFTER `ayah_kandung`;
ALTER TABLE `siswa` ADD COLUMN `ayah_tempat_lahir` varchar(100) DEFAULT NULL AFTER `ayah_nik`;
ALTER TABLE `siswa` ADD COLUMN `ayah_tanggal_lahir` date DEFAULT NULL AFTER `ayah_tempat_lahir`;
ALTER TABLE `siswa` ADD COLUMN `ayah_status` varchar(20) DEFAULT 'Masih Hidup' AFTER `ayah_tanggal_lahir`;
ALTER TABLE `siswa` ADD COLUMN `ayah_pendidikan` varchar(50) DEFAULT NULL AFTER `ayah_status`;
ALTER TABLE `siswa` ADD COLUMN `ayah_pekerjaan` varchar(100) DEFAULT NULL AFTER `ayah_pendidikan`;
ALTER TABLE `siswa` ADD COLUMN `ayah_penghasilan` varchar(50) DEFAULT NULL AFTER `ayah_pekerjaan`;
ALTER TABLE `siswa` ADD COLUMN `ayah_no_hp` varchar(20) DEFAULT NULL AFTER `ayah_penghasilan`;
ALTER TABLE `siswa` ADD COLUMN `ayah_alamat` text DEFAULT NULL AFTER `ayah_no_hp`;
ALTER TABLE `siswa` ADD COLUMN `ayah_status_rumah` varchar(50) DEFAULT NULL AFTER `ayah_alamat`;

-- Kolom Data Ibu Lengkap
ALTER TABLE `siswa` ADD COLUMN `ibu_nik` varchar(20) DEFAULT NULL AFTER `ibu_kandung`;
ALTER TABLE `siswa` ADD COLUMN `ibu_tempat_lahir` varchar(100) DEFAULT NULL AFTER `ibu_nik`;
ALTER TABLE `siswa` ADD COLUMN `ibu_tanggal_lahir` date DEFAULT NULL AFTER `ibu_tempat_lahir`;
ALTER TABLE `siswa` ADD COLUMN `ibu_status` varchar(20) DEFAULT 'Masih Hidup' AFTER `ibu_tanggal_lahir`;
ALTER TABLE `siswa` ADD COLUMN `ibu_pendidikan` varchar(50) DEFAULT NULL AFTER `ibu_status`;
ALTER TABLE `siswa` ADD COLUMN `ibu_pekerjaan` varchar(100) DEFAULT NULL AFTER `ibu_pendidikan`;
ALTER TABLE `siswa` ADD COLUMN `ibu_penghasilan` varchar(50) DEFAULT NULL AFTER `ibu_pekerjaan`;
ALTER TABLE `siswa` ADD COLUMN `ibu_no_hp` varchar(20) DEFAULT NULL AFTER `ibu_penghasilan`;
ALTER TABLE `siswa` ADD COLUMN `ibu_alamat` text DEFAULT NULL AFTER `ibu_no_hp`;
ALTER TABLE `siswa` ADD COLUMN `ibu_status_rumah` varchar(50) DEFAULT NULL AFTER `ibu_alamat`;

-- Kolom Data Wali Lengkap
ALTER TABLE `siswa` ADD COLUMN `wali_nik` varchar(20) DEFAULT NULL AFTER `ibu_status_rumah`;
ALTER TABLE `siswa` ADD COLUMN `wali_nama` varchar(100) DEFAULT NULL AFTER `wali_nik`;
ALTER TABLE `siswa` ADD COLUMN `wali_tempat_lahir` varchar(100) DEFAULT NULL AFTER `wali_nama`;
ALTER TABLE `siswa` ADD COLUMN `wali_tanggal_lahir` date DEFAULT NULL AFTER `wali_tempat_lahir`;
ALTER TABLE `siswa` ADD COLUMN `wali_hubungan` varchar(50) DEFAULT NULL AFTER `wali_tanggal_lahir`;
ALTER TABLE `siswa` ADD COLUMN `wali_pendidikan` varchar(50) DEFAULT NULL AFTER `wali_hubungan`;
ALTER TABLE `siswa` ADD COLUMN `wali_pekerjaan` varchar(100) DEFAULT NULL AFTER `wali_pendidikan`;
ALTER TABLE `siswa` ADD COLUMN `wali_penghasilan` varchar(50) DEFAULT NULL AFTER `wali_pekerjaan`;
ALTER TABLE `siswa` ADD COLUMN `wali_no_hp` varchar(20) DEFAULT NULL AFTER `wali_penghasilan`;
ALTER TABLE `siswa` ADD COLUMN `wali_alamat` text DEFAULT NULL AFTER `wali_no_hp`;
ALTER TABLE `siswa` ADD COLUMN `wali_status_rumah` varchar(50) DEFAULT NULL AFTER `wali_alamat`;

-- =====================================================
-- BAGIAN 4: REMOVE UNUSED COLUMNS
-- Kolom yang ada di hosting tapi tidak dipakai
-- =====================================================

-- Hapus kolom created_at dari siswa jika ada (diganti dengan struktur baru)
-- ALTER TABLE `siswa` DROP COLUMN `created_at`;

-- =====================================================
-- BAGIAN 5: ADD FOREIGN KEY CONSTRAINTS
-- =====================================================

ALTER TABLE `guru_fungsi` 
  ADD CONSTRAINT `fk_guru_fungsi_guru` FOREIGN KEY (`id_guru`) REFERENCES `guru`(`id_guru`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_guru_fungsi_tp` FOREIGN KEY (`id_tp`) REFERENCES `tp`(`id_tp`) ON DELETE CASCADE;

ALTER TABLE `siswa_dokumen`
  ADD CONSTRAINT `fk_siswa_dokumen_siswa` FOREIGN KEY (`id_siswa`) REFERENCES `siswa`(`id_siswa`) ON DELETE CASCADE;

-- =====================================================
-- SELESAI - Verifikasi dengan menjalankan:
-- SHOW COLUMNS FROM siswa;
-- SHOW COLUMNS FROM pengaturan_aplikasi;
-- SHOW TABLES LIKE 'guru_fungsi';
-- SHOW TABLES LIKE 'siswa_dokumen';
-- SHOW TABLES LIKE 'pengaturan_dokumen';
-- SHOW TABLES LIKE 'pengaturan_jadwal';
-- =====================================================
