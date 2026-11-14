# ⚙️ Portal Sistem (`/portal-sistem`)

Portal khusus Administrator Sistem & Keamanan ERP berbasis **Filament Panel** (`SistemPanelProvider`) untuk mengelola akun admin/role, hak akses seluruh portal ERP, gateway WhatsApp, pemisahan notifikasi WA per modul, mode pemeliharaan (*maintenance*), serta tema visual sistem.

---

## 🎯 Tujuan Portal
1. Memisahkan pengaturan teknis sistem, otorisasi, dan gateway notifikasi dari portal operasional harian (`/admin`, `/portal-presensi`, `/portal-akademik`, `/portal-web`, dll.).
2. Menyediakan pusat kendali akses portal (*RBAC & Portal Access*) dengan otorisasi otomatis penuh bagi akun **Super Admin**.
3. Memecah konfigurasi notifikasi WhatsApp secara modular per domain (**Presensi**, **Jurnal Mengajar**, **SPIKAP**, **Guru Wali**, dan **Pelayanan Publik**) agar tidak saling tumpang tindih.

---

## 🔐 Hak Akses & Routing
- **URL Utama**: `/portal-sistem`
- **Panel Provider**: [`SistemPanelProvider`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Providers/Filament/SistemPanelProvider.php)
- **Middleware**: [`EnsureCanAccessSistem`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Http/Middleware/EnsureCanAccessSistem.php) & `User::canAccessPanel('sistem')`
- **Role yang Diizinkan**: `super_admin` (otomatis akses penuh pada seluruh portal dan seeder) atau `admin_portal_sistem`.
- **Registrasi Hub Portal**: Terdaftar di [`User::getAccessiblePortals()`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Models/User.php) agar tampil di `/pilih-portal`.

---

## 📋 Peta Menu Portal Sistem

### 1. Grup `Manajemen Akses`
1. **Manajemen Admin** (`/portal-sistem/users` — `UserResource`)
   - Pengelolaan akun pengguna/admin sistem dan penetapan role Spatie (`super_admin`, `admin_portal_sistem`, `admin_portal_akademik`, `admin_portal_presensi`, `admin_portal_setup`, `admin_portal_web`, dll.).
2. **Manajemen Akses Portal** (`/portal-sistem/manajemen-akses-portal` — `ManajemenAksesPortalResource`)
   - Mengatur izin login dan batasan akses per portal:
     - **Portal Guru** (`/portal-guru`) + batasan kelas & supervisi jurnal
     - **Portal Perpustakaan** (`/portal-perpustakaan`)
     - **Portal Presensi** (`/portal-presensi`)
     - **Portal Web Sekolah** (`/portal-web`)
     - **Portal Setup & Instalasi** (`/portal-setup`)
     - **Portal Akademik** (`/portal-akademik`) + level hak akses (`Editor` / `Viewer`)
     - **Portal Sistem** (`/portal-sistem`)
   - **Otomasi Super Admin**: Akun dengan status `isSuperAdmin()` otomatis menampilkan seluruh toggle akses dalam kondisi aktif & terkunci (`disabled`) dengan badge penanda, serta disinkronkan melalui `SuperAdminSeeder`, `CoreSystemSeeder`, dan `DatabaseSeeder`.

### 2. Grup `Gateway & Pengaturan Modul`
1. **Pengaturan Koneksi WA** (`/portal-sistem/koneksi-wa` — [`SistemKoneksiWaPage`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Filament/Sistem/Pages/SistemKoneksiWaPage.php))
   - Konfigurasi provider gateway WhatsApp (Evolution API / gateway kustom), API Key, Instance Name, URL endpoint, uji koneksi, dan sinkronisasi template dasar.
2. **Pengaturan SPIKAP** (`/portal-sistem/pengaturan-spikap` — [`SistemPengaturanSpikapPage`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Filament/Sistem/Pages/SistemPengaturanSpikapPage.php))
   - Penetapan daftar guru/staf yang berhak memantau & menindaklanjuti laporan SPIKAP beserta hak akses per fitur (lihat, balas/tindak lanjut, ubah status, hapus).
3. **Pesan Massal Android** (`/portal-sistem/pesan-massal-android` — [`SistemPesanMassalPage`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Filament/Sistem/Pages/SistemPesanMassalPage.php))
   - Manajemen pengiriman pesan/siaran massal melalui aplikasi Android.
4. **Mode Maintenance & Pengumuman** (`/portal-sistem/maintenance-portal` — [`SistemMaintenancePortalPage`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Filament/Sistem/Pages/SistemMaintenancePortalPage.php))
   - **Mode Pemeliharaan (503) per Portal**: Penguncian sementara akses Portal Siswa (`/portal-siswa`), Portal Guru (`/portal-guru`), dan Portal Perpustakaan (`/portal-perpustakaan`) lengkap dengan pesan layar pemeliharaan serta bypass otomatis untuk `super_admin` di [`CheckPortalMaintenance`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Http/Middleware/CheckPortalMaintenance.php).
   - **Buka/Tutup Formulir Pengaduan Publik (`/pengaduan`)**: Kontrol status aktif/nonaktif formulir aspirasi & pengaduan masyarakat (`PengaduanSetting.is_active`), diproteksi pula di [`BerandaController::pengaduanStore()`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Http/Controllers/BerandaController.php). *(Pengaturan teks banner, gambar visi-misi/maklumat, dan tautan SP4N-LAPOR tetap dikelola di Portal Web).*
   - **Siaran Pengumuman Sistem Global**: Banner pengumuman jadwal pemeliharaan/informasi penting lintas portal (`global_announcement_active` & `global_announcement`).
5. **Pengaturan Tema** (`/portal-sistem/theme-settings-page` — [`SistemThemeSettingsPage`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Filament/Sistem/Pages/SistemThemeSettingsPage.php))
   - Pemilihan preset tema siap pakai dan palet warna kustom (`theme_primary`, `theme_secondary`, `theme_accent`, `theme_warning`, `theme_danger`, `theme_info`) beserta reset tema bawaan.

### 3. Grup `Notifikasi WhatsApp`
1. **Notifikasi WA Presensi** (`/portal-sistem/wa-presensi` — [`SistemWaPresensiPage`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Filament/Sistem/Pages/SistemWaPresensiPage.php))
   - Pengaturan notifikasi scan gerbang (datang/pulang/terlambat), presensi sholat dhuhur, ringkasan harian, serta eskalasi pelanggaran kehadiran (surat peringatan/alfa beruntun).
2. **Notifikasi WA Jurnal Mengajar** (`/portal-sistem/wa-jurnal-mengajar` — [`SistemWaJurnalMengajarPage`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Filament/Sistem/Pages/SistemWaJurnalMengajarPage.php))
   - Pengaturan notifikasi otomatis ke orang tua ketika siswa terdeteksi tidak hadir/bolos pada jam KBM.
3. **Notifikasi WA SPIKAP** (`/portal-sistem/wa-spikap` — [`SistemWaSpikapPage`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Filament/Sistem/Pages/SistemWaSpikapPage.php))
   - Pengaturan aktivasi notifikasi WA saat ada laporan SPIKAP baru yang masuk serta status perubahan tindak lanjut ke pelapor.
4. **Notifikasi WA Guru Wali** (`/portal-sistem/wa-guru-wali` — [`SistemWaGuruWaliPage`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Filament/Sistem/Pages/SistemWaGuruWaliPage.php))
   - Dikhususkan untuk alur **Janjian Konsultasi Tatap Muka**: notifikasi ke Guru Wali saat siswa mengajukan janji tatap muka (`/portal-siswa/konsultasi-guru-wali`) dan notifikasi ke Siswa saat jadwal tatap muka ditetapkan/disetujui (`/portal-guru/guru-wali`).
   - Dikelola oleh [`GuruWaliNotificationService`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Services/GuruWaliNotificationService.php) *(curhat/bimbingan daring biasa ditangani langsung di dalam aplikasi tanpa spam WA)*.
5. **Notifikasi WA Pelayanan Publik** (`/portal-sistem/wa-pelayanan-publik` — [`SistemWaPelayananPublikPage`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Filament/Sistem/Pages/SistemWaPelayananPublikPage.php))
   - Pengaturan nomor WA petugas penerima laporan baru (Admin/Petugas PPID/Waka Humas), konfirmasi ke pelapor saat pengaduan dikirim via `/pengaduan`, serta notifikasi perubahan status/tanggapan pengaduan dari Portal Web.
   - Dikelola oleh [`PengaduanNotificationService`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Services/PengaduanNotificationService.php).
