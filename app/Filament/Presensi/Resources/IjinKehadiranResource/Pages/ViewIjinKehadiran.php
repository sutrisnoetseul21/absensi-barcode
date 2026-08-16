<?php

namespace App\Filament\Presensi\Resources\IjinKehadiranResource\Pages;

use App\Filament\Presensi\Resources\IjinKehadiranResource;
use App\Models\Presensi;
use App\Models\EnrollmentSiswa;
use App\Services\KalenderSekolahService;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ViewIjinKehadiran extends ViewRecord
{
    protected static string $resource = IjinKehadiranResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('approve')
                ->label('Setujui (Approve)')
                ->color('success')
                ->icon('heroicon-o-check')
                ->visible(fn () => (auth()->user()?->isSuperAdmin() || auth()->user()?->hasRole('admin_presensi_editor')) && $this->record->status !== 'approved')
                ->requiresConfirmation()
                ->action(function () {
                    $record = $this->record;
                    $oldStatus = $record->status;
                    $user = auth()->user();

                    DB::beginTransaction();
                    try {
                        $record->update([
                            'status' => 'approved',
                            'approved_by' => $user->id,
                            'approved_by_type' => get_class($user),
                            'approved_at' => now(),
                        ]);

                        $record->recordLog('approved', "Status diubah dari '" . ucfirst($oldStatus) . "' ke 'Approved' oleh admin");

                        $service = app(\App\Services\LeaveRequestService::class);
                        $generatedCount = $service->syncAttendances($record);

                        DB::commit();

                        Notification::make()
                            ->title('Ijin Disetujui')
                            ->body("Berhasil meng-approve ijin dan men-generate $generatedCount record presensi.")
                            ->success()
                            ->send();

                    } catch (\Exception $e) {
                        DB::rollBack();
                        Notification::make()
                            ->title('Terjadi Kesalahan')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Actions\Action::make('reject')
                ->label('Tolak (Reject)')
                ->color('danger')
                ->icon('heroicon-o-x-mark')
                ->visible(fn () => (auth()->user()?->isSuperAdmin() || auth()->user()?->hasRole('admin_presensi_editor')) && $this->record->status !== 'rejected')
                ->requiresConfirmation()
                ->form([
                    \Filament\Forms\Components\Textarea::make('reason')
                        ->label('Alasan Penolakan')
                        ->required(),
                ])
                ->action(function (array $data) {
                    $record = $this->record;
                    $oldStatus = $record->status;
                    $user = auth()->user();

                    DB::beginTransaction();
                    try {
                        if ($oldStatus === 'approved') {
                            app(\App\Services\LeaveRequestService::class)->removeAttendances($record);
                        }

                        $record->update([
                            'status' => 'rejected',
                            'approved_by' => $user->id,
                            'approved_by_type' => get_class($user),
                            'approved_at' => now(),
                        ]);

                        $record->recordLog('rejected', "Status diubah dari '" . ucfirst($oldStatus) . "' ke 'Rejected'. Alasan: " . ($data['reason'] ?? '-'));

                        DB::commit();

                        Notification::make()
                            ->title('Ijin Ditolak')
                            ->success()
                            ->send();

                    } catch (\Exception $e) {
                        DB::rollBack();
                        Notification::make()
                            ->title('Terjadi Kesalahan')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Actions\Action::make('reset_pending')
                ->label('Kembalikan ke Pending')
                ->color('warning')
                ->icon('heroicon-o-arrow-path')
                ->visible(fn () => (auth()->user()?->isSuperAdmin() || auth()->user()?->hasRole('admin_presensi_editor')) && $this->record->status !== 'pending')
                ->requiresConfirmation()
                ->form([
                    \Filament\Forms\Components\Textarea::make('reason')
                        ->label('Alasan Pengembalian ke Pending')
                        ->required(),
                ])
                ->action(function (array $data) {
                    $record = $this->record;
                    $oldStatus = $record->status;

                    DB::beginTransaction();
                    try {
                        if ($oldStatus === 'approved') {
                            app(\App\Services\LeaveRequestService::class)->removeAttendances($record);
                        }

                        $record->update([
                            'status' => 'pending',
                            'approved_by' => null,
                            'approved_by_type' => null,
                            'approved_at' => null,
                        ]);

                        $record->recordLog('updated', "Status diubah dari '" . ucfirst($oldStatus) . "' ke 'Pending'. Alasan: " . ($data['reason'] ?? '-'));

                        DB::commit();

                        Notification::make()
                            ->title('Status Dikembalikan ke Pending')
                            ->success()
                            ->send();

                    } catch (\Exception $e) {
                        DB::rollBack();
                        Notification::make()
                            ->title('Terjadi Kesalahan')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
