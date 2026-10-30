# 📋 Plan Matang Tahap 9 (Menu 9): Profil Saya & Akun Pendidik (`/portal-guru/profil`)

Dokumen ini merupakan **Blueprint Teknis & Panduan Implementasi Komprehensif** untuk pengembangan **Menu 9: Profil Saya (Biodata Pendidik, Penugasan Akademik, Kontak, Foto Profil, Keamanan Kata Sandi, & Tautan Microsite)** pada aplikasi mobile Flutter (`apk-android-presensi`) dan backend Laravel (`projek-absensi-barcode`), yang mengacu pada fitur web:
- **Komponen Livewire**: [`GuruProfil.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Livewire/GuruProfil.php)
- **Tampilan Blade**: [`guru-profil.blade.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/resources/views/livewire/guru-profil.blade.php)
- **Rujukan URL Web**: `http://localhost:8001/portal-guru/profil`

---

## 1. Analisis Fitur Web & Logika Bisnis Profil Guru

Pada portal web guru (`/portal-guru/profil`), modul Profil Saya memiliki **5 pilar fungsi utama**:

### 1. 🪪 Header Identitas & Kartu Hero Guru
- **Cover Banner Gradasi Elegan**: Menampilkan tema visual portal guru dengan badge status "Portal Guru • Profil & Akun Pendidik".
- **Badge Peran Utama**:
  - Jika guru menjabat sebagai wali kelas pada tahun ajaran aktif: Badge hijau emerald *"Wali Kelas {Nama Kelas}"*.
  - Jika tidak bertugas sebagai wali kelas: Badge *"Guru Pengajar"*.
- **Foto Profil / Avatar Guru**:
  - Menampilkan foto resmi pendidik dari `storage/{photo_path}`.
  - Jika belum memiliki foto, otomatis menampilkan inisial huruf nama berlatar gradien profesional.
- **Identitas Ringkas & Status**:
  - Nama lengkap guru terdaftar resmi.
  - Badge denyut hijau *"Tenaga Pendidik Aktif"*.
  - Baris chips informasi cepat: NIP resmi, nomor WhatsApp, email login, dan badge tombol tautan microsite aktif.

### 2. 📸 Pengelolaan Foto Profil (Upload & Hapus)
- **Unggah Foto Baru**: Pendidik dapat memilih foto dari galeri HP atau memotret langsung dengan kamera.
- **Validasi Ketat**: Format gambar didukung (`jpg`, `jpeg`, `png`, `webp`) dengan ukuran maksimum 2MB.
- **Manajemen Berkas Server**:
  - Berkas baru disimpan pada disk `public`: `guru-photos/guru_{teacher_id}_{timestamp}.{ext}`.
  - Berkas foto lama di server otomatis dihapus saat diganti agar menghemat ruang penyimpanan server.
- **Hapus Foto Profil**: Tombol hapus foto dengan dialog konfirmasi, menghapus berkas fisik di server dan mereset nilai kolom `photo_path` menjadi `null`.

### 3. 🏛️ Penugasan Akademik (Master Data Terproteksi)
- Menampilkan seluruh penugasan resmi guru yang berlaku pada **tahun ajaran aktif**:
  - **Wali Kelas Binaan**: Daftar rombongan belajar yang dibina langsung (misal: *Kelas 7A, 8B*).
  - **Akses Kelas Pantau (BK)**: Daftar kelas pantauan pendampingan khusus bimbingan konseling jika guru bertugas sebagai Guru BK.
  - **Mata Pelajaran yang Diampu**: Daftar nama mapel beserta kelas yang diajar (misal: *Matematika (7A), Matematika (7B)*).
  - **Jabatan & Tugas Tambahan**: Daftar jabatan struktural aktif dari master data (misal: *Wakil Kepala Sekolah Bidang Kurikulum, Koordinator P5, Pembina OSIS*).
- **Banner Peringatan**: Ditegaskan bahwa data penugasan akademik bersifat *read-only* karena dikelola terpusat oleh Administrator Kurikulum.

### 4. 📞 Kontak & Akun Login (Pengkinian Mandiri)
- **Alamat Email Login**: Wajib diisi, format email valid, unik di tabel `users` (dapat digunakan untuk masuk ke aplikasi).
- **Nomor WhatsApp / HP**: Format angka nomor telepon seluler aktif, otomatis disinkronkan ke tabel `users.no_hp` dan `teachers.no_hp` dengan format standar `62...`.
- **Link Microsite Guru**: Tautan laman eksternal guru (Google Sites, Canva Portofolio, S.id, Linktree). Tautan ini terintegrasi ke website resmi sekolah pada menu *Akademik > Microsite Sekolah* sehingga siswa dan umum dapat mengakses materi belajar guru.
- Tombol aksi simpan informasi kontak dengan indikator loading state.

### 5. 🔒 Keamanan & Perubahan Kata Sandi (Password)
- **Kata Sandi Saat Ini**: Wajib diisi dan diverifikasi keasliannya dengan hash tersimpan di database (`Hash::check`).
- **Kata Sandi Baru**: Minimal 8 karakter, wajib dikonfirmasi dengan kata sandi yang sama persis.
- **Reset Keamanan**: Setelah berhasil diubah, flag `must_change_password` pada user otomatis diset menjadi `false`.
- **Feedback & Pembersihan**: Form otomatis dikosongkan dan menampilkan pesan notifikasi berhasil.

---

## 2. Arsitektur Antarmuka Mobile (Wireframe UX Flutter)

Layar `ProfilGuruScreen` dirancang dengan arsitektur visual mobile-first modern, ergonomis untuk penggunaan satu tangan, dan navigasi tab tersegmentasi:

```
┌──────────────────────────────────────────────────────────────┐
│  ←  Profil Saya                                         ⚙️   │
│     SMP Negeri 3 Kedungreja                                  │
├──────────────────────────────────────────────────────────────┤
│  [ HERO COVER GRADIENT & AVATAR PENDIDIK ]                   │
│  ┌────────────────────────────────────────────────────────┐  │
│  │  [FOTO GURU 110dp]       Sutrisno, S.Pd.               │  │
│  │  (📷 Tap Ubah Foto)     🟢 Tenaga Pendidik Aktif      │  │
│  │                          NIP: 198501012010011001       │  │
│  │                          [🏷️ Wali Kelas 8A]            │  │
│  └────────────────────────────────────────────────────────┘  │
├──────────────────────────────────────────────────────────────┤
│  [ SEGMENTED TABS PILIHAN ]                                  │
│  [ 👤 Kontak & Bio ]  [ 📚 Akademik ]  [ 🔒 Keamanan ]       │
├──────────────────────────────────────────────────────────────┤
│  [ TAB 1: KONTAK & BIODATA ]                                 │
│  • Nama Lengkap: Sutrisno, S.Pd. (Read-only)                 │
│  • NIP Pegawai: 198501012010011001 (Read-only)               │
│  • Email Login: [ sutrisno@smpn3.sch.id                ]     │
│  • Nomor WhatsApp: [ 081234567890                     ]     │
│  • Link Microsite: [ https://sites.google.com/view/... ]     │
│    [🔗 Buka Tautan Microsite]                                │
│  ┌────────────────────────────────────────────────────────┐  │
│  │ [ 💾 Simpan Perubahan Kontak & Microsite             ] │  │
│  └────────────────────────────────────────────────────────┘  │
├──────────────────────────────────────────────────────────────┤
│  [ TAB 2: PENUGASAN AKADEMIK ]                               │
│  • Info: "Dikelola terpusat oleh Administrator Kurikulum"    │
│  • Wali Kelas: [ Kelas 8A ] [ Kelas 8B ]                     │
│  • Mata Pelajaran: • Matematika (8A)  • Matematika (8B)      │
│  • Kelas Pantau BK: [ Kelas 7A ] [ Kelas 7B ]                │
│  • Jabatan Tambahan: [ Wakasek Kurikulum ]                   │
├──────────────────────────────────────────────────────────────┤
│  [ TAB 3: KEAMANAN KATA SANDI ]                              │
│  • Sandi Saat Ini: [ **********           👁️ ]             │
│  • Sandi Baru:     [ **********           👁️ ]             │
│  • Ulangi Sandi:   [ **********           👁️ ]             │
│  ┌────────────────────────────────────────────────────────┐  │
│  │ [ 🔒 Perbarui Kata Sandi Akun                        ] │  │
│  └────────────────────────────────────────────────────────┘  │
├──────────────────────────────────────────────────────────────┤
│  [ FOOTER INFORMASI SESI & LOGOUT ]                          │
│  Versi Aplikasi: v1.0.0+1 • SMP Negeri 3 Kedungreja          │
│  [ 🚪 Keluar dari Akun Guru                             ]    │
└──────────────────────────────────────────────────────────────┘
```

---

## 3. Arsitektur Backend REST API Modular Laravel (`projek-absensi-barcode`)

Kami membuat controller modular khusus di backend Laravel:
[`app/Http/Controllers/Api/Mobile/GuruProfilController.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Http/Controllers/Api/Mobile/GuruProfilController.php)

### Endpoint REST API Terproteksi (`/api/v1/guru/profil`):

1. **`GET /api/v1/guru/profil`**:
   - Menghasilkan detail profil guru, user login, penugasan tahun ajaran aktif, dan tautan avatar.
   - Response Payload:
     ```json
     {
       "success": true,
       "message": "Data profil guru berhasil dimuat",
       "data": {
         "user": {
           "id": "uuid",
           "name": "Sutrisno, S.Pd.",
           "email": "sutrisno@smpn3.sch.id",
           "no_hp": "081234567890"
         },
         "teacher": {
           "id": "uuid",
           "name": "Sutrisno, S.Pd.",
           "nip": "198501012010011001",
           "no_hp": "081234567890",
           "photo_path": "guru-photos/guru_123.jpg",
           "photo_url": "http://localhost:8001/storage/guru-photos/guru_123.jpg",
           "microsite_url": "https://sites.google.com/view/guru-sutrisno"
         },
         "akademik": {
           "is_wali_kelas": true,
           "kelas_wali": ["8A", "8B"],
           "kelas_pantau": [],
           "mapel_diampu": ["Matematika (8A)", "Matematika (8B)"],
           "jabatan_aktif": ["Wali Kelas 8A", "Wali Kelas 8B", "Wakil Kepala Sekolah"]
         }
       }
     }
     ```

2. **`POST /api/v1/guru/profil/kontak`**:
   - Validasi data:
     - `email`: `required|email|max:255|unique:users,email,{userId}`
     - `no_hp`: `nullable|string|max:20`
     - `microsite_url`: `nullable|url|max:500`
   - Memperbarui `users` dan `teachers`.

3. **`POST /api/v1/guru/profil/foto`**:
   - Multipart file upload: `photo` (`required|image|mimes:jpeg,png,jpg,webp|max:2048`).
   - Otomatis menghapus file foto lama di storage jika ada.
   - Menyimpan ke disk public: `guru-photos/guru_{teacherId}_{timestamp}.{ext}`.
   - Mengembalikan `photo_path` dan `photo_url`.

4. **`DELETE /api/v1/guru/profil/foto`**:
   - Menghapus file fisik di storage.
   - Mengubah `teachers.photo_path` menjadi `null`.

5. **`POST /api/v1/guru/profil/password`**:
   - Validasi: `current_password` (wajib), `new_password` (min:8, confirmed).
   - Verifikasi hash kata sandi lama.
   - Simpan hash baru dan reset `must_change_password` = `false`.

---

## 4. Rencana Kerja Bertahap (Tahap 1 s.d. Tahap 6)

### 🔹 Tahap 1: Backend REST API Modular Profil Guru (Laravel)
- Membuat [`GuruProfilController.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Http/Controllers/Api/Mobile/GuruProfilController.php) dengan 5 method API:
  - `getProfile()`
  - `updateContact()`
  - `updatePhoto()`
  - `removePhoto()`
  - `updatePassword()`
- Mendaftarkan rute grup `Route::prefix('profil')` di `routes/api.php`.
- Melakukan verifikasi sintaks PHP lint dan pengujian 5 endpoint dengan cURL.

### 🔹 Tahap 2: Dart Models, Constants & Service di Flutter
- Membuat model data di `lib/features/guru/models/guru_profil_model.dart`:
  - `GuruProfilResponse`, `GuruUserData`, `GuruTeacherData`, `GuruAkademikData`.
- Menambahkan konstanta endpoint di `lib/core/constants/api_endpoints.dart`:
  - `guruProfil`, `guruProfilKontak`, `guruProfilFoto`, `guruProfilPassword`.
- Membuat `GuruProfilService` di `lib/features/guru/services/guru_profil_service.dart`:
  - Method fetch profil, update kontak, upload multipart foto, delete foto, dan ganti password.
- Verifikasi static analysis `analyze_files`.

### 🔹 Tahap 3: Layar Utama, Header Hero & Tab 1: Kontak & Biodata
- Membuat `ProfilGuruScreen` di `lib/features/guru/screens/profil_guru_screen.dart`:
  - Header Hero Banner dengan gradasi biru-indigo, squircle avatar guru, badge wali kelas, NIP, dan denyut aktif.
  - Tab Navigasi Segmen (Kontak & Bio, Penugasan Akademik, Keamanan Sandi).
- Membangun Tab 1 (Kontak & Bio):
  - Field NIP & Nama Lengkap (Read-only dengan latar soft grey).
  - Field Email & Nomor WhatsApp (Input interaktif).
  - Field Tautan Microsite Guru dengan tombol uji preview langsung (`url_launcher`).
  - Tombol Simpan Perubahan Kontak dengan feedback visual sukses.

### 🔹 Tahap 4: Integrasi Upload & Hapus Foto Profil (Camera / Gallery)
- Membangun modal bottom sheet pemilihan foto:
  - Opsi: *Ambil Foto dari Kamera*, *Pilih dari Galeri*, *Hapus Foto Profil Saat Ini*.
- Integrasi package `image_picker` dengan kompresi ukuran gambar.
- Integrasi multipart file upload ke endpoint API `POST /api/v1/guru/profil/foto`.
- Live preview update langsung pada avatar profil dashboard dan layar profil.

### 🔹 Tahap 5: Tab 2: Penugasan Akademik & Tab 3: Keamanan Sandi + Logout
- Membangun Tab 2 (Penugasan Akademik):
  - Banner informasi regulasi administrator.
  - Card Wali Kelas dengan chips kelas binaan.
  - Card Akses Kelas Pantau (BK) jika ada.
  - Card Mata Pelajaran yang diampu per kelas.
  - Card Jabatan & Tugas Tambahan.
- Membangun Tab 3 (Keamanan Kata Sandi):
  - Field Sandi Saat Ini, Sandi Baru, dan Konfirmasi Sandi dengan toggle icon mata (show/hide).
  - Validasi kecocokan dan syarat minimal 8 karakter.
  - Tombol aksi perbarui kata sandi dengan dialog feedback.
- Footer Sesi & Logout:
  - Kartu informasi versi aplikasi, identitas sekolah, dan status session.
  - Tombol Logout akun dengan konfirmasi keamanan.

### 🔹 Tahap 6: Integrasi Dashboard Guru, Testing & Push GitHub
- Menghubungkan Card Menu 9 pada `DashboardGuruScreen` (`_openProfilMenu`).
- Menghubungkan avatar header dashboard untuk navigasi instan ke Profil.
- Menjalankan analisis statis `analyze_files` (memastikan 0 error/warning).
- Pengujian hot reload pada emulator/perangkat.
- Sinkronisasi dokumen plan dan git commit & push ke kedua repositori GitHub.

---

## 5. Matriks Pengujian & Kriteria Keberhasilan

1. **Pengkinian Kontak**:
   - Email berhasil diperbarui dan dicek keunikannya (tidak boleh bentrok dengan akun lain).
   - Nomor WhatsApp otomatis terformat rapi dan tersimpan di tabel `users` maupun `teachers`.
   - Link microsite dapat dibuka via tombol preview eksternal browser.
2. **Pengelolaan Foto**:
   - Foto dari kamera atau galeri berhasil diunggah dan disimpan ke server.
   - Foto profil lama di server otomatis terhapus saat diganti.
   - Hapus foto mengembalikan avatar inisial default secara instan.
3. **Penugasan Akademik**:
   - Daftar wali kelas dan mata pelajaran sinkron dengan tahun ajaran aktif di database.
4. **Keamanan Kata Sandi**:
   - Kata sandi lama divalidasi kebenarannya; penolakan terjadi jika password lama salah.
   - Kata sandi baru minimal 8 karakter dan harus cocok dengan konfirmasi.
   - Login selanjutnya dapat menggunakan password baru.
5. **Dashboard Integration**:
   - Menekan Menu 9 atau foto avatar pada dashboard langsung membuka `ProfilGuruScreen`.
   - Perubahan nama, email, atau foto di profil otomatis menyegarkan tampilan dashboard saat kembali.

---

## 6. Status Eksekusi Fitur
- [x] **Tahap 1: Backend REST API Modular Profil Guru (Laravel)**
- [x] **Tahap 2: Dart Models, Constants & Service (Flutter)**
- [x] **Tahap 3: Layar Utama, Hero Header & Tab 1 Kontak/Biodata**
- [x] **Tahap 4: Integrasi Upload & Hapus Foto Profil (Camera/Gallery)**
- [x] **Tahap 5: Tab 2 Penugasan Akademik & Tab 3 Keamanan Sandi + Logout**
- [ ] **Tahap 6: Integrasi Dashboard Guru & Finalisasi**

