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
                    ->directory('siswa-photos')
                    ->nullable(),

                TextInput::make('barcode_code')
                    ->label('Kode Barcode')
                    ->maxLength(50)
                    ->unique(
                        table: 'students',
                        column: 'barcode_code',
                        ignoreRecord: true
                    )
                    ->helperText('Kosongkan untuk otomatis mengisi kode barcode dari NISN.')
                    ->nullable(),

                TextInput::make('username')
                    ->label('Username Login')
                    ->disabled()
                    ->dehydrated(false)
                    ->helperText('Username otomatis diset dari NISN saat pembuatan data baru.')
                    ->visibleOn('edit'),
            ]);
    }
}
