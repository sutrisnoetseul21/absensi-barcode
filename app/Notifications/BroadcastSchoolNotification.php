<?php

namespace App\Notifications;

use App\Notifications\Channels\FcmChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class BroadcastSchoolNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $title,
        public readonly string $body,
        public readonly string $targetGroup = 'all',
        public readonly ?string $senderName = 'Admin Sekolah'
    ) {
        $this->onQueue('notifications');
    }

    public function via(mixed $notifiable): array
    {
        return ['database', FcmChannel::class];
    }

    public function toDatabase(mixed $notifiable): array
    {
        return [
            'title'        => $this->title,
            'body'         => $this->body,
            'type'         => 'broadcast_school',
            'id'           => (string) Str::uuid(),
            'event_status' => 'info',
            'meta'         => [
                'target_group' => $this->targetGroup,
                'sender_name'  => $this->senderName,
                'sent_at'      => now()->toIso8601String(),
            ],
        ];
    }

    public function toFcm(mixed $notifiable): array
    {
        return [
            'title'        => $this->title,
            'body'         => $this->body,
            'type'         => 'broadcast_school',
            'related_id'   => (string) Str::uuid(),
            'event_status' => 'info',
            'data'         => [
                'type'         => 'broadcast_school',
                'target_group' => $this->targetGroup,
                'sender_name'  => (string) $this->senderName,
            ],
        ];
    }
}
