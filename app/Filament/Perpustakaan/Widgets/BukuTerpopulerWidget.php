<?php

namespace App\Filament\Perpustakaan\Widgets;

use App\Models\Buku;
use App\Models\Peminjaman;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class BukuTerpopulerWidget extends BaseWidget
{
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $heading = 'Buku Paling Sering Dipinjam (Bulan Ini)';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Buku::query()
                    ->select('bukus.*')
                    ->join('eksemplar_bukus', 'bukus.id', '=', 'eksemplar_bukus.buku_id')
                    ->join('peminjamans', 'eksemplar_bukus.id', '=', 'peminjamans.eksemplar_id')
                    ->whereMonth('peminjamans.tanggal_pinjam', now()->month)
                    ->whereYear('peminjamans.tanggal_pinjam', now()->year)
                    ->selectRaw('COUNT(peminjamans.id) as total_pinjam')
                    ->groupBy('bukus.id')
                    ->orderByDesc('total_pinjam')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('judul')
                    ->label('Judul Buku')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('penulis')
                    ->label('Penulis'),
                Tables\Columns\TextColumn::make('kategori.nama_kategori')
                    ->label('Kategori')
                    ->badge(),
                Tables\Columns\TextColumn::make('total_pinjam')
                    ->label('Jumlah Dipinjam')
                    ->numeric()
                    ->sortable(),
            ])
            ->paginated(false);
    }
}
