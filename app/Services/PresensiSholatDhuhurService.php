<?php

namespace App\Services;

use App\Models\EnrollmentSiswa;
use App\Models\HariLibur;
use App\Models\Kelas;
use App\Models\PengaturanSekolah;
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
        // 1. Hari Jumat: cek apakah presensi sholat hari Jumat diaktifkan di pengaturan sekolah
        if ($date->isFriday()) {
            $settings = PengaturanSekolah::current();
            $enableJumat = (bool) ($settings->enable_sholat_jumat ?? false);
            if (! $enableJumat) {
                return false;
            }
            // Jika diaktifkan, Jumat bukan otomatis libur, tetapi tetap mengikuti aturan libur di bawah (HariLibur)
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

        // 4. Hari Libur Nasional, Cuti Bersama, atau Libur Khusus Kelas (/admin/pengaturan-hari-libur)
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
            $settings = PengaturanSekolah::current();
            $enableJumat = (bool) ($settings->enable_sholat_jumat ?? false);
            if (! $enableJumat) {
                return 'Hari Jumat';
            }
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

        $allStudents = $kelas ? $kelas->enrollments->pluck('siswa')->filter()->sortBy('name')->values() : collect();

        // Pisahkan siswa Muslim dan Non-Muslim
        // Jika religion kosong/null, dianggap Islam (default)
        $students       = $allStudents->filter(fn ($s) => empty($s->religion) || strtolower(trim($s->religion)) === 'islam')->values();
        $nonMuslimStudents = $allStudents->filter(fn ($s) => !empty($s->religion) && strtolower(trim($s->religion)) !== 'islam')->values();

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
                'hadir'          => $hCount,
                'ijin'           => $iCount,
                'tidak_hadir'    => $aCount,
                'persentase'     => $persentase,
                'daily'          => $daily,
                'daily_notes'    => $dailyNotes,
                'is_non_muslim'  => false,
            ];

            $totalHadirClass += $hCount;
            $totalIjinClass += $iCount;
            $totalTidakHadirClass += $aCount;
        }

        // Tambahkan entri NM untuk siswa Non-Muslim (tidak masuk statistik)
        foreach ($nonMuslimStudents as $nm) {
            $nmDaily = [];
            $nmNotes = [];
            for ($day = 1; $day <= $daysInMonth; $day++) {
                if ($holidaysCache[$day]) {
                    $nmDaily[$day] = 'L';
                    $nmNotes[$day] = $holidayDescriptions[$day] ?? 'Libur';
                } else {
                    $nmDaily[$day] = 'NM';
                    $nmNotes[$day] = 'Non-Muslim (' . ($nm->religion ?? '-') . ')';
                }
            }
            $monthlyStats[$nm->id] = [
                'hadir'          => 0,
                'ijin'           => 0,
                'tidak_hadir'    => 0,
                'persentase'     => null,
                'daily'          => $nmDaily,
                'daily_notes'    => $nmNotes,
                'is_non_muslim'  => true,
            ];
        }

        // Hitung total hari efektif sholat dhuhur bulan ini
        $effectiveDays = count(array_filter($holidaysCache, fn ($isHoliday) => !$isHoliday));
        // total_students hanya menghitung siswa Muslim (untuk statistik kelas)
        $totalStudents = count($students);
        // total_all_students mencakup semua siswa (Muslim + Non-Muslim) untuk tampilan tabel
        $totalAllStudents = $allStudents->count();
        $grandTotalActive = $totalHadirClass + $totalIjinClass + $totalTidakHadirClass;
        $classPercentage = $grandTotalActive > 0 ? round(($totalHadirClass / $grandTotalActive) * 100, 1) : 0;

        $classMonthlyStats = [
            'hadir'               => $totalHadirClass,
            'ijin'                => $totalIjinClass,
            'tidak_hadir'         => $totalTidakHadirClass,
            'effective_days'      => $effectiveDays,
            'total_students'      => $totalStudents,
            'total_all_students'  => $totalAllStudents,
            'non_muslim_count'    => $nonMuslimStudents->count(),
            'max_possible'        => $totalStudents * $effectiveDays,
            'class_percentage'    => $classPercentage,
        ];

        // Stats hari ini untuk kelas ini
        $todaySholat = $sholatRecords->filter(fn ($item) => $item->date->toDateString() === $todayDate);
        $todayMorning = $morningRecords->filter(fn ($item) => $item->date->toDateString() === $todayDate);

        $todayAttendances = [];
        $todayHadir = 0;
        $todayIjin = 0;
        $todayTidakHadir = 0;

        foreach ($students as $student) {
            $sh = $todaySholat->firstWhere('student_id', $student->id);
            if ($sh) {
                $status = $sh->status;
                $todayAttendances[$student->id] = [
                    'id'         => $sh->id,
                    'status'     => $status,
                    'keterangan' => $sh->keterangan,
                    'time'       => $sh->created_at ? Carbon::parse($sh->created_at)->format('H:i') : null,
                ];
                if ($status === 'hadir') $todayHadir++;
                elseif ($status === 'ijin') $todayIjin++;
                elseif ($status === 'tidak_hadir') $todayTidakHadir++;
                continue;
            }

            // Jika belum ada record sholat, periksa presensi pagi
            $m = $todayMorning->firstWhere('student_id', $student->id);
            if ($m) {
                if ($m->status === 'alpa') {
                    $todayAttendances[$student->id] = [
                        'id'         => null,
                        'status'     => 'tidak_hadir',
                        'keterangan' => 'Alpa (Presensi Pagi)',
                        'time'       => null,
                    ];
                    $todayTidakHadir++;
                    continue;
                } elseif (in_array($m->status, ['izin', 'sakit'])) {
                    $todayAttendances[$student->id] = [
                        'id'         => null,
                        'status'     => 'ijin',
                        'keterangan' => ucfirst($m->status) . ' (Presensi Pagi)',
                        'time'       => null,
                    ];
                    $todayIjin++;
                    continue;
                }
            }

            // Belum ada presensi sholat
            $todayAttendances[$student->id] = [
                'id'         => null,
                'status'     => 'belum',
                'keterangan' => null,
                'time'       => null,
            ];
        }

        // Tandai siswa Non-Muslim sebagai non_muslim (bebas kewajiban sholat)
        foreach ($nonMuslimStudents as $nm) {
            $todayAttendances[$nm->id] = [
                'id'         => null,
                'status'     => 'non_muslim',
                'keterangan' => 'Non-Muslim (' . ($nm->religion ?? '-') . ') — Tidak wajib sholat',
                'time'       => null,
            ];
        }

        $todayBelum = max(0, $totalStudents - ($todayHadir + $todayIjin + $todayTidakHadir));

        $todayStats = [
            'hadir'            => $todayHadir,
            'ijin'             => $todayIjin,
            'tidak_hadir'      => $todayTidakHadir,
            'belum'            => $todayBelum,
            'total'            => $totalStudents,      // hanya Muslim
            'total_all'        => $totalAllStudents,   // semua siswa (termasuk non-Muslim)
            'non_muslim_count' => $nonMuslimStudents->count(),
            'persentase_hadir' => $totalStudents > 0 ? round(($todayHadir / $totalStudents) * 100, 1) : 0,
            'is_holiday'       => !$this->isHariSholatDhuhur(Carbon::parse($todayDate), $classId),
        ];

        // Gabungkan untuk tampilan tabel: Muslim dulu, lalu Non-Muslim di bawah
        $displayStudents = $students->concat($nonMuslimStudents);

        return [
            'students'          => $displayStudents,
            'monthlyStats'      => $monthlyStats,
            'classMonthlyStats' => $classMonthlyStats,
            'todayStats'        => $todayStats,
            'todayAttendances'  => $todayAttendances,
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

        $allStudents = $kelas ? $kelas->enrollments->pluck('siswa')->filter()->sortBy('name')->values() : collect();

        // Pisahkan Muslim dan Non-Muslim
        $students          = $allStudents->filter(fn ($s) => empty($s->religion) || strtolower(trim($s->religion)) === 'islam')->values();
        $nonMuslimStudents = $allStudents->filter(fn ($s) => !empty($s->religion) && strtolower(trim($s->religion)) !== 'islam')->values();

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
                'student'        => $st,
                'name'           => $st->name,
                'nisn'           => $st->nisn,
                'gender'         => $st->gender ?? '-',
                'religion'       => $st->religion,
                'is_non_muslim'  => false,
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

        // Tambahkan entri Non-Muslim (tanpa statistik sholat)
        foreach ($nonMuslimStudents as $nm) {
            $studentsData[$nm->id] = [
                'student'        => $nm,
                'name'           => $nm->name,
                'nisn'           => $nm->nisn,
                'gender'         => $nm->gender ?? '-',
                'religion'       => $nm->religion,
                'is_non_muslim'  => true,
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

    /**
     * Menyinkronkan data presensi pagi (Alpa, Izin, Sakit) ke presensi sholat dhuhur untuk SEMUA KELAS.
     * Siswa hadir/telat pagi TIDAK diubah.
     * Siswa non-Muslim disinkronkan / dikecualikan dari kewajiban sholat.
     *
     * @param string|null $dateString Tanggal (format Y-m-d), default hari ini
     * @param string|null $actorTeacherId ID Guru yang memicu (jika manual)
     * @return array
     */
    public function syncAllClassesFromMorning(?string $dateString = null, ?string $actorTeacherId = null): array
    {
        $today = $dateString ? Carbon::parse($dateString, 'Asia/Jakarta') : now('Asia/Jakarta');
        $date = $today->toDateString();

        // Cek apakah tanggal ini adalah hari sholat dhuhur
        if (!$this->isHariSholatDhuhur($today)) {
            return [
                'status'        => 'holiday',
                'message'       => 'Hari ini bukan hari pelaksanaan sholat dhuhur (' . ($this->getHolidayDescription($today) ?? 'Libur') . ').',
                'synced_count'  => 0,
                'classes_count' => 0,
            ];
        }

        $settings = PengaturanSekolah::current();
        $academicYearId = $settings->academic_year_id_active ?? null;
        if (!$academicYearId) {
            $ay = TahunAjaran::where('status', 'aktif')->first();
            $academicYearId = $ay?->id;
        }

        if (!$academicYearId) {
            return [
                'status'        => 'error',
                'message'       => 'Tidak ada Tahun Ajaran aktif.',
                'synced_count'  => 0,
                'classes_count' => 0,
            ];
        }

        // Ambil data presensi pagi (attendances) untuk hari ini yang berstatus alpa, izin, atau sakit
        $morningAbsents = Presensi::where('academic_year_id', $academicYearId)
            ->whereDate('date', $date)
            ->whereIn('status', ['alpa', 'izin', 'sakit'])
            ->with(['student'])
            ->get();

        $syncedCount = 0;
        $affectedClassIds = [];

        foreach ($morningAbsents as $morning) {
            $student = $morning->student;
            if (!$student) continue;

            // Pastikan jika ada hari libur khusus kelas, kelas tersebut dilewati
            if (!$this->isHariSholatDhuhur($today, $morning->class_id)) {
                continue;
            }

            // Cek apakah siswa non-Muslim
            $religion = strtolower(trim($student->religion ?? ''));
            $isNonMuslim = !empty($student->religion) && $religion !== 'islam';
            if ($isNonMuslim) {
                // Non-Muslim: tidak diwajibkan sholat dhuhur
                continue;
            }

            // Tentukan status dan keterangan sholat dhuhur
            $sholatStatus = match ($morning->status) {
                'alpa'  => 'tidak_hadir',
                'izin'  => 'ijin',
                'sakit' => 'ijin',
                default => null,
            };

            if (!$sholatStatus) continue;

            $keterangan = match ($morning->status) {
                'alpa'  => 'Alpa di presensi pagi',
                'izin'  => 'Izin di presensi pagi',
                'sakit' => 'Sakit di presensi pagi',
                default => null,
            };

            PresensiSholatDhuhur::updateOrCreate(
                [
                    'student_id' => $student->id,
                    'date'       => $date,
                ],
                [
                    'academic_year_id'    => $academicYearId,
                    'class_id'            => $morning->class_id,
                    'enrollment_id'       => $morning->enrollment_id,
                    'status'              => $sholatStatus,
                    'keterangan'          => $keterangan,
                    'input_by_teacher_id' => $actorTeacherId,
                ]
            );

            $syncedCount++;
            $affectedClassIds[$morning->class_id] = true;
        }

        return [
            'status'        => 'success',
            'message'       => "Berhasil menyinkronkan {$syncedCount} siswa (Alpa/Izin/Sakit) dari presensi pagi ke sholat dhuhur untuk semua kelas.",
            'synced_count'  => $syncedCount,
            'classes_count' => count($affectedClassIds),
            'date'          => $date,
        ];
    }

    /**
     * Dapatkan rekapitulasi data harian sholat dhuhur untuk seluruh sekolah.
     * Digunakan oleh export PDF dan Excel rekap harian sekolah.
     */
    public function getDailySchoolSummaryData(string $academicYearId, string $dateString): array
    {
        $ay = TahunAjaran::find($academicYearId);
        if (!$ay) {
            $ay = TahunAjaran::where('status', 'aktif')->first();
            $academicYearId = $ay?->id ?? $academicYearId;
        }

        $dateObj = Carbon::parse($dateString, 'Asia/Jakarta');
        $date = $dateObj->toDateString();

        $isHoliday = ! $this->isHariSholatDhuhur($dateObj);
        $holidayDesc = $this->getHolidayDescription($dateObj);

        // 1. Ambil semua kelas di tahun ajaran ini
        $classes = Kelas::whereHas('kelasAjarans', function ($query) use ($academicYearId) {
            $query->where('academic_year_id', $academicYearId);
        })->orderBy('name', 'asc')->get();

        // 2. Ambil seluruh enrollment siswa aktif
        $enrollments = EnrollmentSiswa::where('academic_year_id', $academicYearId)
            ->where('status', 'aktif')
            ->with([
                'siswa' => fn ($q) => $q->select('id', 'name', 'nisn', 'gender', 'religion', 'no_hp', 'no_hp_orang_tua'),
                'kelas' => fn ($q) => $q->select('id', 'name', 'grade_level'),
            ])
            ->get();

        // 3. Ambil data presensi sholat dhuhur hari ini
        $sholatRecords = PresensiSholatDhuhur::where('academic_year_id', $academicYearId)
            ->whereDate('date', $date)
            ->get()
            ->keyBy('student_id');

        // 4. Kalkulasi per kelas dan sekolah
        $classesSummary = [];
        $unattendedStudents = [];
        $allStudents = [];
        $gradeGroups = [];

        $schoolTotal = 0; $schoolTotal_L = 0; $schoolTotal_P = 0;
        $schoolMuslim = 0; $schoolMuslim_L = 0; $schoolMuslim_P = 0;
        $schoolNonMuslim = 0; $schoolNonMuslim_L = 0; $schoolNonMuslim_P = 0;

        $schoolHadir = 0; $schoolHadir_L = 0; $schoolHadir_P = 0;
        $schoolIjin = 0;  $schoolIjin_L = 0;  $schoolIjin_P = 0;
        $schoolTidakHadir = 0; $schoolTidakHadir_L = 0; $schoolTidakHadir_P = 0;
        $schoolBelum = 0; $schoolBelum_L = 0; $schoolBelum_P = 0;

        foreach ($classes as $cls) {
            $cEnrollments = $enrollments->where('class_id', $cls->id);
            $cTotal = $cEnrollments->count();
            if ($cTotal === 0) continue;

            $cTotal_L = 0; $cTotal_P = 0;
            $cMuslim_L = 0; $cMuslim_P = 0;
            $cNonMuslim_L = 0; $cNonMuslim_P = 0;

            $cHadir_L = 0; $cHadir_P = 0;
            $cIjin_L = 0;  $cIjin_P = 0;
            $cTidakHadir_L = 0; $cTidakHadir_P = 0;
            $cBelum_L = 0; $cBelum_P = 0;

            $cHadir = 0;
            $cIjin = 0;
            $cTidakHadir = 0;
            $cBelum = 0;

            foreach ($cEnrollments as $enr) {
                $st = $enr->siswa;
                if (! $st) continue;

                $gender = $st->gender ?? '-';
                $isL = ($gender === 'L');
                if ($isL) {
                    $cTotal_L++;
                } else {
                    $cTotal_P++;
                }

                $religion = strtolower(trim($st->religion ?? ''));
                $isMuslim = empty($st->religion) || $religion === 'islam';

                if ($isMuslim) {
                    if ($isL) $cMuslim_L++; else $cMuslim_P++;
                } else {
                    if ($isL) $cNonMuslim_L++; else $cNonMuslim_P++;
                }

                $sholat = $sholatRecords->get($st->id);
                $status = 'belum';
                $keterangan = '';
                $scanTime = null;

                if (! $isMuslim) {
                    $status = 'non_muslim';
                    $keterangan = 'Non-Muslim (' . ($st->religion ?: '-') . ')';
                } elseif ($sholat) {
                    $status = $sholat->status;
                    $keterangan = $sholat->keterangan ?? '';
                    $scanTime = $sholat->updated_at ? Carbon::parse($sholat->updated_at)->format('H:i') : null;

                    if ($status === 'hadir') {
                        $cHadir++;
                        if ($isL) $cHadir_L++; else $cHadir_P++;
                    } elseif ($status === 'ijin') {
                        $cIjin++;
                        if ($isL) $cIjin_L++; else $cIjin_P++;
                    } elseif ($status === 'tidak_hadir') {
                        $cTidakHadir++;
                        if ($isL) $cTidakHadir_L++; else $cTidakHadir_P++;
                    } else {
                        // Status libur atau lainnya dianggap belum
                        $cBelum++;
                        if ($isL) $cBelum_L++; else $cBelum_P++;
                        $status = 'belum';
                    }
                } else {
                    // Muslim tapi belum ada record presensi sholat
                    $cBelum++;
                    if ($isL) $cBelum_L++; else $cBelum_P++;
                    $status = 'belum';
                }

                // WhatsApp Phone Processing
                $rawPhone = $st->no_hp_orang_tua ?: $st->no_hp;
                $cleanPhone = preg_replace('/[^0-9]/', '', $rawPhone ?? '');
                if (! empty($cleanPhone)) {
                    if (str_starts_with($cleanPhone, '0')) {
                        $cleanPhone = '62' . substr($cleanPhone, 1);
                    } elseif (str_starts_with($cleanPhone, '8')) {
                        $cleanPhone = '62' . $cleanPhone;
                    }
                }

                $studentItem = [
                    'id'            => $st->id,
                    'name'          => $st->name,
                    'nisn'          => $st->nisn,
                    'gender'        => $gender,
                    'religion'      => $st->religion ?? 'Islam',
                    'class_id'      => $cls->id,
                    'class_name'    => $cls->name,
                    'grade_level'   => $cls->grade_level ?? '',
                    'status'        => $status,
                    'keterangan'    => $keterangan,
                    'scan_time'     => $scanTime,
                    'phone'         => $rawPhone,
                    'clean_phone'   => $cleanPhone,
                    'is_non_muslim' => ! $isMuslim,
                ];

                $allStudents[] = $studentItem;

                if ($status === 'belum' && $isMuslim) {
                    $unattendedStudents[] = $studentItem;
                }
            }

            $cMuslimTotal = $cMuslim_L + $cMuslim_P;
            $cNonMuslimTotal = $cNonMuslim_L + $cNonMuslim_P;

            // Persentase dihitung dari total siswa Muslim (karena kewajiban sholat hanya siswa Muslim)
            $pct = $cMuslimTotal > 0 ? round(($cHadir / $cMuslimTotal) * 100, 1) : 0;
            $pct_L = $cMuslim_L > 0 ? round(($cHadir_L / $cMuslim_L) * 100, 1) : 0;
            $pct_P = $cMuslim_P > 0 ? round(($cHadir_P / $cMuslim_P) * 100, 1) : 0;

            $classData = [
                'id'               => $cls->id,
                'name'             => $cls->name,
                'grade_level'      => $cls->grade_level ?? '',
                'wali_kelas'       => $cls->waliKelas($academicYearId)?->name ?? '-',
                'total'            => $cTotal,
                'total_l'          => $cTotal_L,
                'total_p'          => $cTotal_P,
                'muslim_total'     => $cMuslimTotal,
                'muslim_l'         => $cMuslim_L,
                'muslim_p'         => $cMuslim_P,
                'non_muslim_total' => $cNonMuslimTotal,
                'non_muslim_l'     => $cNonMuslim_L,
                'non_muslim_p'     => $cNonMuslim_P,
                'hadir'            => $cHadir,
                'hadir_l'          => $cHadir_L,
                'hadir_p'          => $cHadir_P,
                'ijin'             => $cIjin,
                'ijin_l'           => $cIjin_L,
                'ijin_p'           => $cIjin_P,
                'tidak_hadir'      => $cTidakHadir,
                'tidak_hadir_l'    => $cTidakHadir_L,
                'tidak_hadir_p'    => $cTidakHadir_P,
                'belum'            => $cBelum,
                'belum_l'          => $cBelum_L,
                'belum_p'          => $cBelum_P,
                'persen'           => $pct,
                'persen_l'         => $pct_L,
                'persen_p'         => $pct_P,
            ];

            $classesSummary[] = $classData;

            // Akumulasi tingkat kelas (Grade 7, 8, 9)
            $gLevel = (string)($cls->grade_level ?? 'Lainnya');
            if (!isset($gradeGroups[$gLevel])) {
                $gradeGroups[$gLevel] = [
                    'grade_level'      => $gLevel,
                    'total'            => 0,
                    'muslim_total'     => 0,
                    'non_muslim_total' => 0,
                    'hadir'            => 0,
                    'ijin'             => 0,
                    'tidak_hadir'      => 0,
                    'belum'            => 0,
                    'persen'           => 0,
                ];
            }
            $gradeGroups[$gLevel]['total'] += $cTotal;
            $gradeGroups[$gLevel]['muslim_total'] += $cMuslimTotal;
            $gradeGroups[$gLevel]['non_muslim_total'] += $cNonMuslimTotal;
            $gradeGroups[$gLevel]['hadir'] += $cHadir;
            $gradeGroups[$gLevel]['ijin'] += $cIjin;
            $gradeGroups[$gLevel]['tidak_hadir'] += $cTidakHadir;
            $gradeGroups[$gLevel]['belum'] += $cBelum;

            // Akumulasi sekolah
            $schoolTotal += $cTotal;
            $schoolTotal_L += $cTotal_L;
            $schoolTotal_P += $cTotal_P;

            $schoolMuslim += $cMuslimTotal;
            $schoolMuslim_L += $cMuslim_L;
            $schoolMuslim_P += $cMuslim_P;

            $schoolNonMuslim += $cNonMuslimTotal;
            $schoolNonMuslim_L += $cNonMuslim_L;
            $schoolNonMuslim_P += $cNonMuslim_P;

            $schoolHadir += $cHadir;
            $schoolHadir_L += $cHadir_L;
            $schoolHadir_P += $cHadir_P;

            $schoolIjin += $cIjin;
            $schoolIjin_L += $cIjin_L;
            $schoolIjin_P += $cIjin_P;

            $schoolTidakHadir += $cTidakHadir;
            $schoolTidakHadir_L += $cTidakHadir_L;
            $schoolTidakHadir_P += $cTidakHadir_P;

            $schoolBelum += $cBelum;
            $schoolBelum_L += $cBelum_L;
            $schoolBelum_P += $cBelum_P;
        }

        // Hitung persentase grade level
        foreach ($gradeGroups as $gKey => $gData) {
            $gradeGroups[$gKey]['persen'] = $gData['muslim_total'] > 0
                ? round(($gData['hadir'] / $gData['muslim_total']) * 100, 1)
                : 0;
        }
        ksort($gradeGroups);

        $schoolPct = $schoolMuslim > 0 ? round(($schoolHadir / $schoolMuslim) * 100, 1) : 0;
        $schoolPct_L = $schoolMuslim_L > 0 ? round(($schoolHadir_L / $schoolMuslim_L) * 100, 1) : 0;
        $schoolPct_P = $schoolMuslim_P > 0 ? round(($schoolHadir_P / $schoolMuslim_P) * 100, 1) : 0;

        $stats = [
            'total_students'   => $schoolTotal,
            'total_l'          => $schoolTotal_L,
            'total_p'          => $schoolTotal_P,
            'muslim_total'     => $schoolMuslim,
            'muslim_l'         => $schoolMuslim_L,
            'muslim_p'         => $schoolMuslim_P,
            'non_muslim_total' => $schoolNonMuslim,
            'non_muslim_l'     => $schoolNonMuslim_L,
            'non_muslim_p'     => $schoolNonMuslim_P,
            'hadir'            => $schoolHadir,
            'hadir_l'          => $schoolHadir_L,
            'hadir_p'          => $schoolHadir_P,
            'ijin'             => $schoolIjin,
            'ijin_l'           => $schoolIjin_L,
            'ijin_p'           => $schoolIjin_P,
            'tidak_hadir'      => $schoolTidakHadir,
            'tidak_hadir_l'    => $schoolTidakHadir_L,
            'tidak_hadir_p'    => $schoolTidakHadir_P,
            'belum'            => $schoolBelum,
            'belum_l'          => $schoolBelum_L,
            'belum_p'          => $schoolBelum_P,
            'persen'           => $schoolPct,
            'persen_l'         => $schoolPct_L,
            'persen_p'         => $schoolPct_P,
        ];

        return [
            'academic_year'      => $ay,
            'date'               => $date,
            'date_formatted'     => $dateObj->locale('id')->translatedFormat('l, d F Y'),
            'is_holiday'         => $isHoliday,
            'holiday_desc'       => $holidayDesc,
            'stats'              => $stats,
            'classesSummary'     => $classesSummary,
            'unattendedStudents' => $unattendedStudents,
            'allStudents'        => $allStudents,
            'gradeGroups'        => array_values($gradeGroups),
        ];
    }
}

