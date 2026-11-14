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

    protected static ?string $navigationLabel = 'Manajemen Akses Portal';

    protected static string|\UnitEnum|null $navigationGroup = 'Manajemen Akses';

    protected static ?string $modelLabel = 'Akses Portal Pengguna';

    protected static ?string $pluralModelLabel = 'Manajemen Akses Portal';

    protected static ?int $navigationSort = 2;

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
                    ->description('Akun ini berstatus Super Admin sehingga secara otomatis memiliki akses penuh ke seluruh portal tanpa perlu dicentang satu per satu.')
                    ->schema([
                        Placeholder::make('super_admin_notice')
                            ->hiddenLabel()
                            ->content(new HtmlString('<div class="p-3 rounded-lg bg-purple-50 dark:bg-purple-950/40 border border-purple-200 dark:border-purple-800 text-sm text-purple-800 dark:text-purple-200 font-medium">👑 Seluruh akses portal aktif otomatis untuk Super Admin. Untuk mencabut status Super Admin, kelola melalui menu <strong>Manajemen Admin</strong>.</div>')),
                    ])
                    ->visible(fn ($record) => (bool) $record?->isSuperAdmin())
                    ->columnSpanFull(),

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
                            ->disabled(fn ($record) => (bool) $record?->isSuperAdmin())
                            ->live(),

                        Placeholder::make('wali_kelas_utama')
                            ->label('Wali Kelas Utama (Sesuai Data Induk)')
                            ->content(function ($record) {
                                if (!$record || !$record->teacher) return '-';
                                $activeYear = \App\Models\TahunAjaran::where('status', 'aktif')->first();
                                if (!$activeYear) return '-';
                                $kelas = $record->teacher->kelasAjarans()->where('academic_year_id', $activeYear->id)->with('kelas')->get();
                                if ($kelas->isEmpty()) return 'Tidak ada (Bukan wali kelas utama di tahun aktif)';
                                return $kelas->map(fn($k) => $k->kelas->name ?? 'Unknown')->implode(', ');
                            })
                            ->visible(fn ($get) => (bool) $get('akses_portal_guru')),

                        Toggle::make('bypass_semua_kelas')
                            ->label('Akses Semua Kelas (Bypass Mode)')
                            ->helperText('Hanya aktifkan jika guru ini diberi hak khusus untuk memantau presensi seluruh kelas di sekolah.')
                            ->disabled(fn ($record) => (bool) $record?->isSuperAdmin())
                            ->visible(fn ($get) => (bool) $get('akses_portal_guru')),

                        Toggle::make('supervisi_jurnal_guru')
                            ->label('Supervisi Jurnal Mengajar (Akses Seluruh Guru)')
                            ->helperText('Aktifkan jika pengguna ini (misal Kepala Sekolah / Waka Kurikulum) berwenang memantau, memeriksa riwayat KBM, dan mencetak rekap jurnal seluruh guru.')
                            ->disabled(fn ($record) => (bool) $record?->isSuperAdmin())
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
                        return $record->isGuruBk() || ($record->teacher !== null && $record->teacher->hasJabatan(['Guru BK', 'BK']));
                    })
                    ->columns(1),

                Section::make('Akses Portal Perpustakaan (/portal-perpustakaan)')
                    ->description('Atur izin staf / guru ini untuk mengakses dan mengelola sistem sirkulasi perpustakaan.')
                    ->schema([
                        Toggle::make('akses_portal_perpustakaan')
                            ->label('Izinkan Akses Portal Perpustakaan')
                            ->helperText('Jika diaktifkan, pengguna akan diberikan role Petugas Perpustakaan.')
                            ->disabled(fn ($record) => (bool) $record?->isSuperAdmin()),
                    ])->columns(1),

                Section::make('Akses Manajemen Presensi (/portal-presensi)')
                    ->description('Atur izin staf / admin ini untuk mengakses Dashboard Manajemen Presensi. (Catatan: Semua Guru otomatis mendapatkan akses Kiosk Presensi).')
                    ->schema([
                        Toggle::make('akses_dashboard_presensi')
                            ->label('Izinkan Akses Portal Presensi')
                            ->helperText('Jika diaktifkan, pengguna akan menjadi Admin Presensi yang dapat mengelola rekapitulasi (otomatis mendapatkan akses Kiosk).')
                            ->disabled(fn ($record) => (bool) $record?->isSuperAdmin()),
                    ])->columns(1),

                Section::make('Akses Portal Web Sekolah (/portal-web)')
                    ->description('Atur izin admin ini untuk mengelola konten web publik sekolah: berita, pengumuman, prestasi, galeri, alumni, dan layanan publik.')
                    ->schema([
                        Toggle::make('akses_portal_web')
                            ->label('Izinkan Akses Portal Web Sekolah')
                            ->helperText('Jika diaktifkan, pengguna mendapat role Admin Portal Web dan dapat mengakses command center konten web sekolah.')
                            ->disabled(fn ($record) => (bool) $record?->isSuperAdmin()),
                    ])->columns(1),

                Section::make('Akses Portal Setup & Instalasi (/portal-setup)')
                    ->description('Atur izin pengguna untuk mengakses Portal Setup & Instalasi awal (pengaturan sekolah, tahun ajaran, dan kelas).')
                    ->schema([
                        Toggle::make('akses_portal_setup')
                            ->label('Izinkan Akses Portal Setup & Instalasi')
                            ->helperText('Jika diaktifkan, pengguna mendapat role Admin Portal Setup dan dapat mengakses /portal-setup.')
                            ->disabled(fn ($record) => (bool) $record?->isSuperAdmin()),
                    ])->columns(1),

                Section::make('Akses Portal Akademik (/portal-akademik)')
                    ->description('Atur izin pengguna untuk mengakses Portal Akademik (data guru, siswa, rombel, enrollment, jurnal mengajar, dan guru wali).')
                    ->schema([
                        Toggle::make('akses_portal_akademik')
                            ->label('Izinkan Akses Portal Akademik')
                            ->helperText('Jika diaktifkan, pengguna dapat mengakses /portal-akademik sesuai level hak akses yang dipilih.')
                            ->disabled(fn ($record) => (bool) $record?->isSuperAdmin())
                            ->live(),

                        Select::make('hak_akses_akademik')
                            ->label('Level Hak Akses Akademik')
                            ->options([
                                'editor' => 'Editor (Bisa Tambah, Edit, & Hapus)',
                                'viewer' => 'Viewer (Hanya Lihat & Monitoring)',
                            ])
                            ->default('editor')
                            ->required()
                            ->disabled(fn ($record) => (bool) $record?->isSuperAdmin())
                            ->visible(fn ($get) => (bool) $get('akses_portal_akademik'))
                            ->helperText('Menentukan apakah pengguna hanya dapat memantau/melihat data atau juga mengelola data di Portal Akademik.'),
                    ])->columns(1),

                Section::make('Akses Portal Sistem (/portal-sistem)')
                    ->description('Atur izin pengguna untuk mengakses Portal Sistem (manajemen akun admin/role dan manajemen akses seluruh portal ERP).')
                    ->schema([
                        Toggle::make('akses_portal_sistem')
                            ->label('Izinkan Akses Portal Sistem')
                            ->helperText('Jika diaktifkan, pengguna mendapat akses Portal Sistem (/portal-sistem) beserta pengelolaan admin dan akses portal.')
                            ->disabled(fn ($record) => (bool) $record?->isSuperAdmin()),
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
