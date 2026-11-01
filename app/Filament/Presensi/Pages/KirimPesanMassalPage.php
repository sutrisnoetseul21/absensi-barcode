<?php

namespace App\Filament\Presensi\Pages;

use App\Jobs\ProcessSchoolBroadcastJob;
use App\Models\Kelas;
use App\Models\SchoolBroadcast;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class KirimPesanMassalPage extends Page implements HasForms, HasTable
{
    use InteractsWithForms, InteractsWithTable;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-megaphone';

    protected static ?string $navigationLabel = 'Pesan Massal Android';

    protected static ?string $slug = 'pesan-massal-android';

    protected static ?string $title = 'Kirim Pesan Massal ke Aplikasi Android';

    protected static string|\UnitEnum|null $navigationGroup = 'Presensi';

    protected static ?int $navigationSort = 9;

    protected string $view = 'filament.pages.kirim-pesan-massal';

    public ?array $data = [];

    // Statistik device tokens terdaftar di database sekolah ini
    public int $totalDevices = 0;
    public int $totalSiswaDevices = 0;
    public int $totalGuruDevices = 0;

    public function mount(): void
    {
        $this->refreshStats();

        $this->form->fill([
            'target_group' => 'all',
            'active_hours' => 10,
            'title'        => '',
            'body'         => '',
        ]);
    }

    public function refreshStats(): void
    {
        $this->totalDevices = DB::table('fcm_device_tokens')->count();

        // Hitung perangkat siswa
        $this->totalSiswaDevices = DB::table('fcm_device_tokens')
            ->join('users', 'fcm_device_tokens.user_id', '=', 'users.id')
            ->join('students', 'users.id', '=', 'students.user_id')
            ->count();

        // Hitung perangkat guru/staf
        $this->totalGuruDevices = DB::table('fcm_device_tokens')
            ->join('users', 'fcm_device_tokens.user_id', '=', 'users.id')
            ->join('teachers', 'users.id', '=', 'teachers.user_id')
            ->count();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Buat Pesan Pengumuman / Notifikasi')
                    ->description('Pesan akan dikirim langsung sebagai Push Notification (pop-up & nada dering di HP) serta otomatis masuk ke Pusat Notifikasi aplikasi siswa & guru.')
                    ->schema([
                        Select::make('target_group')
                            ->label('Target Penerima')
                            ->options([
                                'all'   => 'Semua Pengguna Sekolah Ini (Siswa & Guru)',
                                'siswa' => 'Hanya Semua Siswa',
                                'guru'  => 'Hanya Bapak/Ibu Guru & Wali Kelas',
                                'kelas' => 'Siswa Kelas Tertentu',
                            ])
                            ->default('all')
                            ->required()
                            ->live(),

                        Select::make('class_id')
                            ->label('Pilih Rombongan Belajar (Kelas)')
                            ->options(fn () => Kelas::pluck('name', 'id')->toArray())
                            ->visible(fn ($get) => $get('target_group') === 'kelas')
                            ->required(fn ($get) => $get('target_group') === 'kelas')
                            ->searchable(),

                        Select::make('active_hours')
                            ->label('Masa Aktif Pesan di HP (TTL Firebase)')
                            ->options([
                                6   => '6 Jam (Pengumuman Singkat / Pagi)',
                                10  => '10 Jam (Standar Pengumuman Sekolah)',
                                24  => '24 Jam (1 Hari Penuh)',
                                48  => '48 Jam (2 Hari)',
                                168 => '7 Hari (1 Minggu)',
                            ])
                            ->default(10)
                            ->required()
                            ->helperText('Jika HP penerima offline lebih dari batas masa aktif ini, notifikasi otomatis dibatalkan/dihapus oleh server Firebase.'),

                        TextInput::make('title')
                            ->label('Judul Notifikasi')
                            ->placeholder('Contoh: Pengumuman Upacara Hari Senin / Libur Sekolah')
                            ->required()
                            ->maxLength(100),

                        Textarea::make('body')
                            ->label('Isi Pesan Notifikasi')
                            ->placeholder('Tuliskan rincian pengumuman yang ingin disampaikan ke seluruh HP siswa/guru...')
                            ->rows(4)
                            ->required()
                            ->maxLength(500),
                    ]),
            ])
            ->statePath('data');
    }

    public function table(Table $table): Table
    {
        return $table
            ->poll('5s')
            ->query(SchoolBroadcast::query()->latest())
            ->columns([
                TextColumn::make('created_at')
                    ->label('Waktu Kirim')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),

                TextColumn::make('title')
                    ->label('Judul Notifikasi')
                    ->weight('bold')
                    ->searchable()
                    ->limit(35),

                TextColumn::make('body')
                    ->label('Isi Pesan')
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->body),

                TextColumn::make('target_group')
                    ->label('Target')
                    ->badge()
                    ->formatStateUsing(fn ($record) => $record->target_group_label)
                    ->color(fn (string $state): string => match ($state) {
                        'all'   => 'primary',
                        'siswa' => 'success',
                        'guru'  => 'info',
                        'kelas' => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('total_target_users')
                    ->label('Penerima')
                    ->formatStateUsing(function ($record) {
                        $users = number_format($record->total_target_users, 0, ',', '.');
                        $hp = $record->total_fcm_sent;
                        return "{$users} Akun" . ($hp > 0 ? " ({$hp} HP)" : "");
                    }),

                TextColumn::make('sisa_waktu')
                    ->label('Masa Aktif')
                    ->badge()
                    ->color(fn ($record) => $record->status_color)
                    ->tooltip(fn ($record) => $record->expires_at ? 'Kadaluarsa pada: ' . $record->expires_at->format('d M Y, H:i') : null),

                TextColumn::make('sender_name')
                    ->label('Pengirim')
                    ->color('gray'),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($record): string => $record->status_color)
                    ->formatStateUsing(fn ($record): string => $record->status_label),
            ])
            ->actions([
                Action::make('stop')
                    ->label('Hentikan')
                    ->icon('heroicon-o-stop-circle')
                    ->color('danger')
                    ->visible(fn ($record) => $record->status === 'sent' && !$record->is_expired)
                    ->requiresConfirmation()
                    ->modalHeading('Hentikan Pesan Broadcast Ini?')
                    ->modalDescription('Apakah Anda yakin ingin menghentikan pengumuman ini sekarang? Status pesan akan langsung diubah menjadi Dihentikan/Kadaluarsa.')
                    ->modalSubmitActionLabel('Ya, Hentikan Sekarang')
                    ->action(function ($record) {
                        $record->update([
                            'status'     => 'cancelled',
                            'expires_at' => now(),
                        ]);

                        FilamentNotification::make()
                            ->title('Pesan Berhasil Dihentikan')
                            ->body('Broadcast pengumuman ini telah dihentikan.')
                            ->warning()
                            ->send();
                    }),

                DeleteAction::make()
                    ->label('Hapus')
                    ->modalHeading('Hapus Riwayat Broadcast')
                    ->modalDescription('Apakah Anda yakin ingin menghapus catatan riwayat broadcast ini dari daftar?')
                    ->successNotificationTitle('Riwayat broadcast berhasil dihapus.'),
            ])
            ->emptyStateHeading('Belum Ada Riwayat Broadcast')
            ->emptyStateDescription('Pesan massal yang Anda kirimkan ke aplikasi Android akan tercatat di sini.')
            ->emptyStateIcon('heroicon-o-megaphone');
    }

    public function send(): void
    {
        $data = $this->form->getState();

        $targetGroup = $data['target_group'] ?? 'all';
        $classId     = $data['class_id'] ?? null;
        $activeHours = (int) ($data['active_hours'] ?? 10);
        $title       = trim($data['title']);
        $body        = trim($data['body']);
        $senderName  = auth()->user()?->name ?? 'Admin Sekolah';
        $className   = $classId ? Kelas::find($classId)?->name : null;
        $expiresAt   = now()->addHours($activeHours);

        // 1. Tentukan query penerima sesuai jangkauan sekolah ini
        $query = User::query();

        if ($targetGroup === 'siswa') {
            $query->whereHas('student');
        } elseif ($targetGroup === 'guru') {
            $query->where(function ($q) {
                $q->whereHas('teacher')->orWhereHas('roles', fn ($r) => $r->whereIn('name', ['guru', 'wali_kelas']));
            });
        } elseif ($targetGroup === 'kelas' && !empty($classId)) {
            $query->whereHas('student.enrollmentAktif', function ($q) use ($classId) {
                $q->where('class_id', $classId);
            });
        }

        $totalRecipients = $query->count();

        if ($totalRecipients === 0) {
            FilamentNotification::make()
                ->title('Tidak Ada Penerima')
                ->body('Tidak ditemukan pengguna yang sesuai dengan kriteria target penerima.')
                ->warning()
                ->send();
            return;
        }

        // 2. Buat catatan riwayat broadcast dengan masa aktif (TTL)
        $broadcast = SchoolBroadcast::create([
            'title'              => $title,
            'body'               => $body,
            'target_group'       => $targetGroup,
            'class_id'           => $classId,
            'class_name'         => $className,
            'sender_name'        => $senderName,
            'active_hours'       => $activeHours,
            'expires_at'         => $expiresAt,
            'total_target_users' => $totalRecipients,
            'total_fcm_sent'     => 0,
            'status'             => 'processing',
        ]);

        // 3. Dispatch Job di antrean (Sangat cepat < 0.1 detik, tanpa membebani browser)
        ProcessSchoolBroadcastJob::dispatch($broadcast->id);

        $this->refreshStats();

        // 4. Reset form
        $this->form->fill([
            'target_group' => 'all',
            'active_hours' => 10,
            'title'        => '',
            'body'         => '',
        ]);

        FilamentNotification::make()
            ->title('Pesan Massal Berhasil Dijadwalkan! 🚀')
            ->body("Notifikasi aktif selama {$activeHours} jam untuk {$totalRecipients} akun sekolah ini. HP yang online akan langsung menerima push notification.")
            ->success()
            ->send();
    }
}
