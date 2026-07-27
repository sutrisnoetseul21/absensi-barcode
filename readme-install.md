# Panduan Instalasi & Deployment Server Production (Sistem Presensi Digital)

Panduan ini berisi instruksi teknis langkah demi langkah untuk melakukan *deployment* aplikasi Sistem Presensi Digital (termasuk Modul ERP Perpustakaan, dsb) ke server *production* (VPS/Dedicated Server).

## 1. Persyaratan Sistem (System Requirements)
Pastikan server Anda sudah terinstal *stack* berikut:
- **Web Server**: Nginx atau Apache (Disarankan Nginx).
- **PHP**: Versi 8.2 atau lebih baru.
- **Ekstensi PHP**: BCMath, Ctype, Fileinfo, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML, cURL, GD/ImageMagick, Zip, Intl.
- **Database**: MySQL 8.0+ atau MariaDB 10.5+.
- **Composer**: Versi 2.x.
- **Node.js & NPM**: Node.js 18.x atau lebih baru (untuk *build frontend asset* Vite).

---

## 2. Kloning Repositori & Persiapan Direktori
Masuk ke terminal server Anda melalui SSH, lalu arahkan ke direktori web root (contoh: `/var/www/html`):

```bash
cd /var/www/html
git clone [URL_REPOSITORI_GITHUB_ANDA] absensi-barcode
cd absensi-barcode
```

Ubah kepemilikan direktori agar web server (misal: `www-data` untuk Nginx/Apache di Ubuntu) memiliki akses tulis ke folder tertentu:
```bash
sudo chown -R $USER:www-data storage
sudo chown -R $USER:www-data bootstrap/cache
sudo chmod -R 775 storage
sudo chmod -R 775 bootstrap/cache
```

---

## 3. Instalasi Dependensi
Instal *package* PHP melalui Composer. Gunakan flag khusus production agar lebih optimal:
```bash
composer install --optimize-autoloader --no-dev
```

Instal dependensi Javascript/CSS dan *compile* *asset* Vite:
```bash
npm install
npm run build
```

---

## 4. Konfigurasi Environment (`.env`)
Salin file `.env.example` menjadi `.env`:
```bash
cp .env.example .env
```

Buka dan edit file `.env` (contoh menggunakan `nano`):
```bash
nano .env
```

**Penyesuaian Kritis untuk Production:**
- `APP_ENV=production`
- `APP_DEBUG=false` *(PENTING: Jangan biarkan bernilai true di production)*
- `APP_URL=https://domain-sekolah.com` *(Sesuaikan dengan domain SSL Anda)*
- Konfigurasi Database (`DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`).
- Pastikan `SESSION_DRIVER=database` (Penting untuk mengatasi error URL terlalu panjang pada fitur cetak massal barcode Perpustakaan).

*Simpan dan keluar (Ctrl+O, Enter, Ctrl+X).*

Lalu *generate* Application Key rahasia:
```bash
php artisan key:generate --force
```

---

## 5. Migrasi Database
Jalankan migrasi database. Karena Anda berada di mode production, Anda wajib menambahkan parameter `--force`:
```bash
php artisan migrate --force
```

Jika ini adalah instalasi pertama (bukan *update*), jalankan *Seeder* untuk mengisi data *default* (seperti admin awal):
```bash
php artisan db:seed --force
```

---

## 6. Persiapan Storage
Buat *symbolic link* agar folder `storage/app/public` dapat diakses dari browser lewat direktori `public/storage`:
```bash
php artisan storage:link
```

---

## 7. Optimasi Aplikasi (Production Cache)
Jalankan perintah optimasi *all-in-one* bawaan Laravel beserta optimasi panel Filament untuk mempercepat loading drastis:
```bash
php artisan optimize:clear
php artisan optimize
php artisan filament:optimize
php artisan view:cache
php artisan event:cache
```

> **Perhatian**: Jika di kemudian hari Anda mengubah isi file `.env`, Anda **WAJIB** menjalankan `php artisan optimize:clear` agar konfigurasi baru terbaca oleh *cache*.

---

## 8. Konfigurasi Nginx (Contoh Server Block)
Apabila Anda menggunakan Nginx, berikut adalah cuplikan contoh konfigurasi *Server Block* yang direkomendasikan. Pastikan root Nginx mengarah ke folder `/public`.

```nginx
server {
    listen 80;
    server_name domain-sekolah.com;
    root /var/www/html/absensi-barcode/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

---

## 9. Selesai!
Aplikasi kini seharusnya sudah aktif dan dapat diakses melalui URL domain Anda.
- **Login Admin**: `https://domain-sekolah.com/admin`
- **Login Admin Perpustakaan**: `https://domain-sekolah.com/admin-perpustakaan`

*Selamat melakukan deployment!*
