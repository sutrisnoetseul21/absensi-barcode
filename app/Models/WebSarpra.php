<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Traits\ResizesImages;

class WebSarpra extends Model
{
    use SoftDeletes, HasUuids, ResizesImages;

    protected $table = 'web_sarpras';
    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();

        static::saved(function ($sarpra) {
            if ($sarpra->wasChanged('foto') && $sarpra->foto) {
                $sarpra->resizeImage('public', 'foto', 800); // max 800px
            }
        });
    }
}
