<?php

namespace App\Filament\Perpustakaan\Resources\InventarisBukus\Pages;

use App\Filament\Perpustakaan\Resources\InventarisBukus\InventarisBukuResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListInventarisBukus extends ListRecords
{
    protected static string $resource = InventarisBukuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
