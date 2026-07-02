<?php

namespace App\Filament\Resources\TahunAjarans\Pages;

use App\Filament\Resources\TahunAjarans\TahunAjaranResource;
use App\Models\PengaturanSekolah;
use App\Models\TahunAjaran;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTahunAjaran extends EditRecord
{
    protected static string $resource = TahunAjaranResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    /**
     * Setelah tahun ajaran diupdate:
     * - Jika status = 'aktif' → arsipkan semua tahun lain
     * - Sync PengaturanSekolah.academic_year_id_active
     */
    protected function afterSave(): void
    {
        if ($this->record->status === 'aktif') {
            TahunAjaran::where('id', '!=', $this->record->id)
                ->update(['status' => 'arsip']);

            PengaturanSekolah::updateOrCreate([], [
                'academic_year_id_active' => $this->record->id,
            ]);
        }
    }
}
