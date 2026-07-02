<?php

namespace App\Filament\Resources\Guru\Schemas;

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

                TextInput::make('username')
                    ->label('Username Login')
                    ->disabled()
                    ->dehydrated(false) // tidak disimpan saat edit (auto-generated)
                    ->helperText('Username di-generate otomatis dari NIP (jika ada) atau nama lengkap tanpa gelar.')
                    ->visibleOn('edit'),
            ]);
    }
}
