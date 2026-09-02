<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebStatistic extends Model
{
    protected $fillable = [
        'icon',
        'value',
        'label',
        'order',
    ];
}
