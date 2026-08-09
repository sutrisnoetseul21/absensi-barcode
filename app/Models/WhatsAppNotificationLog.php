<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsAppNotificationLog extends Model
{
    protected $table = 'whatsapp_notification_logs';

    protected $fillable = [
        'module',
        'recipient_type',
        'recipient_number',
        'message',
        'status',
        'response_payload',
        'related_type',
        'related_id',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    /**
     * Relasi polymorphic ke model yang berkaitan (misal: Presensi)
     */
    public function related()
    {
        return $this->morphTo();
    }
}
