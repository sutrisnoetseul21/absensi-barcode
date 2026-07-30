<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventarisBuku extends Model
{
    use HasUuids;

    protected $fillable = [
        'buku_id',
        'no_inventaris',
        'tanggal_masuk',
        'asal',
        'harga',
        'jumlah_eksemplar',
        'status',
        'alasan_pembatalan',
    ];

    public function buku(): BelongsTo
    {
        return $this->belongsTo(Buku::class, 'buku_id');
    }

    public function eksemplarBukus(): HasMany
    {
        return $this->hasMany(EksemplarBuku::class, 'inventaris_buku_id');
    }
}
