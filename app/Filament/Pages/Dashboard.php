<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Actions\Action;
use App\Services\KalenderSekolahService;
use App\Models\EnrollmentSiswa;
use App\Models\Presensi;
use App\Models\PengaturanSekolah;
use Filament\Notifications\Notification;
use Illuminate\Support\HtmlString;

class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'Dashboard';

    protected function getHeaderActions(): array
    {
        $today = now('Asia/Jakarta');
        $kalenderService = app(KalenderSekolahService::class);
        $isHariSekolahGlobal = $kalenderService->isHariSekolah($today);

        $actions = [
            Action::make('web_utama')
                ->label('Web Utama')
                ->icon('heroicon-o-globe-alt')
                ->color('gray')
                ->url(url('/'))
                ->openUrlInNewTab(),
            Action::make('pilih_portal')
                ->label('Pilih Portal ERP')
                ->icon('heroicon-o-squares-2x2')
                ->color('info')
                ->url(url('/pilih-portal')),
            Action::make('portal_web')
                ->label('Portal Web')
                ->icon('heroicon-o-computer-desktop')
                ->color('primary')
                ->url(url('/portal-web')),
        ];

        if ($isHariSekolahGlobal) {
            $dateStr = $today->translatedFormat('l, d F Y');

            $actions[] = Action::make('proses_alpa_massal')
                ->label('Tandai Alpa (Hari Ini)')
                ->icon('heroicon-o-exclamation-triangle')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Konfirmasi Proses Alpa Massal')
                ->modalDescription(new HtmlString(
                    "Anda akan mencatat status <b>Alpa</b> untuk semua siswa aktif yang belum absen hari ini ({$dateStr}). Lanjutkan?"
                ))
                ->modalSubmitActionLabel('Ya, Tetap Lanjutkan')
                ->action(function () {
                    $result = \App\Services\AlpaAutomationService::process('manual_admin');
                    
                    if ($result['status'] === 'success') {
                        if ($result['count'] > 0) {
                            Notification::make()
                                ->title('Sukses')
                                ->body($result['message'])
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Selesai')
                                ->body('Tidak ada siswa baru yang ditandai Alpa. (Semua sudah absen atau libur)')
                                ->info()
                                ->send();
                        }
                    } else {
                        $notification = Notification::make()->title(ucfirst($result['status']))->body($result['message']);
                        if ($result['status'] === 'error') {
                            $notification->danger();
                        } else {
                            $notification->info();
                        }
                        $notification->send();
                    }
                });
        }

        return $actions;
    }

    public function getWidgets(): array
    {
        return [
            \Filament\Widgets\AccountWidget::class,
            \App\Filament\Akademik\Widgets\AkademikStatsWidget::class,
            \App\Filament\Widgets\AdminStatsOverview::class,
            \App\Filament\Perpustakaan\Widgets\PerpustakaanStatsWidget::class,
            \App\Filament\Widgets\PresensiStatusDonutChart::class,
            \App\Filament\Widgets\AdminAttendanceChart::class,
            \App\Filament\Perpustakaan\Widgets\SirkulasiBulananChart::class,
            \App\Filament\Perpustakaan\Widgets\TerlambatKritisWidget::class,
            \App\Filament\Perpustakaan\Widgets\BukuTerpopulerWidget::class,
            \App\Filament\Widgets\ProblematicStudentsTable::class,
        ];
    }
}
