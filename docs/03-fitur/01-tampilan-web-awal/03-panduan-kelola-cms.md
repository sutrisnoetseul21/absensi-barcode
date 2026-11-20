# ⚙️ Panduan Pengelolaan Konten Web Awal (CMS Admin)

Seluruh konten visual, teks, dan media di halaman beranda dapat dikelola langsung oleh administrator tanpa perlu menyentuh kode program melalui antarmuka **Portal Web** (`/portal-web`).

---

## 1. Dashboard & Reorganisasi Menu Portal Web (`/portal-web`)

Halaman beranda Portal Web dikelola oleh [`App\Livewire\PortalWeb\Dashboard`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Livewire/PortalWeb/Dashboard.php) dan view [`dashboard.blade.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/resources/views/livewire/portal-web/dashboard.blade.php), dengan sidebar terstruktur di [`portal.blade.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/resources/views/components/layouts/portal.blade.php):
- **Pengelompokan Menu 4 Kotak per Baris**:
  1. **Upload & Publikasi (Operasional Harian)**: Artikel & Pengumuman *(pop-up Lihat Daftar / Tambah)*, Prestasi Sekolah *(pop-up)*, dan Galeri Foto *(pop-up)* — mendukung parameter `?action=create` untuk membuka modal tambah langsung pada komponen [`Artikel`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Livewire/PortalWeb/Artikel.php), [`Prestasi`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Livewire/PortalWeb/Prestasi.php), dan [`Galeri`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Livewire/PortalWeb/Galeri.php).
  2. **Pelayanan Publik & Data Alumni**: Pengaduan & Aspirasi, Kategori Pengaduan, Halaman Layanan, FAQ Sekolah, Data Alumni, dan Jenjang Lanjutan. *(Pengaturan modul Pelayanan Publik & Tracer Alumni telah dipusatkan di Portal Sistem `/portal-sistem`, dengan redirect otomatis di [`routes/web.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/routes/web.php))*.
  3. **Setup Beranda & Profil Sekolah**: Pengaturan Web, Akses Cepat, Sarana & Prasarana, Pilar Keunggulan, Statistik Web, Halaman Akademik, dan Microsite.

---

## 2. Peta Menu Admin Portal Web

| Menu Portal Web | Komponen Livewire Pengelola | Konten Beranda yang Terpengaruh |
| :--- | :--- | :--- |
| **Pengaturan Web** | [`App\Livewire\PortalWeb\Pengaturan`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Livewire/PortalWeb/Pengaturan.php) | Banner Hero, Running Text, Sambutan Kepsek, Link YouTube, Medsos |
| **Pilar Sekolah** | [`App\Livewire\PortalWeb\Pengaturan`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Livewire/PortalWeb/Pengaturan.php) | Section Profil: Kartu pilar keunggulan & karakter |
| **Artikel & Berita** | [`App\Livewire\PortalWeb\Artikel`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Livewire/PortalWeb/Artikel.php) | Section Berita & Pengumuman (tipe `berita` dan `pengumuman`) |
| **Prestasi** | [`App\Livewire\PortalWeb\Prestasi`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Livewire/PortalWeb/Prestasi.php) | Section Prestasi Siswa & Guru |
| **Sarana Prasarana** | [`App\Livewire\PortalWeb\Sarpras`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Livewire/PortalWeb/Sarpras.php) | Section Fasilitas (upload foto, nama fasilitas, deskripsi) |
| **Galeri Foto** | [`App\Livewire\PortalWeb\Galeri`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Livewire/PortalWeb/Galeri.php) | Section Galeri Foto kegiatan sekolah |
| **Akses Cepat** | [`App\Livewire\PortalWeb\AksesCepat`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Livewire/PortalWeb/AksesCepat.php) | Section Widget: Tombol pintas link eksternal |

---

## 3. Prosedur Pembaruan Konten Kritis

### A. Mengubah Banner Hero & Running Text
1. Login ke `/portal-web` menggunakan akun admin web atau superadmin.
2. Buka menu **Pengaturan Web**.
3. Di tab *Hero & Beranda*:
   - Upload file gambar baru pada field **Hero Image** (rekomendasi rasio 16:9, resolusi minimal 1920x1080px).
   - Masukkan pesan terbaru pada field **Running Text**.
4. Klik **Simpan Pengaturan**. Perubahan akan langsung terlihat saat beranda di-refresh.

### B. Mengubah Video YouTube Sekolah
1. Buka menu **Pengaturan Web** &rarr; tab *Media Sosial & Video*.
2. Isi kolom **Link Video YouTube** dengan URL lengkap video (contoh: `https://www.youtube.com/watch?v=XXXXX` atau `https://youtu.be/XXXXX`).
3. Sistem secara otomatis akan mengekstrak ID video dan menampilkannya sebagai player embed interaktif di section widget beranda.

### C. Menambah Prestasi Siswa
1. Buka menu **Prestasi** &rarr; klik **Tambah Prestasi**.
2. Isi data:
   - Judul Prestasi (misal: *Juara 1 OSN Matematika Tingkat Kabupaten*).
   - Nama Siswa / Tim Pemenang.
   - Tingkat Lomba (Kabupaten, Provinsi, Nasional).
   - Upload Foto dokumentasi penerimaan penghargaan.
3. Pastikan status dicentang **Publikasikan**.
4. Prestasi akan otomatis muncul di grid slider section prestasi di beranda.

---

## 4. Catatan Penyimpanan Media (*Storage*)
- Seluruh upload gambar (hero, foto kepsek, foto fasilitas, foto galeri) disimpan di disk `public`:
  - `storage/app/public/web-setting/`
  - `storage/app/public/sarpras/`
  - `storage/app/public/galeri/`
- Pastikan symlink storage aktif:
  ```bash
  php artisan storage:link
  ```
