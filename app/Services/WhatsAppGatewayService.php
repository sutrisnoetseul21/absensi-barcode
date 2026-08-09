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
            ])->post($endpoint, [
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
}
