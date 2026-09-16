<?php

namespace Tests\Feature\Cbt;

use App\Exceptions\Cbt\CbtAuthenticationException;
use App\Exceptions\Cbt\CbtRateLimitException;
use App\Exceptions\Cbt\CbtServerException;
use App\Exceptions\Cbt\CbtValidationException;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\NilaiUjian;
use App\Models\Siswa;
use App\Models\StudentCbtProfile;
use App\Models\TahunAjaran;
use App\Models\UjianAkademik;
use App\Services\Cbt\CbtServiceInterface;
use App\Services\Cbt\ZenCbtService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class ZenCbtServiceTest extends TestCase
{
    use DatabaseTransactions;

    protected ZenCbtService $service;

    protected function setUp(): void
    {
        parent::setUp();

        // Gunakan koneksi MySQL dengan DatabaseTransactions agar data uji di-rollback otomatis
        config([
            'database.default'                    => 'mysql',
            'database.connections.mysql.database' => 'absensi_barcode',
            'cbt.api_url'                         => 'http://127.0.0.1:7879',
            'cbt.api_key'                         => 'test_bridge_secret_key_mock',
            'cbt.timeout'                         => 5,
        ]);
        DB::purge('mysql');
        DB::reconnect('mysql');

        $this->service = app(ZenCbtService::class);
    }

    public function test_service_is_bound_in_container(): void
    {
        $instance = app(CbtServiceInterface::class);
        $this->assertInstanceOf(ZenCbtService::class, $instance);
    }

    public function test_ping_returns_true_when_bridge_is_alive(): void
    {
        Http::fake([
            'http://127.0.0.1:7879/' => Http::response(['success' => true], 200),
        ]);

        $this->assertTrue($this->service->ping());
    }

    public function test_ping_returns_false_when_bridge_is_unreachable(): void
    {
        Http::fake([
            'http://127.0.0.1:7879/' => Http::response(null, 500),
        ]);

        $this->assertFalse($this->service->ping());
    }

    public function test_health_check_returns_data_on_200(): void
    {
        Http::fake([
            'http://127.0.0.1:7879/api/v1/health' => Http::response([
                'success' => true,
                'data' => [
                    'status'     => 'healthy',
                    'database'   => 'connected',
                    'latency_ms' => 12.34,
                ],
            ], 200),
        ]);

        $health = $this->service->healthCheck();
        $this->assertEquals('healthy', $health['status']);
        $this->assertEquals('connected', $health['database']);
    }

    public function test_health_check_throws_authentication_exception_on_401(): void
    {
        Http::fake([
            'http://127.0.0.1:7879/api/v1/health' => Http::response([
                'success' => false,
                'message' => 'Token Bearer tidak valid.',
            ], 401),
        ]);

        $this->expectException(CbtAuthenticationException::class);
        $this->service->healthCheck();
    }

    public function test_throws_rate_limit_exception_on_429_with_retry_after(): void
    {
        Http::fake([
            'http://127.0.0.1:7879/api/v1/sync/master' => Http::response([
                'success' => false,
                'message' => 'Rate limit exceeded.',
            ], 429, ['Retry-After' => '45']),
        ]);

        try {
            $this->service->syncMasterData();
            $this->fail('Harusnya melempar CbtRateLimitException');
        } catch (CbtRateLimitException $e) {
            $this->assertEquals(45, $e->getRetryAfter());
            $this->assertEquals(429, $e->getCode());
        }
    }

    public function test_throws_validation_exception_on_422(): void
    {
        Http::fake([
            'http://127.0.0.1:7879/api/v1/sync/master' => Http::response([
                'success' => false,
                'message' => 'Validasi Relasi Kelas Gagal',
                'error'   => ["Tingkat kelas dengan kode '99' belum terdaftar."],
            ], 422),
        ]);

        try {
            $this->service->syncMasterData();
            $this->fail('Harusnya melempar CbtValidationException');
        } catch (CbtValidationException $e) {
            $this->assertEquals(422, $e->getCode());
            $this->assertCount(1, $e->getErrors());
        }
    }

    public function test_throws_server_exception_on_500(): void
    {
        Http::fake([
            'http://127.0.0.1:7879/api/v1/exams' => Http::response([
                'success' => false,
                'message' => 'Internal Server Error pada database ZenCBT',
            ], 500),
        ]);

        $this->expectException(CbtServerException::class);
        $this->service->getExams();
    }

    public function test_sync_master_data_formats_and_sends_payload(): void
    {
        Http::fake([
            'http://127.0.0.1:7879/api/v1/sync/master' => Http::response([
                'success' => true,
                'data'    => [
                    'programs_synced' => 1,
                    'levels_synced'   => 1,
                    'groups_synced'   => 1,
                    'subjects_synced' => 1,
                ],
            ], 200),
        ]);

        $result = $this->service->syncMasterData();

        $this->assertArrayHasKey('programs_synced', $result);
        Http::assertSent(function ($request) {
            return $request->url() === 'http://127.0.0.1:7879/api/v1/sync/master'
                && isset($request['academic_years'])
                && isset($request['levels'])
                && isset($request['classes'])
                && isset($request['subjects']);
        });
    }

    public function test_sync_students_auto_creates_cbt_profile_and_excludes_password(): void
    {
        // 1. Buat siswa dummy untuk pengujian
        $dummyNisn = '99990099';
        Siswa::where('nisn', $dummyNisn)->forceDelete();

        $siswa = Siswa::create([
            'nisn'     => $dummyNisn,
            'nis'      => '99099',
            'name'     => 'Siswa Uji Sync Unit Test',
            'status'   => 'aktif',
            'religion' => 'Islam',
        ]);

        // Pastikan belum ada StudentCbtProfile
        StudentCbtProfile::where('student_id', $siswa->id)->delete();
        $this->assertNull($siswa->cbtProfile()->first());

        Http::fake([
            'http://127.0.0.1:7879/api/v1/sync/students' => Http::response([
                'success' => true,
                'data'    => [
                    'total_processed' => 1,
                    'inserted'        => 1,
                    'updated'         => 0,
                ],
            ], 200),
        ]);

        $result = $this->service->syncStudents();

        // 2. Profil CBT harus otomatis dibuatkan
        $profile = $siswa->cbtProfile()->first();
        $this->assertNotNull($profile);
        $this->assertEquals('Sesi 1', $profile->cbt_sesi);
        $this->assertEquals('active', $profile->cbt_status);

        // 3. Verifikasi payload HTTP: TIDAK ADA field password
        Http::assertSent(function ($request) use ($dummyNisn) {
            $students = $request['students'] ?? [];
            foreach ($students as $s) {
                if ($s['nisn'] === $dummyNisn) {
                    return !isset($s['password']) && !isset($s['password_plain']) && $s['session'] === 'Sesi 1';
                }
            }
            return false;
        });

        // Cleanup
        $siswa->forceDelete();
    }

    public function test_pull_exam_results_saves_to_nilai_ujians_and_skips_unknown_nisn(): void
    {
        Log::shouldReceive('warning')->atLeast()->once();
        Log::shouldReceive('error')->zeroOrMoreTimes();
        Log::shouldReceive('debug')->zeroOrMoreTimes();

        // 1. Siapkan data master & agenda ujian
        $ta = TahunAjaran::where('status', 'aktif')->first() ?: TahunAjaran::create([
            'name'       => '2025/2026',
            'start_year' => 2025,
            'end_year'   => 2026,
            'status'     => 'aktif',
        ]);

        $mapel = MataPelajaran::first() ?: MataPelajaran::create([
            'kode_mapel' => 'TEST-01',
            'nama_mapel' => 'Mapel Uji',
            'kkm_default' => 75.00,
        ]);

        $ujian = UjianAkademik::create([
            'academic_year_id'  => $ta->id,
            'semester'          => 'ganjil',
            'mata_pelajaran_id' => $mapel->id,
            'nama_ujian'        => 'Ujian Matematika Semester 1',
            'jenis_ujian'       => 'harian',
            'kkm'               => 75.00,
            'total_soal'        => 40,
            'durasi_menit'      => 60,
            'tanggal_mulai'     => now(),
            'tanggal_selesai'   => now()->addHours(2),
            'status'            => 'selesai',
        ]);

        $kelas = Kelas::first() ?: Kelas::create(['name' => '7A', 'grade_level' => 7]);

        // 2. Siapkan 1 siswa valid
        $validNisn = '99990001';
        $siswa = Siswa::where('nisn', $validNisn)->first() ?: Siswa::create([
            'nisn'            => $validNisn,
            'name'            => 'Siswa Valid Test',
            'status'          => 'aktif',
            'admission_class' => $kelas->name,
        ]);
        if (!$siswa->admission_class || $siswa->admission_class !== $kelas->name) {
            $siswa->update(['admission_class' => $kelas->name]);
        }

        // 3. Mock respons CBT dengan 1 siswa valid + 1 siswa fiktif
        Http::fake([
            'http://127.0.0.1:7879/api/v1/exams/101/results' => Http::response([
                'success' => true,
                'data'    => [
                    [
                        'siswa_id'        => $validNisn,
                        'score'           => 85.00,
                        'jumlah_benar'    => 34,
                        'jumlah_salah'    => 6,
                        'jumlah_kosong'   => 0,
                        'violation_count' => 0,
                    ],
                    [
                        'siswa_id'        => '99999999_UNKNOWN', // Siswa tidak terdaftar di Laravel
                        'score'           => 90.00,
                        'jumlah_benar'    => 36,
                        'jumlah_salah'    => 4,
                        'jumlah_kosong'   => 0,
                        'violation_count' => 0,
                    ],
                ],
            ], 200),
        ]);

        $summary = $this->service->pullExamResults('101', $ujian);

        // 4. Verifikasi ringkasan
        $this->assertEquals(2, $summary['total_results']);
        $this->assertEquals(1, $summary['synced']);
        $this->assertEquals(1, $summary['skipped']);

        // 5. Verifikasi di database nilai_ujians
        $nilai = NilaiUjian::where('ujian_akademik_id', $ujian->id)
            ->where('student_id', $siswa->id)
            ->first();

        $this->assertNotNull($nilai);
        $this->assertEquals(85.00, (float) $nilai->nilai_akhir);
        $this->assertTrue((bool) $nilai->is_tuntas); // KKM 75, nilai 85 -> tuntas
        $this->assertEquals((string) $ta->id, (string) $nilai->academic_year_id);
        $this->assertEquals('ganjil', $nilai->semester);

        // Siswa unknown tidak tersimpan di database
        $this->assertEquals(1, NilaiUjian::where('ujian_akademik_id', $ujian->id)->count());
    }

    public function test_pull_exam_results_is_idempotent(): void
    {
        $ta = TahunAjaran::where('status', 'aktif')->first() ?: TahunAjaran::create([
            'name'       => '2025/2026',
            'start_year' => 2025,
            'end_year'   => 2026,
            'status'     => 'aktif',
        ]);

        $mapel = MataPelajaran::first() ?: MataPelajaran::create([
            'kode_mapel' => 'TEST-02',
            'nama_mapel' => 'Mapel Uji Idempotency',
            'kkm_default' => 75.00,
        ]);

        $ujian = UjianAkademik::create([
            'academic_year_id'  => $ta->id,
            'semester'          => 'ganjil',
            'mata_pelajaran_id' => $mapel->id,
            'nama_ujian'        => 'Ujian Idempotency Test',
            'jenis_ujian'       => 'harian',
            'kkm'               => 75.00,
            'durasi_menit'      => 60,
            'total_soal'        => 30,
            'tanggal_mulai'     => now(),
            'tanggal_selesai'   => now()->addHours(2),
            'status'            => 'selesai',
        ]);

        $kelas = Kelas::first() ?: Kelas::create(['name' => '7A', 'grade_level' => 7]);

        $validNisn = '99990002';
        $siswa = Siswa::where('nisn', $validNisn)->first() ?: Siswa::create([
            'nisn'            => $validNisn,
            'name'            => 'Siswa Idempotent Test',
            'status'          => 'aktif',
            'admission_class' => $kelas->name,
        ]);
        if (!$siswa->admission_class || $siswa->admission_class !== $kelas->name) {
            $siswa->update(['admission_class' => $kelas->name]);
        }

        Http::fake([
            'http://127.0.0.1:7879/api/v1/exams/102/results' => Http::response([
                'success' => true,
                'data'    => [
                    [
                        'siswa_id'        => $validNisn,
                        'score'           => 80.00,
                        'jumlah_benar'    => 32,
                        'jumlah_salah'    => 8,
                        'jumlah_kosong'   => 0,
                        'violation_count' => 0,
                    ],
                ],
            ], 200),
        ]);

        // Panggilan 1
        $this->service->pullExamResults('102', $ujian);
        $this->assertEquals(1, NilaiUjian::where('ujian_akademik_id', $ujian->id)->count());

        // Panggilan 2 (Menarik ulang hasil yang sama)
        $this->service->pullExamResults('102', $ujian);
        // Baris nilai di database TIDAK BOLEH berlipat ganda
        $this->assertEquals(1, NilaiUjian::where('ujian_akademik_id', $ujian->id)->count());
    }

    public function test_set_student_session_updates_local_profile(): void
    {
        $dummyNisn = '99990003';
        $siswa = Siswa::where('nisn', $dummyNisn)->first() ?: Siswa::create([
            'nisn'   => $dummyNisn,
            'name'   => 'Siswa Set Session Test',
            'status' => 'aktif',
        ]);

        Http::fake([
            'http://127.0.0.1:7879/api/v1/students/' . $dummyNisn . '/set-session' => Http::response([
                'success' => true,
                'message' => 'Sesi berhasil diubah',
                'data'    => [
                    'nisn'             => $dummyNisn,
                    'previous_session' => 'Sesi 1',
                    'new_session'      => 'Sesi 3',
                ],
            ], 200),
        ]);

        $this->service->setStudentSession($dummyNisn, '3');

        $profile = $siswa->cbtProfile()->first();
        $this->assertNotNull($profile);
        $this->assertEquals('Sesi 3', $profile->cbt_sesi);
    }
}
