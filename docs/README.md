# Dokumentasi Sistem Presensi Barcode

Selamat datang di direktori dokumentasi proyek Sistem Presensi Barcode. 

Dokumentasi ini disusun untuk memandu jalannya pengembangan aplikasi dari tahap awal (MVP) hingga visi jangka panjang menjadi sebuah **ERP (Enterprise Resource Planning)** Sekolah yang komprehensif.

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
   - Antarmuka *resource* yang secara jelas memisahkan *concern* antara admin Master Data (seperti import biodata siswa) dan admin Operasional (seperti pendaftaran ke kelas/presensi).

---

## Struktur Folder Dokumentasi

Berikut adalah panduan isi dari masing-masing folder di dalam `docs/`:

### 📂 `blueprint/`
Merupakan **Single Source of Truth** (Sumber Kebenaran Tunggal) dari spesifikasi teknis dan bisnis sistem. Jika Anda ingin mengetahui aturan main, skema database aktif, atau alur kerja sistem *saat ini*, silakan merujuk ke folder ini.
- `01-project-overview.md` - Gambaran besar proyek.
- `05-database.md` - Skema database terkini.
- `06-business-rules.md` - Aturan bisnis dan validasi.
- *(dan file blueprint lainnya)*

### 📂 `progres-aplikasi-absensi/`
Folder ini berisi log atau catatan perkembangan pengerjaan secara kronologis (dari Tahap 0 sampai seterusnya). Jika Anda ingin melihat rincian riwayat fitur apa saja yang dikerjakan pada tahap tertentu, Anda bisa melihat file di sini.

### 📂 `stack/`
Berisi referensi, *guidelines*, dan panduan integrasi teknologi yang kita gunakan:
- Panduan Laravel 12 & Filament v4
- Panduan integrasi Alpine & Tailwind
- Persiapan database (MySQL)

### 📂 `archive/`
Menyimpan dokumen bersejarah (seperti catatan *refactoring* Tahap 1-4). Dokumen di sini sengaja disimpan untuk referensi perjalanan *development*, namun tidak lagi menjadi sumber rujukan utama untuk sistem berjalan.

---

*Dokumen ini bertujuan agar setiap developer yang bergabung dapat langsung memahami struktur proyek dan tidak tercampur antara dokumen rencana sistem (blueprint) dengan log pengerjaan (progres).*
