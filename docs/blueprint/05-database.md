# 05. Database

> **Keputusan Desain:**
> - Guard Admin (Filament) → pakai tabel `users` bawaan Laravel/Filament (guard `web`)
> - `admin_users` **tidak dibuat** — tidak perlu tabel terpisah
> - Wali kelas bisa diampu > 1 kelas per tahun ajaran (relasi banyak-ke-banyak via pivot)
> - Jam masuk/batas telat = **setting global** dari `school_settings`

**Prinsip utama:** desain harus mendukung histori kenaikan kelas — jangan simpan `class_id` langsung di `students`, gunakan tabel pivot per tahun ajaran.

---

## Entitas & Skema Tabel

### `school_settings` ← **NEW**
> Konfigurasi global sekolah. Dipakai untuk cetak PDF, kartu OSIS, dan logika jam masuk.
```
- id (uuid, primary)
- school_name (string)
- school_address (text, nullable)
- school_logo_path (string, nullable)
- principal_name (string, nullable)          → nama kepala sekolah untuk TTD
- checkin_time (time)                        → jam batas "Hadir" global, misal "07:00"
- late_threshold_minutes (unsignedInteger)   → menit toleransi sebelum dianggap telat
- academic_year_id_active (foreignUuid, nullable → academic_years)
- timestamps()
```
> **Catatan:** Tabel ini cukup 1 baris. Gunakan Filament "Settings" resource untuk mengelolanya.

---

### `academic_years`
```
- id (uuid, primary)
- name (string)              → contoh: "2025/2026"
- start_date (date)
- end_date (date)
- status (enum: 'aktif','arsip') default 'aktif'
- timestamps()
```

---

### `classes` ← **Template Nama Kelas Permanen**
> Tabel ini berisi daftar nama kelas yang **tidak berubah antar tahun ajaran** (7A, 7B, ..., 9C).
> Kelas tidak perlu dibuat ulang setiap tahun — cukup assign ke `class_academic_year`.
> Gunakan seeder awal untuk isi kelas 7A–9C (atau sesuai jenjang sekolah).
```
- id (uuid, primary)
- name (string)              → contoh: "7A", "8B", "9C"
- grade_level (tinyInteger)  → 7, 8, 9 (untuk SMP) / 10, 11, 12 (untuk SMA)
- deleted_at (softDeletes)   → soft delete, jangan hard delete
- timestamps()
```
> **Seeder:** Isi otomatis kelas 7A, 7B, 7C, 8A, 8B, 8C, 9A, 9B, 9C saat install awal.

---

### `teachers`
```
- id (uuid, primary)
- name (string)
- nip (string, nullable, unique)
- username (string, unique)
- password (string)
- must_change_password (boolean) default true
- deleted_at (softDeletes)
- timestamps()
```

---

### `students`
> **Master Data Murni:** Identitas siswa tanpa data transaksional.
```
- id (uuid, primary)
- nisn (string, unique)          → INDEX wajib
- name (string)
- birth_place (string, nullable)
- birth_date (date, nullable)
- address (text, nullable)
- photo_path (string, nullable)
- username (string, unique)      → default = NISN
- password (string)
- must_change_password (boolean) default true
- deleted_at (softDeletes)
- timestamps()
```

---

### `student_presensi_profiles` ← **NEW (Layer Operasional)**
> Profil presensi khusus untuk siswa (dipisahkan dari identitas inti).
```
- id (uuid, primary)
- student_id (foreignUuid → students)
- barcode_code (string, unique)  → INDEX wajib
- barcode_active (boolean) default true
- timestamps()
```

---

### `class_academic_year` (pivot wali kelas per tahun ajaran)
> Satu wali kelas BISA mengelola lebih dari 1 kelas per tahun ajaran.
```
- id (uuid, primary)
- class_id (foreignUuid → classes)
- academic_year_id (foreignUuid → academic_years)
- teacher_id (foreignUuid, nullable → teachers)  → wali kelas
- timestamps()
- UNIQUE: [class_id, academic_year_id]
```

---

### `student_enrollments`
> **Kunci riwayat kenaikan kelas.** Satu siswa = satu baris per tahun ajaran.
```
- id (uuid, primary)
- student_id (foreignUuid → students)
- class_id (foreignUuid → classes)
- academic_year_id (foreignUuid → academic_years)
- status (enum: 'aktif','naik','tinggal','pindah','lulus') default 'aktif'
- timestamps()
- UNIQUE: [student_id, academic_year_id]  → 1 siswa, 1 kelas per tahun ajaran
```

---

### `holidays`
> Support **range tanggal** untuk cuti bersama (misal 25–28 Desember).
```
- id (uuid, primary)
- start_date (date)                            → tanggal mulai libur
- end_date (date, nullable)                    → null = 1 hari saja
- description (string)
- type (enum: 'nasional','cuti_bersama','khusus')
- class_id (foreignUuid, nullable → classes)   → null = semua kelas libur
- timestamps()
```
> **Query cek libur:** `WHERE start_date <= $tanggal AND (end_date IS NULL OR end_date >= $tanggal)`

---

### `attendances`
> Denormalisasi `class_id` dan `academic_year_id` langsung di sini untuk mempercepat query dashboard (hindari join berlapis ke `student_enrollments`).
```
- id (uuid, primary)
- student_id (foreignUuid → students)
- enrollment_id (foreignUuid → student_enrollments)
- class_id (foreignUuid → classes)               → DENORMALIZED — disalin saat insert
- academic_year_id (foreignUuid → academic_years) → DENORMALIZED — disalin saat insert
- date (date)
- scan_time (time, nullable)
- status (enum: 'hadir','telat','alpa','sakit','izin')
- late_minutes (unsignedInteger) default 0
- note (string, nullable)                         → alasan Izin/Sakit dari wali kelas
- is_manual_input (boolean) default false
- manual_input_by_id (uuid, nullable)             → polymorphic: bisa Teacher atau User
- manual_input_by_type (string, nullable)         → 'App\Models\Teacher' atau 'App\Models\User'
- scanned_by (foreignUuid, nullable → users)      → admin yang scan (guard Filament = users)
- timestamps()
- UNIQUE: [student_id, date]
- INDEX: [class_id, academic_year_id, date]       → untuk query dashboard
```
> **Morph Map** di `AppServiceProvider`: `'admin' => User::class, 'wali_kelas' => Teacher::class`

---

### `invalid_scan_logs`
```
- id (uuid, primary)
- scanned_code (string)
- scan_time (datetime)
- ip_address (string, nullable)
- timestamps()
```

---

### `promotion_logs`
```
- id (uuid, primary)
- academic_year_from_id (foreignUuid → academic_years)
- academic_year_to_id (foreignUuid → academic_years)
- executed_by (foreignUuid → users)    → admin yang menjalankan
- notes (text, nullable)
- timestamps()
```

---

### `promotion_log_details` ← **NEW**
> Detail audit per siswa saat proses kenaikan kelas, untuk keperluan rollback dan histori.
```
- id (uuid, primary)
- promotion_log_id (foreignUuid → promotion_logs)
- student_id (foreignUuid → students)
- old_enrollment_id (foreignUuid → student_enrollments)
- new_enrollment_id (foreignUuid, nullable → student_enrollments)  → null jika lulus
- decision (enum: 'naik','tinggal','pindah','lulus')
- timestamps()
```

---

## Alur Kenaikan Kelas via Excel

> Fitur ini mempermudah proses kenaikan kelas massal tanpa harus input satu per satu.

1. **Export** → Admin download file Excel berisi: `NISN`, `Nama Siswa`, `Kelas Saat Ini`, `Kolom Kelas Baru (kosong)`
2. **Isi** → Admin/operator isi kolom "Kelas Baru" (nama kelas, misal "8A") di Excel
3. **Import** → Upload kembali → sistem validasi nama kelas vs `classes.name` → buat `student_enrollments` baru untuk tahun ajaran baru → catat di `promotion_logs` + `promotion_log_details`
4. **Review** → Siswa yang belum diisi / nama kelas tidak valid → ditampilkan di daftar error

---

## Catatan Guard & Auth

| Guard | Model | Tabel | Keterangan |
|---|---|---|---|
| `web` (default Filament) | `User` | `users` | Admin, Super Admin |
| `wali_kelas` | `Teacher` | `teachers` | Portal Wali Kelas |
| `siswa` | `Student` | `students` | Portal Siswa |

---

## ERD (Relasi)

```
academic_years ──< class_academic_year >── classes (template permanen)
                                  |
                               teachers

students ──< student_enrollments >── classes
                    |
                academic_years

students ──< student_presensi_profiles > (1-to-1 relasi logis)

student_enrollments <── attendances ──> users (scanned_by)
                                    ──> polymorphic (manual_input_by)

promotion_logs ──< promotion_log_details >── students
promotion_logs ──> academic_years (from/to)
promotion_logs ──> users (executed_by)

school_settings ──> academic_years (active)
holidays ──> classes (nullable, khusus kelas)
invalid_scan_logs (standalone)
```

**Index wajib:** `students.barcode_code` (unique), `students.nisn` (unique), `attendances.[class_id, academic_year_id, date]`
