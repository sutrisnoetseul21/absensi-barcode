<?php

namespace App\Filament\Resources\TahunAjarans\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TahunAjaranForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama Tahun Ajaran')
                    ->placeholder('Contoh: 2025/2026')
                    ->required()
                    ->maxLength(20),

                DatePicker::make('start_date')
                    ->label('Tanggal Mulai')
                    ->required()
                    ->native(false),

                DatePicker::make('end_date')
                    ->label('Tanggal Selesai')
                    ->required()
                    ->native(false)
                    ->after('start_date'),

                Select::make('status')
                    ->label('Status')
                    ->options([
                        'aktif' => 'Aktif',
                        'arsip' => 'Arsip',
                    ])
                    ->default('aktif')
                    ->required()
                    ->helperText('Hanya 1 tahun ajaran yang bisa berstatus "Aktif" dalam satu waktu. Mengubah ke Aktif akan mengarsipkan tahun ajaran lainnya.'),
            ]);
    }
}
