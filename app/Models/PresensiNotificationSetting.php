<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PresensiNotificationSetting extends Model
{
    protected $table = 'presensi_notification_settings';

    protected $fillable = [
        'status_presensi',
        'is_active',
        'recipients',
        'template_pesan',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'recipients' => 'array',
    ];
}
