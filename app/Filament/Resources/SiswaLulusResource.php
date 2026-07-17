<?php

namespace App\Filament\Resources;

use App\Actions\Student\ReactivateStudentAction;
use App\Models\PengaturanSekolah;
use App\Models\Siswa;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

class SiswaLulusResource extends Resource
{
    protected static ?string $model = Siswa::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;

    protected static ?string $navigationLabel = 'Siswa Lulus';

    protected static ?string $modelLabel = 'Siswa Lulus';

    protected static ?string $pluralModelLabel = 'Siswa Lulus';

    protected static ?string $slug = 'siswa-lulus';

    protected static string|\UnitEnum|null $navigationGroup = 'Data Master';

    protected static ?int $navigationSort = 6;

    /**
     * Hanya Superadmin yang boleh akses menu ini.
     */
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
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'lulus'))
            ->columns([
                ImageColumn::make('photo_path')
                    ->label('Foto')
                    ->disk('public')
                    ->circular()
                    ->defaultImageUrl(url('https://ui-avatars.com/api/?name=Siswa&color=7F9CF5&background=EBF4FF')),

                TextColumn::make('nisn')
                    ->label('NISN')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('nis')
                    ->label('NIS')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Nama Siswa')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('kelas_terakhir')
                    ->label('Kelas Terakhir')
                    ->getStateUsing(function (Siswa $record) {
                        $lastEnrollment = $record->enrollments()
                            ->with('kelas', 'tahunAjaran')
                            ->latest()
                            ->first();
                        if (!$lastEnrollment) return '—';
                        return ($lastEnrollment->kelas?->name ?? '—')
                            . ' (TA ' . ($lastEnrollment->tahunAjaran?->name ?? '—') . ')';
                    }),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color('success'),
            ])
            ->recordActions([
                // Batalkan kelulusan (kembalikan ke Aktif)
                Action::make('batalkan_kelulusan')
                    ->label('Aktifkan Kembali')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Batalkan Kelulusan Siswa')
                    ->modalDescription('Siswa ini akan dikembalikan ke status Aktif. Status enrollment kelulusan juga akan dibatalkan. Apakah Anda yakin?')
                    ->action(function (Siswa $record) {
                        $activeYearId = PengaturanSekolah::current()?->academic_year_id_active;
                        (new ReactivateStudentAction)->cancelGraduation($record, $activeYearId);

                        Notification::make()
                            ->title('Kelulusan Dibatalkan')
                            ->body("Siswa **{$record->name}** telah dikembalikan ke status Aktif.")
                            ->success()
                            ->send();
                    }),
            ])
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\SiswaLulusResource\Pages\ListSiswaLulus::route('/'),
        ];
    }
}
