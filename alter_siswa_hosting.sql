-- =====================================================
-- ALTER TABLE untuk Tabel `siswa` di Hosting
-- Jalankan script ini di phpMyAdmin pada database hosting
-- Dibuat: 2025-12-31
-- =====================================================

-- Tambahkan kolom-kolom yang kurang di tabel siswa

-- Kolom NIK dan data pribadi
ALTER TABLE `siswa` 
ADD COLUMN `nik` varchar(20) DEFAULT NULL AFTER `nisn`,
ADD COLUMN `agama` varchar(20) DEFAULT NULL AFTER `jenis_kelamin`,
ADD COLUMN `hobi` varchar(100) DEFAULT NULL AFTER `foto`,
ADD COLUMN `cita_cita` varchar(100) DEFAULT NULL AFTER `hobi`,
ADD COLUMN `jumlah_saudara` int DEFAULT 0 AFTER `cita_cita`,
ADD COLUMN `anak_ke` int DEFAULT 1 AFTER `jumlah_saudara`,
ADD COLUMN `kip` varchar(50) DEFAULT NULL AFTER `anak_ke`,
ADD COLUMN `yang_membiayai` varchar(50) DEFAULT 'Orang Tua' AFTER `kip`,
ADD COLUMN `kebutuhan_khusus` varchar(100) DEFAULT 'Tidak Ada' AFTER `yang_membiayai`,
ADD COLUMN `kebutuhan_disabilitas` varchar(100) DEFAULT 'Tidak Ada' AFTER `kebutuhan_khusus`;

-- Kolom Alamat Detail
ALTER TABLE `siswa`
ADD COLUMN `rt` varchar(5) DEFAULT NULL AFTER `alamat`,
ADD COLUMN `rw` varchar(5) DEFAULT NULL AFTER `rt`,
ADD COLUMN `dusun` varchar(100) DEFAULT NULL AFTER `rw`,
ADD COLUMN `desa` varchar(100) DEFAULT NULL AFTER `dusun`,
ADD COLUMN `kelurahan` varchar(100) DEFAULT NULL AFTER `desa`,
ADD COLUMN `kecamatan` varchar(100) DEFAULT NULL AFTER `kelurahan`,
ADD COLUMN `kabupaten` varchar(100) DEFAULT NULL AFTER `kecamatan`,
ADD COLUMN `kota` varchar(100) DEFAULT NULL AFTER `kabupaten`,
ADD COLUMN `provinsi` varchar(100) DEFAULT NULL AFTER `kota`,
ADD COLUMN `kode_pos` varchar(10) DEFAULT NULL AFTER `provinsi`,
ADD COLUMN `status_tempat_tinggal` varchar(50) DEFAULT NULL AFTER `kode_pos`,
ADD COLUMN `jarak_ke_sekolah` varchar(50) DEFAULT NULL AFTER `status_tempat_tinggal`,
ADD COLUMN `waktu_tempuh` varchar(50) DEFAULT NULL AFTER `jarak_ke_sekolah`,
ADD COLUMN `transportasi` varchar(50) DEFAULT NULL AFTER `waktu_tempuh`;

-- Kolom Data Ayah Lengkap
ALTER TABLE `siswa`
ADD COLUMN `ayah_nik` varchar(20) DEFAULT NULL AFTER `ayah_kandung`,
ADD COLUMN `ayah_tempat_lahir` varchar(100) DEFAULT NULL AFTER `ayah_nik`,
ADD COLUMN `ayah_tanggal_lahir` date DEFAULT NULL AFTER `ayah_tempat_lahir`,
ADD COLUMN `ayah_status` varchar(20) DEFAULT 'Masih Hidup' AFTER `ayah_tanggal_lahir`,
ADD COLUMN `ayah_pendidikan` varchar(50) DEFAULT NULL AFTER `ayah_status`,
ADD COLUMN `ayah_pekerjaan` varchar(100) DEFAULT NULL AFTER `ayah_pendidikan`,
ADD COLUMN `ayah_penghasilan` varchar(50) DEFAULT NULL AFTER `ayah_pekerjaan`,
ADD COLUMN `ayah_no_hp` varchar(20) DEFAULT NULL AFTER `ayah_penghasilan`,
ADD COLUMN `ayah_alamat` text DEFAULT NULL AFTER `ayah_no_hp`,
ADD COLUMN `ayah_status_rumah` varchar(50) DEFAULT NULL AFTER `ayah_alamat`;

-- Kolom Data Ibu Lengkap
ALTER TABLE `siswa`
ADD COLUMN `ibu_nik` varchar(20) DEFAULT NULL AFTER `ibu_kandung`,
ADD COLUMN `ibu_tempat_lahir` varchar(100) DEFAULT NULL AFTER `ibu_nik`,
ADD COLUMN `ibu_tanggal_lahir` date DEFAULT NULL AFTER `ibu_tempat_lahir`,
ADD COLUMN `ibu_status` varchar(20) DEFAULT 'Masih Hidup' AFTER `ibu_tanggal_lahir`,
ADD COLUMN `ibu_pendidikan` varchar(50) DEFAULT NULL AFTER `ibu_status`,
ADD COLUMN `ibu_pekerjaan` varchar(100) DEFAULT NULL AFTER `ibu_pendidikan`,
ADD COLUMN `ibu_penghasilan` varchar(50) DEFAULT NULL AFTER `ibu_pekerjaan`,
ADD COLUMN `ibu_no_hp` varchar(20) DEFAULT NULL AFTER `ibu_penghasilan`,
ADD COLUMN `ibu_alamat` text DEFAULT NULL AFTER `ibu_no_hp`,
ADD COLUMN `ibu_status_rumah` varchar(50) DEFAULT NULL AFTER `ibu_alamat`;

-- Kolom Data Wali Lengkap
ALTER TABLE `siswa`
ADD COLUMN `wali_nik` varchar(20) DEFAULT NULL AFTER `ibu_status_rumah`,
ADD COLUMN `wali_nama` varchar(100) DEFAULT NULL AFTER `wali_nik`,
ADD COLUMN `wali_tempat_lahir` varchar(100) DEFAULT NULL AFTER `wali_nama`,
ADD COLUMN `wali_tanggal_lahir` date DEFAULT NULL AFTER `wali_tempat_lahir`,
ADD COLUMN `wali_hubungan` varchar(50) DEFAULT NULL AFTER `wali_tanggal_lahir`,
ADD COLUMN `wali_pendidikan` varchar(50) DEFAULT NULL AFTER `wali_hubungan`,
ADD COLUMN `wali_pekerjaan` varchar(100) DEFAULT NULL AFTER `wali_pendidikan`,
ADD COLUMN `wali_penghasilan` varchar(50) DEFAULT NULL AFTER `wali_pekerjaan`,
ADD COLUMN `wali_no_hp` varchar(20) DEFAULT NULL AFTER `wali_penghasilan`,
ADD COLUMN `wali_alamat` text DEFAULT NULL AFTER `wali_no_hp`,
ADD COLUMN `wali_status_rumah` varchar(50) DEFAULT NULL AFTER `wali_alamat`;

-- =====================================================
-- SELESAI - Script ALTER TABLE untuk tabel siswa
-- =====================================================
