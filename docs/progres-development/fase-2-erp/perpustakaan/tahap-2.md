# Modul Perpustakaan: Tahap 2 (Filament Resource Buku & Eksemplar)

**Status:** Selesai
**Tanggal:** 26 Juli 2026

## Deskripsi Pekerjaan
Tahap kedua dari pengembangan Modul Perpustakaan ini berfokus pada pembuatan antarmuka admin menggunakan Filament Resource untuk entitas Kategori Buku, Buku, dan Eksemplar Buku, dilengkapi dengan fitur bulk insert eksemplar massal.

## Detail Implementasi
1. **KategoriBukuResource:**
   - Dibuat operasi CRUD standar untuk tabel `kategori_bukus`.
   - Menggunakan ikon `heroicon-o-tag`.

2. **BukuResource:**
   - Pembuatan form untuk data tabel `bukus`.
   - Input `mapel_id` dan `grade_level` dibiarkan secara eksplisit sebagai kolom *nullable* yang bisa diisi jika relevan.
   - Tabel dikonfigurasi untuk menampilkan kolom perhitungan dari relasi yaitu `jumlah_eksemplar` dan `jumlah_tersedia`.
   - Menerapkan **Interactive Blocking Notification** pada proses `Delete`, `ForceDelete`, `DeleteBulk`, dan `ForceDeleteBulk` untuk mengecek ketersediaan riwayat di tabel `peminjamans`.

3. **EksemplarBukusRelationManager:**
   - Didaftarkan sebagai *Relation Manager* di halaman Edit `BukuResource`.
   - Menggunakan fitur Action `generateMassal` untuk men-*generate* kode secara *bulk insert*, dengan parameter awalan (prefix), nomor awal (mulai dari), jumlah urutan, dan digit panjang.
   - **Validasi Bentrok**: Algoritma memeriksa apakah `kode_eksemplar` yang digenerate bentrok dengan yang ada di database. Jika ada, tampilkan notifikasi nama kode yang bentrok (maks. 5) dan gagalkan proses tersebut.
   - Menggunakan UUID yang di-generate *on the fly* di setiap *looping* untuk mengantisipasi kegagalan trait `HasUuids` dalam mode bulk insert. Kolom `created_at` dan `updated_at` juga di-set secara eksplisit.
   - Sama seperti buku, Action Delete pada Eksemplar mengimplementasikan *Interactive Blocking Notification*.

## Verifikasi yang Sudah Dilakukan
- Generate KategoriBuku dan Buku berhasil dioperasikan.
- Generate massal EksemplarBuku bekerja sempurna secara asinkron lewat bulk insert.
- `id` UUID dan timestamps `created_at` & `updated_at` pada record yang baru di-generate massal dipastikan terisi.
- Proteksi bentrok (collision prevention) tereksekusi dengan baik (ditampilkan maksimal 5 sampel kode di *banner* bahaya).
