<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebQuickLink extends Model
{
    protected $fillable = [
        'title',
        'description',
        'url',
        'icon',
        'color_class',
        'is_active',
        'order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
