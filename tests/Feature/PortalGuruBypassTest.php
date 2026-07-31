<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\KelasAjaran;
use App\Models\EnrollmentSiswa;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Livewire\Livewire;
use App\Livewire\WaliKelasDashboard;
use App\Livewire\WaliKelasStudentDetail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class PortalGuruBypassTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Setup Roles and Permissions
        Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'admin_presensi', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'wali_kelas', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'portal_guru:akses_semua_kelas', 'guard_name' => 'web']);

        // Setup Academic Year & Classes
        $this->ay = TahunAjaran::create(['name' => '2025/2026', 'start_year' => 2025, 'end_year' => 2026, 'status' => 'aktif']);
        $this->kelasWali = Kelas::create(['name' => 'Kelas XA']);
        $this->kelasBukanWali = Kelas::create(['name' => 'Kelas XB']);

        // Guru B (Wali Kelas Biasa, tanpa bypass)
        $this->guruB = User::create(['name' => 'Guru B Test', 'email' => 'gurub@test.com', 'password' => bcrypt('password')]);
        $this->guruB->assignRole('wali_kelas');
        $this->profileB = Guru::create(['user_id' => $this->guruB->id, 'name' => 'Profile Guru B', 'nip' => '111', 'nuptk' => '111']);
        KelasAjaran::create(['class_id' => $this->kelasWali->id, 'academic_year_id' => $this->ay->id, 'teacher_id' => $this->profileB->id]);

        // Guru A (Dengan Bypass)
        $this->guruA = User::create(['name' => 'Guru A Test', 'email' => 'gurua@test.com', 'password' => bcrypt('password')]);
        $this->guruA->assignRole('wali_kelas');
        $this->guruA->givePermissionTo('portal_guru:akses_semua_kelas');
        $this->profileA = Guru::create(['user_id' => $this->guruA->id, 'name' => 'Profile Guru A', 'nip' => '222', 'nuptk' => '222']);
        // Guru A is also assigned to Kelas XA, but wants to see XB
        KelasAjaran::create(['class_id' => $this->kelasWali->id, 'academic_year_id' => $this->ay->id, 'teacher_id' => $this->profileA->id]);

        // Guru C (Fail Closed, null teacher profile)
        $this->guruC = User::create(['name' => 'Guru C Test', 'email' => 'guruc@test.com', 'password' => bcrypt('password')]);
        $this->guruC->assignRole('wali_kelas');

        // Siswa di Kelas XB (bukan kelas wali)
        $this->siswaTargetUser = User::create(['name' => 'Siswa Test', 'email' => 'siswa@test.com', 'password' => bcrypt('password')]);
        $this->siswaTarget = Siswa::create(['user_id' => $this->siswaTargetUser->id, 'name' => 'Siswa Test', 'nisn' => '12345']);
        EnrollmentSiswa::create([
            'student_id' => $this->siswaTarget->id,
            'class_id' => $this->kelasBukanWali->id,
            'academic_year_id' => $this->ay->id,
            'status' => 'aktif'
        ]);
        
        // Buat kelas ajaran kosong untuk Kelas XB agar aktif di tahun ajaran ini (diperlukan filter bypass)
        KelasAjaran::create(['class_id' => $this->kelasBukanWali->id, 'academic_year_id' => $this->ay->id, 'teacher_id' => $this->profileB->id]);
    }

    public function test_guru_b_cannot_bypass()
    {
        echo "\n\n--- 2. Pengujian Guru B (Wali Kelas Biasa) ---\n";
        $this->actingAs($this->guruB);
        
        $dashboard = Livewire::test(WaliKelasDashboard::class);
        $classes = $dashboard->get('classes');
        
        echo "=> Menampilkan kelas: " . $classes->pluck('name')->join(', ') . "\n";
        $this->assertCount(1, $classes);
        $this->assertEquals('Kelas XA', $classes->first()->name);

        echo "=> Akses profil siswa kelas XB: ";
        $detail = Livewire::test(WaliKelasStudentDetail::class, ['id' => $this->siswaTarget->id]);
        $detail->assertForbidden(); // Should throw 403
        echo "SUCCESS (Akses Ditolak 403)\n";
    }

    public function test_guru_a_can_bypass()
    {
        echo "\n\n--- 3. Pengujian Guru A (Dengan Bypass Permission) ---\n";
        $this->actingAs($this->guruA);
        
        $dashboard = Livewire::test(WaliKelasDashboard::class);
        $classes = $dashboard->get('classes');
        
        echo "=> Menampilkan kelas: " . $classes->pluck('name')->join(', ') . "\n";
        $this->assertCount(2, $classes); // Should see both XA and XB

        echo "=> Akses profil siswa kelas XB: ";
        $detail = Livewire::test(WaliKelasStudentDetail::class, ['id' => $this->siswaTarget->id]);
        $detail->assertStatus(200);
        echo "SUCCESS (Halaman Terbuka 200)\n";
    }

    public function test_guru_c_fail_closed()
    {
        echo "\n\n--- 4. Pengujian Guru C (Fail-Closed: Tidak Punya Profil) ---\n";
        $this->actingAs($this->guruC);
        
        echo "=> Akses Dashboard: ";
        $dashboard = Livewire::test(WaliKelasDashboard::class);
        $dashboard->assertForbidden();
        echo "SUCCESS (Akses Ditolak 403)\n";

        echo "=> Akses Detail Siswa: ";
        $detail = Livewire::test(WaliKelasStudentDetail::class, ['id' => $this->siswaTarget->id]);
        $detail->assertForbidden();
        echo "SUCCESS (Akses Ditolak 403)\n";
    }
}
