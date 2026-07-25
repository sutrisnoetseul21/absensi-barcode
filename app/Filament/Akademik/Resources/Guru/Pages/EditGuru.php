<?php

namespace App\Filament\Akademik\Resources\Guru\Pages;

use App\Filament\Akademik\Resources\Guru\GuruResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditGuru extends EditRecord
{
    protected static string $resource = GuruResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $userData = [];
        if (!empty($data['email'])) {
            $userData['email'] = $data['email'];
        }
        if (!empty($data['password'])) {
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
