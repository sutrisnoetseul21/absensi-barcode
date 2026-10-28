# 🗄️ Tabel Database & Mekanisme Cache Tampilan Web Awal

Dokumen ini menjelaskan tabel-tabel basis data yang dibaca saat me-render halaman beranda, struktur query Eloquent, serta mekanisme cache untuk menjaga performa server tetap ringan.

---

## 1. Tabel-Tabel Database yang Dibaca

| Nama Tabel | Peran di Halaman Beranda | Pola Pengambilan |
| :--- | :--- | :--- |
| `school_settings` | Identitas nama, logo, favicon, dan variabel CSS `:root` | Singleton row (`PengaturanSekolah::current()`) |
| `web_settings` | Konten sambutan, teks berjalan, video YouTube, medsos | Singleton row (`WebSetting::instance()`) |
| `web_pillars` | Pilar karakter / nilai keunggulan sekolah | `WebPillar::active()->get()` |
| `web_sarpras` | Daftar sarana dan prasarana sekolah | `WebSarpra::orderBy('urutan')->get()` |
| `web_galeris` | Dokumentasi album foto sekolah | `WebGaleri::orderBy('urutan')->get()` |
| `web_artikels` | Berita, pengumuman, dan prestasi siswa | Filter `tipe` dan `published()` |
| `web_statistics` | Data angka capaian kustom sekolah | `WebStatistic::orderBy('order')->take(4)->get()` |
| `web_quick_links` | Tombol tautan cepat aplikasi eksternal | `WebQuickLink::where('is_active', true)->take(4)->get()` |
| `teachers` | Data guru untuk slider beranda | Eager loaded dengan jabatans & mapel |
| `attendances` & `students` | Perhitungan agregat statistik angka | Dikelola via `HomepageStatsService` ber-cache |

---

## 2. Mekanisme Caching (`HomepageStatsService`)

Halaman beranda adalah halaman publik yang paling sering diakses (*high traffic*). Untuk mencegah query berat (`COUNT` pada tabel `attendances` dan `students`) dieksekusi pada setiap kunjungan, sistem menerapkan **dua level cache**:

### A. Cache Kehadiran Hari Ini (`getPresensiHariIni`)
- **Cache Key**: `homepage:presensi:{YYYY-MM-DD}`
- **Durasi (TTL)**: **90 detik** (1.5 menit).
- **Logika**:
  ```php
  Cache::remember("homepage:presensi:{$tanggal}", 90, function () use ($tanggal) {
      return Presensi::whereDate('date', $tanggal)
          ->whereNotNull('scan_time')
          ->count();
  });
  ```
- **Alasan**: Memberikan sensasi *real-time* bagi pengunjung di pagi hari tanpa membebani database setiap detik.

### B. Cache Master Data Statistik (`getStatistikMaster`)
- **Cache Key**: `homepage:statistik`
- **Durasi (TTL)**: **1800 detik** (30 menit).
- **Logika**:
  - Mengambil total siswa aktif pada tahun ajaran yang sedang aktif.
  - Menghitung jumlah rombel aktif (`KelasAjaran`).
  - Menghitung total guru dan staf tendik.
- **Alasan**: Data master seperti jumlah siswa dan guru jarang berubah dalam hitungan menit.

---

## 3. Query Efisien untuk Tenaga Pendidik

Untuk mencegah masalah *N+1 Query* saat me-render slider guru di beranda:
```php
$gurus = Guru::with([
    'user',
    'jabatans',
    'kelasAjarans.kelas',
    'pengajarans.mataPelajaran',
    'pengajarans.kelasAjaran.kelas'
])->get();
```
Semua relasi jabatan dan mata pelajaran dimuat dalam **1 batch query**.
