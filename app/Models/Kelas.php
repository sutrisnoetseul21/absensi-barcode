<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kelas extends Model
{
    use HasUuids, SoftDeletes;

    // Template nama kelas permanen (7A, 7B, ..., 9C)
    // Tidak berubah antar tahun ajaran
    protected $table = 'classes';

    protected $fillable = [
        'name',
        'grade_level',
    ];

    protected $casts = [
        'grade_level' => 'integer',
    ];

    // Pivot ke tahun ajaran (assign wali kelas)
    public function kelasAjarans(): HasMany
    {
        return $this->hasMany(KelasAjaran::class, 'class_id');
    }

    /**
     * Dapatkan Guru Wali Kelas untuk tahun ajaran tertentu (atau aktif).
     */
    public function waliKelas(?string $academicYearId = null): ?Guru
    {
        $academicYearId = $academicYearId ?? TahunAjaran::where('status', 'aktif')->value('id');
        if (! $academicYearId) {
            return null;
        }

        return $this->kelasAjarans()
            ->where('academic_year_id', $academicYearId)
            ->with('guru')
            ->first()
            ?->guru;
    }

    // Akses pantau tambahan (Guru BK dll)
    public function guruKelasPantau(): HasMany
    {
        return $this->hasMany(TeacherClassAccess::class, 'class_id');
    }

    // Enrollment siswa di kelas ini
    public function enrollments(): HasMany
    {
        return $this->hasMany(EnrollmentSiswa::class, 'class_id');
    }

    // Ujian akademik yang ditugaskan ke kelas ini
    public function ujianAkademiks(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(UjianAkademik::class, 'ujian_akademik_classes', 'class_id', 'ujian_akademik_id')
            ->using(UjianAkademikClass::class)
            ->withTimestamps();
    }

    // Absensi di kelas ini (denormalized)
    public function absensis(): HasMany
    {
        return $this->hasMany(Presensi::class, 'class_id');
    }

    // Hari libur khusus kelas ini
    public function hariLiburs(): HasMany
    {
        return $this->hasMany(HariLibur::class, 'class_id');
    }

    // Pengajaran yang terhubung ke kelas ini lewat tahun ajaran
    public function pengajarans()
    {
        return $this->hasManyThrough(
            Pengajaran::class,
            KelasAjaran::class,
            'class_id', 
            'class_academic_year_id', 
            'id', 
            'id' 
        );
    }
}
