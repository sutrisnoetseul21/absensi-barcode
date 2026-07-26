<?php

namespace App\Filament\Akademik\Resources\Jabatans\Pages;

use App\Filament\Akademik\Resources\Jabatans\JabatanResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditJabatan extends EditRecord
{
    protected static string $resource = JabatanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
