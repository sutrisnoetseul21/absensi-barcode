<?php

namespace App\Http\Controllers\Api\Mobile\Siswa;

use App\Models\Presensi;
use App\Models\PresensiSholatDhuhur;
use App\Models\LeaveRequest;
use App\Models\KonsultasiGuruWali;
use App\Models\Peminjaman;
use App\Models\SpikapLaporan;
use App\Models\PengaturanSekolah;
use App\Services\KalenderSekolahService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SiswaDashboardController extends BaseSiswaApiController
{
    /**
     * GET /api/v1/siswa/dashboard-summary
     * Menyediakan ringkasan profil, status presensi hari ini, sholat dhuhur, dan badge 7 menu utama.
     */
    public function dashboardSummary(Request $request): JsonResponse
    {
        $student = $this->resolveStudent($request);
        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Data profil siswa tidak ditemukan. Pastikan Anda telah login dengan akun siswa atau menyertakan NISN.',
            ], 404);
        }

        $activeYear = $this->getActiveAcademicYear();
        $enrollment = $student->enrollmentAktif()->with(['kelas', 'tahunAjaran'])->first()
            ?: $student->enrollments()->with(['kelas', 'tahunAjaran'])->latest()->first();

        $className = $enrollment?->kelas?->name ?? 'Kelas Siswa';
        $academicYearName = $enrollment?->tahunAjaran?->name ?? ($activeYear?->name ?? 'Tahun Ajaran Aktif');
        $classId = $enrollment?->class_id;

        $today = Carbon::today('Asia/Jakarta');
        $todayDateStr = $today->toDateString();

        // 1. Cek Presensi Hari Ini
        $presensi = Presensi::where('student_id', $student->id)
            ->whereDate('date', $todayDateStr)
            ->first();

        $kalenderService = app(KalenderSekolahService::class);
        $isHolidayToday = !$kalenderService->isHariSekolah($today, $classId);

        $attendanceToday = [
            'date' => $todayDateStr,
            'day_name' => $today->translatedFormat('l'),
            'formatted_date' => $today->translatedFormat('d F Y'),
            'has_scanned_in' => false,
            'scan_in_time' => null,
            'has_scanned_out' => false,
            'scan_out_time' => null,
            'status' => 'belum_hadir',
            'status_label' => 'Belum Presensi',
            'late_minutes' => 0,
            'is_holiday' => $isHolidayToday,
        ];

        if ($presensi) {
            $statusLower = strtolower($presensi->status);
            $attendanceToday['has_scanned_in'] = in_array($statusLower, ['hadir', 'telat']);
            $attendanceToday['scan_in_time'] = $presensi->scan_time ? substr($presensi->scan_time, 0, 5) : null;
            $attendanceToday['has_scanned_out'] = !empty($presensi->scan_out_time);
            $attendanceToday['scan_out_time'] = $presensi->scan_out_time ? substr($presensi->scan_out_time, 0, 5) : null;
            $attendanceToday['status'] = $statusLower;
            $attendanceToday['late_minutes'] = (int) ($presensi->late_minutes ?? 0);

            $attendanceToday['status_label'] = match ($statusLower) {
                'hadir' => 'Tepat Waktu',
                'telat' => 'Terlambat (' . $attendanceToday['late_minutes'] . ' Menit)',
                'sakit' => 'Sakit',
                'izin' => 'Izin',
                'alpa' => 'Alpa',
                default => ucfirst($statusLower),
            };
        } elseif ($isHolidayToday) {
            $attendanceToday['status'] = 'libur';
            $attendanceToday['status_label'] = 'Hari Libur';
        }

        // 2. Cek Sholat Dhuhur Hari Ini
        $sekolah = PengaturanSekolah::current();
        $enableSholat = $sekolah?->enable_sholat_dhuhur ?? true;
        $sholatToday = [
            'enabled' => $enableSholat,
            'status' => 'belum',
            'status_label' => 'Belum Sholat',
            'is_holiday' => $isHolidayToday,
        ];

        if ($enableSholat) {
            $sholatRecord = PresensiSholatDhuhur::where('student_id', $student->id)
                ->whereDate('date', $todayDateStr)
                ->first();

            if ($sholatRecord) {
                $sholatLower = strtolower($sholatRecord->status);
                $sholatToday['status'] = $sholatLower;
                $sholatToday['status_label'] = match ($sholatLower) {
                    'berjamaah' => 'Berjamaah',
                    'masbuk' => 'Masbuk',
                    'halangan' => 'Halangan/Haid',
                    'alpha', 'tidak_sholat' => 'Tidak Hadir',
                    default => ucfirst($sholatLower),
                };
            } elseif ($isHolidayToday) {
                $sholatToday['status'] = 'libur';
                $sholatToday['status_label'] = 'Hari Libur';
            }
        }

        // 3. Quick Stats untuk 7 Menu
        $pendingLeavesCount = LeaveRequest::where('student_id', $student->id)
            ->where('status', 'pending')
            ->count();

        $activeCounselingCount = KonsultasiGuruWali::where('student_id', $student->id)
            ->whereIn('status_pengajuan', ['menunggu', 'terjadwal'])
            ->count();

        $activeLoansCount = Peminjaman::where('peminjam_type', 'siswa')
            ->where('peminjam_id', $student->id)
            ->where('status', 'dipinjam')
            ->count();

        $spikapReportsCount = SpikapLaporan::where('student_id', $student->id)->count();

        // 4. Identitas Sekolah & Logo
        $schoolLogoUrl = null;
        if (!empty($sekolah?->logo_path)) {
            $schoolLogoUrl = asset('storage/' . $sekolah->logo_path);
        }

        return response()->json([
            'success' => true,
            'message' => 'Ringkasan dashboard siswa berhasil dimuat.',
            'data' => [
                'student' => [
                    'id' => $student->id,
                    'name' => $student->name,
                    'nisn' => $student->nisn,
                    'nis' => $student->nis,
                    'gender' => $student->gender ?? 'L',
                    'avatar_url' => $student->avatar_url,
                    'class_id' => $classId,
                    'class_name' => $className,
                    'academic_year' => $academicYearName,
                    'barcode_code' => $student->barcode_code ?? $student->nisn,
                    'no_hp' => $student->no_hp,
                ],
                'school' => [
                    'name' => $sekolah?->nama_sekolah ?? 'SMP Negeri 3 Kedungreja',
                    'logo_url' => $schoolLogoUrl,
                ],
                'today_attendance' => $attendanceToday,
                'today_sholat' => $sholatToday,
                'quick_stats' => [
                    'pending_leaves_count' => $pendingLeavesCount,
                    'active_counseling_count' => $activeCounselingCount,
                    'active_loans_count' => $activeLoansCount,
                    'spikap_reports_count' => $spikapReportsCount,
                ],
            ],
        ]);
    }

    /**
     * GET /api/v1/siswa/akademik/dashboard
     * Menu 1: Kalender Matriks Presensi Bulanan (Tanggal 1..31), Statistik Kehadiran, & Riwayat Aktivitas.
     */
    public function akademikDashboard(Request $request): JsonResponse
    {
        $student = $this->resolveStudent($request);
        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Data profil siswa tidak ditemukan.',
            ], 404);
        }

        $activeYear = $this->getActiveAcademicYear();
        $enrollment = $student->enrollmentAktif()->with(['kelas', 'tahunAjaran'])->first()
            ?: $student->enrollments()->with(['kelas', 'tahunAjaran'])->latest()->first();

        // Bangun Daftar Bulan Tahun Ajaran
        $availableMonths = [];
        $tahunAjaran = $enrollment?->tahunAjaran ?? $activeYear;
        if ($tahunAjaran) {
            $sy = $tahunAjaran->start_year;
            $ey = $tahunAjaran->end_year;
            $monthNames = [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
            ];

            for ($m = 7; $m <= 12; $m++) {
                $key = sprintf('%02d-%d', $m, $sy);
                $availableMonths[] = [
                    'key' => $key,
                    'month' => $m,
                    'year' => $sy,
                    'label' => $monthNames[$m] . ' ' . $sy,
                ];
            }
            for ($m = 1; $m <= 6; $m++) {
                $key = sprintf('%02d-%d', $m, $ey);
                $availableMonths[] = [
                    'key' => $key,
                    'month' => $m,
                    'year' => $ey,
                    'label' => $monthNames[$m] . ' ' . $ey,
                ];
            }
        }

        // Tentukan Bulan & Tahun Terpilih
        $reqMonthYear = $request->input('month_year');
        $month = (int) ($request->input('month') ?: date('n'));
        $year = (int) ($request->input('year') ?: date('Y'));

        if (!empty($reqMonthYear) && str_contains($reqMonthYear, '-')) {
            $parts = explode('-', $reqMonthYear);
            $month = (int) $parts[0];
            $year = (int) $parts[1];
        }

        $startOfMonth = Carbon::createFromDate($year, $month, 1, 'Asia/Jakarta');
        $daysInMonth = $startOfMonth->daysInMonth;
        $startOfMonthOffset = $startOfMonth->dayOfWeekIso - 1; // 0 = Senin

        // Ambil Data Presensi Siswa pada Bulan Ini
        $presensiQuery = Presensi::where('student_id', $student->id)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->orderBy('date', 'desc');

        if ($enrollment) {
            $presensiQuery->where('enrollment_id', $enrollment->id);
        }

        $records = $presensiQuery->get();

        $attendanceMatrix = [];
        $recentActivity = [];
        $totalH = 0;
        $totalT = 0;
        $totalS = 0;
        $totalI = 0;
        $totalA = 0;
        $totalLateMinutes = 0;

        foreach ($records as $p) {
            $day = (int) date('j', strtotime($p->date));
            $stLower = strtolower($p->status);

            $attendanceMatrix[$day] = [
                'day' => $day,
                'status' => $stLower,
                'late_minutes' => (int) ($p->late_minutes ?? 0),
                'scan_time' => $p->scan_time ? substr($p->scan_time, 0, 5) : null,
                'scan_out_time' => $p->scan_out_time ? substr($p->scan_out_time, 0, 5) : null,
                'date' => is_string($p->date) ? substr($p->date, 0, 10) : $p->date->format('Y-m-d'),
                'is_manual_input' => (bool) $p->is_manual_input,
            ];

            if (count($recentActivity) < 5) {
                $recentActivity[] = [
                    'id' => $p->id,
                    'date' => is_string($p->date) ? substr($p->date, 0, 10) : $p->date->format('Y-m-d'),
                    'formatted_date' => Carbon::parse($p->date)->translatedFormat('l, d M Y'),
                    'status' => $stLower,
                    'scan_time' => $p->scan_time ? substr($p->scan_time, 0, 5) : null,
                    'scan_out_time' => $p->scan_out_time ? substr($p->scan_out_time, 0, 5) : null,
                    'late_minutes' => (int) ($p->late_minutes ?? 0),
                    'note' => $p->note,
                ];
            }

            if ($stLower === 'hadir') $totalH++;
            elseif ($stLower === 'telat') {
                $totalT++;
                $totalLateMinutes += (int) $p->late_minutes;
            } elseif ($stLower === 'sakit') $totalS++;
            elseif ($stLower === 'izin') $totalI++;
            elseif ($stLower === 'alpa') $totalA++;
        }

        // Hitung Hari Libur & Hari Efektif
        $kalenderService = app(KalenderSekolahService::class);
        $classId = $enrollment?->class_id;

        $holidays = [];
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $dateObj = Carbon::create($year, $month, $d, 0, 0, 0, 'Asia/Jakarta');
            $holidays[$d] = !$kalenderService->isHariSekolah($dateObj, $classId);
        }

        $endCalcDate = $startOfMonth->copy()->endOfMonth();
        if ($year == date('Y') && $month == date('m')) {
            $endCalcDate = Carbon::today('Asia/Jakarta');
        }

        $effectiveDays = $kalenderService->getEffectiveDays($startOfMonth, $endCalcDate, $classId);
        $effectiveDays = max(1, $effectiveDays);

        $presentCount = $totalH + $totalT;
        $attendancePercentage = min(100, round(($presentCount / $effectiveDays) * 100, 1));

        return response()->json([
            'success' => true,
            'message' => 'Data kalender presensi akademik siswa berhasil dimuat.',
            'data' => [
                'student' => [
                    'name' => $student->name,
                    'nisn' => $student->nisn,
                    'class_name' => $enrollment?->kelas?->name ?? 'Kelas Siswa',
                ],
                'selected_period' => [
                    'month' => $month,
                    'year' => $year,
                    'month_year' => sprintf('%02d-%d', $month, $year),
                    'days_in_month' => $daysInMonth,
                    'start_of_month_offset' => $startOfMonthOffset,
                    'today_date' => date('Y-m-d'),
                ],
                'available_months' => $availableMonths,
                'stats' => [
                    'total_hadir' => $totalH,
                    'total_telat' => $totalT,
                    'total_sakit' => $totalS,
                    'total_izin' => $totalI,
                    'total_alpa' => $totalA,
                    'total_late_minutes' => $totalLateMinutes,
                    'attendance_percentage' => $attendancePercentage,
                    'effective_days' => $effectiveDays,
                ],
                'attendance_matrix' => $attendanceMatrix,
                'holidays' => $holidays,
                'recent_activity' => $recentActivity,
            ],
        ]);
    }
}

