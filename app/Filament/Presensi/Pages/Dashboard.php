<?php

namespace App\Filament\Presensi\Pages;

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
    protected static ?string $title = 'Dashboard Presensi';

    protected function getHeaderActions(): array
    {
        $today = now('Asia/Jakarta');
        $kalenderService = app(KalenderSekolahService::class);
        $isHariSekolahGlobal = $kalenderService->isHariSekolah($today);

        if (!$isHariSekolahGlobal) {
            return [];
        }

        $dateStr = $today->translatedFormat('l, d F Y');

        return [
            Action::make('proses_alpa_massal')
                ->label('Tandai Alpa (Hari Ini)')
                ->icon('heroicon-o-exclamation-triangle')
                ->color($isHariSekolahGlobal ? 'warning' : 'danger')
                ->requiresConfirmation()
                ->modalHeading('Konfirmasi Proses Alpa Massal')
                ->modalDescription(new HtmlString(
                    $isHariSekolahGlobal
                        ? "Anda akan mencatat status <b>Alpa</b> untuk semua siswa aktif yang belum absen hari ini ({$dateStr}). Lanjutkan?"
                        : "<div class='text-danger-600 font-bold mb-2'>⚠️ PERINGATAN: Hari ini direkomendasikan sebagai HARI LIBUR / AKHIR PEKAN.</div>Anda yakin ingin tetap memproses Alpa untuk hari ini ({$dateStr})?"
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
                }),
        ];
    }
}
