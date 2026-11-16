<?php

namespace App\Filament\Akademik\Resources\Ekstrakurikuler;

use App\Filament\Akademik\Resources\Ekstrakurikuler\Pages;
use App\Filament\Akademik\Resources\Ekstrakurikuler\RelationManagers;
use App\Filament\Traits\HasSimpleRoleAccess;
use App\Models\Ekstrakurikuler;
use App\Models\Kelas;
use App\Models\PengaturanSekolah;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Validation\Rules\Unique;

class EkstrakurikulerResource extends Resource
{
    use HasSimpleRoleAccess;

    protected static function getModuleRolePrefix(): string
    {
        return 'akademik';
    }

    protected static ?string $model = Ekstrakurikuler::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-sparkles';
    protected static string|\UnitEnum|null $navigationGroup = 'Ekstrakurikuler';
    protected static ?string $navigationLabel = 'Data Ekstrakurikuler';
    protected static ?string $modelLabel = 'Ekstrakurikuler';
    protected static ?string $pluralModelLabel = 'Data Ekstrakurikuler';
    protected static ?string $slug = 'ekstrakurikuler/data';
    protected static ?int $navigationSort = 1;

    public static function getActiveAcademicYearId(): ?string
    {
        return PengaturanSekolah::current()?->academic_year_id_active
            ?? TahunAjaran::where('status', 'aktif')->value('id');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Select::make('academic_year_id')
                    ->label('Tahun Ajaran')
                    ->options(fn () => TahunAjaran::orderBy('start_year', 'desc')->pluck('name', 'id'))
                    ->default(fn () => static::getActiveAcademicYearId())
                    ->searchable()
                    ->preload()
                    ->live()
                    ->required()
                    ->helperText('Otomatis memilih Tahun Ajaran Aktif. Tetap dapat memilih tahun ajaran lain jika diperlukan.'),

                Forms\Components\TextInput::make('nama_ekskul')
                    ->label('Nama Ekstrakurikuler')
                    ->required()
                    ->maxLength(120)
                    ->placeholder('Contoh: Pramuka, Futsal, PMR, Hadroh')
                    ->unique(
                        table: Ekstrakurikuler::class,
                        column: 'nama_ekskul',
                        ignoreRecord: true,
                        modifyRuleUsing: function (Unique $rule, $get) {
                            return $rule
                                ->where('academic_year_id', $get('academic_year_id'))
                                ->whereNull('deleted_at');
                        }
                    )
                    ->validationMessages([
                        'unique' => 'Nama ekstrakurikuler sudah terdaftar pada tahun ajaran yang dipilih.',
                    ]),

                Forms\Components\Select::make('pembinas')
                    ->label('Guru Pembina')
                    ->relationship('pembinas', 'name')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->required()
                    ->minItems(1)
                    ->helperText('Diambil dari data guru. Anda dapat memilih lebih dari satu pembina.'),

                Forms\Components\Textarea::make('keterangan')
                    ->label('Keterangan (Opsional)')
                    ->rows(2)
                    ->placeholder('Catatan pembinaan atau deskripsi kegiatan...')
                    ->columnSpanFull(),

                Forms\Components\Toggle::make('status_aktif')
                    ->label('Status Aktif')
                    ->default(true)
                    ->helperText('Matikan jika kegiatan ekstrakurikuler ini sedang tidak aktif berjalan.'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_ekskul')
                    ->label('Nama Ekstrakurikuler')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('pembinas.name')
                    ->label('Guru Pembina')
                    ->badge()
                    ->color('info')
                    ->searchable(),

                Tables\Columns\TextColumn::make('tahunAjaran.name')
                    ->label('Tahun Ajaran')
                    ->badge()
                    ->color('gray')
                    ->sortable(),

                Tables\Columns\TextColumn::make('anggota_aktif_count')
                    ->label('Anggota Aktif')
                    ->counts('anggotaAktif')
                    ->badge()
                    ->color('success')
                    ->sortable(),

                Tables\Columns\TextColumn::make('slug')
                    ->label('Link Scanner Barcode')
                    ->formatStateUsing(fn (?string $state) => $state ? url("/portal-presensi/scan-ekstrakurikuler/{$state}") : '-')
                    ->copyable()
                    ->copyableState(fn (?string $state) => $state ? url("/portal-presensi/scan-ekstrakurikuler/{$state}") : '')
                    ->copyMessage('Link scanner berhasil disalin!')
                    ->icon('heroicon-o-link')
                    ->limit(38)
                    ->tooltip('Klik untuk menyalin link Kiosk Scanner Barcode')
                    ->color('primary'),

                Tables\Columns\IconColumn::make('status_aktif')
                    ->label('Status')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('academic_year_id')
                    ->label('Tahun Ajaran')
                    ->options(fn () => TahunAjaran::orderBy('start_year', 'desc')->pluck('name', 'id'))
                    ->default(fn () => static::getActiveAcademicYearId())
                    ->placeholder('Semua Tahun Ajaran'),

                Tables\Filters\TernaryFilter::make('status_aktif')
                    ->label('Status Ekstrakurikuler'),
            ])
            ->actions([
                Action::make('buka_scanner')
                    ->label('Buka Scanner')
                    ->icon('heroicon-o-qr-code')
                    ->color('success')
                    ->url(fn (Ekstrakurikuler $record) => route('kiosk.scan-ekstrakurikuler', ['slug' => $record->slug]), shouldOpenInNewTab: true)
                    ->visible(fn (Ekstrakurikuler $record) => $record->status_aktif && filled($record->slug)),
                Action::make('manage_anggota')
                    ->label('Kelola Anggota')
                    ->icon('heroicon-o-user-group')
                    ->color('primary')
                    ->modalWidth('7xl')
                    ->modalHeading(fn (Ekstrakurikuler $record) => "Kelola Anggota - {$record->nama_ekskul} ({$record->tahunAjaran?->name})")
                    ->modalSubmitAction(false)
                    ->modalCancelAction(false)
                    ->modalContent(function (Ekstrakurikuler $record, $livewire) {
                        if (($livewire->currentEkskulId ?? null) !== $record->id) {
                            $livewire->currentEkskulId = $record->id;
                            $livewire->filterKelasId = null;
                            $livewire->searchKandidat = null;
                        }

                        $record->loadMissing(['pembinas', 'tahunAjaran']);
                        $yearId = $record->academic_year_id;

                        // Panel Kanan: Anggota aktif di ekstrakurikuler ini
                        $memberStudents = $record->anggotaAktif()
                            ->with([
                                'siswa.enrollments' => fn ($eq) => $eq->where('academic_year_id', $yearId)
                                    ->where('status', 'aktif')
                                    ->with('kelas'),
                            ])
                            ->get()
                            ->sortBy(fn ($a) => mb_strtolower($a->siswa?->name ?? ''))
                            ->values();

                        // Panel Kiri: Siswa aktif yang belum berstatus aktif di ekstrakurikuler ini
                        $availableStudents = Siswa::where('status', 'aktif')
                            ->whereDoesntHave('ekstrakurikulerSiswas', fn ($eq) =>
                                $eq->where('ekstrakurikuler_id', $record->id)
                                    ->where('status_aktif', true)
                            )
                            ->when($livewire->searchKandidat, function ($q) use ($livewire) {
                                $q->where(fn ($sub) => $sub
                                    ->where('name', 'like', '%' . $livewire->searchKandidat . '%')
                                    ->orWhere('nisn', 'like', '%' . $livewire->searchKandidat . '%')
                                );
                            })
                            ->when($livewire->filterKelasId === 'none', function ($q) use ($yearId) {
                                $q->whereDoesntHave('enrollments', fn ($eq) =>
                                    $eq->where('academic_year_id', $yearId)->where('status', 'aktif')
                                );
                            })
                            ->when($livewire->filterKelasId && $livewire->filterKelasId !== 'none', function ($q) use ($livewire, $yearId) {
                                $q->whereHas('enrollments', fn ($eq) =>
                                    $eq->where('academic_year_id', $yearId)
                                        ->where('class_id', $livewire->filterKelasId)
                                        ->where('status', 'aktif')
                                );
                            })
                            ->with([
                                'enrollments' => fn ($eq) => $eq->where('academic_year_id', $yearId)
                                    ->where('status', 'aktif')
                                    ->with('kelas'),
                            ])
                            ->orderBy('name')
                            ->limit(150)
                            ->get();

                        $daftarKelas = Kelas::orderBy('grade_level')->orderBy('name')->get(['id', 'name', 'grade_level']);

                        return view('filament.resources.ekstrakurikuler.pages.anggota-manager-modal', [
                            'ekskul'            => $record,
                            'availableStudents' => $availableStudents,
                            'memberStudents'    => $memberStudents,
                            'daftarKelas'       => $daftarKelas,
                        ]);
                    }),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([])
            ->defaultSort('nama_ekskul', 'asc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\AnggotaEkskulRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListEkstrakurikulers::route('/'),
            'create' => Pages\CreateEkstrakurikuler::route('/create'),
            'edit'   => Pages\EditEkstrakurikuler::route('/{record}/edit'),
        ];
    }
}

