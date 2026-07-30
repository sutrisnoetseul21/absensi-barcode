<?php

namespace App\Filament\Perpustakaan\Resources\Bukus\Pages;

use App\Filament\Perpustakaan\Resources\Bukus\BukuResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListBukus extends ListRecords
{
    protected static string $resource = BukuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'aktif' => Tab::make('Buku Aktif')
                ->modifyQueryUsing(fn (Builder $query) => $query->withoutTrashed()),
            'trashed' => Tab::make('Sampah / Dihapus')
                ->badge(fn () => \App\Models\Buku::onlyTrashed()->count())
                ->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query) => $query->onlyTrashed()),
        ];
    }
}
