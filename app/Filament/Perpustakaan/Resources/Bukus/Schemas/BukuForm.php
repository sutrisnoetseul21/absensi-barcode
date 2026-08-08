<?php

namespace App\Filament\Perpustakaan\Resources\Bukus\Schemas;

use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\ViewField;
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
                            ->placeholder('Pilih Koleksi / Kategori')
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
                        ViewField::make('klasifikasi_ddc_id')
                            ->label('Klasifikasi DDC (Opsional)')
                            ->view('filament.perpustakaan.components.autocomplete-ddc-field'),
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
                            ->formatStateUsing(fn ($state) => $state === null ? 'umum' : (string) $state)
                            ->dehydrateStateUsing(fn ($state) => ($state === 'umum' || blank($state)) ? null : (int) $state),
                    ])->columns(2)->columnSpanFull(),

                    Group::make()->schema([
                        ViewField::make('penulis')
                            ->label('Penulis')
                            ->view('filament.perpustakaan.components.autocomplete-field', [
                                'column' => 'penulis',
                                'placeholder' => 'Nama Penulis (min. 3 huruf)',
                            ]),
                        ViewField::make('penerbit')
                            ->label('Penerbit')
                            ->view('filament.perpustakaan.components.autocomplete-field', [
                                'column' => 'penerbit',
                                'placeholder' => 'Nama Penerbit (min. 3 huruf)',
                            ]),
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

                    FileUpload::make('sampul_buku')
                        ->label('Sampul Buku')
                        ->image()
                        ->disk('public')
                        ->directory('sampul-buku')
                        ->visibility('public')
                        ->openable()
                        ->downloadable()
                        ->imageEditor()
                        ->imageEditorAspectRatios([
                            '2:3',
                            '3:4',
                            '1:1',
                        ])
                        ->imageResizeMode('cover')
                        ->imageCropAspectRatio('2:3')
                        ->imageResizeTargetWidth('800')
                        ->imageResizeTargetHeight('1200')
                        ->maxSize(5120)
                        ->nullable()
                        ->deletable(false)
                        ->columnSpanFull(),

                    FileUpload::make('file_pdf')
                        ->label('File PDF (E-Book / Baca Online)')
                        ->disk('public')
                        ->directory('buku-pdf')
                        ->visibility('public')
                        ->openable()
                        ->downloadable()
                        ->acceptedFileTypes(['application/pdf'])
                        ->maxSize(51200)
                        ->nullable()
                        ->deletable(false)
                        ->helperText('Upload file PDF buku untuk fitur baca online. Maks. 50MB.')
                        ->columnSpanFull(),
                ]),

                Section::make('Pratinjau Sampul & E-Book (PDF)')
                    ->description('Buka atau lihat pratinjau gambar sampul dan isi dokumen PDF secara langsung.')
                    ->schema([
                        Placeholder::make('preview_sampul')
                            ->label('Pratinjau Sampul Buku')
                            ->content(function ($record) {
                                if (!$record || !$record->sampul_buku) {
                                    return new \Illuminate\Support\HtmlString('<span class="text-sm text-slate-400 italic">Belum ada sampul buku yang diunggah.</span>');
                                }
                                $url = asset('storage/' . $record->sampul_buku);
                                return new \Illuminate\Support\HtmlString('
                                    <div class="flex items-start gap-4 p-3 bg-slate-50 rounded-xl border border-slate-200">
                                        <img src="' . $url . '" class="w-32 h-44 object-cover rounded-lg shadow-md border border-slate-300">
                                        <div class="flex flex-col gap-2">
                                            <span class="text-xs font-semibold text-slate-500">File: ' . e(basename($record->sampul_buku)) . '</span>
                                            <a href="' . $url . '" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded-md shadow-sm transition">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                                Lihat Gambat Ukuran Penuh
                                            </a>
                                        </div>
                                    </div>
                                ');
                            })
                            ->columnSpanFull(),

                        Placeholder::make('preview_pdf_player')
                            ->label('Pratinjau Dokumen PDF (E-Book)')
                            ->content(function ($record) {
                                if (!$record || !$record->file_pdf) {
                                    return new \Illuminate\Support\HtmlString('<span class="text-sm text-slate-400 italic">Belum ada file PDF yang diunggah.</span>');
                                }
                                $bacaUrl = route('perpustakaan.baca-buku', $record);
                                $fileUrl = asset('storage/' . $record->file_pdf);
                                return new \Illuminate\Support\HtmlString('
                                    <div class="flex flex-col gap-3 p-4 bg-slate-900 text-white rounded-xl shadow-inner border border-slate-800">
                                        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-3">
                                            <div class="flex items-center gap-2">
                                                <span class="px-2.5 py-1 bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 rounded-md text-xs font-semibold">PDF Ready</span>
                                                <span class="text-xs text-slate-400">' . e(basename($record->file_pdf)) . '</span>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <a href="' . $bacaUrl . '" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-lg shadow transition">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                                    Buka Mode Baca Fullscreen
                                                </a>
                                                <a href="' . $fileUrl . '" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-medium rounded-lg border border-slate-700 transition">
                                                    Unduh File PDF
                                                </a>
                                            </div>
                                        </div>
                                        <div class="w-full h-[450px] bg-slate-950 rounded-lg overflow-hidden border border-slate-800">
                                            <iframe src="' . $fileUrl . '" class="w-full h-full border-0"></iframe>
                                        </div>
                                    </div>
                                ');
                            })
                            ->columnSpanFull(),
                    ])
                    ->visible(fn ($record) => $record !== null),

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
                            ->placeholder('Pilih Asal Buku')
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
                            ->label('Prefix Kode/Singkatan Buku')
                            ->placeholder('Misal: INF, MAT, UMM')
                            ->helperText('Contoh: INF untuk Informatika, MAT untuk Matematika')
                            ->maxLength(10)
                            ->required()
                            ->dehydrateStateUsing(fn ($state) => strtoupper($state)),
                    ])
                    ->columns(2)
                    ->visible(fn ($operation) => $operation === 'create'),
            ]);
    }
}
