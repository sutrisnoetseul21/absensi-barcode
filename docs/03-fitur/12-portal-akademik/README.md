# 🎓 Portal Akademik (`/portal-akademik`)

Portal khusus pengelolaan operasional akademik, master data GTK & kesiswaan, rombongan belajar, jurnal pembelajaran, serta bimbingan guru wali berbasis **Filament Panel** ([`AkademikPanelProvider`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Providers/Filament/AkademikPanelProvider.php)).

---

## 🎯 Tujuan Portal
1. Memisahkan pengelolaan **Master Data & Operasional Akademik** (Kurikulum, Kesiswaan, Jurnal Mengajar, Guru Wali) dari panel administrator teknis (`/admin` & `/portal-sistem`).
2. Mendukung level hak akses terpisah **Editor** (`admin_akses_editor`) dan **Viewer** (`admin_akses_viewer`) bagi staf kurikulum, tata usaha, kepala sekolah, maupun pengawas akademik.
3. Menyediakan perangkat supervisi & analisa KBM tingkat kelas dan siswa (deteksi bolos jam KBM dan rekap isian jurnal guru).

---

## 🔐 Hak Akses & Routing
- **URL Utama**: `/portal-akademik`
- **Panel Provider**: [`AkademikPanelProvider`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Providers/Filament/AkademikPanelProvider.php)
- **Middleware**: [`EnsureCanAccessAkademik`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Http/Middleware/EnsureCanAccessAkademik.php) & `User::canAccessPanel('akademik')`
- **Role yang Diizinkan**: `super_admin`, `admin_portal_akademik`, atau role berawalan `admin_akademik*` (diatur melalui **Manajemen Akses Portal** dengan opsi level `Editor` / `Viewer`).
- **Registrasi Hub Portal**: Terdaftar di [`User::getAccessiblePortals()`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Models/User.php) agar tampil di `/pilih-portal`.

---

## 📋 Peta Menu Portal Akademik

### 1. Grup `Data Master`
1. **Data Guru & GTK** (`/portal-akademik/guru` — [`GuruResource`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Filament/Akademik/Resources/Guru/GuruResource.php))
   - Pengelolaan biodata guru & tenaga kependidikan, NIP/NIY, foto, tautan akun user, dan penugasan mengajar.
2. **Data Siswa** (`/portal-akademik/siswa` — [`SiswaResource`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Filament/Akademik/Resources/Siswa/SiswaResource.php))
   - Pengelolaan data pokok siswa, import siswa baru massal, update data & nomor HP orang tua, serta import foto massal.
3. **Master Kelas & Pembelajaran** (`/portal-akademik/kelas` — [`KelasResource`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Filament/Akademik/Resources/Kelas/KelasResource.php))
   - Pengelolaan kelas/rombel beserta relation manager `KelasAjaranRelationManager` (penugasan wali kelas per tahun ajaran) dan `PengajaranRelationManager` (plotting guru & mata pelajaran per kelas).

### 2. Grup `Akademik`
1. **Enrollment / Rombongan Belajar** (`/portal-akademik/enrollment/enrollments` — [`EnrollmentResource`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Filament/Akademik/Resources/Enrollment/EnrollmentResource.php))
   - Penempatan siswa ke kelas per tahun ajaran, enroll massal, import naik kelas, proses kelulusan kelas akhir (`LuluskanKelas9Action`), dan pembatalan kelulusan massal.
2. **Pindah Kelas** (`/portal-akademik/pindah-kelas` — [`PindahKelasResource`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Filament/Akademik/Resources/PindahKelasResource.php))
   - Mutasi internal perpindahan rombel siswa pada tahun ajaran berjalan.
3. **Siswa Lulus** (`/portal-akademik/siswa-lulus` — [`SiswaLulusResource`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Filament/Akademik/Resources/SiswaLulusResource.php))
   - Arsip daftar siswa yang telah berstatus lulus beserta detail angkatan.
4. **Siswa Mutasi / Keluar** (`/portal-akademik/siswa-mutasi` — [`SiswaMutasiResource`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Filament/Akademik/Resources/SiswaMutasiResource.php))
   - Pencatatan dan riwayat siswa mutasi/pindah/keluar.

### 3. Grup `Jurnal Mengajar`
1. **Daftar Jurnal Pembelajaran** (`/portal-akademik/jurnal-mengajar` — [`JurnalPembelajaranResource`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Filament/Akademik/Resources/JurnalPembelajaranResource.php))
   - Monitoring catatan jurnal KBM harian guru beserta cetak laporan jurnal mengajar.
2. **Monitoring Guru & Kelas** (`/portal-akademik/jurnal-monitoring-guru-kelas` — [`MonitoringGuruKelasJurnalPage`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Filament/Akademik/Pages/MonitoringGuruKelasJurnalPage.php))
   - Rekap kepatuhan pengisian jurnal mengajar per guru dan per kelas berdasarkan periode semester atau bulan.
3. **Analisa Presensi KBM Siswa** (`/portal-akademik/jurnal-analisa-presensi-siswa` — [`AnalisaPresensiKbmSiswaPage`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Filament/Akademik/Pages/AnalisaPresensiKbmSiswaPage.php))
   - Deteksi siswa yang masuk gerbang namun tidak mengikuti jam KBM (*bolos jam pelajaran*), maupun siswa tidak hadir per mata pelajaran dan periode.

### 4. Grup `Guru Wali`
1. **Kelompok Guru Wali** (`/portal-akademik/guru-wali/kelompok` — [`KelompokGuruWaliResource`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Filament/Akademik/Resources/GuruWali/KelompokGuruWaliResource.php))
   - Pengaturan pembagian kelompok bimbingan **Guru Wali** dan daftar anggota siswa binaan (`AnggotaSiswaRelationManager`) sesuai implementasi program pembinaan karakter.
