# Perencanaan & Analisis Migrasi Data SLiMS ke ERP (Projek Absensi Barcode)

## 1. Tujuan Utama
Dokumen ini merangkum proses pemindahan/sinkronisasi data dari sistem perpustakaan **SLiMS** (`perpus-slims`) ke dalam modul perpustakaan sistem ERP **Projek Absensi Barcode** (`projek-absensi-barcode`). Tujuannya agar katalog buku, eksemplar, klasifikasi DDC, dan pengarang SLiMS dapat diakses di portal perpustakaan ERP.

## 2. Analisis Database Target (ERP)
Fokus kita hanya pada entitas perpustakaan, mengabaikan data Siswa, Guru, dan Mata Pelajaran. Sesuai aturan ketat (**Tidak boleh ada perubahan struktur / penambahan kolom baru di ERP**), berikut adalah pemetaan _(mapping)_ yang akan digunakan:

1. **`klasifikasi_ddcs` (Master DDC)**
   - `kode_ddc` -> Map dari `mst_topic` (classification SLiMS).
   - `kategori` -> Map dari `mst_topic` (topic_name SLiMS).

2. **`kategori_bukus` (Kategori Buku)**
   - Akan diisi dengan data default/statis seperti "Buku Paket", "Referensi", atau "Fiksi", mengingat struktur klasifikasi SLiMS kadang tidak sepenuhnya sejalan dengan Kategori Buku ERP.

3. **`bukus` (Katalog Buku Utama)**
   - `kategori_id` -> Diisi dengan ID Kategori Default.
   - `mapel_id`, `grade_level` -> Dibiarkan `null`.
   - `judul` -> Map dari `biblio.title`.
   - `penulis` -> Map dari gabungan `mst_author.author_name`.
   - `penerbit` -> Map dari `mst_publisher.publisher_name`.
   - `tahun_terbit` -> Map dari `biblio.publish_year`.
   - `isbn` -> Map dari `biblio.isbn_issn`.
   - `lokasi_rak` -> Map dari `mst_place.place_name`.

4. **`eksemplar_bukus` (Item / Fisik Buku)**
   - `buku_id` -> Relasi ke UUID tabel `bukus`.
   - `kode_eksemplar` -> Map dari `item.item_code` (Barcode SLiMS).
   - `status` -> Map ke enum ERP ('tersedia', 'dipinjam', 'rusak', 'hilang').
   - `kondisi_fisik` -> Default diset 'baik'.

## 3. Rencana Pembuatan Antarmuka (UI) di Panel Admin
Nantinya, kita akan menyediakan _Custom Page_ khusus di panel admin:
- **Nama Menu/Halaman:** `Import Dari SLiMS` (ImportSlims)
- **Lokasi URL:** `/admin-perpustakaan/import-slims`
- **Metode Sinkronisasi:**
  Daripada mengupload file SQL (.sql backup), lebih stabil dan cepat jika kita membuat koneksi database sementara (`DB_CONNECTION_SLIMS`) di `.env` yang langsung menunjuk ke database lokal SLiMS. Kemudian, kita melakukan `DB::transaction()` untuk membaca dari SLiMS dan melakukan `insert` ke ERP.

## 4. Langkah-Langkah Eksekusi (Jika Nanti Dijalankan)
1. **Konfigurasi Database:** Menambah `slims` connection di `config/database.php` dan `.env`.
2. **Pembuatan Filament Page:** Membuat komponen `ImportSlims` di `app/Filament/Perpustakaan/Pages/` yang berisi tombol trigger eksekusi.
3. **Pembuatan Service Migrasi:** Membuat `SlimsMigrationService.php` yang berisi logika _query_ dari SLiMS, penyesuaian format data (seperti UUID dan relasi), dan _bulk insert_ ke database ERP secara _soft-delete safe_.
4. **Testing:** Melakukan uji coba eksekusi (sebelumnya kita sudah melakukan backup DB: `absensi_barcode_backup_before_slims.sql` sebagai pengaman).

---
*Catatan: Dokumen ini disimpan sebagai referensi agar saat Anda siap mengeksekusi migrasi, seluruh rencana, arsitektur, dan pemetaannya sudah terdokumentasi dengan rapi.*
