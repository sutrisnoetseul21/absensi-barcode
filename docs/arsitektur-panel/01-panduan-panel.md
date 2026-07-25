# Panduan Arsitektur Multi-Panel Filament

Sistem Presensi Digital menggunakan arsitektur multi-panel Filament untuk memisahkan *concern* atau tanggung jawab dari masing-masing role pengguna (konsep menuju ERP).

## Struktur Panel Berjalan

Saat ini terdapat 4 panel Filament utama:

1.  **Panel Super Admin (`/admin`)**
    *   **Provider:** `app/Providers/Filament/AdminPanelProvider.php`
    *   **Direktori Resource:** `app/Filament/Resources`
    *   **Tujuan:** Pengaturan root, hak akses sistem, dan semua menu (kecuali yang sengaja disembunyikan).

2.  **Panel Admin Master (`/admin-master`)**
    *   **Provider:** `app/Providers/Filament/MasterPanelProvider.php`
    *   **Direktori Resource:** `app/Filament/Master/Resources`
    *   **Tujuan:** Murni untuk input Data Induk (Tahun Ajaran, Kelas, Siswa, Guru). Dikhususkan untuk staf Tata Usaha.

3.  **Panel Admin Akademik (`/admin-akademik`)**
    *   **Provider:** `app/Providers/Filament/AkademikPanelProvider.php`
    *   **Direktori Resource:** `app/Filament/Akademik/Resources`
    *   **Tujuan:** Operasional kesiswaan (Pembagian Kelas, Kenaikan Kelas, Mutasi, Pindah Kelas).

4.  **Panel Admin Presensi (`/admin-presensi`)**
    *   **Provider:** `app/Providers/Filament/PresensiPanelProvider.php`
    *   **Direktori Resource:** `app/Filament/Presensi/Resources`
    *   **Tujuan:** Operasional harian presensi (Input Presensi, Rekapitulasi, Pengaturan Jam Presensi & Hari Libur). Dikhususkan untuk Guru Piket / BK.

---

## Panduan Membuat Panel Baru (Contoh: Panel Keuangan)

Jika di masa depan Anda perlu membuat panel baru (misalnya panel keuangan dengan URL `/admin-keuangan`), ikuti langkah-langkah berikut:

**Cara 1: Copy-Paste (Direkomendasikan agar tema & logo sama)**
1. Copy file `app/Providers/Filament/AdminPanelProvider.php` dan beri nama `KeuanganPanelProvider.php`.
2. Ubah `namespace App\Providers\Filament;` (tetap sama).
3. Ubah nama class menjadi `class KeuanganPanelProvider extends PanelProvider`.
4. Di dalam fungsi `panel()`, ubah:
    *   `->id('admin-keuangan')`
    *   `->path('admin-keuangan')`
    *   `->discoverResources(in: app_path('Filament/Keuangan/Resources'), for: 'App\Filament\Keuangan\Resources')`
    *   `->discoverPages(in: app_path('Filament/Keuangan/Pages'), for: 'App\Filament\Keuangan\Pages')`
    *   `->discoverWidgets(in: app_path('Filament/Keuangan/Widgets'), for: 'App\Filament\Keuangan\Widgets')`
5. Daftarkan provider baru tersebut di `bootstrap/providers.php` (Laravel 11) atau `config/app.php` (Laravel 10).
6. Buat folder `app/Filament/Keuangan/Resources`, `Pages`, dan `Widgets`.

**Cara 2: Menggunakan Artisan Command**
Anda bisa menggunakan *command* bawaan Filament:
```bash
php artisan make:filament-panel keuangan
```
Namun, cara ini akan membuat panel dengan tema *default* (polos), sehingga Anda harus memindahkan setting *custom logo*, *favicon*, dan warna secara manual dari `AdminPanelProvider.php`.

## Menentukan Resource Masuk ke Panel Mana?

Saat Anda membuat Resource baru (contoh: `php artisan make:filament-resource PembayaranSpp`), Anda bisa menentukan *resource* tersebut akan didaftarkan ke panel mana dengan menambahkan *flag* `--panel`.

```bash
php artisan make:filament-resource PembayaranSpp --panel=admin-keuangan
```
Secara otomatis Filament akan meletakkan file resource tersebut di dalam folder `app/Filament/Keuangan/Resources/`.
