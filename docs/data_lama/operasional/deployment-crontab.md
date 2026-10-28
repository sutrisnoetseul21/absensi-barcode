# ⏰ Panduan Deployment & Konfigurasi Crontab

Dokumen ini memuat petunjuk konfigurasi penjadwalan tugas (*task scheduler*) dan background process di server Linux.

---

## 1. Konfigurasi Crontab Sistem

Agar seluruh scheduler otomatisasi Laravel dapat berjalan setiap menit, daftarkan entry berikut ke dalam crontab server pengguna:

```bash
crontab -e
```

Tambahkan baris berikut:
```cron
* * * * * cd /home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode && php artisan schedule:run >> /dev/null 2>&1
```

---

## 2. Daftar Tugas Terjadwal (`routes/console.php`)

| Jadwal | Perintah / Handler | Deskripsi |
| :--- | :--- | :--- |
| **Setiap Menit** | `schedule:run` | Engine evaluasi waktu scheduler Laravel. |
| **Pagi (07:30 WIB)** | `presensi:send-unattended-notification` | Mengirim peringatan WA ke ortu siswa yang belum scan masuk. |
| **Sore (15:00 WIB)** | Auto-Alpa Processing | Menandai siswa tidak hadir tanpa keterangan menjadi Alpa. |
| **Sore (16:00 WIB)** | Rekap Harian Kelas | Mengirim laporan rekap kehadiran ke WhatsApp wali kelas. |
| **Sore (16:30 WIB)** | Rekap Sekolah | Mengirim resume tingkat kehadiran ke WhatsApp Kepala Sekolah. |

---

## 3. Verifikasi Scheduler
Untuk menguji dan melihat jadwal scheduler yang aktif:
```bash
php artisan schedule:list
```

Untuk menguji eksekusi manual tanpa menunggu waktu cron:
```bash
php artisan presensi:send-unattended-notification --force
```
