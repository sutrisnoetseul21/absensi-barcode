<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WebMicrositeResource\Pages;
use App\Models\WebMicrosite;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class WebMicrositeResource extends Resource
{
    protected static ?string $model = WebMicrosite::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-globe-alt';

    protected static string|\UnitEnum|null $navigationGroup = 'Web Profil Sekolah';

    protected static ?string $navigationLabel = 'Microsite Sekolah';

    protected static ?string $pluralModelLabel = 'Microsite Sekolah';

    protected static ?string $modelLabel = 'Microsite Sekolah';

    protected static ?string $slug = 'microsite-sekolah';

    protected static ?int $navigationSort = 6;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Microsite Sekolah')
                    ->description('Kelola tautan microsite resmi sekolah seperti Google Sites, Canva, Linktree, atau modul pembelajaran digital.')
                    ->schema([
                        Forms\Components\TextInput::make('judul')
                            ->label('Judul Microsite')
                            ->placeholder('Contoh: Portal Pembelajaran Digital, Canva Profil Sekolah, PPDB Online')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(2),

                        Forms\Components\TextInput::make('url')
                            ->label('Tautan URL')
                            ->placeholder('https://sites.google.com/view/... atau https://canva.com/...')
                            ->url()
                            ->required()
                            ->maxLength(500)
                            ->columnSpan(2),

                        Forms\Components\Textarea::make('deskripsi')
                            ->label('Deskripsi Singkat')
                            ->placeholder('Deskripsi ringkas mengenai peruntukan atau isi dari microsite ini.')
                            ->rows(3)
                            ->nullable()
                            ->columnSpan(2),

                        Forms\Components\TextInput::make('kategori')
                            ->label('Kategori')
                            ->placeholder('Contoh: Utama, Pembelajaran, Kurikulum, Profil, Layanan')
                            ->default('Utama')
                            ->maxLength(100),

                        Forms\Components\TextInput::make('icon')
                            ->label('Ikon FontAwesome')
                            ->placeholder('fas fa-globe')
                            ->default('fas fa-globe')
                            ->maxLength(100),

                        Forms\Components\TextInput::make('button_text')
                            ->label('Teks Tombol Tautan')
                            ->placeholder('Kunjungi Microsite')
                            ->default('Kunjungi Microsite')
                            ->required()
                            ->maxLength(100),

                        Forms\Components\TextInput::make('urutan')
                            ->label('Urutan Tampil')
                            ->numeric()
                            ->default(0)
                            ->helperText('Angka lebih kecil akan tampil lebih awal di halaman publik.'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Status Aktif (Tampilkan di Publik)')
                            ->default(true)
                            ->columnSpan(2),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('urutan')
                    ->label('Urutan')
                    ->sortable()
                    ->width(70),

                Tables\Columns\TextColumn::make('judul')
                    ->label('Judul Microsite')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('kategori')
                    ->label('Kategori')
                    ->badge()
                    ->color('primary')
                    ->searchable(),

                Tables\Columns\TextColumn::make('url')
                    ->label('Tautan URL')
                    ->limit(40)
                    ->url(fn ($record) => $record->url, shouldOpenInNewTab: true)
                    ->color('info')
                    ->copyable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('urutan', 'asc')
            ->reorderable('urutan')
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

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListWebMicrosites::route('/'),
            'create' => Pages\CreateWebMicrosite::route('/create'),
            'edit'   => Pages\EditWebMicrosite::route('/{record}/edit'),
        ];
    }
}
