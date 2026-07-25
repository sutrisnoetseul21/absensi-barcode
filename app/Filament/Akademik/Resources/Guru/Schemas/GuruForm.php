<?php

namespace App\Filament\Akademik\Resources\Guru\Schemas;

use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;

class GuruForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama Lengkap')
                    ->placeholder('Contoh: Dr. Budi Santoso, M.Pd.')
                    ->required()
                    ->maxLength(255),

                TextInput::make('nip')
                    ->label('NIP')
                    ->placeholder('Nomor Induk Pegawai (opsional)')
                    ->nullable()
                    ->maxLength(30)
                    ->unique(
                        table: 'teachers',
                        column: 'nip',
                        ignoreRecord: true
                    )
                    ->helperText('Jika diisi, NIP akan digunakan sebagai username login.'),

                TextInput::make('email')
                    ->label('Email Login')
                    ->email()
                    ->required(fn (string $operation): bool => $operation === 'edit')
                    ->maxLength(255)
                    ->helperText('Otomatis di-generate jika dikosongkan saat create.')
                    ->formatStateUsing(fn (?Model $record) => $record?->user?->email),

                TextInput::make('password')
                    ->label(fn (string $operation): string => $operation === 'edit' ? 'Password Baru' : 'Password Login')
                    ->password()
                    ->revealable()
                    ->dehydrated(false)
                    ->helperText(fn (string $operation): string => $operation === 'edit' ? 'Kosongkan jika tidak ingin mengubah password.' : 'Kosongkan untuk meng-generate random password (akan ditampilkan setelah disimpan).'),
            ]);
    }
}
