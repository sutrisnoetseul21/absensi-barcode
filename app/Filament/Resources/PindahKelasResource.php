<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PindahKelasResource\Pages;
use App\Models\EnrollmentSiswa;
use App\Models\Kelas;
use App\Models\PengaturanSekolah;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PindahKelasResource extends Resource
{
    protected static ?string $model = EnrollmentSiswa::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';
    protected static ?string $navigationLabel = 'Pindah Kelas';
    protected static ?string $pluralLabel = 'Pindah Kelas';
    protected static ?string $modelLabel = 'Siswa';
    protected static ?string $navigationGroup = 'Akademik';
    protected static ?int $navigationSort = 2;

    public static function canViewAny(): bool
    {
        return auth()->user()?->isSuperAdmin() ?? false;
    }

    // Hanya tampilkan enrollment aktif pada tahun ajaran aktif
    public static function getEloquentQuery(): Builder
    {
        $activeYearId = PengaturanSekolah::current()?->academic_year_id_active;

        return parent::getEloquentQuery()
            ->where('academic_year_id', $activeYearId)
            ->where('status', 'aktif')
            ->whereHas('student'); // Pastikan siswanya ada
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('class_id')
                    ->label('Kelas Tujuan')
                    ->options(Kelas::pluck('name', 'id'))
                    ->required()
                    ->searchable()
                    ->native(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('student.nisn')
                    ->label('NISN')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('student.name')
                    ->label('Nama Siswa')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('kelas.name')
                    ->label('Kelas Saat Ini')
                    ->badge()
                    ->color('success')
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('class_id')
                    ->label('Filter Kelas')
                    ->options(Kelas::pluck('name', 'id'))
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Pindah Kelas')
                    ->icon('heroicon-o-arrows-right-left')
                    ->color('warning')
                    ->modalHeading(fn(EnrollmentSiswa $record) => 'Pindah Kelas: ' . ($record->student?->name))
                    ->modalDescription('Pilih kelas baru untuk memindahkan siswa ini di tahun ajaran aktif.')
                    ->modalWidth('sm'),
            ])
            ->bulkActions([
                // Kosongkan karena kita fokus perpindahan individual
            ])
            ->paginated([10, 25, 50, 100]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManagePindahKelas::route('/'),
        ];
    }
}
