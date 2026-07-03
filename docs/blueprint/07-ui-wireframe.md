# 07. UI Wireframe

Daftar layar yang perlu di-sketch:

- **Login Admin** (Filament default)

- **Dashboard Publik**
  - **Constraint utama**: HANYA menampilkan data agregat per kelas (jumlah siswa, % kehadiran, ranking kelas). TIDAK PERNAH menampilkan nama, foto, atau status individu siswa di halaman ini — itu domain Dashboard Admin/Wali Kelas.
  - **2 Mode Tampilan** (route terpisah):
    - **Mode Publik** (`/`) — untuk diakses via HP/browser orang tua/pengunjung. Filter manual aktif (Tahun Ajaran, Kelas, Bulan). Layout compact, responsif, scroll vertikal.
    - **Mode TV/Kios** (`/display`) — untuk ditampilkan di layar lobi sekolah. Tanpa filter (default: tahun ajaran aktif, bulan berjalan). Full-screen, font besar untuk dilihat dari jarak jauh. Render sekali saat load — TIDAK auto-refresh/polling (karena jendela absensi cuma pagi hari, data tidak berubah signifikan setelah itu).
  - Kartu ringkasan per kelas (% hadir hari ini, jumlah siswa).
  - **Wall of Fame**: Top 5 Kelas dengan kehadiran terbaik bulan ini (lencana/ranking) — nama kelas saja.
  - **Grafik Interaktif (Chart.js)**:
    - Donut Chart (Hadir vs Sakit vs Izin vs Alpa hari ini — total sekolah)
    - Bar Chart (Kehadiran antar kelas, bulan ini)
    - Line Chart (Tren kehadiran harian, 1 bulan)
  - Filter (hanya di Mode Publik `/`): Tahun Ajaran, Kelas, Bulan
  - State: loading (skeleton saat fetch data), empty (sebelum jam masuk / hari libur / kelas baru tanpa histori — tampilkan pesan ramah, bukan chart kosong/error), error (gagal load data — tampilkan pesan + tombol retry di mode publik; di mode TV cukup fallback pesan statis)
  - Responsif: Wajib untuk Mode Publik (`/`). Mode TV (`/display`) didesain untuk layar besar tetap (tidak perlu responsif ke ukuran HP).

- **Dashboard Admin** (lebih detail)
  - Ringkasan total siswa, total hadir hari ini, % per kelas
  - Daftar alert siswa bermasalah (sering alpa / telat banyak)
  - Grafik tren dan per-kelas

- **Pengaturan Sekolah** ← **NEW**
  - Form: nama sekolah, alamat, upload logo, nama kepala sekolah
  - Setting jam masuk global & batas menit toleransi
  - Tahun ajaran aktif default
  - Hanya bisa diakses Super Admin

- **Halaman Scan Absensi (Kios Mode)**
  - Layar penuh (full-screen), background putih bersih
  - Logo sekolah di bagian atas tengah (dari `school_settings.school_logo_path`)
  - Card besar di tengah untuk area feedback: foto siswa + nama + status (hijau/kuning/merah)
  - Input barcode tersembunyi (auto-focus)
  - Respon real-time & asynchronous

- **Manajemen Kelas (Template Nama Kelas)** ← **NEW**
  - Tabel daftar nama kelas permanen (7A, 7B, ..., 9C)
  - Tambah/edit/soft-delete nama kelas
  - Tombol "Seeder Otomatis" untuk isi kelas SMP/SMA dari template standar

- **Assign Wali Kelas per Tahun Ajaran**
  - Pilih tahun ajaran → tampil daftar kelas → assign wali kelas untuk masing-masing

- **Manajemen Siswa**
  - Tabel daftar + filter kelas & tahun ajaran
  - Form tambah/edit (NISN, nama, TTL, alamat, foto, barcode)
  - Tombol "Cetak Kartu OSIS" (1 atau massal per kelas)
  - Tombol "Reset Password"
  - **Soft-deleted siswa** bisa di-restore lewat filter "Tampilkan yang dihapus"

- **Halaman Import/Export Data**
  - Upload Excel siswa, download template, download presensi
  - Laporan hasil import (berhasil/gagal per baris)

- **Halaman Import/Export Kenaikan Kelas** ← **NEW**
  - Tombol "Download Template Kenaikan Kelas" (Excel: NISN, Nama, Kelas Saat Ini, Kelas Baru)
  - Upload file yang sudah diisi → preview validasi → konfirmasi → proses
  - Tabel error: baris yang gagal + alasan (kelas tidak valid / NISN tidak ditemukan)

- **Kalender Hari Libur**
  - Kalender interaktif (FullCalendar)
  - Klik tanggal → tambah libur (form: range tanggal, deskripsi, tipe, kelas opsional)
  - Highlight libur nasional vs cuti bersama vs khusus kelas

- **Laporan & Export Presensi**
  - Filter: Tahun Ajaran + Kelas + Bulan + Status
  - Tabel rekap per siswa per hari
  - Export PDF dan Excel
  - Cetak kartu OSIS massal

- **Wizard Kenaikan Kelas** (alternatif UI)
  - Step 1: Pilih tahun ajaran baru
  - Step 2: Download/upload Excel kenaikan (atau bulk edit via tabel di UI)
  - Step 3: Preview hasil — daftar naik, tinggal, lulus, error
  - Step 4: Konfirmasi → proses → simpan `promotion_logs` + `promotion_log_details`

- **Halaman Arsip Tahun Ajaran** (read-only, filter per tahun)

- **Login Wali Kelas** (form terpisah di `/wali-kelas/login`)

- **Dashboard Wali Kelas**
  - Pilih kelas yang diampu (dropdown jika pegang > 1 kelas)
  - Rekap hari ini: tabel daftar siswa + status hadir/tidak
  - Filter per bulan
  - **Daftar Alert Pelanggaran**: label merah siswa >= 3x Alpa atau >= 100 menit telat
  - Tombol/modal Input Manual Absensi (cari nama/NISN, pilih status, isi `note`)

- **Login Siswa & Dashboard Siswa**
  - Tabel riwayat absensi semua bulan (filter per bulan)
  - Ringkasan: total hadir, telat, sakit, izin, alpa + akumulasi menit telat

Untuk tiap layar: komponen utama, state (loading/empty/error), responsif atau tidak.
