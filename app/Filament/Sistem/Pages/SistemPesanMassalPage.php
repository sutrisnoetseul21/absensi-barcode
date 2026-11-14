<?php

namespace App\Filament\Sistem\Pages;

use App\Filament\Presensi\Pages\KirimPesanMassalPage;

class SistemPesanMassalPage extends KirimPesanMassalPage
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-megaphone';

    protected static ?string $navigationLabel = 'Pesan Massal Android';

    protected static ?string $slug = 'pesan-massal-android';

    protected static ?string $title = 'Broadcast Notifikasi Aplikasi Android';

    protected ?string $subheading = 'Kirim pengumuman push notification (FCM) secara terpusat ke perangkat siswa, guru, atau kelas tertentu.';

    protected static string|\UnitEnum|null $navigationGroup = 'Gateway & Pengaturan Modul';

    protected static ?int $navigationSort = 3;

    public static function canAccess(): bool
    {
        $user = auth()->user();
        if (!$user) {
            return false;
        }

        return $user->isSuperAdmin()
            || $user->hasRole(['super_admin', 'admin_portal_sistem', 'admin_presensi_editor']);
    }
}
