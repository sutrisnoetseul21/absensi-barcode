<?php

namespace App\Filament\Sistem\Pages;

use App\Models\SpikapNotifSetting;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class SistemPengaturanSpikapPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shield-exclamation';

    protected string $view = 'filament.pages.spikap-notif-setting-page';

    protected static ?string $navigationLabel = 'Pengaturan Modul SPIKAP';

    protected static ?string $slug = 'pengaturan-spikap';

    protected static ?string $title = 'Pengaturan Modul SPIKAP & Wewenang Darurat';

    protected ?string $subheading = 'Atur penamaan aplikasi, slug URL, dan jabatan yang berwenang menangani laporan darurat di Portal Guru.';

    protected static string|\UnitEnum|null $navigationGroup = 'Gateway & Pengaturan Modul';

    protected static ?int $navigationSort = 2;

    public ?array $data = [];

    public static function canAccess(): bool
    {
        $user = auth()->user();
        if (!$user) {
            return false;
        }

        return $user->isSuperAdmin()
            || $user->hasRole(['super_admin', 'admin_portal_sistem', 'spikap_admin']);
    }

    public function mount(): void
    {
        $setting = SpikapNotifSetting::instance();

        $this->form->fill([
            'nama_aplikasi'       => $setting->getNamaAplikasi(),
            'sub_judul'           => $setting->getSubJudul(),
            'slug_url'            => $setting->getSlugUrl(),
            'penjelasan_aplikasi' => $setting->getPenjelasanAplikasi(),
            'emergency_handlers'  => $setting->getActiveEmergencyHandlers(),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identitas & Penamaan Aplikasi / Modul')
                    ->description('Atur nama, sub-judul, penjelasan, dan slug URL modul pengaduan siswa yang tampil di Portal Siswa (/portal-siswa/...) dan Portal Guru (/portal-guru/...).')
                    ->columns(2)
                    ->schema([
                        TextInput::make('nama_aplikasi')
                            ->label('Nama Aplikasi / Singkatan')
                            ->required()
                            ->maxLength(50)
                            ->default('SPIKAP')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, Set $set) {
                                if (!empty($state)) {
                                    $set('slug_url', Str::slug((string) $state));
                                }
                            })
                            ->helperText('Nama utama modul yang tampil di menu portal, judul halaman, dan placeholder {nama_aplikasi} di WA.'),

                        TextInput::make('sub_judul')
                            ->label('Sub-Judul / Label Pendamping')
                            ->maxLength(100)
                            ->default('Anti-Perundungan & Pengaduan Siswa')
                            ->helperText('Label singkat pendamping yang muncul di kartu dashboard dan badge.'),

                        TextInput::make('slug_url')
                            ->label('Slug URL Khusus')
                            ->maxLength(50)
                            ->placeholder('spikap')
                            ->helperText('Bagian URL setelah /portal-siswa/ atau /portal-guru/ (contoh: spikap atau suara-kita).')
                            ->columnSpanFull(),

                        Textarea::make('penjelasan_aplikasi')
                            ->label('Penjelasan Lengkap / Kepanjangan Aplikasi')
                            ->rows(2)
                            ->default('Sistem Pelaporan Integratif Konflik & Anti-Perundungan')
                            ->helperText('Penjelasan resmi yang tampil di bawah judul formulir pembuatan laporan siswa.')
                            ->columnSpanFull(),
                    ]),

                Section::make('Wewenang Penanganan Kasus Darurat di Portal Guru')
                    ->description('Tentukan pejabat / peran guru yang memiliki wewenang mengubah status & menindaklanjuti laporan bersifat DARURAT di Portal Guru (/portal-guru/spikap).')
                    ->schema([
                        CheckboxList::make('emergency_handlers')
                            ->label('Pejabat Berwenang Menangani Kasus Darurat di Portal Guru')
                            ->options([
                                'kepala_sekolah' => 'Kepala Sekolah (Monitoring penuh semua laporan + tindak lanjut darurat)',
                                'wali_kelas'     => 'Wali Kelas (Khusus laporan darurat dari siswa kelas binaannya)',
                                'guru_bk'        => 'Guru BK (Khusus laporan darurat dari siswa kelas binaan BK)',
                                'waka_kesiswaan' => 'Waka Kesiswaan (Seluruh laporan darurat sekolah)',
                            ])
                            ->columns(2)
                            ->helperText('Catatan: Kepala Sekolah secara default dapat memantau seluruh laporan SPIKAP di Portal Guru. Pengaturan penerima & template pesan WhatsApp SPIKAP kini dipusatkan di menu "Notifikasi WA SPIKAP".'),

                        Placeholder::make('info_wa_spikap')
                            ->hiddenLabel()
                            ->content(new HtmlString('
                                <div class="p-3 rounded-lg bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800 text-xs text-blue-800 dark:text-blue-200">
                                    💡 <strong>Tidak Ganda Lagi:</strong> Pengaturan toggle aktif, daftar penerima WhatsApp darurat, dan template pesan WhatsApp SPIKAP diatur terpusat melalui menu <a href="' . url('/portal-sistem/wa-spikap') . '" class="underline font-bold">Notifikasi WA SPIKAP</a>.
                                </div>
                            ')),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data    = $this->form->getState();
        $setting = SpikapNotifSetting::instance();

        $namaApp     = trim((string) ($data['nama_aplikasi'] ?? '')) ?: 'SPIKAP';
        $inputSlug   = trim((string) ($data['slug_url'] ?? ''));
        $namaAppSlug = Str::slug($namaApp);

        if (empty($inputSlug) || ($inputSlug === 'spikap' && $namaAppSlug !== 'spikap')) {
            $slugUrl = $namaAppSlug;
        } else {
            $slugUrl = Str::slug($inputSlug);
        }

        $fixedKeys = ['wali_kelas', 'kepala_sekolah', 'guru_bk', 'waka_kesiswaan'];
        $handlers  = array_values(array_filter(
            (array) ($data['emergency_handlers'] ?? []),
            fn ($k) => in_array($k, $fixedKeys, true)
        ));

        $setting->update([
            'nama_aplikasi'       => $namaApp,
            'sub_judul'           => trim((string) ($data['sub_judul'] ?? '')) ?: 'Anti-Perundungan & Pengaduan Siswa',
            'slug_url'            => $slugUrl ?: 'spikap',
            'penjelasan_aplikasi' => trim((string) ($data['penjelasan_aplikasi'] ?? '')) ?: 'Sistem Pelaporan Integratif Konflik & Anti-Perundungan',
            'emergency_handlers'  => $handlers,
        ]);

        Notification::make()
            ->success()
            ->title("Pengaturan Modul {$namaApp} Berhasil Disimpan")
            ->body('Identitas modul dan wewenang penanganan darurat di Portal Guru telah diperbarui.')
            ->send();
    }
}

