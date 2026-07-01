# TAHAP 2 — Multi-Guard Authentication

**Status: Selesai ✅**

> **Goal**: 3 guard login terpisah berfungsi penuh (`web` untuk admin, `wali_kelas`, `siswa`).

## Checklist yang Telah Dikerjakan:

- [x] **Konfigurasi Auth**: Pemisahan 3 guard (`web`, `wali_kelas`, `siswa`) pada `config/auth.php` yang dipetakan ke provider model masing-masing (`User`, `Guru`, `Siswa`).
- [x] **Update Model**: Model `Guru` dan `Siswa` telah diperbarui dengan `$incrementing = false`, `$keyType = 'string'`, serta *mutator* `password => hashed` di `casts()`.
- [x] **Perbaikan MorphMap**: Meluruskan `Relation::morphMap` pada `AppServiceProvider` dari `Teacher::class` ke `Guru::class` untuk *alias* `wali_kelas`.
- [x] **Middleware & Guest Redirect**: Pembuatan `EnsureIsWaliKelas` dan `EnsureIsSiswa`, serta memodifikasi `bootstrap/app.php` dengan *redirect* pintar untuk `guest` berdasarkan guard yang aktif (redirect otomatis jika user sudah login).
- [x] **Livewire Components (Login)**: Pembuatan komponen *full-page* `WaliKelasLogin` dan `SiswaLogin` yang dilengkapi **Rate Limiter** spesifik per *Guard+IP+Username* dan `session()->regenerate()` pasca login.
- [x] **Force Change Password**: Implementasi komponen `ForceChangePassword` dengan validasi password saat ini (*current_password*), yang secara paksa akan muncul bagi user yang status `must_change_password`-nya bernilai `true`.
- [x] **Rute Otentikasi**: Penetapan rute spesifik `/wali-kelas/login`, `/siswa/login`, dasbor masing-masing, dan *endpoint* *logout*.

## Hasil Verifikasi:

- [x] *MorphMap* terbukti merujuk alias `wali_kelas` ke `App\Models\Guru`.
- [x] Masing-masing guard (`web`, `wali_kelas`, `siswa`) terisolasi dan tidak bisa saling *bypass*.
- [x] *Rate limiter* sukses membendung upaya tebakan password brute-force pada rute login.
- [x] Perlindungan halaman aktif: User dengan `must_change_password` = true wajib mengganti password dan dilarang mengakses rute lainnya sebelum selesai.

---

## 🔑 Akun Testing (Hasil Seeder)

Untuk mencoba secara langsung hasil dari Tahap 2, Anda dapat menggunakan rincian login berikut yang sudah disiapkan di dalam *database*:

**1. Admin (Panel Filament)**
- **URL Login**: `http://localhost:8000/admin/login` (atau `/admin`)
- **Email**: `admin@sekolah.com`
- **Password**: `password`

**2. Wali Kelas (Guru)**
- **URL Login**: `http://localhost:8000/wali-kelas/login`
- **Username**: `guru123`
- **Password**: `password`
- *(Catatan: Saat login pertama kali, sistem akan langsung me-redirect ke halaman wajib ganti password karena status `must_change_password` aktif. Masukkan "password" di kolom Password Saat Ini)*

**3. Siswa**
- **URL Login**: `http://localhost:8000/siswa/login`
- **Username / NISN**: `1234567890`
- **Password**: `password`
- *(Catatan: Sama seperti Wali Kelas, akan di-redirect untuk wajib ganti password saat login).*
