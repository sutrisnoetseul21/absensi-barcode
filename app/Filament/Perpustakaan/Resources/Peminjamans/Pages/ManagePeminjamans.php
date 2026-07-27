<?php

namespace App\Filament\Perpustakaan\Resources\Peminjamans\Pages;

use App\Filament\Perpustakaan\Resources\Peminjamans\PeminjamanResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManagePeminjamans extends ManageRecords
{
    protected static string $resource = PeminjamanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
