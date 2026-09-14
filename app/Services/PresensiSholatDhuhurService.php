<?php

namespace App\Services;

use App\Models\EnrollmentSiswa;
use App\Models\HariLibur;
use App\Models\Kelas;
use App\Models\Presensi;
use App\Models\PresensiSholatDhuhur;
use App\Models\TahunAjaran;
use Carbon\Carbon;

class PresensiSholatDhuhurService
{
    protected KalenderSekolahService $kalenderService;

    public function __construct(KalenderSekolahService $kalenderService)
    {
        $this->kalenderService = $kalenderService;
    }

    /**
     * Cek apakah suatu tanggal merupakan hari pelaksanaan Sholat Dhuhur sekolah.
     * Aturan:
     * - Hari Jumat: DIANGGAP LIBUR (siswa sholat Jumat di masjid masing-masing/pulang awal).
     * - Hari Minggu: LIBUR.
     * - Hari Sabtu: LIBUR jika sistem 5 hari sekolah.
     * - Hari Libur Nasional / Cuti Bersama di tabel HariLibur: LIBUR.
     */
    public function isHariSholatDhuhur(Carbon $date, ?string $classId = null): bool
    {
        // 1. Hari Jumat selalu libur untuk Sholat Dhuhur di sekolah
        if ($date->isFriday()) {
            return false;
        }

        // 2. Hari Minggu selalu libur
        if ($date->isSunday()) {
            return false;
        }

        // 3. Hari Sabtu libur jika sekolah menerapkan 5 hari sekolah
        $workDaysType = $this->kalenderService->getWorkDaysTypeForDate($date);
        if ($workDaysType === '5_hari' && $date->isSaturday()) {
            return false;
        }

        // 4. Hari Libur Nasional, Cuti Bersama, atau Libur Khusus Kelas
        $isHoliday = HariLibur::hariIni($date->toDateString(), $classId)->exists();
        if ($isHoliday) {
            return false;
        }

        return true;
    }

    /**
     * Dapatkan keterangan hari libur jika tanggal tersebut libur sholat.
     */
    public function getHolidayDescription(Carbon $date, ?string $classId = null): ?string
    {
        if ($date->isFriday()) {
            return 'Hari Jumat';
        }
        if ($date->isSunday()) {
            return 'Hari Minggu';
        }
        $workDaysType = $this->kalenderService->getWorkDaysTypeForDate($date);
        if ($workDaysType === '5_hari' && $date->isSaturday()) {
            return 'Hari Sabtu (Libur 5 Hari Sekolah)';
        }

        $holiday = HariLibur::hariIni($date->toDateString(), $classId)->first();
        if ($holiday) {
            return $holiday->description ?: 'Hari Libur Sekolah';
        }

        return null;
    }

    /**
     * Ambil data matriks bulanan presensi sholat dhuhur per kelas.
     * Mengintegrasikan otomatis data presensi pagi jika siswa alpa/sakit/izin.
     */
    public function getMonthlyMatrixData(
        string $academicYearId,
        string $classId,
        string $month,
        ?int $year = null
    ): array {
        $ay = TahunAjaran::find($academicYearId);
        if (!$ay) {
            return $this->emptyMatrixResult();
        }

        $calendarYear = $year ?: $this->resolveCalendarYear($ay, (int)$month);

        // Ambil siswa aktif di kelas
        $kelas = Kelas::with(['enrollments' => function ($q) use ($academicYearId) {
            $q->where('academic_year_id', $academicYearId)
              ->where('status', 'aktif')
              ->with('siswa');
        }])->find($classId);

        $students = $kelas ? $kelas->enrollments->pluck('siswa')->filter()->sortBy('name')->values() : collect();

        $startDateObj = Carbon::create($calendarYear, $month, 1)->startOfMonth();
        $daysInMonth  = $startDateObj->daysInMonth;
        $startDate    = $startDateObj->toDateString();
        $endDate      = $startDateObj->endOfMonth()->toDateString();
        $todayDate    = Carbon::now('Asia/Jakarta')->toDateString();

        // 1. Cache status libur per tanggal
        $holidaysCache = [];
        $holidayDescriptions = [];
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $dateObj = Carbon::create($calendarYear, $month, $day);
            $isSholatDay = $this->isHariSholatDhuhur($dateObj, $classId);
            $holidaysCache[$day] = !$isSholatDay;
            if (!$isSholatDay) {
                $holidayDescriptions[$day] = $this->getHolidayDescription($dateObj, $classId);
            }
        }

        // 2. Ambil data presensi sholat dhuhur yang sudah diinput
        $sholatRecords = PresensiSholatDhuhur::where('academic_year_id', $academicYearId)
            ->where('class_id', $classId)
            ->whereBetween('date', [$startDate, $endDate])
            ->get();

        // 3. Ambil data presensi pagi reguler (attendances) untuk sinkronisasi ketidakhadiran
        $morningRecords = Presensi::where('academic_year_id', $academicYearId)
            ->where('class_id', $classId)
            ->whereBetween('date', [$startDate, $endDate])
            ->get();

        // 4. Bangun data matriks per siswa
        $monthlyStats = [];
        $totalHadirClass = 0;
        $totalIjinClass = 0;
        $totalTidakHadirClass = 0;

        foreach ($students as $student) {
            $studentSholat = $sholatRecords->where('student_id', $student->id);
            $studentMorning = $morningRecords->where('student_id', $student->id);

            $daily = [];
            $dailyNotes = [];
            $hCount = 0;
            $iCount = 0;
            $aCount = 0;

            for ($day = 1; $day <= $daysInMonth; $day++) {
                $currentDateStr = sprintf('%04d-%02d-%02d', $calendarYear, (int)$month, $day);

                // Jika hari libur sholat (Jumat, Minggu, Sabtu 5 hari, Libur Nasional)
                if ($holidaysCache[$day]) {
                    $daily[$day] = 'L';
                    $dailyNotes[$day] = $holidayDescriptions[$day] ?? 'Libur';
                    continue;
                }

                // Cek apakah sudah ada input manual sholat dhuhur
                $sholatAtt = $studentSholat->first(function ($item) use ($currentDateStr) {
                    return $item->date->toDateString() === $currentDateStr;
                });

                if ($sholatAtt) {
                    $code = match ($sholatAtt->status) {
                        'hadir'       => 'H',
                        'ijin'        => 'I',
                        'tidak_hadir' => 'A',
                        'libur'       => 'L',
                        default       => '-',
                    };
                    $daily[$day] = $code;
                    $dailyNotes[$day] = $sholatAtt->keterangan;

                    if ($code === 'H') $hCount++;
                    if ($code === 'I') $iCount++;
                    if ($code === 'A') $aCount++;
                    continue;
                }

                // Jika belum ada input manual sholat, periksa presensi pagi
                $morningAtt = $studentMorning->first(function ($item) use ($currentDateStr) {
                    return $item->date->toDateString() === $currentDateStr;
                });

                if ($morningAtt) {
                    // Siswa alpa di pagi hari otomatis ikut tidak hadir di sholat
                    if ($morningAtt->status === 'alpa') {
                        $daily[$day] = 'A';
                        $dailyNotes[$day] = 'Alpa (Presensi Pagi)';
                        $aCount++;
                        continue;
                    }

                    // Siswa sakit atau izin di pagi hari otomatis ikut ijin di sholat
                    if (in_array($morningAtt->status, ['izin', 'sakit'])) {
                        $daily[$day] = 'I';
                        $dailyNotes[$day] = ucfirst($morningAtt->status) . ' (Presensi Pagi)';
                        $iCount++;
                        continue;
                    }

                    if ($morningAtt->status === 'libur') {
                        $daily[$day] = 'L';
                        $dailyNotes[$day] = 'Libur';
                        continue;
                    }
                }

                // Jika tanggal belum tiba atau belum diinput oleh wali kelas
                $daily[$day] = '-';
                $dailyNotes[$day] = null;
            }

            $totalActive = $hCount + $iCount + $aCount;
            $persentase = $totalActive > 0 ? round(($hCount / $totalActive) * 100, 1) : 0;

            $monthlyStats[$student->id] = [
                'hadir'       => $hCount,
                'ijin'        => $iCount,
                'tidak_hadir' => $aCount,
                'persentase'  => $persentase,
                'daily'       => $daily,
                'daily_notes' => $dailyNotes,
            ];

            $totalHadirClass += $hCount;
            $totalIjinClass += $iCount;
            $totalTidakHadirClass += $aCount;
        }

        // Hitung total hari efektif sholat dhuhur bulan ini
        $effectiveDays = count(array_filter($holidaysCache, fn ($isHoliday) => !$isHoliday));
        $totalStudents = count($students);
        $grandTotalActive = $totalHadirClass + $totalIjinClass + $totalTidakHadirClass;
        $classPercentage = $grandTotalActive > 0 ? round(($totalHadirClass / $grandTotalActive) * 100, 1) : 0;

        $classMonthlyStats = [
            'hadir'            => $totalHadirClass,
            'ijin'             => $totalIjinClass,
            'tidak_hadir'      => $totalTidakHadirClass,
            'effective_days'   => $effectiveDays,
            'total_students'   => $totalStudents,
            'max_possible'     => $totalStudents * $effectiveDays,
            'class_percentage' => $classPercentage,
        ];

        // Stats hari ini untuk kelas ini
        $todaySholat = $sholatRecords->filter(fn ($item) => $item->date->toDateString() === $todayDate);
        $todayStats = [
            'hadir'       => $todaySholat->where('status', 'hadir')->count(),
            'ijin'        => $todaySholat->where('status', 'ijin')->count(),
            'tidak_hadir' => $todaySholat->where('status', 'tidak_hadir')->count(),
            'total'       => $totalStudents,
            'is_holiday'  => !$this->isHariSholatDhuhur(Carbon::parse($todayDate), $classId),
        ];

        return [
            'students'          => $students,
            'monthlyStats'      => $monthlyStats,
            'classMonthlyStats' => $classMonthlyStats,
            'todayStats'        => $todayStats,
            'daysInMonth'       => $daysInMonth,
            'calendarYear'      => $calendarYear,
            'month'             => $month,
            'todayDate'         => $todayDate,
            'holidaysCache'     => $holidaysCache,
        ];
    }

    /**
     * Mengambil rekap semester atau tahunan untuk cetak laporan sholat dhuhur.
     */
    public function getSemesterYearlyData(
        string $academicYearId,
        string $classId,
        string $startDate,
        string $endDate
    ): array {
        $kelas = Kelas::with(['enrollments' => function ($q) use ($academicYearId) {
            $q->where('academic_year_id', $academicYearId)
              ->where('status', 'aktif')
              ->with('siswa');
        }])->find($classId);

        $students = $kelas ? $kelas->enrollments->pluck('siswa')->filter()->sortBy('name')->values() : collect();

        $start = Carbon::parse($startDate)->startOfMonth();
        $end   = Carbon::parse($endDate)->endOfMonth();

        $monthsList = [];
        $curr = $start->copy();
        while ($curr->lessThanOrEqualTo($end)) {
            $mKey = $curr->format('Y-m');
            $monthsList[$mKey] = [
                'key'       => $mKey,
                'name'      => $curr->translatedFormat('F Y'),
                'month'     => $curr->format('m'),
                'year'      => (int)$curr->format('Y'),
                'label'     => $curr->translatedFormat('F Y'),
                'short'     => $curr->translatedFormat('M'),
            ];
            $curr->addMonth();
        }

        // Ambil data per bulan
        $studentsData = [];
        foreach ($students as $st) {
            $studentsData[$st->id] = [
                'student'     => $st,
                'name'        => $st->name,
                'nisn'        => $st->nisn,
                'gender'      => $st->gender ?? '-',
                'months'      => [],
                'totals'      => [
                    'hadir'       => 0,
                    'ijin'        => 0,
                    'tidak_hadir' => 0,
                ],
                'total'       => [
                    'hadir'       => 0,
                    'ijin'        => 0,
                    'tidak_hadir' => 0,
                ],
            ];
        }

        foreach ($monthsList as $mKey => $mInfo) {
            $mResult = $this->getMonthlyMatrixData($academicYearId, $classId, $mInfo['month'], $mInfo['year']);
            foreach ($students as $st) {
                $stStat = $mResult['monthlyStats'][$st->id] ?? ['hadir' => 0, 'ijin' => 0, 'tidak_hadir' => 0];
                $studentsData[$st->id]['months'][$mKey] = [
                    'hadir'       => $stStat['hadir'],
                    'ijin'        => $stStat['ijin'],
                    'tidak_hadir' => $stStat['tidak_hadir'],
                ];
                $studentsData[$st->id]['totals']['hadir'] += $stStat['hadir'];
                $studentsData[$st->id]['totals']['ijin'] += $stStat['ijin'];
                $studentsData[$st->id]['totals']['tidak_hadir'] += $stStat['tidak_hadir'];
                $studentsData[$st->id]['total']['hadir'] += $stStat['hadir'];
                $studentsData[$st->id]['total']['ijin'] += $stStat['ijin'];
                $studentsData[$st->id]['total']['tidak_hadir'] += $stStat['tidak_hadir'];
            }
        }

        return [
            'studentsData' => $studentsData,
            'monthsList'   => $monthsList,
            'kelas'        => $kelas,
        ];
    }

    protected function resolveCalendarYear(TahunAjaran $ay, int $month): int
    {
        return ($month >= 7 && $month <= 12) ? $ay->start_year : $ay->end_year;
    }

    protected function emptyMatrixResult(): array
    {
        return [
            'students'          => collect(),
            'monthlyStats'      => [],
            'classMonthlyStats' => [],
            'todayStats'        => [],
            'daysInMonth'       => 0,
            'calendarYear'      => (int)date('Y'),
            'month'             => date('m'),
            'todayDate'         => date('Y-m-d'),
            'holidaysCache'     => [],
        ];
    }
}
