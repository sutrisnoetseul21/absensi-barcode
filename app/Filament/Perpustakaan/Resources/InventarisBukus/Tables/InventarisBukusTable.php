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
                    ->date('d M Y')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('no_inventaris')
                    ->searchable(),
                TextColumn::make('buku.judul')
                    ->label('Judul Buku')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('asal'),
                TextColumn::make('jumlah_eksemplar')
                    ->label('Jumlah'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'aktif' => 'success',
                        'dibatalkan' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('batalkan_entri')
                    ->label('Batalkan Entri')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->status === 'aktif')
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
            ])
            ->toolbarActions([]);
    }
}
