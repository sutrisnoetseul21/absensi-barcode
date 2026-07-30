<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EksemplarBuku extends Model
{
    use HasUuids, SoftDeletes;

    protected static function booted()
    {
        static::deleted(function ($eksemplar) {
            if ($eksemplar->inventaris_buku_id) {
                $eksemplar->inventarisBuku()->decrement('jumlah_eksemplar');
            }
        });

        static::restored(function ($eksemplar) {
            if ($eksemplar->inventaris_buku_id) {
                $eksemplar->inventarisBuku()->increment('jumlah_eksemplar');
            }
        });
    }

    protected $fillable = [
        'buku_id',
        'inventaris_buku_id',
        'kode_eksemplar',
        'status',
        'kondisi_fisik',
    ];

    public function buku(): BelongsTo
    {
        return $this->belongsTo(Buku::class, 'buku_id')->withTrashed();
    }

    public function inventarisBuku(): BelongsTo
    {
        return $this->belongsTo(InventarisBuku::class, 'inventaris_buku_id');
    }

    public function peminjamans(): HasMany
    {
        return $this->hasMany(Peminjaman::class, 'eksemplar_id');
    }

    public static function generateKodeEksemplar($prefix, $jumlah = 1)
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($prefix, $jumlah) {
            // Kita gunakan pessimistic locking dengan lockForUpdate pada record dummy di tabel lain (misal school_settings) 
            // atau jika tidak ada, cukup lock EksemplarBuku terbaru (jika ada).
            // Paling aman: lockForUpdate() jika ada row.
            
            $latest = self::orderBy('created_at', 'desc')->lockForUpdate()->first();

            $codes = self::pluck('kode_eksemplar')
                ->map(function ($code) {
                    if (preg_match('/(\d+)$/', $code, $matches)) {
                        return (int) $matches[1];
                    }
                    return 0;
                })
                ->toArray();

            $maxNum = count($codes) > 0 ? max($codes) : 0;
            
            $generatedCodes = [];
            $startNum = $maxNum + 1;
            
            for ($i = 1; $i <= $jumlah; $i++) {
                $nextNum = $maxNum + $i;
                if ($nextNum > 99999) {
                    throw new \Exception("Nomor urut sudah mencapai batas maksimum (99999).");
                }
                $generatedCodes[] = $prefix . str_pad((string)$nextNum, 5, '0', STR_PAD_LEFT);
            }
            
            $endNum = $maxNum + $jumlah;

            return [
                'codes' => $generatedCodes,
                'start_num' => str_pad((string)$startNum, 5, '0', STR_PAD_LEFT),
                'end_num' => str_pad((string)$endNum, 5, '0', STR_PAD_LEFT),
            ];
        });
    }
}
