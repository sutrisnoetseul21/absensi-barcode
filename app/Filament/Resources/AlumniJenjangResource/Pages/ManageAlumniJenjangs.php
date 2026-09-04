<?php

namespace App\Filament\Resources\AlumniJenjangResource\Pages;

use App\Filament\Resources\AlumniJenjangResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageAlumniJenjangs extends ManageRecords
{
    protected static string $resource = AlumniJenjangResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Jenjang')
                ->modalHeading('Tambah Jenjang Lanjutan'),
        ];
    }
}
