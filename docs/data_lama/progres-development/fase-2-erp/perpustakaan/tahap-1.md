# Modul Perpustakaan: Tahap 1 (Migration & Fondasi Database)

**Status:** Selesai
**Tanggal:** 26 Juli 2026

## Deskripsi Pekerjaan
Tahap pertama dari pengembangan Modul Perpustakaan ini berfokus pada pembuatan skema basis data inti untuk entitas perpustakaan, pendaftaran *Morph Map* untuk proses peminjaman yang polimorfik, serta pembuatan *skeleton* untuk *Filament Panel* Perpustakaan.

## Detail Implementasi
1. **Migration (Database Schema):**
   - Pembuatan tabel `kategori_bukus` (ID UUID).
   - Pembuatan tabel `bukus` (ID UUID, foreign key ke `kategori_bukus` dan `mata_pelajarans`). Kolom `mapel_id` menggunakan tipe `bigInt` (sesuai spesifikasi tabel `mata_pelajarans`).
   - Pembuatan tabel `eksemplar_bukus` (ID UUID).
   - Pembuatan tabel `peminjamans` (ID UUID, foreign key ke `eksemplar_bukus` dan `users`, tipe `morphs` untuk peminjam).
   - Migrasi berhasil dijalankan tanpa isu referensial.

2. **Morph Map (`AppServiceProvider`):**
   - Menambahkan key `siswa` yang memetakan ke `App\Models\Siswa::class`.
   - Menambahkan key `guru` yang memetakan ke `App\Models\Guru::class`.
   - Pola ini ditambahkan agar tabel polimorfik `peminjamans` menyimpan string `siswa` atau `guru` alih-alih *fully-qualified class name* yang panjang.

3. **Panel Perpustakaan (Filament):**
   - Membuat `PerpustakaanPanelProvider.php` yang mereplika konfigurasi standar pada `AkademikPanelProvider` (termasuk *guard* `web` standar).
   - Path dan ID: `admin-perpustakaan`.
   - Menggunakan logo dan *branding* sistem yang terintegrasi (mengambil dari tabel `school_settings`).
   - Berhasil didaftarkan di `bootstrap/providers.php`.

4. **Blueprint Update:**
   - Tabel-tabel yang baru dibuat telah ditambahkan ke dalam `docs/blueprint/05-database.md`.
   - ERD telah diperbarui untuk mencakup modul perpustakaan.

## Catatan Penting untuk Tahap Selanjutnya (Tahap 2)
1. **Interactive Blocking Notification:** Pada tahap pembuatan *Resource* (seperti `BukuResource` dan `EksemplarBukuResource`), WAJIB mengkustomisasi `DeleteAction`, `BulkDeleteAction`, `ForceDeleteAction`, dan `ForceDeleteBulkAction`. Penggunaan *hook* `before()` di action ini sangat krusial untuk membatalkan penghapusan secara keras (*hard delete*) pada data buku dan eksemplar yang memiliki riwayat di tabel `peminjamans`.

## Catatan Teknis Penting untuk Tahap 4
**Anomali Morph Map Guru pada Model Peminjaman:**
Terdapat ambiguitas alias Morph Map untuk `Guru::class` di dalam `AppServiceProvider`, di mana `wali_kelas` terdaftar sebelum `guru`. Akibat urutan pencarian, panggilan `getMorphClass()` pada objek Guru akan selalu me-resolve menjadi `'wali_kelas'`. 

Oleh karena itu, pada saat pembuatan logika sirkulasi (Tahap 4):
> [!WARNING]
> **JANGAN PERNAH** menggunakan fungsi bawaan `$peminjaman->peminjam()->associate($guruInstance)` untuk proses peminjaman oleh Guru. Ini akan mengakibatkan `peminjam_type` salah tersimpan sebagai `'wali_kelas'`.
> **WAJIB** set nilai tersebut secara manual/eksplisit (misal: `$peminjaman->peminjam_type = 'guru'; $peminjaman->peminjam_id = $guru->id;`) saat membuat/menyimpan *record* Peminjaman.
