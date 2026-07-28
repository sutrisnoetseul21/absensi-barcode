<?php

namespace App\Filament\Perpustakaan\Widgets;

use App\Models\Peminjaman;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class SirkulasiBulananChart extends ChartWidget
{
    protected ?string $heading = 'Statistik Sirkulasi (30 Hari Terakhir)';
    protected static ?int $sort = 1;

    protected function getData(): array
    {
        $data = [];
        $labels = [];

        $start = now()->subDays(29);
        
        $peminjamans = Peminjaman::where('tanggal_pinjam', '>=', $start->startOfDay())
            ->get()
            ->groupBy(function($date) {
                return Carbon::parse($date->tanggal_pinjam)->format('Y-m-d');
            });

        $pengembalians = Peminjaman::whereNotNull('tanggal_kembali')
            ->where('tanggal_kembali', '>=', $start->startOfDay())
            ->get()
            ->groupBy(function($date) {
                return Carbon::parse($date->tanggal_kembali)->format('Y-m-d');
            });

        $pinjamData = [];
        $kembaliData = [];

        for ($i = 0; $i < 30; $i++) {
            $date = $start->copy()->addDays($i)->format('Y-m-d');
            $labels[] = Carbon::parse($date)->format('d M');
            $pinjamData[] = isset($peminjamans[$date]) ? $peminjamans[$date]->count() : 0;
            $kembaliData[] = isset($pengembalians[$date]) ? $pengembalians[$date]->count() : 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Dipinjam',
                    'data' => $pinjamData,
                    'backgroundColor' => '#3b82f6', // blue
                    'borderColor' => '#3b82f6',
                ],
                [
                    'label' => 'Dikembalikan',
                    'data' => $kembaliData,
                    'backgroundColor' => '#10b981', // green
                    'borderColor' => '#10b981',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
