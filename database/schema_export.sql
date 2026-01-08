-- MySQL dump 10.13  Distrib 8.0.30, for Win64 (x86_64)
--
-- Host: localhost    Database: absen
-- ------------------------------------------------------
-- Server version	8.0.30

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `absensi`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `absensi` (
  `id_absensi` int NOT NULL AUTO_INCREMENT,
  `id_jurnal` int NOT NULL,
  `id_siswa` int NOT NULL,
  `status_kehadiran` enum('H','I','S','A','T') COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Hadir, Izin, Sakit, Alfa, Terlambat',
  `keterangan` text COLLATE utf8mb4_general_ci,
  `waktu_input` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_absensi`),
  KEY `id_jurnal` (`id_jurnal`),
  KEY `id_siswa` (`id_siswa`),
  CONSTRAINT `absensi_ibfk_1` FOREIGN KEY (`id_jurnal`) REFERENCES `jurnal` (`id_jurnal`),
  CONSTRAINT `absensi_ibfk_2` FOREIGN KEY (`id_siswa`) REFERENCES `siswa` (`id_siswa`)
) ENGINE=InnoDB AUTO_INCREMENT=14377 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cms_institutions`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cms_institutions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `short_name` varchar(50) DEFAULT NULL,
  `icon` varchar(50) DEFAULT 'school',
  `color` varchar(20) DEFAULT 'blue',
  `count_mode` enum('manual','auto') DEFAULT 'manual',
  `manual_count` int DEFAULT '0',
  `selected_classes` text,
  `target_tp_id` int DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `order_index` int DEFAULT '1',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cms_menus`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cms_menus` (
  `id` int NOT NULL AUTO_INCREMENT,
  `label` varchar(50) NOT NULL,
  `url` varchar(255) DEFAULT '#',
  `parent_id` int DEFAULT '0',
  `type` enum('page','link','custom') DEFAULT 'link',
  `order_index` int DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cms_popups`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cms_popups` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(100) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `content` text,
  `link_url` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `frequency` enum('once','always','daily') DEFAULT 'once',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cms_posts`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cms_posts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `content` longtext,
  `type` enum('page','news','announcement') DEFAULT 'news',
  `image` varchar(255) DEFAULT NULL,
  `author_id` int DEFAULT NULL,
  `view_count` int DEFAULT '0',
  `is_published` tinyint(1) DEFAULT '1',
  `published_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cms_settings`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cms_settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=48 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cms_sliders`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cms_sliders` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(100) DEFAULT NULL,
  `description` text,
  `image` varchar(255) NOT NULL,
  `link_url` varchar(255) DEFAULT NULL,
  `button_text` varchar(50) DEFAULT 'Selengkapnya',
  `order_index` int DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `guru`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `guru` (
  `id_guru` int NOT NULL AUTO_INCREMENT,
  `nik` varchar(30) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nama_guru` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id_guru`),
  UNIQUE KEY `nip` (`nik`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `guru_mapel`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `guru_mapel` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_guru` int NOT NULL,
  `id_mapel` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_guru_mapel` (`id_guru`,`id_mapel`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `jadwal_istirahat`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jadwal_istirahat` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_kelas` int NOT NULL,
  `hari` varchar(20) NOT NULL,
  `setelah_jam` int NOT NULL,
  `id_tp` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_istirahat` (`id_kelas`,`hari`,`setelah_jam`,`id_tp`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `jadwal_pelajaran`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jadwal_pelajaran` (
  `id_jadwal` int NOT NULL AUTO_INCREMENT,
  `id_kelas` int NOT NULL,
  `id_guru` int NOT NULL,
  `id_mapel` int NOT NULL,
  `id_jam` int NOT NULL,
  `hari` enum('Senin','Selasa','Rabu','Kamis','Jumat','Sabtu') NOT NULL,
  `id_ruangan` int DEFAULT NULL,
  `id_tp` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_jadwal`),
  UNIQUE KEY `no_guru_conflict` (`id_guru`,`id_jam`,`hari`,`id_tp`),
  UNIQUE KEY `no_kelas_conflict` (`id_kelas`,`id_jam`,`hari`,`id_tp`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `jam_pelajaran`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jam_pelajaran` (
  `id_jam` int NOT NULL AUTO_INCREMENT,
  `jam_ke` tinyint NOT NULL,
  `waktu_mulai` time NOT NULL,
  `waktu_selesai` time NOT NULL,
  `is_istirahat` tinyint DEFAULT '0',
  `keterangan` varchar(50) DEFAULT NULL,
  `urutan` int DEFAULT '0',
  PRIMARY KEY (`id_jam`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `jurnal`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jurnal` (
  `id_jurnal` int NOT NULL AUTO_INCREMENT,
  `id_penugasan` int NOT NULL,
  `pertemuan_ke` int NOT NULL,
  `tanggal` date NOT NULL,
  `topik_materi` text COLLATE utf8mb4_general_ci,
  `catatan` text COLLATE utf8mb4_general_ci,
  `timestamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_jurnal`),
  KEY `id_penugasan` (`id_penugasan`),
  CONSTRAINT `jurnal_ibfk_1` FOREIGN KEY (`id_penugasan`) REFERENCES `penugasan` (`id_penugasan`)
) ENGINE=InnoDB AUTO_INCREMENT=507 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `keanggotaan_kelas`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `keanggotaan_kelas` (
  `id_keanggotaan` int NOT NULL AUTO_INCREMENT,
  `id_siswa` int NOT NULL,
  `id_kelas` int NOT NULL,
  `id_tp` int NOT NULL,
  PRIMARY KEY (`id_keanggotaan`),
  KEY `id_siswa` (`id_siswa`),
  KEY `id_kelas` (`id_kelas`),
  KEY `id_tp` (`id_tp`),
  CONSTRAINT `keanggotaan_kelas_ibfk_1` FOREIGN KEY (`id_siswa`) REFERENCES `siswa` (`id_siswa`),
  CONSTRAINT `keanggotaan_kelas_ibfk_2` FOREIGN KEY (`id_kelas`) REFERENCES `kelas` (`id_kelas`),
  CONSTRAINT `keanggotaan_kelas_ibfk_3` FOREIGN KEY (`id_tp`) REFERENCES `tp` (`id_tp`)
) ENGINE=InnoDB AUTO_INCREMENT=205 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kebutuhan_jam_mapel`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kebutuhan_jam_mapel` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_kelas` int NOT NULL,
  `id_mapel` int NOT NULL,
  `jumlah_jam` int NOT NULL DEFAULT '2',
  `id_tp` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_kelas_mapel_tp` (`id_kelas`,`id_mapel`,`id_tp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kelas`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kelas` (
  `id_kelas` int NOT NULL AUTO_INCREMENT,
  `nama_kelas` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `jenjang` varchar(10) COLLATE utf8mb4_general_ci NOT NULL,
  `id_tp` int NOT NULL,
  PRIMARY KEY (`id_kelas`),
  KEY `id_tp` (`id_tp`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mapel`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mapel` (
  `id_mapel` int NOT NULL AUTO_INCREMENT,
  `kode_mapel` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `nama_mapel` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id_mapel`),
  UNIQUE KEY `kode_mapel` (`kode_mapel`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `nilai_siswa`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `nilai_siswa` (
  `id_nilai` int NOT NULL AUTO_INCREMENT,
  `id_siswa` int NOT NULL,
  `id_guru` int NOT NULL,
  `id_mapel` int NOT NULL,
  `id_semester` int NOT NULL,
  `jenis_nilai` enum('harian','sts','sas','praktek','proyek') COLLATE utf8mb4_general_ci NOT NULL,
  `keterangan` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Nama tugas/ulangan',
  `nilai` decimal(5,2) NOT NULL,
  `tanggal_input` date NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_nilai`),
  KEY `idx_siswa_semester` (`id_siswa`,`id_semester`),
  KEY `idx_jenis_nilai` (`jenis_nilai`),
  KEY `idx_guru` (`id_guru`),
  KEY `idx_mapel` (`id_mapel`),
  KEY `idx_semester` (`id_semester`),
  CONSTRAINT `fk_nilai_guru` FOREIGN KEY (`id_guru`) REFERENCES `guru` (`id_guru`) ON DELETE CASCADE,
  CONSTRAINT `fk_nilai_mapel` FOREIGN KEY (`id_mapel`) REFERENCES `mapel` (`id_mapel`) ON DELETE CASCADE,
  CONSTRAINT `fk_nilai_semester` FOREIGN KEY (`id_semester`) REFERENCES `semester` (`id_semester`) ON DELETE CASCADE,
  CONSTRAINT `fk_nilai_siswa` FOREIGN KEY (`id_siswa`) REFERENCES `siswa` (`id_siswa`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=208 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pembayaran_kategori`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pembayaran_kategori` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) NOT NULL,
  `deskripsi` varchar(255) DEFAULT NULL,
  `aktif` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pembayaran_tagihan`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pembayaran_tagihan` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nama` varchar(150) NOT NULL,
  `kategori_id` int DEFAULT NULL,
  `is_global` tinyint(1) NOT NULL DEFAULT '0',
  `ref_global_id` int DEFAULT NULL,
  `id_tp` int NOT NULL,
  `id_semester` int DEFAULT NULL,
  `id_kelas` int DEFAULT NULL,
  `tipe` enum('sekali','bulanan') NOT NULL DEFAULT 'sekali',
  `nominal_default` bigint NOT NULL DEFAULT '0',
  `jatuh_tempo` date DEFAULT NULL,
  `created_by_user` int DEFAULT NULL,
  `created_by_role` enum('admin','wali_kelas','guru') DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_tag_kat` (`kategori_id`),
  KEY `idx_tagihan_kelas` (`id_tp`,`id_semester`,`id_kelas`),
  KEY `idx_tagihan_ref` (`ref_global_id`),
  CONSTRAINT `fk_tag_kat` FOREIGN KEY (`kategori_id`) REFERENCES `pembayaran_kategori` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_tag_ref_global` FOREIGN KEY (`ref_global_id`) REFERENCES `pembayaran_tagihan` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pembayaran_tagihan_siswa`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pembayaran_tagihan_siswa` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tagihan_id` int NOT NULL,
  `id_siswa` int NOT NULL,
  `nominal` bigint NOT NULL DEFAULT '0',
  `diskon` bigint NOT NULL DEFAULT '0',
  `total_terbayar` bigint NOT NULL DEFAULT '0',
  `status` enum('belum','sebagian','lunas') NOT NULL DEFAULT 'belum',
  `jatuh_tempo` date DEFAULT NULL,
  `periode_bulan` tinyint NOT NULL DEFAULT '0',
  `periode_tahun` int NOT NULL DEFAULT '0',
  `keterangan` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_tagihan_siswa_periode` (`tagihan_id`,`id_siswa`,`periode_bulan`,`periode_tahun`),
  KEY `idx_tgs_siswa` (`id_siswa`),
  CONSTRAINT `fk_tgs_tagihan` FOREIGN KEY (`tagihan_id`) REFERENCES `pembayaran_tagihan` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pembayaran_transaksi`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pembayaran_transaksi` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `tagihan_id` int NOT NULL,
  `id_siswa` int NOT NULL,
  `tanggal` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `jumlah` bigint NOT NULL,
  `metode` varchar(50) DEFAULT NULL,
  `keterangan` varchar(255) DEFAULT NULL,
  `bukti_path` varchar(255) DEFAULT NULL,
  `user_input_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_trx_tagihan` (`tagihan_id`),
  KEY `idx_trx_siswa` (`id_siswa`,`tagihan_id`),
  CONSTRAINT `fk_trx_tagihan` FOREIGN KEY (`tagihan_id`) REFERENCES `pembayaran_tagihan` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pengaturan_aplikasi`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pengaturan_aplikasi` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nama_aplikasi` varchar(255) NOT NULL DEFAULT 'Smart Absensi',
  `url_web` varchar(255) DEFAULT 'http://localhost/absen',
  `logo` varchar(255) DEFAULT NULL,
  `favicon` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `wa_gateway_url` varchar(255) DEFAULT 'https://api.fonnte.com/send',
  `wa_gateway_token` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pengaturan_jadwal`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pengaturan_jadwal` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nama_pengaturan` varchar(50) NOT NULL,
  `nilai` text,
  `keterangan` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nama_pengaturan` (`nama_pengaturan`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pengaturan_rapor`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pengaturan_rapor` (
  `id_pengaturan` int NOT NULL AUTO_INCREMENT,
  `id_guru` int NOT NULL COMMENT 'ID Guru yang menjadi wali kelas',
  `id_tp` int NOT NULL COMMENT 'ID Tahun Pelajaran',
  `kop_rapor` varchar(255) DEFAULT NULL COMMENT 'Path file gambar kop rapor',
  `nama_madrasah` varchar(255) DEFAULT NULL,
  `alamat_madrasah` text,
  `telp_madrasah` varchar(20) DEFAULT NULL,
  `email_madrasah` varchar(100) DEFAULT NULL,
  `tempat_cetak` varchar(100) DEFAULT NULL COMMENT 'Tempat cetak rapor, misal: Mojokerto',
  `nama_kepala_madrasah` varchar(255) DEFAULT NULL,
  `nip_kepala_madrasah` varchar(50) DEFAULT NULL,
  `ttd_kepala_madrasah` varchar(255) DEFAULT NULL COMMENT 'Path file tanda tangan kepala madrasah',
  `ttd_wali_kelas` varchar(255) DEFAULT NULL COMMENT 'Path file tanda tangan wali kelas',
  `tanggal_cetak` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `mapel_rapor` text COMMENT 'ID mapel yang ditampilkan di rapor (JSON array)',
  `persen_harian_sts` int DEFAULT '60' COMMENT 'Persentase nilai harian untuk rapor STS',
  `persen_sts` int DEFAULT '40' COMMENT 'Persentase nilai STS untuk rapor STS',
  `persen_harian_sas` int DEFAULT '40' COMMENT 'Persentase nilai harian untuk rapor SAS',
  `persen_sts_sas` int DEFAULT '30' COMMENT 'Persentase nilai STS untuk rapor SAS',
  `persen_sas` int DEFAULT '30' COMMENT 'Persentase nilai SAS untuk rapor SAS',
  `logo_madrasah` varchar(255) DEFAULT NULL COMMENT 'Path file logo madrasah',
  PRIMARY KEY (`id_pengaturan`),
  UNIQUE KEY `unique_guru_tp` (`id_guru`,`id_tp`),
  KEY `idx_guru` (`id_guru`),
  KEY `idx_tp` (`id_tp`),
  CONSTRAINT `pengaturan_rapor_ibfk_1` FOREIGN KEY (`id_guru`) REFERENCES `guru` (`id_guru`) ON DELETE CASCADE,
  CONSTRAINT `pengaturan_rapor_ibfk_2` FOREIGN KEY (`id_tp`) REFERENCES `tp` (`id_tp`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pengaturan_rpp`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pengaturan_rpp` (
  `id` int NOT NULL AUTO_INCREMENT,
  `wajib_rpp_disetujui` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1=Wajib RPP disetujui untuk akses fitur',
  `blokir_absensi` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1=Blokir akses absensi',
  `blokir_jurnal` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1=Blokir akses jurnal',
  `blokir_nilai` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1=Blokir akses input nilai',
  `pesan_blokir` text COMMENT 'Pesan yang ditampilkan saat diblokir',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `penugasan`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `penugasan` (
  `id_penugasan` int NOT NULL AUTO_INCREMENT,
  `id_guru` int NOT NULL,
  `id_mapel` int NOT NULL,
  `id_kelas` int NOT NULL,
  `id_semester` int NOT NULL,
  PRIMARY KEY (`id_penugasan`),
  KEY `id_guru` (`id_guru`),
  KEY `id_mapel` (`id_mapel`),
  KEY `id_kelas` (`id_kelas`),
  KEY `id_semester` (`id_semester`),
  CONSTRAINT `penugasan_ibfk_1` FOREIGN KEY (`id_guru`) REFERENCES `guru` (`id_guru`),
  CONSTRAINT `penugasan_ibfk_2` FOREIGN KEY (`id_mapel`) REFERENCES `mapel` (`id_mapel`),
  CONSTRAINT `penugasan_ibfk_3` FOREIGN KEY (`id_kelas`) REFERENCES `kelas` (`id_kelas`),
  CONSTRAINT `penugasan_ibfk_4` FOREIGN KEY (`id_semester`) REFERENCES `semester` (`id_semester`)
) ENGINE=InnoDB AUTO_INCREMENT=115 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `psb_akun`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `psb_akun` (
  `id_akun` int NOT NULL AUTO_INCREMENT,
  `nisn` varchar(20) NOT NULL COMMENT 'NISN sebagai username',
  `nama_lengkap` varchar(100) NOT NULL,
  `no_wa` varchar(20) NOT NULL COMMENT 'Untuk notifikasi & reset password',
  `password` varchar(255) NOT NULL,
  `reset_token` varchar(10) DEFAULT NULL,
  `reset_token_expiry` datetime DEFAULT NULL,
  `status` enum('aktif','nonaktif') DEFAULT 'aktif',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `last_login` datetime DEFAULT NULL,
  PRIMARY KEY (`id_akun`),
  UNIQUE KEY `nisn` (`nisn`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `psb_dokumen`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `psb_dokumen` (
  `id_dokumen` int NOT NULL AUTO_INCREMENT,
  `id_pendaftar` int NOT NULL,
  `jenis_dokumen` varchar(30) NOT NULL,
  `nama_file` varchar(255) NOT NULL,
  `path_file` varchar(255) NOT NULL,
  `ukuran` int DEFAULT '0',
  `status` enum('pending','diterima','ditolak') DEFAULT 'pending',
  `catatan` varchar(255) DEFAULT NULL,
  `uploaded_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_dokumen`),
  KEY `idx_pendaftar` (`id_pendaftar`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `psb_jalur`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `psb_jalur` (
  `id_jalur` int NOT NULL AUTO_INCREMENT,
  `kode_jalur` varchar(20) NOT NULL,
  `nama_jalur` varchar(100) NOT NULL,
  `deskripsi` text,
  `persyaratan` text,
  `urutan` int DEFAULT '0',
  `aktif` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_jalur`),
  UNIQUE KEY `kode_jalur` (`kode_jalur`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `psb_kuota_jalur`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `psb_kuota_jalur` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_periode` int NOT NULL,
  `id_jalur` int NOT NULL,
  `kuota` int DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_periode` (`id_periode`,`id_jalur`),
  KEY `id_jalur` (`id_jalur`),
  CONSTRAINT `psb_kuota_jalur_ibfk_1` FOREIGN KEY (`id_periode`) REFERENCES `psb_periode` (`id_periode`) ON DELETE CASCADE,
  CONSTRAINT `psb_kuota_jalur_ibfk_2` FOREIGN KEY (`id_jalur`) REFERENCES `psb_jalur` (`id_jalur`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `psb_lembaga`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `psb_lembaga` (
  `id_lembaga` int NOT NULL AUTO_INCREMENT,
  `kode_lembaga` varchar(20) NOT NULL,
  `nama_lembaga` varchar(100) NOT NULL,
  `jenjang` enum('TK','SD','SMP','SMA','SMK') NOT NULL,
  `alamat` text,
  `kuota_default` int DEFAULT '0',
  `urutan` int DEFAULT '0',
  `aktif` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_lembaga`),
  UNIQUE KEY `kode_lembaga` (`kode_lembaga`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `psb_pendaftar`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `psb_pendaftar` (
  `id_pendaftar` int NOT NULL AUTO_INCREMENT,
  `id_akun` int DEFAULT NULL,
  `id_periode` int NOT NULL,
  `id_jalur` int NOT NULL,
  `no_pendaftaran` varchar(20) NOT NULL,
  `nisn` varchar(20) DEFAULT NULL,
  `nik` varchar(20) DEFAULT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `jenis_kelamin` enum('L','P') NOT NULL,
  `tempat_lahir` varchar(100) DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `agama` varchar(20) DEFAULT NULL,
  `alamat` text,
  `no_hp` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `nama_ayah` varchar(100) DEFAULT NULL,
  `pekerjaan_ayah` varchar(100) DEFAULT NULL,
  `nama_ibu` varchar(100) DEFAULT NULL,
  `pekerjaan_ibu` varchar(100) DEFAULT NULL,
  `asal_sekolah` varchar(200) DEFAULT NULL,
  `tahun_lulus` int DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `status` enum('draft','pending','verifikasi','revisi','diterima','ditolak','daftar_ulang','selesai') DEFAULT 'draft',
  `catatan_admin` text,
  `id_kelas_diterima` int DEFAULT NULL,
  `id_siswa` int DEFAULT NULL,
  `tanggal_daftar` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `tanggal_verifikasi` datetime DEFAULT NULL,
  `tanggal_keputusan` datetime DEFAULT NULL,
  `verified_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `kip` varchar(50) DEFAULT NULL,
  `jumlah_saudara` int DEFAULT '0',
  `anak_ke` int DEFAULT '1',
  `hobi` varchar(100) DEFAULT NULL,
  `cita_cita` varchar(100) DEFAULT NULL,
  `yang_membiayai` varchar(50) DEFAULT 'Orang Tua',
  `kebutuhan_disabilitas` varchar(100) DEFAULT 'Tidak Ada',
  `kebutuhan_khusus` varchar(100) DEFAULT 'Tidak Ada',
  `rt` varchar(5) DEFAULT NULL,
  `rw` varchar(5) DEFAULT NULL,
  `dusun` varchar(100) DEFAULT NULL,
  `desa` varchar(100) DEFAULT NULL,
  `kelurahan` varchar(100) DEFAULT NULL,
  `kecamatan` varchar(100) DEFAULT NULL,
  `kabupaten` varchar(100) DEFAULT NULL,
  `kota` varchar(100) DEFAULT NULL,
  `provinsi` varchar(100) DEFAULT NULL,
  `kode_pos` varchar(10) DEFAULT NULL,
  `status_tempat_tinggal` varchar(50) DEFAULT NULL,
  `jarak_ke_sekolah` varchar(50) DEFAULT NULL,
  `waktu_tempuh` varchar(50) DEFAULT NULL,
  `transportasi` varchar(50) DEFAULT NULL,
  `ayah_nik` varchar(20) DEFAULT NULL,
  `ayah_nama` varchar(100) DEFAULT NULL,
  `ayah_tempat_lahir` varchar(100) DEFAULT NULL,
  `ayah_tanggal_lahir` date DEFAULT NULL,
  `ayah_status` varchar(20) DEFAULT 'Masih Hidup',
  `ayah_pendidikan` varchar(50) DEFAULT NULL,
  `ayah_pekerjaan` varchar(100) DEFAULT NULL,
  `ayah_penghasilan` varchar(50) DEFAULT NULL,
  `ayah_no_hp` varchar(20) DEFAULT NULL,
  `ayah_alamat` text,
  `ayah_status_rumah` varchar(50) DEFAULT NULL,
  `ibu_nik` varchar(20) DEFAULT NULL,
  `ibu_nama` varchar(100) DEFAULT NULL,
  `ibu_tempat_lahir` varchar(100) DEFAULT NULL,
  `ibu_tanggal_lahir` date DEFAULT NULL,
  `ibu_status` varchar(20) DEFAULT 'Masih Hidup',
  `ibu_pendidikan` varchar(50) DEFAULT NULL,
  `ibu_pekerjaan` varchar(100) DEFAULT NULL,
  `ibu_penghasilan` varchar(50) DEFAULT NULL,
  `ibu_no_hp` varchar(20) DEFAULT NULL,
  `ibu_alamat` text,
  `ibu_status_rumah` varchar(50) DEFAULT NULL,
  `wali_nik` varchar(20) DEFAULT NULL,
  `wali_nama` varchar(100) DEFAULT NULL,
  `wali_tempat_lahir` varchar(100) DEFAULT NULL,
  `wali_tanggal_lahir` date DEFAULT NULL,
  `wali_hubungan` varchar(50) DEFAULT NULL,
  `wali_pendidikan` varchar(50) DEFAULT NULL,
  `step_terakhir` int DEFAULT '1',
  `tanggal_submit` datetime DEFAULT NULL,
  `wali_no_hp` varchar(20) DEFAULT NULL,
  `wali_alamat` text,
  `wali_status_rumah` varchar(50) DEFAULT NULL,
  `wali_penghasilan` varchar(50) DEFAULT NULL,
  `wali_pekerjaan` varchar(100) DEFAULT NULL,
  `nama_sekolah_asal` varchar(255) DEFAULT NULL,
  `npsn_sekolah_asal` varchar(20) DEFAULT NULL,
  `alamat_sekolah_asal` text,
  PRIMARY KEY (`id_pendaftar`),
  UNIQUE KEY `no_pendaftaran` (`no_pendaftaran`),
  KEY `id_periode` (`id_periode`),
  KEY `id_jalur` (`id_jalur`),
  CONSTRAINT `psb_pendaftar_ibfk_1` FOREIGN KEY (`id_periode`) REFERENCES `psb_periode` (`id_periode`),
  CONSTRAINT `psb_pendaftar_ibfk_2` FOREIGN KEY (`id_jalur`) REFERENCES `psb_jalur` (`id_jalur`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `psb_pengaturan`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `psb_pengaturan` (
  `id` int NOT NULL DEFAULT '1',
  `judul_halaman` varchar(200) DEFAULT 'Penerimaan Siswa Baru',
  `deskripsi` text,
  `syarat_pendaftaran` text,
  `alur_pendaftaran` text,
  `kontak_info` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `wa_gateway_url` varchar(255) DEFAULT 'https://api.fonnte.com/send',
  `wa_gateway_token` varchar(255) DEFAULT NULL,
  `brosur_gambar` varchar(255) DEFAULT NULL,
  `tentang_sekolah` text,
  `keunggulan` text,
  `visi_misi` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `psb_periode`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `psb_periode` (
  `id_periode` int NOT NULL AUTO_INCREMENT,
  `id_lembaga` int NOT NULL,
  `nama_periode` varchar(100) NOT NULL,
  `id_tp` int NOT NULL,
  `tanggal_buka` date NOT NULL,
  `tanggal_tutup` date NOT NULL,
  `kuota` int DEFAULT '0',
  `biaya_pendaftaran` decimal(10,2) DEFAULT '0.00',
  `status` enum('draft','aktif','selesai') DEFAULT 'draft',
  `keterangan` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_periode`),
  KEY `id_lembaga` (`id_lembaga`),
  KEY `id_tp` (`id_tp`),
  CONSTRAINT `psb_periode_ibfk_1` FOREIGN KEY (`id_lembaga`) REFERENCES `psb_lembaga` (`id_lembaga`),
  CONSTRAINT `psb_periode_ibfk_2` FOREIGN KEY (`id_tp`) REFERENCES `tp` (`id_tp`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `qr_validation_logs`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `qr_validation_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `token` char(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `scanned_at` datetime NOT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_valid` tinyint(1) NOT NULL,
  `notes` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `k_token` (`token`),
  KEY `k_scanned` (`scanned_at`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `qr_validation_tokens`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `qr_validation_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `doc_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `doc_id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `identifier` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` char(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `issued_at` datetime NOT NULL,
  `expires_at` datetime DEFAULT NULL,
  `meta_json` json DEFAULT NULL,
  `revoked` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `u_token` (`token`),
  KEY `k_doc` (`doc_type`,`doc_id`),
  KEY `k_expires` (`expires_at`)
) ENGINE=InnoDB AUTO_INCREMENT=174 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rapor_sts`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rapor_sts` (
  `id_rapor` int NOT NULL AUTO_INCREMENT,
  `id_siswa` int NOT NULL,
  `id_semester` int NOT NULL,
  `id_kelas` int NOT NULL,
  `tanggal_cetak` date DEFAULT NULL,
  `status` enum('draft','final','dicetak') COLLATE utf8mb4_general_ci DEFAULT 'draft',
  `catatan_wali_kelas` text COLLATE utf8mb4_general_ci,
  `sakit` int DEFAULT '0',
  `izin` int DEFAULT '0',
  `alpa` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_rapor`),
  UNIQUE KEY `unique_siswa_semester` (`id_siswa`,`id_semester`),
  KEY `idx_semester` (`id_semester`),
  KEY `idx_kelas` (`id_kelas`),
  CONSTRAINT `fk_rapor_kelas` FOREIGN KEY (`id_kelas`) REFERENCES `kelas` (`id_kelas`) ON DELETE CASCADE,
  CONSTRAINT `fk_rapor_semester` FOREIGN KEY (`id_semester`) REFERENCES `semester` (`id_semester`) ON DELETE CASCADE,
  CONSTRAINT `fk_rapor_siswa` FOREIGN KEY (`id_siswa`) REFERENCES `siswa` (`id_siswa`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rpp`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rpp` (
  `id_rpp` int NOT NULL AUTO_INCREMENT,
  `id_guru` int NOT NULL,
  `id_mapel` int NOT NULL,
  `id_kelas` int NOT NULL,
  `id_tp` int NOT NULL,
  `id_semester` int NOT NULL,
  `id_penugasan` int DEFAULT NULL,
  `nama_madrasah` varchar(255) DEFAULT NULL,
  `alokasi_waktu` varchar(100) DEFAULT NULL,
  `tanggal_rpp` date DEFAULT NULL,
  `peserta_didik` text,
  `materi_pelajaran` text,
  `dimensi_profil_lulusan` text,
  `materi_integrasi_kbc` text,
  `capaian_pembelajaran` text,
  `tujuan_pembelajaran` text,
  `praktik_pedagogis` text,
  `kemitraan_pembelajaran` text,
  `lingkungan_pembelajaran` text,
  `pemanfaatan_digital` text,
  `kegiatan_awal` text,
  `kegiatan_inti_memahami` text,
  `kegiatan_inti_mengaplikasi` text,
  `kegiatan_inti_merefleksi` text,
  `kegiatan_penutup` text,
  `asesmen_awal` text,
  `asesmen_proses` text,
  `asesmen_akhir` text,
  `rpp_field_values` json DEFAULT NULL COMMENT 'Dynamic field values from template',
  `file_rpp` varchar(255) DEFAULT NULL,
  `status` enum('draft','submitted','approved','revision') DEFAULT 'draft',
  `catatan_review` text,
  `reviewed_by` int DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_rpp`),
  UNIQUE KEY `unique_rpp` (`id_guru`,`id_mapel`,`id_kelas`,`id_tp`,`id_semester`),
  KEY `id_mapel` (`id_mapel`),
  KEY `id_kelas` (`id_kelas`),
  CONSTRAINT `rpp_ibfk_1` FOREIGN KEY (`id_guru`) REFERENCES `guru` (`id_guru`),
  CONSTRAINT `rpp_ibfk_2` FOREIGN KEY (`id_mapel`) REFERENCES `mapel` (`id_mapel`),
  CONSTRAINT `rpp_ibfk_3` FOREIGN KEY (`id_kelas`) REFERENCES `kelas` (`id_kelas`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rpp_data`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rpp_data` (
  `id_rpp_data` int NOT NULL AUTO_INCREMENT,
  `id_rpp` int NOT NULL,
  `id_field` int NOT NULL,
  `nilai` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_rpp_data`),
  UNIQUE KEY `unique_rpp_field` (`id_rpp`,`id_field`),
  KEY `id_field` (`id_field`),
  CONSTRAINT `rpp_data_ibfk_1` FOREIGN KEY (`id_rpp`) REFERENCES `rpp` (`id_rpp`) ON DELETE CASCADE,
  CONSTRAINT `rpp_data_ibfk_2` FOREIGN KEY (`id_field`) REFERENCES `rpp_template_field` (`id_field`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rpp_template_field`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rpp_template_field` (
  `id_field` int NOT NULL AUTO_INCREMENT,
  `id_section` int NOT NULL,
  `nama_field` varchar(255) NOT NULL,
  `kode_field` varchar(100) NOT NULL,
  `tipe_input` enum('text','textarea','number','date','file') DEFAULT 'textarea',
  `placeholder` varchar(255) DEFAULT NULL,
  `urutan` int NOT NULL DEFAULT '0',
  `is_required` tinyint(1) DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_field`),
  KEY `id_section` (`id_section`),
  CONSTRAINT `rpp_template_field_ibfk_1` FOREIGN KEY (`id_section`) REFERENCES `rpp_template_section` (`id_section`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rpp_template_section`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rpp_template_section` (
  `id_section` int NOT NULL AUTO_INCREMENT,
  `kode_section` varchar(10) NOT NULL,
  `nama_section` varchar(255) NOT NULL,
  `urutan` int NOT NULL DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_section`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ruangan`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ruangan` (
  `id_ruangan` int NOT NULL AUTO_INCREMENT,
  `nama_ruangan` varchar(50) NOT NULL,
  `kapasitas` int DEFAULT '0',
  `keterangan` varchar(255) DEFAULT NULL,
  `is_active` tinyint DEFAULT '1',
  PRIMARY KEY (`id_ruangan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `semester`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `semester` (
  `id_semester` int NOT NULL AUTO_INCREMENT,
  `id_tp` int NOT NULL,
  `semester` enum('Ganjil','Genap') COLLATE utf8mb4_general_ci NOT NULL,
  `status` enum('open','closed') COLLATE utf8mb4_general_ci DEFAULT 'open',
  `is_active` enum('ya','tidak') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'tidak',
  PRIMARY KEY (`id_semester`),
  KEY `id_tp` (`id_tp`),
  CONSTRAINT `semester_ibfk_1` FOREIGN KEY (`id_tp`) REFERENCES `tp` (`id_tp`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `siswa`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `siswa` (
  `id_siswa` int NOT NULL AUTO_INCREMENT,
  `nisn` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nik` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nama_siswa` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `jenis_kelamin` enum('L','P') COLLATE utf8mb4_general_ci NOT NULL,
  `agama` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tgl_lahir` date DEFAULT NULL,
  `tempat_lahir` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `alamat` text COLLATE utf8mb4_general_ci,
  `rt` varchar(5) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `rw` varchar(5) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `dusun` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `desa` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `kelurahan` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `kecamatan` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `kabupaten` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `kota` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `provinsi` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `kode_pos` varchar(10) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status_tempat_tinggal` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `jarak_ke_sekolah` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `waktu_tempuh` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `transportasi` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `no_wa` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `foto` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `hobi` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cita_cita` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `jumlah_saudara` int DEFAULT '0',
  `anak_ke` int DEFAULT '1',
  `kip` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `yang_membiayai` varchar(50) COLLATE utf8mb4_general_ci DEFAULT 'Orang Tua',
  `kebutuhan_khusus` varchar(100) COLLATE utf8mb4_general_ci DEFAULT 'Tidak Ada',
  `kebutuhan_disabilitas` varchar(100) COLLATE utf8mb4_general_ci DEFAULT 'Tidak Ada',
  `ayah_kandung` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ayah_nik` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ayah_tempat_lahir` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ayah_tanggal_lahir` date DEFAULT NULL,
  `ayah_status` varchar(20) COLLATE utf8mb4_general_ci DEFAULT 'Masih Hidup',
  `ayah_pendidikan` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ayah_pekerjaan` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ayah_penghasilan` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ayah_no_hp` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ayah_alamat` text COLLATE utf8mb4_general_ci,
  `ayah_status_rumah` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ibu_kandung` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ibu_nik` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ibu_tempat_lahir` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ibu_tanggal_lahir` date DEFAULT NULL,
  `ibu_status` varchar(20) COLLATE utf8mb4_general_ci DEFAULT 'Masih Hidup',
  `ibu_pendidikan` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ibu_pekerjaan` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ibu_penghasilan` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ibu_no_hp` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ibu_alamat` text COLLATE utf8mb4_general_ci,
  `ibu_status_rumah` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `wali_nik` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `wali_nama` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `wali_tempat_lahir` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `wali_tanggal_lahir` date DEFAULT NULL,
  `wali_hubungan` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `wali_pendidikan` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `wali_pekerjaan` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `wali_penghasilan` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `wali_no_hp` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `wali_alamat` text COLLATE utf8mb4_general_ci,
  `wali_status_rumah` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status_siswa` enum('aktif','lulus','pindah') COLLATE utf8mb4_general_ci DEFAULT 'aktif',
  PRIMARY KEY (`id_siswa`),
  UNIQUE KEY `nisn` (`nisn`)
) ENGINE=InnoDB AUTO_INCREMENT=401 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `siswa_dokumen`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `siswa_dokumen` (
  `id_dokumen` int NOT NULL AUTO_INCREMENT,
  `id_siswa` int NOT NULL,
  `jenis_dokumen` varchar(30) NOT NULL,
  `nama_file` varchar(255) NOT NULL,
  `path_file` varchar(255) NOT NULL,
  `ukuran` int DEFAULT '0',
  `status` enum('pending','diterima','ditolak') DEFAULT 'pending',
  `catatan` varchar(255) DEFAULT NULL,
  `uploaded_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_dokumen`),
  KEY `id_siswa` (`id_siswa`),
  CONSTRAINT `siswa_dokumen_ibfk_1` FOREIGN KEY (`id_siswa`) REFERENCES `siswa` (`id_siswa`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sksa_nomor`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sksa_nomor` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_siswa` int NOT NULL,
  `id_tp` int NOT NULL,
  `nomor_urut` int NOT NULL,
  `nomor_surat` varchar(100) NOT NULL,
  `tanggal_cetak` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` int NOT NULL COMMENT 'ID user yang mencetak (wali kelas)',
  `nama_walas` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_siswa` (`id_siswa`),
  KEY `id_tp` (`id_tp`),
  KEY `created_by` (`created_by`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `surat_tugas`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `surat_tugas` (
  `id_surat` int NOT NULL AUTO_INCREMENT,
  `id_lembaga` int NOT NULL,
  `nomor_surat` varchar(100) NOT NULL,
  `tanggal_surat` date NOT NULL,
  `perihal` varchar(255) NOT NULL,
  `tanggal_mulai` date DEFAULT NULL,
  `tanggal_selesai` date DEFAULT NULL,
  `tempat_tugas` varchar(255) DEFAULT NULL,
  `status` enum('draft','terbit','selesai') DEFAULT 'draft',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_surat`),
  KEY `id_lembaga` (`id_lembaga`),
  CONSTRAINT `surat_tugas_ibfk_1` FOREIGN KEY (`id_lembaga`) REFERENCES `surat_tugas_lembaga` (`id_lembaga`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `surat_tugas_backup_20251214220504`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `surat_tugas_backup_20251214220504` (
  `id_surat` int NOT NULL AUTO_INCREMENT,
  `id_lembaga` int NOT NULL,
  `nomor_surat` varchar(100) NOT NULL,
  `tanggal_surat` date NOT NULL,
  `kota_surat` varchar(100) NOT NULL,
  `menimbang` text,
  `dasar` text,
  `untuk` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` int DEFAULT NULL,
  PRIMARY KEY (`id_surat`),
  KEY `id_lembaga` (`id_lembaga`),
  CONSTRAINT `fk_st_lembaga` FOREIGN KEY (`id_lembaga`) REFERENCES `surat_tugas_lembaga_backup_20251214220504` (`id_lembaga`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `surat_tugas_lembaga`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `surat_tugas_lembaga` (
  `id_lembaga` int NOT NULL AUTO_INCREMENT,
  `nama_lembaga` varchar(255) NOT NULL,
  `kop_surat` varchar(255) DEFAULT NULL,
  `alamat` text,
  `kota` varchar(100) DEFAULT NULL,
  `nama_kepala_lembaga` varchar(255) DEFAULT NULL,
  `nip_kepala` varchar(100) DEFAULT NULL,
  `jabatan_kepala` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `telepon` varchar(50) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_lembaga`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `surat_tugas_lembaga_backup_20251214220504`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `surat_tugas_lembaga_backup_20251214220504` (
  `id_lembaga` int NOT NULL AUTO_INCREMENT,
  `nama_lembaga` varchar(255) NOT NULL,
  `kop_surat` varchar(255) DEFAULT NULL,
  `alamat` text,
  `kota` varchar(100) DEFAULT NULL,
  `nama_kepala` varchar(150) NOT NULL,
  `nip_kepala` varchar(50) DEFAULT NULL,
  `jabatan_kepala` varchar(100) DEFAULT 'Kepala Madrasah',
  `email` varchar(100) DEFAULT NULL,
  `telepon` varchar(50) DEFAULT NULL,
  `website` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_lembaga`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `surat_tugas_petugas`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `surat_tugas_petugas` (
  `id_petugas` int NOT NULL AUTO_INCREMENT,
  `id_surat` int NOT NULL,
  `nama_petugas` varchar(255) NOT NULL,
  `jenis_identitas` enum('NIP','NIK','NISN','Lainnya') DEFAULT 'NIK',
  `identitas_petugas` varchar(100) DEFAULT NULL COMMENT 'NIP/NIK/NUPTK',
  `jabatan_petugas` varchar(100) DEFAULT NULL,
  `pangkat_golongan` varchar(100) DEFAULT NULL,
  `keterangan` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_petugas`),
  KEY `id_surat` (`id_surat`),
  CONSTRAINT `surat_tugas_petugas_ibfk_1` FOREIGN KEY (`id_surat`) REFERENCES `surat_tugas` (`id_surat`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `surat_tugas_petugas_backup_20251214220504`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `surat_tugas_petugas_backup_20251214220504` (
  `id_petugas` int NOT NULL AUTO_INCREMENT,
  `id_surat` int NOT NULL,
  `nama` varchar(150) NOT NULL,
  `identitas_label` varchar(50) DEFAULT 'NIP',
  `identitas_value` varchar(100) DEFAULT NULL,
  `jabatan` varchar(100) NOT NULL,
  PRIMARY KEY (`id_petugas`),
  KEY `id_surat` (`id_surat`),
  CONSTRAINT `fk_st_surat` FOREIGN KEY (`id_surat`) REFERENCES `surat_tugas_backup_20251214220504` (`id_surat`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tp`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tp` (
  `id_tp` int NOT NULL AUTO_INCREMENT,
  `nama_tp` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `tgl_mulai` date NOT NULL,
  `tgl_selesai` date NOT NULL,
  PRIMARY KEY (`id_tp`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id_user` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `password_plain` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nama_lengkap` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `role` enum('admin','guru','siswa','kepala_madrasah','wali_kelas') COLLATE utf8mb4_general_ci NOT NULL,
  `id_ref` int DEFAULT NULL,
  `status` enum('aktif','nonaktif') COLLATE utf8mb4_general_ci DEFAULT 'aktif',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=421 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `wali_kelas`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wali_kelas` (
  `id_walikelas` int NOT NULL AUTO_INCREMENT,
  `id_guru` int NOT NULL,
  `id_kelas` int NOT NULL,
  `id_tp` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_walikelas`),
  UNIQUE KEY `unique_walikelas` (`id_guru`,`id_tp`),
  UNIQUE KEY `unique_kelas_walikelas` (`id_kelas`,`id_tp`),
  KEY `idx_guru` (`id_guru`),
  KEY `idx_kelas` (`id_kelas`),
  KEY `idx_tp` (`id_tp`),
  CONSTRAINT `fk_walikelas_guru` FOREIGN KEY (`id_guru`) REFERENCES `guru` (`id_guru`) ON DELETE CASCADE,
  CONSTRAINT `fk_walikelas_kelas` FOREIGN KEY (`id_kelas`) REFERENCES `kelas` (`id_kelas`) ON DELETE CASCADE,
  CONSTRAINT `fk_walikelas_tp` FOREIGN KEY (`id_tp`) REFERENCES `tp` (`id_tp`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-12-16 19:22:26
