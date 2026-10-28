# Tahap 16: Perbaikan Skema Activity Log (`batch_uuid`)

## Tujuan
Memperbaiki error SQL 500 (`SQLSTATE[42S22]: Column not found: 1054 Unknown column 'batch_uuid' in 'field list'`) yang terjadi saat pencatatan aktivitas di Filament (misal: saat mengubah pengaturan hari kerja sekolah di `ListHariLiburs.php`).

## Penyebab
Pencatatan log aktivitas menggunakan `activity()` (Spatie Laravel Activitylog v4+), yang secara *default* menyertakan kolom `batch_uuid` saat melakukan pembaruan/pembuatan record aktivitas. Namun, tabel `activity_log` pada migrasi sebelumnya belum memiliki kolom `batch_uuid`.

## Perubahan yang Dilakukan

1. **Migrasi Baru**:
   - Membuat migrasi `database/migrations/2026_08_01_000000_add_batch_uuid_to_activity_log_table.php`.
   - Menambahkan kolom `batch_uuid` ber-tipe `uuid` (`nullable`) setelah kolom `properties` pada tabel `activity_log`.

2. **Pembaruan Blueprint Database**:
   - Memperbarui file `docs/blueprint/05-database.md` untuk menyertakan skema tabel `activity_log` secara lengkap termasuk kolom `batch_uuid`.

## File Terpengaruh & Ditambahkan
- `database/migrations/2026_08_01_000000_add_batch_uuid_to_activity_log_table.php` (Baru)
- `docs/blueprint/05-database.md` (Diperbarui)
- `docs/progres-development/fase-1-absensi/tahap-16-activity-log-patch.md` (Baru)

## Status
**Selesai & Diverifikasi (Migrasi berhasil dieksekusi via `php artisan migrate`)**
