# 🏛️ Arsitektur Sistem & Multi-Portal

Dokumen ini menjelaskan struktur arsitektur aplikasi, pemisahan antarmuka (portal), alur request data, dan integrasi antar komponen.

---

## 1. Topologi Antarmuka (Multi-Portal)

Sistem dirancang dengan pendekatan modular berbasis peran pengguna yang dibagi menjadi beberapa portal:

```text
                                  +------------------------------+
                                  |      Web Browser Client      |
                                  +------------------------------+
                                                 |
                                     (HTTP Routing & Middleware)
                                                 v
           +-------------------------------------+-------------------------------------+
           |                                     |                                     |
           v                                     v                                     v
+-----------------------+             +-----------------------+             +-----------------------+
|  Panel Admin Filament |             |      Portal Guru      |             |   Kiosk Mode Client   |
|       (/admin)        |             |     (/portal-guru)    |             |  (/attendance-kiosk)  |
| - Super Admin         |             | - Wali Kelas          |             | - Scan Mandiri Masuk  |
| - Pengaturan Sekolah  |             | - Guru Pengajar       |             | - Scan Mandiri Pulang |
| - Manajemen User      |             | - Sholat Dhuhur Input |             | - Kiosk Sholat Dhuhur |
+-----------------------+             +-----------------------+             +-----------------------+
           |                                     |                                     |
           +-------------------------------------+-------------------------------------+
                                                 |
                                                 v
                                  +------------------------------+
                                  |     Controller / Livewire    |
                                  +------------------------------+
                                                 |
                                                 v
                                  +------------------------------+
                                  |    Action & Service Layer    |
                                  +------------------------------+
                                                 |
                                                 v
                                  +------------------------------+
                                  |       Eloquent Database      |
                                  +------------------------------+
```

### Daftar Portal:
1. **Admin Master (`/admin`)**:
   Dibangun dengan Filament v4 untuk Super Admin dan staf pengelola data master (Tahun Ajaran, Pengaturan Sekolah, Master Siswa, Master Guru).
2. **Portal Presensi & Kiosk (`/portal-presensi` & `/attendance-kiosk`)**:
   Dikhususkan untuk monitor kehadiran harian, penanganan rekap presensi kelas, kiosk scan barcode gerbang sekolah, dan pengaturan notifikasi WA.
3. **Portal Guru & Wali Kelas (`/portal-guru`)**:
   Antarmuka Livewire responsif untuk guru menginput keterangan kehadiran kelas, presensi sholat harian, dan monitoring perkembangan siswa bimbingan.
4. **Portal SPIKAP & Bimbingan Konseling (`/spikap`)**:
   Portal terisolasi untuk tim BK dan wali kelas menangani keluhan, aduan pelanggaran, dan agenda sesi konseling.
5. **Portal Publik & Web Profil (`/`)**:
   Halaman beranda sekolah, layanan pengaduan masyarakat, tracer study alumni, dan katalog perpustakaan publik.

---

## 2. Pola Penanganan Request Kiosk (High Performance)

Kiosk presensi menerima ribuan scan kartu dalam jendela waktu 30-45 menit setiap pagi:
1. **Scanner Input**: Input scanner berupa string barcode dikirim via WebSocket/Livewire event tanpa refresh halaman.
2. **Action Processing**: Dikelola oleh dedicated action class [`app/Actions/ProcessScanAction.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Actions) dan [`app/Actions/ProcessScanSholatDhuhurAction.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Actions/ProcessScanSholatDhuhurAction.php).
3. **Event Dispatching**: Presensi yang tercatat memicu event/observer untuk pembaruan status dan pengiriman pesan WhatsApp secara asinkron (queue/scheduler).
