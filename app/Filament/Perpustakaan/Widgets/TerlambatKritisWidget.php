<?php

namespace App\Filament\Perpustakaan\Widgets;

use App\Models\Peminjaman;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class TerlambatKritisWidget extends BaseWidget
{
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $heading = 'Daftar Keterlambatan Kritis (> 3 Hari)';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Peminjaman::query()
                    ->where('status', 'dipinjam')
                    ->where('tanggal_jatuh_tempo', '<=', now()->subDays(3)->startOfDay())
                    ->orderBy('tanggal_jatuh_tempo', 'asc')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('peminjam.name')
                    ->label('Peminjam'),
                Tables\Columns\TextColumn::make('peminjam_type')
                    ->label('Tipe')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'siswa' => 'Siswa',
                        'guru' => 'Guru',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'siswa' => 'info',
                        'guru' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('eksemplar.buku.judul')
                    ->label('Buku'),
                Tables\Columns\TextColumn::make('tanggal_jatuh_tempo')
                    ->label('Jatuh Tempo')
                    ->date('d M Y'),
                Tables\Columns\TextColumn::make('keterlambatan')
                    ->label('Terlambat')
                    ->getStateUsing(fn (Peminjaman $record): string => Carbon::parse($record->tanggal_jatuh_tempo)->diffInDays(now()) . ' Hari')
                    ->color('danger')
                    ->weight('bold'),
            ])
            ->paginated(false);
    }
}
