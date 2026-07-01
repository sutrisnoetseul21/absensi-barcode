<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class LogScanInvalid extends Model
{
    use HasUuids;

    protected $table = 'invalid_scan_logs';

    protected $fillable = [
        'scanned_code',
        'scan_time',
        'ip_address',
    ];

    protected $casts = [
        'scan_time' => 'datetime',
    ];
}
