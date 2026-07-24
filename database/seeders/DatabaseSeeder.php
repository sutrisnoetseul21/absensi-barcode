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
        ]);

        // Akun Admin Filament
        User::firstOrCreate(
            ['email' => 'admin@sekolah.com'],
            [
                'name' => 'Admin Utama',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
            ]
        );

        // Akun Wali Kelas
        \App\Models\Guru::firstOrCreate(
            ['username' => 'guru123'],
            [
                'name' => 'Bapak Budi',
                'nip' => '198001012005011001',
                'password' => 'password',
            ]
        );

        // Akun Siswa
        $siswa = \App\Models\Siswa::firstOrCreate(
            ['username' => '1234567890'],
            [
                'nisn' => '1234567890',
                'name' => 'Andi Siswa',
                'password' => 'password',
            ]
        );

        if ($siswa->wasRecentlyCreated) {
            $siswa->presensiProfile()->create([
                'barcode_code' => '1234567890',
                'barcode_active' => true,
            ]);
        }
    }
}
