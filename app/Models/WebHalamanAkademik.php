<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebHalamanAkademik extends Model
{
    protected $fillable = [
        'judul',
        'slug',
        'kategori',
        'deskripsi_singkat',
        'file_pdf',
        'konten',
        'urutan',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'urutan' => 'integer',
    ];
}
