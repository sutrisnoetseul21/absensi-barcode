<?php

namespace App\Filament\Resources\UserResource\Tables;

use App\Filament\Resources\ManajemenAksesPortalResource;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UserTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordUrl(fn (User $record): string => ManajemenAksesPortalResource::getUrl('edit', ['record' => $record]))
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
                    ->state(fn (User $record) => $record->no_hp ?? $record->teacher?->no_hp ?? $record->guru?->no_hp)
                    ->placeholder('-'),

                TextColumn::make('terkait_guru')
                    ->label('Terkait Guru')
                    ->state(fn (User $record) => $record->teacher?->name ?? $record->guru?->name)
                    ->searchable(query: function ($query, string $search) {
                        $query->whereHas('teacher', fn ($q) => $q->where('name', 'like', "%{$search}%"))
                            ->orWhereHas('guru', fn ($q) => $q->where('name', 'like', "%{$search}%"));
                    })
                    ->placeholder('-'),

                TextColumn::make('roles.name')
                    ->label('Hak Akses (Roles)')
                    ->badge()
                    ->searchable()
                    ->separator(',')
                    ->sortable(),

                TextColumn::make('password_reset')
                    ->label('Password')
                    ->state('Ubah Password')
                    ->badge()
                    ->color('warning')
                    ->icon('heroicon-o-key')
                    ->action(
                        Action::make('ubah_password_kolom')
                            ->modalHeading(fn (User $record): string => "Ubah Password — {$record->name}")
                            ->modalDescription('Masukkan password baru untuk akun ini.')
                            ->modalSubmitActionLabel('Simpan Password')
                            ->modalWidth('md')
                            ->form(self::passwordFormSchema())
                            ->action(fn (User $record, array $data) => self::savePassword($record, $data))
                    ),

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
                Action::make('atur_akses')
                    ->label('Atur Akses')
                    ->icon('heroicon-o-key')
                    ->color('primary')
                    ->url(fn (User $record): string => ManajemenAksesPortalResource::getUrl('edit', ['record' => $record])),

                Action::make('edit_profil')
                    ->label('Edit')
                    ->icon('heroicon-o-pencil-square')
                    ->color('info')
                    ->visible(fn (User $record): bool => ! $record->isGuruUser())
                    ->modalHeading(fn (User $record): string => "Edit User — {$record->name}")
                    ->modalWidth('md')
                    ->fillForm(fn (User $record): array => [
                        'name'  => $record->name,
                        'email' => $record->email,
                        'no_hp' => $record->no_hp,
                    ])
                    ->form([
                        TextInput::make('name')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->unique(table: User::class, column: 'email', ignorable: fn (User $record) => $record)
                            ->maxLength(255),

                        TextInput::make('no_hp')
                            ->label('Nomor WhatsApp')
                            ->tel()
                            ->rule(function () {
                                return function (string $attribute, $value, \Closure $fail) {
                                    $digits = preg_replace('/\D/', '', $value ?? '');
                                    if ($digits) {
                                        $normalized = $digits;
                                        if (str_starts_with($normalized, '0')) {
                                            $normalized = '62' . substr($normalized, 1);
                                        } elseif (! str_starts_with($normalized, '62')) {
                                            $normalized = '62' . $normalized;
                                        }
                                        if (! preg_match('/^628[0-9]{7,12}$/', $normalized)) {
                                            $fail('Format nomor HP tidak valid. Contoh: 081234567890');
                                        }
                                    }
                                };
                            }),
                    ])
                    ->action(function (User $record, array $data): void {
                        $record->update([
                            'name'  => $data['name'],
                            'email' => $data['email'],
                            'no_hp' => $data['no_hp'] ?? null,
                        ]);

                        Notification::make()
                            ->title('Data User Diperbarui')
                            ->body("Profil {$record->name} berhasil diperbarui.")
                            ->success()
                            ->send();
                    }),

                Action::make('ubah_password')
                    ->label('Password')
                    ->icon('heroicon-o-lock-closed')
                    ->color('warning')
                    ->modalHeading(fn (User $record): string => "Ubah Password — {$record->name}")
                    ->modalDescription('Masukkan password baru untuk akun ini.')
                    ->modalSubmitActionLabel('Simpan Password')
                    ->modalWidth('md')
                    ->form(self::passwordFormSchema())
                    ->action(fn (User $record, array $data) => self::savePassword($record, $data)),

                DeleteAction::make()
                    ->hidden(fn (User $record): bool => $record->id === auth()->id() || $record->isGuruUser())
                    ->modalDescription('Apakah Anda yakin ingin menghapus user ini? Data yang terhubung dengan akun ini mungkin akan terdampak.'),
            ])
            ->bulkActions([
                //
            ]);
    }

    protected static function passwordFormSchema(): array
    {
        return [
            TextInput::make('password')
                ->label('Password Baru')
                ->password()
                ->revealable()
                ->required()
                ->minLength(6)
                ->maxLength(255)
                ->helperText('Minimal 6 karakter.'),

            TextInput::make('password_confirmation')
                ->label('Konfirmasi Password Baru')
                ->password()
                ->revealable()
                ->required()
                ->same('password'),
        ];
    }

    protected static function savePassword(User $record, array $data): void
    {
        $record->update([
            'password' => $data['password'],
        ]);

        Notification::make()
            ->title('Password Berhasil Diperbarui')
            ->body("Password untuk akun {$record->name} telah diubah.")
            ->success()
            ->send();
    }
}
