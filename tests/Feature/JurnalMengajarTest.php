<?php

namespace Tests\Feature;

use App\Livewire\PortalGuru\JurnalMengajar\JurnalMengajarForm;
use App\Livewire\PortalGuru\JurnalMengajar\JurnalMengajarList;
use App\Models\EnrollmentSiswa;
use App\Models\Guru;
use App\Models\JurnalPembelajaran;
use App\Models\Kelas;
use App\Models\KelasAjaran;
use App\Models\MataPelajaran;
use App\Models\Pengajaran;
use App\Models\PengaturanSekolah;
use App\Models\Presensi;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class JurnalMengajarTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $guru;
    protected $tahunAjaran;
    protected $kelas;
    protected $kelasAjaran;
    protected $mapel;
    protected $pengajaran;
    protected $siswa1;
    protected $siswa2;

    protected function setUp(): void
    {
        parent::setUp();

        // Roles & Permissions
        Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'guru', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'wali_kelas', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'portal_guru:supervisi_jurnal', 'guard_name' => 'web']);

        // Tahun Ajaran
        $this->tahunAjaran = TahunAjaran::create([
            'name'       => '2026/2027',
            'start_year' => 2026,
            'end_year'   => 2027,
            'status'     => 'aktif',
        ]);

        // Pengaturan Sekolah
        PengaturanSekolah::create([
            'school_name'              => 'Sekolah Demo',
            'academic_year_id_active'  => $this->tahunAjaran->id,
            'barcode_scan_mode'        => 'nis',
        ]);

        // Guru & User
        $this->user = User::create([
            'name'     => 'Budi Santoso, S.Pd.',
            'email'    => 'budi@sekolah.sch.id',
            'password' => bcrypt('password123'),
        ]);
        $this->user->assignRole('guru');

        $this->guru = Guru::create([
            'user_id' => $this->user->id,
            'name'    => 'Budi Santoso, S.Pd.',
            'nip'     => '198501012010011001',
        ]);

        // Kelas
        $this->kelas = Kelas::create([
            'name'        => 'X MIPA 1',
            'grade_level' => 10,
        ]);

        $this->kelasAjaran = KelasAjaran::create([
            'class_id'         => $this->kelas->id,
            'academic_year_id' => $this->tahunAjaran->id,
            'teacher_id'       => $this->guru->id,
        ]);

        // Mata Pelajaran & Pengajaran
        $this->mapel = MataPelajaran::create([
            'nama_mapel' => 'Fisika',
            'kode_mapel' => 'FIS-10',
        ]);

        $this->pengajaran = Pengajaran::create([
            'class_academic_year_id' => $this->kelasAjaran->id,
            'teacher_id'             => $this->guru->id,
            'mata_pelajaran_id'      => $this->mapel->id,
        ]);

        // Siswa 1 & 2
        $userSiswa1 = User::create([
            'name'     => 'Ahmad Dani',
            'email'    => 'ahmad@siswa.sch.id',
            'password' => bcrypt('password'),
        ]);
        $this->siswa1 = Siswa::create([
            'user_id' => $userSiswa1->id,
            'name'    => 'Ahmad Dani',
            'nis'     => '1001',
            'nisn'    => '001001001',
        ]);

        $userSiswa2 = User::create([
            'name'     => 'Bunga Citra',
            'email'    => 'bunga@siswa.sch.id',
            'password' => bcrypt('password'),
        ]);
        $this->siswa2 = Siswa::create([
            'user_id' => $userSiswa2->id,
            'name'    => 'Bunga Citra',
            'nis'     => '1002',
            'nisn'    => '001001002',
        ]);

        // Enrollment aktif
        EnrollmentSiswa::create([
            'student_id'       => $this->siswa1->id,
            'class_id'         => $this->kelas->id,
            'academic_year_id' => $this->tahunAjaran->id,
            'status'           => 'aktif',
        ]);

        $this->enrollment2 = EnrollmentSiswa::create([
            'student_id'       => $this->siswa2->id,
            'class_id'         => $this->kelas->id,
            'academic_year_id' => $this->tahunAjaran->id,
            'status'           => 'aktif',
        ]);
    }

    /**
     * Test Guru dapat mengakses halaman riwayat jurnal mengajar
     */
    public function test_guru_can_access_jurnal_mengajar_list(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('portal-guru.jurnal-mengajar'));
        $response->assertStatus(200);

        Livewire::test(JurnalMengajarList::class)
            ->assertStatus(200);
    }

    /**
     * Test Guru dapat input jurnal baru dengan kalkulasi cerdas Jam Selesai & JP
     */
    public function test_guru_can_create_jurnal_with_smart_jp_calculation(): void
    {
        $this->actingAs($this->user);

        Livewire::test(JurnalMengajarForm::class)
            ->set('tanggal', now()->format('Y-m-d'))
            ->set('class_id', $this->kelas->id)
            ->set('mata_pelajaran_id', $this->mapel->id)
            ->set('jam_mulai', 3)
            ->set('durasi_jp', 2)
            ->call('calculateJamSelesai')
            ->assertSet('jam_selesai', 4)
            ->assertSet('jam_ke_label', 'Jam ke 3 - 4 (2 JP)')
            ->set('materi', 'Hukum Gerak Newton')
            ->set('uraian_materi', 'Diskusi kelompok mengenai inersia dan penerapan hukum Newton dalam kehidupan sehari-hari.')
            ->set('refleksi', 'Siswa antusias dan memahami konsep dasar dengan baik.')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('portal-guru.jurnal-mengajar'));

        $this->assertDatabaseHas('jurnal_pembelajarans', [
            'teacher_id'        => $this->guru->id,
            'class_id'          => $this->kelas->id,
            'mata_pelajaran_id' => $this->mapel->id,
            'jam_mulai'         => 3,
            'jam_selesai'       => 4,
            'durasi_jp'         => 2,
            'jam_ke_label'      => 'Jam ke 3 - 4 (2 JP)',
            'materi'            => 'Hukum Gerak Newton',
        ]);
    }

    /**
     * Test siswa yang belum absen gerbang dapat diabsenkan dengan status 'telat' otomatis
     */
    public function test_unscanned_student_marked_present_creates_telat_attendance(): void
    {
        $this->actingAs($this->user);
        $today = now()->format('Y-m-d');

        // Siswa 1 belum punya presensi gerbang
        $this->assertDatabaseMissing('attendances', [
            'student_id' => $this->siswa1->id,
            'date'       => $today,
        ]);

        $component = Livewire::test(JurnalMengajarForm::class)
            ->set('tanggal', $today)
            ->set('class_id', $this->kelas->id)
            ->set('mata_pelajaran_id', $this->mapel->id)
            ->set('jam_mulai', 1)
            ->set('durasi_jp', 2)
            ->set('materi', 'Pengukuran dan Besaran Fisika')
            ->set('uraian_materi', 'Praktik penggunaan jangka sorong dan mikrometer sekrup.')
            ->call('toggleAbsenkanTelat', $this->siswa1->id)
            ->call('save');

        $component->assertHasNoErrors();

        // Verifikasi tabel attendances terbuat otomatis dengan status telat
        $this->assertDatabaseHas('attendances', [
            'student_id'      => $this->siswa1->id,
            'status'          => 'telat',
            'source'          => 'jurnal_guru',
            'is_manual_input' => 1,
        ]);
    }

    /**
     * Test siswa yang memiliki status gerbang 'sakit' terkunci dan tidak dapat diubah hadir di kelas
     */
    public function test_locked_student_with_sakit_or_izin_preserves_gate_status(): void
    {
        $this->actingAs($this->user);
        $today = now()->format('Y-m-d');

        // Siswa 2 tercatat sakit di gerbang
        Presensi::create([
            'student_id'      => $this->siswa2->id,
            'enrollment_id'   => $this->enrollment2->id,
            'class_id'        => $this->kelas->id,
            'academic_year_id'=> $this->tahunAjaran->id,
            'date'            => $today,
            'status'          => 'sakit',
            'notes'           => 'Surat dokter terlampir',
        ]);

        $component = Livewire::test(JurnalMengajarForm::class)
            ->set('tanggal', $today)
            ->set('class_id', $this->kelas->id)
            ->set('mata_pelajaran_id', $this->mapel->id);

        $studentList = $component->get('studentList');

        // Baris siswa 2 harus terkunci
        $this->assertTrue($studentList[$this->siswa2->id]['is_locked']);
        $this->assertEquals('sakit', $studentList[$this->siswa2->id]['status_gerbang']);
        $this->assertEquals('tidak_mengikuti', $studentList[$this->siswa2->id]['status_jurnal']);

        // Jika guru mencoba memanggil setStatusJurnal('hadir'), status tetap tidak_mengikuti
        $component->call('setStatusJurnal', $this->siswa2->id, 'hadir');
        $updatedList = $component->get('studentList');
        $this->assertEquals('tidak_mengikuti', $updatedList[$this->siswa2->id]['status_jurnal']);
    }

    /**
     * Test download / stream rekap cetak PDF jurnal mengajar
     */
    public function test_cetak_pdf_rekap_returns_successful_stream(): void
    {
        $this->actingAs($this->user);

        // Buat 1 record jurnal
        JurnalPembelajaran::create([
            'academic_year_id'      => $this->tahunAjaran->id,
            'teacher_id'            => $this->guru->id,
            'class_id'              => $this->kelas->id,
            'mata_pelajaran_id'     => $this->mapel->id,
            'tanggal'               => now()->format('Y-m-d'),
            'jam_mulai'             => 1,
            'durasi_jp'             => 2,
            'jam_selesai'           => 2,
            'jam_ke_label'          => 'Jam ke 1 - 2 (2 JP)',
            'materi'                => 'Vektor dan Kinematika',
            'uraian_materi'         => 'Penjelasan penjumlahan vektor metode poligon dan analitis.',
            'refleksi'              => 'Sebagian siswa butuh latihan trigonometri dasar.',
            'total_siswa'           => 2,
            'total_hadir'           => 2,
            'total_tidak_mengikuti' => 0,
            'created_by'            => $this->user->id,
        ]);

        $response = $this->get(route('portal-guru.jurnal-mengajar.cetak', [
            'teacher_id'        => $this->guru->id,
            'class_id'          => $this->kelas->id,
            'mata_pelajaran_id' => $this->mapel->id,
            'academic_year_id'  => $this->tahunAjaran->id,
        ]));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_cetak_pdf_with_semester_and_monthly_periods()
    {
        $this->actingAs($this->user);

        // Cetak Bulanan
        $resBulan = $this->get(route('portal-guru.jurnal-mengajar.cetak', [
            'periode_type' => 'bulanan',
            'month_year'   => '10-2026',
            'class_id'     => $this->kelas->id,
        ]));
        $resBulan->assertStatus(200);
        $resBulan->assertHeader('content-type', 'application/pdf');

        // Cetak Semester Ganjil
        $resSemester = $this->get(route('portal-guru.jurnal-mengajar.cetak', [
            'periode_type' => 'semester',
            'semester'     => 'ganjil',
            'class_id'     => $this->kelas->id,
        ]));
        $resSemester->assertStatus(200);
        $resSemester->assertHeader('content-type', 'application/pdf');

        // Cetak Tahunan
        $resTahun = $this->get(route('portal-guru.jurnal-mengajar.cetak', [
            'periode_type' => 'tahunan',
            'class_id'     => $this->kelas->id,
        ]));
        $resTahun->assertStatus(200);
        $resTahun->assertHeader('content-type', 'application/pdf');
    }

    public function test_download_excel_rekap()
    {
        $this->actingAs($this->user);

        $response = $this->get(route('portal-guru.jurnal-mengajar.excel', [
            'teacher_id'        => $this->guru->id,
            'class_id'          => $this->kelas->id,
            'mata_pelajaran_id' => $this->mapel->id,
            'periode_type'      => 'bulanan',
            'month_year'        => '10-2026',
        ]));

        $response->assertStatus(200);
        $this->assertTrue(
            str_contains($response->headers->get('content-disposition', ''), '.xlsx') ||
            str_contains($response->headers->get('content-type', ''), 'spreadsheet')
        );
    }

    public function test_livewire_modal_cetak_methods()
    {
        $this->actingAs($this->user);

        Livewire::test(JurnalMengajarList::class)
            ->call('openCetakModal')
            ->assertSet('showCetakModal', true)
            ->assertSet('cetakTeacherId', $this->guru->id)
            ->assertSet('cetakMapelId', $this->mapel->id)
            ->assertCount('cetakAvailableClasses', 1)
            ->assertSet('cetakJenis', 'bulanan')
            ->set('cetakJenis', 'semester')
            ->set('cetakSemester', 'ganjil')
            ->set('cetakClassId', $this->kelas->id)
            ->call('downloadCetakPdf')
            ->assertSet('showCetakModal', false);
    }

    public function test_jurnal_mengajar_month_filter_and_supervisor_mode()
    {
        $this->actingAs($this->user);

        // Buat jurnal bulan Oktober 2026
        $jurnalOkt = JurnalPembelajaran::create([
            'academic_year_id'  => $this->tahunAjaran->id,
            'teacher_id'        => $this->guru->id,
            'class_id'          => $this->kelas->id,
            'mata_pelajaran_id' => $this->mapel->id,
            'tanggal'           => '2026-10-15',
            'jam_mulai'         => 1,
            'jam_selesai'       => 2,
            'durasi_jp'         => 2,
            'jam_ke_label'      => 'Jam ke 1 - 2 (2 JP)',
            'materi'            => 'Materi Oktober',
            'uraian_materi'     => 'Uraian materi bulan Oktober',
            'total_siswa'       => 2,
            'total_hadir'       => 2,
        ]);

        // Buat jurnal bulan November 2026
        $jurnalNov = JurnalPembelajaran::create([
            'academic_year_id'  => $this->tahunAjaran->id,
            'teacher_id'        => $this->guru->id,
            'class_id'          => $this->kelas->id,
            'mata_pelajaran_id' => $this->mapel->id,
            'tanggal'           => '2026-11-20',
            'jam_mulai'         => 3,
            'jam_selesai'       => 4,
            'durasi_jp'         => 2,
            'jam_ke_label'      => 'Jam ke 3 - 4 (2 JP)',
            'materi'            => 'Materi November',
            'uraian_materi'     => 'Uraian materi bulan November',
            'total_siswa'       => 2,
            'total_hadir'       => 2,
        ]);

        // Filter Oktober 2026 -> Hanya muncul Materi Oktober
        Livewire::test(JurnalMengajarList::class)
            ->set('selectedMonthYear', '10-2026')
            ->assertSee('Materi Oktober')
            ->assertDontSee('Materi November');

        // Filter November 2026 -> Hanya muncul Materi November
        Livewire::test(JurnalMengajarList::class)
            ->set('selectedMonthYear', '11-2026')
            ->assertSee('Materi November')
            ->assertDontSee('Materi Oktober');

        // Filter Semua Bulan ('') -> Muncul kedua materi
        Livewire::test(JurnalMengajarList::class)
            ->set('selectedMonthYear', '')
            ->assertSee('Materi Oktober')
            ->assertSee('Materi November');
    }

    /**
     * Test Jam Mulai (1-9), Durasi Pembelajaran (1-5 JP), dan kalkulasi menit (40 menit/JP)
     */
    public function test_jam_mulai_1_to_9_and_durasi_1_to_5_calculation(): void
    {
        $this->actingAs($this->user);

        Livewire::test(JurnalMengajarForm::class)
            ->call('setJamMulai', 9)
            ->call('setDurasi', 2)
            ->assertSet('jam_mulai', 9)
            ->assertSet('durasi_jp', 2)
            ->assertSet('jam_selesai', 10)
            ->assertSet('jam_ke_label', 'Jam ke 9 - 10 (2 JP)')
            ->assertSee('80 Menit')
            ->assertSee('40 Menit / JP')
            ->assertSee('1 JP = 40 Menit')
            // Test clamping pada setter (maks 9 untuk jam mulai, maks 5 untuk durasi):
            ->call('setJamMulai', 15)
            ->assertSet('jam_mulai', 9)
            ->call('setDurasi', 10)
            ->assertSet('durasi_jp', 5)
            ->call('setJamMulai', 0)
            ->assertSet('jam_mulai', 1)
            ->call('setDurasi', 0)
            ->assertSet('durasi_jp', 1)
            ->assertSet('jam_selesai', 1)
            ->assertSet('jam_ke_label', 'Jam ke 1 (1 JP)')
            ->assertSee('40 Menit');
    }
}

