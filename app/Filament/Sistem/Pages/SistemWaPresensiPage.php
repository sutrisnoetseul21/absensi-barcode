<?php

namespace App\Filament\Sistem\Pages;

use App\Filament\Presensi\Pages\ManajemenNotifikasiWaPage;
use App\Models\PresensiDailyReportSetting;
use App\Models\PresensiNotificationSetting;
use App\Models\PresensiSchoolSummarySetting;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;
use Filament\Support\Exceptions\Halt;

class SistemWaPresensiPage extends ManajemenNotifikasiWaPage
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationLabel = 'Notifikasi WA Presensi';

    protected static ?string $slug = 'wa-presensi';

    protected static ?string $title = 'Notifikasi WhatsApp Presensi Harian';

    protected ?string $subheading = 'Atur template dan jadwal pengiriman otomatis WhatsApp khusus presensi kehadiran pagi, sholat dhuhur, ijin online, dan rekap harian.';

    protected static string|\UnitEnum|null $navigationGroup = 'Notifikasi WhatsApp';

    protected static ?int $navigationSort = 1;

    public static function canAccess(): bool
    {
        $user = auth()->user();
        if (!$user) {
            return false;
        }

        return $user->isSuperAdmin()
            || $user->hasRole(['super_admin', 'admin_portal_sistem', 'admin_presensi_editor']);
    }

    public function form(Schema $schema): Schema
    {
        $parentSchema     = parent::form($schema);
        $parentComponents = $parentSchema->getComponents(withActions: true, withHidden: true);

        foreach ($parentComponents as $component) {
            if ($component instanceof Tabs) {
                $allTabs = $component->getComponents(withActions: true, withHidden: true);
                // Hanya ambil 6 Tab Presensi (tanpa SPIKAP, Guru Wali, dan Jurnal Mengajar)
                $component->tabs(array_slice($allTabs, 0, 6));
            }
        }

        return $schema
            ->components($parentComponents)
            ->statePath('data');
    }

    public function save(): void
    {
        try {
            $data = $this->form->getState();

            $exceptionStatuses = ['alpa', 'sakit', 'izin'];
            foreach ($exceptionStatuses as $status) {
                PresensiNotificationSetting::updateOrCreate(
                    ['status_presensi' => $status],
                    [
                        'is_active'      => $data['student_notif_is_active'] ?? false,
                        'recipients'     => $data['student_notif_recipients'] ?? [],
                        'template_pesan' => $data['student_notif_template_pesan'] ?? '',
                    ]
                );
            }

            PresensiNotificationSetting::updateOrCreate(
                ['status_presensi' => 'telat'],
                [
                    'is_active'      => $data['telat_notif_is_active'] ?? false,
                    'recipients'     => $data['telat_notif_recipients'] ?? [],
                    'template_pesan' => $data['telat_notif_template_pesan'] ?? '',
                ]
            );

            PresensiNotificationSetting::updateOrCreate(
                ['status_presensi' => 'belum'],
                [
                    'is_active'      => $data['belum_notif_is_active'] ?? false,
                    'is_auto_send'   => $data['belum_notif_is_auto_send'] ?? false,
                    'auto_send_time' => $data['belum_notif_auto_send_time'] ?? '07:30:00',
                    'recipients'     => $data['belum_notif_recipients'] ?? [],
                    'template_pesan' => $data['belum_notif_template_pesan'] ?? '',
                ]
            );

            PresensiNotificationSetting::updateOrCreate(
                ['status_presensi' => 'sholat_dhuhur'],
                [
                    'is_active'      => $data['sholat_dhuhur_notif_is_active'] ?? false,
                    'is_auto_send'   => $data['sholat_dhuhur_notif_is_auto_send'] ?? false,
                    'auto_send_time' => $data['sholat_dhuhur_notif_auto_send_time'] ?? '13:00:00',
                    'recipients'     => $data['sholat_dhuhur_notif_recipients'] ?? [],
                    'template_pesan' => $data['sholat_dhuhur_notif_template_pesan'] ?? '',
                ]
            );

            PresensiNotificationSetting::updateOrCreate(
                ['status_presensi' => 'leave_request'],
                [
                    'is_active'      => $data['leave_request_notif_is_active'] ?? false,
                    'recipients'     => $data['leave_request_notif_recipients'] ?? [],
                    'template_pesan' => $data['leave_request_notif_template_pesan'] ?? '',
                ]
            );

            PresensiNotificationSetting::updateOrCreate(
                ['status_presensi' => 'leave_approval'],
                [
                    'is_active'      => $data['leave_approval_notif_is_active'] ?? false,
                    'recipients'     => $data['leave_approval_notif_recipients'] ?? [],
                    'template_pesan' => $data['leave_approval_notif_template_pesan'] ?? '',
                ]
            );

            $daily = PresensiDailyReportSetting::current();
            $daily->update([
                'is_active'      => $data['daily_is_active'] ?? false,
                'cutoff_time'    => $data['daily_cutoff_time'] ?? '08:00:00',
                'recipients'     => $data['daily_recipients'] ?? [],
                'template_pesan' => $data['daily_template_pesan'] ?? '',
            ]);

            $school = PresensiSchoolSummarySetting::current();
            $school->update([
                'is_active'       => $data['school_is_active'] ?? false,
                'cutoff_time'     => $data['school_cutoff_time'] ?? '08:15:00',
                'recipients'      => $data['school_recipients'] ?? [],
                'template_header' => $data['school_template_header'] ?? '',
                'template_row'    => $data['school_template_row'] ?? '',
                'template_footer' => $data['school_template_footer'] ?? '',
            ]);

            Notification::make()
                ->title('Pengaturan notifikasi WA presensi berhasil disimpan')
                ->success()
                ->send();
        } catch (Halt $exception) {
            return;
        }
    }
}

