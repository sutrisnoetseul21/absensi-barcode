# Modul Perpustakaan ERP (Rangkuman Akhir)

Modul Perpustakaan ini dikembangkan secara komprehensif dalam kerangka Fase 2 ERP. Pengembangan dibagi menjadi 6 tahap iteratif (Tahap 0 hingga Tahap 5), dengan fokus pada relasi *polymorphic* sirkulasi yang kokoh, arsitektur UI/UX Kiosk modern yang diadaptasi dari Modul Presensi, dan implementasi efisiensi pencetakan barcode.

## Histori Tahapan
1. **[Tahap 0 (24 Juli 2026)]**: Perancangan struktur relasi antar entitas (Buku, Kategori, Eksemplar, Peminjaman) dan mendefinisikan alias Map Polymorphic.
2. **[Tahap 1 (24 Juli 2026)]**: Pembuatan migration, model, dan setup Filament untuk Katalog Buku, Kategori, serta Eksemplar (beserta RelationManager). Penegasan solusi terkait anomali *MorphMap* `Guru`.
3. **[Tahap 2 (25 Juli 2026)]**: Implementasi fitur *Generate Eksemplar Massal* di dalam *EksemplarBukusRelationManager*. Penanganan *Race Condition* menggunakan DB Transaction dan pelaporan error constraint duplikat via Notifikasi Filament.
4. **[Tahap 3 (26 Juli 2026)]**: Realisasi Cetak Barcode Eksemplar. Mengadopsi metode render browser murni via HTML+CSS Print (bukan DomPDF) demi ketajaman scan. Masalah URI Too Long dalam cetak bulk 200 buku diselesaikan menggunakan metode transit ID via Session Database.
5. **[Tahap 4 (26 Juli 2026)]**: Pembangunan UI Kiosk Sirkulasi menggunakan Alpine JS dan Livewire (dua state scan: Peminjam & Buku). Fitur relasi barcode ke model Guru lewat entitas `teacher_presensi_profiles`. Perbaikan bug Enum `status` Peminjaman dari 'selesai' ke `'dikembalikan'`.
6. **[Tahap 5 (26 Juli 2026)]**: Penyempurnaan akhir dengan Widget Statistik di halaman dashboard (mengalkulasi *buku terlambat* secara real-time tanpa *cronjob*), serta melengkapi tabel *Riwayat Peminjaman* dan penambahan jumlah eksemplar *Tersedia* secara agregasi performa tinggi (`withCount`).
7. **[Tahap 8 (27 Juli 2026)]**: Restrukturisasi navigasi sidebar panel Perpustakaan (grup Koleksi Buku, Sirkulasi, Laporan, Pengaturan). Pemisahan PeminjamanAktifResource dan RiwayatPengembalianResource. Tambah halaman Laporan Sirkulasi dan Pengaturan Perpustakaan.
8. **[Tahap 9 (28 Juli 2026)]**: Penyempurnaan UI halaman Anggota (kolom Kelas, kolom Pinjaman Aktif interaktif dengan pop-up modal, barcode copyable, filter baru). Revisi Cetak Kartu Siswa: judul diubah dari "KARTU PRESENSI" menjadi "KARTU SISWA", nilai NIS/NISN pada kartu mengikuti setting `barcode_scan_mode`, URL footer kartu dinamis dari env `SCHOOL_EMAIL_DOMAIN`.
9. **[Tahap 10 (29 Juli 2026)]**: Unifikasi UI cetak label spine dan barcode 100% mengikuti standar visual SLiMS (Grid 3x7 ukuran 6x3.5cm, garis tepi guard bar, spasi karakter). Refactor generate kode eksemplar menjadi format 5 digit terpisah per prefix (contoh `UMM00001`). Perbaikan bug formasi cetak massal berbasis eksemplar.
10. **[Tahap 11 (30 Juli 2026)]**: Penyesuaian nomenklatur dan URL slug (`/buku`, `/klasifikasi-ddc`, serta menyembunyikan navigasi Klasifikasi Buku yang statis 3 item dari seeder). Refactoring penomoran barcode eksemplar menjadi **Global Sequence** yang terlindungi *Pessimistic Locking* (`lockForUpdate()`). Implementasi penuh Modul **Inventaris Buku** (buku induk/audit trail) dengan Foreign Key `inventaris_buku_id` di `eksemplar_bukus`. Otomatisasi pencatatan batch penerimaan saat tambah buku / generate eksemplar. Panel Inventaris di-set *Read-Only* dengan fitur khusus **"Batalkan Entri"** (validasi status eksemplar & histori peminjaman, Soft Deletes, serta penyesuaian agregat `jumlah_eksemplar` berbasis Eloquent Event).
11. **[Tahap 12 (30 Juli 2026)]**: Unifikasi sirkulasi peminjaman (`/admin-perpustakaan/peminjaman`) dengan 3 Tab Navigasi (*Peminjaman Aktif*, *Dikembalikan*, *Semua Transaksi*) dan menyembunyikan menu terpisah Riwayat Pengembalian. Pengaktifan kembali transaksi peminjaman manual oleh Admin/Petugas lengkap dengan fitur scan barcode NISN/NIS/NIP & Kode Eksemplar. Pemulihan kaskade otomatis (*Cascade Restore*) saat entri inventaris atau buku dipulihkan dari Soft Delete. Penataan ulang struktur navigasi sidebar ke dalam 5 grup utama (*Koleksi Buku*, *Sirkulasi*, *Keanggotaan*, *Laporan*, *Pengaturan*).
12. **[Tahap 13 (30 Juli 2026)]**: Implementasi modul **Presensi Kunjungan Perpustakaan**. Pembuatan tabel & model `KunjunganPerpustakaan` (`kunjungan_perpustakaans`), Action `ProcessKunjunganAction`, Halaman Kiosk Scanner khusus Kunjungan Perpustakaan (`/perpustakaan/kunjungan`) berbasis barcode reader & kamera browser (Audio & visual feedback, log 10 pengunjung terbaru real-time), serta pendaftaran menu **Riwayat Presensi** di panel Admin Perpustakaan (`/admin-perpustakaan/riwayat-presensi`) lengkap dengan tombol pintas buka Kiosk dan filter tanggal/kelas.
13. **[Tahap 14 (30 Juli 2026)]**: Pembangunan **Portal Petugas Perpustakaan (`/portal-perpustakaan`)** mandiri berarsitektur konsisten dengan Portal Guru (`/portal-guru`). Menyediakan 5 modul operasional: *Dashboard*, *Katalog & Input Buku* (auto sequence barcode & inventaris batch), *Inventaris Buku*, *Sirkulasi Peminjaman/Pengembalian* (scan anggota & eksemplar), serta *Riwayat Presensi Kunjungan*. Dilengkapi middleware `auth.perpus` (`EnsureIsPetugasPerpustakaan`) dan sistem tema kustom.
14. **[Tahap 15 (31 Juli 2026)]**: Implementasi **Smart Multi-Book Draft Cart Sirkulasi**, penyempurnaan UI Kiosk tanpa overlay di Portal Perpustakaan (`/portal-perpustakaan/sirkulasi`), pembangunan menu baru **Data Peminjaman** (`/portal-perpustakaan/peminjaman`) dengan 3 tab navigasi (*Dipinjam*, *Terlambat*, *Riwayat*), integrasi tombol cetak Barcode & Label Spine di katalog buku, penyamaan nama sekolah dinamis pada cetak label (`PengaturanSekolah->school_name`), dan pemindahan Kartu Digital Siswa ke halaman Profil.

## Daftar Model & Tabel Inti
- `KategoriBuku` (`kategori_bukus`) - Klasifikasi standar (Referensi, Fiksi, Non Fiksi).
- `KlasifikasiDdc` (`klasifikasi_ddcs`) - Klasifikasi DDC perpustakaan.
- `Buku` (`bukus`) - Katalog buku/judul utama, memiliki opsional relasi ke mata pelajaran.
- `InventarisBuku` (`inventaris_bukus`) - Catatan resmi / *audit trail* penerimaan batch pengadaan buku.
- `EksemplarBuku` (`eksemplar_bukus`) - Tiap fisik buku, referensi utama sirkulasi (terkoneksi ke `inventaris_buku_id`).
- `Peminjaman` (`peminjamans`) - Pivot transaksional polymorphic ke user `Guru` atau `Siswa`.
- `KunjunganPerpustakaan` (`kunjungan_perpustakaans`) - Catatan kehadiran/presensi kunjungan siswa & guru ke perpustakaan.
- `TeacherPresensiProfile` (`teacher_presensi_profiles`) - Ekstensi profil scanner barcode guru.
- `PengaturanSekolah` (`school_settings`) - Konfigurasi jatuh tempo peminjaman.

## ⚠️ Pointer Teknis Penting (Untuk Pengembang / Sesi AI Selanjutnya)

1. **Polymorphic MorphMap Guru**: 
   Model `Guru` di project ini di-assign ke 2 alias MorphMap: `'wali_kelas'` dan `'guru'`. Metode `getMorphClass()` atau fungsi associate bawaan Eloquent (misal `$peminjaman->peminjam()->associate($guru)`) berisiko mengembalikan `'wali_kelas'` sebagai tipe utama karena urutannya dalam mapping.
   - **Solusi Wajib**: Saat membuat entitas Peminjaman untuk Guru, Anda **HARUS MENG-HARDCODE** nama aliasnya: `['peminjam_type' => 'guru']`. Jangan pernah pakai *associate* otomatis.

2. **Enum Status Peminjaman**: 
   Kolom `status` pada tabel `peminjamans` murni di-set tipe `ENUM` di level MySQL: `'dipinjam'`, `'dikembalikan'`, `'terlambat'`, `'hilang'`. Memasukkan string di luar ini (seperti 'selesai' atau 'aktif') akan diam-diam terkena *data truncate* oleh MySQL (Warning 1265) tanpa memicu Exception standar Laravel. Selalu gunakan nilai valid!

3. **Status "Terlambat" yang Real-Time**: 
   Sistem tidak mengandalkan *Scheduled Job / Cron* untuk mengubah nilai `status` di DB dari `dipinjam` menjadi `terlambat`. Laporan buku terlambat dihitung murni dengan query komparasi tanggal: 
   `where('status', 'dipinjam')->where('tanggal_jatuh_tempo', '<', now()->startOfDay())`.

4. **Metode Cetak (Print HTML)**:
   Semua fitur pencetakan ID/Barcode (baik untuk OSIS, maupun buku Perpustakaan) dilarang menggunakan lib *DomPDF*. Gunakan pola *Halaman HTML terpisah + `@media print` + `window.print()`* seperti yang diimplementasikan pada fungsi Cetak Barcode Eksemplar untuk memastikan layout Grid presisi dan gambar *scalable* saat di-scan scanner fisik.

5. **Pengiriman ID Massal (Bulk Print)**:
   Saat menangani form bulk (memilih banyak ID di Filament) yang butuh dibuka di tab terpisah, selalu gunakan metode *Session Transfer* (generate UUID key unik, taruh ID array ke `cache`/`session`, lalu *redirect* membawa key). Mengirim comma-separated string panjang di parameter URL (`?ids=`) terbukti menghasilkan `Error 414 URI Too Long` dari server web Nginx.

6. **Hotfix Regresi Relasi Siswa (Tahap 8)**:
   Jangan pernah menggunakan metode relasi `$siswa->kelasAjaranAktif()` karena metode tersebut **TIDAK PERNAH ADA** di Model `Siswa`. Gunakan `$siswa->enrollmentAktif` untuk mengakses data pendaftaran aktif. (Bug ini sempat menyebabkan Kiosk Sirkulasi lumpuh dari Tahap 4 hingga Tahap 8).

7. **Environment Variable `SCHOOL_EMAIL_DOMAIN` (Tahap 9)**:
   URL domain sekolah yang tercetak di footer Kartu Siswa diambil dari env variable `.env` ini. Pastikan diisi saat deploy ke server baru:
   ```
   SCHOOL_EMAIL_DOMAIN=smpn1majenang.sch.id
   ```
   Jika tidak diset, sistem akan menggunakan nilai fallback `smpn1majenang.sch.id`. Variabel yang sama berlaku untuk cetak kartu tunggal maupun massal.

8. **Global Sequence Barcode dengan Pessimistic Locking (Tahap 11)**:
   Penomoran kode eksemplar di `EksemplarBuku::generateKodeEksemplar()` berlanjut secara global (misal `PAI00001` - `PAI00020`, dilanjutkan `TIK00021` - `TIK00030`). Seluruh proses kueri nomor urut maksimal dibungkus dalam `DB::transaction` dan menggunakan `lockForUpdate()` untuk mencegah bentrok/race condition antar pengguna.

9. **Kebijakan Audit Trail Inventaris & Soft Deletes (Tahap 11)**:
   Tabel `inventaris_bukus` tidak boleh di-delete secara langsung. Pembatalan entri dilakukan via Action **"Batalkan Entri"** di Filament (mengubah status menjadi `dibatalkan` + wajib alasan). Action ini menolak pembatalan jika ada eksemplar yang berstatus bukan `tersedia` ATAU pernah memiliki riwayat peminjaman (`orWhereHas('peminjamans')`). Penghapusan eksemplar bersifat Soft Delete dan secara otomatis mengupdate `jumlah_eksemplar` di tabel inventaris lewat Eloquent Event `deleted` & `restored`.
