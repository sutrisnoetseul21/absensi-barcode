<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Str;
use App\Traits\ResizesImages;

class WebArtikel extends Model
{
    use SoftDeletes, HasUuids, ResizesImages;

    protected $guarded = [];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    /**
     * Scope untuk mengambil artikel yang sudah dipublish dan tanggal publishnya valid.
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true)
                     ->where(function ($q) {
                         $q->whereNull('published_at')
                           ->orWhere('published_at', '<=', now());
                     });
    }

    /**
     * Auto generate unique slug saat create
     * dan otomatis resize image saat saved
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($artikel) {
            if (empty($artikel->slug)) {
                $slug = Str::slug($artikel->judul);
                $originalSlug = $slug;
                $count = 1;

                // Cek keunikan slug, append -1, -2 dst jika bentrok
                while (static::where('slug', $slug)->exists()) {
                    $slug = "{$originalSlug}-{$count}";
                    $count++;
                }

                $artikel->slug = $slug;
            }
        });

        static::saved(function ($artikel) {
            if (($artikel->wasRecentlyCreated || $artikel->wasChanged('thumbnail')) && $artikel->thumbnail) {
                $artikel->resizeImage('public', 'thumbnail', 800, forceJpeg: true, quality: 80); // max 800px, JPEG 80%
            }
        });
    }
}
