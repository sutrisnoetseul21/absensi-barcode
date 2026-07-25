# Audit Preflight: Penggabungan Panel Admin Master dan Admin Akademik

Berikut adalah hasil investigasi (read-only) terhadap sistem saat ini sebelum eksekusi penggabungan panel:

## 1. User yang Terikat Role `admin_master`
Berdasarkan query ke database menggunakan Tinker pada model `Role` dan relasi `users`, hasilnya adalah **NIHIL**.
Saat ini **tidak ada** user yang terikat ke role `admin_master` (baik itu view, editor, maupun admin). Penghapusan role ini dari seeder dan database aman dilakukan karena tidak akan membuat user manapun kehilangan akses secara tiba-tiba.

## 2. Referensi Namespace Master di Luar Folder Resources
Pencarian terhadap string `Filament\Master` di seluruh codebase (`app/`) menunjukkan bahwa namespace ini **hanya** digunakan di dalam:
1. File-file class yang ada di dalam `app/Filament/Master/Resources` dan sub-foldernya (seperti Pages, Schemas, Tables, dll).
2. File `app/Providers/Filament/MasterPanelProvider.php` (untuk mendaftarkan resources, pages, dan widgets).
Tidak ada indikasi penggunaan namespace ini secara hardcode di file lain (seperti model, middleware, atau custom controller). 

## 3. Konflik Nama Resource
Tidak ada konflik (bentrok) penamaan antara file di folder `Master` dan `Akademik`.
*   **Resources di folder Master:** `GuruResource`, `KelasResource`, `SiswaResource`, `TahunAjaranResource`, `PengumumanResource`, `UserResource`.
*   **Resources di folder Akademik:** `Enrollment(Resource)`, `PindahKelasResource`, `SiswaLulusResource`, `SiswaMutasiResource`.
Karena tidak ada kesamaan nama resource, seluruh folder resource dari Master dapat dipindahkan langsung ke dalam Akademik tanpa perlu me-rename nama base Resource-nya.

## 4. Cek MasterPanelProvider Secara Detail
Konfigurasi `MasterPanelProvider.php` terbukti identik 100% (kecuali untuk ID dan Path panel) dengan `AkademikPanelProvider.php`.
*   Theme, Favicon, Brand Name, Logo: Identik (mengambil dari tabel `PengaturanSekolah`).
*   Colors: Keduanya menggunakan `Color::Amber`.
*   Navigation Groups: Sama (Data Master, Akademik, Presensi, Laporan, dll).
*   Middleware & Plugins: Sama.
**Kesimpulan:** `MasterPanelProvider.php` aman untuk langsung dihapus tanpa perlu memigrasikan konfigurasi khusus apapun ke `AkademikPanelProvider.php`.

## 5. Cek Isi PanelRolesSeeder.php Saat Ini
Potongan relevan dari array definisi resources pada seeder saat ini:
```php
$masterResources = ['Guru', 'Siswa', 'Kelas', 'TahunAjaran', 'Pengumuman', 'HariLibur'];
$akademikResources = ['Kelas', 'Siswa', 'RiwayatPindahKelas'];
$presensiResources = ['HariLibur']; // + Custom pages khusus presensi

$divisions = [
    'admin_master' => $masterResources,
    'admin_akademik' => $akademikResources,
    'admin_presensi' => $presensiResources,
];

$customPermissions = [
    'admin_master' => ['View:Dashboard'],
    'admin_akademik' => ['View:Dashboard'],
    // ...
];
```
Nantinya `$masterResources` dan key `admin_master` akan dihapus, kemudian isi `$masterResources` dapat dimerge ke dalam `$akademikResources`.

## 6. Cek Apakah Ada Test/Fitur Lain yang Bergantung pada Panel Master
Berdasarkan pencarian string `admin-master` dan `admin_master` di luar folder `docs/`, ditemukan beberapa hardcode path/role yang **KRUSIAL** dan harus diupdate saat penggabungan:

1.  **`app/Models/User.php` (Baris 63 & 82)**
    Terdapat mapping manual yang memeriksa ID panel dan mencocokkannya dengan role:
    `'admin-master' => 'admin_master',`
    *(Ini harus dihapus/dibersihkan saat panel dihilangkan).*

2.  **`resources/views/filament/widgets/portal-widget.blade.php` (Baris 23)**
    Terdapat hardcode link HTML: `<a href="{{ url('/admin-master') }}" ...>`
    *(Link menuju /admin-master harus diubah menuju /admin-akademik atau card-nya dihapus/digabung).*

3.  **`resources/views/filament/pages/auth/custom-login.blade.php` (Baris 12)**
    Terdapat pengecekan spesifik: `if ($panelId === 'admin-master') {`
    *(Ini harus dibersihkan atau digabungkan penanganannya dengan admin-akademik).*

4.  **`resources/views/auth/portal-selection.blade.php` (Baris 64)**
    Terdapat hardcode link untuk pilihan portal login: `<a href="{{ url('/admin-master/login') }}" ...>`
    *(Sama seperti portal-widget, card opsi login master harus digabung/dihapus).*

---
*Laporan preflight selesai. Tidak ada modifikasi file yang dilakukan selama proses ini.*
