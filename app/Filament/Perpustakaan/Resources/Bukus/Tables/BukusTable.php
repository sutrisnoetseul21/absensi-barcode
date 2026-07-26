<?php

namespace App\Filament\Perpustakaan\Resources\Bukus\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class BukusTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->withCount([
                'eksemplarBukus as jumlah_eksemplar',
                'eksemplarBukus as jumlah_tersedia' => fn ($q) => $q->where('status', 'tersedia')
            ]))
            ->columns([
                TextColumn::make('judul')
                    ->searchable(),
                TextColumn::make('kategoriBuku.nama_kategori')
                    ->label('Kategori')
                    ->sortable(),
                TextColumn::make('jumlah_eksemplar')
                    ->label('Total Eksemplar')
                    ->sortable(),
                TextColumn::make('jumlah_tersedia')
                    ->label('Tersedia')
                    ->sortable(),
                TextColumn::make('penulis')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('isbn')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->before(function (DeleteAction $action, $record) {
                        if ($record->eksemplarBukus()->whereHas('peminjamans')->exists()) {
                            Notification::make()
                                ->danger()
                                ->title('Gagal Menghapus')
                                ->body('Buku ini tidak dapat dihapus karena salah satu eksemplarnya memiliki riwayat peminjaman.')
                                ->send();
                            $action->halt();
                        }
                    }),
                ForceDeleteAction::make()
                    ->before(function (ForceDeleteAction $action, $record) {
                        if ($record->eksemplarBukus()->whereHas('peminjamans')->exists()) {
                            Notification::make()
                                ->danger()
                                ->title('Gagal Menghapus Permanen')
                                ->body('Buku ini tidak dapat dihapus permanen karena salah satu eksemplarnya memiliki riwayat peminjaman.')
                                ->send();
                            $action->halt();
                        }
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->before(function (DeleteBulkAction $action, $records) {
                            foreach ($records as $record) {
                                if ($record->eksemplarBukus()->whereHas('peminjamans')->exists()) {
                                    Notification::make()
                                        ->danger()
                                        ->title('Penghapusan Massal Gagal')
                                        ->body("Buku '{$record->judul}' tidak dapat dihapus karena memiliki riwayat peminjaman.")
                                        ->send();
                                    $action->halt();
                                }
                            }
                        }),
                    ForceDeleteBulkAction::make()
                        ->before(function (ForceDeleteBulkAction $action, $records) {
                            foreach ($records as $record) {
                                if ($record->eksemplarBukus()->whereHas('peminjamans')->exists()) {
                                    Notification::make()
                                        ->danger()
                                        ->title('Penghapusan Permanen Massal Gagal')
                                        ->body("Buku '{$record->judul}' tidak dapat dihapus permanen karena memiliki riwayat peminjaman.")
                                        ->send();
                                    $action->halt();
                                }
                            }
                        }),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
