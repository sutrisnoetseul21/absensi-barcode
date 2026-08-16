<?php

namespace App\Filament\Resources\UserResource\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use App\Models\Guru;
use Spatie\Permission\Models\Role;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Akun & Pengguna')
                    ->description('Kelola data profil, email, dan otentikasi login pengguna.')
                    ->schema([
                        Select::make('teacher_id')
                            ->label('Terhubung dengan Data Guru (Opsional)')
                            ->relationship('guru', 'name')
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function (Set $set, ?string $state) {
                                if ($state) {
                                    $guru = Guru::find($state);
                                    if ($guru) {
                                        $set('name', $guru->name);
                                        // Buat email otomatis: nip@domain (domain dari APP_URL)
                                        $appUrl = config('app.url', 'http://localhost');
                                        $domain = parse_url($appUrl, PHP_URL_HOST) ?? 'localhost';
                                        $nip = $guru->nip ?? strtolower(str_replace(' ', '.', $guru->name));
                                        $set('email', $nip . '@' . $domain);
                                    }
                                }
                            })
                            ->helperText('Pilih guru untuk mengisi otomatis nama dan email (format: nip@domain).'),

                        TextInput::make('name')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        TextInput::make('no_hp')
                            ->label('Nomor WhatsApp')
                            ->tel()
                            ->rule(function () {
                                return function (string $attribute, $value, \Closure $fail) {
                                    $digits = preg_replace('/\D/', '', $value ?? '');
                                    if ($digits) {
                                        $normalized = $digits;
                                        if (str_starts_with($normalized, '0')) {
                                            $normalized = '62' . substr($normalized, 1);
                                        } elseif (!str_starts_with($normalized, '62')) {
                                            $normalized = '62' . $normalized;
                                        }
                                        if (!preg_match('/^628[0-9]{7,12}$/', $normalized)) {
                                            $fail('Format nomor HP tidak valid. Contoh: 081234567890');
                                        }
                                    }
                                };
                            })
                            ->visible(fn (?\App\Models\User $record) =>
                                $record === null || (!$record->teacher && !$record->student)
                            )
                            ->helperText('Isi untuk akun staff tanpa profil Guru/Siswa.'),

                        Placeholder::make('no_hp_info')
                            ->label('Nomor WhatsApp')
                            ->content('Nomor HP dikelola dari profil Guru/Siswa terkait, lihat menu Akademik.')
                            ->visible(fn (?\App\Models\User $record) =>
                                $record && ($record->teacher || $record->student)
                            ),

                        TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->revealable()
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->helperText('Kosongkan jika tidak ingin mengubah password saat mengedit.')
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Section::make('Hak Akses Panel (Role)')
                    ->description('Tentukan hak akses modul panel. Mencentang Super Admin otomatis memilih seluruh akses, dan mencentang Editor otomatis mencentang Viewer terkait.')
                    ->schema([
                        CheckboxList::make('roles')
                            ->hiddenLabel()
                            ->relationship(
                                name: 'roles', 
                                titleAttribute: 'name',
                                modifyQueryUsing: fn ($query) => $query->whereIn('name', [
                                    'super_admin',
                                    'admin_akademik_editor', 'admin_akademik_viewer',
                                    'admin_presensi_editor', 'admin_presensi_viewer',
                                    'admin_perpustakaan_editor', 'admin_perpustakaan_viewer',
                                    'admin_master_editor', 'admin_master_viewer',
                                ])->orderByRaw("FIELD(name, 'super_admin', 'admin_akademik_editor', 'admin_akademik_viewer', 'admin_presensi_editor', 'admin_presensi_viewer', 'admin_perpustakaan_editor', 'admin_perpustakaan_viewer', 'admin_master_editor', 'admin_master_viewer')")
                            )
                            ->getOptionLabelFromRecordUsing(fn ($record) => match ($record->name) {
                                'super_admin' => '👑 Super Admin (Akses Penuh Seluruh Panel)',
                                'admin_akademik_editor' => '✏️ Admin Akademik (Bisa Tambah/Edit/Hapus)',
                                'admin_akademik_viewer' => '👁️ Admin Akademik (Hanya Lihat / Viewer)',
                                'admin_presensi_editor' => '✏️ Admin Presensi (Bisa Kelola & Setujui Ijin)',
                                'admin_presensi_viewer' => '👁️ Admin Presensi (Hanya Lihat Rekap/Laporan)',
                                'admin_perpustakaan_editor' => '✏️ Admin Perpustakaan (Bisa Kelola & Sirkulasi)',
                                'admin_perpustakaan_viewer' => '👁️ Admin Perpustakaan (Hanya Lihat Katalog/Koleksi)',
                                'admin_master_editor' => '✏️ Admin Data Master (Bisa Tambah/Edit Jabatan dll)',
                                'admin_master_viewer' => '👁️ Admin Data Master (Hanya Lihat Master Data)',
                                default => $record->name,
                            })
                            ->columns([
                                'default' => 1,
                                'sm' => 2,
                            ])
                            ->bulkToggleable()
                            ->live()
                            ->afterStateUpdated(function (Set $set, ?array $state, ?array $old) {
                                $state = array_map('intval', (array) ($state ?? []));
                                $old   = array_map('intval', (array) ($old ?? []));

                                $roles = Role::whereIn('name', [
                                    'super_admin',
                                    'admin_akademik_editor', 'admin_akademik_viewer',
                                    'admin_presensi_editor', 'admin_presensi_viewer',
                                    'admin_perpustakaan_editor', 'admin_perpustakaan_viewer',
                                    'admin_master_editor', 'admin_master_viewer',
                                ])->get()->keyBy('name');

                                $superAdminId = $roles->get('super_admin')?->id;
                                $allRoleIds = $roles->pluck('id')->map(fn($id) => (int)$id)->all();

                                // 1. Jika Super Admin baru saja dicentang -> Centang semua role
                                if ($superAdminId && in_array($superAdminId, $state) && !in_array($superAdminId, $old)) {
                                    $set('roles', $allRoleIds);
                                    return;
                                }

                                // 2. Jika Super Admin baru saja di-uncheck -> Uncheck semua role
                                if ($superAdminId && !in_array($superAdminId, $state) && in_array($superAdminId, $old)) {
                                    $set('roles', []);
                                    return;
                                }

                                // 3. Mapping Editor -> Viewer
                                $pairs = [
                                    'admin_akademik_editor'     => 'admin_akademik_viewer',
                                    'admin_presensi_editor'     => 'admin_presensi_viewer',
                                    'admin_perpustakaan_editor' => 'admin_perpustakaan_viewer',
                                    'admin_master_editor'       => 'admin_master_viewer',
                                ];

                                foreach ($pairs as $editorName => $viewerName) {
                                    $editorId = $roles->get($editorName)?->id;
                                    $viewerId = $roles->get($viewerName)?->id;

                                    if (!$editorId || !$viewerId) continue;

                                    // Jika Editor dicentang -> pastikan Viewer juga dicentang
                                    if (in_array($editorId, $state) && !in_array($viewerId, $state)) {
                                        $state[] = $viewerId;
                                    }

                                    // Jika Viewer di-uncheck -> pastikan Editor juga di-uncheck
                                    if (!in_array($viewerId, $state) && in_array($editorId, $state)) {
                                        $state = array_values(array_diff($state, [$editorId]));
                                    }
                                }

                                // 4. Jika Super Admin sedang tercentang tetapi ada role lain yang di-uncheck -> lepaskan centang Super Admin
                                if ($superAdminId && in_array($superAdminId, $state)) {
                                    $nonSuperAdminRoles = array_diff($allRoleIds, [$superAdminId]);
                                    if (count(array_intersect($state, $nonSuperAdminRoles)) < count($nonSuperAdminRoles)) {
                                        $state = array_values(array_diff($state, [$superAdminId]));
                                    }
                                }

                                // 5. Jika SEMUA role non-superadmin dicentang secara manual -> otomatis centang Super Admin
                                if ($superAdminId && !in_array($superAdminId, $state)) {
                                    $nonSuperAdminRoles = array_diff($allRoleIds, [$superAdminId]);
                                    if (count(array_intersect($state, $nonSuperAdminRoles)) === count($nonSuperAdminRoles)) {
                                        $state[] = $superAdminId;
                                    }
                                }

                                $set('roles', array_values(array_unique($state)));
                            }),
                    ]),
            ]);
    }
}
