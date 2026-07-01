<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HariLibur extends Model
{
    use HasUuids;

    // Support range tanggal (start_date..end_date) untuk cuti bersama
    protected $table = 'holidays';

    protected $fillable = [
        'start_date',
        'end_date',
        'description',
        'type',
        'class_id',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    // Kelas yang libur (null = semua kelas)
    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'class_id');
    }

    /**
     * Cek apakah tanggal tertentu masuk dalam range hari libur ini.
     */
    public function meliputiTanggal(string $tanggal): bool
    {
        $tgl = \Carbon\Carbon::parse($tanggal);

        return $tgl->greaterThanOrEqualTo($this->start_date)
            && ($this->end_date === null || $tgl->lessThanOrEqualTo($this->end_date));
    }

    /**
     * Scope: cek apakah suatu tanggal adalah hari libur (untuk kelas tertentu atau semua kelas).
     */
    public static function scopeHariIni($query, string $tanggal, ?string $classId = null)
    {
        return $query->where('start_date', '<=', $tanggal)
            ->where(function ($q) use ($tanggal) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', $tanggal);
            })
            ->where(function ($q) use ($classId) {
                $q->whereNull('class_id');
                if ($classId) {
                    $q->orWhere('class_id', $classId);
                }
            });
    }
}
