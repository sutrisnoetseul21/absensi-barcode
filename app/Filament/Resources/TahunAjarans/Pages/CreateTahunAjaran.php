<?php

namespace App\Filament\Resources\TahunAjarans\Pages;

use App\Filament\Resources\TahunAjarans\TahunAjaranResource;
use App\Models\PengaturanSekolah;
use App\Models\TahunAjaran;
use Filament\Resources\Pages\CreateRecord;

class CreateTahunAjaran extends CreateRecord
{
    protected static string $resource = TahunAjaranResource::class;

    /**
     * Setelah tahun ajaran baru disimpan:
     * - Jika status = 'aktif' → arsipkan semua tahun lain
     * - Sync PengaturanSekolah.academic_year_id_active
     *
     * Sumber kebenaran = kolom status di academic_years.
     * PengaturanSekolah.academic_year_id_active = shortcut/cache saja.
     */
    protected function afterCreate(): void
    {
        if ($this->record->status === 'aktif') {
            TahunAjaran::where('id', '!=', $this->record->id)
                ->update(['status' => 'arsip']);

            $settings = PengaturanSekolah::first();
            if ($settings) {
                $settings->update([
                    'academic_year_id_active' => $this->record->id,
                ]);
            } else {
                PengaturanSekolah::create([
                    'school_name' => 'Sistem Presensi',
                    'academic_year_id_active' => $this->record->id,
                ]);
            }
        }
    }
}
