<?php

namespace App\Filament\Akademik\Resources\MataPelajarans\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MataPelajaransTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama_mapel')->searchable()->sortable(),
                TextColumn::make('kode_mapel')->searchable()->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()
                    ->modalDescription(function (\App\Models\MataPelajaran $record) {
                        if ($record->pengajarans()->exists()) {
                            return new \Illuminate\Support\HtmlString('<span style="color: #ef4444; font-weight: bold;">⚠️ Peringatan: Mata Pelajaran ini sudah dipakai dalam Pembelajaran. Mengubah data dapat merusak riwayat akademik!</span>');
                        }
                        return null;
                    })
                    ->before(function (\App\Models\MataPelajaran $record, \Filament\Actions\EditAction $action) {
                        if ($record->pengajarans()->exists()) {
                            \Filament\Notifications\Notification::make()
                                ->title('Perubahan Ditolak')
                                ->body('Mata Pelajaran tidak dapat diubah karena sedang digunakan dalam Pembelajaran.')
                                ->danger()
                                ->persistent()
                                ->send();

                            $action->cancel();
                        }
                    }),

                \Filament\Actions\DeleteAction::make()
                    ->before(function (\App\Models\MataPelajaran $record, \Filament\Actions\DeleteAction $action) {
                        if ($record->pengajarans()->exists()) {
                            \Filament\Notifications\Notification::make()
                                ->title('Akses Ditolak')
                                ->body('Mata Pelajaran tidak dapat dihapus! Hapus semua pengisian Pembelajaran untuk mata pelajaran ini terlebih dahulu.')
                                ->danger()
                                ->persistent()
                                ->send();

                            $action->cancel();
                        }
                    }),
            ]);
    }
}
