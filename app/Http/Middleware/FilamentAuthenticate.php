<?php

namespace App\Http\Middleware;

use Filament\Http\Middleware\Authenticate as BaseAuthenticate;

class FilamentAuthenticate extends BaseAuthenticate
{
    /**
     * Redirect unauthenticated users to the unified login page.
     */
    protected function redirectTo($request): ?string
    {
        return route('login');
    }
}
