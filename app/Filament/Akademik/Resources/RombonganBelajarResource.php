<?php

namespace App\Filament\Akademik\Resources;

use App\Filament\Akademik\Resources\RombonganBelajar\Pages\ListRombonganBelajars;
use App\Filament\Akademik\Resources\RombonganBelajar\Tables\RombonganBelajarsTable;
use App\Models\Kelas;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class RombonganBelajarResource extends Resource
{
    protected static ?string $model = Kelas::class;

    protected static ?string $slug = 'rombongan-belajar';

    protected static ?string $modelLabel = 'Rombongan Belajar';
    protected static ?string $pluralModelLabel = 'Rombongan Belajar';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static string|\UnitEnum|null $navigationGroup = 'Akademik';
    
    protected static ?int $navigationSort = 1;

    public static function canCreate(): bool
    {
        return false; // Creation is handled in KelasResource (Data Master)
    }

    public static function table(Table $table): Table
    {
        return RombonganBelajarsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRombonganBelajars::route('/'),
            'pembelajaran' => \App\Filament\Akademik\Resources\Kelas\Pages\ManagePembelajaranKelas::route('/{record}/pembelajaran'),
        ];
    }
}
