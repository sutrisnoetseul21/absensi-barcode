<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TahunAjaran extends Model
{
    use HasUuids;

    protected $table = 'academic_years';

    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    // Kelas yang aktif di tahun ajaran ini
    public function kelasAjarans(): HasMany
    {
        return $this->hasMany(KelasAjaran::class, 'academic_year_id');
    }

    // Enrollment siswa di tahun ajaran ini
    public function enrollments(): HasMany
    {
        return $this->hasMany(EnrollmentSiswa::class, 'academic_year_id');
    }

    // Absensi di tahun ajaran ini (denormalized)
    public function absensis(): HasMany
    {
        return $this->hasMany(Absensi::class, 'academic_year_id');
    }

    // Log kenaikan kelas dari tahun ajaran ini
    public function logKenaikansFrom(): HasMany
    {
        return $this->hasMany(LogKenaikan::class, 'academic_year_from_id');
    }

    // Log kenaikan kelas menuju tahun ajaran ini
    public function logKenaikansTo(): HasMany
    {
        return $this->hasMany(LogKenaikan::class, 'academic_year_to_id');
    }

    // Scope untuk tahun ajaran aktif
    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }
}
