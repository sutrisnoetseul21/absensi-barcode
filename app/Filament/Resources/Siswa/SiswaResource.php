<?php

namespace App\Filament\Resources\Siswa;

use App\Filament\Resources\Siswa\Pages\CreateSiswa;
use App\Filament\Resources\Siswa\Pages\EditSiswa;
use App\Filament\Resources\Siswa\Pages\ListSiswas;
use App\Filament\Resources\Siswa\Schemas\SiswaForm;
use App\Filament\Resources\Siswa\Tables\SiswaTable;
use App\Models\Siswa;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SiswaResource extends Resource
{
    protected static ?string $model = Siswa::class;

    protected static ?string $modelLabel = 'Siswa';

    protected static ?string $pluralModelLabel = 'Siswa';

    protected static ?string $slug = 'siswa';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?string $navigationLabel = 'Siswa';

    protected static string|\UnitEnum|null $navigationGroup = 'Data Master';

    protected static ?int $navigationSort = 4;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return SiswaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SiswaTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListSiswas::route('/'),
            'create' => CreateSiswa::route('/create'),
            'edit'   => EditSiswa::route('/{record}/edit'),
        ];
    }
}
