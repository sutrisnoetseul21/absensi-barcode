# Modul Perpustakaan: Tahap 8 (Restrukturisasi Navigasi Sidebar)

**Status:** Selesai
**Tanggal:** 28 Juli 2026

## Deskripsi Pekerjaan
Tahap ini difokuskan pada pengelompokan dan perapian struktur menu di *Sidebar* Panel Perpustakaan. Mengubah susunan yang sebelumnya masih default menjadi lebih spesifik sesuai kebutuhan operasional Pustakawan (mulai dari Koleksi Buku, Manajemen Anggota, Peminjaman, Pengembalian, Reservasi, Laporan, hingga Pengaturan).

## Detail Implementasi
1. **Navigasi Dasar & Grup Menu:**
   - Menambahkan property `$navigationGroup` dan `$navigationSort` pada Resource eksisting.
   - `BukuResource` (Grup: *Koleksi Buku*, Sort: 1)
   - `KategoriBukuResource` dipindahkan ke Grup *Pengaturan* (Sort: 1) karena bersifat data master awal.

2. **Menu Anggota (Custom Page):**
   - Membuat `AnggotaResource` berupa *Filament Custom Page*.
   - Halaman ini menggunakan *Database Query Builder* untuk melakukan `UNION` (menggabungkan) data `students` (yang terdaftar pada tahun ajaran aktif) dan `teachers`.
   - Tabel murni bersifat *read-only* untuk identitas, namun diberikan *Action* khusus "Aktifkan/Nonaktifkan" barcode yang mengeksekusi *update* langsung ke tabel profil (seperti `student_presensi_profiles`). Hal ini memastikan sistem terhindar dari *Coupling* berlapis dengan Modul Akademik/Master.

3. **Peminjaman & Pengembalian (Separasi Resource):**
   - Memisahkan resource sebelumnya menjadi 2 menu terpisah dalam Grup *Sirkulasi*.
   - **`PeminjamanAktifResource`**: Menampilkan khusus yang sedang 'dipinjam' dan 'terlambat'.
   - **`RiwayatPengembalianResource`**: Menampilkan riwayat transaksi ('dikembalikan', 'hilang').
   - Pemisahan ini dilakukan murni di layer UI Filament dengan *overriding* method `getEloquentQuery()`, sehingga struktur database `peminjamans` tidak tersentuh (tetap 1 tabel master).

4. **Placeholder Reservasi:**
   - Membuat *Custom Page* sederhana (`ReservasiSegeraHadir`) di dalam grup *Sirkulasi* sebagai *placeholder*.
   - Tidak ada skema *database* atau relasi yang dimodifikasi.

5. **Laporan Sirkulasi:**
   - Membuat halaman khusus Laporan di Grup *Laporan*.
   - Berisi 3 *Widget* agregasi:
     1. **`SirkulasiBulananChart`**: Grafik frekuensi Peminjaman dan Pengembalian dalam 30 hari terakhir.
     2. **`BukuTerpopulerWidget`**: Top 10 Buku paling banyak dipinjam pada bulan berjalan.
     3. **`TerlambatKritisWidget`**: Menampilkan peminjaman yang terlambat lebih dari 3 hari (mengembalikan kalkulasi hari keterlambatan, tanpa ekuivalensi estimasi nominal denda untuk menghindari misinformasi).

6. **Pengaturan Perpustakaan:**
   - Membuat halaman formulir pengaturan (`PengaturanPerpustakaan`) yang terkoneksi langsung ke tabel global `school_settings`.
   - Berguna untuk mengubah variabel *Lama Pinjam Default* (`lama_pinjam_buku_hari`).

## Verifikasi dan Keamanan Fitur
- Fungsi **Generate Massal Eksemplar**, **Cetak Barcode**, serta mekanisme blokir hapus keras (Interative Blocking Deletion) telah diverifikasi dan dipastikan tidak terdampak (*no orphan resources*), mengingat fitur tersebut tersimpan di dalam struktur internal `BukuResource` dan `EksemplarBukusRelationManager`.
