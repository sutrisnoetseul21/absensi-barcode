<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Exports\RekapSholatDhuhurHarianExport;
use App\Http\Controllers\Controller;
use App\Models\EnrollmentSiswa;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Pengajaran;
use App\Models\PengaturanSekolah;
use App\Models\Presensi;
use App\Models\PresensiNotificationSetting;
use App\Models\PresensiSholatDhuhur;
use App\Models\TahunAjaran;
use App\Models\User;
use App\Services\PresensiSholatDhuhurService;
use App\Services\WhatsAppGatewayService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class GuruSholatDhuhurController extends Controller
{
    /**
     * Resolusi user yang terautentikasi (Sanctum Token / Fallback Base64 Token / Identifier Header).
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

        $bearer = $request->bearerToken();
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
     * Cek apakah fitur Presensi Sholat Dhuhur aktif di /admin/pengaturan-presensi.
     */
    protected function isSholatDhuhurEnabled(): bool
    {
        $sekolah = PengaturanSekolah::current();
        return (bool) ($sekolah?->enable_sholat_dhuhur ?? true);
    }

    /**
     * Ambil satu-satunya Tahun Ajaran Aktif (terkunci).
     */
    protected function getActiveAcademicYear(): ?TahunAjaran
    {
        return TahunAjaran::where('status', 'aktif')
            ->orderBy('start_year', 'desc')
            ->first()
            ?: TahunAjaran::orderBy('start_year', 'desc')->first();
    }

    /**
     * Tentukan daftar kelas binaan yang berhak diakses oleh Guru pada menu Sholat Dhuhur
     * mengacu pada pengaturan /admin/manajemen-akses-portal/{id}/edit:
     * 1. Wali Kelas Utama (tabel class_academic_year / relasi kelasAjarans) -> 1 atau 2 kelas
     * 2. Kelas Binaan BK (tabel teacher_class_accesses / relasi kelasPantau) -> kelas yang dipilih Admin
     * 3. Bypass Semua Kelas (permission portal_guru:akses_semua_kelas atau Super Admin / Admin Presensi)
     */
    protected function getAccessibleClasses(?User $user, ?string $activeYearId): array
    {
        if (!$user || !$activeYearId) {
            return [
                'classes' => collect(),
                'is_bk' => false,
                'is_bypass' => false,
                'role_summary' => 'Guru',
            ];
        }

        $isAdminMode = (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin())
            || $user->hasAnyRole(['super_admin', 'admin_presensi', 'admin_portal_presensi']);
        $hasBypass = false;
        try {
            $hasBypass = method_exists($user, 'can') && $user->can('portal_guru:akses_semua_kelas');
        } catch (\Throwable $e) {}

        $isGuruBk = false;
        try {
            $isGuruBk = method_exists($user, 'isGuruBk') && $user->isGuruBk();
        } catch (\Throwable $e) {}

        $actor = $user->teacher ?: Guru::where('user_id', $user->id)->first();

        if ($isAdminMode || $hasBypass) {
            $rawClasses = Kelas::whereHas('kelasAjarans', function ($query) use ($activeYearId) {
                $query->where('academic_year_id', $activeYearId);
            })->orderBy('name', 'asc')->get();

            $formatted = $rawClasses->map(fn(Kelas $c) => [
                'id' => $c->id,
                'name' => $c->name,
                'grade_level' => $c->grade_level,
                'access_type' => 'bypass',
                'access_label' => 'Semua Kelas',
            ])->values();

            return [
                'classes' => $formatted,
                'is_bk' => $isGuruBk,
                'is_bypass' => true,
                'role_summary' => $isAdminMode ? 'Admin Presensi' : 'Akses Semua Kelas',
            ];
        }

        if (!$actor) {
            return [
                'classes' => collect(),
                'is_bk' => $isGuruBk,
                'is_bypass' => false,
                'role_summary' => 'Guru',
            ];
        }

        $waliClassIds = $actor->kelasAjarans()
            ->where('academic_year_id', $activeYearId)
            ->pluck('class_id')
            ->toArray();

        $pantauClassIds = $actor->kelasPantau()
            ->where('academic_year_id', $activeYearId)
            ->pluck('class_id')
            ->toArray();

        $allowedClassIds = array_values(array_unique(array_merge($waliClassIds, $pantauClassIds)));

        $rawClasses = !empty($allowedClassIds)
            ? Kelas::whereIn('id', $allowedClassIds)->orderBy('name', 'asc')->get()
            : collect();

        $formatted = $rawClasses->map(function (Kelas $c) use ($waliClassIds, $isGuruBk) {
            $isWali = in_array($c->id, $waliClassIds, true);
            return [
                'id' => $c->id,
                'name' => $c->name,
                'grade_level' => $c->grade_level,
                'access_type' => $isWali ? 'wali_kelas' : 'binaan_bk',
                'access_label' => $isWali ? 'Wali Kelas' : ($isGuruBk ? 'Binaan BK' : 'Kelas Binaan'),
            ];
        })->values();

        $roleSummary = 'Wali Kelas';
        if ($isGuruBk && !empty($pantauClassIds) && !empty($waliClassIds)) {
            $roleSummary = 'Wali Kelas & Guru BK';
        } elseif ($isGuruBk || !empty($pantauClassIds)) {
            $roleSummary = 'Guru BK';
        }

        return [
            'classes' => $formatted,
            'is_bk' => $isGuruBk || !empty($pantauClassIds),
            'is_bypass' => false,
            'role_summary' => $roleSummary,
        ];
    }

    /**
     * Ambil daftar Koordinator Sholat Dhuhur (Guru PAI & Guru Lainnya).
     */
    protected function getKoordinatorsList(?string $requestedKoordinatorId = null): array
    {
        $mapelPaiIds = MataPelajaran::where('nama_mapel', 'like', '%Agama Islam%')
            ->orWhere('kode_mapel', 'PAIBP')
            ->pluck('id');

        $paiTeacherIds = Pengajaran::whereIn('mata_pelajaran_id', $mapelPaiIds)
            ->distinct()
            ->pluck('teacher_id')
            ->filter()
            ->toArray();

        $pai = Guru::whereIn('id', $paiTeacherIds)->orderBy('name')->get(['id', 'name', 'nip']);
        $others = Guru::whereNotIn('id', $paiTeacherIds)->orderBy('name')->get(['id', 'name', 'nip']);

        $selectedId = $requestedKoordinatorId;
        if (!$selectedId) {
            $defaultPai = $pai->first(fn($g) => stripos($g->name, 'ASIS') !== false) ?? $pai->first();
            $selectedId = $defaultPai ? $defaultPai->id : ($others->first()?->id ?? null);
        }

        $items = [];
        foreach ($pai as $g) {
            $items[] = [
                'id' => $g->id,
                'name' => $g->name,
                'nip' => $g->nip ?? '-',
                'group' => 'Guru PAI',
            ];
        }
        foreach ($others as $g) {
            $items[] = [
                'id' => $g->id,
                'name' => $g->name,
                'nip' => $g->nip ?? '-',
                'group' => 'Guru Lainnya',
            ];
        }

        return [
            'items' => $items,
            'selected_id' => $selectedId,
        ];
    }

    protected function resolveKoordinator(?string $koordinatorId = null): ?Guru
    {
        if ($koordinatorId) {
            $found = Guru::find($koordinatorId);
            if ($found) {
                return $found;
            }
        }
        return Guru::where('name', 'like', '%ASIS%')->first() ?? Guru::first();
    }

    /**
     * GET /api/v1/guru/sholat-dhuhur/dashboard
     * Mengembalikan seluruh data dashboard Presensi Sholat Dhuhur sesuai /portal-guru/sholat-dhuhur (WaliKelasSholatDhuhur).
     */
    public function dashboard(Request $request): JsonResponse
    {
        if (!$this->isSholatDhuhurEnabled()) {
            return response()->json([
                'success' => false,
                'message' => 'Fitur Presensi Sholat Dhuhur sedang dinonaktifkan di Pengaturan Presensi.',
            ], 403);
        }

        $user = $this->resolveUser($request);
        $sekolah = PengaturanSekolah::current();
        $enableSholatJumat = (bool) ($sekolah?->enable_sholat_jumat ?? false);

        $activeYear = $this->getActiveAcademicYear();
        $selectedAcademicYearId = $activeYear?->id;
        $academicYears = $activeYear ? collect([$activeYear]) : collect();

        $availableMonths = [];
        if ($activeYear) {
            $sy = $activeYear->start_year;
            $ey = $activeYear->end_year;
            $monthNames = [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
            ];

            for ($m = 7; $m <= 12; $m++) {
                $key = sprintf('%02d-%d', $m, $sy);
                $availableMonths[] = [
                    'key' => $key,
                    'label' => $monthNames[$m] . ' ' . $sy,
                ];
            }
            for ($m = 1; $m <= 6; $m++) {
                $key = sprintf('%02d-%d', $m, $ey);
                $availableMonths[] = [
                    'key' => $key,
                    'label' => $monthNames[$m] . ' ' . $ey,
                ];
            }
        }

        $selectedMonthYear = $request->input('month_year');
        $monthKeys = collect($availableMonths)->pluck('key')->toArray();
        if (!$selectedMonthYear || !in_array($selectedMonthYear, $monthKeys)) {
            $currentMonthYear = date('m-Y');
            $selectedMonthYear = in_array($currentMonthYear, $monthKeys)
                ? $currentMonthYear
                : ($monthKeys[0] ?? $currentMonthYear);
        }

        $accessInfo = $this->getAccessibleClasses($user, $selectedAcademicYearId);
        $classes = $accessInfo['classes'];

        $selectedClassId = $request->input('class_id');
        if ($classes->isNotEmpty()) {
            if (!$selectedClassId || !$classes->contains('id', $selectedClassId)) {
                $selectedClassId = $classes->first()['id'];
            }
        } else {
            $selectedClassId = null;
        }

        $koordinatorsData = $this->getKoordinatorsList($request->input('koordinator_id'));

        $todayDate = Carbon::now('Asia/Jakarta')->toDateString();
        $selectedDailyDate = $request->input('daily_date') ?: $todayDate;

        $isNotifSholatActive = (bool) (PresensiNotificationSetting::where('status_presensi', 'sholat_dhuhur')->value('is_active') ?? false);

        $studentsFormatted = [];
        $todayStats = [
            'hadir' => 0,
            'ijin' => 0,
            'tidak_hadir' => 0,
            'belum' => 0,
            'total' => 0,
            'total_all' => 0,
            'non_muslim_count' => 0,
            'persentase_hadir' => 0,
            'is_holiday' => false,
            'holiday_desc' => null,
        ];
        $classMonthlyStats = [
            'hadir' => 0,
            'ijin' => 0,
            'tidak_hadir' => 0,
            'effective_days' => 0,
            'total_students' => 0,
            'total_all_students' => 0,
            'non_muslim_count' => 0,
            'max_possible' => 0,
            'class_percentage' => 0,
        ];
        $daysInMonth = 30;
        $className = '-';
        $classAccessLabel = 'Wali Kelas';

        if ($selectedClassId && $selectedAcademicYearId && $selectedMonthYear) {
            $currentClassObj = $classes->firstWhere('id', $selectedClassId);
            $className = $currentClassObj['name'] ?? (Kelas::find($selectedClassId)?->name ?? '-');
            $classAccessLabel = $currentClassObj['access_label'] ?? 'Wali Kelas';

            $parts = explode('-', $selectedMonthYear);
            $month = $parts[0] ?? date('m');
            $year = (int) ($parts[1] ?? date('Y'));

            $service = app(PresensiSholatDhuhurService::class);
            $result = $service->getMonthlyMatrixData(
                $selectedAcademicYearId,
                $selectedClassId,
                $month,
                $year
            );

            $rawStudents = $result['students'] ?? collect();
            $monthlyStats = $result['monthlyStats'] ?? [];
            $classMonthlyStats = $result['classMonthlyStats'] ?? $classMonthlyStats;
            $daysInMonth = $result['daysInMonth'] ?? 30;

            // Jika daily_date berbeda dengan todayDate, hitung stats & attendances untuk selectedDailyDate
            $dailyDateObj = Carbon::parse($selectedDailyDate, 'Asia/Jakarta');
            $isDailyHoliday = !$service->isHariSholatDhuhur($dailyDateObj, $selectedClassId);
            $dailyHolidayDesc = $isDailyHoliday ? ($service->getHolidayDescription($dailyDateObj, $selectedClassId) ?? 'Hari Libur') : null;

            $dailySholatRecords = PresensiSholatDhuhur::where('academic_year_id', $selectedAcademicYearId)
                ->where('class_id', $selectedClassId)
                ->whereDate('date', $selectedDailyDate)
                ->get()
                ->keyBy('student_id');

            $dailyMorningRecords = Presensi::where('academic_year_id', $selectedAcademicYearId)
                ->where('class_id', $selectedClassId)
                ->whereDate('date', $selectedDailyDate)
                ->get()
                ->keyBy('student_id');

            $waliKelasName = $user?->teacher?->name ?? $user?->display_name ?? $user?->name ?? 'Wali Kelas';
            $dateLabel = $dailyDateObj->locale('id')->translatedFormat('l, d F Y');

            $dHadir = 0;
            $dIjin = 0;
            $dTidakHadir = 0;
            $dMuslimCount = 0;
            $dNonMuslimCount = 0;

            foreach ($rawStudents as $student) {
                $religion = strtolower(trim($student->religion ?? ''));
                $isNonMuslim = !empty($student->religion) && $religion !== 'islam';

                $sh = $dailySholatRecords->get($student->id);
                $m = $dailyMorningRecords->get($student->id);

                $status = 'belum';
                $keterangan = null;
                $scanTime = null;
                $isHalangan = false;
                $isPagiSynced = false;

                if ($isNonMuslim) {
                    $dNonMuslimCount++;
                    $status = 'non_muslim';
                    $keterangan = 'Non-Muslim (' . ($student->religion ?? '-') . ') — Tidak wajib sholat';
                } else {
                    $dMuslimCount++;
                    if ($sh) {
                        $status = $sh->status;
                        $keterangan = $sh->keterangan;
                        $scanTime = $sh->created_at ? Carbon::parse($sh->created_at)->format('H:i') : null;
                        $isHalangan = str_contains((string) $keterangan, 'Halangan') || str_contains((string) $keterangan, 'Haid');
                        if ($status === 'hadir') {
                            $dHadir++;
                        } elseif ($status === 'ijin') {
                            $dIjin++;
                        } elseif ($status === 'tidak_hadir') {
                            $dTidakHadir++;
                        }
                    } elseif ($m && in_array($m->status, ['alpa', 'izin', 'sakit'])) {
                        // Read-time fallback dari presensi pagi khusus Alpa / Izin / Sakit
                        $isPagiSynced = true;
                        if ($m->status === 'alpa') {
                            $status = 'tidak_hadir';
                            $keterangan = 'Alpa di presensi pagi';
                            $dTidakHadir++;
                        } else {
                            $status = 'ijin';
                            $keterangan = ucfirst($m->status) . ' di presensi pagi';
                            $dIjin++;
                        }
                    }
                }

                $mStat = $monthlyStats[$student->id] ?? [
                    'hadir' => 0,
                    'ijin' => 0,
                    'tidak_hadir' => 0,
                    'persentase' => 0,
                    'daily' => [],
                    'daily_notes' => [],
                    'is_non_muslim' => $isNonMuslim,
                ];

                $rawPhone = $student->no_hp_orang_tua ?: $student->no_hp;
                $cleanPhone = preg_replace('/[^0-9]/', '', $rawPhone ?? '');
                if (!empty($cleanPhone)) {
                    if (str_starts_with($cleanPhone, '0')) {
                        $cleanPhone = '62' . substr($cleanPhone, 1);
                    } elseif (str_starts_with($cleanPhone, '8')) {
                        $cleanPhone = '62' . $cleanPhone;
                    }
                }

                $waTextSholat = '';
                try {
                    $waTextSholat = PresensiNotificationSetting::renderTemplate('sholat_dhuhur', [
                        'nama_siswa' => $student->name,
                        'kelas' => $className,
                        'tanggal' => $dateLabel,
                        'status_kehadiran' => ($status === 'tidak_hadir' ? 'Tidak Hadir Sholat' : 'Belum Presensi Sholat'),
                        'nama_wali_kelas' => $waliKelasName,
                    ]);
                } catch (\Throwable $e) {
                    $waTextSholat = "Assalamu'alaikum Wr. Wb.\nYth. Orang Tua/Wali dari *{$student->name}* (Kelas {$className}).\nKami informasikan bahwa pada *{$dateLabel}*, putra/putri Bapak/Ibu tercatat *{$status}* pada ibadah Sholat Dhuhur berjamaah di sekolah.\nTerima kasih.\n- {$waliKelasName}";
                }

                $effDays = (int) ($classMonthlyStats['effective_days'] ?? 0);
                $hCount = (int) ($mStat['hadir'] ?? 0);
                $pct = (!$isNonMuslim && $effDays > 0) ? round(($hCount / $effDays) * 100, 1) : 0;

                $studentsFormatted[] = [
                    'id' => $student->id,
                    'name' => $student->name,
                    'initials' => strtoupper(substr($student->name, 0, 2)),
                    'nisn' => $student->nisn ?? '-',
                    'nis' => $student->nis ?? '-',
                    'gender' => $student->gender ?? 'L',
                    'religion' => $student->religion ?? 'Islam',
                    'is_non_muslim' => $isNonMuslim,
                    'phone_raw' => $rawPhone ?? '',
                    'phone_clean' => $cleanPhone ?? '',
                    'today' => [
                        'status' => $status, // hadir | ijin | tidak_hadir | belum | non_muslim
                        'ui_status' => $isHalangan ? 'haid' : $status,
                        'is_halangan' => $isHalangan,
                        'is_pagi_synced' => $isPagiSynced,
                        'keterangan' => $keterangan,
                        'time' => $scanTime,
                        'wa_text' => $waTextSholat,
                    ],
                    'monthly' => [
                        'hadir' => (int) ($mStat['hadir'] ?? 0),
                        'ijin' => (int) ($mStat['ijin'] ?? 0),
                        'tidak_hadir' => (int) ($mStat['tidak_hadir'] ?? 0),
                        'persentase' => $pct,
                        'is_non_muslim' => $isNonMuslim,
                        'daily' => $mStat['daily'] ?? [],
                    ],
                ];
            }

            $dBelum = max(0, $dMuslimCount - ($dHadir + $dIjin + $dTidakHadir));
            $todayStats = [
                'hadir' => $dHadir,
                'ijin' => $dIjin,
                'tidak_hadir' => $dTidakHadir,
                'belum' => $dBelum,
                'total' => $dMuslimCount,
                'total_all' => $dMuslimCount + $dNonMuslimCount,
                'non_muslim_count' => $dNonMuslimCount,
                'persentase_hadir' => $dMuslimCount > 0 ? round(($dHadir / $dMuslimCount) * 100, 1) : 0,
                'is_holiday' => $isDailyHoliday,
                'holiday_desc' => $dailyHolidayDesc,
            ];
        }

        $dailyDateLabel = Carbon::parse($selectedDailyDate)->locale('id')->translatedFormat('l, d F Y');
        $selectedMonthLabel = collect($availableMonths)->firstWhere('key', $selectedMonthYear)['label'] ?? $selectedMonthYear;

        return response()->json([
            'success' => true,
            'data' => [
                'enable_sholat_dhuhur' => true,
                'enable_sholat_jumat' => $enableSholatJumat,
                'academic_years' => $academicYears->map(fn($y) => [
                    'id' => $y->id,
                    'name' => $y->name,
                    'start_year' => $y->start_year,
                    'end_year' => $y->end_year,
                    'is_active' => true,
                    'is_locked' => true,
                ])->values(),
                'selected_academic_year_id' => $selectedAcademicYearId,
                'selected_academic_year_name' => $activeYear?->name ?? '-',
                'is_academic_year_locked' => true,
                'is_bk' => $accessInfo['is_bk'],
                'is_bypass' => $accessInfo['is_bypass'],
                'role_summary' => $accessInfo['role_summary'],
                'classes' => $classes->values(),
                'selected_class_id' => $selectedClassId,
                'selected_class_name' => $className,
                'selected_class_access_label' => $classAccessLabel,
                'available_months' => $availableMonths,
                'selected_month_year' => $selectedMonthYear,
                'selected_month_label' => $selectedMonthLabel,
                'koordinators' => $koordinatorsData['items'],
                'selected_koordinator_id' => $koordinatorsData['selected_id'],
                'today_date' => $selectedDailyDate,
                'today_date_label' => $dailyDateLabel,
                'days_in_month' => $daysInMonth,
                'is_notif_sholat_active' => $isNotifSholatActive,
                'today_stats' => $todayStats,
                'class_monthly_stats' => $classMonthlyStats,
                'students' => $studentsFormatted,
            ],
        ]);
    }

    /**
     * GET /api/v1/guru/sholat-dhuhur/manual-input
     * Mengambil daftar siswa untuk Modal Input Presensi Sholat Dhuhur pada tanggal tertentu.
     * Juga mendukung parameter pull_morning=1 untuk menarik data Izin/Sakit/Alpa dari presensi pagi ke form modal.
     */
    public function getManualInput(Request $request): JsonResponse
    {
        if (!$this->isSholatDhuhurEnabled()) {
            return response()->json([
                'success' => false,
                'message' => 'Fitur Presensi Sholat Dhuhur sedang dinonaktifkan di Pengaturan Presensi.',
            ], 403);
        }

        $user = $this->resolveUser($request);
        $activeYear = $this->getActiveAcademicYear();
        $academicYearId = $activeYear?->id ?: $request->input('academic_year_id');
        $classId = $request->input('class_id');
        $inputDate = $request->input('date') ?: Carbon::now('Asia/Jakarta')->toDateString();
        $pullMorning = $request->boolean('pull_morning', false);

        if (!$classId || !$academicYearId) {
            return response()->json([
                'success' => false,
                'message' => 'Kelas dan Tahun Ajaran Aktif wajib dipilih.',
            ], 422);
        }

        if ($user) {
            $accessInfo = $this->getAccessibleClasses($user, $academicYearId);
            if ($accessInfo['classes']->isNotEmpty() && !$accessInfo['classes']->contains('id', $classId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses ke kelas ini.',
                ], 403);
            }
        }

        $sholatService = app(PresensiSholatDhuhurService::class);
        $dateObj = Carbon::parse($inputDate, 'Asia/Jakarta');
        $isHoliday = !$sholatService->isHariSholatDhuhur($dateObj, $classId);
        $holidayDesc = $sholatService->getHolidayDescription($dateObj, $classId) ?? '';

        $kelas = Kelas::with(['enrollments' => function ($q) use ($academicYearId) {
            $q->where('academic_year_id', $academicYearId)
              ->where('status', 'aktif')
              ->with('siswa');
        }])->find($classId);

        $allStudents = $kelas ? $kelas->enrollments->pluck('siswa')->filter()->sortBy('name')->values() : collect();
        $muslimStudents = $allStudents->filter(fn($s) => empty($s->religion) || strtolower(trim($s->religion)) === 'islam')->values();
        $nonMuslimStudents = $allStudents->filter(fn($s) => !empty($s->religion) && strtolower(trim($s->religion)) !== 'islam')->values();
        $students = $muslimStudents->concat($nonMuslimStudents);

        $existingSholat = PresensiSholatDhuhur::where('academic_year_id', $academicYearId)
            ->where('class_id', $classId)
            ->whereDate('date', $inputDate)
            ->get()
            ->keyBy('student_id');

        $morningAtts = collect();
        if ($pullMorning && !$isHoliday) {
            // HANYA ambil presensi pagi yang berstatus alpa, izin, atau sakit
            $morningAtts = Presensi::where('academic_year_id', $academicYearId)
                ->where('class_id', $classId)
                ->whereDate('date', $inputDate)
                ->whereIn('status', ['alpa', 'izin', 'sakit'])
                ->get()
                ->keyBy('student_id');
        }

        $list = [];
        $syncedMorningCount = 0;

        foreach ($students as $student) {
            $religion = strtolower(trim($student->religion ?? ''));
            $isNonMuslim = !empty($student->religion) && $religion !== 'islam';

            if ($isNonMuslim) {
                $list[] = [
                    'id' => $student->id,
                    'name' => $student->name,
                    'nisn' => $student->nisn ?? '-',
                    'gender' => $student->gender ?? 'L',
                    'religion' => $student->religion ?? '-',
                    'status' => 'non_muslim',
                    'keterangan' => 'Non-Muslim (' . ($student->religion ?? '-') . ') — Tidak wajib sholat dhuhur',
                    'is_halangan' => false,
                    'is_pagi_absent' => false,
                    'is_non_muslim' => true,
                ];
                continue;
            }

            $sholat = $existingSholat->get($student->id);
            $morning = $morningAtts->get($student->id);

            $status = '';
            $ket = '';
            $isHalangan = false;
            $isPagiAbsent = false;

            if ($sholat) {
                $status = $sholat->status;
                $ket = (string) ($sholat->keterangan ?? '');
                $isHalangan = str_contains($ket, 'Halangan') || str_contains($ket, 'Haid');
                if ($isHalangan) {
                    $status = 'haid';
                }
            }

            // Jika tombol Sinkron Presensi Pagi di dalam modal ditekan, timpa dengan status Izin/Sakit/Alpa pagi
            if ($pullMorning && $morning) {
                if ($morning->status === 'alpa') {
                    $status = 'tidak_hadir';
                    $ket = 'Alpa di presensi pagi';
                    $isHalangan = false;
                    $isPagiAbsent = true;
                    $syncedMorningCount++;
                } elseif (in_array($morning->status, ['izin', 'sakit'])) {
                    $status = 'ijin';
                    $ket = ucfirst($morning->status) . ' di presensi pagi';
                    $isHalangan = false;
                    $isPagiAbsent = true;
                    $syncedMorningCount++;
                }
            }

            $list[] = [
                'id' => $student->id,
                'name' => $student->name,
                'nisn' => $student->nisn ?? '-',
                'gender' => $student->gender ?? 'L',
                'religion' => $student->religion ?? 'Islam',
                'status' => $status,
                'keterangan' => $ket,
                'is_halangan' => $isHalangan,
                'is_pagi_absent' => $isPagiAbsent,
                'is_non_muslim' => false,
            ];
        }

        return response()->json([
            'success' => true,
            'message' => $pullMorning
                ? ($syncedMorningCount > 0
                    ? "Berhasil menarik {$syncedMorningCount} data ketidakhadiran (Izin/Sakit/Alpa) dari presensi pagi."
                    : 'Tidak ditemukan data Izin, Sakit, atau Alpa di presensi pagi pada tanggal ini.')
                : 'Data siswa berhasil dimuat.',
            'data' => [
                'date' => $inputDate,
                'date_label' => $dateObj->locale('id')->translatedFormat('l, d F Y'),
                'is_holiday' => $isHoliday,
                'holiday_desc' => $holidayDesc,
                'synced_morning_count' => $syncedMorningCount,
                'students' => $list,
            ],
        ]);
    }

    /**
     * POST /api/v1/guru/sholat-dhuhur/sync-morning
     * Menyinkronkan data Presensi Pagi (HANYA yang berstatus izin, sakit, atau alpa) ke tabel PresensiSholatDhuhur.
     * Siswa yang hadir atau telat di pagi hari TIDAK diubah.
     */
    public function syncFromMorning(Request $request): JsonResponse
    {
        if (!$this->isSholatDhuhurEnabled()) {
            return response()->json([
                'success' => false,
                'message' => 'Fitur Presensi Sholat Dhuhur sedang dinonaktifkan di Pengaturan Presensi.',
            ], 403);
        }

        $user = $this->resolveUser($request);
        $activeYear = $this->getActiveAcademicYear();
        $academicYearId = $activeYear?->id ?: $request->input('academic_year_id');
        $classId = $request->input('class_id');
        $targetDate = $request->input('date') ?: Carbon::today('Asia/Jakarta')->toDateString();

        $sholatService = app(PresensiSholatDhuhurService::class);
        $dateObj = Carbon::parse($targetDate, 'Asia/Jakarta');

        if (!$sholatService->isHariSholatDhuhur($dateObj, $classId)) {
            $desc = $sholatService->getHolidayDescription($dateObj, $classId) ?? 'Hari Libur';
            return response()->json([
                'success' => false,
                'message' => "Tidak dapat menyinkronkan presensi sholat dhuhur pada hari libur ({$desc}).",
            ], 422);
        }

        $teacherId = $user?->teacher?->id ?: ($user ? Guru::where('user_id', $user->id)->value('id') : null);

        // Jika class_id dikirim, sinkronkan khusus untuk kelas binaan yang dipilih (atau semua kelas jika tidak ada class_id)
        if ($classId && $academicYearId) {
            $morningAbsents = Presensi::where('academic_year_id', $academicYearId)
                ->where('class_id', $classId)
                ->whereDate('date', $targetDate)
                ->whereIn('status', ['alpa', 'izin', 'sakit'])
                ->with('student')
                ->get();

            if ($morningAbsents->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'synced_count' => 0,
                    'message' => 'Tidak ditemukan siswa yang Izin, Sakit, atau Alpa pada presensi pagi di kelas ini untuk tanggal ' . $dateObj->locale('id')->translatedFormat('d F Y') . '.',
                ]);
            }

            $enrollmentMap = EnrollmentSiswa::where('class_id', $classId)
                ->where('academic_year_id', $academicYearId)
                ->where('status', 'aktif')
                ->pluck('id', 'student_id')
                ->toArray();

            $syncedCount = 0;
            foreach ($morningAbsents as $morning) {
                $student = $morning->student;
                if (!$student) {
                    continue;
                }

                // Lewati siswa Non-Muslim
                $religion = strtolower(trim($student->religion ?? ''));
                if (!empty($student->religion) && $religion !== 'islam') {
                    continue;
                }

                $sholatStatus = match ($morning->status) {
                    'alpa' => 'tidak_hadir',
                    'izin', 'sakit' => 'ijin',
                    default => null,
                };
                if (!$sholatStatus) {
                    continue;
                }

                $keterangan = match ($morning->status) {
                    'alpa' => 'Alpa di presensi pagi',
                    'izin' => 'Izin di presensi pagi',
                    'sakit' => 'Sakit di presensi pagi',
                    default => null,
                };

                PresensiSholatDhuhur::updateOrCreate(
                    [
                        'student_id' => $student->id,
                        'date' => $targetDate,
                    ],
                    [
                        'academic_year_id' => $academicYearId,
                        'class_id' => $classId,
                        'enrollment_id' => $morning->enrollment_id ?? ($enrollmentMap[$student->id] ?? null),
                        'status' => $sholatStatus,
                        'keterangan' => $keterangan,
                        'input_by_teacher_id' => $teacherId,
                    ]
                );
                $syncedCount++;
            }

            return response()->json([
                'success' => true,
                'synced_count' => $syncedCount,
                'message' => $syncedCount > 0
                    ? "Berhasil menyinkronkan {$syncedCount} siswa (Izin/Sakit/Alpa) dari presensi pagi!"
                    : 'Tidak ada data siswa Muslim yang Izin/Sakit/Alpa di presensi pagi.',
            ]);
        }

        // Fallback: sinkronkan seluruh kelas seperti tombol header di web
        $result = $sholatService->syncAllClassesFromMorning($targetDate, $teacherId);
        return response()->json([
            'success' => $result['status'] === 'success',
            'synced_count' => $result['synced_count'] ?? 0,
            'message' => $result['message'] ?? 'Sinkronisasi selesai.',
        ], $result['status'] === 'error' ? 422 : 200);
    }

    /**
     * POST /api/v1/guru/sholat-dhuhur/manual-input
     * Menyimpan hasil Input Presensi Sholat Dhuhur (mendukung 1 siswa dari pop-up maupun massal 1 kelas).
     */
    public function saveManualInput(Request $request): JsonResponse
    {
        if (!$this->isSholatDhuhurEnabled()) {
            return response()->json([
                'success' => false,
                'message' => 'Fitur Presensi Sholat Dhuhur sedang dinonaktifkan di Pengaturan Presensi.',
            ], 403);
        }

        $user = $this->resolveUser($request);
        $activeYear = $this->getActiveAcademicYear();
        $academicYearId = $activeYear?->id ?: $request->input('academic_year_id');
        $classId = $request->input('class_id');
        $inputDate = $request->input('date') ?: Carbon::now('Asia/Jakarta')->toDateString();
        $inputStudents = $request->input('students', []);

        if (!$classId || !$academicYearId || !$inputDate) {
            return response()->json([
                'success' => false,
                'message' => 'Parameter kelas, tahun ajaran aktif, dan tanggal wajib diisi.',
            ], 422);
        }

        if ($user) {
            $accessInfo = $this->getAccessibleClasses($user, $academicYearId);
            if ($accessInfo['classes']->isNotEmpty() && !$accessInfo['classes']->contains('id', $classId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses untuk mengubah presensi sholat kelas ini.',
                ], 403);
            }
        }

        $sholatService = app(PresensiSholatDhuhurService::class);
        $dateObj = Carbon::parse($inputDate, 'Asia/Jakarta');
        if (!$sholatService->isHariSholatDhuhur($dateObj, $classId)) {
            $desc = $sholatService->getHolidayDescription($dateObj, $classId) ?? 'Libur Sholat';
            return response()->json([
                'success' => false,
                'message' => "Tidak dapat menyimpan presensi sholat dhuhur pada hari libur ({$desc})!",
            ], 422);
        }

        $teacherId = $user?->teacher?->id ?: ($user ? Guru::where('user_id', $user->id)->value('id') : null);

        $enrollmentMap = EnrollmentSiswa::where('class_id', $classId)
            ->where('academic_year_id', $academicYearId)
            ->where('status', 'aktif')
            ->pluck('id', 'student_id')
            ->toArray();

        $savedCount = 0;
        foreach ($inputStudents as $stData) {
            if (!empty($stData['is_non_muslim'])) {
                continue;
            }
            if (empty($stData['status']) || empty($stData['id']) || $stData['status'] === 'non_muslim') {
                continue;
            }

            $studentId = $stData['id'];
            $enrId = $enrollmentMap[$studentId] ?? null;
            $rawStatus = strtolower(trim((string) $stData['status']));
            $isHalangan = ($rawStatus === 'haid') || !empty($stData['is_halangan']);
            $dbStatus = ($rawStatus === 'haid') ? 'ijin' : $rawStatus;

            if (!in_array($dbStatus, ['hadir', 'ijin', 'tidak_hadir'])) {
                continue;
            }

            $keterangan = trim((string) ($stData['keterangan'] ?? ''));
            if ($isHalangan && $keterangan === '') {
                $keterangan = 'Halangan / Haid';
            }

            PresensiSholatDhuhur::updateOrCreate(
                [
                    'student_id' => $studentId,
                    'date' => $inputDate,
                ],
                [
                    'academic_year_id' => $academicYearId,
                    'class_id' => $classId,
                    'enrollment_id' => $enrId,
                    'status' => $dbStatus,
                    'keterangan' => $keterangan !== '' ? $keterangan : null,
                    'input_by_teacher_id' => $teacherId,
                ]
            );
            $savedCount++;
        }

        if ($savedCount === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Pilih status kehadiran sholat terlebih dahulu.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Presensi Sholat Dhuhur tanggal ' . $dateObj->locale('id')->translatedFormat('d F Y') . " berhasil disimpan ({$savedCount} siswa)!",
        ]);
    }

    /**
     * POST /api/v1/guru/sholat-dhuhur/send-wa
     * Mengirim pesan WhatsApp Sholat Dhuhur ke orang tua siswa melalui WhatsAppGatewayService.
     */
    public function sendWaDirect(Request $request): JsonResponse
    {
        if (!$this->isSholatDhuhurEnabled()) {
            return response()->json([
                'success' => false,
                'message' => 'Fitur Presensi Sholat Dhuhur sedang dinonaktifkan di Pengaturan Presensi.',
            ], 403);
        }

        $studentId = $request->input('student_id');
        $targetPhone = $request->input('phone');
        $messageText = $request->input('message');
        $type = $request->input('type', 'sholat_dhuhur');

        if (empty($studentId) || empty($targetPhone) || empty($messageText)) {
            return response()->json([
                'success' => false,
                'message' => 'Nomor WhatsApp dan isi pesan tidak boleh kosong.',
            ], 422);
        }

        $notifSetting = PresensiNotificationSetting::where('status_presensi', 'sholat_dhuhur')->first();
        if ($notifSetting && !$notifSetting->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Fitur notifikasi WhatsApp Sholat Dhuhur sedang dinonaktifkan oleh Administrator.',
            ], 422);
        }

        $cleanPhone = preg_replace('/[^0-9]/', '', $targetPhone);
        if (empty($cleanPhone)) {
            return response()->json([
                'success' => false,
                'message' => 'Nomor WhatsApp orang tua tidak valid atau masih kosong.',
            ], 422);
        }

        if (str_starts_with($cleanPhone, '0')) {
            $cleanPhone = '62' . substr($cleanPhone, 1);
        } elseif (str_starts_with($cleanPhone, '8')) {
            $cleanPhone = '62' . $cleanPhone;
        }

        try {
            $waService = app(WhatsAppGatewayService::class);
            $sent = $waService->sendMessage(
                toNumber: $cleanPhone,
                message: $messageText,
                relatedType: 'presensi_' . $type,
                relatedId: $studentId,
                recipientType: 'ortu',
                logId: null,
                ignoreDelay: true
            );

            if ($sent) {
                return response()->json([
                    'success' => true,
                    'message' => "Pesan WhatsApp berhasil dikirim ke nomor {$cleanPhone}!",
                ]);
            }
        } catch (\Throwable $e) {
            // Tangani error koneksi gateway
        }

        return response()->json([
            'success' => false,
            'message' => 'Gagal mengirim via sistem gateway WhatsApp. Anda dapat menggunakan tombol "Buka di WA" sebagai alternatif.',
        ], 500);
    }

    /**
     * GET /api/v1/guru/sholat-dhuhur/export
     * Mengunduh laporan Presensi Sholat Dhuhur (Bulanan PDF, Semester PDF, Rekap Kelas PDF, Rekap Harian PDF, atau Excel).
     */
    public function exportReport(Request $request)
    {
        if (!$this->isSholatDhuhurEnabled()) {
            return response()->json([
                'success' => false,
                'message' => 'Fitur Presensi Sholat Dhuhur sedang dinonaktifkan di Pengaturan Presensi.',
            ], 403);
        }

        $user = $this->resolveUser($request);
        $activeYear = $this->getActiveAcademicYear();
        $academicYearId = $activeYear?->id ?: $request->input('academic_year_id');
        $classId = $request->input('class_id');
        $cetakJenis = strtolower($request->input('jenis', 'bulanan')); // bulanan | semester | rekap_kelas | rekap_harian | excel_harian
        $cetakBulanYear = $request->input('month_year', date('m-Y'));
        $cetakSemester = $request->input('semester', (int) date('m') >= 7 ? 'ganjil' : 'genap');
        $targetDate = $request->input('date') ?: Carbon::today('Asia/Jakarta')->toDateString();
        $koordinator = $this->resolveKoordinator($request->input('koordinator_id'));
        $sekolah = PengaturanSekolah::current();
        $sholatService = app(PresensiSholatDhuhurService::class);

        // 1. Ekspor Harian (Rekap Kelas PDF, Rekap Harian PDF, atau Excel Harian)
        if (in_array($cetakJenis, ['rekap_kelas', 'rekap_harian', 'excel_harian'])) {
            $summary = $sholatService->getDailySchoolSummaryData($academicYearId, $targetDate);
            $safeDate = str_replace('-', '_', $targetDate);

            if ($cetakJenis === 'excel_harian') {
                $fileName = "Rekap_Sholat_Dhuhur_Harian_{$safeDate}.xlsx";
                return Excel::download(
                    new RekapSholatDhuhurHarianExport(
                        $targetDate,
                        $academicYearId,
                        $summary['stats'],
                        $summary['classesSummary'],
                        $summary['unattendedStudents'],
                        $summary['allStudents'],
                        $koordinator
                    ),
                    $fileName
                );
            }

            $dateFormatted = Carbon::parse($targetDate)->locale('id')->translatedFormat('l, d F Y');
            $viewName = $cetakJenis === 'rekap_kelas'
                ? 'pdf.laporan-sholat-dhuhur-rekap-per-kelas'
                : 'pdf.laporan-sholat-dhuhur-rekap-harian';
            $fileName = $cetakJenis === 'rekap_kelas'
                ? "Rekap_Sholat_Dhuhur_Per_Kelas_{$safeDate}.pdf"
                : "Rekap_Presensi_Sholat_Dhuhur_Harian_{$safeDate}.pdf";

            $pdf = Pdf::loadView($viewName, [
                'stats' => $summary['stats'],
                'classesSummary' => $summary['classesSummary'],
                'gradeGroups' => $summary['gradeGroups'] ?? [],
                'sekolah' => $sekolah,
                'koordinator' => $koordinator,
                'tahunAjaran' => $summary['academic_year'],
                'selectedDateFormatted' => $dateFormatted,
                'date' => $targetDate,
                'isHoliday' => $summary['is_holiday'],
                'holidayDesc' => $summary['holiday_desc'],
                'unattendedStudents' => $summary['unattendedStudents'],
                'allStudents' => $summary['allStudents'],
                'generatedAt' => now('Asia/Jakarta')->locale('id')->translatedFormat('l, d F Y H:i') . ' WIB',
            ])->setPaper('a4', 'landscape');

            return $pdf->download($fileName);
        }

        // 2. Ekspor Bulanan atau Semester per Kelas
        $ay = $activeYear ?: TahunAjaran::find($academicYearId);
        $kelas = Kelas::find($classId);
        if (!$ay || !$kelas) {
            return response()->json([
                'success' => false,
                'message' => 'Kelas dan Tahun Ajaran tidak ditemukan.',
            ], 404);
        }

        $sy = $ay->start_year;
        $ey = $ay->end_year;
        $waliKelas = $user?->teacher ?? $kelas->waliKelas($academicYearId);

        if ($cetakJenis === 'bulanan') {
            $parts = explode('-', $cetakBulanYear);
            $month = $parts[0] ?? date('m');
            $year = (int) ($parts[1] ?? date('Y'));
            $label = Carbon::createFromDate($year, $month, 1)->locale('id')->translatedFormat('F Y');
            $safeClassName = str_replace(['/', '\\'], '-', $kelas->name);
            $safeLabel = str_replace([' ', '/', '\\'], ['_', '-', '-'], $label);
            $fileName = 'Laporan_Sholat_Dhuhur_' . $safeClassName . '_' . $safeLabel . '.pdf';

            $result = $sholatService->getMonthlyMatrixData($academicYearId, $classId, $month, $year);
            $pdf = Pdf::loadView('pdf.laporan-sholat-dhuhur-matrix', [
                'students' => $result['students'],
                'monthlyStats' => $result['monthlyStats'],
                'classMonthlyStats' => $result['classMonthlyStats'],
                'daysInMonth' => $result['daysInMonth'],
                'periodeLabel' => $label,
                'kelas' => $kelas,
                'sekolah' => $sekolah,
                'waliKelas' => $waliKelas,
                'koordinator' => $koordinator,
                'generatedAt' => now('Asia/Jakarta')->locale('id')->translatedFormat('l, d F Y H:i'),
            ])->setPaper('a4', 'landscape');

            return $pdf->download($fileName);
        }

        // Semester
        if ($cetakSemester === 'ganjil') {
            $start = Carbon::createFromDate($sy, 7, 1)->startOfMonth()->toDateString();
            $end = Carbon::createFromDate($sy, 12, 31)->endOfMonth()->toDateString();
            $label = 'Semester Ganjil ' . $sy . '/' . $ey;
        } else {
            $start = Carbon::createFromDate($ey, 1, 1)->startOfMonth()->toDateString();
            $end = Carbon::createFromDate($ey, 6, 30)->endOfMonth()->toDateString();
            $label = 'Semester Genap ' . $sy . '/' . $ey;
        }

        $safeClassName = str_replace(['/', '\\'], '-', $kelas->name);
        $safeLabel = str_replace([' ', '/', '\\'], ['_', '-', '-'], $label);
        $fileName = 'Laporan_Sholat_Dhuhur_' . $safeClassName . '_' . $safeLabel . '.pdf';

        $result = $sholatService->getSemesterYearlyData($academicYearId, $classId, $start, $end);
        $pdf = Pdf::loadView('pdf.laporan-sholat-dhuhur-range', [
            'studentsData' => $result['studentsData'],
            'monthsList' => $result['monthsList'],
            'periodeLabel' => $label,
            'kelas' => $kelas,
            'sekolah' => $sekolah,
            'waliKelas' => $waliKelas,
            'koordinator' => $koordinator,
            'generatedAt' => now('Asia/Jakarta')->locale('id')->translatedFormat('l, d F Y H:i'),
        ])->setPaper('a4', 'landscape');

        return $pdf->download($fileName);
    }
}

