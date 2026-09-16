# 05. Pemetaan Data & Spesifikasi REST API Bridge (Edisi Khusus SMP)

Dokumen ini mendefinisikan pemetaan (*mapping*) antar kolom dari MySQL (`projek-absensi-barcode`) ke PostgreSQL (`zencbt`), berdasarkan kesepakatan arsitektur untuk jenjang **SMP**:
1. Tabel **`programs`** di ZenCBT dialihfungsikan untuk menampung **Tahun Ajaran**.
2. Kolom **`mapel.program_id`** diset **`NULL`** agar mata pelajaran bersifat universal (tidak perlu dibuat ulang tiap tahun).
3. Kategori asesmen (Ulangan Harian, ASTS, ASAS, Try Out) dipetakan ke **`ujian.event_nama`** di ZenCBT.

---

## 1. Pemetaan Kolom Data (Data Mapping)

### A. Data Tahun Ajaran ➡️ Program ZenCBT
Karena SMP tidak memiliki jurusan (seperti IPA/IPS), tabel `programs` ZenCBT dimanfaatkan secara optimal untuk mengelompokkan siswa per Tahun Ajaran:

| Sumber: MySQL (`academic_years`) | Target: PostgreSQL (`programs`) | Contoh Nilai & Logika |
| :--- | :--- | :--- |
| `name` (misal: "2025/2026") | `code` (VARCHAR) | `"2025-2026"` (slugified) |
| `name` | `nama` (VARCHAR) | `"Tahun Ajaran 2025/2026"` |

> **Efek di ZenCBT**: Operator CBT dapat memfilter siswa di layar admin berdasarkan dropdown **Program** yang otomatis berisi pilihan tahun ajaran!

---

### B. Data Tingkat (Grade Level) ➡️ Levels ZenCBT
| Sumber: MySQL (`classes`) | Target: PostgreSQL (`levels`) | Logika Konversi |
| :--- | :--- | :--- |
| `grade_level` (7, 8, 9) | `code` (VARCHAR) | `(string) $grade_level` (e.g. `"7"`, `"8"`, `"9"`) |
| `grade_level` | `nama` (VARCHAR) | `"Kelas " . $grade_level` (e.g. `"Kelas 7"`) |

---

### C. Data Rombel / Kelas ➡️ Groups ZenCBT
| Sumber: MySQL (`classes`) | Target: PostgreSQL (`groups`) | Logika Konversi |
| :--- | :--- | :--- |
| `grade_level` | `level_id` (INT) | ID lookup dari tabel `levels` di CBT |
| `name` (contoh: "7A", "8B") | `code` (VARCHAR) | `$class->name` (e.g. `"7A"`) |
| `name` | `nama` (VARCHAR) | `"Kelas " . $class->name` (e.g. `"Kelas 7A"`) |

---

### D. Data Mata Pelajaran ➡️ Mapel ZenCBT
| Sumber: MySQL (`mata_pelajarans`) | Target: PostgreSQL (`mapel`) | Logika Konversi |
| :--- | :--- | :--- |
| `kode_mapel` (e.g. "MTK", "IPA") | `code` (VARCHAR) | `$mapel->kode_mapel` |
| `nama_mapel` | `nama` (VARCHAR) | `$mapel->nama_mapel` |
| Kategori Default | `kategori` (VARCHAR) | `"Umum"` atau `"Muatan Lokal"` |
| Jurusan | `program_id` (INT) | **Wajib `NULL`** (Universal antar tahun ajaran) |

---

### E. Data Siswa ➡️ Siswa ZenCBT (PUSH Siswa)
| Sumber: MySQL (`students` + `student_enrollments` + `student_cbt_profiles`) | Target: PostgreSQL (`siswa`) | Logika Konversi |
| :--- | :--- | :--- |
| `students.nisn` (atau `nis`) | `siswa_id` (VARCHAR) | Identifier login siswa di CBT (Wajib unik) |
| `students.name` | `nama` (VARCHAR) | Nama lengkap siswa |
| `student_cbt_profiles.cbt_password` (atau NISN) | `password_plain` (TEXT) | Password terbaca untuk cetak kartu & login CBT (default: NISN) |
| `classes.grade_level` | `level_id` (INT) | Lookup ke tabel `levels` |
| `classes.name` | `group_id` (INT) | Lookup ke tabel `groups` |
| `academic_years.name` | `program_id` (INT) | Lookup ke tabel `programs` (Tahun Ajaran aktif) |
| `students.religion` | `agama` (VARCHAR) | Enum: `Islam`, `Protestan`, `Katolik`, `Hindu`, `Buddha`, `Konghucu` |
| `student_cbt_profiles.cbt_sesi` | `sesi` (VARCHAR) | `"Sesi 1"`, `"Sesi 2"`, dll |
| `student_cbt_profiles.cbt_status` | `status` (VARCHAR) | `'active'`, `'blocked'` |

---

### F. Data Agenda Ujian & Hasil Nilai (PULL Nilai)
| Komponen di ZenCBT | Komponen di Laravel Absensi | Logika Konversi |
| :--- | :--- | :--- |
| `ujian.event_nama` (e.g. `"ASTS Ganjil 2025/2026"`) | `ujian_akademiks.cbt_event_nama` & `jenis_ujian_id` | Tersimpan di kolom event dan ditautkan ke master `jenis_ujians` |
| `ujian.id` | `ujian_akademiks.cbt_ujian_id` | ID referensi sinkronisasi |
| `peserta_ujian.score` | `nilai_ujians.nilai_akhir` | Nilai angka (skala 0 - 100) |
| Detail jawaban di `jawaban_ujian` | `jumlah_benar`, `jumlah_salah`, `jumlah_kosong` | Dihitung agregat per butir soal |
| `peserta_ujian.violation_count` | `nilai_ujians.violation_count` | Catatan pelanggaran layar/tab siswa |
| `peserta_ujian.status` | `nilai_ujians.status_kehadiran` | `'submitted'` ➡️ `'hadir'`, `'absent'` ➡️ `'tidak_hadir'` |

---

## 2. Format Nama Event Ujian (Standarisasi `ujian.event_nama`)

Untuk menjaga kerapian di ZenCBT dan memudahkan pembacaan di Laravel Absensi, disepakati format penamaan standar yang otomatis dipetakan ke master `jenis_ujians`:

| Jenis Asesmen | Format `event_nama` di ZenCBT | Master `jenis_ujians.kode` di Laravel |
| :--- | :--- | :--- |
| **Ulangan Harian** | `UH - [Nama Materi / Bab]` (contoh: `"UH - Aljabar"`) | `UH` |
| **Sumatif Tengah Semester Ganjil** | `"ASTS Ganjil [Tahun Ajaran]"` (contoh: `"ASTS Ganjil 2025/2026"`) | `ASTS` |
| **Sumatif Akhir Semester Ganjil** | `"ASAS Ganjil [Tahun Ajaran]"` (contoh: `"ASAS Ganjil 2025/2026"`) | `ASAS` |
| **Sumatif Tengah Semester Genap** | `"ASTS Genap [Tahun Ajaran]"` (contoh: `"ASTS Genap 2025/2026"`) | `ASTS` |
| **Sumatif Akhir Tahun / Genap** | `"ASAS Genap [Tahun Ajaran]"` (contoh: `"ASAS Genap 2025/2026"`) | `ASAS` |
| **Asesmen Sumatif Akhir Jenjang** | `"ASAJ [Tahun Ajaran]"` (contoh: `"ASAJ 2025/2026"`) | `ASAJ` |
| **Try Out / Asesmen Bakat Minat** | `"Try Out [Ke-N] [Tahun Ajaran]"` (contoh: `"Try Out 1 2025/2026"`) | `TO` / `TRYOUT` |

---

## 3. Spesifikasi Teknis Endpoint REST API Bridge

### Base URL & Header
* **Base URL**: `http://<ip-bridge>:<port>/api/v1`
* **Header**:
  ```http
  Authorization: Bearer <TOKEN_SECRET_64_KARAKTER>
  Content-Type: application/json
  Accept: application/json
  ```

---

### Endpoint 1: `POST /api/v1/sync/master`
Mengirim data struktur akademik: Tahun Ajaran (ke `programs`), Tingkat (ke `levels`), Rombel (ke `groups`), dan Mata Pelajaran (ke `mapel`).

#### Request Payload:
```json
{
  "academic_years": [
    { "code": "2025-2026", "nama": "Tahun Ajaran 2025/2026" }
  ],
  "levels": [
    { "code": "7", "nama": "Kelas 7" },
    { "code": "8", "nama": "Kelas 8" },
    { "code": "9", "nama": "Kelas 9" }
  ],
  "groups": [
    { "level_code": "7", "code": "7A", "nama": "Kelas 7A" },
    { "level_code": "7", "code": "7B", "nama": "Kelas 7B" },
    { "level_code": "8", "code": "8A", "nama": "Kelas 8A" }
  ],
  "mapel": [
    { "code": "MTK", "nama": "Matematika", "kategori": "Umum" },
    { "code": "IPA", "nama": "Ilmu Pengetahuan Alam", "kategori": "Umum" },
    { "code": "PAI", "nama": "Pendidikan Agama Islam", "kategori": "Umum" }
  ]
}
```

#### Response `200 OK`:
```json
{
  "success": true,
  "message": "Master academic data successfully synced to ZenCBT",
  "data": {
    "programs_synced": 1,
    "levels_synced": 3,
    "groups_synced": 3,
    "mapel_synced": 3
  }
}
```

---

### Endpoint 2: `POST /api/v1/sync/students`
Bulk Upsert data siswa dari enrollment aktif ke tabel `siswa` ZenCBT.

#### Request Payload:
```json
{
  "academic_year_code": "2025-2026",
  "students": [
    {
      "siswa_id": "0089123456",
      "nama": "Ahmad Fauzi",
      "password_plain": "0089123456",
      "level_code": "7",
      "group_code": "7A",
      "agama": "Islam",
      "sesi": "Sesi 1",
      "status": "active"
    },
    {
      "siswa_id": "0089123457",
      "nama": "Dewi Sartika",
      "password_plain": "0089123457",
      "level_code": "7",
      "group_code": "7A",
      "agama": "Islam",
      "sesi": "Sesi 2",
      "status": "active"
    }
  ]
}
```

#### Response `200 OK`:
```json
{
  "success": true,
  "message": "Students synced successfully",
  "data": {
    "total": 2,
    "inserted": 0,
    "updated": 2,
    "errors": []
  }
}
```

#### 💡 Mekanisme Anti-Duplikasi & Transisi Kenaikan Kelas (UPSERT)
Karena di ZenCBT kolom `siswa.siswa_id` (NISN) memiliki batasan **`UNIQUE NOT NULL`**, maka **tidak akan pernah ada siswa ganda/duplikat** meskipun berganti tahun ajaran.

Sistem Bridge menjalankan perintah PostgreSQL:
```sql
INSERT INTO siswa (
    siswa_id, nama, password_plain, level_id, group_id, program_id, agama, sesi, status
)
VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9)
ON CONFLICT (siswa_id) DO UPDATE SET
    nama = EXCLUDED.nama,
    password_plain = EXCLUDED.password_plain,
    level_id = EXCLUDED.level_id,        -- Berubah dari Kelas 7 -> Kelas 8
    group_id = EXCLUDED.group_id,        -- Berubah dari 7A -> 8A
    program_id = EXCLUDED.program_id,    -- Berubah dari 2026/2027 -> 2027/2028
    agama = EXCLUDED.agama,
    sesi = EXCLUDED.sesi,
    status = EXCLUDED.status,
    updated_at = NOW();
```

**Simulasi Perjalanan Data Siswa:**
| Atribut Siswa | Saat Tahun Ajaran 2026/2027 | Saat Naik Kelas Tahun Ajaran 2027/2028 |
| :--- | :--- | :--- |
| `siswa_id` (NISN) | `0089123456` (Tetap sama) | `0089123456` (Tetap sama, ID tidak ganda) |
| `nama` | Ahmad Fauzi | Ahmad Fauzi |
| `level_id` (Tingkat) | **Kelas 7** | Diperbarui ke ➡️ **Kelas 8** |
| `group_id` (Rombel) | **Kelas 7A** | Diperbarui ke ➡️ **Kelas 8A** |
| `program_id` (Tahun) | **Tahun Ajaran 2026/2027** | Diperbarui ke ➡️ **Tahun Ajaran 2027/2028** |
| **Status Riwayat Ujian** | Nilai ujian Kelas 7 tersimpan di `peserta_ujian` | **Nilai lama TIDAK HILANG** karena terikat ke ID ujian kelas 7 |

---

### Endpoint 3: `GET /api/v1/exams`
Mengambil daftar ujian yang ada di ZenCBT beserta nama event (`ASTS`, `ASAS`, `UH`).

#### Response `200 OK`:
```json
{
  "success": true,
  "data": [
    {
      "id": 10,
      "title": "Matematika Kelas 7",
      "event_nama": "ASTS Ganjil 2025/2026",
      "mapel_code": "MTK",
      "mapel_nama": "Matematika",
      "start_time": "2026-09-20 07:30:00",
      "end_time": "2026-09-20 09:30:00",
      "durasi": 90,
      "passing_grade": 75.0,
      "peserta_count": 64,
      "submitted_count": 64
    }
  ]
}
```

---

### Endpoint 4: `GET /api/v1/exams/{cbt_ujian_id}/results`
Menarik rekap nilai siswa pada ujian tertentu beserta rincian butir soal benar, salah, kosong, dan pelanggaran.

#### Response `200 OK`:
```json
{
  "success": true,
  "data": {
    "ujian_id": 10,
    "title": "Matematika Kelas 7",
    "event_nama": "ASTS Ganjil 2025/2026",
    "passing_grade": 75.0,
    "total_peserta": 32,
    "results": [
      {
        "siswa_id": "0089123456",
        "nama": "Ahmad Fauzi",
        "group_code": "7A",
        "score": 88.50,
        "score_irt": null,
        "status": "submitted",
        "jumlah_soal": 40,
        "jumlah_benar": 35,
        "jumlah_salah": 5,
        "jumlah_kosong": 0,
        "violation_count": 0,
        "started_at": "2026-09-20 07:35:10",
        "submitted_at": "2026-09-20 08:55:45"
      }
    ]
  }
}
```

---

### Endpoint 5: `POST /api/v1/students/{siswa_id}/reset-session`
Aksi darurat untuk mereset status siswa yang terkunci (*blocked*) akibat peringatan pelanggaran keluar tab/layar di ZenCBT.

#### Request Payload:
```json
{
  "reason": "Izin pengawas ruangan ujian karena browser tertutup tidak sengaja"
}
```

#### Response `200 OK`:
```json
{
  "success": true,
  "message": "Student 0089123456 has been unlocked and session reset"
}
```
