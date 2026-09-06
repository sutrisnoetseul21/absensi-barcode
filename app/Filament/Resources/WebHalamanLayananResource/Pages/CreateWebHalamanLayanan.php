<?php

namespace App\Filament\Resources\WebHalamanLayananResource\Pages;

use App\Filament\Resources\WebHalamanLayananResource;
use Filament\Resources\Pages\CreateRecord;

class CreateWebHalamanLayanan extends CreateRecord
{
    protected static string $resource = WebHalamanLayananResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
