# Rencana Redesign Import SLiMS v2
# (Hasil Diskusi 2026-08-07 — Baca Ini Sebelum Coding)

> **PENTING:** Dokumen ini adalah hasil diskusi yang sudah disepakati.
> Jika sesi AI terputus, baca dokumen ini dulu sebelum lanjut coding.
> Jangan memulai coding tanpa membaca seluruh dokumen ini terlebih dahulu.

---

## Keputusan yang Sudah Disepakati

### 1. DDC: Mapping Otomatis dari Kode ke Nama

**Sumber DDC:** `biblio.classification` (BUKAN `mst_topic`)

Kode DDC di SLiMS tersimpan di kolom `biblio.classification` (contoh: `510`, `420`, `813`, `323.607`).
Tabel `mst_topic` berisi subjek/topik bebas yang pustakawan buat sendiri — bukan nomor DDC standar.
Seluruh 935 record di `mst_topic.classification` KOSONG, sehingga menghasilkan kode T1, T2, dll. yang tidak bermakna.

**Cara mengisi nama kategori DDC:**
Saat import, kita mapping otomatis berdasarkan 3 digit pertama angkanya menggunakan tabel DDC standar internasional:

```
000-099  = Karya Umum & Komputer
100-199  = Filsafat & Psikologi
200-299  = Agama
300-399  = Ilmu Sosial
400-499  = Bahasa & Linguistik
500-599  = Ilmu Pengetahuan Murni (Sains)
600-699  = Ilmu Terapan & Teknologi
700-799  = Kesenian & Olahraga
800-899  = Kesusastraan
900-999  = Sejarah, Geografi & Biografi
```

Sub-kelas yang ada di data SLiMS kita (contoh: `510`=Matematika, `420`=B.Inggris, `613.7`=Kesehatan)
juga akan dipetakan ke nama yang lebih spesifik jika memungkinkan.

**Query DDC dari SLiMS:**
```sql
SELECT DISTINCT classification AS kode_ddc
FROM biblio
WHERE classification IS NOT NULL
  AND classification != ''
  AND UPPER(classification) != 'NONE'
ORDER BY classification
```

---

### 2. Buku + Eksemplar Dijadikan Satu Proses

Buku dan Eksemplar TIDAK dipisah menjadi 2 tombol import berbeda.
Alasan: Eksemplar tanpa buku tidak berguna. Keduanya adalah satu kesatuan.

Satu method baru: `importBukuDanEksemplar()`
- Import buku dulu (semua 2.829 judul)
- Langsung lanjut import eksemplar di run yang SAMA
- Mapping biblio_id -> buku UUID disimpan ke memori PHP (array), bukan cache
- TIDAK bergantung pada Laravel Cache sama sekali

**FIX UTAMA - Hapus dependency Cache:**
Tambah kolom `slims_biblio_id` (INT, nullable, index) di tabel `bukus` ERP.
Saat import buku: simpan `biblio_id` SLiMS ke kolom ini.
Saat import eksemplar: baca mapping langsung dari tabel `bukus`:
```php
$biblioToBukuId = DB::table('bukus')
    ->whereNotNull('slims_biblio_id')
    ->pluck('id', 'slims_biblio_id')
    ->toArray();
// Hasilnya: [biblio_id_slims => uuid_buku_erp]
// 100% akurat, tidak bergantung cache apapun
```

---

### 3. Tombol Import: Halaman Baru (Bukan Popup/Modal)

**Alasan:** Popup/modal mengikat koneksi HTTP yang sama -> rawan 504 Gateway Timeout.
Halaman baru memiliki lifecycle HTTP sendiri dan lebih tahan timeout.

**Flow baru:**

```
/admin-perpustakaan/import-slims
    (Halaman Koneksi & Pilihan)
    
    1. Isi form koneksi DB SLiMS
    2. Klik "Tes Koneksi"  
    3. Muncul statistik ringkas SLiMS
    4. Pilih jenis import:
        [Preview & Import DDC Saja]
        [Preview & Import Buku + Eksemplar]   <-- UTAMA
        [Preview & Import Semua]              <-- TERBESAR
    
    Klik salah satu tombol --> membuka halaman baru:
    
/admin-perpustakaan/import-slims/preview/{ddc|buku|semua}
    (Halaman Preview)
    
    - Tampilkan statistik: jumlah buku, eksemplar, DDC yang akan diimport
    - Tampilkan tabel sample 10 buku pertama (preview)
    - Tampilkan warning OVERWRITE
    - Tombol [Batal] dan [Mulai Import Sekarang]
    
    Klik Mulai Import --> redirect ke:
    
/admin-perpustakaan/import-slims/proses/{ddc|buku|semua}
    (Halaman Proses & Progress)
    
    - Menjalankan import di server (set_time_limit = 3600)
    - Auto-refresh status setiap 5 detik via polling
    - Menampilkan progress bar visual + persen + angka
    - Tombol Refresh manual
    - Setelah selesai: tampilkan laporan lengkap
```

---

### 4. Progress Bar Visual

Di halaman proses import, tampilkan progress bar yang:
- Diupdate setiap kali 500 item selesai diproses (disimpan ke DB cache)
- Auto-refresh browser setiap 5 detik untuk ambil angka terbaru
- Ada tombol Refresh manual juga
- Format tampilan:

```
Import Buku:
[████████████░░░░░░░░░░░░] 1.800 / 2.829 (63%) — Estimasi sisa: ~2 menit

Import Eksemplar:
[████░░░░░░░░░░░░░░░░░░░░] 5.000 / 33.188 (15%) — Sedang memproses...

Error: 3 item (klik untuk detail)
```

Progress disimpan ke tabel `cache` dengan key `slims_import_progress` berisi:
```json
{
  "fase": "buku",
  "buku_selesai": 1800,
  "buku_total": 2829,
  "eksemplar_selesai": 0,
  "eksemplar_total": 33188,
  "error": 3,
  "mulai_pada": "2026-08-07 07:00:00"
}
```

---

### 5. Urutan Import Saat "Import Semua"

Urutan berjalan berurutan (sequential), satu per satu:
1. **DDC** -- dari `biblio.classification`, auto-mapping nama berdasarkan range angka
2. **Buku** -- dari `biblio` SLiMS, simpan `slims_biblio_id`
3. **Eksemplar** -- dari `item` SLiMS, join via `slims_biblio_id` (bukan cache)
4. **Rekap inventaris** -- update `jumlah_eksemplar` di `inventaris_bukus` dari COUNT aktual

Untuk "Import Buku + Eksemplar" (tanpa DDC):
- Jika DDC belum ada sama sekali: auto-import DDC dulu di background
- Jika DDC sudah ada: lewati, langsung import buku+eksemplar

Untuk "Import DDC Saja":
- Berguna untuk sekolah baru yang hanya perlu referensi DDC
- Bisa dieksport ke Excel juga

---

## File yang Perlu Dibuat/Diubah

### Migrasi Baru
- `add_slims_biblio_id_to_bukus_table.php` -- tambah kolom `slims_biblio_id` INT nullable index

### Service (Ubah)
- `SlimsMigrationService.php`:
  - `importDdc()` -- ganti sumber ke `biblio.classification`, tambah auto-mapping nama DDC
  - `importBuku()` -- tambah simpan `slims_biblio_id`
  - `importEksemplar()` -- ganti lookup via `DB::table('bukus')` bukan cache
  - `importBukuDanEksemplar()` -- method baru, jalankan buku+eksemplar sekaligus
  - `importSemua()` -- panggil DDC -> BukuDanEksemplar
  - `getProgress()` -- method baru, baca progress dari cache
  - `simpanProgress()` -- method baru, simpan progress ke cache

### Filament Page (Ubah Total)
- `ImportSlims.php` -- redesign sebagai halaman index + navigasi ke subhalaman
  - Tidak ada lagi popup/modal konfirmasi
  - Tombol membuka URL halaman baru

### Filament Page Baru
- `ImportSlimsPreview.php` -- halaman preview sebelum eksekusi
  - Parameter: jenis (ddc/buku/semua)
  - Tampilkan statistik dan sample data
  - Tombol "Mulai Import" yang submit ke halaman proses

- `ImportSlimsProses.php` -- halaman proses + progress bar
  - Auto-poll progress setiap 5 detik
  - Tampilkan progress bar visual
  - Tampilkan laporan setelah selesai

### Blade Views (Baru/Ubah)
- `import-slims.blade.php` -- diupdate (halaman koneksi+pilihan)
- `import-slims-preview.blade.php` -- BARU (halaman preview)
- `import-slims-proses.blade.php` -- BARU (halaman proses+progress)

---

## Mapping DDC Standar (untuk diimplementasikan di kode)

```php
// Di SlimsMigrationService::getNamaDdc($kode)
public static function getNamaDdc(string $kode): string
{
    $angka = (int) $kode;
    
    // Sub-kelas spesifik yang sering muncul di data kita
    $spesifik = [
        510 => 'Matematika',
        420 => 'Bahasa Inggris',
        413 => 'Kamus Bahasa Inggris',
        410 => 'Bahasa Indonesia',
        323 => 'Kewarganegaraan',
        500 => 'Ilmu Pengetahuan Alam',
        507 => 'Prakarya & Sains Terapan',
        707 => 'Kesenian & Seni Budaya',
        780 => 'Musik',
        613 => 'Kesehatan & Jasmani',
        813 => 'Fiksi (Novel Bahasa Inggris)',
        297 => 'Agama Islam',
        398 => 'Folklore & Cerita Rakyat',
        // dst...
    ];
    
    if (isset($spesifik[$angka])) {
        return $spesifik[$angka];
    }
    
    // Fallback ke kelas utama (100an)
    $kelas = intdiv($angka, 100) * 100;
    return match($kelas) {
        0   => 'Karya Umum & Komputer',
        100 => 'Filsafat & Psikologi',
        200 => 'Agama',
        300 => 'Ilmu Sosial',
        400 => 'Bahasa & Linguistik',
        500 => 'Ilmu Pengetahuan Murni',
        600 => 'Ilmu Terapan & Teknologi',
        700 => 'Kesenian & Olahraga',
        800 => 'Kesusastraan',
        900 => 'Sejarah, Geografi & Biografi',
        default => 'Lain-lain',
    };
}
```

---

## Status & Checklist Pekerjaan

### Pra-syarat (sebelum coding)
- [x] Analisis root cause selesai (lihat analisis-masalah-import.md)
- [x] Desain flow baru disepakati
- [x] Keputusan DDC, progress bar, dan urutan import disepakati

### Migrasi Database
- [ ] Buat migrasi tambah kolom `slims_biblio_id` di tabel `bukus`
- [ ] Jalankan migrasi

### Backend Service
- [ ] Perbaiki `importDdc()` -- sumber dari `biblio.classification` + auto-nama DDC
- [ ] Perbaiki `importBuku()` -- simpan `slims_biblio_id`, simpan progress ke cache
- [ ] Perbaiki `importEksemplar()` -- lookup via `DB::table('bukus')` bukan cache
- [ ] Buat `importBukuDanEksemplar()` -- gabungan buku+eksemplar sekaligus
- [ ] Perbaiki `importSemua()` -- panggil DDC -> BukuDanEksemplar
- [ ] Buat `getProgress()` dan `simpanProgress()`

### Frontend (Filament Pages)
- [ ] Redesign `ImportSlims.php` (halaman koneksi + pilihan tombol)
- [ ] Buat `ImportSlimsPreview.php` (halaman preview sebelum eksekusi)
- [ ] Buat `ImportSlimsProses.php` (halaman proses + progress bar visual)
- [ ] Update blade `import-slims.blade.php`
- [ ] Buat blade `import-slims-preview.blade.php`
- [ ] Buat blade `import-slims-proses.blade.php`

### Testing
- [ ] Test koneksi ke SLiMS
- [ ] Test import DDC -- verifikasi kode dan nama benar (500=Sains, 420=B.Inggris, dll)
- [ ] Test import Buku -- verifikasi `slims_biblio_id` tersimpan
- [ ] Test import Eksemplar -- verifikasi 33.188 eksemplar masuk semua (bukan hanya 8.727)
- [ ] Test progress bar -- verifikasi angka terupdate
- [ ] Test setelah selesai -- cek laporan error

### Cleanup
- [ ] Hapus data lama (TRUNCATE) sebelum final test
- [ ] Push ke GitHub
- [ ] Buat git tag baru

---

## Catatan Teknis Penting

1. **Nginx timeout:** Konfigurasikan `fastcgi_read_timeout 1800` di nginx config.
2. **PHP timeout:** Setiap method import wajib ada `set_time_limit(3600)`.
3. **Cache driver:** Menggunakan `database` (tabel `cache`). Bukan file.
4. **Kebijakan duplikat:** OVERWRITE (updateOrCreate/updateOrInsert) -- bukan skip.
5. **Kolom `slims_biblio_id`:** INT, nullable, index -- jangan unique karena bisa NULL banyak.
6. **Progress polling:** Gunakan wire:poll atau setInterval JavaScript sederhana, bukan WebSocket.

---

## Perintah Lanjut (Jika Token Habis di Tengah)

Jika sesi terputus, beri instruksi ini ke AI agent baru:

> "Lanjutkan redesign fitur import SLiMS di projek-absensi-barcode.
> Baca SEMUA file di docs/export-slims-erp/ terlebih dahulu, khususnya:
> - rencana-redesign-import-v2.md (dokumen utama rencana yang sudah disepakati)
> - analisis-masalah-import.md (root cause masalah yang sudah ditemukan)
> - slims-database-reference.md (referensi struktur DB SLiMS)
> Lanjutkan dari checklist yang masih [ ] di bagian 'Status & Checklist Pekerjaan'.
> Jangan scan ulang database SLiMS. Semua info ada di dokumen tersebut."
