# Progres Aplikasi Absensi - Tahap 2: Multi-Guard Authentication

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

---

Dokumen ini mencatat seluruh perubahan, keputusan desain, dan hasil pekerjaan pada Tahap 2.

---

## Ringkasan Fitur

Tahap 2 mengimplementasikan sistem autentikasi multi-guard sehingga tiga peran pengguna (Admin, Wali Kelas, Siswa) dapat login dan logout secara independen tanpa saling campur.

---

## Detail Implementasi

### 1. Konfigurasi Guard (`config/auth.php`)

Tiga guard didaftarkan:
- `web` (default) → Provider model `User` (tabel `users`) → dikelola Filament Admin Panel
- `wali_kelas` → Provider model `Teacher` (tabel `teachers`)
- `siswa` → Provider model `Siswa` (tabel `students`)

**Catatan penting model:**
Model `Teacher` dan `Siswa` wajib implement `Authenticatable` dan menggunakan trait `HasUuids` karena primary key UUID:
```php
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Teacher extends Authenticatable {
    use HasUuids;
    protected $keyType = 'string';
    public $incrementing = false;
}
```

### 2. Route Group per Guard

```php
// Wali Kelas
Route::middleware('auth:wali_kelas')->prefix('wali-kelas')->group(...)

// Siswa
Route::middleware('auth:siswa')->prefix('siswa')->group(...)
```

### 3. Middleware Custom

- `EnsureIsWaliKelas` — redirect ke `/wali-kelas/login` jika belum login
- `EnsureIsSiswa` — redirect ke `/siswa/login` jika belum login

### 4. Halaman Login & Logout

| Guard | Login URL | Logout URL |
|-------|-----------|-----------|
| Admin | `/admin/login` (Filament built-in) | Filament built-in |
| Wali Kelas | `/wali-kelas/login` (Livewire component) | `/wali-kelas/logout` |
| Siswa | `/siswa/login` (Livewire component) | `/siswa/logout` |

### 5. Flow Ganti Password Wajib (`must_change_password`)

Untuk Wali Kelas dan Siswa, jika `must_change_password = true` saat login:
- Redirect otomatis ke halaman ganti password sebelum bisa mengakses halaman lain
- Setelah berhasil ganti password → `must_change_password` di-set `false` → redirect ke dashboard

### 6. Filament Panel Admin

- Guard: `web`
- Prefix: `/admin`
- Login page: `/admin/login`

---

## Verifikasi Tahap 2

- ✅ Login sebagai Admin melalui `/admin/login` berhasil
- ✅ Login sebagai Wali Kelas di `/wali-kelas/login` berhasil
- ✅ Login sebagai Siswa di `/siswa/login` berhasil
- ✅ Guard tidak saling campur
- ✅ `must_change_password = true` → paksa ganti password sebelum akses halaman lain

---
*Dokumen ini dibuat pada **2 Juli 2026**.*
