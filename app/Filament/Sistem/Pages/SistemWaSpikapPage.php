<?php

namespace App\Filament\Sistem\Pages;

use App\Filament\Traits\HasSpikapDanGuruWaliWaNotifications;
use App\Models\PresensiNotificationSetting;
use App\Models\SpikapNotifSetting;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Exceptions\Halt;

class SistemWaSpikapPage extends Page implements HasForms
{
    use InteractsWithForms;
    use HasSpikapDanGuruWaliWaNotifications;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shield-exclamation';

    protected string $view = 'filament.pages.manajemen-notifikasi-wa';

    protected static ?string $navigationLabel = 'Notifikasi WA SPIKAP';

    protected static ?string $slug = 'wa-spikap';

    protected static ?string $title = 'Notifikasi WhatsApp SPIKAP';

    protected ?string $subheading = 'Pengaturan terpusat penerima dan template WhatsApp untuk Laporan Darurat, Laporan Biasa ke Guru, Apresiasi, serta Update Status Kasus.';

    protected static string|\UnitEnum|null $navigationGroup = 'Notifikasi WhatsApp';

    protected static ?int $navigationSort = 3;

    public ?array $data = [];

    public static function canAccess(): bool
    {
        $user = auth()->user();
        if (!$user) {
            return false;
        }

        return $user->isSuperAdmin()
            || $user->hasRole(['super_admin', 'admin_portal_sistem', 'spikap_admin', 'admin_presensi_editor']);
    }

    public function mount(): void
    {
        $data = [];
        $this->loadSpikapDanGuruWaliData($data);
        $this->form->fill($data);
    }

    public function form(Schema $schema): Schema
    {
        $tab = $this->getSpikapWaTab();

        return $schema
            ->components($tab->getComponents(withActions: true, withHidden: true))
            ->statePath('data');
    }

    public function save(): void
    {
        try {
            $data = $this->form->getState();

            PresensiNotificationSetting::updateOrCreate(
                ['status_presensi' => 'spikap_darurat'],
                [
                    'is_active'      => $data['spikap_darurat_is_active'] ?? false,
                    'recipients'     => $data['spikap_darurat_recipients'] ?? [],
                    'template_pesan' => $data['spikap_darurat_template_pesan'] ?? '',
                ]
            );

            PresensiNotificationSetting::updateOrCreate(
                ['status_presensi' => 'spikap_biasa'],
                [
                    'is_active'      => $data['spikap_biasa_is_active'] ?? false,
                    'recipients'     => ['wali_kelas', 'guru_bk'],
                    'template_pesan' => $data['spikap_biasa_template_pesan'] ?? '',
                ]
            );

            PresensiNotificationSetting::updateOrCreate(
                ['status_presensi' => 'spikap_apresiasi_siswa'],
                [
                    'is_active'      => $data['spikap_apresiasi_is_active'] ?? false,
                    'recipients'     => $data['spikap_apresiasi_targets'] ?? ['siswa'],
                    'template_pesan' => $data['spikap_apresiasi_siswa_template'] ?? '',
                ]
            );

            PresensiNotificationSetting::updateOrCreate(
                ['status_presensi' => 'spikap_apresiasi_ortu'],
                [
                    'is_active'      => $data['spikap_apresiasi_is_active'] ?? false,
                    'recipients'     => $data['spikap_apresiasi_targets'] ?? ['siswa'],
                    'template_pesan' => $data['spikap_apresiasi_ortu_template'] ?? '',
                ]
            );

            PresensiNotificationSetting::updateOrCreate(
                ['status_presensi' => 'spikap_status_siswa'],
                [
                    'is_active'      => $data['spikap_status_is_active'] ?? false,
                    'recipients'     => $data['spikap_apresiasi_targets'] ?? ['siswa'],
                    'template_pesan' => $data['spikap_status_siswa_template'] ?? '',
                ]
            );

            PresensiNotificationSetting::updateOrCreate(
                ['status_presensi' => 'spikap_status_ortu'],
                [
                    'is_active'      => $data['spikap_status_is_active'] ?? false,
                    'recipients'     => $data['spikap_apresiasi_targets'] ?? ['siswa'],
                    'template_pesan' => $data['spikap_status_ortu_template'] ?? '',
                ]
            );

            // Sinkronkan ke SpikapNotifSetting tanpa menimpa emergency_handlers yang diatur di Pengaturan Modul SPIKAP
            try {
                $spikapModel = SpikapNotifSetting::instance();
                $spikapModel->update([
                    'recipients'                 => $data['spikap_darurat_recipients'] ?? [],
                    'notify_guru_laporan_biasa'  => (bool) ($data['spikap_biasa_is_active'] ?? false),
                    'notify_siswa_apresiasi'     => (bool) ($data['spikap_apresiasi_is_active'] ?? false),
                    'notify_siswa_tindak_lanjut' => (bool) ($data['spikap_status_is_active'] ?? false),
                    'target_penerima_siswa'      => $data['spikap_apresiasi_targets'] ?? ['siswa'],
                ]);
            } catch (\Throwable $e) {
            }

            Notification::make()
                ->title('Pengaturan notifikasi WA SPIKAP berhasil disimpan')
                ->success()
                ->send();
        } catch (Halt $exception) {
            return;
        }
    }
}

