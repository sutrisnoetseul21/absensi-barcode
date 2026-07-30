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
- enable_promotion_features (boolean)
- barcode_scan_mode (string) default 'nisn'  → mode kios presensi ('nisn' atau 'nis')
- lama_pinjam_buku_hari (integer) default 7  → batas lama peminjaman buku dalam hari
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

### `teacher_presensi_profiles` ← **NEW (Layer Operasional)**
> Profil presensi dan keanggotaan perpustakaan khusus untuk guru.
```
- id (uuid, primary)
- teacher_id (foreignUuid → teachers)
- barcode_code (string, unique)  → INDEX wajib (format angka)
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

## Modul Akademik & Kepegawaian (HRD) - NEW ERP

### `jabatans` (Master Jabatan)
```
- id (unsignedBigInteger, primary)
- nama_jabatan (string)
- timestamps()
```

### `teacher_jabatan` (Pivot Guru & Jabatan)
> Satu guru bisa merangkap beberapa jabatan (misal: "Wali Kelas" & "Wakil Kepala Sekolah").
```
- id (unsignedBigInteger, primary)
- teacher_id (foreignUuid → teachers)
- jabatan_id (foreignBigInteger → jabatans)
- timestamps()
```

### `mata_pelajarans` (Master Mata Pelajaran)
```
- id (unsignedBigInteger, primary)
- nama_mapel (string)
- kode_mapel (string)
- timestamps()
```

### `pengajarans` (Jadwal/Distribusi Mengajar)
> Memetakan Guru ke Mata Pelajaran, untuk diajarkan di Kelas (beserta Tahun Ajarannya).
```
- id (uuid, primary)
- class_academic_year_id (foreignUuid → class_academic_year)
- teacher_id (foreignUuid → teachers)
- mata_pelajaran_id (foreignBigInteger → mata_pelajarans)
- timestamps()
```

---

## Modul Perpustakaan - NEW ERP

### `kategori_bukus`
```
- id (uuid, primary)
- nama_kategori (string)
- timestamps()
```

### `bukus`
```
- id (uuid, primary)
- kategori_id (foreignUuid → kategori_bukus)
- mapel_id (foreignId, nullable → mata_pelajarans)
- grade_level (tinyInteger, nullable)
- judul (string)
- penulis (string, nullable)
- penerbit (string, nullable)
- tahun_terbit (integer, nullable)
- isbn (string, nullable)
- lokasi_rak (string, nullable)
- deleted_at (softDeletes)
- timestamps()
```

### `inventaris_bukus` ← **NEW ERP**
```
- id (uuid, primary)
- buku_id (foreignUuid → bukus)
- no_inventaris (string)
- tanggal_masuk (date)
- asal (enum: 'pembelian','hibah','tukar','terbitan_sendiri') default 'pembelian'
- harga (integer) default 0
- jumlah_eksemplar (integer) default 0
- status (enum: 'aktif','dibatalkan') default 'aktif'
- alasan_pembatalan (text, nullable)
- timestamps()
```

### `eksemplar_bukus`
```
- id (uuid, primary)
- buku_id (foreignUuid → bukus)
- inventaris_buku_id (foreignUuid, nullable → inventaris_bukus)
- kode_eksemplar (string, unique)
- status (enum: 'tersedia','dipinjam','rusak','hilang') default 'tersedia'
- kondisi_fisik (enum: 'baik','rusak_ringan','rusak_berat') default 'baik'
- deleted_at (softDeletes)
- timestamps()
```

### `peminjamans`
```
- id (uuid, primary)
- eksemplar_id (foreignUuid → eksemplar_bukus)
- peminjam_type (string) → 'siswa' atau 'guru' (Morph Map)
- peminjam_id (uuid)
- tanggal_pinjam (date)
- tanggal_jatuh_tempo (date)
- tanggal_kembali (date, nullable)
- status (enum: 'dipinjam','dikembalikan','terlambat','hilang') default 'dipinjam'
- petugas_id (foreignUuid, nullable → users)
- catatan (text, nullable)
- timestamps()
```

### `kunjungan_perpustakaans` ← **NEW ERP**
```
- id (uuid, primary)
- pengunjung_type (string) → 'siswa' atau 'guru' (Morph Map)
- pengunjung_id (uuid)
- tanggal (date, index)
- waktu_masuk (time)
- tujuan_kunjungan (string) default 'Membaca / Belajar'
- catatan (string, nullable)
- petugas_id (foreignUuid, nullable → users)
- timestamps()
```

---

> **Catatan Teknis Peminjaman Guru:** Karena adanya anomali Morph Map (`Guru::class` memiliki alias `wali_kelas` & `guru`), DILARANG menggunakan metode `associate()` secara implisit saat pembuatan record peminjaman oleh Guru. Referensi dan detail aturan wajib *set manual* dapat dibaca pada file `docs/progres-development/fase-2-erp/perpustakaan/README.md`.

---

## ERD (Relasi Seluruh Modul ERP)

```
[HRD]
jabatans ──< teacher_jabatan >── teachers

[AKADEMIK - DISTRIBUSI]
academic_years ──< class_academic_year >── classes (template permanen)
                                  |
                               teachers (Wali Kelas)
                                  |
                             pengajarans (Distribusi Mengajar) ──> mata_pelajarans
                                  |
                               teachers (Guru Mapel)

[AKADEMIK - ENROLLMENT SISWA]
students ──< student_enrollments >── classes
                    |
              academic_years

[PRESENSI ALAT]
students ──< student_presensi_profiles > (1-to-1 relasi logis)
teachers ──< teacher_presensi_profiles > (1-to-1 relasi logis)

[PRESENSI HARIAN]
student_enrollments <── attendances ──> users (scanned_by)
                                    ──> polymorphic (manual_input_by)

[KENAIKAN KELAS]
promotion_logs ──< promotion_log_details >── students
promotion_logs ──> academic_years (from/to)
promotion_logs ──> users (executed_by)

[PERPUSTAKAAN]
kategori_bukus ──< bukus >── mata_pelajarans (opsional)
bukus ──< inventaris_bukus ──< eksemplar_bukus
eksemplar_bukus ──< peminjamans >── users (petugas)
peminjamans ──> polymorphic (Siswa / Guru)
kunjungan_perpustakaans ──> polymorphic (Siswa / Guru)
kunjungan_perpustakaans ──> users (petugas)
```
school_settings ──> academic_years (active)
holidays ──> classes (nullable, khusus kelas)
invalid_scan_logs (standalone)

**Index wajib:** `students.barcode_code` (unique), `students.nisn` (unique), `attendances.[class_id, academic_year_id, date]`
