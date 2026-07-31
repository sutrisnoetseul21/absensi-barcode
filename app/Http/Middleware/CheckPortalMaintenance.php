<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPortalMaintenance
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $portal): Response
    {
        // 1. Bypass untuk super_admin
        if (auth()->check() && auth()->user()->hasRole('super_admin')) {
            return $next($request);
        }

        // 2. Ambil pengaturan dari cache
        $settings = \Illuminate\Support\Facades\Cache::remember('public_pengaturan_sekolah', 3600, function () {
            return \App\Models\PengaturanSekolah::first();
        });

        if (!$settings) {
            return $next($request);
        }

        // 3. Cek status maintenance berdasarkan portal
        $isMaintenance = false;
        $pesan = 'Portal sedang dalam perbaikan rutin.';

        if ($portal === 'siswa' && $settings->maintenance_portal_siswa) {
            $isMaintenance = true;
            $pesan = $settings->welcome_message_siswa ?: $pesan;
        } elseif ($portal === 'guru' && $settings->maintenance_portal_guru) {
            $isMaintenance = true;
            $pesan = $settings->welcome_message_guru ?: $pesan;
        } elseif ($portal === 'perpustakaan' && $settings->maintenance_portal_perpustakaan) {
            $isMaintenance = true;
            $pesan = $settings->welcome_message_perpustakaan ?: $pesan;
        }

        // 4. Jika aktif, kembalikan response halaman khusus
        if ($isMaintenance) {
            // Pastikan view tidak crash jika file belum ada (untuk tahap dev)
            if (!view()->exists('errors.maintenance')) {
                abort(503, $pesan);
            }
            
            return response()->view('errors.maintenance', [
                'pesan' => $pesan,
                'portal' => $portal
            ])->setStatusCode(503);
        }

        return $next($request);
    }
}
