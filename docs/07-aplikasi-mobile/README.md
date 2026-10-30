# 📱 Dokumentasi Resmi Aplikasi Mobile (Flutter)

Selamat datang di direktori dokumentasi teknis **Aplikasi Mobile Presensi & Portal Smart School** berbasis Flutter.

Direktori ini dirancang khusus dengan pola terstruktur (mirip dengan dokumentasi backend Laravel) agar pengembang dan AI Agent dapat langsung memahami konteks proyek secara cepat saat fokus pengerjaan berpindah ke sisi mobile Android.

---

## 🗺️ Peta Navigasi Dokumen

| No | Berkas Panduan | Topik Pembahasan |
| :-: | :--- | :--- |
| **01** | [`01-arsitektur-dan-struktur-kode.md`](file:///home/sutris-remote/Documents/projek-laravel/apk-android-presensi/docs/01-arsitektur-dan-struktur-kode.md) | Desain arsitektur Flutter, pola MVVM / Feature-first, Riverpod, Dio interceptor, dan hierarki folder `lib/`. |
| **02** | [`02-konfigurasi-eksklusif-per-sekolah.md`](file:///home/sutris-remote/Documents/projek-laravel/apk-android-presensi/docs/02-konfigurasi-eksklusif-per-sekolah.md) | **Model Eksklusif**: Mekanisme injeksi alamat server (`BASE_URL`), warna tema, dan logo sekolah secara langsung (Beda Sekolah = Beda File APK). |
| **03** | [`03-modul-portal-siswa.md`](file:///home/sutris-remote/Documents/projek-laravel/apk-android-presensi/docs/03-modul-portal-siswa.md) | Spesifikasi teknis Portal Siswa: Kartu Pelajar Digital Barcode Code 128, rekap kehadiran, dan form izin/sakit. |
| **04** | [`04-modul-portal-guru.md`](file:///home/sutris-remote/Documents/projek-laravel/apk-android-presensi/docs/04-modul-portal-guru.md) | Spesifikasi teknis Portal Guru: Presensi kelas cepat, presensi sholat dhuhur massal mushola, dan persetujuan izin. |
| **05** | [`05-panduan-build-dan-release-apk.md`](file:///home/sutris-remote/Documents/projek-laravel/apk-android-presensi/docs/05-panduan-build-dan-release-apk.md) | Prosedur kompilasi APK release per sekolah (`--split-per-abi`), optimasi ukuran file, dan konfigurasi Android Studio. |
| **06** | [`06-implementasi-unified-login-dan-identitas-dinamis.md`](file:///home/sutris-remote/Documents/projek-laravel/apk-android-presensi/docs/06-implementasi-unified-login-dan-identitas-dinamis.md) | Layar Login Terpadu (*Unified Login*), logo & nama sekolah dinamis dari database, serta pemisahan alur portal siswa vs guru. |
| **07** | [`07-rencana-bertahap-portal-guru.md`](file:///home/sutris-remote/Documents/projek-laravel/apk-android-presensi/docs/07-rencana-bertahap-portal-guru.md) | **Master Plan Portal Guru Android**: Desain kotak-kotak tanpa sidebar kiri beserta tahapan 9 menu secara berurutan. |
| **08** | [`08-plan-menu-1-presensi-guru.md`](file:///home/sutris-remote/Documents/projek-laravel/apk-android-presensi/docs/08-plan-menu-1-presensi-guru.md) | **Analisa & Plan Matang Menu 1 (Presensi)**: Bedah detail `/portal-guru/akademik` (Rekap Harian, Matriks Bulanan 1–31, Input Manual Massal, WA Ortu Gateway, & Cetak PDF/Excel). |
| **09** | [`09-revisi-akses-kelas-dan-tahun-ajaran-presensi.md`](file:///home/sutris-remote/Documents/projek-laravel/apk-android-presensi/docs/09-revisi-akses-kelas-dan-tahun-ajaran-presensi.md) | **Revisi Menu 1 (Presensi)**: Penguncian Tahun Ajaran Aktif, filter ketat kelas Wali Kelas (1–2 kelas), dan dukungan Kelas Binaan BK multi-kelas sesuai `/admin/manajemen-akses-portal`. |
| **10** | [`10-plan-menu-2-presensi-sholat-dhuhur.md`](file:///home/sutris-remote/Documents/projek-laravel/apk-android-presensi/docs/10-plan-menu-2-presensi-sholat-dhuhur.md) | **Analisa & Plan Matang Menu 2 (Sholat Dhuhur)**: Bedah detail `/portal-guru/sholat-dhuhur` (Sinkron Presensi Pagi khusus Izin/Sakit/Alpa, Pop-up Input 1 Siswa vs Massal, Halangan/Haid, Non-Muslim, WA Ortu, & Cetak PDF/Excel). |
| **11** | [`11-plan-menu-3-jurnal-mengajar.md`](file:///home/sutris-remote/Documents/projek-laravel/apk-android-presensi/docs/11-plan-menu-3-jurnal-mengajar.md) | **Analisa & Plan Matang Menu 3 (Jurnal Mengajar)**: Bedah detail `/portal-guru/jurnal-mengajar` (Smart JP Calculator 1–9 Jam & 1–5 JP, Smart Gate Sync, + Hadirkan Wajib Telat, & Cetak Rekap PDF/Excel). |
| **12** | [`12-plan-menu-4-persetujuan-ijin.md`](file:///home/sutris-remote/Documents/projek-laravel/apk-android-presensi/docs/12-plan-menu-4-persetujuan-ijin.md) | **Analisa & Plan Matang Menu 4 (Persetujuan Ijin)**: Bedah detail `/portal-guru/ijin-kehadiran` (Otorisasi Wali Kelas vs BK, Auto Presensi Sync/Remove, Notifikasi WA Gateway & Log, serta Push Notification Android). |
| **13** | [`13-plan-menu-5-guru-wali.md`](file:///home/sutris-remote/Documents/projek-laravel/apk-android-presensi/docs/13-plan-menu-5-guru-wali.md) | **Analisa & Plan Matang Menu 5 (Guru Wali SI-WALI)**: Bedah detail `/portal-guru/guru-wali/*` (Permendikdasmen 11/2025, Single Command Center, Model Kotak-Kotak Siswa Asuh, Bimbingan Klasikal & Individu, Ruang Aman Cerita, Push Notif FCM). |
| **14** | [`14-plan-menu-6-data-siswa.md`](file:///home/sutris-remote/Documents/projek-laravel/apk-android-presensi/docs/14-plan-menu-6-data-siswa.md) | **Analisa & Plan Matang Menu 6 (Data Siswa)**: Bedah detail `/portal-guru/data-siswa` (Direktori Siswa Binaan, Kartu Barcode, Edit Biodata, WhatsApp Ortu 1-Klik, Reset Password Akun, Cetak PDF Kartu & Biodata). |
| **15** | [`15-plan-menu-7-spikap.md`](file:///home/sutris-remote/Documents/projek-laravel/apk-android-presensi/docs/15-plan-menu-7-spikap.md) | **Analisa & Plan Matang Menu 7 (SPIKAP Anti-Perundungan)**: Bedah detail `/portal-guru/spikap` (Hak Akses Ketat BK/Wali/KS, Kotak Masuk Kasus, Lembar Investigasi & Bukti, Form Status & WA Ortu, serta Analitik Kasus). |
| **16** | [`16-plan-menu-8-perpustakaan.md`](file:///home/sutris-remote/Documents/projek-laravel/apk-android-presensi/docs/16-plan-menu-8-perpustakaan.md) | **Analisa & Plan Matang Menu 8 (Perpustakaan)**: Bedah detail `/portal-guru/perpustakaan` (Katalog Buku, Pinjaman Pribadi, Riwayat Pribadi, Kunjungan Fisik, & Pemantauan Literasi Siswa Kelas Ampu). |
| **17** | [`17-plan-menu-9-profil.md`](file:///home/sutris-remote/Documents/projek-laravel/apk-android-presensi/docs/17-plan-menu-9-profil.md) | **Analisa & Plan Matang Menu 9 (Profil Saya & Akun Pendidik)**: Bedah detail `/portal-guru/profil` (Biodata Pendidik, Penugasan Akademik Terproteksi, Pengkinian Kontak & Microsite, Upload/Hapus Foto, & Keamanan Kata Sandi). |
| **18** | [`18-plan-push-notification-fcm.md`](file:///home/sutris-remote/Documents/projek-laravel/apk-android-presensi/docs/18-plan-push-notification-fcm.md) | **Blueprint Teknis Push Notification FCM**: Panduan & Rencana Kerja Terstruktur (Tahap 1 s.d. 6) integrasi notifikasi real-time persis WhatsApp saat aplikasi mati/closed (*Killed State*) via Firebase Cloud Messaging & Laravel HTTP v1. |

---

## 🏛️ Prinsip Utama Proyek Mobile

1. **Aplikasi Eksklusif Setiap Sekolah**:
   Setiap sekolah mendapatkan APK tersendiri dengan alamat server yang **langsung di-injek saat build**. Siswa dan guru tidak perlu repot mengetik URL server lagi; saat aplikasi dibuka, langsung tampil identitas sekolah yang bersangkutan.
2. **Koneksi Tunggal via REST API Sanctum**:
   Aplikasi mobile murni berperan sebagai *client frontend*. Seluruh manipulasi data kehadiran, izin, dan sholat berkomunikasi langsung ke backend Laravel via JSON Bearer Token.
3. **Penyelarasan Desain Material 3**:
   Menggunakan font resmi *Plus Jakarta Sans* dan palet warna yang serasi dengan antarmuka web Filament sekolah.

