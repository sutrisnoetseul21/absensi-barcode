<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MataPelajaran extends Model
{
    protected $fillable = ['nama_mapel', 'kode_mapel'];

    public function pengajarans()
    {
        return $this->hasMany(Pengajaran::class, 'mata_pelajaran_id');
    }
}
