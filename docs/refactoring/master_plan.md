# Master Plan Refactoring: Modul Inti Siswa (Master Data)

**Tujuan Utama:**
Mengekstrak entitas `Siswa` agar menjadi "Modul Inti (Master Data)" murni yang bebas dari logika transaksional operasional (seperti presensi, pendaftaran kelas). Modul inti ini nantinya akan siap menampung standar data Dapodik dan menjadi fondasi kokoh untuk modul masa depan (seperti LMS dan Sistem Pembayaran).

---

## Peta Arsitektur Modul (Big Picture)

Berikut adalah gambaran besar arsitektur sistem yang menjadi acuan arah refactoring ini.
Setiap modul baru yang ditambahkan di masa depan harus mengikuti struktur layer ini.

```
╔══════════════════════════════════════════════════════════════════════╗
║              LAYER 1 — MASTER DATA (Fondasi / Referensi)            ║
║                                                                      ║
║  ┌────────────────────────┐   ┌──────────────────────────────────┐  ║
║  │   Modul Inti Siswa     │   │   Modul Akademik (Referensi)     │  ║
║  │                        │   │                                  │  ║
║  │  Tabel:                │   │  Tabel:                          │  ║
║  │  ▸ students            │   │  ▸ academic_years (TahunAjaran)  │  ║
║  │                        │   │  ▸ classes (Kelas)               │  ║
║  │  Isi Data:             │   │  ▸ class_academic_year (Pivot)   │  ║
║  │  ▸ Identitas (Nama,    │   │                                  │  ║
║  │    NIS, NISN, TTL)     │   │  Isi Data:                       │  ║
║  │  ▸ Status siswa        │   │  ▸ Definisi kelas (VII A, dsb)   │  ║
║  │    (Aktif/Mutasi/Lulus)│   │  ▸ Tahun ajaran aktif            │  ║
║  │  ▸ Data Dapodik        │   │  ▸ Kelas aktif per tahun ajaran  │  ║
║  └────────────────────────┘   └──────────────────────────────────┘  ║
╚══════════════════════════════════════════════════════════════════════╝
                         ↓  dikonsumsi oleh
╔══════════════════════════════════════════════════════════════════════╗
║           LAYER 2 — OPERASIONAL (Data Transaksional)                ║
║                                                                      ║
║  ┌─────────────────┐  ┌──────────────────┐  ┌───────────────────┐  ║
║  │  Modul Presensi │  │ Modul Enrollment │  │ Modul Pembayaran  │  ║
║  │  (Sudah Ada)    │  │ (Sudah Ada)      │  │ (Masa Depan)      │  ║
║  │                 │  │                  │  │                   │  ║
║  │  Tabel:         │  │  Tabel:          │  │  Tabel:           │  ║
║  │  ▸ attendances  │  │  ▸ student_      │  │  ▸ invoices       │  ║
║  │  ▸ student_     │  │    enrollments   │  │  ▸ payments       │  ║
║  │    presensi_    │  │                  │  │                   │  ║
║  │    profiles     │  │                  │  │                   │  ║
║  └─────────────────┘  └──────────────────┘  └───────────────────┘  ║
╚══════════════════════════════════════════════════════════════════════╝
                         ↓  diakses oleh
╔══════════════════════════════════════════════════════════════════════╗
║              LAYER 3 — UI / PRESENTASI (Filament Resources)         ║
║                                                                      ║
║   SiswaResource │ EnrollmentResource │ PresensiResource │ dst...     ║
╚══════════════════════════════════════════════════════════════════════╝
```

### Klarifikasi Scope: Apa yang Termasuk "Modul Inti Siswa"?

| Entitas | Termasuk Modul Inti Siswa? | Alasan |
|---|:---:|---|
| `students` (biodata, NIS, status) | ✅ **Ya** | Identitas murni siswa |
| `academic_years` (Tahun Ajaran) | ❌ **Tidak** | Ini *Master Data Akademik*, bukan identitas siswa |
| `classes` (Kelas) | ❌ **Tidak** | Ini *Master Data Akademik*, referensi untuk Enrollment |
| `student_enrollments` (Pendaftaran Kelas) | ❌ **Tidak** | Ini data *transaksional* (siswa di kelas X tahun Y) |
| `attendances` (Presensi) | ❌ **Tidak** | Ini data *operasional* milik Modul Presensi |
| `student_presensi_profiles` (Barcode) | ❌ **Tidak** | Ini *profil operasional* untuk Presensi |

> **Catatan Penting:**
> Kelas & Tahun Ajaran memang **terpisah** dari Modul Inti Siswa — dan itu **sudah benar**.
> Modul Inti Siswa hanya bertanggung jawab atas **siapa siswa itu** (identitas & status).
> Sedangkan **di mana siswa itu belajar** (kelas & tahun ajaran) adalah tanggung jawab Modul Enrollment.
> Keduanya dihubungkan secara longgar (loose coupling) melalui Event-Driven Architecture (Tahap 2).

> **Keputusan Arsitektur (2026-07-17): Pisah Total**
> Pendekatan yang dipilih adalah **pisah total** antara data Siswa dan data Enrollment.
> - **Import Siswa Baru** di `SiswaResource` → hanya mengisi tabel `students` (tanpa kolom Kelas)
> - **Pendaftaran Kelas / Enrollment** sepenuhnya di `EnrollmentResource` (pilih siswa, assign ke kelas + tahun ajaran)
> - Alasan: Arsitektur bersih jangka panjang, siap untuk Dapodik, LMS, dan Pembayaran.

---

### Roadmap Modul Masa Depan

Dengan fondasi yang bersih, modul-modul berikut dapat dibangun di atas Layer 1 tanpa konflik:

| Modul | Layer | Ketergantungan Utama |
|---|---|---|
| **LMS (E-Learning)** | Operasional | `students`, `student_enrollments`, `academic_years` |
| **Pembayaran / SPP** | Operasional | `students`, `academic_years` |
| **Rapor / Penilaian** | Operasional | `students`, `student_enrollments`, `classes` |
| **Perpustakaan** | Operasional | `students` |
| **Inventaris** | Operasional | `teachers` |

---

## Tahap 1: Ekstraksi Data & Pemindahan Logika (Status: ✅ SELESAI PENUH)
**Fokus:** Memisahkan penyimpanan data presensi dan menstrukturkan ulang logika tanpa memutus alur yang sudah ada.
- [x] Membuat tabel baru `student_presensi_profiles` untuk menyimpan `barcode_code` dan `barcode_active`.
- [x] Membuat *migration* yang menyalin data barcode lama dari tabel `students` ke tabel baru (tanpa langsung men-drop kolom lama).
- [x] Membuat Model `StudentPresensiProfile` dan mengaitkannya via relasi di Model `Siswa`.
- [x] Mengekstrak logika dari *closure* Filament ke dalam Single Responsibility Action Classes:
  - `MutateStudentAction`
  - `ReactivateStudentAction`
  - `GraduateStudentAction`
- [x] Mengganti logika *inline* di `SiswaTable` agar memanggil `MutateStudentAction` dan `ReactivateStudentAction`.
- [x] Mengganti logika *inline* di `SiswaMutasiResource` agar memanggil `ReactivateStudentAction`.
- [x] Mengganti logika *inline* di `SiswaLulusResource` agar memanggil `ReactivateStudentAction::cancelGraduation()` (sekaligus memperbaiki *bug*: enrollment lulus kini ikut di-rollback).

---

## Tahap 2: Penerapan Event-Driven Architecture (Status: ✅ SELESAI PENUH)
**Fokus:** Memutus (*decouple*) ketergantungan erat antara Modul Siswa dan Modul Enrollment / Presensi menggunakan pola Pub/Sub (synchronous).

### Events yang Dibuat (`app/Events/Student/`)
- [x] `StudentMutated` — dipicu saat siswa ditandai mutasi
- [x] `StudentGraduated` — dipicu saat siswa dinyatakan lulus (membawa `$activeYearId`)
- [x] `StudentReactivated` — dipicu saat siswa aktif kembali dari mutasi
- [x] `StudentGraduationCancelled` — dipicu saat kelulusan dibatalkan (membawa `$activeYearId`)

### Listeners Enrollment yang Dibuat (`app/Listeners/Enrollment/`)
- [x] `HandleStudentMutated` — ubah enrollment aktif → `pindah`
- [x] `HandleStudentGraduated` — ubah enrollment aktif/tahun ajaran tertentu → `lulus`
- [x] `HandleStudentReactivated` — kembalikan enrollment `pindah` → `aktif`
- [x] `HandleStudentGraduationCancelled` — kembalikan enrollment `lulus` → `aktif`

### Listeners Presensi yang Dibuat (`app/Listeners/Presensi/`)
- [x] `HandleStudentDeactivatedForPresensi` — nonaktifkan barcode (mendengarkan `StudentMutated` **|** `StudentGraduated` via PHP 8 Union Types)
- [x] `HandleStudentReactivatedForPresensi` — aktifkan kembali barcode (mendengarkan `StudentReactivated` **|** `StudentGraduationCancelled` via PHP 8 Union Types)

### Action Classes Dimodifikasi
- [x] `MutateStudentAction` — hapus logika enrollment langsung, tambah `event(new StudentMutated)` dalam `DB::transaction`
- [x] `GraduateStudentAction` — hapus logika enrollment langsung, tambah `event(new StudentGraduated)` dalam `DB::transaction`
- [x] `ReactivateStudentAction` — `execute()` + `cancelGraduation()` masing-masing dispatch Event dalam `DB::transaction`

### Registrasi
- [x] Semua pasangan Event → Listener didaftarkan di `AppServiceProvider::boot()` via `Event::listen()`
- *Keuntungan:* Modul Master Siswa sama sekali tidak lagi memanggil tabel/kode Enrollment maupun Presensi secara langsung.

---

## Tahap 3: Perapihan UI / Filament Resources (Status: ✅ SELESAI PENUH)
**Fokus:** Membersihkan antarmuka Master Data dari tanggung jawab Modul Enrollment dan Presensi. Menerapkan keputusan **pisah total** pada UI layer.

### 3A — Pemisahan Import Siswa vs Enrollment
- [x] **Pisahkan template Excel** Import Siswa Baru: hapus kolom `Kelas` dari template, sehingga import hanya mengisi tabel `students`.
- [x] **Pisahkan logika di `ImportSiswaBaruAction`**: hapus validasi kelas & pendaftaran ke kelas. Action ini hanya membuat data identitas siswa.
- [x] **Buat/pindahkan action "Daftarkan Siswa ke Kelas"** ke `EnrollmentResource` → admin dapat memilih siswa yang belum punya enrollment dan assign ke kelas + tahun ajaran aktif (`BulkEnrollStudentsAction`).

### 3B — Bersihkan Aksi Presensi dari Modul Siswa
- [x] Pindahkan/pisahkan aksi **"Cetak Kartu Presensi"** (per siswa & massal) dari `SiswaTable` ke Resource/menu khusus Presensi (`ManajemenKartuPresensi` Page).
- [x] Pastikan `SiswaResource` tidak lagi memuat *concern* tentang barcode/kartu login.

### 3C — Struktur Navigasi Filament
- [x] Pastikan grup navigasi terstruktur dengan jelas:
  - **Master Data** → `SiswaResource`, `KelasResource`, `TahunAjaranResource`
  - **Akademik / Operasional** → `EnrollmentResource`
  - **Presensi** → `PresensiResource`, `ManajemenKartuPresensi`

---

## Tahap 4: Cleanup & Finalisasi Production (Status: 🔜 MENDATANG)
**Fokus:** Pembersihan sisa-sisa *legacy code* setelah refactoring terbukti aman di production.
- [ ] Validasi dan *testing* menyeluruh di *local* maupun *staging environment*.
- [ ] Membuat "Migration B" yang secara spesifik melakukan `dropColumn` untuk `barcode_code` dan `barcode_active` pada tabel `students`.
- [ ] Eksekusi "Migration B" di *production* ketika tabel `student_presensi_profiles` sudah berjalan mulus tanpa masalah.

---

*Dokumen ini merupakan "living document" yang akan diperbarui seiring dengan berjalannya proses refactoring.*
*Terakhir diperbarui: 2026-07-17 — Tahap 3 SELESAI (Perapihan UI Filament)*
