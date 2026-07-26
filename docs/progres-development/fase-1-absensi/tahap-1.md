# Progres Aplikasi Absensi - Tahap 1: Skema Database

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

---

Dokumen ini mencatat seluruh keputusan desain, perubahan, dan skema aktual tabel yang dibuat pada Tahap 1. Berguna sebagai referensi cepat untuk pemeliharaan (*maintenance*) di masa mendatang.

---

## Ringkasan Tabel yang Dibuat

| No | Nama Migration | Nama Tabel | Keterangan |
|----|----------------|------------|-----------|
| 1  | `create_academic_years_table` | `academic_years` | Tahun ajaran |
| 2  | `create_classes_table` | `classes` | Template kelas permanen |
| 3  | `create_school_settings_table` | `school_settings` | Pengaturan sekolah singleton |
| 4  | `create_teachers_table` | `teachers` | Data guru |
| 5  | `create_students_table` | `students` | Data siswa |
| 6  | `create_class_academic_year_table` | `class_academic_year` | Pivot wali kelas per TP |
| 7  | `create_student_enrollments_table` | `student_enrollments` | Enrollment siswa per TP |
| 8  | `create_holidays_table` | `holidays` | Hari libur |
| 9  | `create_attendances_table` | `attendances` | Absensi harian |
| 10 | `create_invalid_scan_logs_table` | `invalid_scan_logs` | Log scan kartu tidak valid |
| 11 | `create_promotion_logs_table` | `promotion_logs` | Log kenaikan kelas |

> **Catatan**: Semua primary key menggunakan **UUID** (`HasUuids`), bukan auto-increment integer.

---

## Detail Skema Tabel Aktual

### Tabel `academic_years`
```
- id             : uuid, primary
- name           : string → format "2025/2026" (auto-generated dari start_year/end_year)
- start_year     : integer, UNIQUE → tahun mulai (misal: 2025)
- end_year       : integer, UNIQUE → tahun selesai (misal: 2026)
- status         : enum('aktif','arsip') default 'aktif'
- timestamps()
```
> **Catatan Revisi (2 Juli 2026):** Kolom `start_date` (date) dan `end_date` (date) **dihapus** dan diganti dengan `start_year` (integer, UNIQUE) dan `end_year` (integer, UNIQUE). Field `name` tidak lagi diisi manual — di-generate otomatis dari `"{start_year}/{end_year}"` via `boot()` model `TahunAjaran`. Alasan: lebih simpel, mencegah duplikasi tahun ajaran, dan urutan otomatis berdasarkan `start_year`.

### Tabel `classes`
```
- id          : uuid, primary
- name        : string → "7A", "7B", "9C", dst.
- grade_level : tinyInteger → 7, 8, atau 9 (khusus SMP)
- deleted_at  : softDeletes
- timestamps()
```

### Tabel `school_settings`
```
- id                       : uuid, primary
- school_name              : string
- school_address           : text, nullable
- school_logo_path         : string, nullable
- principal_name           : string, nullable
- checkin_time             : time → jam batas "Hadir" (misal "07:00")
- late_threshold_minutes   : unsignedInteger, default 0
- academic_year_id_active  : foreignUuid → academic_years (nullable)
- timestamps()
```

### Tabel `teachers`
```
- id                   : uuid, primary
- name                 : string
- nip                  : string, nullable, unique
- username             : string, unique
- password             : string
- must_change_password : boolean, default true
- deleted_at           : softDeletes
- timestamps()
```

### Tabel `students`
```
- id                   : uuid, primary
- nisn                 : string, unique (INDEX wajib)
- name                 : string
- birth_place          : string, nullable
- birth_date           : date, nullable
- address              : text, nullable
- photo_path           : string, nullable
- barcode_code         : string, unique (INDEX wajib, default = NISN)
- barcode_active       : boolean, default true
- username             : string, unique (default = NISN)
- password             : string
- must_change_password : boolean, default true
- deleted_at           : softDeletes
- timestamps()
```

### Tabel `class_academic_year` (pivot wali kelas)
```
- id              : uuid, primary
- class_id        : foreignUuid → classes
- academic_year_id: foreignUuid → academic_years
- teacher_id      : foreignUuid, nullable → teachers
- timestamps()
- UNIQUE: [class_id, academic_year_id]
```

### Tabel `student_enrollments`
```
- id               : uuid, primary
- student_id       : foreignUuid → students
- class_id         : foreignUuid → classes
- academic_year_id : foreignUuid → academic_years
- status           : enum('aktif','naik','tinggal','pindah','lulus') default 'aktif'
- timestamps()
- UNIQUE: [student_id, academic_year_id]
```

### Tabel `holidays`
```
- id          : uuid, primary
- start_date  : date
- end_date    : date, nullable (null = 1 hari)
- description : string
- type        : enum('nasional','cuti_bersama','khusus')
- class_id    : foreignUuid, nullable → classes (null = semua kelas libur)
- timestamps()
```

### Tabel `attendances`
```
- id                   : uuid, primary
- student_id           : foreignUuid → students
- enrollment_id        : foreignUuid → student_enrollments
- class_id             : foreignUuid → classes (DENORMALIZED)
- academic_year_id     : foreignUuid → academic_years (DENORMALIZED)
- date                 : date
- scan_time            : time, nullable
- status               : enum('hadir','telat','alpa','sakit','izin')
- late_minutes         : unsignedInteger, default 0
- note                 : string, nullable
- is_manual_input      : boolean, default false
- manual_input_by_id   : uuid, nullable (polymorphic)
- manual_input_by_type : string, nullable (morph type)
- scanned_by           : foreignUuid, nullable → users
- timestamps()
- UNIQUE: [student_id, date]
- INDEX: [class_id, academic_year_id, date]
```

### Tabel `invalid_scan_logs`
```
- id           : uuid, primary
- scanned_code : string
- scan_time    : datetime
- ip_address   : string, nullable
- timestamps()
```

### Tabel `promotion_logs`
```
- id                    : uuid, primary
- academic_year_from_id : foreignUuid → academic_years
- academic_year_to_id   : foreignUuid → academic_years
- executed_by           : foreignUuid → users
- notes                 : text, nullable
- timestamps()
```

---

## Seeder

Setelah migration, **ClassSeeder** dijalankan untuk mengisi template nama kelas:
`7A, 7B, 7C, 8A, 8B, 8C, 9A, 9B, 9C`

---

## Revisi Setelah Implementasi Awal

| Tanggal | Perubahan | Alasan |
|---------|-----------|--------|
| 2 Juli 2026 | Kolom `start_date` dan `end_date` di `academic_years` **dihapus**, diganti `start_year` dan `end_year` (integer, UNIQUE) | Lebih simpel, mencegah duplikasi TP, urutan otomatis |
| 2 Juli 2026 | Migration `update_academic_years_use_year_integers.php` ditambahkan | Migrasi data dari kolom lama ke baru |
| 2 Juli 2026 | Kolom `is_super_admin` ditambahkan ke tabel `users` via `add_is_super_admin_to_users_table` | Pembatasan akses Super Admin vs Admin biasa |

---
*Dokumen ini dibuat pada **2 Juli 2026**.*
