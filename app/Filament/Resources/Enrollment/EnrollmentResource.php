<?php

namespace App\Filament\Resources\Enrollment;

use App\Filament\Resources\Enrollment\Pages\CreateEnrollment;
use App\Filament\Resources\Enrollment\Pages\EditEnrollment;
use App\Filament\Resources\Enrollment\Pages\ListEnrollments;
use App\Filament\Resources\Enrollment\Schemas\EnrollmentForm;
use App\Filament\Resources\Enrollment\Tables\EnrollmentTable;
use App\Models\EnrollmentSiswa;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class EnrollmentResource extends Resource
{
    protected static ?string $model = \App\Models\Kelas::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?string $navigationLabel = 'Pendaftaran Kelas';

    protected static ?string $pluralLabel = 'Pendaftaran Kelas';

    protected static ?string $modelLabel = 'Pendaftaran Kelas';

    protected static string|\UnitEnum|null $navigationGroup = 'Data Master';

    protected static ?int $navigationSort = 5;

    public static function canViewAny(): bool
    {
        return auth()->user()?->isSuperAdmin() ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    public static function table(Table $table): Table
    {
        return EnrollmentTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListEnrollments::route('/'),
        ];
    }
}
