# Progres Aplikasi Absensi - Tahap 3: Stabilisasi & Manajemen Kelas (Wali Kelas & Import Excel)

Dokumen ini mencatat seluruh perubahan, keputusan desain, dan hasil pekerjaan pada Tahap 3 untuk memudahkan pemeliharaan (*maintenance*) di masa mendatang.

## Ringkasan Fitur & Perubahan

### 1. Tampilan Wali Kelas Aktif pada Tabel Kelas
* **File Terkait:** [KelasTable.php](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Filament/Resources/Kelas/Tables/KelasTable.php)
* **Deskripsi:** Menambahkan kolom `Wali Kelas (Aktif)` di tabel utama. Kolom ini akan mencari guru wali kelas pada tabel pivot `class_academic_year` berdasarkan **Tahun Ajaran Aktif** saat ini. Jika kosong atau belum ditentukan, kolom akan menampilkan tanda strip `—`.

### 2. Form Add / Edit Kelas Terintegrasi Wali Kelas
* **File Terkait:** [KelasForm.php](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Filament/Resources/Kelas/Schemas/KelasForm.php)
* **Deskripsi:** 
  * Menambahkan dropdown **Wali Kelas (Tahun Ajaran Aktif)** di form input kelas.
  * Menggunakan taktik `dehydrated(false)` dengan `afterStateHydrated()` dan `saveRelationshipsUsing()` untuk melakukan sinkronisasi otomatis ke tabel pivot `class_academic_year` sesuai tahun ajaran aktif tanpa memengaruhi kolom model `Kelas` itu sendiri.
  * Menghapus opsi SMA (Kelas 10, 11, 12) dari dropdown **Tingkat**, membatasinya hanya untuk SMP saja (Kelas 7, 8, 9).

### 3. Template Excel dengan Data Validation (Dropdown)
* **File Terkait:**
  * [KelasTemplateExport.php](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Exports/KelasTemplateExport.php)
  * [KelasTemplateSheet.php](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Exports/Sheets/KelasTemplateSheet.php)
  * [TeachersListSheet.php](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Exports/Sheets/TeachersListSheet.php)
* **Deskripsi:** 
  * Mengintegrasikan package `maatwebsite/excel` (Laravel Excel).
  * Membuat template ekspor `.xlsx` yang di dalamnya terpasang aturan *Data Validation* (Dropdown):
    * Kolom **Tingkat** hanya memperbolehkan opsi `7,8,9`.
    * Kolom **Wali Kelas** memuat nama-nama Guru secara dinamis (mengambil dari database guru saat ini) melalui *Sheet* tersembunyi `TeachersList`.
    * Mengunci baris validasi maksimal **33 baris** saja (karena kuota tiap angkatan maksimal hanya 11 kelas).

### 4. Pop-up Import Kelas dengan Preview Dinamis & Validasi Ganda
* **File Terkait:**
  * [KelasTable.php](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Filament/Resources/Kelas/Tables/KelasTable.php)
  * [KelasImport.php](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Imports/KelasImport.php)
* **Deskripsi:**
  * **Unggah Reaktif (Live):** Form upload di dalam modal pop-up dipasang status `->live()`. Begitu berkas ditarik/dipilih, file langsung diunggah sementara ke server.
  * **Preview Instan:** Blok `Placeholder` di bawah form upload akan langsung membaca file Excel sementara tersebut dan me-render tabel pratinjau HTML yang rapi dengan CSS inline (agar kebal terhadap *reset stylesheet* browser).
  * **Validasi Visual (Badge Peringatan):**
    * Tingkat divalidasi harus berupa angka 7, 8, atau 9. Jika di luar itu, tampil badge merah: `⚠️ [Tingkat] (Tidak valid)`.
    * Wali kelas divalidasi terdaftar di database guru. Jika tidak ada, tampil badge merah: `⚠️ [Nama] (Tidak terdaftar)`.
    * Baris Excel yang kosong otomatis di-filter agar tidak mengotori layar pratinjau.
  * **Pembatalan Kirim (Action Validation):**
    * Ketika user menekan tombol **Submit**, sistem akan melakukan pengecekan ulang.
    * Jika ditemukan tingkat yang tidak valid atau nama wali kelas tidak terdaftar, proses impor akan dibatalkan seketika dan menampilkan notifikasi pop-up merah yang merinci nama guru atau tingkat yang bermasalah.

### 5. Pop-up Import Guru via Excel dengan Preview & Validasi NIP
* **File Terkait:**
  * [CreateGuru.php](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Filament/Resources/Guru/Pages/CreateGuru.php)
  * [GuruTemplateExport.php](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Exports/GuruTemplateExport.php)
  * [GuruImport.php](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Imports/GuruImport.php)
  * [GuruTable.php](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Filament/Resources/Guru/Tables/GuruTable.php)
* **Deskripsi:**
  * **Nonaktifkan Wajib Ganti Password:** Status `must_change_password` di-set menjadi `false` pada form manual web dan proses impor.
  * **Template Kolom Excel:** Template `.xlsx` berisi kolom: `Nama Guru` (Wajib), `NIP (Opsional)`, dan `Password (Opsional)`.
  * **Manajemen Sandi:** Jika kolom password dikosongkan, sistem mengeset default `'password'`. Jika diisi, menggunakan password inputan di Excel.
  * **Deteksi Bentrok NIP (Preview & Action):**
    * Sistem otomatis memeriksa NIP. Jika terdeteksi NIP yang diunggah sudah dimiliki oleh guru berlainan nama di database, akan muncul badge peringatan merah: `⚠️ [NIP] (Milik: [Nama Guru Lain])`.
    * Jika pengguna nekat menekan Submit dalam kondisi NIP bentrok, sistem memblokir import dan menembakkan notifikasi error merah.

### 6. Proteksi Berkas Silang (Cross-Template Protection)
* **File Terkait:** [KelasTable.php](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Filament/Resources/Kelas/Tables/KelasTable.php) & [GuruTable.php](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Filament/Resources/Guru/Tables/GuruTable.php)
* **Deskripsi:**
  * Menambahkan validasi **nama kolom header (baris pertama)** di file Excel untuk mencegah pengguna salah mengunggah file template.
  * Impor Kelas akan mengecek apakah header berisi kolom `'Nama Kelas'` dan `'Tingkat (7, 8, 9)'`.
  * Impor Guru akan mengecek apakah header berisi kolom `'Nama Guru'`.
  * Jika template yang diunggah tidak sesuai, baik di bagian pratinjau (Preview) maupun saat aksi pengiriman (Submit), sistem akan langsung menampilkan pesan error dan memblokir jalannya proses impor.

### 7. Tampilan Kelas Dinamis pada Tabel Siswa
* **File Terkait:** [SiswaTable.php](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Filament/Resources/Siswa/Tables/SiswaTable.php)
* **Deskripsi:** 
  * Menambahkan kolom `Kelas (Aktif)` di tabel utama.
  * Kolom ini tersinkronisasi dinamis dengan filter **Tahun Ajaran**. Jika filter Tahun Ajaran diaktifkan ke tahun tertentu, kolom Kelas akan otomatis mencari kelas siswa di tahun ajaran yang dipilih tersebut. Jika filter kosong, maka otomatis merujuk ke tahun ajaran aktif sistem.

### 8. Fitur Impor Siswa Baru, Kenaikan Kelas Massal, Kelulusan Kelas 9 & Redesign Tahun Ajaran
* **File Terkait:**
  * [CreateSiswa.php](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Filament/Resources/Siswa/Pages/CreateSiswa.php)
  * [SiswaBaruTemplateExport.php](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Exports/SiswaBaruTemplateExport.php)
  * [SiswaNaikKelasExport.php](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Exports/SiswaNaikKelasExport.php)
  * [SiswaBaruImport.php](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Imports/SiswaBaruImport.php)
  * [SiswaNaikKelasImport.php](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Imports/SiswaNaikKelasImport.php)
  * [SiswaTable.php](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Filament/Resources/Siswa/Tables/SiswaTable.php)
  * [TahunAjaranForm.php](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Filament/Resources/TahunAjarans/Schemas/TahunAjaranForm.php)
  * [TahunAjaransTable.php](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Filament/Resources/TahunAjarans/Tables/TahunAjaransTable.php)
  * [TahunAjaran.php (Model)](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Models/TahunAjaran.php)
  * [SiswaNaikKelasTemplateSheet.php](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Exports/Sheets/SiswaNaikKelasTemplateSheet.php)
* **Deskripsi:**
  * **Nonaktifkan Wajib Ganti Password:** Status `must_change_password` untuk pendaftaran siswa baru secara manual maupun impor Excel di-set ke `false`.
  * **Form Input manual Siswa (`SiswaForm.php`):** Menambahkan select dropdown **Kelas (Tahun Ajaran Aktif)**. Komponen ini memuat kelas secara dinamis dan menyinkronkan data langsung ke tabel pivot `student_enrollments` saat disimpan.
  * **Impor Siswa Baru:** Mengunggah file Excel berisi kolom: `NISN`, `Nama Siswa`, `Password`, `Kelas`. Otomatis terdaftar ke Tahun Ajaran aktif berjalan.

  * **Aksi Luluskan Kelas 9 Massal:** Tombol **Luluskan Kelas 9** di header tabel Siswa. Pop-up modal meminta admin memilih **Tahun Ajaran** sebelum mengkonfirmasi. Sistem secara massal mengubah status enrollment aktif siswa kelas 9 di tahun ajaran tersebut menjadi `'lulus'`.
  * **Aksi Batalkan Kelulusan (Massal):** Tombol **Batalkan Kelulusan** di header tabel Siswa. Admin memilih **Tahun Ajaran**, lalu sistem memulihkan status seluruh siswa yang berstatus `'lulus'` kembali menjadi `'aktif'`. Berguna untuk pembatalan jika salah pencet.
  * **Aksi Batalkan Kelulusan (Individual):** Aksi baris pada siswa yang berstatus lulus — mengembalikan status ke `'aktif'` satu per satu.

  * **Naik Kelas Massal (Dari TP → Ke TP):**
    * Admin memilih **Dari Tahun Ajaran** (TP asal) dan **Ke Tahun Ajaran** (TP tujuan) — dua select terpisah di modal.
    * Dropdown "Ke TP" bersifat **reaktif**: hanya menampilkan TP yang `start_year == dari_TP.end_year` — sehingga mustahil memilih TP yang tidak berurutan langsung.
    * **Guard 1 — Loncatan diblokir (server-side):** Jika `target.start_year ≠ source.end_year` → aksi dibatalkan dengan notifikasi error.
    * **Guard 2 — Kelas 9 harus lulus dulu:** Sistem mengecek apakah masih ada siswa kelas 9 berstatus `'aktif'` di TP asal. Jika masih ada → aksi diblokir dengan pesan berapa siswa yang belum lulus. **Wajib luluskan kelas 9 sebelum menaikkan kelas.**
    * Unduhan Excel mencantumkan riwayat kelas tiap siswa dengan format `"7B (2024/2025)"` per kolom Tingkat — sehingga admin tahu siswa tersebut di kelas mana pada tahun ajaran berapa.
    * Header kolom F dinamis: `"Kelas Baru (2027/2028)"` — mengikuti TP tujuan yang dipilih.
    * Validasi header saat upload menggunakan `str_starts_with("kelas baru")` agar cocok meski ada tahun di belakangnya.
    * Sistem menyaring: siswa kelas tingkat 9 dan yang **sudah aktif di TP tujuan tidak muncul** di Excel.
    * **Proteksi Lembar Kerja Excel:** Seluruh sheet dikunci kecuali kolom **Kelas Baru** (Kolom F) yang terbuka dengan dropdown kelas valid.
    * Logika impor: enrollment lama di TP asal diubah statusnya dari `'aktif'` → `'naik'`, lalu dibuat enrollment baru di TP tujuan dengan kelas baru yang dipilih, berstatus `'aktif'`.
    * Nama file unduhan mencantumkan TP asal dan tujuan: `"template_naik_kelas_2025-2026_ke_2026-2027.xlsx"`.

### 9. Redesign Tahun Ajaran — Input Tahun (Bukan Tanggal)
* **File Terkait:**
  * [TahunAjaranForm.php](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Filament/Resources/TahunAjarans/Schemas/TahunAjaranForm.php)
  * [TahunAjaransTable.php](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Filament/Resources/TahunAjarans/Tables/TahunAjaransTable.php)
  * [TahunAjaran.php (Model)](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Models/TahunAjaran.php)
  * Migration: `update_academic_years_use_year_integers.php`
* **Deskripsi:**
  * **Form diubah total:** `DatePicker` dan field `name` dihapus dari form. Diganti dengan dua `TextInput` integer: **Tahun Mulai** (`start_year`) dan **Tahun Selesai** (`end_year`).
  * **Validasi uniqueness:** `start_year` dan `end_year` masing-masing **UNIQUE** di database. Tidak bisa membuat dua TP dengan tahun mulai yang sama atau tahun selesai yang sama.
  * **Validasi urutan:** `end_year` harus lebih besar dari `start_year` (validasi `->gt('start_year')`).
  * **Nama auto-generate:** Field `name` tidak lagi diisi manual. Model `TahunAjaran` memiliki `boot()` yang otomatis mengisi `name = "{start_year}/{end_year}"` setiap kali record disimpan.
  * **Tabel urut otomatis:** `defaultSort('start_year', 'asc')` — Tahun Ajaran terlama tampil paling atas, terbaru di bawah.
  * **Kolom `start_date`/`end_date` dihapus** dari tabel `academic_years` (database) via migration.
  * **Model scope `orderedByYear()`** tersedia untuk query yang membutuhkan urutan berdasarkan `start_year ASC`.

---
*Dokumen ini dibuat pada **2 Juli 2026** dan terakhir diperbarui pada **2 Juli 2026**.*
