<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use App\Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use App\Filament\Widgets\QuickLinksWidget;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->favicon(function () {
                try {
                    $logo = \App\Models\PengaturanSekolah::current()?->school_logo_path;
                    return $logo ? asset('storage/' . $logo) : asset('favicon.ico');
                } catch (\Exception $e) {
                    return asset('favicon.ico');
                }
            })
            ->brandName(function () {
                try {
                    $name = \App\Models\PengaturanSekolah::current()?->school_name;
                    return $name ? 'Sistem Presensi Digital ' . $name : 'Sistem Presensi Digital';
                } catch (\Exception $e) {
                    return 'Sistem Presensi Digital';
                }
            })
            ->brandLogo(function () {
                try {
                    $logo = \App\Models\PengaturanSekolah::current()?->school_logo_path;
                    $name = \App\Models\PengaturanSekolah::current()?->school_name;
                    $title = $name ? 'Sistem Presensi Digital ' . $name : 'Sistem Presensi Digital';
                    
                    if ($logo) {
                        return new \Illuminate\Support\HtmlString('
                            <div class="flex items-center gap-2">
                                <img src="' . asset('storage/' . $logo) . '" alt="Logo" style="height: 2rem; width: auto;" />
                                <span class="font-bold text-lg leading-tight">' . $title . '</span>
                            </div>
                        ');
                    }
                    return null;
                } catch (\Exception $e) {
                    return null;
                }
            })
            ->login(\App\Filament\Pages\Auth\CustomLogin::class)
            ->profile()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->navigationGroups([
                'Data Master',
                'Akademik',
                'Presensi',
                'Perpustakaan',
                'Web Profil Sekolah',
                'Pengaturan Sistem',
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverResources(in: app_path('Filament/Akademik/Resources'), for: 'App\Filament\Akademik\Resources')
            ->discoverResources(in: app_path('Filament/Presensi/Resources'), for: 'App\Filament\Presensi\Resources')
            ->discoverResources(in: app_path('Filament/Perpustakaan/Resources'), for: 'App\Filament\Perpustakaan\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
                // Akademik Pages (exclude AkademikDashboard — rute konflik dengan /admin)
                \App\Filament\Akademik\Pages\AkademikSettingsPage::class,
                // Presensi Pages (exclude Presensi Dashboard — rute konflik dengan /admin)
                \App\Filament\Presensi\Pages\LaporanPresensi::class,
                \App\Filament\Presensi\Pages\CetakLaporanPresensi::class,
                \App\Filament\Presensi\Pages\InputPresensiManual::class,
                \App\Filament\Presensi\Pages\ManajemenKartuPresensi::class,
                \App\Filament\Presensi\Pages\ManajemenNotifikasiWaPage::class,
                \App\Filament\Presensi\Pages\PengaturanPresensiPage::class,
                \App\Filament\Presensi\Pages\RekapAbsensiKelas::class,
                \App\Filament\Presensi\Pages\RekapAbsensiSekolah::class,
                // Perpustakaan Pages (exclude Perpustakaan Dashboard — rute konflik dengan /admin)
                \App\Filament\Perpustakaan\Pages\AnggotaResource::class,
                \App\Filament\Perpustakaan\Pages\ImportSlims::class,
                \App\Filament\Perpustakaan\Pages\LaporanSirkulasi::class,
                \App\Filament\Perpustakaan\Pages\PengaturanPerpustakaan::class,
                \App\Filament\Perpustakaan\Pages\ReservasiSegeraHadir::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->discoverWidgets(in: app_path('Filament/Akademik/Widgets'), for: 'App\Filament\Akademik\Widgets')
            ->discoverWidgets(in: app_path('Filament/Perpustakaan/Widgets'), for: 'App\Filament\Perpustakaan\Widgets')
            ->widgets([])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
