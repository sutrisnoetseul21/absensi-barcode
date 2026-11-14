<?php

namespace App\Filament\Sistem\Widgets;

use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Spatie\Permission\Models\Role;

class SistemStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
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

        return [
            Stat::make('Total Akun Staf / Admin', $totalPenggunaNonSiswa)
                ->description('Akun non-siswa terdaftar')
                ->icon('heroicon-o-users')
                ->color('primary'),
            Stat::make('Super Admin', $totalSuperAdmin)
                ->description('Akses penuh seluruh sistem')
                ->icon('heroicon-o-shield-check')
                ->color('danger'),
            Stat::make('Admin Modul / Portal', $totalAdminModul)
                ->description('Pengelola portal & modul khusus')
                ->icon('heroicon-o-key')
                ->color('success'),
            Stat::make('Role Terdaftar', $totalRoleSistem)
                ->description('Total role Spatie aktif')
                ->icon('heroicon-o-lock-closed')
                ->color('info'),
        ];
    }
}

