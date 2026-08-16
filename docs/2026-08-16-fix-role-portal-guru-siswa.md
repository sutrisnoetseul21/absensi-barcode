# Update: Fix Role Otomatis Portal Guru & Siswa

**Tanggal:** 2026-08-16  
**Versi:** Hotfix  
**Status:** ✅ Selesai & Terverifikasi

---

## Latar Belakang

Ditemukan dua masalah akses portal:

1. **`/portal-siswa`** — Siswa tidak bisa login meskipun akun sudah ada. Error `403 Akses Ditolak`.
2. **`/portal-guru`** — Toggle "Izinkan Akses Portal Guru" di halaman Manajemen Akses Portal tampil **OFF** padahal guru sudah punya akun.

### Akar Masalah

Semua siswa (25 data) dan sebagian guru sudah memiliki `user_id` yang terhubung ke tabel `users`, **namun user-user tersebut tidak memiliki role Spatie Permission** (`siswa` / `wali_kelas`). Hal ini terjadi karena:

- Data lama diinput/diimpor **sebelum** kode `assignRole()` ditambahkan ke sistem.
- Command `app:migrate-auth` hanya memproses siswa/guru yang `user_id`-nya masih `NULL` — sehingga data yang sudah terhubung **tidak pernah mendapat role**.

---

## Perubahan yang Dilakukan

### 1. Bug Fix: `GuruImport.php`

**File:** `app/Imports/GuruImport.php`

Pada skenario **"guru lama diimport ulang"** (user sudah ada, hanya update nama/NIP), role `wali_kelas` tidak pernah dicek atau diassign.

```php
// SEBELUM — tidak ada pengecekan role
if ($user) {
    $user->update($userData);
}

// SESUDAH — cek dan assign role jika belum ada
if ($user) {
    $user->update($userData);
    if (!$user->hasRole('wali_kelas')) {
        $user->assignRole('wali_kelas');
    }
}
```

### 2. Perbaikan: `MigrateAuthToUsers.php`

**File:** `app/Console/Commands/MigrateAuthToUsers.php`

Ditambahkan blok sinkronisasi role siswa lama setelah loop utama. Sekarang saat command `app:migrate-auth` dijalankan (dengan atau tanpa `--only=siswa`), command ini **juga memperbaiki role** untuk siswa yang `user_id`-nya sudah terisi tapi belum punya role `siswa`.

### 3. Command Baru: `app:fix-siswa-roles`

**File:** `app/Console/Commands/FixSiswaRoles.php`

Command one-time untuk memberikan role `siswa` ke semua user yang terhubung ke data siswa tapi belum memiliki role tersebut.

```bash
php artisan app:fix-siswa-roles
```

**Output saat dijalankan:**
```
Memulai pengecekan role siswa...
 25/25 [▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓] 100%

| Keterangan                            | Jumlah |
| Role siswa diberikan (baru fix)       | 25     |
| Sudah punya role (di-skip)            | 0      |
| Total user dengan role siswa sekarang | 25     |
```

### 4. Command Baru: `app:fix-guru-roles`

**File:** `app/Console/Commands/FixGuruRoles.php`

Command one-time untuk memberikan role `wali_kelas` ke semua user yang terhubung ke data guru tapi belum memiliki role tersebut.

```bash
php artisan app:fix-guru-roles
```

---

## Status Role Setelah Fix

| Role | Jumlah User |
|------|-------------|
| `siswa` | 25 |
| `wali_kelas` | 3 |

---

## Cara Kerja Sekarang (Setelah Fix)

### Ketika Tambah Guru Baru

| Jalur | Role `wali_kelas` | Portal Guru Otomatis Aktif? |
|-------|-------------------|-----------------------------|
| Form Filament (tambah manual) | ✅ Auto-assign di `CreateGuru.php` | ✅ Ya |
| Import Excel (guru baru) | ✅ Auto-assign di `GuruImport.php` | ✅ Ya |
| Import Excel (guru lama, update) | ✅ Dicek & diassign (**baru difix**) | ✅ Ya |

### Ketika Tambah Siswa Baru

| Jalur | Role `siswa` | Portal Siswa Otomatis Aktif? |
|-------|--------------|------------------------------|
| Form Filament (tambah manual) | ✅ Auto-assign di `CreateSiswa.php` | ✅ Ya |
| Import Excel (siswa baru) | ✅ Auto-assign di `SiswaBaruImport.php` | ✅ Ya |

---

## Langkah Jika Server Masih Pakai DB Lama

Jika melakukan deploy ke server dengan database lama (sebelum fix ini), jalankan command berikut setelah deploy:

```bash
# 1. Fix role guru lama
php artisan app:fix-guru-roles

# 2. Fix role siswa lama
php artisan app:fix-siswa-roles

# 3. Reset cache permission Spatie
php artisan permission:cache-reset
```

> **Catatan:** Command ini aman dijalankan berulang kali. Jika semua role sudah benar, command akan skip dan tidak melakukan perubahan apapun.

---

## File yang Diubah

| File | Jenis Perubahan |
|------|-----------------|
| `app/Imports/GuruImport.php` | Bug fix — assign role saat guru lama diimport ulang |
| `app/Console/Commands/MigrateAuthToUsers.php` | Enhancement — tambah sync role siswa lama |
| `app/Console/Commands/FixSiswaRoles.php` | **[BARU]** Command one-time fix role siswa |
| `app/Console/Commands/FixGuruRoles.php` | **[BARU]** Command one-time fix role guru |

---

## Konfigurasi Kamera Scanner (Update Terpisah, Hari yang Sama)

Selain fix role, pada sesi yang sama dilakukan update konfigurasi kamera pada semua halaman scanner:

**Problem:** Halaman scanner default ke kamera depan di smartphone, padahal seharusnya kamera belakang.

**Fix:** Ditambahkan logika deteksi kamera belakang otomatis dengan fallback ke kamera pertama yang tersedia.

**File yang diubah:**
- `resources/views/livewire/attendance-kiosk.blade.php`
- `resources/views/livewire/attendance-kiosk-nis.blade.php`
- `resources/views/livewire/petugas-perpus-sirkulasi.blade.php`
- `resources/views/livewire/sirkulasi-kiosk.blade.php`
- `resources/views/livewire/kunjungan-kiosk.blade.php`
