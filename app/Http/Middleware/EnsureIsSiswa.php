<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureIsSiswa
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::guard('web')->check()) {
            return redirect('/portal-siswa/login');
        }

        $user = Auth::user();

        if (!$user->hasRole('super_admin')) {
            if (!$user->hasRole('siswa') || $user->student === null) {
                // Jika user adalah wali kelas
                if ($user->hasRole('wali_kelas')) {
                    return redirect('/portal-guru');
                }
                // Jika user adalah petugas perpustakaan
                if ($user->hasRole('petugas_perpustakaan') || $user->hasRole('admin_perpustakaan')) {
                    return redirect('/portal-perpustakaan');
                }
                // Jika user adalah petugas presensi
                if ($user->hasRole('petugas_presensi')) {
                    return redirect('/portal-presensi/scan');
                }
                abort(403, 'Akses ditolak. Anda tidak terdaftar sebagai Siswa.');
            }
        }

        $user = Auth::user();

        if ($user && $user->must_change_password) {
            if (!$request->is('portal-siswa/ganti-password') && !$request->is('portal-siswa/logout')) {
                return redirect('/portal-siswa/ganti-password');
            }
        }

        return $next($request);
    }
}
