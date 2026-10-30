# 📋 Plan Matang Tahap 7 (Menu 7): SPIKAP - Sistem Pengaduan Anti-Perundungan (`/portal-guru/spikap`)

Dokumen ini merupakan **Blueprint Teknis & Panduan Implementasi Komprehensif** untuk pengembangan **Menu 7: SPIKAP (Anti-Perundungan & Pengaduan Siswa)** pada aplikasi mobile Flutter (`apk-android-presensi`) dan backend Laravel (`projek-absensi-barcode`), yang mengacu pada fitur web:
- **Komponen Livewire Kotak Masuk**: [`SpikapInbox.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Livewire/PortalGuru/SpikapInbox.php)
- **Komponen Livewire Detail Kasus**: [`SpikapDetail.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Livewire/PortalGuru/SpikapDetail.php)
- **Komponen Livewire Analitik Kasus**: [`SpikapAnalitik.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Livewire/PortalGuru/SpikapAnalitik.php)
- **Middleware Proteksi Akses**: [`EnsureCanAccessSpikapGuru.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Http/Middleware/EnsureCanAccessSpikapGuru.php)
- **Rujukan URL**: `http://localhost:8001/portal-guru/spikap`

---

## 1. Analisis Fitur Web & Aturan Ketat Hak Akses (Authorization Rule)

### 🔒 Aturan Hak Akses Utama (Spesifikasi Khusus Pengguna)
> **"Untuk menu ini, jika BUKAN jabatan Guru BK, Wali Kelas, atau Kepala Sekolah, maka TIDAK BISA memantau."**

Modul SPIKAP memuat informasi sensitif mengenai kasus perundungan (*bullying*), kekerasan fisik/verbal, pemalakan, atau pelecehan anak. Oleh karena itu, prinsip kerahasiaan (*confidentiality*) diterapkan secara mutlak:

1. **Pengguna yang Berhak Mengakses**:
   - **Kepala Sekolah**: Hak pemantauan menyeluruh (*Supervisory Oversight*) atas seluruh kasus di sekolah dan dasbor analitik.
   - **Guru Bimbingan Konseling (BK)**: Menangani laporan biasa yang ditujukan ke Guru BK, laporan darurat, dan memantau siswa pada kelas pantauan/binaan BK, serta akses dasbor analitik penanganan kasus.
   - **Wali Kelas**: Menangani laporan yang ditujukan ke Wali Kelas dan laporan darurat yang melibatkan siswa dari kelas binaan/ampualannya saja.
   - **Super Admin / SPIKAP Admin**: Hak akses administratif dan teknis.

2. **Pengguna Tanpa Hak Akses (Guru Mata Pelajaran Non-Wali/Non-BK)**:
   - Pada **Dashboard Mobile (`DashboardGuruScreen`)**:
     - Badge Menu 7 menampilkan label: `Akses Khusus (BK/Wali/KS)`.
     - Ketika diklik, aplikasi **tidak membuka data kasus**, melainkan menampilkan dialog/bottomsheet informatif edukatif:
       *"Akses Terbatas: Modul SPIKAP bersifat rahasia dan hanya dapat diakses oleh Guru BK, Wali Kelas, dan Kepala Sekolah demi menjamin perlindungan anak dan privasi siswa."*
   - Pada **Backend API**:
     - Memverifikasi fungsi `$user->canAccessSpikapGuru()`. Jika bernilai `false`, API mengembalikan respons HTTP `403 Forbidden` dengan pesan keamanan yang jelas.

---

## 2. Arsitektur Antarmuka Mobile (Wireframe UX)

### A. Layar Kotak Masuk SPIKAP (`SpikapScreen`)
```
┌──────────────────────────────────────────────────────────────┐
│  ←  SPIKAP (Pengaduan & Anti-Perundungan)              🔍    │
│     SMP Negeri 3 Kedungreja • Peran: Guru BK / Wali          │
├──────────────────────────────────────────────────────────────┤
│  [ KPI RINGKASAN LAPORAN ]                                   │
│  ┌──────────────┬──────────────┬──────────────┬───────────┐  │
│  │ Total Laporan│ 🚨 Darurat   │ ⏳ Diproses  │ ✅ Selesai│  │
│  │ 12 Kasus     │ 2 Kritis     │ 4 Investigasi│ 6 Kasus   │  │
│  └──────────────┴──────────────┴──────────────┴───────────┘  │
├──────────────────────────────────────────────────────────────┤
│  [ SEARCH & FILTER CHIP ]                                    │
│  [ 🔍 Cari nama korban, lokasi, atau uraian kejadian...   ]  │
│  (•) Semua (12)  ( ) 🚨 Darurat (2)  ( ) Baru (3)  ( ) Selesai│
├──────────────────────────────────────────────────────────────┤
│  [ DAFTAR TIKET LAPORAN ADUAN ]                              │
│                                                              │
│  ┌────────────────────────────────────────────────────────┐  │
│  │ [🚨 DARURAT]  #SPK-084                     Baru • 2j lalu│  │
│  │ Siswa: MUHAMMAD RIZKY (8A)                             │  │
│  │ Jenis: Fisik • Lokasi: Belakang Kantin Sekolah         │  │
│  │ "Didorong dan dipalak uang jajan oleh kakak kelas..."  │  │
│  │ [📎 2 Foto Bukti]                                      │  │
│  │ ────────────────────────────────────────────────────── │  │
│  │ Status: Menunggu Tindakan       [🔍 Buka Investigasi]  │  │
│  └────────────────────────────────────────────────────────┘  │
│  ┌────────────────────────────────────────────────────────┐  │
│  │ [ℹ️ BIASA]    #SPK-082             Investigasi • Kemarin│  │
│  │ Siswa: SITI AISYAH (7B)                                │  │
│  │ Jenis: Verbal / Siber • Lokasi: Grup WhatsApp Kelas    │  │
│  │ "Diejek di grup WA dengan kata-kata merendahkan..."    │  │
│  │ ────────────────────────────────────────────────────── │  │
│  │ Status: Dalam Investigasi       [🔍 Buka Investigasi]  │  │
│  └────────────────────────────────────────────────────────┘  │
└──────────────────────────────────────────────────────────────┘
```

### B. Lembar Detail Laporan & Investigasi (`SpikapDetailScreen`)
```
┌──────────────────────────────────────────────────────────────┐
│  ←  Investigasi Kasus #SPK-084                         🛡️    │
├──────────────────────────────────────────────────────────────┤
│  [ STATUS BANNER ]                                           │
│  🚨 LAPORAN DARURAT (Status: Dalam Investigasi)             │
├──────────────────────────────────────────────────────────────┤
│  [ IDENTITAS KORBAN & PELAPOR ]                              │
│  Nama Siswa : Muhammad Rizky (NISN: 3146943024)              │
│  Kelas      : 8A (Wali Kelas: Sutrisno, S.Pd.)               │
│  Kontak Ortu: 0812-3456-7890 [💬 WA Ortu]                    │
├──────────────────────────────────────────────────────────────┤
│  [ KRONOLOGI KEJADIAN ]                                      │
│  Waktu Kejadian  : 29 Okt 2026, 10:15 WIB                    │
│  Lokasi Kejadian : Belakang Kantin Sekolah                   │
│  Kategori Kasus  : Perundungan Fisik                         │
│  Uraian Lengkap  :                                           │
│  "Korban dihadang saat jam istirahat oleh 2 siswa..."        │
├──────────────────────────────────────────────────────────────┤
│  [ LAMPIRAN BUKTI KASUS (FOTO / VIDEO) ]                     │
│  ┌──────────────┐  ┌──────────────┐                          │
│  │ [Foto Luka]  │  │ [Screenshot] │  (Klik untuk perbesar)   │
│  └──────────────┘  └──────────────┘                          │
├──────────────────────────────────────────────────────────────┤
│  [ FORM TINDAK LANJUT INVESTIGASI ]                          │
│  Pilih Status: ( ) Diterima  (•) Dalam Investigasi  ( ) Selesai
│  Catatan Tindak Lanjut:                                      │
│  [ Siswa yang terlibat sudah dipanggil ke ruang BK...      ] │
│  ☑️ Kirim notifikasi WA perkembangan kasus ke orang tua      │
│  [ 💾 Simpan Pembaruan Kasus ]                               │
├──────────────────────────────────────────────────────────────┤
│  [ AUDIT TRAIL LOG STATUS ]                                  │
│  • 30 Okt 09:00: Status diubah ke 'Dalam Investigasi' (Guru BK)
│  • 29 Okt 10:20: Laporan masuk diterima sistem               │
└──────────────────────────────────────────────────────────────┘
```

---

## 3. Arsitektur Backend Modular Laravel (`projek-absensi-barcode`)

Seluruh endpoint Menu 7 ditempatkan pada namespace modular:
`app/Http/Controllers/Api/Mobile/Spikap/`

```
app/Http/Controllers/Api/Mobile/Spikap/
├── SpikapBaseController.php       # Resolusi hak akses (BK/Wali/KS), verifikasi canAccessSpikapGuru()
├── SpikapInboxController.php      # List tiket laporan, filter status/sifat/jenis, search & ringkasan KPI
├── SpikapDetailController.php     # Detail lengkap kasus, bukti foto/video, ubah status, catat log & notifikasi WA
└── SpikapAnalitikController.php   # Agregasi statistik kasus per bulan/jenis/lokasi (khusus KS & BK)
```

### Rincian Endpoint REST API (`routes/api.php`):
Prefix: `/api/v1/guru/spikap`

| No | Endpoint | Method | Fungsi & Otorisasi |
|:---|:---|:---:|:---|
| 1 | `/check-access` | `GET` | Memeriksa apakah guru berhak mengakses modul SPIKAP (BK / Wali / KS) beserta peran khususnya |
| 2 | `/inbox` | `GET` | Mengambil daftar tiket laporan terfilter sesuai peran (KS melihat semua, BK melihat binaan & darurat, Wali melihat kelas binaan) |
| 3 | `/laporan/{id}` | `GET` | Mengambil data lengkap 1 kasus, identitas siswa, kronologi, lampiran bukti, dan riwayat audit trail |
| 4 | `/laporan/{id}/status` | `POST` | Memperbarui status penanganan (`diterima`, `dalam_investigasi`, `selesai`), mencatat log audit, dan kirim notifikasi WA |
| 5 | `/analitik` | `GET` | Mengambil grafik tren kasus dan pemetaan kategori (Khusus Kepala Sekolah & Guru BK) |

---

## 4. Rincian 6 Tahap Implementasi

### 🔹 Tahap 1: Backend API Modular & Otorisasi Hak Akses Ketat
- Membuat 4 sub-controller modular di `app/Http/Controllers/Api/Mobile/Spikap/`.
- Memvalidasi otorisasi dengan metode bawaan `$user->canAccessSpikapGuru()`. Jika gagal, lempar status HTTP 403 Forbidden.
- Mengintegrasikan scope data `SpikapLaporan::forUser($user)` agar Kepala Sekolah, Guru BK, dan Wali Kelas mendapatkan data yang sesuai dengan batasan kewenangannya.
- Mendaftarkan rute lengkap di `routes/api.php`.

### 🔹 Tahap 2: Dart Models & SpikapService (Flutter)
- Membuat model data:
  - `spikap_laporan_model.dart`: Detail kasus, sifat biasa/darurat, jenis perundungan, pelapor, kronologi, status penanganan.
  - `spikap_lampiran_model.dart`: Berkas bukti foto/video beserta URL stream aman.
  - `spikap_log_status_model.dart`: Audit trail riwayat investigasi dan catatan guru penangan.
  - `spikap_access_model.dart`: Status hak akses (`canAccess`, `roleLabel`, `isBk`, `isWali`, `isKs`).
- Membuat `SpikapService` (`lib/features/guru/services/spikap_service.dart`) dengan penanganan token Sanctum dan identifier.
- Mendaftarkan endpoint di `lib/core/constants/api_endpoints.dart`.

### 🔹 Tahap 3: Proteksi & Penanganan Hak Akses di Dashboard Guru
- Di `DashboardGuruScreen`:
  - Ambil parameter `can_access_spikap` dari endpoint ringkasan guru.
  - Jika **tidak memiliki hak akses** (guru mapel biasa):
    - Tampilkan badge `"Akses Khusus (BK/Wali/KS)"` pada Card Menu 7.
    - Ketika ditekan, munculkan Bottom Sheet edukatif: *"Akses Dibatasi: Menu SPIKAP hanya untuk Guru BK, Wali Kelas, dan Kepala Sekolah."*
  - Jika **memiliki hak akses**:
    - Tampilkan badge jumlah aduan masuk.
    - Ketika ditekan, navigasi langsung ke `SpikapScreen`.

### 🔹 Tahap 4: Layar Kotak Masuk SPIKAP (`SpikapScreen`)
- KPI Summary Cards: Total Kasus, Laporan Darurat, Sedang Investigasi, Selesai.
- Search Bar Debounce & Filter Chips (Semua, Darurat, Baru, Investigasi, Selesai).
- List Card Aduan Siswa Interaktif dengan badge urgensi darurat dan cuplikan kejadian.
- Refresh Indicator dan penanganan state loading/error.

### 🔹 Tahap 5: Lembar Detail Kasus, Bukti Lampiran & Form Update Status (`SpikapDetailScreen`)
- Menampilkan rincian identitas korban/pelapor, kelas, kronologi kejadian, dan lokasi.
- Preview galeri lampiran berkas bukti (foto & video).
- Form pembaruan status investigasi: `diterima` &rarr; `dalam_investigasi` &rarr; `selesai` dilengkapi catatan penanganan.
- Checkbox otomatis kirim notifikasi WhatsApp ke orang tua/siswa.
- Tampilan timeline audit trail log penanganan kasus.

### 🔹 Tahap 6: Dasbor Analitik Kasus & Finalisasi
- Tab/Modal Analitik Khusus Kepala Sekolah & Guru BK (pemetaan jenis kekerasan, tren per bulan).
- Pengujian komprehensif hak akses (Guru Mapel vs Wali Kelas vs Guru BK vs Kepala Sekolah).
- Commit & push kedua repositori dan update dokumentasi.

---

## 5. Matriks Pengujian & Verifikasi

1. **Pengujian Hak Akses (Otorisasi Negatif & Positif)**:
   - Guru Mapel biasa non-wali/non-BK: Mengakses menu memunculkan dialog blokir ramah dan API merespons HTTP 403.
   - Wali Kelas: Hanya melihat laporan dari siswa di kelas binaannya.
   - Guru BK: Melihat laporan umum siswa binaan BK dan seluruh laporan darurat.
   - Kepala Sekolah: Melihat seluruh laporan di sekolah.
2. **Penanganan Kasus Darurat**:
   - Laporan berkategori darurat memiliki indikator visual merah menyala dan ditempatkan pada urutan teratas.
3. **Audit Trail**:
   - Setiap pergantian status tercatat dengan rapi beserta nama guru dan catatan investigasinya.

---

## 6. Status Eksekusi Fitur
- [x] **Tahap 1: Backend API Modular & Otorisasi Hak Akses Ketat** (Selesai - Commit `2640e8f`)
- [x] **Tahap 2: Dart Models & SpikapService (Flutter)** (Selesai - `spikap_models.dart`, `spikap_service.dart`)
- [x] **Tahap 3: Proteksi & Penanganan Hak Akses di Dashboard Guru** (Selesai - `DialogAksesSpikapDitolak`, `DashboardGuruScreen`)
- [x] **Tahap 4: Layar Kotak Masuk SPIKAP** (Selesai - `SpikapScreen`)
- [x] **Tahap 5: Lembar Detail Kasus & Form Status** (Selesai - `SpikapDetailScreen`)
- [x] **Tahap 6: Dasbor Analitik Kasus & Finalisasi** (Selesai - `SpikapAnalitikScreen`)


