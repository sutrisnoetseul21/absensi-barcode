<?php

namespace App\Filament\Akademik\Resources\UjianAkademik;

use App\Filament\Akademik\Resources\UjianAkademik\Pages\ListUjianAkademik;
use App\Filament\Akademik\Resources\UjianAkademik\Pages\ViewUjianAkademik;
use App\Filament\Akademik\Resources\UjianAkademik\RelationManagers\NilaiUjianRelationManager;
use App\Filament\Akademik\Resources\UjianAkademik\Schemas\UjianAkademikForm;
use App\Filament\Akademik\Resources\UjianAkademik\Tables\UjianAkademikTable;
use App\Filament\Traits\HasSimpleRoleAccess;
use App\Models\UjianAkademik;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class UjianAkademikResource extends Resource
{
    use HasSimpleRoleAccess;

    protected static function getModuleRolePrefix(): string
    {
        return 'akademik';
    }

    protected static ?string $model = UjianAkademik::class;

    protected static ?string $modelLabel = 'Sinkron Nilai CBT';

    protected static ?string $pluralModelLabel = 'Sinkron Nilai CBT';

    protected static ?string $slug = 'ujian-akademik';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;

    protected static ?string $navigationLabel = 'Sinkron Nilai CBT';

    protected static string|\UnitEnum|null $navigationGroup = 'Akademik';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'nama_ujian';

    public static function form(Schema $schema): Schema
    {
        return UjianAkademikForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UjianAkademikTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            NilaiUjianRelationManager::class,
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery();

        $user = auth()->user();

        // Jika user adalah super admin atau admin akademik/master, bisa lihat semua ujian
        if ($user && ($user->isSuperAdmin() || $user->hasRole('admin_akademik_editor') || $user->hasRole('admin_master_editor'))) {
            return $query;
        }

        // Jika user adalah guru, batasi ujian sesuai mata pelajaran yang diajar
        if ($user && $user->guru) {
            $mataPelajaranIds = \App\Models\Pengajaran::where('teacher_id', $user->guru->id)
                ->pluck('mata_pelajaran_id')
                ->unique()
                ->toArray();

            $query->whereIn('mata_pelajaran_id', $mataPelajaranIds);
        }

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListUjianAkademik::route('/'),
            'view'   => ViewUjianAkademik::route('/{record}'),
        ];
    }
}
