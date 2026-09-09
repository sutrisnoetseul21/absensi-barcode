<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;
use App\Models\SpikapNotifSetting;

class SpikapRoleSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        DB::beginTransaction();
        try {
            // ─── 1. Buat semua permissions SPIKAP ────────────────────────────
            $permissions = [
                'spikap.view_any',       // Lihat daftar laporan (scope per role)
                'spikap.view',           // Lihat detail laporan
                'spikap.update_status',  // Update status & tambah catatan tindak lanjut
                'spikap.view_analytics', // Akses dashboard analitik pola perundungan
                'spikap.manage_settings',// Kelola konfigurasi penerima notifikasi
            ];
            // CATATAN: spikap.view_identity dihapus — privasi diatur lewat routing
            // (penerima resmi selalu berhak lihat identitas siswa penuh)

            foreach ($permissions as $perm) {
                Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
            }

            $this->command->info('✅ Permissions SPIKAP berhasil dibuat (5 total)');

            // ─── 2. Role: spikap_guru_bk ─────────────────────────────────────
            // Guru BK: lihat & tangani laporan biasa yang ditujukan ke BK + analitik
            $roleBk = Role::firstOrCreate(['name' => 'spikap_guru_bk', 'guard_name' => 'web']);
            $roleBk->syncPermissions([
                'spikap.view_any',
                'spikap.view',
                'spikap.update_status',
                'spikap.view_analytics',
            ]);
            $this->command->info('✅ Role spikap_guru_bk berhasil dibuat (4 perm)');

            // ─── 3. Role: spikap_wali_kelas ──────────────────────────────────
            // Wali Kelas: lihat & tangani laporan biasa ke WK + laporan darurat dari siswa di kelasnya
            $roleWk = Role::firstOrCreate(['name' => 'spikap_wali_kelas', 'guard_name' => 'web']);
            $roleWk->syncPermissions([
                'spikap.view_any',
                'spikap.view',
                'spikap.update_status',
            ]);
            $this->command->info('✅ Role spikap_wali_kelas berhasil dibuat (3 perm)');

            // ─── 4. Role: spikap_kepala_sekolah ──────────────────────────────
            // Kepala Sekolah: baca semua laporan darurat + lihat analitik
            $roleKs = Role::firstOrCreate(['name' => 'spikap_kepala_sekolah', 'guard_name' => 'web']);
            $roleKs->syncPermissions([
                'spikap.view_any',
                'spikap.view',
                'spikap.update_status',
                'spikap.view_analytics',
            ]);
            $this->command->info('✅ Role spikap_kepala_sekolah berhasil dibuat (4 perm)');

            // ─── 5. Role: spikap_admin ───────────────────────────────────────
            // Admin: full access semua laporan + konfigurasi
            $roleAdmin = Role::firstOrCreate(['name' => 'spikap_admin', 'guard_name' => 'web']);
            $roleAdmin->syncPermissions($permissions); // semua 5 permission
            $this->command->info('✅ Role spikap_admin berhasil dibuat (5 perm)');

            // Berikan izin spikap juga ke role wali_kelas reguler
            $roleWkReguler = Role::where('name', 'wali_kelas')->first();
            if ($roleWkReguler) {
                $roleWkReguler->givePermissionTo(['spikap.view_any', 'spikap.view', 'spikap.update_status']);
                $this->command->info('✅ Role wali_kelas reguler diberikan izin SPIKAP');
            }

            // ─── 6. Buat default konfigurasi notifikasi (singleton id=1) ─────
            SpikapNotifSetting::instance();
            $this->command->info('✅ Default spikap_notif_settings (id=1) berhasil dibuat');

            // ─── 7. Otomatis sinkronisasi role ke Guru BK & Kepala Sekolah aktif ───
            $bkTeachers = \App\Models\Guru::whereHas('jabatans', function ($q) {
                $q->where('nama_jabatan', 'like', '%Guru BK%')
                  ->orWhere('nama_jabatan', 'like', '%BK%');
            })->with('user')->get();

            foreach ($bkTeachers as $guru) {
                if ($guru->user) {
                    $guru->user->assignRole('spikap_guru_bk');
                    $this->command->info("✅ User {$guru->user->email} ({$guru->name}) diberikan role spikap_guru_bk");
                }
            }

            $ksTeachers = \App\Models\Guru::whereHas('jabatans', function ($q) {
                $q->where('nama_jabatan', 'like', '%Kepala Sekolah%');
            })->with('user')->get();

            foreach ($ksTeachers as $guru) {
                if ($guru->user) {
                    $guru->user->assignRole('spikap_kepala_sekolah');
                    $this->command->info("✅ User {$guru->user->email} ({$guru->name}) diberikan role spikap_kepala_sekolah");
                }
            }

            DB::commit();
            $this->command->info('🎉 SpikapRoleSeeder selesai.');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('❌ Gagal: ' . $e->getMessage());
            throw $e;
        }
    }
}
