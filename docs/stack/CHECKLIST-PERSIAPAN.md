# ✅ CHECKLIST PERSIAPAN — Laravel 12 App

> Ikuti urutan ini dari atas ke bawah sebelum mulai menulis kode aplikasi.
> Centang setiap item setelah selesai.
> Stack: **Laravel 12 · PHP 8.2 · Livewire v3 · Tailwind v3 · Alpine v3 · Filament v4**

---

## FASE 1 — Persiapan Sistem (Lokal)

### 🖥️ 1.1 Verifikasi PHP & Composer

- [ ] PHP versi **8.2 atau lebih tinggi** terinstall
- [ ] Composer versi **2.x** terinstall
- [ ] **Laravel Installer** terinstall
- [ ] Ekstensi PHP yang dibutuhkan aktif

```bash
# Cek versi
php -v             # harus >= 8.2
composer -V        # harus 2.x
laravel --version  # harus ada

# Cek ekstensi yang dibutuhkan Laravel
php -m | grep -E "pdo|mbstring|openssl|tokenizer|xml|ctype|json|bcmath|curl|fileinfo"
```

#### Install PHP + Composer + Laravel Installer Sekaligus (via php.new):

Jika belum ada, gunakan cara tercepat ini:

```bash
# macOS:
/bin/bash -c "$(curl -fsSL https://php.new/install/mac/8.2)"

# Linux:
/bin/bash -c "$(curl -fsSL https://php.new/install/linux/8.2)"

# Windows (PowerShell — run as Administrator):
Set-ExecutionPolicy Bypass -Scope Process -Force; [System.Net.ServicePointManager]::SecurityProtocol = [System.Net.ServicePointManager]::SecurityProtocol -bor 3072; iex ((New-Object System.Net.WebClient).DownloadString('https://php.new/install/windows/8.2'))
```

> **Restart terminal** setelah menjalankan command di atas.

Ekstensi wajib:
- [ ] `pdo_pgsql` (untuk PostgreSQL) atau `pdo_mysql` (untuk MySQL)
- [ ] `mbstring`
- [ ] `openssl`
- [ ] `tokenizer`
- [ ] `xml`
- [ ] `ctype`
- [ ] `json`
- [ ] `bcmath`
- [ ] `curl`
- [ ] `fileinfo`
- [ ] `zip`

---

### 🟢 1.2 Node.js & npm

- [ ] Node.js versi **20+** terinstall
- [ ] npm atau **pnpm** terinstall
- [ ] Verifikasi berjalan normal

```bash
node -v    # harus >= 20.x
npm -v
# atau: pnpm -v
```

---

### 🐘 1.3 Database

**Jika pakai PostgreSQL (default stack ini):**
- [ ] PostgreSQL **14** terinstall & berjalan
- [ ] Ekstensi `uuid-ossp` aktif
- [ ] Ekstensi `pg_trgm` aktif (untuk fuzzy search)
- [ ] Buat database baru untuk proyek ini
- [ ] User & password database sudah disiapkan

```bash
# Cek PostgreSQL berjalan
pg_isready

# Masuk ke psql dan buat database
psql -U postgres
CREATE DATABASE nama_proyek;
CREATE USER nama_user WITH PASSWORD 'password_kuat';
GRANT ALL PRIVILEGES ON DATABASE nama_proyek TO nama_user;

# Aktifkan ekstensi
\c nama_proyek
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE EXTENSION IF NOT EXISTS pg_trgm;
\q
```

**Jika pakai MySQL (proyek klien):**
- [ ] MySQL **8.0+** atau MariaDB **10.6+** terinstall & berjalan
- [ ] Buat database dengan charset `utf8mb4`
- [ ] User & password database sudah disiapkan

```bash
# Buat database MySQL
mysql -u root -p
CREATE DATABASE nama_proyek CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'nama_user'@'localhost' IDENTIFIED BY 'password_kuat';
GRANT ALL PRIVILEGES ON nama_proyek.* TO 'nama_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

> **Catatan:** Default database Laravel 12 out-of-the-box adalah **SQLite** (zero config).
> Stack ini menggunakan **PostgreSQL 14** yang harus dikonfigurasi manual.

---

### 🔴 1.4 Redis (Opsional, tapi Dianjurkan untuk Production)

- [ ] Redis terinstall (untuk queue & cache di production)
- [ ] Redis berjalan di port default `6379`

```bash
redis-server --version
redis-cli ping  # harus jawab: PONG
```

---

### 🌐 1.5 Dev Server Lokal

Pilih salah satu:

**Opsi A — Laravel Herd (Paling Mudah):**
- [ ] Download & install [Laravel Herd](https://herd.laravel.com)
- [ ] Herd otomatis handle PHP, Nginx, DNS
- [ ] Herd Pro juga menyediakan MySQL, PostgreSQL, dan Redis lokal

**Opsi B — Manual (composer run dev):**
- [ ] Tidak perlu setup tambahan
- [ ] Jalankan `composer run dev` dari direktori proyek
- [ ] Ini menjalankan PHP server + Queue worker + Vite sekaligus

---

## FASE 2 — Instalasi Laravel 12

### 🚀 2.1 Buat Proyek Baru

- [ ] Tentukan nama proyek (huruf kecil, gunakan `-` sebagai pemisah)
- [ ] Tentukan direktori kerja
- [ ] Jalankan perintah instalasi

```bash
# === CARA RESMI DIANJURKAN (via Laravel Installer) ===

# Dengan Livewire starter kit + PostgreSQL (stack ini):
laravel new nama-proyek --database=pgsql --starter-kit=livewire --no-interaction

# Dengan Livewire starter kit + MySQL (proyek klien):
laravel new nama-proyek --database=mysql --starter-kit=livewire --no-interaction

# Interaktif (installer akan tanya pilihan satu per satu):
laravel new nama-proyek
cd nama-proyek

# === CARA ALTERNATIF (via Composer) ===
composer create-project laravel/laravel:^12.0 nama-proyek
cd nama-proyek
```

> [!NOTE]
> Starter kit Livewire sudah include **Tailwind CSS v3**, **Alpine.js v3**, dan **Livewire v3** secara otomatis.
> Tidak perlu install terpisah jika menggunakan starter kit.

---

### ⚙️ 2.2 Konfigurasi `.env`

- [ ] Salin `.env.example` ke `.env` (jika menggunakan `composer create-project`)
- [ ] Generate `APP_KEY`
- [ ] Atur koneksi database
- [ ] Atur `APP_NAME`, `APP_URL`

```bash
cp .env.example .env  # skip jika menggunakan laravel installer
php artisan key:generate
```

Edit `.env`:
```env
APP_NAME="Nama Proyek Kamu"
APP_ENV=local
APP_URL=http://localhost:8000

# === PostgreSQL (stack ini) ===
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=nama_proyek
DB_USERNAME=nama_user
DB_PASSWORD=password_kuat

# === Jika MySQL ===
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=nama_proyek
# DB_USERNAME=nama_user
# DB_PASSWORD=password_kuat
# DB_CHARSET=utf8mb4
# DB_COLLATION=utf8mb4_unicode_ci

# Queue (mulai dari database)
QUEUE_CONNECTION=database

# Cache
CACHE_STORE=database
```

---

### 🗃️ 2.3 Jalankan Migration Awal

- [ ] Pastikan koneksi database berhasil
- [ ] Jalankan migration bawaan Laravel

```bash
# Test koneksi dulu
php artisan db:show

# Jalankan migration
php artisan migrate
```

---

### 📦 2.4 Install Package Frontend

- [ ] Install dependensi npm
- [ ] Verifikasi Vite berjalan normal

```bash
npm install
```

---

## FASE 3 — Install Stack Utama

> [!NOTE]
> Jika menggunakan `--starter-kit=livewire` saat membuat proyek, **Tailwind v3, Livewire v3, dan Alpine v3 sudah terinstall otomatis**. Lewati fase 3.1–3.3 dan langsung ke 3.4.

### 🎨 3.1 Tailwind CSS v3 (Jika Install Manual)

- [ ] Install Tailwind CSS v3 & PostCSS

```bash
npm install -D tailwindcss postcss autoprefixer
npx tailwindcss init -p
```

Ini membuat dua file: `tailwind.config.js` dan `postcss.config.js`.

Konfigurasi `tailwind.config.js`:
```js
/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],
    theme: {
        extend: {},
    },
    plugins: [],
};
```

Tambahkan ke `resources/css/app.css`:
```css
@tailwind base;
@tailwind components;
@tailwind utilities;
```

- [ ] Verifikasi Tailwind berjalan di browser

> [!CAUTION]
> Tailwind v3 **WAJIB** punya `tailwind.config.js`. Jangan hapus file ini.
> Jangan gunakan `@import "tailwindcss"` (itu sintaks v4) atau `@theme {}` (itu v4).

---

### ⚡ 3.2 Livewire v3 (Jika Install Manual)

- [ ] Install Livewire v3

```bash
composer require livewire/livewire:^3.0
php artisan livewire:publish --config
```

- [ ] Tambahkan ke layout Blade:

```html
<!-- Di dalam <head> -->
@livewireStyles

<!-- Sebelum </body> -->
@livewireScripts
```

> [!NOTE]
> Di Livewire v3, tag `@livewireStyles` dan `@livewireScripts` **masih diperlukan** di layout.
> Berbeda dengan v4 yang inject otomatis.

- [ ] Test buat komponen pertama

```bash
php artisan make:livewire TestComponent
```

- [ ] Verifikasi komponen muncul di browser

---

### 🏔️ 3.3 Alpine.js v3 (Jika Install Manual)

- [ ] Install Alpine.js

```bash
npm install alpinejs
```

Inisialisasi di `resources/js/app.js`:
```js
import Alpine from 'alpinejs'
window.Alpine = Alpine
Alpine.start()
```

> [!NOTE]
> Di Livewire v3 dengan starter kit, Alpine biasanya sudah ter-include.
> Cek `package.json` sebelum install ulang.

- [ ] Verifikasi Alpine bekerja (coba `x-data`, `x-show`)

---

### 🗂️ 3.4 Filament v4 (Jika Butuh Admin Panel)

- [ ] Install Filament v4

```bash
composer require filament/filament:"^4.0"
php artisan filament:install --panels
php artisan migrate
```

- [ ] Buat user admin

```bash
php artisan make:filament-user
```

- [ ] Akses panel di `http://localhost:8000/admin`

- [ ] Test buat resource pertama

```bash
php artisan make:filament-resource Post --generate
```

> [!IMPORTANT]
> Filament v4 berjalan di atas **Livewire v3**. Pastikan Livewire v3 sudah terinstall sebelum install Filament.
> Baca [`docs/FILAMENT-V4-INTEGRATION.md`](FILAMENT-V4-INTEGRATION.md) untuk panduan lengkap.

---

## FASE 4 — Setup Autentikasi

### 🔑 4.1 Pilih Sistem Auth

Pilih **salah satu**:

**Opsi A — Starter Kit Livewire (Sudah Terinstall, Dianjurkan):**
Jika membuat proyek dengan `--starter-kit=livewire`, auth scaffolding sudah terpasang otomatis.
- [ ] Verifikasi login/register sudah berfungsi di browser

**Opsi B — Laravel Breeze (Install Manual):**
- [ ] Install Breeze

```bash
composer require laravel/breeze --dev
php artisan breeze:install livewire
npm install && npm run build
php artisan migrate
```

**Opsi C — Laravel Jetstream:**
- [ ] Install Jetstream

```bash
composer require laravel/jetstream
php artisan jetstream:install livewire
npm install && npm run build
php artisan migrate
```

**Opsi D — Custom (tanpa starter kit):**
- [ ] Buat sendiri dengan `php artisan make:livewire Auth/Login`
- [ ] Gunakan Laravel Fortify sebagai backend

---

## FASE 5 — Struktur Proyek

### 🏗️ 5.1 Buat Struktur Folder

- [ ] Buat folder `app/Actions/`
- [ ] Buat folder `app/Actions/{NamaFitur}/`
- [ ] Verifikasi folder `app/Livewire/` ada (Livewire otomatis buat ini)

```bash
mkdir -p app/Actions
mkdir -p app/Actions/Auth
```

---

### 📐 5.2 Buat Layout Utama

- [ ] Buat `resources/views/layouts/app.blade.php`
- [ ] Buat `resources/views/layouts/guest.blade.php`
- [ ] Buat folder `resources/views/components/ui/`
- [ ] Buat komponen UI dasar: Button, Card, Badge, Alert

```bash
php artisan make:component ui/Button --view
php artisan make:component ui/Card --view
```

---

### 🎨 5.3 Design System Awal

- [ ] Tentukan palet warna utama (di `tailwind.config.js`)
- [ ] Tentukan font (dari Google Fonts atau Tailwind default)
- [ ] Tentukan dark mode strategy
- [ ] Buat variabel CSS kustom jika perlu

```js
// tailwind.config.js
export default {
    darkMode: 'class',
    theme: {
        extend: {
            colors: {
                primary: {
                    500: '#6366f1',  // indigo
                    600: '#4f46e5',
                },
            },
            fontFamily: {
                sans: ['Inter', 'sans-serif'],
            },
        },
    },
};
```

---

## FASE 6 — Pengujian (Testing)

### 🧪 6.1 Install Pest PHP

- [ ] Install Pest

```bash
composer require pestphp/pest --dev
composer require pestphp/pest-plugin-laravel --dev
composer require pestphp/pest-plugin-livewire --dev
php artisan pest:install
```

- [ ] Jalankan test pertama untuk memastikan berjalan

```bash
php artisan test
# atau: ./vendor/bin/pest
```

---

### 🗃️ 6.2 Konfigurasi Database untuk Testing

- [ ] Buat database terpisah untuk testing (jika pakai PostgreSQL)

```bash
# PostgreSQL
createdb nama_proyek_test
```

Edit `phpunit.xml`:
```xml
<!-- Untuk PostgreSQL test (konsisten dengan production) -->
<env name="DB_CONNECTION" value="pgsql"/>
<env name="DB_DATABASE" value="nama_proyek_test"/>

<!-- Untuk SQLite in-memory (lebih cepat, untuk proyek MySQL) -->
<!-- <env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/> -->
```

---

## FASE 7 — Tools & Optimasi Dev

### 🔍 7.1 Debugbar & Dev Tools

- [ ] Install Laravel Debugbar

```bash
composer require barryvdh/laravel-debugbar --dev
```

- [ ] Install Telescope (jika butuh monitoring lebih dalam)

```bash
composer require laravel/telescope --dev
php artisan telescope:install
php artisan migrate
```

---

### 📝 7.2 IDE Helper (Untuk Autocomplete yang Lebih Baik)

- [ ] Install IDE Helper agar VSCode/Cursor lebih pintar

```bash
composer require barryvdh/laravel-ide-helper --dev
php artisan ide-helper:generate
php artisan ide-helper:models --write
php artisan ide-helper:meta
```

- [ ] Tambahkan ke `.gitignore`:
```
_ide_helper.php
_ide_helper_models.php
.phpstorm.meta.php
```

---

### 🎯 7.3 Code Quality Tools

- [ ] Install Laravel Pint (code formatter, sudah include di Laravel)

```bash
./vendor/bin/pint  # format semua file
```

- [ ] Setup Pint di `pint.json`:

```json
{
    "preset": "laravel"
}
```

- [ ] Opsional: Install Larastan untuk static analysis

```bash
composer require nunomaduro/larastan --dev
php artisan vendor:publish --tag=larastan-config
./vendor/bin/phpstan analyse
```

---

## FASE 8 — Persiapan Deployment

### ☁️ 8.1 Pilih Platform Deployment

Pilih **salah satu**:

- [ ] **Laravel Cloud** (paling mudah, resmi dari Laravel) — [cloud.laravel.com](https://cloud.laravel.com)
- [ ] **Laravel Forge** (untuk VPS/server sendiri)
- [ ] **VPS Manual** (DigitalOcean, Vultr, Hetzner, dsb.)
- [ ] **Shared Hosting** (tidak dianjurkan untuk Laravel 12)

### ☁️ 8.2 Build Production Assets

```bash
# Build frontend assets untuk production
npm run build

# Optimasi Laravel untuk production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

### 🔒 8.3 Siapkan `.env.example`

- [ ] Update `.env.example` dengan semua key yang dibutuhkan (tanpa nilai sensitif)
- [ ] Tambahkan komentar untuk setiap key

```env
# Salin ke .env dan isi nilainya
APP_NAME=
APP_ENV=production
APP_KEY=
APP_URL=https://

DB_CONNECTION=pgsql
DB_HOST=
DB_PORT=5432
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=

QUEUE_CONNECTION=database
CACHE_STORE=database
```

---

### 📜 8.4 Dokumentasi Proyek

- [ ] Update `README.md` dengan instruksi instalasi
- [ ] Pastikan `PROJECT-STACK.md` sudah lengkap
- [ ] Catat semua keputusan arsitektur penting

---

## FASE 9 — Verifikasi Akhir

Sebelum mulai coding fitur pertama, pastikan semua ini berjalan:

- [ ] `composer run dev` berjalan tanpa error (PHP server + queue + Vite)
- [ ] Aplikasi bisa diakses di `http://localhost:8000`
- [ ] `php artisan migrate` berhasil
- [ ] `php artisan test` — semua test pass (meskipun masih test default)
- [ ] Login/register berfungsi (starter kit)
- [ ] Tailwind CSS terapply di browser (cek via DevTools)
- [ ] `tailwind.config.js` ada di root proyek
- [ ] Livewire component bisa di-render
- [ ] Alpine.js berfungsi (coba `x-data` + `x-show` toggle)
- [ ] Filament panel bisa diakses di `/admin` (jika diinstall)
- [ ] Cek versi package: `cat composer.json | grep -E "laravel|livewire|filament"`

---

## 📋 Ringkasan Urutan Instalasi Cepat

```bash
# === CARA PALING CEPAT (starter kit — semua terinstall otomatis) ===

# 1. Buat proyek Laravel 12 dengan Livewire starter kit
laravel new nama-proyek --database=pgsql --starter-kit=livewire --no-interaction
cd nama-proyek

# 2. Jalankan migration
php artisan migrate

# 3. Jalankan dev environment
composer run dev
# → Aplikasi berjalan di http://localhost:8000
# → Tailwind v3, Livewire v3, Alpine v3 sudah aktif

# 4. (Opsional) Install Filament v4 — jika butuh admin panel
composer require filament/filament:"^4.0"
php artisan filament:install --panels
php artisan migrate
php artisan make:filament-user

# === ATAU CARA MANUAL (lebih banyak kontrol) ===

# 1. Buat proyek
composer create-project laravel/laravel:^12.0 nama-proyek && cd nama-proyek

# 2. Konfigurasi .env (edit manual)
cp .env.example .env && php artisan key:generate

# 3. Database & migration
php artisan migrate

# 4. Frontend
npm install

# 5. Tailwind v3
npm install -D tailwindcss postcss autoprefixer
npx tailwindcss init -p

# 6. Livewire v3
composer require livewire/livewire:^3.0

# 8. Alpine.js v3
npm install alpinejs

# 9. Auth (pilih salah satu)
composer require laravel/breeze --dev && php artisan breeze:install livewire
npm run build && php artisan migrate

# 10. Pest PHP
composer require pestphp/pest pestphp/pest-plugin-laravel pestphp/pest-plugin-livewire --dev
php artisan pest:install

# 11. Filament v4 (opsional)
composer require filament/filament:"^4.0"
php artisan filament:install --panels && php artisan migrate

# 12. IDE Helper
composer require barryvdh/laravel-ide-helper --dev && php artisan ide-helper:generate

# 13. Debugbar
composer require barryvdh/laravel-debugbar --dev

# 14. Jalankan dev server
composer run dev
```

---

## 🗂️ Dokumen Pendukung di Proyek Ini

| File | Fungsi |
|------|--------|
| [`README-DOCS.md`](../README-DOCS.md) | Peta navigasi utama — baca ini dulu |
| [`PROJECT-STACK.md`](PROJECT-STACK.md) | Referensi stack (PostgreSQL 14) untuk AI agent |
| [`PROJECT-STACK-MYSQL.md`](PROJECT-STACK-MYSQL.md) | Referensi stack (MySQL 8+) untuk proyek klien |
| [`LIVEWIRE-V3-GUIDE.md`](LIVEWIRE-V3-GUIDE.md) | Panduan Livewire v3 |
| [`TAILWIND-V3-GUIDE.md`](TAILWIND-V3-GUIDE.md) | Panduan Tailwind CSS v3 |
| [`FILAMENT-V4-INTEGRATION.md`](FILAMENT-V4-INTEGRATION.md) | Panduan Filament v4 |
| [`mengenal-laravel-12.md`](mengenal-laravel-12.md) | Pengenalan fitur Laravel 12 |
| [`CHECKLIST-PERSIAPAN.md`](CHECKLIST-PERSIAPAN.md) | File ini — panduan persiapan awal |

---

*Checklist ini diperbarui 1 Juli 2026 untuk stack Laravel 12 + Livewire v3 + Tailwind v3 + Alpine v3 + Filament v4.*
*Update sesuai kebutuhan proyek.*
