# SPESIFIKASI TEKNIS: MODUL GURU WALI

> **Dokumen Panduan Pengembang & AI Agent**
> **Versi**: 2.3 (Skema revisi: tahun_masuk di level siswa kelompok_guru_wali_siswa, teacher_id di kelompok_guru_wali UNIQUE)
> **Tanggal**: September 2026
> **Landasan Regulasi**: Permendikdasmen No. 11 Tahun 2025 & Kepmendikdasmen No. 221/P/2025
> *(Ekuivalensi Beban Kerja 2 JP/Minggu)*

---

## 1. Ringkasan Eksekutif & Tujuan

Modul ini menambahkan fitur **Jurnal Guru Wali** pada sistem absensi yang sudah ada, dengan **tiga portal** terpisah:

| Portal | Prefix Route | Middleware | Pengguna |
| :--- | :--- | :--- | :--- |
| **Admin** | `/admin` | Filament Auth | Admin / Kepala Sekolah |
| **Portal Guru** | `/portal-guru` | `auth.wali` | Guru Wali |
| **Portal Siswa** | `/portal-siswa` | `auth.siswa` | Siswa Dampingan |

### Prinsip Utama

| Prinsip | Keterangan |
| :--- | :--- |
| **Kelompok Dampingan Multi-Tahun** | Terpisah dari Rombel akademik (`student_enrollments`). Bersifat permanen sejak siswa masuk hingga lulus. Mirip seperti "kelas" namun lintas tahun ajaran. |
| **Form Input Terstruktur** | Dropdown terstandar untuk kategori pendampingan agar mudah direkapitulasi. |
| **Privasi Ketat (Strict Isolation)** | Jurnal hanya dapat dibaca oleh Guru Wali pemilik. Guru Wali lain, Wali Kelas, dan Guru BK tidak dapat mengakses. |
| **Indikator Minimum Bulanan** | Setiap siswa harus mendapat pendampingan langsung **minimal 1x per bulan**. |
| **Siswa: Read-Only Terbatas** | Siswa hanya melihat ringkasan sesi & pesan tindak lanjut dari Guru Walinya sendiri. |

---

## 2. Arsitektur Portal & Navigasi Menu

### 2.1 Portal Admin — `/admin` (Filament)

Menu baru di bawah grup **"Guru Wali"** pada sidebar Filament Admin:

```
/admin
└── Guru Wali                          [grup baru]
    ├── Daftar Kelompok    → /admin/guru-wali/kelompok
    ├── Tambah Kelompok    → /admin/guru-wali/kelompok/create
    ├── Penugasan          → /admin/guru-wali/penugasan
    └── Laporan & Rekap    → /admin/guru-wali/laporan
```

### 2.2 Portal Guru — `/portal-guru`

Menu baru di bawah grup **"Wali Dampingan"** pada sidebar Portal Guru (Livewire):

```
/portal-guru
└── Wali Dampingan                      [grup baru di sidebar]
    ├── Kelompok Saya      → /portal-guru/guru-wali/kelompok
    ├── Input Jurnal       → /portal-guru/guru-wali/jurnal/create
    ├── Riwayat Jurnal     → /portal-guru/guru-wali/jurnal
    └── Pemantauan Bulanan → /portal-guru/guru-wali/pemantauan
```

### 2.3 Portal Siswa — `/portal-siswa`

Menu baru **"Guru Wali Saya"** pada sidebar Portal Siswa (Livewire):

```
/portal-siswa
└── Guru Wali Saya                      [menu baru di sidebar]
    ├── Informasi Guru Wali → /portal-siswa/guru-wali
    └── Catatan Saya        → /portal-siswa/guru-wali/catatan
```

---

## 3. Konsep Kelompok (Seperti Kelas)

Kelompok Dampingan bekerja mirip seperti "kelas", namun bersifat lintas tahun ajaran dan dikelola khusus oleh seorang Guru Wali.

### Analogi Perbandingan

| Aspek | Kelas / Rombel (Existing) | Kelompok Dampingan Guru Wali (Baru) |
| :--- | :--- | :--- |
| **Tabel DB** | `classes` + `student_enrollments` | `kelompok_guru_wali` + `kelompok_guru_wali_siswa` |
| **Pengelola** | Wali Kelas (FK `teacher_id` di `class_academic_year`) | Guru Wali (FK `teacher_id` di `kelompok_guru_wali`) |
| **Sifat** | Per tahun ajaran (`academic_year_id`) | Multi-tahun (tidak terikat `academic_year_id`) |
| **Tujuan** | Administrasi akademik & presensi | Pendampingan personal & karakter |
| **Dibuat oleh** | Admin | Admin (ditugaskan ke Guru Wali) |
| **Akses data** | Wali Kelas bisa lihat semua siswa kelasnya | Hanya Guru Wali pemilik kelompok |

### Alur Pembentukan Kelompok

```
[Admin] Buat Kelompok Baru di /admin/guru-wali/kelompok/create
    → Input nama kelompok (cth: "Kelompok Dandelion")
    → Pilih Guru Wali → FK ke tabel teachers (uuid)
    → Tambahkan Siswa → FK ke tabel students (uuid)
    → Simpan → Kelompok aktif, Guru Wali bisa mulai input jurnal
```

---

## 4. Arsitektur Data & Skema Basis Data (Database Schema)

> **Catatan Akurasi DB**: Tabel `teachers` menggunakan UUID dan punya kolom `user_id` (FK ke `users`).
> Tabel `students` juga UUID dan punya `user_id` (FK ke `users`). Kedua tabel pakai `SoftDeletes`.

### 4.1 Tabel Baru: `kelompok_guru_wali`
Menyimpan entitas kelompok dampingan (seperti "kelas" untuk Guru Wali).

```sql
CREATE TABLE kelompok_guru_wali (
    id            CHAR(36)     NOT NULL PRIMARY KEY,       -- UUID, pakai HasUuids
    nama_kelompok VARCHAR(100) NOT NULL,                   -- Contoh: "Kelompok Dandelion"
    teacher_id    CHAR(36)     NOT NULL UNIQUE,            -- FK ke tabel teachers.id (UUID), 1 guru = 1 kelompok
    status_aktif  TINYINT(1)   NOT NULL DEFAULT 1,
    created_at    TIMESTAMP    NULL,
    updated_at    TIMESTAMP    NULL,
    deleted_at    TIMESTAMP    NULL,                       -- SoftDeletes

    FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE RESTRICT
);
```

> **Catatan**: Kolom FK ke guru menggunakan `teacher_id` (bukan `guru_id`) agar konsisten
> dengan konvensi naming project ini (tabel `teachers`, model `Guru`).
> Kolom `teacher_id` memiliki unique constraint karena 1 guru wali hanya memegang 1 kelompok dampingan permanen.

### 4.2 Tabel Baru: `kelompok_guru_wali_siswa`
Mapping siswa ke kelompok dampingan (1 siswa aktif hanya boleh punya 1 Guru Wali aktif). Kolom `tahun_masuk` berada di level keanggotaan siswa untuk mencatat kapan siswa bergabung ke kelompok.

```sql
CREATE TABLE kelompok_guru_wali_siswa (
    id           CHAR(36)   NOT NULL PRIMARY KEY,         -- UUID
    kelompok_id  CHAR(36)   NOT NULL,                     -- FK ke kelompok_guru_wali.id
    student_id   CHAR(36)   NOT NULL,                     -- FK ke students.id (UUID)
    tahun_masuk  YEAR       NOT NULL,                     -- Tahun siswa bergabung ke kelompok dampingan ini
    status_aktif TINYINT(1) NOT NULL DEFAULT 1,
    created_at   TIMESTAMP  NULL,
    updated_at   TIMESTAMP  NULL,

    FOREIGN KEY (kelompok_id) REFERENCES kelompok_guru_wali(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id)  REFERENCES students(id) ON DELETE RESTRICT,

    -- Satu siswa hanya boleh aktif di 1 kelompok pada waktu bersamaan
    UNIQUE INDEX unique_student_aktif (student_id, status_aktif)
);
```

> **Catatan**: Kolom FK ke siswa menggunakan `student_id` (bukan `siswa_id`) agar konsisten
> dengan konvensi project (`students`, `student_enrollments`, `student_id`).

### 4.3 Tabel Baru: `jurnal_guru_wali`
Menyimpan catatan jurnal pendampingan harian.

```sql
CREATE TABLE jurnal_guru_wali (
    id                    CHAR(36)     NOT NULL PRIMARY KEY,   -- UUID
    teacher_id            CHAR(36)     NOT NULL,               -- FK ke teachers.id
    kelompok_id           CHAR(36)     NOT NULL,               -- FK ke kelompok_guru_wali.id
    student_id            CHAR(36)     NOT NULL,               -- FK ke students.id
    tanggal_waktu         DATETIME     NOT NULL,
    jenis_pendampingan    ENUM(
        'Individu',
        'Kelompok Kecil',
        'Klasikal'
    )                               NOT NULL,
    kategori_pendampingan ENUM(
        'Akademik',
        'Karakter & Kedisiplinan',
        'Minat & Bakat / Ekskul',
        'Sosial & Psikologis'
    )                               NOT NULL,
    uraian_pembahasan     TEXT        NOT NULL,
    status_sesi           ENUM(
        'Tuntas / Selesai',
        'Dalam Pemantauan',
        'Bimbingan Lanjutan'
    )                               NOT NULL,
    rujukan_kolaborasi    ENUM(
        'Mandiri',
        'Wali Kelas',
        'Guru BK',
        'Orang Tua / Wali'
    )                               NOT NULL DEFAULT 'Mandiri',
    rencana_tindak_lanjut TEXT        NOT NULL,
    is_public_note        TINYINT(1)  NOT NULL DEFAULT 0,      -- Jika 1, siswa bisa lihat
    created_at            TIMESTAMP   NULL,
    updated_at            TIMESTAMP   NULL,
    deleted_at            TIMESTAMP   NULL,                    -- SoftDeletes

    FOREIGN KEY (teacher_id)  REFERENCES teachers(id) ON DELETE RESTRICT,
    FOREIGN KEY (kelompok_id) REFERENCES kelompok_guru_wali(id) ON DELETE RESTRICT,
    FOREIGN KEY (student_id)  REFERENCES students(id) ON DELETE RESTRICT,

    INDEX idx_teacher_tanggal (teacher_id, tanggal_waktu),
    INDEX idx_student_tanggal (student_id, tanggal_waktu)
);
```

> **Tambahan kolom `is_public_note`**: Jika `TRUE`, siswa bisa melihat `rencana_tindak_lanjut`
> sebagai catatan motivasi dari Guru Wali. `uraian_pembahasan` tetap PRIVAT (tidak pernah tampil ke siswa).

---

## 5. Matriks Hak Akses (Access Control & Isolation)

Hak akses diatur secara ketat (*Row-Level Security* di level Controller/Policy):

| Peran (Role) | Kelola Kelompok | Baca `uraian_pembahasan` | Baca `rencana_tindak_lanjut` | Tulis Jurnal |
| :--- | :---: | :---: | :---: | :---: |
| **Admin / Kepsek** | ✅ Full CRUD | ✅ Read-Only | ✅ Read-Only | ❌ |
| **Guru Wali (Pemilik)** | ❌ (hanya lihat kelompoknya) | ✅ Full | ✅ Full | ✅ Full |
| **Guru Wali Lain** | ❌ | ❌ 403 | ❌ 403 | ❌ |
| **Wali Kelas** | ❌ | ❌ 403 | ❌ 403 | ❌ |
| **Guru BK** | ❌ | ❌ 403 | ❌ 403 | ❌ |
| **Siswa Dampingan** | ❌ | ❌ (privat) | ✅ Jika `is_public_note = 1` | ❌ |

> **📌 Catatan Auto-Arsip**: Siswa dengan status `'lulus'` atau `'mutasi'` otomatis dikeluarkan dari keanggotaan aktif kelompok via `SiswaObserver` (kolom `status_aktif = false` di `kelompok_guru_wali_siswa`), namun **seluruh riwayat jurnal dan riwayat keanggotaan kelompok tetap tersimpan** sebagai arsip dan dapat dilihat kembali (read-only) oleh Admin. Pengeluaran manual oleh Admin juga menggunakan mekanisme soft-archive yang sama (bukan hard delete).

---

## 6. Spesifikasi Halaman — Portal Admin (`/admin`)

> Implementasi menggunakan **Filament Resource** (sudah dipakai di project ini untuk semua modul admin).

### 6.1 Daftar Kelompok — `/admin/guru-wali/kelompok`

**Filament Resource**: `KelompokGuruWaliResource`

| Kolom Tabel | Keterangan |
| :--- | :--- |
| Nama Kelompok | Nama kelompok |
| Guru Wali | Relasi ke `teachers.name` |
| Jumlah Siswa | Count dari `kelompok_guru_wali_siswa` |
| Tahun Masuk | Tahun angkatan |
| Status Aktif | Badge Aktif / Nonaktif |
| Aksi | Lihat Detail, Edit, Nonaktifkan |

- Tombol **"+ Tambah Kelompok"** → mengarah ke form create
- Filter: Status Aktif / Tidak Aktif, Guru Wali
- Search: nama kelompok, nama guru

---

### 6.2 Tambah / Edit Kelompok — `/admin/guru-wali/kelompok/create`

**Form Input (Filament Form):**

| Field | Tipe Filament | Keterangan |
| :--- | :--- | :--- |
| Nama Kelompok | `TextInput` | Wajib. Contoh: "Kelompok Dandelion" |
| Guru Wali | `Select` → relasi `teachers` | Filter hanya guru aktif (`deleted_at IS NULL`) |
| Tahun Masuk | `Select` / `TextInput` | Tahun 4 digit |
| Status Aktif | `Toggle` | Default: Aktif |
| Daftar Siswa | `CheckboxList` / `Select (multiple)` | Pilih dari `students` aktif, tampilkan NISN + Nama |

> **Validasi**: Jika siswa sudah terdaftar di kelompok aktif lain, tampilkan peringatan dan blokir penyimpanan.

---

### 6.3 Penugasan Guru Wali — `/admin/guru-wali/penugasan`

- Tabel semua guru beserta info kelompok dampingan yang diampu.
- Kolom: `Nama Guru`, `NIP`, `Kelompok Diampu`, `Jumlah Siswa`, `Status`
- Admin bisa mengubah Guru Wali yang bertanggung jawab atas sebuah kelompok (reassign).

---

### 6.4 Laporan & Rekap — `/admin/guru-wali/laporan`

**Fitur:**
- Rekap frekuensi pendampingan per bulan per guru wali (verifikasi ekuivalensi 2 JP)
- Filter: Guru Wali (`teacher_id`), Kelompok, Bulan/Tahun, Kategori Pendampingan
- Export ke **PDF** / **Excel**
- Tabel rekap: nama siswa + indikator 🔴 jika belum didampingi bulan berjalan

---

## 7. Spesifikasi Halaman — Portal Guru (`/portal-guru`)

> Implementasi menggunakan **Livewire** (konsisten dengan modul portal guru yang sudah ada).
> Route ditambahkan di `routes/web.php` dalam prefix `portal-guru` middleware `auth.wali`.

### 7.1 Kelompok Saya — `/portal-guru/guru-wali/kelompok`

**Livewire Component**: `PortalGuru\GuruWali\KelompokSaya`

| Kolom | Keterangan |
| :--- | :--- |
| Nama Siswa | Dari `students.name` |
| NISN | Dari `students.nisn` |
| NIS | Dari `students.nis` |
| Status Bulan Ini | 🟢 Sudah / 🔴 Belum (cek `jurnal_guru_wali` bulan berjalan) |
| Terakhir Didampingi | `MAX(tanggal_waktu)` dari `jurnal_guru_wali` |
| Aksi | Tombol "Input Jurnal" (pre-fill `student_id`) |

---

### 7.2 Input Jurnal — `/portal-guru/guru-wali/jurnal/create`

**Livewire Component**: `PortalGuru\GuruWali\InputJurnal`

**Alur Input:**
1. Header auto-populate dari sesi login: Nama Guru, NIP (dari `teachers.nip`), Unit Kerja (dari `school_settings`).
2. Dropdown Pilih Kelompok → Dropdown Pilih Siswa (dengan indikator 🟢/🔴).
3. Isi form jurnal:

| Field | Tipe | Keterangan |
| :--- | :--- | :--- |
| Tanggal & Waktu | Datetime Picker | Default: `now()` |
| Jenis Pendampingan | Dropdown | `Individu`, `Kelompok Kecil`, `Klasikal` |
| Kategori Pendampingan | Dropdown | `Akademik`, `Karakter & Kedisiplinan`, `Minat & Bakat / Ekskul`, `Sosial & Psikologis` |
| Uraian Pembahasan | Textarea | Wajib, min. 10 karakter. **PRIVAT** — tidak pernah tampil ke siswa. |
| Status Sesi | Dropdown | `Tuntas / Selesai`, `Dalam Pemantauan`, `Bimbingan Lanjutan` |
| Rujukan / Kolaborasi | Dropdown | `Mandiri`, `Wali Kelas`, `Guru BK`, `Orang Tua / Wali` |
| Rencana Tindak Lanjut | Textarea | Wajib. |
| Tampilkan ke Siswa? | Toggle (`is_public_note`) | Jika ON, siswa bisa melihat `rencana_tindak_lanjut`. |

---

### 7.3 Riwayat Jurnal — `/portal-guru/guru-wali/jurnal`

**Livewire Component**: `PortalGuru\GuruWali\RiwayatJurnal`

- Daftar semua jurnal yang diinput oleh guru yang login (`teacher_id = auth teacher`).
- Filter: Nama Siswa, Bulan/Tahun, Kategori, Status Sesi.
- Aksi per baris: **Lihat Detail**, **Edit** (hanya di hari yang sama), **Hapus (SoftDelete)**.

---

### 7.4 Pemantauan Bulanan — `/portal-guru/guru-wali/pemantauan`

**Livewire Component**: `PortalGuru\GuruWali\PemantauanBulanan`

- Ringkasan per bulan: daftar siswa + frekuensi pendampingan.
- Highlight 🔴 siswa yang belum didampingi di bulan berjalan.
- Statistik: Total sesi bulan ini, rata-rata per siswa, daftar siswa yang perlu segera didampingi.

---

## 8. Spesifikasi Halaman — Portal Siswa (`/portal-siswa`)

> Implementasi menggunakan **Livewire** (konsisten dengan modul portal siswa yang sudah ada).
> Route ditambahkan di `routes/web.php` dalam prefix `portal-siswa` middleware `auth.siswa`.

### 8.1 Informasi Guru Wali Saya — `/portal-siswa/guru-wali`

**Livewire Component**: `PortalSiswa\GuruWali\InfoGuruWali`

Siswa melihat informasi dasar Guru Wali yang mendampinginya.

| Informasi | Sumber Data |
| :--- | :--- |
| Nama Guru Wali | `teachers.name` |
| NIP | `teachers.nip` |
| Foto | `teachers.photo_path` |
| Nama Kelompok | `kelompok_guru_wali.nama_kelompok` |
| Jumlah Teman Satu Kelompok | Count siswa aktif di kelompok yang sama |

> Jika siswa belum terdaftar di kelompok manapun, tampilkan pesan:
> *"Kamu belum memiliki Guru Wali yang ditugaskan. Hubungi Admin untuk informasi lebih lanjut."*

---

### 8.2 Catatan Saya — `/portal-siswa/guru-wali/catatan`

**Livewire Component**: `PortalSiswa\GuruWali\CatatanSaya`

Siswa melihat ringkasan sesi pendampingan yang **ditandai publik** oleh Guru Wali (`is_public_note = 1`).

**Yang TAMPIL ke siswa:**
- Tanggal sesi
- Kategori pendampingan
- Rencana Tindak Lanjut (`rencana_tindak_lanjut`)

**Yang TIDAK TAMPIL ke siswa (PRIVAT):**
- `uraian_pembahasan` — catatan detail privat Guru Wali
- `rujukan_kolaborasi`
- `status_sesi`

**Komponen UI:**
- Timeline/list catatan dari Guru Wali (hanya `is_public_note = 1`)
- Jika tidak ada catatan publik: tampilkan pesan motivasi netral

---

## 9. Spesifikasi API Backend

> Implementasi melalui **Livewire actions** (bukan REST API terpisah), konsisten dengan
> pola yang sudah digunakan di semua portal (portal-guru, portal-siswa, portal-presensi).

### 9.1 Query Utama — Daftar Siswa + Indikator Status

```php
// Di KelompokSaya Livewire Component
// Ambil teacher dari user yang login
$teacher = auth()->user()->teacher; // via User->teacher() relation

$kelompok = KelompokGuruWali::with('siswa')
    ->where('teacher_id', $teacher->id)
    ->where('status_aktif', true)
    ->firstOrFail();

$siswaList = $kelompok->siswa()
    ->withCount([
        'jurnals as sudah_bulan_ini' => function ($q) {
            $q->whereYear('tanggal_waktu', now()->year)
              ->whereMonth('tanggal_waktu', now()->month);
        }
    ])
    ->withMax('jurnals', 'tanggal_waktu')
    ->get();
```

### 9.2 Validasi Simpan Jurnal — Pastikan Siswa Anggota Kelompok

```php
// Di InputJurnal Livewire Component — method save()
$teacher = auth()->user()->teacher;

$isMember = KelompokGuruWaliSiswa::where('kelompok_id', $this->kelompok_id)
    ->where('student_id', $this->student_id)
    ->where('status_aktif', true)
    ->whereHas('kelompok', fn($q) => $q->where('teacher_id', $teacher->id))
    ->exists();

if (!$isMember) {
    abort(403, 'Siswa bukan anggota kelompok dampingan Anda.');
}
```

### 9.3 Query Catatan Siswa (Portal Siswa)

```php
// Di CatatanSaya Livewire Component
$student = auth()->user()->student; // via User->student() relation

$catatan = JurnalGuruWali::where('student_id', $student->id)
    ->where('is_public_note', true)
    ->select(['tanggal_waktu', 'kategori_pendampingan', 'rencana_tindak_lanjut'])
    // Tidak mengambil uraian_pembahasan — PRIVAT
    ->orderByDesc('tanggal_waktu')
    ->get();
```

### 9.4 Contoh Struktur Response Data Kelompok Siswa

```json
{
  "kelompok": {
    "id": "uuid-kelompok",
    "nama_kelompok": "Kelompok Dandelion"
  },
  "siswa": [
    {
      "id": "uuid-student",
      "nisn": "0123456789",
      "name": "Rizky Pratama",
      "tahun_masuk": 2024,
      "sudah_bulan_ini": 1,
      "jurnals_max_tanggal_waktu": "2026-09-02 13:00:00"
    },
    {
      "id": "uuid-student-2",
      "nisn": "0987654321",
      "name": "Dewi Lestari",
      "tahun_masuk": 2024,
      "sudah_bulan_ini": 0,
      "jurnals_max_tanggal_waktu": "2026-08-15 10:30:00"
    }
  ]
}
```

---

## 10. Checklist Pengujian & Verifikasi

### Portal Admin
- [ ] Admin bisa membuat kelompok baru dengan nama, guru wali (`teacher_id`), dan daftar siswa (`student_id`).
- [ ] Admin bisa menambah/menghapus siswa dari kelompok yang sudah ada.
- [ ] Peringatan muncul jika siswa yang ditambahkan sudah aktif di kelompok lain.
- [ ] Admin bisa melihat laporan rekap jurnal semua guru wali dengan filter bulan.
- [ ] Halaman laporan menampilkan indikator 🔴 untuk siswa yang belum didampingi.

### Portal Guru
- [ ] Guru Wali hanya melihat daftar siswa di kelompok dampingannya sendiri (`teacher_id` cocok).
- [ ] Indikator 🟢/🔴 akurat sesuai data jurnal bulan berjalan.
- [ ] Setelah menyimpan jurnal, indikator siswa berubah ke 🟢 secara realtime.
- [ ] Dropdown kategori mencakup 4 pilar yang benar.
- [ ] Header auto-populate: Nama Guru dari `teachers.name`, NIP dari `teachers.nip`.
- [ ] Edit jurnal hanya bisa dilakukan di hari yang sama (`DATE(created_at) = today`).

### Portal Siswa
- [ ] Siswa hanya melihat info Guru Wali dan catatan dari kelompok **miliknya sendiri**.
- [ ] Siswa tidak dapat melihat `uraian_pembahasan` (field privat).
- [ ] Catatan yang tampil hanya yang `is_public_note = 1`.
- [ ] Jika belum punya Guru Wali, tampilkan pesan informatif (bukan error).

### Isolasi Hak Akses
- [ ] Guru Wali lain yang mencoba akses jurnal/kelompok bukan miliknya mendapat `403 Forbidden`.
- [ ] Wali Kelas & Guru BK tidak bisa mengakses endpoint/komponen jurnal guru wali.
- [ ] Admin dapat mengakses semua data hanya dalam mode read-only untuk supervisi.
- [ ] Siswa yang mencoba akses langsung ke URL jurnal guru mendapat `403`.

---

### Riwayat Revisi

| Versi | Tanggal | Perubahan |
| :--- | :--- | :--- |
| v2.0 | September 2026 | Versi awal revisi: tiga portal (Admin, Guru, Siswa), konsep kelompok, skema DB, API, checklist |
| v2.1 | September 2026 | Disesuaikan dengan struktur DB aktual: `teacher_id`, `student_id`, UUID, pola Livewire |
| v2.2 | September 2026 | Menambahkan mekanisme auto-arsip (bukan hard delete) untuk siswa lulus/mutasi dan pengeluaran manual oleh Admin dari kelompok. Ditambahkan relasi `riwayatGuruWali()` di Model Siswa. |
| v2.3 | September 2026 | Refactoring skema: `tahun_masuk` dipindahkan dari kelompok ke level keanggotaan siswa (`kelompok_guru_wali_siswa`), kolom `teacher_id` di `kelompok_guru_wali` diberi unique constraint (1 guru = 1 kelompok dampingan). |

---

## 11. Referensi Struktur Database Aktual (Project Ini)

Untuk memastikan konsistensi, berikut tabel & konvensi naming yang sudah ada:

| Entitas | Tabel DB | Model Laravel | PK Type | Auth Guard |
| :--- | :--- | :--- | :--- | :--- |
| Guru | `teachers` | `App\Models\Guru` | UUID | `auth.wali` |
| Siswa | `students` | `App\Models\Siswa` | UUID | `auth.siswa` |
| Kelas | `classes` | `App\Models\Kelas` | UUID | — |
| Enrollment | `student_enrollments` | `App\Models\EnrollmentSiswa` | UUID | — |
| User | `users` | `App\Models\User` | UUID | `web` |

> **Catatan**: Tabel `teachers` dan `students` keduanya punya kolom `user_id` (FK ke `users.id`)
> yang digunakan untuk login via Livewire unified login. Relasi: `User->teacher()` dan `User->student()`.
