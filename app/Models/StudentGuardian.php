<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentGuardian extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'student_guardians';

    protected $fillable = [
        'student_id',
        'type',
        'name',
        'occupation',
        'address',
        'phone',
    ];

    /**
     * Label yang tampil di UI untuk setiap tipe wali.
     */
    public static function typeLabels(): array
    {
        return [
            'ayah' => 'Ayah Kandung',
            'ibu'  => 'Ibu Kandung',
            'wali' => 'Wali',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'student_id');
    }
}
