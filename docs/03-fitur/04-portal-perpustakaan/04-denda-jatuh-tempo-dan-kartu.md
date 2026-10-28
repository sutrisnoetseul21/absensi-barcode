# 💰 Kebijakan Denda, Jatuh Tempo, & Audit Penghapusan Buku

Dokumen ini mendokumentasikan tata cara penanganan keterlambatan pengembalian buku, kalkulasi denda, pencetakan kartu anggota, dan riwayat penghapusan buku.

---

## 1. Perhitungan Jatuh Tempo & Denda

- **Penetapan Waktu**:
  `tanggal_jatuh_tempo = tanggal_pinjam + durasi_hari_pinjam`.
- **Kondisi Keterlambatan (*Overdue*)**:
  Jika buku dikembalikan setelah `tanggal_jatuh_tempo` atau belum dikembalikan saat tanggal hari ini melewati tanggal tempo.
- **Kalkulasi Denda**:
  `total_denda = jumlah_hari_terlambat * tarif_denda_per_hari`.
- **Status Transaksi**:
  - `dipinjam`: Buku masih aktif dipinjam.
  - `terlambat`: Melewati tempo dan belum dikembalikan.
  - `dikembalikan`: Buku sudah kembali ke rak perpustakaan.
  - `hilang`: Buku dilaporkan hilang dan memerlukan penggantian fisik/biaya.

---

## 2. Riwayat Penghapusan Buku (`/portal-perpustakaan/riwayat-hapus-buku`)

Komponen Livewire `App\Livewire\PortalPerpustakaan\RiwayatHapusBuku`:
- **Audit Data**: Buku dan eksemplar yang dihapus oleh petugas tidak langsung hilang permanen dari database (*Soft Delete Pattern*).
- **Log Catatan**:
  - Tanggal penghapusan (`deleted_at`).
  - Alasan penghapusan (rusak berat, dimakan rayap, hilang, atau usang/kadaluwarsa).
  - Nama petugas yang mengeksekusi penghapusan.
- **Restore Data**: Petugas atau admin dapat memulihkan (*restore*) buku jika salah hapus.

---

## 3. Cetak Kartu Anggota Perpustakaan (`/portal-perpustakaan/cetak-kartu`)

Portal Perpustakaan terintegrasi dengan modul pencetakan kartu anggota:
- Menggunakan komponen bersama [`App\Livewire\PortalPresensi\CetakKartuSiswa`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Livewire/PortalPresensi).
- **Format Barcode**: Barcode kartu anggota perpustakaan menggunakan kode yang sama dengan kartu presensi gerbang (NISN/NIS).
- Siswa dan guru tidak perlu memiliki dua kartu berbeda; kartu pelajar/kartu guru otomatis berfungsi sebagai kartu perpustakaan terpadu (*One Card for All*).
