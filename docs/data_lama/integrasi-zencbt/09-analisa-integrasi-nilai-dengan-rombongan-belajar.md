# 09. Analisa Integrasi Nilai Ujian Akademik dengan Rombongan Belajar

## 1. Latar Belakang

Sistem absensi/ERP saat ini sudah memiliki modul **Rombongan Belajar** (`/admin/rombongan-belajar`) yang sangat mumpuni. Di dalamnya, sudah terdapat fitur alokasi/pemetaan **Pembelajaran** (tabel `pengajarans`), di mana Admin dapat menentukan Guru pengampu untuk setiap Mata Pelajaran pada kelas tertentu di Tahun Ajaran yang aktif.

Di sisi lain, proses sinkronisasi dari ZenCBT ditarik melalui modul **Sinkron Nilai CBT** (`UjianAkademikResource`). Saat ini, penarikan nilai (Action *Tarik Nilai*) berhasil mengambil data nilai ujian dan menyimpannya di tabel `nilai_ujians` yang terhubung ke `UjianAkademik` (Agenda Ujian), `Siswa`, dan `Kelas`.

**Tantangan Saat Ini:**
Bagaimana cara mengintegrasikan data nilai (`Ujian Akademik`) agar Guru Pengampu (berdasarkan `Rombongan Belajar`) dapat melihat, memantau, dan mengelola nilai siswa untuk kelas dan mapel yang mereka ajar?

## 2. Analisis Alur Data & Hak Akses (Role-Based Access)

Sistem sudah memiliki relasi berikut:
- **`UjianAkademik`**: Memiliki `mata_pelajaran_id` dan ditujukan untuk tingkat kelas tertentu.
- **`NilaiUjian`**: Memiliki `class_id` (Kelas siswa saat ujian).
- **`Pengajaran` (Rombel)**: Memetakan `guru_id` -> `mata_pelajaran_id` -> `class_id`.

Agar integrasi ini berjalan mulus dan *seamless* dari sudut pandang Guru, kita memerlukan penyesuaian *Query Scoping* di Filament.

### Opsi Integrasi (Rekomendasi Utama)

Kita akan melakukan modifikasi pada **`UjianAkademikResource`** dan **`NilaiUjianRelationManager`** agar beradaptasi dengan peran (Role) pengguna yang sedang login.

#### Skenario A: Login sebagai Super Admin
- **Ujian Akademik:** Dapat melihat **semua** agenda ujian.
- **Nilai Peserta:** Dapat melihat **semua** nilai siswa dari berbagai kelas untuk ujian tersebut.
- Dapat melakukan aksi sinkronisasi (Tarik Nilai).

#### Skenario B: Login sebagai Guru Pengampu
- **Ujian Akademik:** Hanya melihat agenda ujian di mana `mata_pelajaran_id`-nya sesuai dengan mapel yang ia ajar di setidaknya satu kelas (berdasarkan data di `Rombongan Belajar`).
- **Nilai Peserta (Relation Manager):** Ketika Guru membuka ujian tersebut, tabel rekapitulasi nilai **difilter secara otomatis**. Guru hanya akan melihat nilai siswa dari `class_id` yang memang ia ajar untuk mapel tersebut. (Siswa dari kelas lain disembunyikan).
- **Pembatasan Aksi:** Guru mungkin hanya bisa melihat (Read-only) dan mereset sesi, tetapi tidak diizinkan membuat Agenda Ujian baru atau menghapus agenda.

## 3. Rencana Modifikasi Kode (Action Plan)

Jika alur di atas disetujui, berikut adalah kode yang akan kita implementasikan:

### Tahap 1: Modifikasi `UjianAkademikResource.php`
Menambahkan method `getEloquentQuery()` untuk membatasi tampilan daftar ujian bagi Guru.

```php
public static function getEloquentQuery(): Builder
{
    $query = parent::getEloquentQuery();
    $user = auth()->user();

    // Jika bukan Super Admin, filter berdasarkan mapel yang diajar
    if (!$user->isSuperAdmin() && $user->guru) {
        $mapelIds = \App\Models\Pengajaran::where('teacher_id', $user->guru->id)
            ->pluck('mata_pelajaran_id')
            ->unique()
            ->toArray();

        $query->whereIn('mata_pelajaran_id', $mapelIds);
    }

    return $query;
}
```

### Tahap 2: Modifikasi `NilaiUjianRelationManager.php`
Membatasi baris data nilai siswa berdasarkan relasi *Rombongan Belajar* dari Guru yang bersangkutan.

```php
protected function getTableQuery(): Builder
{
    $query = parent::getTableQuery();
    $user = auth()->user();
    $ujian = $this->getOwnerRecord();

    if (!$user->isSuperAdmin() && $user->guru) {
        // Cari kelas apa saja yang diajar oleh guru ini untuk mapel pada ujian ini
        $classIds = \App\Models\Pengajaran::where('teacher_id', $user->guru->id)
            ->where('mata_pelajaran_id', $ujian->mata_pelajaran_id)
            ->join('class_academic_year', 'pengajarans.class_academic_year_id', '=', 'class_academic_year.id')
            ->pluck('class_academic_year.class_id')
            ->toArray();

        // Hanya tampilkan nilai siswa yang kelasnya termasuk di $classIds
        $query->whereIn('class_id', $classIds);
    }

    return $query;
}
```

### Tahap 3: Pembaruan Hak Akses Action (UX)
Menyembunyikan aksi-aksi administratif dari Guru biasa.
- `Tarik Nilai dari ZenCBT` (Header Action) -> Hanya untuk Super Admin (atau diizinkan untuk guru, sesuai kebijakan sekolah).
- `DeleteAction` -> Disembunyikan untuk Guru.

## 4. Kesimpulan

Dengan modifikasi ini, kita tidak perlu membuat menu baru yang redundant. Kita cukup membuat **satu pintu** di menu `Ujian Akademik`, namun konten yang ditampilkan ke Guru secara cerdas akan disaring berdasarkan pengaturan yang sudah dilakukan oleh Admin di modul `Rombongan Belajar`. 

Ini membuat UX lebih bersih dan menjamin privasi/keamanan data nilai antar guru.
