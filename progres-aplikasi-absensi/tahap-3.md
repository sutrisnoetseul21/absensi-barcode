# TAHAP 3 — Data Master & Modul Admin Dasar (Filament)

**Status: Selesai ✅**

> **Goal**: Admin bisa mengelola data master (Tahun Ajaran, Kelas, Guru, Siswa) melalui panel Filament.
> **Blueprint Terkait**: 
> - [03-features.md](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/docs/blueprint/03-features.md)
> - [08-pages-routes.md](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/docs/blueprint/08-pages-routes.md)
> - [02-roles-permissions.md](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/docs/blueprint/02-roles-permissions.md)

---

## Keputusan Desain yang Sudah Dikunci

| Poin | Keputusan |
|------|-----------|
| Nama Resource | `GuruResource` (bukan TeacherResource), `SiswaResource` (bukan StudentResource) |
| Pembatasan akses | Kolom `is_super_admin` (boolean) di tabel `users` + override `canAccess()` |
| Username Guru (NIP ada) | `username = NIP` |
| Username Guru (NIP kosong) | Strip gelar → nama tanpa spasi → lowercase (contoh: `"Dr. H. Budi, M.Pd"` → `budisantoso`). Jika konflik → append angka |
| Username Siswa | `username = NISN` (selalu) |
| Barcode Siswa | `barcode_code = NISN` jika tidak diisi manual |
| Password default | Random 8 karakter, ditampilkan 1x via Filament Notification setelah create |
| Reset Password | Generate baru, tampilkan di modal, set `must_change_password = true` |
| Sumber kebenaran tahun ajaran aktif | Kolom `status` di `academic_years` (bukan `PengaturanSekolah`) |
| 1 tahun ajaran aktif | `afterSave()`: set aktif → arsipkan semua lain → sync `PengaturanSekolah.academic_year_id_active` |
| KelasAjaran assign wali kelas | `updateOrCreate` (upsert) — tidak error jika kombinasi kelas+tahun sudah ada |
| Enrollment duplikat | Cek di `beforeCreate()` → Notification danger + `halt()` (tidak error 500) |
| Validasi unique form | `->unique(ignoreRecord: true)` untuk `nip`, `nisn`, `barcode_code` |
| Filter siswa | SelectFilter Tahun Ajaran (default: aktif) + SelectFilter Kelas via `whereHas('enrollments')` |
| SchoolSettingsPage | Filament Custom Page (bukan Resource), singleton, `updateOrCreate` |
| Seed Super Admin | Akun baru terpisah via `SuperAdminSeeder` |

---

## Urutan Langkah Eksekusi

| Langkah | Deskripsi | Status |
|---------|-----------|--------|
| 3.0 | Migration `is_super_admin` + User model + SuperAdminSeeder | ⬜ |
| 3.1 | `TahunAjaranResource` (Super Admin only) + sync logic | ⬜ |
| 3.2 | `KelasResource` + `KelasAjaranRelationManager` (upsert) | ⬜ |
| 3.3 | `GuruResource` + auto-generate credentials + Reset Password | ⬜ |
| 3.4 | `SiswaResource` + auto-generate credentials + Reset Password + filter multi-tahun | ⬜ |
| 3.5 | `EnrollmentResource` + validasi duplikat | ⬜ |
| 3.6 | `SchoolSettingsPage` (Super Admin only, singleton) | ⬜ |

---

## File yang Telah Dibuat/Dimodifikasi (Referensi Code & Logika Singkat)

### Langkah 3.0 (Pre-flight)
- `[MODIFY]` [app/Models/User.php](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Models/User.php) — Tambah field `is_super_admin`, casting boolean, dan method `isSuperAdmin()`.
- `[NEW]` [database/seeders/SuperAdminSeeder.php](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/database/seeders/SuperAdminSeeder.php) — Seeder akun khusus superadmin (`superadmin@sekolah.com`).

### Langkah 3.1 (Tahun Ajaran)
- `[NEW]` [app/Filament/Resources/TahunAjarans/TahunAjaranResource.php](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Filament/Resources/TahunAjarans/TahunAjaranResource.php) — `canAccess()` dibatasi khusus Super Admin.
- `[NEW]` [app/Filament/Resources/TahunAjarans/Schemas/TahunAjaranForm.php](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Filament/Resources/TahunAjarans/Schemas/TahunAjaranForm.php)
- `[NEW]` [app/Filament/Resources/TahunAjarans/Tables/TahunAjaransTable.php](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Filament/Resources/TahunAjarans/Tables/TahunAjaransTable.php)
- `[NEW]` [app/Filament/Resources/TahunAjarans/Pages/CreateTahunAjaran.php](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Filament/Resources/TahunAjarans/Pages/CreateTahunAjaran.php) — `afterCreate()` otomatis mengarsipkan tahun lain jika status = aktif.
- `[NEW]` [app/Filament/Resources/TahunAjarans/Pages/EditTahunAjaran.php](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Filament/Resources/TahunAjarans/Pages/EditTahunAjaran.php) — `afterSave()` otomatis mengarsipkan tahun lain dan sync PengaturanSekolah.

### Langkah 3.2 (Kelas)
- `[NEW]` [app/Filament/Resources/Kelas/KelasResource.php](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Filament/Resources/Kelas/KelasResource.php)
- `[NEW]` [app/Filament/Resources/Kelas/RelationManagers/KelasAjaranRelationManager.php](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Filament/Resources/Kelas/RelationManagers/KelasAjaranRelationManager.php) — Tombol & Aksi "Assign Wali Kelas" (Hanya Super Admin), menggunakan logika *upsert* (`updateOrCreate`) untuk menghindari error constraint unique.

### Langkah 3.3 (Guru)
- `[NEW]` [app/Helpers/UsernameHelper.php](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Helpers/UsernameHelper.php) — Helper regex strip gelar dan generator unique username.
- `[NEW]` [app/Filament/Resources/Guru/GuruResource.php](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Filament/Resources/Guru/GuruResource.php)
- `[NEW]` [app/Filament/Resources/Guru/Pages/CreateGuru.php](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Filament/Resources/Guru/Pages/CreateGuru.php) — `mutateFormDataBeforeCreate()` auto-generate username via helper dan set password acak; `afterCreate()` menampilkan notifikasi password.
- `[NEW]` [app/Filament/Resources/Guru/Tables/GuruTable.php](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Filament/Resources/Guru/Tables/GuruTable.php) — Action "Reset Password" dengan notifikasi modal persisten.

### Langkah 3.4 (Siswa)
- `[NEW]` [app/Filament/Resources/Siswa/SiswaResource.php](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Filament/Resources/Siswa/SiswaResource.php)
- `[NEW]` [app/Filament/Resources/Siswa/Pages/CreateSiswa.php](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Filament/Resources/Siswa/Pages/CreateSiswa.php) — Auto-generate `barcode_code` dan `username` dari NISN, generate random password.
- `[NEW]` [app/Filament/Resources/Siswa/Tables/SiswaTable.php](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Filament/Resources/Siswa/Tables/SiswaTable.php) — Filter Select Tahun Ajaran (dengan `whereHas`) dan Reset Password action.

### Langkah 3.5 (Enrollment)
- `[NEW]` [app/Filament/Resources/Enrollment/EnrollmentResource.php](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Filament/Resources/Enrollment/EnrollmentResource.php)
- `[NEW]` [app/Filament/Resources/Enrollment/Pages/CreateEnrollment.php](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Filament/Resources/Enrollment/Pages/CreateEnrollment.php) — `beforeCreate()` validasi duplikasi untuk memastikan 1 siswa hanya terdaftar di 1 kelas per tahun ajaran; memanggil `$this->halt()` jika terdeteksi.

### Langkah 3.6 (Pengaturan Sekolah)
- `[NEW]` [app/Filament/Pages/SchoolSettingsPage.php](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Filament/Pages/SchoolSettingsPage.php) — Custom Page untuk Super Admin (bukan CRUD biasa), mengambil/menyimpan data singleton dari/ke tabel `pengaturan_sekolahs`.
- `[NEW]` [resources/views/filament/pages/school-settings.blade.php](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/resources/views/filament/pages/school-settings.blade.php)


---

## Catatan Teknis Penting

### Regex Strip Gelar Guru
```php
// Hapus gelar depan
$name = preg_replace('/\b(Prof|Dr|Drs|Ir|H|Hj|KH|Ustadz|Ustadzah)\.?\s*/iu', '', $name);
// Hapus gelar belakang (pola: huruf kapital, titik opsional, koma/titik sebelumnya)
$name = preg_replace('/,?\s*[A-Z][A-Za-z]{1,8}\.?\s*(\,?\s*[A-Z][A-Za-z]{1,8}\.?\s*)*$/u', '', $name);
// Hapus semua karakter non-huruf, lowercase
$username = strtolower(preg_replace('/[^a-zA-Z]/', '', $name));
```

### Upsert KelasAjaran
```php
KelasAjaran::updateOrCreate(
    ['class_id' => $ownerRecord->id, 'academic_year_id' => $data['academic_year_id']],
    ['teacher_id' => $data['teacher_id']]
);
```

### Sinkronisasi Tahun Ajaran Aktif
```php
// Di afterSave() Edit/Create TahunAjaran:
if ($this->record->status === 'aktif') {
    TahunAjaran::where('id', '!=', $this->record->id)->update(['status' => 'arsip']);
    PengaturanSekolah::updateOrCreate([], ['academic_year_id_active' => $this->record->id]);
}
```

### Validasi Duplikat Enrollment
```php
// Di beforeCreate() EnrollmentResource:
$exists = EnrollmentSiswa::where('student_id', $data['student_id'])
    ->where('academic_year_id', $data['academic_year_id'])
    ->exists();
if ($exists) {
    Notification::make()
        ->title('Siswa sudah terdaftar di tahun ajaran ini')
        ->body('Gunakan fitur Edit enrollment untuk mengubah kelasnya.')
        ->danger()->send();
    $this->halt();
}
```

---

## Checklist Verifikasi (Selesai Dieksekusi)

- `[x]` Kolom `is_super_admin` ada di DB, akun Super Admin baru bisa login di `/admin`
- `[x]` `TahunAjaranResource` hanya bisa diakses Super Admin
- `[x]` `SchoolSettingsPage` hanya bisa diakses Super Admin
- `[x]` Set tahun ajaran `aktif` → semua lain jadi `arsip` + `PengaturanSekolah` ter-sync otomatis
- `[x]` CRUD + restore untuk Kelas, Guru, Siswa berfungsi
- `[x]` Create Guru: username auto-generate, password random, notifikasi 1x
- `[x]` Create Siswa: username=NISN, barcode=NISN, password random, notifikasi 1x
- `[x]` Reset Password: password baru di modal, `must_change_password = true`
- `[x]` Assign wali kelas: upsert (tidak error duplikat), tombol hanya untuk Super Admin
- `[x]` Enrollment duplikat: Notification danger, tidak error 500
- `[x]` Field `nip`/`nisn`/`barcode_code` duplikat: pesan validasi jelas
- `[x]` Soft delete + restore aktif untuk Kelas, Guru, Siswa
- `[x]` Pengaturan Sekolah tersimpan dan terbaca dengan benar

---

## Akun Testing (Diisi Setelah Eksekusi Selesai)

| Role | URL Login | Email/Username | Password |
|------|-----------|----------------|---------|
| Super Admin | `/admin/login` | `superadmin@sekolah.com` | `superadmin123` |
| Admin biasa | `/admin/login` | `admin@sekolah.com` | `password` |

---

*Dokumen ini dibuat 2 Juli 2026. Eksekusi tahap 3 telah selesai.*

