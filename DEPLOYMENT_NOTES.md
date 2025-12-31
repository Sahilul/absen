# Smart Absensi - Deployment Notes

## Konfigurasi aaPanel + Nginx + Cloudflare

### Persyaratan Server
- **Web Server**: Nginx
- **PHP**: 8.1+
- **Database**: MySQL/MariaDB
- **DNS**: Cloudflare (optional)

---

## Langkah Deploy ke aaPanel

### 1. Upload Files
Upload semua file aplikasi ke folder website, contoh:
```
/www/wwwroot/sabilillah.id/
```

### 2. Setting Website Directory
Di aaPanel → Website → [domain] → Settings → Site directory:

| Setting | Value |
|---------|-------|
| **Site directory** | `/www/wwwroot/sabilillah.id` |
| **Running directory** | `/public` ← **PENTING!** |

### 3. URL Rewrite (WAJIB untuk Nginx)
Di aaPanel → Website → [domain] → URL rewrite

Tambahkan konfigurasi ini:

```nginx
location / {
    try_files $uri $uri/ @rewrite;
}

location @rewrite {
    rewrite ^/(.*)$ /index.php?url=$1 last;
}
```

**Atau versi alternatif:**
```nginx
location / {
    try_files $uri $uri/ /index.php?url=$1;
}
```

### 4. Restart Nginx
Setelah mengubah konfigurasi, **restart nginx**:
- aaPanel → Home → Nginx → **Restart**

---

## Konfigurasi Database

### Import Database
1. Buat database baru di aaPanel → Database
2. **WAJIB: Jalankan file SQL migrasi** di phpMyAdmin:
   - Buka phpMyAdmin
   - Pilih database yang sudah dibuat
   - Klik tab **SQL** → **Import**
   - Upload/paste isi file `database/fix_psb_tables.sql`
   - Klik **Execute/Go**
   
   ⚠️ **PENTING**: File `fix_psb_tables.sql` berisi tabel-tabel PSB yang wajib ada (psb_pengaturan, psb_lembaga, psb_jalur, dll). Tanpa ini, halaman `/psb` akan error!

### Edit Config Database
Edit file `config/database.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'username_database');
define('DB_PASS', 'password_database');
define('DB_NAME', 'nama_database');
```

---

## Konfigurasi Cloudflare (Jika Pakai)

### SSL/TLS
- Mode: **Full** atau **Full (Strict)**

### Caching
Jika ada masalah tampilan tidak update:
1. Cloudflare → Caching → **Purge Everything**
2. Atau tekan **Ctrl+Shift+R** di browser

### Troubleshooting
Jika website tidak berfungsi dengan Cloudflare:
1. Sementara ubah DNS dari **Proxied** (orange) ke **DNS only** (grey)
2. Test website
3. Jika berfungsi, berarti masalah di Cloudflare caching

---

## Troubleshooting Umum

### URL Berubah Tapi Tampilan Tidak
**Masalah:** Routing nginx tidak bekerja
**Solusi:** Pastikan URL rewrite sudah dikonfigurasi dan nginx sudah direstart

### Error 404 pada Gambar/Assets
**Masalah:** Path assets tidak ditemukan
**Solusi:** Pastikan folder `public/img/`, `public/css/`, `public/js/` terupload dengan benar

### Login Tidak Berfungsi
**Masalah:** Form action tidak mengarah ke URL yang benar
**Solusi:** 
1. Cek URL rewrite nginx
2. Pastikan Running directory = `/public`
3. Clear browser cache

### Session Hilang / Sering Logout
**Masalah:** Session tidak persistent
**Solusi:** Pastikan folder `/tmp` atau session save path writable oleh PHP

---

## File Penting

| File | Fungsi |
|------|--------|
| `config/database.php` | Konfigurasi koneksi database |
| `config/config.php` | Konfigurasi aplikasi (BASEURL, secret key, dll) |
| `public/index.php` | Entry point aplikasi |
| `database/migrate.php` | Script migrasi database |
| `database/install.php` | Script instalasi database fresh |

---

## Catatan Keamanan

⚠️ **Setelah deployment, HAPUS file berikut:**
- `database/install.php`
- `database/migrate.php`
- File SQL backup apapun di folder public

---

*Last updated: 16 Desember 2025*
