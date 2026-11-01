<?php

namespace App\Filament\Presensi\Pages;

use App\Models\Kelas;
use App\Models\User;
use App\Notifications\BroadcastSchoolNotification;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;

class KirimPesanMassalPage extends Page implements HasForms
{
    use InteractsWithForms;

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
                    ->description('Pesan akan dikirim langsung sebagai Push Notification (pop-up & suara di HP) serta masuk ke riwayat Pusat Notifikasi aplikasi mobile.')
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

                        TextInput::make('title')
                            ->label('Judul Notifikasi')
                            ->placeholder('Contoh: Pengumuman Upacara Hari Senin / Libur Nasional')
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

    public function send(): void
    {
        $data = $this->form->getState();

        $targetGroup = $data['target_group'] ?? 'all';
        $classId     = $data['class_id'] ?? null;
        $title       = trim($data['title']);
        $body        = trim($data['body']);
        $senderName  = auth()->user()?->name ?? 'Admin Sekolah';

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

        $recipients = $query->get();

        if ($recipients->isEmpty()) {
            FilamentNotification::make()
                ->title('Tidak Ada Penerima')
                ->body('Tidak ditemukan pengguna yang sesuai dengan kriteria target penerima.')
                ->warning()
                ->send();
            return;
        }

        // 2. Broadcast notifikasi antrean
        $notification = new BroadcastSchoolNotification(
            title: $title,
            body: $body,
            targetGroup: $targetGroup,
            senderName: $senderName
        );

        $count = 0;
        foreach ($recipients as $recipient) {
            $recipient->notify($notification);
            $count++;
        }

        $this->refreshStats();

        // 3. Reset form
        $this->form->fill([
            'target_group' => 'all',
            'title'        => '',
            'body'         => '',
        ]);

        FilamentNotification::make()
            ->title('Pesan Massal Berhasil Dijadwalkan! 🚀')
            ->body("Notifikasi sedang diproses antrean untuk {$count} akun pengguna sekolah ini. HP yang aktif akan segera menerima push notification.")
            ->success()
            ->send();
    }
}
