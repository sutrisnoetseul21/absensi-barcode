# 🚀 Panduan Migrasi Basis Data & Database Seeder

Dokumen ini menjelaskan prosedur menjalankan migrasi, struktur urutan seeder, serta kredensial akun bawaan setelah reset database.

---

## 1. Menjalankan Migrasi

Untuk menjalankan migrasi perubahan skema baru:
```bash
php artisan migrate
```

Untuk memeriksa status migrasi:
```bash
php artisan migrate:status
```

---

## 2. Prosedur Reset & Seeding Bersih

> [!WARNING]
> Perintah `migrate:fresh` akan menghapus seluruh data yang ada di database. Pastikan melakukan backup terlebih dahulu sebelum menjalankan perintah ini di server produksi.

```bash
php artisan migrate:fresh --seed
```

### Urutan Eksekusi Seeder (`DatabaseSeeder.php`):
1. **KelasSeeder**: Rombongan belajar awal (VII-A s/d IX-H).
2. **JabatanSeeder**: Daftar jabatan struktural guru dan staf.
3. **SuperAdminSeeder**: Akun super administrator utama.
4. **KategoriBukuSeeder & KlasifikasiDdcSeeder**: Master kategori dan kode DDC perpustakaan.
5. **RoleAdminSeeder, RoleAkademikSeeder, RolePresensiSeeder, RolePerpustakaanSeeder**: Hak akses modul Spatie Permission.
6. **GuruAksesSemuaKelasSeeder**: Permission akses lintas kelas.
7. **WebSarpraSeeder, WebPillarSeeder, WebFaqSeeder**: Konten profil publik awal.
8. **SpikapRoleSeeder**: Hak akses modul penanganan aduan & BK.
9. **PresensiDailyReportSettingSeeder & PresensiNotificationSettingSeeder**: Pengaturan notifikasi pesan WhatsApp.

---

## 3. Kredensial Akun Bawaan (Default Accounts)

Setelah proses seed selesai, akun default berikut dapat langsung digunakan untuk login:

| Akun / Role | Email | Password Default | Hak Akses |
| :--- | :--- | :--- | :--- |
| **Super Admin** | `superadmin@haflaitsolution.id` | `SuperAdmin123!` | Akses penuh: Tahun Ajaran, Pengaturan Sekolah, Konfigurasi Sistem. |
| **Admin Utama** | `admin@sekolah.com` | `password` | Akun admin Filament umum. |
