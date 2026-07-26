<?php

namespace App\Filament\Perpustakaan\Resources\Bukus\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class BukuForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('kategori_id')
                    ->label('Kategori')
                    ->relationship('kategoriBuku', 'nama_kategori')
                    ->required(),
                Select::make('mapel_id')
                    ->label('Mata Pelajaran')
                    ->relationship('mataPelajaran', 'nama_mapel')
                    ->nullable(),
                Select::make('grade_level')
                    ->label('Jenjang (Grade)')
                    ->options([
                        '7' => 'Kelas 7',
                        '8' => 'Kelas 8',
                        '9' => 'Kelas 9',
                        '10' => 'Kelas 10',
                        '11' => 'Kelas 11',
                        '12' => 'Kelas 12',
                    ])
                    ->nullable(),
                TextInput::make('judul')
                    ->required(),
                TextInput::make('penulis')
                    ->nullable(),
                TextInput::make('penerbit')
                    ->nullable(),
                TextInput::make('tahun_terbit')
                    ->numeric()
                    ->nullable(),
                TextInput::make('isbn')
                    ->nullable(),
                TextInput::make('lokasi_rak')
                    ->nullable(),
            ]);
    }
}
