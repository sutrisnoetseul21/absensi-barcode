<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ManajemenAksesPortalResource\Pages;
use App\Models\User;
use App\Models\Kelas;
use App\Models\TahunAjaran;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Placeholder;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Actions\EditAction;
use Illuminate\Support\HtmlString;

class ManajemenAksesPortalResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-key';

    protected static ?string $navigationLabel = 'Manajemen Akses Portal';

    protected static string|\UnitEnum|null $navigationGroup = 'Pengaturan Sistem';

    protected static ?string $modelLabel = 'Akses Portal Pengguna';

    protected static ?string $pluralModelLabel = 'Manajemen Akses Portal';

    protected static ?int $navigationSort = 11;

    protected static ?string $slug = 'manajemen-akses-portal';

    public static function canViewAny(): bool
    {
        return auth()->user()?->isSuperAdmin() ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        $activeYear = TahunAjaran::where('status', 'aktif')->first();
        $kelasOptions = [];
        if ($activeYear) {
            $kelasOptions = Kelas::whereHas('kelasAjarans', function ($query) use ($activeYear) {
                $query->where('academic_year_id', $activeYear->id);
            })->orderBy('name', 'asc')->pluck('name', 'id')->toArray();
        }

        return $schema
            ->components([
                Section::make('Informasi Pengguna')
                    ->schema([
                        Placeholder::make('user_info')
                            ->label('Nama & Email Staf')
                            ->content(fn ($record) => $record ? "{$record->name} ({$record->email})" : '-'),

                        Placeholder::make('teacher_info')
                            ->label('Profil Guru Terkait')
                            ->content(fn ($record) => $record && $record->teacher ? $record->teacher->name : 'Non-Guru (Hanya Staf / Admin)'),
                    ])->columns(2),

                Section::make('Akses Portal Guru (/portal-guru)')
                    ->description('Atur apakah pengguna ini boleh login ke Portal Guru dan batasan kelas yang bisa diakses.')
                    ->schema([
                        Toggle::make('akses_portal_guru')
                            ->label('Izinkan Akses Portal Guru')
                            ->helperText('Jika diaktifkan, pengguna akan diberikan role Wali Kelas.')
                            ->live(),

                        Select::make('mode_akses_kelas')
                            ->label('Tipe Akses Kelas')
                            ->options([
                                'wali_kelas_saja' => '1. Hanya Kelas Binaan Utama (Default Wali Kelas)',
                                'kelas_tertentu'   => '2. Akses Beberapa Kelas Pilihan (Multi-Select)',
                                'semua_kelas'      => '3. Akses Semua Kelas (Bypass Mode 7A - 9C)',
                            ])
                            ->default('wali_kelas_saja')
                            ->visible(fn ($get) => (bool) $get('akses_portal_guru'))
                            ->live(),

                        Select::make('kelas_pilihan_ids')
                            ->label('Pilih Kelas Spesifik (Tahun Ajaran Aktif)')
                            ->options($kelasOptions)
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->helperText('Pilih kelas apa saja yang boleh diakses dan dikelola oleh guru ini.')
                            ->visible(fn ($get) => (bool) $get('akses_portal_guru') && $get('mode_akses_kelas') === 'kelas_tertentu'),
                    ])->columns(1),

                Section::make('Akses Portal Perpustakaan (/portal-perpustakaan)')
                    ->description('Atur izin staf / guru ini untuk mengakses dan mengelola sistem sirkulasi perpustakaan.')
                    ->schema([
                        Toggle::make('akses_portal_perpustakaan')
                            ->label('Izinkan Akses Portal Perpustakaan')
                            ->helperText('Jika diaktifkan, pengguna akan diberikan role Petugas Perpustakaan.'),
                    ])->columns(1),
            ])
            ->statePath('data');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Lengkap')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),

                TextColumn::make('guru.name')
                    ->label('Terkait Guru')
                    ->placeholder('Staf Non-Guru')
                    ->sortable(),

                TextColumn::make('status_portal_guru')
                    ->label('Akses Portal Guru')
                    ->badge()
                    ->state(function (User $record): string {
                        if ($record->hasPermissionTo('portal_guru:akses_semua_kelas')) {
                            return 'Bypass Semua Kelas';
                        }
                        if ($record->hasRole('wali_kelas')) {
                            $activeYear = TahunAjaran::where('status', 'aktif')->first();
                            if ($record->teacher && $activeYear) {
                                $count = $record->teacher->kelasAjarans()->where('academic_year_id', $activeYear->id)->count();
                                return "Wali Kelas ({$count} Kelas)";
                            }
                            return 'Wali Kelas';
                        }
                        return 'Tidak Aktif';
                    })
                    ->color(fn (string $state): string => match (true) {
                        str_contains($state, 'Bypass') => 'success',
                        str_contains($state, 'Wali Kelas') => 'info',
                        default => 'gray',
                    }),

                TextColumn::make('status_portal_perpustakaan')
                    ->label('Akses Portal Perpus')
                    ->badge()
                    ->state(fn (User $record): string => $record->hasRole(['petugas_perpustakaan', 'admin_perpustakaan']) ? 'Aktif (Petugas)' : 'Tidak Aktif')
                    ->color(fn (string $state): string => str_contains($state, 'Aktif') ? 'success' : 'gray'),
            ])
            ->actions([
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListManajemenAksesPortals::route('/'),
            'edit'  => Pages\EditManajemenAksesPortal::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        // Hanya tampilkan user non-siswa (Guru, Staf, Admin, Petugas)
        return parent::getEloquentQuery()->whereDoesntHave('student');
    }
}
