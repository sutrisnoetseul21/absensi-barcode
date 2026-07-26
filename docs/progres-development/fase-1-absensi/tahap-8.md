# TAHAP 8 — Import/Export Excel & Cetak Kartu OSIS

> **Goal**: Admin bisa upload data siswa via Excel dan cetak kartu OSIS ber-barcode.
> Referensi: `03-features.md`, `09-third-party.md`.

- `[ ]` Install `Maatwebsite/Laravel-Excel` dan `picqer/php-barcode-generator` dan `barryvdh/laravel-dompdf`
- `[ ]` **Import Siswa (Excel)**:
  - Download template Excel (header: NISN, Nama, Tempat Lahir, Tanggal Lahir, Alamat, Kelas)
  - Upload Excel → validasi NISN → jika ada: UPDATE; jika baru: INSERT
  - Laporan hasil import: X berhasil, Y gagal (dengan keterangan error per baris)
- `[ ]` **Export/Download Presensi (Excel)**:
  - Filter: pilih Kelas + Bulan + Tahun Ajaran
  - Format: tabel rekap absensi per siswa per hari
- `[ ]` **Cetak Kartu OSIS (PDF)**:
  - Layout kartu sesuai blueprint (header sekolah, foto kiri, biodata kanan, barcode bawah, TTD kepsek)
  - Bisa cetak 1 siswa atau massal (pilih kelas)
  - Generate barcode dari `barcode_code` siswa menggunakan `picqer/php-barcode-generator`

**Verifikasi Tahap 8 Selesai:**
- [ ] Upload Excel 10 data → semua tersimpan/terupdate dengan benar
- [ ] Download Excel presensi berhasil dengan format yang benar
- [ ] PDF Kartu OSIS ter-generate dengan foto, barcode, dan biodata lengkap

**Status: Belum dimulai ⬜**
