<?php

namespace App\Filament\Resources\WebFaqResource\Pages;

use App\Filament\Resources\WebFaqResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageWebFaqs extends ManageRecords
{
    protected static string $resource = WebFaqResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
