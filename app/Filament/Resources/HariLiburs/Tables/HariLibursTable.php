<?php

namespace App\Filament\Resources\HariLiburs\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Table;

class HariLibursTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('description')
                    ->label('Keterangan')
                    ->searchable()
                    ->sortable(),
                    
                \Filament\Tables\Columns\TextColumn::make('start_date')
                    ->label('Mulai')
                    ->date('d M Y')
                    ->sortable(),
                    
                \Filament\Tables\Columns\TextColumn::make('end_date')
                    ->label('Selesai')
                    ->date('d M Y')
                    ->sortable()
                    ->placeholder('-'),
                    
                \Filament\Tables\Columns\TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'nasional' => 'primary',
                        'cuti_bersama' => 'success',
                        'khusus' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'nasional' => 'Nasional',
                        'cuti_bersama' => 'Cuti Bersama',
                        'khusus' => 'Khusus Kelas',
                        default => $state,
                    })
                    ->searchable(),
                    
                \Filament\Tables\Columns\TextColumn::make('kelas.name')
                    ->label('Kelas')
                    ->placeholder('Semua Kelas'),
            ])
            ->defaultSort('start_date', 'desc')
            ->filters([
                //
            ])
            ->actions([
                EditAction::make()
                    ->button()
                    ->size('xs'),
                DeleteAction::make()
                    ->button()
                    ->color('danger')
                    ->size('xs')
                    ->modalHeading('Hapus Hari Libur')
                    ->modalDescription('Apakah Anda yakin ingin menghapus data hari libur ini? Tanggal ini akan kembali dihitung sebagai hari sekolah efektif.')
                    ->before(function ($record) {
                        activity()
                            ->performedOn($record)
                            ->causedBy(auth()->user())
                            ->withProperties([
                                'description' => $record->description,
                                'start_date' => $record->start_date ? $record->start_date->toDateString() : null,
                                'end_date' => $record->end_date ? $record->end_date->toDateString() : null,
                                'type' => $record->type,
                            ])
                            ->log('Menghapus hari libur');
                    }),
                \Filament\Actions\Action::make('force_libur')
                    ->label('Force Libur')
                    ->icon('heroicon-o-exclamation-triangle')
                    ->color('warning')
                    ->button()
                    ->size('xs')
                    ->requiresConfirmation()
                    ->modalHeading('Paksa Libur (Hapus Absensi)')
                    ->modalDescription('Tindakan ini akan MENGHAPUS secara permanen semua data absensi (Hadir, Izin, Sakit, Alpa) yang sudah tercatat pada rentang tanggal libur ini. Lanjutkan?')
                    ->action(function ($record) {
                        $query = \App\Models\Presensi::where('date', '>=', $record->start_date);
                        
                        if ($record->end_date) {
                            $query->where('date', '<=', $record->end_date);
                        } else {
                            $query->where('date', '<=', $record->start_date);
                        }
                        
                        if ($record->class_id) {
                            $query->where('class_id', $record->class_id);
                        }
                        
                        $deleted = $query->delete();
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Force Libur Berhasil')
                            ->body("$deleted data absensi berhasil dihapus.")
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->before(function (\Illuminate\Database\Eloquent\Collection $records) {
                            foreach ($records as $record) {
                                activity()
                                    ->performedOn($record)
                                    ->causedBy(auth()->user())
                                    ->withProperties([
                                        'description' => $record->description,
                                        'start_date' => $record->start_date ? $record->start_date->toDateString() : null,
                                        'end_date' => $record->end_date ? $record->end_date->toDateString() : null,
                                        'type' => $record->type,
                                    ])
                                    ->log('Menghapus hari libur (Bulk)');
                            }
                        }),
                ]),
            ]);
    }
}
