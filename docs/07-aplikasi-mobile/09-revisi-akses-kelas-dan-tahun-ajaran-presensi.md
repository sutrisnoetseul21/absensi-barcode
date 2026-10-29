# Rencana Revisi Menu 1 (Presensi): Penguncian Tahun Ajaran Aktif & Filter Hak Akses Kelas (Wali Kelas & Guru BK)

Dokumen ini mencatat hasil analisis dan rencana revisi untuk **Tahap 1 — Menu 1: Presensi** pada Portal Guru Android (`apk-android-presensi`) dan Backend API (`projek-absensi-barcode`), mengacu langsung pada konfigurasi **Manajemen Akses Portal** (`/admin/manajemen-akses-portal/{id}/edit`).

---

## 1. Latar Belakang & Poin Revisi

Berdasarkan evaluasi terhadap implementasi awal Menu 1 (Presensi) serta aturan otorisasi pada halaman Admin `/admin/manajemen-akses-portal/{id}/edit`, terdapat **3 poin revisi utama**:

1. **Kunci Tahun Ajaran menggunakan Tahun Ajaran Aktif**:
   - Guru tidak perlu (dan tidak boleh) memilih atau mengganti tahun ajaran lama pada halaman Presensi.
   - Sistem selalu mengunci data ke **Tahun Ajaran Aktif** (`TahunAjaran::where('status', 'aktif')->first()`).
2. **Sortir & Batasi Kelas Sesuai Hak Akses Wali Kelas (1 atau 2 Kelas Saja)**:
   - Jika pengguna adalah **Wali Kelas**, daftar kelas yang muncul **hanya** kelas yang diampu pada tahun ajaran aktif (tabel `class_academic_year` / relasi `kelasAjarans`), misalnya hanya 1 kelas (`7A`) atau 2 kelas (`7A`, `7B`).
   - Menghapus *fallback* yang sebelumnya menampilkan seluruh kelas sekolah apabila guru belum memiliki kelas atau saat pengujian.
3. **Dukungan Penuh Kelas Binaan Guru BK (Lebih dari 1 Kelas Sesuai Pilihan Admin)**:
   - Mengacu pada `/admin/manajemen-akses-portal/{id}/edit` bagian **"Akses Kelas Binaan BK (Khusus Guru BK)"**, Admin memilih daftar kelas binaan (`kelas_binaan_bk_ids`) yang disimpan ke tabel `teacher_class_accesses` (model `TeacherClassAccess` / relasi `Guru::kelasPantau()`).
   - Jika pengguna adalah **Guru BK** dan memiliki lebih dari 1 kelas binaan yang dipilih Admin (misal: `7A, 7B, 7C`), maka aplikasi hanya menampilkan daftar kelas binaan yang dipilih tersebut, mengizinkan Guru BK berpindah antar kelas binaannya, dan langsung memuat data presensi sesuai kelas yang dipilih.

---

## 2. Analisis Arsitektur Akses di `/admin/manajemen-akses-portal/{id}/edit`

Berdasarkan kode sumber [`ManajemenAksesPortalResource.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Filament/Resources/ManajemenAksesPortalResource.php) dan [`EditManajemenAksesPortal.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Filament/Resources/ManajemenAksesPortalResource/Pages/EditManajemenAksesPortal.php):

| Jenis Pengaturan di Admin | Tabel / Permission di Database | Perilaku di Menu Presensi |
| :--- | :--- | :--- |
| **Tahun Ajaran Aktif** | `academic_years` (`status = 'aktif'`) | Satu-satunya tahun ajaran yang digunakan (dikunci otomatis). |
| **Wali Kelas Utama** | `class_academic_year` (`teacher_id`, `academic_year_id`, `class_id`) melalui `$teacher->kelasAjarans()` | Hanya menampilkan 1 atau 2 kelas tempat guru tersebut menjadi Wali Kelas Utama. |
| **Akses Kelas Binaan BK (Khusus Guru BK)** | `teacher_class_accesses` (`teacher_id`, `academic_year_id`, `class_id`) melalui `$teacher->kelasPantau()` / `$kelas->guruKelasPantau()` | Menampilkan kelas-kelas yang dipilih Admin pada field `kelas_binaan_bk_ids`. Jika > 1 kelas, guru dapat memilih kelas dari daftar tersebut. |
| **Akses Semua Kelas (Bypass Mode)** | Permission Spatie `portal_guru:akses_semua_kelas` atau role `super_admin` / `admin_presensi` | Menampilkan seluruh kelas yang aktif pada tahun ajaran berjalan. |
| **Guru Tanpa Kelas Binaan / Tanpa Akses Pantau** | Tidak ada record di `class_academic_year` maupun `teacher_class_accesses`, serta tanpa permission bypass | Menampilkan *Empty State* **"Belum Memiliki Kelas Binaan"** (tidak boleh menampilkan semua kelas). |

---

## 3. Temuan Penyebab Masalah pada Implementasi Sebelumnya

1. **Resolusi User (`resolveUser`) di `GuruPresensiController.php`**:
   - Pencarian user berdasarkan NIP sebelumnya hanya mengecek `User::where('email', $identifier)` dan `Guru::where('nip', $identifier)` dengan `guru->user_id`. Jika gagal menemukan user yang tepat, baris ke-83 melakukan fallback ke `User::first()` (yang merupakan `super_admin`), sehingga semua kelas terbuka.
2. **Fallback Semua Kelas di `GuruPresensiController::dashboard()` (Baris 304–311)**:
   - Terdapat blok `if ($classes->isEmpty()) { $classes = Kelas::whereHas('kelasAjarans', ...)->get(); }` yang menyebabkan guru tanpa kelas binaan tetap dikirimi seluruh daftar kelas di sekolah.
3. **Perhitungan Jumlah Kelas di `dashboardSummary()` (Baris 123–132)**:
   - Sebelumnya menggabungkan `$kelasAjar` (jadwal mengajar mapel biasa dari `pengajarans`) ke dalam `kelas_ampu_count`. Akibatnya badge pada kotak **1. Presensi** di beranda menghitung kelas mengajar mapel, bukan khusus kelas binaan Wali Kelas / Binaan BK.
4. **Dropdown Tahun Ajaran & Data Dummy Awal di `PresensiKelasScreen`**:
   - State awal `_classes` di Flutter sebelumnya diisi 4 kelas dummy (`VII-A`, `VII-B`, `VIII-A`, `IX-A`) dan Tahun Ajaran masih berupa dropdown yang bisa diganti.

---

## 4. Rencana Perubahan Teknis (Step-by-Step)

### A. Revisi Backend Laravel (`GuruPresensiController.php`)

1. **Perbaikan `resolveUser(Request $request)`**:
   - Memprioritaskan pencocokan akurat:
     1. Token Sanctum (`auth('sanctum')->user()`)
     2. Fallback Token Base64 (`user_id|timestamp|random`) dari endpoint login
     3. Header `X-User-Identifier` / input `identifier` dengan urutan:
        - Cari di `Guru::where('nip', $identifier)->first()` -> ambil `$guru->user` atau `User::where('teacher_id', $guru->id)->first()`
        - Cari di `User::where('email', $identifier)->orWhere('name', $identifier)->first()`
   - Menghapus fallback ke `User::first()` (Super Admin) jika identifier dikirim, agar hak akses kelas tidak pernah bocor ke level Super Admin.

2. **Penguncian Tahun Ajaran Aktif (`getActiveAcademicYear()`)**:
   - Membuat helper internal yang selalu mengambil `TahunAjaran::where('status', 'aktif')->first()`.
   - Mengabaikan input tahun ajaran non-aktif dari client dan mengembalikan objek `active_academic_year` dengan flag `is_locked: true`.

3. **Helper Tunggal Penentuan Kelas (`getAccessibleClasses(User $user, string $activeYearId)`)**:
   - Mengecek apakah user memiliki hak bypass:
     - `$isAdminOrBypass = $user->isSuperAdmin() || $user->hasRole('admin_presensi') || $user->can('portal_guru:akses_semua_kelas');`
   - Jika **`$isAdminOrBypass === true`**:
     - Ambil semua kelas pada `academic_year_id = $activeYearId`, diurutkan `orderBy('name', 'asc')`, dengan label akses `"Semua Kelas (Bypass)"`.
   - Jika **`$isAdminOrBypass === false`** (Wali Kelas / Guru BK):
     - Ambil daftar ID kelas dari **Wali Kelas Utama**:
       `$waliClassIds = $teacher->kelasAjarans()->where('academic_year_id', $activeYearId)->pluck('class_id')->toArray();`
     - Ambil daftar ID kelas dari **Kelas Binaan BK** (`/admin/manajemen-akses-portal/{id}/edit` -> `teacher_class_accesses`):
       `$bkClassIds = $teacher->kelasPantau()->where('academic_year_id', $activeYearId)->pluck('class_id')->toArray();`
     - Ambil hanya kelas yang termasuk dalam `array_unique(array_merge($waliClassIds, $bkClassIds))`, diurutkan `orderBy('name', 'asc')`.
     - Sertakan metadata per kelas pada response JSON:
       ```json
       {
         "id": "uuid-kelas",
         "name": "7A",
         "grade_level": 7,
         "access_type": "wali_kelas", // atau "binaan_bk" atau "bypass"
         "access_label": "Wali Kelas" // atau "Binaan BK"
       }
       ```
     - **Tanpa fallback ke seluruh kelas sekolah**: jika guru tidak punya kelas di `$waliClassIds` maupun `$bkClassIds`, kembalikan array kosong `[]`.
   - Validasi `selectedClassId`:
     - Jika request mengirimkan `class_id`, pastikan `class_id` tersebut berada di dalam daftar `$classes` milik guru tersebut. Jika tidak ada atau belum dikirim, otomatis pilih kelas pertama (`$classes->first()?->id`).

4. **Sinkronisasi `dashboardSummary()` untuk Beranda 9 Kotak Menu**:
   - `kelas_ampu_count` pada kotak **1. Presensi** menggunakan jumlah kelas dari `getAccessibleClasses($user, $activeYear->id)` agar angka badge di kotak menu Beranda (`misal: 1 Kelas` untuk Wali Kelas atau `3 Kelas` untuk Guru BK) sama persis dengan jumlah kelas di dalam halaman Presensi.

---

### B. Revisi Frontend Flutter (`presensi_kelas_screen.dart`)

1. **Kunci Field Tahun Ajaran (Read-Only / Locked)**:
   - Mengganti `DropdownButton` Tahun Ajaran menjadi kartu/field terkunci (**Tahun Ajaran Aktif**) dengan ikon gembok (`Icons.lock_outline_rounded`) dan badge `AKTIF`, sehingga tidak bisa diubah ke tahun ajaran lain.

2. **Tampilan Pilih Kelas yang Adaptif (Wali Kelas vs Guru BK Multi-Kelas)**:
   - Menghapus daftar kelas *hardcoded/dummy* (`VII-A`, `VII-B`, dll.) pada inisialisasi state.
   - **Kondisi 1 — Hanya 1 Kelas (Wali Kelas 1 Rombel / Guru BK 1 Rombel)**:
     - Ditampilkan sebagai field kelas terkunci atau terpilih otomatis (`Kelas 7A • Wali Kelas`) sehingga guru langsung fokus pada daftar presensi kelasnya.
   - **Kondisi 2 — Lebih dari 1 Kelas (Wali Kelas 2 Rombel / Guru BK dengan Beberapa Kelas Binaan)**:
     - Cukup menampilkan **1 Dropdown Pilih Kelas Binaan** (`PILIH KELAS BINAAN`) di samping field Tahun Ajaran Aktif tanpa mengulang baris chip daftar kelas di bawahnya, sehingga tampilan tetap bersih dan ringkas.
   - **Kondisi 3 — Belum Memiliki Kelas (`_classes.isEmpty`)**:
     - Menampilkan kartu *Empty State* **"Belum Memiliki Kelas Binaan"** sesuai tampilan `empty-states.blade.php` di web.

3. **Pop-Up Input Presensi Khusus 1 Siswa vs Input Massal Sekelas**:
   - Tombol **`Input Presensi` / `Ubah`** pada kartu masing-masing siswa di daftar Presensi Harian membuka pop-up khusus **1 siswa tersebut saja** (`_openSingleStudentInputModal`), menampilkan identitas siswa, pilihan tanggal, status datang (`Hadir`, `Terlambat` + input menit, `Izin`, `Sakit`, `Alpa`), serta status pulang.
   - Sedangkan tombol **`Input Manual`** di bagian atas (Header Banner) tetap digunakan untuk **input presensi massal seluruh siswa satu kelas** (`_openInputManualModal`).

4. **Sesi Login Permanen (`SessionStorage`)**:
   - Sesi login Guru/Siswa disimpan secara permanen di perangkat menggunakan `FlutterSecureStorage` ([`session_storage.dart`](file:///home/sutris-remote/Documents/projek-laravel/apk-android-presensi/lib/core/storage/session_storage.dart)).
   - Saat aplikasi dibuka kembali, [`SplashScreen`](file:///home/sutris-remote/Documents/projek-laravel/apk-android-presensi/lib/features/auth/screens/splash_screen.dart) otomatis melanjutkan ke beranda portal tanpa meminta login ulang, kecuali pengguna menekan tombol **Keluar Portal (Logout)** secara eksplisit.

---

## 5. File yang Dimodifikasi

1. **Backend (`projek-absensi-barcode`)**:
   - [`app/Http/Controllers/Api/Mobile/GuruPresensiController.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Http/Controllers/Api/Mobile/GuruPresensiController.php)
2. **Frontend (`apk-android-presensi`)**:
   - [`lib/features/guru/screens/presensi_kelas_screen.dart`](file:///home/sutris-remote/Documents/projek-laravel/apk-android-presensi/lib/features/guru/screens/presensi_kelas_screen.dart)
   - [`lib/core/storage/session_storage.dart`](file:///home/sutris-remote/Documents/projek-laravel/apk-android-presensi/lib/core/storage/session_storage.dart)
   - [`lib/features/auth/screens/splash_screen.dart`](file:///home/sutris-remote/Documents/projek-laravel/apk-android-presensi/lib/features/auth/screens/splash_screen.dart)
   - [`lib/features/auth/screens/login_screen.dart`](file:///home/sutris-remote/Documents/projek-laravel/apk-android-presensi/lib/features/auth/screens/login_screen.dart)
   - [`lib/features/guru/screens/dashboard_guru_screen.dart`](file:///home/sutris-remote/Documents/projek-laravel/apk-android-presensi/lib/features/guru/screens/dashboard_guru_screen.dart)

