<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AlumniJenjang extends Model
{
    protected $fillable = [
        'nama_jenjang',
    ];

    public function alumnis(): HasMany
    {
        return $this->hasMany(Alumni::class, 'jenjang_id');
    }
}
