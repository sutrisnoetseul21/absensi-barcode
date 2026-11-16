# 📋 Manajemen Data, Rekapitulasi, Sholat Dhuhur, & Pengaturan

Dokumen ini menjelaskan modul operasional di **Portal Presensi**: Input Manual, Rekap dan Cetak Laporan, Submodul Sholat Dhuhur, Pengaturan Presensi & Hari Libur, serta Cetak Kartu Siswa.

---

## 1. Submodul Presensi Kehadiran

### A. Input Presensi Manual (`/portal-presensi/input-manual`)
Dikelola oleh [`App\Livewire\PortalPresensi\InputPresensiManual`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Livewire/PortalPresensi/InputPresensiManual.php):
- Filter **Tahun Ajaran**, **Kelas**, dan **Tanggal Presensi** menggunakan pemilih kalender Indonesia [`date-indo.blade.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/resources/views/components/portal-presensi/date-indo.blade.php).
- Mendukung status masuk (`hadir`, `telat`, `izin`, `sakit`, `alpa`) serta kolom **Status Pulang** bila `enable_checkout` aktif.
- Menyimpan catatan ke tabel `attendances` dengan penanda `is_manual_input = true`.

### B. Rekap dan Cetak Laporan (`/portal-presensi/cetak-laporan`)
Dikelola oleh [`App\Livewire\PortalPresensi\CetakLaporanPresensi`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Livewire/PortalPresensi/CetakLaporanPresensi.php) (menggabungkan fungsi *Rekap Kelas*, *Rekap Sekolah*, dan *Cetak Laporan*; menu sidebar terpisah lama disembunyikan tanpa menghapus rutenya):
- **4 Mode Laporan**:
  1. **Bulanan** *(Per Kelas)*: matriks harian tanggal 1–31 via `PresensiRekapService::getMonthlyCalendarData()`, ekspor Excel ([`PresensiMatrixExport`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Exports/PresensiMatrixExport.php)) & PDF ([`laporan-presensi-matrix.blade.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/resources/views/pdf/laporan-presensi-matrix.blade.php)).
  2. **Semester** *(Per Kelas — Ganjil Jul–Des / Genap Jan–Jun)*: via `getStudentSemesterYearlyData()`, ekspor [`LaporanPresensiRangeExport`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Exports/LaporanPresensiRangeExport.php) & PDF `laporan-presensi-range`.
  3. **Tahunan** *(Per Kelas — Jul–Jun)*.
  4. **Sekolah** *(Semua Kelas × 12 Bulan)*: via `getYearlySchoolData()`, ekspor [`RekapAbsensiSekolahExport`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Exports/RekapAbsensiSekolahExport.php) & PDF `rekap-absensi-sekolah`.
- **Opsi Centang `Sertakan Absen Pulang`**:
  - Nilai default mengikuti `PengaturanSekolah::current()->enable_checkout`.
  - Jika dicentang pada mode **Bulanan**: setiap sel tanggal menampilkan **2 baris bertumpuk** (atas = status datang `Hadir` / `Telat` / `Izin` / `Sakit` / `Alpa` / `Libur`, bawah = status pulang `Pulang` / `Izin` / `Sakit` / `Alpa` / `-` jika belum absen pulang), plus kolom total **`P`** di sebelah kanan.
  - Jika dicentang pada mode **Semester / Tahunan**: menambah kolom total **`P`** pada blok **TOTAL**.
  - Jika tidak dicentang: laporan hanya menampilkan kode status kedatangan standar (`H / T / I / S / A / L`).

---

## 2. Submodul Presensi Sholat Dhuhur

Tampil pada sidebar accordion **Presensi Sholat Dhuhur** bila `enable_sholat_dhuhur` aktif:
- **Dashboard** (`/portal-presensi/sholat-dhuhur`): pemantauan harian & ekspor ([`DashboardSholatDhuhur`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Livewire/PortalPresensi/SholatDhuhur/DashboardSholatDhuhur.php)).
- **Input Manual** (`/portal-presensi/sholat-dhuhur/input-manual`): [`InputManualSholatDhuhur`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Livewire/PortalPresensi/SholatDhuhur/InputManualSholatDhuhur.php), mendukung status `hadir`, `ijin`, `haid` (khusus siswi, disimpan sebagai `ijin` dengan keterangan `Halangan / Haid`), `tidak_hadir`, sinkronisasi dari presensi pagi, set massal, dan penanda *read-only* bagi siswa Non-Muslim.
- **Rekap dan Cetak Laporan** (`/portal-presensi/sholat-dhuhur/laporan`): [`LaporanSholatDhuhur`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Livewire/PortalPresensi/SholatDhuhur/LaporanSholatDhuhur.php) dengan 4 mode (*Bulanan*, *Semester*, *Tahunan*, dan *Sekolah* harian) beserta ekspor Excel/CSV dan PDF.

---

## 3. Pengaturan Presensi & Hari Libur (`/portal-presensi/pengaturan`)

Dikelola oleh [`App\Livewire\PortalPresensi\PengaturanPresensi`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Livewire/PortalPresensi/PengaturanPresensi.php) dengan 2 tab terpadu:
1. **Tab Aturan Presensi & Otomatisasi**:
   - **Jam Masuk (`checkin_time`)**, **Batas Jam Absen Datang (`batas_scan_datang_time`)**, **Aktifkan Presensi Pulang (`enable_checkout`)** + **Jam Mulai Pulang (`start_scan_out_time`)**, dan **Auto Alpa (`auto_alpa_active` + `auto_alpa_time`)**.
   - Semua input jam memakai pemilih **24 jam WIB** ([`time-24-wib.blade.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/resources/views/components/portal-presensi/time-24-wib.blade.php)).
   - Pengaturan **Koneksi API WhatsApp** tidak ditampilkan di sini karena sudah dipusatkan di **Portal Sistem (`/portal-sistem`)**.
2. **Tab Hari Kerja & Hari Libur**:
   - Pengaturan pola **5 Hari** (Senin–Jumat) atau **6 Hari** (Senin–Sabtu) beserta **Tanggal Mulai Berlaku** format Indonesia (`Hari, Tanggal Bulan Tahun`) dan riwayat perubahan (`work_days_history`).
   - CRUD **Hari Libur** (`nasional`, `cuti_bersama`, `khusus` per kelas), impor libur nasional, aksi **Force Libur** (menghapus catatan presensi di rentang tanggal libur), **Kalender Interaktif FullCalendar** (klik tanggal untuk tambah cepat), serta log aktivitas perubahan pengaturan.

---

## 4. Cetak Kartu Siswa & Barcode (`/portal-presensi/cetak-kartu`)

Komponen Livewire [`App\Livewire\PortalPresensi\CetakKartuSiswa`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Livewire/PortalPresensi/CetakKartuSiswa.php) menghasilkan kartu ber-barcode **Code 128** berbasis NISN atau NIS (`school_settings.barcode_scan_mode`) untuk dicetak per kelas maupun satuan.
