# Rencana Kerja: Menu 7 - Profil Saya & Akun Pelajar

> **Dokumen Rencana Detail**: Menu 7 Layanan Profil Saya, Biodata Akademik, Perbarui Kontak, Unggah Foto Profil, Keamanan Akun (Ganti Password), dan Logout Terintegrasi  
> **Referensi Web**: `http://localhost:8001/portal-siswa/profil` (`App\Livewire\SiswaProfil`)  
> **Status**: **SELESAI 100%** ✅ (Sub-tahap 1 s.d. 4 Tuntas & Terverifikasi - Seluruh 7 Menu Portal Siswa Live)

---

## 📌 Ringkasan Fitur

1. **Hero Header Pelajar Modern**:
   - Avatar foto resmi siswa (dengan fallback inisial nama).
   - Tombol ganti/hapus foto langsung di atas avatar.
   - Nama lengkap siswa, badge status keanggotaan aktif (*Aktif*), NISN, NIS, dan Kelas / Tahun Ajaran.
   - Nuansa warna Sky-Cyan / Navy Blue (`#0284C7` s.d. `#0369A1`).

2. **Tab 1: Biodata Akademik & Kontak Siswa**:
   - **Data Akademik (Read-Only Resmi)**:
     - Nama Lengkap, NISN, NIS.
     - Tempat & Tanggal Lahir, Jenis Kelamin, Agama.
     - Kelas & Rombel, Tahun Ajaran Aktif.
     - Nama Ayah, Nama Ibu, Nama Wali.
   - **Formulir Pengkinian Kontak Mandiri**:
     - Alamat tempat tinggal / domisili aktif (textarea).
     - Nomor HP / WhatsApp siswa / orang tua yang dapat dihubungi.
     - Tombol *"Simpan Perubahan Kontak"*.

3. **Fitur Pengelolaan Foto Profil Pelajar**:
   - Pilihan sumber: Kamera atau Galeri.
   - **Kompresi Otomatis ala WhatsApp** menggunakan `ImageCompressUtil` (maksimal 800px, quality 75%) sebelum dikirim ke backend.
   - Opsi *"Hapus Foto Profil"* untuk kembali ke inisial nama.

4. **Tab 2: Keamanan Akun & Sandi**:
   - Formulir ganti kata sandi login:
     - Kata Sandi Lama / Saat Ini (dengan toggle mata show/hide).
     - Kata Sandi Baru (minimal 8 karakter dengan toggle mata).
     - Konfirmasi Kata Sandi Baru.
   - Validasi kecocokan sandi dan tombol *"Perbarui Kata Sandi"*.

5. **Tombol Keluar Akun (Logout)**:
   - Tombol logout dengan dialog konfirmasi aman.
   - Pembersihan session token lokal (`SessionStorage.clear()`).
   - Navigasi kembali ke layar login (`LoginScreen`).

---

## 🗂️ Rincian Sub-tahap Pengerjaan

### 1️⃣ Sub-tahap 1: Backend REST API Profil Siswa (`projek-absensi-barcode`) [SELESAI ✅]
* Controller: [`SiswaProfilController.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Http/Controllers/Api/Mobile/Siswa/SiswaProfilController.php) (extends `BaseSiswaApiController`).
* Rute API (di `routes/api.php` under prefix `/api/v1/siswa/profil`):
  * `GET /info` -> Mengambil biodata lengkap siswa, kelas, kontak, foto URL, data ortu, dan izin unggah foto.
  * `POST /update-kontak` -> Memperbarui data alamat domisili dan no_hp siswa.
  * `POST /update-foto` -> Menerima berkas upload foto siswa (jpg, jpeg, png, webp), simpan ke storage publik `siswa-photos/`.
  * `DELETE /hapus-foto` -> Menghapus berkas foto lama dan mengosongkan `photo_path`.
  * `POST /update-password` -> Memverifikasi password saat ini via `Hash::check()` dan mengupdate kata sandi baru.
* Pengujian live via `curl`: Seluruh response berstatus **200 OK** (uji info biodata, update domisili, dan validasi sandi).

### 2️⃣ Sub-tahap 2: Flutter Data Model, Endpoints & Service (`apk-android-presensi`) [SELESAI ✅]
* Registrasi rute endpoint di [`api_endpoints.dart`](file:///home/sutris-remote/Documents/projek-laravel/apk-android-presensi/lib/core/constants/api_endpoints.dart):
  * `siswaProfilInfo`, `siswaProfilUpdateKontak`, `siswaProfilUpdateFoto`, `siswaProfilHapusFoto`, `siswaProfilUpdatePassword`.
* Data Model di [`siswa_profil_model.dart`](file:///home/sutris-remote/Documents/projek-laravel/apk-android-presensi/lib/features/siswa/models/siswa_profil_model.dart):
  * `SiswaProfilDetail`, `SiswaProfilResponse`.
* Service di [`siswa_profil_service.dart`](file:///home/sutris-remote/Documents/projek-laravel/apk-android-presensi/lib/features/siswa/services/siswa_profil_service.dart):
  * `getProfilInfo()`
  * `updateKontak()`
  * `updateFoto()`
  * `hapusFoto()`
  * `updatePassword()`

### 3️⃣ Sub-tahap 3: Layar Utama Profil Siswa & Tab Navigasi (`apk-android-presensi`) [SELESAI ✅]
* Layar: [`profil_siswa_screen.dart`](file:///home/sutris-remote/Documents/projek-laravel/apk-android-presensi/lib/features/siswa/screens/profil_siswa_screen.dart)
  * Hero banner profil bernuansa Sky Blue - Navy dengan avatar dinamis dan bottom sheet pemilih foto (Kamera / Galeri / Hapus).
  * 2 Tab Navigasi Segmented:
    - **Tab 1: Biodata & Kontak**: Kartu identitas resmi read-only, form edit alamat & no HP dengan tombol simpan.
    - **Tab 2: Keamanan Sandi**: Form ganti password dengan toggle show/hide, indikator aturan sandi.
  * Tombol Logout merah elegan di bagian bawah layar dengan dialog konfirmasi aman.
  * Layout responsif Tablet (grid 2 kolom side-by-side) vs HP (1 kolom vertikal).

### 4️⃣ Sub-tahap 4: Integrasi Beranda Siswa, Audit Aksesibilitas & Validasi Akhir [SELESAI ✅]
* Sambungkan kartu Menu 7 di [`dashboard_siswa_screen.dart`](file:///home/sutris-remote/Documents/projek-laravel/apk-android-presensi/lib/features/siswa/screens/dashboard_siswa_screen.dart) (`_navigateToProfil()`).
* Tombol shortcut profil di AppBar dan welcome avatar header disematkan.
* Audit Aksesibilitas (WCAG AA): Tooltip pada seluruh icon button, tap target min 48x48 dp, semantic label pada textfield sandi dan foto avatar.
* Pengujian analisis statis: `flutter analyze` terverifikasi **0 Error** (0 issue pada seluruh modul portal siswa).
