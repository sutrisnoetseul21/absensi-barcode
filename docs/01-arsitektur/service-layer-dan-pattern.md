# 🧩 Service Layer, Action Classes, & Observers

Dokumen ini mendokumentasikan pola desain (*design patterns*) yang digunakan untuk memisahkan logika bisnis dari Controller dan Komponen Livewire.

---

## 1. Action Classes (`app/Actions`)

Action class bertugas menangani satu tugas bisnis spesifik (*Single Responsibility Principle*):

| Action Class | Fungsi Utama |
| :--- | :--- |
| [`ProcessScanAction`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Actions) | Menerima barcode scan dari kiosk gerbang, memvalidasi jam datang/pulang, mendeteksi scan duplikat dalam cooldown, dan menyimpan catatan ke tabel `attendances`. |
| [`ProcessScanSholatDhuhurAction`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Actions/ProcessScanSholatDhuhurAction.php) | Memvalidasi identitas siswa dan mencatat presensi sholat dhuhur/jumat harian. |

---

## 2. Dedicated Services (`app/Services`)

Service layer menangani agregasi data kompleks, pelaporan, dan integrasi pihak ketiga:

| Service | Peran & Tanggung Jawab |
| :--- | :--- |
| [`DailyClassReportService`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Services/DailyClassReportService.php) | Mengumpulkan statistik kehadiran per kelas (hadir, terlambat, izin, sakit, alpa) untuk dikirim ke wali kelas. |
| [`SchoolSummaryReportService`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Services/SchoolSummaryReportService.php) | Mengumpulkan ringkasan kehadiran seluruh sekolah untuk dikirim ke Kepala Sekolah. |
| [`UnattendedAttendanceNotificationService`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Services/UnattendedAttendanceNotificationService.php) | Mendeteksi siswa yang belum hadir pada batas jam masuk dan mengirimkan peringatan ke nomor WA orang tua. |
| [`WhatsAppGatewayService`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Services/WhatsAppGatewayService.php) | Abstraksi pengiriman pesan WhatsApp via API gateway dengan logging otomatis ke tabel `whats_app_notification_logs`. |
| [`PresensiSholatDhuhurService`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Services/PresensiSholatDhuhurService.php) | Logika rekapitulasi, matriks bulanan, dan status sholat siswa. |

---

## 3. Eloquent Observers (`app/Observers`)

Observer digunakan untuk mendengarkan perubahan model dan memicu efek samping (*side effects*) otomatis:
- [`PresensiObserver`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Observers/PresensiObserver.php): Memantau pembuatan catatan kehadiran baru untuk mentrigger notifikasi real-time ke orang tua sesuai template yang aktif.
