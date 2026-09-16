<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\Pivot;

class UjianAkademikClass extends Pivot
{
    use HasUuids;

    protected $table = 'ujian_akademik_classes';

    public $incrementing = false;

    protected $keyType = 'string';
}
