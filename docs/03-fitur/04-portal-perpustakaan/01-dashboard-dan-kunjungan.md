# 📊 Dashboard Analitik Perpustakaan & Buku Tamu Kunjungan

Dokumen ini menjelaskan halaman dashboard pemantauan koleksi dan sirkulasi buku serta modul pencatatan buku tamu kunjungan di Portal Perpustakaan.

---

## 1. Dashboard Utama Petugas (`PetugasPerpusDashboard`)

Dashboard menyajikan ringkasan metrik inventaris dan status pinjaman aktif secara real-time:

### A. Metrik Koleksi & Sirkulasi
- **Total Judul Buku**: Jumlah seluruh entitas katalog judul buku (`Buku::count()`).
- **Total Eksemplar Fisik**: Akumulasi seluruh kopi fisik buku yang dimiliki perpustakaan (`EksemplarBuku::count()`).
- **Eksemplar Tersedia**: Eksemplar dengan status `tersedia` yang siap dipinjam di rak.
- **Eksemplar Dipinjam**: Eksemplar yang saat ini berada di tangan siswa atau guru.
- **Peminjaman Aktif**: Total transaksi peminjaman berstatus `dipinjam`.
- **Peminjaman Terlambat (Overdue)**: Pinjaman aktif yang telah melewati batas `tanggal_jatuh_tempo`.

### B. Widget Peringatan Jatuh Tempo (Overdue List)
- Menampilkan 5 transaksi pinjaman yang paling mendesak/terlambat dikembalikan.
- Menampilkan nama peminjam (siswa/guru), kelas, judul buku, nomor barcode eksemplar, dan jumlah hari keterlambatan.

---

## 2. Modul Buku Tamu Kunjungan (`/portal-perpustakaan/kunjungan`)

Dikelola oleh komponen Livewire [`App\Livewire\PetugasPerpusKunjungan`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Livewire/PetugasPerpusKunjungan.php).

### A. Pencatatan Kunjungan
- **Tujuan**: Merekam data pengunjung harian perpustakaan sekolah untuk akreditasi perpustakaan dan statistik literasi.
- **Metode Input**:
  - Scan barcode kartu siswa/guru secara instan di pintu masuk perpus.
  - Pencarian manual berdasarkan NISN/NIP jika kartu fisik tertinggal.
- **Data yang Disimpan**: `student_id` / `teacher_id`, tanggal kunjungan, jam masuk, dan keperluan (membaca, meminjam, belajar mandiri).

### B. Ekspor Laporan Kunjungan
Petugas dapat mengekspor histori kunjungan dengan filter rentang tanggal:
- **Format PDF**: Dokumen tabel kunjungan siap cetak untuk laporan bulanan kepala sekolah.
- **Format Excel**: Data spreadsheet untuk analisis grafik minat baca siswa per kelas.
