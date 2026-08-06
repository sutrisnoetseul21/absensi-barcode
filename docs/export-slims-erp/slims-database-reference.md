# Referensi Database SLiMS (`perpus_db_perpus`)

> **Tujuan dokumen ini:** Agar developer tidak perlu melakukan `DESCRIBE` ulang ke database SLiMS. Semua struktur tabel, data, dan relasi yang dibutuhkan untuk proses import sudah didokumentasikan di sini.
>
> **Kredensial SLiMS (lokal):**
> - Host: `127.0.0.1`
> - Port: `3306`
> - Database: `perpus_db_perpus`
> - Username: `perpus_user`
> - Password: `L@7qeQg+FyBze?Xc`
>
> _(Ditemukan dari file `perpus-slims/sysconfig.local.inc.php`)_

---

## Statistik Data (per 2026-08-06)

| Tabel SLiMS | Jumlah Record | Keterangan |
|---|---|---|
| `biblio` | **2.829** | Katalog judul buku |
| `item` | **33.188** | Eksemplar/fisik buku |
| `mst_topic` | **935** | Klasifikasi DDC |
| `mst_publisher` | **591** | Master penerbit |
| `mst_author` | **2.100** | Master pengarang |
| `mst_coll_type` | **4** | Jenis koleksi |

**Catatan penting dari hasil analisis:**
- `biblio` tanpa ISBN: **452** → duplikat deteksi pakai `judul + penerbit`
- `item` tanpa `item_code`: **0** → semua item punya kode (aman untuk cetak barcode)
- `item` tanpa `received_date`: **31.603** → mayoritas NULL, gunakan tanggal hari ini sebagai default
- `item_status_id`: **31.603 NULL** + **1.585 status '0'** → semua dianggap `tersedia`

---

## Struktur Tabel yang Digunakan

### 1. `biblio` — Katalog Buku Utama

```sql
biblio_id       INT, PK, AUTO_INCREMENT
title           TEXT, NOT NULL          ← judul buku
isbn_issn       VARCHAR(20), NULLABLE   ← ISBN (452 record NULL/kosong)
publish_year    VARCHAR(20), NULLABLE   ← tahun terbit (string, bukan integer)
classification  VARCHAR(40), NULLABLE   ← nomor klasifikasi DDC (misal: "510", "813", "NONE")
publisher_id    INT, NULLABLE           ← FK → mst_publisher.publisher_id
-- kolom lain yang DIABAIKAN saat import:
-- gmd_id, sor, edition, collation, series_title, call_number, language_id,
-- source, publish_place_id, notes, image, file_att, opac_hide, promoted,
-- labels, frequency_id, spec_detail_info, input_date, last_update, uid
```

**Contoh data:**
```
biblio_id | title                            | isbn_issn     | publish_year | classification | publisher_name
3         | MATEMATIKA KELAS VII SEMESTER 1  | 9786022823520 | 2014         | 510            | KEMENDIKBUD
813       | [buku fiksi]                     | NULL          | ...          | 813            | ...
```

---

### 2. `item` — Eksemplar / Fisik Buku

```sql
item_id         INT, PK, AUTO_INCREMENT
biblio_id       INT, NULLABLE, FK → biblio.biblio_id
item_code       VARCHAR(20), UNIQUE, NULLABLE ← kode barcode eksemplar (TIDAK ADA yang NULL!)
inventory_code  VARCHAR(200), NULLABLE        ← nomor inventaris (banyak yang NULL)
coll_type_id    INT, NULLABLE, FK → mst_coll_type.coll_type_id
item_status_id  CHAR(3), NULLABLE, FK → mst_item_status.item_status_id
received_date   DATE, NULLABLE               ← tanggal terima (31.603 record NULL!)
price           INT, NULLABLE                ← harga beli
invoice         VARCHAR(20), NULLABLE
-- kolom lain yang DIABAIKAN:
-- call_number, supplier_id, order_no, location_id, order_date, site, source,
-- price_currency, invoice_date, input_date, last_update, uid
```

**Distribusi `coll_type_id` di tabel `item`:**
| coll_type_id | coll_type_name | Jumlah Item |
|---|---|---|
| 1 | Reference | 26.313 |
| 2 | Textbook | 5.856 |
| 3 | Fiction | 968 |
| 4 | Ensiklopedia | 51 |

**Distribusi `item_status_id` di tabel `item`:**
| item_status_id | Jumlah | Mapping ke ERP |
|---|---|---|
| NULL | 31.603 | → `tersedia` |
| '0' | 1.585 | → `tersedia` |
| 'R' (Repair) | 0 di data aktual | → `rusak` |
| 'NL' (No Loan) | 0 di data aktual | → `tersedia` |
| 'MIS' (Missing) | 0 di data aktual | → `hilang` |

---

### 3. `mst_coll_type` — Jenis Koleksi (untuk mapping kategori)

```sql
coll_type_id    INT, PK
coll_type_name  VARCHAR(30), UNIQUE
input_date      DATE
last_update     DATE
```

**Data lengkap:**
| coll_type_id | coll_type_name |
|---|---|
| 1 | Reference |
| 2 | Textbook |
| 3 | Fiction |
| 4 | Ensiklopedia |

---

### 4. `mst_topic` — Klasifikasi DDC

```sql
topic_id        INT, PK, AUTO_INCREMENT
topic           VARCHAR(50), NOT NULL    ← nama topik/subjek
topic_type      ENUM('t','g','n','tm','gr','oc')  ← t=topik utama
classification  VARCHAR(50)              ← nomor DDC (sering kosong di data ini!)
auth_list       VARCHAR(20), NULLABLE
input_date      DATE
last_update     DATE
```

**Catatan:** Field `classification` di `mst_topic` seringkali kosong. Nomor DDC aktual ada di `biblio.classification`. Yang di-import ke `klasifikasi_ddcs` ERP adalah `topic` sebagai `kategori`, bukan `classification`.

**Contoh data:**
```
topic_id | topic                      | topic_type | classification
1        | MATEMATIKA                 | t          | (kosong)
4        | BAHASA INDONESIA           | t          | (kosong)
```

---

### 5. `mst_author` — Master Pengarang

```sql
author_id       INT, PK, AUTO_INCREMENT
author_name     VARCHAR(100), NOT NULL  ← nama pengarang
authority_type  ENUM('p','o','c')       ← p=person, o=organization, c=conference
author_year     VARCHAR(20), NULLABLE
input_date      DATE
last_update     DATE
```

---

### 6. `biblio_author` — Relasi Buku ↔ Pengarang (Pivot)

```sql
biblio_id   INT, PK (composite)    ← FK → biblio.biblio_id
author_id   INT, PK (composite)    ← FK → mst_author.author_id
level       INT, NOT NULL, default 1   ← urutan pengarang (1=pengarang utama)
```

**Query untuk ambil pengarang (digabung jadi satu string):**
```sql
SELECT
    b.biblio_id,
    GROUP_CONCAT(a.author_name ORDER BY ba.level SEPARATOR ', ') AS penulis
FROM biblio b
LEFT JOIN biblio_author ba ON b.biblio_id = ba.biblio_id
LEFT JOIN mst_author a ON ba.author_id = a.author_id
GROUP BY b.biblio_id
```

---

### 7. `mst_publisher` — Master Penerbit

```sql
publisher_id    INT, PK, AUTO_INCREMENT
publisher_name  VARCHAR(100), UNIQUE, NOT NULL
input_date      DATE
last_update     DATE
```

---

### 8. `mst_item_status` — Status Eksemplar

```sql
item_status_id    CHAR(3), PK
item_status_name  VARCHAR(30), UNIQUE
rules             VARCHAR(255), NULLABLE
no_loan           SMALLINT, default 0
skip_stock_take   SMALLINT, default 0
```

**Data lengkap:**
| item_status_id | item_status_name | no_loan | Mapping ERP |
|---|---|---|---|
| 'R' | Repair | 1 | → `rusak` |
| 'NL' | No Loan | 1 | → `tersedia` |
| 'MIS' | Missing | 1 | → `hilang` |
| NULL | (tidak ada status) | - | → `tersedia` |
| '0' | (tidak ada di mst, tapi ada di data) | - | → `tersedia` |

---

## Query Utama untuk Import

### Query Import Buku (dari biblio)
```sql
SELECT
    b.biblio_id,
    b.title,
    b.isbn_issn,
    b.publish_year,
    b.classification,
    p.publisher_name,
    GROUP_CONCAT(a.author_name ORDER BY ba.level SEPARATOR ', ') AS penulis,
    -- coll_type diambil dari item pertama milik biblio ini
    (SELECT i2.coll_type_id FROM item i2 WHERE i2.biblio_id = b.biblio_id LIMIT 1) AS coll_type_id
FROM biblio b
LEFT JOIN mst_publisher p ON b.publisher_id = p.publisher_id
LEFT JOIN biblio_author ba ON b.biblio_id = ba.biblio_id
LEFT JOIN mst_author a ON ba.author_id = a.author_id
GROUP BY b.biblio_id, b.title, b.isbn_issn, b.publish_year, b.classification, p.publisher_name
ORDER BY b.biblio_id
```

### Query Import Eksemplar (dari item)
```sql
SELECT
    i.item_id,
    i.biblio_id,
    i.item_code,
    i.inventory_code,
    i.coll_type_id,
    i.item_status_id,
    i.received_date,
    i.price
FROM item i
WHERE i.item_code IS NOT NULL AND i.item_code != ''
ORDER BY i.biblio_id, i.item_id
```

### Query Import DDC (dari mst_topic)
```sql
SELECT
    topic_id,
    topic AS kategori,
    classification AS kode_ddc
FROM mst_topic
WHERE topic IS NOT NULL AND topic != ''
ORDER BY topic_id
```
> **Catatan:** Karena `classification` di `mst_topic` sering kosong, `kode_ddc` di ERP akan diisi `topic_id` (sebagai string unik) jika classification kosong.

---

## ERD Relasi Tabel SLiMS (yang digunakan)

```
mst_publisher ──── biblio ──── biblio_author ──── mst_author
                     │
                     │ (biblio_id)
                     ▼
                   item ──── mst_coll_type
                     │
                     └──── mst_item_status
                     
mst_topic (berdiri sendiri, tidak join ke biblio di query kita)
```
