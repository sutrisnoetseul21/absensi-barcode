<?php

namespace App\Filament\Resources\WebHalamanLayananResource\Pages;

use App\Filament\Resources\WebHalamanLayananResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWebHalamanLayanan extends EditRecord
{
    protected static string $resource = WebHalamanLayananResource::class;

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
