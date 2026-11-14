<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureCanAccessSetup
{
    /**
     * Handle an incoming request — Middleware untuk Portal Setup & Instalasi.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::guard('web')->check()) {
            return redirect()->guest('/login');
        }

        $user = Auth::user();

        if (!$user->isSuperAdmin() && !$user->hasRole(['super_admin', 'admin_portal_setup'])) {
            abort(403, 'Anda tidak memiliki akses ke Portal Setup & Instalasi.');
        }

        return $next($request);
    }
}

