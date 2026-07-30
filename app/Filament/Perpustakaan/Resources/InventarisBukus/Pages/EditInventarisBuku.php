<?php

namespace App\Filament\Perpustakaan\Resources\InventarisBukus\Pages;

use App\Filament\Perpustakaan\Resources\InventarisBukus\InventarisBukuResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditInventarisBuku extends EditRecord
{
    protected static string $resource = InventarisBukuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
