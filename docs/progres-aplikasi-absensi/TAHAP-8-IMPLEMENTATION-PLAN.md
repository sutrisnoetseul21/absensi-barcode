# TAHAP 8 - IMPLEMENTATION PLAN: Import/Export Excel & Cetak Kartu OSIS

**Goal**: Menyediakan fitur Import massal biodata siswa via Excel, Export presensi ke Excel (format matrix dinamis), dan Cetak Kartu OSIS ber-barcode ke format PDF.

## Keputusan User Review

> [!IMPORTANT]
> **Format Password Default Siswa Baru (Import)**: Password dapat diisi oleh admin/guru melalui kolom Excel. Jika dibiarkan kosong, password default adalah **NISN** siswa. Field `must_change_password` diset ke `false` agar siswa tidak dipaksa mengganti password.

> [!WARNING]
> **Barcode Code Scanner**: Saya akan menggunakan format barcode standar **Code128** dari `picqer/php-barcode-generator` karena ukurannya pas untuk teks numerik (NISN). Jika scanner Kios Anda tidak mendukung `Code128`, mohon beri tahu agar formatnya bisa diganti.

> [!NOTE]
> **Template Kartu OSIS**: Karena tidak ada blueprint spesifik mengenai desain fisik, saya akan menggunakan standar layout vertikal berukuran ID card (CR80: 85.6mm x 54mm) yang terlihat profesional dan elegan. Saya juga akan memastikan fallback jika foto siswa/TTD kosong agar PDF tidak error.

## Hasil Verifikasi Skema
Berdasarkan pengecekan file migration dan model secara langsung di server, ini adalah temuan verifikasi (sesuai instruksi poin 2):

- **Struktur tabel `students` (model `Siswa`)**: Sudah dipastikan memiliki kolom yang lengkap sesuai blueprint (`nisn`, `name`, `birth_place`, `birth_date`, `address`, `photo_path`, `barcode_code`, `barcode_active`, `username`, `password`, `must_change_password`).
- **Mekanisme generate `barcode_code` & `username`**: Di model `Siswa.php` pada event `saving`, `barcode_code` dan `username` otomatis di-fallback ke `nisn` jika belum diset. Proses import bisa memanfaatkan ini.
- **Tabel `school_settings`**: Kolom `principal_signature_path` **BELUM ADA**. Kita benar-benar perlu menambahkannya via migration baru (retroaktif Tahap 3).
- **Tabel Enrollment Siswa**: Tabel penghubung siswa dan kelas pada tahun ajaran aktif telah terverifikasi yaitu `student_enrollments` yang terhubung ke `academic_years` dan `classes` dengan enum `status`.

## Proposed Changes

---

### 1. Retroaktif Tahap 3: Pengaturan Sekolah (Tanda Tangan)
Menambahkan kolom TTD Kepala Sekolah untuk ditampilkan di Kartu OSIS.

#### [NEW] `database/migrations/2026_07_XX_XXXXXX_add_principal_signature_path_to_school_settings_table.php`
- Menambahkan nullable column `principal_signature_path`.
#### [MODIFY] `app/Filament/Pages/PengaturanSekolah.php`
- Menambahkan field `FileUpload` untuk gambar tanda tangan (PNG) di panel pengaturan.

---

### 2. Import Siswa (Excel)
Mengimplementasi upload massal data siswa menggunakan template yang bisa didownload.

#### [MODIFY] `app/Filament/Resources/Siswa/Tables/SiswaTable.php`
- Menambahkan/menyempurnakan Action `download_template_siswa_baru`.
- Menyempurnakan Action `ImportSiswaBaruAction`.
#### [NEW] `app/Exports/SiswaBaruTemplateExport.php`
- Template header & 1 baris contoh: NISN, Nama, Tempat Lahir, Tanggal Lahir, Alamat, Kelas, Password.
#### [MODIFY] `app/Imports/SiswaBaruImport.php`
- Melakukan logic *upsert*: jika NISN ada -> update data biodata; jika tidak ada -> insert baru beserta pembuatan `student_enrollments` ke kelas yang ditentukan.
- Menyediakan UI laporan (Notification Filament dengan file detail error/baris gagal jika perlu menggunakan `SkipsOnFailure`).

---

### 3. Export Presensi Matrix
Membuat report presensi rekap absen (H/T/S/I/A) dalam bentuk matrix.

#### [NEW] `app/Exports/PresensiMatrixExport.php`
- Logic array 2D matrix (`FromArray`, `WithHeadings`).
- Menghandle logic pengecekan hari libur/weekend sebagai "-" atau "LIBUR".
#### [MODIFY] `app/Filament/Pages/RekapAbsensiKelas.php` 
- (Atau page rekap lainnya) Tambahkan tombol/action Export Excel (download presensi bulan tertentu & kelas tertentu).

---

### 4. Cetak Kartu OSIS (PDF)
Membuat action cetak PDF ID Card menggunakan mPDF / dompdf.

#### [NEW] `resources/views/pdf/kartu-osis.blade.php`
- Layout 1 kartu atau grid (tergantung mode satuan/massal).
- Terdapat foto, biodata, base64 inline Barcode128, dan TTD Kepala Sekolah.
#### [MODIFY] `app/Filament/Resources/Siswa/Tables/SiswaTable.php`
- Action di tiap baris data: "Cetak Kartu".
- Bulk Action/Action di header untuk Cetak Massal per kelas.

## Verification Plan

### Automated Tests
*Aplikasi ini difokuskan pada manual testing dari UI Filament Admin.*

### Manual Verification
- **Test Pengaturan Sekolah**: Upload file tanda tangan `.png` di halaman Pengaturan Sekolah lalu simpan.
- **Test Import**:
  - Download template dan isi dengan ~5 data (2 siswa baru, 1 siswa dengan kelas salah, 1 duplikat dalam file).
  - Lakukan Import, pastikan yang gagal ditolak dan diberikan peringatan, siswa lama hanya diupdate biodatanya, siswa baru terbuat (termasuk `student_enrollments` ke kelas aktif).
- **Test Export Matrix**: Masuk ke menu laporan presensi, pilih bulan dan tekan tombol Download Excel. Buka file hasil dan verifikasi matrix grid sesuai (baris: siswa, kolom: tanggal, dan isian tidak bolong).
- **Test Cetak Kartu OSIS**:
  - Cetak satuan pada baris siswa tertentu -> Lihat PDF terbuka dengan layout ID Card dan ukuran kertas CR80.
  - Test scan barcode yang tertera pada layar menggunakan aplikasi scanner barcode (di HP) untuk memastikan terbaca sesuai NISN.
