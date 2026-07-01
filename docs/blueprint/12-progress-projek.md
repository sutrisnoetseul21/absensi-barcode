# 12. Progress Proyek — Panduan Implementasi Bertahap untuk AI Agent

> **PENTING UNTUK AI AGENT:**
> Baca file ini dari awal sebelum menulis kode apapun.
> File ini adalah **panduan eksekusi bertahap** — kerjakan satu tahap, verifikasi, baru lanjut ke tahap berikutnya.
> Jangan skip tahap. Jangan kerjakan lebih dari satu tahap dalam satu sesi tanpa konfirmasi pengguna.

---

## 🗺️ Peta Dokumen Wajib Baca

Sebelum memulai sesi coding apapun, AI agent WAJIB membaca dokumen berikut:

| Prioritas | File | Tujuan |
|-----------|------|--------|
| 1 | [`README-DOCS.md`](../../README-DOCS.md) | Tech stack, aturan larangan, cara jalankan dev |
| 2 | [`docs/stack/PROJECT-STACK-MYSQL.md`](../stack/PROJECT-STACK-MYSQL.md) | Konvensi MySQL: UUID, migration, model |
| 3 | [`docs/stack/LIVEWIRE-V3-GUIDE.md`](../stack/LIVEWIRE-V3-GUIDE.md) | Livewire v3 — wajib setiap bikin komponen |
| 4 | [`docs/stack/ALPINE-V3-GUIDE.md`](../stack/ALPINE-V3-GUIDE.md) | Alpine.js v3 — khusus interaktivitas kios |
| 5 | [`docs/stack/FILAMENT-V4-INTEGRATION.md`](../stack/FILAMENT-V4-INTEGRATION.md) | Filament v4 — wajib sebelum buat Resource |
| 6 | [`docs/blueprint/05-database.md`](05-database.md) | Skema database dan relasi |
| 7 | [`docs/blueprint/06-business-rules.md`](06-business-rules.md) | Aturan bisnis (jam absen, debounce, formula) |

---

## ⚙️ Tech Stack yang Digunakan

```
Framework     : Laravel 12
Frontend      : Livewire v3 · Tailwind CSS v3 · Alpine.js v3
Admin Panel   : Filament v4
Database      : MySQL 8+ (primary key UUID, bukan auto-increment)
Grafik        : ApexCharts atau Chart.js
Kalender      : FullCalendar
PDF           : barryvdh/laravel-dompdf
Excel         : Maatwebsite/Laravel-Excel
Barcode PHP   : picqer/php-barcode-generator
Dev Command   : composer run dev (bukan php artisan serve)
```

---

## 📋 Aturan Wajib AI Agent Sebelum Coding

> [!CAUTION]
> Pelanggaran aturan ini menyebabkan bug yang sulit di-trace. Baca dan patuhi.

| ❌ DILARANG | ✅ GANTI DENGAN |
|------------|----------------|
| `$table->id()` | `$table->uuid('id')->primary()` |
| `php artisan serve` | `composer run dev` |
| Vue.js / React | Livewire v3 |
| Logic di Controller | Action Classes di `app/Actions/` |
| `$table->json()` | Tetap `json()` untuk MySQL (bukan jsonb) |
| `wire:model` tanpa modifier | `wire:model.live` atau `wire:model.blur` |
| Filament v5 syntax | Gunakan Filament v4 docs |
| File > 250 baris | Pecah jadi Action Class / View Component |
| `timestamps()` dengan timezone | gunakan `timestamps()` biasa untuk MySQL |

---

## 🚀 Tahap-Tahap Implementasi

Setiap tahap harus **selesai dan diverifikasi** sebelum lanjut ke tahap berikutnya.
Status: `[ ]` = belum | `[/]` = sedang dikerjakan | `[x]` = selesai

---

### TAHAP 0 — Inisiasi Proyek & Setup Awal

> **Goal**: Proyek Laravel 12 berjalan di local dengan database MySQL terhubung.

**Checklist:**
- `[x]` Install Laravel 12 dengan perintah:
  ```bash
  laravel new absensi-barcode --database=mysql --starter-kit=livewire --no-interaction
  ```
  *atau jika menggunakan folder yang sudah ada:*
  ```bash
  composer create-project laravel/laravel . --prefer-dist
  ```
- `[x]` Konfigurasi file `.env`:
  ```
  DB_CONNECTION=mysql
  DB_HOST=127.0.0.1
  DB_PORT=3306
  DB_DATABASE=absensi_barcode
  DB_USERNAME=root
  DB_PASSWORD=
  APP_NAME="Sistem Absensi Barcode"
  APP_URL=http://localhost:8000
  ```
- `[x]` Buat database MySQL dengan nama `absensi_barcode`
- `[x]` Install Filament v4:
  ```bash
  composer require filament/filament:"^4.0"
  php artisan filament:install --panels
  ```
- `[x]` Jalankan `composer run dev` dan pastikan tidak ada error
- `[x]` Akses `http://localhost:8000` → tampil halaman default Laravel

**Verifikasi Tahap 0 Selesai:**
- [x] `php artisan migrate` berjalan tanpa error
- [x] `http://localhost:8000` dapat diakses di browser

---

### TAHAP 1 — Skema Database (Migrations)

> **Goal**: Semua tabel terbuat di MySQL sesuai blueprint `05-database.md`.
> **Penting**: Semua primary key WAJIB UUID — baca `PROJECT-STACK-MYSQL.md` dulu.
> **Keputusan desain**: Admin = tabel `users` bawaan Filament. Tidak ada tabel `admin_users` terpisah.

**Urutan pembuatan migration (urut karena ada foreign key):**

- `[x]` **Migration 1**: `create_academic_years_table`
  ```
  - id (uuid, primary)
  - name (string) → contoh: "2025/2026"
  - start_date (date)
  - end_date (date)
  - status (enum: 'aktif', 'arsip') default 'aktif'
  - timestamps()
  ```

- `[x]` **Migration 2**: `create_classes_table` ← TEMPLATE NAMA KELAS PERMANEN
  ```
  - id (uuid, primary)
  - name (string) → contoh: "7A" — tidak berubah antar tahun ajaran
  - grade_level (tinyInteger) → 7, 8, 9 (SMP) / 10, 11, 12 (SMA)
  - deleted_at (softDeletes)
  - timestamps()
  ```
  > Setelah migration, jalankan **ClassSeeder** untuk isi otomatis: 7A, 7B, 7C, 8A, 8B, 8C, 9A, 9B, 9C

- `[x]` **Migration 3**: `create_school_settings_table` ← **NEW**
  ```
  - id (uuid, primary)
  - school_name (string)
  - school_address (text, nullable)
  - school_logo_path (string, nullable)
  - principal_name (string, nullable) → nama kepala sekolah untuk TTD di PDF/kartu
  - checkin_time (time) → jam batas "Hadir" global, misal "07:00"
  - late_threshold_minutes (unsignedInteger) default 0
  - academic_year_id_active (foreignUuid, nullable → academic_years)
  - timestamps()
  ```

- `[x]` **Migration 4**: `create_teachers_table`
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

- `[x]` **Migration 5**: `create_students_table`
  ```
  - id (uuid, primary)
  - nisn (string, unique) → INDEX wajib
  - name (string)
  - birth_place (string, nullable)
  - birth_date (date, nullable)
  - address (text, nullable)
  - photo_path (string, nullable)
  - barcode_code (string, unique) → INDEX wajib, default = NISN
  - barcode_active (boolean) default true
  - username (string, unique) → default = NISN
  - password (string)
  - must_change_password (boolean) default true
  - deleted_at (softDeletes)
  - timestamps()
  ```

- `[x]` **Migration 6**: `create_class_academic_year_table` (pivot wali kelas per tahun)
  ```
  - id (uuid, primary)
  - class_id (foreignUuid → classes)
  - academic_year_id (foreignUuid → academic_years)
  - teacher_id (foreignUuid, nullable → teachers) → wali kelas (1 wali bisa > 1 kelas)
  - timestamps()
  - UNIQUE: [class_id, academic_year_id]
  ```

- `[x]` **Migration 7**: `create_student_enrollments_table`
  ```
  - id (uuid, primary)
  - student_id (foreignUuid → students)
  - class_id (foreignUuid → classes)
  - academic_year_id (foreignUuid → academic_years)
  - status (enum: 'aktif','naik','tinggal','pindah','lulus') default 'aktif'
  - timestamps()
  - UNIQUE: [student_id, academic_year_id]
  ```

- `[x]` **Migration 8**: `create_holidays_table`
  ```
  - id (uuid, primary)
  - start_date (date) ← support range tanggal (cuti bersama)
  - end_date (date, nullable) → null = 1 hari saja
  - description (string)
  - type (enum: 'nasional','cuti_bersama','khusus')
  - class_id (foreignUuid, nullable → classes) → null = semua kelas libur
  - timestamps()
  ```

- `[x]` **Migration 9**: `create_attendances_table`
  ```
  - id (uuid, primary)
  - student_id (foreignUuid → students)
  - enrollment_id (foreignUuid → student_enrollments)
  - class_id (foreignUuid → classes)               ← DENORMALIZED — disalin saat insert
  - academic_year_id (foreignUuid → academic_years) ← DENORMALIZED — disalin saat insert
  - date (date)
  - scan_time (time, nullable)
  - status (enum: 'hadir','telat','alpa','sakit','izin')
  - late_minutes (unsignedInteger) default 0
  - note (string, nullable)            ← NEW — alasan Izin/Sakit
  - is_manual_input (boolean) default false
  - manual_input_by_id (uuid, nullable)   ← polymorphic (Teacher atau User)
  - manual_input_by_type (string, nullable) ← morph type string
  - scanned_by (foreignUuid, nullable → users) ← Admin Filament = tabel users
  - timestamps()
  - UNIQUE: [student_id, date]
  - INDEX: [class_id, academic_year_id, date]
  ```

- `[x]` **Migration 10**: `create_invalid_scan_logs_table`
  ```
  - id (uuid, primary)
  - scanned_code (string)
  - scan_time (datetime)
  - ip_address (string, nullable)
  - timestamps()
  ```

- `[x]` **Migration 11**: `create_promotion_logs_table`
  ```
  - id (uuid, primary)
  - academic_year_from_id (foreignUuid → academic_years)
  - academic_year_to_id (foreignUuid → academic_years)
  - executed_by (foreignUuid → users) ← foreignUuid ke tabel users (Filament)
  - notes (text, nullable)
  - timestamps()
  ```

- `[x]` **Migration 12**: `create_promotion_log_details_table` ← **NEW**
  ```
  - id (uuid, primary)
  - promotion_log_id (foreignUuid → promotion_logs)
  - student_id (foreignUuid → students)
  - old_enrollment_id (foreignUuid → student_enrollments)
  - new_enrollment_id (foreignUuid, nullable → student_enrollments) → null = lulus
  - decision (enum: 'naik','tinggal','pindah','lulus')
  - timestamps()
  ```

- `[x]` Jalankan `php artisan migrate`
- `[x]` Jalankan **ClassSeeder** untuk isi template nama kelas (7A–9C)
- `[x]` Buat model untuk setiap tabel dengan relasi (`HasMany`, `BelongsTo`, `MorphTo`)
- `[x]` Daftarkan Morph Map di `AppServiceProvider`:
  ```php
  Relation::morphMap([
      'admin'      => \App\Models\User::class,
      'wali_kelas' => \App\Models\Teacher::class,
  ]);
  ```

**Verifikasi Tahap 1 Selesai:**
- [x] `php artisan migrate:fresh --seed` berjalan tanpa error
- [x] Semua 12 tabel baru muncul di database MySQL
- [x] Tabel `classes` terisi seeder (7A, 7B, 7C, 8A, 8B, 8C, 9A, 9B, 9C)

---

### TAHAP 2 — Multi-Guard Authentication

> **Goal**: 3 guard login terpisah berfungsi penuh (`web` untuk admin, `wali_kelas`, `siswa`).
> Referensi: `02-roles-permissions.md`, `09-third-party.md`.

- `[x]` Konfigurasi `config/auth.php`:
  - Guard `web` (default) → provider model `User` (tabel `users`, dikelola Filament)
  - Guard `wali_kelas` → provider model `Teacher` (tabel `teachers`)
  - Guard `siswa` → provider model `Student` (tabel `students`)
- `[x]` Model `Teacher` dan `Student` implement `Authenticatable`
- `[x]` **PENTING**: Model `Teacher` dan `Student` WAJIB menggunakan trait `HasUuids` dan properti `$keyType = 'string'; public $incrementing = false;` karena primary key menggunakan UUID.
- `[x]` Buat middleware `EnsureIsWaliKelas` dan `EnsureIsSiswa`
- `[x]` Daftarkan route group per guard:
  - `/wali-kelas/*` → middleware `auth:wali_kelas`
  - `/siswa/*` → middleware `auth:siswa`
- `[x]` Buat halaman Login Wali Kelas (Livewire component) di `/wali-kelas/login`
- `[x]` Buat halaman Login Siswa (Livewire component) di `/siswa/login`
- `[x]` Implementasi flow ganti password wajib (`must_change_password` = true) untuk wali kelas dan siswa setelah login (form ganti password + redirect logic)
- `[x]` Buat route logout per guard (`/wali-kelas/logout`, `/siswa/logout`)
- `[x]` Setup redirect setelah login sukses ke dashboard masing-masing
- `[x]` Setup Filament Panel Admin (menggunakan guard `web`, prefix `/admin`)

**Verifikasi Tahap 2 Selesai:**
- [x] Bisa login sebagai Admin melalui `/admin/login` dan masuk ke panel Filament
- [x] Bisa login sebagai Wali Kelas di `/wali-kelas/login` dan logout berfungsi
- [x] Bisa login sebagai Siswa di `/siswa/login` dan logout berfungsi
- [x] Akses `/admin` tanpa login di-redirect ke `/admin/login`
- [x] Guard tidak saling campur (login wali kelas tidak bisa akses route siswa)
- [x] User dengan `must_change_password = true` dipaksa redirect ke form ganti password sebelum bisa akses halaman lain

---

### TAHAP 3 — Data Master & Modul Admin Dasar (Filament)

> **Goal**: Admin bisa mengelola data master (Tahun Ajaran, Kelas, Guru, Siswa) melalui panel Filament.
> Referensi: `03-features.md`, `08-pages-routes.md`.

- `[ ]` **Filament Resource**: `AcademicYearResource` (CRUD Tahun Ajaran)
- `[ ]` **Filament Resource**: `ClassResource` (CRUD Kelas, assign wali kelas per tahun ajaran)
- `[ ]` **Filament Resource**: `TeacherResource` (CRUD Guru, reset password)
- `[ ]` **Filament Resource**: `StudentResource`
  - Tabel daftar siswa (filter kelas, tahun ajaran)
  - Form tambah/edit (NISN, nama, TTL, alamat, foto, barcode_code)
  - Auto-generate `barcode_code` = NISN saat siswa baru dibuat
  - Tombol "Reset Password" (reset ke default)
- `[ ]` **Filament Resource**: `StudentEnrollmentResource`
  - Assign siswa ke kelas per tahun ajaran
  - Tampilkan status enrollment

**Verifikasi Tahap 3 Selesai:**
- [ ] Admin bisa tambah/edit/lihat Tahun Ajaran
- [ ] Admin bisa tambah/edit/lihat Kelas dan assign Wali Kelas
- [ ] Admin bisa tambah/edit/lihat Siswa dan Guru
- [ ] Barcode siswa ter-generate otomatis saat data baru dibuat

---

### TAHAP 4 — Kios Scanner Absensi (High-Speed)

> **Goal**: Halaman kios scan bekerja cepat, async, dengan feedback suara dan visual.
> Ini adalah fitur INTI proyek. Referensi: `06-business-rules.md`.
> **UI**: Background putih, logo sekolah di tengah atas, card input scanner di tengah layar.

- `[ ]` Buat route `/scan` (akses publik atau dengan PIN kios tersendiri)
- `[ ]` Buat Livewire component `AttendanceKiosk`
- `[ ]` **Layout UI Kios**:
  - Background putih bersih
  - Logo sekolah di bagian atas tengah
  - Card besar di tengah layar untuk menampung input dan feedback
  - Area feedback: foto siswa + nama + status (hijau=hadir, kuning=telat, merah=error)
- `[ ]` **Logic Scan (Alpine.js / Livewire)**:
  - Event listener `keydown` di input tersembunyi (auto-focus saat halaman dibuka)
  - Kumpulkan karakter hingga tombol `Enter` → kirim ke server
  - Debounce 3 detik per barcode (per `barcode_code`, bukan global)
  - Kirim via `fetch()` async ke endpoint `/api/scan` (non-blocking)
  - Reset input langsung setelah Enter, tanpa tunggu response server
- `[ ]` **Backend endpoint `POST /api/scan`** (Action Class `ProcessScanAction`):
  - Cari `student` berdasarkan `barcode_code`
  - Jika tidak ditemukan → return `{status: 'not_found'}`
  - Cek hari ini libur atau tidak (`holidays` table)
  - Cek sudah absen hari ini atau belum (`attendances` table)
  - Hitung `late_minutes` berdasarkan `scan_time` vs jam batas masuk
  - Simpan ke `attendances`
  - Simpan log ke `invalid_scan_logs` jika barcode tidak dikenal
  - Return JSON: `{status, name, photo_url, late_minutes, message}`
- `[ ]` **Audio Feedback (Web Speech API)**:
  - Hadir tepat waktu: *"Terima kasih [Nama], selamat belajar"*
  - Hadir tapi telat: *"[Nama], Anda terlambat [X] menit"*
  - Sudah absen: *"[Nama] sudah absen tadi jam [waktu]"*
  - Barcode tidak dikenal: *"Barcode tidak terdaftar"*

**Verifikasi Tahap 4 Selesai:**
- [ ] Scan kartu → muncul nama + foto + status dalam < 1 detik
- [ ] Scan 5 kartu berurutan cepat → semua masuk ke database tanpa lag
- [ ] Barcode sama discan 2x dalam 3 detik → hanya 1 record yang tersimpan
- [ ] Barcode tidak terdaftar → muncul peringatan & log tersimpan
- [ ] Suara muncul setiap scan

---

### TAHAP 5 — Dashboard Publik

> **Goal**: Halaman `/` dapat diakses publik tanpa login, menampilkan grafik dan Wall of Fame.
> Referensi: `07-ui-wireframe.md`, library Chart.js/ApexCharts.

- `[ ]` Install ApexCharts atau Chart.js via npm/CDN
- `[ ]` Buat Livewire component `PublicDashboard`
- `[ ]` **Widget Ringkasan**: Kartu per kelas (% hadir hari ini, jumlah siswa)
- `[ ]` **Wall of Fame**: Top 5 kelas kehadiran tertinggi bulan ini (dengan lencana ranking)
- `[ ]` **Grafik Donut**: Hadir vs Telat vs Sakit vs Izin vs Alpa hari ini (total sekolah)
- `[ ]` **Grafik Bar**: Perbandingan kehadiran antar kelas bulan ini
- `[ ]` **Grafik Line**: Tren kehadiran harian dalam 1 bulan
- `[ ]` Filter: pilih Tahun Ajaran, Kelas, Bulan (default: aktif & bulan berjalan)

**Verifikasi Tahap 5 Selesai:**
- [ ] Halaman `/` dapat diakses tanpa login
- [ ] Grafik tampil dengan data yang benar
- [ ] Wall of Fame menampilkan 5 kelas teratas

---

### TAHAP 6 — Portal Wali Kelas

> **Goal**: Wali kelas bisa login, lihat rekap kelasnya, input absensi manual, dan lihat alert siswa bermasalah.
> Referensi: `02-roles-permissions.md`, `04-user-flow.md`.

- `[ ]` Buat Livewire component `WaliKelasLogin` (di `/wali-kelas/login`)
- `[ ]` Buat Livewire component `WaliKelasDashboard` (di `/wali-kelas`)
  - Auto-load kelas yang diampu berdasarkan `class_academic_year.teacher_id`
  - Rekap hari ini: tabel daftar siswa + status hadir/tidak
  - Filter rekap per bulan
  - **Alert Pelanggaran**: Label merah untuk siswa >= 3x Alpa atau late_minutes >= 100 menit sebulan
- `[ ]` Tangani edge case: wali kelas login tapi belum ter-assign ke kelas (tampilkan empty state, bukan error)
- `[ ]` Buat Livewire component `ManualAttendanceInput` (modal di dalam dashboard)
  - Pencarian siswa: bisa ketik **Nama** atau **NISN** (live search)
  - Pilih status: Sakit / Izin / Alpa
  - Simpan dengan flag `is_manual_input = true`, `manual_input_by = teacher_id`, `manual_input_role = 'wali_kelas'`
- `[ ]` Wali kelas TIDAK BISA edit data scan "Hadir" yang sudah masuk

**Verifikasi Tahap 6 Selesai:**
- [ ] Login wali kelas berhasil dan redirect ke dashboard kelasnya
- [ ] Wali kelas tidak bisa akses kelas lain
- [ ] Input manual absensi tersimpan dengan flag benar
- [ ] Alert pelanggaran muncul untuk siswa yang memenuhi kriteria
- [ ] Pencarian siswa bisa pakai nama dan NISN

---

### TAHAP 7 — Portal Siswa

> **Goal**: Siswa bisa login dengan NISN dan lihat riwayat absensinya sendiri.

- `[ ]` Buat Livewire component `SiswaLogin` (di `/siswa/login`)
- `[ ]` Buat Livewire component `SiswaDashboard` (di `/siswa`)
  - Tampilkan: Nama, NISN, Kelas, Tahun Ajaran aktif
  - Tabel riwayat absensi semua bulan (filter per bulan)
  - Ringkasan: total hadir, telat, sakit, izin, alpa
  - Akumulasi total menit keterlambatan bulan ini
- `[ ]` Siswa TIDAK BISA melakukan perubahan data apapun (pure read-only)
- `[ ]` Ganti password default jika `must_change_password = true`

**Verifikasi Tahap 7 Selesai:**
- [ ] Login siswa via NISN berhasil
- [ ] Siswa hanya melihat data dirinya sendiri
- [ ] Filter bulan bekerja dengan benar
- [ ] Siswa dengan `must_change_password` diwajibkan ganti password saat login pertama

---

### TAHAP 8 — Import/Export Excel & Cetak Kartu OSIS

> **Goal**: Admin bisa upload data siswa via Excel dan cetak kartu OSIS ber-barcode.
> Referensi: `03-features.md`, `09-third-party.md`.

- `[ ]` Install `Maatwebsite/Laravel-Excel` dan `picqer/php-barcode-generator` dan `barryvdh/laravel-dompdf`
- `[ ]` **Import Siswa (Excel)**:
  - Download template Excel (header: NISN, Nama, Tempat Lahir, Tanggal Lahir, Alamat, Kelas)
  - Upload Excel → validasi NISN → jika ada: UPDATE; jika baru: INSERT
  - Laporan hasil import: X berhasil, Y gagal (dengan keterangan error per baris)
- `[ ]` **Export/Download Presensi (Excel)**:
  - Filter: pilih Kelas + Bulan + Tahun Ajaran
  - Format: tabel rekap absensi per siswa per hari
- `[ ]` **Cetak Kartu OSIS (PDF)**:
  - Layout kartu sesuai blueprint (header sekolah, foto kiri, biodata kanan, barcode bawah, TTD kepsek)
  - Bisa cetak 1 siswa atau massal (pilih kelas)
  - Generate barcode dari `barcode_code` siswa menggunakan `picqer/php-barcode-generator`

**Verifikasi Tahap 8 Selesai:**
- [ ] Upload Excel 10 data → semua tersimpan/terupdate dengan benar
- [ ] Download Excel presensi berhasil dengan format yang benar
- [ ] PDF Kartu OSIS ter-generate dengan foto, barcode, dan biodata lengkap

---

### TAHAP 9 — Kalender Hari Libur

> **Goal**: Admin bisa menandai hari libur sehingga tidak ada alpa otomatis di hari tersebut.

- `[ ]` Install FullCalendar via npm atau CDN
- `[ ]` **Filament Resource**: `HolidayResource` (CRUD Hari Libur)
  - Tipe: Nasional / Cuti Bersama / Khusus Kelas
  - Jika "Khusus Kelas": pilih kelas mana yang libur
- `[ ]` Tampilan kalender interaktif (klik tanggal untuk tambah/lihat libur)
- `[ ]` Integrasi ke logika kios: cek `holidays` sebelum proses scan

**Verifikasi Tahap 9 Selesai:**
- [ ] Admin bisa tambah hari libur via kalender
- [ ] Saat siswa scan di hari libur → sistem menolak atau memberi info "hari ini libur"
- [ ] Hari libur dikecualikan dari perhitungan % kehadiran

---

### TAHAP 10 — Laporan & Dashboard Admin Lengkap

> **Goal**: Admin punya dashboard detail dan laporan yang bisa di-export.

- `[ ]` **Dashboard Admin** (Filament Widget):
  - Ringkasan total siswa, total hadir hari ini, % per kelas
  - Daftar alert siswa bermasalah (sering alpa / telat banyak)
  - Grafik (Chart.js/ApexCharts)
- `[ ]` **Halaman Laporan** (`/admin/laporan`):
  - Filter: Tahun Ajaran + Kelas + Bulan + Status
  - Tabel rekap per siswa
  - Export ke PDF dan Excel

**Verifikasi Tahap 10 Selesai:**
- [ ] Dashboard admin menampilkan data yang akurat
- [ ] Export laporan PDF dan Excel berhasil

---

### TAHAP 11 — Fitur Multi-Tahun Ajaran & Kenaikan Kelas

> **Goal**: Admin bisa arsip tahun ajaran lama dan proses kenaikan kelas massal.
> Referensi: `05-database.md` (catatan kenaikan kelas), `11-roadmap.md` Fase 3.

- `[ ]` **Fitur Arsip**: Tombol "Arsipkan Tahun Ajaran" → ubah status `academic_years` ke 'arsip'
- `[ ]` **Wizard Kenaikan Kelas** (multi-step):
  1. Pilih Tahun Ajaran baru (buat atau pilih yang sudah ada)
  2. Mapping massal: siswa aktif → kelas baru (otomatis naik 1 level)
  3. Review manual: tandai siswa yang tinggal kelas / pindah / lulus
  4. Konfirmasi → buat baris baru di `student_enrollments` untuk tahun baru
- `[ ]` Data absensi dan laporan tahun lama tetap read-only dan dapat dilihat
- `[ ]` Log proses kenaikan kelas tersimpan di `promotion_logs`

**Verifikasi Tahap 11 Selesai:**
- [ ] Proses kenaikan kelas menghasilkan enrollment baru untuk tahun baru
- [ ] Data absensi tahun lama tetap dapat diakses
- [ ] Dashboard bisa difilter per tahun ajaran (aktif maupun arsip)

---

### TAHAP 12 — Auto-Mark Alpa (Scheduler)

> **Goal**: Siswa yang tidak scan sampai jam tertentu otomatis dicatat Alpa.

- `[ ]` Buat Artisan Command `attendance:mark-absent`
- `[ ]` Daftarkan ke `app/Console/Kernel.php` (jadwal: setiap hari, misal jam 08.00)
- `[ ]` Logic: cari semua `student_enrollments` aktif yang belum punya `attendance` hari ini → insert dengan status 'alpa'
- `[ ]` Skip jika hari ini adalah `holiday`

**Verifikasi Tahap 12 Selesai:**
- [ ] Command berjalan manual tanpa error: `php artisan attendance:mark-absent`
- [ ] Siswa tanpa scan hari ini ter-mark Alpa setelah jam yang ditentukan

---

## 📊 Status Progres Keseluruhan

| Tahap | Nama | Status |
|-------|------|--------|
| 0 | Inisiasi Proyek & Setup | ✅ Selesai |
| 1 | Skema Database | ✅ Selesai |
| 2 | Multi-Guard Authentication | ✅ Selesai |
| 3 | Data Master & Modul Admin | ⬜ Belum dimulai |
| 4 | Kios Scanner Absensi | ⬜ Belum dimulai |
| 5 | Dashboard Publik | ⬜ Belum dimulai |
| 6 | Portal Wali Kelas | ⬜ Belum dimulai |
| 7 | Portal Siswa | ⬜ Belum dimulai |
| 8 | Import/Export Excel & Kartu OSIS | ⬜ Belum dimulai |
| 9 | Kalender Hari Libur | ⬜ Belum dimulai |
| 10 | Laporan & Dashboard Admin | ⬜ Belum dimulai |
| 11 | Multi-Tahun Ajaran & Kenaikan Kelas | ⬜ Belum dimulai |
| 12 | Auto-Mark Alpa (Scheduler) | ⬜ Belum dimulai |

---

## 🔁 Instruksi untuk AI Agent Saat Memulai Sesi Baru

1. **Baca file ini (`12-progress-projek.md`) dari awal**.
2. **Cek tabel Status Progres** — lihat tahap mana yang terakhir selesai.
3. **Baca dokumen stack** yang relevan untuk tahap yang akan dikerjakan (lihat tabel di atas).
4. **Kerjakan hanya 1 tahap** dalam satu sesi — selesaikan semua checklist-nya.
5. **Update status** di tabel "Status Progres" setelah tahap selesai (ganti ⬜ jadi ✅).
6. **Laporkan ke pengguna** apa saja yang sudah dikerjakan dan minta konfirmasi sebelum lanjut.

---

*Dokumen ini dibuat 1 Juli 2026. Update status setiap kali satu tahap selesai dikerjakan.*
