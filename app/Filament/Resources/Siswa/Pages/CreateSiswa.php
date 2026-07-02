<?php

namespace App\Filament\Resources\Siswa\Pages;

use App\Filament\Resources\Siswa\SiswaResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateSiswa extends CreateRecord
{
    protected static string $resource = SiswaResource::class;

    /** Simpan sementara password plain-text untuk ditampilkan di notifikasi. */
    private string $generatedPassword = '';

    /**
     * Auto-generate credentials sebelum data disimpan:
     * - barcode_code: dari NISN jika kosong
     * - username: dari NISN
     * - password: random 8 karakter
     * - must_change_password: true
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->generatedPassword = Str::random(8);

        $data['barcode_code']         = $data['barcode_code'] ?? $data['nisn'];
        $data['username']             = $data['nisn'];
        $data['password']             = $this->generatedPassword; // otomatis di-hash via cast 'hashed'
        $data['must_change_password'] = true;

        return $data;
    }

    /**
     * Setelah siswa berhasil disimpan, tampilkan notifikasi berisi
     * username dan password default — hanya tampil 1x dan persistent.
     */
    protected function afterCreate(): void
    {
        Notification::make()
            ->title('Siswa berhasil ditambahkan')
            ->body(
                "**Simpan informasi login ini sekarang:**\n\n" .
                "Username/NISN: `{$this->record->username}`\n" .
                "Password: `{$this->generatedPassword}`\n\n" .
                "Siswa diwajibkan mengganti password saat login pertama kali."
            )
            ->success()
            ->persistent()
            ->send();
    }
}
