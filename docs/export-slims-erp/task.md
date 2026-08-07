# Task & Progres: Fitur Import SLiMS ke ERP (Redesign v2)

> **Cara baca dokumen ini:**
> - `[ ]` = Belum dikerjakan
> - `[/]` = Sedang dikerjakan
> - `[x]` = Selesai

---

## File & Komponen Sistem

### Layer 0: Database & Migration
- `[x]` `database/migrations/2026_08_07_002254_add_slims_biblio_id_to_bukus_table.php` ✅ Selesai & Run

### Layer 1: Service (Business Logic v2)
- `[x]` `app/Services/SlimsConnectionService.php` ✅ Selesai
- `[x]` `app/Services/SlimsMigrationService.php` ✅ Selesai (v2: DDC dari biblio.classification, auto-name DDC, lookup slims_biblio_id, progress cache tracking)

### Layer 2: Export (Maatwebsite Excel)
- `[x]` `app/Exports/SlimsDdcExport.php` ✅ Selesai
- `[x]` `app/Exports/SlimsBukuExport.php` ✅ Selesai
- `[x]` `app/Exports/SlimsEksemplarExport.php` ✅ Selesai

### Layer 3: Filament Pages & Blade Views (Redesign v2 - No Modal/Popup)
- `[x]` `app/Filament/Perpustakaan/Pages/ImportSlims.php` ✅ Selesai (Main connection & selection page)
- `[x]` `app/Filament/Perpustakaan/Pages/ImportSlimsPreview.php` ✅ Selesai (Preview page before execution)
- `[x]` `app/Filament/Perpustakaan/Pages/ImportSlimsProses.php` ✅ Selesai (Execution page with progress bar & polling)
- `[x]` `resources/views/filament/perpustakaan/pages/import-slims.blade.php` ✅ Selesai
- `[x]` `resources/views/filament/perpustakaan/pages/import-slims-preview.blade.php` ✅ Selesai
- `[x]` `resources/views/filament/perpustakaan/pages/import-slims-proses.blade.php` ✅ Selesai

---

## Progres Detail v2

### 1. DDC dari `biblio.classification` ✅ SELESAI
- Disimpan di `klasifikasi_ddcs` dengan nama kategori otomatis berdasarkan DDC standar (500=Sains, 420=B.Inggris, dst).

### 2. Buku + Eksemplar Gabung ✅ SELESAI
- Disimpan dengan kolom `slims_biblio_id` di tabel `bukus` ERP.
- Eksemplar di-lookup via database `bukus` langsung (100% akurat, tanpa cache dependency).

### 3. Halaman Baru & Progress Bar ✅ SELESAI
- Navigasi berbasis URL (bukan modal/popup).
- Progress bar visual dengan auto-refresh 5 detik (`wire:poll`).

---

## Catatan Teknis Penting

### Koneksi Dinamis SLiMS
Koneksi dibuat runtime dari session, bukan dari `.env`:
```php
// Di SlimsConnectionService::getConnection()
config(['database.connections.slims_dynamic' => [
    'driver'   => 'mysql',
    'host'     => session('slims_config.host'),
    'port'     => session('slims_config.port'),
    'database' => session('slims_config.database'),
    'username' => session('slims_config.username'),
    'password' => session('slims_config.password'),
    'charset'  => 'utf8mb4',
    'collation'=> 'utf8mb4_unicode_ci',
]]);
DB::purge('slims_dynamic');
return DB::connection('slims_dynamic');
```

### Mapping Kategori (coll_type_id → kategori_bukus.id)
```php
match($collTypeId) {
    1, 4    => $kategoriMap['Referensi'],  // Reference & Ensiklopedia
    3       => $kategoriMap['Fiksi'],
    default => $kategoriMap['Non Fiksi'],  // Textbook (2) & NULL
}
```

### Mapping Status Item (item_status_id → eksemplar_bukus.status)
```php
match($statusId) {
    'R'     => 'rusak',
    'MIS'   => 'hilang',
    default => 'tersedia',  // NULL, '0', 'NL', dll
}
```

### Deteksi Duplikat Buku (untuk updateOrInsert)
- Jika `isbn_issn` tidak kosong → key: `isbn`
- Jika `isbn_issn` kosong → key: `judul` + `penerbit`

### Auto-create Inventaris
- 1 record `inventaris_bukus` per `buku_id`
- `updateOrCreate(['buku_id' => $bukuId], [...])`
- `no_inventaris` fallback: `"SLIMS-{biblio_id}"`
- `tanggal_masuk` fallback: `today()`
- `harga` fallback: `0`

---

## Perintah Lanjut (jika token habis di tengah)

Jika sesi terputus, beri instruksi ini ke AI agent baru:

> "Lanjutkan coding fitur import SLiMS di projek-absensi-barcode.
> Baca dulu semua file di `docs/export-slims-erp/` (task.md, implementation-plan.md, mapping-data-slims-erp.md, slims-database-reference.md).
> Lanjutkan dari task yang masih `[ ]` atau `[/]` di task.md.
> Jangan scan ulang database SLiMS kecuali diperlukan."
