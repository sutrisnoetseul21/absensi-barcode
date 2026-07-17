# Checklist & Dokumentasi Refactoring (Tahap 1)

**Tujuan Utama:** Memisahkan entitas "Siswa" menjadi *Master Data* murni yang terbebas dari logika operasional (seperti alat presensi, mutasi, kelulusan) untuk persiapan integrasi standar Dapodik.

## Pekerjaan yang Telah Diselesaikan (Tahap 1)
Berikut adalah daftar file yang telah dibuat dan dimodifikasi di dalam direktori `projek-absensi-barcode`:

1. **Database Migration**
   - File: `database/migrations/2026_07_16_165400_create_student_presensi_profiles_table.php`
   - *Fungsi*: Membuat tabel baru khusus untuk presensi tanpa melakukan *drop* pada kolom lama di tabel `students`. Terdapat *raw SQL statement* untuk menyalin data barcode secara otomatis.

2. **Model & Relasi**
   - File: `app/Models/StudentPresensiProfile.php` (Baru)
   - File: `app/Models/Siswa.php` (Dimodifikasi)
   - *Fungsi*: Menambahkan relasi `presensiProfile()` agar data barcode bisa diakses secara terpisah dari objek induk Siswa.

3. **Action Classes (Business Logic)**
   - Direktori: `app/Actions/Student/`
   - File:
     - `MutateStudentAction.php`
     - `ReactivateStudentAction.php`
     - `GraduateStudentAction.php`
   - *Fungsi*: Memindahkan logika status mutasi, reaktivasi, dan kelulusan dari UI Filament menjadi satu sumber kebenaran (*Single Source of Truth*).

---

## Yang Perlu Dicek dan Diverifikasi oleh Anda (User)

Sebelum kita melanjutkan ke Tahap 2, silakan lakukan verifikasi berikut di *local environment* Anda:

- [ ] **Jalankan Migration**
      Buka terminal di folder `projek-absensi-barcode` lalu jalankan:
      ```bash
      php artisan migrate
      ```
      Pastikan tidak ada *error* saat *migration* berjalan.

- [ ] **Periksa Tabel di Database**
      Buka *Database Manager* (phpMyAdmin, DBeaver, TablePlus, dsb), lalu periksa:
      1. Apakah tabel `student_presensi_profiles` berhasil terbuat?
      2. Apakah data `barcode_code` yang sudah ada di tabel `students` (jika ada) berhasil tersalin dengan benar ke tabel baru?

- [ ] **Pastikan Aplikasi Masih Berjalan Normal**
      Karena pada Tahap 1 kita belum menyentuh antarmuka UI (Filament Resources) maupun mengubah alur kode lama, aplikasi seharusnya tetap berjalan tanpa *error* dan *breaking changes*. 

---

## Langkah Selanjutnya (Tahap Berikutnya)
Jika checklist di atas sudah Anda konfirmasi aman, kabari saya agar kita bisa mengeksekusi tahapan berikutnya:

- **Tahap 2**: Pembuatan `Event` dan `Listener` (Pub/Sub) agar saat Action Class dijalankan, Modul Presensi/Pendaftaran Kelas akan bereaksi secara otomatis.
- **Tahap 3**: Mengubah (refactor) tombol/aksi di `SiswaResource`, `SiswaMutasiResource`, dan `SiswaLulusResource` (Filament) agar murni memanggil Action Class, bukan memodifikasi model secara langsung. 
