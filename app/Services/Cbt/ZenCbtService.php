<?php

namespace App\Services\Cbt;

use App\Exceptions\Cbt\CbtAuthenticationException;
use App\Exceptions\Cbt\CbtConnectionException;
use App\Exceptions\Cbt\CbtException;
use App\Exceptions\Cbt\CbtRateLimitException;
use App\Exceptions\Cbt\CbtServerException;
use App\Exceptions\Cbt\CbtValidationException;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\NilaiUjian;
use App\Models\PengaturanSekolah;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\UjianAkademik;
use Illuminate\Http\Client\ConnectionException as HttpClientConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ZenCbtService implements CbtServiceInterface
{
    /**
     * Resolusi konfigurasi CBT bertingkat:
     * 1. school_settings (PengaturanSekolah::current()) jika sudah diisi.
     * 2. config/cbt.php (fallback ke .env).
     * 
     * @return array<string, mixed>
     */
    protected function resolveConfig(): array
    {
        $settings = PengaturanSekolah::current();

        $url = $settings?->cbt_api_url ?: config('cbt.api_url', 'http://127.0.0.1:7879');
        $key = $settings?->cbt_api_key ?: config('cbt.api_key', '');
        $timeout = (int) config('cbt.timeout', 15);
        $connectTimeout = (int) config('cbt.connect_timeout', 5);

        return [
            'url'             => rtrim((string) $url, '/'),
            'key'             => (string) $key,
            'timeout'         => $timeout,
            'connect_timeout' => $connectTimeout,
        ];
    }

    /**
     * Siapkan instance HTTP Client dengan otentikasi Bearer dan timeout.
     */
    protected function client(): PendingRequest
    {
        $config = $this->resolveConfig();

        return Http::baseUrl($config['url'])
            ->withToken($config['key'])
            ->timeout($config['timeout'])
            ->connectTimeout($config['connect_timeout'])
            ->acceptJson();
    }

    /**
     * Eksekusi HTTP request dengan penanganan error & mapping exception terstruktur.
     *
     * @throws CbtConnectionException
     * @throws CbtAuthenticationException
     * @throws CbtValidationException
     * @throws CbtRateLimitException
     * @throws CbtServerException
     * @throws CbtException
     */
    protected function send(string $method, string $endpoint, array $data = []): array
    {
        try {
            $client = $this->client();

            /** @var Response $response */
            $response = match (strtoupper($method)) {
                'GET'    => $client->get($endpoint, $data),
                'POST'   => $client->post($endpoint, $data),
                'PUT'    => $client->put($endpoint, $data),
                'DELETE' => $client->delete($endpoint, $data),
                default  => throw new CbtException("HTTP method '{$method}' tidak didukung."),
            };
        } catch (HttpClientConnectionException $e) {
            Log::error("Koneksi ke ZenCBT Bridge terputus / timeout", [
                'endpoint' => $endpoint,
                'error'    => $e->getMessage(),
            ]);
            throw new CbtConnectionException("Gagal terhubung ke ZenCBT Bridge: " . $e->getMessage(), 0, $e);
        }

        $statusCode = $response->status();
        $payload = $response->json();

        if ($statusCode === 200 || $statusCode === 201) {
            return (array) ($payload['data'] ?? $payload);
        }

        if ($statusCode === 401) {
            $msg = $payload['message'] ?? 'Autentikasi Bearer token ke ZenCBT Bridge ditolak (HTTP 401).';
            Log::error("Autentikasi CBT gagal", ['endpoint' => $endpoint, 'message' => $msg]);
            throw new CbtAuthenticationException($msg, 401);
        }

        if ($statusCode === 422) {
            $msg = $payload['message'] ?? 'Validasi relasi atau parameter CBT gagal (HTTP 422).';
            $errors = (array) ($payload['error'] ?? []);
            Log::warning("Validasi payload CBT ditolak bridge", ['endpoint' => $endpoint, 'errors' => $errors]);
            throw new CbtValidationException($msg, $errors, 422);
        }

        if ($statusCode === 429) {
            $retryAfter = (int) ($response->header('Retry-After') ?: 60);
            Log::warning("Rate limit ZenCBT Bridge terlampaui (HTTP 429)", [
                'endpoint'    => $endpoint,
                'retry_after' => $retryAfter,
            ]);
            throw new CbtRateLimitException(
                "Pembatasan laju request ZenCBT terlampaui. Coba lagi dalam {$retryAfter} detik.",
                $retryAfter,
                429
            );
        }

        if ($statusCode >= 500) {
            $msg = $payload['message'] ?? 'Terjadi kesalahan internal pada server ZenCBT Bridge (HTTP 500).';
            Log::error("Server ZenCBT Bridge error", ['endpoint' => $endpoint, 'status' => $statusCode, 'payload' => $payload]);
            throw new CbtServerException($msg, $statusCode);
        }

        $defaultMsg = $payload['message'] ?? "ZenCBT Bridge mengembalikan HTTP status {$statusCode}.";
        throw new CbtException($defaultMsg, $statusCode);
    }

    /**
     * {@inheritDoc}
     */
    public function ping(): bool
    {
        try {
            $config = $this->resolveConfig();
            $response = Http::baseUrl($config['url'])
                ->timeout($config['connect_timeout'])
                ->get('/');

            return $response->successful();
        } catch (\Throwable $e) {
            Log::debug("CBT ping gagal: " . $e->getMessage());
            return false;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function healthCheck(): array
    {
        return $this->send('GET', '/api/v1/health');
    }

    /**
     * {@inheritDoc}
     */
    public function syncMasterData(): array
    {
        // 1. Academic Years (Tahun Ajaran aktif & relevan)
        $academicYears = TahunAjaran::all()->map(function (TahunAjaran $ta) {
            $code = ($ta->start_year && $ta->end_year)
                ? "{$ta->start_year}-{$ta->end_year}"
                : str_replace('/', '-', $ta->name);

            return [
                'code' => $code,
                'name' => $ta->name,
            ];
        })->values()->all();

        // 2. Levels (Tingkat Kelas: 7, 8, 9)
        $classes = Kelas::all();
        $distinctGrades = $classes->pluck('grade_level')->filter()->unique()->sort()->values();
        $levels = $distinctGrades->map(function ($grade) {
            return [
                'code' => (string) $grade,
                'name' => "Kelas {$grade}",
            ];
        })->all();

        // 3. Groups (Rombel / Kelas: 7A, 8B, dll)
        $classesPayload = $classes->map(function (Kelas $cls) {
            return [
                'code'             => $cls->name,
                'name'             => $cls->name,
                'grade_level_code' => (string) $cls->grade_level,
            ];
        })->values()->all();

        // 4. Subjects (Mata Pelajaran)
        $subjects = MataPelajaran::all()->map(function (MataPelajaran $mapel) {
            return [
                'code'     => $mapel->kode_mapel ?: 'MAPEL-' . $mapel->id,
                'name'     => $mapel->nama_mapel,
                'category' => $mapel->kategori ?: 'Umum',
            ];
        })->values()->all();

        $payload = [
            'academic_years' => $academicYears,
            'grade_levels'   => $levels,
            'levels'         => $levels,
            'classes'        => $classesPayload,
            'subjects'       => $subjects,
        ];

        return $this->send('POST', '/api/v1/sync/master', $payload);
    }

    /**
     * {@inheritDoc}
     */
    public function syncStudents(?array $classIds = null): array
    {
        $activeYear = TahunAjaran::where('status', 'aktif')->first();
        $yearCode = $activeYear
            ? (($activeYear->start_year && $activeYear->end_year)
                ? "{$activeYear->start_year}-{$activeYear->end_year}"
                : str_replace('/', '-', $activeYear->name))
            : null;

        $query = Siswa::where('status', 'aktif');

        if (!empty($classIds)) {
            $query->whereHas('enrollments', function ($q) use ($classIds, $activeYear) {
                $q->whereIn('class_id', $classIds);
                if ($activeYear) {
                    $q->where('academic_year_id', $activeYear->id);
                }
            });
        }

        $students = $query->with(['cbtProfile'])->get();

        if ($students->isEmpty()) {
            return [
                'total_processed' => 0,
                'inserted'        => 0,
                'updated'         => 0,
                'message'         => 'Tidak ada siswa aktif yang memenuhi kriteria filter.',
            ];
        }

        $chunkSize = (int) config('cbt.chunk_size', 50);
        $totalInserted = 0;
        $totalUpdated = 0;

        foreach ($students->chunk($chunkSize) as $chunk) {
            $batchPayload = [];

            foreach ($chunk as $siswa) {
                // Pastikan StudentCbtProfile ada (Profil CBT Pattern)
                $profile = $siswa->cbtProfile;
                if (!$profile) {
                    $profile = $siswa->cbtProfile()->create([
                        'cbt_sesi'   => 'Sesi 1',
                        'cbt_status' => 'active',
                    ]);
                }

                $kelas = $siswa->resolveKelasModel($activeYear?->id);
                $kelasNama = $kelas ? $kelas->name : '';

                // Format password: KelasNISN — contoh: 7A1000000001
                // Selalu dihitung ulang agar otomatis update saat kenaikan kelas
                $cbtPassword = $kelasNama . $siswa->nisn;

                $batchPayload[] = [
                    'nisn'               => (string) $siswa->nisn,
                    'name'               => (string) $siswa->name,
                    'password'           => $cbtPassword,
                    'academic_year_code' => $yearCode,
                    'level_code'         => $kelas ? (string) $kelas->grade_level : null,
                    'group_code'         => $kelas ? (string) $kelas->name : null,
                    'religion'           => (string) ($siswa->religion ?? 'Islam'),
                    'session'            => (string) ($profile->cbt_sesi ?: 'Sesi 1'),
                ];
            }

            $response = $this->send('POST', '/api/v1/sync/students', [
                'students' => $batchPayload,
            ]);

            $totalInserted += (int) ($response['inserted'] ?? 0);
            $totalUpdated  += (int) ($response['updated'] ?? 0);
        }

        return [
            'total_processed' => $students->count(),
            'inserted'        => $totalInserted,
            'updated'         => $totalUpdated,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getExams(): array
    {
        return $this->send('GET', '/api/v1/exams');
    }

    /**
     * Mempratinjau jadwal yang akan masuk atau berubah dari ZenCBT ke Laravel
     *
     * @return array<string, mixed>
     */
    public function previewSyncExams(): array
    {
        $response = $this->getExams();
        $exams = $response['exams'] ?? [];

        $newExams = [];
        $updatedExams = [];

        foreach ($exams as $exam) {
            $cbtExamId = (int) $exam['id'];
            $local = UjianAkademik::where('cbt_ujian_id', $cbtExamId)->first();

            if (!$local) {
                $newExams[] = $exam['title'] ?: "Ujian CBT {$cbtExamId}";
            } else {
                // Periksa apakah ada perubahan nama atau event
                $titleCbt = $exam['title'] ?: "Ujian CBT {$cbtExamId}";
                if ($local->nama_ujian !== $titleCbt || $local->cbt_event_nama !== $exam['event_nama']) {
                    $updatedExams[] = $titleCbt . ' (sebelumnya: ' . $local->nama_ujian . ')';
                }
            }
        }

        return [
            'new' => $newExams,
            'updated' => $updatedExams,
            'total_changes' => count($newExams) + count($updatedExams),
            'raw_exams' => $exams,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function syncExams(array $exams = []): array
    {
        if (empty($exams)) {
            $response = $this->getExams();
            $exams = $response['exams'] ?? [];
        }

        $synced = 0;
        $activeYear = TahunAjaran::where('status', 'aktif')->first();

        foreach ($exams as $exam) {
            $cbtExamId = (int) $exam['id'];
            
            // Map status
            $status = 'draft';
            if ($exam['is_active']) {
                $status = 'aktif';
            } elseif (!empty($exam['end_time']) && strtotime($exam['end_time']) < time()) {
                $status = 'selesai';
            }

            // Map mapel jika ada
            $mapelId = null;
            if (!empty($exam['mapel_kode'])) {
                $mapel = MataPelajaran::where('kode_mapel', $exam['mapel_kode'])->first();
                if ($mapel) {
                    $mapelId = $mapel->id;
                }
            }

            $local = UjianAkademik::where('cbt_ujian_id', $cbtExamId)->first();
            $jenisUjianId = $local ? $local->jenis_ujian_id : null;

            UjianAkademik::updateOrCreate(
                ['cbt_ujian_id' => $cbtExamId],
                [
                    'academic_year_id' => $activeYear?->id,
                    'semester'         => $activeYear?->semester ?? 'ganjil',
                    'mata_pelajaran_id'=> $mapelId,
                    'nama_ujian'       => $exam['title'] ?: 'Ujian CBT ' . $cbtExamId,
                    'cbt_event_nama'   => $exam['event_nama'],
                    'jenis_ujian_id'   => $jenisUjianId, // Tidak mengubah yang sudah di-set
                    'kkm'              => UjianAkademik::resolveDefaultKkm($mapelId),
                    'durasi_menit'     => $exam['durasi'] ?? 0,
                    'tanggal_mulai'    => $exam['start_time'] ?? null,
                    'tanggal_selesai'  => $exam['end_time'] ?? null,
                    'status'           => $status,
                ]
            );

            $synced++;
        }

        return [
            'total_exams' => count($exams),
            'synced'      => $synced,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function pullExamResults(string|int $cbtExamId, UjianAkademik $ujianAkademik): array
    {
        $response = $this->send('GET', "/api/v1/exams/{$cbtExamId}/results");
        $results = (array) ($response['results'] ?? $response);

        $synced = 0;
        $skipped = 0;

        foreach ($results as $item) {
            $nisn = trim((string) ($item['siswa_id'] ?? $item['nisn'] ?? ''));

            if ($nisn === '') {
                $skipped++;
                continue;
            }

            $siswa = Siswa::where('nisn', $nisn)->first();

            if (!$siswa) {
                Log::warning("CBT Pull: Siswa dengan NISN '{$nisn}' tidak ditemukan di database Laravel. Nilai di-skip.", [
                    'cbt_exam_id'       => $cbtExamId,
                    'ujian_akademik_id' => $ujianAkademik->id,
                    'raw_item'          => $item,
                ]);
                $skipped++;
                continue; // Lanjut ke siswa berikutnya, jangan membatalkan penarikan
            }

            $classId = $siswa->resolveKelasModel($ujianAkademik->academic_year_id)?->id
                ?: $ujianAkademik->classes()->first()?->id;

            if (!$classId) {
                Log::warning("CBT Pull: Rombel/kelas siswa '{$nisn}' tidak ditemukan untuk ujian ini. Nilai di-skip.", [
                    'cbt_exam_id'       => $cbtExamId,
                    'ujian_akademik_id' => $ujianAkademik->id,
                ]);
                $skipped++;
                continue;
            }

            // PENTING: academic_year_id dan semester TIDAK disertakan di payload updateOrCreate
            // karena model NilaiUjian secara otomatis mengisinya via saving hook dari $ujianAkademik
            NilaiUjian::updateOrCreate(
                [
                    'ujian_akademik_id' => $ujianAkademik->id,
                    'student_id'        => $siswa->id,
                ],
                [
                    'class_id'          => $classId,
                    'nilai_akhir'       => $item['score'] ?? $item['nilai_akhir'] ?? 0,
                    'jumlah_benar'      => $item['jumlah_benar'] ?? 0,
                    'jumlah_salah'      => $item['jumlah_salah'] ?? 0,
                    'jumlah_kosong'     => $item['jumlah_kosong'] ?? 0,
                    'violation_count'   => $item['violation_count'] ?? 0,
                    'started_at'        => !empty($item['started_at']) ? $item['started_at'] : null,
                    'submitted_at'      => !empty($item['submitted_at']) ? $item['submitted_at'] : null,
                    'synced_at'         => now(),
                ]
            );

            $synced++;
        }

        return [
            'total_results' => count($results),
            'synced'        => $synced,
            'skipped'       => $skipped,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function setStudentSession(string $nisn, string $session): array
    {
        $cleanNisn = trim($nisn);
        $response = $this->send('POST', "/api/v1/students/{$cleanNisn}/set-session", [
            'session' => $session,
        ]);

        // Sinkronkan cbt_sesi lokal di student_cbt_profiles
        $siswa = Siswa::where('nisn', $cleanNisn)->first();
        if ($siswa) {
            $newSession = $response['new_session'] ?? $session;
            $siswa->cbtProfile()->updateOrCreate(
                ['student_id' => $siswa->id],
                ['cbt_sesi' => $newSession]
            );
        }

        return $response;
    }

    /**
     * {@inheritDoc}
     */
    public function resetStudentSession(string $nisn): array
    {
        $cleanNisn = trim($nisn);
        return $this->send('POST', "/api/v1/students/{$cleanNisn}/reset-session");
    }

    /**
     * {@inheritDoc}
     *
     * Logika Ekspansi Tingkat:
     * Guru yang mengajar di kelas 7A akan mendapat akses ke SEMUA kelas
     * yang berada di tingkat yang sama (7A, 7B, 7C, dst) di ZenCBT,
     * sehingga bisa membuat dan menjadwalkan ujian untuk seluruh tingkat.
     */
    public function syncTeachers(?array $guruIds = null): array
    {
        $query = \App\Models\Guru::query();
        if (! empty($guruIds)) {
            $query->whereIn('id', $guruIds);
        }

        $gurus = $query->with(['pengajarans.kelasAjaran.kelas'])->get();

        if ($gurus->isEmpty()) {
            return ['total_processed' => 0, 'inserted' => 0, 'updated' => 0,
                    'message' => 'Tidak ada data guru yang ditemukan.'];
        }

        $activeYear = TahunAjaran::where('status', 'aktif')->first();
        $payload    = [];

        foreach ($gurus as $guru) {
            // ─── Hitung password default ──────────────────────────────────
            if (! empty(trim($guru->nip ?? ''))) {
                $password = trim($guru->nip) . '@03';
            } else {
                $password = str_replace(' ', '', $guru->name);
            }

            // ─── Logika Ekspansi Tingkat ──────────────────────────────────
            // 1. Cari grade_level unik dari semua kelas yang diajar guru ini
            $gradeLevels = $guru->pengajarans
                ->map(fn ($p) => $p->kelasAjaran?->kelas?->grade_level)
                ->filter()
                ->unique()
                ->values()
                ->toArray();

            // 2. Dapatkan SEMUA kelas dalam grade_level tersebut
            $groupCodes = [];
            if (! empty($gradeLevels)) {
                $groupCodes = Kelas::whereIn('grade_level', $gradeLevels)
                    ->pluck('name')
                    ->toArray();
            }

            // ─── Email sebagai identifier unik di ZenCBT ─────────────────
            $email = $guru->user?->email ?? (str_replace(' ', '', strtolower($guru->name)) . '@cbt.local');

            $payload[] = [
                'identifier' => trim($guru->nip ?? str_replace(' ', '', $guru->name)),
                'email'      => $email,
                'name'       => $guru->name,
                'password'   => $password,
                'role'       => 'guru',
                'groups'     => $groupCodes,
            ];
        }

        $response = $this->send('POST', '/api/v1/sync/teachers', [
            'teachers' => $payload,
        ]);

        return [
            'total_processed' => count($payload),
            'inserted'        => $response['inserted'] ?? 0,
            'updated'         => $response['updated']  ?? 0,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function resetTeacherPassword(string $guruId): array
    {
        $guru = \App\Models\Guru::findOrFail($guruId);
        $email = $guru->user?->email ?? (str_replace(' ', '', strtolower($guru->name)) . '@cbt.local');

        if (! empty(trim($guru->nip ?? ''))) {
            $password = trim($guru->nip) . '@03';
        } else {
            $password = str_replace(' ', '', $guru->name);
        }

        return $this->send('POST', '/api/v1/teachers/reset-password', [
            'email'    => $email,
            'password' => $password,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function updateTeacherPassword(string $guruId, string $password): array
    {
        $guru  = \App\Models\Guru::findOrFail($guruId);
        $email = $guru->user?->email ?? (str_replace(' ', '', strtolower($guru->name)) . '@cbt.local');

        return $this->send('POST', '/api/v1/teachers/reset-password', [
            'email'    => $email,
            'password' => $password,
        ]);
    }
}
