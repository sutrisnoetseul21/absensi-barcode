# Modul Perpustakaan ERP - Tahap 6: Katalog Publik

Tahap ini berfokus pada pembuatan Halaman Publik Katalog Perpustakaan (`/perpustakaan`) yang bisa diakses tanpa login. Fitur ini dirancang untuk menyajikan informasi ketersediaan koleksi perpustakaan secara transparan kepada seluruh warga sekolah.

## Fitur Utama

1. **Eksplorasi Katalog Real-time**: Pencarian berbasis judul, penulis, dan ISBN serta filter kategori & jenjang kelas.
2. **Statistik Agregat Terbuka**: Menampilkan metrik total judul buku, total fisik eksemplar, jumlah buku tersedia, dan buku sedang sirkulasi.
3. **Kategori Terpopuler**: Menampilkan peringkat (*wall of fame* style) 5 kategori dengan koleksi buku terbanyak.
4. **Keamanan Data Pribadi (Privasi)**: Halaman ini 100% *read-only* dari sisi publik. Riwayat "Buku yang Sedang Saya Pinjam" secara ketat dibatasi hanya untuk user yang sedang aktif login (menggunakan *multi-guard* cek `auth('siswa')` dan `auth('wali_kelas')`).
5. **Rate Limiting (Throttle)**: Rute ini dilindungi oleh *middleware* `throttle:60,1` untuk mencegah serangan spam/DDoS. Pengguna hanya dapat melakukan maksimal 60 request per menit. Jika melebihi batas, server akan menolak akses dengan respons HTTP 429 (Too Many Requests).

## Struktur Komponen

- **Route**: `GET /perpustakaan` -> `App\Livewire\Public\KatalogPerpustakaan::class`
- **Controller/Livewire**: Menggunakan Livewire v3 (`#[Url(history: true)]`) agar parameter pencarian tersimpan di URL, mendukung *shareable links* dan SEO *friendly*. 
- **View**: `resources/views/livewire/public/katalog-perpustakaan.blade.php` 
- **Efisiensi Database**: Menghindari problem N+1 menggunakan `with(['kategori', 'mapel'])` dan menghitung ketersediaan per judul dengan subquery `withCount(['eksemplars as eksemplar_tersedia_count' => function ($q) { $q->where('status', 'tersedia'); }])`.

## Arsitektur Sistem Tema CSS Terpusat (Tailwind v4)

Halaman `/perpustakaan` di desain untuk memiliki tampilan visual yang **100% identik/senada** dengan Dashboard Presensi (`/presensi`), menggunakan estetika modern dengan *hero banner background*, *glassmorphism*, dan kartu berbasis *solid gradient*.

Untuk mencapai hal ini tanpa *hardcode* nama warna dasar Tailwind di file Blade, kami membangun **Sistem Tema Terpusat** di dalam file konfigurasi utama CSS proyek ini.

### Panduan Konfigurasi Tema (`app.css`)
Sistem warna menggunakan direktif `@theme` dari Tailwind v4 di dalam file `resources/css/app.css`. Warna-warna dasar *brand* (seperti `brand-primary`, `brand-secondary`) di-*map* ke nilai hex eksak yang diinginkan. 

Contoh penggunaannya:
```css
@theme {
  --color-brand-primary: #4f46e5;      /* indigo-600 */
  --color-brand-secondary: #8b5cf6;     /* violet-500 */
  --color-brand-accent: #10b981;        /* emerald-500 */
  --color-brand-warning: #f59e0b;       /* amber-500 */
  --color-brand-danger: #f43f5e;        /* rose-500 */
  --color-brand-info: #0ea5e9;          /* sky-500 */
}
```

**Cara Mengganti Tema Sekolah di Masa Depan:**
Jika pihak manajemen sekolah ingin mengubah *branding* warna sistem dari nuansa "Biru/Ungu" (saat ini) ke nuansa "Hijau", Admin/Developer cukup mengganti nilai HEX di dalam blok `@theme` di `app.css`. Misalnya, mengubah `--color-brand-primary` ke `#16a34a` (green-600). Semua halaman yang menggunakan standar `bg-brand-primary` akan otomatis berubah warna tanpa harus mengedit ratusan *class* di *file* Blade.

> **Catatan Historis (Penting):**
> Saat tahap ini dikerjakan (27 Juli 2026), halaman `/presensi` bawaan belum menggunakan arsitektur CSS Tema `brand-*` ini (masih hardcode bawaan Tailwind seperti `bg-indigo-600`). Halaman `/presensi` sengaja tidak di-refactor demi keamanan stabilitas kode *production*. Sistem tema ini diciptakan khusus menjadi **standar baru** untuk `/perpustakaan` dan modul-modul publik selanjutnya.
