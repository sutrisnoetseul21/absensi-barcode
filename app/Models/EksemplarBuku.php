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

    protected $fillable = [
        'buku_id',
        'kode_eksemplar',
        'status',
        'kondisi_fisik',
    ];

    public function buku(): BelongsTo
    {
        return $this->belongsTo(Buku::class, 'buku_id');
    }

    public function peminjamans(): HasMany
    {
        return $this->hasMany(Peminjaman::class, 'eksemplar_id');
    }

    public static function generateKodeEksemplar($prefix, $jumlah = 1)
    {
        $codes = self::where('kode_eksemplar', 'like', $prefix . '%')
            ->pluck('kode_eksemplar')
            ->map(function ($code) {
                // Ekstrak semua digit angka di akhir string (mendukung 3 digit lama maupun 5 digit baru)
                if (preg_match('/(\d+)$/', $code, $matches)) {
                    return (int) $matches[1];
                }
                return 0;
            })
            ->toArray();

        $maxNum = count($codes) > 0 ? max($codes) : 0;
        
        $generatedCodes = [];
        for ($i = 1; $i <= $jumlah; $i++) {
            $nextNum = $maxNum + $i;
            if ($nextNum > 99999) {
                throw new \Exception("Nomor urut untuk prefix {$prefix} sudah mencapai batas maksimum (99999).");
            }
            $generatedCodes[] = $prefix . str_pad((string)$nextNum, 5, '0', STR_PAD_LEFT);
        }
        
        return $generatedCodes;
    }
}
