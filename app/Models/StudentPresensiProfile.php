<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentPresensiProfile extends Model
{
    use HasUuids;

    protected $table = 'student_presensi_profiles';

    protected $fillable = [
        'student_id',
        'barcode_code',
        'barcode_active',
    ];

    protected $casts = [
        'barcode_active' => 'boolean',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'student_id');
    }
}
