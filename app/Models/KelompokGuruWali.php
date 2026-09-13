<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class KelompokGuruWali extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'kelompok_guru_wali';

    protected $fillable = [
        'nama_kelompok',
        'teacher_id',
        'status_aktif',
    ];

    protected $casts = [
        'status_aktif' => 'boolean',
    ];

    /**
     * Guru Wali yang bertanggung jawab atas kelompok ini.
     * FK: teacher_id → teachers.id
     */
    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class, 'teacher_id');
    }

    /**
     * Alias ke guru() untuk kompatibilitas konvensi nama kolom teacher_id.
     */
    public function teacher(): BelongsTo
    {
        return $this->guru();
    }

    /**
     * Semua mapping siswa dalam kelompok ini (termasuk nonaktif).
     * FK: kelompok_id → kelompok_guru_wali.id
     */
    public function anggota(): HasMany
    {
        return $this->hasMany(KelompokGuruWaliSiswa::class, 'kelompok_id');
    }

    /**
     * Shortcut ke Model Siswa melalui tabel pivot kelompok_guru_wali_siswa.
     *
     * hasManyThrough(
     *   TargetModel,
     *   IntermediateModel,
     *   firstKey  = FK di intermediate yang mengarah ke kelompok ini,
     *   secondKey = FK di intermediate yang mengarah ke target (students),
     *   localKey  = PK kelompok_guru_wali,
     *   secondLocalKey = PK students
     * )
     */
    public function siswa()
    {
        return $this->hasManyThrough(
            Siswa::class,               // Model tujuan
            KelompokGuruWaliSiswa::class, // Model perantara
            'kelompok_id',              // FK di kelompok_guru_wali_siswa → kelompok ini
            'id',                       // PK di students
            'id',                       // PK di kelompok_guru_wali
            'student_id'                // FK di kelompok_guru_wali_siswa → students
        );
    }

    /**
     * Hanya anggota yang berstatus aktif.
     */
    public function anggotaAktif(): HasMany
    {
        return $this->hasMany(KelompokGuruWaliSiswa::class, 'kelompok_id')
                    ->where('status_aktif', true);
    }

    /**
     * Anggota yang sudah diarsipkan (lulus, mutasi, dipindahkan).
     */
    public function anggotaArsip(): HasMany
    {
        return $this->hasMany(KelompokGuruWaliSiswa::class, 'kelompok_id')
                    ->where('status_aktif', false);
    }

    /**
     * Seluruh catatan jurnal pendampingan dalam kelompok ini.
     */
    public function jurnals(): HasMany
    {
        return $this->hasMany(JurnalGuruWali::class, 'kelompok_id');
    }

    /**
     * Seluruh permohonan konsultasi dari murid di kelompok ini.
     */
    public function konsultasis(): HasMany
    {
        return $this->hasMany(KonsultasiGuruWali::class, 'kelompok_id');
    }

    /**
     * Memastikan seluruh guru yang terdaftar memiliki satu baris kelompok_guru_wali.
     */
    public static function syncAllTeachers(): int
    {
        $teachers = Guru::all();
        $created = 0;

        foreach ($teachers as $teacher) {
            $kelompok = static::withTrashed()->where('teacher_id', $teacher->id)->first();
            if (! $kelompok) {
                static::create([
                    'teacher_id'    => $teacher->id,
                    'nama_kelompok' => 'Kelompok ' . $teacher->name,
                    'status_aktif'  => true,
                ]);
                $created++;
            } elseif ($kelompok->trashed()) {
                $kelompok->restore();
            }
        }

        return $created;
    }
}
