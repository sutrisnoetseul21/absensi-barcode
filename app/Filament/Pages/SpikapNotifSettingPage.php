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
    protected static string|\UnitEnum|null $navigationGroup = 'SPIKAP';
    protected static ?string $title = 'Pengaturan SPIKAP & Notifikasi Darurat';
    protected static ?string $navigationLabel = 'Pengaturan SPIKAP';
    protected static ?string $slug = 'spikap/pengaturan-notifikasi';
    protected static ?int $navigationSort = 2;

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
                            ->helperText('Pihak yang dicentang akan dapat melihat laporan darurat di inbox Portal Guru dan form Tindak Lanjut akan aktif (dapat memperbarui status). Super Admin dan SPIKAP Admin selalu memiliki wewenang penuh.'),
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
        $setting->update([
            'emergency_handlers'         => $data['emergency_handlers'] ?? ['wali_kelas', 'kepala_sekolah'],
            'recipients'                 => $data['recipients'] ?? [],
            'notify_guru_laporan_biasa'  => (bool) ($data['notify_guru_laporan_biasa'] ?? false),
            'notify_siswa_apresiasi'     => (bool) ($data['notify_siswa_apresiasi'] ?? false),
            'notify_siswa_tindak_lanjut' => (bool) ($data['notify_siswa_tindak_lanjut'] ?? false),
            'target_penerima_siswa'      => $data['target_penerima_siswa'] ?? ['siswa'],
        ]);

        Notification::make()
            ->success()
            ->title('Pengaturan SPIKAP Berhasil Disimpan')
            ->body('Seluruh pengaturan wewenang, notifikasi WhatsApp guru, dan konfirmasi siswa/orang tua telah diperbarui.')
            ->send();
    }
}
