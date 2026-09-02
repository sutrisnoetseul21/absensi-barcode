<?php
namespace App\Filament\Resources\WebStatisticResource\Pages;
use App\Filament\Resources\WebStatisticResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
class ListWebStatistics extends ListRecords
{
    protected static string $resource = WebStatisticResource::class;
    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
