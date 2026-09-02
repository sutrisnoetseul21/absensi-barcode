<?php

namespace App\Filament\Resources\WebPrestasiResource\Pages;

use App\Filament\Resources\WebPrestasiResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageWebPrestasis extends ManageRecords
{
    protected static string $resource = WebPrestasiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->mutateFormDataUsing(function (array $data): array {
                    $data['tipe'] = 'prestasi';
                    return $data;
                }),
        ];
    }
}
