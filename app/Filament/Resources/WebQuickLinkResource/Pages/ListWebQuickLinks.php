<?php
namespace App\Filament\Resources\WebQuickLinkResource\Pages;
use App\Filament\Resources\WebQuickLinkResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
class ListWebQuickLinks extends ListRecords
{
    protected static string $resource = WebQuickLinkResource::class;
    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
