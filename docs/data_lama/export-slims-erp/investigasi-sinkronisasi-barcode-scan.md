# Investigasi Sinkronisasi Barcode Scan (SLiMS ↔ ERP)

Dokumen ini merangkum hasil investigasi mengenai masalah tidak ditemukannya barcode buku lama saat di-scan melalui aplikasi ERP (Sistem Presensi Berbasis Barcode).

## 1. Latar Belakang Masalah

Setelah proses export data dari SLiMS ke format XLS dan dilanjutkan dengan import ke dalam ERP (`projek-absensi-barcode`), ditemukan kendala terkait pencarian barcode eksemplar buku:
- **Buku BARU** (contoh: `PG31894`) yang baru diimport berhasil di-scan dan langsung terdeteksi/ketemu di dalam sistem ERP.
- Namun, barcode fisik pada **buku LAMA** (buku yang sudah lama didata dan ditempatkan di rak perpustakaan) saat di-scan lewat aplikasi ERP, menghasilkan error: `"Buku dengan kode barcode ... tidak ditemukan"`.

## 2. Hipotesis Awal (Sudah Terbukti SALAH)

**Dugaan Awal:**
SLiMS melakukan transformasi karakter (seperti spasi atau slash `/` menjadi underscore `_`, serta menghapus karakter khusus seperti `:`, `*`, `@`) saat men-generate barcode atau label cetak pada skrip `item_barcode_generator.php`. Karena transformasi ini, diduga barcode fisik yang tercetak pada label berbeda dengan string `item_code` murni di database.

**Fakta (Bantahan):**
Setelah dicek dengan contoh sampel nyata (`PG31894`), ternyata string kode di database SLiMS dan database ERP adalah **SAMA PERSIS**. Tidak ada transformasi karakter yang terjadi pada kasus ini, sehingga teori penggantian spasi ke underscore tidak berlaku untuk semua kasus, terutama buku-buku yang tidak memiliki spasi pada kodenya.

## 3. Investigasi Data Buku Lama (Hasil: Semua Match)

Investigasi data di level database (backend) dilakukan untuk buku-buku LAMA. Diambil 3 sampel buku lama dengan `item_id` terawal dari SLiMS: `MTK10598`, `BI10984`, `PKN11984`.

- **Perbandingan By-Eye dan HEX/Byte:** 
  Dilakukan komparasi antara kolom `item.item_code` di SLiMS dan kolom `eksemplar_bukus.kode_eksemplar` di ERP.
  Hasilnya **identik 100%**. Tidak ada perbedaan *length*, tidak ada *trailing space*, *non-breaking space*, maupun karakter tersembunyi lainnya.
- **Validasi Proses Import:** 
  Dicek jumlah baris pada tabel `eksemplar_bukus` di ERP, hasilnya mencapai **33.194** baris. Buku-buku lama tersebut secara positif **sudah ter-import** ke database ERP dan tidak berstatus `deleted_at`.
- **Analisis Fungsi Pencarian Scan ERP:** 
  Pada skrip `ProcessSirkulasiAction.php::processCheckBuku`, pencarian menggunakan metode _exact match_ via Eloquent (`where('kode_eksemplar', $barcodeBuku)`). Collation yang digunakan adalah `utf8mb4_unicode_ci` (bersifat _case-insensitive_). Selain itu, Frontend JS sudah melakukan `.trim()` sebelum nilai dikirim ke backend.
- **Analisis Proses Import ERP:** 
  Skrip import `SlimsEksemplarSheetImport.php` konsisten melakukan `trim()` pada semua *row*. Tidak ada perbedaan proses (algoritma import) antara buku lama dan buku baru (keduanya menggunakan *flow* yang sama).

**Kesimpulan Sementara:** 
Secara struktur data dan logika kode, semuanya konsisten dan seharusnya buku-buku lama tersebut **bisa ditemukan** ketika di-scan.

## 4. Dugaan Baru: Masalah di Fisik Label / Scanner (Belum Diverifikasi)

Karena aliran data, integritas database, dan kode pencarian terbukti tidak bermasalah, titik kecurigaan kini bergeser pada **fisik label buku atau alat scanner**:

- **a. Label Fisik Tidak Sinkron Sejak Awal:** 
  Ada kemungkinan label barcode fisik yang menempel pada buku lama adalah label dari sistem/skema penomoran LAMA (sebelum migrasi ke SLiMS versi saat ini). Akibatnya, barcode yang tercetak dan terbaca oleh scanner memang sudah berbeda dari `item_code` yang sekarang tercatat di database SLiMS.
- **b. Simbologi Barcode dan Start/Stop Character:** 
  Mungkin simbologi barcode lama (seperti *Code39*) menyebabkan scanner menambahkan *start/stop character* (contohnya menambahkan simbol `*` di awal/akhir) yang ikut terkirim sebagai input teks jika konfigurasi scanner kurang tepat.
- **c. Label Usang/Pudar (Misbaca):** 
  Label fisik sudah tua, tergores, atau pudar sehingga scanner salah membaca karakter yang mirip (contoh: angka `0` dibaca sebagai huruf `O`, angka `1` dibaca sebagai huruf `I`).

## 5. Langkah Verifikasi Lanjutan (Rencana untuk Besok)

Untuk membuktikan sumber masalah sebenarnya (khususnya dugaan 4a, 4b, 4c), akan dilakukan verifikasi manual besok dengan langkah-langkah:

1. Buka aplikasi **Notepad / Text Editor** (halaman kosong) di komputer yang tersambung dengan alat scanner barcode.
2. Lakukan **scan langsung** (secara fisik) pada salah satu buku LAMA yang gagal ditemukan di ERP tadi ke arah Notepad tersebut.
3. **Catat persis** apa teks string yang diketik otomatis oleh scanner. 
   - Apakah *exact match* dengan `item_code` di database?
   - Apakah ada tambahan karakter (prefix/suffix)?
   - Apakah ada salah pembacaan karakter (O vs 0)?
4. **Cocokkan** string hasil scan di Notepad dengan isi pesan *error* di layar aplikasi ERP untuk mengetahui secara pasti string *barcode* apa yang dikirim ERP ke backend.
5. Berdasarkan hasil pembuktian Notepad ini, barulah diputuskan apa *action plan* perbaikannya (apakah harus cetak ulang label barcode buku lama dari SLiMS, melakukan *re-config* pada hardware scanner, atau menerapkan filter *cleaning text* khusus di backend ERP).

## 6. Hasil Verifikasi Lapangan (Update Lanjutan)

Berdasarkan pengecekan fisik buku lama "Penelitian Tindakan Kelas" (kode `PTK15221`) dan cross-check dengan sistem ERP, berikut adalah temuan aktualnya:

**1. Keberadaan Data di Database ERP (Positif Ada)**
Query `SELECT id, buku_id, kode_eksemplar, HEX(kode_eksemplar), LENGTH(kode_eksemplar) FROM eksemplar_bukus WHERE kode_eksemplar = 'PTK15221'` menghasilkan:
- `id`: `49ae679a-bf41-4b9b-80ae-cf72bd66daba`
- `kode_eksemplar`: `PTK15221`
- `HEX`: `50544B3135323231` (Murni `PTK15221`)
- `LENGTH`: `8` karakter.
Tidak ada *hidden characters*, spasi, atau *newline* yang tersimpan di kolom database.

**2. Verifikasi Query Eloquent (Berhasil)**
Pengujian pencarian secara manual menggunakan query Eloquent yang persis sama dengan yang ada di `ProcessSirkulasiAction.php` via Tinker:
```php
$result = \App\Models\EksemplarBuku::where('kode_eksemplar', 'PTK15221')->first();
```
**Hasil:** Data `KETEMU` (`id: 49ae679a...`). Model Eloquent berhasil melakukan *exact match* tanpa terhalang *Global Scope* atau *SoftDeletes*.

**3. Penelusuran Log Aplikasi & Middleware**
- **Log Gagal Scan:** Tidak ditemukan *error log* atau *warning* pada `laravel.log` yang mencatat kegagalan *scan* untuk barcode `PTK15221`.
- **Middleware/Observer:** Tidak ada *middleware*, *event listener*, atau *observer* yang memanipulasi, menyaring, atau mengubah string input barcode (di luar `.trim()` pada *frontend* JS) sebelum pencarian dilakukan.

**4. Status Pengujian Buku `PTK15221`**
Buku dengan kode `PTK15221` merupakan kasus **BARU** yang diangkat pada sesi investigasi lapangan ini, dan sebelumnya **BELUM PERNAH DITES SCAN** ke dalam sistem ERP (baik menu presensi maupun sirkulasi). Karena data di sisi server 100% valid dan query Eloquent terbukti *match*, **diasumsikan kuat bahwa proses *scan* untuk buku ini akan BERHASIL** (asalkan teks yang ditembakkan scanner murni berupa `PTK15221`).

**Status Lanjutan:** Menunggu proses *scan test* secara langsung ke sistem ERP untuk buku `PTK15221` guna melihat respon final dari aplikasi (berhasil dikenali atau tidak).

## 7. Solusi Final & Perbaikan Sistem (Completed)

Berdasarkan laporan tambahan mengenai "Server Error" saat proses sirkulasi dan fitur pencarian barcode yang tidak berfungsi, telah dilakukan audit dan perbaikan dengan hasil berikut:

1. **Server Error Saat Scan Buku yang Sedang Dipinjam:** 
   - **Penyebab:** Terjadi _fatal error_ (500 Server Error) karena sistem memanggil _class_ lama `\App\Models\Student` dan `\App\Models\Teacher` di dalam `ProcessSirkulasiAction.php` untuk menampilkan pesan peringatan.
   - **Solusi:** _Class_ telah disesuaikan menjadi `\App\Models\Siswa` dan `\App\Models\Guru`. Kini saat men-scan buku yang dipinjam, sistem berhasil memunculkan pesan peringatan ramah pengguna: `"Buku ... sedang dipinjam oleh ..."`.

2. **Error Scan Buku PAI30417 di Sirkulasi Kiosk:**
   - **Penyebab:** Buku `PAI30417` ternyata masuk dalam kategori **Koleksi Referensi** yang tidak dapat dipinjam.
   - **Solusi:** Sistem sirkulasi kini memunculkan respons peringatan khusus berwarna kuning (_amber_) di layar scanner: `"⚠️ PAI30417 adalah Referensi yang tidak boleh dipinjam dan hanya dapat dibaca di tempat"`.

3. **Gagal Pencarian Barcode di Portal & Admin Panel:**
   - **Penyebab:** Kolom pencarian di halaman *frontend* (Livewire) dan tabel admin (Filament) awalnya hanya diatur untuk mencari teks pada field `judul`, `penulis`, dan `isbn`.
   - **Solusi:** Telah ditambahkan *Closure Custom Query* menggunakan `orWhereHas('eksemplarBukus')` agar fitur pencarian secara otomatis mendahulukan kecocokan penuh (*exact match*) pada `kode_eksemplar`. Kini, hasil *scan* barcode langsung memunculkan spesifik buku yang dicari dalam hitungan milidetik.

**Kesimpulan Akhir:** Masalah sinkronisasi barcode fisik LAMA bukan terletak pada datanya, melainkan pada kelemahan fungsi pencarian di antarmuka sistem dan *bug* dalam menampilkan peringatan peminjaman. Semua kode terkait sudah diperbaiki dan diperbarui.
