<?php

namespace App\Filament\Resources\SpikapLaporanResource\Pages;

use App\Filament\Resources\SpikapLaporanResource;
use App\Models\SpikapLogStatus;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewSpikapLaporan extends ViewRecord
{
    protected static string $resource = SpikapLaporanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('tindak_lanjut')
                ->label('Tindak Lanjut / Ubah Status')
                ->icon('heroicon-o-pencil-square')
                ->color('primary')
                ->visible(fn () => auth()->user()?->isSuperAdmin()
                    || auth()->user()?->hasRole('super_admin')
                    || auth()->user()?->hasRole('spikap_admin')
                    || auth()->user()?->can('spikap.update_status'))
                ->form([
                    Select::make('status')
                        ->label('Ubah Status Penanganan')
                        ->options([
                            'diterima'          => 'Diterima',
                            'dalam_investigasi' => 'Dalam Investigasi',
                            'selesai'           => 'Selesai',
                        ])
                        ->default(fn () => $this->record->status)
                        ->required(),

                    Textarea::make('catatan')
                        ->label('Catatan Tindak Lanjut & Penanganan')
                        ->placeholder('Jelaskan langkah investigasi, mediasi, atau hasil penyelesaian kasus...')
                        ->required()
                        ->rows(4),
                ])
                ->action(function (array $data) {
                    $user = auth()->user();
                    $record = $this->record;
                    $oldStatus = $record->status;
                    $newStatus = $data['status'];
                    $catatan = $data['catatan'];

                    $updateData = ['status' => $newStatus];
                    if ($user->teacher) {
                        $updateData['last_handled_by'] = $user->teacher->id;
                    }
                    $record->update($updateData);

                    SpikapLogStatus::catatGuru(
                        $record->id,
                        $oldStatus,
                        $newStatus,
                        $catatan,
                        $user->id
                    );

                    try {
                        app(\App\Services\SpikapNotificationService::class)->sendStatusUpdateNotificationToSiswaDanOrtu(
                            $record,
                            $newStatus,
                            $catatan
                        );
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::error("SPIKAP: gagal kirim WA status update dari ViewSpikapLaporan: " . $e->getMessage());
                    }

                    Notification::make()
                        ->success()
                        ->title('Status Laporan Berhasil Diperbarui')
                        ->body("Status laporan #{$record->id} kini tercatat sebagai " . ucfirst(str_replace('_', ' ', $newStatus)))
                        ->send();
                }),

            DeleteAction::make()
                ->visible(fn () => auth()->user()?->isSuperAdmin()
                    || auth()->user()?->hasRole('super_admin')
                    || auth()->user()?->hasRole('spikap_admin')),
        ];
    }
}
