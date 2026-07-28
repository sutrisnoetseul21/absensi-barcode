<?php

namespace App\Filament\Perpustakaan\Resources\RiwayatPengembalianResource\Pages;

use App\Filament\Perpustakaan\Resources\RiwayatPengembalianResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageRiwayatPengembalians extends ManageRecords
{
    protected static string $resource = RiwayatPengembalianResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
