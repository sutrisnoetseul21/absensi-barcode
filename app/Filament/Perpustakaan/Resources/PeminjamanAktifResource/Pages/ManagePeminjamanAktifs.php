<?php

namespace App\Filament\Perpustakaan\Resources\PeminjamanAktifResource\Pages;

use App\Filament\Perpustakaan\Resources\PeminjamanAktifResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManagePeminjamanAktifs extends ManageRecords
{
    protected static string $resource = PeminjamanAktifResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
