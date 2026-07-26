<?php

namespace App\Filament\Perpustakaan\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Peminjaman;
use App\Models\EksemplarBuku;
use Illuminate\Support\Carbon;

class PerpustakaanStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $bukuDipinjam = Peminjaman::where('status', 'dipinjam')->count();
        
        $bukuTerlambat = Peminjaman::where('status', 'dipinjam')
            ->where('tanggal_jatuh_tempo', '<', Carbon::now()->startOfDay())
            ->count();
            
        $eksemplarTersedia = EksemplarBuku::where('status', 'tersedia')->count();

        return [
            Stat::make('Buku Sedang Dipinjam', $bukuDipinjam)
                ->description('Total transaksi pinjam aktif')
                ->descriptionIcon('heroicon-m-book-open')
                ->color('primary'),
                
            Stat::make('Buku Terlambat', $bukuTerlambat)
                ->description('Melewati batas jatuh tempo')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($bukuTerlambat > 0 ? 'danger' : 'success'),
                
            Stat::make('Eksemplar Tersedia', $eksemplarTersedia)
                ->description('Total stok buku fisik di rak')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
        ];
    }
}
