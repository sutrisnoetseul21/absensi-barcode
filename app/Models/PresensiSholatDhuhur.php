<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PresensiSholatDhuhur extends Model
{
    use HasUuids;

    protected $table = 'presensi_sholat_dhuhur';

    protected $fillable = [
        'academic_year_id',
        'class_id',
        'student_id',
        'enrollment_id',
        'date',
        'status',
        'keterangan',
        'input_by_teacher_id',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    /**
     * Siswa yang diabsen.
     */
    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'student_id');
    }

    public function student(): BelongsTo
    {
        return $this->siswa();
    }

    /**
     * Kelas siswa.
     */
    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'class_id');
    }

    /**
     * Tahun Ajaran.
     */
    public function tahunAjaran(): BelongsTo
    {
        return $this->belongsTo(TahunAjaran::class, 'academic_year_id');
    }

    /**
     * Enrollment siswa.
     */
    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(EnrollmentSiswa::class, 'enrollment_id');
    }

    /**
     * Guru (Wali Kelas) yang melakukan input presensi.
     */
    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class, 'input_by_teacher_id');
    }

    /**
     * Scope filter tanggal.
     */
    public function scopeTanggal($query, $date)
    {
        return $query->whereDate('date', $date);
    }

    /**
     * Scope filter status.
     */
    public function scopeHadir($query)
    {
        return $query->where('status', 'hadir');
    }

    public function scopeIjin($query)
    {
        return $query->where('status', 'ijin');
    }

    public function scopeTidakHadir($query)
    {
        return $query->where('status', 'tidak_hadir');
    }
}
