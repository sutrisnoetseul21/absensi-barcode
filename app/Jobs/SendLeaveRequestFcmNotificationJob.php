<?php

namespace App\Jobs;

use App\Models\KelasAjaran;
use App\Models\LeaveRequest;
use App\Services\FcmPushService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Job pengiriman notifikasi Push Notification (FCM HTTP v1) kepada perangkat Android Wali Kelas
 * ketika siswa mengajukan permohonan ijin/sakit baru.
 */
class SendLeaveRequestFcmNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 60;

    public function __construct(
        public readonly string $leaveRequestId
    ) {}

    public function backoff(): array
    {
        return [30, 120, 300];
    }

    public function handle(FcmPushService $fcm): void
    {
        $record = LeaveRequest::with(['student.enrollmentAktif.kelas'])->find($this->leaveRequestId);
        if (!$record) {
            Log::warning("SendLeaveRequestFcmNotificationJob: LeaveRequest #{$this->leaveRequestId} tidak ditemukan.");
            return;
        }

        $student = $record->student;
        $enrollment = $student?->enrollmentAktif;
        if (!$enrollment) {
            Log::info("SendLeaveRequestFcmNotificationJob: Siswa #{$record->student_id} tidak memiliki enrollment aktif.");
            return;
        }

        // Cari Wali Kelas dari class_academic_year
        $kelasAjaran = KelasAjaran::where('class_id', $enrollment->class_id)
            ->where('academic_year_id', $enrollment->academic_year_id)
            ->with(['guru.user'])
            ->first();

        $waliUser = $kelasAjaran?->guru?->user;
        if (!$waliUser) {
            Log::info("SendLeaveRequestFcmNotificationJob: Rombel {$enrollment->class_id} tidak memiliki user Wali Kelas terdaftar.");
            return;
        }

        $kelasName = $enrollment->kelas->name ?? '-';
        $typeLabel = $record->type === 'sakit' ? 'Sakit' : 'Izin';
        $title = "📋 Permohonan {$typeLabel} Baru: {$student?->name} ({$kelasName})";
        $body = "{$typeLabel} ({$record->duration_days} hari): {$record->reason}";

        $payload = [
            'type' => 'leave_request',
            'leave_request_id' => (string) $record->id,
            'student_name' => (string) ($student?->name ?? ''),
            'class_name' => (string) $kelasName,
            'duration_days' => (string) $record->duration_days,
            'title' => $title,
            'body' => $body,
            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
        ];

        try {
            $res = $fcm->sendToUser($waliUser, $title, $body, $payload);
            Log::info("SendLeaveRequestFcmNotificationJob: Notifikasi ijin dikirim ke Wali Kelas {$waliUser->name}: " . json_encode($res));
        } catch (Throwable $e) {
            Log::error("SendLeaveRequestFcmNotificationJob error: " . $e->getMessage());
        }
    }
}
