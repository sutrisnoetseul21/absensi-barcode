# Blueprint: Portal Siswa & Portal Guru

Modul ini menyediakan antarmuka pengguna (UI) mandiri yang dirancang khusus untuk Siswa dan Guru. Portal ini berbeda dari panel admin Filament dan dibangun menggunakan **Livewire v3** beserta kerangka kerja desain kustom untuk memberikan pengalaman pengguna (UX) yang premium, responsif, dan dinamis.

## 1. Arsitektur Portal

Berbeda dengan antarmuka administratif (Admin/HRD), Portal Siswa dan Portal Guru ditujukan untuk penggunaan harian (End-User).

- **Framework**: Laravel + Livewire v3 + Tailwind CSS.
- **Pendekatan Desain**: Glassmorphism, Micro-interactions, *Mobile-first*, dan Gradasi Warna.
- **Otentikasi**:
  - Portal Siswa: Membutuhkan peran `siswa` (`auth.siswa` middleware).
  - Portal Guru: Membutuhkan peran `wali_kelas` atau `guru` (`auth.wali` middleware).

---

## 2. Portal Siswa (`/portal-siswa`)

Portal ini dirancang agar siswa dapat memantau data akademis, riwayat kehadiran, dan status perpustakaan mereka secara mandiri.

### A. Dashboard Utama (`SiswaMainDashboard`)
Berfungsi sebagai *hub* pusat yang memuat:
1. **Sambutan & Pengumuman**: Menampilkan sapaan nama lengkap dan *badge* pengumuman aktif dari sekolah.
2. **Widget Kehadiran**: Rekapitulasi persentase kehadiran berdasarkan jumlah hari efektif bulan berjalan.
3. **Widget Perpustakaan**: Menampilkan jumlah buku yang sedang dipinjam secara aktif (status: *dipinjam*).

### B. Presensi & Akademik (`SiswaDashboard`)
1. **Kalender Kehadiran**: Menampilkan visualisasi bulanan terkait kehadiran, keterlambatan, sakit, izin, atau alpa.
2. **Statistik Keterlambatan**: Mengkalkulasi durasi menit keterlambatan.

### C. Perpustakaan (`SiswaPerpustakaan`)
Terdiri atas tiga tab fungsional:
1. **Katalog Buku**: Fasilitas pencarian buku (*search*) dan filter terpopuler/terbaru. Siswa dapat melihat ketersediaan eksemplar. Jika buku habis, sistem dapat menampilkan estimasi pengembalian terdekat (Earliest Return Date).
2. **Pinjaman Aktif (Sedang Dipinjam)**: Daftar buku fisik yang sedang dipegang oleh siswa berserta tenggat waktu pengembalian.
3. **Riwayat Peminjaman**: Riwayat seluruh peminjaman masa lalu.
4. **Riwayat Kunjungan**: Rekam jejak fisik ketika siswa masuk ke perpustakaan.

---

## 3. Portal Guru (`/portal-guru`)

Portal Guru didesain agar tenaga pendidik dapat memantau kelas yang mereka ampu sekaligus menggunakan layanan perpustakaan secara mandiri.

### A. Dashboard Utama (`GuruMainDashboard`)
Menampilkan ringkasan informasi yang krusial bagi guru:
1. **Widget Kelas Ampu**: Menghitung secara otomatis *unique class* yang sedang diajar oleh guru pada tahun ajaran aktif, baik dari relasi *Wali Kelas* maupun *Guru Mata Pelajaran*.
2. **Widget Perpustakaan**: Menampilkan jumlah buku milik perpustakaan yang sedang dipinjam oleh guru.

### B. Presensi & Akademik (`WaliKelasDashboard`)
- Memungkinkan guru (terutama Wali Kelas) untuk melihat rekap kehadiran seluruh siswa dalam kelasnya.
- Mendukung fitur input absen manual pada hari tersebut apabila kelas belum diabsen secara otomatis via barcode/kartu.

### C. Perpustakaan (`GuruPerpustakaan`)
Memiliki fitur yang sedikit lebih *advance* dari Portal Siswa:
1. **Katalog Buku & Riwayat Pribadi**: Guru dapat meminjam, mencari buku, dan melihat riwayat kunjungannya sendiri sama seperti siswa.
2. **Tab Peminjaman Siswa (Khusus Kelas Ampu)**: 
   - Sistem secara otomatis membangun *dropdown* kelas-kelas yang diampu oleh sang guru.
   - Guru dapat menyortir dan melihat siswa mana di kelasnya yang sedang meminjam buku.
   - **Indikator Keterlambatan**: Jika ada siswa yang jadwal kembalinya terlambat, nama siswa tersebut akan di-sorot dengan peringatan merah berkedip (*animate-pulse*), memudahkan guru menegurnya di dalam kelas.

---

## 4. Keamanan dan Batasan

1. **Strict Binding**: 
   - Pengguna dengan peran `siswa` yang tidak memiliki relasi model `Student` yang sah akan dicegah masuk.
   - Pengguna dengan peran `guru/wali_kelas` yang tidak memiliki relasi model `Teacher` yang sah akan dicegah masuk.
2. **Prevent Cross-Access**: Siswa tidak bisa memotong jalur URL ke Portal Guru, dan sebaliknya. Admin dapat *impersonate* tetapi secara default memiliki portalnya sendiri di `/admin`.
