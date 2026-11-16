<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EkstrakurikulerSiswa extends Model
{
    use HasUuids;

    protected $table = 'ekstrakurikuler_siswas';

    protected $fillable = [
        'ekstrakurikuler_id',
        'student_id',
        'tanggal_gabung',
        'status_aktif',
    ];

    protected $casts = [
        'status_aktif'   => 'boolean',
        'tanggal_gabung' => 'date',
    ];

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('status_aktif', true);
    }

    public function ekstrakurikuler(): BelongsTo
    {
        return $this->belongsTo(Ekstrakurikuler::class, 'ekstrakurikuler_id');
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'student_id');
    }
}
