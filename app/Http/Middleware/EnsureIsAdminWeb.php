<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureIsAdminWeb
{
    /**
     * Handle an incoming request — Middleware untuk Portal Web Sekolah.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::guard('web')->check()) {
            return redirect('/portal-web/login');
        }

        $user = Auth::user();

        if (!$user->hasRole(['admin_portal_web', 'super_admin'])) {
            // Redirect ke portal yang sesuai role-nya
            if ($user->hasRole('siswa')) {
                return redirect('/portal-siswa');
            }
            if ($user->hasRole('wali_kelas')) {
                return redirect('/portal-guru');
            }
            if ($user->hasRole(['petugas_perpustakaan', 'admin_perpustakaan'])) {
                return redirect('/portal-perpustakaan');
            }
            if ($user->hasRole(['admin_portal_presensi', 'petugas_presensi'])) {
                return redirect('/portal-presensi');
            }
            abort(403, 'Anda tidak memiliki akses ke Portal Web Sekolah.');
        }

        return $next($request);
    }
}
