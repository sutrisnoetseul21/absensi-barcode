<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebAplikasi extends Model
{
    protected $fillable = [
        'name',
        'url',
        'icon',
        'icon_color',
        'is_active',
        'order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
