<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FcmNotificationLog extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'fcm_notification_logs';

    protected $fillable = [
        'user_id',
        'fcm_token',
        'type',
        'related_id',
        'event_status',
        'title',
        'body',
        'payload',
        'status',
        'error_message',
        'sent_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'sent_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
