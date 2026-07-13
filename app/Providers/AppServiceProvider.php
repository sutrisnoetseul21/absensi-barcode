<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(\Filament\Auth\Http\Responses\Contracts\LogoutResponse::class, \App\Http\Responses\LogoutResponse::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Morph Map untuk polymorphic manual_input_by di tabel attendances.
        // 'admin'      → User (admin Filament, tabel users)
        // 'wali_kelas' → Guru (wali kelas, tabel teachers)
        Relation::morphMap([
            'admin'      => \App\Models\User::class,
            'wali_kelas' => \App\Models\Guru::class,
        ]);
    }
}
