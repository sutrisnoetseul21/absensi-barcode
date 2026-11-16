<?php

namespace App\Filament\Akademik\Resources\Ekstrakurikuler\Pages;

use App\Filament\Akademik\Resources\Ekstrakurikuler\EkstrakurikulerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEkstrakurikuler extends EditRecord
{
    protected static string $resource = EkstrakurikulerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

