<?php

namespace App\Notifications;

use App\Models\Presensi;
use App\Notifications\Channels\FcmChannel;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class AttendanceStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Presensi $presensi
    ) {
        $this->onQueue(config('queue.connections.database.queue', 'default'));
    }

    /**
     * Kanal pengiriman notifikasi.
     */
    public function via(mixed $notifiable): array
    {
        return ['database', FcmChannel::class];
    }

    /**
     * Resolusi judul dan isi notifikasi berdasarkan status presensi.
     */
    protected function resolveMessageContent(): array
    {
        $status = strtolower((string) $this->presensi->status);
        $dateStr = $this->presensi->date
            ? Carbon::parse($this->presensi->date)->translatedFormat('d F Y')
            : Carbon::today()->translatedFormat('d F Y');

        $lateMinutes = (int) ($this->presensi->late_minutes ?? 0);

        return match ($status) {
            'telat' => [
                'title' => 'Status Presensi: Terlambat',
                'body'  => "Anda tercatat hadir terlambat ({$lateMinutes} menit) pada {$dateStr}.",
            ],
            'alpa' => [
                'title' => 'Status Presensi: Alpa',
                'body'  => "Anda tercatat tidak hadir (Alpa) pada {$dateStr}.",
            ],
            'ijin' => [
                'title' => 'Status Presensi: Izin',
                'body'  => "Status kehadiran Anda pada {$dateStr} tercatat Izin.",
            ],
            'sakit' => [
                'title' => 'Status Presensi: Sakit',
                'body'  => "Status kehadiran Anda pada {$dateStr} tercatat Sakit.",
            ],
            default => [
                'title' => 'Pemberitahuan Presensi',
                'body'  => "Status kehadiran Anda pada {$dateStr} tercatat: " . ucfirst($status) . ".",
            ],
        };
    }

    /**
     * Format data untuk penyimpanan di tabel `notifications` (Inbox).
     */
    public function toDatabase(mixed $notifiable): array
    {
        $content = $this->resolveMessageContent();
        $dateIso = $this->presensi->date ? Carbon::parse($this->presensi->date)->toDateString() : Carbon::today()->toDateString();
        $relatedId = "{$dateIso}:{$this->presensi->student_id}";

        return [
            'title'        => $content['title'],
            'body'         => $content['body'],
            'type'         => 'attendance_status',
            'id'           => $relatedId,
            'event_status' => (string) $this->presensi->status,
            'meta'         => [
                'attendance_id' => (string) $this->presensi->id,
                'student_id'    => (string) $this->presensi->student_id,
                'date'          => $dateIso,
                'status'        => (string) $this->presensi->status,
                'late_minutes'  => $this->presensi->late_minutes,
            ],
        ];
    }

    /**
     * Format data untuk push notification FCM HTTP v1.
     */
    public function toFcm(mixed $notifiable): array
    {
        $content = $this->resolveMessageContent();
        $dateIso = $this->presensi->date ? Carbon::parse($this->presensi->date)->toDateString() : Carbon::today()->toDateString();
        $relatedId = "{$dateIso}:{$this->presensi->student_id}";

        return [
            'title'        => $content['title'],
            'body'         => $content['body'],
            'type'         => 'attendance_status',
            'related_id'   => $relatedId,
            'event_status' => (string) $this->presensi->status,
            'data'         => [
                'type' => 'attendance_status',
                'id'   => $relatedId,
            ],
        ];
    }
}
