<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->hidden(fn ($record): bool => $record->id === auth()->id()),
        ];
    }
    
    protected function afterSave(): void
    {
        $hasSuperAdminRole = $this->record->hasRole('super_admin');
        if ((bool) $this->record->is_super_admin !== $hasSuperAdminRole) {
            $this->record->forceFill(['is_super_admin' => $hasSuperAdminRole])->saveQuietly();
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
