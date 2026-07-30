<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class KunjunganPerpustakaan extends Model
{
    use HasUuids;

    protected $table = 'kunjungan_perpustakaans';

    protected $fillable = [
        'pengunjung_type',
        'pengunjung_id',
        'tanggal',
        'waktu_masuk',
        'tujuan_kunjungan',
        'catatan',
        'petugas_id',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function pengunjung(): MorphTo
    {
        return $this->morphTo();
    }

    public function petugas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'petugas_id');
    }
}
