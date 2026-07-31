<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class GuruAksesSemuaKelasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Permission::firstOrCreate([
            'name' => 'portal_guru:akses_semua_kelas',
            'guard_name' => 'web',
        ]);
        
        $this->command->info('✅ Permission portal_guru:akses_semua_kelas berhasil dibuat!');
    }
}
