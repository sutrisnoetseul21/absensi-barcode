<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class CoreSystemSeeder extends Seeder
{
    /**
     * Seeder inti sistem (Role, Permission, & Pengaturan Default Modul).
     * Seluruh proses bersifat idempoten (aman dijalankan berulang kali).
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->call([
            RoleAdminSeeder::class,
            RoleAkademikSeeder::class,
            RolePresensiSeeder::class,
            RolePerpustakaanSeeder::class,
            SpikapRoleSeeder::class,
            GuruAksesSemuaKelasSeeder::class,
            KategoriBukuSeeder::class,
            KlasifikasiDdcSeeder::class,
            PresensiDailyReportSettingSeeder::class,
            PresensiNotificationSettingSeeder::class,
            PresensiSchoolSummarySettingSeeder::class,
        ]);

        // Pastikan seluruh role akses portal juga tersedia
        $portalRoles = [
            'admin_portal_setup',
            'admin_portal_akademik',
            'admin_portal_web',
            'admin_portal_presensi',
            'petugas_presensi',
            'petugas_perpustakaan',
        ];

        foreach ($portalRoles as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}

