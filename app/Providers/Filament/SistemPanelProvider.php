<?php

namespace App\Providers\Filament;

use App\Filament\Resources\ManajemenAksesPortalResource;
use App\Filament\Resources\UserResource;
use App\Filament\Sistem\Pages\SistemDashboard;
use App\Filament\Sistem\Pages\SistemKoneksiWaPage;
use App\Filament\Sistem\Pages\SistemMaintenancePortalPage;
use App\Filament\Sistem\Pages\SistemPengaturanSpikapPage;
use App\Filament\Sistem\Pages\SistemPesanMassalPage;
use App\Filament\Sistem\Pages\SistemThemeSettingsPage;
use App\Filament\Sistem\Pages\SistemWaGuruWaliPage;
use App\Filament\Sistem\Pages\SistemWaJurnalMengajarPage;
use App\Filament\Sistem\Pages\SistemWaPelayananPublikPage;
use App\Filament\Sistem\Pages\SistemWaPresensiPage;
use App\Filament\Sistem\Pages\SistemWaSpikapPage;
use App\Filament\Sistem\Widgets\SistemStatsWidget;
use App\Http\Middleware\EnsureCanAccessSistem;
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

class SistemPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('sistem')
            ->path('portal-sistem')
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
                    return ($name ?: 'Sekolah') . ' — Portal Sistem';
                } catch (\Exception $e) {
                    return 'Portal Sistem';
                }
            })
            ->brandLogo(function () {
                try {
                    $logo = PengaturanSekolah::current()?->school_logo_path;
                    $name = PengaturanSekolah::current()?->school_name ?? 'Portal Sistem';

                    if ($logo) {
                        return new HtmlString('
                            <div class="flex items-center gap-2.5">
                                <img src="' . asset('storage/' . $logo) . '" alt="Logo" class="h-8 w-auto object-contain" />
                                <div class="flex flex-col leading-tight">
                                    <span class="font-bold text-sm tracking-tight text-slate-900 dark:text-white">' . e($name) . '</span>
                                    <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Portal Sistem</span>
                                </div>
                            </div>
                        ');
                    }

                    return new HtmlString('
                        <div class="flex flex-col leading-tight">
                            <span class="font-bold text-base tracking-tight text-slate-900 dark:text-white">' . e($name) . '</span>
                            <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Portal Sistem</span>
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
                        'primary' => $hex ? Color::hex($hex) : Color::Purple,
                    ];
                } catch (\Exception $e) {
                    return [
                        'primary' => Color::Purple,
                    ];
                }
            })
            ->navigationGroups([
                'Manajemen Akses',
                'Gateway & Pengaturan Modul',
                'Notifikasi WhatsApp',
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
                UserResource::class,
                ManajemenAksesPortalResource::class,
            ])
            ->pages([
                SistemDashboard::class,
                SistemKoneksiWaPage::class,
                SistemPengaturanSpikapPage::class,
                SistemPesanMassalPage::class,
                SistemMaintenancePortalPage::class,
                SistemThemeSettingsPage::class,
                SistemWaPresensiPage::class,
                SistemWaJurnalMengajarPage::class,
                SistemWaSpikapPage::class,
                SistemWaGuruWaliPage::class,
                SistemWaPelayananPublikPage::class,
            ])
            ->widgets([
                SistemStatsWidget::class,
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
                EnsureCanAccessSistem::class,
            ])
            ->renderHook(
                PanelsRenderHook::USER_MENU_BEFORE,
                fn (): string => view('filament.hooks.topbar-quick-links')->render()
            );
    }
}

