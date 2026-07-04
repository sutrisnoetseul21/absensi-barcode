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
        'start_year',
        'end_year',
        'status',
    ];

    protected $casts = [
        'start_year' => 'integer',
        'end_year'   => 'integer',
    ];

    // Auto-generate field 'name' dari start_year/end_year sebelum disimpan
    protected static function boot(): void
    {
        parent::boot();

        static::saving(function (self $model) {
            if ($model->start_year && $model->end_year) {
                $model->name = "{$model->start_year}/{$model->end_year}";
            }
        });
    }

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
        return $this->hasMany(Presensi::class, 'academic_year_id');
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

    // Scope: tampilkan urut dari tahun paling awal
    public function scopeOrderedByYear($query)
    {
        return $query->orderBy('start_year', 'asc');
    }

    // Scope untuk tahun ajaran aktif
    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }
}
