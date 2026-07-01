# 08. Pages & Routes

| Path | Halaman | Akses | Keterangan |
|---|---|---|---|
| `/` | Dashboard Publik | Publik | Wall of Fame, grafik kehadiran |
| `/wali-kelas/login` | Login Wali Kelas | Publik | Guard `wali_kelas` |
| `/wali-kelas` | Dashboard Rekap Kelas | Wali Kelas | Kelas yang diampu (bisa > 1) |
| `/wali-kelas/input-absensi` | Form/Modal Input Manual Absensi | Wali Kelas | Cari via nama/NISN, isi `note` |
| `/siswa/login` | Login Siswa | Publik | Guard `siswa` |
| `/siswa` | Riwayat Absensi Pribadi | Siswa | Read-only |
| `/admin` | Dashboard Admin | Admin | Filament panel |
| `/admin/scan` | Halaman Scan Absensi (Kios) | Admin | Full-screen, async |
| `/admin/siswa` | Manajemen Siswa | Admin | + soft delete restore |
| `/admin/siswa/cetak-kartu` | Cetak Kartu OSIS/Barcode | Admin | Data dari `school_settings` |
| `/admin/siswa/import` | Import/Export Excel Siswa | Admin | Upload, download template |
| `/admin/kelas` | Manajemen Kelas (Template Nama) | Admin | Master nama kelas permanen |
| `/admin/kelas/assign-wali` | Assign Wali Kelas per Tahun Ajaran | Super Admin | Per kelas per tahun |
| `/admin/tahun-ajaran` | Manajemen Tahun Ajaran & Arsip | Super Admin | Aktif / arsip |
| `/admin/kenaikan-kelas` | Wizard Kenaikan Kelas | Super Admin | UI step-by-step |
| `/admin/kenaikan-kelas/excel` | Import/Export Excel Kenaikan Kelas | Super Admin | Download template, upload hasil |
| `/admin/libur` | Kalender Hari Libur | Admin | FullCalendar, range tanggal |
| `/admin/laporan` | Laporan & Export Presensi | Admin | Filter, PDF, Excel |
| `/admin/pengaturan` | Pengaturan Sekolah | Super Admin | Nama, logo, jam masuk, kepsek |
