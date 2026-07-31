<?php

namespace App\Filament\Pages;

use App\Models\PengaturanSekolah;
use App\Models\TahunAjaran;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Exceptions\Halt;

use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;

class SchoolSettingsPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected string $view = 'filament.pages.school-settings';

    protected static ?string $navigationLabel = 'Pengaturan Sekolah';

    protected static ?string $slug = 'pengaturan-sekolah';

    protected static ?string $title = 'Pengaturan Sekolah';

    protected static string|\UnitEnum|null $navigationGroup = 'Pengaturan Sistem';

    public ?array $data = [];

    /**
     * Hanya Super Admin yang bisa mengakses halaman pengaturan.
     */
    public static function canAccess(): bool
    {
        return auth()->user()?->isSuperAdmin() ?? false;
    }

    public function mount(): void
    {
        // Load data singleton
        $settings = PengaturanSekolah::current();

        if ($settings) {
            $this->form->fill($settings->toArray());
        } else {
            $this->form->fill();
        }
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Settings')
                    ->tabs([
                        Tab::make('Identitas & Tema Sekolah')
                            ->schema([
                                Section::make('Identitas Sekolah')
                                    ->schema([
                                        TextInput::make('school_name')
                                            ->label('Nama Sekolah')
                                            ->required()
                                            ->maxLength(255),
                
                                        Textarea::make('school_address')
                                            ->label('Alamat Sekolah')
                                            ->required()
                                            ->maxLength(500),
                
                                        TextInput::make('principal_name')
                                            ->label('Nama Kepala Sekolah')
                                            ->required()
                                            ->maxLength(255),
                
                                        FileUpload::make('principal_signature_path')
                                            ->label('Tanda Tangan Kepala Sekolah (PNG)')
                                            ->image()
                                            ->acceptedFileTypes(['image/png'])
                                            ->imageResizeTargetWidth(500)
                                            ->imageResizeTargetHeight(300)
                                            ->disk('public')
                                            ->directory('settings')
                                            ->nullable()
                                            ->helperText('Gunakan format PNG dengan background transparan untuk hasil terbaik di Kartu OSIS.'),
                
                                        FileUpload::make('district_logo_path')
                                            ->label('Logo Kabupaten (Kiri Kartu OSIS)')
                                            ->image()
                                            ->imageResizeTargetWidth(500)
                                            ->imageResizeTargetHeight(500)
                                            ->disk('public')
                                            ->directory('settings')
                                            ->nullable(),
                                            
                                        FileUpload::make('school_logo_path')
                                            ->label('Logo Sekolah (Kanan Kartu OSIS)')
                                            ->image()
                                            ->imageResizeTargetWidth(500)
                                            ->imageResizeTargetHeight(500)
                                            ->disk('public')
                                            ->directory('settings')
                                            ->nullable(),
                                            
                                        FileUpload::make('hero_image_path')
                                            ->label('Gambar Background Frontend (Hero Image)')
                                            ->image()
                                            ->imageResizeTargetWidth(1920)
                                            ->imageResizeTargetHeight(1080)
                                            ->imageResizeMode('contain')
                                            ->imageResizeUpscale(false)
                                            ->maxSize(10240)
                                            ->disk('public')
                                            ->directory('settings')
                                            ->nullable()
                                            ->helperText('Gambar ini akan digunakan sebagai background halaman depan portal presensi (Maksimal 10MB, disarankan format JPG/PNG).'),
                                            
                                        FileUpload::make('login_background_path')
                                            ->label('Gambar Background Login (Portal)')
                                            ->image()
                                            ->imageResizeTargetWidth(1920)
                                            ->imageResizeTargetHeight(1080)
                                            ->imageResizeMode('contain')
                                            ->imageResizeUpscale(false)
                                            ->maxSize(10240)
                                            ->disk('public')
                                            ->directory('settings')
                                            ->nullable()
                                            ->helperText('Gambar ini akan digunakan sebagai background halaman Login Siswa dan Wali Kelas (Maksimal 10MB, disarankan format JPG/PNG).'),
                                    ])->columns(1),
                            ]),

                        Tab::make('Manajemen Portal & Pengumuman')
                            ->schema([
                                Section::make('Status Maintenance Portal')
                                    ->description('Aktifkan maintenance untuk memblokir akses ke portal tertentu (Kecuali Super Admin).')
                                    ->schema([
                                        Toggle::make('maintenance_portal_siswa')
                                            ->label('Maintenance Portal Siswa')
                                            ->helperText('Jika aktif, siswa tidak bisa login atau mengakses dashboard.'),
                                        Textarea::make('welcome_message_siswa')
                                            ->label('Pesan Maintenance Siswa')
                                            ->placeholder('Contoh: Portal siswa sedang dalam perbaikan rutin.')
                                            ->nullable(),

                                        Placeholder::make('separator1')->content(new \Illuminate\Support\HtmlString('<hr>')),

                                        Toggle::make('maintenance_portal_guru')
                                            ->label('Maintenance Portal Guru')
                                            ->helperText('Jika aktif, wali kelas tidak bisa login atau mengakses dashboard.'),
                                        Textarea::make('welcome_message_guru')
                                            ->label('Pesan Maintenance Guru')
                                            ->placeholder('Contoh: Portal guru sedang dalam perbaikan rutin.')
                                            ->nullable(),

                                        Placeholder::make('separator2')->content(new \Illuminate\Support\HtmlString('<hr>')),

                                        Toggle::make('maintenance_portal_perpustakaan')
                                            ->label('Maintenance Portal Perpustakaan')
                                            ->helperText('Jika aktif, petugas perpus tidak bisa login atau mengakses dashboard.'),
                                        Textarea::make('welcome_message_perpustakaan')
                                            ->label('Pesan Maintenance Perpustakaan')
                                            ->placeholder('Contoh: Sistem perpustakaan sedang sinkronisasi data.')
                                            ->nullable(),
                                    ])->columns(1),

                                Section::make('Pengumuman Global')
                                    ->description('Banner pengumuman yang muncul di atas layar semua portal aktif.')
                                    ->schema([
                                        Toggle::make('global_announcement_active')
                                            ->label('Tampilkan Pengumuman Global')
                                            ->helperText('Aktifkan toggle ini agar pengumuman di bawah muncul di semua portal.'),
                                        RichEditor::make('global_announcement')
                                            ->label('Teks Pengumuman')
                                            ->toolbarButtons([
                                                'bold', 'italic', 'link', 'redo', 'undo',
                                            ])
                                            ->nullable(),
                                    ])->columns(1),
                            ]),
                    ])
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        try {
            $data = $this->form->getState();

            // Karena data singleton, kita pake id 1 jika belum ada
            $settings = PengaturanSekolah::first();

            if ($settings) {
                $settings->update($data);
            } else {
                PengaturanSekolah::create($data);
            }

            \Illuminate\Support\Facades\Cache::forget('public_pengaturan_sekolah');

            Notification::make()
                ->title('Pengaturan berhasil disimpan')
                ->success()
                ->send();
        } catch (Halt $exception) {
            return;
        }
    }
}
