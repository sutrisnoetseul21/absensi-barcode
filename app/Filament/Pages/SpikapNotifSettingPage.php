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
    protected static ?string $title = 'Pengaturan Notifikasi SPIKAP';
    protected static ?string $navigationLabel = 'Pengaturan Notifikasi';
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
            'recipients' => $setting->getActiveRecipients(),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
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
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $setting = SpikapNotifSetting::instance();
        $setting->update([
            'recipients' => $data['recipients'] ?? [],
        ]);

        Notification::make()
            ->success()
            ->title('Pengaturan Notifikasi Berhasil Disimpan')
            ->body('Daftar penerima notifikasi WhatsApp untuk laporan darurat SPIKAP telah diperbarui.')
            ->send();
    }
}
