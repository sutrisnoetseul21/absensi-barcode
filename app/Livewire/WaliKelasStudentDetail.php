<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Presensi;
use App\Models\Siswa;
use App\Models\KelasAjaran;

class WaliKelasStudentDetail extends Component
{
    public $student;
    public $enrollment;
    public $selectedMonthYear;
    public $availableMonths = [];
    
    public $daysInMonth = 0;
    public $startOfMonthOffset = 0;
    public $todayDate;
    
    public $monthlyStats = [];
    public $attendanceData = [];
    public $attendancePercentage = 0;
    public $recentActivity = [];
    public $holidays = [];

    public function mount($id)
    {
        $this->student = Siswa::findOrFail($id);
        
        $actor = Auth::guard('wali_kelas')->user();
        
        // Find the active enrollment for this student
        $this->enrollment = $this->student->enrollmentAktif()->with(['kelas', 'tahunAjaran'])->first();
        
        if (!$this->enrollment) {
            abort(404, 'Siswa ini tidak memiliki riwayat pendaftaran aktif.');
        }

        // Verify if the authenticated teacher is indeed the Wali Kelas for this enrollment
        $isAdminMode = Auth::guard('web')->check();
        
        if (!$isAdminMode && $actor) {
            $isWaliKelas = KelasAjaran::where('class_id', $this->enrollment->class_id)
                ->where('academic_year_id', $this->enrollment->academic_year_id)
                ->where('teacher_id', $actor->id)
                ->exists();
                
            if (!$isWaliKelas) {
                abort(403, 'Akses Ditolak. Siswa ini bukan anggota dari kelas yang Anda bina saat ini.');
            }
        }
        
        $this->generateAvailableMonths();
        
        $currentMonthYear = date('m-Y');
        if (array_key_exists($currentMonthYear, $this->availableMonths)) {
            $this->selectedMonthYear = $currentMonthYear;
        } else {
            // Default to the first available month if current is not in academic year
            $this->selectedMonthYear = array_key_first($this->availableMonths) ?? date('m-Y');
        }
        
        $this->loadData();
    }
    
    private function generateAvailableMonths()
    {
        $this->availableMonths = [];
        if ($this->enrollment && $this->enrollment->tahunAjaran) {
            $sy = $this->enrollment->tahunAjaran->start_year;
            $ey = $this->enrollment->tahunAjaran->end_year;
            
            $monthNames = [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
            ];
            
            // Juli - Desember (Start Year)
            for ($m = 7; $m <= 12; $m++) {
                $key = sprintf('%02d-%d', $m, $sy);
                $this->availableMonths[$key] = $monthNames[$m] . ' ' . $sy;
            }
            // Januari - Juni (End Year)
            for ($m = 1; $m <= 6; $m++) {
                $key = sprintf('%02d-%d', $m, $ey);
                $this->availableMonths[$key] = $monthNames[$m] . ' ' . $ey;
            }
        }
    }

    public function updatedSelectedMonthYear()
    {
        $this->loadData();
    }

    public function loadData()
    {
        if (!$this->enrollment || !$this->selectedMonthYear) return;

        $parts = explode('-', $this->selectedMonthYear);
        if (count($parts) !== 2) return;
        
        $month = (int)$parts[0];
        $year = (int)$parts[1];

        $startOfMonth = Carbon::createFromDate($year, $month, 1);
        $this->daysInMonth = $startOfMonth->daysInMonth;
        
        // Offset (1 = Monday -> offset 0)
        $this->startOfMonthOffset = $startOfMonth->dayOfWeekIso - 1; 
        
        $this->todayDate = date('Y-m-d');

        $presensiQuery = Presensi::where('student_id', $this->student->id)
            ->where('enrollment_id', $this->enrollment->id)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->orderBy('date', 'desc');

        $records = $presensiQuery->get();

        $this->attendanceData = [];
        $this->recentActivity = [];
        
        $totalH = 0;
        $totalT = 0;
        $totalS = 0;
        $totalI = 0;
        $totalA = 0;
        $totalLateMinutes = 0;

        foreach ($records as $p) {
            $day = (int)date('j', strtotime($p->date));
            $this->attendanceData[$day] = [
                'status' => strtolower($p->status),
                'late_minutes' => $p->late_minutes,
                'scan_time' => $p->scan_time ? substr($p->scan_time, 0, 5) : null,
                'date' => $p->date->format('Y-m-d'),
                'is_manual_input' => $p->is_manual_input,
            ];

            // For recent activity list (show max 5 recent records)
            if (count($this->recentActivity) < 5) {
                $this->recentActivity[] = $p;
            }

            if (strtolower($p->status) === 'hadir') $totalH++;
            if (strtolower($p->status) === 'telat') {
                $totalT++;
                $totalLateMinutes += $p->late_minutes;
            }
            if (strtolower($p->status) === 'sakit') $totalS++;
            if (strtolower($p->status) === 'izin') $totalI++;
            if (strtolower($p->status) === 'alpa') $totalA++;
        }

        $this->monthlyStats = [
            'H' => $totalH,
            'T' => $totalT,
            'S' => $totalS,
            'I' => $totalI,
            'A' => $totalA,
            'late_minutes' => $totalLateMinutes,
        ];
        
        // Calculate percentage
        $endCalcDate = $startOfMonth->copy()->endOfMonth();
        if ($year == date('Y') && $month == date('m')) {
            $endCalcDate = Carbon::today('Asia/Jakarta');
        }
        
        $kalenderService = app(\App\Services\KalenderSekolahService::class);
        
        $this->holidays = [];
        for ($day = 1; $day <= $this->daysInMonth; $day++) {
            $dateObj = Carbon::create($year, $month, $day);
            $this->holidays[$day] = !$kalenderService->isHariSekolah($dateObj, $this->enrollment->class_id);
        }

        $effectiveDays = $kalenderService->getEffectiveDays($startOfMonth, $endCalcDate, $this->enrollment->class_id);
        $effectiveDays = max(1, $effectiveDays);
        
        $presentCount = $totalH + $totalT;
        $this->attendancePercentage = min(100, round(($presentCount / $effectiveDays) * 100, 1));
    }

    public function render()
    {
        return view('livewire.wali-kelas-student-detail')->layout('components.layouts.app');
    }
}
