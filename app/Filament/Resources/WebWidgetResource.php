<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WebWidgetResource\Pages;
use App\Models\WebWidget;
use App\Filament\Components\IconPickerField;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class WebWidgetResource extends Resource
{
    protected static ?string $model = WebWidget::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-link';
    protected static string|\UnitEnum|null $navigationGroup = 'Web Profil Sekolah';
    protected static ?string $navigationLabel = 'Widget / Tautan';
    protected static ?string $pluralModelLabel = 'Widget / Tautan';
    protected static ?int $navigationSort = 5;

    public static function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema
            ->components([
                Forms\Components\TextInput::make('nama_widget')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('url_link')
                    ->required()
                    ->url()
                    ->maxLength(255),
                IconPickerField::make('icon')
                    ->label('Icon')
                    ->helperText('Pilih icon dari daftar yang tersedia.'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_widget')
                    ->searchable(),
                Tables\Columns\TextColumn::make('url_link')
                    ->limit(50),
                Tables\Columns\TextColumn::make('icon'),
            ])
            ->reorderable('urutan')
            ->defaultSort('urutan')
            ->filters([
                //
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageWebWidgets::route('/'),
        ];
    }
}
