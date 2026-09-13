# SPESIFIKASI TEKNIS FITUR: KONSULTASI DUA ARAH SISWA & GURU WALI

> **Dokumen Spesifikasi Pengembang & Prompt AI Agent**
> **Versi**: 1.1 (Revisi konvensi nama tabel & kolom sesuai skema project aktual)
> **Tanggal**: September 2026
> **Landasan Regulasi**: Permendikdasmen No. 11 Tahun 2025 & Kepmendikdasmen No. 221/P/2025 (Pembangunan Kedekatan Personal & Pendampingan Holistik)

> **⚠️ Catatan Revisi (v1.1)**: Draft awal dokumen ini menggunakan nama tabel `siswa`/`guru` dan kolom `siswa_id`/`guru_id` yang **TIDAK sesuai** dengan skema aktual project. Telah dikoreksi mengikuti konvensi project yang sebenarnya:
> - Tabel: `students` (bukan `siswa`), `teachers` (bukan `guru`)
> - FK kolom: `student_id` (bukan `siswa_id`), `teacher_id` (bukan `guru_id`)
> - Tipe UUID: `CHAR(36)` (bukan `VARCHAR(36)`)
> - Model Laravel: `App\Models\Siswa`, `App\Models\Guru` (bukan Student/Teacher)
> - Implementasi: **Livewire actions** (bukan REST API endpoint), konsisten dengan pola seluruh portal di project ini
>
> Lihat juga: "Referensi Struktur Database Aktual" di `spesifikasi-modul-guru-wali.md`.

---

## 1. Ringkasan Eksekutif & Tujuan

Fitur **Konsultasi Dua Arah** dirancang untuk melengkapi sistem pendampingan Guru Wali agar tidak hanya bersifat satu arah (*top-down* atau pemanggilan oleh guru), melainkan juga memfasilitasi kebutuhan dan inisiatif murid (*student-initiated consultation*).

### Tujuan Utama Fitur
1. **Aksesibilitas & Kedekatan Personal**: Memberikan saluran komunikasi yang aman dan mudah diakses bagi murid untuk menyampaikan kendala akademik, minat/bakat, maupun sosial-psikologis.
2. **Deteksi Dini (Early Warning System)**: Membantu Guru Wali mengidentifikasi permasalahan siswa secara lebih awal sebelum berdampak pada hasil belajar atau kedisiplinan.
3. **Efisiensi Administrasi (Auto-Convert to Journal)**: Hasil dan sesi konsultasi yang diinisiasi siswa dapat langsung dikonversi menjadi catatan resmi **Jurnal Guru Wali** hanya dengan satu klik (*One-Click Auto-Fill*).
4. **Kerahasiaan Tingkat Tinggi (Strict Privacy)**: Seluruh interaksi konsultasi bersifat rahasia dan privat antara murid pengaju dan Guru Wali pengampu.

---

## 2. Arsitektur Data & Skema Basis Data (Database Schema)

Untuk mendukung alur dua arah ini, ditambahkan tabel `konsultasi_guru_wali` yang terhubung dengan tabel `jurnal_guru_wali`.

### Tabel `konsultasi_guru_wali`

```sql
CREATE TABLE konsultasi_guru_wali (
    id          CHAR(36) NOT NULL PRIMARY KEY,         -- UUID, pakai HasUuids
    student_id  CHAR(36) NOT NULL,                    -- FK ke tabel students.id (Pengaju)
    teacher_id  CHAR(36) NOT NULL,                    -- FK ke tabel teachers.id (Guru Wali)
    kelompok_id CHAR(36) NOT NULL,                    -- FK ke kelompok_guru_wali.id

    kategori_pendampingan ENUM(
        'Akademik',
        'Karakter & Kedisiplinan',
        'Minat & Bakat / Ekskul',
        'Sosial & Psikologis'
    ) NOT NULL,

    topik_konsultasi    VARCHAR(255) NOT NULL,         -- Ringkasan topik dari siswa
    detail_permasalahan TEXT         NOT NULL,         -- Penjelasan detail dari siswa

    mode_konsultasi ENUM(
        'Tatap Muka',
        'Pesan Portal'
    ) NOT NULL DEFAULT 'Tatap Muka',

    usulan_tanggal_waktu DATETIME     NULL,            -- Usulan waktu dari siswa
    jadwal_pasti         DATETIME     NULL,            -- Jadwal yang disetujui Guru Wali

    status_pengajuan ENUM(
        'Menunggu Konfirmasi',
        'Dijadwalkan',
        'Selesai',
        'Ditolak',
        'Dikonversi ke Jurnal'
    ) NOT NULL DEFAULT 'Menunggu Konfirmasi',

    tanggapan_guru    TEXT         NULL,               -- Pesan/arahan balasan dari Guru Wali
    alasan_penolakan  VARCHAR(255) NULL,               -- Opsional jika pengajuan ditolak

    jurnal_id CHAR(36) NULL,                          -- FK ke jurnal_guru_wali (jika sudah dikonversi)

    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    FOREIGN KEY (student_id)  REFERENCES students(id)            ON DELETE RESTRICT,
    FOREIGN KEY (teacher_id)  REFERENCES teachers(id)            ON DELETE RESTRICT,
    FOREIGN KEY (kelompok_id) REFERENCES kelompok_guru_wali(id)  ON DELETE CASCADE,
    FOREIGN KEY (jurnal_id)   REFERENCES jurnal_guru_wali(id)    ON DELETE SET NULL,

    INDEX idx_student_status (student_id, status_pengajuan),
    INDEX idx_teacher_status (teacher_id, status_pengajuan)
);
```

> **Tidak pakai SoftDeletes** — konsultasi yang ditolak/selesai tetap sebagai arsip lewat kolom `status_pengajuan`, tidak perlu dihapus.

### Model Laravel

```php
// app/Models/KonsultasiGuruWali.php
class KonsultasiGuruWali extends Model
{
    use HasUuids;

    protected $table = 'konsultasi_guru_wali';

    // Relasi ke siswa pengaju
    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'student_id');   // App\Models\Siswa
    }

    // Relasi ke Guru Wali pengampu
    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class, 'teacher_id');    // App\Models\Guru
    }

    // Relasi ke kelompok
    public function kelompok(): BelongsTo
    {
        return $this->belongsTo(KelompokGuruWali::class, 'kelompok_id');
    }

    // Relasi ke jurnal hasil konversi
    public function jurnal(): BelongsTo
    {
        return $this->belongsTo(JurnalGuruWali::class, 'jurnal_id');
    }
}
```

---

## 3. Matriks Hak Akses & Isolasi Kerahasiaan (Privacy Rules)

| Peran (Role) | Hak Akses pada Data Konsultasi | Keterangan |
| :--- | :--- | :--- |
| **Siswa Pengaju** | **Owner (Create & Read Own Only)** | Hanya dapat melihat pengajuan konsultasi miliknya sendiri. Tidak bisa melihat pengajuan siswa lain. |
| **Guru Wali Pengampu** | **Full Access (Read, Update, Convert)** | Hanya dapat melihat & merespons pengajuan dari siswa yang terdaftar di kelompok dampingannya. |
| **Guru Wali Lain** | **NO ACCESS (403 Forbidden)** | Terisolasi sepenuhnya dari kelompok lain. |
| **Wali Kelas** | **NO ACCESS (403 Forbidden)** | Kerahasiaan konsultasi terisolasi dari Rombel Akademik. |
| **Guru Bimbingan Konseling (BK)** | **NO ACCESS (403 Forbidden)** | Tidak dapat mengakses percakapan/pengajuan ini secara langsung kecuali dirujuk oleh Guru Wali. |
| **Kepala Sekolah / Admin** | **Read-Only (Statistik Agregat)** | Hanya melihat angka statistik (misal: "Total 15 Pengajuan Bulan Ini") tanpa melihat detail isi pesan. |

---

## 4. Spesifikasi UI/UX & Alur Kerja (Workflow)

### A. Alur di Portal Siswa (`/portal-siswa`) — Menu: "Konsultasi Guru Wali"

**Route baru** (ditambahkan di `routes/web.php` dalam prefix `portal-siswa` middleware `auth.siswa`):
```
/portal-siswa/konsultasi-guru-wali          → Livewire: PortalSiswa\KonsultasiList
/portal-siswa/konsultasi-guru-wali/form     → Livewire: PortalSiswa\KonsultasiForm
```

**Alur Form Pengajuan Konsultasi Baru**:
1. Sistem otomatis menampilkan **Nama Guru Wali** yang mengampu siswa tersebut (read-only, dari `kelompok_guru_wali` → `teachers.name`).
2. **Dropdown Kategori Pendampingan**: *Akademik*, *Karakter & Kedisiplinan*, *Minat & Bakat / Ekskul*, *Sosial & Psikologis*.
3. **Dropdown Mode Konsultasi**: `Tatap Muka di Sekolah` ATAU `Pesan Singkat Portal`.
4. **Input Topik & Detail**: Field teks singkat dan deskripsi kendala yang ingin didiskusikan.
5. **Date-Time Picker**: Usulan tanggal & jam pertemuan (opsional).

**Dashboard Status Pengajuan Siswa** (`KonsultasiList`):
- 🟡 **Menunggu Konfirmasi** — Pengajuan telah terkirim ke Guru Wali.
- 🔵 **Dijadwalkan** — Guru Wali menyetujui usulan waktu / menentukan jadwal baru.
- 🟢 **Selesai / Dikonversi** — Sesi konsultasi telah terlaksana.
- 💬 **Tanggapan Guru** — Siswa dapat membaca `tanggapan_guru` dari Guru Wali.

> **Isolasi**: Query di Livewire selalu difilter dengan `student_id = auth()->user()->student->id` untuk mencegah akses silang antar siswa.

---

### B. Alur di Portal Guru (`/portal-guru`) — Menu: "Inbox Konsultasi Siswa"

**Route baru** (ditambahkan di `routes/web.php` dalam prefix `portal-guru` middleware `auth.wali`):
```
/portal-guru/konsultasi-siswa           → Livewire: PortalGuru\KonsultasiInbox
/portal-guru/konsultasi-siswa/{id}      → Livewire: PortalGuru\KonsultasiDetail
```

**Inbox & Notifikasi** (`KonsultasiInbox`):
- Badge notifikasi menampilkan jumlah pengajuan baru yang `status_pengajuan = 'Menunggu Konfirmasi'`.
- Query selalu difilter: `teacher_id = auth()->user()->teacher->id`.

**Pilihan Aksi Guru Wali** (di `KonsultasiDetail` via Livewire actions):

| Aksi | Perubahan DB |
|---|---|
| **Terima & Jadwalkan** | `status_pengajuan = 'Dijadwalkan'`, isi `jadwal_pasti`, isi `tanggapan_guru` |
| **Balas Pesan** | Isi `tanggapan_guru`, `status_pengajuan = 'Dijadwalkan'` atau tetap |
| **Tolak** | `status_pengajuan = 'Ditolak'`, isi `alasan_penolakan` |
| **Konversi ke Jurnal** | Insert `jurnal_guru_wali`, set `status_pengajuan = 'Dikonversi ke Jurnal'`, set `jurnal_id` |

**Fitur Khusus: "Konversi ke Jurnal Guru Wali"**:
- Tombol **[+ Buat Jurnal dari Konsultasi Ini]** muncul pada pengajuan berstatus `Dijadwalkan` atau `Selesai`.
- Ketika diklik, sistem membuka form Jurnal dengan **auto-populate**:

| Field Jurnal | Sumber Data |
|---|---|
| `student_id` | `konsultasi_guru_wali.student_id` |
| `kategori_pendampingan` | `konsultasi_guru_wali.kategori_pendampingan` |
| `uraian_pembahasan` | `konsultasi_guru_wali.topik_konsultasi` + `detail_permasalahan` |
| `tanggal_waktu` | `konsultasi_guru_wali.jadwal_pasti` ?? `now()` |

- Guru Wali tinggal melengkapi `status_sesi`, `rujukan_kolaborasi`, dan `rencana_tindak_lanjut`, lalu simpan.

---

## 5. Spesifikasi Backend (Livewire Actions)

> **Catatan Implementasi**: Project ini menggunakan **Livewire** untuk semua interaksi portal (bukan REST API terpisah). Semua aksi di bawah diimplementasikan sebagai Livewire component methods.

### A. Livewire: `PortalSiswa\KonsultasiForm` — Simpan Pengajuan Baru

```php
public function simpan(): void
{
    $student = auth()->user()->student; // App\Models\Siswa via User->student()

    // Pastikan siswa punya Guru Wali aktif
    $keanggotaan = KelompokGuruWaliSiswa::where('student_id', $student->id)
        ->where('status_aktif', true)
        ->with('kelompok')
        ->firstOrFail();

    KonsultasiGuruWali::create([
        'student_id'           => $student->id,
        'teacher_id'           => $keanggotaan->kelompok->teacher_id,
        'kelompok_id'          => $keanggotaan->kelompok_id,
        'kategori_pendampingan' => $this->kategori,
        'topik_konsultasi'     => $this->topik,
        'detail_permasalahan'  => $this->detail,
        'mode_konsultasi'      => $this->mode,
        'usulan_tanggal_waktu' => $this->usulan_waktu,
        'status_pengajuan'     => 'Menunggu Konfirmasi',
    ]);
}
```

### B. Livewire: `PortalGuru\KonsultasiDetail` — Konversi ke Jurnal

```php
public function konversiKeJurnal(array $data): void
{
    $teacher = auth()->user()->teacher; // App\Models\Guru via User->teacher()
    $konsultasi = KonsultasiGuruWali::where('id', $this->konsultasiId)
        ->where('teacher_id', $teacher->id) // Pastikan milik guru yang login
        ->firstOrFail();

    // Insert jurnal baru
    $jurnal = JurnalGuruWali::create([
        'teacher_id'            => $teacher->id,
        'kelompok_id'           => $konsultasi->kelompok_id,
        'student_id'            => $konsultasi->student_id,
        'tanggal_waktu'         => $konsultasi->jadwal_pasti ?? now(),
        'kategori_pendampingan' => $konsultasi->kategori_pendampingan,
        'uraian_pembahasan'     => $konsultasi->topik_konsultasi . "\n\n" . $konsultasi->detail_permasalahan,
        'jenis_pendampingan'    => $data['jenis_pendampingan'],
        'status_sesi'           => $data['status_sesi'],
        'rujukan_kolaborasi'    => $data['rujukan_kolaborasi'],
        'rencana_tindak_lanjut' => $data['rencana_tindak_lanjut'],
    ]);

    // Update status konsultasi
    $konsultasi->update([
        'status_pengajuan' => 'Dikonversi ke Jurnal',
        'jurnal_id'        => $jurnal->id,
    ]);
}
```

---

## 6. Kriteria Penerimaan & Checklist Pengujian

### Validasi & Keamanan
- [ ] Siswa hanya dapat mengajukan konsultasi kepada Guru Wali yang mengampu kelompoknya secara aktif (`status_aktif = true`).
- [ ] Query di semua Livewire komponen difilter berdasarkan `student_id` atau `teacher_id` yang login — tidak ada akses silang.
- [ ] Siswa B tidak dapat membaca pengajuan Konsultasi milik Siswa A meskipun berada dalam satu kelompok dampingan yang sama.
- [ ] Guru Wali lain yang mencoba akses `KonsultasiDetail` bukan miliknya mendapat `403 Forbidden`.

### Fungsional
- [ ] Pengajuan baru dari siswa memunculkan badge notifikasi di portal Guru Wali.
- [ ] Aksi "Terima & Jadwalkan" mengubah `status_pengajuan` menjadi `'Dijadwalkan'` dan menyimpan `jadwal_pasti`.
- [ ] Tombol "Konversi ke Jurnal" **hanya muncul** pada status `Dijadwalkan` atau `Selesai`.
- [ ] Setelah konversi: `jurnal_guru_wali` memiliki record baru, `konsultasi_guru_wali.jurnal_id` terisi, dan `status_pengajuan = 'Dikonversi ke Jurnal'`.
- [ ] Konversi ke Jurnal mengubah indikator status bulanan siswa menjadi 🟢 (Sudah Didampingi) di halaman Pemantauan Bulanan Guru Wali.

---

## 7. Integrasi dengan Modul Lain

| Modul | Hubungan |
|---|---|
| **Kelompok Guru Wali** | `konsultasi_guru_wali.kelompok_id` → `kelompok_guru_wali.id` |
| **Jurnal Guru Wali** | `konsultasi_guru_wali.jurnal_id` → `jurnal_guru_wali.id` (setelah konversi) |
| **Pemantauan Bulanan** | Jurnal hasil konversi ikut dihitung sebagai sesi pendampingan bulan berjalan |
| **Portal Siswa** | Route baru `/portal-siswa/konsultasi-guru-wali` (middleware `auth.siswa`) |
| **Portal Guru** | Route baru `/portal-guru/konsultasi-siswa` (middleware `auth.wali`) |
