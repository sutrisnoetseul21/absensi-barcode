<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WebPrestasiResource\Pages;
use App\Models\WebArtikel;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class WebPrestasiResource extends Resource
{
    protected static ?string $model = WebArtikel::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-trophy';
    protected static string|\UnitEnum|null $navigationGroup = 'Web Profil Sekolah';
    protected static ?string $navigationLabel = 'Prestasi Sekolah';
    protected static ?string $pluralModelLabel = 'Prestasi Sekolah';
    protected static ?string $modelLabel = 'Prestasi';
    protected static ?string $slug = 'web-prestasis';
    protected static ?int $navigationSort = 3;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('tipe', 'prestasi');
    }

    public static function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema
            ->components([
                Forms\Components\Hidden::make('tipe')
                    ->default('prestasi'),
                Forms\Components\TextInput::make('judul')
                    ->label('Judul Prestasi')
                    ->placeholder('Contoh: Juara 1 Lomba OSN Matematika Tingkat Kabupaten')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Forms\Components\RichEditor::make('konten')
                    ->label('Detail & Cerita Capaian Prestasi')
                    ->placeholder('Tuliskan nama siswa/tim peraih, nama pembimbing, penyelenggara, dan deskripsi perlombaan...')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\FileUpload::make('thumbnail')
                    ->label('Foto Dokumentasi / Penyerahan Piala / Piagam')
                    ->image()
                    ->disk('public')
                    ->directory('web-artikel')
                    ->imageEditor()
                    ->maxSize(10240)
                    ->columnSpanFull(),
                Forms\Components\DateTimePicker::make('published_at')
                    ->label('Tanggal Prestasi')
                    ->default(now()),
                Forms\Components\Toggle::make('is_published')
                    ->label('Tampilkan di Website?')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('thumbnail')
                    ->label('Foto')
                    ->disk('public')
                    ->size(80),
                Tables\Columns\TextColumn::make('judul')
                    ->label('Judul Prestasi')
                    ->searchable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('published_at')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_published')
                    ->boolean()
                    ->label('Aktif'),
            ])
            ->defaultSort('published_at', 'desc')
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                    \Filament\Actions\ForceDeleteBulkAction::make(),
                    \Filament\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageWebPrestasis::route('/'),
        ];
    }
}
