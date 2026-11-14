<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureCanAccessSistem
{
    /**
     * Handle an incoming request — Middleware untuk Portal Sistem.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::guard('web')->check()) {
            return redirect()->guest('/login');
        }

        $user = Auth::user();
        $hasSistemRole = $user->isSuperAdmin()
            || $user->hasRole(['super_admin', 'admin_portal_sistem'])
            || $user->roles->contains(fn ($r) => str_starts_with($r->name, 'admin_akses'));

        if (!$hasSistemRole) {
            abort(403, 'Anda tidak memiliki akses ke Portal Sistem.');
        }

        return $next($request);
    }
}

