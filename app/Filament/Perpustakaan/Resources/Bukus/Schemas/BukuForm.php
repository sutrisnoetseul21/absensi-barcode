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
                            ->label('Koleksi')
                            ->relationship('kategoriBuku', 'nama_kategori')
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, $set) {
                                if ($state) {
                                    $kategori = \App\Models\KategoriBuku::find($state);
                                    if (! $kategori || strtolower(trim($kategori->nama_kategori)) !== 'non fiksi') {
                                        $set('mapel_id', null);
                                    }
                                } else {
                                    $set('mapel_id', null);
                                }
                            }),
                        Select::make('mapel_id')
                            ->label('Mata Pelajaran')
                            ->options(function () {
                                $options = \App\Models\MataPelajaran::orderBy('nama_mapel')->pluck('nama_mapel', 'id')->toArray();
                                $options['lainnya'] = 'Lainnya';
                                return $options;
                            })
                            ->placeholder('Pilih Mata Pelajaran')
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->formatStateUsing(fn ($state) => $state ?? 'lainnya')
                            ->dehydrateStateUsing(fn ($state) => $state === 'lainnya' ? null : $state)
                            ->disabled(function ($get) {
                                $kategoriId = $get('kategori_id');
                                if (! $kategoriId) {
                                    return true;
                                }
                                $kategori = \App\Models\KategoriBuku::find($kategoriId);
                                return ! $kategori || strtolower(trim($kategori->nama_kategori)) !== 'non fiksi';
                            }),
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
                                'umum' => 'Semua Jenjang / Umum',
                                '7' => 'Kelas 7',
                                '8' => 'Kelas 8',
                                '9' => 'Kelas 9',
                            ])
                            ->placeholder('Pilih Jenjang')
                            ->nullable()
                            ->formatStateUsing(fn ($state) => $state !== null ? (string)$state : 'umum')
                            ->dehydrateStateUsing(fn ($state) => $state === 'umum' ? null : $state),
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

                Section::make('Generate Eksemplar Awal & Inventaris')
                    ->description('Isi data inventaris dan jumlah eksemplar yang ingin dibuat otomatis setelah buku ini disimpan.')
                    ->schema([
                        Select::make('asal_buku')
                            ->label('Asal Buku')
                            ->options([
                                'Pembelian' => 'Pembelian',
                                'Hibah' => 'Hibah',
                                'Tukar' => 'Tukar',
                                'Terbitan Sendiri' => 'Terbitan Sendiri',
                            ])
                            ->default('Pembelian')
                            ->required(),
                        TextInput::make('harga_buku')
                            ->label('Harga Buku (Rp)')
                            ->numeric()
                            ->nullable(),
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
