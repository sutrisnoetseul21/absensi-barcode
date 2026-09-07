<?php

namespace App\Filament\Resources\WebHalamanAkademikResource\Pages;

use App\Filament\Resources\WebHalamanAkademikResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWebHalamanAkademiks extends ListRecords
{
    protected static string $resource = WebHalamanAkademikResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
