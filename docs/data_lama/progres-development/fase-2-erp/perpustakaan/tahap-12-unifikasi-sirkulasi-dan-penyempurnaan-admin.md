# Modul Perpustakaan Tahap 12: Unifikasi Sirkulasi Peminjaman & Penyempurnaan Panel Admin

## 1. Unifikasi URL & Navigasi Sirkulasi
- **Pembersihan Navigasi**: Menu terpisah *Riwayat Pengembalian* (`RiwayatPengembalianResource`) disembunyikan dari sidebar navigasi (`$shouldRegisterNavigation = false`).
- **Refactoring URL & Slug**: URL menu sirkulasi diubah menjadi `http://[domain]/admin-perpustakaan/peminjaman` (slug `peminjaman`, tanpa akhiran `s` dan tanpa kata `aktif`).
- **Sistem Tab Navigasi**: Halaman Peminjaman kini disatukan dalam 1 tempat dengan 3 Tab:
  - **Peminjaman Aktif** (*Badge Warning*): Menampilkan buku yang sedang dipinjam atau terlambat.
  - **Dikembalikan** (*Badge Success*): Menampilkan riwayat transaksi buku yang sudah dikembalikan.
  - **Semua Transaksi**: Menampilkan seluruh log historis sirkulasi.

## 2. Fitur Input Transaksi Peminjaman Manual oleh Admin
- **Pengaktifan Tombol Create**: Mengaktifkan kembali `canCreate() = true` pada `PeminjamanAktifResource.php` (kini dinamai `PeminjamanResource`).
- **Pencarian Fleksibel**:
  - **Peminjam**: Mengizinkan pencarian dan pemindaian barcode Kartu Siswa (NISN/NIS) maupun NIP Guru.
  - **Buku & Eksemplar**: Mengizinkan pencarian dan pemindaian barcode label fisik buku (Kode Eksemplar) yang hanya menampilkan stok berstatus `tersedia`.
- **Kalkulasi Jatuh Tempo Dinamis**: Tanggal jatuh tempo dihitung otomatis berdasarkan setting `lama_pinjam_buku_hari` dari `PengaturanSekolah` (default 7 hari).
- **Otomatisasi Status Eksemplar**:
  - Menyimpan transaksi baru otomatis mengubah status eksemplar dari `tersedia` menjadi `dipinjam`.
  - Mengklik tombol **Kembalikan** otomatis mengubah status transaksi menjadi `dikembalikan` dan status eksemplar menjadi `tersedia`.

## 3. Kebijakan Soft Delete & Pemulihan Kaskade (Cascade Restore)
- **Restorasi Kaskade Inventaris**: Saat tombol **"Pulihkan Entri"** ditekan di *Inventaris Buku*, sistem secara otomatis mengecek apakah Master Buku induknya dalam kondisi terhapus (*soft-deleted*). Jika ya, sistem otomatis memulihkan (*restore*) Master Buku + eksemplarnya agar langsung tayang kembali di katalog tanpa *double-work*.
- **Penyelamatan Relasi Soft Delete**: Menambahkan `withTrashed()` pada relasi `buku()` di model `InventarisBuku` dan `EksemplarBuku`, serta relasi `eksemplar()` & `eksemplarBuku()` di model `Peminjaman` agar tabel sirkulasi dan inventaris tetap menampilkan Judul Buku & Kode Eksemplar secara utuh meskipun datanya pernah terhapus.
- **Management Soft Delete Buku**: Menambahkan Tab **"Buku Aktif"** dan **"Sampah / Dihapus"** *(Badge Danger)* pada katalog Buku untuk memfasilitasi *Restore* dan *Force Delete* buku dengan proteksi kaskade eksemplar.

## 4. Penataan Ulang Struktur Sidebar Navigasi
Seluruh *resource* dan *page* dikelompokkan dan diurutkan secara rapi ke dalam 5 grup utama:
1. 📚 **Koleksi Buku** (Buku, Koleksi/Kategori, Klasifikasi DDC, Inventaris Buku)
2. 🔄 **Sirkulasi** (Peminjaman, Reservasi)
3. 👥 **Keanggotaan** (Anggota)
4. 📊 **Laporan** (Laporan Sirkulasi)
5. ⚙️ **Pengaturan** (Pengaturan Default)
