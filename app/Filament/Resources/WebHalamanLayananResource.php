<?php

namespace App\Filament\Resources;

use App\Filament\Components\TinyEditor;
use App\Filament\Resources\WebHalamanLayananResource\Pages;
use App\Models\WebHalamanLayanan;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class WebHalamanLayananResource extends Resource
{
    protected static ?string $model = WebHalamanLayanan::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-duplicate';

    protected static string|\UnitEnum|null $navigationGroup = 'Layanan Publik';

    protected static ?string $navigationLabel = 'Halaman Layanan';

    protected static ?string $pluralModelLabel = 'Halaman Layanan';

    protected static ?string $modelLabel = 'Halaman Layanan';

    protected static ?string $slug = 'layanan/halaman';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Utama Layanan')
                    ->description('Tentukan judul layanan, alamat tautan (slug), kategori, biaya, dan estimasi waktu.')
                    ->schema([
                        Forms\Components\TextInput::make('judul')
                            ->label('Judul Layanan / SOP')
                            ->placeholder('Contoh: Alur Layanan Ijazah, Legalisir Dokumen, dll.')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (string $operation, ?string $state, Set $set) {
                                if ($operation === 'create' && filled($state)) {
                                    $set('slug', Str::slug($state));
                                }
                            })
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('slug')
                            ->label('Slug (URL)')
                            ->placeholder('alur-layanan-ijazah')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText('Digunakan untuk tautan publik (/layanan-publik/{slug}).')
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('kategori')
                            ->label('Kategori / Bidang Layanan')
                            ->placeholder('Contoh: Kesiswaan, Tata Usaha, Kurikulum')
                            ->maxLength(255)
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('biaya')
                            ->label('Biaya Layanan')
                            ->default('Gratis (Rp 0)')
                            ->placeholder('Contoh: Gratis (Rp 0)')
                            ->maxLength(255)
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('waktu_layanan')
                            ->label('Estimasi Waktu Layanan')
                            ->default('15 - 30 Menit')
                            ->placeholder('Contoh: 15 - 30 Menit, 1 Hari Kerja')
                            ->maxLength(255)
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('urutan')
                            ->label('Urutan Tampil')
                            ->numeric()
                            ->default(0)
                            ->helperText('Angka lebih kecil akan tampil lebih awal pada daftar publik.')
                            ->columnSpan(1),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Status Publikasi (Tampilkan ke Publik)')
                            ->default(true)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Ringkasan & Dokumen SOP')
                    ->description('Ringkasan persyaratan atau alur singkat, serta dokumen SOP resmi dalam format PDF.')
                    ->schema([
                        Forms\Components\Textarea::make('deskripsi_singkat')
                            ->label('Deskripsi Singkat')
                            ->placeholder('Tuliskan ringkasan alur atau persyaratan layanan ini...')
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\FileUpload::make('file_pdf')
                            ->label('Dokumen SOP Resmi (PDF)')
                            ->acceptedFileTypes(['application/pdf'])
                            ->disk('public')
                            ->directory('layanan-pdf')
                            ->maxSize(10240)
                            ->downloadable()
                            ->openable()
                            ->helperText('Unggah berkas SOP/Panduan/Formulir resmi dalam format PDF (maks. 10MB).')
                            ->columnSpanFull(),
                    ]),

                Section::make('Konten & Alur Pelayanan')
                    ->description('Panduan lengkap alur pelayanan, diagram prosedur, dan rincian persyaratan.')
                    ->schema([
                        TinyEditor::make('konten')
                            ->label('Konten Alur & Prosedur')
                            ->helperText('Tuliskan rincian langkah alur pelayanan, tabel berkas persyaratan, bagan, atau dokumen terkait.')
                            ->height(550)
                            ->uploadDirectory('web-layanan/editor')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('urutan')
                    ->label('Urutan')
                    ->sortable()
                    ->alignCenter()
                    ->width('70px'),

                Tables\Columns\TextColumn::make('judul')
                    ->label('Judul Layanan')
                    ->description(fn (WebHalamanLayanan $record): string => '/layanan-publik/' . $record->slug)
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('kategori')
                    ->label('Kategori')
                    ->badge()
                    ->color('primary')
                    ->placeholder('-')
                    ->sortable(),

                Tables\Columns\TextColumn::make('waktu_layanan')
                    ->label('Durasi')
                    ->icon('heroicon-o-clock')
                    ->color('gray')
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('biaya')
                    ->label('Biaya')
                    ->icon('heroicon-o-banknotes')
                    ->color('gray')
                    ->placeholder('-'),

                Tables\Columns\IconColumn::make('file_pdf')
                    ->label('SOP (PDF)')
                    ->boolean()
                    ->trueIcon('heroicon-o-document-arrow-down')
                    ->falseIcon('heroicon-o-minus')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->getStateUsing(fn (WebHalamanLayanan $record): bool => !empty($record->file_pdf))
                    ->url(fn (WebHalamanLayanan $record): ?string => $record->file_pdf ? asset('storage/' . $record->file_pdf) : null, shouldOpenInNewTab: true)
                    ->tooltip(fn (WebHalamanLayanan $record): string => $record->file_pdf ? 'Klik untuk melihat berkas PDF' : 'Belum ada PDF')
                    ->alignCenter(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status Aktif')
                    ->boolean()
                    ->alignCenter(),
            ])
            ->defaultSort('urutan', 'asc')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Publikasi')
                    ->trueLabel('Aktif Saja')
                    ->falseLabel('Nonaktif Saja'),
            ])
            ->actions([
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWebHalamanLayanans::route('/'),
            'create' => Pages\CreateWebHalamanLayanan::route('/create'),
            'edit' => Pages\EditWebHalamanLayanan::route('/{record}/edit'),
        ];
    }
}
