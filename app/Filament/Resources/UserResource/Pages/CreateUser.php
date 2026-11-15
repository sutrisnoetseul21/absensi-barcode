<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\ManajemenAksesPortalResource;
use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\CreateRecord;
use Spatie\Permission\Models\Role;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function afterCreate(): void
    {
        $hasSuperAdminRole = $this->record->hasRole('super_admin');
        if ((bool) $this->record->is_super_admin !== $hasSuperAdminRole) {
            $this->record->forceFill(['is_super_admin' => $hasSuperAdminRole])->saveQuietly();
        }

        if ($this->record->isGuruUser()) {
            Role::firstOrCreate(['name' => 'wali_kelas', 'guard_name' => 'web']);
            if (! $this->record->hasRole('wali_kelas')) {
                $this->record->assignRole('wali_kelas');
            }
        }
    }

    protected function getRedirectUrl(): string
    {
        return ManajemenAksesPortalResource::getUrl('edit', ['record' => $this->getRecord()]);
    }
}
