# 🏫 Panduan Arsitektur & Operasional Multi-Sekolah (Opsi 1: 1 Codebase, Eksklusif per Sekolah)

Dokumen ini adalah panduan resmi pengelolaan arsitektur sistem absensi barcode untuk melayani **banyak sekolah di VPS dan domain yang berbeda**, dengan **1 repository Git tunggal** baik di Flutter maupun Laravel.

---

## 🎯 1. Filosofi & Gambaran Arsitektur

### Prinsip Utama:
1. **Dilarang Meng-clone Folder Proyek**: Repository Flutter (`apk-android-presensi`) dan Laravel (`projek-absensi-barcode`) tetap **satu repository tunggal**.
2. **Sekolah Mandiri (Single Tenant)**: Masing-masing sekolah berjalan di VPS dan domain sendiri untuk performa maksimal dan isolasi data siswa yang aman.
3. **Injeksi Parameter saat Build**: Alamat server backend (`BASE_URL`) dan nama aplikasi di-injek saat compile APK, sehingga siswa/guru tinggal pakai tanpa perlu memilih server manual.

```
                      [ GITHUB REPOSITORY ]
                                │
        ┌───────────────────────┼───────────────────────┐
   (git pull)              (git pull)              (git pull)
        ▼                       ▼                       ▼
   🏢 VPS 1 (Sekolah A)    🏢 VPS 2 (Sekolah B)    🏢 VPS 3 (Sekolah C)
  https://sekolah-a.id    https://sekolah-b.id    https://sekolah-c.id
  (db_sekolah_a)          (db_sekolah_b)          (db_sekolah_c)
        ▲                       ▲                       ▲
        │                       │                       │
 [APK Sekolah A]         [APK Sekolah B]         [APK Sekolah C]
 (arm64-v8a / v7a)       (arm64-v8a / v7a)       (arm64-v8a / v7a)
        └───────────────────────┴───────────────────────┘
                     Dicompile dari 1 Repo Flutter
```

---

## 🔔 2. Kebijakan & Pengaturan Firebase Push Notifikasi (FCM)

### Pertanyaan: Apakah Perlu Membuat File JSON Baru di Firebase untuk Setiap Sekolah?
👉 **JAWABAN: TIDAK HARUS, BISA MENGGUNAKAN 1 PROYEK FIREBASE BERSAMA (Direkomendasikan)**.

Google Firebase mengizinkan:
1. **Satu Project Firebase** dapat melayani **banyak aplikasi Android**.
2. **Kunci Service Account (`firebase-service-account.json`) yang sama** dapat dipasang di **VPS 1, VPS 2, dan VPS 3**.
3. Google tidak membatasi dari IP server mana notifikasi dikirimkan, asalkan kunci privatnya sah.

### Skenario Penggunaan Firebase:

#### Skenario Rekomendasi (Praktis & Terpusat):
* **Nama Proyek Firebase**: Misalnya `Smart School Platform` (atau project yang sudah ada: `notif-android-testing`).
* **Untuk Backend Laravel (Semua VPS)**:
  - Salin file `firebase-service-account.json` yang sama ke setiap VPS di:
    ```text
    storage/app/firebase-service-account.json
    ```
  - Konfigurasi `.env` di setiap VPS sama:
    ```env
    FIREBASE_CREDENTIALS=storage/app/firebase-service-account.json
    ```
* **Untuk Aplikasi Android (Flutter)**:
  - Jika Package ID Android sama (`id.haflaitsolution.apk_portal_smpn1_sampang`), Anda **TIDAK PERLU** membuat file JSON baru. Cukup gunakan satu file `google-services.json` yang sudah ada sekarang.
  - *Catatan*: Jika suatu saat Anda ingin membedakan Package ID (misal `id.sekolah.smpn3kedungreja` agar siswa yang punya 2 adik di sekolah berbeda bisa install 2 aplikasi sekaligus di 1 HP), cukup klik **Add App** di project Firebase yang sama, unduh `google-services.json` terbaru yang berisi daftar kedua aplikasi tersebut.

---

## ⚙️ 3. Konfigurasi di Sisi Backend Laravel (Tiap VPS)

Setiap ada sekolah baru yang berlangganan dan disiapkan di VPS baru:

### Langkah 1: Clone & Setup Awal
```bash
git clone git@github.com:sutrisnoetseul21/absensi-barcode.git /var/www/absensi
cd /var/www/absensi
composer install --no-dev --optimize-autoloader
cp .env.example .env
php artisan key:generate
```

### Langkah 2: Sesuaikan File `.env` Khusus Sekolah Tersebut
```env
APP_NAME="Sistem Presensi Sekolah"
APP_ENV=production
APP_URL=https://domainsekolah.sch.id

# Database masing-masing sekolah
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=absensi_sekolah_x
DB_USERNAME=user_sekolah_x
DB_PASSWORD=Password_Kuat_123

# Queue database
QUEUE_CONNECTION=database

# Firebase Push Notifikasi
FIREBASE_PROJECT_ID=notif-android-testing
FIREBASE_CREDENTIALS=storage/app/firebase-service-account.json
```

### Langkah 3: Pasang File Service Account Firebase
Salin file kunci service account ke:
```text
storage/app/firebase-service-account.json
```

### Langkah 4: Migrasi & Jalankan Queue Worker
```bash
php artisan migrate --force
php artisan storage:link
php artisan optimize

# Jalankan worker antrean via PM2 (sudah ada di ecosystem.config.cjs)
pm2 start ecosystem.config.cjs
pm2 save
```

### Langkah 5: Cara Update Sistem Jika Ada Perubahan / Fitur Baru
Jika di komputer lokal ada perbaikan fitur dan sudah di-push ke GitHub:
```bash
# Di setiap VPS cukup jalankan:
cd /var/www/absensi
git pull origin main
php artisan migrate --force
php artisan optimize:clear
pm2 restart ecosystem.config.cjs
```

---

## 🎨 4. Lokasi Penyimpanan Logo & Pengaturan Nama Aplikasi

Folder khusus untuk logo 4 sekolah sudah disiapkan di dalam proyek Flutter:
```text
apk-android-presensi/
└── assets/
    └── schools/
        ├── smpn3kedungreja/  --> Taruh file logo.png di sini (domain: smpn3kedungreja.sch.id)
        ├── smpn1majenang/    --> Taruh file logo.png di sini (domain: smpn1majenang.sch.id)
        ├── smpn1sampang/     --> Taruh file logo.png di sini (domain: smp1sampang.sch.id)
        └── smpn1jeruklegi/   --> Taruh file logo.png di sini (domain: smpn1jeruklegi.sch.id)
```

### Format Logo yang Dianjurkan:
* **Nama file**: Wajib bernama `logo.png`
* **Format**: PNG dengan latar belakang transparan
* **Resolusi Rekomendasi**: `512 x 512` pixel atau `1024 x 1024` pixel (persegi)

---

## 🚀 5. Cara Build Otomatis Sekali Klik (`build_all_schools.sh`)

Script `build_all_schools.sh` di root repository Flutter akan otomatis:
1. Mengambil logo sekolah dari folder di atas dan menggenerasikan ikon Android resmi (menggantikan logo default Flutter).
2. Menyetel nama aplikasi di layar HP menjadi:
   - **Portal SMPN 3 Kedungreja**
   - **Portal SMPN 1 Majenang**
   - **Portal SMPN 1 Sampang**
   - **Portal SMPN 1 Jeruklegi**
3. Mengunci koneksi API ke domain masing-masing sekolah.
4. Menyimpan seluruh hasil APK yang siap dibagikan ke folder `dist/`:
   - `dist/Portal-SMPN-3-Kedungreja-arm64-v8a.apk`
   - `dist/Portal-SMPN-1-Majenang-arm64-v8a.apk`
   - `dist/Portal-SMPN-1-Sampang-arm64-v8a.apk`
   - `dist/Portal-SMPN-1-Jeruklegi-arm64-v8a.apk`

### Perintah Menjalankan:
```bash
./build_all_schools.sh
```
*(Bisa juga dijalankan satu per satu bila hanya ingin meng-compile sekolah tertentu).*

