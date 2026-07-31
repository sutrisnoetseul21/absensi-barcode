# Panduan Pengaturan Role & Permission Admin Seeder

Sistem Presensi Digital menggunakan pendekatan Role-Based Access Control (RBAC) yang spesifik untuk setiap panel admin (Akademik, Presensi, Perpustakaan). Untuk mempermudah pembuatan Role dan Permission, kita telah memecah Seeder menjadi 4 file terpisah berdasarkan modulnya.

## Daftar Seeder

Seeder tersedia di dalam folder `database/seeders/`:

1. **`RoleAdminSeeder.php`**
   - **Tujuan**: Membuat Role `super_admin`.
   - **Akses**: Otomatis memiliki hak akses penuh (*God Mode*) ke seluruh panel, termasuk panel inti `/admin` (Manajemen Akun & Pengaturan Sekolah).
   
2. **`RoleAkademikSeeder.php`**
   - **Tujuan**: Membuat Role untuk panel `/admin-akademik` (`admin_akademik_view`, `admin_akademik_editor`, `admin_akademik_admin`).
   - **Resources**: Guru, Siswa, Kelas, TahunAjaran, Pengumuman, HariLibur, RiwayatPindahKelas, User, Jabatan, MataPelajaran.
   
3. **`RolePresensiSeeder.php`**
   - **Tujuan**: Membuat Role untuk panel `/admin-presensi` (`admin_presensi_view`, `admin_presensi_editor`, `admin_presensi_admin`).
   - **Resources & Pages**: HariLibur, Dashboard Presensi, Chart Kehadiran, Input Presensi Manual, Rekap, Cetak Laporan, dll.

4. **`RolePerpustakaanSeeder.php`**
   - **Tujuan**: Membuat Role untuk panel `/admin-perpustakaan` (`admin_perpustakaan_view`, `admin_perpustakaan_editor`, `admin_perpustakaan_admin`).
   - **Resources & Pages**: Buku, Kategori, Sirkulasi, Kunjungan, Laporan, Pengaturan Perpustakaan, dll.

## Cara Penggunaan

### 1. Menjalankan Semua Seeder Sekaligus (Saat Install Ulang)
Keempat seeder di atas sudah didaftarkan di dalam `DatabaseSeeder.php`. Jadi jika Anda menjalankan *fresh install*, Anda hanya perlu memanggil:
```bash
php artisan db:seed
```

### 2. Menjalankan Seeder Spesifik (Jika Ada Penambahan Fitur)
Jika Anda menambahkan fitur baru di panel Akademik (misalnya resource baru `JadwalPelajaran`), maka:
1. Tambahkan nama resource tersebut ke dalam array `$resources` di dalam file `RoleAkademikSeeder.php`.
2. Buka terminal dan jalankan perintah:
```bash
php artisan db:seed --class=RoleAkademikSeeder
```
3. Role akan diperbarui dan Permission baru akan otomatis dibuat & ditempelkan tanpa merusak permission yang sudah ada.

## Catatan Penting
- **Penamaan Role**: Ingat, sistem akan mendeteksi akses panel berdasarkan **awalan nama role**. Role berawalan `admin_akademik_` otomatis bisa masuk ke panel `/admin-akademik`. Jika membuat role kustom di halaman Shield UI, patuhi aturan penamaan ini!
- **Menu Super Admin**: Menu seperti Pindah Kelas, Siswa Mutasi, dan Siswa Lulus di-lock keras (hardcode) dalam fungsinya hanya untuk Super Admin. Jika ingin dibuka untuk role lain, Anda harus memodifikasi fungsi `canViewAny()` di dalam file resource yang bersangkutan.
