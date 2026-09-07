<?php

namespace App\Filament\Resources\WebHalamanAkademikResource\Pages;

use App\Filament\Resources\WebHalamanAkademikResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWebHalamanAkademik extends EditRecord
{
    protected static string $resource = WebHalamanAkademikResource::class;

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
