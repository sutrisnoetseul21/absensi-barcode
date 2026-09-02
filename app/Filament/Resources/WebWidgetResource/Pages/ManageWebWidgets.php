<?php

namespace App\Filament\Resources\WebWidgetResource\Pages;

use App\Filament\Resources\WebWidgetResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageWebWidgets extends ManageRecords
{
    protected static string $resource = WebWidgetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
