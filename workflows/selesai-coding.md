---
description: Pengingat wajib setelah selesai coding - update versi dan migrasi database
---

# 🔔 WORKFLOW SELESAI CODING

## LANGKAH WAJIB SETELAH CODING:

### 1. UPDATE VERSION.JSON
// turbo
Lokasi: `c:\laragon\www\absen\version.json`
- Bug fix kecil: increment patch (1.0.0 → 1.0.1)
- Fitur baru: increment minor (1.0.x → 1.1.0)
- Update juga build number dan tanggal

### 2. BUAT FILE MIGRASI DATABASE (jika ada perubahan struktur DB)
Lokasi: `c:\laragon\www\absen\migrations\{versi}.sql`
- Contoh nama: `1.0.1.sql`
- Gunakan IF NOT EXISTS / IF EXISTS

### 3. GIT ADD
// turbo
```bash
git add .
```

### 4. GIT COMMIT
// turbo
```bash
git commit -m "v{VERSI}: {DESKRIPSI SINGKAT}"
```
Contoh: `git commit -m "v1.0.1: Fix upload limit dan tambah fitur auto-update"`

### 5. GIT PUSH
// turbo
```bash
git push origin main
```

### 6. LIST FILE UNTUK UPLOAD MANUAL (jika tidak pakai auto-update)
Beri tahu user file apa saja yang berubah dan perlu diupload ke hosting.

---

## FORMAT RESPONSE SETELAH SELESAI:

```
## ✅ Coding Selesai & Sudah di-Push ke GitHub!

### Versi:
- Lama: 1.0.0 → Baru: 1.0.1

### Git:
- ✓ Committed: "v1.0.1: deskripsi"
- ✓ Pushed to origin/main

### File yang berubah:
- `path/file1.php`
- `path/file2.php`

### Database:
- [x] Tidak ada perubahan
ATAU
- [x] Migrasi dibuat: `migrations/1.0.1.sql`
```
