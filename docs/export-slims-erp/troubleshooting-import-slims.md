# Troubleshooting Migrasi Data SLiMS ke ERP

Dokumen ini mencatat berbagai kendala yang ditemukan selama proses pengembangan dan uji coba import data dari SLiMS ke sistem perpustakaan ERP, beserta solusi yang telah diimplementasikan.

---

## 1. Timeout `504 Gateway Time-out` (Nginx/PHP-FPM)

**Masalah:** 
Saat melakukan "Import Semua" (~3.000 buku dan ~33.000 eksemplar), koneksi di sisi browser terputus dan menampilkan `504 Gateway Time-out`.

**Analisis:** 
Proses migrasi mengelola data yang masif dalam satu *request* HTTP. Nginx dan PHP-FPM memiliki batas default waktu eksekusi skrip, biasanya 30-60 detik. Karena proses migrasi membutuhkan waktu lebih lama (bisa memakan waktu beberapa menit), koneksi dibatalkan secara sepihak oleh Nginx/FPM sebelum respon selesai dikembalikan.

**Solusi yang Diterapkan:**
- Memodifikasi konfigurasi pool PHP-FPM (`/etc/php/8.4/fpm/pool.d/www.conf`) untuk menambahkan / menyesuaikan:
  ```ini
  request_terminate_timeout = 1800
  ```
- Memodifikasi konfigurasi Nginx *server block* (`/etc/nginx/sites-enabled/absensi-barcode`) pada *location block* `~ \.php$` dengan menambahkan parameter:
  ```nginx
  fastcgi_read_timeout 1800;
  fastcgi_send_timeout 1800;
  ```
- Di sisi kode Laravel `SlimsMigrationService`, menambahkan simpanan *progress* sementara menggunakan `Cache::put('slims_last_report', ...)` setiap kali perulangan `chunk` selesai. Sehingga meskipun Nginx/browser terputus (karena limit yang tidak terduga), proses *background* PHP tetap menyimpan *report* yang bisa diakses pengguna.

---

## 2. Ribuan Eksemplar Berstatus "Dilewati" (Skipped) secara Masif

**Masalah:** 
Dari total ~33.184 item eksemplar di database SLiMS, hanya sekitar 8.727 item yang berhasil diproses, dan sisanya sebanyak **24.461 item "Dilewati" (Skipped)**.

**Analisis:**
1. Logika awal di dalam closure perulangan `chunk` eksemplar, metode memanggil fallback koneksi ke database SLiMS: `$slims = $this->slimsConn->getConnection();`.
2. Pemanggilan `getConnection()` ternyata mengeksekusi `DB::purge('slims_dynamic')` secara internal untuk melakukan set koneksi baru.
3. Karena pemanggilan `purge()` dilakukan **di tengah-tengah iterasi `chunk` yang sedang aktif (open cursor)**, hal ini membuat koneksi aktif PDO untuk SLiMS tertutup atau rusak. Akibatnya, Laravel gagal me-*load* halaman/chunk berikutnya secara diam-diam.
4. Item yang tidak ter-*load* otomatis dianggap tidak ada, yang kemudian mengacaukan perhitungan.

**Solusi yang Diterapkan & Status Saat Ini:**
- **Status: BELUM TUNTAS (Unresolved).** 
- Meskipun kita telah memindahkan logika pembacaan ke array *in-memory* (`$biblioToBukuId`) dan meniadakan fungsi `DB::purge()` di dalam *chunk*, hasil *import* akhir **tetap persis sama**: 
  - Baru: 8.727
  - Dilewati: 24.461
- **Agenda Debugging Selanjutnya (Untuk Besok):**
  1. Apakah angka 24.461 ini terjadi karena `$bukuId = $biblioToBukuId[$item->biblio_id]` menghasilkan `NULL`?
  2. Jika ya, mengapa `NULL`? Apakah puluhan ribu item tersebut mengacu pada `biblio_id` yang memang tidak ada/terhapus di tabel `biblio` SLiMS (*orphan data* masif)?
  3. Ataukah proses penyimpanan cache di `importBuku` gagal menyimpan sebagian `biblio_id` karena limitasi cache, memori, atau salah pembacaan UUID?

---

## 3. Error `Integrity constraint violation: 1452 Cannot add or update a child row (Foreign Key)`

**Masalah:** 
Setelah mengimplementasi *Pre-loading Mapping* dari Cache (solusi no 2), tiba-tiba terjadi error masif saat import "Eksemplar" di mana sistem menolak membuat `inventaris_bukus`. Error berbunyi:
`a foreign key constraint fails (absensi_barcode.inventaris_bukus, CONSTRAINT inventaris_bukus_buku_id_foreign FOREIGN KEY (buku_id) REFERENCES bukus (id) ON DELETE CASCADE)`

**Analisis:**
1. Error ini terjadi saat database ERP sengaja dikosongkan (di-*truncate*) sebelum percobaan import ulang.
2. Saat *truncate*, tabel `bukus` dan `inventaris_bukus` menjadi bersih.
3. Namun, **Laravel Cache lama tidak terhapus sempurna** (misalnya akibat ketidakcocokan query MySQL `LIKE '%slims_biblio_%'` dengan prefix *cache driver* sesungguhnya).
4. Akibatnya, pada iterasi berikutnya, script membaca *mapping cache lama* yang memberikan `buku_id` yang sebenarnya sudah dihapus (tidak valid). Saat skrip mencoba melakukan `InventarisBuku::create()` menggunakan UUID lama tersebut, MySQL langsung menolak karena Foreign Key ke tabel `bukus` tidak ditemukan.

**Solusi yang Diterapkan:**
- Menambahkan lapisan **Keamanan Validasi Silang (Cross-Validation)** pada kode sebelum *chunk* dimulai:
  ```php
  // Membuang nilai Cache (UUID) yang sebenarnya sudah tidak ada di tabel bukus
  $validBukuIds = Buku::withTrashed()
      ->whereIn('id', $uniqueBukuIds)
      ->pluck('id')
      ->flip()
      ->toArray();
      
  $biblioToBukuId = array_filter(
      $biblioToBukuId,
      fn($bukuId) => isset($validBukuIds[$bukuId])
  );
  ```
- Dengan kode di atas, sistem kebal terhadap *cache kotor*. Jika *cache* mengembalikan `buku_id` yang sudah terlanjur dihapus dari database, UUID tersebut otomatis ditendang dari memori sebelum iterasi `chunk` dimulai.

---

## 4. Error "Numeric value out of range" untuk Kolom Tahun Terbit

**Masalah:** 
Proses migrasi buku terhenti dan melempar *exception*:
`SQLSTATE[22003]: Numeric value out of range: 1264 Out of range value for column 'tahun_terbit'`

**Analisis:** 
Database SLiMS tipe data untuk kolom tahun biasanya bertipe *string* bebas, dan terkadang pustakawan salah input (misal memasukkan nomor ISBN panjang, atau teks ke kolom tahun). Sedangkan tabel `bukus` di sistem ERP menggunakan kolom tipe `INT`. Ketika dikonversi, angkanya menjadi *overflow*.

**Solusi yang Diterapkan:**
Menambahkan validasi *range* tahun pada `SlimsMigrationService`.
```php
$rawTahun = trim($biblio->publish_year ?? '');
$tahunTerbit = (is_numeric($rawTahun) && (int)$rawTahun >= 1000 && (int)$rawTahun <= 2099)
    ? (int) $rawTahun
    : null;
```
Data tahun yang tidak masuk akal / di luar kisaran (1000 - 2099) akan dianggap sebagai data kotor dan disimpan sebagai `NULL`, agar proses import tidak gagal secara keseluruhan.
