<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WebArtikelResource\Pages;
use App\Models\WebArtikel;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class WebArtikelResource extends Resource
{
    protected static ?string $model = WebArtikel::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-newspaper';
    protected static string|\UnitEnum|null $navigationGroup = 'Web Profil Sekolah';
    protected static ?string $navigationLabel = 'Artikel & Pengumuman';
    protected static ?string $pluralModelLabel = 'Artikel & Pengumuman';
    protected static ?int $navigationSort = 3;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->whereIn('tipe', ['berita', 'pengumuman']);
    }

    public static function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema
            ->components([
                Forms\Components\TextInput::make('judul')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('tipe')
                    ->options([
                        'berita' => 'Berita',
                        'pengumuman' => 'Pengumuman',
                    ])
                    ->required(),
                Forms\Components\RichEditor::make('konten')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\FileUpload::make('thumbnail')
                    ->image()
                    ->disk('public')
                    ->directory('web-artikel')
                    ->imageEditor()
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('meta_description')
                    ->maxLength(160)
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('is_published')
                    ->label('Publish Sekarang?')
                    ->default(true),
                Forms\Components\DateTimePicker::make('published_at')
                    ->label('Tanggal Publish (Kosongkan jika langsung)'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('thumbnail')
                    ->disk('public'),
                Tables\Columns\TextColumn::make('judul')
                    ->searchable(),
                Tables\Columns\BadgeColumn::make('tipe')
                    ->colors([
                        'primary' => 'berita',
                        'warning' => 'pengumuman',
                    ]),
                Tables\Columns\IconColumn::make('is_published')
                    ->boolean()
                    ->label('Published'),
                Tables\Columns\TextColumn::make('published_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('tipe')
                    ->options([
                        'berita' => 'Berita',
                        'pengumuman' => 'Pengumuman',
                    ]),
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
            'index' => Pages\ManageWebArtikels::route('/'),
        ];
    }
}
