<?php

namespace App\Filament\Akademik\Resources\TahunAjarans\Tables;

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
                EditAction::make()
                    ->visible(fn () => auth()->user()?->isSuperAdmin() || auth()->user()?->hasRole('admin_master_editor') || auth()->user()?->hasRole('admin_akademik_editor'))
                    ->modalDescription(function (\App\Models\TahunAjaran $record) {
                        $hasData = $record->kelasAjarans()->exists() || $record->enrollments()->exists() || $record->absensis()->exists();
                        if ($hasData || $record->status === 'aktif') {
                            return new \Illuminate\Support\HtmlString('<span style="color: #ef4444; font-weight: bold;">⚠️ Peringatan: Tahun Ajaran ini sudah terisi data. Anda hanya dapat merubah Status (Aktif/Arsip). Tahun mulai/selesai tidak dapat diubah lagi!</span>');
                        }
                        return null;
                    }),

                DeleteAction::make()
                    ->visible(fn () => auth()->user()?->isSuperAdmin() || auth()->user()?->hasRole('admin_master_editor') || auth()->user()?->hasRole('admin_akademik_editor'))
                    ->before(function (\App\Models\TahunAjaran $record, DeleteAction $action) {
                        $hasData = $record->kelasAjarans()->exists() || $record->enrollments()->exists() || $record->absensis()->exists();
                        if ($hasData || $record->status === 'aktif') {
                            \Filament\Notifications\Notification::make()
                                ->title('Akses Ditolak')
                                ->body('Tahun ajaran tidak dapat dihapus karena berstatus Aktif atau memiliki data kelas, siswa, atau presensi terkait.')
                                ->danger()
                                ->persistent()
                                ->send();

                            $action->cancel();
                        }
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()?->isSuperAdmin() || auth()->user()?->hasRole('admin_master_editor') || auth()->user()?->hasRole('admin_akademik_editor'))
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
