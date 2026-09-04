<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Alumni extends Model
{
    protected $fillable = [
        'student_id',
        'source',
        'nisn',
        'nama',
        'jenis_kelamin',
        'tahun_lulus',
        'melanjutkan',
        'jenjang_id',
        'nama_sekolah',
        'no_hp',
        'foto',
    ];

    protected $casts = [
        'melanjutkan' => 'boolean',
        'tahun_lulus' => 'integer',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'student_id');
    }

    public function jenjang(): BelongsTo
    {
        return $this->belongsTo(AlumniJenjang::class, 'jenjang_id');
    }
}
