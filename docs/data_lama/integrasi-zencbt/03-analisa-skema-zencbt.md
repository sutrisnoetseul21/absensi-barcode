# 03. Analisis Skema Database ZenCBT (PostgreSQL)

Dokumen ini membedah struktur internal database **ZenCBT v1.4.0** berbasis PostgreSQL (`init_db_zencbt_1.x.x.sql`). Pemahaman skema ini sangat penting agar proses *push* (sinkronisasi siswa/kelas) dan *pull* (penarikan nilai) berjalan tanpa kendala integritas relasi data (*foreign key constraints*).

---

## 1. Peta Tabel Utama ZenCBT

ZenCBT memiliki 20 tabel yang terbagi dalam 5 domain utama:

```
[ Domain 1: Pengguna & Hak Akses ]
  └── users, privilege, audit_logs

[ Domain 2: Master Data Akademik ]
  ├── levels (Tingkat: X, XI, XII / 7, 8, 9)
  ├── groups (Rombel/Kelas: 7A, 8B, X RPL 1)
  ├── programs (Jurusan/Peminatan: IPA, IPS, RPL, Umum)
  └── mapel (Mata Pelajaran)

[ Domain 3: Data Siswa (Peserta Tes) ]
  └── siswa (Akun login, password plain, rombel, tingkat, sesi)

[ Domain 4: Soal & Bank Soal ]
  ├── bank_soal, bank_soal_collaborators
  └── soal (pg, pgk, isian, essay, benar, matriks, menjodohkan)

[ Domain 5: Pelaksanaan Ujian & Penilaian ]
  ├── ujian (Jadwal, durasi, KKM, token, sesi)
  ├── ujian_group (Daftar kelas yang ditugaskan ikut ujian)
  ├── peserta_ujian (Skor akhir, status kehadiran, pelanggaran)
  └── jawaban_ujian (Detail butir jawaban & poin tiap soal)
```

---

## 2. Bedah Tabel yang Terkait Langsung dengan Integrasi

### A. Tabel `levels` (Tingkat Pendidikan)
Menyimpan data angkatan / tingkat kelas (misal: "7", "8", "9" atau "X", "XI", "XII").
```sql
CREATE TABLE levels (
    id SERIAL PRIMARY KEY,
    code VARCHAR(50) UNIQUE NOT NULL, -- Contoh: "7", "8", "9"
    nama VARCHAR(100) NOT NULL,       -- Contoh: "Kelas 7", "Kelas 8"
    created_at TIMESTAMP DEFAULT NOW()
);
```
> **Aturan**: Kolom `code` harus unik. Ini adalah induk dari tabel `groups` dan `siswa`.

---

### B. Tabel `groups` (Rombel / Kelas)
Menyimpan nama rombongan belajar (kelas siswa).
```sql
CREATE TABLE groups (
    id SERIAL PRIMARY KEY,
    level_id INT NOT NULL REFERENCES levels(id) ON DELETE CASCADE,
    code VARCHAR(50) NOT NULL,        -- Contoh: "7A", "8B"
    nama VARCHAR(100) NOT NULL,       -- Contoh: "Kelas 7A"
    created_at TIMESTAMP DEFAULT NOW(),
    UNIQUE(level_id, code)            -- Kombinasi tingkat dan kode harus unik
);
```
> **Aturan**: Setiap kelas wajib terikat pada satu `level_id`.

---

### C. Tabel `programs` (Jurusan / Program Keahlian)
```sql
CREATE TABLE programs (
    id SERIAL PRIMARY KEY,
    code VARCHAR(50) UNIQUE NOT NULL, -- Contoh: "UMUM", "IPA", "RPL"
    nama VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT NOW()
);
```
> **Catatan**: Jika di jenjang SMP/MTS tidak ada jurusan, dapat dibuat satu program default bernama `"UMUM"` atau `"REGULER"`.

---

### D. Tabel `mapel` (Mata Pelajaran)
```sql
CREATE TABLE mapel (
    id SERIAL PRIMARY KEY,
    code VARCHAR(50) UNIQUE NOT NULL, -- Kode Mapel (e.g. "MTK", "BINDO")
    nama VARCHAR(100) NOT NULL,       -- Nama Mapel (e.g. "Matematika")
    kategori VARCHAR(50),             -- Kelompok A, Kelompok B, Muatan Lokal
    program_id INT REFERENCES programs(id) ON DELETE SET NULL,
    created_at TIMESTAMP DEFAULT NOW()
);
```

---

### E. Tabel `siswa` (Akun Peserta Ujian ZenCBT)
Inilah tabel kunci untuk proses **PUSH Data Siswa**.
```sql
CREATE TABLE siswa (
    id SERIAL PRIMARY KEY,
    siswa_id VARCHAR(50) UNIQUE NOT NULL, -- ID Login Siswa (Wajib diisi NISN atau NIS)
    nama VARCHAR(100) NOT NULL,           -- Nama Lengkap Siswa
    password_plain TEXT NOT NULL,         -- Password teks biasa untuk login siswa
    level_id INT REFERENCES levels(id) ON DELETE SET NULL,
    group_id INT REFERENCES groups(id) ON DELETE SET NULL,
    program_id INT REFERENCES programs(id) ON DELETE SET NULL,
    agama VARCHAR(20) CHECK (agama IN ('Islam', 'Protestan', 'Katolik', 'Hindu', 'Buddha', 'Konghucu')),
    status VARCHAR(20) DEFAULT 'active' CHECK (status IN ('active', 'blocked')),
    blocked_reason TEXT,
    blocked_at TIMESTAMP,
    photo_path TEXT,
    sesi VARCHAR(10) DEFAULT 'Sesi 1',    -- 'Sesi 1', 'Sesi 2', ..., 'Sesi 8'
    last_activity TIMESTAMP,
    current_activity VARCHAR(50) DEFAULT 'offline', -- 'online', 'exam', 'view_result', 'offline'
    browser_info TEXT DEFAULT '',
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);
```

> **Perhatian Khusus**:
> 1. `siswa_id`: Merupakan identifier login di layar depan ZenCBT. Sangat direkomendasikan menggunakan **NISN**.
> 2. `password_plain`: Harus berupa teks biasa (bukan hash).
> 3. `sesi`: Sangat berguna jika ujian di sekolah dibagi per gelombang jam masuk.
> 4. `status`: Nilai harus `'active'` agar siswa diizinkan login. Jika ada siswa terkunci karena keluar fullscreen/tab, statusnya bisa berubah menjadi `'blocked'`.

---

### F. Tabel `ujian` & `ujian_group` (Agenda Ujian)
```sql
CREATE TABLE ujian (
    id SERIAL PRIMARY KEY,
    title VARCHAR(255) NOT NULL,          -- Judul Ujian
    event_nama VARCHAR(100),              -- Agenda (e.g. "STS Ganjil 2025/2026")
    mapel_id INT REFERENCES mapel(id),
    bank_id INT NOT NULL REFERENCES bank_soal(id),
    start_time TIMESTAMP NOT NULL,
    end_time TIMESTAMP NOT NULL,
    durasi INT NOT NULL,                  -- Dalam menit
    passing_grade NUMERIC(5,2),           -- KKM (e.g. 75.00)
    peserta_type VARCHAR(20) DEFAULT 'umum', -- 'umum' (per kelas) atau 'spesifik'
    sesi VARCHAR(10) DEFAULT 'Sesi 1',
    is_active BOOLEAN DEFAULT false,
    ...
);
```

---

### G. Tabel `peserta_ujian` (Hasil & Nilai Ujian)
Inilah tabel kunci untuk proses **PULL Data Nilai**.
```sql
CREATE TABLE peserta_ujian (
    id SERIAL PRIMARY KEY,
    ujian_id INT NOT NULL REFERENCES ujian(id) ON DELETE CASCADE,
    siswa_id INT NOT NULL REFERENCES siswa(id) ON DELETE CASCADE,
    token VARCHAR(100),
    status VARCHAR(20) DEFAULT 'not_started', -- 'not_started', 'ongoing', 'submitted', 'absent'
    started_at TIMESTAMP,                     -- Waktu mulai membuka soal
    submitted_at TIMESTAMP,                   -- Waktu menekan tombol selesai
    score NUMERIC(5,2) DEFAULT 0,             -- Nilai Akhir (Skala 0 - 100)
    score_irt DOUBLE PRECISION,               -- Nilai Item Response Theory (jika diaktifkan)
    violation_count INT DEFAULT 0 NOT NULL,   -- Pelanggaran (buka tab baru, switch app)
    created_at TIMESTAMP DEFAULT NOW(),
    UNIQUE(ujian_id, siswa_id)
);
```

---

### H. Tabel `jawaban_ujian` (Rincian Jawaban Per Butir Soal)
Jika ingin menganalisis butir soal yang dijawab benar/salah oleh siswa:
```sql
CREATE TABLE jawaban_ujian (
    id SERIAL PRIMARY KEY,
    ujian_id INT NOT NULL REFERENCES ujian(id) ON DELETE CASCADE,
    siswa_id INT NOT NULL REFERENCES siswa(id) ON DELETE CASCADE,
    soal_id INT NOT NULL REFERENCES soal(id) ON DELETE CASCADE,
    answer_text TEXT,
    is_submitted BOOLEAN DEFAULT false,
    is_correct BOOLEAN,                       -- true = benar, false = salah
    nilai NUMERIC(5,2),                       -- Poin yang diperoleh di butir ini
    submitted_at TIMESTAMP,
    UNIQUE(ujian_id, siswa_id, soal_id)
);
```
Dengan menghitung `COUNT(CASE WHEN is_correct = true THEN 1 END)`, kita bisa langsung memperoleh **jumlah jawaban benar**, **jumlah salah**, dan **jumlah kosong** per siswa!
