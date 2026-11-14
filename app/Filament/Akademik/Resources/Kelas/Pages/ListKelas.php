<?php

namespace App\Filament\Akademik\Resources\Kelas\Pages;

use App\Filament\Akademik\Resources\Kelas\KelasResource;
use App\Filament\Traits\ManagesRombelStudents;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListKelas extends ListRecords
{
    use ManagesRombelStudents;

    protected static string $resource = KelasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Kelas')
                ->icon('heroicon-o-plus')
                ->visible(fn () => auth()->user()?->isSuperAdmin() || auth()->user()?->hasRole('admin_akademik_editor') || auth()->user()?->hasRole('admin_master_editor')),
        ];
    }
}
