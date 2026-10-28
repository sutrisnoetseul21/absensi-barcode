# 🚀 Panduan Deployment & Konfigurasi Lingkungan Server

Dokumen ini memuat petunjuk teknis langkah-demi-langkah instalasi server Linux produksi (Direct VPS atau Control Panel HestiaCP), konfigurasi Virtual Host Nginx, izin berkas (*permissions*), pengerasan keamanan (*security hardening*), serta skrip deployment otomatis untuk sistem **Presensi Barcode & Smart School ERP**.

---

## 📌 Daftar Isi
1. [Prasyarat Perangkat Keras & Perangkat Lunak](#1-prasyarat-perangkat-keras--perangkat-lunak)
2. [Instalasi Awal Aplikasi (First-Time Setup)](#2-instalasi-awal-aplikasi-first-time-setup)
3. [Standarisasi Hak Akses & Kepemilikan Berkas Linux](#3-standarisasi-hak-akses--kepemilikan-berkas-linux)
4. [Konfigurasi Web Server Nginx Produksi](#4-konfigurasi-web-server-nginx-produksi)
5. [Pengaturan SSL HTTPS & Hardening Keamanan `.env`](#5-pengaturan-ssl-https--hardening-keamanan-env)
6. [Skrip Deployment Rutin (Zero-Downtime Update)](#6-skrip-deployment-rutin-zero-downtime-update)

---

## 1. Prasyarat Perangkat Keras & Perangkat Lunak

### Spesifikasi Minimum Server Produksi
- **CPU**: Minimal 2 vCPU (Disarankan 4 vCPU jika melayani > 1.000 siswa dan asesmen ZenCBT serentak).
- **RAM**: Minimal 4 GB (Disarankan 8 GB untuk mengakomodasi caching MySQL, PM2 Queue Worker, dan pemrosesan kompresi gambar GD/WebP).
- **Penyimpanan**: Minimal 40 GB SSD / NVMe (partisi khusus data pas foto siswa dan berkas cadangan database).
- **Sistem Operasi**: Linux Ubuntu 22.04 LTS / 24.04 LTS atau Debian 12.

### Daftar Paket Software Wajib
- **PHP**: Versi **8.2** atau **8.4** CLI & FPM.
  Ekstensi PHP yang wajib terpasang:
  ```bash
  sudo apt install -y php8.4-fpm php8.4-cli php8.4-mysql php8.4-mbstring \
  php8.4-xml php8.4-curl php8.4-zip php8.4-gd php8.4-bcmath php8.4-intl
  ```
- **Web Server**: Nginx (direkomendasikan) atau Apache 2.4.
- **Database Engine**: MySQL 8.0+ atau MariaDB 10.11+ (Engine: InnoDB, Charset: `utf8mb4`, Collation: `utf8mb4_unicode_ci`).
- **Node.js & NPM**: Node.js v18 LTS atau v20 LTS (untuk *Vite build* dan *Tailwind compiling*).
- **Process Manager**: PM2 (`npm install -g pm2`) atau Supervisor untuk mengelola background queue worker.
- **Composer**: Composer v2.6+.
- **Git**: Versi 2.30+.

---

## 2. Instalasi Awal Aplikasi (First-Time Setup)

Jalankan perintah berikut di bawah user sistem aplikasi (misalnya user `smpn3kedungreja` atau `deploy`):

```bash
# 1. Kloning repositori ke direktori web server
cd /var/www/html # Atau folder public_html HestiaCP
git clone https://github.com/username/projek-absensi-barcode.git app
cd app

# 2. Salin dan konfigurasi berkas environment
cp .env.example .env

# 3. Pasang dependensi PHP produksi (tanpa dev package)
composer install --no-dev --optimize-autoloader

# 4. Generate application key
php artisan key:generate

# 5. Hubungkan tautan simbolik storage publik untuk foto profil siswa & logo
php artisan storage:link

# 6. Jalankan migrasi basis data dan seeder master data awal
php artisan migrate --force --seed

# 7. Pasang dependensi frontend dan kompilasi aset Vite
npm ci
npm run build

# 8. Optimasi cache performa Laravel & Filament
php artisan optimize:clear
php artisan optimize
php artisan filament:cache-components
```

---

## 3. Standarisasi Hak Akses & Kepemilikan Berkas Linux

Salah satu penyebab paling umum kegagalan penulisan log atau kegagalan unggah foto adalah ketidakcocokan izin kepemilikan (*file permissions*):

```bash
# 1. Atur kepemilikan kepada user web server (Nginx/Apache)
# Jika menggunakan HestiaCP, gunakan user akun tersebut: misal smpn3kedungreja:www-data
sudo chown -R www-data:www-data /var/www/html/app

# 2. Berikan izin baca/tulis khusus pada direktori dinamis
sudo chmod -R 775 /var/www/html/app/storage
sudo chmod -R 775 /var/www/html/app/bootstrap/cache

# 3. Pastikan berkas .env hanya bisa dibaca oleh user pemilik & web server
chmod 640 /var/www/html/app/.env
```

> [!CAUTION]
> Jangan pernah memberikan izin `chmod -R 777` pada seluruh root proyek! Cukup berikan izin tulis pada folder `storage/` dan `bootstrap/cache/`.

---

## 4. Konfigurasi Web Server Nginx Produksi

Berikut adalah blok konfigurasi Nginx Virtual Host lengkap yang telah dioptimasi khusus untuk arsitektur Laravel 12, Livewire v3, dan modul impor massal:

```nginx
server {
    listen 80;
    server_name app.sekolah.sch.id;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    server_name app.sekolah.sch.id;

    root /var/www/html/app/public;
    index index.php index.html;

    # SSL Certificate (Let's Encrypt Certbot)
    ssl_certificate /etc/letsencrypt/live/app.sekolah.sch.id/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/app.sekolah.sch.id/privkey.pem;
    include /etc/letsencrypt/options-ssl-nginx.conf;
    ssl_dhparam /etc/letsencrypt/ssl-dhparams.pem;

    # 1. PENTING: Batas Ukuran Upload Berkas (ZIP Foto Siswa & Excel Massal)
    client_max_body_size 50M;

    # 2. Karakter Charset & Header Keamanan
    charset utf-8;
    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header X-XSS-Protection "1; mode=block";
    add_header Referrer-Policy "strict-origin-when-cross-origin";

    # 3. Routing Utama Laravel
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # 4. PROTEKSI ANTI-WEBSHELL / PHP2SHELL (Folder Upload & Storage)
    # Jangan pernah izinkan web server mengeksekusi script PHP di folder penyimpanan berkas publik!
    location ~* ^/storage/.*\.(php|phar|phtml|php[0-9]?|inc|cgi|pl|sh)$ {
        deny all;
        return 403;
    }

    # 5. Routing Khusus Pemrosesan PHP-FPM
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        
        # Cegah 504 Gateway Timeout saat impor Excel/ZIP ribuan data
        fastcgi_read_timeout 300;
        fastcgi_send_timeout 300;
        fastcgi_buffer_size 128k;
        fastcgi_buffers 256 16k;
        fastcgi_busy_buffers_size 256k;
        fastcgi_temp_file_write_size 256k;
    }

    # 5. Caching Aset Statis Frontend (Vite Build, CSS, JS, Gambar)
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|webp|svg|woff|woff2|ttf)$ {
        expires 30d;
        add_header Cache-Control "public, no-transform";
        access_log off;
    }

    # 6. PROTEKSI KETAT: Blokir Akses Langsung ke Berkas Rahasia (.env, .git)
    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Uji dan muat ulang konfigurasi Nginx:
```bash
sudo nginx -t
sudo systemctl reload nginx
```

---

## 5. Pengaturan SSL HTTPS & Hardening Keamanan `.env`

### Sertifikat SSL Otomatis Let's Encrypt
Gunakan Certbot untuk mengamankan komunikasi data antara Kiosk, gawai siswa/guru, dan server:
```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d app.sekolah.sch.id
```

### Parameter `.env` Produksi yang Wajib Disesuaikan
Buka berkas `.env` di server dan pastikan pengaturan keamanan berikut aktif:

```ini
APP_NAME="Presensi Barcode & ERP Sekolah"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://app.sekolah.sch.id

# Keamanan Cookie Session (Wajib Aktif saat HTTPS)
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax

# Queue Connection Wajib Database
QUEUE_CONNECTION=database

# WhatsApp Evolution API Gateway
WA_BASE_URL=http://10.77.77.21:8088
WA_API_KEY=KunciApiRahasiaEvolutionAnda

# Cloudflare Turnstile Anti-Bot (Panel Admin /admin)
TURNSTILE_SITE_KEY=0x4AAAAAA...
TURNSTILE_SECRET_KEY=0x4AAAAAA...

# ZenCBT Bridge Gateway (Jika menggunakan ujian online)
CBT_DRIVER=zencbt
CBT_API_URL=http://127.0.0.1:7879
CBT_API_KEY=KunciApiZenCBT
CBT_TIMEOUT=15
```

---

## 6. Skrip Deployment Rutin (Zero-Downtime Update)

Untuk pembaruan rutin kode program dari repositori Git tanpa merusak sesi pengguna yang sedang aktif, buat berkas skrip `deploy.sh` di root proyek:

```bash
#!/bin/bash
set -e

echo "🚀 [1/7] Memulai deployment aplikasi..."
cd /var/www/html/app

echo "📦 [2/7] Menarik pembaruan dari Git..."
git pull origin main

echo "🧩 [3/7] Menginstal dependensi PHP Composer..."
composer install --no-dev --prefer-dist --optimize-autoloader

echo "🗄️ [4/7] Menjalankan migrasi database..."
php artisan migrate --force

echo "🎨 [5/7] Mengompilasi aset Vite & Livewire..."
npm ci
npm run build

echo "⚡ [6/7] Membersihkan dan meregenerasi cache aplikasi..."
php artisan optimize:clear
php artisan optimize
php artisan filament:cache-components

echo "🔄 [7/7] Me-restart Queue Worker daemon..."
pm2 restart absensi-queue || supervisorctl restart absensi-queue

echo "✅ Deployment berhasil diselesaikan dengan sempurna!"
```

Berikan izin eksekusi pada skrip:
```bash
chmod +x deploy.sh
```

Setiap kali ada rilis perbaikan fitur, cukup jalankan:
```bash
./deploy.sh
```
