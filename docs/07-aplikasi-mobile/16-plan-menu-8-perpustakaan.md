# 📋 Plan Matang Tahap 8 (Menu 8): Perpustakaan Sekolah (`/portal-guru/perpustakaan`)

Dokumen ini merupakan **Blueprint Teknis & Panduan Implementasi Komprehensif** untuk pengembangan **Menu 8: Perpustakaan (Katalog, Peminjaman Pribadi, Riwayat Kunjungan, & Pemantauan Siswa)** pada aplikasi mobile Flutter (`apk-android-presensi`) dan backend Laravel (`projek-absensi-barcode`), yang mengacu pada fitur web:
- **Komponen Livewire**: [`GuruPerpustakaan.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Livewire/GuruPerpustakaan.php)
- **Tampilan Blade**: [`guru-perpustakaan.blade.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/resources/views/livewire/guru-perpustakaan.blade.php)
- **Rujukan URL Web**: `http://localhost:8001/portal-guru/perpustakaan`

---

## 1. Analisis Fitur Web & Logika Bisnis Perpustakaan Guru

Pada portal web guru (`/portal-guru/perpustakaan`), modul Perpustakaan memiliki **5 pilar fungsi utama**:

### 1. 📚 Katalog Buku Perpustakaan (`katalog`)
- **Pencarian Terpadu**: Mencari judul buku, nama penulis, penerbit, atau nomor ISBN secara instan dengan debounce 300ms.
- **Pengurutan Fleksibel**:
  - `Buku Terbaru` (berdasarkan `created_at desc`)
  - `Sering Dipinjam / Populer` (berdasarkan agregasi `peminjamans_count desc`)
- **Informasi Kartu Buku**:
  - Sampul buku (*cover image*) dari `storage/{sampul_buku}` atau placeholder buku menarik.
  - Judul buku, nama penulis, dan kategori buku (`KategoriBuku`).
  - Nomor panggil buku (*Call Number*) dan klasifikasi DDC.
  - **Status Ketersediaan Real-Time**:
    - 🟡 **Sedang Anda Pinjam**: Ditandai warna kuning amber jika guru yang login sedang meminjam buku tersebut, disertai batas tanggal pengembalian (*earliest_return_date*).
    - 🟢 **Tersedia (X Eksemplar)**: Ditandai hijau emerald jika eksemplar buku masih ada di perpustakaan, disertai informasi **Lokasi Rak Fisik** (*misal: Rak B-02*).
    - 🔴 **Habis Dipinjam**: Ditandai merah jika seluruh eksemplar sedang dipinjam pihak lain, disertai perkiraan tanggal buku akan kembali tersedia (*earliest_return_date*).
- **Akses E-Book / PDF**: Jika buku memiliki berkas digital (`file_pdf`), guru dapat membaca pratinjau buku secara digital.

### 2. 📖 Pinjaman Pribadi Guru (`peminjaman`)
- Menampilkan buku-buku fisik yang **saat ini sedang aktif dipinjam oleh guru**.
- Memuat informasi: Judul buku, barcode kode eksemplar (`kode_eksemplar`), tanggal peminjaman, dan tanggal jatuh tempo.
- **Deteksi Keterlambatan**: Jika `now() > tanggal_jatuh_tempo`, status otomatis berlabel **Terlambat** berwarna merah dengan badge berdenyut (*pulse*).

### 3. 🕒 Riwayat Peminjaman Pribadi (`riwayat`)
- Menampilkan riwayat buku-buku yang **telah dikembalikan** oleh guru di masa lalu.
- Memuat informasi: Tanggal pinjam, tanggal aktual pengembalian (`tanggal_kembali`), dan status `Dikembalikan`.

### 4. 🏛️ Riwayat Kunjungan Fisik Guru (`kunjungan`)
- Rekam jejak kehadiran presensi fisik guru ke perpustakaan sekolah (`KunjunganPerpustakaan`).
- Memuat: Tanggal kunjungan, jam/waktu masuk, tujuan kunjungan (*Membaca, Meminjam Buku, Mengembalikan Buku, Riset / Literasi*), dan catatan petugas.

### 5. 👥 Pemantauan Peminjaman Siswa Kelas Ampu (`siswa`)
- Fitur kolaboratif bagi Guru & Wali Kelas untuk **memantau literasi siswa di kelas binaannya**.
- Otomatis memuat daftar **Kelas Ampu** guru (kombinasi: Wali Kelas, Guru Mata Pelajaran, dan Kelas Pantauan Guru BK pada tahun ajaran aktif).
- Selector / dropdown pemilihan kelas ampu aktif.
- Menampilkan daftar siswa yang sedang meminjam buku perpustakaan:
  - Nama siswa & inisial avatar.
  - Judul buku yang dipinjam.
  - Tanggal pinjam & batas jatuh tempo.
  - Status peminjaman (*Dipinjam* atau *Terlambat*).
  - Tombol pengingat komunikasi WhatsApp ke siswa/wali murid jika terjadi keterlambatan.

---

## 2. Arsitektur Antarmuka Mobile (Wireframe UX)

### Layar Utama Perpustakaan (`PerpustakaanScreen`)
```
┌──────────────────────────────────────────────────────────────┐
│  ←  Perpustakaan Sekolah                                🔍   │
│     SMP Negeri 3 Kedungreja • 2 Buku Sedang Dipinjam         │
├──────────────────────────────────────────────────────────────┤
│  [ HEADER BANNER GRADASI LITERASI ]                          │
│  📖 Pusat Literasi & Katalog Digital Sekolah                 │
│  "Temukan buku, pantau pinjaman & literasi kelas ampu"      │
├──────────────────────────────────────────────────────────────┤
│  [ 5 TAB NAVIGASI HORIZONTAL ]                               │
│  (•) 📚 Katalog  ( ) 📖 Pinjaman (2)  ( ) 🕒 Riwayat        │
│  ( ) 🏛️ Kunjungan  ( ) 👥 Siswa Ampu (8A, 8B)                │
├──────────────────────────────────────────────────────────────┤
│  [ KONTEN TAB 1: KATALOG BUKU ]                              │
│  ┌────────────────────────────────────────────────────────┐  │
│  │ 🔍 [ Cari judul buku, penulis, atau ISBN...         ]  │  │
│  │ Urutkan: [ Buku Terbaru ▼ ]  Kategori: [ Semua ▼ ]     │  │
│  └────────────────────────────────────────────────────────┘  │
│                                                              │
│  ┌────────────────────────────────────────────────────────┐  │
│  │ [Cover]  Laskar Pelangi                    🟢 Tersedia │  │
│  │          Andrea Hirata • Fiksi Sastra                  │  │
│  │          📍 Rak: A-04 • 3 Eksemplar Tersedia           │  │
│  │          [🔍 Detail & Info Rak]  [📖 Baca PDF]         │  │
│  └────────────────────────────────────────────────────────┘  │
│  ┌────────────────────────────────────────────────────────┐  │
│  │ [Cover]  Matematika Kelas 8        🟡 Sedang Dipinjam  │  │
│  │          Kemendikbud • Buku Pelajaran                  │  │
│  │          ⏳ Batas Kembali: 05 Nov 2026                 │  │
│  │          [🔍 Detail Buku]                              │  │
│  └────────────────────────────────────────────────────────┘  │
│  ┌────────────────────────────────────────────────────────┐  │
│  │ [Cover]  Bumi Manusia                    🔴 Habis      │  │
│  │          Pramoedya Ananta Toer • Sastra                │  │
│  │          Perkiraan Kembali: 02 Nov 2026                │  │
│  │          [🔍 Detail Buku]                              │  │
│  └────────────────────────────────────────────────────────┘  │
└──────────────────────────────────────────────────────────────┘
```

---

## 3. Arsitektur Backend REST API Modular Laravel (`projek-absensi-barcode`)

Endpoint baru ditempatkan pada namespace modular:
`app/Http/Controllers/Api/Mobile/Perpustakaan/`

```
app/Http/Controllers/Api/Mobile/Perpustakaan/
├── PerpustakaanBaseController.php       # Resolusi guru, user auth, helper kelas ampu
├── PerpustakaanKatalogController.php    # GET /katalog & GET /katalog/{id}
├── PerpustakaanPeminjamanController.php # GET /pinjaman-saya & GET /riwayat-pinjaman
├── PerpustakaanKunjunganController.php  # GET /kunjungan-saya
└── PerpustakaanSiswaController.php      # GET /kelas-ampu & GET /pinjaman-siswa
```

### Rincian Endpoint REST API (`routes/api.php`):
Prefix: `/api/v1/guru/perpustakaan`

| No | Endpoint | Method | Fungsi & Deskripsi |
|:---|:---|:---:|:---|
| 1 | `/summary` | `GET` | Mengambil ringkasan metrik: jumlah buku sedang dipinjam guru, terlambat, total riwayat, kunjungan, dan jumlah siswa ampu yang meminjam |
| 2 | `/katalog` | `GET` | Mengambil daftar katalog buku (search live, sort `terbaru`/`populer`, filter kategori, status ketersediaan & rak) |
| 3 | `/katalog/{id}` | `GET` | Mengambil detail lengkap 1 buku (deskripsi, penerbit, tahun, ISBN, klasifikasi DDC, eksemplar, link baca PDF) |
| 4 | `/kategori` | `GET` | Mengambil daftar master kategori buku untuk filter chip mobile |
| 5 | `/pinjaman-saya` | `GET` | Mengambil daftar buku yang sedang aktif dipinjam guru (kode eksemplar, batas waktu, status terlambat) |
| 6 | `/riwayat-pinjaman` | `GET` | Mengambil riwayat pengembalian buku guru berpaginasi |
| 7 | `/kunjungan-saya` | `GET` | Mengambil riwayat kehadiran fisik guru di perpustakaan (tanggal, waktu, tujuan, catatan) |
| 8 | `/kelas-ampu` | `GET` | Mengambil daftar kelas yang diampu guru pada tahun ajaran aktif (wali kelas, guru mapel, kelas pantau) |
| 9 | `/pinjaman-siswa` | `GET` | Mengambil daftar peminjaman buku oleh siswa pada kelas ampu tertentu (identitas siswa, judul buku, jatuh tempo, status) |

---

## 4. Rincian 6 Tahap Implementasi

### 🔹 Tahap 1: Backend REST API Modular Perpustakaan (Laravel)
- Membuat namespace controller modular: `app/Http/Controllers/Api/Mobile/Perpustakaan/`.
- Mengimplementasikan 5 sub-controller:
  - `PerpustakaanBaseController.php`: Logika resolusi guru, penanganan `peminjam_type` (`guru` & `wali_kelas`), penarikan kelas ampu dari tahun ajaran aktif.
  - `PerpustakaanKatalogController.php`: Query buku dengan `eksemplarBukus` count tersedia, status `dipinjam_oleh_saya`, `earliest_return_date`, live search, pagination.
  - `PerpustakaanPeminjamanController.php`: Query pinjaman aktif guru & riwayat pengembalian.
  - `PerpustakaanKunjunganController.php`: Query kunjungan fisik guru.
  - `PerpustakaanSiswaController.php`: Query peminjaman siswa di kelas binaan.
- Mendaftarkan rute lengkap di `routes/api.php` dan menguji endpoint via curl.

### 🔹 Tahap 2: Dart Models & PerpustakaanService (Flutter Mobile)
- Membuat model data di `lib/features/guru/models/perpustakaan_models.dart`:
  - `BukuKatalogItem`: ID, judul, penulis, penerbit, tahun, sampul URL, kategori, lokasi rak, eksemplar tersedia, status ketersediaan, status dipinjam oleh saya, batas kembali, file PDF URL.
  - `BukuDetailModel`: Spesifikasi lengkap, klasifikasi DDC, call number, daftar eksemplar.
  - `KategoriBukuItem`: ID dan nama kategori.
  - `PeminjamanGuruItem`: ID peminjaman, kode eksemplar, judul buku, sampul, tgl pinjam, batas kembali, status terlambat, sisa hari.
  - `RiwayatPeminjamanItem`: ID, judul, kode eksemplar, tgl pinjam, tgl kembali, status.
  - `KunjunganPerpustakaanItem`: Tanggal, waktu masuk, tujuan kunjungan, catatan.
  - `KelasAmpuPerpustakaanItem`: ID kelas dan nama kelas.
  - `PeminjamanSiswaItem`: ID siswa, nama siswa, NISN, judul buku, tgl pinjam, batas kembali, status terlambat.
  - `PerpustakaanSummaryModel`: Angka counter untuk badge dan header.
- Membuat `PerpustakaanService` di `lib/features/guru/services/perpustakaan_service.dart`.
- Menambahkan konstanta endpoint di `lib/core/constants/api_endpoints.dart`.

### 🔹 Tahap 3: Layar Utama & Tab 1: Katalog Buku Perpustakaan
- Membuat `PerpustakaanScreen` di `lib/features/guru/screens/perpustakaan_screen.dart`:
  - Header gradien tema perpustakaan dengan ringkasan status pinjaman aktif guru.
  - Tab Bar navigasi 5 pilar (Katalog, Pinjaman Saya, Riwayat, Kunjungan, Siswa).
- Membangun Tab 1 (Katalog Buku):
  - Search bar interaktif ber-debounce.
  - Filter chips kategori buku dan pemilih urutan (*Terbaru* vs *Populer*).
  - Grid / List kartu buku dengan sampul dinamis, badge ketersediaan (*Tersedia*, *Dipinjam*, *Habis*), dan lokasi rak.
  - Modal lembar detail buku (`_BukuDetailModal`) dengan opsi baca file PDF digital jika tersedia.

### 🔹 Tahap 4: Tab 2: Pinjaman Pribadi & Tab 3: Riwayat Pribadi
- Membangun Tab 2 (Pinjaman Pribadi Guru):
  - Daftar buku yang sedang aktif dipinjam guru.
  - Visual barcode kode eksemplar.
  - Indikator hitung mundur hari jatuh tempo pengembalian.
  - Badge peringatan *Terlambat* merah berdenyut jika melewati batas pengembalian.
- Membangun Tab 3 (Riwayat Pengembalian):
  - Daftar buku yang pernah dipinjam dan telah tuntas dikembalikan.
  - Tanggal pinjam dan tanggal kembali.

### 🔹 Tahap 5: Tab 4: Riwayat Kunjungan & Tab 5: Pemantauan Siswa Ampu
- Membangun Tab 4 (Riwayat Kunjungan Fisik):
  - Timeline kehadiran guru di perpustakaan sekolah beserta tujuan dan catatan petugas.
- Membangun Tab 5 (Pemantauan Siswa Kelas Ampu):
  - Selector kelas ampu aktif (kelas wali / kelas mapel / kelas BK).
  - Daftar siswa yang sedang meminjam buku perpustakaan.
  - Indikator keterlambatan buku siswa.
  - Tombol WhatsApp pengingat 1-klik ke nomor siswa/wali murid jika terjadi keterlambatan.

### 🔹 Tahap 6: Integrasi Dashboard Guru, Testing & Push GitHub
- Menghubungkan Card Menu 8 pada `DashboardGuruScreen` (`_openPerpustakaanMenu`).
- Menjalankan static analysis Dart (`analyze_files`) untuk memastikan 0 error.
- Pengujian hot reload pada aplikasi mobile.
- Sinkronisasi dokumentasi dan push kedua repositori ke GitHub.

---

## 5. Matriks Pengujian & Kriteria Keberhasilan

1. **Katalog Buku**:
   - Pencarian judul/penulis/ISBN mengembalikan hasil relevan tanpa lag.
   - Status ketersediaan buku mencerminkan kondisi riil eksemplar di perpustakaan.
   - Buku yang sedang dipinjam oleh guru bersangkutan otomatis ditandai kuning *"Sedang Anda Pinjam"*.
2. **Pinjaman Pribadi**:
   - Jumlah buku aktif pada dashboard sesuai dengan daftar yang tampil di tab pinjaman.
   - Peringatan keterlambatan muncul akurat jika tanggal hari ini telah melewati batas jatuh tempo.
3. **Pemantauan Siswa**:
   - Guru dapat berganti kelas ampu dan melihat daftar siswa yang meminjam buku.
   - Tombol WA pengingat membuka percakapan ke orang tua siswa yang meminjam.

---

## 6. Status Eksekusi Fitur
- [x] **Tahap 1: Backend REST API Modular Perpustakaan (Laravel)**
- [x] **Tahap 2: Dart Models & PerpustakaanService (Flutter)**
- [x] **Tahap 3: Layar Utama & Tab 1 Katalog Buku**
- [x] **Tahap 4: Tab 2 Pinjaman Pribadi & Tab 3 Riwayat Pribadi**
- [x] **Tahap 5: Tab 4 Kunjungan & Tab 5 Siswa Kelas Ampu**
- [x] **Tahap 6: Integrasi Dashboard Guru & Finalisasi**
