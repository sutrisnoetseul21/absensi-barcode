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
            \Filament\Actions\DeleteAction::make()
                ->before(function (\Filament\Actions\DeleteAction $action, TahunAjaran $record) {
                    if ($record->kelasAjarans()->count() > 0 || $record->enrollments()->count() > 0 || $record->absensis()->count() > 0) {
                        \Filament\Notifications\Notification::make()
                            ->warning()
                            ->title('Gagal menghapus!')
                            ->body('Tahun ajaran tidak dapat dihapus karena memiliki data kelas, siswa, atau presensi terkait.')
                            ->send();
                        $action->halt();
                    }
                }),
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
