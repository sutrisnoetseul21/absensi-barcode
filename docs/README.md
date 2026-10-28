# 📚 Dokumentasi Teknis Sistem Presensi & ERP Sekolah

Selamat datang di repositori dokumentasi sistem **Presensi Barcode & Smart School ERP**. Dokumentasi ini dirancang dengan struktur terstandarisasi untuk memudahkan pemahaman arsitektur, basis data, implementasi fitur, pemeliharaan operasional, serta standarisasi pengembangan.

---

## 🗺️ Peta Navigasi Dokumentasi

```text
docs/
├── README.md                           # Peta navigasi utama
├── 00-konteks/                         # Pemahaman sistem & fondasi
│   ├── gambaran-sistem.md              # Visi, latar belakang, dan cakupan sistem
│   ├── tech-stack.md                   # Spesifikasi teknologi & dependensi
│   └── glosarium.md                    # Istilah teknis & domain sekolah
├── 01-arsitektur/                      # Desain perangkat lunak
│   ├── arsitektur-sistem.md            # Arsitektur modul portal & kiosk scan
│   ├── role-dan-permission.md          # Model akses RBAC & otorisasi
│   └── service-layer-dan-pattern.md    # Action, Services, dan Observer pattern
├── 02-database/                        # Struktur & manajemen data (Modular AI-Friendly)
│   ├── skema-dan-relasi.md             # Master Index & prinsip arsitektur global
│   ├── kamus-data.md                   # Direktori A-Z pencarian cepat 87 tabel
│   ├── panduan-migrasi-seeder.md       # Urutan migrasi, data master seeder, & akun bawaan
│   ├── 01-akademik-dan-users.md        # Users, teachers, students, classes, enrollments
│   ├── 02-presensi-dan-kiosk.md        # Attendances, scan_logs, leave_requests, sholat
│   ├── 03-whatsapp-gateway.md          # WhatsApp settings, notification templates, logs
│   ├── 04-guru-wali-dan-bk.md          # Kelompok guru wali, jurnal, badge, spikap, konseling
│   ├── 05-perpustakaan.md              # Bukus, eksemplar, peminjamans, ddc, inventaris
│   ├── 06-cbt-dan-akademik.md          # Mata pelajaran, ujian, cbt profiles, nilai
│   └── 07-portal-web-dan-pengaduan.md  # Web settings, sarpras, galeri, pengaduan, alumni
├── 03-fitur/                           # Spesifikasi modul fungsional per folder
│   ├── 01-tampilan-web-awal/           # 🌟 Landing page, profil, 11 sections, & CMS
│   ├── 02-portal-siswa/                # 🎒 Dashboard siswa, presensi, izin, spikap, & kartu
│   ├── 03-portal-presensi/             # 📟 Kiosk scan gerbang, monitoring live, rekap, & WA
│   ├── 04-portal-perpustakaan/         # 📚 Sirkulasi buku, katalog DDC, kiosk, & peminjaman paket
│   ├── 05-portal-guru/                 # 👨‍🏫 Presensi kelas, guru wali, BK, sholat, & nilai
│   ├── 06-cbt-dan-akademik/            # 📝 Integrasi ZenCBT, asesmen, sinkron nilai, & kartu
│   ├── 07-portal-admin/                # ⚙️ Pusat kendali admin Filament, setting sekolah, & branding
│   └── 08-master-data-siswa-dan-guru/  # 👥 Core ERP: Siswa, GTK, skema DB, import massal, & integrasi
├── 04-operasional/                     # DevOps, pemeliharaan server, & SOP
│   ├── README.md                       # Peta navigasi operasional, topologi server, & SOP harian
│   ├── 01-deployment-dan-lingkungan-server.md # Setup Nginx (50M), SSL, izin Linux, & deploy.sh
│   ├── 02-queue-worker-dan-otomasi-scheduler.md # Antrean PM2/Supervisor & Crontab scheduler
│   ├── 03-operasional-kiosk-dan-whatsapp-gateway.md # Setup hardware scanner & Evolution API
│   ├── 04-backup-restore-dan-disaster-recovery.md # Skrip backup otomatis (DB + foto) & DRP
│   └── 05-monitoring-log-dan-troubleshooting.md # Buku saku tanggap darurat & logrotate
├── 05-keputusan/                       # Architecture Decision Records (ADR)
│   ├── README.md                       # Indeks catatan keputusan arsitektur
│   ├── 0001-single-auth-portal.md      # Keputusan penggunaan single authentication
│   ├── 0002-hard-delete-vs-arsip.md    # Kebijakan retensi data vs soft delete
│   └── 0003-arsitektur-wa-gateway.md   # Integrasi & failover gateway WhatsApp
├── 06-template/                        # Standarisasi & templat kerja
│   ├── template-adr.md                 # Format dokumentasi keputusan teknis baru
│   └── template-fitur-baru.md          # Format Product Requirements Document (PRD)
├── 07-aplikasi-mobile/                 # 📱 Perencanaan Aplikasi Mobile Flutter (Portal Siswa & Guru)
│   ├── README.md                       # Peta umum arsitektur & integrasi mobile
│   ├── 01-arsitektur-dan-tech-stack-mobile.md # Tech stack Flutter & state management
│   ├── 02-cakupan-menu-dan-spesifikasi-fitur.md # Fitur terpilih Portal Siswa & Guru
│   ├── 03-desain-rest-api-laravel-sanctum.md # Desain endpoint REST API & Sanctum
│   └── 04-roadmap-dan-tahapan-implementasi.md # Roadmap bertahap dari API ke APK
└── data_lama/                          # Arsip catatan historis sebelumnya
```

---

## 📌 Ringkasan Isi Direktori

| Direktori | Deskripsi |
| :--- | :--- |
| [`00-konteks`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/docs/00-konteks) | Berisi latar belakang bisnis sekolah, stack teknologi yang digunakan, serta kamus istilah (*glossary*). |
| [`01-arsitektur`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/docs/01-arsitektur) | Menjelaskan bagaimana komponen sistem saling terhubung, manajemen hak akses pengguna, serta arsitektur kode. |
| [`02-database`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/docs/02-database) | Referensi lengkap skema basis data, pemetaan relasi foreign key, dan tata cara eksekusi database seeder. |
| [`03-fitur`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/docs/03-fitur/README.md) | Dokumentasi mendalam 8 modul fungsional (Web Publik, Portal Siswa, Presensi Kiosk, Perpustakaan, Portal Guru, ZenCBT, Portal Admin, dan Core Master Siswa & Guru). |
| [`04-operasional`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/docs/04-operasional/README.md) | Petunjuk pemeliharaan server produksi, konfigurasi crontab scheduler, prosedur backup, dan tanggap insiden. |
| [`05-keputusan`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/docs/05-keputusan) | Kumpulan **Architecture Decision Records (ADR)** yang mendokumentasikan alasan di balik setiap keputusan teknis penting. |
| [`06-template`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/docs/06-template) | Cetak biru (*boilerplate template*) untuk penulisan PRD fitur baru dan pembuatan catatan ADR baru. |
| [`07-aplikasi-mobile`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/docs/07-aplikasi-mobile/README.md) | Perencanaan arsitektur aplikasi mobile berbasis **Flutter**, spesifikasi endpoint REST API Laravel Sanctum, dan cakupan menu terpilih (Portal Siswa & Portal Guru). |
| [`data_lama`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/docs/data_lama) | Arsip catatan fase pengembangan awal yang tetap dipertahankan untuk referensi historis. |

---

## 💡 Panduan Kontribusi Dokumentasi

1. **Membuat Fitur Baru**: Gunakan [`06-template/template-fitur-baru.md`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/docs/06-template/template-fitur-baru.md) sebelum memulai koding.
2. **Membuat Perubahan Desain Arsitektur**: Catat pertimbangan dan konsekuensinya di [`05-keputusan/`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/docs/05-keputusan) menggunakan template ADR.
3. **Memperbarui Skema Data**: Pastikan dokumen [`02-database/kamus-data.md`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/docs/02-database/kamus-data.md) dan relasi selalu disinkronkan setelah membuat migration baru.
