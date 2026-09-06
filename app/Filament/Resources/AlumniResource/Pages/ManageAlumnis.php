<?php

namespace App\Filament\Resources\AlumniResource\Pages;

use App\Filament\Resources\AlumniResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Filament\Schemas\Components\Tabs\Tab;

class ManageAlumnis extends ManageRecords
{
    protected static string $resource = AlumniResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Data Alumni')
                ->modalHeading('Tambah Data Alumni')
                ->modalWidth('2xl'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'semua' => Tab::make('Semua Alumni')
                ->badge(\App\Models\Alumni::count()),
            'sistem' => Tab::make('Lulusan Sistem')
                ->badge(\App\Models\Alumni::where('source', 'sistem')->count())
                ->modifyQueryUsing(fn ($query) => $query->where('source', 'sistem')),
            'mandiri' => Tab::make('Alumni Lama (Web)')
                ->badge(\App\Models\Alumni::where('source', 'web_mandiri')->count())
                ->modifyQueryUsing(fn ($query) => $query->where('source', 'web_mandiri')),
        ];
    }
}
