<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\TahunAjaran;
use App\Actions\GetPublicDashboardDataAction;

class AdminAttendanceChart extends ChartWidget
{
    protected static ?int $sort = 2;
    protected static ?string $heading_title = 'Tren Kehadiran (30 Hari Terakhir)'; // Use internal variable if needed, or override getHeading
    protected int | string | array $columnSpan = 'full';

    public function getHeading(): string
    {
        return 'Tren Kehadiran (30 Hari Terakhir)';
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

        $lineData = $data['lineData'] ?? ['labels' => [], 'data' => []];

        return [
            'datasets' => [
                [
                    'label' => 'Persentase Kehadiran (%)',
                    'data' => $lineData['data'],
                    'borderColor' => '#3b82f6',
                    'fill' => 'start',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                ],
            ],
            'labels' => $lineData['labels'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
