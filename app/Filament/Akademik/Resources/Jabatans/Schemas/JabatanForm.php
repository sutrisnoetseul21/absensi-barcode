<?php

namespace App\Filament\Akademik\Resources\Jabatans\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class JabatanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama_jabatan')
                    ->required()
                    ->maxLength(255),
            ]);
    }
}
