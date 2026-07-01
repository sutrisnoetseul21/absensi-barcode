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

    // Enrollment siswa di kelas ini
    public function enrollments(): HasMany
    {
        return $this->hasMany(EnrollmentSiswa::class, 'class_id');
    }

    // Absensi di kelas ini (denormalized)
    public function absensis(): HasMany
    {
        return $this->hasMany(Absensi::class, 'class_id');
    }

    // Hari libur khusus kelas ini
    public function hariLiburs(): HasMany
    {
        return $this->hasMany(HariLibur::class, 'class_id');
    }
}
