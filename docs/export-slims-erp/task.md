# Task & Progres: Fitur Import SLiMS ke ERP

> **Cara baca dokumen ini:**
> - `[ ]` = Belum dikerjakan
> - `[/]` = Sedang dikerjakan
> - `[x]` = Selesai
>
> **Jika token habis / sesi terputus:** Baca dokumen ini + `implementation-plan.md` + `mapping-data-slims-erp.md` + `slims-database-reference.md` sebelum melanjutkan. Tidak perlu scan ulang database SLiMS.

---

## File yang Akan Dibuat

### Layer 1: Service (Business Logic)
- `[x]` `app/Services/SlimsConnectionService.php` ✅ Selesai
- `[x]` `app/Services/SlimsMigrationService.php` ✅ Selesai
- `[ ]` `app/Services/SlimsExportService.php` ← digabung langsung ke Filament Page

### Layer 2: Export (Maatwebsite Excel)
- `[x]` `app/Exports/SlimsDdcExport.php` ✅ Selesai
- `[x]` `app/Exports/SlimsBukuExport.php` ✅ Selesai
- `[x]` `app/Exports/SlimsEksemplarExport.php` ✅ Selesai

### Layer 3: Filament Page (UI)
- `[x]` `app/Filament/Perpustakaan/Pages/ImportSlims.php` ✅ Selesai
- `[x]` `resources/views/filament/perpustakaan/pages/import-slims.blade.php` ✅ Selesai

### Layer 4: Route Export (Download XLS/CSV)
- `[ ]` Tambah route di `routes/web.php` untuk download XLS/CSV ← BELUM (opsional, download sudah handle di Filament Page)

---

## Progres Detail

### `SlimsConnectionService.php` ✅ SELESAI
- `[x]` Method `testConnection(array $config): true|string`
- `[x]` Method `getConnection(): \Illuminate\Database\Connection`
- `[x]` Method `getStats(): array`
- `[x]` Method `forgetConnection(): void`

### `SlimsMigrationService.php` ✅ SELESAI
- `[x]` Method `importDdc(): array`
- `[x]` Method `importBuku(): array`
- `[x]` Method `importEksemplar(): array` (termasuk auto-create `inventaris_bukus`)
- `[x]` Method `importSemua(): array`

### Export Classes ✅ SELESAI
- `[x]` `SlimsDdcExport.php` — export DDC ke XLS
- `[x]` `SlimsBukuExport.php` — export katalog buku ke XLS
- `[x]` `SlimsEksemplarExport.php` — export eksemplar ke XLS

### `ImportSlims.php` (Filament Page) ✅ SELESAI
- `[x]` Step 1: Form koneksi + action `testKoneksi()`
- `[x]` Step 2: Statistik SLiMS + tombol-tombol aksi
- `[x]` Action `importDdc()` dengan modal konfirmasi + warning
- `[x]` Action `importBuku()` dengan modal konfirmasi + warning
- `[x]` Action `importEksemplar()` dengan modal konfirmasi + warning
- `[x]` Action `importSemua()` dengan modal konfirmasi + warning
- `[x]` Action `downloadDdcXls()`, `downloadBukuXls()`, `downloadEksemplarXls()`
- `[x]` Action `putusKoneksi()`
- `[x]` Register ke panel (NavigationGroup, sort)

### `import-slims.blade.php` (View) ✅ SELESAI
- `[x]` Card Step 1: Form koneksi DB
- `[x]` Card Step 2: Badge terhubung + statistik
- `[x]` Grid tombol aksi Import (DDC, Buku, Eksemplar, Semua)
- `[x]` Grid tombol Download XLS/CSV
- `[x]` Warning banner merah
- `[x]` Panel laporan hasil import terakhir
- `[x]` Modal konfirmasi per tombol import

### `routes/web.php` ← Tidak Diperlukan
- `[x]` Download XLS sudah di-handle langsung dari Filament Page (`wire:click="downloadXxx"`)

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
