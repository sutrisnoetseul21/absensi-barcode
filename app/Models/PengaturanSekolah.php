<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PengaturanSekolah extends Model
{
    use HasUuids;

    protected $table = 'school_settings';

    protected $fillable = [
        'school_name',
        'school_address',
        'school_logo_path',
        'principal_name',
        'checkin_time',
        'late_threshold_minutes',
        'academic_year_id_active',
        'enable_promotion_features',
    ];

    protected $casts = [
        'checkin_time'              => 'string',
        'late_threshold_minutes'    => 'integer',
        'enable_promotion_features' => 'boolean',
    ];

    // Tahun ajaran yang sedang aktif
    public function tahunAjaranAktif(): BelongsTo
    {
        return $this->belongsTo(TahunAjaran::class, 'academic_year_id_active');
    }

    /**
     * Ambil pengaturan sekolah (tabel hanya berisi 1 baris).
     */
    public static function current(): ?static
    {
        return static::first();
    }
}
