-- Migration: Tabel Pesan
-- Jalankan di phpMyAdmin hosting

CREATE TABLE IF NOT EXISTS pesan (
    id_pesan int(11) NOT NULL AUTO_INCREMENT,
    pengirim_type enum('admin','guru','siswa') NOT NULL DEFAULT 'admin',
    pengirim_id int(11) DEFAULT NULL,
    judul varchar(255) NOT NULL,
    konten text NOT NULL,
    target_type enum('semua_guru','semua_siswa','kelas','individu_guru','individu_siswa') NOT NULL,
    target_id int(11) DEFAULT NULL,
    lampiran varchar(255) DEFAULT NULL,
    created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_pesan)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS pesan_penerima (
    id int(11) NOT NULL AUTO_INCREMENT,
    id_pesan int(11) NOT NULL,
    penerima_type enum('guru','siswa') NOT NULL,
    penerima_id int(11) NOT NULL,
    dibaca tinyint(1) NOT NULL DEFAULT 0,
    dibaca_at datetime DEFAULT NULL,
    created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Rename kolom konten ke isi setelah tabel dibuat
ALTER TABLE pesan CHANGE COLUMN konten isi text NOT NULL;
