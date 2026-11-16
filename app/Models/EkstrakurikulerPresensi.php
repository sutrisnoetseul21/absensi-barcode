<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class EkstrakurikulerPresensi extends Model
{
    use HasUuids;

    protected $table = 'ekstrakurikuler_presensis';

    protected $fillable = [
        'pertemuan_id',
        'ekstrakurikuler_id',
        'student_id',
        'status',
        'scan_time',
        'is_manual_input',
        'manual_input_by_id',
        'manual_input_by_type',
        'manual_input_at',
        'manual_input_user_id',
        'scanned_by',
        'keterangan',
    ];

    protected $casts = [
        'is_manual_input' => 'boolean',
        'manual_input_at' => 'datetime',
    ];

    public function pertemuan(): BelongsTo
    {
        return $this->belongsTo(EkstrakurikulerPertemuan::class, 'pertemuan_id');
    }

    public function ekstrakurikuler(): BelongsTo
    {
        return $this->belongsTo(Ekstrakurikuler::class, 'ekstrakurikuler_id');
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'student_id');
    }

    public function petugas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'scanned_by');
    }

    public function inputManualOleh(): MorphTo
    {
        return $this->morphTo('manual_input_by');
    }

    public function manualInputUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manual_input_user_id');
    }
}

