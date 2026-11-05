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
use App\Services\PresensiSholatDhuhurService;
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
        $logoPath = $sekolah?->school_logo_path ?? $sekolah?->logo_path;
        if (!empty($logoPath)) {
            $schoolLogoUrl = asset('storage/' . $logoPath);
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
                    'name' => $sekolah?->school_name ?? $sekolah?->nama_sekolah ?? config('app.name', 'Portal Siswa'),
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
                'attendance_matrix' => (object) $attendanceMatrix,
                'holidays' => (object) $holidays,
                'recent_activity' => $recentActivity,
                'enable_sholat_dhuhur' => (bool) (PengaturanSekolah::current()?->enable_sholat_dhuhur ?? true),
                'enable_presensi_kbm' => (bool) (PengaturanSekolah::current()?->enable_presensi_kbm ?? true),
            ],
        ]);
    }

    /**
     * GET /api/v1/siswa/sholat-dhuhur/dashboard
     * Menu 1 (Tab 2): Kalender Matriks Sholat Dhuhur Bulanan, Statistik Ibadah, & Status Kepatuhan.
     */
    public function sholatDhuhurDashboard(Request $request): JsonResponse
    {
        $student = $this->resolveStudent($request);
        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Data profil siswa tidak ditemukan.',
            ], 404);
        }

        $sekolah = PengaturanSekolah::current();
        $enableSholat = (bool) ($sekolah?->enable_sholat_dhuhur ?? true);

        $activeYear = $this->getActiveAcademicYear();
        $enrollment = $student->enrollmentAktif()->with(['kelas', 'tahunAjaran'])->first()
            ?: $student->enrollments()->with(['kelas', 'tahunAjaran'])->latest()->first();

        $tahunAjaran = $enrollment?->tahunAjaran ?? $activeYear;

        // Bangun Daftar Bulan Tahun Ajaran
        $availableMonths = [];
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
        $todayDate = Carbon::now('Asia/Jakarta')->toDateString();

        $academicYearId = $enrollment?->academic_year_id ?? $activeYear?->id;
        $classId = $enrollment?->class_id;

        $isNonMuslim = !empty($student->religion) && strtolower($student->religion) !== 'islam';

        $sholatService = app(PresensiSholatDhuhurService::class);

        // Ambil data sholat dhuhur bulan ini untuk siswa
        $sholatRecords = PresensiSholatDhuhur::where('student_id', $student->id)
            ->whereYear('date', $year)
            ->whereMonth('date', $month);
        if ($academicYearId) {
            $sholatRecords->where('academic_year_id', $academicYearId);
        }
        $records = $sholatRecords->get()->keyBy(fn ($item) => (int) Carbon::parse($item->date)->day);

        // Ambil data presensi pagi bulan ini untuk sinkronisasi otomatis jika belum ada record sholat
        $morningQuery = Presensi::where('student_id', $student->id)
            ->whereYear('date', $year)
            ->whereMonth('date', $month);
        if ($academicYearId) {
            $morningQuery->where('academic_year_id', $academicYearId);
        }
        $morningRecords = $morningQuery->get()->keyBy(fn ($item) => (int) Carbon::parse($item->date)->day);

        $attendanceMatrix = [];
        $recentActivity = [];
        $totalBerjamaah = 0;
        $totalMasbuk = 0;
        $totalIjin = 0;
        $totalTidakHadir = 0;
        $totalLibur = 0;
        $effectiveDays = 0;

        for ($d = 1; $d <= $daysInMonth; $d++) {
            $dateObj = Carbon::create($year, $month, $d, 0, 0, 0, 'Asia/Jakarta');
            $dateStr = $dateObj->toDateString();
            $isEffective = $sholatService->isHariSholatDhuhur($dateObj, $classId);

            if (!$isEffective) {
                $desc = $sholatService->getHolidayDescription($dateObj, $classId);
                $status = 'libur';
                $statusLabel = 'Hari Libur';
                $keterangan = $desc ?: 'Hari Libur';
                $totalLibur++;
            } else {
                $effectiveDays++;
                $rec = $records->get($d);

                if ($rec) {
                    $rawStatus = strtolower($rec->status);
                    if ($rawStatus === 'berjamaah' || $rawStatus === 'hadir') {
                        $status = 'berjamaah';
                        $statusLabel = 'Berjamaah';
                        $totalBerjamaah++;
                    } elseif ($rawStatus === 'masbuk') {
                        $status = 'masbuk';
                        $statusLabel = 'Masbuk';
                        $totalMasbuk++;
                    } elseif (in_array($rawStatus, ['halangan', 'haid'])) {
                        $status = 'halangan';
                        $statusLabel = 'Halangan/Haid';
                        $totalIjin++;
                    } elseif ($rawStatus === 'ijin' || $rawStatus === 'izin' || $rawStatus === 'sakit') {
                        $status = 'ijin';
                        $statusLabel = 'Izin/Sakit';
                        $totalIjin++;
                    } else {
                        $status = 'tidak_hadir';
                        $statusLabel = 'Tidak Hadir';
                        $totalTidakHadir++;
                    }
                    $keterangan = $rec->keterangan ?? $statusLabel;
                } else {
                    $morning = $morningRecords->get($d);
                    if ($morning && $morning->status === 'alpa') {
                        $status = 'tidak_hadir';
                        $statusLabel = 'Tidak Hadir';
                        $keterangan = 'Alpa (Sinkron Presensi Pagi)';
                        $totalTidakHadir++;
                    } elseif ($morning && in_array($morning->status, ['izin', 'sakit'])) {
                        $status = 'ijin';
                        $statusLabel = ucfirst($morning->status);
                        $keterangan = ucfirst($morning->status) . ' (Sinkron Presensi Pagi)';
                        $totalIjin++;
                    } elseif ($dateStr <= $todayDate) {
                        $status = 'belum';
                        $statusLabel = 'Belum Ada Presensi';
                        $keterangan = 'Belum Ada Presensi Sholat';
                    } else {
                        $status = 'mendatang';
                        $statusLabel = 'Mendatang';
                        $keterangan = 'Mendatang';
                    }
                }
            }

            $attendanceMatrix[$d] = [
                'day' => $d,
                'date' => $dateStr,
                'status' => $status,
                'status_label' => $statusLabel,
                'keterangan' => $keterangan,
                'day_name' => $dateObj->translatedFormat('l'),
            ];

            if ($dateStr <= $todayDate && !in_array($status, ['libur', 'mendatang']) && count($recentActivity) < 5) {
                $recentActivity[] = [
                    'date' => $dateStr,
                    'formatted_date' => $dateObj->translatedFormat('l, d M Y'),
                    'status' => $status,
                    'status_label' => $statusLabel,
                    'note' => $keterangan,
                ];
            }
        }

        $presentCount = $totalBerjamaah + $totalMasbuk;
        $attendancePercentage = $effectiveDays > 0
            ? min(100, round(($presentCount / $effectiveDays) * 100, 1))
            : 0;

        return response()->json([
            'success' => true,
            'message' => 'Data kalender sholat dhuhur siswa berhasil dimuat.',
            'data' => [
                'enable_sholat_dhuhur' => $enableSholat,
                'is_non_muslim' => $isNonMuslim,
                'student' => [
                    'name' => $student->name,
                    'nisn' => $student->nisn,
                    'class_name' => $enrollment?->kelas?->name ?? 'Kelas Siswa',
                    'religion' => $student->religion ?? 'Islam',
                ],
                'selected_period' => [
                    'month' => $month,
                    'year' => $year,
                    'month_year' => sprintf('%02d-%d', $month, $year),
                    'days_in_month' => $daysInMonth,
                    'start_of_month_offset' => $startOfMonthOffset,
                    'today_date' => $todayDate,
                ],
                'available_months' => $availableMonths,
                'stats' => [
                    'total_berjamaah' => $totalBerjamaah,
                    'total_masbuk' => $totalMasbuk,
                    'total_ijin' => $totalIjin,
                    'total_tidak_hadir' => $totalTidakHadir,
                    'total_libur' => $totalLibur,
                    'attendance_percentage' => $attendancePercentage,
                    'effective_days' => $effectiveDays,
                ],
                'attendance_matrix' => (object) $attendanceMatrix,
                'recent_activity' => $recentActivity,
            ],
        ]);
    }

    /**
     * GET /api/v1/siswa/presensi-kbm/dashboard
     * Menu 1 (Tab 3): Rekapitulasi Presensi KBM per Mata Pelajaran (11-12 Mapel)
     * Model Pertemuan Terdata / Terlaksana dari Jurnal Mengajar Guru.
     */
    public function presensiKbmDashboard(Request $request): JsonResponse
    {
        $student = $this->resolveStudent($request);
        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Data profil siswa tidak ditemukan.',
            ], 404);
        }

        $sekolah = PengaturanSekolah::current();
        $enablePresensiKbm = (bool) ($sekolah?->enable_presensi_kbm ?? true);
        if (!$enablePresensiKbm) {
            return response()->json([
                'success' => true,
                'message' => 'Fitur Presensi KBM sedang dinonaktifkan oleh sekolah.',
                'data' => [
                    'enable_presensi_kbm' => false,
                    'subjects' => [],
                    'summary' => [
                        'total_pertemuan_terdata' => 0,
                        'total_hadir' => 0,
                        'total_sakit' => 0,
                        'total_izin' => 0,
                        'total_alpa' => 0,
                        'total_tidak_mengikuti' => 0,
                        'persentase_kehadiran' => 100.0,
                    ],
                ],
            ]);
        }

        $activeYear = $this->getActiveAcademicYear();
        $enrollment = $student->enrollmentAktif()->with(['kelas', 'tahunAjaran'])->first()
            ?: $student->enrollments()->with(['kelas', 'tahunAjaran'])->latest()->first();

        $classId = $enrollment?->class_id;
        $academicYearId = $enrollment?->academic_year_id ?? $activeYear?->id;
        $tahunAjaran = $enrollment?->tahunAjaran ?? $activeYear;
        $className = $enrollment?->kelas?->name ?? 'Kelas Siswa';

        // Bangun Daftar Bulan Kalender Akademik (Juli - Juni) + Opsi 'all' (Semua Pertemuan)
        $availableMonths = [
            [
                'key' => 'all',
                'month' => 0,
                'year' => 0,
                'label' => 'Semua Bulan (Semester)',
            ]
        ];

        if ($tahunAjaran) {
            $sy = $tahunAjaran->start_year;
            $ey = $tahunAjaran->end_year;
            $monthNames = [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
            ];

            for ($m = 7; $m <= 12; $m++) {
                $availableMonths[] = [
                    'key' => sprintf('%02d-%d', $m, $sy),
                    'month' => $m,
                    'year' => $sy,
                    'label' => $monthNames[$m] . ' ' . $sy,
                ];
            }
            for ($m = 1; $m <= 6; $m++) {
                $availableMonths[] = [
                    'key' => sprintf('%02d-%d', $m, $ey),
                    'month' => $m,
                    'year' => $ey,
                    'label' => $monthNames[$m] . ' ' . $ey,
                ];
            }
        }

        // Filter Periode Terpilih
        $reqMonthYear = $request->input('month_year');
        $isAllMonths = ($reqMonthYear === 'all');
        $month = null;
        $year = null;

        if (!$isAllMonths) {
            if (!empty($reqMonthYear) && str_contains($reqMonthYear, '-')) {
                $parts = explode('-', $reqMonthYear);
                $month = (int) $parts[0];
                $year = (int) $parts[1];
            } elseif ($request->filled('month') && $request->filled('year')) {
                $month = (int) $request->input('month');
                $year = (int) $request->input('year');
            } else {
                $isAllMonths = true;
                $reqMonthYear = 'all';
            }
        }

        // Ambil Daftar 11-12 Mapel Rombongan Belajar dari Pengajaran
        $kelasAjaran = \App\Models\KelasAjaran::where('class_id', $classId)
            ->where('academic_year_id', $academicYearId)
            ->first();

        $pengajarans = collect();
        if ($kelasAjaran) {
            $pengajarans = \App\Models\Pengajaran::where('class_academic_year_id', $kelasAjaran->id)
                ->with(['mataPelajaran', 'guru'])
                ->get();
        }

        // Ambil Seluruh Jurnal Mengajar yang Relevan untuk Kelas & Siswa
        $jurnalQuery = \App\Models\JurnalPembelajaran::where('class_id', $classId)
            ->where('academic_year_id', $academicYearId)
            ->with(['guru', 'mataPelajaran', 'presensis' => function ($q) use ($student) {
                $q->where('student_id', $student->id);
            }]);

        if (!$isAllMonths && $month && $year) {
            $jurnalQuery->whereYear('tanggal', $year)->whereMonth('tanggal', $month);
        }

        $allJurnals = $jurnalQuery->orderBy('tanggal', 'desc')
            ->orderBy('jam_mulai', 'desc')
            ->get();

        $jurnalsByMapel = $allJurnals->groupBy('mata_pelajaran_id');

        $subjectRows = [];
        $grandTotalTerdata = 0;
        $grandTotalHadir = 0;
        $grandTotalSakit = 0;
        $grandTotalIzin = 0;
        $grandTotalAlpa = 0;
        $grandTotalTidakMengikuti = 0;
        $processedMapelIds = [];

        foreach ($pengajarans as $pengajaran) {
            $mapel = $pengajaran->mataPelajaran;
            if (!$mapel) continue;

            $mapelId = $mapel->id;
            $processedMapelIds[] = $mapelId;
            $jurnalsForMapel = $jurnalsByMapel->get($mapelId, collect());

            $totalTerdata = 0;
            $hadir = 0;
            $sakit = 0;
            $izin = 0;
            $alpa = 0;
            $tidakMengikuti = 0;
            $pertemuanList = [];

            // Urutkan tanggal menaik untuk penomoran "Pertemuan 1, 2, ..."
            $sortedMeetings = $jurnalsForMapel->sortBy('tanggal')->values();

            foreach ($sortedMeetings as $index => $jurnal) {
                $studentPresensi = $jurnal->presensis->first();
                $totalTerdata++;

                $statusJurnal = $studentPresensi?->status_jurnal ?? 'hadir';
                $statusGerbang = strtolower($studentPresensi?->status_gerbang_snapshot ?? 'hadir');
                $keterangan = $studentPresensi?->keterangan ?? '';

                $normalizedStatus = 'hadir';
                $statusLabel = 'Hadir';

                if ($statusJurnal === 'tidak_mengikuti') {
                    if ($statusGerbang === 'sakit') {
                        $normalizedStatus = 'sakit';
                        $statusLabel = 'Sakit';
                        $sakit++;
                    } elseif ($statusGerbang === 'izin') {
                        $normalizedStatus = 'izin';
                        $statusLabel = 'Izin';
                        $izin++;
                    } elseif ($statusGerbang === 'alpa') {
                        $normalizedStatus = 'alpa';
                        $statusLabel = 'Alpa';
                        $alpa++;
                    } else {
                        $normalizedStatus = 'tidak_mengikuti';
                        $statusLabel = 'Tidak Mengikuti KBM';
                        $tidakMengikuti++;
                    }
                } else {
                    $normalizedStatus = 'hadir';
                    $statusLabel = 'Hadir';
                    $hadir++;
                }

                $pertemuanList[] = [
                    'jurnal_id' => (string) $jurnal->id,
                    'pertemuan_ke' => $index + 1,
                    'tanggal' => $jurnal->tanggal?->format('Y-m-d'),
                    'tanggal_formatted' => $jurnal->tanggal?->translatedFormat('d F Y'),
                    'hari' => $jurnal->tanggal?->translatedFormat('l'),
                    'jam_ke_label' => $jurnal->jam_ke_label ?: "Jam ke {$jurnal->jam_mulai} - {$jurnal->jam_selesai}",
                    'durasi_jp' => (int) ($jurnal->durasi_jp ?? 2),
                    'materi' => $jurnal->materi ?: 'Materi Pembelajaran',
                    'uraian_materi' => $jurnal->uraian_materi ?: '',
                    'status' => $normalizedStatus,
                    'status_label' => $statusLabel,
                    'status_gerbang' => $statusGerbang,
                    'keterangan' => $keterangan,
                    'guru_nama' => $jurnal->guru?->name ?? ($pengajaran->guru?->name ?? '-'),
                ];
            }

            // Balik urutan agar pertemuan terbaru muncul di paling atas
            $pertemuanList = array_reverse($pertemuanList);

            $percentage = $totalTerdata > 0
                ? round(($hadir / $totalTerdata) * 100, 1)
                : 100.0;

            $grandTotalTerdata += $totalTerdata;
            $grandTotalHadir += $hadir;
            $grandTotalSakit += $sakit;
            $grandTotalIzin += $izin;
            $grandTotalAlpa += $alpa;
            $grandTotalTidakMengikuti += $tidakMengikuti;

            $subjectRows[] = [
                'mata_pelajaran_id' => $mapel->id,
                'nama_mapel' => $mapel->nama_mapel ?? $mapel->nama ?? 'Mata Pelajaran',
                'kode_mapel' => $mapel->kode_mapel ?? $mapel->kode ?? 'MP',
                'kategori' => $mapel->kategori ?? 'Umum',
                'kkm' => (float) ($mapel->kkm_default ?? 75),
                'guru_pengampu' => [
                    'id' => (string) ($pengajaran->guru?->id ?? ''),
                    'name' => $pengajaran->guru?->name ?? 'Guru Pengampu',
                    'nip' => $pengajaran->guru?->nip ?? '-',
                    'photo_url' => $pengajaran->guru?->photo_url ?? null,
                ],
                'stats' => [
                    'total_pertemuan_terdata' => $totalTerdata,
                    'hadir' => $hadir,
                    'sakit' => $sakit,
                    'izin' => $izin,
                    'alpa' => $alpa,
                    'tidak_mengikuti' => $tidakMengikuti,
                    'persentase_kehadiran' => $percentage,
                ],
                'has_data' => $totalTerdata > 0,
                'pertemuan_list' => $pertemuanList,
            ];
        }

        // Periksa jika ada jurnal untuk mapel di luar pengajaran kelas
        foreach ($jurnalsByMapel as $extraMapelId => $extraJurnals) {
            if (in_array($extraMapelId, $processedMapelIds)) continue;

            $sampleJurnal = $extraJurnals->first();
            $extraMapel = $sampleJurnal?->mataPelajaran;
            if (!$extraMapel) continue;

            $totalTerdata = 0;
            $hadir = 0;
            $sakit = 0;
            $izin = 0;
            $alpa = 0;
            $tidakMengikuti = 0;
            $pertemuanList = [];

            $sortedMeetings = $extraJurnals->sortBy('tanggal')->values();
            foreach ($sortedMeetings as $index => $jurnal) {
                $studentPresensi = $jurnal->presensis->first();
                $totalTerdata++;
                $statusJurnal = $studentPresensi?->status_jurnal ?? 'hadir';
                $statusGerbang = strtolower($studentPresensi?->status_gerbang_snapshot ?? 'hadir');
                $keterangan = $studentPresensi?->keterangan ?? '';

                $normalizedStatus = 'hadir';
                $statusLabel = 'Hadir';

                if ($statusJurnal === 'tidak_mengikuti') {
                    if ($statusGerbang === 'sakit') {
                        $normalizedStatus = 'sakit';
                        $statusLabel = 'Sakit';
                        $sakit++;
                    } elseif ($statusGerbang === 'izin') {
                        $normalizedStatus = 'izin';
                        $statusLabel = 'Izin';
                        $izin++;
                    } elseif ($statusGerbang === 'alpa') {
                        $normalizedStatus = 'alpa';
                        $statusLabel = 'Alpa';
                        $alpa++;
                    } else {
                        $normalizedStatus = 'tidak_mengikuti';
                        $statusLabel = 'Tidak Mengikuti KBM';
                        $tidakMengikuti++;
                    }
                } else {
                    $normalizedStatus = 'hadir';
                    $statusLabel = 'Hadir';
                    $hadir++;
                }

                $pertemuanList[] = [
                    'jurnal_id' => (string) $jurnal->id,
                    'pertemuan_ke' => $index + 1,
                    'tanggal' => $jurnal->tanggal?->format('Y-m-d'),
                    'tanggal_formatted' => $jurnal->tanggal?->translatedFormat('d F Y'),
                    'hari' => $jurnal->tanggal?->translatedFormat('l'),
                    'jam_ke_label' => $jurnal->jam_ke_label ?: "Jam ke {$jurnal->jam_mulai} - {$jurnal->jam_selesai}",
                    'durasi_jp' => (int) ($jurnal->durasi_jp ?? 2),
                    'materi' => $jurnal->materi ?: 'Materi Pembelajaran',
                    'uraian_materi' => $jurnal->uraian_materi ?: '',
                    'status' => $normalizedStatus,
                    'status_label' => $statusLabel,
                    'status_gerbang' => $statusGerbang,
                    'keterangan' => $keterangan,
                    'guru_nama' => $jurnal->guru?->name ?? '-',
                ];
            }

            $pertemuanList = array_reverse($pertemuanList);
            $percentage = $totalTerdata > 0 ? round(($hadir / $totalTerdata) * 100, 1) : 100.0;

            $grandTotalTerdata += $totalTerdata;
            $grandTotalHadir += $hadir;
            $grandTotalSakit += $sakit;
            $grandTotalIzin += $izin;
            $grandTotalAlpa += $alpa;
            $grandTotalTidakMengikuti += $tidakMengikuti;

            $subjectRows[] = [
                'mata_pelajaran_id' => $extraMapel->id,
                'nama_mapel' => $extraMapel->nama_mapel ?? $extraMapel->nama ?? 'Mata Pelajaran',
                'kode_mapel' => $extraMapel->kode_mapel ?? $extraMapel->kode ?? 'MP',
                'kategori' => $extraMapel->kategori ?? 'Umum',
                'kkm' => (float) ($extraMapel->kkm_default ?? 75),
                'guru_pengampu' => [
                    'id' => (string) ($sampleJurnal->guru?->id ?? ''),
                    'name' => $sampleJurnal->guru?->name ?? 'Guru Pengampu',
                    'nip' => $sampleJurnal->guru?->nip ?? '-',
                    'photo_url' => $sampleJurnal->guru?->photo_url ?? null,
                ],
                'stats' => [
                    'total_pertemuan_terdata' => $totalTerdata,
                    'hadir' => $hadir,
                    'sakit' => $sakit,
                    'izin' => $izin,
                    'alpa' => $alpa,
                    'tidak_mengikuti' => $tidakMengikuti,
                    'persentase_kehadiran' => $percentage,
                ],
                'has_data' => $totalTerdata > 0,
                'pertemuan_list' => $pertemuanList,
            ];
        }

        $overallPercentage = $grandTotalTerdata > 0
            ? round(($grandTotalHadir / $grandTotalTerdata) * 100, 1)
            : 100.0;

        $summary = [
            'total_pertemuan_terdata' => $grandTotalTerdata,
            'total_hadir' => $grandTotalHadir,
            'total_sakit' => $grandTotalSakit,
            'total_izin' => $grandTotalIzin,
            'total_alpa' => $grandTotalAlpa,
            'total_tidak_mengikuti' => $grandTotalTidakMengikuti,
            'persentase_kehadiran' => $overallPercentage,
        ];

        // Riwayat Aktivitas KBM Terbaru Lintas Mapel (5 Terakhir)
        $recentActivity = [];
        $recentJurnals = $allJurnals->take(5);
        foreach ($recentJurnals as $rj) {
            $sp = $rj->presensis->first();
            $stJurnal = $sp?->status_jurnal ?? 'hadir';
            $stGerbang = strtolower($sp?->status_gerbang_snapshot ?? 'hadir');
            $stLabel = 'Hadir';
            $stCode = 'hadir';
            if ($stJurnal === 'tidak_mengikuti') {
                if ($stGerbang === 'sakit') { $stLabel = 'Sakit'; $stCode = 'sakit'; }
                elseif ($stGerbang === 'izin') { $stLabel = 'Izin'; $stCode = 'izin'; }
                elseif ($stGerbang === 'alpa') { $stLabel = 'Alpa'; $stCode = 'alpa'; }
                else { $stLabel = 'Tidak Mengikuti'; $stCode = 'tidak_mengikuti'; }
            }

            $recentActivity[] = [
                'jurnal_id' => (string) $rj->id,
                'nama_mapel' => $rj->mataPelajaran?->nama_mapel ?? '-',
                'materi' => $rj->materi ?: 'KBM',
                'tanggal' => $rj->tanggal?->format('Y-m-d'),
                'tanggal_formatted' => $rj->tanggal?->translatedFormat('d M Y'),
                'jam_ke_label' => $rj->jam_ke_label ?: "Jam ke {$rj->jam_mulai} - {$rj->jam_selesai}",
                'status' => $stCode,
                'status_label' => $stLabel,
                'guru_nama' => $rj->guru?->name ?? '-',
            ];
        }

        return response()->json([
            'success' => true,
            'message' => 'Data presensi KBM siswa berhasil dimuat.',
            'data' => [
                'enable_presensi_kbm' => $enablePresensiKbm,
                'student' => [
                    'name' => $student->name,
                    'nisn' => $student->nisn,
                    'class_name' => $className,
                ],
                'selected_period' => [
                    'is_all_months' => $isAllMonths,
                    'month_year' => $reqMonthYear,
                    'month' => $month,
                    'year' => $year,
                ],
                'available_months' => $availableMonths,
                'summary' => $summary,
                'subjects' => $subjectRows,
                'recent_activity' => $recentActivity,
            ],
        ]);
    }
}
