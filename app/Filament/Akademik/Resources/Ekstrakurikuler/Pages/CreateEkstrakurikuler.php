<?php

namespace App\Filament\Akademik\Resources\Ekstrakurikuler\Pages;

use App\Filament\Akademik\Resources\Ekstrakurikuler\EkstrakurikulerResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEkstrakurikuler extends CreateRecord
{
    protected static string $resource = EkstrakurikulerResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

