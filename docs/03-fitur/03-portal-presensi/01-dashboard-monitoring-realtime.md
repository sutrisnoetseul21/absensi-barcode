# 📊 Dashboard Monitoring Real-Time Portal Presensi

Halaman utama Portal Presensi ([`App\Livewire\PortalPresensiDashboard`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Livewire/PortalPresensiDashboard.php)) berfungsi sebagai pusat analitik pemantauan kehadiran siswa secara *live* sepanjang hari sekolah.

---

## 1. Kartu Indikator Utama (Live Metrics)

Pada bagian atas dashboard, sistem menyajikan rekap angka kehadiran hari yang dipilih:
- **Total Siswa Aktif**: Jumlah seluruh siswa terdaftar pada tahun ajaran aktif.
- **Hadir Tepat Waktu**: Jumlah siswa yang melakukan pemindaian barcode sebelum batas toleransi jam datang.
- **Terlambat**: Jumlah siswa yang scan melebihi jam masuk (`batas_jam_datang`).
- **Izin / Sakit**: Siswa yang memiliki pengajuan surat dokter / izin yang telah disetujui wali kelas.
- **Belum Presensi / Alpa**: Siswa yang belum memiliki catatan scan hingga waktu saat ini.
- **Persentase Kehadiran Harian**: Rasio kehadiran sekolah `((Hadir + Terlambat) / Total Siswa) * 100%`.

---

## 2. Sistem 4 Tab Filtering Interaktif

Tampilan tabel dashboard dilengkapi tab navigasi reaktif Livewire tanpa reload halaman:

| Tab Navigasi | Fungsi & Isi Tampilan |
| :--- | :--- |
| **`per_kelas`** | Rekapitulasi per rombel: Menampilkan daftar kelas (VII-A s/d IX-H) lengkap dengan jumlah siswa hadir, sakit, izin, alpa, dan persentase kehadiran per kelas. |
| **`belum_presensi`** | Daftar siswa yang belum melakukan pemindaian hari ini. Dilengkapi tombol aksi kirim notifikasi WhatsApp ke orang tua secara individu atau massal. |
| **`siswa_terlambat`** | Daftar siswa yang datang terlambat beserta catatan waktu scan masuk dan jumlah menit keterlambatan. |
| **`semua_siswa`** | Master view seluruh siswa pada tanggal terpilih dengan filter status kehadiran lengkap. |

---

## 3. Fitur Ekspor Laporan (PDF & Excel)

Dari dashboard, administrator presensi dapat langsung mengunduh rekapitulasi data:
1. **Unduh Rekap Spreadsheet (Excel)**:
   - Menggunakan `Maatwebsite\Excel\Facades\Excel` dengan class [`App\Exports\RekapPresensiHarianExport`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Exports).
   - Memuat data nama siswa, kelas, NISN, jam scan masuk, jam scan pulang, dan status.
2. **Unduh Dokumen Laporan (PDF)**:
   - Menggunakan `Barryvdh\DomPDF\Facade\Pdf` format siap cetak lengkap dengan kop surat sekolah dan tanda tangan kepala sekolah.

---

## 4. Quick Actions di Header Dashboard

Dashboard menyediakan jalan pintas instan untuk operator:
- 🚀 **Buka Kiosk Scan**: Tombol membuka mode fullscreen scanner gerbang (`/portal-presensi/scan`).
- 🕌 **Buka Kiosk Sholat**: Tombol membuka scanner presensi sholat dhuhur (`/portal-presensi/scan-sholat-dhuhur`).
- 📢 **Kirim Notifikasi Belum Hadir**: Menjalankan pengiriman broadcast WA otomatis ke orang tua siswa yang belum hadir tanpa menunggu jam cron sore.
