<?php

namespace App\Jobs;

use App\Models\FcmDeviceToken;
use App\Models\KelasAjaran;
use App\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Job pengiriman notifikasi Push Notification (FCM) kepada perangkat Android Wali Kelas
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

    public function handle(): void
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

        // Ambil seluruh token FCM milik Wali Kelas
        $tokens = FcmDeviceToken::where('user_id', $waliUser->id)
            ->pluck('token')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        if (empty($tokens)) {
            Log::info("SendLeaveRequestFcmNotificationJob: Wali Kelas #{$waliUser->id} ({$waliUser->name}) belum memiliki perangkat Android / FCM token terdaftar.");
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
            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
        ];

        $serverKey = config('services.firebase.server_key') ?: env('FCM_SERVER_KEY');

        if (!empty($serverKey)) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => 'key=' . $serverKey,
                    'Content-Type' => 'application/json',
                ])->post('https://fcm.googleapis.com/fcm/send', [
                    'registration_ids' => $tokens,
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                        'sound' => 'default',
                    ],
                    'data' => $payload,
                    'priority' => 'high',
                ]);

                if ($response->successful()) {
                    Log::info("SendLeaveRequestFcmNotificationJob: Push notification terkirim ke " . count($tokens) . " perangkat Wali Kelas {$waliUser->name}.");
                } else {
                    Log::warning("SendLeaveRequestFcmNotificationJob: Gagal mengirim FCM. Respons: " . $response->body());
                }
            } catch (Throwable $e) {
                Log::error("SendLeaveRequestFcmNotificationJob error: " . $e->getMessage());
            }
        } else {
            // Mode Simulasi / Logging jika FCM Server Key belum di-set di .env
            Log::info("SendLeaveRequestFcmNotificationJob (Simulated FCM): Title: [{$title}] Body: [{$body}] Ke " . count($tokens) . " perangkat Wali Kelas #{$waliUser->id}.");
        }
    }
}

