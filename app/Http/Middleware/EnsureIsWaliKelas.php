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
        if (!Auth::guard('wali_kelas')->check()) {
            return redirect('/wali-kelas/login');
        }

        $user = Auth::guard('wali_kelas')->user();

        if ($user && $user->must_change_password) {
            if (!$request->is('wali-kelas/ganti-password') && !$request->is('wali-kelas/logout')) {
                return redirect('/wali-kelas/ganti-password');
            }
        }

        return $next($request);
    }
}
