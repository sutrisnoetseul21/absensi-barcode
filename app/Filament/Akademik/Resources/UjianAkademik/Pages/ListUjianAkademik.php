<?php

namespace App\Filament\Akademik\Resources\UjianAkademik\Pages;

use App\Filament\Akademik\Resources\UjianAkademik\UjianAkademikResource;
use App\Services\Cbt\CbtServiceInterface;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListUjianAkademik extends ListRecords
{
    protected static string $resource = UjianAkademikResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('sync_students_zencbt')
                ->label('Sinkron Data Siswa')
                ->icon('heroicon-o-users')
                ->color('info')
                ->requiresConfirmation()
                ->modalHeading('Sinkronkan Data Siswa ke ZenCBT')
                ->modalDescription(fn () => implode("\n", [
                    'Sistem akan mengirim/memperbarui data seluruh siswa aktif ke ZenCBT.',
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
                }),

            Action::make('sync_exams_zencbt')
                ->label('Sinkron Jadwal CBT')
                ->icon('heroicon-o-calendar')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Tarik Jadwal dari ZenCBT')
                ->modalContent(function () {
                    try {
                        $cbtService = app(CbtServiceInterface::class);
                        $diff = $cbtService->previewSyncExams();
                        return view('filament.components.sync-exams-preview', ['diff' => $diff]);
                    } catch (\Exception $e) {
                        return new \Illuminate\Support\HtmlString('<div class="text-danger-600">Gagal mengambil pratinjau: ' . htmlspecialchars($e->getMessage()) . '</div>');
                    }
                })
                ->action(function () {
                    try {
                        $cbtService = app(CbtServiceInterface::class);
                        // Idealnya kita lempar payload dari preview, tapi untuk simplifikasi
                        // kita panggil syncExams() yang akan nge-fetch ulang (atau cache).
                        $result = $cbtService->syncExams();
                        
                        $total = $result['synced'] ?? 0;

                        Notification::make()
                            ->title('Sinkronisasi Jadwal Berhasil')
                            ->body("Berhasil memperbarui data {$total} jadwal ujian dari ZenCBT.")
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Gagal Sinkronisasi Jadwal')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Action::make('sync_master_to_zencbt')
                ->label('Sinkronkan Master ke ZenCBT')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Sinkronkan Data Master ke ZenCBT')
                ->modalDescription('Tahun Ajaran, Tingkat Kelas, Rombel, dan Mata Pelajaran aktif di Laravel akan dikirim ke engine ZenCBT.')
                ->action(function () {
                    try {
                        $cbtService = app(CbtServiceInterface::class);
                        $result = $cbtService->syncMasterData();

                        $totalPrograms = count($result['programs'] ?? []);
                        $totalLevels   = count($result['levels'] ?? []);
                        $totalGroups   = count($result['groups'] ?? []);
                        $totalMapel    = count($result['mapel'] ?? []);

                        Notification::make()
                            ->title('Sinkronisasi Master Berhasil')
                            ->body("Terkirim: {$totalPrograms} Tahun Ajaran, {$totalLevels} Tingkat, {$totalGroups} Rombel, dan {$totalMapel} Mapel.")
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Gagal Sinkronisasi Master')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
