<?php

namespace App\Filament\Resources\ManajemenAksesPortalResource\Pages;

use App\Filament\Resources\ManajemenAksesPortalResource;
use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\ListRecords;

class ListManajemenAksesPortals extends ListRecords
{
    protected static string $resource = ManajemenAksesPortalResource::class;

    public function mount(): void
    {
        parent::mount();

        $this->redirect(UserResource::getUrl('index'), navigate: false);
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
