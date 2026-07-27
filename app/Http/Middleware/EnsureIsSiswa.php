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
        if (!Auth::guard('web')->check() || !Auth::user()->hasRole('siswa') || Auth::user()->student === null) {
            return redirect('/portal-siswa/login');
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
