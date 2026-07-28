<?php

namespace App\Filament\Perpustakaan\Pages;

use Filament\Pages\Page;

class ReservasiSegeraHadir extends Page
{
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-bookmark-square';
    protected static ?string $navigationLabel = 'Reservasi';
    protected static ?string $title = 'Reservasi Eksemplar';
    protected static \UnitEnum|string|null $navigationGroup = 'Sirkulasi';
    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.perpustakaan.pages.reservasi-segera-hadir';
}
