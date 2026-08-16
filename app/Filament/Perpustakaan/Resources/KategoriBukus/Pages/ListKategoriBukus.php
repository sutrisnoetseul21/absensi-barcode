<?php

namespace App\Filament\Perpustakaan\Resources\KategoriBukus\Pages;

use App\Filament\Perpustakaan\Resources\KategoriBukus\KategoriBukuResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListKategoriBukus extends ListRecords
{
    protected static string $resource = KategoriBukuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->visible(fn () => auth()->user()?->isSuperAdmin() || auth()->user()?->hasRole('admin_perpustakaan_editor') || auth()->user()?->hasRole('admin_master_editor')),
        ];
    }
}
