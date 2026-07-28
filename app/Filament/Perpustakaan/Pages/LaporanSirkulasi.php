<?php

namespace App\Filament\Perpustakaan\Pages;

use Filament\Pages\Page;
use App\Filament\Perpustakaan\Widgets\SirkulasiBulananChart;
use App\Filament\Perpustakaan\Widgets\BukuTerpopulerWidget;
use App\Filament\Perpustakaan\Widgets\TerlambatKritisWidget;

class LaporanSirkulasi extends Page
{
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-chart-bar-square';
    protected static ?string $navigationLabel = 'Laporan';
    protected static ?string $title = 'Laporan Sirkulasi';
    protected static \UnitEnum|string|null $navigationGroup = 'Laporan';
    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.perpustakaan.pages.laporan-sirkulasi';

    protected function getHeaderWidgets(): array
    {
        return [
            SirkulasiBulananChart::class,
            BukuTerpopulerWidget::class,
            TerlambatKritisWidget::class,
        ];
    }
}
