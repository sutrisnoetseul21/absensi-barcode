# Walkthrough Refactoring — Tahap 1 & Tahap 2

**Proyek:** Sistem ERP Sekolah — Absensi Barcode  
**Tujuan Refactoring:** Mengekstrak entitas `Siswa` menjadi "Modul Inti (Master Data)" murni sebagai fondasi untuk modul masa depan (LMS, Pembayaran, Dapodik).  
**Status:** Tahap 1 ✅ SELESAI | Tahap 2 ✅ SELESAI | Tahap 3 🔜 | Tahap 4 🔜

---

## Konteks & Motivasi

Sebelum refactoring, kode `Siswa` memiliki dua masalah utama:

1. **Tight coupling ke Presensi** — tabel `students` menyimpan `barcode_code` dan `barcode_active` yang seharusnya menjadi urusan Modul Presensi.
2. **Logika bisnis di UI layer** — aksi mutasi, kelulusan, dan reaktivasi siswa ditulis langsung sebagai *closure* di dalam Filament Resource, sehingga logika yang sama ter-duplikat di beberapa tempat.

Arsitektur yang dituju adalah **Modular Monolith** dengan 3 layer:

```
Layer 1 — Master Data  : students, academic_years, classes
Layer 2 — Operasional  : student_enrollments, attendances, student_presensi_profiles
Layer 3 — UI/Filament  : Resources, Pages, Actions
```

---

## Tahap 1 — Ekstraksi Data & Pemindahan Logika

**Tag Git:** `tahap-1-refactoring`  
**Commit:** `refactor(tahap-1): ekstraksi Modul Inti Siswa sebagai Master Data murni`

### Yang Dikerjakan

#### 1. Tabel Baru: `student_presensi_profiles`

File: `database/migrations/2026_07_16_165400_create_student_presensi_profiles_table.php`

Kolom `barcode_code` dan `barcode_active` dipindah dari tabel `students` ke tabel baru `student_presensi_profiles`. Data lama disalin secara otomatis via raw SQL di migration, tanpa langsung men-drop kolom lama (aman untuk production).

```sql
-- Di dalam migration up()
INSERT INTO student_presensi_profiles (id, student_id, barcode_code, barcode_active, ...)
SELECT UUID(), id, barcode_code, barcode_active, ...
FROM students WHERE barcode_code IS NOT NULL
```

> Kolom lama di `students` belum di-drop — akan dilakukan di Tahap 4 setelah terbukti stabil di production.

#### 2. Model Baru: `StudentPresensiProfile`

File: `app/Models/StudentPresensiProfile.php`

Model bersih dengan relasi `BelongsTo` ke `Siswa`. Di sisi `Siswa`, ditambahkan relasi `presensiProfile()` → `HasOne(StudentPresensiProfile::class)`.

#### 3. Action Classes — Single Source of Truth

Direktori: `app/Actions/Student/`

| File | Fungsi |
|---|---|
| `MutateStudentAction` | Tandai siswa → `mutasi` |
| `GraduateStudentAction` | Tandai siswa → `lulus` |
| `ReactivateStudentAction` | Aktifkan kembali dari mutasi / batalkan kelulusan |

#### 4. Hubungkan UI ke Action Classes

File yang dimodifikasi: `SiswaTable.php`, `SiswaMutasiResource.php`, `SiswaLulusResource.php`

Semua *inline closure* di Filament yang menduplikasi logika diganti dengan satu baris:
```php
// Sebelum (❌ inline, duplikat di 3 tempat):
$record->update(['status' => 'mutasi']);
$record->enrollments()->where('status', 'aktif')->update(['status' => 'pindah']);

// Sesudah (✅ satu panggilan ke Action Class):
(new MutateStudentAction)->execute($record);
```

**Bug yang diperbaiki:** `SiswaLulusResource` sebelumnya tidak me-rollback status enrollment saat kelulusan dibatalkan. Kini diperbaiki via `ReactivateStudentAction::cancelGraduation()`.

---

## Tahap 2 — Event-Driven Architecture (Pub/Sub)

**Tag Git:** `tahap-2-refactoring`  
**Commit:** `refactor(tahap-2): implementasi Event-Driven Architecture untuk Modul Siswa`

### Yang Dikerjakan

#### Masalah yang Diselesaikan

Setelah Tahap 1, Action Classes masih memanggil `$siswa->enrollments()->update(...)` secara langsung — artinya **Modul Siswa masih tahu tentang Modul Enrollment**. Tahap 2 memutus ketergantungan ini sepenuhnya.

#### Arsitektur Pub/Sub

```
[Action Class] → update status students → dispatch Event
                                               ↓
                             [Listener Enrollment] → update student_enrollments
                             [Listener Presensi]  → update student_presensi_profiles
```

#### Events yang Dibuat (`app/Events/Student/`)

| Event | Payload | Kapan Dipicu |
|---|---|---|
| `StudentMutated` | `$siswa` | Saat siswa ditandai mutasi |
| `StudentGraduated` | `$siswa`, `$activeYearId` | Saat siswa dinyatakan lulus |
| `StudentReactivated` | `$siswa` | Saat siswa aktif kembali dari mutasi |
| `StudentGraduationCancelled` | `$siswa`, `$activeYearId` | Saat kelulusan dibatalkan |

#### Listeners Enrollment (`app/Listeners/Enrollment/`)

| Listener | Mendengarkan | Efek |
|---|---|---|
| `HandleStudentMutated` | `StudentMutated` | enrollment `aktif` → `pindah` |
| `HandleStudentGraduated` | `StudentGraduated` | enrollment → `lulus` |
| `HandleStudentReactivated` | `StudentReactivated` | enrollment `pindah` → `aktif` |
| `HandleStudentGraduationCancelled` | `StudentGraduationCancelled` | enrollment `lulus` → `aktif` |

#### Listeners Presensi (`app/Listeners/Presensi/`) — PHP 8 Union Types

```php
// 1 Listener, menangani 2 Event dengan logika identik
class HandleStudentDeactivatedForPresensi
{
    public function handle(StudentMutated|StudentGraduated $event): void
    {
        $event->siswa->presensiProfile()?->update(['barcode_active' => false]);
    }
}
```

| Listener | Mendengarkan | Efek |
|---|---|---|
| `HandleStudentDeactivatedForPresensi` | `StudentMutated` \| `StudentGraduated` | `barcode_active = false` |
| `HandleStudentReactivatedForPresensi` | `StudentReactivated` \| `StudentGraduationCancelled` | `barcode_active = true` |

#### Registrasi (`AppServiceProvider`)

Semua 8 pasangan Event → Listener didaftarkan via `Event::listen()` di `AppServiceProvider::boot()`. Listener berjalan **synchronous** (tidak di-queue) untuk menjaga infrastruktur tetap sederhana.

#### DB::transaction untuk Integritas Data

Seluruh Action Classes dibungkus `DB::transaction`:

```php
public function execute(Siswa $siswa): void
{
    DB::transaction(function () use ($siswa) {
        $siswa->update(['status' => 'mutasi']); // update master
        event(new StudentMutated($siswa));        // trigger listeners
    });
}
```

**Jaminan:** Jika Listener manapun melempar exception, update status siswa di tabel `students` akan otomatis di-rollback — integritas data terjaga sepenuhnya.

---

## Kondisi Kode Setelah Tahap 2

### Yang SUDAH bersih ✅
- `Siswa` model → tidak ada lagi referensi ke `attendances`, `EnrollmentSiswa`, atau `StudentPresensiProfile` dalam logika bisnis
- Action Classes → hanya berisi `update status` + `event dispatch`
- Listener masing-masing modul bertanggung jawab atas domain-nya sendiri

### Yang BELUM selesai (akan dikerjakan Tahap 3 & 4)
- `ImportSiswaBaruAction` masih sekaligus mendaftarkan siswa ke kelas (coupling ke Enrollment)
- `SiswaTable` masih punya aksi "Cetak Kartu Presensi" (coupling ke Presensi)
- Kolom `barcode_code` dan `barcode_active` lama di tabel `students` belum di-drop

---

## Checklist Verifikasi Manual

Lakukan verifikasi berikut di local environment:

| Skenario | Cek `students` | Cek `student_enrollments` | Cek `student_presensi_profiles` |
|---|---|---|---|
| **Tandai Mutasi** | `status = mutasi` | `status = pindah` | `barcode_active = false` |
| **Aktifkan Kembali** | `status = aktif` | `status = aktif` | `barcode_active = true` |
| **Luluskan Siswa** | `status = lulus` | `status = lulus` | `barcode_active = false` |
| **Batalkan Kelulusan** | `status = aktif` | `status = aktif` | `barcode_active = true` |

> **Catatan:** Siswa yang belum memiliki `student_presensi_profiles` (belum punya barcode) tidak akan error — operator `?->` pada Listener sudah menghandle `null` dengan aman.

---

*Walkthrough ini dibuat pada 2026-07-17 setelah Tahap 2 selesai.*
