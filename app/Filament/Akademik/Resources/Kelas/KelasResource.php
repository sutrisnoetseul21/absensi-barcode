<?php

namespace App\Filament\Akademik\Resources\Kelas;

use App\Filament\Akademik\Resources\Kelas\Pages\CreateKelas;
use App\Filament\Akademik\Resources\Kelas\Pages\EditKelas;
use App\Filament\Akademik\Resources\Kelas\Pages\ListKelas;
use App\Filament\Akademik\Resources\Kelas\RelationManagers\KelasAjaranRelationManager;
use App\Filament\Akademik\Resources\Kelas\Schemas\KelasForm;
use App\Filament\Akademik\Resources\Kelas\Tables\KelasTable;
use App\Models\Kelas;
use BackedEnum;
use Filament\Resources\Resource;
use App\Filament\Traits\HasSimpleRoleAccess;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class KelasResource extends Resource
{
    use HasSimpleRoleAccess;

    protected static function getModuleRolePrefix(): string
    {
        return 'akademik';
    }

    protected static ?string $model = Kelas::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingLibrary;

    protected static ?string $navigationLabel = 'Kelas';

    protected static string|\UnitEnum|null $navigationGroup = 'Akademik';
    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return KelasForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return KelasTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            KelasAjaranRelationManager::class,
            \App\Filament\Akademik\Resources\Kelas\RelationManagers\PengajaranRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListKelas::route('/'),
            'create' => CreateKelas::route('/create'),
        ];
    }
}
