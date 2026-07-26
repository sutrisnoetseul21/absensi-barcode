<?php

namespace App\Filament\Akademik\Resources\MataPelajarans\Pages;

use App\Filament\Akademik\Resources\MataPelajarans\MataPelajaranResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMataPelajarans extends ListRecords
{
    protected static string $resource = MataPelajaranResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
