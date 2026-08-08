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

    public static bool $isGenerating = false;
    public static bool $isBulkImporting = false;

    public static function generateKodeEksemplar($prefix, $jumlah = 1)
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($prefix, $jumlah) {
            self::$isGenerating = true;
            
            try {
                // Gunakan pessimistic locking pada tabel school_settings
                $settings = \App\Models\PengaturanSekolah::lockForUpdate()->first();
                
                if (!$settings) {
                    throw new \Exception("Pengaturan Sekolah belum diatur.");
                }
                
                $maxNum = (int) $settings->last_barcode_number;
                
                $generatedCodes = [];
                $startNum = $maxNum + 1;
                
                for ($i = 1; $i <= $jumlah; $i++) {
                    $nextNum = $maxNum + $i;
                    if ($nextNum > 999999999) {
                        throw new \Exception("Nomor urut sudah mencapai batas maksimum (999999999).");
                    }
                    
                    // Pastikan prefix ditambahkan dengan baik, kalau kosong tidak masalah
                    $finalPrefix = empty($prefix) ? '' : $prefix;
                    
                    // Pad number to 5 digits, but if it's already larger (like 170100), it remains intact
                    $generatedCodes[] = $finalPrefix . str_pad((string)$nextNum, 5, '0', STR_PAD_LEFT);
                }
                
                $endNum = $maxNum + $jumlah;
                
                // Update counter di database
                $settings->update(['last_barcode_number' => $endNum]);
                \Illuminate\Support\Facades\Cache::forget('public_pengaturan_sekolah');

                return [
                    'codes' => $generatedCodes,
                    'start_num' => str_pad((string)$startNum, 5, '0', STR_PAD_LEFT),
                    'end_num' => str_pad((string)$endNum, 5, '0', STR_PAD_LEFT),
                ];
            } finally {
                self::$isGenerating = false;
            }
        });
    }
}
