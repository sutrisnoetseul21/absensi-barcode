<?php

namespace App\Filament\Resources\Siswa\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SiswaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nisn')
                    ->label('NISN')
                    ->required()
                    ->maxLength(20)
                    ->unique(
                        table: 'students',
                        column: 'nisn',
                        ignoreRecord: true
                    ),

                TextInput::make('nis')
                    ->label('NIS')
                    ->nullable()
                    ->maxLength(20)
                    ->unique(
                        table: 'students',
                        column: 'nis',
                        ignoreRecord: true
                    ),

                TextInput::make('name')
                    ->label('Nama Lengkap')
                    ->required()
                    ->maxLength(255),

                TextInput::make('birth_place')
                    ->label('Tempat Lahir')
                    ->maxLength(100)
                    ->nullable(),

                DatePicker::make('birth_date')
                    ->label('Tanggal Lahir')
                    ->native(false)
                    ->nullable(),

                Textarea::make('address')
                    ->label('Alamat Lengkap')
                    ->maxLength(500)
                    ->nullable()
                    ->columnSpanFull(),

                FileUpload::make('photo_path')
                    ->label('Foto Siswa')
                    ->image()
                    ->imageEditor()
                    ->imageEditorAspectRatios([
                        '1:1',
                    ])
                    ->imageResizeTargetWidth(500)
                    ->imageResizeTargetHeight(500)
                    ->disk('public')
                    ->directory('siswa-photos')
                    ->nullable(),

                TextInput::make('password')
                    ->password()
                    ->label('Password')
                    ->dehydrateStateUsing(fn ($state) => filled($state) ? bcrypt($state) : null)
                    ->dehydrated(fn ($state) => filled($state))
                    ->required(fn (string $context): bool => $context === 'create')
                    ->maxLength(255)
                    ->helperText(fn (string $context): string => $context === 'edit' ? 'Biarkan kosong jika tidak ingin mengubah password.' : 'Masukkan password untuk akun siswa ini.'),
            ]);
    }
}
