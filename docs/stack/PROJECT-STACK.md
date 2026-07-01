# 📋 PROJECT STACK — Referensi Global

---

> [!IMPORTANT]
> **🤖 Untuk AI Agent (Claude Code, Cursor, GitHub Copilot, dsb.)**
> Baca dokumen ini **sebelum menulis satu baris kode pun**.
> Proyek ini punya stack & konvensi spesifik. Mengabaikan dokumen ini akan menghasilkan kode yang salah.

---

> Dokumen ini adalah **pengingat stack teknologi** yang digunakan di seluruh proyek.
> AI agent (Claude Code, Cursor, dsb.) harus membaca ini sebelum menulis kode apapun.

---

## 🧱 Tech Stack Utama

| Layer | Teknologi | Versi |
|-------|-----------|-------|
| **Framework** | Laravel | 12.x |
| **PHP** | PHP | 8.2 |
| **Database** | PostgreSQL | 14 |
| **Frontend Reaktif** | Livewire | 3.x |
| **CSS Framework** | Tailwind CSS | 3.x |
| **JS Interaktivity** | Alpine.js | 3.x |
| **Admin Panel** | Filament | 4.x |
| **Queue** | Laravel Queue (database/redis) | - |

---

## 🗄️ Database: PostgreSQL 14

- **Engine:** PostgreSQL 14 (bukan MySQL)
- Selalu gunakan tipe data PostgreSQL yang tepat: `uuid`, `jsonb`, `timestamptz`
- Gunakan `uuid` sebagai primary key secara default

```php
// Migration — gunakan uuid & PostgreSQL-specific types
Schema::create('posts', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->string('title');
    $table->jsonb('meta')->default('{}');    // jsonb, bukan json
    $table->timestampsTz();                  // timestamptz, bukan timestamps
    $table->softDeletes();
});
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
├── Ai/               ← AI agents di sini
│   └── Agents/
│       └── SupportAssistant.php
├── Http/
│   └── Controllers/  ← Hanya memanggil Action
├── Livewire/         ← Komponen Livewire
├── Models/           ← Eloquent models
└── Policies/         ← Authorization
```

### Model
- Semua model gunakan `uuid` primary key
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

- [ ] Sudah baca file `PROJECT-STACK.md` ini?
- [ ] Sudah cek schema database yang ada?
- [ ] Sudah cek route yang sudah ada?
- [ ] Sudah tahu struktur folder proyek ini?
- [ ] Yakin menggunakan PostgreSQL 14, bukan MySQL?
- [ ] Yakin menggunakan Livewire v3, bukan Vue/React?
- [ ] Yakin tidak akan menulis logic di Controller (gunakan Action)?

---

### ✅ Yang BOLEH Dilakukan

| Teknologi | Keterangan |
|-----------|------------|
| **PostgreSQL 14** | Satu-satunya database yang digunakan |
| **Livewire v3** | Untuk semua komponen interaktif |
| **Tailwind CSS v3** | Untuk semua styling (pakai `tailwind.config.js`) |
| **Alpine.js v3** | Untuk UI state ringan di sisi client |
| **Filament v4** | Untuk admin panel |
| **Action Classes** | Untuk semua business logic |
| **UUID primary key** | Untuk semua tabel/model baru |
| **Pest PHP** | Untuk semua pengujian |
| **`jsonb`** | Untuk kolom JSON di PostgreSQL |
| **`timestampsTz()`** | Untuk kolom timestamp di PostgreSQL |
| **`composer run dev`** | Untuk menjalankan dev environment |

---

### ❌ Yang DILARANG Dilakukan

| Yang Dilarang | Gantinya Dengan |
|---------------|-----------------|
| MySQL / `$table->timestamps()` biasa | PostgreSQL / `$table->timestampsTz()` |
| Vue.js / React | Livewire v3 |
| Livewire v2 syntax (`$rules`, `$listeners`) | Livewire v3 Attributes (`#[Validate]`, `#[On]`) |
| jQuery | Alpine.js |
| Bootstrap | Tailwind CSS v3 |
| Logic di Controller | Action Classes |
| `$table->id()` (auto-increment) | `$table->uuid('id')->primary()` |
| Custom CSS inline | Tailwind utility classes |
| `json` column type di PostgreSQL | `jsonb` column type |
| PHPUnit langsung | Pest PHP |
| Hapus `tailwind.config.js` | Pertahankan — **wajib ada** di v3 |
| Tulis `@import "tailwindcss"` di CSS | `@tailwind base/components/utilities` |
| Tulis `@theme {}` di CSS | Gunakan `theme.extend` di `tailwind.config.js` |
| `php artisan serve` + `npm run dev` terpisah | `composer run dev` |
| `wire:model` tanpa modifier untuk live sync | `wire:model.live` |
| Filament v5 docs/syntax | Filament v4 docs |

---

### ⚠️ Kesalahan Umum yang Harus Dihindari

1. **Jangan buat komponen Vue/React** — proyek ini 100% Livewire v3.
2. **Jangan pakai `$table->id()`** — selalu `$table->uuid('id')->primary()`.
3. **Jangan taruh query Eloquent di dalam Controller** — buat Action class dulu.
4. **Jangan hardcode string di Blade** — gunakan translation `__('key')` jika ada.
5. **Jangan asumsi driver database** — selalu PostgreSQL 14, syntax migration bisa berbeda dari MySQL.
6. **Jangan pakai Livewire v2 syntax** (`$rules`, `$listeners` property) — gunakan PHP Attributes.
7. **Jangan hapus `tailwind.config.js`** — file ini wajib ada di Tailwind v3.
8. **Jangan tulis `@import "tailwindcss"`** di CSS — itu sintaks Tailwind v4.
9. **Jangan gunakan `wire:model` tanpa modifier untuk live sync** — gunakan `wire:model.live`.
10. **Jangan assume default database adalah PostgreSQL** — default bawaan Laravel 12 adalah SQLite; harus dikonfigurasi manual.

---

### 🔍 Cara Eksplorasi Proyek

Sebelum menulis kode baru, gunakan perintah ini untuk memahami proyek:

```bash
# Cek schema database:
php artisan db:show
php artisan migrate:status

# Cek routes yang ada:
php artisan route:list

# Cek list model:
php artisan model:show NamaModel

# Tinker untuk coba query:
php artisan tinker
```

---

## 🗄️ PostgreSQL 14 — Hal-Hal yang Sering Salah

PostgreSQL berbeda dari MySQL di beberapa hal penting. AI agent **wajib tahu** ini:

### Tipe Kolom yang Berbeda

| Situasi | MySQL (❌ jangan) | PostgreSQL 14 (✅ gunakan) |
|---------|-------------------|---------------------------|
| JSON data | `json` | `jsonb` |
| Timestamp dengan timezone | `timestamps()` | `timestampsTz()` |
| Primary key | `id()` | `uuid('id')->primary()` |
| Teks panjang | `longText()` | `text()` (tidak ada batas) |
| Boolean | `tinyint(1)` | `boolean()` |
| IP Address | `string` | `ipAddress()` |

### Query Khusus PostgreSQL

```php
// Full-text search (PostgreSQL native)
Post::whereRaw("to_tsvector('indonesian', title) @@ plainsearch('indonesian', ?)", [$keyword]);

// JSONB query
User::where('meta->verified', true)->get();
User::whereJsonContains('settings->roles', 'admin')->get();

// JSONB — set value
User::where('id', $id)->update(['meta->verified' => true]);
```

### Aktifkan Ekstensi yang Dibutuhkan

```php
// Migration untuk aktifkan ekstensi PostgreSQL 14
public function up(): void
{
    DB::statement('CREATE EXTENSION IF NOT EXISTS "uuid-ossp"');
    DB::statement('CREATE EXTENSION IF NOT EXISTS pg_trgm'); // untuk fuzzy search
}
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

## ⚡ Pola Livewire v3 + Alpine.js yang Benar

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

### Pola 4: Infinite Scroll / Load More

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

```blade
{{-- Blade --}}
@foreach ($this->posts as $post)
    <x-post-card :post="$post" />
@endforeach

{{-- Livewire v3: gunakan wire:loading untuk styling --}}
<button
    wire:click="loadMore"
    wire:loading.attr="disabled"
    wire:loading.class="opacity-50 cursor-not-allowed"
    class="btn-primary"
>
    <span wire:loading.remove>Muat Lebih Banyak</span>
    <span wire:loading>Memuat...</span>
</button>
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
PostgreSQL
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
PostgreSQL
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

# Database — POSTGRESQL 14, bukan MySQL!
# (Default Laravel 12 = SQLite, tapi stack ini = PostgreSQL 14)
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=nama_database
DB_USERNAME=postgres
DB_PASSWORD=

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
        "livewire/livewire": "^3.5",
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
- Gunakan `RefreshDatabase` trait di setiap test yang butuh database
- Database test: gunakan **PostgreSQL** juga (bukan SQLite), agar behaviour konsisten

```php
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

*Dokumen ini diperbarui 1 Juli 2026 untuk stack Laravel 12 + Livewire v3 + Tailwind v3 + Alpine v3 + Filament v4.*
*Update jika ada perubahan stack.*
