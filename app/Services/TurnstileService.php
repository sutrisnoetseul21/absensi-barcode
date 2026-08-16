<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class TurnstileService
{
    protected const VERIFY_URL = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    /**
     * Memverifikasi token Cloudflare Turnstile dari frontend.
     * Menggunakan pendekatan Fail-Closed (gagal verifikasi = tolak login).
     */
    public function verify(?string $token, ?string $ip = null): bool
    {
        if (blank($token)) {
            return false;
        }

        $secret = config('services.turnstile.secret_key');
        if (blank($secret)) {
            Log::error('Turnstile verification failed: TURNSTILE_SECRET_KEY is not configured in .env');
            return false; // Fail-closed
        }

        try {
            $response = Http::asForm()
                ->timeout(5) // Timeout 5 detik
                ->post(self::VERIFY_URL, [
                    'secret'   => $secret,
                    'response' => $token,
                    'remoteip' => $ip ?? request()->ip(),
                ]);

            if (!$response->successful()) {
                Log::warning('Turnstile verification HTTP error: ' . $response->status(), [
                    'body' => $response->body(),
                ]);
                return false; // Fail-closed pada HTTP 5xx / 4xx
            }

            $data = $response->json();
            $isSuccess = ($data['success'] ?? false) === true;

            if (!$isSuccess) {
                Log::warning('Turnstile token rejected by Cloudflare', [
                    'error_codes' => $data['error-codes'] ?? [],
                ]);
            }

            return $isSuccess;
        } catch (Throwable $e) {
            Log::error('Turnstile connection exception: ' . $e->getMessage(), [
                'ip' => $ip,
            ]);
            return false; // Fail-closed pada network error / timeout
        }
    }
}
