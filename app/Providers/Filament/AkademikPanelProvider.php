<?php

namespace App\Providers\Filament;

use App\Filament\Akademik\Pages\AkademikDashboard;
use App\Filament\Akademik\Pages\AnalisaPresensiKbmSiswaPage;
use App\Filament\Akademik\Pages\MonitoringGuruKelasJurnalPage;
use App\Filament\Akademik\Resources\Enrollment\EnrollmentResource;
use App\Filament\Akademik\Resources\Guru\GuruResource;
use App\Filament\Akademik\Resources\GuruWali\KelompokGuruWaliResource;
use App\Filament\Akademik\Resources\JurnalPembelajaranResource;
use App\Filament\Akademik\Resources\Kelas\KelasResource;
use App\Filament\Akademik\Resources\PindahKelasResource;
use App\Filament\Akademik\Resources\Siswa\SiswaResource;
use App\Filament\Akademik\Resources\SiswaLulusResource;
use App\Filament\Akademik\Resources\SiswaMutasiResource;
use App\Filament\Akademik\Widgets\AkademikStatsWidget;
use App\Http\Middleware\EnsureCanAccessAkademik;
use App\Http\Middleware\FilamentAuthenticate;
use App\Models\PengaturanSekolah;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Navigation\NavigationItem;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\HtmlString;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AkademikPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('akademik')
            ->path('portal-akademik')
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->favicon(function () {
                try {
                    $logo = PengaturanSekolah::current()?->school_logo_path;
                    return $logo ? asset('storage/' . $logo) : asset('favicon.ico');
                } catch (\Exception $e) {
                    return asset('favicon.ico');
                }
            })
            ->brandName(function () {
                try {
                    $name = PengaturanSekolah::current()?->school_name;
                    return ($name ?: 'Sekolah') . ' — Portal Akademik';
                } catch (\Exception $e) {
                    return 'Portal Akademik';
                }
            })
            ->brandLogo(function () {
                try {
                    $logo = PengaturanSekolah::current()?->school_logo_path;
                    $name = PengaturanSekolah::current()?->school_name ?? 'Portal Akademik';

                    if ($logo) {
                        return new HtmlString('
                            <div class="flex items-center gap-2.5">
                                <img src="' . asset('storage/' . $logo) . '" alt="Logo" class="h-8 w-auto object-contain" />
                                <div class="flex flex-col leading-tight">
                                    <span class="font-bold text-sm tracking-tight text-slate-900 dark:text-white">' . e($name) . '</span>
                                    <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Portal Akademik</span>
                                </div>
                            </div>
                        ');
                    }

                    return new HtmlString('
                        <div class="flex flex-col leading-tight">
                            <span class="font-bold text-base tracking-tight text-slate-900 dark:text-white">' . e($name) . '</span>
                            <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Portal Akademik</span>
                        </div>
                    ');
                } catch (\Exception $e) {
                    return null;
                }
            })
            ->brandLogoHeight('2.25rem')
            ->colors(function () {
                try {
                    $hex = PengaturanSekolah::current()?->theme_primary;
                    return [
                        'primary' => $hex ? Color::hex($hex) : Color::Amber,
                    ];
                } catch (\Exception $e) {
                    return [
                        'primary' => Color::Amber,
                    ];
                }
            })
            ->navigationGroups([
                'Data Master',
                'Akademik',
                'Jurnal Mengajar',
                'Guru Wali',
                'Akses Portal & Web',
            ])
            ->navigationItems([
                NavigationItem::make('Web Utama')
                    ->url(fn (): string => url('/'))
                    ->icon('heroicon-o-globe-alt')
                    ->group('Akses Portal & Web')
                    ->sort(900)
                    ->openUrlInNewTab(),
                NavigationItem::make('Pilih Portal ERP')
                    ->url(fn (): string => url('/pilih-portal'))
                    ->icon('heroicon-o-squares-2x2')
                    ->group('Akses Portal & Web')
                    ->sort(901),
                NavigationItem::make('Portal Setup')
                    ->url(fn (): string => url('/portal-setup'))
                    ->icon('heroicon-o-cog-6-tooth')
                    ->group('Akses Portal & Web')
                    ->sort(902)
                    ->visible(fn (): bool => auth()->user()?->isSuperAdmin() || auth()->user()?->hasRole('admin_portal_setup')),
            ])
            ->userMenuItems([
                MenuItem::make()
                    ->label('Web Utama')
                    ->url(fn (): string => url('/'))
                    ->icon('heroicon-o-globe-alt')
                    ->openUrlInNewTab(),
                MenuItem::make()
                    ->label('Pilih Portal ERP')
                    ->url(fn (): string => url('/pilih-portal'))
                    ->icon('heroicon-o-squares-2x2'),
            ])
            ->profile()
            ->resources([
                GuruResource::class,
                SiswaResource::class,
                KelasResource::class,
                EnrollmentResource::class,
                PindahKelasResource::class,
                SiswaLulusResource::class,
                SiswaMutasiResource::class,
                KelompokGuruWaliResource::class,
                JurnalPembelajaranResource::class,
            ])
            ->pages([
                AkademikDashboard::class,
                MonitoringGuruKelasJurnalPage::class,
                AnalisaPresensiKbmSiswaPage::class,
            ])
            ->widgets([
                AkademikStatsWidget::class,
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
                FilamentAuthenticate::class,
                EnsureCanAccessAkademik::class,
            ])
            ->renderHook(
                PanelsRenderHook::USER_MENU_BEFORE,
                fn (): string => view('filament.hooks.topbar-quick-links')->render()
            );
    }
}

