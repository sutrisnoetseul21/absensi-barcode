<?php

namespace App\Notifications\Channels;

use App\Models\FcmDeviceToken;
use App\Models\FcmNotificationLog;
use App\Models\User;
use App\Services\FcmPushService;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Throwable;

class FcmChannel
{
    public function __construct(
        protected FcmPushService $fcmService
    ) {}

    /**
     * Kirim notifikasi melalui FCM HTTP v1 dan catat ke FcmNotificationLog.
     *
     * @param mixed $notifiable
     * @param Notification $notification
     * @return void
     */
    public function send(mixed $notifiable, Notification $notification): void
    {
        if (!method_exists($notification, 'toFcm')) {
            return;
        }

        $fcmMessage = $notification->toFcm($notifiable);
        if (empty($fcmMessage)) {
            return;
        }

        $title = $fcmMessage['title'] ?? 'Pemberitahuan';
        $body = $fcmMessage['body'] ?? '';
        $data = $fcmMessage['data'] ?? [];
        $type = $fcmMessage['type'] ?? ($data['type'] ?? 'general');
        $relatedId = (string) ($fcmMessage['related_id'] ?? ($data['id'] ?? ''));
        $eventStatus = (string) ($fcmMessage['event_status'] ?? '');

        // Resolve target user
        $user = $notifiable instanceof User ? $notifiable : null;
        $userId = $user?->id;

        // Dedup guard menggunakan tabel fcm_notification_logs
        if ($relatedId !== '' && $eventStatus !== '') {
            $alreadyLogged = FcmNotificationLog::where('type', $type)
                ->where('related_id', $relatedId)
                ->where('event_status', $eventStatus)
                ->whereIn('status', ['pending', 'sent'])
                ->exists();

            if ($alreadyLogged) {
                Log::info("FcmChannel: Dedup skip untuk [{$type}:{$relatedId}:{$eventStatus}]");
                return;
            }
        }

        // Ambil seluruh token perangkat aktif milik user
        $tokens = [];
        if ($user) {
            $tokens = FcmDeviceToken::where('user_id', $user->id)->pluck('token')->toArray();
        }

        // Catat log pengiriman awal
        $log = FcmNotificationLog::create([
            'user_id'      => $userId,
            'fcm_token'    => !empty($tokens) ? implode(',', $tokens) : null,
            'type'         => $type,
            'related_id'   => $relatedId ?: '-',
            'event_status' => $eventStatus ?: '-',
            'title'        => $title,
            'body'         => $body,
            'payload'      => $data,
            'status'       => 'pending',
        ]);

        if (empty($tokens)) {
            $log->update([
                'status'        => 'sent',
                'error_message' => 'No active device tokens registered.',
                'sent_at'       => now(),
            ]);
            return;
        }

        // Pastikan judul dan isi juga masuk ke dalam data payload agar klien Android
        // tetap dapat menampilkan pop-up notifikasi secara konsisten (Foreground & Background)
        $data['title'] = $title;
        $data['body'] = $body;

        // Kirim via FcmPushService
        try {
            $hasSuccess = false;
            $errors = [];

            foreach ($tokens as $token) {
                $res = $this->fcmService->sendToToken($token, $title, $body, $data);
                if (!empty($res['success'])) {
                    $hasSuccess = true;
                } elseif (!empty($res['error'])) {
                    $errors[] = $res['error'];
                }
            }

            $log->update([
                'status'        => $hasSuccess ? 'sent' : ($this->fcmService->isConfigured() ? 'failed' : 'sent'),
                'error_message' => !empty($errors) ? implode('; ', $errors) : null,
                'sent_at'       => now(),
            ]);
        } catch (Throwable $e) {
            Log::error("FcmChannel exception: " . $e->getMessage());
            $log->update([
                'status'        => 'failed',
                'error_message' => $e->getMessage(),
            ]);
        }
    }
}
