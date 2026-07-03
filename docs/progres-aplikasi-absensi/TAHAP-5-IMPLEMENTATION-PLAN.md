# TAHAP 5 - IMPLEMENTATION PLAN: Dashboard Publik

## 1. Ringkasan Scope
Implementasi Dashboard Publik untuk satu sekolah (single-school) yang hanya menyajikan data agregasi tingkat kehadiran per kelas tanpa memaparkan identitas/nama siswa secara individu. Fitur ini menyediakan dua mode tampilan: Mode Publik (`/`) dengan filter tahun ajaran, kelas, dan bulan yang ramah mobile, serta Mode TV/Kios (`/display`) fullscreen tanpa filter yang optimal untuk layar lobi sekolah dengan render statis per-load.

---

## 2. Struktur Route & Component

Kita akan menggunakan **satu** Livewire component `App\Livewire\PublicDashboard` untuk melayani kedua route.
- **Route / (Mode Publik/Mobile)**:
  `Route::get('/', \App\Livewire\PublicDashboard::class)->name('public.dashboard');`
- **Route /display (Mode TV/Kios)**:
  `Route::get('/display', \App\Livewire\PublicDashboard::class)->name('public.display')->defaults('mode', 'display');`

### Analisis Trade-off (1 Component vs 2 Components)
- **Opsi A: 1 Component dengan parameter `mode` (Dipilih)**
  - *Kelebihan*: Menghindari duplikasi query database yang kompleks untuk widget Ringkasan, Wall of Fame, Donut, Bar, dan Line. Single source of truth untuk logika kalkulasi persentase kehadiran.
  - *Kekurangan*: Sedikit kondisional `@if($mode === 'display')` pada layout blade untuk menyembunyikan filter dan mengatur tema full-screen/font besar.
- **Opsi B: 2 Component terpisah (`PublicDashboard` & `PublicDisplayDashboard`)**
  - *Kelebihan*: Pemisahan file blade layout yang bersih tanpa kondisional.
  - *Kekurangan*: Duplikasi query kalkulasi data di kedua component (melanggar prinsip DRY), atau memerlukan class helper/service tambahan untuk membagikan logika query.

---

## 3. Query & Agregasi Data

Agregasi data mengandalkan tabel utama: `attendances` (Absensi), `student_enrollments` (EnrollmentSiswa), `classes` (Kelas), `academic_years` (TahunAjaran), dan `holidays` (HariLibur).

### a. Widget Ringkasan (Kartu per kelas: % hadir hari ini, jumlah siswa)
- **Siswa Aktif Per Kelas**:
  ```php
  $totalStudents = EnrollmentSiswa::where('academic_year_id', $academicYearId)
      ->where('status', 'aktif')
      ->selectRaw('class_id, COUNT(*) as total')
      ->groupBy('class_id')
      ->pluck('total', 'class_id');
  ```
- **Hadir & Telat Hari Ini Per Kelas**:
  ```php
  $presentStudents = Absensi::where('academic_year_id', $academicYearId)
      ->where('date', $today)
      ->whereIn('status', ['hadir', 'telat'])
      ->selectRaw('class_id, COUNT(*) as total')
      ->groupBy('class_id')
      ->pluck('total', 'class_id');
  ```
- **Perhitungan**:
  `% Kehadiran Kelas = ($presentStudents[$classId] / $totalStudents[$classId]) * 100` (default 0% jika total siswa = 0).

### b. Wall of Fame (Top 5 Kelas Kehadiran Terbaik Bulan Ini)
- **Persentase Bulanan per Kelas**:
  Menghitung total kehadiran (`hadir` + `telat`) dalam sebulan dibagi total hari efektif siswa (jumlah siswa aktif dikali jumlah hari efektif sekolah di bulan tersebut).
  - *Hari Efektif*: Jumlah hari kerja (Senin-Jumat) di bulan terpilih dikurangi jumlah hari libur nasional/sekolah di tabel `holidays` (dengan `class_id IS NULL`).
  - *Query*:
    ```php
    // Ambil data absensi hadir & telat per kelas di bulan terpilih
    $monthlyAttendances = Absensi::where('academic_year_id', $academicYearId)
        ->whereMonth('date', $month)
        ->whereYear('date', $year)
        ->whereIn('status', ['hadir', 'telat'])
        ->selectRaw('class_id, COUNT(*) as total_present')
        ->groupBy('class_id')
        ->get();
    ```
  - Kita mengurutkan 5 kelas teratas berdasarkan persentase kehadiran bulanan tertinggi.

### c. Grafik Donut (Hadir vs Telat vs Sakit vs Izin vs Alpa hari ini - total sekolah)
- **Query Kehadiran Terdaftar**:
  ```php
  $statusCounts = Absensi::where('academic_year_id', $academicYearId)
      ->where('date', $today)
      ->selectRaw('status, COUNT(*) as total')
      ->groupBy('status')
      ->pluck('total', 'status');
  ```
- **Kalkulasi Alpa Dinamis**:
  Untuk menghindari keharusan generate record "alpa" otomatis setiap hari, kita dapat menghitung Alpa secara dinamis:
  `Alpa Hari Ini = (Total Seluruh Siswa Aktif) - (Hadir + Telat + Sakit + Izin dari attendances hari ini)`
  *Catatan*: Jika record absensi status 'alpa' diinput manual oleh Wali Kelas di portal, kita kurangi jumlah alpa dinamis dengan record 'alpa' terdaftar agar tidak double counting.

### d. Grafik Bar (Perbandingan Kehadiran Antar Kelas Bulan Ini)
- Menggunakan hasil perhitungan persentase bulanan per kelas (sama seperti Wall of Fame) dan merendernya dalam grafik batang horizontal/vertikal untuk semua kelas aktif.

### e. Grafik Line (Tren Kehadiran Harian total sekolah dalam 1 bulan)
- **Query Tren Harian**:
  ```php
  $dailyTrend = Absensi::where('academic_year_id', $academicYearId)
      ->whereMonth('date', $month)
      ->whereYear('date', $year)
      ->whereIn('status', ['hadir', 'telat'])
      ->selectRaw('date, COUNT(*) as total_present')
      ->groupBy('date')
      ->orderBy('date')
      ->get();
  ```
- Dibagi dengan total siswa aktif sekolah untuk mendapatkan persentase harian, diplot per tanggal (mengabaikan weekend/hari libur agar grafik tidak drop ke 0%).

### Optimasi Query (Index):
Tabel `attendances` telah dilengkapi indeks gabungan `['class_id', 'academic_year_id', 'date']` dari migration bawaan. Ini memastikan pencarian data agregasi per kelas dan per hari berjalan sangat cepat meskipun data bervolume besar.

---

## 4. Chart.js Integration

### a. Instalasi Dependency
1. Jalankan `npm install chart.js`
2. Tambahkan ke `resources/js/app.js`:
   ```javascript
   import Chart from 'chart.js/auto';
   window.Chart = Chart;
   ```
3. Compile assets dengan `npm run build` atau jalankan `npm run dev`.

### b. Pola Re-render Livewire + JS
Karena Livewire melakukan re-render DOM saat filter berubah di route `/`, instance Chart.js di frontend bisa rusak. Untuk mengatasinya:
1. Bungkus elemen `<canvas>` dengan container yang memiliki directive `wire:ignore`.
2. Gunakan Livewire dispatch event untuk memicu update chart dari PHP ke JavaScript.
   - PHP: `$this->dispatch('update-charts', donutData: $donutData, barData: $barData, lineData: $lineData);`
   - JS/Blade:
     ```javascript
     document.addEventListener('livewire:init', () => {
         let donutChart, barChart, lineChart;
         
         // Inisialisasi chart awal
         
         Livewire.on('update-charts', (event) => {
             // Update data & labels pada masing-masing instance chart
             // Panggil chart.update()
         });
     });
     ```

---

## 5. Empty/Loading/Error State Handling

- **Hari Libur / Weekend**:
  Jika tanggal hari ini berada dalam range libur (cek `HariLibur::hariIni($today)`) atau hari Sabtu/Minggu, sembunyikan widget Donut/Ringkasan hari ini dan tampilkan banner informatif: *"Hari ini adalah hari libur ([Nama Libur])"* atau *"Hari libur akhir pekan"*.
- **Sebelum Absensi Dimulai (Pagi Hari)**:
  Jika belum ada data sama sekali sebelum jam masuk sekolah, tampilkan pesan: *"Belum ada aktivitas absensi hari ini. Absensi dimulai pukul [checkin_time]"*.
- **Data Periode Kosong**:
  Jika filter kelas/bulan tidak memiliki riwayat absensi, tampilkan empty state ilustrasi ramah pada grafik: *"Tidak ada data absensi untuk periode terpilih"*.
- **Loading State**:
  Bungkus elemen widget dengan skeleton loader menggunakan `wire:loading` agar transisi filter berjalan mulus dan premium.
- **Error State**:
  Gunakan try-catch di backend. Jika error terjadi, tampilkan error card dengan tombol "Coba Lagi" di mode `/`, dan fallback pesan statis bersih di mode TV (`/display`).

---

## 6. Urutan Eksekusi (Step-by-Step)

1. **Install Chart.js & Bundling**:
   - `npm install chart.js`
   - Setup `resources/js/app.js` & build assets.
2. **Buat Livewire Component**:
   - `php artisan make:livewire PublicDashboard`
3. **Konfigurasi Routing**:
   - Edit `routes/web.php` untuk memetakan `/` dan `/display` ke `PublicDashboard`.
4. **Implementasi Query & Cache Agregasi**:
   - Implementasikan fungsi-fungsi query di `PublicDashboard.php` dengan fallback cache ringan `Cache::remember` (TTL 5-10 menit) untuk route `/`.
5. **Desain Layout Premium**:
   - Implementasikan View Blade dengan layout responsif (untuk `/`) menggunakan Glassmorphic style, warna premium, dan transisi smooth.
   - Implementasikan Layout TV/Kios (untuk `/display`) full-screen, background gelap/kontras tinggi, teks super besar untuk kenyamanan visual jarak jauh.
6. **Integrasi Script Chart.js**:
   - Hubungkan backend Livewire ke frontend JS via event listeners untuk re-render grafis.
7. **Penanganan State & Edge Cases**:
   - Implementasikan validasi hari libur, deteksi pagi hari sebelum scan, dan loading state skeleton.

---

## 7. Test Manual / Verifikasi

Acuan keberhasilan pengerjaan Tahap 5:
- [ ] Halaman `/` dapat diakses tanpa login, filter berfungsi dan responsif (mobile-friendly).
- [ ] Halaman `/display` dapat diakses tanpa login, tanpa filter, tanpa auto-refresh.
- [ ] Grafik tampil dengan data yang benar di kedua mode.
- [ ] Wall of Fame menampilkan 5 kelas teratas.
- [ ] Tidak ada nama/identitas siswa individu yang muncul di halaman publik (agregat per kelas saja).
- [ ] Empty state tertangani dengan baik (bukan error/blank saat data kosong).
