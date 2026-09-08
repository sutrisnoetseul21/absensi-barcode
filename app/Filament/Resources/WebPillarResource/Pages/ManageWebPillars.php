<?php

namespace App\Filament\Resources\WebPillarResource\Pages;

use App\Filament\Resources\WebPillarResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageWebPillars extends ManageRecords
{
    protected static string $resource = WebPillarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
