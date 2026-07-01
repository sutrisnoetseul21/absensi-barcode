# TAHAP 1 — Skema Database (Migrations)

**Status: Selesai ✅**

> **Goal**: Semua tabel terbuat di MySQL sesuai blueprint `05-database.md` dengan primary key UUID.

## Checklist yang Telah Dikerjakan:

- [x] **Migration 1**: `create_academic_years_table` (Tahun Ajaran)
- [x] **Migration 2**: `create_classes_table` (Template Nama Kelas)
- [x] **Migration 3**: `create_school_settings_table` (Pengaturan Sekolah)
- [x] **Migration 4**: `create_teachers_table` (Guru / Wali Kelas)
- [x] **Migration 5**: `create_students_table` (Siswa)
- [x] **Migration 6**: `create_class_academic_year_table` (Pivot Wali Kelas per Tahun)
- [x] **Migration 7**: `create_student_enrollments_table` (Riwayat Kelas Siswa)
- [x] **Migration 8**: `create_holidays_table` (Hari Libur)
- [x] **Migration 9**: `create_attendances_table` (Absensi)
- [x] **Migration 10**: `create_invalid_scan_logs_table` (Log Scan Invalid)
- [x] **Migration 11**: `create_promotion_logs_table` (Log Kenaikan Kelas)
- [x] **Migration 12**: `create_promotion_log_details_table` (Detail Log Kenaikan Kelas)
- [x] Ubah tipe data `id` pada tabel `users` bawaan Laravel dari `bigint` menjadi `uuid`.
- [x] Buat model Eloquent (Bahasa Indonesia) untuk setiap tabel dengan relasi (`HasMany`, `BelongsTo`, `MorphTo`) dan trait `HasUuids`.
- [x] Daftarkan Morph Map di `AppServiceProvider` untuk `admin` dan `wali_kelas`.
- [x] Konfigurasi guard `wali_kelas` dan `siswa` di `config/auth.php`.
- [x] Buat dan jalankan **KelasSeeder** untuk mengisi otomatis template nama kelas 7A-9C.

## Hasil Verifikasi:

- [x] `php artisan migrate:fresh --seed` berjalan tanpa error.
- [x] Semua tabel baru muncul di database MySQL.
- [x] Tabel `classes` terisi dengan benar (7A, 7B, 7C, 8A, 8B, 8C, 9A, 9B, 9C).
