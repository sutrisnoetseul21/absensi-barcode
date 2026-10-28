<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JurnalPembelajaranPresensi extends Model
{
    use HasUuids;

    protected $table = 'jurnal_pembelajaran_presensis';

    protected $fillable = [
        'jurnal_pembelajaran_id',
        'student_id',
        'status_jurnal',
        'status_gerbang_snapshot',
        'keterangan',
        'diabsenkan_telat_di_gerbang',
    ];

    protected $casts = [
        'diabsenkan_telat_di_gerbang' => 'boolean',
    ];

    /**
     * Relasi ke Jurnal Pembelajaran
     */
    public function jurnal(): BelongsTo
    {
        return $this->belongsTo(JurnalPembelajaran::class, 'jurnal_pembelajaran_id');
    }

    /**
     * Relasi ke Siswa
     */
    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'student_id');
    }

    public function student(): BelongsTo
    {
        return $this->siswa();
    }
}
