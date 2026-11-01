<?php

namespace App\Services;

use App\Models\FcmDeviceToken;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Exception\Messaging\NotFound;
use Kreait\Firebase\Exception\Messaging\RegistrationTokenNotRegistered;
use Kreait\Firebase\Messaging\AndroidConfig;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Throwable;

class FcmPushService
{
    protected ?Messaging $messaging = null;
    protected bool $isConfigured = false;

    public function __construct()
    {
        $this->initializeMessaging();
    }

    /**
     * Inisialisasi Firebase Messaging secara aman.
     */
    protected function initializeMessaging(): void
    {
        try {
            $credentials = config('firebase.projects.app.credentials');
            if (empty($credentials)) {
                $credentials = env('FIREBASE_CREDENTIALS');
            }

            if (!empty($credentials) && is_string($credentials) && !str_starts_with(trim($credentials), '{')) {
                if (!file_exists($credentials)) {
                    $basePathCredentials = base_path($credentials);
                    if (file_exists($basePathCredentials)) {
                        $credentials = $basePathCredentials;
                    }
                }
            }

            if (!empty($credentials) && (file_exists($credentials) || (is_string($credentials) && str_starts_with(trim($credentials), '{')))) {
                $this->messaging = app('firebase.messaging');
                $this->isConfigured = true;
            } else {
                $this->isConfigured = false;
            }
        } catch (Throwable $e) {
            $this->isConfigured = false;
            Log::info("FcmPushService: Firebase credentials belum aktif/tersedia: " . $e->getMessage());
        }
    }

    /**
     * Cek apakah Firebase Messaging siap digunakan.
     */
    public function isConfigured(): bool
    {
        return $this->isConfigured && $this->messaging !== null;
    }

    /**
     * Kirim push notification ke satu token FCM spesifik (HTTP v1).
     *
     * @param string $token Token perangkat
     * @param string $title Judul notifikasi
     * @param string $body Isi notifikasi
     * @param array $data Data payload (seluruh value dipastikan bertipe string)
     * @return array Status pengiriman
     */
    public function sendToToken(string $token, string $title, string $body, array $data = [], int $ttlSeconds = 36000): array
    {
        if (!$this->isConfigured()) {
            Log::info("FcmPushService: Push di-skip (Firebase belum dikonfigurasi). Title: [{$title}] Body: [{$body}]");
            return [
                'success' => false,
                'status'  => 'skipped',
                'reason'  => 'Firebase credentials belum dikonfigurasi di server.',
            ];
        }

        // Pastikan seluruh data value bertipe string sesuai kontrak HTTP v1
        $stringData = [];
        foreach ($data as $k => $v) {
            $stringData[(string) $k] = is_scalar($v) ? (string) $v : json_encode($v);
        }

        try {
            $message = CloudMessage::new()
                ->withToken($token)
                ->withNotification(Notification::create($title, $body))
                ->withData($stringData)
                ->withAndroidConfig(AndroidConfig::fromArray([
                    'priority' => 'HIGH',
                    'ttl'      => "{$ttlSeconds}s",
                    'notification' => [
                        'channel_id' => 'high_importance_channel',
                        'sound' => 'default',
                        'default_vibrate_timings' => true,
                    ],
                ]));

            $response = $this->messaging->send($message);

            return [
                'success' => true,
                'status'  => 'sent',
                'result'  => $response,
            ];
        } catch (NotFound | RegistrationTokenNotRegistered $e) {
            // Token sudah usang / app di-uninstall, hapus otomatis dari database
            Log::info("FcmPushService: Token tidak aktif / unregistered, menghapus dari database: {$token}");
            FcmDeviceToken::where('token', $token)->delete();

            return [
                'success' => false,
                'status'  => 'unregistered',
                'error'   => $e->getMessage(),
            ];
        } catch (Throwable $e) {
            Log::error("FcmPushService error sending to token [{$token}]: " . $e->getMessage());

            return [
                'success' => false,
                'status'  => 'failed',
                'error'   => $e->getMessage(),
            ];
        }
    }

    /**
     * Kirim push notification ke seluruh token perangkat milik user tertentu.
     *
     * @param User $user
     * @param string $title
     * @param string $body
     * @param array $data
     * @return array Status agregat pengiriman
     */
    public function sendToUser(User $user, string $title, string $body, array $data = []): array
    {
        $tokens = FcmDeviceToken::where('user_id', $user->id)->pluck('token')->toArray();

        if (empty($tokens)) {
            return [
                'success' => true,
                'status'  => 'no_tokens',
                'count'   => 0,
            ];
        }

        $results = [];
        $successCount = 0;

        foreach ($tokens as $token) {
            $res = $this->sendToToken($token, $title, $body, $data);
            $results[] = $res;
            if (!empty($res['success'])) {
                $successCount++;
            }
        }

        return [
            'success' => $successCount > 0,
            'total'   => count($tokens),
            'sent'    => $successCount,
            'details' => $results,
        ];
    }
}
