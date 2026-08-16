<?php

namespace App\Filament\Akademik\Resources\TahunAjarans\Pages;

use App\Filament\Akademik\Resources\TahunAjarans\TahunAjaranResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTahunAjarans extends ListRecords
{
    protected static string $resource = TahunAjaranResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->visible(fn () => auth()->user()?->isSuperAdmin() || auth()->user()?->hasRole('admin_master_editor') || auth()->user()?->hasRole('admin_akademik_editor')),
        ];
    }
}
