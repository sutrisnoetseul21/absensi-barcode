# 📋 PROJECT STACK — Referensi Global (Versi MySQL)

---

> [!IMPORTANT]
> **🤖 Untuk AI Agent (Claude Code, Cursor, GitHub Copilot, dsb.)**
> Baca dokumen ini **sebelum menulis satu baris kode pun**.
> Proyek ini menggunakan **MySQL**, bukan PostgreSQL.
> Konvensi tipe data, query, dan migration **berbeda** dari versi PostgreSQL.

> [!WARNING]
> **⚠️ Ini adalah versi MySQL dari PROJECT-STACK.md**
> Jika kamu melihat ada file `PROJECT-STACK.md` (PostgreSQL) di proyek ini, **abaikan** dan gunakan file ini sebagai acuan.
> Database proyek ini: **MySQL / MariaDB**, bukan PostgreSQL.

> [!TIP]
> **💡 Cara Daftarkan sebagai Laravel Boost Guideline:**
> Agar AI agent otomatis membaca dokumen ini di setiap sesi, daftarkan sebagai Guideline di Boost:
> ```bash
> php artisan boost:guideline add docs/PROJECT-STACK-MYSQL.md
> ```
> Setelah itu, isi file akan masuk ke `CLAUDE.md` / `AGENTS.md` di root proyek dan
> **AI agent tidak akan pernah lupa** stack proyekmu!

---

> Dokumen ini adalah **pengingat stack teknologi** yang digunakan di proyek klien ini.
> AI agent (Claude Code, Cursor, dsb.) harus membaca ini sebelum menulis kode apapun.

---

## 🧱 Tech Stack Utama

| Layer | Teknologi | Versi |
|-------|-----------|-------|
| **Framework** | Laravel | 12.x |
| **PHP** | PHP | 8.2 |
| **Database** | MySQL | 8.0+ / MariaDB 10.6+ |
| **Frontend Reaktif** | Livewire | 3.x |
| **CSS Framework** | Tailwind CSS | 3.x |
| **JS Interaktivity** | Alpine.js | 3.x |
| **Admin Panel** | Filament | 4.x |
| **Queue** | Laravel Queue (database/redis) | - |

---

## 🗄️ Database: MySQL

- **Engine:** MySQL 8.0+ atau MariaDB 10.6+
- **Charset:** Selalu gunakan `utf8mb4` dan collation `utf8mb4_unicode_ci`
- **Primary Key:** Gunakan `uuid` secara default (bukan auto-increment)
- **Storage Engine:** InnoDB (default, jangan ganti)
- **JSON:** Gunakan tipe `json` (bukan `jsonb` — itu khusus PostgreSQL)

```php
// Migration — gunakan uuid & MySQL-compatible types
Schema::create('posts', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->string('title');
    $table->json('meta')->nullable();   // json biasa, bukan jsonb
    $table->timestamps();               // timestamps() sudah cukup di MySQL
    $table->softDeletes();
});
```

### Koneksi di `.env`

```env
# Database — MYSQL, bukan PostgreSQL!
# (Default Laravel 12 = SQLite, tapi proyek ini = MySQL)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nama_database
DB_USERNAME=root
DB_PASSWORD=
DB_CHARSET=utf8mb4
DB_COLLATION=utf8mb4_unicode_ci
```

---

## 🎨 Frontend: Tailwind CSS v3

- **Jangan** gunakan class inline style jika bisa dengan Tailwind
- Gunakan `@apply` hanya di komponen yang dipakai berulang
- Dark mode menggunakan strategi `class` via `tailwind.config.js`
- Breakpoint standar: `sm`, `md`, `lg`, `xl`, `2xl`
- **Tailwind v3 WAJIB pakai `tailwind.config.js`** — JANGAN hapus file ini
- Entry CSS: `@tailwind base/components/utilities` (bukan `@import "tailwindcss"`)

```html
<!-- Contoh pola yang digunakan -->
<div class="flex items-center gap-4 rounded-xl bg-white p-6 shadow-sm dark:bg-zinc-900">
    <h2 class="text-lg font-semibold text-zinc-800 dark:text-zinc-100">Judul</h2>
</div>
```

---

## ⚡ Frontend Reaktif: Livewire v3

- **Semua** komponen interaktif menggunakan Livewire v3 (bukan Vue/React)
- Gunakan `#[Computed]` attribute untuk computed properties
- Gunakan `#[Validate]` attribute untuk validasi inline
- Gunakan `wire:model.live` untuk live sync (default `wire:model` = deferred di v3)
- Detail lengkap: lihat [`docs/LIVEWIRE-V3-GUIDE.md`](LIVEWIRE-V3-GUIDE.md)

```php
// Contoh komponen Livewire v3 (class + view terpisah)
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use Livewire\Component;

class PostForm extends Component
{
    #[Validate('required|min:3')]
    public string $title = '';

    #[Validate('required')]
    public string $body = '';

    #[Computed]
    public function categories(): Collection
    {
        return Category::orderBy('name')->get();
    }

    public function save(): void
    {
        $this->validate();
        Post::create($this->only(['title', 'body']));
        $this->dispatch('post-created');
    }

    public function render()
    {
        return view('livewire.post-form');
    }
}
```

---

## 🏔️ Interaktivitas: Alpine.js 3

- Gunakan Alpine.js untuk interaksi UI **ringan** yang tidak butuh server round-trip
- Livewire untuk data server, Alpine untuk UI state (toggle, dropdown, modal)
- Keduanya bisa dikombinasikan: `wire:model` + `x-data`

```html
<!-- Contoh kombinasi Alpine + Livewire -->
<div x-data="{ open: false }">
    <button @click="open = !open" class="btn">Toggle</button>

    <div x-show="open" x-transition>
        <input wire:model.live="search" type="text" placeholder="Cari...">
    </div>
</div>
```

---

## 🏗️ Konvensi Kode

### ✂️ Batasan Ukuran File & Modularitas (Anti Fat-Files)
> [!CAUTION]
> **ATURAN WAJIB (MUTLAK):** Dilarang keras membuat file (Controller, View, atau Class) yang berisi ratusan hingga ribuan baris kode! File raksasa (fat-files) sangat tidak efisien, rentan bug, dan sulit dipahami AI.

1. **Views (Blade):** Wajib dipecah menjadi komponen atau *partial views* yang lebih kecil.
   - Contoh: Jangan gabungkan header, hero, konten utama, dan footer di satu file `index.blade.php`.
   - Pecah menjadi: `@include('partials.header')`, `@include('partials.hero')`, `@include('partials.footer')`, dsb.
2. **Controllers & Livewire:** Harus sangat ringkas (Skinny Controllers). 
   - Pindahkan logika bisnis ke **Action Classes** (`app/Actions`).
   - Jika sebuah komponen Livewire terlalu kompleks, wajib dipecah menjadi *parent-child* component.
3. **Maksimal Baris:** Jika sebuah file mulai mendekati **250 baris**, itu adalah tanda pasti bahwa file tersebut HARUS segera dipecah/di-refactor menjadi modul terpisah.

### Structure & Arsitektur
- Gunakan **Action classes** untuk business logic (bukan fat controller)
- Satu Action = satu tanggung jawab
- Controller hanya boleh memanggil Action, bukan business logic langsung

```
app/
├── Actions/          ← Business logic di sini
│   ├── Posts/
│   │   ├── CreatePost.php
│   │   └── DeletePost.php
├── Ai/               ← AI agents di sini (jika butuh fitur AI)
│   └── Agents/
├── Http/
│   └── Controllers/  ← Hanya memanggil Action
├── Livewire/         ← Komponen Livewire
├── Models/           ← Eloquent models
└── Policies/         ← Authorization
```

### Model
- Semua model gunakan `uuid` primary key dengan trait `HasUuids`
- Gunakan `$fillable` atau `$guarded` (gaya tradisional — cukup dan jelas)
- Gunakan soft deletes (`SoftDeletes`) untuk data penting

### Naming Convention
- Controller: `PostController`, `UserController` (singular)
- Action: `CreatePost`, `UpdateUserProfile` (verb + noun)
- Livewire Component: `PostForm`, `PostTable`, `UserProfile`
- Event: `PostCreated`, `UserRegistered` (past tense)
- Job: `ProcessPodcast`, `SendWelcomeEmail` (imperative)

---

## 🤖 Panduan Lengkap untuk AI Agent

### 🚀 Memulai Sesi Baru

Di awal setiap sesi, AI agent harus:

1. **Baca file ini** sebelum menulis kode apapun
2. **Cek schema DB:** `php artisan db:show` atau `php artisan migrate:status`
3. **Cek routes yang ada:** `php artisan route:list`
4. **Jalankan dev environment** dengan: `composer run dev`

### 🖥️ Menjalankan Dev Environment

```bash
# CARA RESMI — jalankan semua sekaligus:
composer run dev

# Ini menjalankan:
# - PHP dev server (localhost:8000)
# - Queue worker
# - Vite dev server (hot reload)

# JANGAN gunakan cara lama ini:
# php artisan serve   ← jangan terpisah
# npm run dev         ← jangan terpisah
```

### ✅ Checklist WAJIB Sebelum Eksekusi

Sebelum menulis kode, pastikan kamu sudah menjawab semua ini:

- [ ] Sudah baca file `PROJECT-STACK-MYSQL.md` ini?
- [ ] Sudah cek schema database yang ada?
- [ ] Sudah cek route yang sudah ada?
- [ ] Sudah tahu struktur folder proyek ini?
- [ ] Yakin menggunakan **MySQL**, bukan PostgreSQL?
- [ ] Yakin **tidak** pakai `jsonb`, `timestampsTz`, atau syntax PostgreSQL lainnya?
- [ ] Yakin menggunakan Livewire v3, bukan Vue/React?
- [ ] Yakin tidak akan menulis logic di Controller (gunakan Action)?

---

### ✅ Yang BOLEH Dilakukan

| Teknologi | Keterangan |
|-----------|------------|
| **MySQL 8+ / MariaDB** | Satu-satunya database yang digunakan |
| **`json`** | Untuk kolom JSON di MySQL |
| **`timestamps()`** | Untuk kolom timestamp di MySQL |
| **`$table->uuid('id')->primary()`** | Primary key UUID |
| **`HasUuids` trait** | Wajib di model untuk UUID di MySQL |
| **Livewire v3** | Untuk semua komponen interaktif |
| **Tailwind CSS v3** | Untuk semua styling (pakai `tailwind.config.js`) |
| **Alpine.js v3** | Untuk UI state ringan di sisi client |
| **Filament v4** | Untuk admin panel |
| **Action Classes** | Untuk semua business logic |
| **Pest PHP** | Untuk semua pengujian |
| **`utf8mb4`** | Charset wajib untuk MySQL |
| **`composer run dev`** | Untuk menjalankan dev environment |

---

### ❌ Yang DILARANG Dilakukan

| Yang Dilarang | Gantinya Dengan |
|---------------|-----------------|
| `jsonb` column type | `json` column type (MySQL) |
| `timestampsTz()` | `timestamps()` (MySQL) |
| `DB::statement('CREATE EXTENSION ...')` | Tidak perlu — MySQL tidak pakai ekstensi |
| `text()->array()` | Tidak ada tipe array di MySQL — gunakan `json` |
| Query PostgreSQL (`@@`, `to_tsvector`) | MySQL FULLTEXT search (`MATCH AGAINST`) |
| `DB_PORT=5432` | `DB_PORT=3306` |
| `DB_CONNECTION=pgsql` | `DB_CONNECTION=mysql` |
| Vue.js / React | Livewire v3 |
| Livewire v2 syntax (`$rules`, `$listeners`) | Livewire v3 Attributes (`#[Validate]`, `#[On]`) |
| jQuery | Alpine.js |
| Bootstrap | Tailwind CSS v3 |
| Logic di Controller | Action Classes |
| `$table->id()` (auto-increment) | `$table->uuid('id')->primary()` |
| Custom CSS inline | Tailwind utility classes |
| PHPUnit langsung | Pest PHP |
| Hapus `tailwind.config.js` | Pertahankan — **wajib ada** di v3 |
| Tulis `@import "tailwindcss"` di CSS | `@tailwind base/components/utilities` |
| Tulis `@theme {}` di CSS | Gunakan `theme.extend` di `tailwind.config.js` |
| `php artisan serve` + `npm run dev` terpisah | `composer run dev` |
| `wire:model` tanpa modifier untuk live sync | `wire:model.live` |
| Filament v5 docs/syntax | Filament v4 docs |

---

### ⚠️ Kesalahan Umum yang Harus Dihindari

1. **Jangan pakai `jsonb`** — MySQL hanya mengenal `json`, bukan `jsonb`.
2. **Jangan pakai `timestampsTz()`** — di MySQL cukup `timestamps()`.
3. **Jangan buat komponen Vue/React** — proyek ini 100% Livewire v3.
4. **Jangan pakai `$table->id()`** — selalu `$table->uuid('id')->primary()`.
5. **Jangan lupa `HasUuids` trait di model** — MySQL tidak native UUID, Laravel butuh trait ini.
6. **Jangan taruh query Eloquent di dalam Controller** — buat Action class dulu.
7. **Jangan lupa charset** — pastikan `DB_CHARSET=utf8mb4` di `.env`.
8. **Jangan pakai Livewire v2 syntax** (`$rules`, `$listeners`) — gunakan PHP Attributes.
9. **Jangan gunakan PostgreSQL-specific function** seperti `pg_trgm`, `plainsearch`, atau `<->` operator.
10. **Jangan hapus `tailwind.config.js`** — file ini wajib ada di Tailwind v3.
11. **Jangan assume default database adalah MySQL** — default bawaan Laravel 12 adalah SQLite; harus dikonfigurasi manual.

---

### 🔍 Cara Eksplorasi Proyek

Sebelum menulis kode baru, gunakan perintah ini untuk memahami proyek:

```bash
# Cek schema database:
php artisan db:show
php artisan migrate:status

# Cek routes:
php artisan route:list

# Tinker:
php artisan tinker
```

---

## 🗄️ MySQL — Hal-Hal yang Perlu Diperhatikan

### Tipe Kolom yang Tepat di MySQL

| Situasi | ✅ Gunakan di MySQL | ❌ Jangan (khusus PostgreSQL) |
|---------|---------------------|-------------------------------|
| JSON data | `json` | `jsonb` |
| Timestamp | `timestamps()` | `timestampsTz()` |
| Teks panjang | `longText()` | `text()` (berbeda behaviour) |
| Boolean | `boolean()` | - |
| UUID | `uuid('id')->primary()` | - |
| Full-text index | `fullText('column')` | `pg_trgm` / `tsvector` |
| Vector/embedding | Butuh package eksternal | Native via `pgvector` |

### UUID di MySQL — Wajib Pakai `HasUuids` Trait

MySQL tidak memiliki tipe kolom `uuid` native (berbeda dari PostgreSQL). Laravel handle ini secara otomatis, tapi pastikan:

```php
// Migration yang benar untuk UUID di MySQL
$table->uuid('id')->primary();   // Laravel otomatis pakai CHAR(36) di MySQL
$table->uuid('user_id');         // Foreign key UUID

// Model: WAJIB tambahkan trait HasUuids
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Post extends Model
{
    use HasUuids; // ← Wajib di MySQL! Tanpa ini UUID tidak di-generate otomatis
}
```

### Query JSON di MySQL

```php
// MySQL mendukung JSON query via Eloquent
User::where('meta->verified', true)->get();
User::whereJsonContains('settings->roles', 'admin')->get();

// JSON extract langsung
User::whereRaw("JSON_EXTRACT(meta, '$.verified') = true")->get();
```

### Full-text Search di MySQL

```php
// MySQL FULLTEXT search (berbeda dari PostgreSQL tsvector)
Post::whereFullText(['title', 'body'], $keyword)->get();

// Atau dengan mode boolean
Post::whereRaw("MATCH(title, body) AGAINST (? IN BOOLEAN MODE)", ['+' . $keyword])->get();
```

### Migration: Tambahkan FULLTEXT Index

```php
Schema::table('posts', function (Blueprint $table) {
    $table->fullText(['title', 'body']); // untuk MySQL FULLTEXT search
});
```

---

## 🖼️ Blade & View — Konvensi Proyek

### Struktur View yang Digunakan

```
resources/views/
├── layouts/
│   ├── app.blade.php        ← Layout utama (authenticated)
│   └── guest.blade.php      ← Layout untuk halaman publik
├── components/
│   └── ui/                  ← Komponen UI reusable (button, card, badge)
├── livewire/                ← View untuk Livewire components
│   └── posts/
│       ├── form.blade.php
│       └── table.blade.php
└── pages/                   ← Halaman statis / non-Livewire
```

### Pola Blade yang Digunakan

```blade
{{-- Komponen UI reusable --}}
<x-ui.button type="submit" variant="primary">Simpan</x-ui.button>
<x-ui.card class="p-6">...</x-ui.card>

{{-- Layout dengan slot --}}
<x-layouts.app :title="'Daftar Post'">
    {{-- konten --}}
</x-layouts.app>

{{-- Jangan pakai @include untuk komponen — pakai <x-component> --}}
{{-- ❌ @include('partials.button') --}}
{{-- ✅ <x-ui.button> --}}
```

---

## ⚡ Pola Livewire 4 + Alpine.js yang Benar

### Pola 1: Form dengan Validasi

```php
// Livewire Component
#[Validate('required|min:3')]
public string $title = '';

#[Validate('required|email')]
public string $email = '';

public function save(): void
{
    $this->validate(); // ← Validasi semua sekaligus
    // ...
}
```

### Pola 2: Real-time Search (Debounce)

```blade
{{-- Blade: debounce 300ms untuk hemat request --}}
<input wire:model.live.debounce.300ms="search" type="text">

{{-- Livewire Component --}}
#[Computed]
public function results(): Collection
{
    return Post::where('title', 'like', "%{$this->search}%")->get();
}
```

### Pola 3: Modal dengan Alpine + Livewire

```blade
<div x-data="{ showModal: false }">

    {{-- Trigger --}}
    <button @click="showModal = true" class="btn-primary">Tambah</button>

    {{-- Modal --}}
    <div x-show="showModal" x-trap="showModal" @keydown.escape="showModal = false">
        <div @click.outside="showModal = false" class="modal-box">
            <livewire:posts.form @saved="showModal = false" />
        </div>
    </div>
</div>
```

### Pola 4: Infinite Scroll / Lazy Load

```php
// Livewire Component
public int $perPage = 10;

#[Computed]
public function posts(): LengthAwarePaginator
{
    return Post::latest()->paginate($this->perPage);
}

public function loadMore(): void
{
    $this->perPage += 10;
}
```

---

## 🔄 Alur Request di Proyek Ini

```
HTTP Request
     ↓
Route (routes/web.php atau routes/api.php)
     ↓
Middleware (auth, throttle, dsb.)
     ↓
Controller (TIPIS — hanya terima request & kembalikan response)
     ↓
Action Class (TEBAL — semua business logic di sini)
     ↓
Model / Repository
     ↓
MySQL
     ↓
Response (View Blade / JSON / Livewire)
```

**Untuk Livewire:**
```
User Interaction (klik, input)
     ↓
Alpine.js (jika hanya UI state) ← berhenti di sini jika tidak butuh server
     ↓
Livewire Component (wire:click, wire:model)
     ↓
Action Class (jika butuh business logic)
     ↓
MySQL
     ↓
Re-render komponen Livewire
```

---

## ⚙️ Environment & Config — Pengingat

### File `.env` yang Wajib Ada

```env
# App
APP_NAME="Nama Proyek"
APP_ENV=local
APP_KEY=base64:...
APP_URL=http://localhost

# Database — MYSQL, bukan PostgreSQL!
# (Default Laravel 12 = SQLite, tapi proyek ini = MySQL)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nama_database
DB_USERNAME=root
DB_PASSWORD=
DB_CHARSET=utf8mb4
DB_COLLATION=utf8mb4_unicode_ci

# Queue
QUEUE_CONNECTION=database

# Broadcasting (Reverb) — opsional
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=
REVERB_APP_KEY=
REVERB_APP_SECRET=
```

### Jangan Hardcode Konfigurasi

```php
// ❌ Jangan
$key = 'sk-ant-xxxxx';

// ✅ Gunakan config/env
$key = config('ai.providers.anthropic.key');
```

---

## 📦 Package yang Biasa Dipakai

```json
{
    "require": {
        "php": "^8.2",
        "laravel/framework": "^12.0",
        "livewire/livewire": "^3.0",
        "filament/filament": "^4.0"
    },
    "require-dev": {
        "pestphp/pest": "^3.0",
        "pestphp/pest-plugin-laravel": "^3.0",
        "pestphp/pest-plugin-livewire": "^3.0",
        "barryvdh/laravel-debugbar": "^3.0",
        "barryvdh/laravel-ide-helper": "^3.0"
    }
}
```

---

## 🧪 Testing

- Gunakan **Pest PHP** (bukan PHPUnit langsung)
- Test Livewire component dengan `pest-plugin-livewire`
- AI SDK: gunakan `Ai::fake()` agar tidak memanggil API nyata saat test
- Gunakan `RefreshDatabase` trait di setiap test yang butuh database
- Database test: bisa pakai **MySQL** atau **SQLite** (`:memory:`) untuk kecepatan

```php
// Untuk test dengan SQLite in-memory (lebih cepat)
// Edit phpunit.xml:
// <env name="DB_CONNECTION" value="sqlite"/>
// <env name="DB_DATABASE" value=":memory:"/>

// Contoh test dengan Pest + Livewire
uses(RefreshDatabase::class);

it('can create a post', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(PostForm::class)
        ->set('title', 'Hello World')
        ->set('body', 'Ini konten post.')
        ->call('save')
        ->assertDispatched('post-created');

    expect(Post::count())->toBe(1);
});

// Test validasi
it('requires title to create a post', function () {
    Livewire::actingAs(User::factory()->create())
        ->test(PostForm::class)
        ->set('title', '')
        ->call('save')
        ->assertHasErrors(['title' => 'required']);
});
```

---

## 🔀 Perbandingan Cepat: MySQL vs PostgreSQL (untuk referensi)

| Hal | MySQL (file ini) | PostgreSQL (PROJECT-STACK.md) |
|-----|-----------------|-------------------------------|
| `DB_CONNECTION` | `mysql` | `pgsql` |
| `DB_PORT` | `3306` | `5432` |
| JSON column | `json` | `jsonb` |
| Timestamp | `timestamps()` | `timestampsTz()` |
| Full-text search | `whereFullText()` / `MATCH AGAINST` | `tsvector` / `plainsearch` |
| UUID native | Tidak (CHAR 36 + `HasUuids` trait) | Ya (native UUID type) |
| Array type | Tidak ada (gunakan `json`) | Ya (native array) |
| Extensions | Tidak ada | `pg_trgm`, dsb. |
| Test DB | SQLite bisa dipakai | Sebaiknya PostgreSQL juga |
| Default Laravel 12 | SQLite (harus dikonfigurasi) | SQLite (harus dikonfigurasi) |

---

*Dokumen ini diperbarui 1 Juli 2026 untuk stack Laravel 12 + Livewire v3 + Tailwind v3 + Alpine v3 + Filament v4 — Versi MySQL untuk kebutuhan proyek klien.*
*Untuk proyek dengan PostgreSQL, gunakan `PROJECT-STACK.md`.*
