<?php

namespace App\Filament\Akademik\Resources\Siswa\Schemas;

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
                                } elseif (!str_starts_with($normalized, '62')) {
                                    $normalized = '62' . $normalized;
                                }
                                if (!preg_match('/^628[0-9]{7,12}$/', $normalized)) {
                                    $fail('Format nomor HP tidak valid. Contoh: 081234567890');
                                }
                            }
                        };
                    })
                    ->helperText('Format bebas, otomatis dinormalisasi ke 62xxx.')
                    ->nullable(),

                FileUpload::make('photo_path')
                    ->label('Foto Siswa')
                    ->image()
                    ->imageEditor()
                    ->imageEditorAspectRatios([
                        '1:1',
                    ])
                    ->imageResizeTargetWidth(400)
                    ->imageResizeTargetHeight(400)
                    ->imageResizeMode('cover')
                    ->imageResizeUpscale(false)
                    ->maxSize(2048)
                    ->disk('public')
                    ->directory('siswa-photos')
                    ->nullable(),

                TextInput::make('email')
                    ->label('Email Login')
                    ->email()
                    ->autocomplete('off')
                    ->required(fn (string $operation): bool => $operation === 'edit')
                    ->maxLength(255)
                    ->helperText('Otomatis di-generate jika dikosongkan saat create.')
                    ->formatStateUsing(fn (?\Illuminate\Database\Eloquent\Model $record) => $record?->user?->email),

                TextInput::make('password')
                    ->password()
                    ->revealable()
                    ->label('Password Login')
                    ->autocomplete('new-password')
                    ->dehydrated(fn ($state) => filled($state))
                    ->maxLength(255)
                    ->helperText(fn (string $operation): string => $operation === 'edit' ? 'Biarkan kosong jika tidak ingin mengubah password.' : 'Kosongkan untuk menggunakan NISN sebagai password default.'),
            ]);
    }
}
