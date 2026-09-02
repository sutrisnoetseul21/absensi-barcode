<?php
namespace App\Filament\Resources\WebStatisticResource\Pages;
use App\Filament\Resources\WebStatisticResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
class EditWebStatistic extends EditRecord
{
    protected static string $resource = WebStatisticResource::class;
    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
