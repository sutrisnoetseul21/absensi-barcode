<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureIsPetugasPresensi
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::guard('web')->check()) {
            return redirect('/portal-presensi/login');
        }

        $user = Auth::user();

        // Cek apakah route yang diakses adalah Kiosk (scan)
        if ($request->routeIs('kiosk.scan') || $request->routeIs('kiosk.scan-nis')) {
            if (!$user->hasRole(['petugas_presensi', 'admin_portal_presensi', 'super_admin', 'wali_kelas'])) {
                abort(403, 'Anda tidak memiliki akses ke Kiosk Presensi.');
            }
        } else {
            // Jika mengakses dashboard atau halaman manajemen presensi lainnya
            if (!$user->hasRole(['admin_portal_presensi', 'super_admin'])) {
                // Jika dia hanya petugas presensi (scanner), alihkan ke Kiosk
                if ($user->hasRole('petugas_presensi')) {
                    $scanMode = \App\Models\PengaturanSekolah::current()?->barcode_scan_mode === 'nis' ? 'kiosk.scan-nis' : 'kiosk.scan';
                    return redirect()->route($scanMode);
                }
                
                // Jika user adalah siswa atau wali kelas, arahkan ke dashboard masing-masing
                if ($user->hasRole('siswa')) {
                    return redirect('/portal-siswa');
                }
                if ($user->hasRole('wali_kelas')) {
                    return redirect('/portal-guru');
                }
                if ($user->hasRole('petugas_perpustakaan') || $user->hasRole('admin_perpustakaan')) {
                    return redirect('/portal-perpustakaan');
                }
                // Jika tidak, tolak akses
                abort(403, 'Anda tidak memiliki akses ke Portal Presensi.');
            }
        }

        return $next($request);
    }
}
