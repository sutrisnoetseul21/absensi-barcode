<?php

namespace App\Filament\Resources\UserResource\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use App\Models\Guru;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('teacher_id')
                    ->label('Terhubung dengan Data Guru (Opsional)')
                    ->relationship('guru', 'name')
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(function (Set $set, ?string $state) {
                        if ($state) {
                            $guru = Guru::find($state);
                            if ($guru) {
                                $set('name', $guru->name);
                                // Buat email otomatis: nip@domain (domain dari APP_URL)
                                $appUrl = config('app.url', 'http://localhost');
                                $domain = parse_url($appUrl, PHP_URL_HOST) ?? 'localhost';
                                $nip = $guru->nip ?? strtolower(str_replace(' ', '.', $guru->name));
                                $set('email', $nip . '@' . $domain);
                            }
                        }
                    })
                    ->helperText('Pilih guru untuk mengisi otomatis nama dan email (format: nip@domain).'),

                TextInput::make('name')
                    ->label('Nama Lengkap')
                    ->required()
                    ->maxLength(255),

                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),

                TextInput::make('password')
                    ->label('Password')
                    ->password()
                    ->revealable()
                    ->dehydrated(fn ($state) => filled($state))
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->helperText('Kosongkan jika tidak ingin mengubah password saat mengedit.')
                    ->maxLength(255),

                Select::make('roles')
                    ->label('Hak Akses Panel (Role)')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->helperText('Pilih portal mana saja yang boleh diakses oleh pengguna ini.'),
            ]);
    }
}
