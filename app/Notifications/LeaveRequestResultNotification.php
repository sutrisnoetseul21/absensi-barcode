<?php

namespace App\Notifications;

use App\Models\LeaveRequest;
use App\Notifications\Channels\FcmChannel;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class LeaveRequestResultNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly LeaveRequest $leaveRequest
    ) {
        $this->onQueue(config('queue.connections.database.queue', 'default'));
    }

    public function via(mixed $notifiable): array
    {
        return ['database', FcmChannel::class];
    }

    protected function resolveMessageContent(): array
    {
        $status    = strtolower((string) $this->leaveRequest->status);
        $typeLabel = ucfirst((string) $this->leaveRequest->type);
        $startDate = $this->leaveRequest->start_date
            ? Carbon::parse($this->leaveRequest->start_date)->translatedFormat('d F Y')
            : '-';

        if ($status === 'approved') {
            return [
                'title' => "Permohonan {$typeLabel} Disetujui",
                'body'  => "Permohonan {$typeLabel} Anda mulai tanggal {$startDate} telah disetujui oleh wali kelas.",
            ];
        }

        return [
            'title' => "Permohonan {$typeLabel} Ditolak",
            'body'  => "Permohonan {$typeLabel} Anda mulai tanggal {$startDate} tidak disetujui oleh wali kelas.",
        ];
    }

    public function toDatabase(mixed $notifiable): array
    {
        $content = $this->resolveMessageContent();

        return [
            'title'        => $content['title'],
            'body'         => $content['body'],
            'type'         => 'leave_result',
            'id'           => (string) $this->leaveRequest->id,
            'event_status' => (string) $this->leaveRequest->status,
            'meta'         => [
                'leave_request_id' => (string) $this->leaveRequest->id,
                'status'           => (string) $this->leaveRequest->status,
                'type'             => (string) $this->leaveRequest->type,
                'start_date'       => (string) $this->leaveRequest->start_date,
                'end_date'         => (string) $this->leaveRequest->end_date,
                'approved_at'      => (string) $this->leaveRequest->approved_at,
            ],
        ];
    }

    public function toFcm(mixed $notifiable): array
    {
        $content = $this->resolveMessageContent();

        return [
            'title'        => $content['title'],
            'body'         => $content['body'],
            'type'         => 'leave_result',
            'related_id'   => (string) $this->leaveRequest->id,
            'event_status' => (string) $this->leaveRequest->status,
            'data'         => [
                'type' => 'leave_result',
                'id'   => (string) $this->leaveRequest->id,
            ],
        ];
    }
}
