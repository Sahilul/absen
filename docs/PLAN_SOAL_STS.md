# 📋 RENCANA FITUR: Buat Soal STS (Sumatif Tengah Semester)

> **Versi Target**: 1.28.0  
> **Tanggal Rencana**: 2026-10-01  
> **Status**: 🟡 PLANNING

---

## 🎯 Tujuan

Admin bisa membuat, mengatur model, dan mencetak soal ujian STS untuk setiap kelas dan mata pelajaran.  
Setiap mapel bisa punya pengaturan jumlah soal dan model soal yang **berbeda-beda**.  
Admin tinggal **print** langsung dari aplikasi.

---

## 🗄️ 1. Database — 2 Tabel Baru (Kop Pakai dari Rapor)

### ⚠️ Kop Soal = Kop Rapor (Tidak Perlu Tabel Baru)

Kop soal STS **menggunakan data yang sama** dengan kop rapor yang sudah ada di tabel `pengaturan_rapor`:

| Field di `pengaturan_rapor` | Dipakai untuk Soal STS |
|-----------------------------|------------------------|
| `kop_rapor` | Gambar kop header (di-upload oleh wali kelas/admin) |
| `nama_madrasah` | Nama sekolah di header soal |
| `nama_kepala_madrasah` | (opsional, untuk tanda tangan) |
| `logo_madrasah` | Logo madrasah (jika ada) |

**Cara ambil data kop:**
- Dari `pengaturan_rapor` JOIN `wali_kelas` berdasarkan `id_kelas` + `id_tp`
- Menggunakan `PengaturanRapor_model::getPengaturanByKelas($id_kelas, $id_tp)`
- Render kop sebagai gambar base64 (pattern yang sama dengan cetak nilai, jurnal, dll)

```php
// Pattern yang sudah dipakai di seluruh aplikasi:
$kopRapor = $pengaturanRapor['kop_rapor'] ?? '';
if (!empty($kopRapor)) {
    $kopPath = 'public/img/kop/' . $kopRapor;
    $imageData = base64_encode(file_get_contents($kopPath));
    $kopHTML = '<img src="data:image/...;base64,' . $imageData . '" ...>';
}
```

> 💡 **Keuntungan**: Admin/wali kelas cukup upload kop **sekali** di Pengaturan Rapor, otomatis terpakai di soal STS juga. Tidak perlu input ulang.

---

### 1.1 `soal_sts_pengaturan` — Pengaturan Model Soal per Mapel per Kelas

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id` | INT AUTO_INCREMENT PK | |
| `id_mapel` | INT FK → mapel | Mata pelajaran |
| `id_kelas` | INT FK → kelas | Kelas target |
| `id_semester` | INT FK → semester | Semester aktif |
| `jumlah_pilihan_ganda` | INT DEFAULT 0 | Jumlah soal PG |
| `jumlah_essay` | INT DEFAULT 0 | Jumlah soal essay |
| `jumlah_isian_singkat` | INT DEFAULT 0 | Jumlah soal isian singkat |
| `opsi_pg` | INT DEFAULT 4 | Jumlah opsi PG (4 = A–D, 5 = A–E) |
| `waktu_pengerjaan` | INT DEFAULT 60 | Waktu dalam menit |
| `petunjuk_umum` | TEXT NULL | Petunjuk umum ujian |
| `created_at` | DATETIME | |
| `updated_at` | DATETIME | |

**UNIQUE KEY**: (`id_mapel`, `id_kelas`, `id_semester`) — satu pengaturan per mapel per kelas per semester.

### 1.2 `soal_sts` — Bank Soal

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id_soal` | INT AUTO_INCREMENT PK | |
| `id_pengaturan` | INT FK → soal_sts_pengaturan | Link ke pengaturan |
| `id_mapel` | INT FK → mapel | |
| `id_kelas` | INT FK → kelas | |
| `id_semester` | INT FK → semester | |
| `tipe_soal` | ENUM('pg','essay','isian') | Pilihan Ganda / Essay / Isian Singkat |
| `nomor_soal` | INT | Urutan nomor dalam tipe-nya |
| `pertanyaan` | TEXT | Isi soal |
| `gambar_soal` | VARCHAR(255) NULL | Path gambar soal (opsional, di `public/uploads/soal_sts/`) |
| `opsi_a` | TEXT NULL | Opsi A (untuk PG) |
| `opsi_b` | TEXT NULL | Opsi B |
| `opsi_c` | TEXT NULL | Opsi C |
| `opsi_d` | TEXT NULL | Opsi D |
| `opsi_e` | TEXT NULL | Opsi E (jika 5 opsi) |
| `kunci_jawaban` | VARCHAR(10) NULL | Kunci jawaban PG (A/B/C/D/E) |
| `skor` | INT DEFAULT 1 | Bobot skor soal |
| `created_by` | INT | ID user admin yang membuat |
| `created_at` | DATETIME | |
| `updated_at` | DATETIME | |

---

## 🏗️ 2. File yang Perlu Dibuat

### Controller (1 file)

| File | Keterangan |
|------|------------|
| `app/controllers/SoalStsController.php` | Controller utama, admin-only |

**Method yang dibutuhkan:**

| Method | HTTP | Fungsi |
|--------|------|--------|
| `index()` | GET | Dashboard — pilih kelas, lihat status per mapel |
| `pengaturan($id_kelas)` | GET | Form pengaturan model soal per mapel |
| `simpanPengaturan()` | POST | Simpan pengaturan model soal |
| `inputSoal($id_pengaturan)` | GET | Form input soal |
| `simpanSoal()` | POST | Simpan soal (single) |
| `simpanSemuaSoal()` | POST | Simpan semua soal sekaligus |
| `editSoal($id_soal)` | GET | Form edit soal |
| `updateSoal()` | POST | Update soal |
| `hapusSoal($id_soal)` | POST/GET | Hapus soal |
| `daftarSoal($id_kelas, $id_mapel)` | GET | Lihat semua soal per mapel per kelas |
| `previewCetak($id_kelas, $id_mapel)` | GET | Preview soal sebelum cetak (HTML) |
| `cetakPDF($id_kelas, $id_mapel)` | GET | Generate PDF via Dompdf |
| `cetakKunci($id_kelas, $id_mapel)` | GET | Cetak kunci jawaban terpisah |

> 💡 **Kop soal** diambil langsung dari `pengaturan_rapor` via `PengaturanRapor_model::getPengaturanByKelas()` — tidak perlu method kop terpisah.

### Model (1 file)

| File | Keterangan |
|------|------------|
| `app/models/SoalSts_model.php` | Semua operasi DB untuk 3 tabel |

**Method model:**

```
// === PENGATURAN ===
getPengaturanByKelas($id_kelas, $id_semester)
getPengaturanByMapelKelas($id_mapel, $id_kelas, $id_semester)
getPengaturanById($id)
simpanPengaturan($data)
updatePengaturan($data)
hapusPengaturan($id)

// === SOAL ===
getSoalByPengaturan($id_pengaturan)
getSoalByMapelKelas($id_mapel, $id_kelas, $id_semester)
getSoalByTipe($id_pengaturan, $tipe_soal)
getSoalById($id_soal)
simpanSoal($data)
updateSoal($data)
hapusSoal($id_soal)
hitungSoalByPengaturan($id_pengaturan, $tipe_soal)
getStatusKelengkapan($id_kelas, $id_semester)

// === KOP (dari pengaturan_rapor, bukan tabel baru) ===
// Menggunakan PengaturanRapor_model::getPengaturanByKelas($id_kelas, $id_tp)
// Field: kop_rapor (gambar), nama_madrasah, logo_madrasah
// Path gambar: public/img/kop/{kop_rapor}
```

### Views (6 file)

| File | Keterangan |
|------|------------|
| `app/views/admin/soal_sts/index.php` | Dashboard — grid kelas, status per mapel |
| `app/views/admin/soal_sts/pengaturan.php` | Form pengaturan model soal per mapel |
| `app/views/admin/soal_sts/input_soal.php` | Form input soal (dinamis sesuai pengaturan) |
| `app/views/admin/soal_sts/daftar_soal.php` | Tabel semua soal, filter, aksi edit/hapus |
| `app/views/admin/soal_sts/preview_cetak.php` | Preview cetak (HTML, print-friendly) |
| `app/views/admin/soal_sts/cetak_pdf.php` | Template PDF untuk Dompdf |

> 💡 **Tidak ada view kop_soal.php** — kop diambil dari Pengaturan Rapor yang sudah ada.

### Migration (1 file)

| File | Keterangan |
|------|------------|
| `migrations/1.28.0.sql` | DDL untuk 2 tabel baru (`soal_sts_pengaturan`, `soal_sts`) + index |

### Edit File Existing (2 file)

| File | Perubahan |
|------|-----------|
| `app/views/templates/sidebar_admin.php` | Tambah menu "Soal STS" di grup **Akademik** |
| `version.json` | Bump version ke `1.28.0` |

---

## 🔄 3. Alur Kerja (User Flow)

```
Admin buka menu "Soal STS" (sidebar Akademik)
    │
    ├─→ [Dashboard] Grid semua kelas aktif
    │       Setiap kelas menampilkan:
    │       - Jumlah mapel yang sudah ada soal
    │       - Status: ✅ Lengkap / ⚠️ Sebagian / ❌ Belum ada
    │       Klik kelas → masuk ke pengaturan
    │
    ├─→ [Pengaturan Model Soal] per kelas
    │       Tabel semua mapel yang ada penugasan guru di kelas ini
    │       Per mapel, admin set:
    │       - Jumlah soal PG (misal: MTK=20, BIN=25, IPA=15)
    │       - Jumlah soal Essay (misal: MTK=5, BIN=3)
    │       - Jumlah soal Isian Singkat (opsional)
    │       - Jumlah opsi PG: 4 (A-D) atau 5 (A-E)
    │       - Waktu pengerjaan (menit)
    │       - Petunjuk umum ujian
    │       Tombol: "Simpan Semua" / "Copy dari Kelas Lain"
    │
    ├─→ [Input Soal] per mapel per kelas
    │       Form dinamis berdasarkan pengaturan:
    │       ┌─ Bagian I: Pilihan Ganda ──────────────┐
    │       │  Soal 1: [pertanyaan] [gambar?]         │
    │       │          A. [___] B. [___] C. [___]     │
    │       │          D. [___] E. [___]              │
    │       │          Kunci: [dropdown A-E]          │
    │       │  Soal 2: ...                            │
    │       └─────────────────────────────────────────┘
    │       ┌─ Bagian II: Essay ──────────────────────┐
    │       │  Soal 1: [pertanyaan] Skor: [___]       │
    │       └─────────────────────────────────────────┘
    │       ┌─ Bagian III: Isian Singkat ─────────────┐
    │       │  Soal 1: [pertanyaan]                   │
    │       └─────────────────────────────────────────┘
    │       Progress bar: "15/20 PG • 3/5 Essay"
    │       Tombol: "Simpan Semua Soal"
    │
    ├─→ [Daftar Soal] per mapel per kelas
    │       Tabel lengkap semua soal
    │       Filter: PG / Essay / Isian
    │       Aksi: Edit, Hapus, Geser urutan
    │       Statistik kelengkapan
    │
    ├─→ [Preview Cetak]
    │       Tampilan print-friendly:
    │       ┌─────────────────────────────────────────┐
    │       │  [LOGO]  MADRASAH TSANAWIYAH SABILILLAH │
    │       │          Alamat: Jl. ...                 │
    │       │  ─────────────────────────────────────── │
    │       │  SUMATIF TENGAH SEMESTER                 │
    │       │  Mata Pelajaran : Matematika             │
    │       │  Kelas          : VII A                  │
    │       │  Waktu          : 60 Menit               │
    │       │  ─────────────────────────────────────── │
    │       │  Petunjuk Umum:                          │
    │       │  1. Tulislah nama pada lembar jawaban    │
    │       │  ...                                     │
    │       │  ─────────────────────────────────────── │
    │       │  I. PILIHAN GANDA                        │
    │       │  1. Berapakah hasil dari 2 + 2?          │
    │       │     A. 3    B. 4    C. 5    D. 6         │
    │       │  2. ...                                  │
    │       │  ─────────────────────────────────────── │
    │       │  II. ESSAY                               │
    │       │  1. Jelaskan pengertian ... (skor: 10)   │
    │       │  ...                                     │
    │       └─────────────────────────────────────────┘
    │       Tombol: [🖨️ Print] [📄 Download PDF]
    │
    └─→ [Kop Soal = Kop Rapor]
            Otomatis diambil dari Pengaturan Rapor (sudah ada)
            Admin cukup upload kop sekali di Akademik → Pengaturan Rapor
```

---

## 📝 4. Detail Fitur per Halaman

### 4.1 Dashboard Soal STS (`index`)
- Grid card kelas aktif (dari tabel `kelas` berdasarkan `id_tp_aktif`)
- Setiap card menampilkan:
  - Nama kelas (VII A, VII B, VIII A, dst)
  - Jenjang
  - Jumlah mapel total vs yang sudah ada soal
  - Badge status kelengkapan
- Tombol cepat ke "Pengaturan Rapor" (untuk atur kop) di header halaman
- Filter berdasarkan jenjang (VII / VIII / IX)

### 4.2 Pengaturan Model Soal (`pengaturan`)
- Tabel semua mapel yang ada penugasan di kelas tersebut
- Kolom per mapel:
  - Nama Mapel
  - Jumlah PG (input number)
  - Jumlah Essay (input number)
  - Jumlah Isian (input number)
  - Opsi PG: dropdown 4 atau 5
  - Waktu (menit)
  - Petunjuk (textarea, collapsible)
- **Fitur batch**: Set semua mapel sekaligus dengan nilai default
- **Fitur copy**: Copy pengaturan dari kelas lain (misal VII A → VII B)
- Simpan semua sekaligus via AJAX atau form biasa

### 4.3 Input Soal (`inputSoal`)
- Form dinamis berdasarkan pengaturan yang sudah di-set
- **Bagian PG**: Nomor, pertanyaan (textarea), **upload gambar** (opsional), opsi A–D/E (input text), kunci jawaban (radio)
- **Bagian Essay**: Nomor, pertanyaan (textarea), **upload gambar** (opsional), skor (input number)
- **Bagian Isian**: Nomor, pertanyaan (textarea), **upload gambar** (opsional)
- Progress bar real-time: "15/20 PG sudah diisi"
- Auto-save per soal (opsional) atau simpan semua sekaligus
- Validasi: tidak boleh ada soal kosong saat simpan

### 4.4 Daftar Soal (`daftarSoal`)
- Tabel semua soal per mapel per kelas
- Tab filter: Semua | PG | Essay | Isian
- Kolom: No, Tipe, Pertanyaan (truncated), Kunci, Skor, Aksi
- Aksi: Edit (modal/halaman), Hapus (konfirmasi), Geser urutan (drag atau tombol ↑↓)
- Statistik di atas: Total soal, PG: x, Essay: x, Isian: x

### 4.5 Kop Soal (Dari Pengaturan Rapor — Tidak Perlu Halaman Baru)
- Kop soal **otomatis diambil** dari tabel `pengaturan_rapor` yang sudah ada
- Data yang dipakai:
  - `kop_rapor` → gambar kop header (sudah di-upload di Pengaturan Rapor)
  - `nama_madrasah` → nama sekolah
  - `logo_madrasah` → logo (jika ada)
- Admin/wali kelas mengatur kop di menu **Akademik → Pengaturan Rapor** (sudah ada)
- Jika kop belum diatur, tampilkan peringatan + link ke Pengaturan Rapor
- Pattern render kop sama persis dengan cetak nilai, jurnal, rapor, dll:
  ```php
  $kopPath = 'public/img/kop/' . $pengaturanRapor['kop_rapor'];
  $imageData = base64_encode(file_get_contents($kopPath));
  // render sebagai <img> base64
  ```

### 4.6 Preview & Cetak (`previewCetak` / `cetakPDF`)
- Layout print-friendly (CSS `@media print`)
- Struktur:
  1. **Kop**: Logo + nama sekolah + alamat (2 kolom)
  2. **Garis pembatas**
  3. **Info ujian**: Mapel, Kelas, Semester, TP, Hari/Tanggal, Waktu
  4. **Petunjuk umum** (numbered list)
  5. **Bagian I: Pilihan Ganda** — soal + opsi (2 kolom untuk opsi jika muat)
  6. **Bagian II: Essay** — soal + skor
  7. **Bagian III: Isian Singkat** — soal
- Tombol "Print" → `window.print()`
- Tombol "Download PDF" → Dompdf generate A4 portrait
- Tombol "Cetak Kunci Jawaban" → halaman terpisah hanya kunci

---

## 🔧 5. Perubahan pada File Existing

### `app/views/templates/sidebar_admin.php`
Tambah menu baru di grup **Akademik**, setelah "Pengaturan Rapor":

```php
<li>
    <a href="<?= BASEURL; ?>/soalSts"
        class="flex items-center p-2.5 text-sm font-medium rounded-lg ...">
        <i data-lucide="file-question" class="w-4 h-4 mr-2"></i>
        Soal STS
    </a>
</li>
```

Update `$akademikActive` keywords array:
```php
$akademikActive = isGroupActive($judul, [
    'Penugasan', 'Anggota Kelas', 'Monitoring Nilai',
    'Review RPP', 'Pengaturan RPP', 'Pengaturan Rapor',
    'Soal STS'  // ← TAMBAH INI
]);
```

### `version.json`
```json
{
    "version": "1.28.0",
    "build": 1280,
    "release_date": "2026-10-01"
}
```

---

## 📊 6. Ringkasan File

| Kategori | Jumlah | File |
|----------|--------|------|
| Controller | 1 | `SoalStsController.php` |
| Model | 1 | `SoalSts_model.php` |
| Views | 6 | `index`, `pengaturan`, `input_soal`, `daftar_soal`, `preview_cetak`, `cetak_pdf` |
| Migration | 1 | `1.28.0.sql` |
| Edit existing | 2 | `sidebar_admin.php`, `version.json` |
| **TOTAL** | **11** | |

---

## ⚡ 7. Urutan Implementasi

| Step | Task | File | Dependensi |
|------|------|------|------------|
| 1 | Buat migration SQL | `migrations/1.28.0.sql` | — |
| 2 | Jalankan migration | Database | Step 1 |
| 3 | Buat Model | `app/models/SoalSts_model.php` | Step 2 |
| 4 | Buat Controller | `app/controllers/SoalStsController.php` | Step 3 |
| 5 | Tambah menu sidebar | `sidebar_admin.php` | — |
| 6 | View: Dashboard | `admin/soal_sts/index.php` | Step 4 |
| 7 | View: Pengaturan | `admin/soal_sts/pengaturan.php` | Step 4 |
| 8 | View: Input Soal | `admin/soal_sts/input_soal.php` | Step 4 |
| 9 | View: Daftar Soal | `admin/soal_sts/daftar_soal.php` | Step 4 |
| 10 | View: Preview Cetak | `admin/soal_sts/preview_cetak.php` | Step 4 |
| 11 | View: Template PDF | `admin/soal_sts/cetak_pdf.php` | Step 4 |
| 12 | Update version.json | `version.json` | All |
| 13 | Testing & QA | — | All |

---

## 🔒 8. Keamanan & Validasi

- **Auth guard**: Hanya role `admin` yang bisa akses `SoalStsController`
- **CSRF**: Semua form POST otomatis terproteksi (sudah ada di `App.php`)
- **Input validation**: Sanitize semua input soal (htmlspecialchars)
- **SQL injection**: Semua query pakai prepared statements (PDO bind)

### Upload Gambar Soal
- **Path upload**: `public/uploads/soal_sts/` (buat folder baru)
- **Format**: JPG, JPEG, PNG, GIF, WEBP
- **Max size**: 2MB per gambar
- **Penamaan file**: `soal_{id_pengaturan}_{nomor}_{timestamp}.{ext}` (hindari duplikat)
- **Preview**: Tampilkan thumbnail di form input setelah upload
- **Render di PDF**: Convert ke base64 (pattern sama dengan kop rapor)
  ```php
  if (!empty($soal['gambar_soal'])) {
      $imgPath = 'public/uploads/soal_sts/' . $soal['gambar_soal'];
      $imgData = base64_encode(file_get_contents($imgPath));
      $imgType = pathinfo($imgPath, PATHINFO_EXTENSION);
      $imgSrc  = 'data:image/' . $imgType . ';base64,' . $imgData;
      // render <img> di bawah pertanyaan
  }
  ```
- **Hapus gambar**: Saat soal dihapus/diganti, file lama dihapus dari disk
- **Nginx**: Folder `uploads/soal_sts/` sudah aman (PHP execution diblokir di uploads)

---

## 💡 9. Catatan Teknis

### Routing
URL akan otomatis ter-route oleh `App.php`:
- `?url=soalSts` → `SoalStsController::index()`
- `?url=soalSts/pengaturan/5` → `SoalStsController::pengaturan(5)`
- `?url=soalSts/inputSoal/3` → `SoalStsController::inputSoal(3)`
- dst.

### PDF Generation
Menggunakan **Dompdf** yang sudah ada di `app/core/dompdf/`:
```php
require_once APPROOT . '/app/core/dompdf/autoload.inc.php';
$dompdf = new Dompdf($options);
```

### Data Dependencies
- Kelas aktif: `Kelas_model::getKelasByTP($id_tp_aktif)`
- Mapel per kelas: `Penugasan_model::getMapelByKelas($id_kelas, $id_semester)`
- Kop soal: `PengaturanRapor_model::getPengaturanByKelas($id_kelas, $id_tp)` → field `kop_rapor`, `nama_madrasah`
- Gambar kop: `public/img/kop/{kop_rapor}` (render sebagai base64 di PDF)
- Siswa per kelas: tidak diperlukan (soal bukan per siswa)

---

## ❓ 10. Keputusan yang Perlu Diambil

| # | Pertanyaan | Default |
|---|-----------|---------|
| 1 | Apakah perlu fitur duplikasi soal dari semester sebelumnya? | Tidak (v1) |
| 2 | Apakah soal ini hanya untuk cetak kertas atau juga ujian online (CBT)? | Cetak kertas saja |
| 3 | Apakah perlu kunci jawaban terpisah untuk guru? | Ya, halaman cetak terpisah |
| 4 | Apakah guru juga bisa input soal, atau hanya admin? | Admin saja (v1) |
| 5 | Apakah perlu fitur import soal dari Excel/Word? | Tidak (v1) |
| 6 | ~~Apakah perlu tabel kop soal terpisah?~~ | ❌ Tidak — pakai kop dari Pengaturan Rapor |
