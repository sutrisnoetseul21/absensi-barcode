<?php

namespace App\Filament\Resources\HariLiburs\Schemas;

use Filament\Schemas\Schema;

class HariLiburForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Forms\Components\DatePicker::make('start_date')
                    ->label('Tanggal Mulai')
                    ->required()
                    ->native(false),
                    
                \Filament\Forms\Components\DatePicker::make('end_date')
                    ->label('Tanggal Selesai')
                    ->nullable()
                    ->native(false)
                    ->helperText('Biarkan kosong jika libur hanya 1 hari.'),
                    
                \Filament\Forms\Components\TextInput::make('description')
                    ->label('Keterangan / Nama Libur')
                    ->required()
                    ->maxLength(255),
                    
                \Filament\Forms\Components\Select::make('type')
                    ->label('Tipe Libur')
                    ->options([
                        'nasional' => 'Libur Nasional',
                        'cuti_bersama' => 'Cuti Bersama',
                        'khusus' => 'Khusus Kelas',
                    ])
                    ->required()
                    ->reactive(),
                    
                \Filament\Forms\Components\Select::make('class_id')
                    ->label('Kelas (Hanya untuk Khusus Kelas)')
                    ->relationship('kelas', 'name')
                    ->searchable()
                    ->preload()
                    ->visible(fn ($get) => $get('type') === 'khusus')
                    ->required(fn ($get) => $get('type') === 'khusus'),
            ]);
    }
}
