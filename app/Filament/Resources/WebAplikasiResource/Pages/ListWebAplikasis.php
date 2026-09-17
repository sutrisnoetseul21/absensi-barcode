<?php
namespace App\Filament\Resources\WebAplikasiResource\Pages;
use App\Filament\Resources\WebAplikasiResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
class ListWebAplikasis extends ListRecords
{
    protected static string $resource = WebAplikasiResource::class;
    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
