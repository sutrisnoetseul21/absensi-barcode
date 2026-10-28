# Mapping Data: SLiMS → ERP

> Dokumen ini adalah **pemetaan lengkap** dari setiap field database SLiMS ke field database ERP.
> Baca dokumen ini sebelum coding agar tidak ada field yang salah dipetakan.

---

## 1. Mapping Kategori Buku: `mst_coll_type` → `kategori_bukus`

Kategori di ERP **tidak perlu dibuat baru**. Mapping dilakukan ke kategori yang sudah ada:

| `mst_coll_type.coll_type_id` | `mst_coll_type.coll_type_name` | → | `kategori_bukus.nama_kategori` | Strategi |
|---|---|---|---|---|
| 1 | Reference | → | **Referensi** | Cari by `nama_kategori = 'Referensi'` |
| 2 | Textbook | → | **Non Fiksi** | Cari by `nama_kategori = 'Non Fiksi'` |
| 3 | Fiction | → | **Fiksi** | Cari by `nama_kategori = 'Fiksi'` |
| 4 | Ensiklopedia | → | **Referensi** | Sama dengan Reference |
| NULL | (tidak ada) | → | **Non Fiksi** | Default fallback |

**Implementasi di Service:**
```php
private function mapKategoriId(int|null $collTypeId, array $kategoriMap): string
{
    return match($collTypeId) {
        1, 4    => $kategoriMap['Referensi'],
        3       => $kategoriMap['Fiksi'],
        default => $kategoriMap['Non Fiksi'],  // 2 (Textbook) dan NULL
    };
}
```

---

## 2. Mapping Status Eksemplar: `item.item_status_id` → `eksemplar_bukus.status`

| `item_status_id` (SLiMS) | Kondisi | → | `status` (ERP enum) |
|---|---|---|---|
| `NULL` | Tidak ada status | → | `tersedia` |
| `'0'` | Status lama (tidak dipakai di mst) | → | `tersedia` |
| `'R'` | Repair / Sedang diperbaiki | → | `rusak` |
| `'NL'` | No Loan / Tidak bisa dipinjam | → | `tersedia` |
| `'MIS'` | Missing / Hilang | → | `hilang` |

**Catatan:** Di data SLiMS ini, 31.603 item status NULL dan 1.585 status '0'. Tidak ada yang 'R', 'NL', atau 'MIS'. Semua dianggap `tersedia`.

---

## 3. Mapping Tabel Lengkap

### 3a. `biblio` → `bukus`

| Field SLiMS | Tabel SLiMS | → | Field ERP | Tabel ERP | Transformasi |
|---|---|---|---|---|---|
| `biblio_id` | `biblio` | → | _(tidak diimport)_ | - | Disimpan sementara di memory untuk mapping eksemplar |
| `title` | `biblio` | → | `judul` | `bukus` | Langsung |
| `isbn_issn` | `biblio` | → | `isbn` | `bukus` | Langsung, nullable |
| `publish_year` | `biblio` | → | `tahun_terbit` | `bukus` | Cast ke integer, nullable |
| `classification` | `biblio` | → | `lokasi_rak` | `bukus` | Langsung (nomor DDC sebagai lokasi rak), "NONE" → NULL |
| `publisher_name` | `mst_publisher` | → | `penerbit` | `bukus` | Join via `publisher_id` |
| `author_name` (gabungan) | `mst_author` + `biblio_author` | → | `penulis` | `bukus` | `GROUP_CONCAT(...SEPARATOR ', ')` |
| `coll_type_id` | `item` (item pertama) | → | `kategori_id` | `bukus` | Mapping via tabel di atas |
| _(tidak ada di SLiMS)_ | - | → | `mapel_id` | `bukus` | `NULL` |
| _(tidak ada di SLiMS)_ | - | → | `grade_level` | `bukus` | `NULL` |
| _(tidak ada di SLiMS)_ | - | → | `sampul_buku` | `bukus` | `NULL` |

**Deteksi Duplikat (untuk overwrite):**
- Jika `isbn` tidak kosong: cek `bukus.isbn = biblio.isbn_issn`
- Jika `isbn` kosong: cek `bukus.judul = biblio.title AND bukus.penerbit = mst_publisher.publisher_name`

---

### 3b. `item` → `eksemplar_bukus`

| Field SLiMS | Tabel SLiMS | → | Field ERP | Tabel ERP | Transformasi |
|---|---|---|---|---|---|
| `item_code` | `item` | → | `kode_eksemplar` | `eksemplar_bukus` | Langsung, **WAJIB ADA** (tidak ada yang NULL) |
| `biblio_id` | `item` | → | `buku_id` | `eksemplar_bukus` | Lookup UUID dari `bukus` yang baru diimport |
| `item_status_id` | `item` | → | `status` | `eksemplar_bukus` | Mapping via tabel di atas |
| _(tidak ada di SLiMS)_ | - | → | `kondisi_fisik` | `eksemplar_bukus` | Default: `'baik'` |
| `inventory_code` | `item` | → | `inventaris_buku_id` | `eksemplar_bukus` | Via relasi ke `inventaris_bukus` |

**Deteksi Duplikat (untuk overwrite):**
- Cek `eksemplar_bukus.kode_eksemplar = item.item_code`

---

### 3c. `item` (dikelompokkan) → `inventaris_bukus`

Satu record `inventaris_bukus` dibuat per `buku_id` (bukan per item):

| Sumber Data | → | Field ERP | Tabel ERP | Logika |
|---|---|---|---|---|
| `item.biblio_id` (lookup buku_id) | → | `buku_id` | `inventaris_bukus` | UUID buku yang baru diimport |
| `item.inventory_code` (item pertama per biblio) | → | `no_inventaris` | `inventaris_bukus` | Jika NULL → `"SLIMS-{biblio_id}"` |
| `item.received_date` (item pertama per biblio) | → | `tanggal_masuk` | `inventaris_bukus` | Jika NULL → `today()` |
| `AVG(item.price)` atau item pertama | → | `harga` | `inventaris_bukus` | Jika NULL → `0` |
| `COUNT(item.item_id)` per biblio | → | `jumlah_eksemplar` | `inventaris_bukus` | Total eksemplar yang berhasil diimport |
| _(fixed)_ | → | `asal` | `inventaris_bukus` | Default: `'pembelian'` |
| _(fixed)_ | → | `status` | `inventaris_bukus` | Default: `'aktif'` |

---

### 3d. `mst_topic` → `klasifikasi_ddcs`

| Field SLiMS | Tabel SLiMS | → | Field ERP | Tabel ERP | Transformasi |
|---|---|---|---|---|---|
| `topic` | `mst_topic` | → | `kategori` | `klasifikasi_ddcs` | Langsung |
| `classification` | `mst_topic` | → | `kode_ddc` | `klasifikasi_ddcs` | Jika kosong → pakai `"T{topic_id}"` sebagai fallback |

**Deteksi Duplikat (untuk overwrite/upsert):**
- Cek `klasifikasi_ddcs.kode_ddc = mst_topic.classification` (atau fallback)

---

## 4. Field yang Diabaikan (Tidak Diimport)

Berikut field SLiMS yang **sengaja diabaikan** dengan alasannya:

| Field SLiMS | Alasan Diabaikan |
|---|---|
| `biblio.image` | Path gambar lokal SLiMS, tidak bisa otomatis dicopy ke storage ERP |
| `biblio.notes` | Catatan pustakawan, tidak ada field di ERP |
| `biblio.spec_detail_info` | Detail teknis, tidak relevan |
| `biblio.gmd_id`, `sor`, `edition`, dll | Tidak ada mapping di ERP |
| `item.location_id` | Lokasi di SLiMS berbeda sistem dengan ERP |
| `item.supplier_id`, `order_no` | Tidak ada di ERP |
| `member` (semua tabel anggota) | Tidak bisa auto-mapping ke `students`/`teachers` ERP |
| `loans` (semua peminjaman) | Butuh mapping anggota dulu, terlalu kompleks |

---

## 5. Diagram Relasi Import

```
Database SLiMS (perpus_db_perpus)         Database ERP (projek-absensi-barcode)
─────────────────────────────────         ─────────────────────────────────────

mst_coll_type ─────────────────────────── ► kategori_bukus (lookup only, tidak insert)
                                                    │
mst_publisher ──┐                                   │
                ├─► biblio ──────────────────────── ► bukus (INSERT/UPDATE)
mst_author ─────┘    │                                      │
biblio_author ───────┘                                      │
                                                            │
                      item ─────────────────────── ► eksemplar_bukus (INSERT/UPDATE)
                        │                                   │
                        └──────────────────────── ► inventaris_bukus (INSERT/UPDATE, 1 per buku_id)

mst_topic ───────────────────────────────────────── ► klasifikasi_ddcs (INSERT/UPDATE)
```

---

## 6. Penanganan Overwrite

**Keputusan desain:** Semua data **di-overwrite** (bukan di-skip) jika ditemukan duplikat. Ini karena asumsi:
> Data ERP biasanya masih kosong/baru saat import dilakukan. SLiMS adalah sumber data utama.

**Peringatan sebelum eksekusi:**
- Sistem akan menampilkan dialog konfirmasi dengan warning jelas sebelum import dimulai.
- Laporan setelah import menampilkan: berhasil diinsert baru / berhasil di-overwrite / error.

**Implementasi di Service (pola upsert):**
```php
// Untuk bukus: updateOrInsert berdasarkan isbn (jika ada) atau judul+penerbit
DB::connection('erp')->table('bukus')->updateOrInsert(
    ['isbn' => $isbn],  // kondisi find
    [...$data, 'updated_at' => now()]  // data untuk update/insert
);
```
