<?php

namespace App\Filament\Resources\WebHalamanAkademikResource\Pages;

use App\Filament\Resources\WebHalamanAkademikResource;
use Filament\Resources\Pages\CreateRecord;

class CreateWebHalamanAkademik extends CreateRecord
{
    protected static string $resource = WebHalamanAkademikResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
