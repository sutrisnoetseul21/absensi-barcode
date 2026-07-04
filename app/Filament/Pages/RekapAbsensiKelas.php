<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class RekapAbsensiKelas extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected string $view = 'filament.pages.rekap-absensi-kelas';
    
    protected static string|\UnitEnum|null $navigationGroup = 'Laporan';
    protected static ?string $title = 'Rekap Absensi Kelas';
    protected static ?string $navigationLabel = 'Rekap Absensi Kelas';
}
