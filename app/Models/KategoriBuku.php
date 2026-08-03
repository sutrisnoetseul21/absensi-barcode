<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KategoriBuku extends Model
{
    use HasUuids;

    protected $fillable = [
        'nama_kategori',
        'is_bisa_dipinjam',
        'is_buku_pelajaran',
        'kode_prefix',
    ];

    protected $casts = [
        'is_bisa_dipinjam' => 'boolean',
        'is_buku_pelajaran' => 'boolean',
    ];

    public function bukus(): HasMany
    {
        return $this->hasMany(Buku::class, 'kategori_id');
    }
}
