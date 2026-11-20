<?php

namespace App\Filament\Sistem\Pages;

use App\Filament\Resources\UserResource;
use App\Models\AlumniSetting;
use App\Models\PengaduanSetting;
use App\Models\PengaturanSekolah;
use App\Models\PresensiNotificationSetting;
use App\Models\SpikapNotifSetting;
use App\Models\User;
use App\Models\WhatsAppSetting;
use Carbon\Carbon;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Contracts\Support\Htmlable;
use Spatie\Permission\Models\Role;

class SistemDashboard extends BaseDashboard
{
    protected static ?string $navigationLabel = 'Dashboard Sistem';

    protected static ?string $title = 'Dashboard Sistem';

    protected static string $routePath = '/';

    protected string $view = 'filament.sistem.pages.dashboard';

    public function getHeading(): string|Htmlable
    {
        return '';
    }

    protected function getViewData(): array
    {
        $user = auth()->user();
        $sekolah = PengaturanSekolah::current();
        $wa = WhatsAppSetting::current();
        $pengaduan = PengaduanSetting::instance();
        $alumni = AlumniSetting::instance();
        $spikap = SpikapNotifSetting::instance();

        // Statistik Pengguna & Role
        $totalPenggunaNonSiswa = User::whereDoesntHave('student')->count();
        $totalSuperAdmin = User::whereDoesntHave('student')
            ->where(function ($query) {
                $query->where('is_super_admin', true)
                    ->orWhereHas('roles', fn ($q) => $q->where('name', 'super_admin'));
            })
            ->count();
        $totalAdminModul = User::whereDoesntHave('student')
            ->whereHas('roles', fn ($q) => $q->where('name', 'like', 'admin_%'))
            ->count();
        $totalRoleSistem = Role::count();

        // Status Scheduler
        $schedulerActive = false;
        $schedulerAgeLabel = 'Belum terdeteksi';
        $heartbeatPath = storage_path('framework/schedule-heartbeat');
        if (file_exists($heartbeatPath)) {
            $timestamp = (int) file_get_contents($heartbeatPath);
            $lastRun = Carbon::createFromTimestamp($timestamp, config('app.timezone', 'Asia/Jakarta'));
            $ageMinutes = (int) round($lastRun->floatDiffInMinutes(now()));
            $schedulerActive = $ageMinutes <= 10;
            $schedulerAgeLabel = $ageMinutes <= 1 ? 'Aktif (< 1 mnt)' : ($schedulerActive ? "Aktif ({$ageMinutes} mnt)" : $lastRun->diffForHumans());
        }

        // Hitung portal yang sedang dalam Mode Maintenance
        $maintenancePortals = [];
        if ($sekolah?->maintenance_portal_siswa) {
            $maintenancePortals[] = 'Portal Siswa';
        }
        if ($sekolah?->maintenance_portal_guru) {
            $maintenancePortals[] = 'Portal Guru';
        }
        if ($sekolah?->maintenance_portal_perpustakaan) {
            $maintenancePortals[] = 'Portal Perpustakaan';
        }

        // Hitung setting notifikasi WA yang aktif
        $waNotifAktifCount = PresensiNotificationSetting::where('is_active', true)->count();

        // Tema Dinamis (mengikuti Pengaturan Tema di SistemThemeSettingsPage)
        $themePrimary = $sekolah?->theme_primary ?: '#7c3aed';
        $themeSecondary = $sekolah?->theme_secondary ?: ($sekolah?->theme_primary ?: '#4f46e5');

        return [
            'userName' => $user?->display_name ?? $user?->name ?? 'Administrator',
            'todayFormatted' => now('Asia/Jakarta')->translatedFormat('l, d F Y'),
            'themePrimary' => $themePrimary,
            'themeSecondary' => $themeSecondary,

            // Statistik
            'totalPenggunaNonSiswa' => $totalPenggunaNonSiswa,
            'totalSuperAdmin' => $totalSuperAdmin,
            'totalAdminModul' => $totalAdminModul,
            'totalRoleSistem' => $totalRoleSistem,

            // Status Sistem Real-Time
            'waActive' => (bool) $wa->is_active,
            'waInstance' => $wa->instance_name ?: 'Belum disetel',
            'schedulerActive' => $schedulerActive,
            'schedulerAgeLabel' => $schedulerAgeLabel,
            'maintenanceCount' => count($maintenancePortals),
            'maintenancePortals' => $maintenancePortals,
            'globalAnnouncementActive' => (bool) $sekolah?->global_announcement_active,
            'pengaduanActive' => (bool) $pengaduan->is_active,
            'alumniActive' => (bool) $alumni->is_active,
            'alumniShowTable' => (bool) $alumni->show_table,
            'spikapName' => $spikap->getNamaAplikasi(),
            'spikapSlug' => $spikap->getSlugUrl(),
            'waNotifAktifCount' => $waNotifAktifCount,

            // Hak Akses & URL
            'canUserAccess' => UserResource::canViewAny(),
            'canCreateUser' => UserResource::canCreate(),
            'urlUsersIndex' => UserResource::getUrl('index'),
            'urlUsersCreate' => UserResource::getUrl('create'),

            'canKoneksiWa' => SistemKoneksiWaPage::canAccess(),
            'urlKoneksiWa' => SistemKoneksiWaPage::getUrl(),

            'canSpikapSetting' => SistemPengaturanSpikapPage::canAccess(),
            'urlSpikapSetting' => SistemPengaturanSpikapPage::getUrl(),

            'canPelayananSetting' => SistemPengaturanPelayananPublikPage::canAccess(),
            'urlPelayananSetting' => SistemPengaturanPelayananPublikPage::getUrl(),

            'canAlumniSetting' => SistemPengaturanTracerAlumniPage::canAccess(),
            'urlAlumniSetting' => SistemPengaturanTracerAlumniPage::getUrl(),

            'canPesanMassal' => SistemPesanMassalPage::canAccess(),
            'urlPesanMassal' => SistemPesanMassalPage::getUrl(),

            'canMaintenance' => SistemMaintenancePortalPage::canAccess(),
            'urlMaintenance' => SistemMaintenancePortalPage::getUrl(),

            'canTheme' => SistemThemeSettingsPage::canAccess(),
            'urlTheme' => SistemThemeSettingsPage::getUrl(),

            'canWaPresensi' => SistemWaPresensiPage::canAccess(),
            'urlWaPresensi' => SistemWaPresensiPage::getUrl(),

            'canWaJurnal' => SistemWaJurnalMengajarPage::canAccess(),
            'urlWaJurnal' => SistemWaJurnalMengajarPage::getUrl(),

            'canWaSpikap' => SistemWaSpikapPage::canAccess(),
            'urlWaSpikap' => SistemWaSpikapPage::getUrl(),

            'canWaGuruWali' => SistemWaGuruWaliPage::canAccess(),
            'urlWaGuruWali' => SistemWaGuruWaliPage::getUrl(),

            'canWaPelayanan' => SistemWaPelayananPublikPage::canAccess(),
            'urlWaPelayanan' => SistemWaPelayananPublikPage::getUrl(),

            'canPortalSetup' => $user && ($user->isSuperAdmin() || $user->hasRole('admin_portal_setup')),
        ];
    }
}
