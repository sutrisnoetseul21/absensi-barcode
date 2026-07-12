<?php

namespace App\Filament\Resources\Pengumumen\Tables;

use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class PengumumenTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('urutan')
                    ->label('#')
                    ->sortable()
                    ->width('50px'),

                TextColumn::make('judul')
                    ->label('Judul Pengumuman')
                    ->searchable()
                    ->sortable()
                    ->limit(50),

                TextColumn::make('tipe')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'info'       => 'info',
                        'penting'    => 'warning',
                        'peringatan' => 'danger',
                        default      => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'info'       => '🔵 Informasi',
                        'penting'    => '🟡 Penting',
                        'peringatan' => '🔴 Peringatan',
                        default      => $state,
                    }),

                IconColumn::make('aktif')
                    ->label('Aktif')
                    ->boolean(),

                TextColumn::make('tanggal_mulai')
                    ->label('Mulai')
                    ->date('d M Y')
                    ->placeholder('Segera')
                    ->sortable(),

                TextColumn::make('tanggal_selesai')
                    ->label('Selesai')
                    ->date('d M Y')
                    ->placeholder('Selamanya')
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('aktif')
                    ->label('Status Aktif'),

                SelectFilter::make('tipe')
                    ->label('Tipe')
                    ->options([
                        'info'       => '🔵 Informasi',
                        'penting'    => '🟡 Penting',
                        'peringatan' => '🔴 Peringatan',
                    ]),
            ])
            ->defaultSort('urutan', 'asc')
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
