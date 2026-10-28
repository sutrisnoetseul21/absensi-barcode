# Tahap 15: Smart Draft Cart Sirkulasi, Kartu Siswa di Profil, & Unifikasi Portal Perpustakaan (31 Juli 2026)

## 📌 Ringkasan Pengerjaan

Pada Tahap 15 ini, dilakukan penyempurnaan menyeluruh pada Modul Perpustakaan dan Portal Siswa, meliputi implementasi alur **Smart Multi-Book Draft Cart Sirkulasi**, penataan ulang layout sirkulasi & peminjaman di Portal Perpustakaan, pencatatan otomatis nama sekolah di cetak label, serta pemindahan pencetakan Kartu Digital Siswa ke halaman Profil.

---

## 🚀 Fitur & Perubahan Utama

### 1. Kartu Digital Siswa di Halaman Profil (`/portal-siswa/profil`)
- **Penempatan Baru**: Komponen pratinjau dan pencetakan Kartu Digital Siswa (`<x-kartu-siswa-card>`) dipindahkan secara permanen ke dalam halaman profil siswa (`#kartu-digital-siswa`).
- **Pembersihan Navigasi**: Menu "Cetak Kartu Siswa" pada sidebar portal siswa dihapus, digantikan dengan tombol pintas langsung dari dashboard ke halaman profil.

### 2. Smart Multi-Book Draft Cart Sirkulasi Perpustakaan
- **Draft Cart Temporary**: Scan barcode buku tidak lagi langsung mengubah database per-buku. Buku yang di-scan akan dikumpulkan ke dalam keranjang transaksi sementara (*Draft Cart*).
- **Penentuan Aksi Otomatis**:
  - Buku baru di-scan → `PINJAM BARU`.
  - Buku sedang dipinjam oleh anggota yang sama → `PENGEMBALIAN`.
  - Tombol Toggle: Petugas dapat mengubah aksi pengembalian menjadi `PERPANJANG +7 HARI`.
- **Pengamanan Transaksi (Batch Submit)**: Seluruh daftar buku di dalam keranjang di-commit dalam satu transaksi atomic `DB::transaction()` via method `processBatchSirkulasi()`.
- **Pembatalan Scan**: Tombol hapus (ikon tempat sampah merah) pada setiap baris keranjang untuk membatalkan buku yang salah scan sebelum submit.
- **Informasi Peminjam Lain (Mode Admin)**: Jika buku yang di-scan sedang dipinjam oleh anggota lain, sistem menampilkan informasi detail (Nama peminjam, Role/Kelas, serta tanggal jatuh tempo).

### 3. Restrukturisasi & Unifikasi Portal Perpustakaan (`/portal-perpustakaan`)
- **Embedded Scanner Sirkulasi (`/portal-perpustakaan/sirkulasi`)**:
  - Widget scanner kiosk berbasis AlpineJS di-embed langsung ke dalam halaman sirkulasi portal.
  - Menghilangkan overlay "Sentuh Layar" sehingga scanner langsung aktif (*`isActive: true`*) begitu halaman dibuka.
- **Menu Baru "Data Peminjaman" (`/portal-perpustakaan/peminjaman`)**:
  - Tabel transaksi peminjaman dipisahkan dari halaman sirkulasi ke halaman khusus.
  - Dilengkapi **3 Tab Navigasi**:
    1. 🟡 **Dipinjam (Aktif)** (badge count)
    2. 🔴 **Terlambat** (badge count merah)
    3. ✅ **Riwayat Pengembalian** (badge count hijau)
  - Fitur pencarian cepat (*search bar*) dan tombol aksi langsung "Kembalikan Buku".
- **Menu Cetak Label pada Katalog Buku (`/portal-perpustakaan/buku`)**:
  - Ditambahkan 2 tombol cetak langsung pada setiap baris koleksi buku: **Barcode** dan **Label Spine**.

### 4. Penyamaan Nomenklatur & Dynamic School Name di Cetak Label
- Penyederhanaan label UI: Mengubah teks "Kiosk Sirkulasi Perpustakaan" dan "Kiosk Kunjungan" menjadi lebih bersih: **"Sirkulasi"** dan **"Kunjungan"**.
- Header dokumen cetak label barcode & spine (`label-barcode-eksemplar.blade.php` & `label-spine-buku.blade.php`) kini 100% menggunakan nama sekolah dinamis dari database (`PengaturanSekolah::current()->school_name`), yaitu **"SMP Negeri 3 Kedungreja"**.

---

## 🛠️ File-File Utama yang Diubah / Dibuat

1. **`app/Actions/ProcessSirkulasiAction.php`**
   - Menambahkan `processCheckBuku()` untuk pre-validation keranjang draft cart.
   - Menambahkan `processBatchSirkulasi()` untuk eksekusi batch submit transaksi dalam `DB::transaction()`.
   - Menambahkan formatter pesan borrower info jika buku dipinjam anggota lain.

2. **`app/Livewire/PetugasPerpusSirkulasi.php` & `resources/views/livewire/petugas-perpus-sirkulasi.blade.php`**
   - Meng-embed widget sirkulasi kiosk langsung di layout portal.

3. **`app/Livewire/PetugasPerpusPeminjaman.php` & `resources/views/livewire/petugas-perpus-peminjaman.blade.php`** [NEW]
   - Component Livewire & View untuk menu Data Peminjaman dengan 3 tab navigasi.

4. **`resources/views/pdf/label-barcode-eksemplar.blade.php` & `resources/views/pdf/label-spine-buku.blade.php`**
   - Penyesuaian nama sekolah dari `$sekolah->school_name`.

5. **`resources/views/livewire/petugas-perpus-buku.blade.php`**
   - Penambahan opsi tombol cetak Barcode & Label Spine pada tabel katalog.

6. **`resources/views/components/layouts/portal.blade.php` & `routes/web.php`**
   - Pendaftaran route `/portal-perpustakaan/peminjaman` dan menu sidebar baru.
