<?php

namespace App\Filament\Akademik\Resources\GuruWali;

use App\Filament\Akademik\Resources\GuruWali\Pages;
use App\Filament\Akademik\Resources\GuruWali\RelationManagers;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\KelompokGuruWali;
use App\Models\PengaturanSekolah;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Filament\Traits\HasSimpleRoleAccess;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;

class KelompokGuruWaliResource extends Resource
{
    use HasSimpleRoleAccess;

    protected static function getModuleRolePrefix(): string
    {
        return 'akademik'; // Atau prefix lain sesuai convention role
    }

    protected static ?string $model = KelompokGuruWali::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-group';
    protected static string|\UnitEnum|null $navigationGroup = 'Guru Wali';
    protected static ?string $navigationLabel = 'Kelompok Dampingan';
    protected static ?string $modelLabel = 'Kelompok';
    protected static ?string $pluralModelLabel = 'Kelompok Dampingan';
    protected static ?string $slug = 'guru-wali/kelompok';
    protected static ?int $navigationSort = 1;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Select::make('teacher_id')
                    ->label('Guru Wali')
                    ->relationship('guru', 'name')
                    ->disabled()
                    ->dehydrated(false)
                    ->helperText('Kelompok dampingan melekat secara permanen pada Guru Wali.'),

                Forms\Components\TextInput::make('nama_kelompok')
                    ->label('Nama Kelompok')
                    ->required()
                    ->maxLength(100)
                    ->placeholder('Contoh: Kelompok Dandelion'),

                Forms\Components\Toggle::make('status_aktif')
                    ->label('Status Penugasan Aktif')
                    ->helperText('Matikan jika guru ini sedang tidak aktif menjalankan tugas Guru Wali.')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('guru.name')
                    ->label('Nama Guru Wali')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('nama_kelompok')
                    ->label('Nama Kelompok')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('anggota_aktif_count')
                    ->label('Siswa Aktif')
                    ->counts('anggotaAktif')
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('anggota_arsip_count')
                    ->label('Siswa Arsip')
                    ->counts('anggotaArsip')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\IconColumn::make('status_aktif')
                    ->label('Status Tugas')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('status_aktif')
                    ->label('Status Tugas Guru Wali'),
            ])
            ->actions([
                Action::make('manage_anggota')
                    ->label('Kelola Anggota')
                    ->icon('heroicon-o-user-group')
                    ->color('primary')
                    ->modalWidth('7xl')
                    ->modalHeading(fn (KelompokGuruWali $record) => "Kelola Anggota - {$record->nama_kelompok}")
                    ->modalSubmitAction(false)
                    ->modalCancelAction(false)
                    ->modalContent(function (KelompokGuruWali $record, $livewire) {
                        if (($livewire->currentKelompokId ?? null) !== $record->id) {
                            $livewire->currentKelompokId = $record->id;
                            $livewire->filterKelasId = null;
                            $livewire->searchKandidat = null;
                        }

                        $academicYearId = PengaturanSekolah::current()?->academic_year_id_active 
                            ?? TahunAjaran::where('status', 'aktif')->first()?->id;

                        $leftStudents = $record->anggotaAktif()->with('siswa')->get();

                        $rightStudents = Siswa::where('status', 'aktif')
                            ->whereDoesntHave('kelompokGuruWali')
                            ->when($livewire->searchKandidat, function ($q) use ($livewire) {
                                $q->where(fn ($sub) => $sub
                                    ->where('name', 'like', '%' . $livewire->searchKandidat . '%')
                                    ->orWhere('nisn', 'like', '%' . $livewire->searchKandidat . '%')
                                );
                            })
                            ->when($livewire->filterKelasId === 'none', function ($q) use ($academicYearId) {
                                $q->whereDoesntHave('enrollments', fn ($eq) =>
                                    $eq->where('academic_year_id', $academicYearId)->where('status', 'aktif')
                                );
                            })
                            ->when($livewire->filterKelasId && $livewire->filterKelasId !== 'none', function ($q) use ($livewire, $academicYearId) {
                                $q->whereHas('enrollments', fn ($eq) =>
                                    $eq->where('academic_year_id', $academicYearId)
                                       ->where('class_id', $livewire->filterKelasId)
                                       ->where('status', 'aktif')
                                );
                            })
                            ->with(['enrollmentAktif.kelas'])
                            ->orderBy('name')
                            ->limit(100)
                            ->get();

                        $daftarKelas = Kelas::orderBy('grade_level')->orderBy('name')->get(['id', 'name', 'grade_level']);

                        return view('filament.resources.guru-wali.pages.anggota-manager-modal', [
                            'kelompok'          => $record,
                            'leftStudents'      => $leftStudents,
                            'rightStudents'     => $rightStudents,
                            'daftarKelas'       => $daftarKelas,
                            'leftStudentsJson'  => $leftStudents->map(fn($a) => [
                                'id'          => $a->id,
                                'student_id'  => $a->student_id,
                                'name'        => $a->siswa?->name ?? '—',
                                'nisn'        => $a->siswa?->nisn ?? '—',
                                'nis'         => $a->siswa?->nis ?? '—',
                                'tahun_masuk' => $a->tahun_masuk,
                            ])->values()->toJson(JSON_UNESCAPED_UNICODE),
                            'rightStudentsJson' => $rightStudents->map(fn($s) => [
                                'id'    => $s->id,
                                'name'  => $s->name,
                                'nisn'  => $s->nisn ?? '—',
                                'nis'   => $s->nis ?? '—',
                                'kelas' => $s->enrollmentAktif?->kelas?->name ?? 'Belum ada kelas',
                            ])->values()->toJson(JSON_UNESCAPED_UNICODE),
                        ]);
                    }),
                EditAction::make(),
            ])
            ->bulkActions([])
            ->defaultSort('created_at', 'asc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\AnggotaSiswaRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListKelompokGuruWali::route('/'),
            'edit'  => Pages\EditKelompokGuruWali::route('/{record}/edit'),
        ];
    }
}
