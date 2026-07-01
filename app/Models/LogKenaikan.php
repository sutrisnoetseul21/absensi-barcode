<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LogKenaikan extends Model
{
    use HasUuids;

    protected $table = 'promotion_logs';

    protected $fillable = [
        'academic_year_from_id',
        'academic_year_to_id',
        'executed_by',
        'notes',
    ];

    // Tahun ajaran asal
    public function tahunAjaranDari(): BelongsTo
    {
        return $this->belongsTo(TahunAjaran::class, 'academic_year_from_id');
    }

    // Tahun ajaran tujuan
    public function tahunAjaranTujuan(): BelongsTo
    {
        return $this->belongsTo(TahunAjaran::class, 'academic_year_to_id');
    }

    // Admin yang menjalankan proses kenaikan
    public function dijalankanOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'executed_by');
    }

    // Detail per siswa dalam proses kenaikan ini
    public function details(): HasMany
    {
        return $this->hasMany(DetailLogKenaikan::class, 'promotion_log_id');
    }
}
