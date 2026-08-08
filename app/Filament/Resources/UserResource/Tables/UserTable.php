<?php

namespace App\Filament\Resources\UserResource\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use App\Models\User;

class UserTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Lengkap')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('no_hp')
                    ->label('No. HP')
                    ->state(fn (\App\Models\User $record) =>
                        $record->no_hp ?? $record->teacher?->no_hp ?? $record->student?->no_hp
                    ),

                TextColumn::make('guru.name')
                    ->label('Terkait Guru')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),

                TextColumn::make('roles.name')
                    ->label('Hak Akses (Roles)')
                    ->badge()
                    ->searchable()
                    ->separator(',')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make()
                    ->hidden(fn (User $record): bool => $record->id === auth()->id())
                    ->modalDescription('Apakah Anda yakin ingin menghapus admin ini? Data yang terhubung dengan admin ini mungkin akan terdampak.')
            ])
            ->bulkActions([
                //
            ]);
    }
}
