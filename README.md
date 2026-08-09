# ERP Presensi Digital & Portal Perpustakaan SMP Negeri 3 Kedungreja

Sistem ERP (Enterprise Resource Planning) berbasis Laravel 12 & Filament untuk pengelolaan presensi digital barcode, manajemen akademik, sirkulasi perpustakaan, serta manajemen portal terpadu SMP Negeri 3 Kedungreja.

---

## 🌟 Fitur Utama System

### 1. 📲 Presensi Digital & Kiosk Barcode
- **Kiosk Scan Barcode**: Scan NISN / NIS cepat untuk presensi harian siswa dengan feedback suara & statistik langsung.
- **Portal Guru & Wali Kelas (`/portal-guru`)**: Monitoring kehadiran siswa per kelas, penginputan absensi manual, cetak rekapitulasi, dan manajemen profil siswa.
- **Portal Siswa (`/portal-siswa`)**: Cek riwayat kehadiran pribadi, informasi profil, dan cetak kartu NISN mandiri.

### 2. 📚 ERP Portal Perpustakaan (`/portal-perpustakaan`)
- **Katalog & Inventaris Buku**: Manajemen data buku, kategori, klasifikasi DDC, eksemplar, dan pencetakan barcode / label spine.
- **Smart Sirkulasi & Kiosk Kunjungan**: Transaksi peminjaman & pengembalian buku cepat berbasis barcode scanner, riwayat denda, serta kiosk presensi pengunjung perpustakaan.

### 3. ⚙️ Manajemen Admin Portal (`/admin/pengaturan-sekolah`)
- **Status Maintenance Portal (Siswa, Guru, Perpus)**: Sakelar (*toggle*) terpusat untuk mematikan/menghidupkan akses portal publik dengan pesan *welcome message* dinamis per portal.
- **Pengumuman Global**: Pita pengumuman (*announcement banner/marquee*) dinamis berbasis Rich Editor yang muncul di bagian atas layar seluruh portal aktif.
- **WhatsApp Notification Gateway**: Modul pengaturan notifikasi via Evolution API untuk pengiriman *real-time alert* (Hadir/Telat/Sakit) dan rekap terjadwal (Laporan Kelas & Rekap Seluruh Sekolah).

### 4. 🔑 Manajemen Akses Portal (`/admin/manajemen-akses-portal`)
- **Akses Portal Guru**:
  - *Wali Kelas Utama*: Akses terbatas hanya pada 1 kelas binaannya.
  - *Akses Kelas Pilihan*: Pengaturan multi-select kelas spesifik (misal: 7A, 7B, 7C) pada tahun ajaran aktif.
  - *Akses Semua Kelas (Bypass Mode)*: Permission `portal_guru:akses_semua_kelas` untuk mengakses seluruh kelas (7A - 9C).
- **Akses Portal Perpustakaan**:
  - Penugasan terpusat role `petugas_perpustakaan` untuk pengguna (Guru, Staf TU, atau Petugas Khusus).

---

## 🚀 Arsitektur Sistem

- **Framework Core**: Laravel 12.x & PHP 8.4
- **Admin Panel**: Filament v3/v4 (Multi-Panel Architecture: Super Admin, Master Data, Akademik, Presensi, Perpustakaan)
- **Frontend Livewire**: Livewire v3, Alpine.js v3, Tailwind CSS v3
- **Database**: MySQL (Spesifikasi UUID & Multi-Table Relational Schema)

---

## 📁 Dokumentasi Lengkap

Dokumentasi arsitektur, panduan seeder, dan log pengembangan dapat ditemukan di folder [`docs/`](docs/):

- [`docs/README.md`](docs/README.md) - Panduan Utama Dokumentasi System
- [`docs/pengaturan-admin-seeder/panduan.md`](docs/pengaturan-admin-seeder/panduan.md) - Panduan Seeder & Manajemen Akses Portal
- [`docs/penjelasan-relasi-data.md`](docs/penjelasan-relasi-data.md) - Penjelasan Struktur Relasi & Skema Database
- [`docs/progres-development/`](docs/progres-development/) - Catatan Tahapan Development (Fase 1 Presensi & Fase 2 ERP Perpustakaan)
- [`docs/production-readiness-checklist-scan.md`](docs/production-readiness-checklist-scan.md) - Checklist Persiapan Production & Penanganan Konkurensi Kiosk Scan Barcode

---

## 🛠️ Cara Instalasi & Jalankan Lokal

1. **Clone repository & Install Dependencies**:
   ```bash
   composer install
   npm install && npm run build
   ```

2. **Pengaturan Environment**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Migrasi Database & Seeders**:
   ```bash
   php artisan migrate --seed
   ```

4. **Jalankan Development Server**:
   ```bash
   php artisan serve --port=8001
   ```

---

*SMP Negeri 3 Kedungreja © 2026 - ERP Presensi Digital & Perpustakaan Sekolah*
