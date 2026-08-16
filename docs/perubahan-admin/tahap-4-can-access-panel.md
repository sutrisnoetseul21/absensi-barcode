# Tahap 4 — Update `canAccessPanel()` (Izinkan Semua Role Login ke `/admin`)

**Status:** ⏳ Belum dikerjakan (tunggu Tahap 3 selesai & disetujui)  
**Estimasi waktu:** ~10 menit  
**Jumlah file yang diubah:** 1 file

---

## Tujuan

Saat ini, panel `/admin` hanya bisa diakses oleh `super_admin` dan `is_super_admin = true`.
Role lain (`admin_akademik`, `admin_presensi`, `admin_perpustakaan`, `petugas_perpustakaan`)
akan ditolak masuk.

Tahap ini mengizinkan **semua role admin** untuk bisa login ke `/admin`.

---

## File yang Diubah

### [MODIFY] `app/Models/User.php`

**Metode: `canAccessPanel()`**

---

## Kode Saat Ini (Sebelum)

```php
public function canAccessPanel(Panel $panel): bool
{
    // Super Admin (role Spatie) boleh masuk ke semua panel
    $superAdminRole = config('filament-shield.super_admin.name', 'super_admin');
    if ($this->hasRole($superAdminRole)) {
        return true;
    }

    // Fallback: is_super_admin kolom lama
    if ($this->isSuperAdmin()) {
        return true;
    }

    $panelRolePrefixes = [
        'admin-akademik' => 'admin_akademik',
        'admin-presensi' => 'admin_presensi',
    ];

    $panelId = $panel->getId();
    
    // Panel utama 'admin' (Super Admin saja)
    if ($panelId === 'admin') {
        return false; // ❌ Role lain ditolak
    }

    if (isset($panelRolePrefixes[$panelId])) {
        $prefix = $panelRolePrefixes[$panelId];
        return $this->roles->contains(function ($role) use ($prefix) {
            return str_starts_with($role->name, $prefix);
        });
    }

    return false;
}
```

---

## Kode Sesudah

```php
public function canAccessPanel(Panel $panel): bool
{
    // Super Admin (role Spatie) boleh masuk ke semua panel
    $superAdminRole = config('filament-shield.super_admin.name', 'super_admin');
    if ($this->hasRole($superAdminRole)) {
        return true;
    }

    // Fallback: is_super_admin kolom lama
    if ($this->isSuperAdmin()) {
        return true;
    }

    $panelId = $panel->getId();

    // Panel utama '/admin' — izinkan semua role admin
    if ($panelId === 'admin') {
        $adminRolePrefixes = [
            'admin_akademik',
            'admin_presensi',
            'admin_perpustakaan',
            'petugas_perpustakaan',
        ];
        foreach ($adminRolePrefixes as $prefix) {
            if ($this->roles->contains(fn($role) => str_starts_with($role->name, $prefix))) {
                return true;
            }
        }
        return false;
    }

    // Panel lama (tetap berfungsi sebagai backup)
    $panelRolePrefixes = [
        'admin-akademik'     => 'admin_akademik',
        'admin-presensi'     => 'admin_presensi',
        'admin-perpustakaan' => ['admin_perpustakaan', 'petugas_perpustakaan'],
    ];

    if (isset($panelRolePrefixes[$panelId])) {
        $prefixes = (array) $panelRolePrefixes[$panelId];
        foreach ($prefixes as $prefix) {
            if ($this->roles->contains(fn($role) => str_starts_with($role->name, $prefix))) {
                return true;
            }
        }
    }

    return false;
}
```

---

## Penjelasan Perubahan

| Bagian | Sebelum | Sesudah |
|---|---|---|
| Panel `admin` | Hanya `super_admin` | + `admin_akademik`, `admin_presensi`, `admin_perpustakaan`, `petugas_perpustakaan` |
| Panel `admin-perpustakaan` | Tidak diatur (selalu false) | Tambah rule untuk `admin_perpustakaan` & `petugas_perpustakaan` |
| Panel lama lainnya | Tetap sama | Tetap sama (tidak diubah) |

---

## Catatan Penting

> [!NOTE]
> Setelah tahap ini, semua role yang login ke `/admin` akan **melihat semua menu**
> di sidebar (Akademik, Presensi, Perpustakaan). Pengaturan visibilitas menu per role
> adalah pekerjaan lanjutan yang akan diintegrasikan ke halaman
> **Manajemen Akses Portal** sesuai kesepakatan.

---

## Cara Verifikasi Setelah Tahap 4

1. Login sebagai akun dengan role `admin_akademik` → cek bisa masuk `/admin`
2. Login sebagai akun dengan role `admin_presensi` → cek bisa masuk `/admin`
3. Login sebagai akun dengan role `petugas_perpustakaan` → cek bisa masuk `/admin`
4. Login sebagai `super_admin` → tetap bisa masuk semua panel
5. Cek URL panel lama masih bisa diakses sesuai role masing-masing:
   - `admin_akademik` → `/admin-akademik` ✅
   - `admin_presensi` → `/admin-presensi` ✅
   - `petugas_perpustakaan` → `/admin-perpustakaan` ✅
