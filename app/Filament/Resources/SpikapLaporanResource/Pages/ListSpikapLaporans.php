<?php

namespace App\Filament\Resources\SpikapLaporanResource\Pages;

use App\Filament\Resources\SpikapLaporanResource;
use Filament\Resources\Pages\ListRecords;

class ListSpikapLaporans extends ListRecords
{
    protected static string $resource = SpikapLaporanResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
