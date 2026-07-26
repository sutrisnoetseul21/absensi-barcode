<?php

namespace App\Filament\Akademik\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Siswa;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\TahunAjaran;

class AkademikStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $activeYear = TahunAjaran::where('status', 'aktif')->first();
        
        $now = now('Asia/Jakarta');
        $kalenderService = app(\App\Services\KalenderSekolahService::class);
        $isHariSekolah = $kalenderService->isHariSekolah($now);

        if (!$isHariSekolah) {
            $holiday = \App\Models\HariLibur::hariIni($now->toDateString())->first();
            $holidayName = $holiday ? $holiday->description : ($now->isWeekend() ? 'Akhir Pekan' : 'Hari Libur');

            return [
                Stat::make('Hari Ini Sekolah Libur', $holidayName)
                    ->description('Kios presensi dinonaktifkan sementara dan tidak ada pencatatan statistik kehadiran.')
                    ->color('warning')
                    ->icon('heroicon-o-calendar-days'),
            ];
        }

        $yearName = $activeYear ? $activeYear->name : 'Tidak Ada';

        return [
            Stat::make('Tahun Ajaran Aktif', $yearName)
                ->description('Tahun Ajaran saat ini')
                ->icon('heroicon-o-calendar')
                ->color('primary'),
            Stat::make('Total Siswa', Siswa::where('status', 'aktif')->count())
                ->description('Siswa berstatus aktif')
                ->icon('heroicon-o-users')
                ->color('success'),
            Stat::make('Total Guru', Guru::count())
                ->description('Guru berstatus aktif')
                ->icon('heroicon-o-briefcase')
                ->color('info'),
            Stat::make('Total Kelas', Kelas::count())
                ->description('Semua kelas terdaftar')
                ->icon('heroicon-o-home-modern')
                ->color('warning'),
        ];
    }
}
