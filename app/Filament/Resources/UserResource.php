<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\Schemas\UserForm;
use App\Filament\Resources\UserResource\Tables\UserTable;
use App\Models\User;
use Filament\Resources\Resource;
use App\Filament\Traits\HasSimpleRoleAccess;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class UserResource extends Resource
{
    use HasSimpleRoleAccess;

    protected static function getModuleRolePrefix(): string
    {
        return 'akses';
    }

    protected static ?string $model = User::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Manajemen User dan Akses';

    protected static ?string $modelLabel = 'User dan Akses';

    protected static ?string $pluralModelLabel = 'Manajemen User dan Akses';

    protected static string|\UnitEnum|null $navigationGroup = 'Manajemen Akses';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UserTable::configure($table);
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        if (! parent::canDelete($record) || ! $record instanceof User) {
            return false;
        }

        return $record->id !== auth()->id()
            && ! $record->isGuruUser();
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->with(['teacher', 'guru', 'roles'])
            ->whereDoesntHave('student');
    }
}
