<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PengaduanKategori extends Model
{
    protected $guarded = [];

    public function pengaduans(): HasMany
    {
        return $this->hasMany(Pengaduan::class, 'pengaduan_kategori_id');
    }
}
