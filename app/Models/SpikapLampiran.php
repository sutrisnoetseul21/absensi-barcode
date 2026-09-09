<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class SpikapLampiran extends Model
{
    protected $table = 'spikap_lampiran';

    protected $guarded = [];

    // ─── Relasi ──────────────────────────────────────────────────────────────

    /**
     * Laporan yang memiliki lampiran ini.
     */
    public function laporan(): BelongsTo
    {
        return $this->belongsTo(SpikapLaporan::class, 'laporan_id');
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    /**
     * Apakah file ini tersimpan di disk local?
     * File TIDAK bisa diakses via URL publik langsung — harus lewat controller.
     */
    public function isExist(): bool
    {
        return Storage::disk('local')->exists($this->path_file);
    }

    /**
     * Ukuran file dalam format manusiawi (KB / MB).
     */
    public function getUkuranReadableAttribute(): string
    {
        $bytes = $this->ukuran_bytes;
        if ($bytes >= 1_048_576) {
            return round($bytes / 1_048_576, 1) . ' MB';
        }
        return round($bytes / 1024, 1) . ' KB';
    }
}
