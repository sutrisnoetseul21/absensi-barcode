<?php

namespace App\Filament\Perpustakaan\Resources\KategoriBukus\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class KategoriBukuForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama_kategori')
                    ->label('Nama Klasifikasi / Koleksi')
                    ->required(),
                TextInput::make('kode_prefix')
                    ->label('Kode Prefix Label')
                    ->helperText('Contoh: SR untuk Sirkulasi, RF untuk Referensi, KK untuk Koleksi Khusus.')
                    ->maxLength(10)
                    ->placeholder('Misal: SR'),
                \Filament\Forms\Components\Toggle::make('is_bisa_dipinjam')
                    ->label('Bisa Dipinjam')
                    ->helperText('Apakah buku dalam koleksi ini boleh dipinjam dibawa pulang?')
                    ->default(true),
                \Filament\Forms\Components\Toggle::make('is_buku_pelajaran')
                    ->label('Buku Pelajaran')
                    ->helperText('Centang jika ini adalah buku teks pelajaran wajib sekolah.')
                    ->default(false),
            ]);
    }
}
