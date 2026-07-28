# Modul Perpustakaan: Tahap 9 (Penyempurnaan UI Anggota & Kartu Siswa)

**Status:** Selesai  
**Tanggal:** 28 Juli 2026

## Deskripsi Pekerjaan
Tahap ini adalah iterasi penyempurnaan (*polish*) setelah pengujian menu satu per satu pasca Tahap 8. Fokus utama pada peningkatan fitur halaman Anggota Perpustakaan dan penyesuaian Cetak Kartu Siswa agar lebih fleksibel dan informatif.

---

## Detail Perubahan

### 1. Halaman Anggota Perpustakaan (`AnggotaResource`)

#### Kolom Baru: Kelas
- Menambahkan kolom **Kelas** di sebelah kolom NISN/NIP.
- Untuk Siswa: mengambil data dari relasi `student_enrollments → classes` (tahun ajaran aktif).
- Untuk Guru: otomatis menampilkan tanda `-` karena tidak memiliki kelas.

#### Kolom Baru: Pinjaman Aktif (Interaktif)
- Menambahkan kolom **Pinjaman Aktif** di sebelah kolom Kelas.
- Angka dihitung via *Subquery* langsung di dalam `UNION` query (menghitung baris pada tabel `peminjamans` dengan status `dipinjam` atau `terlambat`).
- Kolom ditampilkan sebagai *badge* berwarna: abu-abu jika 0, kuning (*warning*) jika > 0.
- **Pop-up Modal Detail**: Jika administrator mengklik angka pada kolom tersebut, akan muncul modal yang merinci:
  - Judul buku dan kode eksemplar yang sedang dipinjam.
  - Tanggal pinjam dan tanggal jatuh tempo.
  - Badge status (Dipinjam / Terlambat) dengan warna merah untuk yang sudah melewati batas.

#### Kolom Kode Barcode: Copyable
- Menambahkan fitur `.copyable()` pada kolom **Kode Barcode**.
- Saat diklik, teks barcode otomatis tersalin ke *clipboard* dan muncul notifikasi pop-up "Kode barcode berhasil disalin".

#### Filter Baru
Menambahkan 3 filter via tombol corong di tabel:
1. **Tipe Anggota** (`SelectFilter`): menyaring khusus "Siswa" atau "Guru".
2. **Status Barcode** (`TernaryFilter`): menyaring barcode yang "Aktif" atau "Nonaktif".
3. **Memiliki Pinjaman Aktif** (`Filter` toggle): jika dinyalakan, hanya menampilkan anggota yang saat ini memiliki pinjaman aktif.

---

### 2. Pengaturan Perpustakaan (`PengaturanPerpustakaan`)

- Menambahkan tampilan *read-only* (disabled) untuk pengaturan **Mode Kios Scanner Barcode (Siswa)** yang menampilkan apakah sistem dikonfigurasi menggunakan NISN atau NIS.
- Field ini diambil dari `school_settings.barcode_scan_mode` dan ditampilkan dengan keterangan bantuan bahwa pengaturan aktual hanya bisa diubah melalui Pengaturan Admin Sekolah Utama.
- Tujuannya agar administrator perpustakaan dapat dengan mudah mengetahui mode scan yang aktif tanpa perlu berpindah panel.

---

### 3. Cetak Kartu Siswa (`kartu-login-siswa.blade.php` & `kartu-login-siswa-massal.blade.php`)

#### Judul Kartu
- Teks "**KARTU PRESENSI**" secara global diubah menjadi "**KARTU SISWA**" agar lebih universal dan tidak menyebabkan kebingungan saat kartu juga digunakan untuk keperluan perpustakaan.

#### Penyesuaian NIS vs NISN
- Sebelumnya, label dan nilai yang tercetak di kartu selalu mengambil NISN secara statis.
- Sekarang sistem membaca pengaturan `barcode_scan_mode` dari `PengaturanSekolah`:
  - Jika `nisn` → label "Username (NISN)", nilai yang dicetak adalah NISN siswa.
  - Jika `nis` → label "Username (NIS)", nilai yang dicetak adalah NIS siswa.
- Logika ini berlaku untuk cetak kartu **tunggal maupun massal**.

#### Footer URL Dinamis
- Teks footer kartu yang sebelumnya hardcoded `presensi.smpn1majenang.sch.id` sekarang diambil secara dinamis dari *environment variable* `.env`:
  ```
  SCHOOL_EMAIL_DOMAIN=smpn1majenang.sch.id
  ```
- Jika `SCHOOL_EMAIL_DOMAIN` tidak di-set di `.env`, sistem akan menggunakan nilai *fallback* `smpn1majenang.sch.id`.

---

## File yang Diubah
| File | Perubahan |
|---|---|
| `app/Filament/Perpustakaan/Pages/AnggotaResource.php` | Tambah kolom Kelas, Pinjaman Aktif, filter baru, copyable barcode |
| `app/Filament/Perpustakaan/Pages/PengaturanPerpustakaan.php` | Tambah tampilan read-only mode NIS/NISN |
| `resources/views/filament/perpustakaan/components/modal-pinjaman.blade.php` | File baru — view konten modal detail pinjaman |
| `resources/views/pdf/kartu-login-siswa.blade.php` | Ubah judul, NIS/NISN dinamis, URL footer dinamis |
| `resources/views/pdf/kartu-login-siswa-massal.blade.php` | Ubah judul, NIS/NISN dinamis, URL footer dinamis |
