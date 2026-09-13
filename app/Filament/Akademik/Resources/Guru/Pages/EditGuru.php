<?php

namespace App\Filament\Akademik\Resources\Guru\Pages;

use App\Filament\Akademik\Resources\Guru\GuruResource;
use App\Helpers\UsernameHelper;
use App\Models\KelompokGuruWali;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Str;

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
        if (!empty($data['name'])) {
            $userData['name'] = $data['name'];
        }
        if (!empty($data['password'])) {
            $userData['password'] = $data['password'];
            $userData['must_change_password'] = false;
        }

        $user = $this->record->user;

        if ($user) {
            if (!empty($userData)) {
                $user->update($userData);
            }
            if (!$user->hasRole('wali_kelas')) {
                $user->assignRole('wali_kelas');
            }
            if (empty($user->teacher_id)) {
                $user->update(['teacher_id' => $this->record->id]);
            }
        } else {
            // Guru belum memiliki akun user yang terhubung
            $email = !empty($data['email'])
                ? $data['email']
                : (UsernameHelper::generateForGuru($data['name'] ?? $this->record->name, $data['nip'] ?? $this->record->nip) . '@' . config('school.email_domain'));

            $user = User::where('email', $email)->first();

            if ($user) {
                $updateFields = [
                    'name' => $data['name'] ?? $this->record->name,
                ];
                if (!empty($data['password'])) {
                    $updateFields['password'] = $data['password'];
                    $updateFields['must_change_password'] = false;
                }
                if (empty($user->teacher_id)) {
                    $updateFields['teacher_id'] = $this->record->id;
                }
                $user->update($updateFields);
            } else {
                $user = User::create([
                    'name'                 => $data['name'] ?? $this->record->name,
                    'email'                => $email,
                    'password'             => !empty($data['password']) ? $data['password'] : Str::random(8),
                    'must_change_password' => false,
                    'teacher_id'           => $this->record->id,
                ]);
            }

            if (!$user->hasRole('wali_kelas')) {
                $user->assignRole('wali_kelas');
            }

            $data['user_id'] = $user->id;
            $this->record->user_id = $user->id;
        }

        unset($data['email'], $data['password']);

        return $data;
    }

    protected function afterSave(): void
    {
        KelompokGuruWali::firstOrCreate(
            ['teacher_id' => $this->record->id],
            [
                'nama_kelompok' => 'Kelompok ' . $this->record->name,
                'status_aktif'  => true,
            ]
        );
    }
}
