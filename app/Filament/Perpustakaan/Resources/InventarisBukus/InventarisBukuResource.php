<?php

namespace App\Filament\Perpustakaan\Resources\InventarisBukus;

use App\Filament\Perpustakaan\Resources\InventarisBukus\Pages\CreateInventarisBuku;
use App\Filament\Perpustakaan\Resources\InventarisBukus\Pages\EditInventarisBuku;
use App\Filament\Perpustakaan\Resources\InventarisBukus\Pages\ListInventarisBukus;
use App\Filament\Perpustakaan\Resources\InventarisBukus\Schemas\InventarisBukuForm;
use App\Filament\Perpustakaan\Resources\InventarisBukus\Tables\InventarisBukusTable;
use App\Models\InventarisBuku;
use BackedEnum;
use Filament\Resources\Resource;
use App\Filament\Traits\HasSimpleRoleAccess;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class InventarisBukuResource extends Resource
{
    use HasSimpleRoleAccess;

    protected static function getModuleRolePrefix(): string
    {
        return 'perpustakaan';
    }

    protected static ?string $model = InventarisBuku::class;

    protected static ?string $modelLabel = 'Inventaris Buku';

    protected static ?string $pluralModelLabel = 'Inventaris Buku';

    protected static ?string $slug = 'inventaris-buku';

    protected static \UnitEnum|string|null $navigationGroup = 'Perpustakaan';

    protected static ?int $navigationSort = 2;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return InventarisBukuForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InventarisBukusTable::configure($table);
    }

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return false;
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
            'index' => ListInventarisBukus::route('/'),
            'create' => CreateInventarisBuku::route('/create'),
            'edit' => EditInventarisBuku::route('/{record}/edit'),
        ];
    }
}
