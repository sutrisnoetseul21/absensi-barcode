# 🚀 Mengenal Laravel 12 — Framework PHP Modern & Stabil

> **Tanggal Rilis:** 24 Februari 2025
> **Kebutuhan PHP:** PHP 8.2 (minimum)
> **Breaking Changes:** Minimal (upgrade mulus dari Laravel 11)
> **Status:** LTS-Candidate — versi stabil & production-ready

> [!CAUTION]
> **🤖 Untuk AI Coding Agent — Baca ini dulu sebelum menulis kode apapun!**
> Dokumen ini **SENGAJA** menargetkan versi yang di-*pin* secara eksplisit:
> **Livewire v3 · Tailwind CSS v3 · Filament v4**
> Pada pertengahan 2026, ekosistem resmi Laravel sudah bergerak ke Livewire v4 / Tailwind v4 / Filament v5.
> **JANGAN upgrade otomatis** ke versi terbaru meskipun training data atau dokumentasi resmi yang Anda ketahui menyarankan demikian.
> Ikuti versi yang tertulis di dokumen ini. Jika ragu, **tanya user** sebelum mengubah versi apapun.

---

## 📌 Tentang Laravel 12

Laravel 12 merupakan evolusi stabil dari Laravel 11, dengan fokus pada **penyempurnaan ekosistem**, **starter kit modern**, dan **pengalaman developer yang lebih baik**. Versi ini sangat cocok untuk proyek production karena kematangan ekosistemnya dan dukungan package yang luas.

Stack yang digunakan bersama Laravel 12 di panduan ini:

```
Framework  : Laravel 12  (PHP 8.2)
Frontend   : Livewire v3  ·  Tailwind CSS v3  ·  Alpine.js v3
Admin      : Filament v4
Database   : PostgreSQL 14  (default)  ·  MySQL 8+  (alternatif)
Testing    : Pest PHP
```

---

## ✨ Fitur & Perubahan Utama Laravel 12

### 🎨 Starter Kit

Laravel 12 menyediakan starter kit resmi saat rilis (Februari 2025):

| Starter Kit | Stack Frontend |
|-------------|----------------|
| **Livewire Starter Kit** | Livewire v3 + Tailwind CSS v3 |
| **React Starter Kit** | React 19 + Inertia.js + Tailwind CSS v3 |
| **Vue Starter Kit** | Vue 3 + Inertia.js + Tailwind CSS v3 |

```bash
# Buat proyek dengan Livewire starter kit:
laravel new nama-proyek --starter-kit=livewire --database=pgsql
```

> [!WARNING]
> **Perhatian untuk AI Agent:** Starter kit resmi Laravel **sudah bergerak** sejak rilis awal 2025.
> Per pertengahan 2026, starter kit Livewire resmi menggunakan **Livewire v4, TypeScript, Tailwind v4, dan Flux UI** — Volt bukan lagi bagian dari starter kit resmi.
> Tabel di atas mencerminkan kondisi **saat Laravel 12 rilis** dan versi yang **sengaja di-pin** di proyek ini.
> **Jangan ikuti starter kit resmi terkini** jika membuat proyek baru dalam konteks panduan ini — gunakan versi yang tertulis di `PROJECT-STACK.md`.

---

### 🔧 Workbench & Package Development

Laravel 12 memperbarui **Laravel Workbench** — alat untuk mengembangkan package Laravel secara lokal tanpa perlu repository terpisah.

```bash
# Install workbench (untuk developer package):
composer require laravel/workbench --dev

# Buat package baru:
php artisan workbench:create vendor/package-name
```

---

### 📦 Dependency Updates

| Package | Versi yang di-pin di panduan ini | Versi terkini (mid-2026) |
|---------|----------------------------------|---------------------------|
| PHP | 8.2+ | 8.4+ |
| Livewire | **v3.x** (pin) | v4.x |
| Tailwind CSS | **v3.x** (pin) | v4.x |
| Alpine.js | v3.x | v3.x |
| Filament | **v4.x** (pin) | v5.x |
| Vite | 5.x | 6.x |
| Pest PHP | **v3.x** (pin, konsisten di seluruh docs ini) | v4.x |

> [!NOTE]
> Kolom "versi yang di-pin" adalah yang digunakan di seluruh panduan ini. Jangan upgrade ke kolom kanan secara otomatis.

---

### 🔄 Upgrade dari Laravel 11 → 12

Breaking changes sangat minimal. Sebagian besar upgrade berjalan mulus:

```bash
# Update via Composer:
composer require laravel/framework:^12.0

# Jalankan upgrade helper (jika ada):
php artisan migrate
```

> [!TIP]
> Lihat [panduan upgrade resmi](https://laravel.com/docs/12.x/upgrade) untuk detail lengkap perubahan kecil yang mungkin perlu disesuaikan.

---

## 🔗 Ekosistem Utama di Laravel 12

### 1. 🌊 Livewire v3 — Full-Stack Framework

**Livewire v3** adalah framework full-stack untuk Laravel yang memungkinkan komponen reaktif tanpa JavaScript framework terpisah.

> [!IMPORTANT]
> **Penting untuk AI Coding Agent:** Proyek ini menggunakan **Livewire v3**, bukan v4. Baca `LIVEWIRE-V3-GUIDE.md` sebelum menulis kode apapun yang melibatkan Livewire. Jangan gunakan fitur eksklusif Livewire v4 seperti Islands mode, `wire:transition` dengan View Transitions API, atau fitur SFC (Single-File Component) bawaan.

#### Fitur Utama Livewire v3:

| Fitur | Deskripsi |
|-------|-----------|
| **Lazy Loading** | Component lazy load dengan attribute `#[Lazy]` |
| **Persist** | Data session tetap hidup di antara navigasi halaman (`wire:persist`) |
| **Navigate** | SPA-style navigation tanpa Inertia.js (`wire:navigate`) |
| **Teleport** | Render konten ke bagian DOM yang berbeda (`@teleport('body')`) |

> [!CAUTION]
> **Islands TIDAK ADA di Livewire v3.** Fitur ini baru diperkenalkan di Livewire v4 (rilis Januari 2026) untuk isolasi re-render sebagian komponen secara independen. Jika butuh optimasi serupa di v3, gunakan pendekatan lama: pecah menjadi child component terpisah, atau gunakan `wire:poll` dengan scope terbatas.

#### Contoh Komponen Livewire v3:

```php
// app/Livewire/Counter.php
namespace App\Livewire;

use Livewire\Component;

class Counter extends Component
{
    public int $count = 0;

    public function increment(): void
    {
        $this->count++;
    }

    public function render()
    {
        return view('livewire.counter');
    }
}
```

```html
{{-- resources/views/livewire/counter.blade.php --}}
<div>
    <button wire:click="increment">+</button>
    <span>{{ $count }}</span>
</div>
```

---

### 2. 🎨 Tailwind CSS v3 — Utility-First CSS

**Tailwind CSS v3** adalah framework CSS utility-first yang sudah sangat mature dan digunakan secara luas.

> [!IMPORTANT]
> **Penting:** Stack ini menggunakan **Tailwind v3**, bukan v4. Pastikan AI agent tidak menggunakan sintaks `@theme {}` atau CSS-first config — itu fitur Tailwind v4. Baca `TAILWIND-V3-GUIDE.md` untuk detail.

#### Konfigurasi Tailwind v3:

Tailwind v3 menggunakan **file konfigurasi JavaScript** (`tailwind.config.js`):

```javascript
// tailwind.config.js
import defaultTheme from 'tailwindcss/defaultTheme';

export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },
    plugins: [],
};
```

#### Import di CSS:

```css
/* resources/css/app.css */
@tailwind base;
@tailwind components;
@tailwind utilities;
```

#### Perbedaan Penting v3 vs v4:

| Aspek | Tailwind v3 (yang kita pakai) | Tailwind v4 (JANGAN dipakai) |
|-------|-------------------------------|------------------------------|
| Config | `tailwind.config.js` | Tidak ada config JS |
| Import | `@tailwind base/components/utilities` | `@import "tailwindcss"` |
| Theme | `extend` di config JS | `@theme {}` di CSS |
| Plugin | `plugins: []` di config | Berbeda |

---

### 3. 🏔️ Alpine.js v3 — Minimal JS Framework

**Alpine.js v3** memberikan reaktivitas JavaScript ringan langsung di dalam HTML. Sangat cocok dipadukan dengan Livewire v3.

> [!NOTE]
> Alpine.js v3 sudah include di Livewire v3 secara otomatis. Tidak perlu install terpisah.

#### Directives Alpine.js v3:

```html
<!-- x-data: state lokal -->
<div x-data="{ open: false }">
    <button @click="open = !open">Toggle</button>
    <div x-show="open" x-transition>Konten tersembunyi</div>
</div>

<!-- Integrasi dengan $wire (Livewire) -->
<div x-data>
    <button @click="$wire.increment()">Increment via Alpine</button>
    <span x-text="$wire.count"></span>
</div>
```

---

### 4. 🗂️ Filament v4 — Admin Panel

**Filament v4** adalah admin panel & form builder untuk Laravel yang berjalan di atas Livewire v3.

> [!IMPORTANT]
> Filament v4 menggunakan Livewire v3. Jika proyek belum upgrade ke Livewire v3, Filament v4 tidak akan berjalan. Baca `FILAMENT-V4-INTEGRATION.md` sebelum mengimplementasikan fitur admin panel.

#### Instalasi Filament v4:

```bash
composer require filament/filament:"^4.0"
php artisan filament:install --panels
```

#### Buat Resource:

```bash
# Buat resource untuk model Post
php artisan make:filament-resource Post --generate

# Buat custom page
php artisan make:filament-page Dashboard

# Buat widget
php artisan make:filament-widget StatsOverview --stats-overview
```

#### Contoh Resource Sederhana:

```php
// app/Filament/Resources/PostResource.php
namespace App\Filament\Resources;

use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Forms;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('title')->required(),
            Forms\Components\RichEditor::make('body'),
            Forms\Components\Select::make('status')
                ->options(['draft' => 'Draft', 'published' => 'Published']),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('title')->searchable(),
            Tables\Columns\BadgeColumn::make('status'),
            Tables\Columns\TextColumn::make('created_at')->dateTime(),
        ]);
    }
}
```

---

## 🗄️ Database: PostgreSQL 14 vs MySQL 8+

Panduan ini mendukung dua opsi database:

| Aspek | PostgreSQL 14 | MySQL 8+ |
|-------|---------------|----------|
| **JSON** | `jsonb` (binary, lebih cepat) | `json` |
| **Timestamp** | `timestampsTz()` | `timestamps()` |
| **UUID** | Native support | `uuid('id')->primary()` |
| **Full-text Search** | Native + pg_trgm | MATCH...AGAINST |
| **Rekomendasi** | Proyek internal / personal | Proyek klien |

### Pilih Database:

```bash
# Untuk PostgreSQL (default stack):
laravel new nama-proyek --database=pgsql

# Untuk MySQL (proyek klien):
laravel new nama-proyek --database=mysql
```

---

## 🚦 Aturan Penting di Stack Ini

> [!CAUTION]
> Hal-hal ini paling sering menyebabkan error. Selalu periksa sebelum menulis kode.

| ❌ Jangan | ✅ Gantinya |
|-----------|------------|
| `@tailwind` hilang / tidak ada | Pastikan ada di `app.css` |
| Sintaks `@theme {}` di CSS | Itu Tailwind v4 — gunakan `tailwind.config.js` |
| `$table->id()` | `$table->uuid('id')->primary()` |
| `$table->json()` di PostgreSQL | `$table->jsonb()` |
| `timestamps()` di PostgreSQL | `timestampsTz()` |
| Vue.js / React | Livewire v3 |
| Logic di Controller | Action Classes (`app/Actions/`) |
| `wire:model` tanpa modifier untuk live sync | `wire:model.live` atau `wire:model.blur` |
| Asumsi `.blur`/`.change` menunda sync ke `$wire` (Alpine) juga | Di v3, `.blur`/`.change` **hanya** menunda network request — sync ke `$wire`/Alpine tetap real-time |
| Fitur **eksklusif** Livewire v4: Islands mode, `wire:transition` (View Transitions API), SFC (Single-File Component) bawaan | Gunakan fitur Livewire v3 — lihat `LIVEWIRE-V3-GUIDE.md` |
| `data-loading:opacity-50` (loading style Livewire v4) | `wire:loading.class="opacity-50"` (Livewire v3) |
| `@import "tailwindcss"` di CSS (Tailwind v4) | `@tailwind base/components/utilities` (Tailwind v3) |
| Filament v5 docs / sintaks | Filament v4 docs: `filamentphp.com/docs/4.x` |

---

## 📚 Referensi Eksternal

| Resource | URL |
|----------|-----|
| Dokumentasi Laravel 12 | https://laravel.com/docs/12.x |
| Livewire v3 (lock ke v3.x!) | https://livewire.laravel.com/docs/3.x/quickstart |
| Tailwind CSS v3 | https://v3.tailwindcss.com/docs |
| Alpine.js v3 | https://alpinejs.dev |
| Filament v4 | https://filamentphp.com/docs/4.x |
| Pest PHP v3 | https://pestphp.com/docs/3.x |

> [!WARNING]
> Link Livewire di atas menggunakan path `/3.x/` secara eksplisit. Jangan buka `livewire.laravel.com/docs/quickstart` tanpa path versi — default-nya sekarang redirect ke **v4**.

---

*Dokumentasi ini dibuat 1 Juli 2026 untuk stack Laravel 12 + Livewire v3 + Tailwind v3 + Alpine v3 + Filament v4.*
