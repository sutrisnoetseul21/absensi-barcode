<?php

namespace App\Services;

use App\Models\WhatsAppSetting;
use App\Models\WhatsAppNotificationLog;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class WhatsAppGatewayService
{
    /**
     * Mengirim pesan ke Evolution API.
     */
    public function sendMessage(
        string $toNumber, 
        string $message, 
        ?string $relatedType = null, 
        ?string $relatedId = null,
        string $recipientType = 'unknown',
        ?int $logId = null
    ): bool {
        // Mode Pengujian Aman SPIKAP (Mencegah pesan test terkirim ke WhatsApp fisik penerima saat testing)
        $isSpikapTestMode = filter_var(config('services.whatsapp.spikap_test_mode', env('SPIKAP_WA_TEST_MODE', false)), FILTER_VALIDATE_BOOLEAN);
        if ($isSpikapTestMode && $relatedType === 'spikap_laporan') {
            $status = 'sent';
            $payload = json_encode([
                'info' => 'MOCKED_SUCCESS: Mode pengujian aktif (SPIKAP_WA_TEST_MODE=true). Panggilan gateway WhatsApp dilewati.',
                'target_number' => $toNumber,
                'recipient_type' => $recipientType,
                'timestamp' => now()->toIso8601String(),
            ]);

            if ($logId) {
                WhatsAppNotificationLog::where('id', $logId)->update([
                    'status' => $status,
                    'response_payload' => $payload,
                    'sent_at' => now(),
                ]);
            } else {
                WhatsAppNotificationLog::create([
                    'module' => 'spikap',
                    'recipient_type' => $recipientType,
                    'recipient_number' => $toNumber,
                    'message' => $message,
                    'status' => $status,
                    'response_payload' => $payload,
                    'related_type' => $relatedType,
                    'related_id' => $relatedId,
                    'sent_at' => now(),
                ]);
            }

            return true;
        }

        $setting = WhatsAppSetting::current();

        if (!$setting->is_active) {
            return false;
        }

        // Cek Send Window
        $now = Carbon::now();
        $currentTime = $now->format('H:i:s');
        
        $start = $setting->send_window_start ?: '00:00:00';
        $end = $setting->send_window_end ?: '23:59:59';
        
        $isOutsideWindow = false;
        if ($start <= $end) {
            $isOutsideWindow = ($currentTime < $start || $currentTime > $end);
        } else {
            // Melintasi tengah malam (misal 22:00:00 s/d 06:00:00)
            $isOutsideWindow = ($currentTime < $start && $currentTime > $end);
        }

        if ($isOutsideWindow) {
            if ($logId) {
                WhatsAppNotificationLog::where('id', $logId)->update([
                    'status' => 'failed',
                    'response_payload' => json_encode(['error' => 'Di luar jam kirim (' . $start . ' - ' . $end . ')']),
                ]);
            } else {
                WhatsAppNotificationLog::create([
                    'module' => 'presensi',
                    'recipient_type' => $recipientType,
                    'recipient_number' => $toNumber,
                    'message' => $message,
                    'status' => 'failed',
                    'response_payload' => json_encode(['error' => 'Di luar jam kirim (' . $start . ' - ' . $end . ')']),
                    'related_type' => $relatedType,
                    'related_id' => $relatedId,
                    'sent_at' => null,
                ]);
            }
            return false;
        }

        // Terapkan delay jika di-set agar tidak terkena rate limit
        if ($setting->delay_between_messages_seconds > 0) {
            sleep($setting->delay_between_messages_seconds);
        }

        // Panggil Evolution API
        $endpoint = rtrim($setting->base_url, '/') . '/message/sendText/' . $setting->instance_name;
        
        try {
            $response = Http::withHeaders([
                'apikey' => $setting->api_key,
                'Content-Type' => 'application/json',
            ])->timeout(10)->post($endpoint, [
                'number' => $toNumber,
                'text' => $message,
                'delay' => 1200 // Optional evolution native delay animation
            ]);

            $status = $response->successful() ? 'sent' : 'failed';
            $payload = $response->body();
        } catch (\Exception $e) {
            $status = 'failed';
            $payload = json_encode(['error' => $e->getMessage()]);
        }

        // Simpan atau update log
        if ($logId) {
            WhatsAppNotificationLog::where('id', $logId)->update([
                'status' => $status,
                'response_payload' => $payload,
                'sent_at' => $status === 'sent' ? now() : null,
            ]);
        } else {
            WhatsAppNotificationLog::create([
                'module' => 'presensi',
                'recipient_type' => $recipientType,
                'recipient_number' => $toNumber,
                'message' => $message,
                'status' => $status,
                'response_payload' => $payload,
                'related_type' => $relatedType,
                'related_id' => $relatedId,
                'sent_at' => $status === 'sent' ? now() : null,
            ]);
        }

        return $status === 'sent';
    }

    /**
     * Mengambil daftar grup WhatsApp dari Evolution API.
     * Mengembalikan array asosiatif ['GROUP:id' => 'Grup: subject']
     */
    public static function getAvailableGroups(): array
    {
        $setting = WhatsAppSetting::current();
        if (!$setting || !$setting->is_active || !$setting->base_url || !$setting->api_key || !$setting->instance_name) {
            return [];
        }

        $cacheKey = 'wa_groups_' . $setting->instance_name;
        
        return \Illuminate\Support\Facades\Cache::remember($cacheKey, now()->addMinutes(15), function () use ($setting) {
            try {
                $endpoint = rtrim($setting->base_url, '/') . '/group/fetchAllGroups/' . $setting->instance_name . '?getParticipants=false';
                $response = Http::withHeaders([
                    'apikey' => $setting->api_key,
                ])->timeout(5)->get($endpoint);

                if ($response->successful()) {
                    $groups = [];
                    foreach ($response->json() as $group) {
                        if (isset($group['id'], $group['subject'])) {
                            $groups['GROUP:' . $group['id']] = 'Grup WA: ' . $group['subject'];
                        }
                    }
                    return $groups;
                }
            } catch (\Exception $e) {
                // Return empty array if request fails, don't throw exception
            }
            return [];
        });
    }
}
