<?php
namespace App\Filament\Resources\WebAplikasiResource\Pages;
use App\Filament\Resources\WebAplikasiResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
class EditWebAplikasi extends EditRecord
{
    protected static string $resource = WebAplikasiResource::class;
    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
