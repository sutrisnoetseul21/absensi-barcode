<?php

namespace App\Filament\Perpustakaan\Resources\Bukus\Pages;

use App\Filament\Perpustakaan\Resources\Bukus\BukuResource;
use App\Models\KategoriBuku;
use App\Models\MataPelajaran;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Radio;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListBukus extends ListRecords
{
    protected static string $resource = BukuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('unduhKatalog')
                ->label('Unduh Katalog')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->modalHeading('Unduh Katalog Buku')
                ->modalDescription('Pilih koleksi dan format dokumen yang ingin diunduh.')
                ->modalWidth('lg')
                ->form(function () {
                    $kategoriOptions = KategoriBuku::orderBy('nama_kategori')
                        ->pluck('nama_kategori', 'id')
                        ->toArray();

                    $mapelOptions = MataPelajaran::orderBy('nama_mapel')
                        ->pluck('nama_mapel', 'id')
                        ->toArray();

                    // Cari ID kategori "Non Fiksi" untuk conditional visibility mapel
                    $nonFiksiId = KategoriBuku::all()
                        ->first(fn ($k) => strtolower(trim($k->nama_kategori)) === 'non fiksi')
                        ?->id;

                    return [
                        CheckboxList::make('kategori_ids')
                            ->label('Pilih Koleksi')
                            ->options($kategoriOptions)
                            ->bulkToggleable()
                            ->helperText('Kosongkan semua untuk mengunduh seluruh koleksi.')
                            ->live()
                            ->columns(3),

                        CheckboxList::make('mapel_ids')
                            ->label('Filter Mata Pelajaran (Non Fiksi)')
                            ->options($mapelOptions)
                            ->bulkToggleable()
                            ->helperText('Hanya berlaku saat koleksi "Non Fiksi" dipilih.')
                            ->visible(function ($get) use ($nonFiksiId) {
                                $selected = $get('kategori_ids') ?? [];
                                return $nonFiksiId && in_array($nonFiksiId, $selected);
                            })
                            ->columns(3),

                        Radio::make('format')
                            ->label('Format Unduhan')
                            ->options([
                                'pdf'   => '📄 PDF (A4 Landscape)',
                                'excel' => '📊 Excel (.xlsx)',
                            ])
                            ->default('pdf')
                            ->inline()
                            ->required(),
                    ];
                })
                ->action(function (array $data) {
                    $format      = $data['format'] ?? 'pdf';
                    $kategoriIds = $data['kategori_ids'] ?? [];
                    $mapelIds    = $data['mapel_ids'] ?? [];

                    $routeName = $format === 'excel'
                        ? 'perpustakaan.katalog-buku.excel'
                        : 'perpustakaan.katalog-buku.pdf';

                    $params = [];
                    if (!empty($kategoriIds)) {
                        $params['kategori_ids'] = $kategoriIds;
                    }
                    if (!empty($mapelIds)) {
                        $params['mapel_ids'] = $mapelIds;
                    }

                    return redirect()->to(route($routeName, $params));
                }),

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
