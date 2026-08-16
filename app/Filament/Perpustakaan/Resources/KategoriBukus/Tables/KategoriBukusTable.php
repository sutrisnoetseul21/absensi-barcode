<?php

namespace App\Filament\Perpustakaan\Resources\KategoriBukus\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class KategoriBukusTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('nama_kategori')
                    ->label('Nama Klasifikasi')
                    ->searchable(),
                TextColumn::make('kode_prefix')
                    ->label('Kode Prefix')
                    ->badge()
                    ->color('info')
                    ->searchable(),
                \Filament\Tables\Columns\IconColumn::make('is_bisa_dipinjam')
                    ->label('Bisa Dipinjam')
                    ->boolean(),
                \Filament\Tables\Columns\IconColumn::make('is_buku_pelajaran')
                    ->label('Buku Pelajaran')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()
                    ->visible(fn () => auth()->user()?->isSuperAdmin() || auth()->user()?->hasRole('admin_perpustakaan_editor') || auth()->user()?->hasRole('admin_master_editor')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()?->isSuperAdmin() || auth()->user()?->hasRole('admin_perpustakaan_editor') || auth()->user()?->hasRole('admin_master_editor')),
                ]),
            ]);
    }
}
