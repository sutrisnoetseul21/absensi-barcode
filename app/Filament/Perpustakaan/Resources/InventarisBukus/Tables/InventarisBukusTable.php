<?php

namespace App\Filament\Perpustakaan\Resources\InventarisBukus\Tables;

use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;

class InventarisBukusTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tanggal_masuk')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('no_inventaris')
                    ->label('No Inventaris')
                    ->fontFamily('mono')
                    ->weight('bold')
                    ->searchable(query: function ($query, string $search) {
                        return $query->where('no_inventaris', 'like', "%{$search}%")
                            ->orWhereHas('eksemplarBukus', fn ($q) => $q->where('kode_eksemplar', $search));
                    }),
                TextColumn::make('buku.judul')
                    ->label('Judul')
                    ->weight('bold')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('buku.penulis')
                    ->label('Pengarang')
                    ->searchable(),
                TextColumn::make('buku.penerbit')
                    ->label('Penerbit')
                    ->searchable(),
                TextColumn::make('buku.tahun_terbit')
                    ->label('Tahun Terbit')
                    ->alignCenter()
                    ->sortable(),
                TextColumn::make('asal')
                    ->label('Asal')
                    ->formatStateUsing(fn (string $state): string => ucwords(str_replace('_', ' ', $state))),
                TextColumn::make('buku.klasifikasiDdc.kode_ddc')
                    ->label('No Klasifikasi')
                    ->fontFamily('mono')
                    ->searchable(),
                TextColumn::make('harga')
                    ->label('Harga')
                    ->alignRight()
                    ->formatStateUsing(fn ($state) => $state > 0 ? 'Rp ' . number_format($state, 0, ',', '.') : '-')
                    ->sortable(),
                TextColumn::make('jumlah_eksemplar')
                    ->label('Jumlah Eksemplar')
                    ->alignCenter()
                    ->numeric()
                    ->sortable(),
                TextColumn::make('buku.isbn')
                    ->searchable()
                    ->hidden(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'aktif' => 'success',
                        'dibatalkan' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'aktif' => 'Aktif',
                        'dibatalkan' => 'Dibatalkan',
                    ])
                    ->label('Filter Status'),
            ])
            ->recordActions([
                Action::make('batalkan_entri')
                    ->label('Batalkan Entri')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->status === 'aktif' && (auth()->user()?->isSuperAdmin() || auth()->user()?->hasRole('admin_perpustakaan_editor') || auth()->user()?->hasRole('admin_master_editor')))
                    ->form([
                        Textarea::make('alasan_pembatalan')
                            ->label('Alasan Pembatalan')
                            ->required()
                    ])
                    ->action(function ($record, array $data, Action $action) {
                        $notValid = $record->eksemplarBukus()
                            ->where(function ($query) {
                                $query->where('status', '!=', 'tersedia')
                                      ->orWhereHas('peminjamans');
                            })->get();

                        if ($notValid->count() > 0) {
                            $codes = $notValid->pluck('kode_eksemplar')->implode(', ');
                            Notification::make()
                                ->danger()
                                ->title('Pembatalan Ditolak')
                                ->body("Tidak bisa dibatalkan, eksemplar berikut sedang tidak tersedia atau memiliki riwayat peminjaman: {$codes}")
                                ->send();
                            $action->halt();
                        }

                        $record->update([
                            'status' => 'dibatalkan',
                            'alasan_pembatalan' => $data['alasan_pembatalan'],
                        ]);
                        
                        // Hapus eksemplar yang terkait (secara bulk query agar tidak trigger decrement jika diatur)
                        $record->eksemplarBukus()->delete();

                        Notification::make()
                            ->success()
                            ->title('Berhasil Dibatalkan')
                            ->body('Entri inventaris berhasil dibatalkan dan eksemplar terkait dihapus.')
                            ->send();
                    }),
                Action::make('pulihkan_entri')
                    ->label('Pulihkan Entri')
                    ->icon('heroicon-o-arrow-path')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Pulihkan Entri Inventaris')
                    ->modalDescription('Apakah Anda yakin ingin memulihkan entri inventaris ini? Eksemplar buku yang dibatalkan akan dikembalikan ke status tersedia.')
                    ->visible(fn ($record) => $record->status === 'dibatalkan' && (auth()->user()?->isSuperAdmin() || auth()->user()?->hasRole('admin_perpustakaan_editor') || auth()->user()?->hasRole('admin_master_editor')))
                    ->action(function ($record) {
                        // 1. Restore Master Buku jika sedang dalam kondisi terhapus (soft-deleted)
                        if ($record->buku && $record->buku->trashed()) {
                            $record->buku->restore();
                        }

                        // 2. Restore eksemplar buku yang di soft-delete
                        $record->eksemplarBukus()->withTrashed()->restore();

                        // 3. Ubah status inventaris menjadi aktif kembali
                        $record->update([
                            'status' => 'aktif',
                            'alasan_pembatalan' => null,
                        ]);

                        Notification::make()
                            ->success()
                            ->title('Berhasil Dipulihkan')
                            ->body('Entri inventaris, eksemplar, dan katalog buku terkait berhasil dipulihkan secara utuh.')
                            ->send();
                    }),
            ])
            ->toolbarActions([]);
    }
}
