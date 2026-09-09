<?php

namespace App\Filament\Pages;

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
        $this->form->fill([
            'nama_aplikasi'              => $setting->getNamaAplikasi(),
            'sub_judul'                  => $setting->getSubJudul(),
            'slug_url'                   => $setting->getSlugUrl(),
            'penjelasan_aplikasi'        => $setting->getPenjelasanAplikasi(),
            'emergency_handlers'         => $setting->getActiveEmergencyHandlers(),
            'recipients'                 => $setting->getActiveRecipients(),
            'notify_guru_laporan_biasa'  => $setting->shouldNotifyGuruLaporanBiasa(),
            'notify_siswa_apresiasi'     => $setting->shouldNotifySiswaApresiasi(),
            'notify_siswa_tindak_lanjut' => $setting->shouldNotifySiswaTindakLanjut(),
            'target_penerima_siswa'      => $setting->getTargetPenerimaSiswa(),
        ]);
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

                Section::make('Wewenang Penanganan Laporan Darurat (Portal Guru)')
                    ->description('Tentukan pihak mana saja yang berhak melihat dan menindaklanjuti (mengubah status / investigasi / mediasi / penutupan kasus) setiap laporan darurat di Portal Guru.')
                    ->schema([
                        Forms\Components\CheckboxList::make('emergency_handlers')
                            ->label('Daftar Pihak yang Berwenang')
                            ->options([
                                'wali_kelas'     => 'Wali Kelas — Wali kelas dari rombel siswa yang melapor.',
                                'kepala_sekolah' => 'Kepala Sekolah — Pimpinan tertinggi yang memantau eskalasi darurat.',
                                'guru_bk'        => 'Guru BK — Guru Bimbingan Konseling untuk penanganan psikis & pembinaan.',
                                'waka_kesiswaan' => 'Waka Kesiswaan — Wakil Kepala Sekolah Bidang Kesiswaan.',
                            ])
                            ->descriptions([
                                'wali_kelas'     => 'Direkomendasikan: Wali kelas adalah pendamping langsung siswa di kelas.',
                                'kepala_sekolah' => 'Direkomendasikan: Mengizinkan Kepala Sekolah langsung mengambil tindakan cepat.',
                                'guru_bk'        => 'Opsional: Centang jika Guru BK diberikan hak investigasi langsung untuk kasus darurat.',
                                'waka_kesiswaan' => 'Opsional: Centang jika Waka Kesiswaan diikutsertakan dalam penanganan kasus darurat.',
                            ])
                            ->columns(1)
                            ->required()
                            ->helperText(fn () => 'Pihak yang dicentang akan dapat melihat laporan darurat di inbox Portal Guru dan form Tindak Lanjut akan aktif (dapat memperbarui status). Super Admin dan Admin ' . SpikapNotifSetting::instance()->getNamaAplikasi() . ' selalu memiliki wewenang penuh.'),
                    ]),

                Section::make('Penerima Notifikasi WhatsApp Laporan Darurat')
                    ->description('Tentukan pihak mana saja yang otomatis menerima pesan WhatsApp segera setelah siswa mengirimkan laporan darurat.')
                    ->schema([
                        Forms\Components\CheckboxList::make('recipients')
                            ->label('Daftar Penerima Notifikasi')
                            ->options([
                                'wali_kelas'     => 'Wali Kelas — Otomatis mendeteksi wali kelas dari rombel/kelas aktif siswa yang melapor.',
                                'kepala_sekolah' => 'Kepala Sekolah — Otomatis mendeteksi guru dengan jabatan Kepala Sekolah di sistem.',
                                'guru_bk'        => 'Guru BK — Seluruh guru dengan jabatan Guru BK di sistem.',
                                'waka_kesiswaan' => 'Waka Kesiswaan — Seluruh guru dengan jabatan Waka Kesiswaan di sistem.',
                            ])
                            ->descriptions([
                                'wali_kelas'     => 'Direkomendasikan: Wali kelas merupakan pihak pertama yang mendampingi siswa di kelas.',
                                'kepala_sekolah' => 'Direkomendasikan: Pimpinan sekolah memantau seluruh eskalasi kasus darurat.',
                                'guru_bk'        => 'Opsional: Dapat diaktifkan jika Guru BK juga perlu disiagakan sejak detik pertama laporan darurat.',
                                'waka_kesiswaan' => 'Opsional: Dapat diaktifkan untuk koordinasi pembinaan kesiswaan.',
                            ])
                            ->columns(1)
                            ->required()
                            ->helperText('Catatan: Pengiriman WA menggunakan gateway resmi sekolah. Pastikan nomor HP guru terkait sudah terdaftar dengan format yang benar pada modul Data Guru.'),
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

        $setting->update([
            'nama_aplikasi'              => $namaApp,
            'sub_judul'                  => trim($data['sub_judul'] ?? '') ?: 'Anti-Perundungan & Pengaduan Siswa',
            'slug_url'                   => $slugUrl ?: 'spikap',
            'penjelasan_aplikasi'        => trim($data['penjelasan_aplikasi'] ?? '') ?: 'Sistem Pelaporan Integratif Konflik & Anti-Perundungan SPENSA',
            'emergency_handlers'         => $data['emergency_handlers'] ?? ['wali_kelas', 'kepala_sekolah'],
            'recipients'                 => $data['recipients'] ?? [],
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
