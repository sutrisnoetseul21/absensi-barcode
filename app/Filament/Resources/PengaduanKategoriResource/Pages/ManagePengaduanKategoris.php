<?php

namespace App\Filament\Resources\PengaduanKategoriResource\Pages;

use App\Filament\Resources\PengaduanKategoriResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManagePengaduanKategoris extends ManageRecords
{
    protected static string $resource = PengaduanKategoriResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
