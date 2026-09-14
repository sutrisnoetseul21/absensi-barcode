<?php

namespace App\Filament\Pages;

use App\Models\Jabatan;
use App\Models\SpikapNotifSetting;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SpikapNotifSettingPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-bell-alert';
    protected static ?string $slug = 'spikap/pengaturan-notifikasi';
    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        try {
            return SpikapNotifSetting::instance()->getNamaAplikasi();
        } catch (\Throwable $e) {
            return 'SPIKAP';
        }
    }

    public function getTitle(): string|\Illuminate\Contracts\Support\Htmlable
    {
        try {
            $name = SpikapNotifSetting::instance()->getNamaAplikasi();
            return "Pengaturan {$name} & Notifikasi Darurat";
        } catch (\Throwable $e) {
            return 'Pengaturan SPIKAP & Notifikasi Darurat';
        }
    }

    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable|null
    {
        try {
            $name = SpikapNotifSetting::instance()->getNamaAplikasi();
            return "Pengaturan {$name} & Notifikasi Darurat";
        } catch (\Throwable $e) {
            return 'Pengaturan SPIKAP & Notifikasi Darurat';
        }
    }

    public static function getNavigationLabel(): string
    {
        try {
            return 'Pengaturan ' . SpikapNotifSetting::instance()->getNamaAplikasi();
        } catch (\Throwable $e) {
            return 'Pengaturan SPIKAP';
        }
    }

    protected string $view = 'filament.pages.spikap-notif-setting-page';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        $user = auth()->user();
        if (!$user) {
            return false;
        }

        return $user->isSuperAdmin()
            || $user->hasRole('super_admin')
            || $user->hasRole('spikap_admin');
    }

    public function mount(): void
    {
        $setting = SpikapNotifSetting::instance();

        // Gabungkan handlers + recipients menjadi satu set pilihan terpadu
        $unified = array_values(array_unique(array_merge(
            $setting->getActiveEmergencyHandlers(),
            $setting->getActiveRecipients()
        )));

        $this->form->fill([
            'nama_aplikasi'              => $setting->getNamaAplikasi(),
            'sub_judul'                  => $setting->getSubJudul(),
            'slug_url'                   => $setting->getSlugUrl(),
            'penjelasan_aplikasi'        => $setting->getPenjelasanAplikasi(),
            'unified_darurat_targets'    => $unified,
            'notify_guru_laporan_biasa'  => $setting->shouldNotifyGuruLaporanBiasa(),
            'notify_siswa_apresiasi'     => $setting->shouldNotifySiswaApresiasi(),
            'notify_siswa_tindak_lanjut' => $setting->shouldNotifySiswaTindakLanjut(),
            'target_penerima_siswa'      => $setting->getTargetPenerimaSiswa(),
        ]);
    }

    /**
     * Bangun semua opsi jabatan: 4 jabatan tetap sistem + jabatan lain dari tabel jabatans.
     * Return format: ['key' => 'Label'] — semua dalam satu flat array.
     */
    private function getAllDaruratOptions(): array
    {
        $fixed = [
            'kepala_sekolah' => 'Kepala Sekolah',
            'wali_kelas'     => 'Wali Kelas',
            'guru_bk'        => 'Guru BK',
            'waka_kesiswaan' => 'Waka Kesiswaan',
        ];

        try {
            $excludeNames = [
                'Kepala Sekolah', 'Guru BK', 'BK', 'Bimbingan Konseling', 'Bimbingan dan Konseling',
                'Waka Kesiswaan', 'Wakil Kepala Sekolah Bidang Kesiswaan',
                'Wakil Kepala Sekolah Bagian Kesiswaan', 'Kesiswaan',
            ];

            $dynamic = Jabatan::whereNotIn('nama_jabatan', $excludeNames)
                ->orderBy('nama_jabatan')
                ->pluck('nama_jabatan')
                ->mapWithKeys(fn($j) => ["jabatan:{$j}" => $j])
                ->toArray();

            return array_merge($fixed, $dynamic);
        } catch (\Throwable $e) {
            return $fixed;
        }
    }



    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identitas & Penamaan Aplikasi / Modul')
                    ->description('Atur nama, sub-judul, penjelasan, dan slug URL modul aduan siswa agar sesuai dengan identitas dan kebijakan sekolah Anda.')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('nama_aplikasi')
                            ->label('Nama Aplikasi / Singkatan')
                            ->required()
                            ->maxLength(50)
                            ->default('SPIKAP')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if (!empty($state)) {
                                    $set('slug_url', \Illuminate\Support\Str::slug($state));
                                }
                            })
                            ->helperText('Nama utama modul yang tampil di menu portal, judul halaman, dashboard, dan notifikasi WA (contoh: SPIKAP, SUARA KITA, LAPOR BK).'),

                        Forms\Components\TextInput::make('sub_judul')
                            ->label('Sub-Judul / Label Pendamping')
                            ->maxLength(100)
                            ->default('Anti-Perundungan & Pengaduan Siswa')
                            ->helperText('Label singkat pendamping yang muncul di kartu dashboard dan badge.'),

                        Forms\Components\TextInput::make('slug_url')
                            ->label('Slug URL Khusus')
                            ->maxLength(50)
                            ->placeholder('spikap')
                            ->helperText('Bagian URL browser setelah /portal-siswa/ atau /portal-guru/ (contoh: suara-kita). Otomatis mengikuti nama aplikasi jika dikosongkan.')
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('penjelasan_aplikasi')
                            ->label('Penjelasan Lengkap / Kepanjangan Aplikasi')
                            ->rows(2)
                            ->default('Sistem Pelaporan Integratif Konflik & Anti-Perundungan SPENSA')
                            ->helperText('Penjelasan resmi yang tampil di bawah judul formulir pembuatan laporan siswa.')
                            ->columnSpanFull(),
                    ]),

                Section::make('Penanganan Laporan Darurat')
                    ->description('Pilih jabatan yang berhak menangani laporan darurat di Portal Guru sekaligus otomatis menerima notifikasi WhatsApp saat laporan masuk. Pilih sesuka Anda — satu jabatan, beberapa, atau semua.')
                    ->schema([
                        Forms\Components\CheckboxList::make('unified_darurat_targets')
                            ->label('Jabatan Penanganan & Penerima WA Darurat')
                            ->options(fn () => $this->getAllDaruratOptions())
                            ->columns(3)
                            ->helperText(fn () => 'Jabatan yang dicentang akan: (1) dapat melihat & menindaklanjuti laporan darurat di Portal Guru, dan (2) otomatis menerima notifikasi WhatsApp. Super Admin & Admin ' . SpikapNotifSetting::instance()->getNamaAplikasi() . ' selalu memiliki wewenang penuh tanpa perlu dicentang.'),
                    ]),

                Section::make('Notifikasi WhatsApp Laporan Biasa ke Guru')
                    ->description('Atur apakah laporan biasa yang dikirimkan siswa akan otomatis meneruskan notifikasi WhatsApp ke Guru yang dituju (Wali Kelas atau Guru BK pembina kelas).')
                    ->schema([
                        Forms\Components\Toggle::make('notify_guru_laporan_biasa')
                            ->label('Aktifkan Notifikasi WA Laporan Biasa ke Guru Tujuan')
                            ->helperText('Jika aktif, saat siswa melapor biasa ke Wali Kelas atau Guru BK, guru terkait akan menerima pesan ringkasan laporan melalui WhatsApp.')
                            ->default(true),
                    ]),

                Section::make('Notifikasi WhatsApp ke Siswa & Orang Tua')
                    ->description('Atur pengiriman pesan WhatsApp kepada pihak siswa/keluarga sebagai tanda terima & apresiasi atas keberanian melapor, serta pembaruan perkembangan kasus.')
                    ->schema([
                        Forms\Components\Toggle::make('notify_siswa_apresiasi')
                            ->label('Kirim Pesan Konfirmasi & Apresiasi Saat Laporan Dibuat')
                            ->helperText('Mengirimkan pesan tanda terima & apresiasi segera setelah laporan baru berhasil tersimpan di sistem.')
                            ->default(true),

                        Forms\Components\Toggle::make('notify_siswa_tindak_lanjut')
                            ->label('Kirim Notifikasi Perkembangan Kasus Saat Status Diperbarui')
                            ->helperText('Mengirimkan pesan WhatsApp pembaruan status (misal: masuk tahap investigasi / selesai) saat petugas memperbarui status penanganan.')
                            ->default(true),

                        Forms\Components\CheckboxList::make('target_penerima_siswa')
                            ->label('Pilihan Kontak Penerima Pesan Siswa/Keluarga')
                            ->options([
                                'siswa'     => 'Nomor HP Siswa — Kirim langsung ke WhatsApp pribadi siswa pelapor.',
                                'orang_tua' => 'Nomor HP Orang Tua — Kirim ke WhatsApp orang tua/wali siswa pelapor.',
                            ])
                            ->descriptions([
                                'siswa'     => 'Direkomendasikan: Memberi rasa aman langsung kepada anak yang berani melapor.',
                                'orang_tua' => 'Opsional: Dapat dicentang jika sekolah menerapkan transparansi laporan langsung ke wali murid.',
                            ])
                            ->columns(1)
                            ->default(['siswa'])
                            ->helperText('Anda dapat memilih Siswa saja, Orang Tua saja, atau Keduanya sekaligus sesuai kebijakan sekolah.'),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $setting = SpikapNotifSetting::instance();

        $namaApp = trim($data['nama_aplikasi'] ?? '') ?: 'SPIKAP';
        $inputSlug = trim($data['slug_url'] ?? '');
        $namaAppSlug = \Illuminate\Support\Str::slug($namaApp);

        if (empty($inputSlug) || ($inputSlug === 'spikap' && $namaAppSlug !== 'spikap')) {
            $slugUrl = $namaAppSlug;
        } else {
            $slugUrl = \Illuminate\Support\Str::slug($inputSlug);
        }

        // Pilihan terpadu: satu field mengontrol sekaligus handlers (portal) & recipients (WA)
        $unified = array_values(array_filter($data['unified_darurat_targets'] ?? []));

        // emergency_handlers: hanya key tetap yang valid di sistem (wali_kelas, kepala_sekolah, dll.)
        // jabatan custom (prefix 'jabatan:') tidak masuk handlers karena tidak ada role-check-nya di sistem
        $fixedKeys   = ['wali_kelas', 'kepala_sekolah', 'guru_bk', 'waka_kesiswaan'];
        $allHandlers = array_values(array_filter($unified, fn($k) => in_array($k, $fixedKeys)));

        // recipients: semua yang dipilih (termasuk jabatan custom) mendapat notifikasi WA
        $allRecipients = $unified;

        $setting->update([
            'nama_aplikasi'              => $namaApp,
            'sub_judul'                  => trim($data['sub_judul'] ?? '') ?: 'Anti-Perundungan & Pengaduan Siswa',
            'slug_url'                   => $slugUrl ?: 'spikap',
            'penjelasan_aplikasi'        => trim($data['penjelasan_aplikasi'] ?? '') ?: 'Sistem Pelaporan Integratif Konflik & Anti-Perundungan SPENSA',
            'emergency_handlers'         => $allHandlers,
            'recipients'                 => $allRecipients,
            'notify_guru_laporan_biasa'  => (bool) ($data['notify_guru_laporan_biasa'] ?? false),
            'notify_siswa_apresiasi'     => (bool) ($data['notify_siswa_apresiasi'] ?? false),
            'notify_siswa_tindak_lanjut' => (bool) ($data['notify_siswa_tindak_lanjut'] ?? false),
            'target_penerima_siswa'      => $data['target_penerima_siswa'] ?? ['siswa'],
        ]);

        Notification::make()
            ->success()
            ->title("Pengaturan {$namaApp} Berhasil Disimpan")
            ->body('Seluruh identitas modul, wewenang, notifikasi WhatsApp guru, dan konfirmasi siswa/orang tua telah diperbarui.')
            ->send();

        $this->redirect(static::getUrl());
    }
}
