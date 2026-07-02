<?php

namespace App\Filament\Resources\Kelas\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class KelasForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama Kelas')
                    ->placeholder('Contoh: 7A, 10 IPA 1')
                    ->required()
                    ->maxLength(20),

                Select::make('grade_level')
                    ->label('Tingkat')
                    ->options([
                        7  => 'Kelas 7 (SMP)',
                        8  => 'Kelas 8 (SMP)',
                        9  => 'Kelas 9 (SMP)',
                        10 => 'Kelas 10 (SMA)',
                        11 => 'Kelas 11 (SMA)',
                        12 => 'Kelas 12 (SMA)',
                    ])
                    ->required(),
            ]);
    }
}
