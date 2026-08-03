# Rencana Implementasi Migrasi SLiMS ke ERP

Sesuai permintaan Anda, sebelum melakukan perubahan kode atau *coding*, berikut adalah analisis dan perencanaan yang matang untuk migrasi data dari SLiMS ke **Projek Absensi Barcode** (Modul Perpustakaan).

## 1. Analisis Database Target (ERP)

Fokus kita hanya pada entitas perpustakaan, mengabaikan data Siswa, Guru, dan Mata Pelajaran. 
Berdasarkan struktur *migration* yang ada, berikut adalah tabel dan kolom yang akan menerima data dari SLiMS:

1. **`klasifikasi_ddcs` (Master DDC)**
   - `id` (UUID)
   - `kode_ddc` (String, Unique) -> *Map dari mst_topic (classification)*
   - `kategori` (String) -> *Map dari mst_topic (topic_name)*

2. **`kategori_bukus` (Kategori Buku)**
   - Akan diisi dengan nilai default seperti "Buku Paket", "Referensi", atau "Fiksi" sebagai *fallback* karena SLiMS mungkin memiliki pengkategorian yang berbeda.

3. **`bukus` (Katalog Buku Utama)**
   - `id` (UUID)
   - `kategori_id` (UUID) -> *Diisi dengan ID Kategori Default*
   - `mapel_id`, `grade_level` -> *Bisa dikosongkan (null) karena tidak relevan dengan SLiMS*
   - `judul` -> *Map dari `biblio.title`*
   - `penulis` -> *Map dari gabungan `mst_author.author_name` yang berelasi ke biblio*
   - `penerbit` -> *Map dari `mst_publisher.publisher_name`*
   - `tahun_terbit` -> *Map dari `biblio.publish_year`*
   - `isbn` -> *Map dari `biblio.isbn_issn`*
   - `lokasi_rak` -> *Map dari `mst_place.place_name` atau dikosongkan jika dinamis di eksemplar*

4. **`eksemplar_bukus` (Item / Fisik Buku)**
   - `id` (UUID)
   - `buku_id` (UUID) -> *Relasi ke tabel `bukus`*
   - `kode_eksemplar` (String, Unique) -> *Map dari `item.item_code` (Barcode di SLiMS)*
   - `status` -> *Map dari status item SLiMS ke enum ERP ('tersedia', 'dipinjam', 'rusak', 'hilang')*
   - `kondisi_fisik` -> *Diberikan default 'baik'*

> [!IMPORTANT]
> **Aturan Ketat Terpenuhi:** Semua field di atas adalah bawaan ERP. Tidak ada *alter table* atau pembuatan kolom baru. Data SLiMS yang berlebih (misal: abstrak, catatan khusus, GMD) akan diabaikan atau digabungkan.

---

## 2. Rencana Pembuatan Antarmuka (UI) di Panel Admin

Kita akan membuat halaman khusus (Custom Page) di **Filament** untuk panel `admin-perpustakaan`.

- **Nama Halaman:** `ImportSlims`
- **URL Akses:** `/admin-perpustakaan/import-slims`
- **Komponen Halaman:** Halaman ini akan berisi tombol/aksi untuk **"Mulai Sinkronisasi Data SLiMS"**. 
- **Pendekatan Koneksi Database:** 
  Daripada mengupload file SQL besar melalui browser, pendekatan paling stabil (berhubung kedua aplikasi ada di server/lokal yang sama) adalah menambahkan koneksi database sementara (`DB_CONNECTION_SLIMS`) di file `.env` dan `config/database.php` ERP, yang langsung menunjuk ke database `perpus-slims`.

---

## 3. Langkah Implementasi (Proposed Changes)

Berikut adalah daftar file yang akan dibuat dan dimodifikasi:

### Konfigurasi Database (Koneksi ke SLiMS)
#### [MODIFY] `config/database.php`
- Menambahkan konfigurasi koneksi array `'slims'` menggunakan driver MySQL.
#### [MODIFY] `.env`
- Menambahkan *environment variables* untuk database SLiMS (seperti `DB_SLIMS_DATABASE`, `DB_SLIMS_USERNAME`, dll).

### Filament Page (Antarmuka Sinkronisasi)
#### [NEW] `app/Filament/Perpustakaan/Pages/ImportSlims.php`
- Membuat class halaman baru yang menampilkan UI informasi migrasi dan _Action Button_ untuk menjalankan proses impor.
#### [NEW] `resources/views/filament/perpustakaan/pages/import-slims.blade.php`
- Tampilan blade sederhana dengan peringatan untuk mem-backup database sebelum proses, beserta indikator loading saat proses sinkronisasi berjalan.

### Service / Logika Bisnis Migrasi
#### [NEW] `app/Services/SlimsMigrationService.php`
- Membuat service class khusus untuk menangani proses penarikan data dari koneksi database `slims` ke koneksi utama ERP. Proses ini akan dijalankan dalam `DB::transaction()` agar jika terjadi error, data akan di-*rollback* (tidak ada data setengah masuk).

---

## 4. Rencana Verifikasi (Verification Plan)

- **Manual Verification:** 
  1. Menjalankan *Export/Backup* database ERP sebelum eksekusi (Telah kita lakukan: `absensi_barcode_backup_before_slims.sql`).
  2. Membuka halaman `/admin-perpustakaan/import-slims` dan menekan tombol eksekusi.
  3. Mengecek menu Katalog Buku dan Eksemplar Buku di `/admin-perpustakaan` untuk memastikan data masuk dengan benar tanpa merusak struktur atau relasi yang ada.

---

## Open Questions untuk Anda (User Review Required)

> [!CAUTION]
> **Mohon konfirmasi Anda mengenai dua hal ini:**
> 1. Apakah Anda setuju dengan pendekatan menggunakan **koneksi database langsung** (menambahkan `DB_CONNECTION_SLIMS`) ke database SLiMS yang sudah ada di lokal? Ini jauh lebih cepat dan aman daripada fitur *upload file .sql*.
> 2. Untuk database SLiMS, apakah nama databasenya sudah ada di MySQL lokal Anda? Jika iya, apa nama database SLiMS tersebut? (Saya asumsikan kita akan cek nama database dari konfigurasi `perpus-slims`).

Jika Anda setuju dengan rencana ini, silakan tekan **Proceed/Lanjutkan**, lalu berikan jawaban untuk nama database SLiMS di chat, dan saya akan mulai membuat koneksi dan komponen halamannya.
