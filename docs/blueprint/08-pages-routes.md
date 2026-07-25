# 08. Pages & Routes

| Path | Halaman | Akses | Keterangan |
|---|---|---|---|
| `/` | Dashboard Publik | Publik | Wall of Fame, grafik kehadiran |
| `/wali-kelas/login` | Login Wali Kelas | Publik | Guard `wali_kelas` |
| `/wali-kelas` | Dashboard Rekap Kelas | Wali Kelas | Kelas yang diampu (bisa > 1) |
| `/wali-kelas/input-absensi` | Form/Modal Input Manual Absensi | Wali Kelas | Cari via nama/NISN, isi `note` |
| `/siswa/login` | Login Siswa | Publik | Guard `siswa` |
| `/siswa` | Riwayat Absensi Pribadi | Siswa | Read-only |
| `/login` | Gerbang Utama (Portal Selection) | Admin | Halaman pilih panel (Super, Master, Akademik, Presensi) |
| `/admin/login` | Login Super Admin | Admin | Custom login layout |
| `/admin-master/login` | Login Master Data | Admin | Custom login layout |
| `/admin-akademik/login` | Login Akademik | Admin | Custom login layout |
| `/admin-presensi/login` | Login Presensi | Admin | Custom login layout |
| `/admin` | Dashboard Super Admin | Admin | Pengaturan Root & Sistem |
| `/admin-master` | Dashboard Master Data | Admin TU | Input Tahun Ajaran, Kelas, Siswa, Guru |
| `/admin-akademik` | Dashboard Akademik | Admin TU | Pembagian/Kenaikan/Mutasi Kelas |
| `/admin-presensi` | Dashboard Presensi | Guru Piket | Input/Rekap Presensi, Libur |
| `/admin-presensi/scan` | Halaman Scan Absensi (Kios) | Admin | Full-screen, async |
| `/admin-master/siswa` | Manajemen Siswa | Admin | + soft delete restore |
| `/admin-presensi/siswa/cetak-kartu` | Cetak Kartu OSIS/Barcode | Admin | Data dari `school_settings` |
| `/admin-master/siswa/import` | Import/Export Excel Siswa | Admin | Upload, download template |
| `/admin-master/kelas` | Manajemen Kelas (Template Nama) | Admin | Master nama kelas permanen |
| `/admin-master/tahun-ajaran` | Manajemen Tahun Ajaran & Arsip | Admin | Aktif / arsip |
| `/admin-akademik/kenaikan-kelas` | Wizard Kenaikan Kelas | Admin | UI step-by-step |
| `/admin-akademik/kenaikan-kelas/excel` | Import/Export Excel Kenaikan Kelas | Admin | Download template, upload hasil |
| `/admin-presensi/libur` | Kalender Hari Libur | Admin | FullCalendar, range tanggal |
| `/admin-presensi/laporan` | Laporan & Export Presensi | Admin | Filter, PDF, Excel |
| `/admin/pengaturan` | Pengaturan Sistem | Super Admin | Nama, logo, admin user |
