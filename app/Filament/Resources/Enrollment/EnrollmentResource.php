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
    protected static ?string $model = EnrollmentSiswa::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?string $navigationLabel = 'Enrollment Siswa';

    protected static string|\UnitEnum|null $navigationGroup = 'Data Master';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return EnrollmentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EnrollmentTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListEnrollments::route('/'),
            'create' => CreateEnrollment::route('/create'),
            'edit'   => EditEnrollment::route('/{record}/edit'),
        ];
    }
}
