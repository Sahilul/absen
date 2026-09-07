# Rencana Rewrite Super App Sabilillah: PHP → Go + Vue 3

> Dokumen ini memetakan SELURUH komponen sistem yang harus di-rewrite,
> strategi migrasi bertahap, estimasi effort, dan checklist per modul.

---

## Daftar Isi

1. [Ringkasan Proyek Saat Ini](#1-ringkasan-proyek-saat-ini)
2. [Arsitektur Target](#2-arsitektur-target)
3. [Tech Stack Baru](#3-tech-stack-baru)
4. [Strategi Migrasi](#4-strategi-migrasi)
5. [Fase 0 — Fondasi](#5-fase-0--fondasi)
6. [Fase 1 — Auth & Core](#6-fase-1--auth--core)
7. [Fase 2 — Modul Akademik](#7-fase-2--modul-akademik)
8. [Fase 3 — Modul Operasional](#8-fase-3--modul-operasional)
9. [Fase 4 — Modul Berat](#9-fase-4--modul-berat)
10. [Fase 5 — Modul Publik & Integrasi](#10-fase-5--modul-publik--integrasi)
11. [Fase 6 — Polish & Cutover](#11-fase-6--polish--cutover)
12. [Peta Lengkap Route → Go Handler](#12-peta-lengkap-route--go-handler)
13. [Peta Lengkap Tabel → GORM Model](#13-peta-lengkap-tabel--gorm-model)
14. [Peta Lengkap View → Vue Component](#14-peta-lengkap-view--vue-component)
15. [Integrasi Eksternal](#15-integrasi-eksternal)
16. [PDF Generation](#16-pdf-generation)
17. [Core Utilities — Peta Migrasi PHP → Go](#17-core-utilities--peta-migrasi-php--go)
18. [Existing Mobile API — Backward Compatibility](#18-existing-mobile-api--backward-compatibility)
19. [QR Validation System](#19-qr-validation-system)
20. [Config & Settings Migration](#20-config--settings-migration)
21. [Static Assets & File Storage](#21-static-assets--file-storage)
22. [Alpine.js → Vue 3 Migration Patterns](#22-alpinejs--vue-3-migration-patterns)
23. [Existing Dev Workflow & Versioning](#23-existing-dev-workflow--versioning)
24. [Risiko & Mitigasi](#24-risiko--mitigasi)

---

## 1. Ringkasan Proyek Saat Ini

| Metrik | Jumlah |
|---|---|
| Kode PHP (tanpa vendor/backup) | ~60.300 baris |
| Inline JavaScript di views | ~19.400 baris |
| Controller + Trait | 38 file |
| Route/method web | ~580+ |
| API endpoint (REST) | ~30 |
| Model | 49 file, ~15.000 baris |
| View | 255 file, ~75.000 baris |
| Tabel database | 80 |
| Foreign key constraint | 43 |
| Integrasi eksternal | 6 |
| Role pengguna | 7 (admin, guru, wali_kelas, siswa, kepala_madrasah, bendahara, petugas_buku_tamu) |
| PDF template | 10+ jenis dokumen |
| Modul fitur | 29 |

### Modul Berdasarkan Kompleksitas

| Kompleksitas | Modul |
|---|---|
| **Sangat Tinggi** | PSB (56 method), Wali Kelas (58 method), Pengaturan Admin (67 method) |
| **Tinggi** | Siswa (39 method), Guru (40 method), Nilai/Rapor, Pembayaran, Dashboard Admin, WhatsApp Gateway, Laporan |
| **Sedang** | Jadwal (28 method), CMS (32 method), Buku Tamu, Persuratan (dashboard gabungan Surat Tugas + Surat Penerimaan, sidebar sendiri), Performa Guru, RPP, Pesan |
| **Rendah** | Foto, QR Validation, Sitemap, News, Update System, PWA Manifest (dynamic) |

---

## 2. Arsitektur Target

```
┌─────────────────────────────────────────────────────┐
│                    NGINX                             │
│  ┌──────────────┐  ┌────────────────────────────┐   │
│  │ /api/*       │  │ /* (SPA)                   │   │
│  │ proxy → :8090│  │ serve dist/ + fallback     │   │
│  └──────┬───────┘  └────────────┬───────────────┘   │
│         │                       │                    │
│  ┌──────▼───────┐  ┌───────────▼────────────────┐   │
│  │  Go Backend  │  │  Vue 3 SPA (Vite build)    │   │
│  │  Gin/Fiber   │  │  + Pinia + Vue Router      │   │
│  │  Port 8090   │  │  + Tailwind CSS            │   │
│  └──────┬───────┘  └───────────────────────────┘    │
│         │                                            │
│  ┌──────▼───────┐  ┌───────────────────────────┐    │
│  │   MySQL      │  │  Cloudflare R2             │    │
│  │  (existing)  │  │  (foto, dokumen)           │    │
│  └──────────────┘  └───────────────────────────┘    │
└─────────────────────────────────────────────────────┘
```

### Perubahan Arsitektur Kunci

| Aspek | PHP Sekarang | Go + Vue Target |
|---|---|---|
| Rendering | Server-side (PHP view) | Client-side SPA |
| Routing | Convention-based PHP | Vue Router (FE) + Gin router (BE) |
| State | PHP Session | JWT + Pinia store |
| CSRF | Token di session | Tidak perlu (JWT stateless) |
| Auth | Session cookie | Access token + refresh token (httpOnly cookie) |
| API format | Mixed (HTML + JSON AJAX) | Pure JSON REST API |
| PDF | Dompdf (PHP) | wkhtmltopdf / Chromedp / Go template → PDF |
| Real-time | Polling | WebSocket (opsional, untuk notifikasi) |

---

## 3. Tech Stack Baru

### Prinsip Desain Frontend

> **Simple, tegas, modern.** Tidak perlu animasi berlebihan atau dekorasi yang tidak fungsional.
> Fokus pada kejelasan informasi, whitespace yang cukup, dan interaksi yang responsif.
>
> - **Clean layout** — sidebar collapsible, content area lega, card-based dashboard
> - **Tipografi tegas** — font-weight jelas antara heading dan body, ukuran konsisten
> - **Warna minimal** — primary color (hijau madrasah), neutral gray scale, accent hanya untuk status/badge
> - **Tanpa clutter** — satu aksi utama per halaman jelas terlihat, secondary actions di dropdown
> - **Mobile-first** — responsive bukan afterthought, sidebar jadi sheet di mobile
> - **Feedback instan** — skeleton loading, optimistic update, toast notification via Sonner
> - **Konsistensi** — semua form, tabel, dialog pakai shadcn-vue components, zero custom CSS kecuali theming

### Backend (Go)

| Komponen | Library | Alasan |
|---|---|---|
| HTTP Framework | `gin-gonic/gin` | Performa tinggi, middleware ecosystem |
| ORM | `gorm.io/gorm` + `gorm.io/driver/mysql` | Familiar, migration support |
| Auth | `golang-jwt/jwt/v5` | JWT access + refresh token |
| Validation | `go-playground/validator/v10` | Struct tag validation |
| Config | `spf13/viper` | YAML/env config |
| Logging | `rs/zerolog` | Structured JSON logging |
| PDF | `nicholasgasior/gowkhtmltopdf` atau `chromedp` | HTML → PDF |
| S3/R2 | `aws/aws-sdk-go-v2` | R2 S3-compatible |
| Google OAuth | `golang.org/x/oauth2` | OAuth2 flow |
| Google Drive | `google.golang.org/api/drive/v3` | File upload/management |
| Firebase FCM | `firebase.google.com/go/v4` | Push notification |
| WhatsApp | Custom HTTP client | Fonnte/GoWA API calls |
| Image Processing | `disintegration/imaging` | Crop, resize foto |
| Excel | `xuri/excelize/v2` | Import/export Excel |
| Cron | `robfig/cron/v3` | In-process scheduler (ganti cron_wa_processor) |
| Migration | `golang-migrate/migrate` | SQL migration runner |
| QR Code | `skip2/go-qrcode` | Generate QR code |

### Frontend (Vue 3)

| Komponen | Library | Alasan |
|---|---|---|
| Framework | Vue 3 (Composition API) | Reactive, TypeScript support |
| Build | Vite | Fast HMR, optimized build |
| Router | Vue Router 4 | SPA routing |
| State | Pinia | Type-safe store |
| HTTP | Axios | Interceptor untuk JWT refresh |
| UI Components | **shadcn-vue** | Komponen headless (Reka UI) + Tailwind, copy-paste ke `src/components/ui/`, fully customizable, tidak ada vendor lock-in |
| Styling | Tailwind CSS 4 | Konsisten dengan desain sekarang, required by shadcn-vue |
| Icons | Lucide Vue (`lucide-vue-next`) | Sama dengan sekarang, default icon set shadcn-vue |
| Charts | Chart.js + vue-chartjs | Dashboard charts (shadcn Chart belum stabil) |
| Rich Text | TipTap | Editor konten CMS |
| Form | VeeValidate + Zod | Form validation (integrasi shadcn Form component) |
| Date | date-fns + `@internationalized/date` | Format tanggal Indonesia, required by shadcn DatePicker |
| PDF Preview | vue-pdf-embed | Preview PDF di browser |
| Camera | MediaDevices API | Foto siswa webcam |
| TypeScript | Ya | Type safety end-to-end |

#### shadcn-vue Components yang Dipakai

Komponen di-install on-demand via CLI (`pnpm dlx shadcn-vue@latest add <name>`), source code masuk ke `src/components/ui/`. Berikut mapping kebutuhan:

| Kebutuhan App | shadcn-vue Component | Catatan |
|---|---|---|
| Layout sidebar + topbar | **Sidebar**, **Sheet** (mobile) | Collapsible sidebar, responsive |
| Tabel data (siswa, guru, dll) | **Table** + **DataTable** pattern | Pakai TanStack Table adapter bawaan shadcn |
| Modal/dialog | **Dialog**, **AlertDialog** | Ganti custom Modal.vue |
| Form input | **Input**, **Textarea**, **Select**, **Checkbox**, **RadioGroup**, **Switch**, **Label** | Semua form field |
| Form validation | **Form** (VeeValidate + Zod) | Auto-bind error messages |
| Tombol | **Button** | Variant: default, destructive, outline, secondary, ghost, link |
| Dropdown menu | **DropdownMenu** | Aksi per-row tabel, user menu |
| Toast/notifikasi | **Sonner** (vue-sonner) | Ganti vue-toastification |
| Konfirmasi hapus | **AlertDialog** | Ganti ConfirmDialog.vue |
| Tab navigasi | **Tabs** | Tab di halaman pengaturan, detail siswa |
| Card | **Card** | Dashboard stats, mobile list view |
| Badge/tag | **Badge** | Status siswa, status pembayaran |
| Pagination | **Pagination** | Ganti custom Pagination.vue |
| Search + filter | **Command** (cmdk), **Popover**, **Combobox** pattern | Search siswa, pilih kelas |
| Date picker | **DatePicker**, **Calendar** | Input tanggal lahir, filter tanggal |
| File upload | **Input** (type=file) + custom | Drag-drop zone tetap custom |
| Avatar | **Avatar** | Foto siswa di tabel/detail |
| Skeleton loading | **Skeleton** | Loading state semua halaman |
| Breadcrumb | **Breadcrumb** | Navigasi hierarki |
| Tooltip | **Tooltip** | Hint pada icon/tombol |
| Progress | **Progress** | Upload progress, kelengkapan dokumen |
| Separator | **Separator** | Pemisah section |
| Accordion | **Accordion** | FAQ PSB, detail formulir |
| Stepper | **Stepper** | Multi-step form PSB |
| Chart | **Chart** (wrapper) | Dashboard chart (opsional, bisa tetap vue-chartjs) |

---

## 4. Strategi Migrasi

### Prinsip: Incremental Cutover, Bukan Big Bang

```
Fase 0 ──→ Fase 1 ──→ Fase 2 ──→ Fase 3 ──→ Fase 4 ──→ Fase 5 ──→ Fase 6
Fondasi    Auth &     Akademik   Operasional  Modul      Publik &   Polish &
           Core                               Berat      Integrasi  Cutover
(2 minggu) (3 minggu) (4 minggu) (3 minggu)  (5 minggu) (3 minggu) (2 minggu)
```

**Total estimasi: ~22 minggu (~5,5 bulan) solo developer full-time.**

### Strategi Paralel PHP ↔ Go

Selama migrasi, kedua sistem berjalan bersamaan:

```
sabilillah.id/          → PHP (existing, tetap jalan)
app.sabilillah.id/      → Vue SPA (modul yang sudah di-rewrite)
api.sabilillah.id/      → Go API (backend baru)
```

Setelah semua modul selesai, cutover DNS `sabilillah.id` ke Vue SPA.

---

## 5. Fase 0 — Fondasi (Minggu 1-2)

### 5.1 Setup Project Go

```
backend/
├── cmd/
│   └── server/
│       └── main.go              # Entry point
├── internal/
│   ├── config/
│   │   └── config.go            # Viper config loader
│   ├── database/
│   │   └── database.go          # GORM connection
│   ├── middleware/
│   │   ├── auth.go              # JWT middleware
│   │   ├── cors.go              # CORS
│   │   ├── logger.go            # Request logging
│   │   └── ratelimit.go         # Rate limiting
│   ├── models/                  # GORM models (80 tabel)
│   ├── handlers/                # HTTP handlers (per modul)
│   ├── services/                # Business logic
│   ├── repositories/            # Database queries
│   ├── dto/                     # Request/Response structs
│   └── pkg/
│       ├── auth/                # JWT helper
│       ├── fonnte/              # WhatsApp client
│       ├── gdrive/              # Google Drive client
│       ├── r2/                  # Cloudflare R2 client
│       ├── fcm/                 # Firebase FCM client
│       ├── pdf/                 # PDF generator
│       ├── qrcode/              # QR code generator
│       ├── excel/               # Excel import/export
│       └── validator/           # Custom validators
├── migrations/                  # SQL migrations
├── config.yaml                  # App config
├── go.mod
└── go.sum
```

### 5.2 Setup Project Vue + shadcn-vue

```
frontend/
├── src/
│   ├── api/
│   │   ├── client.ts            # Axios instance + interceptor
│   │   ├── auth.ts              # Auth API calls
│   │   ├── siswa.ts             # Siswa API calls
│   │   └── ...                  # Per-modul API
│   ├── assets/
│   │   └── css/
│   │       └── main.css         # @import "tailwindcss" + shadcn theme vars
│   ├── components/
│   │   ├── ui/                  # ← shadcn-vue components (auto-generated)
│   │   │   ├── button/
│   │   │   │   ├── Button.vue
│   │   │   │   └── index.ts
│   │   │   ├── card/
│   │   │   ├── dialog/
│   │   │   ├── dropdown-menu/
│   │   │   ├── form/
│   │   │   ├── input/
│   │   │   ├── select/
│   │   │   ├── sidebar/
│   │   │   ├── sonner/
│   │   │   ├── table/
│   │   │   ├── tabs/
│   │   │   └── ...              # ~30 komponen, install on-demand
│   │   ├── app/                 # App-level composed components
│   │   │   ├── AppSidebar.vue   # Sidebar menu (pakai shadcn Sidebar)
│   │   │   ├── AppTopbar.vue    # Top navigation bar
│   │   │   ├── DataTable.vue    # Reusable DataTable (shadcn Table + TanStack)
│   │   │   ├── PhotoCapture.vue # Webcam + upload + crop 3:4
│   │   │   ├── FileUpload.vue   # Drag-drop file upload zone
│   │   │   └── SearchCombobox.vue # Search with shadcn Command
│   │   ├── admin/               # Admin-specific composed components
│   │   ├── guru/
│   │   ├── siswa/
│   │   └── ...
│   ├── composables/             # Reusable logic
│   │   ├── useAuth.ts
│   │   ├── usePagination.ts
│   │   ├── useDebounce.ts
│   │   └── ...
│   ├── layouts/
│   │   ├── AdminLayout.vue      # SidebarProvider + AppSidebar + main content
│   │   ├── GuruLayout.vue
│   │   ├── SiswaLayout.vue
│   │   ├── PublicLayout.vue
│   │   └── AuthLayout.vue
│   ├── lib/
│   │   └── utils.ts             # shadcn-vue utility (cn helper, clsx + twMerge)
│   ├── pages/                   # Route pages (per modul)
│   │   ├── auth/
│   │   ├── admin/
│   │   ├── guru/
│   │   ├── siswa/
│   │   └── ...
│   ├── router/
│   │   └── index.ts             # Vue Router config
│   ├── stores/
│   │   ├── auth.ts              # Auth Pinia store
│   │   ├── semester.ts          # Active semester store
│   │   └── ...
│   ├── types/                   # TypeScript interfaces
│   │   ├── siswa.ts
│   │   ├── guru.ts
│   │   └── ...
│   ├── utils/
│   │   ├── date.ts              # Format tanggal Indonesia
│   │   ├── currency.ts          # Format rupiah
│   │   └── ...
│   ├── App.vue
│   └── main.ts
├── components.json              # ← shadcn-vue config (path aliases, style)
├── index.html
├── vite.config.ts
├── tailwind.config.js
├── tsconfig.json
├── tsconfig.app.json
└── package.json
```

### 5.3 Setup shadcn-vue (Detail)

```bash
# 1. Create project
pnpm create vite@latest frontend --template vue-ts
cd frontend

# 2. Install Tailwind CSS
pnpm add tailwindcss @tailwindcss/vite

# 3. Install path alias support
pnpm add -D @types/node

# 4. Init shadcn-vue (interactive — pilih style, color, dst)
pnpm dlx shadcn-vue@latest init

# 5. Install core components yang pasti dipakai
pnpm dlx shadcn-vue@latest add button card dialog alert-dialog input textarea \
  select checkbox radio-group switch label form table tabs badge avatar \
  dropdown-menu sonner tooltip skeleton breadcrumb separator pagination \
  sidebar sheet popover command calendar date-picker progress accordion stepper

# 6. Install app dependencies
pnpm add vue-router@4 pinia axios @vueuse/core vue-sonner
pnpm add -D unplugin-vue-router unplugin-auto-import
```

File `components.json` yang dihasilkan shadcn-vue init:

```json
{
  "$schema": "https://shadcn-vue.com/schema.json",
  "style": "default",
  "typescript": true,
  "tailwind": {
    "config": "tailwind.config.js",
    "css": "src/assets/css/main.css",
    "baseColor": "zinc"
  },
  "framework": "vite",
  "aliases": {
    "components": "@/components",
    "utils": "@/lib/utils"
  }
}
```

File `src/lib/utils.ts` (auto-generated, helper `cn()`):

```ts
import { type ClassValue, clsx } from 'clsx'
import { twMerge } from 'tailwind-merge'

export function cn(...inputs: ClassValue[]) {
  return twMerge(clsx(inputs))
}
```

### 5.4 Checklist Fase 0

- [ ] Init Go module + install dependencies
- [ ] Setup Vite + Vue 3 + TypeScript
- [ ] Install Tailwind CSS + `@tailwindcss/vite` plugin
- [ ] Run `shadcn-vue init` — configure `components.json`
- [ ] Install ~30 shadcn-vue components (lihat daftar di 5.3)
- [ ] GORM connection ke database existing
- [ ] Generate GORM model dari 80 tabel (bisa pakai `gen` atau manual)
- [ ] Setup JWT auth middleware
- [ ] Setup CORS middleware
- [ ] Setup Axios interceptor (auto-refresh token)
- [ ] Buat AppSidebar.vue (pakai shadcn **Sidebar** component) — clone menu dari PHP
- [ ] Buat AppTopbar.vue — user menu pakai shadcn **DropdownMenu**
- [ ] Buat AdminLayout.vue — **SidebarProvider** + AppSidebar + main content area
- [ ] Buat DataTable.vue — shadcn **Table** + TanStack Table adapter
- [ ] Setup **Sonner** (toast) di App.vue
- [ ] Setup Vue Router dengan route guards (role-based)
- [ ] Setup Pinia stores: auth, semester
- [ ] Deploy Go API ke port 8090, nginx proxy
- [ ] Deploy Vue dist ke nginx
- [ ] **Migration baseline**: dump schema existing (`mysqldump --no-data`) sebagai baseline migration. 34 file di `migrations/` + 8 file di `database/` hanya untuk referensi — Go pakai `golang-migrate` dengan baseline baru. Folder `migrations/executed/` berisi migration yang sudah jalan di PHP.

---

## 6. Fase 1 — Auth & Core (Minggu 3-5)

### 6.1 Auth System

**Go API endpoints:**

| Method | Endpoint | Deskripsi |
|---|---|---|
| POST | `/api/v1/auth/login` | Login (username + password + session_akademik) |
| POST | `/api/v1/auth/logout` | Logout (invalidate refresh token) |
| POST | `/api/v1/auth/refresh` | Refresh access token |
| GET | `/api/v1/auth/me` | Get current user profile |
| GET | `/api/v1/auth/google` | Redirect ke Google OAuth |
| GET | `/api/v1/auth/google/callback` | Handle OAuth callback |

**Vue pages:**
- `pages/auth/LoginPage.vue` — form login + semester picker + Google login
- `stores/auth.ts` — token management, auto-refresh, role state

**Tabel terlibat:** `users`, `login_history`, `semester`, `tp`

### 6.2 Dashboard (per role)

**Go API endpoints:**

| Method | Endpoint | Deskripsi |
|---|---|---|
| GET | `/api/v1/admin/dashboard/stats` | Statistik dashboard admin |
| GET | `/api/v1/admin/dashboard/attendance-today` | Absensi hari ini |
| GET | `/api/v1/admin/dashboard/attendance-trend` | Tren absensi |
| GET | `/api/v1/admin/dashboard/recent-journals` | Jurnal terbaru |
| GET | `/api/v1/admin/dashboard/alerts` | System alerts |
| GET | `/api/v1/guru/dashboard` | Dashboard guru |
| GET | `/api/v1/siswa/dashboard` | Dashboard siswa |
| GET | `/api/v1/wali-kelas/dashboard` | Dashboard wali kelas |
| GET | `/api/v1/kepala-madrasah/dashboard` | Dashboard kepala madrasah |

**Vue pages:**
- `pages/admin/DashboardPage.vue`
- `pages/guru/DashboardPage.vue`
- `pages/siswa/DashboardPage.vue`
- `pages/wali-kelas/DashboardPage.vue`
- `pages/kepala-madrasah/DashboardPage.vue`

### 6.3 Semester Management

| Method | Endpoint | Deskripsi |
|---|---|---|
| POST | `/api/v1/auth/set-semester` | Switch semester aktif |
| GET | `/api/v1/semesters` | List semester |

### 6.4 Profil & Ganti Password (semua role)

| Method | Endpoint | Deskripsi |
|---|---|---|
| GET | `/api/v1/profile` | Get profil user |
| PUT | `/api/v1/profile` | Update profil |
| PUT | `/api/v1/profile/password` | Ganti password |

### 6.5 Checklist Fase 1

- [ ] Handler: login, logout, refresh, me, google OAuth
- [ ] Handler: dashboard stats (5 role × masing-masing endpoint)
- [ ] Handler: semester list, set semester
- [ ] Handler: profil, ganti password
- [ ] Vue: LoginPage
- [ ] Vue: 5 dashboard pages
- [ ] Vue: ProfilPage, GantiPasswordPage
- [ ] Vue: Sidebar navigation (dynamic per role)
- [ ] Test: login flow end-to-end
- [ ] Test: role-based route guard

---

## 7. Fase 2 — Modul Akademik (Minggu 6-9)

### 7.1 Manajemen Siswa (39 method → ~25 endpoint)

| Method | Endpoint | Deskripsi |
|---|---|---|
| GET | `/api/v1/admin/siswa` | List siswa (paginated, search, filter) |
| POST | `/api/v1/admin/siswa` | Tambah siswa |
| GET | `/api/v1/admin/siswa/:id` | Detail siswa |
| PUT | `/api/v1/admin/siswa/:id` | Update siswa |
| DELETE | `/api/v1/admin/siswa/:id` | Hapus siswa |
| DELETE | `/api/v1/admin/siswa/bulk` | Bulk hapus |
| POST | `/api/v1/admin/siswa/:id/foto` | Upload foto (R2) |
| DELETE | `/api/v1/admin/siswa/:id/foto` | Hapus foto |
| GET | `/api/v1/admin/siswa/:id/password` | Lihat password |
| POST | `/api/v1/admin/siswa/generate-password` | Generate password batch |
| POST | `/api/v1/admin/siswa/import` | Import dari Excel |
| POST | `/api/v1/admin/siswa/import/preview` | Preview import |
| GET | `/api/v1/admin/siswa/template` | Download template Excel |
| GET | `/api/v1/admin/siswa/export` | Export ke Excel/JSON |
| POST | `/api/v1/admin/siswa/import-psb` | Import dari PSB |
| GET | `/api/v1/admin/siswa/psb-candidates` | List kandidat PSB |
| GET | `/api/v1/admin/siswa/:id/dokumen` | List dokumen siswa |
| POST | `/api/v1/admin/siswa/:id/dokumen` | Upload dokumen |
| DELETE | `/api/v1/admin/siswa/dokumen/:id` | Hapus dokumen |
| GET | `/api/v1/admin/siswa/dokumen/:id/download` | Download dokumen |
| GET | `/api/v1/admin/siswa/monitoring-dokumen` | Monitoring kelengkapan |
| GET | `/api/v1/admin/siswa/cetak-kartu-login/:kelas` | PDF kartu login |
| GET | `/api/v1/admin/siswa/check-nisn/:nisn` | Cek NISN tersedia |
| GET | `/api/v1/foto/siswa/:id` | Serve foto (proxy R2) |

**Vue pages (12):**
- `SiswaListPage.vue` — tabel + mobile card + search + filter + pagination
- `SiswaAddPage.vue` — form tambah
- `SiswaEditPage.vue` — form edit
- `SiswaDetailModal.vue` — modal detail (reuse component)
- `SiswaFotoModal.vue` — modal foto (webcam + upload + crop 3:4)
- `SiswaDokumenPage.vue` — dokumen per siswa
- `SiswaImportPage.vue` — import Excel
- `SiswaImportPsbPage.vue` — import dari PSB
- `SiswaMonitoringDokumenPage.vue`
- `SiswaExportPage.vue`
- `SiswaCetakKartuPage.vue`

**Tabel:** `siswa`, `users`, `keanggotaan_kelas`, `kelas`, `siswa_dokumen`

### 7.2 Manajemen Guru (9 method → ~8 endpoint)

| Method | Endpoint | Deskripsi |
|---|---|---|
| GET | `/api/v1/admin/guru` | List guru |
| POST | `/api/v1/admin/guru` | Tambah guru |
| GET | `/api/v1/admin/guru/:id` | Detail guru |
| PUT | `/api/v1/admin/guru/:id` | Update guru |
| DELETE | `/api/v1/admin/guru/:id` | Hapus guru |
| DELETE | `/api/v1/admin/guru/bulk` | Bulk hapus |
| POST | `/api/v1/admin/guru/:id/generate-password` | Generate password |
| POST | `/api/v1/admin/guru/reconcile` | Rekonsiliasi akun |

**Vue pages (3):**
- `GuruListPage.vue`
- `GuruAddPage.vue`
- `GuruEditPage.vue`

**Tabel:** `guru`, `users`, `guru_fungsi`

### 7.3 Akademik — Tahun Pelajaran, Kelas, Mapel, Penugasan, Keanggotaan (38 method)

| Method | Endpoint | Deskripsi |
|---|---|---|
| CRUD | `/api/v1/admin/tahun-pelajaran` | CRUD tahun pelajaran |
| POST | `/api/v1/admin/tahun-pelajaran/:id/set-default` | Set default semester |
| CRUD | `/api/v1/admin/kelas` | CRUD kelas |
| CRUD | `/api/v1/admin/mapel` | CRUD mata pelajaran |
| CRUD | `/api/v1/admin/penugasan` | CRUD penugasan guru-kelas-mapel |
| POST | `/api/v1/admin/penugasan/copy` | Copy penugasan antar semester |
| GET | `/api/v1/admin/penugasan/check-duplicate` | Cek duplikat |
| GET | `/api/v1/admin/keanggotaan` | List keanggotaan kelas |
| POST | `/api/v1/admin/keanggotaan` | Tambah anggota kelas |
| DELETE | `/api/v1/admin/keanggotaan/:id` | Hapus anggota |
| POST | `/api/v1/admin/naik-kelas` | Proses naik kelas |
| POST | `/api/v1/admin/kelulusan` | Proses kelulusan |
| GET | `/api/v1/admin/kelas-by-tp/:id` | Kelas per tahun pelajaran |
| GET | `/api/v1/admin/siswa-by-kelas/:kelas/:tp` | Siswa per kelas |

**Vue pages (8):**
- `TahunPelajaranPage.vue`
- `KelasPage.vue`
- `MapelPage.vue`
- `PenugasanPage.vue`
- `KeanggotaanPage.vue`
- `NaikKelasPage.vue`
- `KelulusanPage.vue`

**Tabel:** `tp`, `semester`, `kelas`, `mapel`, `penugasan`, `keanggotaan_kelas`

### 7.4 Checklist Fase 2

- [ ] GORM models: siswa (69 col), guru, tp, semester, kelas, mapel, penugasan, keanggotaan_kelas, siswa_dokumen
- [ ] Repository + Service + Handler: Siswa CRUD (25 endpoint)
- [ ] Repository + Service + Handler: Guru CRUD (8 endpoint)
- [ ] Repository + Service + Handler: Akademik CRUD (14 endpoint)
- [ ] R2 client Go: upload, delete, processImage (crop 3:4 + resize)
- [ ] Excel import/export Go
- [ ] Vue: 23 pages/components untuk Fase 2
- [ ] Test: CRUD siswa end-to-end
- [ ] Test: import Excel
- [ ] Test: foto upload + crop

---

## 8. Fase 3 — Modul Operasional (Minggu 10-12)

### 8.1 Jurnal & Absensi Guru (40 method → ~20 endpoint)

| Method | Endpoint | Deskripsi |
|---|---|---|
| GET | `/api/v1/guru/jurnal` | List jurnal |
| POST | `/api/v1/guru/jurnal` | Tambah jurnal |
| PUT | `/api/v1/guru/jurnal/:id` | Edit jurnal |
| GET | `/api/v1/guru/jurnal/:id/absensi` | Get absensi per jurnal |
| POST | `/api/v1/guru/jurnal/:id/absensi` | Simpan absensi |
| PUT | `/api/v1/guru/jurnal/:id/absensi` | Edit absensi |
| GET | `/api/v1/guru/rekap-absen` | Rekap absensi per mapel |
| GET | `/api/v1/guru/rekap-absen/:id` | Detail rekap |
| GET | `/api/v1/guru/riwayat` | Riwayat mengajar |
| GET | `/api/v1/guru/riwayat/:id` | Detail riwayat per mapel |
| GET | `/api/v1/guru/rincian-absen/:id` | Rincian absensi per mapel |
| GET | `/api/v1/guru/cetak/absensi/:id` | PDF absensi |
| GET | `/api/v1/guru/cetak/rekap/:id` | PDF rekap |
| GET | `/api/v1/guru/cetak/mapel/:id` | PDF per mapel |

**Vue pages (8):**
- `guru/JurnalListPage.vue`
- `guru/JurnalAddPage.vue`
- `guru/JurnalEditPage.vue`
- `guru/AbsensiPage.vue`
- `guru/RekapAbsenPage.vue`
- `guru/RiwayatPage.vue`
- `guru/RincianAbsenPage.vue`

**Tabel:** `jurnal`, `absensi`, `penugasan`, `keanggotaan_kelas`, `siswa`

### 8.2 Nilai & Rapor (20 method NilaiController + rapor di WaliKelas)

| Method | Endpoint | Deskripsi |
|---|---|---|
| GET | `/api/v1/nilai/kelas` | Pilih kelas untuk input nilai |
| GET | `/api/v1/nilai/tugas-harian` | List tugas harian |
| POST | `/api/v1/nilai/tugas-harian` | Simpan nilai harian |
| PUT | `/api/v1/nilai/tugas-harian` | Edit nilai harian |
| DELETE | `/api/v1/nilai/tugas-harian` | Hapus nilai harian |
| GET | `/api/v1/nilai/tengah-semester` | Nilai STS |
| POST | `/api/v1/nilai/tengah-semester` | Simpan STS |
| GET | `/api/v1/nilai/akhir-semester` | Nilai SAS |
| POST | `/api/v1/nilai/akhir-semester` | Simpan SAS |
| GET | `/api/v1/nilai/cetak/harian` | PDF nilai harian |
| GET | `/api/v1/nilai/cetak/sts` | PDF nilai STS |
| GET | `/api/v1/nilai/cetak/sas` | PDF nilai SAS |
| GET | `/api/v1/wali-kelas/rapor/generate/:jenis/:id` | Generate rapor siswa |
| GET | `/api/v1/wali-kelas/rapor/generate-all/:jenis` | Generate rapor kelas |
| GET | `/api/v1/rapor-sts/generate/:id` | Generate rapor STS |
| GET | `/api/v1/rapor-sts/cetak-kelas` | Cetak rapor STS kelas |

**Vue pages (6):**
- `nilai/PilihKelasPage.vue`
- `nilai/TugasHarianPage.vue`
- `nilai/TengahSemesterPage.vue`
- `nilai/AkhirSemesterPage.vue`
- `wali-kelas/RaporPage.vue`
- `rapor-sts/RaporSTSPage.vue`

**Tabel:** `nilai`, `nilai_detail`, `nilai_siswa`, `rapor_sts`, `pengaturan_rapor`

### 8.3 RPP / Modul Ajar (10 method)

| Method | Endpoint | Deskripsi |
|---|---|---|
| GET | `/api/v1/guru/rpp` | List RPP per penugasan |
| POST | `/api/v1/guru/rpp` | Simpan RPP |
| PUT | `/api/v1/guru/rpp/:id` | Edit RPP |
| DELETE | `/api/v1/guru/rpp/:id` | Hapus RPP |
| POST | `/api/v1/guru/rpp/:id/submit` | Submit RPP untuk review |
| GET | `/api/v1/guru/rpp/:id/pdf` | Download RPP PDF |
| GET | `/api/v1/admin/rpp/review` | List RPP pending review |
| POST | `/api/v1/admin/rpp/:id/approve` | Approve RPP |
| POST | `/api/v1/admin/rpp/:id/revision` | Minta revisi |

**Vue pages (4):**
- `guru/RppListPage.vue`
- `guru/RppFormPage.vue`
- `guru/RppDetailPage.vue`
- `admin/RppReviewPage.vue`

**Tabel:** `rpp`, `rpp_data`, `rpp_template_section`, `rpp_template_field`

### 8.4 Izin Siswa (5+5 method)

| Method | Endpoint | Deskripsi |
|---|---|---|
| GET | `/api/v1/izin-siswa` | List izin (admin/wali kelas) |
| POST | `/api/v1/izin-siswa` | Tambah izin |
| PUT | `/api/v1/izin-siswa/:id` | Edit izin |
| POST | `/api/v1/izin-siswa/:id/cancel` | Batalkan izin |
| GET | `/api/v1/izin-siswa/:id` | Detail izin |

**Vue pages (2):**
- `IzinSiswaPage.vue`
- `IzinSiswaFormPage.vue`

**Tabel:** `izin_siswa`

### 8.5 Checklist Fase 3

- [ ] GORM models: jurnal, absensi, nilai, nilai_detail, nilai_siswa, rapor_sts, rpp, rpp_data, rpp_template_*, izin_siswa
- [ ] Handler: Jurnal + Absensi (20 endpoint)
- [ ] Handler: Nilai + Rapor (16 endpoint)
- [ ] Handler: RPP (9 endpoint)
- [ ] Handler: Izin Siswa (5 endpoint)
- [ ] PDF generator: rapor, absensi, nilai, RPP
- [ ] WA notification: kirim notifikasi absensi ke ortu
- [ ] Vue: 20 pages
- [ ] Test: flow jurnal → absensi → notifikasi WA
- [ ] Test: flow nilai → rapor PDF

---

## 9. Fase 4 — Modul Berat (Minggu 13-17)

### 9.1 Wali Kelas (58 method → ~35 endpoint)

Modul terbesar. Mencakup: monitoring absensi, daftar siswa, dokumen, monitoring nilai, pengaturan rapor, cetak rapor, pembayaran kelas, tagihan, transaksi, invoice, SKSA, izin, quick pay.

| Grup | Endpoint | Count |
|---|---|---|
| Monitoring Absensi | GET rekap, detail, harian, export PDF | 6 |
| Daftar Siswa | GET list, edit, dokumen, upload | 5 |
| Monitoring Nilai | GET data nilai, per siswa | 3 |
| Rapor | GET/POST pengaturan, generate, cetak | 5 |
| Pembayaran | CRUD tagihan, transaksi, invoice, thermal, diskon | 12 |
| SKSA | GET cetak | 1 |
| Izin | CRUD izin siswa | 3 |

**Vue pages (12):**
- `wali-kelas/MonitoringAbsensiPage.vue`
- `wali-kelas/AbsensiHarianPage.vue`
- `wali-kelas/DaftarSiswaPage.vue`
- `wali-kelas/DokumenSiswaPage.vue`
- `wali-kelas/MonitoringNilaiPage.vue`
- `wali-kelas/PengaturanRaporPage.vue`
- `wali-kelas/CetakRaporPage.vue`
- `wali-kelas/PembayaranPage.vue`
- `wali-kelas/TagihanPage.vue`
- `wali-kelas/TransaksiPage.vue`
- `wali-kelas/InvoicePage.vue`
- `wali-kelas/IzinSiswaPage.vue`

### 9.2 Pembayaran / Bendahara (29 method → ~20 endpoint)

Banyak overlap dengan WaliKelas. Reuse service layer.

| Method | Endpoint | Deskripsi |
|---|---|---|
| GET | `/api/v1/bendahara/pembayaran` | Overview per kelas |
| GET | `/api/v1/bendahara/pembayaran/:kelas` | Detail per kelas |
| POST | `/api/v1/bendahara/tagihan` | Buat tagihan |
| DELETE | `/api/v1/bendahara/tagihan/:id` | Hapus tagihan |
| GET | `/api/v1/bendahara/tagihan/:id` | Detail tagihan |
| POST | `/api/v1/bendahara/pembayaran/bayar` | Proses pembayaran |
| POST | `/api/v1/bendahara/pembayaran/lunas` | Tandai lunas |
| DELETE | `/api/v1/bendahara/transaksi/:id` | Hapus transaksi |
| GET | `/api/v1/bendahara/riwayat` | Riwayat transaksi |
| GET | `/api/v1/bendahara/export/:kelas` | Export data |
| GET | `/api/v1/bendahara/invoice/:tag/:siswa` | Invoice PDF |
| GET | `/api/v1/bendahara/thermal/:tag/:siswa` | Data struk thermal |
| PUT | `/api/v1/bendahara/diskon` | Update diskon |
| GET | `/api/v1/bendahara/rekap-tagihan/:kelas` | Rekap tagihan PDF |

**Vue pages (6):**
- `bendahara/PembayaranPage.vue`
- `bendahara/KelasDetailPage.vue`
- `bendahara/TagihanPage.vue`
- `bendahara/TransaksiPage.vue`
- `bendahara/RiwayatPage.vue`
- `bendahara/InvoicePage.vue`

**Tabel:** `pembayaran_tagihan`, `pembayaran_tagihan_siswa`, `pembayaran_transaksi`, `pembayaran_kategori`, `jenis_pembayaran`

### 9.3 PSB — Penerimaan Siswa Baru (56 method → ~35 endpoint)

Modul paling kompleks. Punya auth system sendiri (psb_akun), multi-step form, upload dokumen, verifikasi, konversi ke siswa.

**Admin endpoints (~20):**

| Method | Endpoint | Deskripsi |
|---|---|---|
| CRUD | `/api/v1/psb/admin/lembaga` | CRUD lembaga PSB |
| CRUD | `/api/v1/psb/admin/jalur` | CRUD jalur pendaftaran |
| CRUD | `/api/v1/psb/admin/periode` | CRUD periode |
| GET | `/api/v1/psb/admin/dashboard` | Dashboard PSB |
| GET | `/api/v1/psb/admin/pendaftar` | List pendaftar |
| GET | `/api/v1/psb/admin/pendaftar/:id` | Detail pendaftar |
| PUT | `/api/v1/psb/admin/pendaftar/:id/status` | Update status |
| POST | `/api/v1/psb/admin/pendaftar/:id/konversi` | Konversi ke siswa |
| CRUD | `/api/v1/psb/admin/akun` | Kelola akun pendaftar |
| GET/PUT | `/api/v1/psb/admin/pengaturan` | Pengaturan PSB |

**Public endpoints (~15):**

| Method | Endpoint | Deskripsi |
|---|---|---|
| GET | `/api/v1/psb/info` | Landing page data |
| POST | `/api/v1/psb/register` | Daftar akun |
| POST | `/api/v1/psb/login` | Login pendaftar |
| POST | `/api/v1/psb/logout` | Logout |
| POST | `/api/v1/psb/lupa-password` | Reset password |
| GET | `/api/v1/psb/dashboard` | Dashboard pendaftar |
| GET | `/api/v1/psb/periode/:id/jalur` | Pilih jalur |
| POST | `/api/v1/psb/mulai-pendaftaran` | Mulai pendaftaran |
| GET/PUT | `/api/v1/psb/formulir/:id/:section` | Isi formulir per section |
| POST | `/api/v1/psb/formulir/:id/dokumen` | Upload dokumen |
| POST | `/api/v1/psb/formulir/:id/kirim` | Kirim pendaftaran |
| GET | `/api/v1/psb/formulir/:id/cetak` | Cetak formulir PDF |
| GET | `/api/v1/psb/cek-status` | Cek status pendaftaran |
| GET | `/api/v1/psb/cari-npsn/:npsn` | Cari sekolah asal |

**Vue pages (15):**
- `psb/LandingPage.vue`
- `psb/RegisterPage.vue`
- `psb/LoginPage.vue`
- `psb/DashboardPendaftarPage.vue`
- `psb/PilihJalurPage.vue`
- `psb/FormulirPage.vue` (multi-step)
- `psb/UploadDokumenPage.vue`
- `psb/DetailPendaftaranPage.vue`
- `psb/CekStatusPage.vue`
- `psb/admin/DashboardPage.vue`
- `psb/admin/LembagaPage.vue`
- `psb/admin/JalurPage.vue`
- `psb/admin/PeriodePage.vue`
- `psb/admin/PendaftarPage.vue`
- `psb/admin/PengaturanPage.vue`

**Tabel:** `psb_akun`, `psb_pendaftar` (98 kolom!), `psb_dokumen`, `psb_jalur`, `psb_kuota_jalur`, `psb_lembaga`, `psb_pembayaran`, `psb_pengaturan`, `psb_periode`, `psb_settings`

### 9.4 Checklist Fase 4

- [ ] GORM models: semua tabel pembayaran (5), semua tabel PSB (10)
- [ ] Service: PembayaranService (shared wali kelas + bendahara + admin)
- [ ] Service: PSBService (admin + public)
- [ ] Handler: Wali Kelas (35 endpoint)
- [ ] Handler: Bendahara (20 endpoint)
- [ ] Handler: PSB Admin (20 endpoint)
- [ ] Handler: PSB Public (15 endpoint)
- [ ] PDF: invoice, rekap tagihan, bukti pendaftaran, formulir PSB, rapor
- [ ] Vue: 33 pages
- [ ] Test: flow PSB end-to-end (daftar → isi form → upload → kirim → verifikasi → konversi)
- [ ] Test: flow pembayaran (buat tagihan → bayar → cetak invoice)

---

## 10. Fase 5 — Modul Publik & Integrasi (Minggu 18-20)

### 10.1 Pengaturan Admin (67 method → ~40 endpoint)

Modul terbesar dari sisi jumlah method. Mencakup: QR config, menu, Google Drive, role, profil, monitoring nilai, RPP settings, rapor settings, aplikasi settings, dokumen config, WA gateway, antrian WA, login history, field siswa, notifikasi absensi, grup WA kelas, storage R2.

**Grup endpoint:**

| Grup | Endpoint Count |
|---|---|
| Pengaturan Aplikasi | 4 |
| Pengaturan Sistem | 2 |
| Pengaturan Menu | 2 |
| Pengaturan Role | 2 |
| Pengaturan QR | 3 |
| Pengaturan RPP | 6 |
| Pengaturan Rapor | 2 |
| Pengaturan Dokumen | 3 |
| Pengaturan Field Siswa | 2 |
| Pengaturan Storage (R2) | 3 |
| Google Drive | 3 |
| WA Gateway | 8 |
| Antrian WA | 7 |
| Notifikasi Absensi | 4 |
| Grup WA Kelas | 5 |
| Monitoring Nilai | 3 |
| Login History | 1 |
| Cache | 2 |

**Vue pages (10):**
- `admin/PengaturanAplikasiPage.vue`
- `admin/PengaturanSistemPage.vue`
- `admin/PengaturanMenuPage.vue`
- `admin/PengaturanRolePage.vue`
- `admin/PengaturanRppPage.vue`
- `admin/PengaturanRaporPage.vue`
- `admin/WaGatewayPage.vue`
- `admin/AntrianWaPage.vue`
- `admin/NotifikasiAbsensiPage.vue`
- `admin/LoginHistoryPage.vue`

### 10.2 CMS (32 method → ~20 endpoint)

| Grup | Endpoint |
|---|---|
| Settings | GET/PUT |
| Sliders | CRUD + toggle (6) |
| Popups | CRUD + toggle (6) |
| Menus | CRUD (4) |
| Posts | CRUD + toggle (6) |
| Institutions | CRUD + toggle (5) |

**Vue pages (6):**
- `cms/DashboardPage.vue`
- `cms/SlidersPage.vue`
- `cms/PopupsPage.vue`
- `cms/MenusPage.vue`
- `cms/PostsPage.vue`
- `cms/InstitutionsPage.vue`

### 10.3 Jadwal (28 method → ~18 endpoint)

| Grup | Endpoint |
|---|---|
| Pengaturan | GET/PUT |
| Jam Pelajaran | CRUD + auto-generate (5) |
| Guru-Mapel | CRUD (3) |
| Jadwal | CRUD + view per kelas/guru (8) |
| Istirahat | CRUD (3) |
| Cetak | GET PDF |

**Vue pages (5):**
- `jadwal/DashboardPage.vue`
- `jadwal/JamPelajaranPage.vue`
- `jadwal/KelolaJadwalPage.vue`
- `jadwal/LihatJadwalPage.vue`
- `jadwal/CetakJadwalPage.vue`

### 10.4 Laporan Admin (11 method → ~8 endpoint)

| Method | Endpoint | Deskripsi |
|---|---|---|
| GET | `/api/v1/admin/laporan/riwayat-per-mapel` | Riwayat per mapel |
| GET | `/api/v1/admin/laporan/detail/:guru/:mapel` | Detail per guru+mapel |
| GET | `/api/v1/admin/laporan/rincian-absen/:mapel` | Rincian absensi |
| GET | `/api/v1/admin/laporan/cetak/mapel/:combo` | PDF per mapel |
| GET | `/api/v1/admin/laporan/cetak/rincian/:combo` | PDF rincian |
| GET | `/api/v1/admin/laporan/cetak/rekap` | PDF rekap keseluruhan |

**Vue pages (3):**
- `admin/LaporanRiwayatPage.vue`
- `admin/LaporanDetailPage.vue`
- `admin/LaporanRincianPage.vue`

### 10.5 Modul Kecil Lainnya

| Modul | Endpoint | Vue Pages |
|---|---|---|
| Buku Tamu (13 method) | ~10 | 3 (Dashboard, Lembaga, GenerateLink) |
| Surat Tugas (12 method) | ~8 | 3 (Lembaga, List, Form) |
| Surat Penerimaan (7 method) | ~5 | 2 (List, Form) |
| Performa Guru (10 method) | ~7 | 2 (Dashboard, Detail) |
| Performa Siswa (6 method) | ~4 | 2 (Dashboard, Detail) |
| Riwayat Jurnal (6 method) | ~5 | 2 (List, Detail) |
| Pesan (6 method per role) | ~5 | 3 (Inbox, Compose, Detail) |
| Kepala Madrasah (15 method) | ~10 | 3 (Dashboard, Riwayat, Detail) |
| Siswa Portal (17 method) | ~12 | 6 (Dashboard, Absensi, Pembayaran, Dokumen, Profil, Pesan) |
| News (2 method) | ~2 | 2 (List, Detail) |
| Tamu Public (2 method) | ~2 | 1 (Form) |
| QR Validation (2 method) | ~2 | 1 (ValidatePage) |
| Home/Landing (1 method) | ~1 | 1 (HomePage) |

### 10.6 Checklist Fase 5

- [ ] Handler: Pengaturan Admin (40 endpoint)
- [ ] Handler: CMS (20 endpoint)
- [ ] Handler: Jadwal (18 endpoint)
- [ ] Handler: Laporan (8 endpoint)
- [ ] Handler: Buku Tamu, Surat, Performa, Pesan, dll (~50 endpoint)
- [ ] Handler: Portal Siswa (12 endpoint)
- [ ] Handler: Portal Kepala Madrasah (10 endpoint)
- [ ] Handler: Public pages (Home, News, Tamu, QR)
- [ ] Go: Fonnte/WA client (6 provider)
- [ ] Go: Google Drive client
- [ ] Go: FCM client
- [ ] Go: Cron scheduler (ganti cron_wa_processor.php)
- [ ] Vue: ~42 pages
- [ ] Test: WA notification flow
- [ ] Test: Google Drive upload
- [ ] Test: push notification

---

## 11. Fase 6 — Polish & Cutover (Minggu 21-22)

### 11.1 Checklist Polish

- [ ] Responsive design audit (semua page mobile-friendly)
- [ ] Loading states & skeleton screens
- [ ] Error handling & error boundaries
- [ ] Empty states (no data illustrations)
- [ ] Keyboard shortcuts (Escape close modal, etc.)
- [ ] PWA: service worker, offline page, manifest
- [ ] SEO: meta tags untuk halaman publik (Home, News, PSB)
- [ ] Performance: lazy loading routes, code splitting
- [ ] Performance: API response caching (stale-while-revalidate)
- [ ] Security audit: input validation, SQL injection, XSS
- [ ] Rate limiting pada login & API
- [ ] Logging & monitoring (structured logs)

### 11.2 Checklist Cutover

- [ ] Data migration script (jika ada perubahan schema)
- [ ] DNS cutover plan: `sabilillah.id` → Vue SPA
- [ ] Nginx config baru (SPA fallback + API proxy)
- [ ] SSL certificate
- [ ] Backup database sebelum cutover
- [ ] Rollback plan (switch nginx config kembali ke PHP)
- [ ] Monitoring 24 jam pertama
- [ ] Redirect old URLs ke new URLs (jika berubah)
- [ ] Update mobile app API base URL
- [ ] Notify users tentang maintenance window

### 11.3 Post-Cutover

- [ ] Monitor error logs 1 minggu
- [ ] Fix bugs yang muncul
- [ ] Decommission PHP app (tapi simpan backup)
- [ ] Update dokumentasi
- [ ] Archive repo PHP lama

---

## 12. Peta Lengkap Route → Go Handler

### Total: ~300 API endpoint (dari ~580 PHP method, banyak yang merge)

| Fase | Modul | Endpoint Count |
|---|---|---|
| 1 | Auth & Dashboard | ~20 |
| 2 | Siswa + Guru + Akademik | ~47 |
| 3 | Jurnal/Absensi + Nilai/Rapor + RPP + Izin | ~50 |
| 4 | Wali Kelas + Bendahara + PSB | ~90 |
| 5 | Pengaturan + CMS + Jadwal + Laporan + Lainnya | ~100 |
| **Total** | | **~307** |

---

## 13. Peta Lengkap Tabel → GORM Model

### 80 tabel → 80 GORM struct

| Grup | Tabel | Kolom Total |
|---|---|---|
| **Users & Auth** | `users` (10), `login_history` (10), `user_devices` (8) | 28 |
| **Siswa** | `siswa` (69), `siswa_dokumen` (11), `sksa_nomor` (8) | 88 |
| **Guru** | `guru` (6), `guru_fungsi` (7), `guru_mapel` (4) | 17 |
| **Akademik** | `tp` (4), `semester` (7), `kelas` (7), `mapel` (4), `penugasan` (7), `keanggotaan_kelas` (4) | 33 |
| **Jurnal & Absensi** | `jurnal` (7), `absensi` (8), `izin_siswa` (13) | 28 |
| **Nilai & Rapor** | `nilai` (8), `nilai_detail` (8), `nilai_siswa` (11), `rapor_sts` (12), `pengaturan_rapor` (27) | 66 |
| **Jadwal** | `jadwal_pelajaran` (10), `jam_pelajaran` (7), `jadwal_istirahat` (6), `kebutuhan_jam_mapel` (6), `ruangan` (5) | 34 |
| **Pembayaran** | `pembayaran_tagihan` (15), `pembayaran_tagihan_siswa` (13), `pembayaran_transaksi` (10), `pembayaran_kategori` (5), `jenis_pembayaran` (5) | 48 |
| **PSB** | `psb_akun` (11), `psb_pendaftar` (98), `psb_dokumen` (9), `psb_jalur` (8), `psb_kuota_jalur` (5), `psb_lembaga` (16), `psb_pembayaran` (9), `psb_pengaturan` (19), `psb_periode` (11), `psb_settings` (4) | 190 |
| **WhatsApp** | `wa_accounts` (14), `wa_message_queue` (14), `wa_message_log` (7), `kelas_grup_wa` (7) | 42 |
| **CMS** | `cms_settings` (5), `cms_sliders` (9), `cms_popups` (10), `cms_menus` (10), `cms_posts` (12), `cms_institutions` (14) | 60 |
| **Surat** | `surat_tugas` (12), `surat_tugas_lembaga` (13), `surat_tugas_petugas` (9), `surat_penerimaan` (19) | 53 |
| **Buku Tamu** | `buku_tamu` (15), `buku_tamu_lembaga` (5), `buku_tamu_link` (10) | 30 |
| **RPP** | `rpp` (37), `rpp_data` (6), `rpp_template_section` (7), `rpp_template_field` (11) | 61 |
| **Pesan** | `pesan` (9), `pesan_penerima` (6) | 15 |
| **Pengaturan** | `pengaturan_aplikasi` (26), `pengaturan_sistem` (6), `pengaturan_menu` (4), `pengaturan_dokumen` (10), `pengaturan_jadwal` (5), `pengaturan_rpp` (8), `app_settings` (5) | 64 |
| **QR** | `qr_config` (10), `qr_validation_tokens` (9), `qr_validation_logs` (7) | 26 |
| **Visitor** | `visitor_stats` (5), `website_visitors` (9) | 14 |
| **Wali Kelas** | `wali_kelas` (5) | 5 |
| **Migration** | `schema_migrations` (4) | 4 |

---

## 14. Peta Lengkap View → Vue Component

### 255 PHP view → ~150 Vue page + ~40 shared component

| Folder PHP | View Count | Vue Pages | Vue Shared Components |
|---|---|---|---|
| `admin/` | 103 | ~45 | DataTable, Modal, Sidebar, StatsCard |
| `guru/` | 31 | ~12 | JurnalCard, AbsensiGrid |
| `wali_kelas/` | 24 | ~12 | RaporTemplate, InvoiceTemplate |
| `psb/` | 19 | ~15 | FormStep, DokumenUpload |
| `kepala_madrasah/` | 16 | ~5 | — |
| `siswa/` | 12 | ~6 | — |
| `templates/` | 10 | ~5 (layouts) | AppLayout, AuthLayout |
| `jadwal/` | 9 | ~5 | ScheduleGrid, TimeSlotPicker |
| `nilai/` | 7 | ~4 | NilaiInputGrid |
| `tamu/` | 4 | ~2 | — |
| `surat_tugas/` | 4 | ~3 | — |
| `persuratan/` | — | ~2 | PersuratanDashboard (unified sidebar: Surat Tugas + Surat Penerimaan) |
| `bendahara/` | 3 | ~6 | PaymentCard, ThermalReceipt |
| `lainnya` | 13 | ~8 | — |
| **Shared components** | — | — | ~40 |
| **Total** | **255** | **~150** | **~40** |

---

## 15. Integrasi Eksternal

### 15.1 WhatsApp Gateway (Fonnte.php → Go pkg/fonnte/)

| Provider | Method | Auth |
|---|---|---|
| Fonnte (SaaS) | POST `https://api.fonnte.com/send` | API key header |
| Go-WhatsApp-Web (self-hosted) | POST `http://host/send/message` | Basic auth |
| Wablas | POST `https://pati.wablas.com/api/send-message` | Token header |
| Dripsender | POST `https://api.drfrip.com/send` | API key |
| Starsender | POST `https://api.starsender.online/api/send` | API key |
| OneSender/CloudWA | POST `https://api.onesender.id/api/v1/send-message` | Bearer token |

**Go implementation:** Buat interface `WAProvider` dengan method `Send(phone, message)`, lalu 6 implementasi concrete. Factory pattern berdasarkan config.

### 15.2 Google Drive (GoogleDrive.php → Go pkg/gdrive/)

- OAuth2 flow: consent URL → code exchange → refresh token
- Upload file (multipart)
- Create folder
- Set public permission
- Delete file
- Get user info

### 15.3 Cloudflare R2 (R2Storage.php → Go pkg/r2/)

- Upload (PutObject)
- Delete (DeleteObject)
- Get public URL
- Image processing: crop 3:4 + resize (pakai `imaging` library)

### 15.4 Firebase FCM (FCM.php → Go pkg/fcm/)

- Service account JWT auth (RS256)
- Send to device
- Send to multiple devices
- Send to topic

### 15.5 Google OAuth (AuthController → Go handler)

- Redirect ke Google consent
- Handle callback
- Match email ke user

### 15.6 License Server

- GET `https://lisensi.sahil.my.id/api/check?domain=...`
- Cache 24 jam
- Bisa di-embed di Go middleware

---

## 16. PDF Generation

### PHP (Dompdf) → Go (wkhtmltopdf atau chromedp)

| Dokumen | Controller Asal | Template Complexity |
|---|---|---|
| Rapor STS (mid-semester) | WaliKelasController, RaporSTSController | Tinggi (tabel nilai, grafik, QR) |
| Rapor full (end-semester) | WaliKelasController | Sangat Tinggi (multi-page, cover, tabel) |
| Rekap absensi kelas | WaliKelasController | Sedang (tabel landscape) |
| Rekap pembayaran | BendaharaController | Sedang (tabel) |
| Kwitansi/Invoice | BendaharaController, WaliKelasController | Rendah (1 halaman) |
| Surat tugas | SuratTugasController | Sedang (kop surat, tabel petugas) |
| Surat penerimaan | SuratPenerimaanController | Sedang (kop surat) |
| Bukti pendaftaran PSB | PsbController | Sedang (data pendaftar) |
| Formulir PSB | PsbController | Tinggi (multi-section, foto) |
| Kartu login siswa | AdminSiswaTrait | Rendah (kartu kecil, batch) |
| Nilai harian/STS/SAS | NilaiController | Sedang (tabel) |
| Jurnal mengajar | RiwayatJurnalController | Sedang (tabel + QR) |
| Absensi per jurnal | RiwayatJurnalController | Sedang (tabel + QR) |
| Buku tamu | BukuTamuController | Rendah (tabel) |
| Performa guru | PerformaGuruController | Sedang (tabel + grafik) |
| SKSA | WaliKelasController, SiswaController | Rendah (1 halaman, QR) |

**Strategi Go:**
1. Buat HTML template (Go `html/template`) untuk setiap jenis dokumen
2. Render HTML → PDF via `wkhtmltopdf` (CLI) atau `chromedp` (headless Chrome)
3. QR code: generate in-memory via `go-qrcode`, embed sebagai base64 `<img>`

---

## 17. Core Utilities — Peta Migrasi PHP → Go

Selain integrasi eksternal (§15), ada 12 file core utility yang harus di-port atau diganti:

| PHP File | Baris | Go Equivalent | Strategi |
|---|---|---|---|
| `App.php` (Router) | 377 | `gin.Engine` + route groups | Seluruh routing convention-based diganti explicit route registration. Public route whitelist di-port ke middleware. |
| `Controller.php` (Base) | 52 | Tidak perlu | Go handler langsung return JSON. View rendering hilang (SPA). |
| `Database.php` (PDO) | 128 | `gorm.io/gorm` | Prepared statement patterns di-port ke GORM query builder. Transaction pattern (`beginTransaction/commit/rollBack`) → `db.Transaction()`. |
| `Session.php` | 63 | JWT claims | Legacy key normalization (`nama_lengkap` vs `user_nama_lengkap`) → unified JWT claims struct. Tidak perlu `normalize()` lagi. |
| `Csrf.php` | 188 | Tidak perlu | JWT stateless = no CSRF. File ini di-decommission. |
| `Flasher.php` | 76 | Sonner (frontend) | Flash message pattern → Vue toast via Sonner. Backend return error/success di JSON response. |
| `InputValidator.php` | 156 | `validator/v10` + custom | Port custom rules: `validateNISN()` (10 digit), `validatePhone()`, `sanitizeLike()` → custom validator tags. |
| `PasswordGenerator.php` | 162 | Go equivalent | Port password generation logic (Google Workspace-compliant format). |
| `PasswordMask.php` | 69 | Go equivalent | Port masking logic untuk `password_plain` display. |
| `PDFQRHelper.php` | 114 | `pkg/pdf/qr.go` | Port QR injection: generate QR in-memory (`go-qrcode`), embed base64 ke HTML template sebelum PDF render. Token generation (SHA-256 + HMAC) di-port ke Go. |
| `Migrator.php` | 173 | `golang-migrate/migrate` | Existing `schema_migrations` table tetap dipakai. Backfill logic di-port. Baseline: dump schema dari DB existing, bukan dari `schema_export.sql`. |
| `Updater.php` | 438 | **Dihapus** | Auto-update dari GitHub tidak relevan untuk SPA + Go binary. Deployment pakai CI/CD atau manual build. Backup strategy tetap dipertahankan di level ops. |

### Keputusan Arsitektur

- **`password_plain` column**: Tetap dipertahankan di DB (dibutuhkan untuk cetak kartu login siswa). Go handler yang serve data ini harus di-protect ketat (admin-only, audit log).
- **Session normalization**: JWT claims struct harus mengakomodasi semua field yang sebelumnya di-normalize: `user_id`, `role`, `id_ref`, `nama_lengkap`, `email`, `id_semester`, `id_tp`.
- **Auto-update**: Diganti dengan versioned deployment. `version.json` tetap dipakai untuk display versi di frontend.

---

## 18. Existing Mobile API — Backward Compatibility

Sistem PHP saat ini punya API layer terpisah di `api/` yang melayani mobile app. **Harus tetap kompatibel selama transisi.**

### Inventory API PHP Existing

| File | Baris | Endpoint |
|---|---|---|
| `api/index.php` | ~80 | Router + CORS + JWT auth |
| `api/helpers/Auth.php` | ~50 | JWT HS256 (secret dari `pengaturan_sistem`, fallback hardcoded) |
| `api/helpers/Response.php` | ~30 | JSON response helper |
| `api/controllers/AuthController.php` | 287 | `POST /api/auth/login`, `GET /api/auth/me`, `POST /api/auth/logout`, `POST /api/auth/refresh`, `POST /api/auth/google` |
| `api/controllers/GuruController.php` | 421 | `GET /api/guru/dashboard\|jurnal\|kelas\|mapel\|jadwal\|absensi`, `POST /api/guru/jurnal\|absensi` |
| `api/controllers/SiswaController.php` | 292 | `GET /api/siswa/dashboard\|absensi\|rekap\|pembayaran\|profil`, `PUT /api/siswa/profil` |
| `api/controllers/WaliKelasController.php` | 306 | `GET /api/waliKelas/dashboard\|siswa\|absensi\|nilai\|pembayaran` |
| `api/controllers/NotificationController.php` | 110 | `POST /api/notifications/register\|unregister` |

**Total: ~1.416 baris, ~25 endpoint.**

### Strategi Backward Compatibility

```
Fase 1-5:  Mobile app tetap hit /api/* (PHP)
           Go API di /api/v1/* (baru, web SPA)

Fase 6:    Go API serve KEDUA path:
           /api/*    → legacy compat (response format sama persis)
           /api/v1/* → new format
           
Post-cutover: Update mobile app → /api/v1/*
              Deprecate /api/* setelah semua user update app
```

### Yang Harus Di-port Persis

1. **JWT secret**: Baca dari `pengaturan_sistem` table, key `jwt_secret`. Fallback ke hardcoded string yang sama.
2. **Response format**: `{"status": "success", "data": {...}}` dan `{"status": "error", "message": "..."}` — harus identik.
3. **Auth flow**: `POST /api/auth/login` menerima `username` + `password`, return `{token, user}`. Token expiry 24 jam.
4. **camelCase routing**: Mobile app pakai `/api/waliKelas/` (camelCase), bukan `/api/wali-kelas/`.

---

## 19. QR Validation System

Sistem QR terdiri dari 5 komponen yang saling terkait. Plan sebelumnya hanya cover QR *generation* — berikut peta lengkap:

### Komponen

| # | File PHP | Baris | Fungsi |
|---|---|---|---|
| 1 | `config/qrcode.php` | ~130 | Config provider QR (qrserver.com default), `generateQRToken()` (SHA-256 + salt + timestamp), `generatePDFQRCode()` (URL ke QR image API) |
| 2 | `app/core/PDFQRHelper.php` | 114 | Inject QR ke semua PDF: generate token → simpan ke DB → buat QR image URL → inject HTML `<img>` ke PDF template |
| 3 | `app/models/QRValidation_model.php` | 102 | CRUD token di `qr_validation_tokens`, log validasi di `qr_validation_logs`, cek expiry (365 hari default) |
| 4 | `app/controllers/ValidateController.php` | ~30 | Public page: scan QR → lookup token → tampilkan info dokumen |
| 5 | `app/controllers/ValidasiRaporController.php` | 113 | Validasi khusus rapor: lookup token → tampilkan data rapor + nilai siswa |

### Tabel

| Tabel | Kolom | Fungsi |
|---|---|---|
| `qr_config` | 10 | Provider config, salt, ukuran, expiry |
| `qr_validation_tokens` | 9 | Token storage: `token`, `doc_type`, `doc_id`, `created_by`, `expires_at` |
| `qr_validation_logs` | 7 | Log setiap kali QR di-scan: `token`, `ip`, `user_agent`, `scanned_at` |

### Go Implementation Plan

```go
// pkg/qrcode/
├── generator.go    // Generate QR image in-memory (skip2/go-qrcode)
├── token.go        // GenerateToken(docType, docID, userID) → SHA-256 + HMAC
├── validator.go    // ValidateToken(token) → (docInfo, error)
└── pdf_helper.go   // InjectQRToPDF(htmlTemplate, token) → html with QR <img>

// handlers/
├── validate_handler.go      // GET /validate/:token → public validation page
└── validasi_rapor_handler.go // GET /validasi-rapor/:token → rapor validation
```

---

## 20. Config & Settings Migration

### Config Files PHP → Go

| PHP File | Baris | Go Equivalent |
|---|---|---|
| `config/config.php` | ~80 | `config.yaml` + `internal/config/config.go` (Viper) |
| `config/database.php` | ~20 | `config.yaml` section `database:` |
| `config/config_hosting.php` | ~30 | `config.yaml` (production override via env vars) |
| `config/qrcode.php` | ~130 | `config.yaml` section `qrcode:` + `pkg/qrcode/` |

### Dynamic Settings (DB-based)

PHP saat ini menyimpan banyak config di database, bukan file:

| Tabel | Kolom | Contoh Setting |
|---|---|---|
| `pengaturan_sistem` | 6 | `SECRET_KEY`, `QR_ENABLED`, `GOOGLE_*`, `R2_*`, `JWT_SECRET`, `MENU_*_ENABLED` |
| `pengaturan_aplikasi` | 26 | Nama madrasah, logo, alamat, NIS, NPSN, kepala madrasah, WA gateway config |
| `pengaturan_menu` | 4 | Toggle menu per role |
| `pengaturan_dokumen` | 10 | Jenis dokumen siswa yang wajib |
| `pengaturan_jadwal` | 5 | Hari aktif, jam mulai |
| `pengaturan_rpp` | 8 | Template RPP, field wajib |
| `pengaturan_rapor` | 27 | Format rapor, KKM, predikat, per TP+guru |
| `app_settings` | 5 | FCM credentials (project_id, client_email, private_key) |
| `psb_pengaturan` | 19 | WA gateway PSB, template pesan, alur pendaftaran |

### Strategi Go

```go
// internal/config/
├── config.go       // Static config dari config.yaml (Viper)
└── settings.go     // Dynamic settings dari DB (cached 5 menit, invalidate on update)

// Settings service: load dari DB, cache di memory, expose via API
type SettingsService struct {
    cache    map[string]string
    cacheTTL time.Duration  // 5 menit, sama seperti PHP
    mu       sync.RWMutex
}
```

### Config YAML Template

```yaml
server:
  port: 8090
  mode: release  # debug | release

database:
  host: localhost
  port: 3306
  name: sabilillah_id
  user: sabilillah_id
  password: "${DB_PASSWORD}"  # dari env var

jwt:
  access_ttl: 15m
  refresh_ttl: 7d
  # secret dibaca dari pengaturan_sistem table

cors:
  origins:
    - https://sabilillah.id
    - https://app.sabilillah.id

storage:
  type: r2  # local | r2
  # R2 credentials dibaca dari pengaturan_sistem table

qrcode:
  provider: go-qrcode  # in-memory, tidak perlu external API
  salt: "${QR_SALT}"
  expiry_days: 365
  size: 200

license:
  enabled: true
  server_url: https://lisensi.sahil.my.id/api/check
  cache_ttl: 86400
```

---

## 21. Static Assets & File Storage

### Assets yang Harus Di-migrate

| Path PHP | Isi | Strategi Go/Vue |
|---|---|---|
| `public/img/kop/` | Gambar kop surat (header PDF) | Copy ke Go `static/img/kop/`, embed di PDF template |
| `public/img/ttd/` | Gambar tanda tangan (PDF surat) | Copy ke Go `static/img/ttd/` |
| `public/img/app/` | Logo aplikasi, favicon, PWA icons | Copy ke Vue `public/img/` |
| `public/img/cms/` | Gambar upload CMS (slider, post) | Pindah ke R2 atau tetap serve dari Go static |
| `public/css/style.css` | Custom CSS (saat ini kosong) | Tidak perlu — Tailwind di Vue |
| `public/js/main.js` | Custom JS (saat ini kosong) | Tidak perlu — Vue components |
| `public/robots.txt` | Disallow rules | Copy ke Vue `public/robots.txt` |
| `public/offline.html` | PWA offline page | Buat ulang di Vue sebagai offline fallback |
| `public/loaderio-*.txt` | Load testing verification | Copy ke Vue `public/` jika masih dipakai |
| `uploads/siswa_dokumen/` | Dokumen siswa (lokal) | Migrate ke R2 atau tetap serve dari Go |

### Upload Storage Strategy

```
Saat ini (PHP):
  Foto siswa    → Cloudflare R2 (via R2Storage.php)
  Dokumen siswa → Local disk (uploads/siswa_dokumen/)
  CMS images    → Local disk (public/img/cms/)
  Surat/PDF     → Generated on-the-fly, tidak disimpan

Target (Go):
  Foto siswa    → Cloudflare R2 (tetap)
  Dokumen siswa → Cloudflare R2 (migrasi dari lokal)
  CMS images    → Cloudflare R2 (migrasi dari lokal)
  Surat/PDF     → Generated on-the-fly (tetap)
```

### Migrasi File Lokal → R2

```bash
# Script migrasi one-time (jalankan sebelum cutover)
# 1. Upload semua dokumen siswa ke R2
# 2. Update path di DB (siswa_dokumen.file_path → R2 URL)
# 3. Upload semua CMS images ke R2
# 4. Update path di DB (cms_posts.image, cms_sliders.image → R2 URL)
```

---

## 22. Alpine.js → Vue 3 Migration Patterns

Frontend PHP saat ini menggunakan **Alpine.js 3.x** (loaded via CDN) + **Tailwind CSS** (CDN) untuk interaktivitas. Berikut peta konversi pattern:

### Pattern Mapping

| Alpine.js Pattern | Contoh di PHP Views | Vue 3 Equivalent |
|---|---|---|
| `x-data="{ open: false }"` | Modal toggle, dropdown | `const open = ref(false)` |
| `x-show="open"` | Conditional display | `v-show="open"` atau `v-if="open"` |
| `x-on:click="open = !open"` | Event handler | `@click="open = !open"` |
| `x-bind:class="{ 'hidden': !open }"` | Dynamic class | `:class="{ 'hidden': !open }"` |
| `x-for="item in items"` | List rendering | `v-for="item in items"` |
| `x-model="search"` | Two-way binding | `v-model="search"` |
| `x-init="fetchData()"` | Lifecycle | `onMounted(() => fetchData())` |
| `x-transition` | Enter/leave animation | `<Transition>` component |
| `x-collapse` (plugin) | Accordion collapse | shadcn `<Accordion>` atau `<Collapsible>` |
| `$refs.modal` | DOM reference | `const modal = ref<HTMLElement>()` |
| `$dispatch('event')` | Custom events | `emit('event')` atau Pinia action |
| `@click.away` | Click outside | `@click.outside` (Vue 3) atau `vOnClickOutside` directive |
| Inline `fetch()` calls | AJAX di onclick | Composable + Axios (`useApi()`) |

### Inline JS yang Harus Di-port

~19.400 baris inline JS tersebar di 255 view files. Mayoritas berupa:

| Kategori | Estimasi Baris | Vue Equivalent |
|---|---|---|
| AJAX fetch calls (CRUD) | ~6.000 | Axios API layer (`src/api/*.ts`) |
| Modal open/close logic | ~3.000 | shadcn `<Dialog>` state |
| Form validation | ~2.500 | VeeValidate + Zod schema |
| DataTable init + search + filter | ~3.000 | TanStack Table + composable |
| Chart.js initialization | ~1.500 | vue-chartjs components |
| File upload + preview | ~1.000 | `<FileUpload>` component |
| Print / PDF trigger | ~800 | Window.open PDF URL |
| Miscellaneous (copy, toggle, etc.) | ~1.600 | Vue composables |

### Strategi Port

Tidak perlu port baris-per-baris. Tulis ulang per halaman Vue:
1. Baca view PHP → identifikasi data flow (apa yang di-fetch, apa yang di-submit)
2. Buat TypeScript interface untuk data
3. Buat API call di `src/api/`
4. Buat Vue page dengan shadcn components
5. Test fungsionalitas sama

---

## 23. Existing Dev Workflow & Versioning

### File yang Harus Dipahami

| File | Fungsi |
|---|---|
| `version.json` | `{"version": "1.27.1", "build": 1271}` — dibaca oleh `Updater.php` dan ditampilkan di footer |
| `changelog.json` | Riwayat perubahan per versi — ditampilkan di halaman update admin |
| `workflows/selesai-coding.md` | Checklist release: bump version → tulis migration SQL → git commit → push → test |
| `superapp.code-workspace` | VS Code workspace config |

### Release Process Saat Ini (PHP)

```
1. Edit kode
2. Tulis migration SQL di migrations/x.y.z.sql
3. Bump version di version.json
4. Update changelog.json
5. Git commit + push ke Sahilul/absen (main branch)
6. Server auto-pull via Updater.php (atau manual)
```

### Release Process Target (Go + Vue)

```
1. Edit kode (Go backend / Vue frontend)
2. Tulis migration SQL di migrations/ (golang-migrate format)
3. Bump version di version.json (tetap dipakai untuk display)
4. Git commit + push
5. Build:
   - Go: go build → binary
   - Vue: pnpm build → dist/
6. Deploy:
   - SCP binary + restart systemd service
   - SCP dist/ ke nginx static dir
   - Run pending migrations
7. Verify: health check endpoint + smoke test
```

### Rekomendasi CI/CD (Opsional)

```yaml
# GitHub Actions (opsional, bisa manual dulu)
on:
  push:
    branches: [main]

jobs:
  build-backend:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: actions/setup-go@v5
      - run: cd backend && go build -o sabilillah-api ./cmd/server
      - # scp ke server + restart service

  build-frontend:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: actions/setup-node@v4
      - run: pnpm install && pnpm build
      - # scp dist/ ke server
```

---

## 24. Risiko & Mitigasi

| Risiko | Dampak | Mitigasi |
|---|---|---|
| **Scope creep** — fitur baru ditambah selama rewrite | Timeline molor | Feature freeze di PHP selama rewrite. Bug fix only. |
| **Data inconsistency** — 2 sistem jalan paralel | Data tidak sinkron | Satu database, Go dan PHP baca/tulis ke DB yang sama |
| **PDF rendering beda** — wkhtmltopdf vs Dompdf | Layout rapor/surat berubah | Test visual side-by-side, adjust CSS |
| **Mobile app break** — API endpoint berubah | App crash | Versioning API (`/api/v1/`), backward compatible |
| **Performance regression** — Vue SPA initial load | Halaman pertama lambat | Code splitting, lazy routes, preload critical chunks |
| **Session migration** — user harus login ulang | User complaint | Announce maintenance window, auto-redirect ke login |
| **WhatsApp gateway** — 6 provider, edge cases | Notifikasi gagal | Port test suite dari PHP, test setiap provider |
| **Solo developer burnout** | Kualitas turun | Strict fase, celebrate milestones, skip nice-to-have |

---

## Ringkasan Estimasi

| Fase | Durasi | Endpoint | Vue Pages | Deliverable |
|---|---|---|---|---|
| 0 — Fondasi | 2 minggu | 0 | 0 | Project setup, GORM models, shared components |
| 1 — Auth & Core | 3 minggu | ~20 | ~12 | Login, dashboard 5 role, profil |
| 2 — Akademik | 4 minggu | ~47 | ~23 | Siswa, guru, TP, kelas, mapel, penugasan |
| 3 — Operasional | 3 minggu | ~50 | ~20 | Jurnal, absensi, nilai, rapor, RPP, izin |
| 4 — Modul Berat | 5 minggu | ~90 | ~33 | Wali kelas, bendahara, PSB |
| 5 — Publik & Integrasi | 3 minggu | ~100 | ~42 | Pengaturan, CMS, jadwal, laporan, WA, dll |
| 6 — Polish & Cutover | 2 minggu | 0 | 0 | Testing, PWA, SEO, DNS cutover |
| **Total** | **22 minggu** | **~307** | **~150** | **Full feature parity** |

---

> **Catatan:** Estimasi ini untuk solo developer full-time yang sudah familiar
> dengan Go dan Vue. Dengan tim 2 orang (1 backend + 1 frontend), bisa
> dipangkas jadi ~12-14 minggu. Dengan tim 3 orang, ~8-10 minggu.
