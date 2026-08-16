<?php

namespace App\Filament\Perpustakaan\Pages;

use Filament\Pages\Page;
use App\Filament\Traits\HasSimplePageRoleAccess;

class ReservasiSegeraHadir extends Page
{
    use HasSimplePageRoleAccess;

    protected static function getModuleRolePrefix(): string
    {
        return 'perpustakaan';
    }

    protected static function requiresEditorRole(): bool
    {
        return true;
    }

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-bookmark-square';
    protected static ?string $navigationLabel = 'Reservasi';
    protected static ?string $title = 'Reservasi Eksemplar';
    protected static \UnitEnum|string|null $navigationGroup = 'Perpustakaan';
    protected static ?int $navigationSort = 8;

    protected string $view = 'filament.perpustakaan.pages.reservasi-segera-hadir';
}
