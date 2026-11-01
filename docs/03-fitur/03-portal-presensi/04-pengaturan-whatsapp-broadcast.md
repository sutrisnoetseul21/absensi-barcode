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

## 3. Hierarki Saklar Notifikasi (Master Switch & Scheduler)

Sistem menerapkan hierarki kontrol dua lapis untuk menjaga kontrol admin atas lalu lintas pesan WhatsApp:

1. **Toggle 1: `Aktifkan Fitur Notifikasi` (`is_active` - Master Switch)**:
   - Berfungsi sebagai saklar utama di seluruh ekosistem aplikasi.
   - **Jika OFF**:
     - Tombol cepat `[WA Ortu]` / `[Kirim WA]` di `/portal-guru/akademik`, `/portal-presensi`, dan `/portal-guru/sholat-dhuhur` **otomatis disembunyikan**.
     - Guru maupun staf presensi tidak dapat mengirim pesan personal ke nomor orang tua siswa.
     - Background scheduler otomatis juga otomatis tidak akan mengeksekusi pengiriman.
   - **Jika ON**:
     - Tombol cepat `[WA Ortu]` aktif dan dapat digunakan guru/wali kelas untuk mengirim pesan secara personal.

2. **Toggle 2: `Kirim Otomatis Notifikasi` (`is_auto_send` - Scheduler Switch)**:
   - Khusus mengatur apakah **background scheduler/cron job** mengirim broadcast otomatis massal saat jam cut-off tercapai.
   - Jika Toggle 1 **ON** tetapi Toggle 2 **OFF**, robot scheduler **TIDAK** akan mengirim pesan massal otomatis, namun guru/wali kelas **tetap dapat mengirim pesan secara manual per siswa** melalui tombol di tabel.

---

## 4. Otomatisasi Background Scheduler (Auto-Send)

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

## 5. Proteksi Kuota Broadcast Manual (*Rate Limiting*)

Untuk mencegah nomor WhatsApp sekolah diblokir atau di-banned oleh pihak WhatsApp karena terindikasi spam:
- **Batas Maksimal**: Maksimal **5 kali broadcast manual** per hari untuk notifikasi belum hadir (`manual_send_count < 5`) maupun notifikasi sholat dhuhur (`sholat_dhuhur_manual_send_count < 5`).
- **Pengecekan**: Method `canSendManualToday()` dan `canSendSholatDhuhurManualToday()` memvalidasi tanggal broadcast hari ini.
- **Reset Otomatis**: Counter kuota otomatis kembali ke `0` saat pergantian hari kalender.
- **Audit Log**: Setiap pesan yang terkirim maupun yang gagal tersimpan dengan rapi di tabel `whatsapp_notification_logs`.


---

## 6. Panel Manajemen Notifikasi WhatsApp Terpusat di Filament Admin (`/admin/manajemen-notifikasi-wa`)

Sebagai pusat kendali komunikasi WhatsApp seluruh sekolah, halaman [`ManajemenNotifikasiWaPage.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Filament/Presensi/Pages/ManajemenNotifikasiWaPage.php) di panel Filament Admin (`http://localhost:8001/admin/manajemen-notifikasi-wa`) mengintegrasikan seluruh kanal pesan ke dalam satu dasbor terpadu dengan 4 tab utama:

### 6.1 Tab Presensi Harian & Sholat Dhuhur
- Mengatur notifikasi kehadiran masuk sekolah, keterlambatan (beserta durasi menit), rekap belum hadir, dan konfirmasi presensi sholat dhuhur / kepulangan.

### 6.2 Tab Notifikasi SPIKAP (Sistem Pengaduan Siswa)
- **Toggle & Template Pesan**:
  1. **Laporan Aduan Darurat**: Dikirimkan segera ke Wali Kelas, Guru BK, dan Kepala Sekolah saat siswa menekan tombol SOS/Aduan Darurat.
  2. **Laporan Aduan Biasa**: Dikirimkan ke Wali Kelas dan Guru BK untuk investigasi lanjutan.
  3. **Pesan Apresiasi Pengirim (Siswa)**: Mengirimkan ucapan terima kasih dan nomor tiket pelaporan ke WhatsApp siswa.
  4. **Pesan Apresiasi ke Orang Tua**: Memberitahukan orang tua bahwa sekolah mengapresiasi keberanian siswa dalam melapor.
  5. **Update Status Kasus (ke Siswa)**: Otomatis terkirim saat status berubah menjadi Diproses, Ditindaklanjuti, atau Selesai.
  6. **Update Status Kasus (ke Orang Tua)**: Salinan pembaruan status ke WhatsApp orang tua.
- **Placeholder Dinamis Tersedia**: `{nama_siswa}`, `{nisn}`, `{kelas}`, `{kode_tiket}`, `{kategori}`, `{deskripsi}`, `{status}`, `{catatan_guru}`.

### 6.3 Tab Notifikasi Guru Wali (SI-WALI)
- **Toggle & Template Pesan**:
  1. **Pengajuan Bimbingan / Cerita Siswa**: Notifikasi instan ke WhatsApp Guru Wali saat siswa asuhnya mengirimkan pesan curhat/konsultasi baru di aplikasi.
  2. **Balasan Konsultasi Guru Wali**: Terkirim ke WhatsApp siswa saat Guru Wali merespons pesan bimbingan.
  3. **Salinan Balasan ke Orang Tua**: Opsi tembusan ke orang tua siswa mengenai progres pendampingan guru wali.
  4. **Jadwal Temu Bimbingan / Tatap Muka**: Konfirmasi jadwal resmi (tanggal, jam, dan lokasi) ke siswa asuh.
- **Placeholder Dinamis Tersedia**: `{nama_siswa}`, `{nisn}`, `{kelas}`, `{nama_guru}`, `{kategori}`, `{pesan}`, `{tanggal_temu}`, `{jam_temu}`, `{tempat_temu}`.

### 6.4 Penyimpanan & Sinkronisasi Sistem
- Seluruh nilai switch dan template teks disimpan ke tabel `presensi_notification_settings` dengan key prefix `spikap_*` dan `guru_wali_*`.
- Di-sinkronkan otomatis ke model singleton `SpikapNotifSetting` sehingga seluruh modul backend konsisten membaca konfigurasi terbaru.
