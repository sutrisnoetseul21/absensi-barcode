# 📅 08. Analisa & Rencana Matang Menu 1: Presensi (Mengikuti `/portal-guru/akademik`)

Dokumen ini menyajikan hasil bedah kode (*code analysis*) dari tampilan web Laravel `/portal-guru/akademik` ([`WaliKelasDashboard.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Livewire/WaliKelasDashboard.php)) beserta rencana implementasi persisnya ke dalam tampilan mobile Flutter (`PresensiKelasScreen`).

---

## 🔍 1. Hasil Analisa Blade `/portal-guru/akademik`

Berdasarkan bedah kode pada [`WaliKelasDashboard.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Livewire/WaliKelasDashboard.php) dan 6 *partial view* di [`resources/views/livewire/wali-kelas/`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/resources/views/livewire/wali-kelas-dashboard.blade.php), halaman `/portal-guru/akademik` terdiri dari **7 Komponen Utama**:

### 1. Header Banner & Aksi Utama ([`header.blade.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/resources/views/livewire/wali-kelas/header.blade.php))
* **Judul**: **Presensi Kelas** (*Kelola data presensi, pantau keterlambatan harian, dan lakukan presensi manual untuk siswa di kelas binaan Anda*).
* **Indikator Live Hari Ini**:
  - 🟢 **Sudah Absen Hari Ini**: `total - belum`
  - 🔴 **Belum Absen**: `belum`
* **2 Tombol Aksi Utama di Header**:
  - 🖨️ **Cetak Laporan** (Membuka Modal Cetak Excel / PDF).
  - ✏️ **Input Manual** (Membuka Modal Input Presensi Manual).

### 2. Kartu Filter 3 Dropdown ([`filters.blade.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/resources/views/livewire/wali-kelas/filters.blade.php))
* **Tahun Ajaran** (`selectedAcademicYearId` — default tahun ajaran aktif).
* **Pilih Kelas** (`selectedClassId` — otomatis memuat kelas binaan Wali Kelas, Kelas Pantau BK, atau seluruh kelas bagi Admin/Bypass).
* **Bulan Kehadiran** (`selectedMonthYear` — daftar bulan Juli s/d Desember `start_year` dan Januari s/d Juni `end_year`).

### 3. Toggle Mode & 4 Kartu Statistik Interaktif ([`stats-monthly.blade.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/resources/views/livewire/wali-kelas/stats-monthly.blade.php))
* **Saklar Mode Rekap (`rekapMode`)**: **Rekap Harian** vs **Rekap Bulanan**.
* **Sub-info Ringkas**:
  - Saat *Rekap Harian*: Menampilkan Hari/Tanggal, Total Siswa, dan `% Kehadiran`.
  - Saat *Rekap Bulanan*: Menampilkan Potensi Maksimal (`Total Siswa × Hari Efektif`).
* **Grid 4 Kartu Statistik (2×2 di layar HP)**:
  - **Mode Harian** *(Bisa diklik untuk memfilter daftar siswa di bawahnya)*:
    1. 🟢 **Hadir Tepat Waktu**: `hadir / total` + badge `%`.
    2. 🟠 **Terlambat**: `telat / total` + badge `Telat / Nihil`.
    3. 🌸 **Sakit / Izin**: `(sakit + izin) / total` + badge rincian `XS • YI`.
    4. 🟣/⚫ **Belum Absen**: `belum / total` + badge `Tuntas` (jika 0) atau `Perlu Tindakan` (jika $>0$) + info jumlah Alpa.
  - **Mode Bulanan**:
    1. 🟢 **Total Hadir**: `hadir / max_possible` + `%`.
    2. 🟠 **Total Telat**: `telat / max_possible`.
    3. 🌸 **Total Sakit/Izin**: `(sakit + izin) / max_possible`.
    4. ⚫ **Total Alpa**: `alpa / max_possible`.

### 4. Pencarian, Filter Chips & Daftar Siswa ([`table.blade.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/resources/views/livewire/wali-kelas/table.blade.php))
* **Kolom Cari Cepat**: Cari nama siswa atau NISN secara *real-time*.
* **View 1: Presensi Hari Ini (Tampilan Kartu Mobile)**:
  - **Baris Filter Chips Horizontal**: `Semua (N)`, `Belum Absen (N)`, `Hadir (N)`, `Telat (N)`, `Sakit / Izin (N)`, dan `Alpa (N)` + tombol pintas `Input Presensi`.
  - **Banner Apresiasi**: Muncul otomatis jika filter `Belum Absen` dipilih dan nilainya `0` (*"Luar Biasa! Semua Siswa Sudah Presensi 🎉"*).
  - **Kartu Siswa Ringkas (Mobile Card)**:
    - Avatar Inisial 2 Huruf berwarna sesuai status.
    - Nama Siswa & NISN.
    - Badge Status: `Hadir (06:45)`, `Telat (15m)`, `Izin`, `Sakit`, `Alpa`, atau `Belum Absen` (berdenyut).
    - Box `Catatan` (jika ada) dan info `Scan Pulang: HH:mm WIB` (jika ada).
    - **Footer Kartu Siswa**:
      - Link **Detail Siswa >** (Menuju rekam jejak kalender individual siswa).
      - Tombol **WA Ortu** (hijau, jika status `belum` & fitur aktif) atau **WA Telat** (kuning, jika status `telat` & fitur aktif).
      - Tombol **Input Presensi / Ubah** (membuka modal input manual).
* **View 2: Tabel Matriks Bulanan (1 s/d 31 Hari)**:
  - Legenda kode warna: `H` (Hadir - Hijau), `T` (Telat - Kuning), `I` (Izin - Biru), `S` (Sakit - Ungu), `A` (Alpa - Merah), `L` (Libur - Abu).
  - Tabel *scrollable* horizontal dengan kolom **Nama Siswa** yang *sticky* di sebelah kiri (dilengkapi badge peringatan `⚠️ ≥3 Alpa` atau `⚠️ ≥100mnt Telat`), kolom tanggal `1..31`, serta kolom rekap `H | T | I | S | A` di ujung kanan.

### 5. Modal Input Presensi Manual ([`modal-input.blade.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/resources/views/livewire/wali-kelas/modal-input.blade.php))
* **Pemilih Tanggal Input (`inputDate`)**: Otomatis mendeteksi apakah tanggal tersebut **Hari Libur Sekolah** (`KalenderSekolahService::isHariSekolah`). Jika hari libur, tombol simpan dikunci dengan peringatan merah.
* **Fitur Set Massal (Bulk Action)**:
  - Dropdown **Set Massal Datang**: `⚡ Hadir Semua`, `Terlambat Semua`, `Izin Semua`, `Sakit Semua`, `Alpa Semua`.
  - Dropdown **Set Massal Pulang** (jika `enable_checkout` aktif): `⚡ Pulang Semua`, `Izin Semua`, `Sakit Semua`, `Alpa Semua`.
* **Daftar Input Per Siswa**:
  - **Proteksi Scan Kiosk (`🔒 Terkunci`)**: Jika siswa sudah scan langsung di mesin kiosk (`is_manual_input == false` dan status `hadir`/`telat`), status datang dikunci agar guru tidak salah timpa, namun status pulang tetap boleh diisi.
  - Input **Telat (Menit)**: Muncul otomatis hanya jika status datang dipilih `Terlambat`.
  - Sinkronisasi otomatis: Jika status datang dipilih `Izin`, `Sakit`, atau `Alpa`, maka status pulang otomatis mengikuti.

### 6. Modal Kirim WhatsApp Ortu via Sistem Gateway ([`wali-kelas-dashboard.blade.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/resources/views/livewire/wali-kelas-dashboard.blade.php))
* Menampilkan Nama Siswa, Kelas, NISN, dan Status (`Belum Presensi` / `Terlambat`).
* Input **Nomor WhatsApp Orang Tua** (otomatis diformat dari `08...` ke `628...`, dan bisa diedit langsung).
* Textarea **Isi Pesan WhatsApp** yang terisi otomatis dari template `PresensiNotificationSetting::renderTemplate()` + tombol **Reset Format Asli**.
* Tombol **Kirim Sekarang (via Sistem)**: Mengirim pesan langsung melalui `WhatsAppGatewayService` di server tanpa perlu membuka WhatsApp Web.

### 7. Modal Cetak Laporan Presensi ([`modal-cetak.blade.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/resources/views/livewire/wali-kelas/modal-cetak.blade.php))
* Pilihan Jenis Laporan: **Bulanan** (pilih bulan), **Semester** (Ganjil/Genap), atau **Tahunan**.
* 2 Tombol Unduh: **Excel (`.xlsx`)** dan **PDF (`.pdf`)**.

---

## 🛠️ 2. Rencana Implementasi Teknis (Backend API & Flutter UI)

### A. Pembuatan Controller API Backend (`GuruPresensiController.php`)
Kita akan membuat `app/Http/Controllers/Api/Mobile/GuruPresensiController.php` yang memanfaatkan *service* resmi yang sama dengan Livewire (`PresensiRekapService`, `KalenderSekolahService`, `WhatsAppGatewayService`):

| No | Method & Endpoint | Fungsi (Setara Method di `WaliKelasDashboard`) |
| :-: | :--- | :--- |
| **1** | `GET /api/v1/guru/dashboard-summary` | Mengambil angka badge untuk **9 Kotak Menu** di beranda Portal Guru (`kelasAmpuCount`, `jurnalHariIniCount`, `pendingLeaveRequestsCount`, `activeBooksCount`, dll.). |
| **2** | `GET /api/v1/guru/presensi/dashboard` | Memuat daftar Tahun Ajaran, Kelas Binaan, Bulan, `todayStats`, `classMonthlyStats`, daftar `students` + `todayAttendances` (lengkap dengan template teks WA), `monthlyStats` (matriks 1–31), dan `alerts`. |
| **3** | `GET /api/v1/guru/presensi/manual-input` | Memuat daftar siswa pada tanggal tertentu (`?class_id=&date=`) beserta status `is_holiday`, `enable_checkout`, dan flag `is_locked` (proteksi hasil scan kiosk). |
| **4** | `POST /api/v1/guru/presensi/manual-input` | Menyimpan presensi manual satuan/massal dengan validasi hari libur, proteksi scan kiosk, dan pencatatan histori `note` otomatis. |
| **5** | `POST /api/v1/guru/presensi/send-wa` | Mengirim pesan WhatsApp ke orang tua siswa langsung melalui `WhatsAppGatewayService`. |
| **6** | `GET /api/v1/guru/presensi/export` | Mengunduh/membuka file laporan presensi **PDF** atau **Excel** (Bulanan, Semester, Tahunan). |
| **7** | `GET /api/v1/guru/presensi/student/{id}` | Memuat halaman **Detail Siswa** (kalender absensi bulanan per siswa, persentase hadir, dan aktivitas terakhir sesuai `WaliKelasStudentDetail`). |

---

### B. Pembuatan Layar Flutter (`apk-android-presensi`)

1. **`lib/features/guru/screens/dashboard_guru_screen.dart`**:
   - Menampilkan **Banner Sambutan Guru** dan **Grid 9 Kotak Menu** (tanpa sidebar kiri).
2. **`lib/features/guru/screens/presensi_kelas_screen.dart`**:
   - Mengikuti persis struktur visual `/portal-guru/akademik` (Header Banner, 3 Filter Dropdown, Toggle Harian/Bulanan, 4 Kartu Statistik Interaktif, Filter Chips, Kartu Siswa Mobile dengan WA Ortu/WA Telat, Tabel Matriks Bulanan 1–31 dengan kolom sticky & badge warning, Modal Input Manual dengan Set Massal & proteksi scan kiosk, Modal WA Ortu Gateway, serta Modal Cetak PDF/Excel).
3. **`lib/features/guru/screens/detail_presensi_siswa_screen.dart`**:
   - Halaman detail kalender absensi individual siswa saat guru menekan **Detail Siswa >**.

