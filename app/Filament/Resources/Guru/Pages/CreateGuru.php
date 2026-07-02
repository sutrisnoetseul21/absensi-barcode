<?php

namespace App\Filament\Resources\Guru\Pages;

use App\Filament\Resources\Guru\GuruResource;
use App\Helpers\UsernameHelper;
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
        $this->generatedPassword = Str::random(8);

        $data['username']             = UsernameHelper::generateForGuru($data['name'], $data['nip'] ?? null);
        $data['password']             = $this->generatedPassword; // otomatis di-hash via cast 'hashed'
        $data['must_change_password'] = true;

        return $data;
    }

    /**
     * Setelah guru berhasil disimpan, tampilkan notifikasi berisi
     * username dan password default — hanya tampil 1x dan persistent.
     */
    protected function afterCreate(): void
    {
        Notification::make()
            ->title('Guru berhasil ditambahkan')
            ->body(
                "**Simpan informasi login ini sekarang:**\n\n" .
                "Username: `{$this->record->username}`\n" .
                "Password: `{$this->generatedPassword}`\n\n" .
                "Guru diwajibkan mengganti password saat login pertama kali."
            )
            ->success()
            ->persistent()
            ->send();
    }
}
