<?php

namespace App\Filament\Sistem\Pages;

use App\Models\PengaduanSetting;
use App\Models\PengaturanSekolah;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\HtmlString;
use UnitEnum;

class SistemMaintenancePortalPage extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedWrenchScrewdriver;

    protected static ?string $navigationLabel = 'Mode Maintenance & Pengumuman';

    protected static ?string $title = 'Mode Pemeliharaan (Maintenance) Portal & Pengumuman Global';

    protected static ?string $slug = 'maintenance-portal';

    protected static string|UnitEnum|null $navigationGroup = 'Gateway & Pengaturan Modul';

    protected static ?int $navigationSort = 4;

    protected string $view = 'filament.pages.school-settings';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user !== null && ($user->isSuperAdmin() || $user->hasRole('admin_portal_sistem'));
    }

    public function getSubheading(): ?string
    {
        return 'Kelola penguncian sementara akses operasional portal saat pemeliharaan sistem serta tayangan banner pengumuman global di seluruh layar portal.';
    }

    public function mount(): void
    {
        $settings = PengaturanSekolah::first();

        if (! $settings) {
            $settings = PengaturanSekolah::create([
                'school_name' => 'SMA Negeri 1 Contoh',
                'school_address' => 'Jl. Pendidikan No. 1',
                'principal_name' => 'Drs. Kepala Sekolah, M.Pd',
            ]);
        }

        $pengaduanSetting = PengaduanSetting::instance();

        $this->form->fill([
            'maintenance_portal_siswa' => (bool) $settings->maintenance_portal_siswa,
            'welcome_message_siswa' => $settings->welcome_message_siswa,
            'maintenance_portal_guru' => (bool) $settings->maintenance_portal_guru,
            'welcome_message_guru' => $settings->welcome_message_guru,
            'maintenance_portal_perpustakaan' => (bool) $settings->maintenance_portal_perpustakaan,
            'welcome_message_perpustakaan' => $settings->welcome_message_perpustakaan,
            'pengaduan_is_active' => (bool) $pengaduanSetting->is_active,
            'global_announcement_active' => (bool) $settings->global_announcement_active,
            'global_announcement' => $settings->global_announcement,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Kontrol Mode Pemeliharaan (Maintenance) per Portal')
                    ->description('Saat Mode Pemeliharaan diaktifkan pada suatu portal, pengguna umum (Siswa/Guru/Petugas) yang membuka portal tersebut akan dialihkan ke halaman 503 Pemeliharaan Sistem.')
                    ->icon('heroicon-o-shield-exclamation')
                    ->schema([
                        Placeholder::make('info_bypass_superadmin')
                            ->hiddenLabel()
                            ->content(new HtmlString('
                                <div class="rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800 dark:border-amber-800 dark:bg-amber-950/50 dark:text-amber-300">
                                    <strong>Catatan Administrator Sistem:</strong> Akun dengan hak akses <strong>Super Admin</strong> tetap dapat menembus (bypass) mode pemeliharaan untuk keperluan pengujian dan verifikasi sistem sebelum portal dibuka kembali.
                                </div>
                            ')),

                        Grid::make(1)
                            ->schema([
                                Section::make('Portal Siswa (/portal-siswa)')
                                    ->description('Penguncian akses dasbor siswa, kartu pelajar digital, SPIKAP siswa, dan konsultasi guru wali.')
                                    ->compact()
                                    ->schema([
                                        Toggle::make('maintenance_portal_siswa')
                                            ->label('Aktifkan Mode Pemeliharaan (Tutup Sementara Portal Siswa)')
                                            ->onColor('danger')
                                            ->offColor('success')
                                            ->helperText('Jika aktif (Merah), seluruh siswa tidak dapat mengakses Portal Siswa dan akan melihat pesan pemeliharaan di bawah ini.'),
                                        Textarea::make('welcome_message_siswa')
                                            ->label('Pesan Layar Pemeliharaan / Sambutan Portal Siswa')
                                            ->placeholder('Contoh: Mohon maaf, Portal Siswa sedang dalam pemeliharaan rutin peningkatan server hingga pukul 15.00 WIB.')
                                            ->rows(2),
                                    ]),

                                Section::make('Portal Guru & Wali Kelas (/portal-guru)')
                                    ->description('Penguncian akses jurnal mengajar, absensi kelas, tindak lanjut SPIKAP, dan manajemen bimbingan guru wali.')
                                    ->compact()
                                    ->schema([
                                        Toggle::make('maintenance_portal_guru')
                                            ->label('Aktifkan Mode Pemeliharaan (Tutup Sementara Portal Guru)')
                                            ->onColor('danger')
                                            ->offColor('success')
                                            ->helperText('Jika aktif (Merah), guru dan wali kelas akan melihat layar 503 Pemeliharaan saat membuka Portal Guru.'),
                                        Textarea::make('welcome_message_guru')
                                            ->label('Pesan Layar Pemeliharaan / Sambutan Portal Guru')
                                            ->placeholder('Contoh: Sistem Portal Guru sedang dalam proses sinkronisasi data semester baru. Silakan akses kembali beberapa saat lagi.')
                                            ->rows(2),
                                    ]),

                                Section::make('Portal Perpustakaan (/portal-perpustakaan)')
                                    ->description('Penguncian akses sirkulasi peminjaman buku, katalog perpustakaan, dan kunjungan perpustakaan.')
                                    ->compact()
                                    ->schema([
                                        Toggle::make('maintenance_portal_perpustakaan')
                                            ->label('Aktifkan Mode Pemeliharaan (Tutup Sementara Portal Perpustakaan)')
                                            ->onColor('danger')
                                            ->offColor('success')
                                            ->helperText('Jika aktif (Merah), layanan Portal Perpustakaan ditutup sementara untuk pemeliharaan.'),
                                        Textarea::make('welcome_message_perpustakaan')
                                            ->label('Pesan Layar Pemeliharaan / Sambutan Portal Perpustakaan')
                                            ->placeholder('Contoh: Sistem sirkulasi perpustakaan sedang dalam proses opname inventaris buku.')
                                            ->rows(2),
                                    ]),

                                Section::make('Formulir Layanan Pengaduan Publik (/pengaduan)')
                                    ->description('Kontrol buka/tutup akses pengiriman aspirasi & pengaduan masyarakat pada halaman publik sekolah. (Pengaturan teks banner dan gambar maklumat dikelola di Portal Web).')
                                    ->compact()
                                    ->schema([
                                        Toggle::make('pengaduan_is_active')
                                            ->label('Buka Layanan Formulir Pengaduan Publik (/pengaduan)')
                                            ->onColor('success')
                                            ->offColor('danger')
                                            ->helperText('Jika aktif (Hijau), formulir pengaduan publik dapat diisi masyarakat. Jika dimatikan (Merah), halaman /pengaduan menampilkan status layanan sedang ditutup sementara.'),
                                    ]),
                            ]),
                    ]),

                Section::make('Siaran Pengumuman Sistem Global (Banner Seluruh Portal)')
                    ->description('Tampilkan pengumuman penting (jadwal pemeliharaan terjadwal, gangguan layanan sementara, atau informasi sistem) di bagian atas seluruh portal.')
                    ->icon('heroicon-o-megaphone')
                    ->schema([
                        Toggle::make('global_announcement_active')
                            ->label('Tampilkan Banner Pengumuman Sistem Global di Semua Portal')
                            ->onColor('warning')
                            ->helperText('Aktifkan untuk menayangkan banner informasi di layar pengguna tanpa harus menutup akses portal.'),
                        RichEditor::make('global_announcement')
                            ->label('Isi Pengumuman Sistem / Informasi Pemeliharaan')
                            ->placeholder('Contoh: Diberitahukan bahwa sistem akan mengalami pemeliharaan rutin pada hari Sabtu pukul 21.00 - 23.00 WIB...')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'link',
                                'redo',
                                'undo',
                            ]),
                    ]),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Simpan Status Maintenance & Pengumuman')
                ->icon('heroicon-o-check-circle')
                ->submit('save'),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $settings = PengaturanSekolah::first();

        if ($settings) {
            $settings->update([
                'maintenance_portal_siswa' => (bool) ($data['maintenance_portal_siswa'] ?? false),
                'welcome_message_siswa' => $data['welcome_message_siswa'] ?? null,
                'maintenance_portal_guru' => (bool) ($data['maintenance_portal_guru'] ?? false),
                'welcome_message_guru' => $data['welcome_message_guru'] ?? null,
                'maintenance_portal_perpustakaan' => (bool) ($data['maintenance_portal_perpustakaan'] ?? false),
                'welcome_message_perpustakaan' => $data['welcome_message_perpustakaan'] ?? null,
                'global_announcement_active' => (bool) ($data['global_announcement_active'] ?? false),
                'global_announcement' => $data['global_announcement'] ?? null,
            ]);
            Cache::forget('public_pengaturan_sekolah');
        }

        PengaduanSetting::instance()->update([
            'is_active' => (bool) ($data['pengaduan_is_active'] ?? true),
        ]);

        Notification::make()
            ->success()
            ->title('Pengaturan Pemeliharaan Disimpan')
            ->body('Status Mode Maintenance portal dan siaran pengumuman sistem berhasil diperbarui.')
            ->send();
    }
}

