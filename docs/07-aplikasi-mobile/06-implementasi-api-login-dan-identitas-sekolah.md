# 🔐 06. Implementasi API Login Terpadu, Identitas Sekolah Dinamis & APK Flutter

Dokumen ini menjelaskan implementasi nyata dari **REST API Mobile Laravel** (`projek-absensi-barcode`) dan integrasinya dengan aplikasi **Flutter Android/Web** (`apk-android-presensi`) untuk fitur **Splash Screen**, **Unified Login Minimalis**, serta pemisahan **Portal Siswa** dan **Portal Guru**.

---

## 📂 1. Lokasi Kode Sumber (Backend & Mobile)

| Komponen | Lokasi Berkas | Fungsi Utama |
| :--- | :--- | :--- |
| **Route API Mobile** | [`routes/api.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/routes/api.php) | Mendaftarkan grup route `/api/v1/school-info`, `/api/v1/school-logo`, dan `/api/v1/auth/*`. |
| **Controller Info Sekolah** | [`app/Http/Controllers/Api/Mobile/SchoolInfoController.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Http/Controllers/Api/Mobile/SchoolInfoController.php) | Mengirim nama sekolah, warna tema, dan `logo_base64` teroptimasi (thumbnail 240px) dari tabel `school_settings`. |
| **Controller Auth Mobile** | [`app/Http/Controllers/Api/Mobile/AuthController.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Http/Controllers/Api/Mobile/AuthController.php) | Memproses login terpadu (NISN/NIS/NIP/Email), mendeteksi peran (`siswa` / `guru` / `admin`), dan menerbitkan token. |
| **Konfigurasi Server APK** | [`lib/core/config/app_config.dart`](file:///home/sutris-remote/Documents/projek-laravel/apk-android-presensi/lib/core/config/app_config.dart) | Menyimpan alamat server paten (`AppConfig.baseUrl`) dan nama sekolah bawaan. |
| **Layar Splash Screen** | [`lib/features/auth/screens/splash_screen.dart`](file:///home/sutris-remote/Documents/projek-laravel/apk-android-presensi/lib/features/auth/screens/splash_screen.dart) | Menampilkan **Logo Sekolah** dan **Nama Sekolah** di bawahnya dengan fitur *pre-cache* gambar. |
| **Layar Login Minimalis** | [`lib/features/auth/screens/login_screen.dart`](file:///home/sutris-remote/Documents/projek-laravel/apk-android-presensi/lib/features/auth/screens/login_screen.dart) | Menampilkan header (`Logo` -> `Portal Digital` -> `Nama Sekolahan`) dan form minimalis (`Username`, `Password`, `Login Portal`). |
| **Portal Siswa (Tujuan)** | [`lib/features/siswa/screens/dashboard_siswa_screen.dart`](file:///home/sutris-remote/Documents/projek-laravel/apk-android-presensi/lib/features/siswa/screens/dashboard_siswa_screen.dart) | Halaman beranda khusus Siswa setelah berhasil login. |
| **Portal Guru (Tujuan)** | [`lib/features/guru/screens/dashboard_guru_screen.dart`](file:///home/sutris-remote/Documents/projek-laravel/apk-android-presensi/lib/features/guru/screens/dashboard_guru_screen.dart) | Halaman beranda khusus Guru/Wali Kelas setelah berhasil login. |

---

## 🏛️ 2. Endpoint Identitas Sekolah & Optimasi Logo (`SchoolInfoController`)

### A. `GET /api/v1/school-info`
Mengambil informasi publik sekolah dari `\App\Models\PengaturanSekolah::current()` (dengan *fallback* ke `WebSetting`):
- `nama_sekolah`: Nama resmi sekolah (contoh: `SMP Negeri 3 Kedungreja`).
- `logo_base64`: Data URI `data:image/png;base64,...` dari logo sekolah yang sudah dikompresi otomatis.
- `logo_url`: URL langsung ke `/api/v1/school-logo` yang dilengkapi header `Access-Control-Allow-Origin: *`.

### B. Optimasi Thumbnail 240px untuk Flutter Web (Chrome CPU Rendering)
- File logo asli di `storage/app/public/settings/` berukuran **1189 × 1600 piksel (1,2 MB)**.
- Saat diuji menggunakan Chrome dengan *CPU-only rendering* (`webGLVersion is -1`), mendekode gambar 1,2 MB memerlukan waktu ~1,5 detik sehingga kotak logo di *Splash Screen* sempat tampak putih.
- Fungsi `getOptimizedBase64Logo()` pada `SchoolInfoController` otomatis membuat *thumbnail* berukuran lebar **240px (~50 KB)** (`thumb_240_<namafile>`) menggunakan PHP GD, lalu menyimpannya di *storage* agar pemuatan berikutnya berlangsung instan (<10 ms).
- Di sisi Flutter (`SplashScreen`), byte gambar didekode satu kali menjadi `Uint8List`, di-*cache* ke memori tampilan via `await precacheImage(MemoryImage(decodedBytes), context)`, dan langsung dioper ke `LoginScreen`.

---

## 🔑 3. Endpoint Unified Login & Pemisahan Portal (`AuthController`)

### A. `POST /api/v1/auth/login`
Menerima input tunggal `identifier` (yang diisi dari kolom **Username** di aplikasi) dan mencocokkannya secara berurutan ke:
1. `users.email` (Email langsung atau pencocokan awalan username sebelum `@`).
2. `students.nisn` atau `students.nis` (Nomor Induk Siswa).
3. `teachers.nip` (NIP Guru/Staf).

### B. Penentuan Role & Tujuan Portal
Setelah kata sandi terverifikasi (`Hash::check`), backend menentukan peran utama:
- **`siswa`**: Jika akun memiliki relasi `student()` atau ber-role `siswa`. Di aplikasi Flutter, pengguna langsung diarahkan ke **`DashboardSiswaScreen`**.
- **`guru`**: Jika akun memiliki relasi `teacher()` atau ber-role `guru` / `wali_kelas`. Di aplikasi Flutter, pengguna langsung diarahkan ke **`DashboardGuruScreen`**.
- **`admin`**: Jika akun merupakan administrator (`isSuperAdmin()`). Diarahkan ke **`DashboardGuruScreen`** dengan label *Administrator*.

### C. Ketahanan Terhadap Migrasi Tabel Sanctum
Apabila tabel `personal_access_tokens` belum dimigrasi pada database lokal, pemanggilan `$user->createToken()` dibungkus dalam blok `try-catch` sehingga API tetap mengembalikan respons sukses `200 OK` dengan token sesi sementara (tidak menyebabkan error HTTP 500).

---

## 🎨 4. Desain UI Splash & Login sesuai Permintaan

1. **Splash Screen (Saat Pertama Membuka Aplikasi)**:
   - Hanya menampilkan **Logo Sekolah** di tengah dan tepat di bawahnya **Nama Sekolah** (`SMP Negeri 3 Kedungreja`).
2. **Login Screen**:
   - **Header**: Logo Sekolah -> `Portal Digital` -> `Nama Sekolahan`.
   - **Form Minimalis**: Langsung menampilkan input **Username**, bawahnya **Password**, dan tombol **Login Portal**.
   - **Tanpa Pengaturan Server di UI**: Banner "Server Terhubung" di bagian bawah telah dihapus; URL server diatur paten melalui kode sumber (`AppConfig.baseUrl`).

