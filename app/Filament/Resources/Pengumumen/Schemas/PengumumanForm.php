<?php

namespace App\Filament\Resources\Pengumumen\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PengumumanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Pengumuman')
                    ->columns(2)
                    ->schema([
                        TextInput::make('judul')
                            ->label('Judul Pengumuman')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Textarea::make('isi')
                            ->label('Isi Pengumuman')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),

                        Select::make('tipe')
                            ->label('Tipe')
                            ->options([
                                'info'      => '🔵 Informasi',
                                'penting'   => '🟡 Penting',
                                'peringatan' => '🔴 Peringatan',
                            ])
                            ->default('info')
                            ->required(),

                        TextInput::make('urutan')
                            ->label('Urutan Tampil')
                            ->numeric()
                            ->default(0)
                            ->helperText('Angka lebih kecil tampil lebih dulu'),
                    ]),

                Section::make('Jadwal Tayang')
                    ->columns(2)
                    ->schema([
                        Toggle::make('aktif')
                            ->label('Aktifkan Pengumuman')
                            ->default(true)
                            ->columnSpanFull(),

                        DatePicker::make('tanggal_mulai')
                            ->label('Tanggal Mulai')
                            ->nullable()
                            ->helperText('Kosongkan untuk tayang langsung'),

                        DatePicker::make('tanggal_selesai')
                            ->label('Tanggal Selesai')
                            ->nullable()
                            ->helperText('Kosongkan untuk tayang selamanya')
                            ->afterOrEqual('tanggal_mulai'),
                    ]),
            ]);
    }
}
