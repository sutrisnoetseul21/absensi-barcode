<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class SuperAdminSeeder extends Seeder
{
    /**
     * Seed akun Super Admin baru terpisah.
     *
     * Akun ini berbeda dari admin biasa:
     * - is_super_admin = true → bisa akses TahunAjaranResource, SchoolSettingsPage,
     *   dan fitur assign wali kelas
     *
     * Ganti email/password sesuai kebutuhan sekolah.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $superAdmin = User::updateOrCreate(
            ['email' => 'superadmin@haflaitsolution.id'],
            [
                'name'           => 'Super Admin',
                'password'       => Hash::make('SuperAdmin123!'),
                'is_super_admin' => true,
            ]
        );

        $superAdminRole = Role::firstOrCreate([
            'name'       => 'super_admin',
            'guard_name' => 'web',
        ]);

        if (!$superAdmin->hasRole('super_admin')) {
            $superAdmin->assignRole($superAdminRole);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->command?->info('Super Admin seeded: superadmin@haflaitsolution.id / SuperAdmin123!');
    }
}
