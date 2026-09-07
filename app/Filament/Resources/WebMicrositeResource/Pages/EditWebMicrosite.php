<?php

namespace App\Filament\Resources\WebMicrositeResource\Pages;

use App\Filament\Resources\WebMicrositeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWebMicrosite extends EditRecord
{
    protected static string $resource = WebMicrositeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
