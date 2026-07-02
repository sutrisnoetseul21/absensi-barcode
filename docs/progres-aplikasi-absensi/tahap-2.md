# Progres Aplikasi Absensi - Tahap 2: Multi-Guard Authentication

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
