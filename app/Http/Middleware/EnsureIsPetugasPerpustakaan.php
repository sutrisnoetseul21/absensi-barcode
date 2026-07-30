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

        if ($user && $user->must_change_password) {
            if (!$request->is('portal-perpustakaan/ganti-password') && !$request->is('portal-perpustakaan/logout')) {
                return redirect('/portal-perpustakaan/ganti-password');
            }
        }

        return $next($request);
    }
}
