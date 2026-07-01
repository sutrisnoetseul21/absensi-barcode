<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailLogKenaikan extends Model
{
    use HasUuids;

    // Audit detail per siswa dalam satu proses kenaikan kelas
    protected $table = 'promotion_log_details';

    protected $fillable = [
        'promotion_log_id',
        'student_id',
        'old_enrollment_id',
        'new_enrollment_id',
        'decision',
    ];

    // Log kenaikan induk
    public function logKenaikan(): BelongsTo
    {
        return $this->belongsTo(LogKenaikan::class, 'promotion_log_id');
    }

    // Siswa yang diproses
    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'student_id');
    }

    // Enrollment lama (sebelum kenaikan)
    public function enrollmentLama(): BelongsTo
    {
        return $this->belongsTo(EnrollmentSiswa::class, 'old_enrollment_id');
    }

    // Enrollment baru (setelah kenaikan) — null jika lulus
    public function enrollmentBaru(): BelongsTo
    {
        return $this->belongsTo(EnrollmentSiswa::class, 'new_enrollment_id');
    }
}
