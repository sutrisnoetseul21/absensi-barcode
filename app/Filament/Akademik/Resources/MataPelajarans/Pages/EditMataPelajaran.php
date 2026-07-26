<?php

namespace App\Filament\Akademik\Resources\MataPelajarans\Pages;

use App\Filament\Akademik\Resources\MataPelajarans\MataPelajaranResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMataPelajaran extends EditRecord
{
    protected static string $resource = MataPelajaranResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
