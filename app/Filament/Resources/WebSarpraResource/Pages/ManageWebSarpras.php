<?php

namespace App\Filament\Resources\WebSarpraResource\Pages;

use App\Filament\Resources\WebSarpraResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageWebSarpras extends ManageRecords
{
    protected static string $resource = WebSarpraResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
