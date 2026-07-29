<?php

namespace App\Filament\Perpustakaan\Resources\KlasifikasiDdcs\Pages;

use App\Filament\Perpustakaan\Resources\KlasifikasiDdcs\KlasifikasiDdcResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageKlasifikasiDdcs extends ManageRecords
{
    protected static string $resource = KlasifikasiDdcResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
