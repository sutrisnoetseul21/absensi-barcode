<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class WebFaq extends Model
{
    use HasUuids;

    protected $table = 'web_faqs';
    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
        'urutan'    => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('urutan');
    }

    /**
     * Dapatkan daftar kategori umum / rekomendasi
     */
    public static function defaultCategories(): array
    {
        return [
            'SPMB',
            'Layanan Administrasi',
            'Ekstrakurikuler',
            'Perpustakaan',
            'UKS & Kesehatan',
            'Akademik & Pembelajaran',
            'Umum',
        ];
    }
}
