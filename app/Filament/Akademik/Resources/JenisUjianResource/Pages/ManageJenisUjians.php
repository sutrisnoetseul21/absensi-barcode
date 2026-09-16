<?php

namespace App\Filament\Akademik\Resources\JenisUjianResource\Pages;

use App\Filament\Akademik\Resources\JenisUjianResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageJenisUjians extends ManageRecords
{
    protected static string $resource = JenisUjianResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
