<?php

namespace App\Filament\Sistem\Pages;

use App\Filament\Pages\ThemeSettingsPage as BaseThemeSettingsPage;
use App\Models\PengaturanSekolah;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Support\Exceptions\Halt;
use Illuminate\Support\Facades\Cache;
use UnitEnum;

class SistemThemeSettingsPage extends BaseThemeSettingsPage
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-swatch';

    protected static ?string $navigationLabel = 'Pengaturan Tema';

    protected static ?string $title = 'Pengaturan Tema Sistem & Website';

    protected static ?string $slug = 'theme-settings-page';

    protected static string|UnitEnum|null $navigationGroup = 'Gateway & Pengaturan Modul';

    protected static ?int $navigationSort = 5;

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user !== null && ($user->isSuperAdmin() || $user->hasRole('admin_portal_sistem'));
    }

    public function getSubheading(): ?string
    {
        return 'Atur preset warna utama dan palet warna kustom yang diterapkan secara dinamis di seluruh portal ERP dan halaman publik sekolah.';
    }

    public function save(): void
    {
        try {
            $data = $this->form->getState();

            $themeData = [
                'theme_primary' => $data['theme_primary'] ?? null,
                'theme_secondary' => $data['theme_secondary'] ?? null,
                'theme_accent' => $data['theme_accent'] ?? null,
                'theme_warning' => $data['theme_warning'] ?? null,
                'theme_danger' => $data['theme_danger'] ?? null,
                'theme_info' => $data['theme_info'] ?? null,
            ];

            $settings = PengaturanSekolah::first();

            if ($settings) {
                $settings->update($themeData);
            } else {
                PengaturanSekolah::create(array_merge([
                    'school_name' => 'SMA Negeri 1 Contoh',
                    'school_address' => 'Jl. Pendidikan No. 1',
                    'principal_name' => 'Drs. Kepala Sekolah, M.Pd',
                ], $themeData));
            }

            $this->detectActivePreset($themeData['theme_primary']);

            Cache::forget('public_pengaturan_sekolah');

            Notification::make()
                ->title('Pengaturan tema berhasil disimpan')
                ->success()
                ->send();
        } catch (Halt $exception) {
            return;
        }
    }
}

