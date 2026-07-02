# 🚀 Laravel 12 — Panduan Developer & AI Agent

> Dokumentasi ini adalah **pintu masuk utama** sebelum memulai atau melanjutkan proyek Laravel 12.
> Dirancang agar AI agent dapat membaca README ini saja, lalu tahu persis file mana yang harus dibuka selanjutnya.

---

## 🤖 UNTUK AI AGENT — BACA INI DULU

> [!IMPORTANT]
> Jika kamu adalah **AI coding agent** (Claude Code, Cursor, GitHub Copilot, Windsurf, dsb.):
>
> **LANGKAH 1 — Tentukan database proyek ini:**
> - Proyek pakai **PostgreSQL** → baca [`docs/stack/PROJECT-STACK.md`](docs/stack/PROJECT-STACK.md)
> - Proyek pakai **MySQL** → baca [`docs/stack/PROJECT-STACK-MYSQL.md`](docs/stack/PROJECT-STACK-MYSQL.md)
>
> **LANGKAH 1.5 — Pelajari rancangan (blueprint) proyek:**
> - Buka dan baca file-file di folder [`docs/blueprint/`](docs/blueprint/) untuk memahami fitur, database, dan alur aplikasi.
>
> **LANGKAH 2 — WAJIB baca panduan TALL Stack ini SEBELUM menulis kode apapun:**
> Berlaku untuk semua task. Livewire, Tailwind, dan Alpine punya sintaks spesifik versi — training data AI mungkin mengandung sintaks lama yang menghasilkan bug.
> - [`docs/stack/LIVEWIRE-V3-GUIDE.md`](docs/stack/LIVEWIRE-V3-GUIDE.md) — Komponen, wire:model, lazy loading
> - [`docs/stack/TAILWIND-V3-GUIDE.md`](docs/stack/TAILWIND-V3-GUIDE.md) — tailwind.config.js, @apply, utility classes
> - [`docs/stack/ALPINE-V3-GUIDE.md`](docs/stack/ALPINE-V3-GUIDE.md) — Hindari pola v2, integrasi $wire
> - [`docs/stack/INTEGRATION-EXAMPLE.md`](docs/stack/INTEGRATION-EXAMPLE.md) — Rosetta Stone: lihat contoh kode nyata integrasi ketiganya
>
> **LANGKAH 3 — Sesuaikan dengan task:**
> - Mau memulai proyek baru → lihat bagian **Mulai Cepat** di bawah
> - Mau setup environment → baca [`docs/stack/CHECKLIST-PERSIAPAN.md`](docs/stack/CHECKLIST-PERSIAPAN.md)
> - Mau tahu fitur baru Laravel 12 → baca [`docs/stack/mengenal-laravel-12.md`](docs/stack/mengenal-laravel-12.md)

> [!TIP]
> **Dokumentasi resmi Laravel 12:**
> ```
> https://laravel.com/docs/12.x
> ```

---

## 🗺️ Peta Navigasi — "Butuh Apa, Buka File Mana"

> [!IMPORTANT]
> **SOP Pembacaan Stack — WAJIB SETIAP SESI:** Setiap kali Anda akan menulis atau memodifikasi kode frontend, **MUTLAK** baca 3 dokumen ini bersamaan: `LIVEWIRE-V3-GUIDE` (kerangka utama), `ALPINE-V3-GUIDE` (interaktivitas klien), dan `TAILWIND-V3-GUIDE` (styling). Lihat implementasi konkretnya di `docs/stack/INTEGRATION-EXAMPLE.md`.

| Kebutuhan | File yang Dibuka |
|-----------|-----------------|
| Memahami fitur, alur pengguna, dan database | Folder [`docs/blueprint/`](docs/blueprint/) |
| Aturan penulisan komponen & larangan di **Livewire v3** | [`docs/stack/LIVEWIRE-V3-GUIDE.md`](docs/stack/LIVEWIRE-V3-GUIDE.md) |
| Aturan styling di **Tailwind CSS v3** (config JS, @apply) | [`docs/stack/TAILWIND-V3-GUIDE.md`](docs/stack/TAILWIND-V3-GUIDE.md) |
| Aturan integrasi **Alpine.js v3** dengan Livewire v3 | [`docs/stack/ALPINE-V3-GUIDE.md`](docs/stack/ALPINE-V3-GUIDE.md) |
| **CONTOH KODE NYATA** penggabungan Livewire, Tailwind, Alpine | [`docs/stack/INTEGRATION-EXAMPLE.md`](docs/stack/INTEGRATION-EXAMPLE.md) |
| Integrasi **Filament v4** dengan Livewire v3 | [`docs/stack/FILAMENT-V4-INTEGRATION.md`](docs/stack/FILAMENT-V4-INTEGRATION.md) |
| Aturan teknologi, konvensi kode, hal boleh/dilarang (**PostgreSQL 14**) | [`docs/stack/PROJECT-STACK.md`](docs/stack/PROJECT-STACK.md) |
| Aturan teknologi, konvensi kode, hal boleh/dilarang (**MySQL 8+**) | [`docs/stack/PROJECT-STACK-MYSQL.md`](docs/stack/PROJECT-STACK-MYSQL.md) |
| Setup environment dari nol (install PHP, DB, dll) | [`docs/stack/CHECKLIST-PERSIAPAN.md`](docs/stack/CHECKLIST-PERSIAPAN.md) |
| Memahami fitur-fitur di **Laravel 12** | [`docs/stack/mengenal-laravel-12.md`](docs/stack/mengenal-laravel-12.md) |
| Pattern Action Class (business logic) | [`docs/stack/PROJECT-STACK.md`](docs/stack/PROJECT-STACK.md) — bagian **Konvensi Kode** |
| Migration PostgreSQL (`jsonb`, `timestampsTz`) | [`docs/stack/PROJECT-STACK.md`](docs/stack/PROJECT-STACK.md) — bagian **PostgreSQL** |
| Migration MySQL (`json`, `timestamps`, `HasUuids`) | [`docs/stack/PROJECT-STACK-MYSQL.md`](docs/stack/PROJECT-STACK-MYSQL.md) — bagian **MySQL** |
| Menjalankan dev environment | Baca bagian **Mulai Cepat** di bawah |
| Deploy ke production | Baca bagian **Deployment** di bawah |

---

## 📂 Semua File Dokumentasi

| File | Isi Utama |
|------|-----------|
| [`README-DOCS.md`](README-DOCS.md) ← **kamu di sini** | Peta navigasi, mulai cepat, deployment |
| [`docs/blueprint/`](docs/blueprint/) | Kumpulan template rancangan proyek (PRD) |
| [`docs/progres-aplikasi-absensi/`](docs/progres-aplikasi-absensi/) | Panduan eksekusi harian dan checklist setiap tahap progres |
| [`docs/stack/LIVEWIRE-V3-GUIDE.md`](docs/stack/LIVEWIRE-V3-GUIDE.md) | Panduan Livewire v3 (Komponen, wire:model, lazy) |
| [`docs/stack/TAILWIND-V3-GUIDE.md`](docs/stack/TAILWIND-V3-GUIDE.md) | Panduan Tailwind CSS v3 (tailwind.config.js, @apply) |
| [`docs/stack/ALPINE-V3-GUIDE.md`](docs/stack/ALPINE-V3-GUIDE.md) | Panduan Alpine.js v3 (directives, integrasi $wire) |
| [`docs/stack/INTEGRATION-EXAMPLE.md`](docs/stack/INTEGRATION-EXAMPLE.md) | Contoh kode nyata integrasi seluruh stack |
| [`docs/stack/FILAMENT-V4-INTEGRATION.md`](docs/stack/FILAMENT-V4-INTEGRATION.md) | Panduan Integrasi Filament v4 dan Livewire v3 |
| [`docs/stack/PROJECT-STACK.md`](docs/stack/PROJECT-STACK.md) | Stack & konvensi **PostgreSQL 14** — aturan wajib untuk AI agent |
| [`docs/stack/PROJECT-STACK-MYSQL.md`](docs/stack/PROJECT-STACK-MYSQL.md) | Stack & konvensi **MySQL 8+** — untuk proyek klien |
| [`docs/stack/mengenal-laravel-12.md`](docs/stack/mengenal-laravel-12.md) | Fitur-fitur Laravel 12, starter kit, ekosistem |
| [`docs/stack/CHECKLIST-PERSIAPAN.md`](docs/stack/CHECKLIST-PERSIAPAN.md) | Checklist setup Fase 1–9 dari nol |

---

## ⚡ Tech Stack Proyek Ini

```
Framework  : Laravel 12  (PHP 8.2)
Frontend   : Livewire v3  ·  Tailwind CSS v3  ·  Alpine.js v3
Admin      : Filament v4
Database   : PostgreSQL 14                     (default stack)
             MySQL 8+                           (proyek klien)
Testing    : Pest PHP
```

---

## 🚦 Mulai Cepat

### 1. Install PHP + Composer + Laravel Installer (via `php.new`)

Satu command untuk install semua yang dibutuhkan:

```bash
# Linux:
/bin/bash -c "$(curl -fsSL https://php.new/install/linux/8.5)"

# macOS:
/bin/bash -c "$(curl -fsSL https://php.new/install/mac/8.5)"

# Windows (PowerShell — run as Administrator):
Set-ExecutionPolicy Bypass -Scope Process -Force; iex ((New-Object System.Net.WebClient).DownloadString('https://php.new/install/windows/8.5'))
```

> Restart terminal setelah install, lalu verifikasi: `php -v` · `composer -V` · `laravel --version`

---

### 2. Buat Proyek Baru

```bash
# ✅ CARA RESMI — PostgreSQL + Livewire starter kit:
laravel new nama-proyek --database=pgsql --starter-kit=livewire --no-interaction

# Untuk proyek klien MySQL:
laravel new nama-proyek --database=mysql --starter-kit=livewire --no-interaction

# Interaktif (installer tanya satu per satu):
laravel new nama-proyek
```

> [!NOTE]
> - Default database Laravel 12 adalah **SQLite**. Gunakan `--database=pgsql` atau `--database=mysql`
> - Starter kit Livewire sudah include Tailwind CSS v3, Alpine.js v3, dan Livewire v3 otomatis

---

### 3. Jalankan Dev Environment

```bash
cd nama-proyek

# ✅ SATU COMMAND UNTUK SEMUA:
composer run dev
```

> `composer run dev` menjalankan sekaligus:
> - PHP dev server → `localhost:8000`
> - Queue worker (background jobs)
> - Vite dev server (hot reload CSS/JS)
>
> ⛔ **Jangan** gunakan cara lama: `php artisan serve` + `npm run dev` terpisah

---

### 4. Ikuti Checklist Persiapan

Untuk setup lengkap dari nol (database, ekstensi PHP, auth, testing, dsb.):

→ Buka [`docs/stack/CHECKLIST-PERSIAPAN.md`](docs/stack/CHECKLIST-PERSIAPAN.md) dan ikuti dari **Fase 1 sampai Fase 9**.

---

## ⚠️ Aturan Utama yang WAJIB Diikuti AI Agent

> [!CAUTION]
> Hal-hal ini paling sering menyebabkan AI agent error. Baca detail lengkapnya di `PROJECT-STACK.md`.

| ❌ Jangan | ✅ Gantinya |
|----------|-----------|
| `php artisan serve` + `npm run dev` | `composer run dev` |
| `$table->id()` | `$table->uuid('id')->primary()` |
| Vue.js / React | Livewire v3 |
| Logic di Controller | Action Classes (`app/Actions/`) |
| `$table->json()` di PostgreSQL | `$table->jsonb()` |
| `timestamps()` di PostgreSQL | `timestampsTz()` |
| Sintaks `@theme {}` di CSS | Itu Tailwind v4 — gunakan `tailwind.config.js` |
| `@import "tailwindcss"` | `@tailwind base/components/utilities` (v3) |
| `wire:model` tanpa modifier realtime | `wire:model.live` atau `wire:model.blur` |
| Filament v5 syntax/docs | Gunakan Filament v4 docs |
| File raksasa (Controller/View > 250 baris) | Pecah jadi Action Class / Komponen View (Modularitas Wajib) |

---

## ☁️ Deployment

```bash
# Install Laravel Cloud CLI
composer global require laravel/cloud-cli
cloud skills:install
```

Deploy ke [Laravel Cloud](https://cloud.laravel.com) — platform deployment resmi Laravel.

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

---

## 📝 Catatan Penting

- **Default database Laravel 12:** SQLite — stack ini pakai **PostgreSQL 14** (harus konfigurasi manual)
- **PHP yang didukung:** 8.2 (minimum)
- **Dev command resmi:** `composer run dev` (bukan `php artisan serve` terpisah)
- **MySQL vs PostgreSQL:** Tipe data migration berbeda — lihat tabel perbandingan di masing-masing `PROJECT-STACK`
- **Tailwind versi:** v3 — gunakan `tailwind.config.js`, bukan sintaks `@theme {}` (itu v4)
- **Livewire versi:** v3 — sintaks berbeda dari v2 dan v4
- **Filament versi:** v4 — kompatibel dengan Livewire v3

---

*Dokumentasi ini dibuat 1 Juli 2026 untuk stack Laravel 12 + Livewire v3 + Tailwind v3 + Alpine v3 + Filament v4*
*Selalu baca file ini terlebih dahulu, lalu navigasi ke file yang relevan menggunakan peta di atas.*
