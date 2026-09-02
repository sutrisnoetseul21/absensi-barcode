<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WebStatisticResource\Pages;
use App\Models\WebStatistic;
use App\Filament\Components\IconPickerField;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class WebStatisticResource extends Resource
{
    protected static ?string $model = WebStatistic::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';
    protected static string|\UnitEnum|null $navigationGroup = 'Web Profil Sekolah';
    protected static ?string $navigationLabel = 'Statistik & Info';
    protected static ?string $modelLabel = 'Statistik';
    protected static ?int $navigationSort = 3;

    public static function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema
            ->schema([
                IconPickerField::make('icon'),
                Forms\Components\TextInput::make('value')
                    ->label('Nilai / Angka')
                    ->placeholder('A+')
                    ->required(),
                Forms\Components\TextInput::make('label')
                    ->label('Keterangan')
                    ->placeholder('Akreditasi')
                    ->required(),
                Forms\Components\TextInput::make('order')
                    ->label('Urutan')
                    ->numeric()
                    ->default(0),
            ]);
    }

    public static function table(\Filament\Tables\Table $table): \Filament\Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order')
                    ->label('Urutan')
                    ->sortable()
                    ->width(80),
                Tables\Columns\TextColumn::make('icon')
                    ->label('Icon Class')
                    ->copyable(),
                Tables\Columns\TextColumn::make('value')
                    ->label('Nilai')
                    ->searchable(),
                Tables\Columns\TextColumn::make('label')
                    ->label('Keterangan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->defaultSort('order', 'asc')
            ->reorderable('order')
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
            'index' => Pages\ListWebStatistics::route('/'),
            'create' => Pages\CreateWebStatistic::route('/create'),
            'edit' => Pages\EditWebStatistic::route('/{record}/edit'),
        ];
    }
}
