<?php

namespace App\Filament\Perpustakaan\Resources\Bukus\Schemas;

use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class BukuForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()->schema([
                    TextInput::make('judul')
                        ->required()
                        ->columnSpanFull(),

                    Group::make()->schema([
                        Select::make('kategori_id')
                            ->label('Kategori')
                            ->relationship('kategoriBuku', 'nama_kategori')
                            ->required(),
                        Select::make('mapel_id')
                            ->label('Mata Pelajaran')
                            ->relationship('mataPelajaran', 'nama_mapel')
                            ->nullable(),
                    ])->columns(2)->columnSpanFull(),

                    Group::make()->schema([
                        Select::make('klasifikasi_ddc_id')
                            ->label('Klasifikasi DDC')
                            ->relationship('klasifikasiDdc', 'kode_ddc')
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->kode_ddc} - {$record->kategori}")
                            ->searchable(['kode_ddc', 'kategori'])
                            ->preload()
                            ->nullable()
                            ->live(),
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
                    ])->columns(2)->columnSpanFull(),

                    Placeholder::make('call_number')
                        ->label('Call Number (Otomatis)')
                        ->content(fn ($record) => $record ? new \Illuminate\Support\HtmlString(nl2br(e($record->call_number))) : '-')
                        ->columnSpanFull(),

                    Group::make()->schema([
                        TextInput::make('penulis')
                            ->nullable(),
                        TextInput::make('penerbit')
                            ->nullable(),
                    ])->columns(2)->columnSpanFull(),

                    Group::make()->schema([
                        TextInput::make('tahun_terbit')
                            ->numeric()
                            ->nullable(),
                        TextInput::make('isbn')
                            ->nullable(),
                    ])->columns(2)->columnSpanFull(),

                    TextInput::make('lokasi_rak')
                        ->nullable()
                        ->columnSpanFull(),
                ]),

                Section::make('Generate Eksemplar Awal')
                    ->description('Isi jumlah eksemplar yang ingin dibuat otomatis setelah buku ini disimpan. (Hanya muncul saat tambah buku baru)')
                    ->schema([
                        TextInput::make('jumlah_eksemplar')
                            ->label('Jumlah Eksemplar')
                            ->numeric()
                            ->default(1)
                            ->minValue(1)
                            ->required(),
                        TextInput::make('prefix_kode')
                            ->label('Prefix Kode')
                            ->default('UMM')
                            ->maxLength(10)
                            ->required(),
                    ])
                    ->columns(2)
                    ->visible(fn ($operation) => $operation === 'create'),
            ]);
    }
}
