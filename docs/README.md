# Dokumentasi Sistem ERP Sekolah (Tahap Pengembangan)

Selamat datang di direktori dokumentasi proyek **Sistem ERP (Enterprise Resource Planning) Sekolah**.

Proyek ini awalnya bermula sebagai Sistem Presensi Barcode (Fase 1), dan kini sedang dalam masa pengembangan menuju ERP komprehensif yang mencakup Kepegawaian, Akademik, Perpustakaan, dan Keuangan (Fase 2).

> **🤖 INSTRUKSI UNTUK AI AGENT (PENTING)**
> Jika kamu adalah AI yang baru masuk ke proyek ini untuk membantu pengguna (*user*), **wajib hukumnya** untuk selalu membaca dan mengacu pada file di dalam folder `docs/` (terutama `docs/blueprint/` dan `docs/penjelasan-relasi-data.md`) sebelum menulis kode, agar arsitektur *Loose Coupling* dan *Multi-Guard* tidak rusak. Jika kamu diminta mencatat progres, buatlah di dalam folder `docs/progres-development/`.

---

## Konsep Arsitektur (Menuju ERP)

Aplikasi ini awalnya dirancang untuk menangani absensi menggunakan barcode. Namun, seiring dengan proses *refactoring* (Pemisahan Layer Master Data vs Operasional), sistem ini telah memiliki fondasi arsitektur yang kuat dan siap dikembangkan lebih jauh (LMS, Pembayaran, dsb).

Arsitektur sistem dibagi menjadi beberapa *layer*:

1. **Layer Master Data (Fondasi / Referensi)**
   - Bertanggung jawab murni pada identitas inti dan data referensi abadi (misal: Data Identitas Siswa, Data Tahun Ajaran, Data Kelas).
   - Data di sini *agnostik* (tidak terikat pada proses transaksional tertentu).

2. **Layer Operasional (Transaksional)**
   - Modul-modul bisnis yang menggunakan entitas dari Master Data (misal: Modul Enrollment, Modul Presensi).
   - Setiap modul operasional bersifat *loose coupling* terhadap Master Data. Mereka merespon perubahan pada Master Data menggunakan **Event-Driven Architecture** (Pub/Sub).

3. **Layer UI / Presentasi (Filament)**
   - Antarmuka *resource* yang secara jelas memisahkan *concern* melalui arsitektur **Multi-Panel**:
     - **Portal Super Admin (`/admin`)**: Manajemen Root & Sistem.
     - **Portal Master Data (`/admin-master`)**: Manajemen Tahun Ajaran, Kelas, Siswa & Guru.
     - **Portal Akademik (`/admin-akademik`)**: Manajemen Pembagian Kelas & Mutasi Siswa.
     - **Portal Presensi (`/admin-presensi`)**: Input Presensi, Rekapitulasi & Libur.
   - Pintu masuk *Login* disatukan melalui *Portal Selection* (Gerbang Utama) di `/login`.

---

## Struktur Folder Dokumentasi

Berikut adalah panduan isi dari masing-masing folder di dalam `docs/`:

### 📂 `blueprint/`
Merupakan **Single Source of Truth** (Sumber Kebenaran Tunggal) dari spesifikasi teknis dan bisnis sistem. Jika Anda ingin mengetahui aturan main, skema database aktif, atau alur kerja sistem *saat ini*, silakan merujuk ke folder ini.
- `01-project-overview.md` - Gambaran besar proyek.
- `05-database.md` - Skema database terkini.
- `06-business-rules.md` - Aturan bisnis dan validasi.
- *(dan file blueprint lainnya)*

### 📂 `progres-development/`
Dokumen sejarah (fase-1-absensi) dan roadmap fitur ERP yang sedang dikerjakan.
(Misalnya, jika sedang mengerjakan "Tahap 3", buka file `progres-development/fase-1-absensi/tahap-3.md`).n riwayat fitur apa saja yang dikerjakan pada tahap tertentu, Anda bisa melihat file di sini.

### 📂 `stack/`
Berisi referensi, *guidelines*, dan panduan integrasi teknologi yang kita gunakan:
- Panduan Laravel 12 & Filament v4
- Panduan integrasi Alpine & Tailwind
- Persiapan database (MySQL)

### 📂 `archive/`
Menyimpan dokumen bersejarah (seperti catatan *refactoring* Tahap 1-4). Dokumen di sini sengaja disimpan untuk referensi perjalanan *development*, namun tidak lagi menjadi sumber rujukan utama untuk sistem berjalan.

---

*Dokumen ini bertujuan agar setiap developer yang bergabung dapat langsung memahami struktur proyek dan tidak tercampur antara dokumen rencana sistem (blueprint) dengan log pengerjaan (progres).*
