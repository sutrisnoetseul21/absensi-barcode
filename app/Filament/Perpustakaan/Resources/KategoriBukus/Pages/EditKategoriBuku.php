<?php

namespace App\Filament\Perpustakaan\Resources\KategoriBukus\Pages;

use App\Filament\Perpustakaan\Resources\KategoriBukus\KategoriBukuResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditKategoriBuku extends EditRecord
{
    protected static string $resource = KategoriBukuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
