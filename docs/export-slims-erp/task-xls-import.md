# Task Lanjutan: Import SLiMS via XLS (v3)
# Untuk AI Agent Berikutnya

> **BACA INI PERTAMA KALI sebelum melakukan apapun.**
> File ini adalah checkpoint progress pekerjaan yang sedang berjalan.
> Baca juga: `implementation-plan-xls.md` untuk detail keputusan desain.

---

## STATUS SAAT INI (terakhir update: 2026-08-07)

### ✅ SELESAI

| File | Keterangan |
|------|------------|
| `app/Support/DdcHelper.php` | **BARU** — Helper DDC mapping, extract dari SlimsMigrationService. |
| `app/Exports/SlimsDdcExport.php` | **DIUPDATE v2** — Sumber data diubah ke `biblio.classification`, kolom jadi 3 (No, Kode DDC, Nama Kategori), pakai `DdcHelper` |
| `app/Imports/SlimsDdcImport.php` | **BARU** — Logika import XLS untuk DDC dengan validasi kode dan pelaporan baris yang di-skip |
| `ManageKlasifikasiDdcs.php` | **DIUPDATE** — Tombol "Import DDC dari XLS" ditambahkan untuk mengganti fitur CSV lama. |
| `app/Exports/Sheets/SlimsBukuSheet.php` | **BARU** — Sheet khusus ekspor data Buku (TAHAP 2) |
| `app/Exports/Sheets/SlimsEksemplarSheet.php`| **BARU** — Sheet khusus ekspor data Eksemplar (TAHAP 2) |
| `app/Exports/SlimsBukuExport.php` | **DIUPDATE** — Menggabungkan 2 sheet di atas (TAHAP 2) |

### ⏸️ BERHENTI DI SINI — menunggu user test export Buku dulu

User harus **TANYA USER** apakah export Buku (Katalog Buku SLiMS) sudah sukses di-download dan hasilnya ada 2 sheet (Buku & Eksemplar) sebelum lanjut buat logika Import-nya.

---

## YANG HARUS DILANJUTKAN (urutan wajib diikuti)

### TAHAP 1 — DDC (Selesai ✅)

#### [x] Langkah 3: Buat `app/Imports/SlimsDdcImport.php`
#### [x] Langkah 4: Tambah HeaderAction di `KlasifikasiDdcResource.php` / `ManageKlasifikasiDdcs.php`
#### [x] Langkah 5: STOP — user test manual (Sukses)

---

### TAHAP 2 — Buku + Eksemplar

#### [x] Langkah 1: Cek dan tambah kolom `slims_biblio_id`

Sudah dicek, kolom `slims_biblio_id` sudah ada dan sudah ter-index, sehingga siap digunakan untuk `updateOrCreate` sebagai foreign reference dari SLiMS.

#### [x] Langkah 2: Update `app/Exports/SlimsBukuExport.php`
- [x] Sheet 1 "Buku": kolom A=No, B=biblio_id, C=Judul, D=ISBN, E=Penulis, F=Penerbit, G=Tahun Terbit, H=Klasifikasi DDC, I=Jenis Koleksi
- [x] Sheet 2 "Eksemplar": A=No, B=biblio_id, C=Kode Eksemplar, D=No Inventaris, E=Tanggal Masuk, F=Asal, G=Harga, H=Status

#### [ ] Langkah 2.5: STOP — User test Export Buku

**Cek dulu sebelum bikin migration:**
```bash
php artisan tinker --execute="echo Schema::hasColumn('bukus', 'slims_biblio_id') ? 'SUDAH ADA' : 'BELUM ADA';"
```

Dari investigasi sebelumnya: kolom **sudah ada** (`int unsigned`, nullable, MUL index).
Tapi belum UNIQUE — perlu cek apakah perlu di-tambah unique constraint atau tidak.

#### [ ] Langkah 2: Update `app/Exports/SlimsBukuExport.php`
- Sheet 1 "Buku": kolom A=No, B=biblio_id, C=Judul, D=ISBN, E=Penulis, F=Penerbit, G=Tahun Terbit, H=Klasifikasi DDC, I=Jenis Koleksi
- Sheet 2 "Eksemplar": A=No, B=biblio_id, C=Kode Eksemplar, D=No Inventaris, E=Tanggal Masuk, F=Asal, G=Harga, H=Status

#### [x] Langkah 3: Buat `app/Imports/SlimsBukuImport.php`
- `WithChunkReading` (chunk 500) via background job agar tidak RTO (Timeout).
- Proses sheet Buku: upsert by `slims_biblio_id`
- Proses sheet Eksemplar: lookup buku by `slims_biblio_id`, upsert by `kode_eksemplar`
- Database notification dikirim ke user via Job setelah selesai.

#### [x] Langkah 4: Tambah HeaderAction di `BukusTable.php` / `ListBukus.php`
- Tombol "Import Buku dari XLS" ditambahkan di halaman Koleksi Buku.
- [x] Tombol "Import Buku dari XLS" ditambahkan di halaman Koleksi Buku.
- [x] Mengirim tugas ke `queue` untuk dijalankan di background.

#### [x] Langkah 5: STOP — user test sample kecil

---

### TAHAP 3 — Redesign halaman Import-SLiMS (Selesai ✅)

#### [x] Redesign `ImportSlims.php` jadi panduan 2 langkah (download → link ke import)
#### [x] Hapus `ImportSlimsPreview.php` dan `ImportSlimsProses.php` beserta blade view-nya
#### [x] STOP — review final

---

## KEPUTUSAN DESAIN YANG SUDAH FINAL (ringkasan)

1. Format file: **1 file XLS, 2 sheet** (Buku + Eksemplar) — terhubung via `biblio_id`
2. `biblio_id` ada di **kolom B** sheet Buku (kolom A = No)
3. Transformasi `no_inventaris` dilakukan **saat import**, bukan saat export
4. Import Buku: wajib `ShouldQueue + WithChunkReading`
5. Mapping `biblio_id → UUID buku`: query per chunk via `slims_biblio_id`, bukan preload semua
6. Header row dikenali by **nama kolom** (`WithHeadingRow`), bukan posisi baris
7. Upsert: `bukus` by `slims_biblio_id`, `eksemplar_bukus` by `slims_biblio_id + kode_eksemplar`, `klasifikasi_ddcs` by `kode_ddc`
8. Mapping DDC: via `DdcHelper::getNamaKategori()` — JANGAN duplikat logic ini

---

## ATURAN WAJIB UNTUK AGENT BARU

- Jangan hapus `ImportSlims.php`, `ImportSlimsPreview.php`, `ImportSlimsProses.php` sampai Tahap 3
- Kerjakan 1 langkah → STOP → tunggu konfirmasi user sebelum lanjut
- Jika ada ambiguitas, **TANYA user** — jangan menebak
- Setiap file yang dibuat/diubah, tunjukkan diff sebelum lanjut

---

## REFERENSI FILE PENTING

| File | Keterangan |
|------|------------|
| `app/Support/DdcHelper.php` | Helper DDC (BARU) |
| `app/Exports/SlimsDdcExport.php` | Export DDC (sudah diupdate v2) |
| `app/Exports/SlimsBukuExport.php` | Export Buku (belum diubah) |
| `app/Services/SlimsMigrationService.php` | Service lama — `getNamaDdc()` ada di sini (sudah ada di DdcHelper, jangan duplikat) |
| `app/Filament/Perpustakaan/Resources/KlasifikasiDdcs/KlasifikasiDdcResource.php` | Tambah HeaderAction di sini |
| `app/Filament/Perpustakaan/Resources/Bukus/Tables/BukusTable.php` | Tambah HeaderAction di sini |
| `docs/export-slims-erp/implementation-plan-xls.md` | Plan lengkap dengan spesifikasi kolom XLS |
| `docs/export-slims-erp/mapping-data-slims-erp.md` | Mapping field SLiMS → ERP |

---

## KONDISI DATABASE SAAT INI

- `klasifikasi_ddcs`: 598 record (dari import batch sebelumnya, ada 41 kode dirty — biarkan)
- `bukus`: 0 record (sudah di-truncate)
- `eksemplar_bukus`: 0 record (sudah di-truncate)
- `inventaris_bukus`: 0 record (sudah di-truncate)
- Kolom `slims_biblio_id` di tabel `bukus`: **SUDAH ADA** (int unsigned, nullable, MUL index)
- FK `bukus.klasifikasi_ddc_id → klasifikasi_ddcs.id` ON DELETE SET NULL
