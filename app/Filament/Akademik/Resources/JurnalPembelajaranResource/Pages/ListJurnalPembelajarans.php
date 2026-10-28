<?php

namespace App\Filament\Akademik\Resources\JurnalPembelajaranResource\Pages;

use App\Filament\Akademik\Resources\JurnalPembelajaranResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListJurnalPembelajarans extends ListRecords
{
    protected static string $resource = JurnalPembelajaranResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('buka_portal_guru')
                ->label('Buka Jurnal di Portal Guru')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->color('primary')
                ->url(fn () => route('portal-guru.jurnal-mengajar'))
                ->openUrlInNewTab(),
        ];
    }
}
