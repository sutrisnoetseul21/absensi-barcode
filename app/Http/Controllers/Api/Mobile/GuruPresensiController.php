<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Guru;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\TahunAjaran;
use App\Models\Presensi;
use App\Models\EnrollmentSiswa;
use App\Models\LeaveRequest;
use App\Models\Peminjaman;
use App\Models\JurnalPembelajaran;
use App\Models\KonsultasiGuruWali;
use App\Models\SpikapLaporan;
use App\Models\PengaturanSekolah;
use App\Models\PresensiNotificationSetting;
use App\Services\PresensiRekapService;
use App\Services\KalenderSekolahService;
use App\Services\WhatsAppGatewayService;
use App\Exports\PresensiMatrixExport;
use App\Exports\LaporanPresensiRangeExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GuruPresensiController extends Controller
{
    /**
     * Resolusi user yang terautentikasi (Sanctum Token / Fallback Base64 Token / Identifier Header).
     */
    protected function resolveUser(Request $request): ?User
    {
        // 1. Coba melalui guard Sanctum
        try {
            $sanctumUser = auth('sanctum')->user();
            if ($sanctumUser instanceof User) {
                return $sanctumUser;
            }
        } catch (\Throwable $e) {
            // Abaikan jika tabel personal_access_tokens belum dimigrasi
        }

        // 2. Coba decode Fallback Token (base64: user_id|timestamp|random)
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

        // 3. Coba melalui parameter atau header identifier (prioritaskan pencocokan NIP Guru lebih dulu)
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
     * Tentukan daftar kelas yang berhak diakses oleh Guru pada menu Presensi
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

        // 1. Kelas Wali Kelas Utama (1 atau 2 kelas sesuai Data Induk)
        $waliClassIds = $actor->kelasAjarans()
            ->where('academic_year_id', $activeYearId)
            ->pluck('class_id')
            ->toArray();

        // 2. Kelas Binaan BK / Kelas Pantau yang dipilih di /admin/manajemen-akses-portal/{id}/edit
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
     * GET /api/v1/guru/dashboard-summary
     * Ringkasan metrik untuk 9 Kotak Menu di Beranda Portal Guru.
     */
    public function dashboardSummary(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);
        $teacher = $user?->teacher ?: ($user ? Guru::where('user_id', $user->id)->first() : null);
        $sekolah = PengaturanSekolah::current();

        $kelasAmpuCount = 0;
        $totalStudentsCount = 0;
        $pendingLeaveCount = 0;
        $approvedLeaveCount = 0;
        $rejectedLeaveCount = 0;
        $activeBooksCount = 0;
        $jurnalHariIniCount = 0;
        $totalJurnalCount = 0;
        $pendingKonsultasiCount = 0;
        $spikapCount = 0;
        $spikapAktifCount = 0;
        $spikapDaruratCount = 0;

        $activeYear = $this->getActiveAcademicYear();
        $accessInfo = $this->getAccessibleClasses($user, $activeYear?->id);
        $accessibleClassIds = $accessInfo['classes']->pluck('id')->toArray();
        $kelasAmpuCount = count($accessibleClassIds);

        if ($activeYear && !empty($accessibleClassIds)) {
            try {
                $totalStudentsCount = EnrollmentSiswa::whereIn('class_id', $accessibleClassIds)
                    ->where('academic_year_id', $activeYear->id)
                    ->where('status', 'aktif')
                    ->count();

                $pendingLeaveCount = LeaveRequest::where('status', 'pending')
                    ->where('academic_year_id', $activeYear->id)
                    ->whereHas('student.enrollments', function ($q) use ($accessibleClassIds, $activeYear) {
                        $q->whereIn('class_id', $accessibleClassIds)
                          ->where('academic_year_id', $activeYear->id)
                          ->where('status', 'aktif');
                    })->count();

                $approvedLeaveCount = LeaveRequest::where('status', 'approved')
                    ->where('academic_year_id', $activeYear->id)
                    ->whereHas('student.enrollments', function ($q) use ($accessibleClassIds, $activeYear) {
                        $q->whereIn('class_id', $accessibleClassIds)
                          ->where('academic_year_id', $activeYear->id)
                          ->where('status', 'aktif');
                    })->count();

                $rejectedLeaveCount = LeaveRequest::where('status', 'rejected')
                    ->where('academic_year_id', $activeYear->id)
                    ->whereHas('student.enrollments', function ($q) use ($accessibleClassIds, $activeYear) {
                        $q->whereIn('class_id', $accessibleClassIds)
                          ->where('academic_year_id', $activeYear->id)
                          ->where('status', 'aktif');
                    })->count();
            } catch (\Throwable $e) {}
        }

        if ($teacher) {
            try {
                $activeBooksCount = Peminjaman::where('peminjam_type', 'guru')
                    ->where('peminjam_id', $teacher->id)
                    ->where('status', 'dipinjam')
                    ->count();
            } catch (\Throwable $e) {}

            try {
                $jurnalHariIniCount = JurnalPembelajaran::where('teacher_id', $teacher->id)
                    ->whereDate('tanggal', today())
                    ->count();
                $totalJurnalCount = JurnalPembelajaran::where('teacher_id', $teacher->id)->count();
            } catch (\Throwable $e) {}

            try {
                $pendingKonsultasiCount = KonsultasiGuruWali::where('teacher_id', $teacher->id)
                    ->where('status_pengajuan', 'Menunggu Konfirmasi')
                    ->count();
            } catch (\Throwable $e) {}
        }

        $canAccessSpikap = false;
        $spikapRoleLabel = 'Guru Mata Pelajaran';

        if ($user) {
            try {
                $isAdmin = $user->hasAnyRole(['super_admin', 'spikap_admin']);
                $isKs = $user->isKepalaSekolah() || $user->hasRole('spikap_kepala_sekolah');
                $isBk = $user->isGuruBk() || $user->hasRole('spikap_guru_bk');
                $isWk = $user->isWaliKelasMurni() || ($user->hasRole('wali_kelas') && $user->teacher !== null);

                if (method_exists($user, 'canAccessSpikapGuru')) {
                    $canAccessSpikap = $user->canAccessSpikapGuru();
                } else {
                    $canAccessSpikap = $isAdmin || $isKs || $isBk || $isWk;
                }

                if ($isAdmin) {
                    $spikapRoleLabel = 'Administrator SPIKAP';
                } elseif ($isKs) {
                    $spikapRoleLabel = 'Kepala Sekolah';
                } elseif ($isBk) {
                    $spikapRoleLabel = 'Guru Bimbingan Konseling (BK)';
                } elseif ($isWk) {
                    $spikapRoleLabel = 'Wali Kelas';
                }

                if ($canAccessSpikap) {
                    $spikapCount = SpikapLaporan::forUser($user)->count();
                    $spikapAktifCount = SpikapLaporan::forUser($user)->aktif()->count();
                    $spikapDaruratCount = SpikapLaporan::forUser($user)->darurat()->aktif()->count();
                }
            } catch (\Throwable $e) {}
        }

        return response()->json([
            'success' => true,
            'data' => [
                'teacher_name' => $teacher?->name ?? $user?->display_name ?? $user?->name ?? 'Bapak/Ibu Guru',
                'nip' => $teacher?->nip ?? '-',
                'role_summary' => $accessInfo['role_summary'],
                'is_bk' => $accessInfo['is_bk'],
                'kelas_ampu_count' => $kelasAmpuCount,
                'total_students_count' => $totalStudentsCount,
                'jurnal_hari_ini_count' => $jurnalHariIniCount,
                'total_jurnal_count' => $totalJurnalCount,
                'pending_leave_count' => $pendingLeaveCount,
                'approved_leave_count' => $approvedLeaveCount,
                'rejected_leave_count' => $rejectedLeaveCount,
                'active_books_count' => $activeBooksCount,
                'pending_konsultasi_count' => $pendingKonsultasiCount,
                'can_access_spikap' => $canAccessSpikap,
                'spikap_role_label' => $spikapRoleLabel,
                'spikap_count' => $spikapCount,
                'spikap_aktif_count' => $spikapAktifCount,
                'spikap_darurat_count' => $spikapDaruratCount,
                'enable_sholat_dhuhur' => (bool) ($sekolah?->enable_sholat_dhuhur ?? true),
            ],
        ]);
    }

    /**
     * GET /api/v1/guru/presensi/dashboard
     * Mengembalikan seluruh data dashboard presensi kelas sesuai /portal-guru/akademik (WaliKelasDashboard).
     * Revisi:
     * 1. Kunci Tahun Ajaran ke Tahun Ajaran Aktif.
     * 2. Sortir & batasi kelas sesuai Wali Kelas (1-2 kelas) atau Kelas Binaan BK yang dipilih di Manajemen Akses Portal.
     */
    public function dashboard(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);
        $sekolah = PengaturanSekolah::current();
        $enableCheckout = (bool) ($sekolah?->enable_checkout ?? true);

        // 1. Kunci Tahun Ajaran ke Tahun Ajaran Aktif
        $activeYear = $this->getActiveAcademicYear();
        $selectedAcademicYearId = $activeYear?->id;
        $academicYears = $activeYear ? collect([$activeYear]) : collect();

        // 2. Daftar Bulan Kehadiran dalam Tahun Ajaran Aktif
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
                $availableMonths[] = ['key' => $key, 'label' => $monthNames[$m] . ' ' . $sy];
            }
            for ($m = 1; $m <= 6; $m++) {
                $key = sprintf('%02d-%d', $m, $ey);
                $availableMonths[] = ['key' => $key, 'label' => $monthNames[$m] . ' ' . $ey];
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

        // 3. Daftar Kelas Binaan Wali Kelas / Binaan Guru BK (Tanpa Fallback Semua Kelas)
        $accessInfo = $this->getAccessibleClasses($user, $selectedAcademicYearId);
        $classes = $accessInfo['classes'];

        $selectedClassId = $request->input('class_id');
        if (!$selectedClassId || !$classes->contains('id', $selectedClassId)) {
            $selectedClassId = $classes->first()['id'] ?? null;
        }

        $currentKelas = $classes->firstWhere('id', $selectedClassId);
        $className = $currentKelas['name'] ?? '-';
        $classAccessLabel = $currentKelas['access_label'] ?? $accessInfo['role_summary'];
        $waliKelasName = $user?->teacher?->name ?? $user?->name ?? 'Wali Kelas';

        // 4. Hitung Statistik Harian & Matriks Bulanan via PresensiRekapService
        $todayStats = [
            'hadir' => 0, 'telat' => 0, 'izin' => 0, 'sakit' => 0,
            'alpa' => 0, 'belum' => 0, 'total' => 0, 'persentase_hadir' => 0,
        ];
        $classMonthlyStats = [
            'hadir' => 0, 'telat' => 0, 'izin' => 0, 'sakit' => 0,
            'alpa' => 0, 'effective_days' => 0, 'max_possible' => 0, 'total_students' => 0,
        ];
        $alerts = ['alpa' => [], 'telat' => []];
        $daysInMonth = (int) date('t');
        $todayDate = Carbon::now('Asia/Jakarta')->toDateString();
        $studentsFormatted = [];

        $isNotifBelumActive = (bool) (PresensiNotificationSetting::where('status_presensi', 'belum')->value('is_active') ?? true);
        $isNotifTelatActive = (bool) (PresensiNotificationSetting::where('status_presensi', 'telat')->value('is_active') ?? true);

        if ($selectedClassId && $selectedAcademicYearId && $selectedMonthYear) {
            $parts = explode('-', $selectedMonthYear);
            $month = $parts[0] ?? date('m');
            $year = (int) ($parts[1] ?? date('Y'));

            $service = app(PresensiRekapService::class);
            $result = $service->getMonthlyCalendarData(
                $selectedAcademicYearId,
                $selectedClassId,
                $month,
                $year
            );

            $students = $result['students'] ?? collect();
            $monthlyStats = $result['monthlyStats'] ?? [];
            $classMonthlyStats = $result['classMonthlyStats'] ?? $classMonthlyStats;
            $todayStats = $result['todayStats'] ?? $todayStats;
            $todayAttendances = $result['todayAttendances'] ?? [];
            $alerts = $result['alerts'] ?? $alerts;
            $daysInMonth = $result['daysInMonth'] ?? $daysInMonth;
            $todayDate = $result['todayDate'] ?? $todayDate;

            $dateLabel = Carbon::parse($todayDate)->locale('id')->translatedFormat('l, d F Y');

            foreach ($students as $student) {
                $att = $todayAttendances[$student->id] ?? null;
                $status = $att['status'] ?? 'belum';
                $statusPulang = $att['status_pulang'] ?? null;
                $scanDatang = $att['scan_time'] ?? null;
                $scanPulang = $att['scan_out_time'] ?? null;
                $lateMinutes = (int) ($att['late_minutes'] ?? 0);
                $note = $att['note'] ?? null;

                $rawPhone = $student->no_hp_orang_tua ?: $student->no_hp;
                $cleanPhone = preg_replace('/[^0-9]/', '', $rawPhone ?? '');
                if (!empty($cleanPhone)) {
                    if (str_starts_with($cleanPhone, '0')) {
                        $cleanPhone = '62' . substr($cleanPhone, 1);
                    } elseif (str_starts_with($cleanPhone, '8')) {
                        $cleanPhone = '62' . $cleanPhone;
                    }
                }

                $waTextBelum = '';
                $waTextTelat = '';
                try {
                    $waTextBelum = PresensiNotificationSetting::renderTemplate('belum', [
                        'nama_siswa'       => $student->name,
                        'kelas'            => $className,
                        'tanggal'          => $dateLabel,
                        'status_kehadiran' => 'Belum Presensi',
                        'nama_wali_kelas'  => $waliKelasName,
                    ]);
                    $waTextTelat = PresensiNotificationSetting::renderTemplate('telat', [
                        'nama_siswa'       => $student->name,
                        'kelas'            => $className,
                        'tanggal'          => $dateLabel,
                        'jam'              => $scanDatang ?: '-',
                        'status_kehadiran' => 'Terlambat ' . ($lateMinutes ? "({$lateMinutes} menit)" : ''),
                        'nama_wali_kelas'  => $waliKelasName,
                    ]);
                } catch (\Throwable $e) {
                    $waTextBelum = "Assalamu'alaikum Wr. Wb. Bapak/Ibu Orang Tua/Wali dari *{$student->name}* (Kelas {$className}). Menginfokan bahwa pada hari {$dateLabel}, ananda tercatat *Belum Presensi*. Terima kasih. - {$waliKelasName}";
                    $waTextTelat = "Assalamu'alaikum Wr. Wb. Bapak/Ibu Orang Tua/Wali dari *{$student->name}* (Kelas {$className}). Menginfokan bahwa pada hari {$dateLabel}, ananda hadir pada pukul {$scanDatang} WIB (*Terlambat {$lateMinutes} menit*). Terima kasih. - {$waliKelasName}";
                }

                $mStat = $monthlyStats[$student->id] ?? [
                    'hadir' => 0, 'telat' => 0, 'izin' => 0, 'sakit' => 0,
                    'alpa' => 0, 'total_late_minutes' => 0, 'daily' => [],
                ];

                $studentsFormatted[] = [
                    'id' => $student->id,
                    'name' => $student->name,
                    'nisn' => $student->nisn ?? '-',
                    'nis' => $student->nis ?? '-',
                    'gender' => $student->gender ?? '-',
                    'phone' => $rawPhone ?? '',
                    'clean_phone' => $cleanPhone ?? '',
                    'today' => [
                        'status' => $status,
                        'status_pulang' => $statusPulang,
                        'scan_time' => $scanDatang,
                        'scan_out_time' => $scanPulang,
                        'late_minutes' => $lateMinutes,
                        'note' => $note,
                    ],
                    'wa_text_belum' => $waTextBelum,
                    'wa_text_telat' => $waTextTelat,
                    'monthly' => [
                        'hadir' => (int) ($mStat['hadir'] ?? 0),
                        'telat' => (int) ($mStat['telat'] ?? 0),
                        'izin' => (int) ($mStat['izin'] ?? 0),
                        'sakit' => (int) ($mStat['sakit'] ?? 0),
                        'alpa' => (int) ($mStat['alpa'] ?? 0),
                        'total_late_minutes' => (int) ($mStat['total_late_minutes'] ?? 0),
                        'daily' => $mStat['daily'] ?? [],
                        'is_alpa_warning' => in_array($student->id, $alerts['alpa'] ?? []),
                        'is_telat_warning' => in_array($student->id, $alerts['telat'] ?? []),
                    ],
                ];
            }
        }

        $todayDateLabel = Carbon::parse($todayDate)->locale('id')->translatedFormat('l, d F Y');
        $selectedMonthLabel = collect($availableMonths)->firstWhere('key', $selectedMonthYear)['label'] ?? $selectedMonthYear;

        return response()->json([
            'success' => true,
            'data' => [
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
                'today_date' => $todayDate,
                'today_date_label' => $todayDateLabel,
                'days_in_month' => $daysInMonth,
                'enable_checkout' => $enableCheckout,
                'is_notif_belum_active' => $isNotifBelumActive,
                'is_notif_telat_active' => $isNotifTelatActive,
                'today_stats' => $todayStats,
                'class_monthly_stats' => $classMonthlyStats,
                'alerts' => $alerts,
                'students' => $studentsFormatted,
            ],
        ]);
    }

    /**
     * GET /api/v1/guru/presensi/manual-input
     * Memuat daftar siswa untuk Modal Input Presensi Manual pada tanggal tertentu.
     */
    public function getManualInput(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);
        $activeYear = $this->getActiveAcademicYear();
        $academicYearId = $activeYear?->id ?: $request->input('academic_year_id');
        $classId = $request->input('class_id');
        $inputDate = $request->input('date') ?: Carbon::now('Asia/Jakarta')->toDateString();

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

        $sekolah = PengaturanSekolah::current();
        $enableCheckout = (bool) ($sekolah?->enable_checkout ?? true);

        $kalenderService = app(KalenderSekolahService::class);
        $isHoliday = !$kalenderService->isHariSekolah(Carbon::parse($inputDate), $classId);

        $kelas = Kelas::with(['enrollments' => function ($q) use ($academicYearId) {
            $q->where('academic_year_id', $academicYearId)
              ->where('status', 'aktif')
              ->with('siswa');
        }])->find($classId);

        $students = $kelas ? $kelas->enrollments->pluck('siswa')->filter() : collect();

        $attendances = Presensi::where('academic_year_id', $academicYearId)
            ->where('class_id', $classId)
            ->where('date', $inputDate)
            ->get()
            ->keyBy('student_id');

        $list = [];
        foreach ($students as $student) {
            $att = $attendances->get($student->id);
            $isLocked = ($att && $att->is_manual_input === false && !empty($att->scan_time) && in_array($att->status, ['hadir', 'telat']));

            $list[] = [
                'id' => $student->id,
                'name' => $student->name,
                'nisn' => $student->nisn ?? '-',
                'status' => $att ? $att->status : '',
                'status_pulang' => $att ? ($att->status_pulang ?? '') : '',
                'late_minutes' => $att ? (int) ($att->late_minutes ?? 0) : 0,
                'is_manual_input' => $att ? (bool) $att->is_manual_input : null,
                'is_locked' => $isLocked,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'date' => $inputDate,
                'date_label' => Carbon::parse($inputDate)->locale('id')->translatedFormat('l, d F Y'),
                'is_holiday' => $isHoliday,
                'enable_checkout' => $enableCheckout,
                'students' => $list,
            ],
        ]);
    }

    /**
     * POST /api/v1/guru/presensi/manual-input
     * Menyimpan hasil Input Presensi Manual sesuai logika WaliKelasDashboard::saveManualInput().
     */
    public function saveManualInput(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);
        $activeYear = $this->getActiveAcademicYear();
        $academicYearId = $activeYear?->id ?: $request->input('academic_year_id');
        $classId = $request->input('class_id');
        $inputDate = $request->input('date');
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
                    'message' => 'Anda tidak memiliki akses untuk mengubah presensi kelas ini.',
                ], 403);
            }
        }

        $kalenderService = app(KalenderSekolahService::class);
        if (!$kalenderService->isHariSekolah(Carbon::parse($inputDate), $classId)) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat menyimpan presensi pada hari libur!',
            ], 422);
        }

        $isGuru = $user && $user->teacher !== null && !$user->hasAnyRole(['super_admin', 'admin_presensi']);
        $actor = $isGuru ? $user->teacher : $user;
        $actorType = $isGuru ? 'Guru' : 'Admin';
        $savedCount = 0;

        foreach ($inputStudents as $data) {
            if (empty($data['status']) || empty($data['id'])) {
                continue;
            }

            $studentId = $data['id'];
            $enrollment = EnrollmentSiswa::where('student_id', $studentId)
                ->where('academic_year_id', $academicYearId)
                ->where('status', 'aktif')
                ->first();

            $existing = Presensi::where('student_id', $studentId)
                ->where('date', $inputDate)
                ->first();

            $newStatusPulang = empty($data['status_pulang']) ? null : $data['status_pulang'];
            if (in_array($data['status'], ['izin', 'sakit', 'alpa'])) {
                $newStatusPulang = $data['status'];
            }

            $isKioskScan = ($existing && $existing->is_manual_input === false && !empty($existing->scan_time) && in_array($existing->status, ['hadir', 'telat']));

            if ($isKioskScan) {
                $newLate = $existing->late_minutes;
                $data['status'] = $existing->status;
                if ($existing->status_pulang === $newStatusPulang) {
                    continue;
                }
            } else {
                $newLate = ($data['status'] === 'telat') ? ((int) ($data['late_minutes'] ?? 0)) : 0;
            }

            if ($existing && $existing->status === $data['status'] && $existing->late_minutes == $newLate && $existing->status_pulang === $newStatusPulang) {
                continue;
            }

            $note = null;
            if ($existing) {
                $strLama = $existing->status === 'telat' ? "Telat ({$existing->late_minutes} mnt)" : ucfirst($existing->status);
                $strBaru = $data['status'] === 'telat' ? "Telat ({$newLate} mnt)" : ucfirst($data['status']);
                $strPulangLama = $existing->status_pulang ? ucfirst($existing->status_pulang) : 'Kosong';
                $strPulangBaru = $newStatusPulang ? ucfirst($newStatusPulang) : 'Kosong';
                $appendNote = "Diedit oleh {$actorType}: " . ($actor ? $actor->name : 'Sistem') . " (Datang: {$strLama}->{$strBaru}, Pulang: {$strPulangLama}->{$strPulangBaru})";
                $note = $existing->note ? $existing->note . ' | ' . $appendNote : $appendNote;
            } else {
                $note = "Diinput Manual oleh {$actorType}: " . ($actor ? $actor->name : 'Sistem');
            }

            Presensi::updateOrCreate(
                [
                    'student_id' => $studentId,
                    'class_id' => $classId,
                    'academic_year_id' => $academicYearId,
                    'date' => $inputDate,
                ],
                [
                    'enrollment_id' => $enrollment?->id,
                    'status' => $data['status'],
                    'status_pulang' => $newStatusPulang,
                    'late_minutes' => $newLate,
                    'is_manual_input' => $isKioskScan ? false : true,
                    'manual_input_by_id' => $actor?->id,
                    'manual_input_by_type' => $isGuru ? Guru::class : User::class,
                    'note' => $note,
                    'scan_time' => $existing && $existing->scan_time ? $existing->scan_time : now()->toTimeString(),
                    'scan_out_time' => ($newStatusPulang && (!$existing || !$existing->scan_out_time)) ? now()->toTimeString() : ($existing ? $existing->scan_out_time : null),
                ]
            );
            $savedCount++;
        }

        return response()->json([
            'success' => true,
            'message' => "Presensi tanggal {$inputDate} berhasil disimpan ({$savedCount} perubahan)!",
        ]);
    }

    /**
     * POST /api/v1/guru/presensi/send-wa
     * Mengirim pesan WhatsApp ke orang tua siswa melalui WhatsAppGatewayService.
     */
    public function sendWaDirect(Request $request): JsonResponse
    {
        $studentId = $request->input('student_id');
        $targetPhone = $request->input('phone');
        $messageText = $request->input('message');
        $type = $request->input('type', 'belum');
        $classId = $request->input('class_id');

        if (empty($studentId) || empty($targetPhone) || empty($messageText)) {
            return response()->json([
                'success' => false,
                'message' => 'Nomor WhatsApp dan isi pesan tidak boleh kosong.',
            ], 422);
        }

        $notifSetting = PresensiNotificationSetting::where('status_presensi', $type)->first();
        if ($notifSetting && !$notifSetting->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Fitur notifikasi WhatsApp untuk kategori ini dinonaktifkan oleh Administrator.',
            ], 422);
        }

        $kalenderService = app(KalenderSekolahService::class);
        if (!$kalenderService->isHariSekolah(now('Asia/Jakarta'), $classId)) {
            return response()->json([
                'success' => false,
                'message' => 'Hari ini adalah hari libur sekolah. Pengiriman notifikasi WA dinonaktifkan.',
            ], 422);
        }

        $cleanPhone = preg_replace('/[^0-9]/', '', $targetPhone);
        if (empty($cleanPhone)) {
            return response()->json([
                'success' => false,
                'message' => 'Nomor WhatsApp orang tua tidak valid.',
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
            // Tangani kegagalan koneksi gateway
        }

        return response()->json([
            'success' => false,
            'message' => 'Gagal mengirim pesan WhatsApp via sistem gateway. Silakan periksa koneksi WhatsApp Gateway.',
        ], 500);
    }

    /**
     * GET /api/v1/guru/presensi/export
     * Mengunduh laporan presensi dalam bentuk PDF atau Excel (Bulanan, Semester, Tahunan).
     */
    public function exportReport(Request $request)
    {
        $activeYear = $this->getActiveAcademicYear();
        $academicYearId = $activeYear?->id ?: $request->input('academic_year_id');
        $classId = $request->input('class_id');
        $cetakJenis = $request->input('jenis', 'bulanan'); // bulanan | semester | tahunan
        $cetakBulanYear = $request->input('month_year', date('m-Y'));
        $cetakSemester = $request->input('semester', (int) date('m') >= 7 ? 'ganjil' : 'genap');
        $format = strtolower($request->input('format', 'pdf')); // pdf | excel

        $ay = $activeYear ?: TahunAjaran::find($academicYearId);
        $kelas = Kelas::find($classId);
        $sekolah = PengaturanSekolah::current();

        if (!$ay || !$kelas) {
            return response()->json([
                'success' => false,
                'message' => 'Kelas dan Tahun Ajaran tidak ditemukan.',
            ], 404);
        }

        if ($cetakJenis === 'bulanan') {
            $parts = explode('-', $cetakBulanYear);
            $month = (int) ($parts[0] ?? date('m'));
            $year = (int) ($parts[1] ?? date('Y'));
            $start = Carbon::create($year, $month, 1)->startOfMonth();
            $end = Carbon::create($year, $month, 1)->endOfMonth();
            $monthNames = [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
            ];
            $label = 'Bulan ' . ($monthNames[$month] ?? $month) . ' ' . $year;
        } elseif ($cetakJenis === 'semester') {
            if ($cetakSemester === 'ganjil') {
                $start = Carbon::create($ay->start_year, 7, 1)->startOfMonth();
                $end = Carbon::create($ay->start_year, 12, 31)->endOfMonth();
                $label = 'Semester Ganjil TA ' . $ay->name;
            } else {
                $start = Carbon::create($ay->end_year, 1, 1)->startOfMonth();
                $end = Carbon::create($ay->end_year, 6, 30)->endOfMonth();
                $label = 'Semester Genap TA ' . $ay->name;
            }
        } else {
            $start = Carbon::create($ay->start_year, 7, 1)->startOfMonth();
            $end = Carbon::create($ay->end_year, 6, 30)->endOfMonth();
            $label = 'Tahun Ajaran ' . $ay->name;
        }

        $safeClassName = str_replace(['/', '\\'], '-', $kelas->name);
        $safeLabel = str_replace([' ', '/', '\\'], ['_', '-', '-'], $label);

        if ($format === 'excel') {
            $fileName = 'Laporan_Presensi_' . $safeClassName . '_' . $safeLabel . '.xlsx';
            if ($cetakJenis === 'bulanan') {
                $parts = explode('-', $cetakBulanYear);
                $month = $parts[0] ?? date('m');
                $year = (int) ($parts[1] ?? date('Y'));
                return Excel::download(
                    new PresensiMatrixExport($classId, $academicYearId, $month, $year, $label),
                    $fileName
                );
            }
            return Excel::download(
                new LaporanPresensiRangeExport($classId, $academicYearId, $start->toDateString(), $end->toDateString(), $label),
                $fileName
            );
        }

        // Format PDF
        $fileName = 'Laporan_Presensi_' . $safeClassName . '_' . $safeLabel . '.pdf';
        $service = app(PresensiRekapService::class);

        if ($cetakJenis === 'bulanan') {
            $parts = explode('-', $cetakBulanYear);
            $month = $parts[0] ?? date('m');
            $year = (int) ($parts[1] ?? date('Y'));
            $result = $service->getMonthlyCalendarData($academicYearId, $classId, $month, $year);

            $pdf = Pdf::loadView('pdf.laporan-presensi-matrix', [
                'students' => $result['students'],
                'monthlyStats' => $result['monthlyStats'],
                'daysInMonth' => $result['daysInMonth'],
                'periodeLabel' => $label,
                'kelas' => $kelas,
                'sekolah' => $sekolah,
                'generatedAt' => now()->locale('id')->translatedFormat('l, d F Y H:i'),
            ])->setPaper('a4', 'landscape');

            return $pdf->download($fileName);
        }

        $result = $service->getStudentSemesterYearlyData($academicYearId, $classId, $start->toDateString(), $end->toDateString());
        $pdf = Pdf::loadView('pdf.laporan-presensi-range', [
            'studentsData' => $result['studentsData'],
            'monthsList' => $result['monthsList'],
            'jenisLaporan' => $cetakJenis,
            'periodeLabel' => $label,
            'kelas' => $kelas,
            'sekolah' => $sekolah,
            'generatedAt' => now()->locale('id')->translatedFormat('l, d F Y H:i'),
        ])->setPaper('a4', 'landscape');

        return $pdf->download($fileName);
    }

    /**
     * GET /api/v1/guru/presensi/student/{id}
     * Detail riwayat presensi individual siswa (mengikuti WaliKelasStudentDetail).
     */
    public function studentDetail(Request $request, string $id): JsonResponse
    {
        $student = Siswa::find($id);
        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Data siswa tidak ditemukan.',
            ], 404);
        }

        $enrollment = $student->enrollmentAktif()->with(['kelas', 'tahunAjaran'])->first();
        if (!$enrollment) {
            $enrollment = $student->enrollments()->with(['kelas', 'tahunAjaran'])->latest()->first();
        }

        $availableMonths = [];
        if ($enrollment && $enrollment->tahunAjaran) {
            $sy = $enrollment->tahunAjaran->start_year;
            $ey = $enrollment->tahunAjaran->end_year;
            $monthNames = [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
            ];
            for ($m = 7; $m <= 12; $m++) {
                $key = sprintf('%02d-%d', $m, $sy);
                $availableMonths[] = ['key' => $key, 'label' => $monthNames[$m] . ' ' . $sy];
            }
            for ($m = 1; $m <= 6; $m++) {
                $key = sprintf('%02d-%d', $m, $ey);
                $availableMonths[] = ['key' => $key, 'label' => $monthNames[$m] . ' ' . $ey];
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

        $parts = explode('-', $selectedMonthYear);
        $month = (int) ($parts[0] ?? date('m'));
        $year = (int) ($parts[1] ?? date('Y'));

        $startOfMonth = Carbon::createFromDate($year, $month, 1);
        $daysInMonth = $startOfMonth->daysInMonth;
        $startOfMonthOffset = $startOfMonth->dayOfWeekIso - 1;

        $records = Presensi::where('student_id', $student->id)
            ->when($enrollment, fn($q) => $q->where('enrollment_id', $enrollment->id))
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->orderBy('date', 'desc')
            ->get();

        $attendanceData = [];
        $recentActivity = [];
        $totalH = 0;
        $totalT = 0;
        $totalS = 0;
        $totalI = 0;
        $totalA = 0;
        $totalLateMinutes = 0;

        foreach ($records as $p) {
            $day = (int) Carbon::parse($p->date)->format('j');
            $status = strtolower($p->status);
            $attendanceData[$day] = [
                'status' => $status,
                'late_minutes' => (int) ($p->late_minutes ?? 0),
                'scan_time' => $p->scan_time ? substr($p->scan_time, 0, 5) : null,
                'scan_out_time' => $p->scan_out_time ? substr($p->scan_out_time, 0, 5) : null,
                'date' => Carbon::parse($p->date)->format('Y-m-d'),
                'date_label' => Carbon::parse($p->date)->locale('id')->translatedFormat('l, d M Y'),
                'is_manual_input' => (bool) $p->is_manual_input,
                'note' => $p->note,
            ];

            if (count($recentActivity) < 10) {
                $recentActivity[] = $attendanceData[$day];
            }

            if ($status === 'hadir') $totalH++;
            if ($status === 'telat') {
                $totalT++;
                $totalLateMinutes += (int) ($p->late_minutes ?? 0);
            }
            if ($status === 'sakit') $totalS++;
            if ($status === 'izin') $totalI++;
            if ($status === 'alpa') $totalA++;
        }

        $kalenderService = app(KalenderSekolahService::class);
        $holidays = [];
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $dateObj = Carbon::create($year, $month, $day);
            $holidays[$day] = $enrollment
                ? !$kalenderService->isHariSekolah($dateObj, $enrollment->class_id)
                : $dateObj->isWeekend();
        }

        $endCalcDate = $startOfMonth->copy()->endOfMonth();
        if ($year == (int) date('Y') && $month == (int) date('m')) {
            $endCalcDate = Carbon::today('Asia/Jakarta');
        }
        $effectiveDays = $enrollment
            ? max(1, $kalenderService->getEffectiveDays($startOfMonth, $endCalcDate, $enrollment->class_id))
            : 20;
        $attendancePercentage = min(100, round((($totalH + $totalT) / $effectiveDays) * 100, 1));

        return response()->json([
            'success' => true,
            'data' => [
                'student' => [
                    'id' => $student->id,
                    'name' => $student->name,
                    'nisn' => $student->nisn ?? '-',
                    'nis' => $student->nis ?? '-',
                    'gender' => $student->gender ?? '-',
                    'class_name' => $enrollment?->kelas?->name ?? '-',
                    'academic_year' => $enrollment?->tahunAjaran?->name ?? '-',
                    'phone_ortu' => $student->no_hp_orang_tua ?: ($student->no_hp ?? '-'),
                ],
                'available_months' => $availableMonths,
                'selected_month_year' => $selectedMonthYear,
                'days_in_month' => $daysInMonth,
                'start_offset' => $startOfMonthOffset,
                'attendance_percentage' => $attendancePercentage,
                'stats' => [
                    'H' => $totalH,
                    'T' => $totalT,
                    'S' => $totalS,
                    'I' => $totalI,
                    'A' => $totalA,
                    'late_minutes' => $totalLateMinutes,
                ],
                'holidays' => $holidays,
                'attendance_data' => $attendanceData,
                'recent_activity' => $recentActivity,
            ],
        ]);
    }
}

