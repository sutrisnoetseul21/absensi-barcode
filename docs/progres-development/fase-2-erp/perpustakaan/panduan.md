# Buku Panduan Penggunaan Modul Perpustakaan ERP

Panduan ini ditujukan bagi Admin Perpustakaan dan Staf IT Sekolah untuk mengoperasikan Modul Perpustakaan yang baru saja diimplementasikan pada ERP Fase 2.

## 1. Akses Utama (URL & Panel)
Seluruh pengelolaan data perpustakaan dipisahkan dari panel utama (Super Admin) agar fokus dan bersih.
- **URL Panel Perpustakaan**: `http://[domain-sekolah]/admin-perpustakaan`
- *Login menggunakan akun admin/petugas yang memiliki akses (role) ke modul perpustakaan.*

---

## 2. Persiapan Data Anggota (Peminjam)
Sebelum anggota dapat meminjam buku di Kiosk Sirkulasi, mereka **WAJIB** memiliki Barcode yang terdaftar di sistem.

### A. Siswa
Siswa secara otomatis menggunakan barcode presensi harian mereka.
1. Masuk ke Panel Akademik/Utama (`/admin`).
2. Buka Menu **Siswa**. Pastikan siswa sudah aktif dan memiliki Kartu OSIS / Barcode Presensi. 
3. *Tidak perlu setup tambahan dari sisi modul Perpustakaan.*

### B. Guru & Staff (Wali Kelas)
Berbeda dengan siswa, profil barcode guru harus di-*generate* terlebih dahulu.
1. Masuk ke Panel Akademik/Utama (`/admin`).
2. Buka Menu **Akademik > Guru**.
3. Pada tabel daftar guru, cari nama guru yang ingin dibuatkan kartunya, klik ikon QR (opsi **Generate Barcode**).
4. Setelah sukses, barcode tersebut sudah valid digunakan baik untuk Presensi (Kiosk Guru) maupun Peminjaman Buku.

---

## 3. Manajemen Katalog Buku & Eksemplar
Sistem kita menganut pemisahan antara **"Judul Buku"** (Katalog) dan **"Eksemplar"** (Fisik Buku).

### A. Menambah Judul Buku
1. Buka Menu **Perpustakaan > Buku** di panel `/admin-perpustakaan`.
2. Klik **New Buku**. Isi Data ISBN, Judul, Kategori, Penulis, Penerbit, dsb.

### B. Men-generate Eksemplar Fisik
Setelah buku disimpan, Anda wajib mencetak "Fisik Eksemplarnya" ke dalam sistem agar bisa dipinjam.
1. Klik Edit pada Buku yang baru saja Anda buat.
2. Scroll ke bawah hingga menemukan bagian tabel **Eksemplar Buku**.
3. Klik tombol **Generate Eksemplar**.
4. Masukkan jumlah buku fisik yang baru saja dibeli/diterima (Misal: 50 buku).
5. Sistem otomatis membuat 50 baris eksemplar dengan kode unik (contoh: `B000101`, `B000102`, dst).

### C. Mencetak Label Barcode Buku (Tempel ke Fisik)
Label ini akan ditempelkan di cover belakang buku fisik untuk di-*scan* barcode scanner.
1. Tetap di Menu **Perpustakaan > Eksemplar**.
2. Centang checkbox di sebelah kiri pada buku-buku yang ingin dicetak labelnya (Anda dapat memilih hingga ratusan buku sekaligus menggunakan fitur "*Select All*").
3. Di bagian kiri atas tabel (sebelah kanan icon filter), klik dropdown **Bulk Actions**.
4. Pilih **Cetak Barcode Terpilih**.
5. Browser akan membuka tab baru menampilkan desain Grid Label (bersih tanpa tombol). Sistem akan **otomatis memunculkan pop-up Print browser**.
6. Atur setelan Print: *Paper Size A4, Nonaktifkan Headers & Footers, Margins Default/Minimum*. Print menggunakan kertas stiker.

---

## 4. Cara Menggunakan Kiosk Sirkulasi (Petugas)
Halaman Kiosk adalah antarmuka utama Pustakawan untuk melayani transaksi di meja depan menggunakan *Barcode Scanner* genggam.

- **URL Kiosk**: `http://[domain-sekolah]/admin-perpustakaan/sirkulasi`
- **Cara Transaksi:**
  1. Klik area layar manapun untuk memastikan browser mengizinkan pemutaran suara beep (*"Sentuh Layar Untuk Mengaktifkan Kiosk"*).
  2. **Langkah 1 (Scan Anggota)**: *Scan* Kartu OSIS Siswa atau ID Card Guru menggunakan *Barcode Scanner*. (Layar akan berbunyi beep hijau jika terdaftar, lalu menampilkan foto/nama peminjam).
  3. **Langkah 2 (Scan Buku)**: Langsung *scan* barcode label yang tertempel di belakang buku fisik.
     - **Jika Buku Statusnya 'Tersedia'**: Sistem akan meminjamkan buku tersebut (terdengar Beep Sukses, muncul tanggal jatuh tempo).
     - **Jika Buku Sedang Dipinjam Oleh Siswa Ybs**: Sistem akan otomatis membaca itu sebagai proses **Pengembalian Buku**.
  4. Layar akan otomatis *reset* kembali ke Langkah 1 setelah 4-5 detik. Pustakawan bisa langsung men-*scan* anak/guru berikutnya tanpa memegang mouse/keyboard sama sekali!

---

## 5. Melihat Laporan & Riwayat
### A. Dashboard Utama
- Akses halaman depan `/admin-perpustakaan` untuk memantau ringkasan statistik (Buku Sedang Dipinjam, Peringatan Buku Terlambat, Total Stok Tersedia).

### B. Riwayat Transaksi (Peminjaman)
- Buka Menu **Perpustakaan > Riwayat Peminjaman**.
- Anda dapat memfilter tabel berdasarkan **Tipe Anggota** (Guru/Siswa), **Rentang Waktu**, maupun **Status** (Terlambat, Dipinjam, Dikembalikan).
- Tabel ini secara cerdas dan real-time akan menandai buku menjadi "Terlambat" (Badge Merah) apabila sudah melampaui tanggal jatuh tempo (default 7 hari sesuai setelan *Pengaturan Sekolah*).
