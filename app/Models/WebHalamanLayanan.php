<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebHalamanLayanan extends Model
{
    protected $fillable = [
        'judul',
        'kategori',
        'slug',
        'deskripsi_singkat',
        'konten',
        'file_pdf',
        'biaya',
        'waktu_layanan',
        'urutan',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'urutan' => 'integer',
    ];
}
