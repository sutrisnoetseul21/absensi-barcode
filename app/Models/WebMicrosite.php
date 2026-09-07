<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebMicrosite extends Model
{
    protected $fillable = [
        'judul',
        'deskripsi',
        'url',
        'kategori',
        'icon',
        'button_text',
        'urutan',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'urutan' => 'integer',
    ];
}
