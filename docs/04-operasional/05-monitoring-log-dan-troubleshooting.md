# 🔍 Panduan Monitoring, Log Rotation, & Buku Saku Troubleshooting

Dokumen ini merupakan **Buku Saku Tanggap Darurat (*Incident Response Runbook*)** dan panduan diagnostik sistem untuk memantau performa server, merotasi berkas log Linux, serta menangani kendala teknis lapangan pada sistem **Presensi Barcode & Smart School ERP**.

---

## 📌 Daftar Isi
1. [Pemantauan Beban & Kesehatan Server (System Health Monitoring)](#1-pemantauan-beban--kesehatan-server-system-health-monitoring)
2. [Konfigurasi Rotasi Berkas Log Linux (`logrotate`)](#2-konfigurasi-rotasi-berkas-log-linux-logrotate)
3. [Diagnostik Indikator Heartbeat Scheduler](#3-diagnostik-indikator-heartbeat-scheduler)
4. [Buku Saku Tanggap Darurat (7 Skenario Insiden Kritis)](#4-buku-saku-tanggap-darurat-7-skenario-insiden-kritis)

---

## 1. Pemantauan Beban & Kesehatan Server (System Health Monitoring)

Dalam siklus harian sekolah, terdapat dua periode beban lonjakan (*peak traffic spikes*):
- **Periode Pagi (06.30 – 07.15 WIB)**: Lonjakan proses tulis basis data presensi masuk gerbang dan pemrosesan antrean pesan WhatsApp.
- **Periode Asesmen ZenCBT**: Ratusan hingga ribuan siswa mengerjakan ujian online secara bersamaan yang membebani koneksi bersamaan (*concurrent connections*) PHP-FPM dan MySQL.

### Perintah Cepat Diagnostik Server Linux

```bash
# 1. Pantau penggunaan CPU, RAM, dan proses aktif secara interaktif
htop

# 2. Periksa ketersediaan kapasitas ruang harddisk
df -h

# 3. Periksa ketersediaan memori RAM dan Swap
free -m

# 4. Pantau beban I/O baca-tulis harddisk secara real-time
iostat -xz 1 5

# 5. Pantau koneksi aktif Web Server Nginx
ss -s
```

---

## 2. Konfigurasi Rotasi Berkas Log Linux (`logrotate`)

Secara bawaan, berkas `storage/logs/laravel.log` dan log antrean PM2/Supervisor akan terus bertambah besar seiring berjalannya transaksi presensi harian. Jika dibiarkan berbulan-bulan tanpa rotasi, berkas log dapat mencapai puluhan gigabyte dan menyebabkan harddisk server penuh (*Disk Full*).

### Konfigurasi Logrotate Otomatis

Buat berkas konfigurasi rotasi baru:
```bash
sudo nano /etc/logrotate.d/laravel-absensi
```

Isikan konfigurasi berikut:

```text
/var/www/html/app/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    copytruncate
    create 0664 www-data www-data
}
```

| Parameter | Manfaat Operasional |
| :--- | :--- |
| `daily` | Merotasi berkas log setiap tengah malam. |
| `rotate 14` | Menyimpan riwayat log selama 14 hari terakhir (log hari ke-15 otomatis dihapus). |
| `compress` | Mengompresi log arsip lama ke format `.gz` (menghemat ruang disk hingga 90%). |
| `copytruncate` | Menyalin isi berkas lalu memotongnya (*truncate*) tanpa mematikan proses Laravel/PM2 yang sedang aktif menulis. |
| `create 0664 www-data` | Memastikan berkas log baru tetap memiliki hak izin baca/tulis bagi web server. |

Uji coba konfigurasi logrotate secara manual:
```bash
sudo logrotate -d /etc/logrotate.d/laravel-absensi # Mode simulasi uji coba
sudo logrotate -f /etc/logrotate.d/laravel-absensi # Eksekusi paksa sekarang
```

---

## 3. Diagnostik Indikator Heartbeat Scheduler

Sistem presensi memiliki mekanisme pembuktian keaktifan crontab melalui command `scheduler:heartbeat` ([`SchedulerHeartbeatCommand.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Console/Commands/SchedulerHeartbeatCommand.php)).

### Cara Kerja Heartbeat
1. Crontab server mengeksekusi `php artisan schedule:run` setiap 60 detik.
2. Scheduler memicu `scheduler:heartbeat` untuk menulis timestamp waktu saat ini ke berkas:
   `storage/framework/schedule-heartbeat`
3. Dasbor panel admin Filament membaca timestamp berkas tersebut:
   - Jika selisih waktu **< 3 menit**: Menampilkan banner hijau 🟢 **Scheduler Aktif & Normal**.
   - Jika selisih waktu **> 3 menit**: Menampilkan banner merah 🔴 **Scheduler Mati! Segera Periksa Crontab Server**.

### Diagnostik Jika Banner Berwarna Merah:
```bash
# 1. Periksa apakah berkas heartbeat ada dan periksa waktu modifikasinya
ls -la storage/framework/schedule-heartbeat

# 2. Uji jalankan command heartbeat secara langsung
php artisan scheduler:heartbeat

# 3. Periksa apakah entri crontab terdaftar di user yang tepat
crontab -l
```

---

## 4. Buku Saku Tanggap Darurat (7 Skenario Insiden Kritis)

Berikut adalah panduan tindakan cepat langkah-demi-langkah bagi teknisi sekolah saat menghadapi kendala operasional di lapangan:

---

### 🚨 Insiden 1: Scanner Barcode Berbunyi Beep, tetapi Nama Siswa Tidak Muncul di Layar Kiosk

- **Penyebab Utama**:
  1. Kursor peramban kehilangan fokus dari kotak input pemindaian (*lost focus*).
  2. Pemindai barcode belum diprogram untuk mengirimkan karakter penutup `Enter` (`CR/LF`).
- **Langkah Penanganan Cepat**:
  1. Klik sembarang area pada layar antarmuka Kiosk untuk mengembalikan fokus otomatis JavaScript.
  2. Buka aplikasi teks editor biasa (Text Editor / Terminal). Pindai kartu siswa:
     - Jika nomor barcode muncul tetapi kursor **TIDAK** berpindah ke baris baru di bawahnya: Ambil buku panduan scanner barcode, lalu pindai lembar konfigurasi barcode **`[Add Enter / CR Suffix]`**.
  3. Muat ulang halaman kiosk browser (`Ctrl + Shift + R` atau `F5`).

---

### 🚨 Insiden 2: Siswa Berhasil Scan Presensi, tetapi Pesan WhatsApp Tidak Masuk ke Orang Tua

- **Penyebab Utama**:
  1. Daemon Queue Worker PM2 mati.
  2. Sesi bot WhatsApp di Evolution API terputus (*logged out / connection closed*).
- **Langkah Penanganan Cepat**:
  1. Periksa status queue worker di terminal server:
     ```bash
     pm2 list
     ```
     Jika status `absensi-queue` bertanda *errored* atau *stopped*, hidupkan kembali:
     ```bash
     pm2 restart absensi-queue
     ```
  2. Periksa status koneksi Evolution API:
     Buka **Evolution Manager** di browser. Jika status instance `sekolah-bot` berwarna merah (*close*), ambil HP nomor dinas sekolah dan lakukan **Scan Ulang QR Code**.
  3. Cek apakah ada antrean pesan yang tertahan:
     ```bash
     php artisan queue:failed
     php artisan queue:retry all
     ```

---

### 🚨 Insiden 3: Galat 413 "Payload Too Large" saat Unggah File ZIP Pas Foto Massal

- **Penyebab Utama**:
  Batas ukuran unggah berkas pada web server Nginx masih menggunakan batas bawaan (*default 1 MB*).
- **Langkah Penanganan Cepat**:
  1. Buka konfigurasi Virtual Host Nginx:
     ```bash
     sudo nano /etc/nginx/sites-available/app.sekolah.sch.id
     ```
  2. Tambahkan atau ubah baris berikut di dalam blok `server { ... }`:
     ```nginx
     client_max_body_size 50M;
     ```
  3. Pastikan konfigurasi `php.ini` juga mengizinkan:
     ```ini
     upload_max_filesize = 50M
     post_max_size = 50M
     ```
  4. Muat ulang service:
     ```bash
     sudo nginx -t && sudo systemctl reload nginx
     sudo systemctl restart php8.4-fpm
     ```

---

### 🚨 Insiden 4: Galat 504 "Gateway Timeout" saat Impor Data Excel Ribuan Siswa

- **Penyebab Utama**:
  Batas waktu tunggu eksekusi FastCGI PHP-FPM habis sebelum seluruh ribuan baris data selesai divalidasi dan di-hash oleh Bcrypt.
- **Langkah Penanganan Cepat**:
  1. Pada blok Nginx `location ~ \.php$ { ... }`, tingkatkan parameter timeout:
     ```nginx
     fastcgi_read_timeout 300;
     fastcgi_send_timeout 300;
     ```
  2. Di file `php.ini`, tingkatkan batas eksekusi:
     ```ini
     max_execution_time = 300
     ```
  3. Muat ulang Nginx dan PHP-FPM (`sudo systemctl reload nginx && sudo systemctl restart php8.4-fpm`).

---

### 🚨 Insiden 5: Server Mengalami Respon Lambat / Hang Saat Asesmen ZenCBT Serentak

- **Penyebab Utama**:
  Jumlah siswa yang mengakses ujian secara bersamaan melebihi batas slot proses worker PHP-FPM (`pm.max_children`).
- **Langkah Penanganan Cepat**:
  1. Buka berkas pool PHP-FPM:
     ```bash
     sudo nano /etc/php/8.4/fpm/pool.d/www.conf
     ```
  2. Sesuaikan konfigurasi alokasi proses (untuk server RAM 8 GB):
     ```ini
     pm = dynamic
     pm.max_children = 60
     pm.start_servers = 15
     pm.min_spare_servers = 10
     pm.max_spare_servers = 25
     pm.max_requests = 1000
     ```
  3. Buka konfigurasi MySQL `/etc/mysql/my.cnf` dan tingkatkan batas koneksi:
     ```ini
     max_connections = 300
     ```
  4. Restart service:
     ```bash
     sudo systemctl restart php8.4-fpm
     sudo systemctl restart mysql
     ```

---

### 🚨 Insiden 6: Sesi Pengguna Tiba-Tiba Logout / Muncul Galat "419 Page Expired (CSRF Token)"

- **Penyebab Utama**:
  Sistem mengaktifkan `SESSION_SECURE_COOKIE=true` di berkas `.env`, namun situs diakses menggunakan protokol HTTP biasa (tanpa sertifikat SSL), atau terdapat perbedaan domain pada `SESSION_DOMAIN`.
- **Langkah Penanganan Cepat**:
  1. Jika situs diakses di lingkungan internet publik, pastikan pengguna selalu mengakses via alamat resmi ber-HTTPS (`https://app.sekolah.sch.id`).
  2. Jika sedang diuji di jaringan intranet lokal tanpa sertifikat SSL:
     Buka `.env`, ubah sementara:
     ```ini
     SESSION_SECURE_COOKIE=false
     ```
  3. Jalankan pembersihan cache:
     ```bash
     php artisan optimize:clear
     ```

---

### 🚨 Insiden 7: Harddisk Server Penuh 100% (*Disk Space Full*)

- **Penyebab Utama**:
  Akumulasi berkas log Laravel yang tidak dirotasi atau tumpukan berkas backup lama di folder `database/backups/`.
- **Langkah Penanganan Cepat**:
  1. Cari 10 berkas berukuran terbesar di server:
     ```bash
     sudo find / -type f -size +100M -exec ls -lh {} + 2>/dev/null
     ```
  2. Bersihkan berkas log Laravel yang membengkak secara aman (tanpa menghapus berkas):
     ```bash
     truncate -s 0 /var/www/html/app/storage/logs/laravel.log
     truncate -s 0 /var/www/html/app/storage/logs/queue-*.log
     ```
  3. Bersihkan cache paket APT:
     ```bash
     sudo apt clean && sudo apt autoremove -y
     ```
  4. Pasang konfigurasi `logrotate` sesuai Bab 2 dokumen ini agar insiden tidak terulang kembali.
