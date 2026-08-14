<?php

namespace App\Filament\Perpustakaan\Resources\Bukus;

use App\Filament\Perpustakaan\Resources\Bukus\Pages\CreateBuku;
use App\Filament\Perpustakaan\Resources\Bukus\Pages\EditBuku;
use App\Filament\Perpustakaan\Resources\Bukus\Pages\ListBukus;
use App\Filament\Perpustakaan\Resources\Bukus\Schemas\BukuForm;
use App\Filament\Perpustakaan\Resources\Bukus\Tables\BukusTable;
use App\Models\Buku;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BukuResource extends Resource
{
    protected static ?string $model = Buku::class;

    protected static ?string $slug = 'buku';

    protected static ?string $modelLabel = 'Buku';

    protected static ?string $pluralModelLabel = 'Buku';

    protected static ?string $navigationLabel = 'Buku';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-book-open';

    protected static \UnitEnum|string|null $navigationGroup = 'Koleksi Buku';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'judul';

    public static function form(Schema $schema): Schema
    {
        return BukuForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BukusTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Perpustakaan\Resources\Bukus\BukuResource\RelationManagers\EksemplarBukusRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBukus::route('/'),
            'create' => CreateBuku::route('/create'),
            'edit' => EditBuku::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
