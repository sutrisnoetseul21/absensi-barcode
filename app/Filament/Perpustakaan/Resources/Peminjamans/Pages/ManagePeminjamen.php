<?php

namespace App\Filament\Perpustakaan\Resources\Peminjamen\Pages;

use App\Filament\Perpustakaan\Resources\Peminjamen\PeminjamanResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManagePeminjamen extends ManageRecords
{
    protected static string $resource = PeminjamanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
