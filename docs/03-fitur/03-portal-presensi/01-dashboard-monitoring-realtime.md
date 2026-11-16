# 📊 Dashboard Utama & Monitoring Presensi Portal Presensi

Portal Presensi memisahkan **Dashboard Utama** (`/portal-presensi`) sebagai hub pintasan lintas layanan dengan **Dashboard per Submodul** agar tetap ringkas dan terukur seiring bertambahnya jenis presensi.

---

## 1. Dashboard Utama (`/portal-presensi`)

Dikelola oleh [`App\Livewire\PortalPresensi\PortalPresensiHome`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Livewire/PortalPresensi/PortalPresensiHome.php):
- **Hero Welcome Banner**: menampilkan nama pengguna, Tahun Ajaran aktif, tanggal hari ini dalam format Indonesia (`Hari, Tanggal Bulan Tahun`), status hari libur (bila libur), serta pintasan cepat membuka **Kiosk Presensi Datang** dan **Kiosk Sholat Dhuhur**.
- **Grid Kartu Modul + Rekapan Kecil Harian**:
  1. **Presensi Kehadiran** *(fokus absen datang)*: ringkasan ringan **Sudah Presensi** (hadir tepat waktu + terlambat), **Terlambat**, dan **Belum Hadir** (+ ringkasan S/I/A), lengkap dengan tombol menuju **Dashboard Kehadiran**, **Input Manual**, dan **Rekap dan Cetak Laporan**.
  2. **Presensi Sholat Dhuhur** *(tampil jika `enable_sholat_dhuhur` aktif)*: ringkasan **Hadir Sholat**, **Ijin / Haid**, dan **Belum Absen** dari total wajib sholat (siswa Muslim), beserta tombol **Buka Dashboard Dhuhur**, **Input Manual**, dan **Rekap dan Cetak Laporan**.
  3. **Cetak Kartu Presensi** & **Pengaturan & Hari Libur**.

---

## 2. Dashboard Presensi Kehadiran (`/portal-presensi/kehadiran`)

Dikelola oleh [`App\Livewire\PortalPresensiDashboard`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Livewire/PortalPresensiDashboard.php):

### A. Kontrol Header & Opsi Absen Pulang
- **Pemilih Tanggal Indonesia**: menggunakan komponen [`date-indo.blade.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/resources/views/components/portal-presensi/date-indo.blade.php) berformat `Hari, Tanggal Bulan Tahun`.
- **Checkbox `Sertakan Absen Pulang`** (`$includeCheckout`): nilai awal mengikuti `PengaturanSekolah::current()->enable_checkout`. Bila dicentang, kolom **Jam / Status Pulang** tampil di tab *Semua Siswa* serta ikut diekspor pada Excel/PDF harian.

### B. Sistem 4 Tab Filtering & Paginasi
- **`per_kelas`**: rekap harian seluruh kelas lengkap dengan L/P, sakit, izin, alpa, belum absen, dan persentase hadir.
- **`belum_presensi`**: daftar siswa belum scan masuk beserta filter kelas, pencarian, paginasi (`25`/`50`/`100` per halaman), dan tombol kirim WA.
- **`siswa_terlambat`**: daftar siswa terlambat beserta waktu scan dan menit keterlambatan (berpaginasi).
- **`semua_siswa`**: daftar seluruh siswa dengan filter kelas, filter status, pencarian, paginasi server-side agar snapshot Livewire tetap ringan pada data ribuan siswa, serta kolom opsional **Jam / Status Pulang**.

### C. Ekspor Harian
- **Export Excel**: [`App\Exports\RekapPresensiHarianExport`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Exports/RekapPresensiHarianExport.php) (3 sheet: Per Kelas, Belum Presensi, Detail Siswa — kolom Jam & Status Pulang ikut disertakan bila `$includeCheckout` aktif).
- **Cetak PDF**: `Rekap Kelas (PDF)` (`pdf.laporan-rekap-per-kelas`), `Rekap Harian` (`pdf.laporan-presensi-harian-sekolah`), `MBG (PDF)` & `MBG (Excel)`.

---

## 3. Dashboard Presensi Sholat Dhuhur (`/portal-presensi/sholat-dhuhur`)

Dikelola oleh [`App\Livewire\PortalPresensi\SholatDhuhur\DashboardSholatDhuhur`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Livewire/PortalPresensi/SholatDhuhur/DashboardSholatDhuhur.php):
- Pemilih tanggal Indonesia + penanda hari libur/Jumat via `PresensiSholatDhuhurService`.
- Kartu statistik harian (**Hadir Berjamaah**, **Ijin / Halangan**, **Tidak Hadir (Alpa)**, **Belum Absen Sholat** dari total siswa Muslim wajib sholat) + info jumlah Non-Muslim.
- 3 tab interaktif berpaginasi: **Rekap Per Kelas** (dengan pintasan tombol *Input* langsung ke kelas tersebut), **Belum Absen**, dan **Semua Siswa**.
- Tombol **Sinkron Pagi** (menyelaraskan Alpa/Izin/Sakit dari presensi gerbang pagi ke seluruh kelas), **Rekap Kelas (PDF)**, **Export Excel** (`RekapSholatDhuhurHarianExport`), dan **Rekap Harian (PDF)**.
