<?php

namespace Tests\Feature\Cbt;

use App\Filament\Akademik\Resources\UjianAkademik\Pages\CreateUjianAkademik;
use App\Filament\Akademik\Resources\UjianAkademik\Pages\ListUjianAkademik;
use App\Filament\Akademik\Resources\UjianAkademik\Pages\ViewUjianAkademik;
use App\Models\EnrollmentSiswa;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\PengaturanSekolah;
use App\Models\Siswa;
use App\Models\StudentCbtProfile;
use App\Models\TahunAjaran;
use App\Models\UjianAkademik;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UjianAkademikResourceTest extends TestCase
{
    use DatabaseTransactions;

    protected User $adminUser;
    protected TahunAjaran $academicYear;
    protected Kelas $kelas7A;
    protected MataPelajaran $mapelMtk;
    protected Siswa $siswa1;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default'                    => 'mysql',
            'database.connections.mysql.database' => 'absensi_barcode',
        ]);

        Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);

        $this->adminUser = User::firstOrCreate(
            ['email' => 'admin_cbt_test@testing.sch.id'],
            [
                'name'     => 'Admin CBT Test',
                'password' => bcrypt('secret123'),
            ]
        );
        $this->adminUser->assignRole('super_admin');

        $this->academicYear = TahunAjaran::firstOrCreate(
            ['name' => '2025/2026'],
            [
                'start_year' => 2025,
                'end_year'   => 2026,
                'status'     => 'aktif',
            ]
        );

        $settings = PengaturanSekolah::current();
        if ($settings) {
            $settings->update([
                'academic_year_id_active' => $this->academicYear->id,
                'active_semester'         => 'ganjil',
            ]);
        }

        $this->kelas7A = Kelas::firstOrCreate(
            ['name' => '7A'],
            ['grade_level' => 7]
        );

        $this->mapelMtk = MataPelajaran::firstOrCreate(
            ['nama_mapel' => 'Matematika'],
            [
                'kode_mapel'  => 'MTK',
                'kategori'    => 'Umum',
                'kkm_default' => 78.00,
            ]
        );

        $this->siswa1 = Siswa::firstOrCreate(
            ['nisn' => '0099112233'],
            [
                'name'   => 'Ahmad Test CBT',
                'status' => 'aktif',
            ]
        );

        EnrollmentSiswa::firstOrCreate(
            [
                'student_id'       => $this->siswa1->id,
                'academic_year_id' => $this->academicYear->id,
            ],
            [
                'class_id' => $this->kelas7A->id,
                'status'   => 'aktif',
            ]
        );

        StudentCbtProfile::updateOrCreate(
            ['student_id' => $this->siswa1->id],
            [
                'cbt_password' => 'cbtpass123',
                'cbt_sesi'     => 'Sesi 1',
                'cbt_status'   => 'active',
            ]
        );
    }

    public function test_can_render_ujian_akademik_list_page(): void
    {
        $this->actingAs($this->adminUser);

        $response = $this->get(ListUjianAkademik::getUrl());
        $response->assertSuccessful();
    }

    public function test_can_render_create_ujian_akademik_page(): void
    {
        $this->actingAs($this->adminUser);

        $response = $this->get(CreateUjianAkademik::getUrl());
        $response->assertSuccessful();
    }

    public function test_can_create_and_view_ujian_akademik(): void
    {
        $this->actingAs($this->adminUser);

        $ujian = UjianAkademik::create([
            'nama_ujian'        => 'ASTS Ganjil Matematika 7 Test',
            'jenis_ujian'       => 'sts',
            'mata_pelajaran_id' => $this->mapelMtk->id,
            'academic_year_id'  => $this->academicYear->id,
            'semester'          => 'ganjil',
            'kkm'               => 78.00,
            'durasi_menit'      => 90,
            'total_soal'        => 40,
            'tanggal_mulai'     => now(),
            'tanggal_selesai'   => now()->addHours(2),
            'status'            => 'aktif',
            'cbt_event_nama'    => 'ASTS Ganjil - MTK',
        ]);

        $ujian->classes()->attach($this->kelas7A->id);

        $this->assertDatabaseHas('ujian_akademiks', [
            'id'         => $ujian->id,
            'nama_ujian' => 'ASTS Ganjil Matematika 7 Test',
        ]);

        $this->assertDatabaseHas('ujian_akademik_classes', [
            'ujian_akademik_id' => $ujian->id,
            'class_id'          => $this->kelas7A->id,
        ]);

        $response = $this->get(ViewUjianAkademik::getUrl(['record' => $ujian]));
        $response->assertSuccessful();
        $response->assertSee('ASTS Ganjil Matematika 7 Test');
    }

    public function test_can_render_cetak_kartu_ujian_cbt(): void
    {
        $this->actingAs($this->adminUser);

        $ujian = UjianAkademik::create([
            'nama_ujian'        => 'ASTS Ganjil Matematika Cetak',
            'jenis_ujian'       => 'sts',
            'mata_pelajaran_id' => $this->mapelMtk->id,
            'academic_year_id'  => $this->academicYear->id,
            'semester'          => 'ganjil',
            'kkm'               => 78.00,
            'tanggal_mulai'     => now(),
            'tanggal_selesai'   => now()->addHours(2),
            'status'            => 'aktif',
        ]);
        $ujian->classes()->attach($this->kelas7A->id);

        $response = $this->get(route('ujian.cetak-kartu', $ujian));

        $response->assertSuccessful();
        $response->assertViewIs('pdf.kartu-ujian-cbt-massal');
        $response->assertViewHas('cardsData');
        $response->assertSee('Ahmad Test CBT');
        $response->assertSee('0099112233');
        $response->assertSee('cbtpass123'); // Password terdekripsi
        $response->assertSee('Sesi 1');
    }

    public function test_can_filter_cetak_kartu_per_kelas(): void
    {
        $this->actingAs($this->adminUser);

        $kelas7B = Kelas::firstOrCreate(['name' => '7B-Test'], ['grade_level' => 7]);
        $siswa2 = Siswa::firstOrCreate(['nisn' => '0099223344'], ['name' => 'Budi Kelas 7B Test', 'status' => 'aktif']);
        EnrollmentSiswa::firstOrCreate(
            [
                'student_id'       => $siswa2->id,
                'academic_year_id' => $this->academicYear->id,
            ],
            [
                'class_id' => $kelas7B->id,
                'status'   => 'aktif',
            ]
        );

        $ujian = UjianAkademik::create([
            'nama_ujian'        => 'ASTS Gabungan Test',
            'jenis_ujian'       => 'sts',
            'mata_pelajaran_id' => $this->mapelMtk->id,
            'academic_year_id'  => $this->academicYear->id,
            'semester'          => 'ganjil',
            'kkm'               => 75.00,
            'tanggal_mulai'     => now(),
            'tanggal_selesai'   => now()->addHours(2),
            'status'            => 'aktif',
        ]);
        $ujian->classes()->attach([$this->kelas7A->id, $kelas7B->id]);

        // Filter hanya kelas 7A
        $response = $this->get(route('ujian.cetak-kartu', ['ujian' => $ujian, 'class_id' => $this->kelas7A->id]));
        $response->assertSuccessful();
        $response->assertSee('Ahmad Test CBT');
        $response->assertDontSee('Budi Kelas 7B Test');
    }
}
