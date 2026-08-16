<?php

namespace App\Filament\Akademik\Resources\Jabatans;

use App\Filament\Akademik\Resources\Jabatans\Pages\CreateJabatan;
use App\Filament\Akademik\Resources\Jabatans\Pages\EditJabatan;
use App\Filament\Akademik\Resources\Jabatans\Pages\ListJabatans;
use App\Filament\Akademik\Resources\Jabatans\Schemas\JabatanForm;
use App\Filament\Akademik\Resources\Jabatans\Tables\JabatansTable;
use App\Models\Jabatan;
use BackedEnum;
use Filament\Resources\Resource;
use App\Filament\Traits\HasSimpleRoleAccess;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class JabatanResource extends Resource
{
    use HasSimpleRoleAccess;

    protected static function getModuleRolePrefix(): string
    {
        return 'master';
    }

    protected static ?string $model = Jabatan::class;

    protected static ?string $modelLabel = 'Jabatan';
    protected static ?string $pluralModelLabel = 'Jabatan';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|\UnitEnum|null $navigationGroup = 'Data Master';
    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return JabatanForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return JabatansTable::configure($table);
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
            'index' => ListJabatans::route('/'),
            'create' => CreateJabatan::route('/create'),
        ];
    }
}
