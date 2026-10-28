# 📱 Rencana Pengembangan Aplikasi Mobile (Flutter & Laravel API)

Direktori ini mendokumentasikan perencanaan arsitektur, pemilihan cakupan menu, desain kontrak REST API, serta roadmap implementasi aplikasi mobile berbasis **Flutter** dengan backend **Laravel ERP**.

---

## 🎯 Filosofi & Pendekatan Pengembangan

Aplikasi mobile dirancang **ramping dan terfokus (Lightweight & Targeted)**:
- **Bukan Duplikasi Total Web**: Aplikasi mobile tidak memuat seluruh panel admin Filament yang kompleks.
- **Fokus Pengguna Utama**: Khusus memfasilitasi kebutuhan harian **Siswa/Orang Tua** dan **Guru/Wali Kelas**.
- **Akses Cepat & Instan**: Mengoptimalkan performa pemindaian barcode kartu pelajar, pemantauan presensi harian, jurnal kelas, dan notifikasi ke perangkat HP pengguna.

---

## 🗺️ Struktur Dokumen

```text
docs/07-aplikasi-mobile/
├── README.md                                  # Peta umum perencanaan mobile app
├── 01-arsitektur-dan-tech-stack-mobile.md     # Arsitektur Flutter, state management, & konektivitas
├── 02-cakupan-menu-dan-spesifikasi-fitur.md    # Detail fitur terpilih (Portal Siswa & Portal Guru)
├── 03-desain-rest-api-laravel-sanctum.md      # Spesifikasi endpoint API & format JSON response
└── 04-roadmap-dan-tahapan-implementasi.md     # Tahapan kerja bertahap dari backend ke APK
```

---

## 👥 Pengguna Target & Peran Aplikasi

| Peran | Menu Utama di Aplikasi | Tujuan Utama |
| :--- | :--- | :--- |
| **Siswa / Ortu** | Kartu Digital, Riwayat Presensi, Izin Online, SPiKAP | Cek status kehadiran harian, barcode kartu pelajar di HP, dan pengajuan izin tanpa tatap muka. |
| **Guru / Wali Kelas**| Presensi Kelas, Presensi Sholat Dhuhur, Approval Izin, Jurnal Mengajar | Pencatatan kehadiran kelas dan sholat jamaah langsung dari mushola/ruang kelas. |

---

## 🔗 Hubungan dengan Backend Laravel

Aplikasi mobile bertindak sebagai *client* independen yang berkomunikasi melalui:
- **Base URL**: `https://coba.haflaitsolution.my.id/api/v1` (atau local `http://localhost:8001/api/v1`)
- **Autentikasi**: Bearer Token via **Laravel Sanctum**.
- **Standar Keamanan**: Token otomatis hangus saat logout dan dapat dibatasi hak aksesnya sesuai role Spatie.
