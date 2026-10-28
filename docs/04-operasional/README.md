# 🛠️ Modul 04: Panduan Operasional, DevOps, & SOP Server Sekolah

Dokumen ini merupakan **Buku Panduan Operasional (*Operations Runbook & Standard Operating Procedures*)** untuk pengelolaan infrastruktur server, otomasi tugas latar belakang, perangkat keras kiosk gerbang, integrasi WhatsApp Gateway, serta strategi tanggap darurat (*Incident Response*) pada sistem **Presensi Barcode & Smart School ERP**.

---

## 📌 Peta Navigasi Dokumen Operasional

| Dokumen | Topik Pembahasan |
| :--- | :--- |
| [`01-deployment-dan-lingkungan-server.md`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/docs/04-operasional/01-deployment-dan-lingkungan-server.md) | Panduan instalasi server Linux (VPS / HestiaCP), vhost Nginx (upload limit 50M), SSL HTTPS, izin hak akses direktori, hardening `.env`, dan skrip zero-downtime deployment. |
| [`02-queue-worker-dan-otomasi-scheduler.md`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/docs/04-operasional/02-queue-worker-dan-otomasi-scheduler.md) | Manajemen antrean background process WhatsApp via PM2 / Supervisor, otomasi Crontab Laravel (auto-alpa, rekap harian wali kelas, resume kepala sekolah, sync sholat). |
| [`03-operasional-kiosk-dan-whatsapp-gateway.md`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/docs/04-operasional/03-operasional-kiosk-dan-whatsapp-gateway.md) | Konfigurasi hardware scanner barcode USB, Chromium Kiosk Fullscreen mode, autostart on boot komputer gerbang, dan manajemen bot WhatsApp Evolution API. |
| [`04-backup-restore-dan-disaster-recovery.md`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/docs/04-operasional/04-backup-restore-dan-disaster-recovery.md) | Prosedur backup terpadu (database MySQL + storage foto siswa), skrip bash rotasi backup otomatis, sinkronisasi cloud, dan runbook pemulihan bencana (*Disaster Recovery*). |
| [`05-monitoring-log-dan-troubleshooting.md`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/docs/04-operasional/05-monitoring-log-dan-troubleshooting.md) | Buku saku tanggap darurat: penanganan scanner tidak merespons, queue WA macet, scheduler mati, rotasi log Linux (`logrotate`), dan lonjakan beban asesmen ZenCBT. |

---

## 🏛️ Topologi Infrastruktur Server Produksi

Sistem ERP ini menghubungkan server web pusat, antrean latar belakang, gateway pihak ketiga, dan perangkat kiosk lokal di lapangan:

```mermaid
flowchart TD
    subgraph KioskClient [Stasiun Fisik Sekolah (Pos Satpam & Perpus)]
        K1[Kiosk Scanner Gerbang\n- Barcode Scanner USB HID\n- Chromium Kiosk Fullscreen\n- Audio Buzzer Feedback]
        K2[Kiosk Perpustakaan\n- Barcode Scanner Anggota & Buku]
    end

    subgraph ProductionServer [Server Produksi (VPS / HestiaCP Linux)]
        NGINX[Reverse Proxy / Web Server Nginx\n- SSL Let's Encrypt\n- client_max_body_size 50M]
        PHP[PHP 8.2 / 8.4-FPM Engine\n- Laravel 12 + Livewire v3 + Filament v4]
        CRON[Linux Crontab\n- php artisan schedule:run\n- Heartbeat, Auto-Alpa, Rekap WA]
        PM2[PM2 / Supervisor Daemon\n- artisan queue:work (absensi-queue)\n- Broadcast WA Asinkron]
        DB[(MySQL 8 / MariaDB\n- InnoDB UTF8MB4\n- UUID Primary Keys)]
        STORAGE[Storage Disk: storage/app/public\n- Pas Foto Siswa & Guru\n- Arsip Surat Izin & Berkas]
    end

    subgraph ExternalGateways [Layanan & Gateway Eksternal]
        WA[WhatsApp Gateway (Evolution API)\n- Kirim Notifikasi Presensi Ortu\n- Rekap Harian Wali & Kepsek]
        CBT[ZenCBT Engine Bridge\n- Server Ujian Siswa (Port 7879)]
        CF[Cloudflare CDN & Turnstile\n- Anti-Bot Admin Login]
    end

    K1 -->|HTTP/HTTPS LAN / Cloud| NGINX
    K2 -->|HTTP/HTTPS LAN / Cloud| NGINX
    NGINX --> PHP
    PHP --> DB
    PHP --> STORAGE
    CRON --> PHP
    PHP -->|Push Job Antrean| DB
    PM2 -->|Process Queue Job| PHP
    PM2 -->|API HTTP Request| WA
    PHP -.->|Bridge API| CBT
    NGINX -.->|Turnstile Verification| CF
```

---

## 📋 Standard Operating Procedures (SOP) Rutin SysAdmin

Untuk menjamin layanan presensi dan ERP berjalan 100% tanpa gangguan, administrator sistem dan teknisi sekolah menjalankan checklist berkala:

### 1. Checklist Harian

| Waktu | Komponen | Aktivitas Pemantauan |
| :--- | :--- | :--- |
| **06.00 WIB** | **Koneksi WhatsApp Bot** | Buka Evolution Manager / Dasbor WA. Pastikan status bot bertanda 🟢 *Connected / Ready*. Lakukan reconnect via QR jika session terputus. |
| **06.15 WIB** | **Stasiun Kiosk Gerbang** | Nyalakan komputer gerbang/pos satpam. Pastikan browser otomatis masuk mode fullscreen kiosk, scanner membaca barcode kartu uji, dan speaker audio berbunyi chime. |
| **06.30 - 07.15 WIB** | **Jam Sibuk Presensi Masuk** | Pantau menu **Monitoring Presensi Live** di panel admin. Pastikan antrean log masuk bertambah real-time dan tidak ada lonjakan beban CPU/RAM. |
| **07.35 WIB** | **Notifikasi Pagi Siswa Belum Hadir** | Verifikasi bahwa scheduler `presensi:send-unattended-notification` berhasil berjalan dan pesan WA terkirim ke ortu siswa yang belum scan. |
| **12.00 - 13.00 WIB** | **Presensi Sholat Dhuhur** | Pastikan stasiun scan sholat di mushola/masjid aktif bagi siswa muslim. |
| **15.00 WIB** | **Auto-Alpa Processing** | Scheduler otomatis menandai siswa tanpa keterangan menjadi status `Alpa`. |
| **16.00 - 16.30 WIB** | **Laporan Rekap Harian** | Verifikasi pengiriman rekap presensi kelas ke WA masing-masing wali kelas dan rekap sekolah ke WA Kepala Sekolah. |

### 2. Checklist Mingguan
- **Pembersihan Log**: Cek ukuran `storage/logs/laravel.log` dan `storage/logs/queue-*.log`.
- **Verifikasi File Backup**: Pastikan file backup harian di folder `database/backups/` terbentuk sempurna dan ukurannya wajar (tidak 0 bytes).
- **Restart Queue Worker**: Jalankan `pm2 restart absensi-queue` untuk merilis potensi *memory leak* dari pemrosesan ratusan job antrean.

### 3. Checklist Bulanan
- **Latihan Pemulihan Bencana (*Disaster Recovery Drill*)**: Lakukan uji coba *restore* data backup ke server staging / mesin pengujian lokal.
- **Pembaruan Paket Sistem**: Jalankan `apt update && apt upgrade` pada server Linux di luar jam dinas sekolah (akhir pekan).
- **Arsip Database**: Periksa ketersediaan ruang harddisk (`df -h`). Pastikan partisi `/` memiliki sisa kapasitas minimal 20%.

---

## 🚨 Matriks Tingkat Kedaruratan (Severity Levels)

| Level | Kondisi Insiden | Target Waktu Tanggap (SLA) | Langkah Awal |
| :---: | :--- | :---: | :--- |
| **P1 (Kritis)** | Kiosk gerbang macet total saat jam masuk (06.30 - 07.15 WIB), database error, atau web server down. | **< 5 Menit** | Beralih ke presensi manual darurat oleh guru piket; restart service `nginx` & `mysql`; cek `laravel.log`. |
| **P2 (Mayor)** | WhatsApp Gateway disconnect; pesan presensi tidak terkirim ke ortu siswa, tapi presensi di kiosk tetap tercatat. | **< 30 Menit** | Scan ulang QR code di Evolution Manager; cek service PM2 `absensi-queue`. |
| **P3 (Moderat)** | Fitur cetak kartu / export Excel timeout; unggah foto siswa gagal. | **< 2 Jam** | Cek batas `upload_max_filesize`, timeout FastCGI, atau bersihkan cache. |
| **P4 (Minor)** | Kesalahan tampilan minor, typo teks panduan, atau perbaikan kecil antarmuka. | **< 24 Jam** | Ditangani pada jadwal deployment reguler sore hari. |
