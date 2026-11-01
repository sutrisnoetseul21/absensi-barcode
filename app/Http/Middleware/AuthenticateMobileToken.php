<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Autentikasi endpoint mobile (Notifikasi & FCM Token).
 *
 * Menerima:
 *  1. Sanctum Personal Access Token (format "id|plain").
 *  2. Fallback token Base64 ("userId|timestamp|random") yang diterbitkan AuthController
 *     saat tabel personal_access_tokens belum tersedia / sesi login lama di HP.
 *
 * Konsisten dengan resolveUser() pada controller mobile lain, sehingga user yang
 * sudah login sebelumnya tidak perlu login ulang hanya untuk membuka Pusat Notifikasi.
 */
class AuthenticateMobileToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $this->resolve($request);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi login tidak valid. Silakan logout lalu login kembali.',
            ], 401);
        }

        Auth::setUser($user);
        $request->setUserResolver(fn () => $user);

        return $next($request);
    }

    protected function resolve(Request $request): ?User
    {
        try {
            $sanctumUser = Auth::guard('sanctum')->user();
            if ($sanctumUser instanceof User) {
                return $sanctumUser;
            }
        } catch (\Throwable $e) {
            // Tabel personal_access_tokens belum ada -> lanjut ke fallback
        }

        $bearer = (string) $request->bearerToken();
        if ($bearer === '' || str_contains($bearer, '|')) {
            return null;
        }

        $decoded = base64_decode($bearer, true);
        if (!$decoded || !str_contains($decoded, '|')) {
            return null;
        }

        $userId = explode('|', $decoded)[0] ?? null;

        return $userId ? User::find($userId) : null;
    }
}
