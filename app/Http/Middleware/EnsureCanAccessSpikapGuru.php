<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureCanAccessSpikapGuru
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::guard('web')->check()) {
            return redirect('/portal-guru/login');
        }

        $user = Auth::user();

        // Cek hak akses menggunakan method terpusat canAccessSpikapGuru()
        if (!$user->canAccessSpikapGuru()) {
            abort(403, 'Akses ditolak. Anda tidak memiliki izin untuk mengakses modul SPIKAP Guru.');
        }

        if ($user && $user->must_change_password) {
            if (!$request->is('portal-guru/ganti-password') && !$request->is('portal-guru/logout')) {
                return redirect('/portal-guru/ganti-password');
            }
        }

        return $next($request);
    }
}
