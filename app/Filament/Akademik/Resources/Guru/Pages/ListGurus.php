<?php

namespace App\Filament\Akademik\Resources\Guru\Pages;

use App\Filament\Akademik\Resources\Guru\GuruResource;
use App\Services\Cbt\CbtServiceInterface;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListGurus extends ListRecords
{
    protected static string $resource = GuruResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->visible(fn () => auth()->user()?->isSuperAdmin() || auth()->user()?->hasRole('admin_akademik_editor') || auth()->user()?->hasRole('admin_master_editor')),

            // ─── Sync Guru ke ZenCBT ──────────────────────────────────────
            Action::make('sync_teachers_to_cbt')
                ->label('Sync Guru ke ZenCBT')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
                ->visible(fn () => auth()->user()?->isSuperAdmin() || auth()->user()?->hasRole('admin_akademik_editor'))
                ->requiresConfirmation()
                ->modalHeading('Sinkronisasi Guru ke ZenCBT')
                ->modalDescription(fn () => implode("\n", [
                    'Sistem akan membuat/memperbarui akun guru di ZenCBT.',
                    '',
                    '📋 Format Password yang digunakan (hanya saat akun pertama kali dibuat):',
                    '  • Punya NIP  : {NIP}@03  →  contoh: 198501012010011001@03',
                    '  • Tanpa NIP  : Nama tanpa spasi  →  contoh: BudiSantoso',
                    '',
                    '⚠️  Password TIDAK berubah jika akun sudah pernah disinkronkan sebelumnya.',
                    '    Untuk ubah password, gunakan tombol "Reset Password CBT" di baris guru.',
                    '',
                    '📌  Akses kelas di ZenCBT menggunakan logika Ekspansi Tingkat:',
                    '    Guru yang mengajar 7A akan dapat akses ke SEMUA kelas tingkat 7.',
                ]))
                ->action(function (): void {
                    try {
                        $cbtService = app(CbtServiceInterface::class);
                        $result     = $cbtService->syncTeachers();

                        Notification::make()
                            ->title('Sinkronisasi Guru Berhasil')
                            ->body(
                                "Total diproses: {$result['total_processed']} guru | " .
                                "Baru: {$result['inserted']} | Diperbarui: {$result['updated']}"
                            )
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Gagal Sinkronisasi Guru')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
