<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MataPelajaran extends Model
{
    protected $fillable = [
        'nama_mapel',
        'kode_mapel',
        'kategori',
        'kkm_default',
    ];

    protected $casts = [
        'kkm_default' => 'decimal:2',
    ];

    public function pengajarans(): HasMany
    {
        return $this->hasMany(Pengajaran::class, 'mata_pelajaran_id');
    }

    public function ujianAkademiks(): HasMany
    {
        return $this->hasMany(UjianAkademik::class, 'mata_pelajaran_id');
    }
}
