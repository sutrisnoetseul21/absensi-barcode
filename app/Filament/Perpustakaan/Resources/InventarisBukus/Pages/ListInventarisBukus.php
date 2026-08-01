<?php

namespace App\Filament\Perpustakaan\Resources\InventarisBukus\Pages;

use App\Filament\Perpustakaan\Resources\InventarisBukus\InventarisBukuResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Radio;
use Filament\Resources\Pages\ListRecords;

class ListInventarisBukus extends ListRecords
{
    protected static string $resource = InventarisBukuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('unduhInventaris')
                ->label('Unduh Inventaris')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->modalHeading('Unduh Buku Induk Inventaris')
                ->modalDescription('Pilih filter status dan format dokumen yang ingin diunduh.')
                ->modalWidth('md')
                ->form([
                    CheckboxList::make('status')
                        ->label('Filter Status')
                        ->options([
                            'aktif'      => '✅ Aktif',
                            'dibatalkan' => '❌ Dibatalkan',
                        ])
                        ->bulkToggleable()
                        ->helperText('Kosongkan untuk mengunduh semua status.')
                        ->columns(2),

                    Radio::make('format')
                        ->label('Format Unduhan')
                        ->options([
                            'pdf'   => '📄 PDF (A4 Landscape)',
                            'excel' => '📊 Excel (.xlsx)',
                        ])
                        ->default('pdf')
                        ->inline()
                        ->required(),
                ])
                ->action(function (array $data) {
                    $format       = $data['format'] ?? 'pdf';
                    $statusFilter = $data['status'] ?? [];

                    $routeName = $format === 'excel'
                        ? 'perpustakaan.inventaris-buku.excel'
                        : 'perpustakaan.inventaris-buku.pdf';

                    $params = [];
                    if (!empty($statusFilter)) {
                        $params['status'] = $statusFilter;
                    }

                    return redirect()->to(route($routeName, $params));
                }),

            CreateAction::make(),
        ];
    }
}
