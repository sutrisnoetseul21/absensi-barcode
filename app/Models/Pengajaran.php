<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pengajaran extends Model
{
    use HasUuids;

    protected $fillable = [
        'class_academic_year_id',
        'teacher_id',
        'mata_pelajaran_id',
    ];

    public function kelasAjaran(): BelongsTo
    {
        return $this->belongsTo(KelasAjaran::class, 'class_academic_year_id');
    }

    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class, 'teacher_id');
    }

    public function mataPelajaran(): BelongsTo
    {
        return $this->belongsTo(MataPelajaran::class, 'mata_pelajaran_id');
    }
}
