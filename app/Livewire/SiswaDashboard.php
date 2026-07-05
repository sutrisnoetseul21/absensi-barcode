<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Presensi;

class SiswaDashboard extends Component
{
    public $student;
    public $enrollment;
    public $selectedMonth;
    
    public $daysInMonth = 0;
    public $todayDate;
    
    public $monthlyStats = [];
    public $attendanceData = [];

    public function mount()
    {
        $this->student = Auth::guard('siswa')->user();
        $this->enrollment = $this->student->enrollmentAktif()->with(['kelas', 'tahunAjaran'])->first();
        $this->selectedMonth = date('m');
        $this->loadData();
    }

    public function updatedSelectedMonth()
    {
        $this->loadData();
    }

    public function loadData()
    {
        if (!$this->enrollment) return;

        $year = date('Y');
        $this->daysInMonth = Carbon::createFromDate($year, $this->selectedMonth, 1)->daysInMonth;
        $this->todayDate = date('Y-m-d');

        $presensiQuery = Presensi::where('student_id', $this->student->id)
            ->where('enrollment_id', $this->enrollment->id)
            ->whereMonth('date', $this->selectedMonth)
            ->whereYear('date', $year);

        $records = $presensiQuery->get();

        $this->attendanceData = [];
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
            ];

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
    }

    public function render()
    {
        return view('livewire.siswa-dashboard');
    }
}
