<?php

namespace App\Filament\Akademik\Resources\GuruWali\Pages;

use App\Filament\Akademik\Resources\GuruWali\KelompokGuruWaliResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditKelompokGuruWali extends EditRecord
{
    protected static string $resource = KelompokGuruWaliResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
