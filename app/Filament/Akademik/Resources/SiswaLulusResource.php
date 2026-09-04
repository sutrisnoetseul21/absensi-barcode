<?php

namespace App\Filament\Akademik\Resources;

use App\Actions\Student\ReactivateStudentAction;
use App\Models\PengaturanSekolah;
use App\Models\Siswa;
use BackedEnum;
use Filament\Resources\Resource;
use App\Filament\Traits\HasSimpleRoleAccess;
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
    use HasSimpleRoleAccess;

    protected static function getModuleRolePrefix(): string
    {
        return 'akademik';
    }

    protected static ?string $model = Siswa::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;

    protected static ?string $navigationLabel = 'Siswa Lulus';

    protected static ?string $modelLabel = 'Siswa Lulus';

    protected static ?string $pluralModelLabel = 'Siswa Lulus';

    protected static ?string $slug = 'siswa-lulus';

    protected static string|\UnitEnum|null $navigationGroup = 'Akademik';

    protected static ?int $navigationSort = 6;

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

                TextColumn::make('status_melanjutkan')
                    ->label('Melanjutkan')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? 'Melanjutkan' : 'Belum Terdata / Kerja')
                    ->color(fn ($state) => $state ? 'success' : 'gray'),

                TextColumn::make('jenjangLanjutan.nama_jenjang')
                    ->label('Jenjang')
                    ->badge()
                    ->color('primary')
                    ->placeholder('-'),

                TextColumn::make('nama_sekolah_lanjutan')
                    ->label('Sekolah / Kampus Lanjutan')
                    ->searchable()
                    ->placeholder('-')
                    ->limit(25),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color('success'),
            ])
            ->recordActions([
                // Update Tracer Study
                Action::make('edit_tracer_study')
                    ->label('Tracer Study')
                    ->icon('heroicon-o-academic-cap')
                    ->color('info')
                    ->form([
                        \Filament\Forms\Components\Toggle::make('status_melanjutkan')
                            ->label('Melanjutkan Pendidikan')
                            ->reactive(),
                        \Filament\Forms\Components\Select::make('jenjang_lanjutan_id')
                            ->label('Jenjang Lanjutan')
                            ->relationship('jenjangLanjutan', 'nama_jenjang')
                            ->visible(fn ($get) => (bool) $get('status_melanjutkan')),
                        \Filament\Forms\Components\TextInput::make('nama_sekolah_lanjutan')
                            ->label('Nama Sekolah / Instansi Lanjutan')
                            ->visible(fn ($get) => (bool) $get('status_melanjutkan')),
                        \Filament\Forms\Components\TextInput::make('tahun_lulus_override')
                            ->label('Tahun Lulus')
                            ->numeric(),
                    ])
                    ->fillForm(fn (Siswa $record): array => [
                        'status_melanjutkan' => $record->status_melanjutkan,
                        'jenjang_lanjutan_id' => $record->jenjang_lanjutan_id,
                        'nama_sekolah_lanjutan' => $record->nama_sekolah_lanjutan,
                        'tahun_lulus_override' => $record->tahun_lulus_override ?? date('Y'),
                    ])
                    ->action(function (Siswa $record, array $data): void {
                        $record->update($data);

                        // Sinkronkan ke tabel alumnis
                        \App\Models\Alumni::updateOrCreate(
                            ['student_id' => $record->id],
                            [
                                'source' => 'sistem',
                                'nisn' => $record->nisn,
                                'nama' => $record->name,
                                'jenis_kelamin' => $record->gender === 'P' ? 'P' : 'L',
                                'tahun_lulus' => $data['tahun_lulus_override'] ?? date('Y'),
                                'melanjutkan' => (bool) ($data['status_melanjutkan'] ?? false),
                                'jenjang_id' => $data['status_melanjutkan'] ? ($data['jenjang_lanjutan_id'] ?? null) : null,
                                'nama_sekolah' => $data['status_melanjutkan'] ? ($data['nama_sekolah_lanjutan'] ?? null) : null,
                                'foto' => $record->photo_path,
                            ]
                        );

                        Notification::make()
                            ->title('Data Tracer Study Diperbarui')
                            ->success()
                            ->send();
                    }),

                // Batalkan kelulusan (kembalikan ke Aktif)
                Action::make('batalkan_kelulusan')
                    ->label('Aktifkan Kembali')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('warning')
                    ->visible(fn () => auth()->user()?->isSuperAdmin() || auth()->user()?->hasRole('admin_akademik_editor') || auth()->user()?->hasRole('admin_master_editor'))
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
            'index' => \App\Filament\Akademik\Resources\SiswaLulusResource\Pages\ListSiswaLulus::route('/'),
        ];
    }
}
