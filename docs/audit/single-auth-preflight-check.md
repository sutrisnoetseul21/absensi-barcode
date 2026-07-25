# Laporan Audit Preflight: Single-Auth Refactor

Berdasarkan pengecekan read-only pada codebase dan database saat ini, berikut adalah hasil investigasi mendetail sebelum kita melakukan eksekusi refactor.

## 1. Guard Name di Spatie Permission
- **Temuan Role & Guard**: Terdapat 11 role di dalam tabel `roles` (`super_admin`, `panel_user`, dan varian *view/editor/admin* untuk akademik, master, presensi). Seluruhnya menggunakan `guard_name = 'web'`.
- **Temuan model_has_roles**: Hanya `role_id = 2` (`super_admin`) yang saat ini terkait ke 1 user.
- **Status `wali_kelas` & `siswa`**: Kedua nama ini murni terdaftar sebagai **Guard** di `config/auth.php` (dengan provider `gurus` dan `siswas`), dan BUKAN sebagai entri role di tabel Spatie.
- ✅ **Kesimpulan**: Aman. Spatie Permission tidak tercemar oleh guard-guard lain. Setelah refactoring, guard `web` dapat melayani seluruh sistem dengan mulus.

## 2. Potensi Collision Email
- **Data NULL/Kosong**:
  - Tabel `teachers`: **0** baris dengan NIP kosong.
  - Tabel `students`: **0** baris dengan NISN kosong.
- **Duplikasi**: 
  - Tidak ditemukan adanya NIP yang duplikat di tabel `teachers`.
  - Tidak ditemukan NISN yang duplikat di tabel `students`.
- **Constraint Tabel Users**: File migrasi `users` (`0001_01_01_000000_create_users_table.php`) memiliki perintah `$table->string('email')->unique();`.
- ✅ **Kesimpulan**: Sangat aman. Tidak akan ada bentrok/duplikasi (collision) karena kualitas data induk sudah sangat bersih tanpa ada NIP/NISN kembar atau kosong.

## 3. Struktur Skrip Migrasi yang Akan Dibuat
- **Skala Data Saat Ini**:
  - Total `teachers`: 1
  - Total `students`: 802
- ✅ **Kesimpulan**: Skala data tergolong ringan. Proses migrasi pemindahan data ke tabel `users` tidak akan mengalami kendala *timeout* atau *memory limit*. Skrip Artisan biasa (tanpa chunking berlebihan) akan selesai dalam 1-2 detik.

## 4. Constraint `user_id`
- **File Migrasi Saat Ini**: File `2026_07_01_144310_create_teachers_table.php` dan `2026_07_01_144310_create_students_table.php` sama sekali belum memiliki definisi maupun *foreign key* `user_id`.
- ✅ **Kesimpulan**: Aman. Menambahkan kolom baru via migrasi tidak akan mengalami bentrok dengan struktur warisan.

## 5. Sesi Aktif / Guard Usage
- **Temuan Pemakaian Guard Eksplisit**: 
  Metode `Auth::guard('wali_kelas')` atau `Auth::guard('siswa')` digunakan sangat intensif, tersebar di:
  1. Komponen Livewire: `WaliKelasDashboard.php`, `ManualAttendanceInput.php`, `WaliKelasStudentDetail.php`, `SiswaLogin.php`, `SiswaDashboard.php`, `WaliKelasLogin.php`.
  2. Middleware: `EnsureIsWaliKelas.php`, `EnsureIsSiswa.php`.
  3. Routing & View: `routes/web.php` dan header *blade templates*.
- ⚠️ **Kesimpulan**: Terdapat risiko tinggi jika ada satu titik yang terlewat diubah. Saat refactor Fase 2, seluruh panggilan ini harus diarahkan ke satu logika sentral yang mengecek tabel `users` melalui guard default.

## 6. Hardcoded Domain
- **Temuan String Domain**: Tidak ditemukan *hardcode* `@sekolah.sch.id` di seluruh sistem.
  Namun, domain `.sch.id` di-*hardcode* di *blade templates* cetak kartu (`presensi.smpn1majenang.sch.id`).
- ⚠️ **Kesimpulan**: Ide menggunakan gabungan nama + `@domainutama` sebagai email benar-benar baru dan belum ada variabelnya di `.env`. Disarankan kita menambahkan variabel konfigurasi baru (misal: `SCHOOL_EMAIL_DOMAIN=smpn1majenang.sch.id`) di `.env` agar dinamis dan bisa diubah sewaktu-waktu.

## 7. Status `must_change_password` Saat Ini
- **Struktur**: Berupa kolom `boolean` dengan `default(false)`.
- **Sebaran Data**:
  - `teachers`: 0 (True), 1 (False)
  - `students`: 0 (True), 802 (False)
- ✅ **Kesimpulan**: Aman. Perpindahan flag ini ke tabel `users` tidak akan membebani UX secara tiba-tiba karena saat ini semua berstatus false.

## 8. Alur Import Saat Ini
- **`GuruImport.php`**: Menggunakan pola *Update or Create*. Jika NIP/Nama sudah ada di DB, ia akan memperbarui (update) data tersebut.
- **`SiswaBaruImport.php`**: Murni hanya untuk Create (Insert). Jika NISN/NIS sudah ada di database, import akan men-skip baris tersebut tanpa melakukan pembaruan (ditolak masuk log).
- ⚠️ **Kesimpulan**: Pola ini harus diperhatikan. Saat memodifikasi file import untuk menghasilkan User terlebih dahulu:
  - Import Guru wajib melakukan cek apakah `User` sudah ada; jika ya, update datanya (atau abaikan update passwordnya jika tidak diminta).
  - Import Siswa cukup melakukan cek validasi awal; jika sudah ada NISN, langsung skip (tidak berisiko korupsi tabel `users`).

## 9. Detail Guard Usage & Konfirmasi Domain/Import (Audit Susulan)

### A. Detail Lengkap Guard Usage
Berdasarkan pencarian menyeluruh, berikut adalah titik-titik pemakaian *guard* usang yang harus di-refactor:

**`app/Http/Middleware/EnsureIsSiswa.php`** [MIDDLEWARE]
- Baris 19: `if (!Auth::guard('siswa')->check()) {`
- Baris 23: `$user = Auth::guard('siswa')->user();`

**`app/Http/Middleware/EnsureIsWaliKelas.php`** [MIDDLEWARE]
- Baris 19: `if (!Auth::guard('wali_kelas')->check()) {`
- Baris 23: `$user = Auth::guard('wali_kelas')->user();`

**`app/Livewire/ManualAttendanceInput.php`** [SESSION CHECK & RELATION DATA]
- Baris 69: `if ($existing && $existing->is_manual_input === false && Auth::guard('wali_kelas')->check()) {`
- Baris 75-76: `$actor = Auth::guard('wali_kelas')->check() ? Auth::guard('wali_kelas')->user() ...`
- Baris 79: `$actorType = Auth::guard('wali_kelas')->check() ? 'Guru' : 'Admin';`
*⚠️ Catatan: Method `->user()` dipanggil langsung pada guard. Saat disatukan ke `web`, kita harus membedakan apakah user ini Guru atau Admin berdasarkan relasi atau rolenya.*

**`app/Livewire/WaliKelasLogin.php`** [LOGIN CHECK]
- Baris 33: `if (Auth::guard('wali_kelas')->attempt(['username' => $this->username, 'password' => $this->password], $this->remember)) {`

**`app/Livewire/WaliKelasDashboard.php`** [SESSION CHECK & RELATION DATA]
- Baris 105 & 136: `... || !Auth::guard('wali_kelas')->check();`
- Baris 108: `$actor = Auth::guard('wali_kelas')->user();`
- Baris 258: `if ($existing && $existing->is_manual_input === false && Auth::guard('wali_kelas')->check()) {`
- Baris 266: `$actor = Auth::guard('wali_kelas')->check() ? Auth::guard('wali_kelas')->user() : Auth::guard('web')->user();`
- Baris 267: `$actorType = Auth::guard('wali_kelas')->check() ? 'Guru' : 'Admin';`
- Baris 292: `'manual_input_by_type' => Auth::guard('wali_kelas')->check() ? \App\Models\Guru::class : \App\Models\User::class,`

**`app/Livewire/SiswaDashboard.php`** [RELATION DATA]
- Baris 29: `$this->student = Auth::guard('siswa')->user();`
*⚠️ Catatan: Jika login via `web`, kode ini harus berubah menjadi `Auth::guard('web')->user()->student;` (mengambil relasi).*

**`app/Livewire/SiswaLogin.php`** [LOGIN CHECK & LOGOUT]
- Baris 33: `if (Auth::guard('siswa')->attempt(['nisn' => $this->nisn, 'password' => $this->password], $this->remember)) {`
- Baris 34: `$student = Auth::guard('siswa')->user();`
- Baris 40: `Auth::guard('siswa')->logout();`

**`app/Livewire/WaliKelasStudentDetail.php`** [RELATION DATA]
- Baris 33: `$actor = Auth::guard('wali_kelas')->user();`

**`routes/web.php`** [LOGOUT]
- Baris 78: `Auth::guard('wali_kelas')->logout();`
- Baris 94: `Auth::guard('siswa')->logout();`

**`resources/views/livewire/wali-kelas/header.blade.php`** [VIEW/BLADE]
- Baris 54: `@if(Auth::guard('wali_kelas')->check())`

### B. Konfirmasi Domain Email
- **Hardcode Domain `.sch.id`**: Hanya ditemukan string `presensi.smpn1majenang.sch.id` (digunakan sebagai *footer URL* cetak) pada file-file PDF berikut:
  - `resources/views/pdf/kartu-osis-massal.blade.php` (Baris 429)
  - `resources/views/pdf/kartu-login-siswa-massal.blade.php` (Baris 429)
  - `resources/views/pdf/kartu-osis.blade.php` (Baris 422)
  - `resources/views/pdf/kartu-login-siswa.blade.php` (Baris 422)
- **Cek `.env.example`**: Belum ada variabel konfigurasi apapun yang mendefinisikan institusi sekolah. Saat ini file hanya berisi standar Laravel (`APP_NAME`, `APP_URL`, `DB_*`, dll).
- ✅ **Kesimpulan**: Domain `@sekolah.sch.id` di *blueprint* tidak akan bentrok dengan *hardcode* apapun. Saya merekomendasikan kita menambahkan `SCHOOL_EMAIL_DOMAIN=sekolah.sch.id` di `.env` (beserta di `.env.example`).

### C. Perilaku Import Siswa vs Guru
**`app/Imports/SiswaBaruImport.php`** (Kasus: NISN sudah ada)
```php
// Validasi: NISN sudah ada di DB
if (isset($existingNisns[$nisn])) {
    $this->results[] = array_merge($baseRow, [
        'status'       => 'skip',
        'status_label' => '❌ Skip',
        'keterangan'   => "NISN {$nisn} sudah terdaftar di database.",
    ]);
    continue;
}
```
*⚠️ Efek Refactor: Logika `skip` ini sangat aman. Karena datanya dilewati, ia tidak akan memanggil fungsi `User::create()` secara ganda. Laporan diletakkan pada array `$this->results` yang nanti di-*render* di UI tabel.*

**`app/Imports/GuruImport.php`** (Kasus: Update or Create)
```php
$dataToSave = [
    'name' => $name,
    'nip' => $nip,
    'username' => $username,
    'must_change_password' => false,
];
// Hanya update password jika diisi di Excel, atau jika ini record baru
if (!$existingGuru || $passwordVal !== '') {
    $dataToSave['password'] = $password; // hashed automatically by cast
}
if ($existingGuru) {
    $existingGuru->update($dataToSave);
} else {
    Guru::create($dataToSave);
}
```
*⚠️ Efek Refactor: Update hanya menimpa array `$dataToSave`. Jika kita menambahkan `user_id` di tabel Guru, maka update ini **TIDAK AKAN** menghapus atau me-reset `user_id` yang sudah ada karena kolom `user_id` tidak dideklarasikan di dalam array `$dataToSave`. Ini sangat aman.*
