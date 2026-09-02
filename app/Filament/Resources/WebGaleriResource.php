<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WebGaleriResource\Pages;
use App\Models\WebGaleri;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class WebGaleriResource extends Resource
{
    protected static ?string $model = WebGaleri::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-photo';
    protected static string|\UnitEnum|null $navigationGroup = 'Web Profil Sekolah';
    protected static ?string $navigationLabel = 'Galeri Foto';
    protected static ?string $pluralModelLabel = 'Galeri Foto';
    protected static ?int $navigationSort = 4;

    public static function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema
            ->components([
                Forms\Components\TextInput::make('judul')
                    ->maxLength(255)
                    ->columnSpanFull(),
                Forms\Components\FileUpload::make('foto_path')
                    ->required()
                    ->image()
                    ->disk('public')
                    ->directory('web-galeri')
                    ->imageEditor()
                    ->maxSize(10240)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('foto_path')
                    ->disk('public')
                    ->size(100),
                Tables\Columns\TextColumn::make('judul')
                    ->searchable(),
            ])
            ->reorderable('urutan')
            ->defaultSort('urutan')
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
            'index' => Pages\ManageWebGaleris::route('/'),
        ];
    }
}
