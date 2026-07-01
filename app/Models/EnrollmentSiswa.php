<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EnrollmentSiswa extends Model
{
    use HasUuids;

    // Kunci riwayat kenaikan kelas: satu siswa = satu baris per tahun ajaran
    protected $table = 'student_enrollments';

    protected $fillable = [
        'student_id',
        'class_id',
        'academic_year_id',
        'status',
    ];

    // Siswa yang terdaftar
    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'student_id');
    }

    // Kelas yang diambil
    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'class_id');
    }

    // Tahun ajaran
    public function tahunAjaran(): BelongsTo
    {
        return $this->belongsTo(TahunAjaran::class, 'academic_year_id');
    }

    // Absensi yang terkait enrollment ini
    public function absensis(): HasMany
    {
        return $this->hasMany(Absensi::class, 'enrollment_id');
    }
}
