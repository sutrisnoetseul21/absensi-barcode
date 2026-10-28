# Implementation Plan: Import SLiMS via File XLS (v3)
# Status: TAHAP 1 Langkah 1 SELESAI — Menunggu Jawaban User

Terakhir diupdate: 2026-08-07

> **UNTUK AI AGENT BERIKUTNYA:** Baca dokumen ini LENGKAP sebelum melakukan apapun.
> Posisi saat ini: TAHAP 1 Langkah 1 sudah selesai (investigasi DDC).
> Menunggu jawaban user untuk 2 pertanyaan di bagian bawah dokumen ini.

---

## KONTEKS PROYEK

Refactor fitur import data SLiMS ke ERP sekolah (Laravel 12 + Filament v4).
**Pendekatan lama (DB-to-DB langsung) DIGANTI dengan file-based:**
Export XLS dari SLiMS → user review manual → Import XLS ke ERP.

**Masalah yang mendorong refactor:**
- 33.188 eksemplar selalu "dilewati" karena lookup slims_biblio_id tidak match
- Proses berjalan di Livewire (HTTP request), rawan timeout nginx 504
- Sulit di-debug

---

## KEPUTUSAN DESAIN YANG SUDAH FINAL (JANGAN DIUBAH TANPA KONFIRMASI)

1. **Format Eksemplar:** SATU file XLS, 2 sheet ("Buku" dan "Eksemplar"),
   terhubung lewat kolom `biblio_id` sebagai key. Jangan buat file terpisah.

2. **Kolom biblio_id** ada di KOLOM B pada sheet Buku (bukan kolom A/tersembunyi).
   Kolom A tetap "No" untuk keterbacaan manual di Excel.

3. **Transformasi no_inventaris** (format /P/YEAR) dilakukan SAAT IMPORT,
   bukan saat export. File XLS merepresentasikan data mentah SLiMS apa adanya.

4. **Volume besar (33.188 eksemplar):** `SlimsBukuImport` WAJIB implement
   `ShouldQueue + WithChunkReading` (Laravel Excel), jalan sebagai queued job,
   BUKAN proses sinkron di Livewire request. Setelah job selesai, kirim
   Filament notification ke user (database notification, bukan blocking).

5. **Mapping biblio_id → buku UUID:** JANGAN simpan seluruh mapping ribuan buku
   di memory. Tambahkan kolom `slims_biblio_id` (indexed) di tabel `bukus`,
   lalu saat proses tiap chunk eksemplar, query lookup UUID buku langsung
   by `slims_biblio_id` per baris/batch — jangan preload semua ke array.

6. **Header row:** Gunakan `WithHeadingRow` dari Laravel Excel dan cocokkan
   berdasarkan **nama kolom** (misal "Kode DDC"), bukan posisi baris tetap.

7. **Strategi upsert (WAJIB):**
   - `bukus`: upsert by `slims_biblio_id` (kolom baru, unique+indexed)
   - `eksemplar_bukus`: upsert by kombinasi `slims_biblio_id + item_code`
   - `klasifikasi_ddcs`: upsert by `kode_ddc`

8. **Mapping kode DDC → nama kategori:** TIDAK ada tabel referensi DDC standar
   di database ERP. Mapping dilakukan di kode PHP via fungsi `getNamaDdc()`
   yang sudah ada di `SlimsMigrationService`. Fungsi ini HARUS di-extract
   ke class terpisah atau helper agar bisa digunakan di Import class tanpa
   ketergantungan ke SlimsMigrationService.

---

## ATURAN KERJA

- Kerjakan BERTAHAP sesuai urutan di bawah.
- Setelah tiap tahap selesai, STOP dan tunggu verifikasi sebelum lanjut.
- Jangan hapus/ubah `ImportSlims.php`, `ImportSlimsPreview`, `ImportSlimsProses`
  sampai Tahap 3 — biarkan sistem lama tetap jalan sebagai fallback.
- Kalau ada ambiguitas tidak tercakup di keputusan desain, TANYA user.

---

## FORMAT KOLOM XLS (SPESIFIKASI FINAL)

### File: `ddc-slims.xlsx`
| Kolom | Header | Isi |
|-------|--------|-----|
| A | No | Nomor urut |
| B | Kode DDC | Nilai `biblio.classification` dari SLiMS |
| C | Nama Kategori | Auto-mapped dari kode via `getNamaDdc()` |

### File: `katalog-buku-slims.xlsx` — Sheet 1: "Buku"
| Kolom | Header | Isi |
|-------|--------|-----|
| A | No | Nomor urut |
| B | biblio_id | ID asli dari SLiMS `biblio.biblio_id` (key linking) |
| C | Judul | `biblio.title` |
| D | ISBN | `biblio.isbn_issn` |
| E | Penulis | GROUP_CONCAT dari `mst_author` |
| F | Penerbit | `mst_publisher.publisher_name` |
| G | Tahun Terbit | `biblio.publish_year` |
| H | Klasifikasi DDC | `biblio.classification` |
| I | Jenis Koleksi | Textbook / Fiction / Reference / Ensiklopedia / - |

### File: `katalog-buku-slims.xlsx` — Sheet 2: "Eksemplar"
| Kolom | Header | Isi |
|-------|--------|-----|
| A | No | Nomor urut |
| B | biblio_id | ID SLiMS (key linking ke sheet Buku) |
| C | Kode Eksemplar | `item.item_code` |
| D | No Inventaris | `item.inventory_code` (raw, tanpa transformasi) |
| E | Tanggal Masuk | `item.received_date` |
| F | Asal | P=Pembelian, H=Hibah, T=Tukar, dll |
| G | Harga | `item.price` |
| H | Status | tersedia / rusak / hilang |

---

## URUTAN EKSEKUSI

### ✅ TAHAP 1 — DDC (low-risk, validasi pola dulu)

#### [x] Langkah 1: Investigasi tabel DDC — SELESAI
**Hasil investigasi:**
- `klasifikasi_ddcs` mempunyai 5 kolom: `id`, `kode_ddc` (UNIQUE), `kategori`, `created_at`, `updated_at`
- Saat ini berisi **598 record** — hasil import batch sebelumnya dari `biblio.classification` SLiMS
- Ini **BUKAN** tabel referensi DDC standar 000-999
- Ada **41 kode non-numerik** (kotor) seperti `SR 525 MIN g c.1`, `150 NOL a`, `5516`, `2 X 0`
- Mapping nama dilakukan via fungsi `getNamaDdc()` di `SlimsMigrationService`
- **Kolom `kode_ddc` sudah UNIQUE** — strategi upsert langsung bisa digunakan

**⚠️ MENUNGGU JAWABAN USER untuk 2 pertanyaan:**

**Q1: Kode DDC "kotor"** (`SR ...`, `150 NOL a`, `5516`, dll):
- **Opsi A:** Import semua apa adanya dari XLS (termasuk kode kotor)
- **Opsi B:** Filter — hanya terima kode yang cocok regex `^\d[\d.]+$`, kode lain masuk laporan "skipped"

**Q2: Data yang sudah ada di `klasifikasi_ddcs` (598 record):**
- **Opsi Truncate:** Hapus semua dulu, lalu import fresh dari XLS
- **Opsi Upsert:** Kode yang sama di-update, kode baru ditambahkan, kode lama TETAP ada

**Instruksi untuk AI agent setelah user menjawab:**
Catat jawaban user di sini, lalu lanjut ke Langkah 2.

---

#### [ ] Langkah 2: Update `SlimsDdcExport.php`
**Yang perlu diubah:**
- Sumber data: dari `mst_topic` → `biblio.classification` (distinct)
- Kolom output: `No`, `Kode DDC`, `Nama Kategori` (sesuai spesifikasi)
- Constructor: terima `SlimsConnectionService` bukan `Collection`
  (karena data diambil langsung dari SLiMS saat download)
- Header baris 1-3 tetap ada (judul laporan) tapi WithHeadingRow di Import
  akan cocokkan by nama kolom, bukan posisi

**File yang diubah:** `app/Exports/SlimsDdcExport.php`

---

#### [ ] Langkah 3: Buat `app/Imports/SlimsDdcImport.php`
**Requirement:**
- Implement `WithHeadingRow` — deteksi header by nama kolom
- Upsert ke `klasifikasi_ddcs` berdasarkan `kode_ddc`
- Track: baru / update / skipped / error
- TIDAK perlu ShouldQueue (file DDC kecil, ~600 baris)
- Nama kolom yang dikenali: `kode_ddc` atau `kode ddc` (case-insensitive)

---

#### [ ] Langkah 4: Tambah HeaderAction di `KlasifikasiDdcResource.php`
**Requirement:**
- Tombol "Import DDC dari XLS" di header tabel
- Modal upload file `.xlsx`
- Setelah upload, jalankan `SlimsDdcImport` sinkron
- Tampilkan notifikasi Filament: "Berhasil: X baru, Y update, Z skipped, W error"

---

#### [ ] Langkah 5: STOP — Test manual oleh user

---

### [ ] TAHAP 2 — Buku + Eksemplar (higher-risk, volume besar)

#### [ ] Langkah 1: Migration
Tambah kolom `slims_biblio_id` di tabel `bukus`:
```sql
ALTER TABLE bukus ADD COLUMN slims_biblio_id INT UNSIGNED NULL INDEX;
```
*Catatan: kolom ini mungkin sudah ada dari implementasi sebelumnya — CEK DULU!*

```bash
php artisan tinker --execute="echo Schema::hasColumn('bukus', 'slims_biblio_id') ? 'ADA' : 'TIDAK ADA';"
```

---

#### [ ] Langkah 2: Update `SlimsBukuExport.php`
- Sheet "Buku": kolom A=No, B=biblio_id, C=Judul, D=ISBN, E=Penulis,
  F=Penerbit, G=Tahun Terbit, H=Klasifikasi DDC, I=Jenis Koleksi
- Sheet baru "Eksemplar": kolom sesuai spesifikasi di atas
- Data eksemplar diambil dari `item` SLiMS via JOIN ke `biblio`

---

#### [ ] Langkah 3: Buat `app/Imports/SlimsBukuImport.php`
- Implement `ShouldQueue + WithChunkReading` (chunk size: 200)
- Proses sheet "Buku" dulu (upsert by `slims_biblio_id`)
- Proses sheet "Eksemplar" per chunk:
  - Kumpulkan `biblio_id` unik dalam chunk
  - Query `bukus` WHERE `slims_biblio_id` IN (...)
  - Upsert eksemplar by `slims_biblio_id + kode_eksemplar`
  - Auto-create `inventaris_bukus` jika belum ada per `buku_id`
  - Transformasi `no_inventaris` (format /P/YEAR) dilakukan di sini
- Setelah job selesai: kirim database notification ke user

---

#### [ ] Langkah 4: Tambah HeaderAction di `BukusTable.php`
- Tombol "Import Buku dari XLS"
- Modal upload file `.xlsx`
- Dispatch queued job `SlimsBukuImport`
- Tampilkan notifikasi "Import sedang diproses di background..."
- User akan dapat notifikasi Filament saat selesai

---

#### [ ] Langkah 5: STOP — Test manual dengan sample kecil

---

### [ ] TAHAP 3 — Redesign halaman panduan (setelah Tahap 1-2 terbukti jalan)

#### [ ] Langkah 1: Redesign `ImportSlims.php`
- Hapus semua logika koneksi DB SLiMS langsung
- Jadikan halaman panduan 2 langkah:
  - Step 1: Download XLS (tetap butuh koneksi DB SLiMS untuk generate file)
  - Step 2: Link navigasi ke `/buku` dan `/klasifikasi-ddc` untuk import

#### [ ] Langkah 2: Update `import-slims.blade.php`

#### [ ] Langkah 3: Hapus `ImportSlimsPreview.php` dan `ImportSlimsProses.php`

#### [ ] Langkah 4: STOP — Review final

---

## FILE YANG AKAN DIUBAH/DIBUAT (Checklist Lengkap)

### TAHAP 1
- [ ] `app/Exports/SlimsDdcExport.php` — ubah sumber ke `biblio.classification`
- [ ] `app/Imports/SlimsDdcImport.php` — BARU
- [ ] `app/Filament/Perpustakaan/Resources/KlasifikasiDdcs/KlasifikasiDdcResource.php` — tambah HeaderAction

### TAHAP 2
- [ ] `database/migrations/xxxx_add_slims_biblio_id_to_bukus_table.php` — cek dulu apakah sudah ada
- [ ] `app/Exports/SlimsBukuExport.php` — tambah sheet Eksemplar + kolom biblio_id
- [ ] `app/Imports/SlimsBukuImport.php` — BARU (queued job)
- [ ] `app/Filament/Perpustakaan/Resources/Bukus/Tables/BukusTable.php` — tambah HeaderAction

### TAHAP 3
- [ ] `app/Filament/Perpustakaan/Pages/ImportSlims.php` — redesign
- [ ] `resources/views/filament/perpustakaan/pages/import-slims.blade.php` — update
- [ ] `app/Filament/Perpustakaan/Pages/ImportSlimsPreview.php` — HAPUS
- [ ] `app/Filament/Perpustakaan/Pages/ImportSlimsProses.php` — HAPUS

---

## REFERENSI FILE PENTING

- Fungsi `getNamaDdc()` ada di: `app/Services/SlimsMigrationService.php` (perlu di-extract)
- Export classes lama: `app/Exports/SlimsDdcExport.php`, `SlimsBukuExport.php`, `SlimsEksemplarExport.php`
- Tabel `klasifikasi_ddcs`: kolom `id` (UUID), `kode_ddc` (UNIQUE), `kategori`, timestamps
- Tabel `bukus`: cek apakah `slims_biblio_id` sudah ada atau belum

## INSTRUKSI UNTUK AI AGENT BARU

Jika sesi terputus, beri instruksi ini:

> "Baca SEMUA file di docs/export-slims-erp/, khususnya implementation-plan-xls.md.
> Lihat posisi checklist terakhir dan 2 pertanyaan yang menunggu jawaban user.
> Setelah user menjawab 2 pertanyaan tentang kode DDC kotor dan strategi data lama,
> lanjut ke TAHAP 1 Langkah 2: Update SlimsDdcExport.php."
