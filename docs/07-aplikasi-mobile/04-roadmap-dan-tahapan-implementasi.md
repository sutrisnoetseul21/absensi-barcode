# 🗺️ Roadmap & Tahapan Implementasi Flutter + Laravel API

Dokumen ini memetakan langkah-langkah kerja bertahap dari sisi backend Laravel hingga pembuatan build aplikasi Android APK menggunakan Flutter.

---

## 📅 Rencana Tahapan Kerja (Phase Roadmap)

```text
┌────────────────────────────────────────────────────────────────────────┐
│ Phase 1: Fondasi REST API Laravel                                      │
│ - Install & konfigurasi Laravel Sanctum                                │
│ - Endpoint Auth Login (multi-identifier: NISN/NIP/Email)               │
│ - Standarisasi API Resource & Response JSON                            │
└───────────────────────────────────┬────────────────────────────────────┘
                                    ▼
┌────────────────────────────────────────────────────────────────────────┐
│ Phase 2: Inisialisasi Proyek Mobile Flutter                            │
│ - Setup flutter create & paket dependensi (dio, riverpod, storage)     │
│ - Tema & Color Palette (menyelaraskan warna brand sekolah)             │
│ - Layar Login & Splash Screen responsif                                │
└───────────────────────────────────┬────────────────────────────────────┘
                                    ▼
┌────────────────────────────────────────────────────────────────────────┐
│ Phase 3: Modul Portal Siswa                                            │
│ - Kartu Pelajar Digital + Barcode Renderer                             │
│ - Dashboard Ringkasan & Riwayat Presensi Bulanan                       │
│ - Form Pengajuan Izin/Sakit + Upload Kamera                            │
└───────────────────────────────────┬────────────────────────────────────┘
                                    ▼
┌────────────────────────────────────────────────────────────────────────┐
│ Phase 4: Modul Portal Guru & Presensi                                  │
│ - Pemilihan Rombel Kelas Aktif                                         │
│ - Presensi Harian Siswa & Aksi Cepat WA Ortu                           │
│ - Presensi Sholat Dhuhur Berjamaah Massal                              │
│ - Persetujuan Izin Siswa Masuk                                         │
└───────────────────────────────────┬────────────────────────────────────┘
                                    ▼
┌────────────────────────────────────────────────────────────────────────┐
│ Phase 5: Testing, Hardening, & Build APK                               │
│ - Uji coba di perangkat HP Android nyata (beragam resolusi layar)     │
│ - Keamanan token & interceptor refresh                                 │
│ - Build APK Release (`flutter build apk --split-per-abi`)               │
└────────────────────────────────────────────────────────────────────────┘
```

---

## 🛠️ Persiapan Lingkungan Pengembangan (Prerequisites)

Bagi pengembang yang akan melanjutkan pengerjaan proyek Flutter:

1. **Flutter SDK**: Versi stable terbaru (minimal Flutter 3.19+ / Dart 3.3+).
2. **Android Studio**: Android SDK Build-Tools 34, Android Platform SDK 34.
3. **Java JDK**: OpenJDK 17 atau 21.
4. **Perangkat Uji**: Smartphone fisik Android atau Android Emulator (Pixel 6/7 API 33/34).
5. **Backend Server**: Laravel development server berjalan lokal (`http://localhost:8001`) atau staging (`https://coba.haflaitsolution.my.id`).

---

## 📦 Hasil Akhir yang Dihasilkan (Deliverables)

1. **File APK Siap Pakai**:
   - `app-arm64-v8a-release.apk` (ukuran hemat ~18-25 MB, optimal untuk 95% HP Android modern).
2. **Repositori Kode Flutter**:
   - Berdiri sendiri (misal: folder `mobile/` atau repository terpisah `absensi-barcode-mobile`).
3. **Dokumentasi API Swagger/Postman**:
   - Koleksi Postman / Insomnia untuk pengujian endpoint API.
