# Tahap 11: Penyesuaian Klasifikasi Buku, Inventaris Buku & Refaktor Global Sequence

**Tanggal**: 30 Juli 2026

## Ringkasan Perubahan

Pada tahap ini dilakukan refactoring menyeluruh pada sistem penomoran barcode eksemplar, otomatisasi pencatatan inventaris pengadaan buku, penyesuaian nomenklatur dan URL slug, serta penerapan kebijakan *audit trail* ketat (hanya-baca & pembatalan terkontrol) pada menu Inventaris Buku.

---

### 1. Nomenklatur & Perbaikan URL Slug
- **URL Slug Buku**: Diubah dari `/admin-perpustakaan/bukus` menjadi `/admin-perpustakaan/buku` (`BukuResource::$slug = 'buku'`).
- **URL Slug Klasifikasi DDC**: Diubah dari `/admin-perpustakaan/klasifikasi-ddcs` menjadi `/admin-perpustakaan/klasifikasi-ddc` (`KlasifikasiDdcResource::$slug = 'klasifikasi-ddc'`).
- **Klasifikasi Buku (Kategori Buku)**: Nomenklatur label diubah dari "Kategori Buku" menjadi **Klasifikasi Buku**. Karena klasifikasi standar hanya berisi 3 kategori utama (Referensi, Fiksi, Non Fiksi), datanya di-generate melalui `KategoriBukuSeeder` dan dimasukkan ke `DatabaseSeeder`. Menu navigasinya disembunyikan dari sidebar (`$shouldRegisterNavigation = false`) karena sifatnya yang statis.

---

### 2. Refaktor Penomoran Barcode (Global Sequence)
- **Urutan Global**: Penomoran barcode eksemplar diubah dari yang sebelumnya di-reset per-prefix menjadi **Global Sequence** (nomor urut bersambung secara global di seluruh tabel `eksemplar_bukus` terlepas dari prefix yang digunakan).
  - Contoh: Input 20 buku PAI (`PAI00001` s/d `PAI00020`), lalu dilanjutkan input 10 buku TIK (`TIK00021` s/d `TIK00030`).
- **Pessimistic Locking**: `EksemplarBuku::generateKodeEksemplar()` dibungkus dalam `DB::transaction` dan menggunakan `lockForUpdate()` pada record eksemplar terbaru untuk menjamin tidak terjadi *race condition* (bentrok barcode) apabila ada dua request pengadaan simultan.

---

### 3. Arsitektur Relasi & Modul Inventaris Buku
- **Skema Tabel `inventaris_bukus`**: Memiliki kolom `id` (uuid), `buku_id`, `no_inventaris`, `tanggal_masuk`, `asal` (Pembelian, Hibah, Tukar, Terbitan Sendiri), `harga`, `jumlah_eksemplar`, `status` (aktif, dibatalkan), dan `alasan_pembatalan`.
- **Foreign Key di `eksemplar_bukus`**: Ditambahkan kolom `inventaris_buku_id` pada tabel `eksemplar_bukus` (via migration `add_inventaris_buku_id_to_eksemplar_bukus_table`) untuk menghubungkan setiap fisik buku ke *batch* pengadaannya.
- **Otomatisasi Log Pengadaan**: Saat pengguna membuat buku baru di `CreateBuku` atau melakukan *Generate Eksemplar Massal* di `EksemplarBukusRelationManager`, sistem otomatis meminta input `Asal Buku` & `Harga`, membuat 1 entri di `inventaris_bukus`, dan menautkan UUID inventaris tersebut ke seluruh eksemplar fisik yang dibuat.
- **Format No. Inventaris**: Menggunakan pola `{NoUrutAwal}/{KodeAsal}/{Tahun} - {NoUrutAkhir}/{KodeAsal}/{Tahun}` (Contoh: `00001/P/2026 - 00020/P/2026`).

---

### 4. Kebijakan Audit Trail & Kebijakan Hapus Inventaris
- **UI Read-Only**: `InventarisBukuResource` diatur murni **Read-Only** (`canCreate()` dan `canEdit()` mengembalikan `false`). Tombol Hapus (Single/Bulk Delete) dihapus dari antarmuka.
- **Action "Batalkan Entri"**: Pembatalan entri dilakukan lewat Action khusus yang mengubah `status` menjadi `dibatalkan` dan me-request `alasan_pembatalan` dari admin. Eksemplar fisik yang terhubung di-soft-delete.
- **Validasi Ketat Pembatalan**: Pembatalan akan **DITOLAK** jika ada minimal 1 eksemplar di *batch* tersebut yang:
  1. Berstatus selain `tersedia` (misal `dipinjam`, `hilang`, `rusak`).
  2. Memiliki riwayat peminjaman historis (`orWhereHas('peminjamans')`), demi menjaga integritas data transaksi peminjaman di masa lalu.
- **Event-Driven Decrement**: Pengurangan/penambahan `jumlah_eksemplar` pada tabel inventaris di-handle secara terpusat pada Model Event `deleted` & `restored` di `EksemplarBuku.php`.

---

### 5. Otorisasi Access Override untuk Super Admin
- Menambahkan `Gate::before()` pada `AppServiceProvider.php` agar user dengan flag `is_super_admin = true` secara otomatis di-bypass untuk semua pemeriksaan Policy resource Filament.
