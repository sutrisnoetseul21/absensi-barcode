<?php

namespace App\Filament\Resources\Siswa\Pages;

use App\Filament\Resources\Siswa\SiswaResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

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
        $data['barcode_code']         = $data['barcode_code'] ?? $data['nisn'];
        $data['username']             = $data['nisn'];
        $data['must_change_password'] = false;

        return $data;
    }
}
