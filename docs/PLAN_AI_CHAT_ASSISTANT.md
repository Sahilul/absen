# Plan: Integrasi AI Chat Assistant — "Pak Admin"

> **Versi target:** v1.33.0
> **Tanggal:** 2026-10-01
> **Status:** 📋 Perencanaan

---

## 1. Ringkasan (TL;DR)

Menambahkan **floating AI chat widget** bernama **"Pak Admin"** sebagai **asisten pribadi** yang muncul di **semua halaman** untuk **semua role kecuali siswa**.

| Aspek | Keputusan |
|-------|-----------|
| **Nama AI** | **Pak Admin** 🤖 |
| **Label tombol** | **"Tanya Pak Admin"** |
| **Role yang dapat akses** | `admin`, `guru`, `wali_kelas`, `kepala_madrasah`, + fungsi (`bendahara`, `petugas_psb`, `admin_cms`, `petugas_buku_tamu`, `guru_bk`) |
| **Role yang DILARANG** | `siswa` ❌ |
| **Data** | Per-user (riwayat tersimpan di database, terisolasi antar user) |
| **Sesi percakapan** | Tiap percakapan punya sesi sendiri (terfokus). Awal → "Buat Percakapan Baru"; setelahnya → tombol "🕘 Riwayat" untuk melanjutkan |
| **Kesadaran identitas** | Pak Admin **tahu siapa yang sedang chat** (nama, role, jabatan) — di-inject ke system prompt |
| **Backend** | PHP proxy ke **custom OpenAI-compatible endpoint** (base URL + API key + model bisa diatur admin) |
| **Frontend** | Vanilla JS + Tailwind CSS (sesuai stack existing) |
| **Mode respons** | Non-streaming (AJAX biasa) — streaming bisa ditambah nanti |
| **Konfigurasi** | Via tabel `pengaturan_sistem` (enable/disable, base URL, API key, model) + menu admin **"Pengaturan AI Chat"** di sidebar |

**Jawaban singkat: Ya, bisa dilakukan.** Arsitektur app sudah mendukung — ada sistem settings yang mature, auth/session per-role yang jelas, dan layout template yang bisa di-inject widget secara conditional.

---

## 2. Arsitektur

```
┌─────────────────────────────────────────────────────────────┐
│  BROWSER (semua halaman, role != siswa)                     │
│  ┌───────────────────────────────────────────────────────┐  │
│  │  Floating Chat Widget "Pak Admin"                     │  │
│  │  (ai-chat.js + ai-chat.css)                           │  │
│  │  - Tombol "Tanya Pak Admin" (bottom-right)            │  │
│  │  - Chat window (messages, input, conversation list)   │  │
│  └───────────────────────┬───────────────────────────────┘  │
└──────────────────────────┼──────────────────────────────────┘
                           │ AJAX (fetch + CSRF token)
                           ▼
┌─────────────────────────────────────────────────────────────┐
│  PHP BACKEND                                                │
│  ┌───────────────────────────────────────────────────────┐  │
│  │  AiChatController.php                                 │  │
│  │  - Auth guard (block siswa)                           │  │
│  │  - Inject identitas user ke system prompt             │  │
│  │  - Endpoints: conversations, messages, send, delete   │  │
│  └───────────┬───────────────────────────┬───────────────┘  │
│              │                           │                  │
│              ▼                           ▼                  │
│  ┌───────────────────────┐   ┌──────────────────────────┐   │
│  │  AiChat_model.php     │   │  AiProvider.php          │   │
│  │  (conversations +     │   │  (OpenAI-compatible      │   │
│  │   messages, per-user) │   │   client, custom base)   │   │
│  └───────────┬───────────┘   └────────────┬─────────────┘   │
└──────────────┼────────────────────────────┼─────────────────┘
               ▼                            ▼
      ┌─────────────────┐          ┌──────────────────────────┐
      │  MariaDB        │          │  OpenAI-compatible API   │
      │  ai_chat_*      │          │  (base URL custom)       │
      │  pengaturan_*   │          │  OpenAI / OpenRouter /   │
      └─────────────────┘          │  Groq / Ollama / vLLM /  │
                                   │  LM Studio / self-host   │
                                   └──────────────────────────┘
```

---

## 2.1 Di Halaman Mana Bubble Muncul?

**Ya — bubble muncul di SETIAP halaman** yang memuat `templates/footer.php`.

Hasil audit: **190 pemanggilan `header` + 190 pemanggilan `footer`** di 30 controller → semua halaman utama konsisten memuat footer. Karena widget di-inject di `footer.php`, bubble otomatis muncul di semua halaman tersebut.

### ✅ Bubble MUNCUL di halaman:
- Dashboard (semua role: admin, guru, wali_kelas, kepala_madrasah, bendahara, psb, cms, buku_tamu, bk)
- Semua halaman data (siswa, guru, nilai, absensi, jadwal, rapor, dll)
- Halaman pengaturan
- Halaman pesan/inbox
- Semua halaman yang pakai layout `header.php` + `footer.php`

### ❌ Bubble TIDAK muncul di halaman:
| Halaman | Alasan |
|---------|--------|
| **Login** (`auth/login.php`) | Belum login, tidak ada session user |
| **Halaman cetak/print** (`cetak_*.php`, `*_pdf.php`, `preview_cetak.php`) | Standalone HTML (`<!DOCTYPE html>` sendiri), tidak pakai footer — dan memang tidak boleh ada widget di hasil cetak |
| **Halaman publik** (PSB, news, home) | Tidak pakai layout admin/guru |
| **Halaman siswa** | Role siswa dikecualikan (guard di footer) |

> **Catatan:** Halaman cetak memang **sengaja** dikecualikan karena mereka render HTML standalone (bukan lewat `footer.php`), jadi tidak akan terpengaruh sama sekali. Ini justru menguntungkan — hasil cetak tetap bersih.

---

## 3. Alur Data (Chat Flow)

### 3.1 Alur UX — Sesi Percakapan

Setiap percakapan punya **sesi sendiri** supaya topik tetap terfokus. User bisa punya banyak percakapan terpisah (misal: "Jadwal Ujian", "Rekap Nilai", "Surat Tugas").

```
┌─────────────────────────────────────────────────────────────┐
│  PERTAMA KALI BUKA (belum ada percakapan aktif)             │
│                                                             │
│              ┌───────────────────────────────┐              │
│              │   🤖 Pak Admin                │              │
│              │                               │              │
│              │   Halo Ahmad! Ada yang bisa   │              │
│              │   saya bantu hari ini?        │              │
│              │                               │              │
│              │   ┌───────────────────────┐   │              │
│              │   │  ➕ Buat Percakapan   │   │              │
│              │   │     Baru              │   │              │
│              │   └───────────────────────┘   │              │
│              └───────────────────────────────┘              │
└─────────────────────────────────────────────────────────────┘
                            │ klik "Buat Percakapan Baru"
                            ▼
┌─────────────────────────────────────────────────────────────┐
│  PERCAKAPAN AKTIF (sesi terfokus)                           │
│                                                             │
│  ┌───────────────────────────────────────────────────────┐  │
│  │ 🤖 Pak Admin          [🕘 Riwayat]  [➕ Baru]  [✕]    │  │
│  ├───────────────────────────────────────────────────────┤  │
│  │ Kamu: Buat jadwal ujian semester ganjil               │  │
│  │ 🤖: Baik Ahmad, berikut langkah-langkahnya...         │  │
│  │ Kamu: Tambahkan untuk kelas IX juga                   │  │
│  │ 🤖: Siap, saya tambahkan...                           │  │
│  ├───────────────────────────────────────────────────────┤  │
│  │ [Ketik pesan...]                              📤      │  │
│  └───────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────┘
                            │ klik "🕘 Riwayat"
                            ▼
┌─────────────────────────────────────────────────────────────┐
│  DAFTAR RIWAYAT PERCAKAPAN                                  │
│                                                             │
│  ┌───────────────────────────────────────────────────────┐  │
│  │ 🕘 Riwayat Percakapan                    [➕ Baru]     │  │
│  ├───────────────────────────────────────────────────────┤  │
│  │ 💬 Jadwal Ujian Semester Ganjil                       │  │
│  │    2 pesan · 5 menit lalu                    [🗑]     │  │
│  ├───────────────────────────────────────────────────────┤  │
│  │ 💬 Rekap Nilai Kelas VII A                            │  │
│  │    8 pesan · kemarin                         [🗑]     │  │
│  ├───────────────────────────────────────────────────────┤  │
│  │ 💬 Surat Tugas Rapat                                  │  │
│  │    4 pesan · 3 hari lalu                     [🗑]     │  │
│  └───────────────────────────────────────────────────────┘  │
│                                                             │
│  → Klik salah satu → lanjutkan percakapan dari titik terakhir│
└─────────────────────────────────────────────────────────────┘
```

**Poin penting:**
- **Awal**: jika belum ada percakapan aktif → tampil tombol **"➕ Buat Percakapan Baru"**.
- **Setelah ada percakapan**: header menampilkan tombol **"🕘 Riwayat"** (buka daftar percakapan lama) dan **"➕ Baru"** (mulai sesi baru).
- **Lanjutkan**: klik percakapan di riwayat → semua pesan lama dimuat → user bisa **meneruskan** chat dari titik terakhir (context tetap nyambung).
- **Fokus**: setiap percakapan punya konteks sendiri — topik tidak tercampur antar sesi.

### 3.2 Alur Teknis (Request/Response)

```
1. User klik tombol "Tanya Pak Admin" → widget terbuka
2. Widget load daftar conversation (GET /aichat/conversations)
3. Jika belum ada conversation aktif:
   → tampil tombol "Buat Percakapan Baru"
   → klik → POST /aichat/newConversation → dapat id_conversation
4. Jika sudah ada: widget load pesan (GET /aichat/messages/{id})
5. User ketik pesan → klik kirim
6. POST /aichat/send { conversation_id, message }
   ├─ Simpan pesan user ke ai_chat_messages
   ├─ Ambil N pesan terakhir sebagai context (ai_chat_max_history)
   ├─ Bangun system prompt + KONTEKS IDENTITAS user (dari session)
   ├─ Kirim ke OpenAI-compatible API via AiProvider (POST /chat/completions)
   ├─ Simpan respons AI ke ai_chat_messages
   └─ Return JSON { reply }
7. Widget tampilkan respons Pak Admin
8. User klik "🕘 Riwayat" → GET /aichat/conversations → pilih → lanjutkan
```

> **Catatan:** Context percakapan diambil dari **pesan-pesan dalam conversation yang sama** (`WHERE id_conversation = ?`), bukan dari semua percakapan. Jadi tiap sesi benar-benar terisolasi dan terfokus pada topiknya.

---

## 4. Skema Database

### 4.1 Tabel `ai_chat_conversations`

```sql
CREATE TABLE IF NOT EXISTS `ai_chat_conversations` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `id_user` INT(11) NOT NULL COMMENT 'FK ke users.id_user',
  `judul` VARCHAR(255) DEFAULT 'Percakapan Baru' COMMENT 'Auto-generate dari pesan pertama',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_user` (`id_user`),
  INDEX `idx_updated` (`updated_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 4.2 Tabel `ai_chat_messages`

```sql
CREATE TABLE IF NOT EXISTS `ai_chat_messages` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `id_conversation` INT(11) NOT NULL COMMENT 'FK ke ai_chat_conversations.id',
  `role` ENUM('user','assistant') NOT NULL,
  `content` TEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_conversation` (`id_conversation`),
  CONSTRAINT `fk_ai_msg_conv` FOREIGN KEY (`id_conversation`)
    REFERENCES `ai_chat_conversations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 4.3 Settings di `pengaturan_sistem`

```sql
INSERT INTO `pengaturan_sistem` (`key_name`, `value`, `description`) VALUES
('ai_chat_enabled',      '0',                    'Aktifkan AI Chat Assistant (1=ya, 0=tidak)'),
('ai_chat_name',         'Pak Admin',            'Nama tampilan AI'),
('ai_chat_button_label', 'Tanya Pak Admin',      'Teks pada tombol chat'),
('ai_chat_base_url',     'https://api.openai.com/v1', 'Base URL OpenAI-compatible endpoint (tanpa trailing slash)'),
('ai_chat_api_key',      '',                     'API key untuk endpoint AI'),
('ai_chat_model',        'gpt-4o-mini',           'Nama model AI'),
('ai_chat_max_history',  '20',                   'Jumlah pesan terakhir sebagai context'),
('ai_chat_daily_limit',  '50',                   'Batas pesan per user per hari (0 = unlimited, reset 00:00 WIB)'),
('ai_chat_temperature',  '0.7',                  'Kreativitas respons AI (0.0 - 2.0)'),
('ai_chat_system_prompt', 'Kamu adalah "Pak Admin", asisten AI untuk guru dan staf Madrasah Sabilillah. Bantu menjawab pertanyaan seputar pekerjaan, administrasi, dan tugas mengajar dengan ramah dan profesional. Panggil pengguna dengan nama dan sesuaikan jawaban dengan peran/jabatannya.', 'Instruksi sistem untuk AI')
ON DUPLICATE KEY UPDATE `key_name` = `key_name`;
```

> **Catatan:** Karena memakai **OpenAI-compatible API**, admin cukup mengisi **Base URL** + **API Key** + **Model**. Contoh base URL:
> - OpenAI: `https://api.openai.com/v1`
> - OpenRouter: `https://openrouter.ai/api/v1`
> - Groq: `https://api.groq.com/openai/v1`
> - Ollama (self-host): `http://localhost:11434/v1`
> - LM Studio: `http://localhost:1234/v1`
> - vLLM / server sendiri: `http://<host>:<port>/v1`

---

## 4.4 Kesadaran Identitas — Pak Admin Tahu Siapa yang Chat

Karena data **terisolasi per akun**, Pak Admin harus **mengenali siapa** yang sedang berbicara. Identitas user di-inject otomatis ke **system prompt** setiap kali request dikirim.

### Data identitas yang di-inject (dari `$_SESSION`):

| Data | Sumber | Contoh |
|------|--------|--------|
| Nama lengkap | `$_SESSION['nama_lengkap']` | "Ahmad Fauzi, S.Pd." |
| Role | `$_SESSION['role']` | `guru` / `admin` / `wali_kelas` / `kepala_madrasah` |
| Jabatan/fungsi | `guru_fungsi` (jika ada) | "Bendahara", "Guru BK", "Wali Kelas VII A" |
| Semester aktif | `$_SESSION['nama_semester_aktif']` | "2024/2025 - Ganjil" |
| Tahun pelajaran | `$_SESSION['id_tp_aktif']` | "2024/2025" |

### Contoh system prompt yang dikirim ke AI:

```
Kamu adalah "Pak Admin", asisten AI untuk guru dan staf Madrasah Sabilillah.
Bantu menjawab pertanyaan seputar pekerjaan, administrasi, dan tugas mengajar
dengan ramah dan profesional.

KONTEKS PENGGUNA SAAT INI:
- Nama: Ahmad Fauzi, S.Pd.
- Role: guru
- Jabatan tambahan: Wali Kelas VII A
- Semester aktif: 2024/2025 - Ganjil

Sapa pengguna dengan namanya dan sesuaikan jawaban dengan peran/jabatannya.
```

### Implementasi (di `AiChatController::send()`):

```php
// Bangun konteks identitas dari session
$identitas = "KONTEKS PENGGUNA SAAT INI:\n";
$identitas .= "- Nama: " . ($_SESSION['nama_lengkap'] ?? 'Pengguna') . "\n";
$identitas .= "- Role: " . ($_SESSION['role'] ?? '-') . "\n";

// Jabatan tambahan: hanya untuk guru (id_ref = id_guru), bukan admin
$jabatanFungsi = '';
if (in_array($_SESSION['role'] ?? '', ['guru', 'wali_kelas'])) {
    $fungsiModel = $this->model('GuruFungsi_model');
    $daftarFungsi = $fungsiModel->getFungsiByGuru($_SESSION['id_ref'], $_SESSION['id_tp_aktif'] ?? null);
    if (!empty($daftarFungsi)) {
        $label = array_map(fn($f) => ucwords(str_replace('_', ' ', $f['fungsi'])), $daftarFungsi);
        $jabatanFungsi = implode(', ', $label);
    }
}
if ($jabatanFungsi) {
    $identitas .= "- Jabatan tambahan: " . $jabatanFungsi . "\n";
}
$identitas .= "- Semester aktif: " . ($_SESSION['nama_semester_aktif'] ?? '-') . "\n";

// Gabungkan dengan system prompt dari settings
$systemPrompt = getSystemSetting('ai_chat_system_prompt', '...') . "\n\n" . $identitas;
```

> **Catatan lookup jabatan:** Sumber jabatan adalah tabel `guru_fungsi` via `GuruFungsi_model::getFungsiByGuru($id_guru, $id_tp)`. Lookup **hanya dijalankan untuk role `guru`/`wali_kelas`** (yang `id_ref`-nya adalah `id_guru`). Untuk **admin** dan **kepala_madrasah**, `id_ref` bukan guru → lookup **di-skip** agar tidak error. Jika `guru_fungsi` kosong, bagian jabatan tidak ditampilkan.

> **Penting:** Identitas diambil dari **session server-side**, bukan dari input browser. Jadi user **tidak bisa memalsukan** identitasnya — Pak Admin selalu tahu siapa yang sebenarnya login.

> **Isolasi tetap terjaga:** Meskipun Pak Admin tahu identitas, ia **hanya bisa mengakses conversation milik user yang login** (ownership check di model). User A tidak akan pernah melihat riwayat chat user B.

---

## 5. File yang Dibuat (CREATE)

### 5.1 `migrations/1.33.0.sql`
Migration tabel + settings. Format mengikuti `migrations/1.32.0.sql` (header komentar, `CREATE TABLE IF NOT EXISTS`, idempotent guards).

### 5.2 `app/core/AiProvider.php`
Client untuk **OpenAI-compatible API** (endpoint `/chat/completions`).

```php
class AiProvider {
    private $baseUrl;   // contoh: https://api.openai.com/v1
    private $apiKey;
    private $model;
    private $temperature;

    public function __construct(string $baseUrl, string $apiKey, string $model, float $temperature = 0.7);

    /**
     * Kirim chat completion request.
     * @param array  $messages     Format OpenAI: [['role'=>'user','content'=>'...'], ...]
     * @param string $systemPrompt Instruksi sistem
     * @return array ['status'=>bool, 'reply'=>string, 'error'=>string|null]
     */
    public function chat(array $messages, string $systemPrompt): array;
}
```

- Endpoint: `POST {baseUrl}/chat/completions`
- Header: `Authorization: Bearer {apiKey}`, `Content-Type: application/json`
- Body: `{ model, messages, temperature }` (system prompt disisipkan sebagai message pertama dengan `role: system`)
- Pattern cURL mengikuti `app/core/Fonnte.php` (`curl_setopt_array`, timeout 60s untuk LLM, error handling).
- Return format konsisten: `['status' => bool, 'reply' => string, 'error' => string|null]`.
- **Tidak ada** percabangan provider — satu implementasi untuk semua endpoint yang kompatibel OpenAI.

### 5.3 `app/models/AiChat_model.php`
CRUD conversations & messages dengan **ownership check** di setiap query.

| Method | Deskripsi |
|--------|-----------|
| `getConversations($userId, $limit = 50, $offset = 0)` | List conversation milik user (urut `updated_at DESC`) + jumlah pesan & preview pesan terakhir (untuk tampilan riwayat). **Di-paginate** (default 50 terbaru) |
| `getConversation($id, $userId)` | Get 1 conversation + cek kepemilikan |
| `createConversation($userId, $judul)` | Buat conversation baru (sesi baru) |
| `getMessages($conversationId, $limit)` | Get pesan **dalam satu conversation** untuk context (terfokus per sesi) |
| `addMessage($conversationId, $role, $content)` | Simpan pesan |
| `deleteConversation($id, $userId)` | Hapus conversation (cascade ke messages) |
| `countTodayMessages($userId)` | Untuk rate limiting harian (hitung sejak `00:00` **Asia/Jakarta**) |
| `updateJudul($id, $judul)` | Auto-set judul dari pesan pertama |
| `touchConversation($id)` | Update `updated_at` agar urutan riwayat benar |

- Pattern mengikuti `app/core/Database.php`: `query()` → `bind()` → `execute()` / `single()` / `resultSet()`.
- **Context per sesi**: `getMessages()` hanya mengambil pesan dari `id_conversation` yang sama → tiap percakapan terfokus pada topiknya sendiri.
- **Auto-generate judul**: saat pesan **pertama** user dikirim, ambil ~50 karakter pertama dari pesan tersebut, potong di batas kata terakhir, tambahkan `…` jika terpotong. Fallback: `"Percakapan Baru"`. Dipanggil via `updateJudul()`.
- **Pagination riwayat**: `getConversations()` dibatasi 50 terbaru; jika ada lebih, UI menampilkan tombol **"Muat lagi"** (offset bertambah).

### 5.4 `app/controllers/AiChatController.php`
API controller (JSON responses).

| Endpoint | Method | Deskripsi |
|----------|--------|-----------|
| `/aichat/conversations` | GET | List conversation user (untuk riwayat) |
| `/aichat/messages/{id}` | GET | Pesan dalam satu conversation (lanjutkan sesi) |
| `/aichat/send` | POST | Kirim pesan → AI → simpan → return reply |
| `/aichat/newConversation` | POST | Buat conversation baru (sesi baru) |
| `/aichat/deleteConversation/{id}` | POST | Hapus conversation |

**Auth guard** (di `__construct`):
```php
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') === 'siswa') {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}
// Defense in depth: tolak juga jika fitur dimatikan
if (getSystemSetting('ai_chat_enabled', '0') !== '1') {
    http_response_code(403);
    echo json_encode(['error' => 'Fitur AI Chat tidak aktif']);
    exit;
}
```

- CSRF otomatis divalidasi oleh `App.php` untuk semua POST (header `X-CSRF-Token`).
- **Rate limiting**: cek `countTodayMessages()` vs `ai_chat_daily_limit`. Reset harian pada **00:00 Asia/Jakarta**. Nilai `0` = **unlimited**. Jika terlampaui → HTTP **429** + pesan jelas.
- **Inject identitas user** ke system prompt (lihat §4.4) — Pak Admin tahu siapa yang chat.
- **Error handling AI**: jika `AiProvider` gagal/timeout → return HTTP **502** + JSON `{error}`. Pesan user **tetap tersimpan**; UI menampilkan tombol **"Coba lagi"** (tidak menyimpan pesan error sebagai assistant).
- **Logging**: `error_log()` untuk kegagalan API (endpoint, status code, pesan error) — tanpa mencatat API key.
- **Truncation context**: selain batas `ai_chat_max_history` pesan, batasi total panjang context (~8000 karakter) — pesan lama dipotong dari depan jika melebihi, agar tidak melebihi context window model.

### 5.5 `public/js/ai-chat.js`
Chat widget frontend (vanilla JS).

**Tampilan tombol (FAB) — selalu terlihat di pojok kanan bawah:**

```
┌──────────────────────────────────────────────┐
│  [Konten halaman...]                         │
│                                              │
│                                              │
│                                              │
│                                              │
│                        ╭────────────────────╮│
│                        │ 💬 Tanya Pak Admin ││  ← Tombol (FAB)
│                        ╰────────────────────╯│     fixed bottom-6 right-6
└──────────────────────────────────────────────┘
```

**Saat tombol diklik → panel chat terbuka (dengan sesi & riwayat):**

```
┌──────────────────────────────────────────────┐
│  [Konten halaman...]  ┌────────────────────┐ │
│                       │ 🤖 Pak Admin       │ │  ← Header
│                       │ [🕘 Riwayat][➕][✕]│ │     (tombol riwayat/baru/tutup)
│                       ├────────────────────┤ │
│                       │ Halo Ahmad! Ada    │ │
│                       │ yang bisa saya     │ │  ← Area pesan
│                       │ bantu?             │ │     (per sesi)
│                       │                    │ │
│                       │      Kamu:         │ │
│                       │  Buat jadwal ujian │ │
│                       │                    │ │
│                       │ 🤖: Baik Ahmad, ...│ │
│                       ├────────────────────┤ │
│                       │ [Ketik pesan...] 📤│ │  ← Input area
│                       └────────────────────┘ │
└──────────────────────────────────────────────┘
```

- **FAB**: floating button bottom-right dengan **teks "Tanya Pak Admin"** + icon `message-circle` (Lucide), posisi `fixed bottom-6 right-6 z-50` (mengikuti pattern di `admin/cms/identitas.php:177`).
- **Chat window**: header menampilkan **"Pak Admin"** + tombol **🕘 Riwayat**, **➕ Baru**, **✕ Tutup**.
- **State awal**: jika belum ada percakapan → tampil tombol **"➕ Buat Percakapan Baru"**.
- **Greeting**: pesan sambutan ("Halo {nama}! Ada yang bisa saya bantu?") adalah **teks statis** yang di-render dari config — **tidak memanggil API** (hemat biaya). Nama diambil dari config yang di-inject server-side.
- **Riwayat**: panel daftar percakapan lama (judul, jumlah pesan, waktu) → klik untuk **melanjutkan** sesi dari titik terakhir.
- **Sesi terfokus**: setiap percakapan punya konteks sendiri; ganti sesi = ganti topik.
- **Fitur**: auto-scroll, loading indicator, error handling, markdown rendering sederhana.
- **State**: localStorage (open/closed, active conversation id).
- **Responsive**: full-screen di mobile, panel di desktop.

**Konfigurasi yang di-pass dari server ke JS** (via `window.AI_CHAT_CONFIG` di footer.php):

```php
<script>
window.AI_CHAT_CONFIG = {
  baseurl: <?= json_encode(BASEURL) ?>,
  aiName: <?= json_encode(getSystemSetting('ai_chat_name', 'Pak Admin')) ?>,
  buttonLabel: <?= json_encode(getSystemSetting('ai_chat_button_label', 'Tanya Pak Admin')) ?>,
  userName: <?= json_encode($_SESSION['nama_lengkap'] ?? 'Pengguna') ?>,
  csrfToken: <?= json_encode(Csrf::token()) ?>
};
</script>
```

**CSRF pada request AJAX** (WAJIB — `App.php` memvalidasi semua POST ter-autentikasi):

```js
fetch(cfg.baseurl + '/aichat/send', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'X-CSRF-Token': cfg.csrfToken   // nama header sesuai Csrf::HEADER
  },
  body: JSON.stringify({ conversation_id, message })
});
```

> **Penting:** Nama header harus **`X-CSRF-Token`** (konstanta `Csrf::HEADER`). Token juga tersedia di `<meta name="csrf-token">` sebagai fallback.

**Penanganan error khusus:**

| Kondisi | Deteksi | Aksi di JS |
|---------|---------|------------|
| **CSRF gagal** | HTTP **419** + JSON `{success:false}` | Tampilkan "Sesi keamanan tidak valid. Muat ulang halaman." |
| **Session habis** (8 jam) | Response **redirect ke login** (HTML, bukan JSON) / `response.redirected` / parse JSON gagal | Tampilkan "Sesi Anda berakhir. Silakan login ulang." + tombol reload |
| **Fitur dimatikan** | HTTP **403** | Sembunyikan widget |
| **Rate limit** | HTTP **429** + JSON | Tampilkan pesan limit dari server |
| **API AI error/timeout** | HTTP **502/504** + JSON `{error}` | Tampilkan pesan error + tombol **"Coba lagi"** |

**Cegah double-send:** saat menunggu respons, **disable** tombol kirim + input, tampilkan loading indicator. Aktifkan kembali setelah respons/error.

**Markdown rendering + XSS (aman):**
1. **Escape HTML dulu** (`&`, `<`, `>`, `"`, `'`) pada seluruh konten AI.
2. Baru terapkan markdown **terbatas** via regex pada teks yang sudah di-escape: `**bold**`, `*italic*`, `` `code` ``, list (`- ` / `1. `), dan line break.
3. **Jangan** render HTML mentah dari AI. Tidak pakai `innerHTML` untuk konten mentah — hanya untuk hasil yang sudah di-escape + markdown terbatas.

**Konfirmasi hapus:** tombol 🗑 di riwayat memunculkan dialog konfirmasi ("Hapus percakapan ini?") sebelum memanggil `deleteConversation`.

### 5.6 `public/css/ai-chat.css`
Styling widget.

- Glass-effect sesuai design system existing (`backdrop-filter: blur`).
- Animasi open/close (transform + opacity).
- Markdown styling (bold, italic, list, code block).
- Responsive breakpoints.
- **Print-safe**: `@media print { #ai-chat-root { display: none !important; } }` — supaya bubble **tidak ikut tercetak** saat user menekan Ctrl+P di halaman normal.
- **Z-index**: gunakan `z-[60]` (di atas floating button existing yang `z-50`) agar tidak tertutup.

---

## 6. File yang Dimodifikasi (MODIFY)

### 6.1 `app/views/templates/footer.php`
Inject chat widget HTML + JS **setelah** `</script>` terakhir (baris ~466).

Karena **semua halaman** memuat `footer.php`, bubble otomatis muncul di mana-mana. Guard hanya perlu mengecek: (1) fitur aktif, (2) user login, (3) role bukan siswa.

```php
<?php
$aiChatEnabled = getSystemSetting('ai_chat_enabled', '0') === '1';
$aiChatRoleOk  = isset($_SESSION['user_id']) && ($_SESSION['role'] ?? '') !== 'siswa';
if ($aiChatEnabled && $aiChatRoleOk):
?>
<!-- AI Chat Widget "Pak Admin" (tombol muncul di semua halaman) -->
<div id="ai-chat-root"></div>
<script>
window.AI_CHAT_CONFIG = {
  baseurl: <?= json_encode(BASEURL) ?>,
  aiName: <?= json_encode(getSystemSetting('ai_chat_name', 'Pak Admin')) ?>,
  buttonLabel: <?= json_encode(getSystemSetting('ai_chat_button_label', 'Tanya Pak Admin')) ?>,
  userName: <?= json_encode($_SESSION['nama_lengkap'] ?? 'Pengguna') ?>,
  csrfToken: <?= json_encode(Csrf::token()) ?>
};
</script>
<script src="<?= BASEURL ?>/js/ai-chat.js" defer></script>
<?php endif; ?>
```

> **Halaman cetak/PDF tidak terpengaruh** karena mereka render HTML standalone (tidak lewat `footer.php`), sehingga hasil cetak tetap bersih tanpa bubble. Untuk Ctrl+P di halaman normal, bubble disembunyikan via `@media print` (lihat §5.6).

### 6.2 `app/views/templates/header.php`
Inject CSS link (conditional) setelah Quill CSS/JS (~baris 173).

```php
<?php if (getSystemSetting('ai_chat_enabled', '0') === '1' && ($_SESSION['role'] ?? '') !== 'siswa'): ?>
<link rel="stylesheet" href="<?= BASEURL ?>/css/ai-chat.css">
<?php endif; ?>
```

### 6.3 `app/views/templates/sidebar_admin.php`
Tambah link menu **"Pengaturan AI Chat"** di dropdown **Pengaturan** (baris ~398-490), mengikuti pattern link existing:

```php
<li>
    <a href="<?= BASEURL; ?>/admin/pengaturanAiChat"
        class="flex items-center p-2.5 text-sm font-medium rounded-lg transition-all duration-200 <?= isActive($judul, 'Pengaturan AI Chat') ? 'bg-primary-100 text-primary-700' : 'text-secondary-600 hover:bg-violet-50 hover:text-violet-700' ?>">
        <i data-lucide="bot" class="w-4 h-4 mr-2"></i>
        Pengaturan AI Chat
    </a>
</li>
```

Juga tambahkan `'Pengaturan AI Chat'` ke daftar keyword di `isGroupActive()` (baris ~400) agar dropdown ter-highlight saat halaman aktif.

### 6.4 `app/controllers/traits/AdminPengaturanTrait.php`
Tambah method `pengaturanAiChat()` dan `updatePengaturanAiChat()`.

### 6.5 `app/views/admin/pengaturan_ai_chat.php` (BARU)
UI settings AI Chat:
- Toggle enable/disable
- Input **Nama AI** (default: "Pak Admin")
- Input **Label tombol** (default: "Tanya Pak Admin")
- Input **Base URL** (OpenAI-compatible endpoint)
- Input **API Key** (masked, dengan tombol show/hide)
- Input **Model name**
- Input **Temperature** (slider 0.0 - 2.0)
- Textarea **System prompt**
- Input **Max history** & **Daily limit**
- Tombol **"Test Koneksi"** (kirim pesan uji ke endpoint, tampilkan hasil)
- Info bantuan: contoh base URL untuk OpenAI/OpenRouter/Groq/Ollama

### 6.6 `version.json` & `changelog.json`
Bump ke `1.33.0` (build `1330`), tambah entry changelog.

---

## 7. File Referensi (TIDAK diubah)

| File | Kegunaan sebagai referensi |
|------|---------------------------|
| `app/core/Database.php` | Pattern DB query (`query()`, `bind()`, `execute()`, `single()`, `resultSet()`) |
| `app/core/Fonnte.php` | Pattern cURL external API calls |
| `app/models/PengaturanSistem_model.php` | Pattern settings (`get()`, `set()`, `updateMultiple()`) |
| `app/core/Controller.php` | Pattern `view()`, CSRF auto-inject |
| `app/views/admin/cms/identitas.php:177` | Pattern floating button (`fixed bottom-6 right-6 z-50`) |
| `app/views/templates/sidebar_admin.php:398-490` | Pattern link menu dropdown Pengaturan + `isActive()`/`isGroupActive()` |
| `app/views/templates/footer.php:407-468` | Pattern dropdown toggle JS |
| `migrations/1.32.0.sql` | Format migration terbaru |

---

## 8. Keamanan

| Aspek | Implementasi |
|-------|--------------|
| **Auth** | Guard di controller: block `siswa` + user tidak login |
| **Defense in depth** | Endpoint juga tolak jika `ai_chat_enabled != 1` (bukan hanya guard di footer) |
| **Isolasi data** | Setiap query model menyertakan `WHERE id_user = ?` |
| **Identitas anti-spoof** | Identitas user diambil dari **session server-side**, bukan input browser — tidak bisa dipalsukan |
| **CSRF** | Otomatis divalidasi `App.php` untuk POST (header `X-CSRF-Token` dari meta tag) |
| **API key** | Disimpan di `pengaturan_sistem`, hanya diakses server-side (tidak pernah dikirim ke browser) |
| **Rate limiting** | `ai_chat_daily_limit` per user per hari (reset 00:00 WIB, 0 = unlimited) |
| **XSS** | Escape HTML dulu, baru markdown terbatas — respons AI tidak pernah di-render sebagai HTML mentah |
| **SQL injection** | Prepared statements via `Database.php` |
| **SSRF** | Validasi `ai_chat_base_url` hanya boleh http/https, blokir alamat internal jika perlu |
| **Session expiry** | Deteksi response non-JSON (redirect login) → tampilkan pesan login ulang, bukan error parse |

---

## 9. Verifikasi (Testing Checklist)

- [ ] **Migration**: Jalankan migration → tabel `ai_chat_conversations` & `ai_chat_messages` terbuat, settings masuk ke `pengaturan_sistem`
- [ ] **Auth guard**: Login sebagai **siswa** → tombol **tidak muncul**, endpoint return **403**
- [ ] **Auth guard**: Login sebagai guru/admin/wali_kelas/kepala_madrasah → tombol **"Tanya Pak Admin"** muncul
- [ ] **Chat flow**: Kirim pesan → respons Pak Admin muncul → pesan tersimpan di DB
- [ ] **Identitas**: Pak Admin menyapa user dengan **nama yang benar** dan menyesuaikan jawaban dengan role/jabatan
- [ ] **Identitas anti-spoof**: Coba manipulasi request → Pak Admin tetap tahu identitas sebenarnya (dari session)
- [ ] **Per-user isolation**: User A kirim chat, login user B → **tidak bisa** lihat chat user A
- [ ] **Sesi baru**: Buka pertama kali → tampil tombol "Buat Percakapan Baru"
- [ ] **Riwayat**: Setelah ada percakapan → tombol "🕘 Riwayat" muncul dan menampilkan daftar
- [ ] **Lanjutkan sesi**: Klik percakapan di riwayat → pesan lama dimuat → bisa diteruskan
- [ ] **Fokus sesi**: Context percakapan baru tidak tercampur dengan percakapan lama
- [ ] **Conversation management**: Buat baru, switch, hapus
- [ ] **Auto-judul**: Pesan pertama → judul percakapan otomatis terisi (potong ~50 char)
- [ ] **Pagination riwayat**: Jika > 50 percakapan → tombol "Muat lagi" berfungsi
- [ ] **Konfirmasi hapus**: Klik 🗑 → muncul dialog konfirmasi sebelum hapus
- [ ] **Greeting statis**: Pesan sambutan muncul tanpa memanggil API (cek tidak ada request ke endpoint AI)
- [ ] **Double-send**: Klik kirim berkali-kali cepat → hanya 1 request terkirim
- [ ] **CSRF header**: Request POST menyertakan header `X-CSRF-Token`
- [ ] **CSRF gagal**: Token invalid → tampil pesan "muat ulang halaman" (HTTP 419)
- [ ] **Session habis**: Session expired saat chat → tampil pesan "login ulang" (bukan error parse JSON)
- [ ] **Fitur dimatikan**: `ai_chat_enabled=0` → endpoint return 403 (defense in depth)
- [ ] **AI error**: Endpoint AI down → tampil pesan error + tombol "Coba lagi"
- [ ] **Markdown aman**: Respons AI dengan `<script>` → ter-escape, tidak dieksekusi
- [ ] **Print**: Ctrl+P di halaman normal → bubble tidak ikut tercetak
- [ ] **Z-index**: Bubble tidak tertutup floating button lain
- [ ] **Settings**: Admin disable `ai_chat_enabled` → tombol hilang di semua halaman
- [ ] **Settings**: Ganti base URL / API key / model → chat tetap berfungsi
- [ ] **Settings**: Tombol "Test Koneksi" berhasil memanggil endpoint
- [ ] **Rate limit**: Kirim > limit pesan → ditolak dengan pesan yang jelas
- [ ] **Mobile**: Widget responsive di layar kecil
- [ ] **CSRF**: POST requests include CSRF token
- [ ] **Error handling**: API key salah / endpoint down → pesan error yang ramah

---

## 10. Keputusan Desain

| Keputusan | Alasan |
|-----------|--------|
| ✅ **Nama AI: "Pak Admin"** | Sesuai permintaan user — terasa personal & mudah diingat |
| ✅ **Tombol: "Tanya Pak Admin"** | Label jelas, mengundang user untuk berinteraksi |
| ✅ **AI mengenali identitas user** | Pak Admin tahu nama, role, jabatan → jawaban lebih relevan & personal |
| ✅ **Sesi percakapan terpisah** | Tiap percakapan punya konteks sendiri → topik tetap terfokus, tidak tercampur |
| ✅ **Tombol Riwayat** | User bisa melanjutkan percakapan lama dari titik terakhir |
| ✅ **Siswa dikecualikan** | Sesuai permintaan user |
| ✅ **Data per-user** | Setiap user punya conversation sendiri, terisolasi |
| ✅ **No WebSocket** | Menggunakan AJAX biasa (sesuai arsitektur existing yang synchronous) |
| ✅ **OpenAI-compatible API** | Satu implementasi client untuk semua endpoint kompatibel (OpenAI, OpenRouter, Groq, Ollama, vLLM, LM Studio, self-host) — cukup atur Base URL + API Key + Model |
| ✅ **System prompt customizable** | Admin bisa atur personality/context AI |
| ✅ **Non-streaming dulu** | Lebih simpel, cocok dengan arsitektur existing |
| ✅ **Greeting statis** | Pesan sambutan tidak memanggil API → hemat biaya |
| ✅ **Print-safe & z-index** | Bubble disembunyikan saat print, z-index di atas elemen lain |
| ✅ **Markdown aman** | Escape HTML dulu → cegah XSS dari respons AI |
| ❌ **Excluded**: file upload ke AI, voice input, integrasi data sekolah (nilai/absensi) ke context AI | Bisa ditambah di iterasi berikutnya |

---

## 11. Pertimbangan Lanjutan (Future Enhancements)

1. **Streaming (SSE)** — Respons AI muncul bertahap (seperti ChatGPT) untuk UX lebih baik. Perlu endpoint SSE terpisah.
2. **Context sekolah** — Inject nama madrasah & data relevan dari `pengaturan_sistem` ke system prompt otomatis, supaya AI "tahu" konteks sekolah.
3. **Integrasi data** — AI bisa menjawab pertanyaan seperti "berapa siswa kelas VII A?" dengan query ke DB (function calling).
4. **Riwayat & analitik** — Dashboard admin untuk melihat statistik penggunaan AI (jumlah pesan, user aktif, biaya).
5. **Multi-bahasa** — Dukungan bahasa Indonesia & Inggris.
6. **Voice input** — Speech-to-text untuk input pesan.
7. **Tracking token/biaya** — Simpan `tokens_used` per request untuk analitik biaya.
8. **Enkripsi API key** — Simpan API key terenkripsi di DB (saat ini plaintext).
9. **Accessibility** — `aria-label`, navigasi keyboard, focus trap pada panel chat.

---

## 12. Estimasi Urutan Pengerjaan

| # | Task | File |
|---|------|------|
| 1 | Migration DB + settings | `migrations/1.33.0.sql` |
| 2 | OpenAI-compatible client | `app/core/AiProvider.php` |
| 3 | Model | `app/models/AiChat_model.php` |
| 4 | Controller | `app/controllers/AiChatController.php` |
| 5 | Frontend JS | `public/js/ai-chat.js` |
| 6 | Frontend CSS | `public/css/ai-chat.css` |
| 7 | Inject di template | `header.php`, `footer.php` |
| 8 | Admin settings UI + menu sidebar | `AdminPengaturanTrait.php`, `pengaturan_ai_chat.php`, `sidebar_admin.php` |
| 9 | Version bump + changelog | `version.json`, `changelog.json` |
| 10 | Testing | Checklist di §9 |

---

## 13. Catatan Teknis Penting

- **Routing**: Convention-based (`/aichat/send` → `AiChatController::send`). `App.php` parse URL, `ucfirst` + suffix `Controller`.
- **CSRF**: v1.24.0+, auto-injected via `Controller::view()` ke `<head>`. POST divalidasi di `App.php`.
- **Session keys**: `user_id`, `role`, `nama_lengkap`, `id_ref`, `id_semester_aktif`.
- **Sidebar**: Tidak perlu diubah — widget di-inject di footer, bukan sidebar.
- **`pengaturan_sistem`**: Key-value store, akses via `getSystemSetting($key, $default)` (global helper di `config/config.php`).
- **View `admin/pengaturan_sistem.php` tidak ada** — controller `pengaturanSistem()` mereferensikan file yang tidak eksis. Untuk AI Chat, buat view baru `pengaturan_ai_chat.php` dan method terpisah.
- **OpenAI-compatible**: Endpoint standar `POST {baseUrl}/chat/completions` dengan header `Authorization: Bearer {apiKey}`. Body `{ model, messages, temperature }`. Respons: `choices[0].message.content`.
- **Timeout**: Set 60s untuk request LLM (lebih lama dari WA gateway yang 30s) karena generasi teks bisa lambat.
- **CSRF header**: Nama header wajib **`X-CSRF-Token`** (konstanta `Csrf::HEADER`). Token juga tersedia di `<meta name="csrf-token">`. Saat gagal, `App.php` return **HTTP 419** + JSON (untuk AJAX).
- **Session lifetime**: 8 jam (`ABSEN_SESSION`). Jika habis saat chat, request AJAX akan di-redirect ke login (HTML) → JS harus deteksi response non-JSON dan tampilkan pesan login ulang.
- **BASEURL ke JS**: Di-pass via `window.AI_CHAT_CONFIG` (bukan hardcode di JS) agar konsisten dengan environment.
- **`guru_fungsi` lookup**: Hanya untuk role `guru`/`wali_kelas` (id_ref = id_guru). Admin/kepala_madrasah di-skip.
- **Rate limit reset**: Harian pada 00:00 Asia/Jakarta. Nilai `0` = unlimited.
- **Markdown/XSS**: Escape HTML dulu, baru markdown terbatas. Jangan render HTML mentah dari AI.
- **Print-safe**: `@media print { #ai-chat-root { display: none !important; } }`.
- **Z-index**: Widget pakai `z-[60]` (di atas floating button existing `z-50`).
