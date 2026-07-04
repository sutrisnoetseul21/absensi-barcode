<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Kelas;
use App\Models\TahunAjaran;
use App\Models\Presensi;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class WaliKelasDashboard extends Component
{
    public $classes = [];
    public $selectedClassId;
    public $selectedMonth;
    public $academicYears = [];
    public $selectedAcademicYearId;

    public $students = [];
    public $monthlyStats = [];
    public $alerts = [];
    public $todayStats = [];

    public function mount()
    {
        $this->selectedMonth = date('m');
        $this->academicYears = TahunAjaran::orderBy('start_year', 'desc')->get();

        $activeYear = TahunAjaran::where('status', 'aktif')->first() ?? $this->academicYears->first();
        if ($activeYear) {
            $this->selectedAcademicYearId = $activeYear->id;
        }

        $this->loadClasses();
    }

    public function loadClasses()
    {
        if (!$this->selectedAcademicYearId) {
            $this->classes = [];
            $this->selectedClassId = null;
            return;
        }

        $actor = Auth::guard('wali_kelas')->check() ? Auth::guard('wali_kelas')->user() : Auth::guard('web')->user();

        if (Auth::guard('wali_kelas')->check()) {
            $this->classes = Kelas::whereHas('kelasAjarans', function ($query) use ($actor) {
                $query->where('academic_year_id', $this->selectedAcademicYearId)
                      ->where('teacher_id', $actor->id);
            })->get();
        } else {
            // Mode Admin: tampilkan semua kelas tanpa batasan assignment
            $this->classes = Kelas::orderBy('name', 'asc')->get();
        }

        if ($this->classes->isNotEmpty()) {
            if (!$this->classes->contains('id', $this->selectedClassId)) {
                $this->selectedClassId = $this->classes->first()->id;
            }
        } else {
            $this->selectedClassId = null;
            $this->todayStats = [];
        }

        $this->loadDashboardData();
    }

    public function updatedSelectedAcademicYearId()
    {
        $this->loadClasses();
    }

    public function updatedSelectedClassId()
    {
        if (Auth::guard('wali_kelas')->check()) {
            if (!$this->classes->contains('id', $this->selectedClassId)) {
                abort(403, 'Unauthorized action.');
            }
        }
        $this->loadDashboardData();
    }

    public function updatedSelectedMonth()
    {
        $this->loadDashboardData();
    }

    public function loadDashboardData()
    {
        if (!$this->selectedClassId || !$this->selectedAcademicYearId) {
            $this->students = collect();
            $this->monthlyStats = [];
            $this->alerts = [];
            $this->todayStats = [];
            return;
        }

        $ay = TahunAjaran::find($this->selectedAcademicYearId);
        if (!$ay) return;

        $startYear = $ay->start_year ?? ((int)date('Y') - 1);
        $endYear = $ay->end_year ?? (int)date('Y');

        $monthInt = (int)$this->selectedMonth;
        $calendarYear = ($monthInt >= 7) ? $startYear : $endYear;

        $kelas = Kelas::with(['enrollments' => function($q) {
            $q->where('academic_year_id', $this->selectedAcademicYearId)
              ->where('status', 'aktif')
              ->with('siswa');
        }])->find($this->selectedClassId);

        $this->students = $kelas ? $kelas->enrollments->pluck('siswa')->filter() : collect();

        // Calculate Today's Stats
        $todayDate = Carbon::now('Asia/Jakarta')->toDateString();
        $todayAtts = Presensi::where('academic_year_id', $this->selectedAcademicYearId)
            ->where('class_id', $this->selectedClassId)
            ->where('date', $todayDate)
            ->get();

        $totalStudentsCount = $this->students->count();
        $todayHadir = $todayAtts->where('status', 'hadir')->count();
        $todayTelat = $todayAtts->where('status', 'telat')->count();
        $todayIzin = $todayAtts->where('status', 'izin')->count();
        $todaySakit = $todayAtts->where('status', 'sakit')->count();
        $todayAlpa = $todayAtts->where('status', 'alpa')->count();
        $todayBelum = max(0, $totalStudentsCount - ($todayHadir + $todayTelat + $todayIzin + $todaySakit + $todayAlpa));

        $this->todayStats = [
            'hadir' => $todayHadir,
            'telat' => $todayTelat,
            'izin' => $todayIzin,
            'sakit' => $todaySakit,
            'alpa' => $todayAlpa,
            'belum' => $todayBelum,
            'total' => $totalStudentsCount,
            'persentase_hadir' => $totalStudentsCount > 0 
                ? round((($todayHadir + $todayTelat) / $totalStudentsCount) * 100) 
                : 0
        ];

        $startDate = Carbon::create($calendarYear, $this->selectedMonth, 1)->startOfMonth()->toDateString();
        $endDate = Carbon::create($calendarYear, $this->selectedMonth, 1)->endOfMonth()->toDateString();
        
        $attendances = Presensi::where('academic_year_id', $this->selectedAcademicYearId)
            ->where('class_id', $this->selectedClassId)
            ->whereBetween('date', [$startDate, $endDate])
            ->get();

        $stats = [];
        foreach ($this->students as $student) {
            $studentAtts = $attendances->where('student_id', $student->id);
            $stats[$student->id] = [
                'hadir' => $studentAtts->where('status', 'hadir')->count(),
                'telat' => $studentAtts->where('status', 'telat')->count(),
                'izin' => $studentAtts->where('status', 'izin')->count(),
                'sakit' => $studentAtts->where('status', 'sakit')->count(),
                'alpa' => $studentAtts->where('status', 'alpa')->count(),
                'total_late_minutes' => $studentAtts->sum('late_minutes'),
            ];
        }
        $this->monthlyStats = $stats;

        // Alerts (>=3 Alpa atau >=100 menit telat)
        $alpaTerlaluBanyak = Presensi::where('academic_year_id', $this->selectedAcademicYearId)
            ->where('class_id', $this->selectedClassId)
            ->whereBetween('date', [$startDate, $endDate])
            ->where('status', 'alpa')
            ->selectRaw('student_id, count(*) as total')
            ->groupBy('student_id')
            ->havingRaw('count(*) >= 3')
            ->pluck('student_id')->toArray();

        $telatTerlaluBanyak = Presensi::where('academic_year_id', $this->selectedAcademicYearId)
            ->where('class_id', $this->selectedClassId)
            ->whereBetween('date', [$startDate, $endDate])
            ->where('status', 'telat')
            ->selectRaw('student_id, sum(late_minutes) as total')
            ->groupBy('student_id')
            ->havingRaw('sum(late_minutes) >= 100')
            ->pluck('student_id')->toArray();

        $this->alerts = [
            'alpa' => $alpaTerlaluBanyak,
            'telat' => $telatTerlaluBanyak,
        ];
    }

    public function render()
    {
        return view('livewire.wali-kelas-dashboard');
    }
}
