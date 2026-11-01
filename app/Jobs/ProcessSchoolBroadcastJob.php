<?php

namespace App\Jobs;

use App\Models\FcmDeviceToken;
use App\Models\FcmNotificationLog;
use App\Models\SchoolBroadcast;
use App\Models\User;
use App\Services\FcmPushService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class ProcessSchoolBroadcastJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 180;

    public function __construct(
        public readonly string $broadcastId
    ) {
        $this->onQueue('notifications');
    }

    public function handle(FcmPushService $fcmService): void
    {
        $broadcast = SchoolBroadcast::find($this->broadcastId);
        if (!$broadcast) {
            Log::warning("ProcessSchoolBroadcastJob: Broadcast {$this->broadcastId} tidak ditemukan.");
            return;
        }

        try {
            // 1. Tentukan query penerima sesuai jangkauan sekolah ini
            $query = User::query();

            if ($broadcast->target_group === 'siswa') {
                $query->whereHas('student');
            } elseif ($broadcast->target_group === 'guru') {
                $query->where(function ($q) {
                    $q->whereHas('teacher')->orWhereHas('roles', fn ($r) => $r->whereIn('name', ['guru', 'wali_kelas']));
                });
            } elseif ($broadcast->target_group === 'kelas' && !empty($broadcast->class_id)) {
                $query->whereHas('student.enrollmentAktif', function ($q) use ($broadcast) {
                    $q->where('class_id', $broadcast->class_id);
                });
            }

            $targetUserIds = $query->pluck('id')->toArray();
            $totalTargetUsers = count($targetUserIds);

            // 2. Ambil token perangkat aktif milik user-user target
            $deviceTokens = FcmDeviceToken::whereIn('user_id', $targetUserIds)->get();
            $fcmSentCount = 0;

            foreach ($deviceTokens as $deviceToken) {
                try {
                    $res = $fcmService->sendToToken(
                        token: $deviceToken->token,
                        title: $broadcast->title,
                        body: $broadcast->body,
                        data: [
                            'type'         => 'broadcast_school',
                            'broadcast_id' => (string) $broadcast->id,
                            'target_group' => (string) $broadcast->target_group,
                            'sender_name'  => (string) $broadcast->sender_name,
                        ]
                    );

                    $isSuccess = !empty($res['success']);
                    if ($isSuccess) {
                        $fcmSentCount++;
                    }

                    // Catat ke log
                    FcmNotificationLog::create([
                        'user_id'       => $deviceToken->user_id,
                        'fcm_token'     => $deviceToken->token,
                        'type'          => 'broadcast_school',
                        'related_id'    => (string) $broadcast->id,
                        'event_status'  => 'info',
                        'title'         => $broadcast->title,
                        'body'          => $broadcast->body,
                        'payload'       => [
                            'type'         => 'broadcast_school',
                            'broadcast_id' => (string) $broadcast->id,
                            'target_group' => (string) $broadcast->target_group,
                            'sender_name'  => (string) $broadcast->sender_name,
                        ],
                        'status'        => $isSuccess ? 'sent' : 'failed',
                        'error_message' => $res['error'] ?? null,
                        'sent_at'       => now(),
                    ]);
                } catch (Throwable $e) {
                    Log::error("ProcessSchoolBroadcastJob error sending to token [{$deviceToken->token}]: " . $e->getMessage());
                }
            }

            // 3. Masukkan ke tabel notifications (Pusat Notifikasi Aplikasi) secara Bulk Insert efisien
            if (!empty($targetUserIds)) {
                $now = now()->toDateTimeString();
                $isoNow = now()->toIso8601String();
                $dataJson = json_encode([
                    'title'        => $broadcast->title,
                    'body'         => $broadcast->body,
                    'type'         => 'broadcast_school',
                    'id'           => (string) $broadcast->id,
                    'event_status' => 'info',
                    'meta'         => [
                        'broadcast_id' => (string) $broadcast->id,
                        'target_group' => $broadcast->target_group,
                        'class_name'   => $broadcast->class_name,
                        'sender_name'  => $broadcast->sender_name,
                        'sent_at'      => $isoNow,
                    ],
                ]);

                foreach (array_chunk($targetUserIds, 500) as $chunk) {
                    $insertRows = [];
                    foreach ($chunk as $userId) {
                        $insertRows[] = [
                            'id'              => (string) Str::uuid(),
                            'type'            => 'App\\Notifications\\BroadcastSchoolNotification',
                            'notifiable_type' => 'admin',
                            'notifiable_id'   => $userId,
                            'data'            => $dataJson,
                            'read_at'         => null,
                            'created_at'      => $now,
                            'updated_at'      => $now,
                        ];
                    }
                    DB::table('notifications')->insert($insertRows);
                }
            }

            // 4. Update status broadcast
            $broadcast->update([
                'total_target_users' => $totalTargetUsers,
                'total_fcm_sent'     => $fcmSentCount,
                'status'             => 'sent',
            ]);

            Log::info("ProcessSchoolBroadcastJob selesai: Broadcast {$broadcast->id} dikirim ke {$totalTargetUsers} akun, {$fcmSentCount} perangkat FCM aktif.");
        } catch (Throwable $e) {
            Log::error("ProcessSchoolBroadcastJob gagal: " . $e->getMessage());
            $broadcast->update([
                'status' => 'failed',
            ]);
            throw $e;
        }
    }
}
