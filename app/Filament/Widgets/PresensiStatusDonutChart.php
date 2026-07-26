<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\TahunAjaran;
use App\Actions\GetPublicDashboardDataAction;

class PresensiStatusDonutChart extends ChartWidget
{
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';

    public function getHeading(): string
    {
        return 'Status Kehadiran Hari Ini';
    }

    protected function getData(): array
    {
        $activeYear = TahunAjaran::where('status', 'aktif')->first();
        if (!$activeYear) {
            return [
                'datasets' => [],
                'labels' => [],
            ];
        }

        $now = now('Asia/Jakarta');
        $action = app(GetPublicDashboardDataAction::class);
        $data = $action->execute($activeYear->id, $now->month, $now->year);

        $donut = $data['donutData'] ?? [];

        return [
            'datasets' => [
                [
                    'label' => 'Total Siswa',
                    'data' => [
                        $donut['hadir'] ?? 0,
                        $donut['telat'] ?? 0,
                        $donut['sakit'] ?? 0,
                        $donut['izin'] ?? 0,
                        $donut['alpa'] ?? 0,
                        $donut['belum_absen'] ?? 0,
                    ],
                    'backgroundColor' => [
                        '#10b981', // emerald-500
                        '#3b82f6', // blue-500
                        '#f59e0b', // amber-500
                        '#6366f1', // indigo-500
                        '#ef4444', // red-500
                        '#9ca3af', // gray-400
                    ],
                ],
            ],
            'labels' => ['Hadir', 'Telat', 'Sakit', 'Izin', 'Alpa', 'Belum Absen'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}

