<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');

        $middleware->alias([
            'auth.wali'   => \App\Http\Middleware\EnsureIsWaliKelas::class,
            'auth.siswa'  => \App\Http\Middleware\EnsureIsSiswa::class,
            'auth.perpus' => \App\Http\Middleware\EnsureIsPetugasPerpustakaan::class,
            'maintenance' => \App\Http\Middleware\CheckPortalMaintenance::class,
        ]);

        $middleware->redirectUsersTo(function (Request $request) {
            $user = Auth::guard('web')->user();
            if ($user && $user->hasRole('wali_kelas')) {
                return '/portal-guru';
            }
            if ($user && $user->hasRole('siswa')) {
                return '/portal-siswa';
            }
            if ($user && ($user->hasRole('petugas_perpustakaan') || $user->hasRole('admin_perpustakaan'))) {
                return '/portal-perpustakaan';
            }
            return '/admin';
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
