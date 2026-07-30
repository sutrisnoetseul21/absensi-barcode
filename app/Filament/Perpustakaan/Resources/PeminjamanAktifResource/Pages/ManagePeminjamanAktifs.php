<?php

namespace App\Filament\Perpustakaan\Resources\PeminjamanAktifResource\Pages;

use App\Filament\Perpustakaan\Resources\PeminjamanAktifResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ManagePeminjamanAktifs extends ManageRecords
{
    protected static string $resource = PeminjamanAktifResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('New Peminjaman')
                ->modalHeading('Tambah Transaksi Peminjaman')
                ->after(function ($record) {
                    if ($record->eksemplar_id) {
                        \App\Models\EksemplarBuku::where('id', $record->eksemplar_id)->update(['status' => 'dipinjam']);
                    }
                }),
        ];
    }

    public function getTabs(): array
    {
        return [
            'aktif' => Tab::make('Peminjaman Aktif')
                ->badge(fn () => \App\Models\Peminjaman::whereIn('status', ['dipinjam', 'terlambat'])->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('status', ['dipinjam', 'terlambat'])),
            'dikembalikan' => Tab::make('Dikembalikan')
                ->badge(fn () => \App\Models\Peminjaman::where('status', 'dikembalikan')->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'dikembalikan')),
            'semua' => Tab::make('Semua Transaksi')
                ->modifyQueryUsing(fn (Builder $query) => $query),
        ];
    }
}
