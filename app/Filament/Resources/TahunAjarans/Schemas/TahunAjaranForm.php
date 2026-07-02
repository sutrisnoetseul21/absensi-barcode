<?php

namespace App\Filament\Resources\TahunAjarans\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rule;

class TahunAjaranForm
{
    public static function configure(Schema $schema): Schema
    {
        $record = $schema->getLivewire()->getRecord() ?? null;

        return $schema
            ->components([
                TextInput::make('start_year')
                    ->label('Tahun Mulai')
                    ->placeholder('Contoh: 2024')
                    ->integer()
                    ->minValue(2000)
                    ->maxValue(2100)
                    ->required()
                    ->rule(function () use ($record) {
                        return Rule::unique('academic_years', 'start_year')
                            ->ignore($record?->id ?? null);
                    })
                    ->helperText('Tahun awal semester ganjil, misal 2024 untuk TP 2024/2025.'),

                TextInput::make('end_year')
                    ->label('Tahun Selesai')
                    ->placeholder('Contoh: 2025')
                    ->integer()
                    ->minValue(2000)
                    ->maxValue(2100)
                    ->required()
                    ->rule(function () use ($record) {
                        return Rule::unique('academic_years', 'end_year')
                            ->ignore($record?->id ?? null);
                    })
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
