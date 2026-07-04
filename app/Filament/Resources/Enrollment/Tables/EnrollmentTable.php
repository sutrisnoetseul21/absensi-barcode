<?php

namespace App\Filament\Resources\Enrollment\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
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
                DeleteAction::make()
                    ->before(function (DeleteAction $action, \App\Models\EnrollmentSiswa $record) {
                        if ($record->absensis()->count() > 0) {
                            \Filament\Notifications\Notification::make()
                                ->warning()
                                ->title('Gagal menghapus!')
                                ->body('Pendaftaran siswa ini tidak dapat dihapus karena sudah memiliki data absensi.')
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
                                if ($record->absensis()->count() > 0) {
                                    \Filament\Notifications\Notification::make()
                                        ->warning()
                                        ->title('Penghapusan dibatalkan!')
                                        ->body('Beberapa pendaftaran siswa tidak dapat dihapus karena sudah memiliki data absensi.')
                                        ->send();
                                    $action->halt();
                                }
                            }
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
