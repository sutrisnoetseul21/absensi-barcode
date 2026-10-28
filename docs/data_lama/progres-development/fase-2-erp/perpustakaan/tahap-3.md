# Modul Perpustakaan: Tahap 3 (Cetak Barcode Eksemplar)

**Status:** Selesai
**Tanggal:** 26 Juli 2026

## Deskripsi Pekerjaan
Tahap ketiga berfokus pada fitur pembuatan dan pencetakan label barcode untuk `EksemplarBuku`. Fitur ini sangat penting agar pustakawan dapat melabeli buku fisik setelah barang diregistrasi ke sistem. Mengikuti pola OSIS Card, rendering dilakukan langsung oleh Browser (HTML & CSS Grid) menggunakan library `picqer/php-barcode-generator`.

## Detail Implementasi
1. **EksemplarCetakController**:
   - `cetakBarcode(Buku $buku)`: Memuat seluruh eksemplar dari sebuah buku tertentu.
   - `cetakBarcodeMassal(Request $request)`: Menerima list ID (dipisahkan koma) dari *query parameter* untuk mencetak eksemplar terpilih saja.

2. **Routes**:
   - Rute-rute tersebut didaftarkan di dalam `routes/web.php` di bawah grup middleware `auth`, tepat di bawah *Cetak Kartu Routes* milik modul presensi.

3. **View HTML (`resources/views/pdf/label-barcode-eksemplar.blade.php`)**:
   - Layout disusun menggunakan **CSS Grid** (4 kolom per baris) agar menghemat kertas A4.
   - Menggunakan *library* bawaan PHP `Picqer\Barcode\BarcodeGeneratorPNG` untuk membuat *barcode* Code128.
   - Tiap label menampilkan **Judul Buku (singkat)**, **Barcode PNG**, dan teks **Kode Eksemplar**.
   - Menyertakan *print controls* di pojok kanan atas yang disembunyikan saat dicetak (`@media print`).

4. **Tombol Eksekusi di Filament (`EksemplarBukusRelationManager.php`)**:
   - **HeaderAction (Cetak Barcode Semua)**: Menggunakan `url(fn() => route(...))` dengan `openUrlInNewTab()` untuk membuka tab baru.
   - **BulkAction (Cetak Barcode Terpilih)**: Mengeksekusi penarikan data ID dan *redirect* ke *tab* baru menggunakan sistem sesi (detail di bagian Revisi bawah).

## Revisi: Mekanisme Passing ID (Query String → Session)
Dalam perjalanan fitur ini, dilakukan *stress test* pada pengiriman ID secara massal, yang menghasilkan revisi keamanan arsitektur:
1. **Implementasi Awal**: Mengirimkan seluruh ID (UUID) eksemplar yang dipilih melalui *query string* (comma-separated UUID) pada URL.
2. **Masalah Stress Test**: Uji coba pada *generate* 200 eksemplar (batas wajar jumlah buku paket dalam satu kelompok) menghasilkan panjang string URL sebesar **7.873 karakter**. Angka ini terdeteksi sangat berpotensi menghasilkan ralat **414 URI Too Long**, mengingat limit standar *header buffer* milik Nginx adalah 8KB.
3. **Solusi Diterapkan**: Metode *query string* dibongkar dan diganti menjadi *session-based*. Sistem membuat *key* sesi unik per-*request*, memasukkan array UUID tersebut ke dalam sesi, lalu *redirect* URL hanya membawa *session_key* yang pendek.
4. **Catatan Teknis Session Driver**: Solusi *session* ini terverifikasi 100% valid dan aman karena konfigurasi `SESSION_DRIVER` pada *project* diatur ke `'database'` (tabel `sessions`), bukan `'cookie'`. **PENTING:** Jika suatu hari driver diubah kembali ke `'cookie'`, metode ini **wajib ditinjau ulang** mengingat *cookie browser* dibatasi maksimal ukuran ~4KB.
5. **Session Expiry Intentional**: Secara teknis, `session` sengaja **TIDAK** dihancurkan (*forget/destroy*) di dalam *Controller* sesaat setelah dibaca. Hal ini adalah disengaja; memastikan *user* tidak menjumpai halaman *error* jika tak sengaja melakukan *refresh* pada tab cetak (PDF). Pembersihan data ID array di memori diserahkan pada mekanisme masa kedaluwarsa sesi (*session expiry*) standar bawaan Laravel.

## Kesimpulan Arsitektur
Fitur ini telah diintegrasikan tanpa menambah *library* eksternal baru, mengingat fungsionalitas pembuatan barcode dan *rendering* HTML (window.print) sudah menjadi bagian konvensi *project* sejak fitur cetak OSIS Card pada Modul Akademik/Absensi.
