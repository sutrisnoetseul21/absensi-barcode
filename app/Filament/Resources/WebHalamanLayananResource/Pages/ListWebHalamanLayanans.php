<?php

namespace App\Filament\Resources\WebHalamanLayananResource\Pages;

use App\Filament\Resources\WebHalamanLayananResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWebHalamanLayanans extends ListRecords
{
    protected static string $resource = WebHalamanLayananResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Buat Halaman Layanan'),
        ];
    }
}
