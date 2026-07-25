<?php

namespace App\Filament\Akademik\Resources\SiswaLulusResource\Pages;

use App\Filament\Akademik\Resources\SiswaLulusResource;
use Filament\Resources\Pages\ListRecords;

class ListSiswaLulus extends ListRecords
{
    protected static string $resource = SiswaLulusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \App\Filament\Akademik\Resources\Enrollment\Actions\LuluskanKelas9Action::make(),
            \App\Filament\Akademik\Resources\Enrollment\Actions\BatalkanKelulusanMassalAction::make(),
        ];
    }
}
