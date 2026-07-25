<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class PanelRolesSeeder extends Seeder
{
    /**
     * Membuat role beserta struktur hirarki (View, Editor, Admin)
     * dan otomatis mengaitkan Permission bawaan Filament Shield.
     * Jalankan: php artisan db:seed --class=PanelRolesSeeder
     */
    public function run(): void
    {
        // Pastikan tabel cache bersih agar tidak terjadi issue Spatie caching
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Definisikan Resources per Divisi
        $akademikResources = ['Guru', 'Siswa', 'Kelas', 'TahunAjaran', 'Pengumuman', 'HariLibur', 'RiwayatPindahKelas', 'User'];
        $presensiResources = ['HariLibur']; // + Custom pages khusus presensi

        // 2. Definisikan Action per Level
        $viewActions = ['ViewAny', 'View'];
        $editorActions = ['Create', 'Update', 'Reorder'];
        $adminActions = ['Delete', 'DeleteAny', 'Restore', 'RestoreAny', 'ForceDelete', 'ForceDeleteAny', 'Replicate'];

        // 3. Mapping Divisi dan Level Role
        $divisions = [
            'admin_akademik' => $akademikResources,
            'admin_presensi' => $presensiResources,
        ];

        $levels = [
            '_view'   => $viewActions,
            '_editor' => array_merge($viewActions, $editorActions),
            '_admin'  => array_merge($viewActions, $editorActions, $adminActions),
        ];

        // Custom Permissions Khusus Panel (Dashboard, Laporan, dll)
        $customPermissions = [
            'admin_akademik' => ['View:Dashboard'],
            'admin_presensi' => [
                'View:Dashboard',
                'View:AdminAttendanceChart',
                'View:AdminStatsOverview',
                'View:CetakLaporanPresensi',
                'View:InputPresensiManual',
                'View:LaporanPresensi',
                'View:ManajemenKartuPresensi',
                'View:PengaturanPresensiPage',
                'View:ProblematicStudentsTable',
                'View:RekapAbsensiKelas',
                'View:RekapAbsensiSekolah',
            ]
        ];

        DB::beginTransaction();
        try {
            // A. Role Super Admin
            $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
            // Super admin otomatis dihandle bypass di User model, tapi kita bisa attach semuanya jika mau.
            
            // B. Generate Role & Permissions untuk setiap Divisi & Level
            foreach ($divisions as $divName => $resources) {
                foreach ($levels as $levelSuffix => $actions) {
                    $roleName = $divName . $levelSuffix; // e.g. admin_master_view
                    
                    $role = Role::firstOrCreate([
                        'name' => $roleName,
                        'guard_name' => 'web'
                    ]);

                    $permissionsToSync = [];

                    // 1. Kumpulkan permission dari Resources Spatie
                    foreach ($resources as $resource) {
                        foreach ($actions as $action) {
                            $permName = "{$action}:{$resource}";
                            
                            // Pastikan permissionnya ada di database sebelum di-sync
                            $perm = Permission::firstOrCreate(['name' => $permName, 'guard_name' => 'web']);
                            $permissionsToSync[] = $perm->name;
                        }
                    }

                    // 2. Tambahkan Custom Pages (Khusus divisi itu)
                    if (isset($customPermissions[$divName])) {
                        foreach ($customPermissions[$divName] as $customPerm) {
                            Permission::firstOrCreate(['name' => $customPerm, 'guard_name' => 'web']);
                            $permissionsToSync[] = $customPerm;
                        }
                    }

                    // 3. Attach permissions ke role tersebut
                    $role->syncPermissions($permissionsToSync);
                    $this->command->info("✅ Berhasil generate role: {$roleName} dengan " . count($permissionsToSync) . " permissions.");
                }
            }
            
            DB::commit();
            $this->command->info("🎉 Selesai! Semua Role beserta Permission telah berhasil di-generate.");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error("❌ Gagal: " . $e->getMessage());
        }
    }
}
