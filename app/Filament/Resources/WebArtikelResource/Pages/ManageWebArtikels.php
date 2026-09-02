<?php

namespace App\Filament\Resources\WebArtikelResource\Pages;

use App\Filament\Resources\WebArtikelResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageWebArtikels extends ManageRecords
{
    protected static string $resource = WebArtikelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
