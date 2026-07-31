<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureIsPetugasPerpustakaan
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::guard('web')->check()) {
            return redirect('/portal-perpustakaan/login');
        }

        $user = Auth::user();

        if (!$user->hasRole(['petugas_perpustakaan', 'admin_perpustakaan', 'super_admin'])) {
            // Jika user adalah siswa atau wali kelas, arahkan ke dashboard masing-masing
            if ($user->hasRole('siswa')) {
                return redirect('/portal-siswa');
            }
            if ($user->hasRole('wali_kelas')) {
                return redirect('/portal-guru');
            }
            // Jika tidak, tolak akses
            abort(403, 'Anda tidak memiliki akses sebagai Petugas Perpustakaan.');
        }

        if ($user && $user->must_change_password) {
            if (!$request->is('portal-perpustakaan/ganti-password') && !$request->is('portal-perpustakaan/logout')) {
                return redirect('/portal-perpustakaan/ganti-password');
            }
        }

        return $next($request);
    }
}
