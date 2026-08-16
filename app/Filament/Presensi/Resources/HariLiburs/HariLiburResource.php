<?php

namespace App\Filament\Presensi\Resources\HariLiburs;

use App\Filament\Presensi\Resources\HariLiburs\Pages\CreateHariLibur;
use App\Filament\Presensi\Resources\HariLiburs\Pages\EditHariLibur;
use App\Filament\Presensi\Resources\HariLiburs\Pages\ListHariLiburs;
use App\Filament\Presensi\Resources\HariLiburs\Schemas\HariLiburForm;
use App\Filament\Presensi\Resources\HariLiburs\Tables\HariLibursTable;
use App\Models\HariLibur;
use BackedEnum;
use Filament\Resources\Resource;
use App\Filament\Traits\HasSimpleRoleAccess;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class HariLiburResource extends Resource
{
    use HasSimpleRoleAccess;

    protected static function getModuleRolePrefix(): string
    {
        return 'presensi';
    }

    protected static ?string $model = HariLibur::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';
    protected static string|\UnitEnum|null $navigationGroup = 'Presensi';
    protected static ?int $navigationSort = 7;
    protected static ?string $modelLabel = 'Pengaturan Hari Libur';
    protected static ?string $pluralModelLabel = 'Pengaturan Hari Libur';
    protected static ?string $navigationLabel = 'Pengaturan Hari Libur';
    protected static ?string $slug = 'pengaturan-hari-libur';

    public static function form(Schema $schema): Schema
    {
        return HariLiburForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HariLibursTable::configure($table);
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
            'index' => ListHariLiburs::route('/'),
            'create' => CreateHariLibur::route('/create'),
            'edit' => EditHariLibur::route('/{record}/edit'),
        ];
    }
}
