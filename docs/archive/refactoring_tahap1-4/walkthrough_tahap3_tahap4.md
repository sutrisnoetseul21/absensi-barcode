# Laporan Refactoring: Tahap 3 & Tahap 4

Refactoring ini merupakan kelanjutan dan penyelesaian dari arsitektur *Modular Monolith*. Dengan diselesaikannya tahap 3 dan 4, sistem kini sepenuhnya menerapkan pemisahan yang ketat antara **Master Data (Siswa)**, **Akademik (Enrollment)**, dan **Operasional (Presensi)**.

## 🎯 Pencapaian Tahap 3 (Perapihan UI Filament)

Pada tahap ini, fokus utama adalah membersihkan antarmuka Filament agar sesuai dengan domain logic-nya masing-masing.

### 1. Pemisahan Import Siswa vs Pendaftaran Kelas (Enrollment)
Sebelumnya, import Excel Siswa Baru secara otomatis mendaftarkan siswa ke kelas tertentu. Hal ini melanggar batas *bounded context* dari modul siswa.

**Perubahan yang Dilakukan:**
- **[Export Template]** `SiswaBaruTemplateSheet.php` dan `SiswaBaruTemplateExport.php` dimodifikasi: Kolom "Kelas" (H) dan validasi *dropdown* dihapus. Template murni hanya meminta data identitas.
- **[Import Logic]** `SiswaBaruImport.php` dan `ImportSiswaBaruAction.php` dibersihkan dari seluruh logika `EnrollmentSiswa`. Proses import kini hanya melakukan *upsert* ke tabel `students`.
- **[Fitur Baru]** Untuk menggantikan *auto-enrollment*, dibuat `BulkEnrollStudentsAction.php` di dalam menu **Pendaftaran Kelas (`EnrollmentResource`)**.
  - Admin dapat memilih Tahun Ajaran & Kelas.
  - Dropdown hanya menampilkan **Siswa Aktif yang belum memiliki kelas** di tahun ajaran tersebut (mencegah *duplicate enrollment* secara otomatis).

### 2. Ekstraksi Aksi Cetak Kartu Presensi
Tabel `SiswaTable` (Master Data) sebelumnya memiliki tombol cetak kartu presensi/barcode. Ini telah dihapus sepenuhnya (per *record*, *header*, dan *bulk action*).

**Perubahan yang Dilakukan:**
- Dibuat halaman khusus `ManajemenKartuPresensi.php` (berada di bawah grup navigasi **Presensi**).
- Halaman ini memuat tabel siswa yang relasinya difokuskan pada `presensiProfile` (Barcode) dan pendaftaran kelas (Tahun Ajaran Aktif) untuk filter pencetakan.

### 3. Struktur Navigasi Filament
Menu pendaftaran kelas (`EnrollmentResource`) telah dipindahkan dari grup "Data Master" ke grup **"Akademik"** agar sesuai dengan sifat operasional/transaksional-nya.

## 🎯 Pencapaian Tahap 4 (Cleanup & Finalisasi Production)

Tahap 4 adalah langkah pamungkas (akhir) untuk menghapus sisa *legacy code* setelah refactoring tahap 1-3 selesai dan tabel baru terbukti berjalan baik.

**Perubahan yang Dilakukan:**
- **Migration B:** Membuat file migration baru `2026_07_17_030613_drop_barcode_columns_from_students_table.php`.
- **Drop Column:** Melakukan `dropColumn` untuk kolom `barcode_code` dan `barcode_active` pada tabel `students`.
- **Execution:** Migration telah dijalankan dan database `students` kini bersih dari relasi struktural ke sistem presensi. Data barcode kini *sepenuhnya* bersumber dari tabel `student_presensi_profiles`.

---

## ✅ Kesimpulan Arsitektur Akhir
Dengan selesainya seluruh 4 tahap refactoring, struktur ERP ini:
1. **Lebih Kuat:** Perubahan status siswa (*mutasi*, *lulus*, dll) menggunakan *Event-Driven Architecture*, tidak perlu repot *update* silang secara *hard-coded*.
2. **Lebih Bersih:** Tabel `students` kini sangat ramping dan persis sesuai format *Dapodik* murni.
3. **Lebih Modular:** UI Filament sudah dipisah. Menu Siswa untuk data siswa, menu Akademik untuk pendaftaran, dan menu Presensi untuk kartu/barcode.

**Semua tahap refactoring resmi dinyatakan SELESAI PENUH.**
