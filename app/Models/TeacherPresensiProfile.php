<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherPresensiProfile extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'teacher_presensi_profiles';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'teacher_id',
        'barcode_code',
        'barcode_active',
    ];

    protected $casts = [
        'barcode_active' => 'boolean',
    ];

    /**
     * Get the teacher that owns the presensi profile.
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Guru::class, 'teacher_id');
    }
}
