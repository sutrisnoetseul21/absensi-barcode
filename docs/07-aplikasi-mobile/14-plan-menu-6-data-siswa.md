# 📋 Plan Matang Tahap 6 (Menu 6): Data Siswa (`/portal-guru/data-siswa`)

Dokumen ini merupakan **Blueprint Teknis & Panduan Implementasi Komprehensif** untuk pengembangan **Menu 6: Data Siswa** pada aplikasi mobile Flutter (`apk-android-presensi`) dan backend Laravel (`projek-absensi-barcode`), yang mengacu pada fitur web:
- **Komponen Livewire**: [`DataSiswaList.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Livewire/PortalGuru/DataSiswaList.php)
- **Tampilan Blade**: [`data-siswa-list.blade.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/resources/views/livewire/portal-guru/data-siswa-list.blade.php)
- **Rujukan URL**: `http://localhost:8001/portal-guru/data-siswa`

---

## 1. Analisis Fitur Web & Tantangan Adaptasi Mobile

Di portal web, Menu **Data Siswa** adalah pusat manajemen siswa bagi Wali Kelas / Guru yang mengampu rombel, dengan kapabilitas:
1. **Pemilihan Konteks Akademik**: Dropdown Tahun Ajaran dan Kelas Binaan/Ampuan.
2. **Tabel Data Siswa Komprehensif**: Foto Siswa, NISN, NIS, Nama Lengkap, Kontak (HP Siswa & Ortu), serta Kode Barcode Presensi.
3. **Aksi Per Siswa**:
   - **Edit Data Lengkap**: Tempat/Tgl Lahir, Alamat, Gender (L/P), Agama, Tanggal & Kelas Masuk, Asal Sekolah, Status Keluarga, Anak Ke-, Nama & Pekerjaan Ayah/Ibu/Wali, No. HP, dan Ganti Foto Siswa.
   - **Edit No. HP Cepat**: Form modal ringkas untuk update No. HP Siswa & No. HP Orang Tua (dengan normalisasi otomatis `62...`).
   - **Ganti Password Akun Siswa**: Membantu siswa yang lupa password untuk login aplikasi mobile.
   - **Cetak Kartu Login / Barcode**: Unduh kartu digital barcode siswa individual.
   - **Cetak Biodata Siswa**: Unduh berkas PDF biodata resmi siswa per anak.
4. **Aksi Massal / Kelas**:
   - **Cetak Kartu Terpilih / Semua (Batch)** dalam format PDF siap cetak.
   - **Cetak Biodata Siswa 1 Rombel (Batch PDF)**.
   - **Ekspor / Impor Excel** (Data Lengkap & No. HP).
   - **Upload Foto Massal via ZIP** (Ekstrak & mapping otomatis ke NISN/NIS).

### Tantangan & Solusi Desain Mobile (Flutter UX)
Tabel horizontal 7 kolom pada web tidak cocok untuk layar HP portrait. Oleh karena itu, antarmuka mobile diadaptasi menjadi **Direktori Kartu Siswa Interaktif (Mobile Card-Based Student Directory)**:
- **Card Siswa Modern**: Foto avatar bulat (dengan ring status barcode hijau/abu), nama tebal, NISN/NIS, serta chip kelas.
- **1-Klik WhatsApp Orang Tua / Siswa (`wa.me`)**: Fitur unggulan smartphone yang memungkinkan guru langsung menghubungi orang tua murid via WA tanpa perlu menyalin nomor secara manual.
- **1-Klik Panggilan Telepon (`tel:`)**: Panggilan suara cepat saat situasi darurat.
- **Modal BottomSheet Aksi Cepat**: Menggantikan modal popup web dengan bottom sheet ergonomis yang nyaman dioperasikan satu tangan (One-Handed UX).
- **Pratinjau Kartu Siswa Digital Interaktif**: Menampilkan preview kartu pelajar dengan Barcode Code-128 dan QR Code langsung di layar HP sebelum dicetak.

---

## 2. Arsitektur Antarmuka Mobile (Wireframe UX)

```
┌──────────────────────────────────────────────────────────────┐
│  ←  Data Siswa                                         🔍    │
│     Wali Kelas: 8A • 2026/2027                               │
├──────────────────────────────────────────────────────────────┤
│  [ FILTER TAHUN AJARAN & KELAS ]                             │
│  ┌─────────────────────────┬───────────────────────────────┐ │
│  │ 📅 Thn: 2026/2027    ▼  │ 🏫 Kelas: 8A (32 Siswa)    ▼  │ │
│  └─────────────────────────┴───────────────────────────────┘ │
├──────────────────────────────────────────────────────────────┤
│  [ SEARCH & CHIP FILTER ]                                    │
│  [ 🔍 Cari nama, NISN, atau NIS...                      ]    │
│  (•) Semua (32)   ( ) Ada Barcode (30)   ( ) Belum No HP (2) │
├──────────────────────────────────────────────────────────────┤
│  [ RINGKASAN ROMBEL ]                                        │
│  ┌──────────────┬──────────────┬──────────────┬───────────┐  │
│  │ Total Siswa  │ Laki-Laki    │ Perempuan    │ Berbarcode│  │
│  │ 32 Anak      │ 15 Siswa     │ 17 Siswi     │ 94% 🟢    │  │
│  └──────────────┴──────────────┴──────────────┴───────────┘  │
├──────────────────────────────────────────────────────────────┤
│  [ DAFTAR KARTU SISWA (LIST INTERAKTIF) ]                    │
│                                                              │
│  ┌────────────────────────────────────────────────────────┐  │
│  │ [Foto]  HANUNAH                               [8A] 🟢  │  │
│  │         NISN: 3122319930 • NIS: 4044 • P               │  │
│  │         Barcode: 3122319930 (Aktif)                    │  │
│  │         Ortu: Bpk. Hasan (0812-3456-7890)              │  │
│  │         ─────────────────────────────────────────────  │  │
│  │         [💬 WhatsApp]  [📞 Telp]  [🪪 Kartu]  [⚙️ Opsi]│  │
│  └────────────────────────────────────────────────────────┘  │
│  ┌────────────────────────────────────────────────────────┐  │
│  │ [Foto]  INTAN ZAHRA FEBRIYANTI                [8A] 🟢  │  │
│  │         NISN: 3131454553 • NIS: 4045 • P               │  │
│  │         Barcode: 3131454553 (Aktif)                    │  │
│  │         Ortu: Ibu Siti (0857-1122-3344)                │  │
│  │         ─────────────────────────────────────────────  │  │
│  │         [💬 WhatsApp]  [📞 Telp]  [🪪 Kartu]  [⚙️ Opsi]│  │
│  └────────────────────────────────────────────────────────┘  │
├──────────────────────────────────────────────────────────────┤
│  [ ACTION BAR BAWAH / FLOATING ]                             │
│  [ 🖨️ Cetak Kartu Rombel ]     [ 📄 Unduh Biodata Kelas ]    │
└──────────────────────────────────────────────────────────────┘
```

---

## 3. Arsitektur Backend Modular Laravel (Anti-Monolitik)

Memenuhi prinsip kode bersih modular agar controller tidak melebihi 200–300 baris, seluruh endpoint Menu 6 dipecah menjadi modul-modul di direktori:
`app/Http/Controllers/Api/Mobile/DataSiswa/`

```
app/Http/Controllers/Api/Mobile/DataSiswa/
├── DataSiswaBaseController.php      # Resolusi guru, otorisasi kelas ampuan, & helper
├── DataSiswaListController.php      # Opsi tahun ajaran, kelas, direktori siswa, search & statistik
├── DataSiswaEditController.php      # Update biodata lengkap, update no HP, ganti password, upload foto
└── DataSiswaCetakController.php     # Stream/download PDF kartu login, kartu massal, & biodata resmi
```

### Rincian Endpoint REST API (`routes/api.php`):
Prefix: `/api/v1/guru/data-siswa`

| No | Endpoint | Method | Fungsi |
|:---|:---|:---:|:---|
| 1 | `/options` | `GET` | Mengambil daftar Tahun Ajaran dan Kelas yang diampu oleh Guru |
| 2 | `/students` | `GET` | Mengambil daftar siswa berdasarkan `class_id` & `academic_year_id` (dengan parameter `search`, `filter_barcode`, `filter_nohp`) |
| 3 | `/student/{id}` | `GET` | Mengambil profil & biodata lengkap 1 siswa untuk modal edit |
| 4 | `/student/{id}/biodata` | `PUT` | Memperbarui biodata lengkap siswa |
| 5 | `/student/{id}/no-hp` | `PATCH` | Memperbarui No. HP Siswa & No. HP Orang Tua (format otomatis `62...`) |
| 6 | `/student/{id}/ganti-password` | `POST` | Mengubah password akun login siswa (minimal 6 karakter) |
| 7 | `/student/{id}/upload-foto` | `POST` | Mengunggah foto profil siswa baru (JPEG/PNG) dengan kompresi |
| 8 | `/student/{id}/cetak-kartu` | `GET` | Mengambil data / PDF kartu pelajar barcode individual |
| 9 | `/student/{id}/cetak-biodata` | `GET` | Mengambil berkas PDF biodata resmi individual |
| 10 | `/cetak-kartu-massal` | `GET` | Mengunduh PDF kartu pelajar seluruh siswa dalam kelas terpilih |
| 11 | `/cetak-biodata-massal` | `GET` | Mengunduh PDF rekap biodata siswa 1 kelas terpilih |

---

## 4. Rincian 6 Tahap Implementasi

### 🔹 Tahap 1: Backend API Modular Data Siswa
- Membuat 4 sub-controller modular di `app/Http/Controllers/Api/Mobile/DataSiswa/`.
- Mendaftarkan rute lengkap di `routes/api.php` di bawah prefix `/guru/data-siswa`.
- Memastikan sanitasi input nomor telepon (`08...` otomatis menjadi `628...`).
- Uji coba respons endpoint via curl & tinker.

### 🔹 Tahap 2: Dart Models & DataSiswaService (Flutter)
- Membuat berkas model data:
  - `student_model.dart` (Profil siswa, NISN, NIS, Gender, Agama, Alamat, Kontak, Barcode, Data Ortu).
  - `data_siswa_filter_model.dart` (Tahun Ajaran, Kelas Options, Statistik Rombel).
- Membuat `DataSiswaService` (`lib/features/guru/services/data_siswa_service.dart`) dengan penanganan timeout, token Sanctum, dan header `X-User-Identifier`.
- Mendaftarkan endpoint di `lib/core/constants/api_endpoints.dart`.

### 🔹 Tahap 3: Layar Utama Mobile `DataSiswaScreen`
- Mengembangkan `lib/features/guru/screens/data_siswa_screen.dart`.
- Menghubungkan Menu 6 pada `dashboard_guru_screen.dart` (menggantikan notice placeholder "Menu 6 akan dikerjakan").
- Implementasi selector Tahun Ajaran & Kelas, Search Bar Debounce, dan Chip Filter.
- Grid/List Card Siswa modern dengan Avatar, Badge Barcode, dan ringkasan info.

### 🔹 Tahap 4: Integrasi 1-Klik Hubungi Ortu (WhatsApp & Call) + Modal Edit No. HP & Reset Password
- Integrasi `url_launcher` untuk 1-klik buka WhatsApp ortu (`https://wa.me/62...`) dan panggilan telepon (`tel:...`).
- Membuat `ModalEditNoHpSheet`: Dialog cepat update nomor HP Siswa dan Orang Tua.
- Membuat `DialogGantiPasswordSiswa`: Modal input password baru untuk membantu siswa yang lupa sandi.

### 🔹 Tahap 5: Modal Form Lengkap Edit Biodata Siswa + Upload Foto
- Membuat `ModalEditBiodataSiswaSheet`: Form lengkap terstruktur (Data Identitas, Data Sekolah & Keluarga, Data Orang Tua/Wali).
- Integrasi penggantian foto siswa: Ambil dari Kamera atau Galeri ponsel, lalu kirim ke backend API.

### 🔹 Tahap 6: Pratinjau Kartu Digital Barcode Siswa + Unduh PDF Kartu & Biodata
- Membuat `KartuSiswaDigitalDialog`: Pratinjau visual kartu pelajar siswa di layar smartphone dengan Barcode Code-128 & QR Code.
- Opsi cetak / unduh PDF:
  - Unduh Kartu Siswa Individual (PDF)
  - Unduh Kartu Siswa 1 Rombel Massal (PDF)
  - Unduh Berkas Biodata Siswa Resmi (PDF)
- Sinkronisasi dokumentasi akhir di kedua repositori.

---

## 5. Matriks Pengujian & Verifikasi

1. **Akses Guru Wali / Guru Pengampu**:
   - Guru hanya dapat melihat kelas binaan / kelas yang diampunya, atau seluruh kelas jika memiliki hak akses/admin.
2. **Konektivitas WhatsApp**:
   - Nomor HP otomatis terkonversi ke standar internasional `628...` saat tombol WhatsApp diklik.
3. **Integritas Barcode Presensi**:
   - Barcode presensi siswa tampil akurat dan sesuai dengan sistem pemindaian scanner sekolah.
4. **Keamanan Ganti Password**:
   - Validasi minimal 6 karakter, password ter-hash aman dengan `Hash::make()` di backend.
5. **Kinerja & Responsivitas**:
   - Waktu muat daftar siswa < 200ms dengan paginasi atau lazy list.
