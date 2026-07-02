<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class LogScan extends Model
{
    use HasUuids;

    protected $table = 'scan_logs';

    protected $fillable = [
        'barcode_code',
        'student_id',
        'status',
        'scan_time',
        'ip_address',
    ];

    protected $casts = [
        'scan_time' => 'datetime',
    ];
    
    public function student()
    {
        return $this->belongsTo(Siswa::class, 'student_id');
    }
}
