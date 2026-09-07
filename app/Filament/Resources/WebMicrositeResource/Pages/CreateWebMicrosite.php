<?php

namespace App\Filament\Resources\WebMicrositeResource\Pages;

use App\Filament\Resources\WebMicrositeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateWebMicrosite extends CreateRecord
{
    protected static string $resource = WebMicrositeResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
