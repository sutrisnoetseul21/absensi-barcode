# 💬 Pengaturan Notifikasi WhatsApp & Broadcast Presensi

Dokumen ini menjelaskan modul pengaturan pesan WhatsApp di Portal Presensi ([`App\Livewire\PortalPresensi\SettingNotifikasi`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Livewire/PortalPresensi/SettingNotifikasi.php)), otomatisasi pengiriman via background scheduler, dan sistem proteksi kuota kirim harian.

---

## 1. Konfigurasi Template Pesan Per Status

Sistem memungkinkan admin mengatur redaksi pesan berbeda untuk setiap status presensi:

| Status Target | Default Waktu Kirim | Deskripsi Pesan |
| :--- | :--- | :--- |
| **`hadir`** | Real-time (saat scan kiosk) | Mengabarkan bahwa siswa telah tiba dan masuk di sekolah dengan selamat. |
| **`terlambat`** | Real-time (saat scan kiosk) | Mengabarkan bahwa ananda datang melebihi jam masuk beserta info menit keterlambatan. |
| **`belum`** | Terjadwal (`07:30:00` WIB) | Peringatan ke orang tua bahwa hingga jam cut-off siswa belum tercatat presensi. |
| **`sholat_dhuhur`**| Selesai jam sholat | Konfirmasi catatan keikutsertaan ibadah sholat dhuhur/jumat berjamaah. |

---

## 2. Dynamic Placeholders Template Pesan

Template pesan mendukung placeholder dinamis yang otomatis diisi data riil siswa saat dikirim:

```text
Assalamu'alaikum Wr. Wb.
Bpk/Ibu Orang Tua/Wali,

Kami informasikan bahwa ananda :
Nama Siswa : *{nama_siswa}*
Kelas      : {kelas}
Tanggal    : {tanggal}
Pukul      : {jam}
Status     : *{status_kehadiran}*

Wali Kelas : {nama_wali_kelas}
Sekolah    : *{nama_sekolah}*

_Mohon konfirmasi atau balas pesan ini jika berhalangan hadir._
Wassalamu'alaikum Wr. Wb.
```

---

---

## 3. Otomatisasi Background Scheduler (Auto-Send)

Pemberitahuan siswa belum hadir dan belum sholat dhuhur berjalan otomatis melalui Laravel Scheduler:

### A. Notifikasi Siswa Belum Hadir (Presensi Pagi)
1. Kolom `is_auto_send = true` diaktifkan di tabel `presensi_notification_settings`.
2. Jam eksekusi diatur pada `auto_send_time = '07:30:00'`.
3. Console command yang dijalankan scheduler:
   ```bash
   php artisan presensi:send-unattended-notification
   ```
4. Service [`UnattendedAttendanceNotificationService`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Services/UnattendedAttendanceNotificationService.php) memfilter siswa yang belum memiliki record kehadiran di hari ini dan mendispatch job `SendWhatsAppNotificationJob` secara asinkron.

### B. Notifikasi Siswa Belum Absen Sholat Dhuhur
1. Kolom `sholat_dhuhur_notif_is_auto_send = true` diaktifkan pada pengaturan notifikasi sholat dhuhur.
2. Jam eksekusi diatur pada `sholat_dhuhur_notif_auto_send_time = '13:00:00'`.
3. Console command yang dijalankan scheduler:
   ```bash
   php artisan presensi:send-sholat-dhuhur-notification
   ```
4. Service [`UnattendedSholatDhuhurNotificationService`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Services/UnattendedSholatDhuhurNotificationService.php) memvalidasi hari sholat aktif (bukan hari libur/minggu), memfilter siswa Muslim yang belum absen sholat dhuhur, lalu mengirim notifikasi WA ke orang tua via queue job.

---

## 4. Proteksi Kuota Broadcast Manual (*Rate Limiting*)

Untuk mencegah nomor WhatsApp sekolah diblokir atau di-banned oleh pihak WhatsApp karena terindikasi spam:
- **Batas Maksimal**: Maksimal **5 kali broadcast manual** per hari untuk notifikasi belum hadir (`manual_send_count < 5`) maupun notifikasi sholat dhuhur (`sholat_dhuhur_manual_send_count < 5`).
- **Pengecekan**: Method `canSendManualToday()` dan `canSendSholatDhuhurManualToday()` memvalidasi tanggal broadcast hari ini.
- **Reset Otomatis**: Counter kuota otomatis kembali ke `0` saat pergantian hari kalender.
- **Audit Log**: Setiap pesan yang terkirim maupun yang gagal tersimpan dengan rapi di tabel `whatsapp_notification_logs`.
