<?php

namespace App\Filament\Perpustakaan\Resources\KlasifikasiDdcs;

use App\Filament\Perpustakaan\Resources\KlasifikasiDdcs\Pages\ManageKlasifikasiDdcs;
use App\Models\KlasifikasiDdc;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class KlasifikasiDdcResource extends Resource
{
    protected static ?string $model = KlasifikasiDdc::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'kategori';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('kategori')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('kategori')
            ->columns([
                TextColumn::make('kategori')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageKlasifikasiDdcs::route('/'),
        ];
    }
}
