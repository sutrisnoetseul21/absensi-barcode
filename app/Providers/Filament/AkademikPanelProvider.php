<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use App\Filament\Akademik\Pages\AkademikDashboard;
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
use App\Filament\Akademik\Widgets\AkademikStatsWidget;
use App\Filament\Widgets\QuickLinksWidget;

class AkademikPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin-akademik')
            ->path('admin-akademik')
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
                'Laporan',
                'Konten',
                'Pengaturan Sistem',
            ])
            ->discoverResources(in: app_path('Filament/Akademik/Resources'), for: 'App\Filament\Akademik\Resources')
            ->discoverPages(in: app_path('Filament/Akademik/Pages'), for: 'App\Filament\Akademik\Pages')
            ->pages([
                AkademikDashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Akademik/Widgets'), for: 'App\Filament\Akademik\Widgets')
            ->widgets([
                \App\Filament\Widgets\PortalWidget::class,
                QuickLinksWidget::class,
                AkademikStatsWidget::class,
                AccountWidget::class,
            ])
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
