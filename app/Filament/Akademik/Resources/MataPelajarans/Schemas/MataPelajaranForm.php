<?php

namespace App\Filament\Akademik\Resources\MataPelajarans\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class MataPelajaranForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama_mapel')
                    ->required()
                    ->maxLength(255),
                TextInput::make('kode_mapel')
                    ->maxLength(255),
            ]);
    }
}
