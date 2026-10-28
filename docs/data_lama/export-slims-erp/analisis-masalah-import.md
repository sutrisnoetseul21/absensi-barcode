# Analisis Masalah Import SLiMS → ERP

> Dokumen ini ditulis berdasarkan investigasi langsung ke database SLiMS, kode sumber SLiMS (`perpus-slims/`),
> dan hasil import yang berjalan pada 2026-08-07.
> **Tidak ada perubahan kode selama penulisan dokumen ini.**

---

## Masalah 1: DDC Terisi "T1, T2, ..." Bukan "500, 420, ..."

### Latar Belakang
Di halaman Label Buku SLiMS, nomor Call Number yang tercetak adalah kode DDC standar seperti:
- `500 SRI b` (Ilmu Pengetahuan Alam)
- `420 IKA b` (Bahasa Inggris)
- `410 MAY c` (Bahasa Indonesia)

Di halaman ERP Klasifikasi DDC (`/admin-perpustakaan/klasifikasi-ddc`), yang muncul justru:
- `T1` -> MATEMATIKA
- `T2` -> OLAHRAGA

### Root Cause: Dua Tabel Berbeda untuk "DDC" di SLiMS

SLiMS memiliki DUA tempat penyimpanan data DDC yang berbeda:

#### Tabel 1: `mst_topic` (yang kita impor selama ini) --- SALAH
```
topic_id | topic                       | classification
1        | MATEMATIKA                  | (KOSONG)
2        | OLAHRAGA                    | (KOSONG)
4        | BAHASA INDONESIA            | (KOSONG)
```
- `mst_topic` adalah tabel SUBJEK/TOPIK buku, bukan nomor klasifikasi DDC.
- Kolom `classification` di `mst_topic` diisi opsional oleh pustakawan dan ternyata SELURUH 935 record kosong.
- Karena `classification` kosong, kode kita memakai fallback "T" + topic_id (T1, T2, ...) sebagai kode DDC. **Ini SALAH.**

#### Tabel 2: `biblio.classification` (yang seharusnya kita pakai) --- BENAR
```
biblio_id | title                              | classification
3         | MATEMATIKA KELAS VII               | 510
6         | BAHASA INDONESIA KELAS VII         | 410
8         | BAHASA INGGRIS KELAS VII           | 420
18        | ILMU PENGETAHUAN ALAM KELAS VII    | 500
10        | PENDIDIKAN KEWARGANEGARAAN KELAS VII| 323.607
```
- Kolom `biblio.classification` adalah nomor DDC aktual yang dipakai SLiMS untuk mencetak label buku.
- Ini yang tercetak di label: angka `500`, `420`, `410`, dll.
- Ada ~300+ nilai distinct, mencakup seluruh spektrum DDC (000-999).

### Kesimpulan Masalah 1
Kita mengimpor dari tabel yang SALAH. Tabel `mst_topic` berisi subjek/topik buku (semacam tag/label tematik), bukan nomor DDC. Nomor DDC sebenarnya ada di `biblio.classification`.

---

## Masalah 2: Eksemplar Banyak "Dilewati" (24.461 dari 33.188)

### Fakta dari Database SLiMS

| Fakta | Angka |
|---|---|
| Total item di SLiMS | 33.188 |
| Distinct biblio_id di tabel item | 2.827 |
| Total biblio di SLiMS | 2.829 |
| Cache slims_biblio_* tersimpan | 2.829 entri |
| Buku berhasil diimport ke ERP | 2.626 buku |
| Eksemplar berhasil diimport | 8.727 |
| Eksemplar dilewati | 24.461 |

### Distribusi Eksemplar per Biblio (Top 5 terbesar)

| biblio_id | Judul | Eksemplar |
|---|---|---|
| 549 | SENI RUPA UNTUK KELAS VII, VIII, IX BSE | 521 |
| 571 | TERAMPIL BERMUSIK BSE | 496 |
| 542 | BELAJAR IPA MEMBUKA CAKRAWALA ALAM SEKITAR VIII BSE | 231 |
| 6 | BAHASA INDONESIA KELAS VII | 223 |
| 8 | BAHASA INGGRIS KELAS VII | 217 |

### Root Cause: Cache Prefix Tidak Cocok

Kode pre-load saat ini:
```php
$cachePrefix = config('cache.prefix', 'laravel_cache') . 'slims_biblio_';
$cacheRows   = DB::table('cache')
    ->where('key', 'like', $cachePrefix . '%')
```

Masalah: Laravel menyimpan cache key di tabel dengan format: `{app_prefix}_{key}`. Prefix diambil dari `config('cache.prefix')`, yang diambil dari `config('app.name')` di `config/cache.php`. Jika nama aplikasi mengandung spasi atau karakter khusus, prefixnya berubah. Perlu dicek apakah query LIKE benar-benar menangkap semua row yang tersimpan.

Kemungkinan: hanya biblio_id kecil (yang pertama kali diproses dan tersimpan cache saat sesi lama) yang tertangkap, sedangkan biblio_id besar seperti 549, 571 (masing-masing 500+ eksemplar) tidak tertangkap sehingga seluruh eksemplarnya dilewati.

---

## Masalah 3: Kolom Eksemplar Kosong di Halaman Buku

Di `/admin-perpustakaan/buku`, jumlah eksemplar per buku tidak terisi walaupun ada eksemplar yang diimport.

### Root Cause
Dampak langsung dari Masalah 2. Mayoritas buku tidak mendapat eksemplar, sehingga `jumlah_eksemplar` di `inventaris_bukus` tetap 0 atau bahkan tidak ada record inventaris.

---

## Rencana Perbaikan (Belum Dikode - Perlu Persetujuan)

### Langkah 1: Perbaiki Import DDC

Hapus import dari `mst_topic`. Ganti dengan mengekstrak distinct `classification` dari tabel `biblio`:

```sql
SELECT DISTINCT classification AS kode_ddc
FROM biblio
WHERE classification IS NOT NULL
  AND classification != ''
  AND classification != 'NONE'
ORDER BY classification
```

Di ERP, `klasifikasi_ddcs` akan terisi kode DDC sungguhan seperti `500`, `420`, `510`, `813`, dll. Ini sesuai dengan label buku di SLiMS.

Untuk kolom `kategori` di ERP: karena SLiMS tidak memiliki nama kategori per nomor DDC, kita bisa mengisi otomatis berdasarkan range DDC (000=Umum, 100=Filsafat, 200=Agama, 300=IPS, 400=Bahasa, 500=Sains, 600=Teknologi, 700=Seni, 800=Sastra, 900=Geografi/Sejarah).

### Langkah 2: Perbaiki Mapping Biblio ke Buku (Tanpa Cache)

Tambah kolom `slims_biblio_id` (INT, nullable, index) di tabel `bukus` ERP. Saat `importBuku()` simpan nilai `biblio_id` asli dari SLiMS. Saat `importEksemplar()`, pre-load dari DB ERP langsung:

```php
$biblioToBukuId = DB::table('bukus')
    ->whereNotNull('slims_biblio_id')
    ->pluck('id', 'slims_biblio_id')
    ->toArray();
// Hasil: [biblio_id_slims => uuid_buku_erp]
```

Ini 100% akurat karena dibaca langsung dari DB ERP, tidak bergantung pada cache apapun.

Perlu: migrasi baru untuk tambah kolom `slims_biblio_id`.

### Langkah 3: Sinkronisasi `jumlah_eksemplar` di Inventaris

Setelah import eksemplar selesai, jalankan query rekap:
```sql
UPDATE inventaris_bukus iv
SET jumlah_eksemplar = (
    SELECT COUNT(*) FROM eksemplar_bukus eb WHERE eb.inventaris_buku_id = iv.id
)
```

---

## Status Checklist

| Masalah | Status |
|---|---|
| DDC terisi T1/T2 bukan 500/420 | BELUM diperbaiki |
| 24.461 eksemplar dilewati | BELUM diperbaiki |
| Kolom eksemplar kosong di halaman Buku | Dampak dari masalah no 2 |
| Error tahun_terbit out of range | SUDAH diperbaiki |
| Error Class Cache not found | SUDAH diperbaiki |
| 504 Gateway Timeout | SUDAH diperbaiki (background) |

---

*Dokumen ini harus dibaca sebelum melanjutkan coding perbaikan import.*
*Jangan mulai coding sebelum ada kesepakatan pendekatan solusi di atas.*
