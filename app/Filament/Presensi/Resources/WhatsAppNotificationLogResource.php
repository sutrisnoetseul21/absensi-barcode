<?php

namespace App\Filament\Presensi\Resources;

use App\Filament\Presensi\Resources\WhatsAppNotificationLogResource\Pages;
use App\Models\WhatsAppNotificationLog;
use Filament\Forms\Components;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Jobs\SendWhatsAppNotificationJob;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

class WhatsAppNotificationLogResource extends Resource
{
    protected static ?string $model = WhatsAppNotificationLog::class;

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-chat-bubble-left-ellipsis';
    }

    public static function getNavigationLabel(): string
    {
        return 'Laporan WhatsApp';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Laporan WhatsApp';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Presensi';
    }

    public static function getNavigationSort(): ?int
    {
        return 10;
    }

    public static function form(Schema $schema): Schema
    {
        // View-only form for ViewAction
        return $schema
            ->components([
                Components\TextInput::make('recipient_type')
                    ->label('Tipe Penerima')
                    ->disabled(),
                Components\TextInput::make('recipient_number')
                    ->label('Nomor HP')
                    ->disabled(),
                Components\TextInput::make('status')
                    ->label('Status Terakhir')
                    ->disabled(),
                Components\TextInput::make('sent_at')
                    ->label('Waktu Terkirim')
                    ->disabled(),
                Components\Textarea::make('message')
                    ->label('Isi Pesan')
                    ->disabled()
                    ->columnSpanFull()
                    ->rows(5),
                Components\Textarea::make('response_payload')
                    ->label('Respons API / Payload')
                    ->disabled()
                    ->columnSpanFull()
                    ->rows(5),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime('d M Y H:i:s')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('recipient_type')
                    ->label('Tipe')
                    ->badge()
                    ->color('gray')
                    ->searchable(),
                Tables\Columns\TextColumn::make('recipient_number')
                    ->label('Nomor HP')
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('message')
                    ->label('Pesan')
                    ->limit(40)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= $column->getCharacterLimit()) {
                            return null;
                        }
                        return $state;
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'sent' => 'success',
                        'failed' => 'danger',
                        default => 'gray',
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('sent_at')
                    ->label('Terkirim Pada')
                    ->dateTime('d M Y H:i:s')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Filter Status')
                    ->options([
                        'pending' => 'Pending',
                        'sent' => 'Sent',
                        'failed' => 'Failed',
                    ]),
            ])
            ->actions([
                \Filament\Actions\ViewAction::make(),
                \Filament\Actions\Action::make('resend')
                    ->label('Kirim Ulang')
                    ->icon('heroicon-o-arrow-path')
                    ->color('primary')
                    ->requiresConfirmation()
                    ->modalHeading('Kirim Ulang Pesan WhatsApp')
                    ->modalDescription('Apakah Anda yakin ingin memasukkan pesan ini kembali ke dalam antrean?')
                    ->modalSubmitActionLabel('Ya, Kirim Ulang')
                    ->visible(fn (WhatsAppNotificationLog $record): bool => in_array($record->status, ['failed', 'pending']))
                    ->action(function (WhatsAppNotificationLog $record) {
                        // Reset status to pending
                        $record->update([
                            'status' => 'pending',
                            'response_payload' => json_encode(['info' => 'Resent by Admin']),
                        ]);

                        // Dispatch the job again
                        SendWhatsAppNotificationJob::dispatch(
                            $record->recipient_number,
                            $record->message,
                            $record->related_type,
                            $record->related_id,
                            $record->recipient_type,
                            $record->id
                        );

                        Notification::make()
                            ->title('Berhasil')
                            ->body('Pesan telah dimasukkan kembali ke antrean untuk dikirim.')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                // Intentionally empty for safety
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWhatsAppNotificationLogs::route('/'),
        ];
    }
}
