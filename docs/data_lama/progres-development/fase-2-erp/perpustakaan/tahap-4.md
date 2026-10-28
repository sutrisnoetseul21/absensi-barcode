# Modul Perpustakaan Tahap 4: Sirkulasi Kiosk (Scan Pinjam & Kembali)

## 1. Ekosistem Barcode Guru
- **Migration & Model `TeacherPresensiProfile`**: Dibuat sebagai cerminan `StudentPresensiProfile` untuk menyimpan data `barcode_code` yang diperlukan mesin scanner (Kiosk).
- **Relasi**: Ditambahkan fungsi relasi `presensiProfile()` (hasOne) di Model `Guru`.
- **Fitur Generate Barcode**: Aksi `Generate Barcode` disematkan ke dalam Filament (di dalam `GuruTable.php`) agar Admin bisa memberikan barcode unik kepada setiap Guru berdasarkan NIP (atau secara acak, bila NIP kosong, dengan format angka awalan "2").

## 2. Pengaturan Umur Pinjaman Dinamis
- Kolom baru `lama_pinjam_buku_hari` telah ditambahkan di tabel `school_settings` dengan nilai default `7` (integer).
- Pengaturan Sekolah di Filament `SchoolSettingsPage.php` telah diperbarui dengan section khusus *Pengaturan Perpustakaan* untuk mengelola properti jatuh tempo secara global tanpa *hardcode*.
- Nilai ini diambil dan diaplikasikan langsung saat pembuatan data `Peminjaman`.

## 3. UI Kiosk Sirkulasi (Frontend Alpine & Livewire)
- Komponen Livewire `SirkulasiKiosk.php` dan blade view dirancang setara arsitektur `AttendanceKiosk`.
- Terdapat dua layer/state proses *(State 1: Scan Peminjam, State 2: Scan Buku)* di-*handle* via framework Alpine.js.
- UX di-desain responsif dengan warna dinamis yang merepresentasikan state (*Success, Error, dll*), pemutaran audio umpan balik, dan input *hidden autofocus* sehingga tidak perlu mengklik apa-apa saat melakukan pindai.

## 4. Logika Sirkulasi (`ProcessSirkulasiAction.php`)
Logika *Action* dibangun untuk secara cerdas menangani rute sirkulasi:
- Menangani *input scan peminjam*: Mengecek secara terpisah `StudentPresensiProfile` atau `TeacherPresensiProfile`. Memfilter apabila kartu diset `barcode_active = false`.
- Menangani *input scan buku (eksemplar)*: 
  - Jika eksemplar berstatus `tersedia`: memicu proses pembuatan histori **Pinjam**. `peminjam_type` diisi string murni eksplisit `siswa` atau `guru` berdasarkan tipe peminjam. Jatuh tempo dikalkulasi sesuai properti di *school_settings*.
  - Jika berstatus `dipinjam`: divalidasi apakah ID Peminjamnya sama dengan orang yang sedang melakukan pemindaian saat ini. Bila ya: dieksekusi proses pengembalian (status peminjaman diubah jadi `dikembalikan`). Bila tidak: Transaksi ditolak sebagai proteksi ganda (menghindari pengembalian oleh orang yang bukan meminjam).

## Status Revisi & Verifikasi:
- **Verifikasi Polymorphism Manual (Bloker Tahap 1):** Penentuan properti `$peminjaman->peminjam_type = 'guru'` di-set string harfiah (hardcode) dalam `ProcessSirkulasiAction.php` guna mengamankan keabsahan format morph dan menghindari penulisan string otomatis `'wali_kelas'`. Verifikasi *Database Tinker* telah dilaksanakan dan berhasil menyimpan tipe sebagai `guru`.
- **Verifikasi Pesan Gagal**: Menambahkan spesifikasi beda keterangan saat gagal (*kartu tidak ada* vs *kartu dinonaktifkan/hilang*).

## Revisi: Bug Status Enum
1. **Implementasi Awal**: Logika sebelumnya menggunakan string `'selesai'` saat memperbarui status peminjaman pada proses pengembalian buku.
2. **Penemuan Masalah**: Melalui audit dan pengujian *database*, ditemukan bahwa kolom status pada tabel `peminjamans` menggunakan tipe data `ENUM` murni dengan opsi `'dipinjam'`, `'dikembalikan'`, `'terlambat'`, dan `'hilang'`. Penggunaan nilai yang tidak valid membuat MySQL diam-diam memotong (truncate) nilai dengan menghasilkan *Warning 1265*, tanpa memunculkan pesan error yang mencolok di Laravel.
3. **Penyelesaian**: Kode telah diperbaiki untuk menggunakan nilai yang diizinkan yaitu `'dikembalikan'`. Hal ini diverifikasi langsung menggunakan `DB::table()` (melewati lapisan *cache object* Eloquent) guna memastikan nilai tersimpan dengan sempurna di tingkat MySQL.

## Hotfix: Regresi `kelasAjaranAktif()` akibat refactor Model Siswa di luar modul Perpustakaan
1. **Penemuan Masalah**: Saat restrukturisasi modul di Tahap 8, ditemukan bahwa `ProcessSirkulasiAction.php` memanggil relasi `$siswa->kelasAjaranAktif()`. Setelah diaudit, ternyata method ini **TIDAK PERNAH ADA** di `app/Models/Siswa.php`. Fitur sirkulasi Kiosk untuk siswa sebenarnya rusak sejak Tahap 4 dirilis pertama kali di *commit* `849bf36`, dikarenakan pengembang sebelumnya menggunakan asumsi nama metode yang salah. Model Siswa sendiri menggunakan metode `enrollmentAktif()` (sudah ada sejak Fase 1 di *commit* `0780bee`).
2. **Penyelesaian**: Property chaining di `ProcessSirkulasiAction.php` diperbaiki menjadi `$siswa->enrollmentAktif->kelas->name` secara *sekalian* saat mengerjakan Tahap 8 (Commit `12e46af`).
3. **Dampak dan Verifikasi**: Pencarian menyeluruh (grep) membuktikan tidak ada pemanggilan `kelasAjaranAktif` lain di seluruh *codebase*. Uji regresi penuh membuktikan perubahan ini aman, valid, dan berhasil memperbaiki *bug fatal* ini secara permanen tanpa merusak fungsi sirkulasi Guru.
