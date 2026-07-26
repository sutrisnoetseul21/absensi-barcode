<?php

namespace App\Filament\Perpustakaan\Resources\KategoriBukus;

use App\Filament\Perpustakaan\Resources\KategoriBukus\Pages\CreateKategoriBuku;
use App\Filament\Perpustakaan\Resources\KategoriBukus\Pages\EditKategoriBuku;
use App\Filament\Perpustakaan\Resources\KategoriBukus\Pages\ListKategoriBukus;
use App\Filament\Perpustakaan\Resources\KategoriBukus\Schemas\KategoriBukuForm;
use App\Filament\Perpustakaan\Resources\KategoriBukus\Tables\KategoriBukusTable;
use App\Models\KategoriBuku;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class KategoriBukuResource extends Resource
{
    protected static ?string $model = KategoriBuku::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-tag';

    protected static ?string $recordTitleAttribute = 'nama_kategori';

    public static function form(Schema $schema): Schema
    {
        return KategoriBukuForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return KategoriBukusTable::configure($table);
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
            'index' => ListKategoriBukus::route('/'),
            'create' => CreateKategoriBuku::route('/create'),
            'edit' => EditKategoriBuku::route('/{record}/edit'),
        ];
    }
}
