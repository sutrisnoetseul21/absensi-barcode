<?php

namespace App\Notifications;

use App\Models\LeaveRequest;
use App\Notifications\Channels\FcmChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class LeaveRequestSubmittedNotification extends Notification implements ShouldQueue
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

    public function toDatabase(mixed $notifiable): array
    {
        $this->leaveRequest->loadMissing(['student.enrollmentAktif.kelas']);
        $studentName = $this->leaveRequest->student->name ?? 'Siswa';
        $className   = $this->leaveRequest->student->enrollmentAktif->kelas->name ?? '-';
        $typeLabel   = ucfirst((string) $this->leaveRequest->type);
        $days        = (int) ($this->leaveRequest->duration_days ?? 1);

        $title = "Pengajuan Izin Siswa: {$studentName}";
        $body  = "{$studentName} ({$className}) mengajukan permohonan {$typeLabel} selama {$days} hari.";

        return [
            'title'        => $title,
            'body'         => $body,
            'type'         => 'leave_approval',
            'id'           => (string) $this->leaveRequest->id,
            'event_status' => 'pending',
            'meta'         => [
                'leave_request_id' => (string) $this->leaveRequest->id,
                'student_id'       => (string) $this->leaveRequest->student_id,
                'student_name'     => $studentName,
                'class_name'       => $className,
                'type'             => (string) $this->leaveRequest->type,
                'start_date'       => (string) $this->leaveRequest->start_date,
                'end_date'         => (string) $this->leaveRequest->end_date,
                'duration_days'    => $days,
            ],
        ];
    }

    public function toFcm(mixed $notifiable): array
    {
        $this->leaveRequest->loadMissing(['student.enrollmentAktif.kelas']);
        $studentName = $this->leaveRequest->student->name ?? 'Siswa';
        $className   = $this->leaveRequest->student->enrollmentAktif->kelas->name ?? '-';
        $typeLabel   = ucfirst((string) $this->leaveRequest->type);
        $days        = (int) ($this->leaveRequest->duration_days ?? 1);

        $title = "Pengajuan Izin Siswa: {$studentName}";
        $body  = "{$studentName} ({$className}) mengajukan permohonan {$typeLabel} selama {$days} hari.";

        return [
            'title'        => $title,
            'body'         => $body,
            'type'         => 'leave_approval',
            'related_id'   => (string) $this->leaveRequest->id,
            'event_status' => 'pending',
            'data'         => [
                'type' => 'leave_approval',
                'id'   => (string) $this->leaveRequest->id,
            ],
        ];
    }
}
