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
        $tgl = \Carbon\Carbon::parse($tanggal)->startOfDay();
        $start = $this->start_date->copy()->startOfDay();

        if ($this->end_date === null) {
            return $tgl->equalTo($start);
        }

        $end = $this->end_date->copy()->startOfDay();
        return $tgl->betweenIncluded($start, $end);
    }

    /**
     * Scope: cek apakah suatu tanggal adalah hari libur (untuk kelas tertentu atau semua kelas).
     */
    public static function scopeHariIni($query, string $tanggal, ?string $classId = null)
    {
        return $query->where(function ($q) use ($tanggal) {
                $q->where(function ($q2) use ($tanggal) {
                    $q2->whereNull('end_date')->whereDate('start_date', $tanggal);
                })->orWhere(function ($q3) use ($tanggal) {
                    $q3->whereNotNull('end_date')
                       ->whereDate('start_date', '<=', $tanggal)
                       ->whereDate('end_date', '>=', $tanggal);
                });
            })
            ->where(function ($q) use ($classId) {
                $q->whereNull('class_id');
                if ($classId) {
                    $q->orWhere('class_id', $classId);
                }
            });
    }
}
