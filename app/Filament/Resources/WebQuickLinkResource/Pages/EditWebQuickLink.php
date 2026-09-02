<?php
namespace App\Filament\Resources\WebQuickLinkResource\Pages;
use App\Filament\Resources\WebQuickLinkResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
class EditWebQuickLink extends EditRecord
{
    protected static string $resource = WebQuickLinkResource::class;
    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
