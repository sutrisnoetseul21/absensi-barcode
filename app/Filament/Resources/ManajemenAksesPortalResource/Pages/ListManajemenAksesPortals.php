<?php

namespace App\Filament\Resources\ManajemenAksesPortalResource\Pages;

use App\Filament\Resources\ManajemenAksesPortalResource;
use Filament\Resources\Pages\ListRecords;

class ListManajemenAksesPortals extends ListRecords
{
    protected static string $resource = ManajemenAksesPortalResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
