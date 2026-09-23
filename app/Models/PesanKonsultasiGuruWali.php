<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PesanKonsultasiGuruWali extends Model
{
    use HasUuids;

    protected $table = 'pesan_konsultasi_guru_wali';

    protected $fillable = [
        'konsultasi_id',
        'sender_type',
        'pesan',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    /**
     * Konsultasi terkait pesan ini.
     */
    public function konsultasi(): BelongsTo
    {
        return $this->belongsTo(KonsultasiGuruWali::class, 'konsultasi_id');
    }
}
