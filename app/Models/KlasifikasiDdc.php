<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class KlasifikasiDdc extends Model
{
    use HasUuids;

    protected $fillable = [
        'kode_ddc',
        'kategori',
    ];

    public function getNamaKlasifikasiAttribute(): ?string
    {
        return $this->kategori;
    }
}
