<?php

namespace App\Filament\Akademik\Resources\MataPelajarans;

use App\Filament\Akademik\Resources\MataPelajarans\Pages\CreateMataPelajaran;
use App\Filament\Akademik\Resources\MataPelajarans\Pages\EditMataPelajaran;
use App\Filament\Akademik\Resources\MataPelajarans\Pages\ListMataPelajarans;
use App\Filament\Akademik\Resources\MataPelajarans\Schemas\MataPelajaranForm;
use App\Filament\Akademik\Resources\MataPelajarans\Tables\MataPelajaransTable;
use App\Models\MataPelajaran;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MataPelajaranResource extends Resource
{
    protected static ?string $model = MataPelajaran::class;

    protected static ?string $modelLabel = 'Mata Pelajaran';
    protected static ?string $pluralModelLabel = 'Mata Pelajaran';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|\UnitEnum|null $navigationGroup = 'Data Master';

    public static function form(Schema $schema): Schema
    {
        return MataPelajaranForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MataPelajaransTable::configure($table);
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
            'index' => ListMataPelajarans::route('/'),
            'create' => CreateMataPelajaran::route('/create'),
        ];
    }
}
