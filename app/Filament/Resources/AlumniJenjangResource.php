<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AlumniJenjangResource\Pages;
use App\Models\AlumniJenjang;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AlumniJenjangResource extends Resource
{
    protected static ?string $model = AlumniJenjang::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-tag';
    protected static string|\UnitEnum|null $navigationGroup = 'Data Alumni';
    protected static ?string $navigationLabel = 'Pilihan Jenjang';
    protected static ?string $pluralModelLabel = 'Jenjang Lanjutan Alumni';
    protected static ?string $modelLabel = 'Jenjang';
    protected static ?string $slug = 'alumni/jenjang';
    protected static ?int $navigationSort = 2;

    public static function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema
            ->components([
                Forms\Components\TextInput::make('nama_jenjang')
                    ->label('Nama Jenjang Lanjutan')
                    ->required()
                    ->placeholder('Contoh: SMA, SMK, Perguruan Tinggi')
                    ->maxLength(255)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_jenjang')
                    ->label('Nama Jenjang')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('alumnis_count')
                    ->label('Jumlah Alumni')
                    ->counts('alumnis')
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable(),
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
            'index' => Pages\ManageAlumniJenjangs::route('/'),
        ];
    }
}
