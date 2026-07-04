<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Presensi extends Model
{
    use HasUuids;

    protected $table = 'attendances';

    protected $fillable = [
        'student_id',
        'enrollment_id',
        'class_id',
        'academic_year_id',
        'date',
        'scan_time',
        'status',
        'late_minutes',
        'note',
        'is_manual_input',
        'manual_input_by_id',
        'manual_input_by_type',
        'scanned_by',
    ];

    protected $casts = [
        'date'            => 'date',
        'late_minutes'    => 'integer',
        'is_manual_input' => 'boolean',
    ];

    // Siswa yang diabsen
    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'student_id');
    }

    // Enrollment siswa (kelas + tahun ajaran)
    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(EnrollmentSiswa::class, 'enrollment_id');
    }

    // Kelas (denormalized — langsung tanpa join)
    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'class_id');
    }

    // Tahun ajaran (denormalized — langsung tanpa join)
    public function tahunAjaran(): BelongsTo
    {
        return $this->belongsTo(TahunAjaran::class, 'academic_year_id');
    }

    // Admin yang scan (guard Filament = tabel users)
    public function petugas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'scanned_by');
    }

    // Polymorphic: siapa yang input manual (Guru atau User/Admin)
    public function inputManualOleh(): MorphTo
    {
        return $this->morphTo('manual_input_by');
    }
}
