<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ManajemenAksesPortalResource\Pages;
use App\Models\User;
use App\Models\Kelas;
use App\Models\TahunAjaran;
use Filament\Resources\Resource;
use App\Filament\Traits\HasSimpleRoleAccess;
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
    use HasSimpleRoleAccess;

    protected static function getModuleRolePrefix(): string
    {
        return 'akses';
    }

    protected static ?string $model = User::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-key';

    protected static ?string $navigationLabel = 'Manajemen User dan Akses';

    protected static string|\UnitEnum|null $navigationGroup = 'Manajemen Akses';

    protected static ?string $modelLabel = 'Akses Portal Pengguna';

    protected static ?string $pluralModelLabel = 'Manajemen User dan Akses';

    protected static ?int $navigationSort = 2;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $slug = 'manajemen-akses-portal';

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
                Section::make('Status Super Admin (Akses Otomatis Penuh)')
                    ->description('Jika diaktifkan, akun ini memiliki akses penuh ke seluruh portal tanpa perlu mengaktifkan satu per satu.')
                    ->schema([
                        Toggle::make('is_super_admin_toggle')
                            ->label('Jadikan Super Admin')
                            ->helperText(fn ($record) => $record && $record->id === auth()->id()
                                ? '👑 Status Super Admin akun yang sedang login tidak dapat dicabut dari halaman ini.'
                                : '👑 Aktifkan untuk memberikan akses penuh ke seluruh portal & modul sistem.')
                            ->disabled(fn ($record) => $record !== null && $record->id === auth()->id())
                            ->live()
                            ->afterStateUpdated(function (bool $state, $set, $record) {
                                if ($state) {
                                    $set('akses_portal_guru', true);
                                    $set('bypass_semua_kelas', true);
                                    $set('supervisi_jurnal_guru', true);
                                    $set('akses_portal_perpustakaan', true);
                                    $set('akses_dashboard_presensi', true);
                                    $set('akses_portal_web', true);
                                    $set('akses_portal_setup', true);
                                    $set('akses_portal_akademik', true);
                                    $set('akses_portal_sistem', true);

                                    return;
                                }

                                if ($record) {
                                    $guru = $record->teacher ?? $record->guru;
                                    $isBk = $record->isGuruBk() || ($guru !== null && $guru->hasJabatan(['Guru BK', 'BK']));
                                    $hasPantau = $guru ? $guru->kelasPantau()->exists() : false;

                                    $set('akses_portal_guru', $record->isGuruUser() || $record->hasRole('wali_kelas'));
                                    $set('bypass_semua_kelas', (! $isBk && $hasPantau) || $record->permissions->contains('name', 'portal_guru:akses_semua_kelas'));
                                    $set('supervisi_jurnal_guru', $record->permissions->contains('name', 'portal_guru:supervisi_jurnal'));
                                    $set('akses_portal_perpustakaan', $record->hasRole(['petugas_perpustakaan', 'admin_perpustakaan']));
                                    $set('akses_dashboard_presensi', $record->hasRole('admin_portal_presensi'));
                                    $set('akses_portal_web', $record->hasRole('admin_portal_web'));
                                    $set('akses_portal_setup', $record->hasRole('admin_portal_setup'));
                                    $set('akses_portal_akademik', $record->hasRole('admin_portal_akademik') || $record->hasAnyRole(['admin_akademik_editor', 'admin_akademik_viewer']));
                                    $set('akses_portal_sistem', $record->hasRole('admin_portal_sistem'));
                                }
                            }),
                    ])
                    ->columnSpanFull(),

                Section::make('Informasi Pengguna')
                    ->schema([
                        Placeholder::make('user_info')
                            ->label('Nama & Email Staf')
                            ->content(fn ($record) => $record ? "{$record->name} ({$record->email})" : '-'),

                        Placeholder::make('teacher_info')
                            ->label('Profil Guru Terkait')
                            ->content(fn ($record) => $record && ($record->teacher || $record->guru)
                                ? ($record->teacher?->name ?? $record->guru?->name)
                                : 'Non-Guru (Hanya Staf / Admin)'),
                    ])->columns(2),

                Section::make('Akses Portal Guru (/portal-guru)')
                    ->description('Atur apakah pengguna ini boleh login ke Portal Guru dan batasan kelas yang bisa diakses.')
                    ->schema([
                        Toggle::make('akses_portal_guru')
                            ->label('Izinkan Akses Portal Guru')
                            ->helperText(fn (?User $record) => $record?->isGuruUser()
                                ? 'Akun Guru otomatis mendapatkan akses Portal Guru (role Wali Kelas).'
                                : 'Jika diaktifkan, pengguna akan diberikan role Wali Kelas.')
                            ->disabled(fn ($get, ?User $record) => (bool) $get('is_super_admin_toggle') || ($record?->isGuruUser() ?? false))
                            ->dehydrated(true)
                            ->live(),

                        Placeholder::make('wali_kelas_utama')
                            ->label('Wali Kelas Utama (Sesuai Data Induk)')
                            ->content(function ($record) {
                                $guru = $record?->teacher ?? $record?->guru;
                                if (!$guru) return '-';
                                $activeYear = \App\Models\TahunAjaran::where('status', 'aktif')->first();
                                if (!$activeYear) return '-';
                                $kelas = $guru->kelasAjarans()->where('academic_year_id', $activeYear->id)->with('kelas')->get();
                                if ($kelas->isEmpty()) return 'Tidak ada (Bukan wali kelas utama di tahun aktif)';
                                return $kelas->map(fn($k) => $k->kelas->name ?? 'Unknown')->implode(', ');
                            })
                            ->visible(fn ($get) => (bool) $get('akses_portal_guru')),

                        Toggle::make('bypass_semua_kelas')
                            ->label('Akses Kelas Tambahan (Pilih Kelas)')
                            ->helperText('Aktifkan jika guru ini diberi hak khusus memantau presensi (Akademik, Sholat Dhuhur, & Data Siswa) pada kelas tertentu di luar kelas walinya.')
                            ->disabled(fn ($get) => (bool) $get('is_super_admin_toggle'))
                            ->visible(fn ($get) => (bool) $get('akses_portal_guru'))
                            ->live(),

                        Select::make('bypass_class_ids')
                            ->label('Pilih Kelas Tambahan (Tahun Ajaran Aktif)')
                            ->options($kelasOptions)
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->required(function ($get, ?User $record) {
                                if ($get('is_super_admin_toggle') || ! $get('bypass_semua_kelas') || ! $record?->isGuruUser()) {
                                    return false;
                                }
                                $guru = $record->teacher ?? $record->guru;
                                $isBk = $record->isGuruBk() || ($guru !== null && $guru->hasJabatan(['Guru BK', 'BK']));

                                return ! $isBk;
                            })
                            ->visible(function ($get, ?User $record) {
                                if (! $get('akses_portal_guru') || ! $get('bypass_semua_kelas') || $get('is_super_admin_toggle') || ! $record?->isGuruUser()) {
                                    return false;
                                }
                                $guru = $record->teacher ?? $record->guru;
                                $isBk = $record->isGuruBk() || ($guru !== null && $guru->hasJabatan(['Guru BK', 'BK']));

                                return ! $isBk;
                            })
                            ->helperText('Pilih rombel/kelas tambahan yang boleh dipantau (hanya Presensi Akademik, Sholat Dhuhur, & Data Siswa; tidak termasuk persetujuan ijin).'),

                        Placeholder::make('bypass_bk_notice')
                            ->hiddenLabel()
                            ->content('Akun ini terdaftar sebagai Guru BK; daftar kelas pantau dikelola langsung pada bagian Akses Kelas Binaan BK di bawah.')
                            ->visible(function ($get, ?User $record) {
                                if (! $get('akses_portal_guru') || ! $get('bypass_semua_kelas') || $get('is_super_admin_toggle') || ! $record) {
                                    return false;
                                }
                                $guru = $record->teacher ?? $record->guru;

                                return $record->isGuruBk() || ($guru !== null && $guru->hasJabatan(['Guru BK', 'BK']));
                            }),

                        Toggle::make('supervisi_jurnal_guru')
                            ->label('Supervisi Jurnal Mengajar (Akses Seluruh Guru)')
                            ->helperText('Aktifkan jika pengguna ini (misal Kepala Sekolah / Waka Kurikulum) berwenang memantau, memeriksa riwayat KBM, dan mencetak rekap jurnal seluruh guru.')
                            ->disabled(fn ($get) => (bool) $get('is_super_admin_toggle'))
                            ->visible(fn ($get) => (bool) $get('akses_portal_guru')),
                    ])->columns(1),

                Section::make('Akses Kelas Binaan BK (Khusus Guru BK)')
                    ->description('Tentukan daftar kelas binaan yang ditangani oleh Guru BK ini pada tahun ajaran aktif. Guru BK akan dapat memantau presensi dan menangani aduan SPIKAP dari siswa di kelas-kelas ini.')
                    ->schema([
                        Select::make('kelas_binaan_bk_ids')
                            ->label('Pilih Kelas Binaan BK (Tahun Ajaran Aktif)')
                            ->options($kelasOptions)
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->helperText('Pilih rombel/kelas yang dibina oleh Guru BK ini (misal: 7A, 7B, 7C). Aduan SPIKAP dan pantauan presensi Guru BK akan dibatasi pada kelas yang dipilih.')
                    ])
                    ->visible(function ($record, $get) {
                        if (!$get('akses_portal_guru')) return false;
                        if (!$record) return false;
                        $guru = $record->teacher ?? $record->guru;
                        return $record->isGuruBk() || ($guru !== null && $guru->hasJabatan(['Guru BK', 'BK']));
                    })
                    ->columns(1),

                Section::make('Akses Portal Perpustakaan (/portal-perpustakaan)')
                    ->description('Atur izin staf / guru ini untuk mengakses dan mengelola sistem sirkulasi perpustakaan.')
                    ->schema([
                        Toggle::make('akses_portal_perpustakaan')
                            ->label('Izinkan Akses Portal Perpustakaan')
                            ->helperText('Jika diaktifkan, pengguna akan diberikan role Petugas Perpustakaan.')
                            ->disabled(fn ($get) => (bool) $get('is_super_admin_toggle')),
                    ])->columns(1),

                Section::make('Akses Manajemen Presensi (/portal-presensi)')
                    ->description('Atur izin staf / admin ini untuk mengakses Dashboard Manajemen Presensi. (Catatan: Semua Guru otomatis mendapatkan akses Kiosk Presensi).')
                    ->schema([
                        Toggle::make('akses_dashboard_presensi')
                            ->label('Izinkan Akses Portal Presensi')
                            ->helperText('Jika diaktifkan, pengguna akan menjadi Admin Presensi yang dapat mengelola rekapitulasi (otomatis mendapatkan akses Kiosk).')
                            ->disabled(fn ($get) => (bool) $get('is_super_admin_toggle')),
                    ])->columns(1),

                Section::make('Akses Portal Web Sekolah (/portal-web)')
                    ->description('Atur izin admin ini untuk mengelola konten web publik sekolah: berita, pengumuman, prestasi, galeri, alumni, dan layanan publik.')
                    ->schema([
                        Toggle::make('akses_portal_web')
                            ->label('Izinkan Akses Portal Web Sekolah')
                            ->helperText('Jika diaktifkan, pengguna mendapat role Admin Portal Web dan dapat mengakses command center konten web sekolah.')
                            ->disabled(fn ($get) => (bool) $get('is_super_admin_toggle')),
                    ])->columns(1),

                Section::make('Akses Portal Setup & Instalasi (/portal-setup)')
                    ->description('Atur izin pengguna untuk mengakses Portal Setup & Instalasi awal (pengaturan sekolah, tahun ajaran, dan kelas).')
                    ->schema([
                        Toggle::make('akses_portal_setup')
                            ->label('Izinkan Akses Portal Setup & Instalasi')
                            ->helperText('Jika diaktifkan, pengguna mendapat role Admin Portal Setup dan dapat mengakses /portal-setup.')
                            ->disabled(fn ($get) => (bool) $get('is_super_admin_toggle')),
                    ])->columns(1),

                Section::make('Akses Portal Akademik (/portal-akademik)')
                    ->description('Atur izin pengguna untuk mengakses Portal Akademik (data guru, siswa, rombel, enrollment, jurnal mengajar, dan guru wali).')
                    ->schema([
                        Toggle::make('akses_portal_akademik')
                            ->label('Izinkan Akses Portal Akademik')
                            ->helperText('Jika diaktifkan, pengguna dapat mengakses /portal-akademik sesuai level hak akses yang dipilih.')
                            ->disabled(fn ($get) => (bool) $get('is_super_admin_toggle'))
                            ->live(),

                        Select::make('hak_akses_akademik')
                            ->label('Level Hak Akses Akademik')
                            ->options([
                                'editor' => 'Editor (Bisa Tambah, Edit, & Hapus)',
                                'viewer' => 'Viewer (Hanya Lihat & Monitoring)',
                            ])
                            ->default('editor')
                            ->required()
                            ->disabled(fn ($get) => (bool) $get('is_super_admin_toggle'))
                            ->visible(fn ($get) => (bool) $get('akses_portal_akademik'))
                            ->helperText('Menentukan apakah pengguna hanya dapat memantau/melihat data atau juga mengelola data di Portal Akademik.'),
                    ])->columns(1),

                Section::make('Akses Portal Sistem (/portal-sistem)')
                    ->description('Atur izin pengguna untuk mengakses Portal Sistem (manajemen akun admin/role dan manajemen akses seluruh portal ERP).')
                    ->schema([
                        Toggle::make('akses_portal_sistem')
                            ->label('Izinkan Akses Portal Sistem')
                            ->helperText('Jika diaktifkan, pengguna mendapat akses Portal Sistem (/portal-sistem) beserta pengelolaan admin dan akses portal.')
                            ->disabled(fn ($get) => (bool) $get('is_super_admin_toggle')),
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
                        if ($record->isSuperAdmin()) {
                            return 'Super Admin (Semua Akses)';
                        }

                        $labels = [];
                        if ($record->can('portal_guru:akses_semua_kelas')) {
                            $labels[] = 'Bypass Semua Kelas';
                        }
                        if ($record->can('portal_guru:supervisi_jurnal')) {
                            $labels[] = 'Supervisi Jurnal';
                        }
                        if ($record->hasRole('wali_kelas')) {
                            $activeYear = TahunAjaran::where('status', 'aktif')->first();
                            if ($record->teacher && $activeYear) {
                                $count = $record->teacher->kelasAjarans()->where('academic_year_id', $activeYear->id)->count();
                                $labels[] = "Wali Kelas ({$count} Kelas)";
                            } else {
                                $labels[] = 'Wali Kelas';
                            }
                        }
                        return empty($labels) ? 'Tidak Aktif' : implode(', ', $labels);
                    })
                    ->color(fn (string $state): string => match (true) {
                        str_contains($state, 'Super Admin') => 'primary',
                        str_contains($state, 'Supervisi') => 'warning',
                        str_contains($state, 'Bypass') => 'success',
                        str_contains($state, 'Wali Kelas') => 'info',
                        default => 'gray',
                    }),

                TextColumn::make('status_portal_perpustakaan')
                    ->label('Akses Portal Perpus')
                    ->badge()
                    ->state(fn (User $record): string => $record->isSuperAdmin()
                        ? 'Super Admin (Semua Akses)'
                        : ($record->hasRole(['petugas_perpustakaan', 'admin_perpustakaan']) ? 'Aktif (Petugas)' : 'Tidak Aktif'))
                    ->color(fn (string $state): string => match (true) {
                        str_contains($state, 'Super Admin') => 'primary',
                        str_contains($state, 'Aktif') => 'success',
                        default => 'gray',
                    }),

                TextColumn::make('status_portal_presensi')
                    ->label('Akses Kiosk Presensi')
                    ->badge()
                    ->state(fn (User $record): string => $record->isSuperAdmin()
                        ? 'Super Admin (Semua Akses)'
                        : ($record->hasRole(['petugas_presensi']) ? 'Diizinkan' : 'Dilarang'))
                    ->color(fn (string $state): string => match (true) {
                        str_contains($state, 'Super Admin') => 'primary',
                        str_contains($state, 'Diizinkan') => 'success',
                        default => 'gray',
                    }),

                TextColumn::make('status_dashboard_presensi')
                    ->label('Akses Portal Presensi')
                    ->badge()
                    ->state(fn (User $record): string => $record->isSuperAdmin()
                        ? 'Super Admin (Semua Akses)'
                        : ($record->hasRole(['admin_portal_presensi']) ? 'Admin Presensi' : 'Tidak Aktif'))
                    ->color(fn (string $state): string => match (true) {
                        str_contains($state, 'Super Admin') => 'primary',
                        str_contains($state, 'Admin Presensi') => 'success',
                        default => 'gray',
                    }),
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
