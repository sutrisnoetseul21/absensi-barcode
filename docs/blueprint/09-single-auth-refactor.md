# Arsitektur Single-Auth (Sistem Login Terpusat)

## Latar Belakang
Sistem pada awalnya dirancang dengan multi-guard otentikasi di mana Admin login menggunakan tabel `users` (via email), Wali Kelas menggunakan tabel `teachers` (via username), dan Siswa menggunakan tabel `students` (via username).
Seiring dengan kebutuhan di mana seorang Guru bisa merangkap menjadi Admin (Admin Akademik/Presensi), sistem login terpisah ini menyebabkan akun ganda (satu orang memiliki 2 username/password yang berbeda).

Untuk menyelesaikannya, otentikasi disatukan (Single-Auth) ke dalam tabel `users`. Tabel `teachers` dan `students` hanya akan berfungsi sebagai tabel "Profil".

## Struktur Database Baru

### 1. Tabel `users`
Tabel ini akan menyimpan seluruh kredensial login.
- `id` (UUID)
- `name`
- `email` (Digunakan sebagai login. Format: `nip@sekolah.sch.id` atau `nisn@sekolah.sch.id`)
- `password`
- `must_change_password` (Dipindahkan dari teachers/students)

### 2. Tabel `teachers`
- Menghapus kolom `username`, `password`, `must_change_password`.
- Menambahkan kolom `user_id` (FK ke `users.id`).

### 3. Tabel `students`
- Menghapus kolom `username`, `password`, `must_change_password`.
- Menambahkan kolom `user_id` (FK ke `users.id`).

## Alur Kerja (Workflow)

### A. Saat Import Data Excel (Guru / Siswa)
Format Excel **TIDAK BERUBAH** (tidak perlu ada kolom email).
Proses di belakang layar (pada class `GuruImport` dan `SiswaBaruImport`):
1. Sistem membaca NIP/NISN.
2. Sistem *generate* email otomatis: `nip@sekolah.sch.id` (Jika NIP kosong, gunakan `namalengkap_guru@sekolah.sch.id` yang dihilangkan spasinya).
3. Buat/Update baris di tabel `users` menggunakan email tersebut dan password default.
4. Berikan Role standar dari Spatie (contoh: `wali_kelas` atau `siswa`).
5. Buat profil di tabel `teachers` atau `students` dengan menautkan `user_id` yang baru dibuat.

### B. Login & Hak Akses
- **Otentikasi**: Semua portal login (`/admin`, `/wali-kelas`, `/siswa`) akan diarahkan untuk mengecek kredensial ke tabel `users` (Guard: `web`).
- **Otorisasi Panel Admin**: Filament Shield akan mengecek apakah `User` tersebut memiliki role `admin_akademik_view`, dll.
- **Otorisasi Portal Wali Kelas**: Sistem akan mengecek apakah `User` memiliki role `wali_kelas` DAN memiliki profil di tabel `teachers`.
- **User Experience (UX) Login**: Di halaman login Wali Kelas / Siswa, inputnya bisa berlabel "Email / NIP / NISN". Saat form di-*submit*, sistem mengecek: jika *input* tidak mengandung karakter `@`, maka otomatis tambahkan string `@sekolah.sch.id` sebelum mencocokkan ke database. Sehingga Guru dan Siswa tetap merasa login pakai NIP/NISN.

## Rencana Migrasi (Transisi)
1. Buat migration baru untuk `add_user_id_to_teachers_and_students` dan menghapus kolom password lama.
2. Buat skrip *Command* (`php artisan app:migrate-auth`) untuk membaca data `teachers` dan `students` yang sudah ada di database saat ini, lalu otomatiskan pembuatan akun `users`-nya sebelum kolom password lama dihapus.
3. Update Form Login Custom (Livewire).
4. Bersihkan file `config/auth.php` dengan menghapus guard `wali_kelas` dan `siswa` yang sudah usang.

## Addendum: Keputusan Final Pasca-Audit

1. **Domain Email**: Gunakan domain situs sekolah itu sendiri sebagai basis email login. Tambahkan variabel `SCHOOL_EMAIL_DOMAIN` di `.env` dan `.env.example`, contoh nilai: `SCHOOL_EMAIL_DOMAIN=smpn1majenang.sch.id`. Semua generate email (NIP/NISN@domain) HARUS mengambil dari `config('school.email_domain')` — buat file config baru `config/school.php` yang membaca dari env ini. JANGAN hardcode domain di manapun.

2. **Logika Deteksi Admin vs Guru (pasca-refactor)**: Setelah semua login lewat guard `web`, gunakan logika berikut untuk menggantikan pengecekan guard lama di titik-titik yang menyimpan "siapa aktor input" (misalnya kolom polymorphic `manual_input_by_type`):
```php
   $isGuru = Auth::user()->hasRole('wali_kelas') && Auth::user()->teacher !== null;
   $actorType = $isGuru ? \App\Models\Guru::class : \App\Models\User::class;
   $actorName = $isGuru ? Auth::user()->teacher->name : Auth::user()->name;
```
Untuk keperluan tampilan/log input absen, cukup gunakan `$actorName` (nama Guru saja), tidak perlu detail role/ID tambahan. Catat keputusan ini sebagai acuan wajib untuk Fase 2.

3. **Checklist Guard Usage untuk Fase 2**:
- [ ] `app/Http/Middleware/EnsureIsSiswa.php`
- [ ] `app/Http/Middleware/EnsureIsWaliKelas.php`
- [ ] `app/Livewire/ManualAttendanceInput.php`
- [ ] `app/Livewire/WaliKelasLogin.php`
- [ ] `app/Livewire/WaliKelasDashboard.php`
- [ ] `app/Livewire/SiswaDashboard.php`
- [ ] `app/Livewire/SiswaLogin.php`
- [ ] `app/Livewire/WaliKelasStudentDetail.php`
- [ ] `routes/web.php`
- [ ] `resources/views/livewire/wali-kelas/header.blade.php`
