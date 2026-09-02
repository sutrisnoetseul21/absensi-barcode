<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pengaduan extends Model
{
    protected $guarded = [];

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(PengaduanKategori::class, 'pengaduan_kategori_id');
    }
}
