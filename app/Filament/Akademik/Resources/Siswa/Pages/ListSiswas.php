<?php

namespace App\Filament\Akademik\Resources\Siswa\Pages;

use App\Filament\Akademik\Resources\Siswa\SiswaResource;
use App\Services\Cbt\CbtServiceInterface;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListSiswas extends ListRecords
{
    protected static string $resource = SiswaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->visible(fn () => auth()->user()?->isSuperAdmin() || auth()->user()?->hasRole('admin_akademik_editor') || auth()->user()?->hasRole('admin_master_editor')),

            Action::make('sync_students_zencbt')
                ->label('Sinkronkan Siswa ke ZenCBT')
                ->icon('heroicon-o-users')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Sinkronkan Siswa Aktif ke ZenCBT')
                ->modalDescription(fn () => implode("\n", [
                    'Proses ini akan mengirim data semua siswa aktif ke ZenCBT.',
                    'Siswa baru akan didaftarkan, dan siswa lama akan diupdate (nama, rombel, dll).',
                    '',
                    '🔑 Format Password Siswa:',
                    '   {NamaKelas}{NISN}  →  contoh: 7A1000000001',
                    '',
                    'PENTING: Password siswa akan SELALU DIPERBARUI saat proses ini dijalankan.',
                    'Jika siswa naik kelas, passwordnya akan otomatis berubah mengikuti kelas terbarunya.',
                ]))
                ->action(function () {
                    try {
                        $cbtService = app(CbtServiceInterface::class);
                        $result = $cbtService->syncStudents();

                        $totalProcessed = $result['total_processed'] ?? 0;
                        $inserted = $result['inserted'] ?? 0;
                        $updated = $result['updated'] ?? 0;

                        Notification::make()
                            ->title('Sinkronisasi Siswa Berhasil')
                            ->body("Berhasil memproses {$totalProcessed} siswa. (Baru: {$inserted}, Diperbarui: {$updated})\n\nPassword diset ke format: Kelas + NISN.")
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Gagal Sinkronisasi Siswa')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                })
                ->visible(fn () => auth()->user()?->isSuperAdmin() || auth()->user()?->hasRole('admin_akademik_editor') || auth()->user()?->hasRole('admin_master_editor')),
        ];
    }
}
