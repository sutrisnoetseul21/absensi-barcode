# TAHAP 5 — Dashboard Publik

> **Goal**: Halaman `/` dapat diakses publik tanpa login, menampilkan grafik dan Wall of Fame.
> Referensi: `07-ui-wireframe.md`, library Chart.js/ApexCharts.

- `[ ]` Install Chart.js via npm + Vite (bukan CDN)
- `[ ]` Buat Livewire component `PublicDashboard` (mode `/`, dengan filter)
- `[ ]` Buat Livewire component `PublicDisplayDashboard` atau reuse dengan parameter mode (route `/display`, mode TV/kios, tanpa filter)
- `[ ]` Route `/` → mode publik/mobile, filter aktif (Tahun Ajaran, Kelas, Bulan — default: aktif & bulan berjalan)
- `[ ]` Route `/display` → mode TV/kios, TANPA filter, default tahun ajaran aktif + bulan berjalan, render statis per-load (TIDAK pakai wire:poll / auto-refresh)
- `[ ]` **Widget Ringkasan**: Kartu per kelas (% hadir hari ini, jumlah siswa) — agregat saja, tanpa nama siswa
- `[ ]` **Wall of Fame**: Top 5 kelas kehadiran tertinggi bulan ini (dengan lencana ranking) — nama kelas saja, tanpa data individu siswa
- `[ ]` **Grafik Donut**: Hadir vs Telat vs Sakit vs Izin vs Alpa hari ini (total sekolah)
- `[ ]` **Grafik Bar**: Perbandingan kehadiran antar kelas bulan ini
- `[ ]` **Grafik Line**: Tren kehadiran harian dalam 1 bulan
- `[ ]` Filter (hanya di mode `/`, tidak di `/display`): pilih Tahun Ajaran, Kelas, Bulan
- `[ ]` Empty state: sebelum jam masuk (belum ada data hari ini), hari libur, kelas/tahun ajaran baru tanpa histori bulan ini
- `[ ]` (Opsional, bukan prioritas) Cache ringan agregasi (`Cache::remember`, TTL 5-10 menit) sebagai jaga-jaga trafik tinggi

**Verifikasi Tahap 5 Selesai:**
- [ ] Halaman `/` dapat diakses tanpa login, filter berfungsi dan responsif (mobile-friendly)
- [ ] Halaman `/display` dapat diakses tanpa login, tanpa filter, tanpa auto-refresh
- [ ] Grafik tampil dengan data yang benar di kedua mode
- [ ] Wall of Fame menampilkan 5 kelas teratas
- [ ] Tidak ada nama/identitas siswa individu yang muncul di halaman publik (agregat per kelas saja)
- [ ] Empty state tertangani dengan baik (bukan error/blank saat data kosong)

**Status: Belum dimulai ⬜**
