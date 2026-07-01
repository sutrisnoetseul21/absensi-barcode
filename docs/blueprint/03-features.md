# 03. Features

Kelompokkan per modul:

1. **Autentikasi & Manajemen User Admin**
   - Login Admin/Super Admin via Filament (tabel `users`)
   - Login Wali Kelas & Siswa via portal custom (multi-guard)
   - Ganti password default saat login pertama

2. **Pengaturan Sekolah (Global Settings)** ← **NEW**
   - Nama sekolah, alamat, logo, nama kepala sekolah (untuk PDF & kartu)
   - **Jam masuk global & batas toleransi telat** (1 setting berlaku untuk seluruh sekolah)
   - Tahun ajaran aktif default
   - Dikelola oleh Super Admin via Filament Settings page

3. **Manajemen Tahun Ajaran** (aktif/nonaktif, arsip)

4. **Manajemen Kelas (Template Nama Kelas)** ← **DIPERBARUI**
   - Daftar nama kelas bersifat **permanen** (7A, 7B, 7C, 8A, ..., 9C) — tidak dibuat ulang tiap tahun
   - Seeder awal otomatis mengisi nama kelas sesuai jenjang sekolah
   - Per tahun ajaran: assign wali kelas ke kelas via `class_academic_year`
   - Satu wali kelas bisa memegang lebih dari 1 kelas per tahun ajaran

5. **Manajemen Siswa** (biodata, NISN, tempat & tanggal lahir, alamat, foto, barcode, riwayat kelas)

6. **Manajemen & Cetak Kartu OSIS (Barcode)**
   - Generate & cetak kartu pelajar/OSIS: layout foto, barcode, biodata, TTD kepala sekolah
   - Data kepala sekolah & nama sekolah diambil dari `school_settings`

7. **Absensi via Scan Berkecepatan Tinggi (Kios Mode)**
   - Mode layar penuh, fokus pada input scanner
   - **Audio Feedback** (Text-to-Speech): respon setiap scan
   - **Anti-Spam & Concurrency**: debounce per-barcode, pemrosesan async

8. **Kalkulasi Menit Keterlambatan**
   - Otomatis menghitung `late_minutes` = selisih waktu scan vs `school_settings.checkin_time`
   - Akumulasi total menit telat bulanan per siswa

9. **Kalender Hari Libur**
   - Libur nasional, cuti bersama (support range tanggal), libur khusus kelas
   - Hari libur dikecualikan dari perhitungan % kehadiran

10. **Dashboard Publik (Wall of Fame & Grafik)**
    - **Wall of Fame**: Top 5 kelas terajin bulan ini (gamifikasi)
    - **Grafik Interaktif** (Chart.js/ApexCharts): Donut hari ini, Bar antar kelas, Line tren bulanan

11. **Dashboard Admin** (detail per siswa, alert batas pelanggaran, grafik tren, export)

12. **Laporan & Export** (PDF/Excel per kelas/siswa/bulan)

13. **Kenaikan Kelas & Pergantian Tahun Ajaran** ← **DIPERBARUI**
    - **Wizard berbasis Excel** (proses batch akhir tahun):
      1. Admin download template Excel berisi: NISN, Nama, Kelas Saat Ini, kolom "Kelas Baru" (kosong)
      2. Admin/operator isi kolom "Kelas Baru" di Excel (misal: 7A → 8A)
      3. Upload kembali → validasi otomatis nama kelas → buat enrollment baru
      4. Siswa dengan kolom kosong atau kelas tidak valid → tampil di daftar error untuk review
    - Log detail kenaikan per siswa (audit trail: siapa naik ke mana, siapa tinggal kelas)
    - Alternatif: wizard manual step-by-step di UI (bulk action Filament)

14. **Arsip Tahun Ajaran** (data tahun lalu read-only)

15. **Portal Wali Kelas**
    - Rekap kelas yang diampu (bisa > 1 kelas)
    - **Alert Pelanggaran**: notifikasi siswa sering Alpa atau akumulasi telat tinggi
    - Input manual absensi (Sakit/Izin/Alpa) dengan alasan (field `note`)
    - Pencarian siswa via nama atau NISN

16. **Portal Siswa** (riwayat absensi pribadi, read-only)

17. **Import & Export Data via Excel** (Upload/Download berdasarkan NISN)

Untuk tiap fitur, tulis: deskripsi singkat, aktor, prasyarat, output.
