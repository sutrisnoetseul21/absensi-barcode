<?php

namespace App\Services;

use App\Models\Kelas;
use App\Models\TahunAjaran;
use App\Models\Presensi;
use App\Models\EnrollmentSiswa;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AbsensiRekapService
{
    /**
     * Data kalender bulanan per kelas (untuk Rekap Kelas).
     * Mengembalikan: students, monthlyStats (daily + aggregate), todayStats, alerts, daysInMonth, todayDate.
     */
    public function getMonthlyCalendarData(
        string $academicYearId,
        string $classId,
        string $month // '07', '08', dst.
    ): array {
        $ay = TahunAjaran::find($academicYearId);
        if (!$ay) {
            return $this->emptyCalendarResult();
        }

        $startYear = $ay->start_year ?? ((int)date('Y') - 1);
        $endYear   = $ay->end_year ?? (int)date('Y');

        $calendarYear = $this->resolveCalendarYear($ay, (int)$month);

        // Load siswa aktif di kelas
        $kelas = Kelas::with(['enrollments' => function ($q) use ($academicYearId) {
            $q->where('academic_year_id', $academicYearId)
              ->where('status', 'aktif')
              ->with('siswa');
        }])->find($classId);

        $students = $kelas ? $kelas->enrollments->pluck('siswa')->filter() : collect();

        // Stats hari ini
        $todayDate  = Carbon::now('Asia/Jakarta')->toDateString();
        $todayAtts  = Presensi::where('academic_year_id', $academicYearId)
            ->where('class_id', $classId)
            ->where('date', $todayDate)
            ->get();

        $totalCount  = $students->count();
        $todayHadir  = $todayAtts->where('status', 'hadir')->count();
        $todayTelat  = $todayAtts->where('status', 'telat')->count();
        $todayIzin   = $todayAtts->where('status', 'izin')->count();
        $todaySakit  = $todayAtts->where('status', 'sakit')->count();
        $todayAlpa   = $todayAtts->where('status', 'alpa')->count();

        $todayStats = [
            'hadir'            => $todayHadir,
            'telat'            => $todayTelat,
            'izin'             => $todayIzin,
            'sakit'            => $todaySakit,
            'alpa'             => $todayAlpa,
            'belum'            => max(0, $totalCount - ($todayHadir + $todayTelat + $todayIzin + $todaySakit + $todayAlpa)),
            'total'            => $totalCount,
            'persentase_hadir' => $totalCount > 0
                ? round((($todayHadir + $todayTelat) / $totalCount) * 100)
                : 0,
        ];

        // Rentang bulan yang dipilih
        $startDateObj = Carbon::create($calendarYear, $month, 1)->startOfMonth();
        $daysInMonth  = $startDateObj->daysInMonth;
        $startDate    = $startDateObj->toDateString();
        $endDate      = $startDateObj->endOfMonth()->toDateString();

        $attendances = Presensi::where('academic_year_id', $academicYearId)
            ->where('class_id', $classId)
            ->whereBetween('date', [$startDate, $endDate])
            ->get();

        $kalenderService = app(\App\Services\KalenderSekolahService::class);
        $holidaysCache = [];
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $dateObj = Carbon::create($calendarYear, $month, $day);
            $holidaysCache[$day] = !$kalenderService->isHariSekolah($dateObj, $classId);
        }

        // Build stats per siswa
        $monthlyStats = [];
        foreach ($students as $student) {
            $studentAtts = $attendances->where('student_id', $student->id);

            $daily = [];
            for ($day = 1; $day <= $daysInMonth; $day++) {
                if ($holidaysCache[$day]) {
                    $daily[$day] = 'L';
                } else {
                    $daily[$day] = '-'; // Default
                }
            }

            foreach ($studentAtts as $att) {
                $day = (int)Carbon::parse($att->date)->format('d');
                $status = match ($att->status) {
                    'hadir' => 'H',
                    'telat' => 'T',
                    'izin'  => 'I',
                    'sakit' => 'S',
                    'alpa'  => 'A',
                    default => '-',
                };
                
                // Jika hari tersebut terlanjur ditandai L (libur tapi ada yang absen), kita timpa saja dengan aslinya (H, dll)
                // Atau biarkan. Sebaiknya timpa karena fakta siswa hadir itu penting.
                $daily[$day] = $status;
            }

            $monthlyStats[$student->id] = [
                'hadir'             => $studentAtts->where('status', 'hadir')->count(),
                'telat'             => $studentAtts->where('status', 'telat')->count(),
                'izin'              => $studentAtts->where('status', 'izin')->count(),
                'sakit'             => $studentAtts->where('status', 'sakit')->count(),
                'alpa'              => $studentAtts->where('status', 'alpa')->count(),
                'total_late_minutes' => $studentAtts->sum('late_minutes'),
                'daily'             => $daily,
            ];
        }

        // Alerts
        $alpaTerlaluBanyak = Presensi::where('academic_year_id', $academicYearId)
            ->where('class_id', $classId)
            ->whereBetween('date', [$startDate, $endDate])
            ->where('status', 'alpa')
            ->selectRaw('student_id, count(*) as total')
            ->groupBy('student_id')
            ->havingRaw('count(*) >= 3')
            ->pluck('student_id')->toArray();

        $telatTerlaluBanyak = Presensi::where('academic_year_id', $academicYearId)
            ->where('class_id', $classId)
            ->whereBetween('date', [$startDate, $endDate])
            ->where('status', 'telat')
            ->selectRaw('student_id, sum(late_minutes) as total')
            ->groupBy('student_id')
            ->havingRaw('sum(late_minutes) >= 100')
            ->pluck('student_id')->toArray();

        return [
            'students'     => $students,
            'monthlyStats' => $monthlyStats,
            'todayStats'   => $todayStats,
            'alerts'       => ['alpa' => $alpaTerlaluBanyak, 'telat' => $telatTerlaluBanyak],
            'daysInMonth'  => $daysInMonth,
            'todayDate'    => $todayDate,
        ];
    }

    /**
     * Data agregat tahunan semua kelas (untuk Rekap Sekolah).
     * Mengembalikan: classesData, monthsList.
     */
    public function getYearlySchoolData(string $academicYearId): array
    {
        $ay = TahunAjaran::find($academicYearId);
        if (!$ay) return ['classesData' => [], 'monthsList' => []];

        $startYear = $ay->start_year ?? (date('Y') - 1);
        $endYear   = $ay->end_year ?? date('Y');

        $monthNames = [
            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret',
            '04' => 'April',   '05' => 'Mei',       '06' => 'Juni',
            '07' => 'Juli',    '08' => 'Agustus',   '09' => 'September',
            '10' => 'Oktober', '11' => 'November',  '12' => 'Desember',
        ];

        // Generate bulan dari Juli startYear hingga Juni endYear,
        // diperpanjang ke bulan data presensi terbaru jika melebihi batas normal.
        $academicStart = Carbon::create($startYear, 7, 1)->startOfMonth();
        $academicEnd   = Carbon::create($endYear, 6, 30)->endOfMonth();

        $maxDate = Presensi::where('academic_year_id', $academicYearId)->max('date');
        if ($maxDate) {
            $maxCarbon = Carbon::parse($maxDate)->endOfMonth();
            if ($maxCarbon->greaterThan($academicEnd)) {
                $academicEnd = $maxCarbon;
            }
        }

        $monthsStructure = [];
        $cursor = $academicStart->copy();
        while ($cursor->lte($academicEnd)) {
            $mKey = $cursor->format('m');
            $monthsStructure[] = [
                'month' => $mKey,
                'year'  => (int)$cursor->format('Y'),
                'label' => $monthNames[$mKey] . ' ' . $cursor->format('Y'),
            ];
            $cursor->addMonth();
        }

        // Hitung siswa aktif per kelas
        $studentCounts = EnrollmentSiswa::where('academic_year_id', $academicYearId)
            ->where('status', 'aktif')
            ->select('class_id', DB::raw('count(*) as total'))
            ->groupBy('class_id')
            ->pluck('total', 'class_id')
            ->toArray();

        $classes = Kelas::orderBy('name', 'asc')->get();

        // Ambil rekap agregat bulanan
        $attendances = Presensi::where('academic_year_id', $academicYearId)
            ->selectRaw('class_id, YEAR(date) as year, MONTH(date) as month, status, count(*) as count')
            ->groupBy('class_id', 'year', 'month', 'status')
            ->get();

        $classesData = [];
        foreach ($classes as $kelas) {
            $classReport = [
                'id'            => $kelas->id,
                'name'          => $kelas->name,
                'student_count' => $studentCounts[$kelas->id] ?? 0,
                'months'        => [],
            ];

            foreach ($monthsStructure as $m) {
                $monthNum = (int)$m['month'];
                $yearNum  = (int)$m['year'];

                $monthAtts = $attendances
                    ->where('class_id', $kelas->id)
                    ->where('year', $yearNum)
                    ->where('month', $monthNum);

                $key = "{$yearNum}-{$m['month']}";
                $classReport['months'][$key] = [
                    'hadir' => $monthAtts->whereIn('status', ['hadir', 'telat'])->sum('count'),
                    'sakit' => $monthAtts->where('status', 'sakit')->sum('count'),
                    'izin'  => $monthAtts->where('status', 'izin')->sum('count'),
                    'alpa'  => $monthAtts->where('status', 'alpa')->sum('count'),
                ];
            }

            $classesData[] = $classReport;
        }

        return ['classesData' => $classesData, 'monthsList' => $monthsStructure];
    }

    /**
     * Tentukan tahun kalender yang tepat untuk bulan tertentu dalam satu tahun ajaran.
     */
    public function resolveCalendarYear(TahunAjaran $ay, int $month): int
    {
        $startYear   = $ay->start_year ?? ((int)date('Y') - 1);
        $endYear     = $ay->end_year ?? (int)date('Y');
        $currentYear = (int)date('Y');

        if ($currentYear >= $startYear && $currentYear <= $endYear) {
            return $currentYear;
        }

        return $month >= 7 ? $startYear : $endYear;
    }

    private function emptyCalendarResult(): array
    {
        return [
            'students'     => collect(),
            'monthlyStats' => [],
            'todayStats'   => [],
            'alerts'       => ['alpa' => [], 'telat' => []],
            'daysInMonth'  => 31,
            'todayDate'    => date('Y-m-d'),
        ];
    }
}
