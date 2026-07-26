<?php

namespace App\Filament\Akademik\Resources\TahunAjarans\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rule;

class TahunAjaranForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('start_year')
                    ->label('Tahun Mulai')
                    ->placeholder('Contoh: 2024')
                    ->integer()
                    ->minValue(2000)
                    ->maxValue(2100)
                    ->required()
                    ->unique(table: 'academic_years', column: 'start_year', ignoreRecord: true)
                    ->helperText('Tahun awal semester ganjil, misal 2024 untuk TP 2024/2025.'),

                TextInput::make('end_year')
                    ->label('Tahun Selesai')
                    ->placeholder('Contoh: 2025')
                    ->integer()
                    ->minValue(2000)
                    ->maxValue(2100)
                    ->required()
                    ->unique(table: 'academic_years', column: 'end_year', ignoreRecord: true)
                    ->gt('start_year')
                    ->helperText('Harus lebih besar dari Tahun Mulai.'),

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
