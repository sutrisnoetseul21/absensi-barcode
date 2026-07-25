<?php

namespace App\Filament\Master\Resources\Guru\Pages;

use App\Filament\Master\Resources\Guru\GuruResource;
use App\Helpers\UsernameHelper;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateGuru extends CreateRecord
{
    protected static string $resource = GuruResource::class;

    /** Simpan sementara password plain-text untuk ditampilkan di notifikasi. */
    private string $generatedPassword = '';

    /**
     * Auto-generate credentials sebelum data disimpan:
     * - username: dari NIP jika ada, atau nama lengkap tanpa gelar
     * - password: random 8 karakter
     * - must_change_password: true (wajib ganti saat login pertama)
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->generatedPassword = empty($data['password']) ? Str::random(8) : $data['password'];

        $email = $data['email'] ?? UsernameHelper::generateForGuru($data['name'], $data['nip'] ?? null) . '@' . config('school.email_domain');

        $user = User::create([
            'name'                 => $data['name'],
            'email'                => $email,
            'password'             => $this->generatedPassword,
            'must_change_password' => empty($data['password']),
        ]);
        $user->assignRole('wali_kelas');

        $data['user_id'] = $user->id;

        // Hapus field yang tidak ada di tabel teachers
        unset($data['email'], $data['password']);

        return $data;
    }

    /**
     * Setelah guru berhasil disimpan, tampilkan notifikasi berisi
     * email dan password default — hanya tampil 1x dan persistent.
     */
    protected function afterCreate(): void
    {
        Notification::make()
            ->title('Guru berhasil ditambahkan')
            ->body(
                "**Simpan informasi login ini sekarang:**\n\n" .
                "Email: `{$this->record->user->email}`\n" .
                "Password: `{$this->generatedPassword}`\n\n" .
                "Guru diwajibkan mengganti password saat login pertama kali."
            )
            ->success()
            ->persistent()
            ->send();
    }
}
