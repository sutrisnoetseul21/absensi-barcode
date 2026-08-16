<?php

namespace App\Filament\Akademik\Resources\Jabatans\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class JabatansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama_jabatan')->searchable()->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()
                    ->visible(fn () => auth()->user()?->isSuperAdmin() || auth()->user()?->hasRole('admin_master_editor'))
                    ->modalDescription(function (\App\Models\Jabatan $record) {
                        if ($record->gurus()->exists()) {
                            return new \Illuminate\Support\HtmlString('<span style="color: #ef4444; font-weight: bold;">⚠️ Peringatan: Jabatan ini sudah ditugaskan ke Guru. Mengubah nama jabatan dapat merusak histori penugasan!</span>');
                        }
                        return null;
                    })
                    ->before(function (\App\Models\Jabatan $record, \Filament\Actions\EditAction $action) {
                        if ($record->gurus()->exists()) {
                            \Filament\Notifications\Notification::make()
                                ->title('Perubahan Ditolak')
                                ->body('Jabatan tidak dapat diubah karena sedang ditugaskan ke Guru.')
                                ->danger()
                                ->persistent()
                                ->send();

                            $action->cancel();
                        }
                    }),

                \Filament\Actions\DeleteAction::make()
                    ->visible(fn () => auth()->user()?->isSuperAdmin() || auth()->user()?->hasRole('admin_master_editor'))
                    ->before(function (\App\Models\Jabatan $record, \Filament\Actions\DeleteAction $action) {
                        if ($record->gurus()->exists()) {
                            \Filament\Notifications\Notification::make()
                                ->title('Akses Ditolak')
                                ->body('Jabatan tidak dapat dihapus! Lepaskan penugasan jabatan ini dari semua Guru terlebih dahulu.')
                                ->danger()
                                ->persistent()
                                ->send();

                            $action->cancel();
                        }
                    }),
            ]);
    }
}
