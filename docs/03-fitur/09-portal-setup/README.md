# 🛠️ Portal Setup & Instalasi (`/portal-setup`)

Portal khusus untuk **konfigurasi dan instalasi awal sistem** sebelum sekolah mulai menginput/mengunggah data guru, siswa, dan menjalankan operasional harian (presensi, akademik, perpustakaan).

---

## 🎯 Tujuan Portal
1. Menyediakan alur kerja terpandu (*guided setup*) bagi `super_admin` atau `admin_portal_setup` saat pertama kali men-deploy atau menyiapkan tahun ajaran baru.
2. Memastikan fondasi data inti (Identitas Sekolah, Tahun Ajaran Aktif, dan Master Kelas untuk jenjang SD/SMP/SMA/SMK) sudah valid sebelum masuk ke proses import guru dan siswa.

---

## 🔐 Hak Akses & Routing
- **URL Utama**: `/portal-setup`
- **Middleware**: `auth.setup` ([`EnsureCanAccessSetup`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Http/Middleware/EnsureCanAccessSetup.php))
- **Role yang Diizinkan**: `super_admin` atau `admin_portal_setup` (dapat diatur melalui menu **Manajemen Akses Portal** di `/admin/manajemen-akses-portal`).
- **Registrasi Hub Portal**: Terdaftar di [`User::getAccessiblePortals()`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Models/User.php) agar muncul di `/pilih-portal`.

---

## 📋 Peta Menu & Tahapan Pengembangan

### Tahap 1 & 2 (Aktif — Fondasi Sistem)
1. **Setting Sekolah, Tahun Ajaran & Kelas** (`/portal-setup/dasar`)
   - **Tab 1 — Identitas, Jenjang & Status Sekolah** (`school_settings`): Jenjang (`SD`, `SMP`, `SMA`, `SMK`), status kepemilikan (`negeri` memakai NIP / `swasta` memakai NIY/NUPTK), nama sekolah, alamat, kota, email, telepon, kepala sekolah, dan logo.
   - **Tab 2 — Tahun Ajaran & Semester Aktif** (`academic_years` & `school_settings.active_semester`): Terkunci hingga Tab 1 selesai. Mengelola tahun ajaran aktif (otomatis sinkron ke `academic_year_id_active`) serta semester aktif (`ganjil`/`genap`).
   - **Tab 3 — Master Kelas** (`classes`): Terkunci hingga Tab 1 selesai. Pengelolaan tingkat dan rombongan belajar fleksibel untuk **SD (1–6)**, **SMP (7–9)**, **SMA (10–12)**, dan **SMK (10–13 / Kompetensi Keahlian `major`)** lengkap dengan fitur *Generate Massal* dan *live preview*.
2. **Master Jabatan** (`/portal-setup/jabatan`)
   - Terbuka otomatis setelah 3 setup dasar berstatus `OK`.
   - Sinkronisasi otomatis dari `Jabatan::standarByJenjang()` (termasuk `JabatanSeeder`) sesuai jenjang sekolah (`SD`/`SMP`/`SMA`/`SMK`).
   - Jabatan inti sistem (`Kepala Sekolah`, `Guru BK`, `Waka ...`) diproteksi agar integrasi WA (`/admin/manajemen-notifikasi-wa`), SPIKAP, dan hak akses tetap stabil.
   - *Catatan*: **Wali Kelas** tidak disimpan di `jabatans`, melainkan di-assign per kelas dan tahun ajaran melalui `kelas_ajarans` setelah data guru diunggah.
3. **Keamanan Gembok Edit (Opsi B)**:
   - Seluruh form pada `/portal-setup/dasar` dan `/portal-setup/jabatan` otomatis masuk **Mode Terkunci** setelah data terisi (`OK`), dan hanya dapat dibuka menggunakan verifikasi password akun `super_admin`.

### Tahap Berikutnya (Roadmap)
- **Langkah 5**: Inisialisasi Role, Permission, & Template Default Sistem (sebelum upload pengguna)
- Menu **Upload / Import Data Guru & GTK**
- Menu **Upload / Import Data Siswa & Rombel**

