<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\TahunAjaran;
use App\Models\Presensi;
use App\Models\HariLibur;
use App\Actions\GetPublicDashboardDataAction;

class AdminStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $activeYear = TahunAjaran::where('status', 'aktif')->first();
        if (!$activeYear) {
            return [];
        }

        $now = now('Asia/Jakarta');
        $kalenderService = app(\App\Services\KalenderSekolahService::class);
        $isHariSekolah = $kalenderService->isHariSekolah($now);

        if (!$isHariSekolah) {
            $holiday = HariLibur::hariIni($now->toDateString())->first();
            $holidayName = $holiday ? $holiday->description : ($now->isWeekend() ? 'Akhir Pekan' : 'Hari Libur');

            return [
                Stat::make('Hari Ini Sekolah Libur', $holidayName)
                    ->description('Kios absensi dinonaktifkan sementara dan tidak ada pencatatan statistik kehadiran.')
                    ->color('warning')
                    ->icon('heroicon-o-calendar-days'),
            ];
        }

        $month = $now->month;
        $year = $now->year;

        $action = app(GetPublicDashboardDataAction::class);
        $data = $action->execute($activeYear->id, $month, $year);

        $donut = $data['donutData'] ?? [];
        $allClasses = $data['allClasses'] ?? [];
        
        $totalHadir = ($donut['hadir'] ?? 0) + ($donut['telat'] ?? 0);
        
        $totalSiswa = 0;
        foreach ($allClasses as $kelas) {
            $totalSiswa += $kelas['total_students'];
        }

        // Ambil data status presensi riil hari ini langsung dari database
        $todayStr = $now->toDateString();
        $statusCounts = Presensi::where('academic_year_id', $activeYear->id)
            ->where('date', $todayStr)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $dbSakit = $statusCounts->get('sakit', 0);
        $dbIzin = $statusCounts->get('izin', 0);
        $dbAlpa = $statusCounts->get('alpa', 0);
        
        // Jumlah siswa yang sudah tercatat di database hari ini (Hadir, Telat, Sakit, Izin, Alpa)
        $sudahPresensi = $statusCounts->sum();
        $belumPresensi = max(0, $totalSiswa - $sudahPresensi);

        return [
            Stat::make('Total Siswa Aktif', $totalSiswa)
                ->description('Di tahun ajaran ' . $activeYear->name)
                ->color('primary')
                ->icon('heroicon-o-users'),
                
            Stat::make('Hadir Hari Ini', $totalHadir)
                ->description(now('Asia/Jakarta')->translatedFormat('l, d F Y'))
                ->color('success')
                ->icon('heroicon-o-check-circle'),

            Stat::make('Belum Presensi', $belumPresensi)
                ->description('Belum melakukan scan / absen')
                ->color('warning')
                ->icon('heroicon-o-clock'),

            Stat::make('Sakit Hari Ini', $dbSakit)
                ->description('Sakit dengan keterangan')
                ->color('info')
                ->icon('heroicon-o-chat-bubble-bottom-center-text'),

            Stat::make('Izin Hari Ini', $dbIzin)
                ->description('Izin keperluan sekolah/keluarga')
                ->color('info')
                ->icon('heroicon-o-envelope'),

            Stat::make('Tanpa Keterangan (Alpa)', $dbAlpa)
                ->description('Tercatat Alpa di database')
                ->color('danger')
                ->icon('heroicon-o-x-circle'),
        ];
    }
}
