# Plan Implementasi: Portal Sinkronisasi SLiMS → ERP

> **Status:** FINAL — Siap Eksekusi
> **Terakhir diupdate:** 2026-08-06
> **Dibaca setelah:** `slims-database-reference.md` dan `mapping-data-slims-erp.md`

---

## Tujuan

Membuat halaman khusus di panel **Admin Perpustakaan** (`/admin-perpustakaan`) yang memungkinkan admin untuk mensinkronisasi data dari database SLiMS ke modul perpustakaan ERP. Tujuan akhir:
- Data buku bisa dikelola dan dilihat di ERP
- Eksemplar bisa **dicetak barcode**-nya
- **Inventaris** otomatis terbentuk dari data SLiMS
- Tidak ada instalasi atau konfigurasi `.env` tambahan

---

## Lokasi & Akses

| Item | Detail |
|---|---|
| **URL** | `/admin-perpustakaan/import-slims` |
| **Panel Filament** | `admin-perpustakaan` |
| **Akses** | Wajib login sebagai admin perpustakaan (guard panel bawaan) |
| **Koneksi SLiMS** | Form UI → disimpan di **Laravel Session** → tidak menyentuh `.env` |

---

## Alur Kerja UI

```
/admin-perpustakaan/import-slims
│
├─ STEP 1: Form Koneksi Database SLiMS
│   ├─ Host (default: 127.0.0.1)
│   ├─ Port (default: 3306)
│   ├─ Nama Database (contoh: perpus_db_perpus)
│   ├─ Username
│   └─ Password
│       └─ [Tombol: Tes Koneksi]
│           ├─ ✅ BERHASIL → Simpan ke session → Tampilkan STEP 2
│           └─ ❌ GAGAL   → Tampilkan pesan error MySQL
│
└─ STEP 2: Dashboard Import (hanya muncul setelah koneksi OK)
    ├─ Badge: "✅ Terhubung ke: perpus_db_perpus"
    ├─ Statistik SLiMS: 2.829 Judul | 33.188 Eksemplar | 935 Topik DDC
    │
    ├─ [Tombol] Import DDC       → mst_topic → klasifikasi_ddcs
    ├─ [Tombol] Import Buku      → biblio → bukus
    ├─ [Tombol] Import Eksemplar → item → eksemplar_bukus + inventaris_bukus
    └─ [Tombol] Import SEMUA     → jalankan DDC → Buku → Eksemplar berurutan
    
    Setiap tombol menampilkan:
    ├─ ⚠️ Modal konfirmasi dengan WARNING: "Data ERP akan di-OVERWRITE!"
    └─ Setelah selesai: Laporan (N baru diinsert / M di-overwrite / X error)
```

**Urutan import yang wajib diikuti:**
1. DDC (opsional, tapi disarankan)
2. Buku (wajib sebelum Eksemplar)
3. Eksemplar (wajib setelah Buku — karena butuh `buku_id`)

---

## File yang Dibuat

### Service Layer

#### `app/Services/SlimsConnectionService.php` [NEW]
```
- testConnection(array $config): true|string
  → Tes koneksi MySQL ke SLiMS, simpan ke session jika berhasil
- getConnection(): \Illuminate\Database\Connection
  → Return koneksi dinamis dari config session
- getStats(): array
  → Hitung jumlah biblio, item, topic di SLiMS
- forgetConnection(): void
  → Hapus config dari session (tombol "Putus Koneksi")
```

#### `app/Services/SlimsMigrationService.php` [NEW]
```
- importDdc(): array
  → mst_topic ke klasifikasi_ddcs
  → Upsert berdasarkan kode_ddc (overwrite jika sudah ada)
  → Return ['baru' => N, 'diupdate' => N, 'error' => N]

- importBuku(): array
  → biblio + join mst_publisher + GROUP_CONCAT mst_author → bukus
  → Upsert berdasarkan isbn (jika ada) atau judul+penerbit
  → Return ['baru' => N, 'diupdate' => N, 'error' => N]
  → Simpan mapping biblio_id → buku_uuid ke cache untuk dipakai importEksemplar()

- importEksemplar(): array
  → item → eksemplar_bukus (upsert berdasarkan kode_eksemplar)
  → Auto-create inventaris_bukus (1 per buku_id, updateOrCreate)
  → Return ['baru' => N, 'diupdate' => N, 'error' => N, 'inventaris_dibuat' => N]

- importSemua(): array
  → Jalankan importDdc() → importBuku() → importEksemplar() berurutan
  → Aggregasi semua laporan
```

**Semua operasi dalam `DB::transaction()` — rollback otomatis jika ada error.**

### Filament Page

#### `app/Filament/Perpustakaan/Pages/ImportSlims.php` [NEW]
```
- Property: $slimsConnected (bool) — cek dari session di mount()
- Property: $slimsStats (array) — statistik dari SLiMS
- Actions: testKoneksi(), putusKoneksi(), importDdc(), importBuku(), importEksemplar(), importSemua()
- Setiap action import: punya requiresConfirmation() + warning modal
- NavigationGroup: 'Pengaturan', NavigationSort: 99
```

#### `resources/views/filament/perpustakaan/pages/import-slims.blade.php` [NEW]
```
- Step 1: Card form koneksi (host, port, db, user, pass)
- Step 2: Stats badge, tombol-tombol aksi, laporan hasil terakhir
- Warning banner merah sebelum import: "⚠️ Data ERP akan di-OVERWRITE!"
```

---

## Penanganan Overwrite (Keputusan Final)

> **Semua data di-OVERWRITE, bukan di-skip.**

Alasan: Sistem ERP biasanya masih kosong saat import pertama dari SLiMS.

| Tabel ERP | Deteksi Duplikat | Aksi |
|---|---|---|
| `klasifikasi_ddcs` | `kode_ddc` sama | UPDATE |
| `bukus` | `isbn` sama (jika ada) atau `judul + penerbit` sama | UPDATE |
| `eksemplar_bukus` | `kode_eksemplar` sama | UPDATE |
| `inventaris_bukus` | `buku_id` sama | UPDATE |

**Peringatan ditampilkan di:**
1. Modal konfirmasi sebelum import dimulai (per tombol)
2. Banner warning merah permanen di halaman

---

## Referensi Dokumen Pendukung

- [`slims-database-reference.md`](./slims-database-reference.md) — Struktur tabel SLiMS, kredensial, dan query SQL
- [`mapping-data-slims-erp.md`](./mapping-data-slims-erp.md) — Pemetaan lengkap field per field
- [`perencanaan-migrasi.md`](./perencanaan-migrasi.md) — Dokumen perencanaan awal (arsip)

---

## Verifikasi Setelah Implementasi

1. Buka `/admin-perpustakaan/import-slims`
2. Isi form: host=`127.0.0.1`, db=`perpus_db_perpus`, user=`perpus_user`
3. Klik **Tes Koneksi** → pastikan statistik SLiMS muncul (2829 judul, 33188 eksemplar)
4. Klik **Import DDC** → konfirmasi warning → cek `klasifikasi_ddcs` bertambah ke ~935
5. Klik **Import Buku** → konfirmasi warning → cek `bukus` bertambah ke ~2829
6. Klik **Import Eksemplar** → konfirmasi warning → cek `eksemplar_bukus` ~33.188 + `inventaris_bukus` ~2829
7. Buka panel katalog buku → pilih 1 buku hasil import → klik **Cetak Barcode** → harus berhasil
8. Buka `/perpustakaan` → katalog publik harus menampilkan buku-buku dari SLiMS
