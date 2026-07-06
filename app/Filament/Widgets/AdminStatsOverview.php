<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\TahunAjaran;
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
        $month = $now->month;
        $year = $now->year;

        $action = app(GetPublicDashboardDataAction::class);
        $data = $action->execute($activeYear->id, $month, $year);

        $donut = $data['donutData'] ?? [];
        $allClasses = $data['allClasses'] ?? [];
        
        $totalHadir = ($donut['hadir'] ?? 0) + ($donut['telat'] ?? 0);
        $totalAlpa = $donut['alpa'] ?? 0;
        
        $totalSiswa = 0;
        foreach ($allClasses as $kelas) {
            $totalSiswa += $kelas['total_students'];
        }

        return [
            Stat::make('Total Siswa Aktif', $totalSiswa)
                ->description('Di tahun ajaran ' . $activeYear->name)
                ->color('primary')
                ->icon('heroicon-o-users'),
                
            Stat::make('Hadir Hari Ini', $totalHadir)
                ->description(now('Asia/Jakarta')->translatedFormat('l, d F Y'))
                ->color('success')
                ->icon('heroicon-o-check-circle'),
                
            Stat::make('Alpa Hari Ini', $totalAlpa)
                ->description('Tidak ada keterangan')
                ->color('danger')
                ->icon('heroicon-o-x-circle'),
        ];
    }
}
