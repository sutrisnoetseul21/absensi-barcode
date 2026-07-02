<?php

namespace App\Filament\Resources\Guru\Tables;

use App\Models\Guru;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class GuruTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Guru')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('nip')
                    ->label('NIP')
                    ->searchable()
                    ->default('—'),

                TextColumn::make('username')
                    ->label('Username Login')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Username disalin'),

                IconColumn::make('must_change_password')
                    ->label('Ganti Password?')
                    ->boolean()
                    ->trueIcon('heroicon-o-exclamation-triangle')
                    ->falseIcon('heroicon-o-check-circle')
                    ->trueColor('warning')
                    ->falseColor('success'),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),

                // Custom Action: Reset Password
                Action::make('resetPassword')
                    ->label('Reset Password')
                    ->icon('heroicon-o-key')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Reset Password Guru')
                    ->modalDescription(fn (Guru $record): string => "Password baru akan di-generate untuk {$record->name}. Pastikan catat password sebelum menutup dialog ini.")
                    ->action(function (Guru $record): void {
                        $newPassword = Str::random(8);

                        $record->update([
                            'password'             => $newPassword, // otomatis di-hash via cast 'hashed'
                            'must_change_password' => true,
                        ]);

                        Notification::make()
                            ->title('Password berhasil direset')
                            ->body("Username: **{$record->username}**\nPassword baru: **{$newPassword}**\n\nGuru wajib ganti password saat login berikutnya.")
                            ->success()
                            ->persistent() // tidak auto-dismiss, harus diklik manual
                            ->send();
                    }),

                RestoreAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('name');
    }
}
