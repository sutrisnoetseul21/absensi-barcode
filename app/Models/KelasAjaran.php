<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KelasAjaran extends Model
{
    use HasUuids;

    // Pivot: kelas yang di-assign ke tahun ajaran tertentu, beserta wali kelasnya
    protected $table = 'class_academic_year';

    protected $fillable = [
        'class_id',
        'academic_year_id',
        'teacher_id',
    ];

    // Kelas (template permanen)
    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'class_id');
    }

    // Tahun ajaran
    public function tahunAjaran(): BelongsTo
    {
        return $this->belongsTo(TahunAjaran::class, 'academic_year_id');
    }

    // Wali kelas (guru) yang ditugaskan
    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class, 'teacher_id');
    }
}
