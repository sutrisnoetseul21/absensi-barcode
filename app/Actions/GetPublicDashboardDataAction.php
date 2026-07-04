<?php

namespace App\Actions;

use App\Models\Kelas;
use App\Models\Presensi;
use App\Models\EnrollmentSiswa;
use App\Models\PengaturanSekolah;
use App\Models\HariLibur;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class GetPublicDashboardDataAction
{
    public function execute(?string $academicYearId, ?int $month, ?int $year): array
    {
        if (!$academicYearId || !$month || !$year) {
            return $this->emptyData();
        }

        $cacheKey = "public_dashboard_data_{$academicYearId}_{$year}_{$month}";
        
        return Cache::remember($cacheKey, 300, function () use ($academicYearId, $month, $year) {
            // Get all classes
            $classes = Kelas::orderBy('grade_level')->orderBy('name')->get();
            
            // Total students per class
            $totalStudents = EnrollmentSiswa::where('academic_year_id', $academicYearId)
                ->where('status', 'aktif')
                ->selectRaw('class_id, COUNT(*) as total')
                ->groupBy('class_id')
                ->pluck('total', 'class_id');

            // Today's attendance per class
            $today = today()->toDateString();
            $presentToday = Presensi::where('academic_year_id', $academicYearId)
                ->where('date', $today)
                ->whereIn('status', ['hadir', 'telat'])
                ->selectRaw('class_id, COUNT(*) as total')
                ->groupBy('class_id')
                ->pluck('total', 'class_id');

            // Effective days this month
            // TODO: formula hari efektif belum memperhitungkan libur khusus per kelas
            $daysInMonth = Carbon::create($year, $month)->daysInMonth;
            $workDays = 0;
            for ($day = 1; $day <= $daysInMonth; $day++) {
                $date = Carbon::create($year, $month, $day);
                if (!$date->isWeekend()) {
                    $workDays++;
                }
            }
            
            $globalHolidays = HariLibur::whereMonth('start_date', $month)
                ->whereYear('start_date', $year)
                ->whereNull('class_id')
                ->count(); // Simplified logic
                
            $effectiveDays = max(1, $workDays - $globalHolidays);

            // Monthly attendance per class
            $monthlyPresent = Presensi::where('academic_year_id', $academicYearId)
                ->whereMonth('date', $month)
                ->whereYear('date', $year)
                ->whereIn('status', ['hadir', 'telat'])
                ->selectRaw('class_id, COUNT(*) as total')
                ->groupBy('class_id')
                ->pluck('total', 'class_id');

            // Process data for UI
            $processedClasses = [];
            $barData7 = ['labels' => [], 'data' => []];
            $barData8 = ['labels' => [], 'data' => []];
            $barData9 = ['labels' => [], 'data' => []];
            
            $wallOfFameRaw = [];

            foreach ($classes as $kelas) {
                $students = $totalStudents->get($kelas->id, 0);
                $todayP = $presentToday->get($kelas->id, 0);
                $monthP = $monthlyPresent->get($kelas->id, 0);

                $todayPercentage = $students > 0 ? round(($todayP / $students) * 100, 1) : 0;
                $monthPercentage = ($students > 0 && $effectiveDays > 0) 
                    ? round(($monthP / ($students * $effectiveDays)) * 100, 1) : 0;

                $monthPercentage = min(100, $monthPercentage); // cap at 100%

                $processedClasses[] = [
                    'id' => $kelas->id,
                    'name' => $kelas->name,
                    'grade_level' => $kelas->grade_level,
                    'total_students' => $students,
                    'present_today' => $todayP,
                    'today_percentage' => $todayPercentage,
                    'month_percentage' => $monthPercentage,
                ];

                $wallOfFameRaw[] = [
                    'name' => $kelas->name,
                    'percentage' => $monthPercentage
                ];

                if ($kelas->grade_level == 7) {
                    $barData7['labels'][] = $kelas->name;
                    $barData7['data'][] = $monthPercentage;
                } elseif ($kelas->grade_level == 8) {
                    $barData8['labels'][] = $kelas->name;
                    $barData8['data'][] = $monthPercentage;
                } elseif ($kelas->grade_level == 9) {
                    $barData9['labels'][] = $kelas->name;
                    $barData9['data'][] = $monthPercentage;
                }
            }

            // Wall of Fame: Top 5
            usort($wallOfFameRaw, fn($a, $b) => $b['percentage'] <=> $a['percentage']);
            $wallOfFame = array_slice($wallOfFameRaw, 0, 5);

            // Donut Chart logic
            $totalActiveStudents = $totalStudents->sum();
            
            $statusCounts = Presensi::where('academic_year_id', $academicYearId)
                ->where('date', $today)
                ->selectRaw('status, COUNT(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status');

            $settings = PengaturanSekolah::first();
            $now = now();
            $checkinTime = $settings ? $settings->checkin_time : '07:00:00';
            
            $closingTimeString = Carbon::parse($checkinTime)->addMinutes(60)->toTimeString();
            $closingTime = Carbon::parse($today . ' ' . $closingTimeString);

            if ($now->lessThanOrEqualTo($closingTime)) {
                $belumAbsen = $totalActiveStudents - $statusCounts->sum();
                $donutData = [
                    'hadir' => $statusCounts->get('hadir', 0),
                    'telat' => $statusCounts->get('telat', 0),
                    'sakit' => $statusCounts->get('sakit', 0),
                    'izin' => $statusCounts->get('izin', 0),
                    'alpa' => $statusCounts->get('alpa', 0),
                    'belum_absen' => max(0, $belumAbsen)
                ];
            } else {
                $sudahAbsen = $statusCounts->whereIn('status', ['hadir', 'telat', 'sakit', 'izin'])->sum();
                $alpaDinamis = $totalActiveStudents - $sudahAbsen;
                $donutData = [
                    'hadir' => $statusCounts->get('hadir', 0),
                    'telat' => $statusCounts->get('telat', 0),
                    'sakit' => $statusCounts->get('sakit', 0),
                    'izin' => $statusCounts->get('izin', 0),
                    'alpa' => max(0, $alpaDinamis),
                    'belum_absen' => 0
                ];
            }

            // Line Chart (30 days trend)
            $lineData = ['labels' => [], 'data' => []];
            $startDate = today()->subDays(29);
            
            $trendData = Presensi::where('academic_year_id', $academicYearId)
                ->whereBetween('date', [$startDate->toDateString(), $today])
                ->whereIn('status', ['hadir', 'telat'])
                ->selectRaw('date, COUNT(*) as total')
                ->groupBy('date')
                ->pluck('total', 'date');

            for ($i = 0; $i < 30; $i++) {
                $d = $startDate->copy()->addDays($i);
                $dateStr = $d->toDateString();
                
                if (!$d->isWeekend()) {
                    $lineData['labels'][] = $d->format('d/m');
                    $present = $trendData->get($dateStr, 0);
                    $perc = $totalActiveStudents > 0 ? round(($present / $totalActiveStudents) * 100, 1) : 0;
                    $lineData['data'][] = min(100, $perc);
                }
            }

            return [
                'allClasses' => $processedClasses,
                'barData7' => $barData7,
                'barData8' => $barData8,
                'barData9' => $barData9,
                'donutData' => $donutData,
                'lineData' => $lineData,
                'wallOfFame' => $wallOfFame
            ];
        });
    }

    private function emptyData(): array
    {
        return [
            'allClasses' => [],
            'barData7' => ['labels' => [], 'data' => []],
            'barData8' => ['labels' => [], 'data' => []],
            'barData9' => ['labels' => [], 'data' => []],
            'donutData' => ['hadir'=>0,'telat'=>0,'sakit'=>0,'izin'=>0,'alpa'=>0,'belum_absen'=>0],
            'lineData' => ['labels' => [], 'data' => []],
            'wallOfFame' => []
        ];
    }
}
