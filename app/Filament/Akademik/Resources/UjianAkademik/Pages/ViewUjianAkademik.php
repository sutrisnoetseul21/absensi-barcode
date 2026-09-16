<?php

namespace App\Filament\Akademik\Resources\UjianAkademik\Pages;

use App\Filament\Akademik\Resources\UjianAkademik\UjianAkademikResource;
use App\Models\UjianAkademik;
use App\Services\Cbt\CbtServiceInterface;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewUjianAkademik extends ViewRecord
{
    protected static string $resource = UjianAkademikResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // ─── 1. Tombol Tarik Nilai dari ZenCBT ───────────────────────────
            Action::make('pull_results_from_zencbt')
                ->label('Tarik Nilai ZenCBT')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Tarik Hasil Ujian dari ZenCBT')
                ->modalDescription(fn () => 'Sistem akan mengambil perolehan nilai peserta dari engine ZenCBT (ID: ' . ($this->record->cbt_ujian_id ?? '?') . ') dan memperbarui rekapitulasi.')
                ->action(function () {
                    try {
                        $cbtService = app(CbtServiceInterface::class);
                        $cbtExamId  = (int) $this->record->cbt_ujian_id;

                        if (! $cbtExamId) {
                            throw new \Exception('ID Ujian ZenCBT belum ditentukan. Lakukan Sinkron Jadwal CBT terlebih dahulu dari halaman daftar Sinkron Nilai CBT.');
                        }

                        $results = $cbtService->pullExamResults($cbtExamId, $this->record);
                        $total   = count($results);

                        Notification::make()
                            ->title('Penarikan Nilai Berhasil')
                            ->body("Berhasil menarik & memproses {$total} data nilai peserta ujian.")
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Gagal Menarik Nilai')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            // ─── 2. Edit KKM & Jenis Asesmen ─────────────────────────────────
            Action::make('edit_kkm')
                ->label('Edit')
                ->icon('heroicon-o-pencil-square')
                ->color('gray')
                ->fillForm(fn (UjianAkademik $record): array => [
                    'kkm'           => $record->kkm,
                    'jenis_ujian_id' => $record->jenis_ujian_id,
                ])
                ->form([
                    \Filament\Forms\Components\Select::make('jenis_ujian_id')
                        ->label('Jenis Asesmen')
                        ->options(\App\Models\JenisUjian::pluck('nama', 'id'))
                        ->required()
                        ->native(false),

                    TextInput::make('kkm')
                        ->label('KKM (Kriteria Ketuntasan Minimal)')
                        ->numeric()
                        ->required()
                        ->minValue(0)
                        ->maxValue(100)
                        ->helperText('Nilai minimum untuk dinyatakan tuntas pada ujian ini.'),
                ])
                ->action(function (array $data): void {
                    $this->record->update([
                        'kkm'            => $data['kkm'],
                        'jenis_ujian_id' => $data['jenis_ujian_id'],
                    ]);

                    Notification::make()
                        ->title('Berhasil Diperbarui')
                        ->success()
                        ->send();
                }),
        ];
    }
}
