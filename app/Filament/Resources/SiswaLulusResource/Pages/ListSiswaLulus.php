<?php

namespace App\Filament\Resources\SiswaLulusResource\Pages;

use App\Filament\Resources\SiswaLulusResource;
use Filament\Resources\Pages\ListRecords;

class ListSiswaLulus extends ListRecords
{
    protected static string $resource = SiswaLulusResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
