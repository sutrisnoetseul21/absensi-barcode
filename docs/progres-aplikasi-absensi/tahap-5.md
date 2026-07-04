# TAHAP 5 — Dashboard Publik

> **Goal**: Halaman `/` dapat diakses publik tanpa login, menampilkan grafik dan Wall of Fame.
> Referensi: `07-ui-wireframe.md`, library Chart.js/AlpineJS.

- `[x]` Install Chart.js via npm + Vite (bukan CDN)
- `[x]` Buat Livewire component `PublicDashboard` (mode `/`, dengan filter, manual slide) dan mode `/display` (TV/kios, auto-slide 8 detik, tanpa filter).
- `[x]` Struktur Data: Backend query SEKALI untuk semua kelas, grouping tampilan di frontend per `grade_level` (Angkatan 7, 8, 9).
- `[x]` Pemisahan Filter: `wire:model` untuk Tahun/Bulan, `x-model` (Alpine murni) untuk Angkatan/Kelas.
- `[x]` Scope `wire:ignore`: Terapkan pada area Chart.js dan Slider saja.
- `[x]` **Widget Ringkasan Slider**: Kartu per kelas (% hadir hari ini, jumlah siswa) — agregat saja, dinamis sesuai jumlah kelas di database.
- `[x]` **Wall of Fame**: Top 5 kelas kehadiran tertinggi bulan ini.
- `[x]` **Grafik Donut**: Hadir vs Telat vs Sakit vs Izin vs Alpa vs Belum Absen hari ini (total sekolah).
- `[x]` **Grafik Bar**: Perbandingan kehadiran antar kelas bulan ini per Angkatan.
- `[x]` **Grafik Line**: Tren kehadiran harian dalam 1 bulan (Total Sekolah).
- `[x]` (Limitasi MVP) Formula hari efektif sekolah belum memperhitungkan libur khusus kelas (Opsi B - dicatat TODO di komentar kode).
- `[x]` Jam Tutup Absensi dinamis sesuai `checkin_time + 60 menit`. Pembedaan "Belum Absen" vs "Alpa (Dinamis)".
- `[x]` Cache ringan agregasi (`Cache::remember`, TTL 5 menit) tanpa parameter kelas/angkatan.

**Verifikasi Tahap 5 Selesai:**
- `[x]` Halaman `/` dapat diakses tanpa login, navigasi manual dan highlight kelas berfungsi (mobile-friendly).
- `[x]` Halaman `/display` dapat diakses tanpa login, auto-advance 8 detik berjalan lancar.
- `[x]` Grafik terupdate dengan benar saat filter Bulan diganti (menggunakan event dispatch ke Chart.js).
- `[x]` Data grid kelas per angkatan bersifat dinamis (tidak error meski jumlah kelas berbeda tiap angkatan).
- `[x]` Wall of Fame menampilkan 5 kelas teratas.
- `[x]` Tidak ada identitas siswa individu yang muncul (hanya agregat per kelas).
- `[x]` Pembedaan "Belum Absen" dan "Alpa (Dinamis)" berfungsi sesuai jam server.

**Status: Selesai ✅**
