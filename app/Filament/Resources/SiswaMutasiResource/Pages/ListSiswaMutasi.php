<?php

namespace App\Filament\Resources\SiswaMutasiResource\Pages;

use App\Filament\Resources\SiswaMutasiResource;
use Filament\Resources\Pages\ListRecords;

class ListSiswaMutasi extends ListRecords
{
    protected static string $resource = SiswaMutasiResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
