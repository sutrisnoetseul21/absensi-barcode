# TAHAP 9 - IMPLEMENTATION PLAN: Kalender Hari Libur & Setting Hari Kerja

Sistem akan diperbarui untuk memiliki manajemen hari libur yang terpusat dan pengaturan tipe hari kerja (5 hari / 6 hari), serta menerapkannya secara seragam di seluruh aplikasi (Kios Scan, Dashboard Publik, dan Export Presensi).

## Hasil Verifikasi (Task 0)

1. **Tabel `holidays`**: Sudah ada sejak Tahap 5 dengan kolom `id`, `start_date`, `end_date`, `description`, `type` (enum: `nasional`, `cuti_bersama`, `khusus`), `class_id`, dan `timestamps`. Tidak perlu migration baru untuk struktur tabel ini (kita akan menggunakan tipe enum `khusus` untuk merepresentasikan Khusus Kelas).
2. **Tabel `school_settings`**: Sudah ada dan aktif. Kita hanya perlu menambahkan kolom `work_days_type`.
3. **Kios Scan (Tahap 4)**: Ditemukan di `ProcessScanAction.php`, saat ini hanya mengecek `HariLibur::hariIni()`. Akan diganti ke Service terpusat.
4. **Dashboard Publik (Tahap 5)**: Ditemukan di `GetPublicDashboardDataAction.php`, saat ini masih hardcode pengecekan weekend `!$d->isWeekend()` dan menghitung total hari efektif dengan melupakan libur kelas. Akan diganti ke Service terpusat.
5. **Export Presensi (Tahap 8)**: Ditemukan di `PresensiMatrixExport.php`, masih menggunakan `isWeekend()`. Akan diganti ke Service terpusat.

---

## User Review Required

> [!IMPORTANT]
> **Struktur Enum `type` pada Tabel Holidays**
> Karena tabel `holidays` sudah memiliki kolom `type` dengan nilai enum `['nasional', 'cuti_bersama', 'khusus']`, saya berencana **tidak** membuat migration untuk mengubah enum `khusus` menjadi `khusus_kelas`. Kita akan tetap menggunakan string `'khusus'` di database, tetapi di tampilan UI Filament kita akan menampilkannya sebagai "Khusus Kelas". Apakah ini disetujui untuk menghindari komplikasi modifikasi enum di MySQL?

---

## Proposed Changes

---
### 1. Database Migrations

#### [NEW] database/migrations/[timestamp]_add_work_days_type_to_school_settings_table.php
Menambahkan kolom `work_days_type` (enum: `5_hari`, `6_hari`) ke tabel `school_settings` dengan nilai default `5_hari`.

---
### 2. Models & Services

#### [MODIFY] app/Models/PengaturanSekolah.php
Menambahkan `work_days_type` ke dalam array `$fillable`.

#### [NEW] app/Services/KalenderSekolahService.php
Membuat Service class terpusat. Class ini akan memuat fungsi utama:
- `isHariSekolah(Carbon $date, ?string $classId = null): bool` -> Mengembalikan `true` jika tanggal tersebut adalah hari efektif belajar, dan `false` jika hari libur/weekend.
- `getEffectiveDays(Carbon $start, Carbon $end, ?string $classId = null): int` -> Mengembalikan total hari efektif dalam rentang waktu (untuk Dashboard Publik).

---
### 3. Filament Resources & Pages

#### [NEW] app/Filament/Resources/HariLiburResource.php
Membuat resource CRUD standar untuk mengelola `holidays` (Start Date, End Date, Deskripsi, Tipe, Kelas). Kelas hanya muncul jika tipe adalah `khusus`.

#### [NEW] app/Filament/Pages/SettingLibur.php
Halaman custom di Filament yang berada di bawah grup navigasi "Pengaturan Sistem". 
Isi halaman:
- Form untuk mengubah `work_days_type` (5 hari atau 6 hari).
- FullCalendar widget untuk melihat secara visual jadwal libur sekolah dan menambah hari libur langsung dari kalender.

---
### 4. Patch Retroaktif (Integrasi Service Terpusat)

#### [MODIFY] app/Actions/ProcessScanAction.php (Tahap 4 - Kios Scan)
Mengganti pengecekan libur statis menjadi:
`$isHariSekolah = app(KalenderSekolahService::class)->isHariSekolah($now, $classId);`
Jika bukan hari sekolah, scan ditolak dengan pesan: "Hari ini libur, tidak ada absensi".

#### [MODIFY] app/Actions/GetPublicDashboardDataAction.php (Tahap 5 - Dashboard Publik)
Mengganti loop perhitungan hari efektif (`!$date->isWeekend()`) di baris 43-59 menjadi:
`$effectiveDays = app(KalenderSekolahService::class)->getEffectiveDays($startOfMonth, $endOfMonth, $kelas->id);`
Ini memastikan akurasi % kehadiran per kelas di Wall of Fame dan Bar Chart menjadi sangat presisi (memperhitungkan libur kelas dan cuti).

#### [MODIFY] app/Exports/PresensiMatrixExport.php (Tahap 8 - Export Excel)
Mengganti `$carbonDate->isWeekend()` dengan `!app(KalenderSekolahService::class)->isHariSekolah($carbonDate, $classId)`.

#### [MODIFY] app/Filament/Pages/RekapAbsensiKelas.php & RekapAbsensiSekolah.php
Menggunakan `KalenderSekolahService` untuk mengecek apakah suatu tanggal adalah hari libur. Jika ya, maka cell presensi pada tanggal tersebut akan menampilkan tanda **"L"** (Libur) di tampilan tabel halaman rekap, bukan sekadar kosong atau tanda strip.

---
## Verification Plan

### Manual Verification
1. Ubah setting ke 5 Hari, lalu coba scan pada hari Sabtu -> Akan ditolak sistem.
2. Ubah setting ke 6 Hari, lalu coba scan pada hari Sabtu -> Akan berhasil dan dicatat.
3. Buka halaman Dashboard Publik, verifikasi bahwa persentase Wall of Fame tidak eror dan akurat sesuai pemotongan hari libur.
4. Export file Excel dari presensi, pastikan hari libur terisi `-` (strip/label libur) sesuai setting 5/6 hari.
