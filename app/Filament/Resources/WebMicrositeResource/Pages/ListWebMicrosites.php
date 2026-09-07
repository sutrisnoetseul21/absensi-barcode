<?php

namespace App\Filament\Resources\WebMicrositeResource\Pages;

use App\Filament\Resources\WebMicrositeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWebMicrosites extends ListRecords
{
    protected static string $resource = WebMicrositeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
