<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            KelasSeeder::class,
            SuperAdminSeeder::class,
            KategoriBukuSeeder::class,
            KlasifikasiDdcSeeder::class,
            RoleAdminSeeder::class,
            RoleAkademikSeeder::class,
            RolePresensiSeeder::class,
            RolePerpustakaanSeeder::class,
        ]);

        // Akun Admin Filament
        User::firstOrCreate(
            ['email' => 'admin@sekolah.com'],
            [
                'name' => 'Admin Utama',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
            ]
        );

    }
}
