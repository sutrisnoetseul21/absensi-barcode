<?php

namespace App\Filament\Master\Resources\Siswa\Pages;

use App\Filament\Master\Resources\Siswa\SiswaResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditSiswa extends EditRecord
{
    protected static string $resource = SiswaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $userData = [];
        if (!empty($data['email'])) {
            $userData['email'] = $data['email'];
        }
        if (!empty($data['password'])) {
            // Note: SiswaForm already hashes password in dehydrateStateUsing? No, we removed it in previous step!
            // Wait, we removed dehydrateStateUsing in SiswaForm? Let's check what we did.
            // Yes, we removed dehydrateStateUsing.
            $userData['password'] = $data['password'];
            $userData['must_change_password'] = false;
        }

        if (!empty($userData)) {
            $this->record->user->update($userData);
        }

        unset($data['email'], $data['password']);

        return $data;
    }
}
