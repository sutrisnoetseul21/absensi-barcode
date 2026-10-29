# 🕌 Plan Matang Tahap 2 (Menu 2): Presensi Sholat Dhuhur (`/portal-guru/sholat-dhuhur`)

Dokumen ini merinci hasil analisa mendalam dan rancangan implementasi untuk **Menu 2: Presensi Sholat Dhuhur** pada aplikasi mobile Android, yang diselaraskan 100% dengan fitur web Livewire [`WaliKelasSholatDhuhur.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Livewire/PortalGuru/SholatDhuhur/WaliKelasSholatDhuhur.php) (`/portal-guru/sholat-dhuhur`) beserta penyesuaian UX mobile yang telah disepakati pada Menu 1.

---

## 1. Bedah Fitur & Aturan Bisnis `/portal-guru/sholat-dhuhur`

Berdasarkan audit pada [`PengaturanPresensiPage.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Filament/Presensi/Pages/PengaturanPresensiPage.php) (`/admin/pengaturan-presensi`), [`WaliKelasSholatDhuhur.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Livewire/PortalGuru/SholatDhuhur/WaliKelasSholatDhuhur.php), dan [`PresensiSholatDhuhurService.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Services/PresensiSholatDhuhurService.php):

### A. Saklar Utama dari Pengaturan Presensi (`/admin/pengaturan-presensi`)
Pada halaman Admin **Pengaturan Presensi** ([`PengaturanPresensiPage.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Filament/Presensi/Pages/PengaturanPresensiPage.php) baris 114–126) yang tersimpan di model singleton `PengaturanSekolah::current()`:
1. **`enable_sholat_dhuhur` (Aktifkan Presensi Sholat Dhuhur)**:
   - **Di Dashboard Portal Guru (`dashboard_guru_screen.dart` & `guru-main-dashboard.blade.php`)**:
     - API `/api/v1/guru/dashboard-summary` dan `/api/v1/school-info` mengirimkan boolean `enable_sholat_dhuhur: (bool) ($sekolah?->enable_sholat_dhuhur ?? true)`.
     - **Jika `enable_sholat_dhuhur == false` (Dinonaktifkan di Admin)**: Kotak **Menu 2 (Presensi Sholat Dhuhur)** di beranda Portal Guru Android **otomatis disembunyikan (`if (_enableSholatDhuhur)`)**, persis seperti direktif `@if(\App\Models\PengaturanSekolah::current()->enable_sholat_dhuhur ?? true)` pada [`guru-main-dashboard.blade.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/resources/views/livewire/guru-main-dashboard.blade.php#L186) dan [`portal.blade.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/resources/views/components/layouts/portal.blade.php#L805).
     - **Proteksi Endpoint API (`GuruSholatDhuhurController`)**: Sama seperti baris `abort_if(!(\App\Models\PengaturanSekolah::current()->enable_sholat_dhuhur ?? true), 403, 'Fitur Sholat Dhuhur sedang dinonaktifkan.')` pada [`WaliKelasSholatDhuhur.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Livewire/PortalGuru/SholatDhuhur/WaliKelasSholatDhuhur.php#L64), seluruh endpoint `/api/v1/guru/sholat-dhuhur/*` akan menolak akses dengan HTTP `403` dan pesan `'Fitur Presensi Sholat Dhuhur sedang dinonaktifkan di Pengaturan Presensi.'` apabila saklar ini mati.
2. **`enable_sholat_jumat` (Aktifkan Presensi Sholat pada Hari Jumat)**:
   - Hanya muncul/berlaku jika `enable_sholat_dhuhur` aktif.
   - Jika `enable_sholat_jumat == false` (default): Hari Jumat otomatis ditandai sebagai **Hari Libur Sholat (`L` / `Hari Jumat`)** baik di matriks bulanan maupun di modal input.
   - Jika `enable_sholat_jumat == true`: Hari Jumat dihitung sebagai hari efektif ibadah sholat dan dapat diinput seperti biasa.

### B. Penguncian Tahun Ajaran & Hak Akses Kelas Binaan
1. **Tahun Ajaran Aktif Terkunci (`Read-Only`)**:
   - Otomatis dikunci ke Tahun Ajaran Aktif (`AcademicYear::where('is_active', true)`).
   - Menampilkan badge `AKTIF` tanpa mengizinkan pemilihan tahun ajaran lampau di aplikasi mobile.
2. **Pilih Kelas Binaan (1 Dropdown Tunggal)**:
   - Menyaring kelas binaan guru pada tahun ajaran aktif (baik sebagai **Wali Kelas** 1–2 kelas maupun **Guru BK** multi-kelas binaan sesuai pengaturan `/admin/manajemen-akses-portal`).
   - Jika hanya 1 kelas binaan: tampil sebagai kartu terkunci (`Read-Only`).
   - Jika lebih dari 1 kelas binaan: tampil 1 dropdown **Pilih Kelas Binaan** saja (tanpa mengulang barisan *chip* kelas di bawahnya).
3. **Bulan Kehadiran & Koordinator PAI**:
   - Dropdown **Bulan Kehadiran** (mengikuti rentang tahun ajaran aktif).
   - Dropdown **Koordinator Sholat Dhuhur (Guru PAI)**: otomatis memilih guru PAI pertama sebagai default, digunakan saat mencetak laporan PDF/Excel.

### B. Penanganan Siswa Non-Muslim & Hari Libur Sholat
1. **Siswa Non-Muslim (`is_non_muslim`)**:
   - Siswa dengan kolom `religion` selain `'islam'` otomatis ditandai sebagai `Non-Muslim` (`NM` pada matriks bulanan).
   - Tidak dihitung dalam total siswa wajib sholat (`todayStats['total']`) maupun persentase kepatuhan bulanan, dan barisnya bersifat *read-only* (tidak bisa diinput status sholatnya).
2. **Hari Libur Sholat Dhuhur (`PresensiSholatDhuhurService::isHariSholatDhuhur`)**:
   - Hari Jumat otomatis dianggap libur sholat dhuhur di sekolah kecuali pengaturan `enable_sholat_jumat` diaktifkan pada `PengaturanSekolah`.
   - Hari Minggu (dan Sabtu jika `5_hari` kerja) serta tanggal pada tabel `HariLibur` otomatis ditandai `L` (Libur) dan dikecualikan dari hari efektif sholat.

### C. Sinkronisasi dari Presensi Pagi (`Hanya Izin, Sakit, atau Alpa`)
Sesuai instruksi pengguna dan logika resmi di `PresensiSholatDhuhurService`:
- **Aturan Ketat Sinkron Presensi Pagi**:
  - **HANYA** mengambil data siswa Muslim yang pada presensi pagi (`Presensi`) berstatus **`izin`**, **`sakit`**, atau **`alpa`** pada tanggal tersebut:
    - `izin` di presensi pagi $\rightarrow$ disimpan ke Sholat Dhuhur sebagai **`ijin`** dengan keterangan `'Izin di presensi pagi'`.
    - `sakit` di presensi pagi $\rightarrow$ disimpan ke Sholat Dhuhur sebagai **`ijin`** dengan keterangan `'Sakit di presensi pagi'`.
    - `alpa` di presensi pagi $\rightarrow$ disimpan ke Sholat Dhuhur sebagai **`tidak_hadir`** dengan keterangan `'Alpa di presensi pagi'`.
  - Siswa yang berstatus **`hadir`** atau **`telat`** pada presensi pagi **TIDAK** diambil/diubah (tetap menunggu scan barcode sholat di mushola atau input manual wali kelas).
- Tersedia di **2 titik**:
  1. **Tombol `Sinkron Presensi Pagi` di Header Banner Utama**: langsung menyinkronkan data izin/sakit/alpa presensi pagi ke tabel `presensi_sholat_dhuhur` pada tanggal terpilih.
  2. **Tombol `Sinkron Presensi Pagi` di dalam Modal Input Massal**: menarik status izin/sakit/alpa pagi ke daftar form modal sebelum disimpan.

### D. Input Presensi Sholat: Pop-Up 1 Siswa vs Modal Massal 1 Kelas
1. **Pop-Up Input 1 Siswa (Saat Klik `Input Presensi` / `Ubah` di Kartu Siswa Harian)**:
   - Sama seperti revisi pada Menu 1 Presensi Pagi, menekan tombol **`Input Presensi`** atau **`Ubah`** pada kartu nama siswa di daftar harian **hanya membuka pop-up khusus untuk 1 siswa tersebut**.
   - Pilihan status pada pop-up 1 siswa:
     - **`Hadir`** (`hadir` — Hadir Berjamaah)
     - **`Izin / Sakit`** (`ijin`)
     - **`🩸 Halangan / Haid`** (`haid` — khusus tampil jika siswa perempuan `gender === 'P'`, disimpan di DB sebagai `status = 'ijin'` dengan keterangan `'Halangan / Haid'`)
     - **`Tidak Hadir / Alpa`** (`tidak_hadir`)
   - Dilengkapi field **Catatan / Keterangan** dan tombol **Simpan Presensi**.
2. **Modal Input Massal Seluruh Siswa Kelas (Saat Klik Tombol Atas `Input Presensi Sholat`)**:
   - Membuka *bottom sheet* penuh berisi seluruh siswa di kelas binaan.
   - Dilengkapi pemilih tanggal (`Pilih Tanggal`), indikator hari libur sholat, tombol **`Sinkron Presensi Pagi`**, dan dropdown **`Set Massal`** (`Hadir Semua`, `Izin Semua`, `Alpa Semua`).

---

## 2. Desain Kontrak REST API Baru (`GuruSholatDhuhurController.php`)

Kita akan membuat controller baru [`app/Http/Controllers/Api/V1/GuruSholatDhuhurController.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Http/Controllers/Api/V1/GuruSholatDhuhurController.php) dengan route di bawah prefix `/api/v1/guru/sholat-dhuhur`:

| Method | Endpoint | Fungsi Utama |
| :--- | :--- | :--- |
| `GET` | `/api/v1/guru/sholat-dhuhur/dashboard` | Mengembalikan Tahun Ajaran Aktif, daftar kelas binaan (Wali Kelas + BK), daftar bulan, daftar Koordinator PAI, `todayStats` (`hadir`, `ijin`, `tidak_hadir`, `belum`, `non_muslim_count`, `persentase_hadir`), daftar siswa harian beserta status sholat & template WA Ortu, serta matriks bulanan 1–31 (`H`, `I`, `A`, `L`, `NM`, `-`). |
| `GET` | `/api/v1/guru/sholat-dhuhur/manual-input` | Mengambil daftar siswa kelas pada tanggal `date` beserta status sholat saat ini (`hadir`, `ijin`, `haid`, `tidak_hadir`), flag `is_non_muslim`, flag `is_pagi_absent`, dan info apakah tanggal tersebut hari libur sholat (`is_holiday`, `holiday_desc`). |
| `POST` | `/api/v1/guru/sholat-dhuhur/sync-morning` | Menjalankan sinkronisasi dari Presensi Pagi pada tanggal `date` dan `class_id` khusus untuk siswa yang berstatus **`izin`**, **`sakit`**, atau **`alpa`** di presensi pagi. |
| `POST` | `/api/v1/guru/sholat-dhuhur/manual-input` | Menyimpan input presensi sholat dhuhur (mendukung penyimpanan 1 siswa maupun massal 1 kelas, termasuk konversi otomatis status `'haid'` menjadi `status = 'ijin'` + `keterangan = 'Halangan / Haid'`). |
| `POST` | `/api/v1/guru/sholat-dhuhur/send-wa` | Mengirim pesan WhatsApp Sholat Dhuhur ke nomor orang tua siswa via sistem WhatsApp Gateway (`WhatsappService`). |
| `GET` | `/api/v1/guru/sholat-dhuhur/export` | Mengunduh laporan resmi: `rekap_kelas_pdf` (PDF Harian Kelas), `rekap_harian_pdf` (PDF Rekap Harian Sekolah), `excel` (Excel Bulanan `.xlsx`), `bulanan_pdf` (Matriks Bulanan PDF), atau `semester_pdf` (Rekap Semester 6 Bulan PDF). |

---

## 3. Rancangan Antarmuka Mobile (`presensi_sholat_dhuhur_screen.dart`)

Kita akan membuat layar baru [`lib/features/guru/screens/presensi_sholat_dhuhur_screen.dart`](file:///home/sutris-remote/Documents/projek-laravel/apk-android-presensi/lib/features/guru/screens/presensi_sholat_dhuhur_screen.dart) dan menghubungkannya dari kartu menu ke-2 (**Sholat Dhuhur**) di [`dashboard_guru_screen.dart`](file:///home/sutris-remote/Documents/projek-laravel/apk-android-presensi/lib/features/guru/screens/dashboard_guru_screen.dart):

1. **Header Banner Emerald–Teal (Khas Sholat Dhuhur)**:
   - Judul **Presensi Sholat Dhuhur** dengan badge nama kelas & peran (`Wali Kelas` / `Binaan BK`).
   - Pill indikator real-time: **`Hadir Berjamaah: X`** dan **`Belum Sholat: Y`**.
   - 3 Tombol Aksi Utama di Banner:
     - **`Sinkron Presensi Pagi`** (Amber button — menyinkronkan siswa yang Izin/Sakit/Alpa di pagi hari).
     - **`Cetak Laporan`** (Membuka modal cetak PDF Bulanan / Semester / Harian / Excel).
     - **`Input Presensi Sholat`** (Membuka modal input massal seluruh kelas).
2. **Kartu Filter Terkunci & Rapi**:
   - **Tahun Ajaran Aktif**: Terkunci (`AKTIF`).
   - **Pilih Kelas Binaan**: 1 Dropdown tunggal (tanpa duplikasi chip di bawahnya).
   - **Bulan Kehadiran** & **Koordinator PAI**.
3. **Mode Rekap Harian & Rekap Bulanan**:
   - **Mode Harian**:
     - Pemilih **Tanggal Harian** (`selectedDailyDate`) + tombol cepat cetak **Rekap Kelas (PDF)** & **Export Excel**.
     - **4 Kotak Statistik Interaktif (2x2 Grid)**:
       1. **Hadir Berjamaah** (Emerald gradient — klik untuk filter `hadir`)
       2. **Ijin / Halangan** (Blue-Indigo gradient, badge `Haid / Sakit` — klik untuk filter `ijin`)
       3. **Tidak Hadir** (Rose-Pink gradient — klik untuk filter `tidak_hadir`)
       4. **Belum Absen Sholat** (Slate/Indigo gradient — klik untuk filter `belum`)
     - **Filter Chips Horizontal**: `Semua`, `Belum Absen Sholat`, `Hadir Berjamaah`, `Ijin / Halangan`, `Tidak Hadir`, `Non-Muslim` + Kolom Pencarian Nama/NISN + tombol `Segarkan`.
     - **Kartu Siswa Harian**:
       - Menampilkan inisial, nama, NISN, L/P, badge status (`Hadir (12:15)`, `Halangan / Haid`, `Ijin`, `Tidak Hadir`, `Belum Sholat`, atau `Non-Muslim`), serta catatan keterangan.
       - Tombol **`WA Ortu`** (untuk status `belum` atau `tidak_hadir` jika notifikasi aktif) dan tombol **`Input Presensi` / `Ubah`** yang membuka **Pop-Up Khusus 1 Siswa**.
   - **Mode Bulanan**:
     - **4 Kotak Statistik Bulanan (2x2 Grid)**: `Total Hadir`, `Total Ijin/Halangan`, `Total Alpa`, dan `% Kepatuhan` (`X Hari Efektif`).
     - **Tabel Matriks Bulanan 1–31**: Kolom Nama Siswa *sticky* di kiri, scroll horizontal tanggal 1 s/d 31 dengan kode warna `H` (Hijau), `I` (Biru), `A` (Merah), `L` (Abu-abu Libur), `NM` (Indigo Non-Muslim), diikuti kolom total `H`, `I`, `A`, dan `%`.

---

## 4. Langkah Pengerjaan Setelah Plan Disetujui

1. **Backend Laravel (`projek-absensi-barcode`)**:
   - Membuat [`GuruSholatDhuhurController.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Http/Controllers/Api/V1/GuruSholatDhuhurController.php).
   - Mendaftarkan route `/api/v1/guru/sholat-dhuhur/*` pada [`routes/api.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/routes/api.php).
2. **Frontend Flutter (`apk-android-presensi`)**:
   - Menambahkan metode API Sholat Dhuhur pada [`guru_presensi_repository.dart`](file:///home/sutris-remote/Documents/projek-laravel/apk-android-presensi/lib/features/guru/repositories/guru_presensi_repository.dart).
   - Membangun [`presensi_sholat_dhuhur_screen.dart`](file:///home/sutris-remote/Documents/projek-laravel/apk-android-presensi/lib/features/guru/screens/presensi_sholat_dhuhur_screen.dart) lengkap dengan Pop-Up Input 1 Siswa, Modal Input Massal, Sinkron Presensi Pagi (khusus Izin/Sakit/Alpa), Modal WA Ortu, dan Cetak Laporan.
   - Menghubungkan navigasi dari tombol menu ke-2 (**Sholat Dhuhur**) pada [`dashboard_guru_screen.dart`](file:///home/sutris-remote/Documents/projek-laravel/apk-android-presensi/lib/features/guru/screens/dashboard_guru_screen.dart).
3. **Verifikasi & Analisis Statis**:
   - Menjalankan `analyze_files` untuk memastikan 0 error/warning di Flutter dan menguji endpoint API Laravel.

