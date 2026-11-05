<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Exports\JurnalMengajarExport;
use App\Http\Controllers\Controller;
use App\Models\EnrollmentSiswa;
use App\Models\Guru;
use App\Models\JurnalPembelajaran;
use App\Models\JurnalPembelajaranPresensi;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Pengajaran;
use App\Models\PengaturanSekolah;
use App\Models\Presensi;
use App\Models\TahunAjaran;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class GuruJurnalMengajarController extends Controller
{
    /**
     * Resolusi user yang terautentikasi (Sanctum Token / Fallback Base64 Token / Identifier Header / Query).
     */
    protected function resolveUser(Request $request): ?User
    {
        try {
            $sanctumUser = auth('sanctum')->user();
            if ($sanctumUser instanceof User) {
                return $sanctumUser;
            }
        } catch (\Throwable $e) {
            // Abaikan jika tabel personal_access_tokens belum dimigrasi
        }

        $bearer = $request->bearerToken() ?: (string) $request->input('token', '');
        if (!empty($bearer) && !str_contains($bearer, '|')) {
            $decoded = base64_decode($bearer, true);
            if ($decoded && str_contains($decoded, '|')) {
                $parts = explode('|', $decoded);
                $userId = $parts[0] ?? null;
                if ($userId) {
                    $user = User::find($userId);
                    if ($user) {
                        return $user;
                    }
                }
            }
        }

        $identifier = trim((string) ($request->header('X-User-Identifier') ?: $request->input('identifier', '')));
        if ($identifier !== '') {
            $guru = Guru::where('nip', $identifier)
                ->orWhere('name', $identifier)
                ->first();
            if ($guru) {
                if ($guru->user_id) {
                    $user = User::find($guru->user_id);
                    if ($user) {
                        return $user;
                    }
                }
                $userByTeacher = User::where('teacher_id', $guru->id)->first();
                if ($userByTeacher) {
                    return $userByTeacher;
                }
            }

            $user = User::where('email', $identifier)
                ->orWhere('name', $identifier)
                ->orWhere('email', 'like', $identifier . '@%')
                ->first();
            if ($user) {
                return $user;
            }
        }

        return null;
    }

    /**
     * Cek apakah user memiliki hak akses Supervisi Jurnal.
     */
    protected function checkIsSupervisor(?User $user): bool
    {
        if (!$user) {
            return false;
        }
        try {
            return $user->isSuperAdmin() || $user->hasPermissionTo('portal_guru:supervisi_jurnal');
        } catch (\Throwable $e) {
            return $user->isSuperAdmin();
        }
    }

    /**
     * Ambil satu-satunya Tahun Ajaran Aktif (terkunci).
     */
    protected function getActiveAcademicYear(): ?TahunAjaran
    {
        $sekolah = PengaturanSekolah::current();
        if ($sekolah?->academic_year_id_active) {
            $active = TahunAjaran::find($sekolah->academic_year_id_active);
            if ($active) {
                return $active;
            }
        }

        return TahunAjaran::where('status', 'aktif')->first()
            ?? TahunAjaran::orderBy('start_year', 'desc')->first();
    }

    /**
     * Generate daftar bulan kalender akademik (Juli - Juni) untuk Tahun Ajaran terkait.
     */
    protected function buildAvailableMonths(?TahunAjaran $ay): array
    {
        $monthsMap = [];
        $monthsList = [];

        $sy = $ay?->start_year ?: (int) date('Y');
        $ey = $ay?->end_year ?: ($sy + 1);

        $monthNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        for ($m = 7; $m <= 12; $m++) {
            $key = sprintf('%02d-%d', $m, $sy);
            $label = $monthNames[$m] . ' ' . $sy;
            $monthsMap[$key] = $label;
            $monthsList[] = ['key' => $key, 'label' => $label];
        }

        for ($m = 1; $m <= 6; $m++) {
            $key = sprintf('%02d-%d', $m, $ey);
            $label = $monthNames[$m] . ' ' . $ey;
            $monthsMap[$key] = $label;
            $monthsList[] = ['key' => $key, 'label' => $label];
        }

        return [
            'map' => $monthsMap,
            'list' => $monthsList,
        ];
    }

    /**
     * Ambil daftar Mata Pelajaran & Kelas yang diampu guru pada Tahun Ajaran tertentu.
     */
    protected function getTeacherMapelAndClasses(?string $teacherId, ?string $academicYearId, ?int $selectedMapelId = null): array
    {
        if (!$teacherId || !$academicYearId) {
            return [
                'mapels' => [],
                'classes' => [],
                'all_teaching_classes' => [],
                'is_single_mapel' => false,
                'auto_mapel_id' => null,
            ];
        }

        $pengajarans = Pengajaran::where('teacher_id', $teacherId)
            ->whereHas('kelasAjaran', fn($q) => $q->where('academic_year_id', $academicYearId))
            ->with(['mataPelajaran', 'kelasAjaran.kelas'])
            ->get();

        $mapelsCollection = $pengajarans->pluck('mataPelajaran')->unique('id')->filter()->values();
        $mapels = $mapelsCollection->map(fn($m) => [
            'id' => (int) $m->id,
            'nama_mapel' => $m->nama_mapel,
            'kode_mapel' => $m->kode_mapel ?? '',
        ])->values()->toArray();

        $isSingleMapel = count($mapels) === 1;
        $autoMapelId = $isSingleMapel ? (int) $mapels[0]['id'] : null;
        $effectiveMapelId = $selectedMapelId ?: $autoMapelId;

        $allTeachingClasses = [];
        foreach ($pengajarans as $p) {
            $kelas = $p->kelasAjaran?->kelas;
            if (!$kelas || !$p->mata_pelajaran_id) {
                continue;
            }
            $allTeachingClasses[] = [
                'id' => (string) $kelas->id,
                'name' => $kelas->name,
                'grade' => $kelas->grade ?? null,
                'mata_pelajaran_id' => (int) $p->mata_pelajaran_id,
                'mata_pelajaran_name' => $p->mataPelajaran?->nama_mapel ?? '-',
            ];
        }

        $allTeachingClasses = collect($allTeachingClasses)
            ->unique(fn($item) => $item['id'] . '_' . $item['mata_pelajaran_id'])
            ->sortBy('name')
            ->values()
            ->toArray();

        if ($effectiveMapelId) {
            $classes = collect($allTeachingClasses)
                ->where('mata_pelajaran_id', (int) $effectiveMapelId)
                ->unique('id')
                ->sortBy('name')
                ->values()
                ->toArray();
        } else {
            $classes = collect($allTeachingClasses)
                ->unique('id')
                ->sortBy('name')
                ->values()
                ->toArray();
        }

        return [
            'mapels' => $mapels,
            'classes' => $classes,
            'all_teaching_classes' => $allTeachingClasses,
            'is_single_mapel' => $isSingleMapel,
            'auto_mapel_id' => $autoMapelId,
        ];
    }

    /**
     * Format tanggal ke Bahasa Indonesia (Contoh: Kamis, 29 Oktober 2026).
     */
    protected function formatTanggalIndonesia($date): string
    {
        if (function_exists('hari_tanggal_indonesia')) {
            return hari_tanggal_indonesia($date);
        }
        return Carbon::parse($date)->locale('id')->translatedFormat('l, d F Y');
    }

    /**
     * Hitung label jam pelajaran resmi sesuai aturan JurnalMengajarForm.php.
     */
    protected function buildJamKeLabel(int $jamMulai, int $durasiJp): array
    {
        $mulai = max(1, min(9, $jamMulai));
        $durasi = max(1, min(5, $durasiJp));
        $selesai = $mulai + $durasi - 1;

        $label = $durasi === 1
            ? "Jam ke {$mulai} (1 JP)"
            : "Jam ke {$mulai} - {$selesai} ({$durasi} JP)";

        return [
            'jam_mulai' => $mulai,
            'durasi_jp' => $durasi,
            'jam_selesai' => $selesai,
            'jam_ke_label' => $label,
            'durasi_menit' => $durasi * 40,
        ];
    }

    /**
     * GET /api/v1/guru/jurnal-mengajar
     * Daftar riwayat jurnal mengajar, statistik ringkasan, analisa kelas & siswa, dan opsi filter.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json([
                'status' => false,
                'success' => false,
                'message' => 'Tidak terautentikasi. Silakan login kembali.',
            ], 401);
        }

        $isSupervisor = $this->checkIsSupervisor($user);
        $currentTeacher = $user->teacher ?? Guru::where('user_id', $user->id)->first();

        if (!$isSupervisor && !$currentTeacher) {
            return response()->json([
                'status' => false,
                'success' => false,
                'message' => 'Akses ditolak: Anda tidak memiliki profil guru.',
            ], 403);
        }

        $activeYear = $this->getActiveAcademicYear();
        $academicYearId = $activeYear?->id;

        $teachersList = [];
        if ($isSupervisor) {
            $teachersList = Guru::orderBy('name', 'asc')
                ->get(['id', 'name', 'nip'])
                ->map(fn($t) => [
                    'id' => (string) $t->id,
                    'name' => $t->name,
                    'nip' => $t->nip ?? '-',
                ])->values()->toArray();

            $selectedTeacherId = $request->input('teacher_id')
                ?: ($currentTeacher?->id ?? ($teachersList[0]['id'] ?? null));
        } else {
            $selectedTeacherId = $currentTeacher?->id;
        }

        $monthsData = $this->buildAvailableMonths($activeYear);
        $availableMonthsMap = $monthsData['map'];

        $rawMonthYear = $request->input('month_year');
        if ($rawMonthYear === null) {
            $currentMonthYear = date('m-Y');
            $selectedMonthYear = array_key_exists($currentMonthYear, $availableMonthsMap)
                ? $currentMonthYear
                : (array_key_first($availableMonthsMap) ?? $currentMonthYear);
        } elseif ($rawMonthYear === 'all' || $rawMonthYear === '') {
            $selectedMonthYear = '';
        } else {
            $selectedMonthYear = (string) $rawMonthYear;
        }

        $reqMapelId = $request->filled('mata_pelajaran_id') ? (int) $request->input('mata_pelajaran_id') : null;
        $teachingData = $this->getTeacherMapelAndClasses($selectedTeacherId, $academicYearId, $reqMapelId);

        $selectedMapelId = $reqMapelId ?: $teachingData['auto_mapel_id'];
        $selectedClassId = $request->filled('class_id') ? (string) $request->input('class_id') : null;
        $search = trim((string) $request->input('search', ''));
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $perPage = max(5, min(50, (int) $request->input('per_page', 15)));

        $query = JurnalPembelajaran::query()
            ->with(['guru', 'kelas', 'mataPelajaran', 'tahunAjaran'])
            ->when($academicYearId, fn($q) => $q->where('academic_year_id', $academicYearId))
            ->when($selectedTeacherId, fn($q) => $q->where('teacher_id', $selectedTeacherId))
            ->when($selectedMapelId, fn($q) => $q->where('mata_pelajaran_id', $selectedMapelId))
            ->when($selectedClassId, fn($q) => $q->where('class_id', $selectedClassId))
            ->when($selectedMonthYear !== '', function ($q) use ($selectedMonthYear) {
                $parts = explode('-', $selectedMonthYear);
                if (count($parts) === 2) {
                    $q->whereMonth('tanggal', (int) $parts[0])
                        ->whereYear('tanggal', (int) $parts[1]);
                }
            })
            ->when($selectedMonthYear === '' && $dateFrom, fn($q) => $q->whereDate('tanggal', '>=', $dateFrom))
            ->when($selectedMonthYear === '' && $dateTo, fn($q) => $q->whereDate('tanggal', '<=', $dateTo))
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('materi', 'like', '%' . $search . '%')
                        ->orWhere('uraian_materi', 'like', '%' . $search . '%')
                        ->orWhere('refleksi', 'like', '%' . $search . '%')
                        ->orWhere('jam_ke_label', 'like', '%' . $search . '%');
                });
            })
            ->orderBy('tanggal', 'desc')
            ->orderBy('jam_mulai', 'desc');

        $statsQuery = clone $query;
        $totalSesi = (int) $statsQuery->count();
        $totalJp = (int) (clone $statsQuery)->sum('durasi_jp');
        $totalSiswaTercatat = (int) (clone $statsQuery)->sum('total_siswa');
        $totalSiswaHadir = (int) (clone $statsQuery)->sum('total_hadir');
        $rataKehadiran = $totalSiswaTercatat > 0
            ? round(($totalSiswaHadir / $totalSiswaTercatat) * 100, 1)
            : 0.0;

        $todayStr = Carbon::today('Asia/Jakarta')->toDateString();
        $jurnalHariIniCount = JurnalPembelajaran::query()
            ->when($academicYearId, fn($q) => $q->where('academic_year_id', $academicYearId))
            ->when($selectedTeacherId, fn($q) => $q->where('teacher_id', $selectedTeacherId))
            ->whereDate('tanggal', $todayStr)
            ->count();

        $paginator = $query->paginate($perPage);

        $jurnals = collect($paginator->items())->map(function (JurnalPembelajaran $jurnal) use ($isSupervisor, $user, $currentTeacher) {
            $canEdit = !$isSupervisor || $user->isSuperAdmin() || ($currentTeacher && $jurnal->teacher_id === $currentTeacher->id);

            return [
                'id' => (string) $jurnal->id,
                'tanggal' => $jurnal->tanggal ? $jurnal->tanggal->format('Y-m-d') : '-',
                'tanggal_formatted' => $jurnal->tanggal ? $this->formatTanggalIndonesia($jurnal->tanggal) : '-',
                'jam_mulai' => (int) $jurnal->jam_mulai,
                'durasi_jp' => (int) $jurnal->durasi_jp,
                'jam_selesai' => (int) $jurnal->jam_selesai,
                'jam_ke_label' => $jurnal->jam_ke_label ?: "Jam ke {$jurnal->jam_mulai} ({$jurnal->durasi_jp} JP)",
                'durasi_menit' => ((int) $jurnal->durasi_jp) * 40,
                'class_id' => (string) $jurnal->class_id,
                'class_name' => $jurnal->kelas?->name ?? '-',
                'mata_pelajaran_id' => (int) $jurnal->mata_pelajaran_id,
                'mata_pelajaran_name' => $jurnal->mataPelajaran?->nama_mapel ?? '-',
                'teacher_id' => (string) $jurnal->teacher_id,
                'teacher_name' => $jurnal->guru?->name ?? '-',
                'materi' => $jurnal->materi ?? '',
                'uraian_materi' => $jurnal->uraian_materi ?? '',
                'refleksi' => $jurnal->refleksi ?? '',
                'total_siswa' => (int) $jurnal->total_siswa,
                'total_hadir' => (int) $jurnal->total_hadir,
                'total_tidak_mengikuti' => (int) $jurnal->total_tidak_mengikuti,
                'persentase_kehadiran' => (float) $jurnal->persentase_kehadiran,
                'can_edit' => $canEdit,
            ];
        })->values()->toArray();

        $analytics = $this->buildAnalyticsData(
            $academicYearId,
            $selectedTeacherId,
            $selectedMapelId,
            $selectedMonthYear,
            $dateFrom,
            $dateTo,
            $teachingData['classes']
        );

        return response()->json([
            'status' => true,
            'success' => true,
            'message' => 'Daftar jurnal mengajar berhasil dimuat.',
            'data' => [
                'academic_year' => [
                    'id' => (string) ($activeYear?->id ?? ''),
                    'name' => $activeYear?->name ?? 'Tahun Aktif',
                ],
                'is_supervisor' => $isSupervisor,
                'current_teacher' => [
                    'id' => (string) ($currentTeacher?->id ?? ''),
                    'name' => $currentTeacher?->name ?? $user->name,
                ],
                'selected_teacher_id' => (string) ($selectedTeacherId ?? ''),
                'teachers' => $teachersList,
                'selected_month_year' => $selectedMonthYear,
                'available_months' => $monthsData['map'],
                'available_months_list' => $monthsData['list'],
                'is_single_mapel' => $teachingData['is_single_mapel'],
                'selected_mapel_id' => $selectedMapelId,
                'available_mapels' => $teachingData['mapels'],
                'selected_class_id' => $selectedClassId,
                'available_classes' => $teachingData['classes'],
                'all_teaching_classes' => $teachingData['all_teaching_classes'],
                'summary' => [
                    'total_sesi' => $totalSesi,
                    'total_jp' => $totalJp,
                    'rata_kehadiran' => $rataKehadiran,
                    'jurnal_hari_ini_count' => $jurnalHariIniCount,
                ],
                'analytics' => $analytics,
                'jurnals' => $jurnals,
                'meta' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                ],
            ],
        ]);
    }

    /**
     * Bangun data Analisa Kelas & Kehadiran Siswa untuk Tab 2 Jurnal Mengajar.
     */
    protected function buildAnalyticsData(
        ?string $academicYearId,
        ?string $selectedTeacherId,
        ?int $selectedMapelId,
        string $selectedMonthYear,
        ?string $dateFrom,
        ?string $dateTo,
        array $teachingClasses
    ): array {
        $analyticsJurnals = JurnalPembelajaran::query()
            ->with(['kelas:id,name,grade', 'mataPelajaran:id,nama_mapel', 'presensis.siswa:id,name,nis,nisn'])
            ->when($academicYearId, fn($q) => $q->where('academic_year_id', $academicYearId))
            ->when($selectedTeacherId, fn($q) => $q->where('teacher_id', $selectedTeacherId))
            ->when($selectedMapelId, fn($q) => $q->where('mata_pelajaran_id', $selectedMapelId))
            ->when($selectedMonthYear !== '', function ($q) use ($selectedMonthYear) {
                $parts = explode('-', $selectedMonthYear);
                if (count($parts) === 2) {
                    $q->whereMonth('tanggal', (int) $parts[0])
                        ->whereYear('tanggal', (int) $parts[1]);
                }
            })
            ->when($selectedMonthYear === '' && $dateFrom, fn($q) => $q->whereDate('tanggal', '>=', $dateFrom))
            ->when($selectedMonthYear === '' && $dateTo, fn($q) => $q->whereDate('tanggal', '<=', $dateTo))
            ->orderBy('tanggal', 'desc')
            ->orderBy('jam_mulai', 'desc')
            ->get();

        $overallTotals = [
            'total_sesi' => 0,
            'total_jp' => 0,
            'hadir' => 0,
            'izin' => 0,
            'sakit' => 0,
            'izin_sakit' => 0,
            'alpa' => 0,
            'tidak_mengikuti' => 0,
            'total_absen' => 0,
        ];

        $classesMap = [];
        foreach ($teachingClasses as $tc) {
            $cid = (string) ($tc['id'] ?? '');
            if ($cid !== '' && !isset($classesMap[$cid])) {
                $classesMap[$cid] = [
                    'class_id' => $cid,
                    'class_name' => $tc['name'] ?? '-',
                    'total_sesi' => 0,
                    'total_jp' => 0,
                    'hadir' => 0,
                    'izin' => 0,
                    'sakit' => 0,
                    'izin_sakit' => 0,
                    'alpa' => 0,
                    'tidak_mengikuti' => 0,
                    'total_absen' => 0,
                    'siswa_pernah_absen_count' => 0,
                ];
            }
        }

        $studentsMap = [];

        foreach ($analyticsJurnals as $jurnal) {
            $cid = (string) $jurnal->class_id;
            $cname = $jurnal->kelas?->name ?? '-';
            $jp = (int) $jurnal->durasi_jp;
            $tanggalStr = $jurnal->tanggal ? $jurnal->tanggal->format('Y-m-d') : '-';
            $tanggalFormatted = $jurnal->tanggal ? $this->formatTanggalIndonesia($jurnal->tanggal) : '-';
            $jamKeLabel = $jurnal->jam_ke_label ?: "Jam ke {$jurnal->jam_mulai} ({$jp} JP)";
            $mapelName = $jurnal->mataPelajaran?->nama_mapel ?? '-';

            $overallTotals['total_sesi']++;
            $overallTotals['total_jp'] += $jp;

            if (!isset($classesMap[$cid])) {
                $classesMap[$cid] = [
                    'class_id' => $cid,
                    'class_name' => $cname,
                    'total_sesi' => 0,
                    'total_jp' => 0,
                    'hadir' => 0,
                    'izin' => 0,
                    'sakit' => 0,
                    'izin_sakit' => 0,
                    'alpa' => 0,
                    'tidak_mengikuti' => 0,
                    'total_absen' => 0,
                    'siswa_pernah_absen_count' => 0,
                ];
            }

            $classesMap[$cid]['total_sesi']++;
            $classesMap[$cid]['total_jp'] += $jp;

            foreach ($jurnal->presensis as $p) {
                $siswa = $p->siswa;
                $sid = (string) $p->student_id;
                if ($sid === '') {
                    continue;
                }

                $statusJurnal = strtolower((string) $p->status_jurnal);
                $statusGerbang = strtolower((string) ($p->status_gerbang_snapshot ?? ''));

                if ($statusJurnal === 'tidak_mengikuti') {
                    if ($statusGerbang === 'sakit') {
                        $normStatus = 'sakit';
                        $statusLabel = 'Sakit';
                    } elseif ($statusGerbang === 'izin') {
                        $normStatus = 'izin';
                        $statusLabel = 'Izin';
                    } elseif ($statusGerbang === 'alpa') {
                        $normStatus = 'alpa';
                        $statusLabel = 'Alpa';
                    } else {
                        $normStatus = 'tidak_mengikuti';
                        $statusLabel = 'Tidak Mengikuti';
                    }
                } else {
                    $normStatus = 'hadir';
                    $statusLabel = 'Hadir';
                }

                $overallTotals[$normStatus]++;
                $classesMap[$cid][$normStatus]++;

                $studentKey = $cid . '_' . $sid;
                if (!isset($studentsMap[$studentKey])) {
                    $studentsMap[$studentKey] = [
                        'student_id' => $sid,
                        'student_name' => $siswa?->name ?? 'Siswa',
                        'nis' => $siswa?->nis ?? $siswa?->nisn ?? '-',
                        'class_id' => $cid,
                        'class_name' => $cname,
                        'total_pertemuan' => 0,
                        'hadir' => 0,
                        'izin' => 0,
                        'sakit' => 0,
                        'izin_sakit' => 0,
                        'alpa' => 0,
                        'tidak_mengikuti' => 0,
                        'total_absen' => 0,
                        'absence_logs' => [],
                    ];
                }

                $studentsMap[$studentKey]['total_pertemuan']++;
                $studentsMap[$studentKey][$normStatus]++;

                if ($normStatus !== 'hadir') {
                    $studentsMap[$studentKey]['absence_logs'][] = [
                        'jurnal_id' => (string) $jurnal->id,
                        'tanggal' => $tanggalStr,
                        'tanggal_formatted' => $tanggalFormatted,
                        'jam_ke_label' => $jamKeLabel,
                        'mata_pelajaran_name' => $mapelName,
                        'materi' => $jurnal->materi ?? '-',
                        'status' => $normStatus,
                        'status_label' => $statusLabel,
                        'status_gerbang' => $statusGerbang ?: 'belum_absen',
                        'keterangan' => $p->keterangan ?? '',
                    ];
                }
            }
        }

        $overallTotals['izin_sakit'] = $overallTotals['izin'] + $overallTotals['sakit'];
        $overallTotals['total_absen'] = $overallTotals['izin_sakit'] + $overallTotals['alpa'] + $overallTotals['tidak_mengikuti'];

        $studentsList = array_values($studentsMap);
        foreach ($studentsList as &$st) {
            $st['izin_sakit'] = $st['izin'] + $st['sakit'];
            $st['total_absen'] = $st['izin_sakit'] + $st['alpa'] + $st['tidak_mengikuti'];
            if ($st['total_absen'] > 0 && isset($classesMap[$st['class_id']])) {
                $classesMap[$st['class_id']]['siswa_pernah_absen_count']++;
            }
        }
        unset($st);

        foreach ($classesMap as &$cm) {
            $cm['izin_sakit'] = $cm['izin'] + $cm['sakit'];
            $cm['total_absen'] = $cm['izin_sakit'] + $cm['alpa'] + $cm['tidak_mengikuti'];
        }
        unset($cm);

        usort($studentsList, function (array $a, array $b) {
            if ($a['tidak_mengikuti'] !== $b['tidak_mengikuti']) {
                return $b['tidak_mengikuti'] <=> $a['tidak_mengikuti'];
            }
            if ($a['alpa'] !== $b['alpa']) {
                return $b['alpa'] <=> $a['alpa'];
            }
            if ($a['total_absen'] !== $b['total_absen']) {
                return $b['total_absen'] <=> $a['total_absen'];
            }
            return strcasecmp($a['student_name'], $b['student_name']);
        });

        $classesList = array_values($classesMap);
        usort($classesList, fn(array $a, array $b) => strnatcasecmp($a['class_name'], $b['class_name']));

        return [
            'totals' => $overallTotals,
            'classes_summary' => $classesList,
            'students_summary' => $studentsList,
        ];
    }

    /**
     * GET /api/v1/guru/jurnal-mengajar/form-options
     */
    public function formOptions(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json([
                'status' => false,
                'success' => false,
                'message' => 'Tidak terautentikasi.',
            ], 401);
        }

        $isSupervisor = $this->checkIsSupervisor($user);
        $currentTeacher = $user->teacher ?? Guru::where('user_id', $user->id)->first();

        if (!$isSupervisor && !$currentTeacher) {
            return response()->json([
                'status' => false,
                'success' => false,
                'message' => 'Akses ditolak: Akun Anda tidak memiliki profil guru.',
            ], 403);
        }

        $activeYear = $this->getActiveAcademicYear();
        $academicYearId = $activeYear?->id;

        $teacherId = $request->input('teacher_id') ?: $currentTeacher?->id;
        $teacherModel = $teacherId ? Guru::find($teacherId) : $currentTeacher;

        $reqMapelId = $request->filled('mata_pelajaran_id') ? (int) $request->input('mata_pelajaran_id') : null;
        $teachingData = $this->getTeacherMapelAndClasses($teacherModel?->id, $academicYearId, $reqMapelId);
        $monthsData = $this->buildAvailableMonths($activeYear);

        $today = Carbon::today('Asia/Jakarta')->toDateString();

        return response()->json([
            'status' => true,
            'success' => true,
            'data' => [
                'academic_year' => [
                    'id' => (string) ($activeYear?->id ?? ''),
                    'name' => $activeYear?->name ?? 'Tahun Aktif',
                ],
                'teacher' => [
                    'id' => (string) ($teacherModel?->id ?? ''),
                    'name' => $teacherModel?->name ?? $user->name,
                ],
                'is_supervisor' => $isSupervisor,
                'is_single_mapel' => $teachingData['is_single_mapel'],
                'auto_mapel_id' => $teachingData['auto_mapel_id'],
                'available_mapels' => $teachingData['mapels'],
                'available_classes' => $teachingData['all_teaching_classes'],
                'available_months' => $monthsData['map'],
                'available_months_list' => $monthsData['list'],
                'today' => $today,
                'today_formatted' => $this->formatTanggalIndonesia($today),
                'default_jam_mulai' => 1,
                'default_durasi_jp' => 2,
            ],
        ]);
    }

    /**
     * GET /api/v1/guru/jurnal-mengajar/students
     */
    public function students(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json([
                'status' => false,
                'success' => false,
                'message' => 'Tidak terautentikasi.',
            ], 401);
        }

        $classId = $request->input('class_id');
        $tanggal = $request->input('tanggal') ?: Carbon::today('Asia/Jakarta')->toDateString();
        $jurnalId = $request->input('jurnal_id');

        if (!$classId) {
            return response()->json([
                'status' => false,
                'success' => false,
                'message' => 'Pilih Kelas Rombel terlebih dahulu.',
            ], 422);
        }

        $activeYear = $this->getActiveAcademicYear();
        $academicYearId = $activeYear?->id;

        $studentRows = $this->buildStudentAttendanceList($classId, $academicYearId, $tanggal, $jurnalId);

        return response()->json([
            'status' => true,
            'success' => true,
            'data' => [
                'class_id' => (string) $classId,
                'tanggal' => $tanggal,
                'tanggal_formatted' => $this->formatTanggalIndonesia($tanggal),
                'total_students' => count($studentRows),
                'students' => $studentRows,
            ],
        ]);
    }

    protected function buildStudentAttendanceList(string $classId, ?string $academicYearId, string $tanggal, ?string $jurnalId = null): array
    {
        $enrollments = EnrollmentSiswa::where('class_id', $classId)
            ->when($academicYearId, fn($q) => $q->where('academic_year_id', $academicYearId))
            ->where('status', 'aktif')
            ->with('student')
            ->get();

        $students = $enrollments->pluck('student')->filter()->sortBy('name')->values();
        $studentIds = $students->pluck('id')->toArray();

        $attendances = Presensi::whereIn('student_id', $studentIds)
            ->whereDate('date', $tanggal)
            ->get()
            ->keyBy('student_id');

        $savedPresensis = collect();
        if ($jurnalId) {
            $savedPresensis = JurnalPembelajaranPresensi::where('jurnal_pembelajaran_id', $jurnalId)
                ->get()
                ->keyBy('student_id');
        }

        $rows = [];
        foreach ($students as $siswa) {
            $saved = $savedPresensis->get($siswa->id);
            $att = $attendances->get($siswa->id);

            if ($saved) {
                $statusGerbang = $saved->status_gerbang_snapshot ?: ($att ? $att->status : 'belum_absen');
                $isLocked = in_array($statusGerbang, ['izin', 'sakit', 'alpa'], true);
                $statusJurnal = $isLocked ? 'tidak_mengikuti' : $saved->status_jurnal;
                $diabsenkanTelat = (bool) $saved->diabsenkan_telat_di_gerbang;
                $keterangan = $saved->keterangan ?? '';
            } else {
                $statusGerbang = $att ? $att->status : 'belum_absen';
                $isLocked = in_array($statusGerbang, ['izin', 'sakit', 'alpa'], true);

                if ($isLocked) {
                    $statusJurnal = 'tidak_mengikuti';
                    $keterangan = 'Tercatat ' . ucfirst($statusGerbang) . ' di gerbang pagi';
                } elseif ($statusGerbang === 'belum_absen') {
                    $statusJurnal = 'tidak_mengikuti';
                    $keterangan = '';
                } else {
                    $statusJurnal = 'hadir';
                    $keterangan = '';
                }
                $diabsenkanTelat = false;
            }

            $rows[] = [
                'student_id' => (string) $siswa->id,
                'nis' => $siswa->nis ?? $siswa->nisn ?? '-',
                'name' => $siswa->name,
                'gender' => $siswa->gender ?? null,
                'status_gerbang' => $statusGerbang,
                'status_jurnal' => $statusJurnal,
                'is_locked' => $isLocked,
                'diabsenkan_telat' => $diabsenkanTelat,
                'keterangan' => $keterangan,
            ];
        }

        if ($jurnalId && $savedPresensis->isNotEmpty() && empty($rows)) {
            $jurnal = JurnalPembelajaran::with('presensis.siswa')->find($jurnalId);
            if ($jurnal) {
                foreach ($jurnal->presensis as $presensi) {
                    $siswa = $presensi->siswa;
                    if (!$siswa) {
                        continue;
                    }
                    $statusGerbang = $presensi->status_gerbang_snapshot ?: 'belum_absen';
                    $isLocked = in_array($statusGerbang, ['izin', 'sakit', 'alpa'], true);
                    $rows[] = [
                        'student_id' => (string) $siswa->id,
                        'nis' => $siswa->nis ?? $siswa->nisn ?? '-',
                        'name' => $siswa->name,
                        'gender' => $siswa->gender ?? null,
                        'status_gerbang' => $statusGerbang,
                        'status_jurnal' => $presensi->status_jurnal,
                        'is_locked' => $isLocked,
                        'diabsenkan_telat' => (bool) $presensi->diabsenkan_telat_di_gerbang,
                        'keterangan' => $presensi->keterangan ?? '',
                    ];
                }
            }
        }

        return $rows;
    }

    /**
     * GET /api/v1/guru/jurnal-mengajar/{id}
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json([
                'status' => false,
                'success' => false,
                'message' => 'Tidak terautentikasi.',
            ], 401);
        }

        $jurnal = JurnalPembelajaran::with(['guru', 'kelas', 'mataPelajaran', 'tahunAjaran', 'presensis.siswa'])
            ->find($id);

        if (!$jurnal) {
            return response()->json([
                'status' => false,
                'success' => false,
                'message' => 'Data jurnal mengajar tidak ditemukan.',
            ], 404);
        }

        $teachingData = $this->getTeacherMapelAndClasses(
            $jurnal->teacher_id,
            $jurnal->academic_year_id,
            (int) $jurnal->mata_pelajaran_id
        );

        $tanggalStr = $jurnal->tanggal ? $jurnal->tanggal->format('Y-m-d') : date('Y-m-d');
        $students = $this->buildStudentAttendanceList(
            (string) $jurnal->class_id,
            (string) $jurnal->academic_year_id,
            $tanggalStr,
            (string) $jurnal->id
        );

        return response()->json([
            'status' => true,
            'success' => true,
            'data' => [
                'jurnal' => [
                    'id' => (string) $jurnal->id,
                    'academic_year_id' => (string) $jurnal->academic_year_id,
                    'academic_year_name' => $jurnal->tahunAjaran?->name ?? 'Tahun Aktif',
                    'teacher_id' => (string) $jurnal->teacher_id,
                    'teacher_name' => $jurnal->guru?->name ?? 'Guru Pengampu',
                    'tanggal' => $tanggalStr,
                    'tanggal_formatted' => $this->formatTanggalIndonesia($tanggalStr),
                    'class_id' => (string) $jurnal->class_id,
                    'class_name' => $jurnal->kelas?->name ?? '-',
                    'mata_pelajaran_id' => (int) $jurnal->mata_pelajaran_id,
                    'mata_pelajaran_name' => $jurnal->mataPelajaran?->nama_mapel ?? '-',
                    'jam_mulai' => (int) $jurnal->jam_mulai,
                    'durasi_jp' => (int) $jurnal->durasi_jp,
                    'jam_selesai' => (int) $jurnal->jam_selesai,
                    'jam_ke_label' => $jurnal->jam_ke_label,
                    'durasi_menit' => ((int) $jurnal->durasi_jp) * 40,
                    'materi' => $jurnal->materi ?? '',
                    'uraian_materi' => $jurnal->uraian_materi ?? '',
                    'refleksi' => $jurnal->refleksi ?? '',
                    'total_siswa' => (int) $jurnal->total_siswa,
                    'total_hadir' => (int) $jurnal->total_hadir,
                    'total_tidak_mengikuti' => (int) $jurnal->total_tidak_mengikuti,
                    'persentase_kehadiran' => (float) $jurnal->persentase_kehadiran,
                ],
                'is_single_mapel' => $teachingData['is_single_mapel'],
                'available_mapels' => $teachingData['mapels'],
                'available_classes' => $teachingData['all_teaching_classes'],
                'students' => $students,
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        return $this->saveJurnal($request, null);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        return $this->saveJurnal($request, $id);
    }

    protected function saveJurnal(Request $request, ?string $jurnalId = null): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json([
                'status' => false,
                'success' => false,
                'message' => 'Tidak terautentikasi.',
            ], 401);
        }

        $isSupervisor = $this->checkIsSupervisor($user);
        $currentTeacher = $user->teacher ?? Guru::where('user_id', $user->id)->first();

        if (!$isSupervisor && !$currentTeacher) {
            return response()->json([
                'status' => false,
                'success' => false,
                'message' => 'Akses ditolak: Akun Anda tidak memiliki profil guru.',
            ], 403);
        }

        $validated = $request->validate([
            'tanggal' => 'required|date',
            'class_id' => 'required|exists:classes,id',
            'mata_pelajaran_id' => 'required|exists:mata_pelajarans,id',
            'jam_mulai' => 'required|integer|min:1|max:9',
            'durasi_jp' => 'required|integer|min:1|max:5',
            'materi' => 'required|string|max:255',
            'uraian_materi' => 'required|string|min:5',
            'refleksi' => 'nullable|string',
            'students' => 'nullable|array',
        ], [
            'tanggal.required' => 'Tanggal pembelajaran wajib diisi.',
            'class_id.required' => 'Pilih kelas rombongan belajar.',
            'mata_pelajaran_id.required' => 'Pilih mata pelajaran.',
            'jam_mulai.required' => 'Jam mulai wajib ditentukan.',
            'jam_mulai.min' => 'Jam mulai minimal jam ke-1.',
            'jam_mulai.max' => 'Jam mulai maksimal jam ke-9.',
            'durasi_jp.required' => 'Durasi JP wajib ditentukan.',
            'durasi_jp.min' => 'Durasi JP minimal 1 JP.',
            'durasi_jp.max' => 'Durasi JP maksimal 5 JP.',
            'materi.required' => 'Judul materi pokok pembelajaran wajib diisi.',
            'uraian_materi.required' => 'Uraian aktivitas KBM wajib diisi.',
            'uraian_materi.min' => 'Uraian aktivitas KBM minimal 5 karakter.',
        ]);

        $existingJurnal = null;
        if ($jurnalId) {
            $existingJurnal = JurnalPembelajaran::find($jurnalId);
            if (!$existingJurnal) {
                return response()->json([
                    'status' => false,
                    'success' => false,
                    'message' => 'Data jurnal mengajar tidak ditemukan.',
                ], 404);
            }

            if (!$isSupervisor && $existingJurnal->teacher_id !== $currentTeacher?->id) {
                return response()->json([
                    'status' => false,
                    'success' => false,
                    'message' => 'Anda tidak berwenang mengubah jurnal milik guru lain.',
                ], 403);
            }
        }

        $activeYear = $this->getActiveAcademicYear();
        $academicYearId = $existingJurnal?->academic_year_id ?: $activeYear?->id;
        $teacherId = $existingJurnal?->teacher_id
            ?: ($isSupervisor && $request->filled('teacher_id') ? $request->input('teacher_id') : $currentTeacher?->id);

        if (!$teacherId) {
            return response()->json([
                'status' => false,
                'success' => false,
                'message' => 'Guru pengampu tidak ditemukan.',
            ], 422);
        }

        $jpCalc = $this->buildJamKeLabel((int) $validated['jam_mulai'], (int) $validated['durasi_jp']);
        $tanggal = Carbon::parse($validated['tanggal'])->format('Y-m-d');
        $classId = (string) $validated['class_id'];

        $inputStudents = $request->input('students', []);
        if (empty($inputStudents)) {
            $inputStudents = $this->buildStudentAttendanceList($classId, $academicYearId, $tanggal, $jurnalId);
        }

        $studentIds = collect($inputStudents)->pluck('student_id')->filter()->toArray();
        $gateAttendances = Presensi::whereIn('student_id', $studentIds)
            ->whereDate('date', $tanggal)
            ->get()
            ->keyBy('student_id');

        DB::beginTransaction();
        try {
            $totalHadir = 0;
            $totalTidakMengikuti = 0;

            $normalizedStudents = [];
            foreach ($inputStudents as $item) {
                $studentId = $item['student_id'] ?? null;
                if (!$studentId) {
                    continue;
                }

                $att = $gateAttendances->get($studentId);
                $statusGerbang = $item['status_gerbang'] ?? ($att ? $att->status : 'belum_absen');
                $isLocked = in_array($statusGerbang, ['izin', 'sakit', 'alpa'], true);

                $reqStatusJurnal = $item['status_jurnal'] ?? 'hadir';
                $diabsenkanTelat = (bool) ($item['diabsenkan_telat'] ?? false);

                if ($isLocked) {
                    $statusJurnal = 'tidak_mengikuti';
                    $diabsenkanTelat = false;
                } elseif ($statusGerbang === 'belum_absen') {
                    if ($diabsenkanTelat || $reqStatusJurnal === 'hadir') {
                        $statusJurnal = 'hadir';
                        $diabsenkanTelat = true;
                    } else {
                        $statusJurnal = 'tidak_mengikuti';
                        $diabsenkanTelat = false;
                    }
                } else {
                    $statusJurnal = $reqStatusJurnal === 'tidak_mengikuti' ? 'tidak_mengikuti' : 'hadir';
                }

                if ($statusJurnal === 'hadir') {
                    $totalHadir++;
                } else {
                    $totalTidakMengikuti++;
                }

                $normalizedStudents[] = [
                    'student_id' => (string) $studentId,
                    'status_gerbang' => $statusGerbang,
                    'status_jurnal' => $statusJurnal,
                    'diabsenkan_telat' => $diabsenkanTelat,
                    'keterangan' => trim((string) ($item['keterangan'] ?? '')) ?: null,
                ];
            }

            $jurnalData = [
                'academic_year_id' => $academicYearId,
                'teacher_id' => $teacherId,
                'class_id' => $classId,
                'mata_pelajaran_id' => (int) $validated['mata_pelajaran_id'],
                'tanggal' => $tanggal,
                'jam_mulai' => $jpCalc['jam_mulai'],
                'durasi_jp' => $jpCalc['durasi_jp'],
                'jam_selesai' => $jpCalc['jam_selesai'],
                'jam_ke_label' => $jpCalc['jam_ke_label'],
                'materi' => trim($validated['materi']),
                'uraian_materi' => trim($validated['uraian_materi']),
                'refleksi' => isset($validated['refleksi']) && trim($validated['refleksi']) !== ''
                    ? trim($validated['refleksi'])
                    : null,
                'total_siswa' => count($normalizedStudents),
                'total_hadir' => $totalHadir,
                'total_tidak_mengikuti' => $totalTidakMengikuti,
                'created_by' => $user->id,
            ];

            if ($existingJurnal) {
                $existingJurnal->update($jurnalData);
                $existingJurnal->presensis()->delete();
                $jurnal = $existingJurnal;
            } else {
                $jurnal = JurnalPembelajaran::create($jurnalData);
            }

            foreach ($normalizedStudents as $item) {
                $studentId = $item['student_id'];
                $statusGerbang = $item['status_gerbang'];
                $statusJurnal = $item['status_jurnal'];
                $diabsenkanTelat = $item['diabsenkan_telat'];

                if ($statusGerbang === 'belum_absen' && ($diabsenkanTelat || $statusJurnal === 'hadir')) {
                    $diabsenkanTelat = true;

                    $enrollment = EnrollmentSiswa::where('student_id', $studentId)
                        ->where('academic_year_id', $academicYearId)
                        ->first();

                    Presensi::updateOrCreate(
                        [
                            'student_id' => $studentId,
                            'date' => $tanggal,
                        ],
                        [
                            'enrollment_id' => $enrollment?->id,
                            'class_id' => $classId,
                            'academic_year_id' => $academicYearId,
                            'status' => 'telat',
                            'late_minutes' => 1,
                            'is_manual_input' => true,
                            'manual_input_by_id' => $teacherId,
                            'manual_input_by_type' => Guru::class,
                            'source' => 'jurnal_guru',
                            'note' => 'Diabsenkan oleh guru mata pelajaran di Jurnal Pembelajaran',
                            'scan_time' => now('Asia/Jakarta')->toTimeString(),
                        ]
                    );
                }

                JurnalPembelajaranPresensi::create([
                    'jurnal_pembelajaran_id' => $jurnal->id,
                    'student_id' => $studentId,
                    'status_jurnal' => $statusJurnal,
                    'status_gerbang_snapshot' => $statusGerbang,
                    'keterangan' => $item['keterangan'],
                    'diabsenkan_telat_di_gerbang' => $diabsenkanTelat,
                ]);
            }

            DB::commit();

            try {
                app(\App\Services\JurnalMengajarNotificationService::class)->processPresensiJurnal($jurnal, $normalizedStudents);
            } catch (\Throwable $notifErr) {
                \Illuminate\Support\Facades\Log::error('GuruJurnalMengajarController: Gagal memproses notifikasi meninggalkan kelas: ' . $notifErr->getMessage());
            }

            return response()->json([
                'status' => true,
                'success' => true,
                'message' => $existingJurnal
                    ? 'Perubahan jurnal mengajar berhasil disimpan!'
                    : 'Jurnal mengajar berhasil disimpan!',
                'data' => [
                    'id' => (string) $jurnal->id,
                    'jam_ke_label' => $jurnal->jam_ke_label,
                    'total_siswa' => $jurnal->total_siswa,
                    'total_hadir' => $jurnal->total_hadir,
                    'total_tidak_mengikuti' => $jurnal->total_tidak_mengikuti,
                    'persentase_kehadiran' => $jurnal->persentase_kehadiran,
                ],
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'success' => false,
                'message' => 'Gagal menyimpan jurnal: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json([
                'status' => false,
                'success' => false,
                'message' => 'Tidak terautentikasi.',
            ], 401);
        }

        $jurnal = JurnalPembelajaran::find($id);
        if (!$jurnal) {
            return response()->json([
                'status' => false,
                'success' => false,
                'message' => 'Data jurnal mengajar tidak ditemukan.',
            ], 404);
        }

        $currentTeacher = $user->teacher ?? Guru::where('user_id', $user->id)->first();
        if (!$user->isSuperAdmin() && $jurnal->teacher_id !== $currentTeacher?->id) {
            return response()->json([
                'status' => false,
                'success' => false,
                'message' => 'Anda tidak memiliki hak untuk menghapus jurnal guru lain.',
            ], 403);
        }

        $jurnal->presensis()->delete();
        $jurnal->delete();

        return response()->json([
            'status' => true,
            'success' => true,
            'message' => 'Jurnal mengajar berhasil dihapus.',
        ]);
    }

    public function export(Request $request)
    {
        $user = $this->resolveUser($request);
        $isSupervisor = $this->checkIsSupervisor($user);
        $currentTeacher = $user?->teacher ?? ($user ? Guru::where('user_id', $user->id)->first() : null);

        $activeYear = $this->getActiveAcademicYear();
        $academicYearId = $activeYear?->id ?: $request->input('academic_year_id');
        $ay = $activeYear ?: ($academicYearId ? TahunAjaran::find($academicYearId) : null);

        $sy = $ay?->start_year ?: (int) date('Y');
        $ey = $ay?->end_year ?: ($sy + 1);

        $cetakJenis = strtolower((string) $request->input('jenis', 'bulanan'));
        $cetakBulanYear = (string) $request->input('month_year', date('m-Y'));
        $cetakSemester = strtolower((string) $request->input('semester', (int) date('m') >= 7 ? 'ganjil' : 'genap'));
        $format = strtolower((string) $request->input('format', 'pdf'));

        $teacherId = $isSupervisor && $request->filled('teacher_id')
            ? (string) $request->input('teacher_id')
            : ($currentTeacher?->id ?: (string) $request->input('teacher_id', ''));

        $mapelId = $request->filled('mata_pelajaran_id') ? (int) $request->input('mata_pelajaran_id') : null;
        $classId = $request->filled('class_id') ? (string) $request->input('class_id') : null;

        $monthNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        if ($cetakJenis === 'bulanan') {
            $parts = explode('-', $cetakBulanYear);
            $m = (int) ($parts[0] ?? date('m'));
            $y = (int) ($parts[1] ?? date('Y'));
            $start = Carbon::create($y, $m, 1)->startOfMonth();
            $end = Carbon::create($y, $m, 1)->endOfMonth();
            $periodeLabel = 'Bulan ' . ($monthNames[$m] ?? $m) . ' ' . $y;
        } elseif ($cetakJenis === 'semester') {
            if ($cetakSemester === 'ganjil') {
                $start = Carbon::create($sy, 7, 1)->startOfMonth();
                $end = Carbon::create($sy, 12, 31)->endOfMonth();
                $periodeLabel = 'Semester Ganjil TA ' . ($ay?->name ?? ($sy . '/' . $ey));
            } else {
                $start = Carbon::create($ey, 1, 1)->startOfMonth();
                $end = Carbon::create($ey, 6, 30)->endOfMonth();
                $periodeLabel = 'Semester Genap TA ' . ($ay?->name ?? ($sy . '/' . $ey));
            }
        } elseif ($cetakJenis === 'tahunan') {
            $start = Carbon::create($sy, 7, 1)->startOfMonth();
            $end = Carbon::create($ey, 6, 30)->endOfMonth();
            $periodeLabel = 'Tahun Ajaran ' . ($ay?->name ?? ($sy . '/' . $ey));
        } else {
            $dateFrom = $request->input('date_from');
            $dateTo = $request->input('date_to');
            $start = $dateFrom ? Carbon::parse($dateFrom) : null;
            $end = $dateTo ? Carbon::parse($dateTo) : null;
            $periodeLabel = ($start ? $start->format('d/m/Y') : 'Awal') . ' s.d. ' . ($end ? $end->format('d/m/Y') : 'Sekarang');
        }

        $startStr = $start?->toDateString();
        $endStr = $end?->toDateString();

        $guru = $teacherId ? Guru::find($teacherId) : null;
        $mapel = $mapelId ? MataPelajaran::find($mapelId) : null;
        $kelas = $classId ? Kelas::find($classId) : null;
        $sekolah = PengaturanSekolah::current();

        $jurnals = JurnalPembelajaran::query()
            ->with(['guru', 'kelas', 'mataPelajaran'])
            ->when($academicYearId, fn($q) => $q->where('academic_year_id', $academicYearId))
            ->when($teacherId, fn($q) => $q->where('teacher_id', $teacherId))
            ->when($mapelId, fn($q) => $q->where('mata_pelajaran_id', $mapelId))
            ->when($classId, fn($q) => $q->where('class_id', $classId))
            ->when($startStr, fn($q) => $q->whereDate('tanggal', '>=', $startStr))
            ->when($endStr, fn($q) => $q->whereDate('tanggal', '<=', $endStr))
            ->orderBy('tanggal', 'asc')
            ->orderBy('jam_mulai', 'asc')
            ->get();

        $parts = ['Jurnal_Mengajar'];
        if ($guru) {
            $parts[] = str_replace([' ', '/', '\\'], '_', $guru->name);
        }
        if ($kelas) {
            $parts[] = str_replace([' ', '/', '\\'], '_', $kelas->name);
        }
        $parts[] = str_replace([' ', '/', '\\'], '_', $periodeLabel);

        if ($format === 'excel') {
            $fileName = implode('_', $parts) . '.xlsx';
            return Excel::download(
                new JurnalMengajarExport(
                    $jurnals,
                    $guru,
                    $ay,
                    $mapel,
                    $kelas,
                    $sekolah,
                    $periodeLabel
                ),
                $fileName
            );
        }

        $fileName = implode('_', $parts) . '.pdf';
        $pdf = Pdf::loadView('pdf.jurnal-mengajar-rekap', [
            'jurnals' => $jurnals,
            'guru' => $guru,
            'tahunAjaran' => $ay,
            'mapel' => $mapel,
            'kelas' => $kelas,
            'sekolah' => $sekolah,
            'dateFrom' => $startStr,
            'dateTo' => $endStr,
            'periodeLabel' => $periodeLabel,
        ])->setPaper('a4', 'landscape');

        return $pdf->download($fileName);
    }
}
