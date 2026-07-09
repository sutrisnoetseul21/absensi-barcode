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
            'auth.wali'  => \App\Http\Middleware\EnsureIsWaliKelas::class,
            'auth.siswa' => \App\Http\Middleware\EnsureIsSiswa::class,
        ]);

        $middleware->redirectUsersTo(function (Request $request) {
            if (Auth::guard('wali_kelas')->check()) {
                return '/wali-kelas';
            }
            if (Auth::guard('siswa')->check()) {
                return '/siswa';
            }
            return '/admin';
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
