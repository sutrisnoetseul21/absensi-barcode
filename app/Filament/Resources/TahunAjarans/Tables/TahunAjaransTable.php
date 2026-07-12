<?php

namespace App\Filament\Resources\TahunAjarans\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TahunAjaransTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Tahun Ajaran')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('start_year')
                    ->label('Tahun Mulai')
                    ->sortable(),

                TextColumn::make('end_year')
                    ->label('Tahun Selesai')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'aktif' => 'success',
                        'arsip' => 'gray',
                        default => 'gray',
                    }),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'aktif' => 'Aktif',
                        'arsip' => 'Arsip',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->before(function (DeleteAction $action, \App\Models\TahunAjaran $record) {
                        if ($record->kelasAjarans()->count() > 0 || $record->enrollments()->count() > 0 || $record->absensis()->count() > 0) {
                            \Filament\Notifications\Notification::make()
                                ->warning()
                                ->title('Gagal menghapus!')
                                ->body('Tahun ajaran tidak dapat dihapus karena memiliki data kelas, siswa, atau presensi terkait.')
                                ->send();
                            $action->halt();
                        }
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->before(function (DeleteBulkAction $action, \Illuminate\Database\Eloquent\Collection $records) {
                            foreach ($records as $record) {
                                if ($record->kelasAjarans()->count() > 0 || $record->enrollments()->count() > 0 || $record->absensis()->count() > 0) {
                                    \Filament\Notifications\Notification::make()
                                        ->warning()
                                        ->title('Penghapusan dibatalkan!')
                                        ->body('Beberapa tahun ajaran tidak dapat dihapus karena sudah memiliki data kelas, siswa, atau presensi terkait.')
                                        ->send();
                                    $action->halt();
                                }
                            }
                        }),
                ]),
            ])
            ->defaultSort('start_year', 'asc');
    }
}
