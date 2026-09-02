<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Traits\ResizesImages;

class WebGaleri extends Model
{
    use SoftDeletes, HasUuids, ResizesImages;

    protected $table = 'web_galeris';
    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();

        static::saved(function ($galeri) {
            if (($galeri->wasRecentlyCreated || $galeri->wasChanged('foto_path')) && $galeri->foto_path) {
                $galeri->resizeImage('public', 'foto_path', 1200, forceJpeg: true, quality: 80); // max 1200px, JPEG 80%
            }
        });
    }
}
