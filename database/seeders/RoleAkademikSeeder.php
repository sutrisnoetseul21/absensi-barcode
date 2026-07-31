<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class RoleAkademikSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $resources = ['Guru', 'Siswa', 'Kelas', 'TahunAjaran', 'Pengumuman', 'HariLibur', 'RiwayatPindahKelas', 'User', 'Jabatan', 'MataPelajaran'];
        $customPages = ['View:Dashboard'];

        $levels = [
            '_view'   => ['ViewAny', 'View'],
            '_editor' => ['ViewAny', 'View', 'Create', 'Update', 'Reorder'],
            '_admin'  => ['ViewAny', 'View', 'Create', 'Update', 'Reorder', 'Delete', 'DeleteAny', 'Restore', 'RestoreAny', 'ForceDelete', 'ForceDeleteAny', 'Replicate'],
        ];

        DB::beginTransaction();
        try {
            foreach ($levels as $levelSuffix => $actions) {
                $roleName = 'admin_akademik' . $levelSuffix;
                $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
                $permissionsToSync = [];

                foreach ($resources as $resource) {
                    foreach ($actions as $action) {
                        $permName = "{$action}:{$resource}";
                        Permission::firstOrCreate(['name' => $permName, 'guard_name' => 'web']);
                        $permissionsToSync[] = $permName;
                    }
                }

                foreach ($customPages as $customPerm) {
                    Permission::firstOrCreate(['name' => $customPerm, 'guard_name' => 'web']);
                    $permissionsToSync[] = $customPerm;
                }

                $role->syncPermissions($permissionsToSync);
                $this->command->info("✅ Berhasil generate role: {$roleName}");
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error("❌ Gagal: " . $e->getMessage());
        }
    }
}
