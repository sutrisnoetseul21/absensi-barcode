# Modul Perpustakaan ERP (Rangkuman Akhir)

Modul Perpustakaan ini dikembangkan secara komprehensif dalam kerangka Fase 2 ERP. Pengembangan dibagi menjadi 6 tahap iteratif (Tahap 0 hingga Tahap 5), dengan fokus pada relasi *polymorphic* sirkulasi yang kokoh, arsitektur UI/UX Kiosk modern yang diadaptasi dari Modul Presensi, dan implementasi efisiensi pencetakan barcode.

## Histori Tahapan
1. **[Tahap 0 (24 Juli 2026)]**: Perancangan struktur relasi antar entitas (Buku, Kategori, Eksemplar, Peminjaman) dan mendefinisikan alias Map Polymorphic.
2. **[Tahap 1 (24 Juli 2026)]**: Pembuatan migration, model, dan setup Filament untuk Katalog Buku, Kategori, serta Eksemplar (beserta RelationManager). Penegasan solusi terkait anomali *MorphMap* `Guru`.
3. **[Tahap 2 (25 Juli 2026)]**: Implementasi fitur *Generate Eksemplar Massal* di dalam *EksemplarBukusRelationManager*. Penanganan *Race Condition* menggunakan DB Transaction dan pelaporan error constraint duplikat via Notifikasi Filament.
4. **[Tahap 3 (26 Juli 2026)]**: Realisasi Cetak Barcode Eksemplar. Mengadopsi metode render browser murni via HTML+CSS Print (bukan DomPDF) demi ketajaman scan. Masalah URI Too Long dalam cetak bulk 200 buku diselesaikan menggunakan metode transit ID via Session Database.
5. **[Tahap 4 (26 Juli 2026)]**: Pembangunan UI Kiosk Sirkulasi menggunakan Alpine JS dan Livewire (dua state scan: Peminjam & Buku). Fitur relasi barcode ke model Guru lewat entitas `teacher_presensi_profiles`. Perbaikan bug Enum `status` Peminjaman dari 'selesai' ke `'dikembalikan'`.
6. **[Tahap 5 (26 Juli 2026)]**: Penyempurnaan akhir dengan Widget Statistik di halaman dashboard (mengalkulasi *buku terlambat* secara real-time tanpa *cronjob*), serta melengkapi tabel *Riwayat Peminjaman* dan penambahan jumlah eksemplar *Tersedia* secara agregasi performa tinggi (`withCount`).

## Daftar Model & Tabel Inti
- `KategoriBuku` (`kategori_bukus`)
- `Buku` (`bukus`) - Memiliki opsional relasi ke mata pelajaran.
- `EksemplarBuku` (`eksemplar_bukus`) - Tiap fisik buku, referensi utama sirkulasi.
- `Peminjaman` (`peminjamans`) - Pivot transaksional polymoprhic ke user `Guru` atau `Siswa`.
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
