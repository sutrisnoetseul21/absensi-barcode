# 04. Desain Perbaikan Database Absensi (Modul Nilai & Ujian)

Dokumen ini merinci rancangan perbaikan dan penambahan skema database pada `projek-absensi-barcode` (Laravel + MySQL) agar mampu menampung data **Tahun Ajaran, Semester, Mata Pelajaran, Master Ujian, dan Nilai Hasil Ujian Siswa (baik dari CBT maupun manual)**.

---

## 1. Diagram Relasi Entitas (ERD Usulan)

```
+------------------+
|  academic_years  |
+--------+---------+
         | 1
         |
         | M
+--------v---------+          +------------------+          +-------------------+
|  ujian_akademiks | M      1 |  mata_pelajarans |          |     teachers      |
|  (Master Ujian)  +--------->+ (Mata Pelajaran) |          |  (Guru Pengampu)  |
+---+----+---------+          +------------------+          +---------+---------+
    |    |                                                            |
    |    | M                                                        1 |
    |    +-------------------------+                                  |
    |                              |                                  |
    | 1                            | M                                | M
    |                              v                                  v
    |                     +--------+----------------+        +--------+---------+
    |                     | ujian_akademik_classes  |        |    pengajarans   |
    |                     |   (Target Kelas Ujian)  |        +------------------+
    |                     +--------+----------------+
    |                              | M
    |                              |
    | M                            v 1
+---v--------------+      +--------+---------+
|   nilai_ujians   | M  1 |     classes      |
|  (Nilai Siswa)   +----->+     (Kelas)      |
+--------+---------+      +------------------+
         | M
         |
         | 1
+--------v---------+
|     students     |
|     (Siswa)      |
+------------------+
```

---

## 2. Blueprint Migrasi Database

### A. Update Tabel Eksisting 1: `school_settings`
Menambahkan pengaturan **Semester Aktif** dan konfigurasi koneksi CBT ke database sekolah.

```php
// database/migrations/xxxx_xx_xx_add_semester_and_cbt_to_school_settings_table.php
Schema::table('school_settings', function (Blueprint $table) {
    // Semester Aktif ('ganjil' atau 'genap')
    $table->enum('active_semester', ['ganjil', 'genap'])->default('ganjil')->after('academic_year_id_active');
    
    // Konfigurasi ZenCBT
    $table->enum('cbt_driver', ['api', 'database'])->default('api')->after('active_semester');
    $table->string('cbt_api_url')->nullable()->after('cbt_driver');
    $table->string('cbt_api_key')->nullable()->after('cbt_api_url');
});
```

---

### B. Tabel Baru Pendamping: `student_cbt_profiles` (Pola *Profile Pattern* - Tabel `students` 0% Disentuh)
Mengikuti pola arsitektur bawaan ERP Anda (`StudentPresensiProfile`), kita **TIDAK mengubah struktur tabel `students` sama sekali**. Semua data pendukung CBT (password kartu & sesi) diisolasi ke tabel pendamping:

```php
// database/migrations/xxxx_xx_xx_create_student_cbt_profiles_table.php
Schema::create('student_cbt_profiles', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->foreignUuid('student_id')->constrained('students')->cascadeOnDelete();
    
    // Kredensial & Pengaturan CBT
    $table->string('cbt_password', 50)->nullable(); // Password terbaca untuk cetak kartu & login CBT
    $table->string('cbt_sesi', 20)->default('Sesi 1'); // Sesi 1, Sesi 2, dst
    $table->enum('cbt_status', ['active', 'blocked'])->default('active');
    
    $table->timestamps();

    // 1 siswa hanya memiliki 1 profil CBT
    $table->unique('student_id');
});
```

> 🛡️ **Jaminan Keamanan ERP**: 
> * Tabel utama `students` tetap murni dan tidak ada perubahan sama sekali.
> * Modul Presensi Barcode, SPIKAP, Guru Wali, BK, dan SLiMS Perpustakaan tetap berjalan 100% normal tanpa risiko *side-effect*.

---

### C. Update Tabel Eksisting 3: `mata_pelajarans`
Menambahkan kategori mapel (Umum/Muatan Lokal) dan batas KKM default.

```php
// database/migrations/xxxx_xx_xx_add_kategori_and_kkm_to_mata_pelajarans_table.php
Schema::table('mata_pelajarans', function (Blueprint $table) {
    $table->string('kategori', 50)->default('Umum')->after('nama_mapel'); // 'Umum', 'Muatan Lokal', 'Pilihan'
    $table->decimal('kkm_default', 5, 2)->default(75.00)->after('kategori');
});
```

---

### D. Tabel Baru: `jenis_ujians` (Master Kategori Asesmen Dinamis)
Menyediakan pengelolaan master kategori ujian (UH, ASTS, ASAS, ASAJ, Try Out) secara dinamis melalui UI Filament tanpa terikat batasan enum statis.

```php
// database/migrations/2026_09_16_003952_create_jenis_ujians_table.php
Schema::create('jenis_ujians', function (Blueprint $table) {
    $table->id();
    $table->string('kode', 50)->unique(); // Misal: ASAJ, UH, PAS, ASTS, ASAS
    $table->string('nama', 100);          // Misal: Asesmen Sumatif Akhir Jenjang
    $table->softDeletes();
    $table->timestamps();
});
```

---

### E. Tabel Baru: `ujian_akademiks` (Master Ujian / Agenda Asesmen)
Mewadahi semua kegiatan asesmen (Ulangan Harian, UTS/STS, UAS/SAS, Try Out, Ujian Sekolah). Terhubung dengan master `jenis_ujians` dan jadwal ZenCBT.

```php
// database/migrations/xxxx_xx_xx_create_ujian_akademiks_table.php
// database/migrations/2026_09_16_004017_alter_jenis_ujian_on_ujian_akademiks_table.php
Schema::create('ujian_akademiks', function (Blueprint $table) {
    $table->uuid('id')->primary();
    
    // Relasi Utama
    $table->foreignUuid('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
    $table->enum('semester', ['ganjil', 'genap'])->default('ganjil');
    $table->foreignId('mata_pelajaran_id')->nullable()->constrained('mata_pelajarans')->nullOnDelete();
    $table->foreignUuid('teacher_id')->nullable()->constrained('teachers')->nullOnDelete();
    
    // Tautan ZenCBT
    $table->integer('cbt_ujian_id')->nullable()->index(); // ID asli ujian di ZenCBT
    $table->string('cbt_event_nama', 100)->nullable();    // Kolom 'Event' di ZenCBT (misal: "ASTS Ganjil 2025/2026")
    
    // Informasi Ujian & Asesmen
    $table->string('nama_ujian'); // Contoh: "Ulangan Harian 1 - Bilangan Bulat" atau "ASTS Matematika 7"
    $table->foreignId('jenis_ujian_id')->nullable()->constrained('jenis_ujians')->nullOnDelete(); // Relasi dinamis ke jenis_ujians
    $table->decimal('kkm', 5, 2)->default(75.00); // Standar kelulusan KKM
    $table->integer('durasi_menit')->default(60);
    $table->integer('total_soal')->nullable()->default(0);
    
    // Waktu Pelaksanaan
    $table->dateTime('tanggal_mulai');
    $table->dateTime('tanggal_selesai');
    $table->enum('status', ['draft', 'aktif', 'selesai', 'arsip'])->default('draft');
    $table->text('keterangan')->nullable();
    
    $table->softDeletes();
    $table->timestamps();
});
```

---

### D. Tabel Baru: `ujian_akademik_classes` (Pivot Kelas yang Mengikuti Ujian)
Satu ujian (misal: Matematika Kelas 7) dapat ditugaskan untuk banyak kelas rombel (7A, 7B, 7C).

```php
// database/migrations/xxxx_xx_xx_create_ujian_akademik_classes_table.php
Schema::create('ujian_akademik_classes', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->foreignUuid('ujian_akademik_id')->constrained('ujian_akademiks')->cascadeOnDelete();
    $table->foreignUuid('class_id')->constrained('classes')->cascadeOnDelete();
    $table->timestamps();

    $table->unique(['ujian_akademik_id', 'class_id']);
});
```

---

### E. Tabel Baru: `nilai_ujians` (Transaksi Nilai Siswa)
Menyimpan nilai tiap siswa per ujian, baik yang ditarik dari ZenCBT maupun hasil input manual guru.

```php
// database/migrations/xxxx_xx_xx_create_nilai_ujians_table.php
Schema::create('nilai_ujians', function (Blueprint $table) {
    $table->uuid('id')->primary();
    
    // Relasi Kunci
    $table->foreignUuid('ujian_akademik_id')->constrained('ujian_akademiks')->cascadeOnDelete();
    $table->foreignUuid('student_id')->constrained('students')->cascadeOnDelete();
    $table->foreignUuid('class_id')->constrained('classes')->cascadeOnDelete();
    $table->foreignUuid('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
    $table->enum('semester', ['ganjil', 'genap'])->default('ganjil');

    // Nilai & Skor
    $table->decimal('nilai_akhir', 5, 2)->default(0.00); // Skala 0 - 100
    $table->double('score_irt')->nullable();             // Skor IRT (jika ada)
    $table->boolean('is_tuntas')->default(false);        // true jika nilai_akhir >= KKM
    
    // Detail Butir Soal (dari CBT)
    $table->integer('jumlah_benar')->nullable()->default(0);
    $table->integer('jumlah_salah')->nullable()->default(0);
    $table->integer('jumlah_kosong')->nullable()->default(0);
    
    // Status & Keamanan
    $table->enum('status_kehadiran', ['hadir', 'tidak_hadir', 'susulan'])->default('hadir');
    $table->integer('violation_count')->default(0);      // Rekap pelanggaran tab/layar CBT
    
    // Tracking Waktu
    $table->timestamp('started_at')->nullable();
    $table->timestamp('submitted_at')->nullable();
    $table->timestamp('synced_at')->nullable();         // Waktu data diimpor dari ZenCBT
    $table->text('catatan')->nullable();                // Catatan khusus guru
    
    $table->timestamps();

    // Pastikan satu siswa hanya punya 1 rekaman per ujian
    $table->unique(['ujian_akademik_id', 'student_id']);
});
```

---

## 3. Desain Model Eloquent

### Model `UjianAkademik.php`
```php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class UjianAkademik extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'academic_year_id',
        'semester',
        'mata_pelajaran_id',
        'teacher_id',
        'cbt_ujian_id',
        'nama_ujian',
        'jenis_ujian',
        'kkm',
        'durasi_menit',
        'total_soal',
        'tanggal_mulai',
        'tanggal_selesai',
        'status',
        'keterangan',
    ];

    public function tahunAjaran(): BelongsTo
    {
        return $this->belongsTo(TahunAjaran::class, 'academic_year_id');
    }

    public function mataPelajaran(): BelongsTo
    {
        return $this->belongsTo(MataPelajaran::class, 'mata_pelajaran_id');
    }

    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class, 'teacher_id');
    }

    public function classes(): BelongsToMany
    {
        return $this->belongsToMany(Kelas::class, 'ujian_akademik_classes', 'ujian_akademik_id', 'class_id');
    }

    public function nilaiUjians(): HasMany
    {
        return $this->hasMany(NilaiUjian::class, 'ujian_akademik_id');
    }
}
```

### Model `NilaiUjian.php`
```php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NilaiUjian extends Model
{
    use HasUuids;

    protected $fillable = [
        'ujian_akademik_id',
        'student_id',
        'class_id',
        'academic_year_id',
        'semester',
        'nilai_akhir',
        'score_irt',
        'is_tuntas',
        'jumlah_benar',
        'jumlah_salah',
        'jumlah_kosong',
        'status_kehadiran',
        'violation_count',
        'started_at',
        'submitted_at',
        'synced_at',
        'catatan',
    ];

    protected static function boot(): void
    {
        parent::boot();

        // Otomatis tentukan status ketuntasan berdasarkan KKM ujian
        static::saving(function (self $model) {
            if ($model->ujianAkademik) {
                $model->is_tuntas = ($model->nilai_akhir >= $model->ujianAkademik->kkm);
            }
        });
    }

    public function ujianAkademik(): BelongsTo
    {
        return $this->belongsTo(UjianAkademik::class, 'ujian_akademik_id');
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'student_id');
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'class_id');
    }

    public function tahunAjaran(): BelongsTo
    {
        return $this->belongsTo(TahunAjaran::class, 'academic_year_id');
    }
}
```

### Model `StudentCbtProfile.php` (Model Pendamping)
```php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentCbtProfile extends Model
{
    use HasUuids;

    protected $table = 'student_cbt_profiles';

    protected $fillable = [
        'student_id',
        'cbt_password',
        'cbt_sesi',
        'cbt_status',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'student_id');
    }
}
```

### Relasi Tambahan di `Siswa.php` (Sama seperti `presensiProfile`):
```php
public function cbtProfile(): \Illuminate\Database\Eloquent\Relations\HasOne
{
    return $this->hasOne(StudentCbtProfile::class, 'student_id');
}
```

---

## 4. Keuntungan Desain Ini Bagi Sistem Rapor & Akademik

1. **Siap Menghitung Nilai Akhir Rapor**:
   Nilai Harian, Nilai STS, dan Nilai SAS dapat di-filter dengan cepat:
   ```php
   $nilaiHarian = NilaiUjian::where('student_id', $siswaId)
       ->where('academic_year_id', $taId)
       ->where('semester', 'ganjil')
       ->whereHas('ujianAkademik', fn($q) => $q->where('jenis_ujian', 'harian'))
       ->avg('nilai_akhir');
   ```

2. **Dukungan Cetak Kartu Ujian Lengkap**:
   Di panel Filament, admin dapat langsung mencetak kartu peserta ujian berisi foto siswa, NISN, QR Code, dan password CBT (`cbt_password`).

3. **Kompatibilitas Sinkronisasi ZenCBT**:
   Kolom `cbt_ujian_id` pada `ujian_akademiks` membuat proses penarikan nilai dari ZenCBT berjalan 100% otomatis tanpa tertukar antar sesi ujian.
