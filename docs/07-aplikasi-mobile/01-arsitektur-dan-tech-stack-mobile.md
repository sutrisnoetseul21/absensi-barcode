# 🏗️ Arsitektur & Spesifikasi Tech Stack Mobile

Dokumen ini menjelaskan arsitektur perangkat lunak aplikasi mobile berbasis **Flutter** dan integrasinya dengan backend **Laravel ERP**.

---

## 1. Arsitektur Komunikasi Sistem

```text
┌────────────────────────────────────────────────────────┐
│              Aplikasi Mobile (Flutter APK)             │
│                                                        │
│  ┌───────────────────────┐    ┌─────────────────────┐  │
│  │     Portal Siswa      │    │     Portal Guru     │  │
│  │ (Kartu, Izin, Riwayat)│    │ (Presensi & Sholat) │  │
│  └───────────┬───────────┘    └──────────┬──────────┘  │
│              │                           │             │
│              └─────────────┬─────────────┘             │
│                            │                           │
│               [ Riverpod / Bloc State ]                │
│                            │                           │
│                 [ Dio HTTP + Interceptor ]             │
│                 [ Secure Token Storage ]               │
└────────────────────────────┼───────────────────────────┘
                             │  HTTPS JSON + Bearer Token
                             ▼
┌────────────────────────────────────────────────────────┐
│                 Laravel ERP Server Backend             │
│                                                        │
│  ┌──────────────────────────────────────────────────┐  │
│  │        API Gateway (`routes/api.php`)            │  │
│  │  - Sanctum Auth Middleware                       │  │
│  │  - Rate Limiter & Role Gate                      │  │
│  └──────────────────────────┬───────────────────────┘  │
│                             ▼                          │
│  ┌──────────────────────────────────────────────────┐  │
│  │   Existing Services & Database Layer:            │  │
│  │   - PresensiSholatDhuhurService                  │  │
│  │   - KalenderSekolahService                       │  │
│  │   - WhatsAppGatewayService                       │  │
│  │   - PostgreSQL / MySQL (Students, Teachers, etc) │  │
│  └──────────────────────────────────────────────────┘  │
└────────────────────────────────────────────────────────┘
```

---

## 2. Pilihan Tech Stack Aplikasi Mobile

| Komponen | Pilihan Teknologi | Alasan Pemilihan |
| :--- | :--- | :--- |
| **Framework Mobile** | **Flutter 3.x (Dart 3)** | Satu basis kode untuk kompilasi native Android (APK) dan iOS (IPA), UI 60-120 fps tanpa lag. |
| **State Management** | **Flutter Riverpod** (atau **Bloc**) | Manajemen state terstruktur, *reactive*, mudah dites (*testable*), dan bebas *memory leak*. |
| **HTTP Networking** | **Dio** | Dukungan interceptor otomatis untuk Bearer Token Sanctum, auto-retry koneksi, dan upload file multipart. |
| **Penyimpanan Kredensial**| **flutter_secure_storage** | Menyimpan token login terenkripsi menggunakan Android Keystore / iOS Keychain. |
| **Barcode & QR Generator**| **barcode_widget** / **qr_flutter**| Menampilkan barcode ID kartu pelajar siswa langsung di layar HP secara presisi dan tajam. |
| **Kamera Scanner (Opsional)**| **mobile_scanner** | Pemindaian barcode cepat berbasis kamera native untuk guru jika diperlukan. |
| **Push Notification** | **Firebase Cloud Messaging (FCM)**| Menerima notifikasi langsung di bilah status HP saat ada presensi atau persetujuan izin. |
| **Desain & Tipografi** | **Material 3 + Plus Jakarta Sans** | Menyelaraskan identitas visual dengan tema Portal Web ERP yang sudah ada. |

---

## 3. Keunggulan Dibandingkan Akses Web Browser

1. **Barcode Selalu Siap di Saku**:
   Siswa tidak perlu khawatir kartu fisik tertinggal; cukup buka aplikasi untuk menampilkan barcode kartu pelajar saat scan di gerbang atau pinjam buku perpustakaan.
2. **Koneksi Tetap Bertahan (Biometric Login)**:
   Mendukung login sidik jari (*fingerprint*) atau Face Unlock tanpa harus mengetik ulang NISN/password setiap kali membuka aplikasi.
3. **Notifikasi Instan Tanpa Biaya Pulsa**:
   Selain via WhatsApp Gateway, sekolah dapat mengirimkan update status kehadiran siswa langsung ke aplikasi orang tua secara gratis menggunakan push notification.
