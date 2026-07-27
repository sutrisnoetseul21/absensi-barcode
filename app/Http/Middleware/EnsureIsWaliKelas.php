<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureIsWaliKelas
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::guard('web')->check() || !Auth::user()->hasRole('wali_kelas') || Auth::user()->teacher === null) {
            return redirect('/portal-guru/login');
        }

        $user = Auth::user();

        if ($user && $user->must_change_password) {
            if (!$request->is('portal-guru/ganti-password') && !$request->is('portal-guru/logout')) {
                return redirect('/portal-guru/ganti-password');
            }
        }

        return $next($request);
    }
}
