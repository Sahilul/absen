-- Versi 1.8.9
-- Menambahkan role 'bendahara' ke kolom created_by_role di tabel pembayaran_tagihan
-- Mengubah tipe kolom menjadi VARCHAR(20) jika sebelumnya ENUM terbatas, atau memperluas ENUM

ALTER TABLE pembayaran_tagihan 
MODIFY COLUMN created_by_role ENUM('admin', 'wali_kelas', 'bendahara') DEFAULT 'wali_kelas';
