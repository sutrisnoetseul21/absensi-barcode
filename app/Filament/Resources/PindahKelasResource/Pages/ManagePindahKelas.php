<?php

namespace App\Filament\Resources\PindahKelasResource\Pages;

use App\Filament\Resources\PindahKelasResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManagePindahKelas extends ManageRecords
{
    protected static string $resource = PindahKelasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Kosong, karena tambah siswa (enrollment) dilakukan dari Pendaftaran Kelas
        ];
    }
}
