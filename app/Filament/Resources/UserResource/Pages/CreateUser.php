<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
    
    protected function afterCreate(): void
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
