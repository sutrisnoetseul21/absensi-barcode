<?php

namespace App\Filament\Perpustakaan\Resources\KunjunganPerpustakaanResource\Pages;

use App\Filament\Perpustakaan\Resources\KunjunganPerpustakaanResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageKunjunganPerpustakaans extends ManageRecords
{
    protected static string $resource = KunjunganPerpustakaanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('buka_kiosk')
                ->label('Buka Kiosk Presensi Kunjungan')
                ->icon('heroicon-o-qr-code')
                ->color('success')
                ->url(route('perpustakaan.kunjungan'))
                ->openUrlInNewTab(),

            Actions\CreateAction::make()
                ->label('Tambah Manual')
                ->icon('heroicon-o-plus'),
        ];
    }
}
