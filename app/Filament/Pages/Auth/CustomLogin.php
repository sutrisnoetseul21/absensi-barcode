<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\Login as BaseLogin;
use Illuminate\Contracts\Support\Htmlable;

class CustomLogin extends BaseLogin
{
    protected static string $layout = 'filament-panels::components.layout.base';
    protected string $view = 'filament.pages.auth.custom-login';

    public function getHeading(): string | Htmlable
    {
        return '';
    }
}
