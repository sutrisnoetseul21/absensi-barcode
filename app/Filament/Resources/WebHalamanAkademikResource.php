<?php

namespace App\Filament\Resources;

use App\Filament\Components\TinyEditor;
use App\Filament\Resources\WebHalamanAkademikResource\Pages;
use App\Models\WebHalamanAkademik;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class WebHalamanAkademikResource extends Resource
{
    protected static ?string $model = WebHalamanAkademik::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-academic-cap';

    protected static string|\UnitEnum|null $navigationGroup = 'Web Profil Sekolah';

    protected static ?string $navigationLabel = 'Halaman Akademik';

    protected static ?string $pluralModelLabel = 'Halaman Akademik';

    protected static ?string $modelLabel = 'Halaman Akademik';

    protected static ?string $slug = 'akademik/halaman';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Utama Akademik')
                    ->description('Tentukan judul halaman akademik, alamat tautan (slug), kategori, dan urutan tampil.')
                    ->schema([
                        Forms\Components\TextInput::make('judul')
                            ->label('Judul Halaman Akademik')
                            ->placeholder('Contoh: Kalender Pendidikan 2026/2027, Kurikulum Merdeka, Jadwal KBM')
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
                            ->placeholder('kalender-pendidikan-2026-2027')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText('Digunakan untuk tautan publik (/akademik/{slug}).')
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('kategori')
                            ->label('Kategori')
                            ->placeholder('Contoh: Kurikulum, Kalender Pendidikan, Jadwal, Panduan')
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

                Section::make('Ringkasan & Dokumen Lampiran')
                    ->description('Ringkasan informasi dan dokumen PDF resmi (misal: Kalender Akademik PDF, Silabus/KSP).')
                    ->schema([
                        Forms\Components\Textarea::make('deskripsi_singkat')
                            ->label('Deskripsi Singkat')
                            ->placeholder('Tuliskan ringkasan singkat halaman akademik ini...')
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\FileUpload::make('file_pdf')
                            ->label('Dokumen Lampiran Resmi (PDF)')
                            ->acceptedFileTypes(['application/pdf'])
                            ->disk('public')
                            ->directory('akademik-pdf')
                            ->maxSize(15360)
                            ->downloadable()
                            ->openable()
                            ->helperText('Unggah berkas resmi dalam format PDF (maks. 15MB).')
                            ->columnSpanFull(),
                    ]),

                Section::make('Konten & Rincian Pembelajaran')
                    ->description('Panduan lengkap, penjelasan struktur kurikulum, tabel jadwal, atau diagram alur.')
                    ->schema([
                        TinyEditor::make('konten')
                            ->label('Konten Lengkap')
                            ->helperText('Tuliskan rincian penjelasan, tabel mata pelajaran, alur kurikulum, atau panduan asesmen.')
                            ->height(550)
                            ->uploadDirectory('web-akademik/editor')
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
                    ->label('Judul Akademik')
                    ->description(fn (WebHalamanAkademik $record): string => '/akademik/' . $record->slug)
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('kategori')
                    ->label('Kategori')
                    ->badge()
                    ->color('primary')
                    ->placeholder('-')
                    ->sortable(),

                Tables\Columns\IconColumn::make('file_pdf')
                    ->label('PDF')
                    ->boolean()
                    ->trueIcon('heroicon-o-document-arrow-down')
                    ->falseIcon('heroicon-o-minus')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->getStateUsing(fn (WebHalamanAkademik $record): bool => !empty($record->file_pdf))
                    ->url(fn (WebHalamanAkademik $record): ?string => $record->file_pdf ? asset('storage/' . $record->file_pdf) : null, shouldOpenInNewTab: true)
                    ->tooltip(fn (WebHalamanAkademik $record): string => $record->file_pdf ? 'Klik untuk melihat berkas PDF' : 'Belum ada PDF')
                    ->alignCenter(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status Aktif')
                    ->boolean()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Terakhir Diperbarui')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListWebHalamanAkademiks::route('/'),
            'create' => Pages\CreateWebHalamanAkademik::route('/create'),
            'edit'   => Pages\EditWebHalamanAkademik::route('/{record}/edit'),
        ];
    }
}
