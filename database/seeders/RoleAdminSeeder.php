<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;

class RoleAdminSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        DB::beginTransaction();
        try {
            Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
            
            DB::commit();
            $this->command->info("✅ Berhasil generate role: super_admin");
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error("❌ Gagal: " . $e->getMessage());
        }
    }
}
