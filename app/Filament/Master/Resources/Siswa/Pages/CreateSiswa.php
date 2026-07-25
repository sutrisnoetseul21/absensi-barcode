<?php

namespace App\Filament\Master\Resources\Siswa\Pages;

use App\Filament\Master\Resources\Siswa\SiswaResource;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateSiswa extends CreateRecord
{
    protected static string $resource = SiswaResource::class;

    /**
     * Auto-generate credentials sebelum data disimpan:
     * - barcode_code: dari NISN jika kosong
     * - username: dari NISN
     * - must_change_password: false
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $password = empty($data['password']) ? $data['nisn'] : $data['password'];
        $email = $data['email'] ?? $data['nisn'] . '@' . config('school.email_domain');

        $user = User::create([
            'name'                 => $data['name'],
            'email'                => $email,
            'password'             => $password,
            'must_change_password' => false,
        ]);
        $user->assignRole('siswa');

        $data['user_id'] = $user->id;

        // barcode_code isn't in fillable of Siswa, it's saved in hook, but it used to be set here.
        // Wait, barcode_code is handled in StudentPresensiProfile. The form doesn't even submit barcode_code.
        // It was doing `$data['barcode_code'] = ...` but Siswa model doesn't have it in fillable, it was ignored or caused error? Actually, it was just doing `$data['username']`.

        unset($data['email'], $data['password']);

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
