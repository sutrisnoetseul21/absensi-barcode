<?php

namespace App\Filament\Resources\Enrollment\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class EnrollmentTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('siswa.name')
                    ->label('Nama Siswa')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('siswa.nisn')
                    ->label('NISN')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('kelas.name')
                    ->label('Kelas')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('tahunAjaran.name')
                    ->label('Tahun Ajaran')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'aktif'   => 'success',
                        'naik'    => 'info',
                        'tinggal' => 'danger',
                        'pindah'  => 'warning',
                        'lulus'   => 'primary',
                        default   => 'gray',
                    }),
            ])
            ->filters([
                SelectFilter::make('academic_year_id')
                    ->label('Tahun Ajaran')
                    ->relationship('tahunAjaran', 'name'),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'aktif'   => 'Aktif',
                        'naik'    => 'Naik Kelas',
                        'tinggal' => 'Tinggal Kelas',
                        'pindah'  => 'Pindah',
                        'lulus'   => 'Lulus',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
